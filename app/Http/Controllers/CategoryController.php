<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
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

    public function show($id)
    {
        return redirect()->route('categories.index', ['category' => $id]);
    }
}
