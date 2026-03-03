<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display all categories.
     */
    public function index(Request $request)
    {
        $query = Category::withCount(['products'])
            ->orderBy('name');

        // Search
        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $categories = $query->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug']      = Category::generateSlug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$data['name']}\" created successfully.");
    }

    /**
     * Return category data as JSON for the edit modal.
     */
    public function show(Category $category)
    {
        return response()->json($category);
    }

    /**
     * Update an existing category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        // Re-generate slug only if name changed
        if ($data['name'] !== $category->name) {
            $data['slug'] = Category::generateSlug($data['name'], $category->id);
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$category->name}\" updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$category->name}\" {$status}.");
    }

    /**
     * Delete a category (only if it has no products).
     */
    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', "Cannot delete \"{$category->name}\" — it has products assigned. Deactivate it instead.");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$name}\" deleted successfully.");
    }
}
