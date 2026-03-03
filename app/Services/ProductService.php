<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
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
     * Create a new product with optional image.
     */
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        if ($image) {
            $data['image'] = $this->uploadImage($image);
        }

        $data['show_in_catalog'] = isset($data['show_in_catalog']) ? (bool)$data['show_in_catalog'] : true;
        $data['is_active']       = isset($data['is_active']) ? (bool)$data['is_active'] : true;

        return Product::create($data);
    }

    /**
     * Update an existing product with optional image change.
     */
    public function update(Product $product, array $data, ?UploadedFile $image = null, bool $removeImage = false): Product
    {
        if ($removeImage && $product->image) {
            $this->deleteImage($product->image);
            $data['image'] = null;
        }

        if ($image) {
            // Delete old image before replacing
            $this->deleteImage($product->image);
            $data['image'] = $this->uploadImage($image);
        }

        $data['show_in_catalog'] = isset($data['show_in_catalog']) ? (bool)$data['show_in_catalog'] : false;
        $data['is_active']       = isset($data['is_active']) ? (bool)$data['is_active'] : false;

        $product->update($data);
        return $product->fresh();
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
