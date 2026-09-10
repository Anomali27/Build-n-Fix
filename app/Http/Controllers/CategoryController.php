<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService,
        protected ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $role = session('user.role', 'customer');
        $selectedCategory = $request->get('category', 'all');
        $selectedBranches = (array) $request->get('branches', []);
        $location = $request->get('location');
        $sort = $request->get('sort', 'terpopuler');
        $view = $request->get('view', 'grid');
        $search = $request->get('q');

        $result = $this->categoryService->getFilteredCategories([
            'category' => $selectedCategory,
            'branches' => $selectedBranches,
            'location' => $location,
            'search' => $search,
        ], $sort);

        return view('categories.index', array_merge($result, [
            'role' => $role,
            'selectedCategory' => $selectedCategory,
            'selectedBranches' => $selectedBranches,
            'location' => $location,
            'sort' => $sort,
            'view' => $view,
            'search' => $search,
        ]));
    }

    public function create()
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat menambah kategori.');
        }

        return view('categories.create', ['role' => $role]);
    }

    public function store(Request $request)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat menyimpan kategori.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|string',
        ]);

        $category = $this->categoryService->createCategory($request->only([
            'name', 'description', 'status', 'image', 'branches',
        ]));

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "'.$category['name'].'" berhasil ditambahkan.');
    }

    public function show($category, Request $request)
    {
        $role = session('user.role', 'customer');
        $categoryData = $this->categoryService->getCategoryByIdOrSlug($category);

        if (! $categoryData) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak ditemukan.');
        }

        $sort = $request->get('sort', 'terpopuler');
        $view = $request->get('view', 'grid');
        $selectedBranches = (array) $request->get('branches', []);
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');

        // Fetch products using ProductService
        $productResult = $this->productService->getFilteredProducts([
            'category' => $categoryData['id'],
            'branches' => $selectedBranches,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ], $sort);

        return view('categories.show', array_merge($productResult, [
            'role' => $role,
            'category' => $categoryData,
            'sort' => $sort,
            'view' => $view,
            'selectedBranches' => $selectedBranches,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]));
    }

    public function edit($category)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat mengubah kategori.');
        }

        $categoryData = $this->categoryService->getCategoryByIdOrSlug($category);

        if (! $categoryData) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak ditemukan.');
        }

        return view('categories.edit', [
            'role' => $role,
            'category' => $categoryData,
        ]);
    }

    public function update(Request $request, $category)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat mengubah kategori.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image' => 'nullable|string',
        ]);

        $updated = $this->categoryService->updateCategory($category, $request->only([
            'name', 'description', 'status', 'image', 'branches',
        ]));

        if (! $updated) {
            return redirect()->route('categories.index')->with('error', 'Gagal memperbarui kategori.');
        }

        return redirect()->route('categories.show', $updated['slug'])
            ->with('success', 'Kategori "'.$updated['name'].'" berhasil diperbarui.');
    }

    public function destroy($category)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat menghapus kategori.');
        }

        $deleted = $this->categoryService->deleteCategory($category);

        if (! $deleted) {
            return redirect()->route('categories.index')->with('error', 'Gagal menghapus kategori.');
        }

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
