<?php

namespace App\Http\Controllers;

use App\Repositories\CategoryRepositories;
use App\Repositories\ProductRepositories;
use App\Repositories\SupplierRepositories;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $role = session('user.role', 'customer');
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
            $categories = CategoryRepositories::getAll();
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
            'role' => $role,
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

    public function create()
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang memiliki akses untuk menambah produk.');
        }

        $categories = CategoryRepositories::getAll();
        $suppliers = SupplierRepositories::getAll();

        return view('products.create', compact('categories', 'suppliers', 'role'));
    }

    public function store(Request $request)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang dapat menyimpan produk.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'price' => 'required|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'brand' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'stock_serdam' => 'nullable|numeric|min:0',
            'stock_gajahmada' => 'nullable|numeric|min:0',
            'stock_kotabaru' => 'nullable|numeric|min:0',
        ]);

        $category = CategoryRepositories::getAll();
        $catName = 'Semen, Pasir & Mortar';
        foreach ($category as $c) {
            if ((string) $c['id'] === (string) $request->input('category_id')) {
                $catName = $c['name'];
                break;
            }
        }

        $productData = [
            'name' => $request->input('name'),
            'sku' => $request->input('sku'),
            'category_id' => (int) $request->input('category_id'),
            'category' => $catName,
            'brand' => $request->input('brand', 'Build n Fix'),
            'unit' => $request->input('unit', 'Unit'),
            'price' => (int) $request->input('price'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description', substr($request->input('description', ''), 0, 120)),
            'image' => $request->input('image') ?: 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80',
            'stock_serdam' => (int) $request->input('stock_serdam', 50),
            'stock_gajahmada' => (int) $request->input('stock_gajahmada', 40),
            'stock_kotabaru' => (int) $request->input('stock_kotabaru', 30),
        ];

        $newProduct = ProductRepositories::create($productData);

        // If supplier assigned
        if ($request->filled('supplier_id')) {
            SupplierRepositories::assignProduct((int) $request->input('supplier_id'), (int) $newProduct['id']);
        }

        return redirect()->route('products.index')->with('success', 'Produk "'.$newProduct['name'].'" berhasil ditambahkan.');
    }

    public function show(string|int $categoryOrProduct, ?string $productSlug = null)
    {
        $role = session('user.role', 'customer');

        // Handles both /categories/{category}/{product} and /products/{product}
        $targetProduct = $productSlug ?: $categoryOrProduct;
        $targetCategory = $productSlug ? $categoryOrProduct : null;

        $data = null;
        if ($targetCategory) {
            $data = $this->productService->getProductDetail($targetCategory, $targetProduct);
        }

        if (! $data) {
            $productItem = ProductRepositories::find($targetProduct);
            if ($productItem) {
                $catSlug = $productItem['category_slug'] ?? 'kategori';
                $data = $this->productService->getProductDetail($catSlug, (string) $productItem['id']);
            }
        }

        if (! $data) {
            abort(404, 'Produk tidak ditemukan');
        }

        // Get suppliers for this product
        $productId = (int) ($data['product']['id'] ?? 0);
        $suppliers = SupplierRepositories::getSuppliersForProduct($productId);

        return view('products.show', array_merge($data, [
            'role' => $role,
            'suppliers' => $suppliers,
        ]));
    }

    public function edit(string|int $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang dapat mengedit produk.');
        }

        $product = ProductRepositories::find($id);
        if (! $product) {
            return redirect()->route('products.index')->with('error', 'Produk tidak ditemukan.');
        }

        $categories = CategoryRepositories::getAll();
        $suppliers = SupplierRepositories::getAll();
        $assignedSuppliers = SupplierRepositories::getSuppliersForProduct((int) $product['id']);

        return view('products.edit', compact('product', 'categories', 'suppliers', 'assignedSuppliers', 'role'));
    }

    public function update(Request $request, string|int $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang dapat mengubah produk.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'price' => 'required|numeric|min:0',
        ]);

        $category = CategoryRepositories::getAll();
        $catName = 'Semen, Pasir & Mortar';
        foreach ($category as $c) {
            if ((string) $c['id'] === (string) $request->input('category_id')) {
                $catName = $c['name'];
                break;
            }
        }

        $updateData = [
            'name' => $request->input('name'),
            'sku' => $request->input('sku'),
            'category_id' => (int) $request->input('category_id'),
            'category' => $catName,
            'brand' => $request->input('brand', 'Build n Fix'),
            'unit' => $request->input('unit', 'Unit'),
            'price' => (int) $request->input('price'),
            'description' => $request->input('description'),
            'short_description' => $request->input('short_description', substr($request->input('description', ''), 0, 120)),
        ];

        if ($request->filled('image')) {
            $updateData['image'] = $request->input('image');
        }

        $updated = ProductRepositories::update($id, $updateData);

        if ($request->filled('supplier_id')) {
            SupplierRepositories::assignProduct((int) $request->input('supplier_id'), (int) $id);
        }

        return redirect()->route('products.index')->with('success', 'Produk "'.$updated['name'].'" berhasil diperbarui.');
    }

    public function destroy(string|int $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang dapat menghapus produk.');
        }

        $deleted = ProductRepositories::delete($id);

        if (! $deleted) {
            return redirect()->route('products.index')->with('error', 'Gagal menghapus produk.');
        }

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
