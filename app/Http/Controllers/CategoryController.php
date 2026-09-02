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
        $selectedCategory = $request->get('category', 'all');
        $selectedBranches = (array) $request->get('branches', []);
        $sort = $request->get('sort', 'terpopuler');
        $view = $request->get('view', 'grid');
        $search = $request->get('q');

        $result = $this->categoryService->getFilteredCategories([
            'category' => $selectedCategory,
            'branches' => $selectedBranches,
            'search' => $search,
        ], $sort);

        return view('categories.index', array_merge($result, [
            'selectedCategory' => $selectedCategory,
            'selectedBranches' => $selectedBranches,
            'sort' => $sort,
            'view' => $view,
            'search' => $search,
        ]));
    }

    public function show($category, Request $request)
    {
        $categoryData = $this->categoryService->getCategoryByIdOrSlug($category);

        if (!$categoryData) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak ditemukan.');
        }

        $sort = $request->get('sort', 'terpopuler');
        $view = $request->get('view', 'grid');
        $selectedBranches = (array) $request->get('branches', []);
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');

        // Fetch products specifically belonging to this category
        $productResult = $this->productService->getFilteredProducts([
            'category' => $categoryData['id'],
            'branches' => $selectedBranches,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
        ], $sort);

        return view('categories.show', array_merge($productResult, [
            'category' => $categoryData,
            'sort' => $sort,
            'view' => $view,
            'selectedBranches' => $selectedBranches,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]));
    }
}
