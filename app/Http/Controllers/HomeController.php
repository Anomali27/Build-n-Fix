<?php

namespace App\Http\Controllers;

use App\Services\BranchService;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\ReviewService;

class HomeController extends Controller
{
    public function __construct(
        protected BranchService $branchService,
        protected CategoryService $categoryService,
        protected ProductService $productService,
        protected ReviewService $reviewService
    ) {}

    public function index()
    {
        $branches = $this->branchService->getAllBranches();
        $categories = $this->categoryService->getAllCategories();
        $products = $this->productService->getFeaturedProducts();
        $reviews = $this->reviewService->getAllReviews();

        return view('home.index', compact('branches', 'categories', 'products', 'reviews'));
    }
}
