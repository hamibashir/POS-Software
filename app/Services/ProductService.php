<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Generate a unique SKU from the product name.
     * Format: XX-NNNN  (2-letter prefix + 4-digit number)
     */
    public function generateSku(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 2));
        $prefix = str_pad($prefix, 2, 'X');

        do {
            $sku = $prefix . '-' . str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        } while (Product::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Handle image upload and return stored path.
     */
    public function uploadImage(UploadedFile $file): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('products', $filename, 'public');
        return 'products/' . $filename;
    }

    /**
     * Delete a product image from storage.
     */
    public function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Process image from file upload, base64 paste data, or image URL.
     */
    public function processImage(array &$data, ?UploadedFile $image = null, ?Product $existingProduct = null, bool $removeImage = false): void
    {
        if ($removeImage && $existingProduct?->image) {
            $this->deleteImage($existingProduct->image);
            $data['image'] = null;
        }

        if ($image) {
            if ($existingProduct?->image) {
                $this->deleteImage($existingProduct->image);
            }
            $data['image'] = $this->uploadImage($image);
        } elseif (!empty($data['image_base64'])) {
            if ($existingProduct?->image) {
                $this->deleteImage($existingProduct->image);
            }
            $saved = $this->saveBase64Image($data['image_base64']);
            if ($saved) {
                $data['image'] = $saved;
            }
        } elseif (!empty($data['image_url'])) {
            if ($existingProduct?->image) {
                $this->deleteImage($existingProduct->image);
            }
            $saved = $this->downloadAndSaveImageUrl($data['image_url']);
            if ($saved) {
                $data['image'] = $saved;
            }
        }

        unset($data['image_base64'], $data['image_url']);
    }

    /**
     * Save base64-encoded image data to public storage.
     */
    public function saveBase64Image(string $base64String): ?string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);
            if ($type === 'jpeg') $type = 'jpg';
            $decoded = base64_decode($data);
            if ($decoded === false) return null;

            $filename = Str::uuid() . '.' . $type;
            Storage::disk('public')->put('products/' . $filename, $decoded);
            return 'products/' . $filename;
        }
        return null;
    }

    /**
     * Download and save image from an external web URL to public storage.
     */
    public function downloadAndSaveImageUrl(string $url): ?string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(12)->get($url);
            if ($response->successful()) {
                $content = $response->body();
                $contentType = strtolower($response->header('Content-Type') ?? '');
                $ext = 'jpg';
                if (str_contains($contentType, 'png')) $ext = 'png';
                elseif (str_contains($contentType, 'webp')) $ext = 'webp';
                elseif (str_contains($contentType, 'gif')) $ext = 'gif';
                elseif (str_contains($contentType, 'jpeg')) $ext = 'jpg';

                $filename = Str::uuid() . '.' . $ext;
                Storage::disk('public')->put('products/' . $filename, $content);
                return 'products/' . $filename;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to download image from URL: {$url}. Error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Create a new product with optional image.
     */
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        $this->processImage($data, $image);

        $data['slug']            = Product::generateSlug($data['name']);
        $data['show_in_catalog'] = isset($data['show_in_catalog']) ? (bool)$data['show_in_catalog'] : true;
        $data['is_active']       = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        return Product::create($data);
    }

    /**
     * Update an existing product with optional image change.
     */
    public function update(Product $product, array $data, ?UploadedFile $image = null, bool $removeImage = false): Product
    {
        return DB::transaction(function () use ($product, $data, $image, $removeImage) {
            $this->processImage($data, $image, $product, $removeImage);

            // Regenerate slug if name changed
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = Product::generateSlug($data['name'], $product->id);
            }

            $data['show_in_catalog'] = isset($data['show_in_catalog']) ? (bool)$data['show_in_catalog'] : false;
            $data['is_active']       = isset($data['is_active']) ? (bool)$data['is_active'] : false;

            // Only administrators (or system processes) can modify stock quantity directly
            $isAdmin = !auth()->check() || auth()->user()->isAdmin();
            if (!$isAdmin) {
                unset($data['stock_quantity']);
            } elseif (isset($data['stock_quantity']) && (int)$data['stock_quantity'] !== (int)$product->stock_quantity) {
                $stockBefore = (int)$product->stock_quantity;
                $stockAfter  = (int)$data['stock_quantity'];
                $diff        = $stockAfter - $stockBefore;
                $type        = $diff > 0 ? 'adjustment_in' : 'adjustment_out';

                StockMovement::create([
                    'product_id'   => $product->id,
                    'type'         => $type,
                    'quantity'     => abs($diff),
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'user_id'      => auth()->id(),
                    'notes'        => 'Stock quantity updated directly via product management by admin',
                ]);
            }

            $product->update($data);
            return $product->fresh();
        });
    }

    /**
     * Soft-delete a product, cleanup image if needed.
     */
    public function delete(Product $product): void
    {
        // Do NOT delete the image on soft delete — it may still be shown in sales history
        $product->delete();
    }
}
