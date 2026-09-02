<?php

namespace App\Http\Controllers;

use App\Data\CategoryData;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $selectedCategory = $request->get('category', 'all');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $selectedBrands = (array) $request->get('brands', []);
        $selectedBranches = (array) $request->get('branches', []);
        $sort = $request->get('sort', 'terpopuler');
        $view = $request->get('view', 'grid');
        $search = $request->get('q');

        // Resolve Page Title
        $pageTitle = 'Semua Produk';
        if ($selectedCategory !== 'all' && $selectedCategory !== 'semua' && ! empty($selectedCategory)) {
            $categories = CategoryData::getAll();
            foreach ($categories as $cat) {
                if ((string) $cat['id'] === (string) $selectedCategory
                    || strtolower($cat['slug'] ?? '') === strtolower($selectedCategory)
                    || strtolower($cat['name']) === strtolower($selectedCategory)) {
                    $pageTitle = $cat['name'];
                    break;
                }
            }
        } elseif (! empty($search)) {
            $pageTitle = 'Hasil Pencarian: "'.e($search).'"';
        }

        $result = $this->productService->getFilteredProducts([
            'category' => $selectedCategory,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'brands' => $selectedBrands,
            'branches' => $selectedBranches,
            'search' => $search,
        ], $sort);

        return view('products.index', array_merge($result, [
            'pageTitle' => $pageTitle,
            'selectedCategory' => $selectedCategory,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'selectedBrands' => $selectedBrands,
            'selectedBranches' => $selectedBranches,
            'sort' => $sort,
            'view' => $view,
            'search' => $search,
        ]));
    }

    public function show($product)
    {
        $productData = $this->productService->getProductByIdOrSlug($product);

        if (! $productData) {
            return redirect()->route('products.index')->with('error', 'Produk tidak ditemukan.');
        }

        $category = $this->productService->getProductCategory($productData['category_id']);
        $branchStocks = $this->productService->getBranchStocksForProduct($productData['id']);
        $relatedProducts = $this->productService->getRelatedProducts($productData['category_id'], $productData['id'], 5);

        return view('products.show', [
            'product' => $productData,
            'category' => $category,
            'branchStocks' => $branchStocks,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
