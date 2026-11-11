<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Branch;
use App\Models\BranchStock;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $role = auth()->user()?->role ?? 'customer';
        $selectedCategory = (string) $request->get('category', 'all');
        $selectedBrands = array_values(array_filter((array) $request->get('brands', [])));
        $sort = (string) $request->get('sort', 'terpopuler');
        $view = (string) $request->get('view', 'grid');
        $search = $request->get('q');

        $isAllCategory = $selectedCategory === '' || in_array(strtolower($selectedCategory), ['all', 'semua'], true);
        $hasSearch = is_string($search) && trim($search) !== '';
        $hasBrandFilter = count($selectedBrands) > 0;
        $isTopPopularDefault = $isAllCategory && ! $hasSearch && ! $hasBrandFilter;

        $activeCategory = null;
        if (! $isAllCategory) {
            $activeCategory = Category::findByIdentifier($selectedCategory);
        }

        $pageTitle = 'Produk Terpopuler';
        if ($activeCategory) {
            $pageTitle = $activeCategory->name;
        } elseif ($hasSearch) {
            $pageTitle = 'Hasil Pencarian: "'.trim((string) $search).'"';
        } elseif (! $isTopPopularDefault) {
            $pageTitle = 'Semua Produk';
        }

        $query = Product::query()
            ->with(['category', 'branchStocks'])
            ->when($role === 'customer', fn (Builder $q) => $q->active())
            ->forCategory($selectedCategory)
            ->search(is_string($search) ? $search : null);

        if ($hasBrandFilter) {
            $query->whereIn('brand', $selectedBrands);
        }

        $limit = $isTopPopularDefault && $role === 'customer' ? 20 : null;

        if ($limit !== null) {
            $popularIds = Product::query()
                ->active()
                ->orderBy('popularity')
                ->limit($limit)
                ->pluck('id');
            $query->whereIn('id', $popularIds);
        }

        match (strtolower($sort)) {
            'harga-terendah' => $query->orderBy('price', 'asc'),
            'harga-tertinggi' => $query->orderBy('price', 'desc'),
            'nama-a-z' => $query->orderBy('name', 'asc'),
            'nama-z-a' => $query->orderBy('name', 'desc'),
            'terbaru' => $query->orderBy('id', 'desc'),
            default => $query->orderBy('popularity', 'asc'),
        };

        $products = $query->get();
        $total = $products->count();
        $from = $total > 0 ? 1 : 0;
        $to = $total;

        $sidebarCategories = Category::active()
            ->withCount(['products' => fn (Builder $q) => $q->active()])
            ->orderBy('popular_rank')
            ->orderBy('id')
            ->get()
            ->map(fn (Category $cat): array => [
                'id' => $cat->id,
                'slug' => $cat->slug,
                'name' => $cat->name,
                'count' => $cat->products_count,
            ])
            ->all();

        $sidebarBrands = Product::query()
            ->when($role === 'customer', fn (Builder $q) => $q->active())
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->selectRaw('brand as name, COUNT(*) as count')
            ->groupBy('brand')
            ->orderBy('brand')
            ->get()
            ->map(fn ($row): array => [
                'name' => (string) $row->name,
                'count' => (int) $row->count,
            ])
            ->all();

        return view('products.index', [
            'role' => $role,
            'pageTitle' => $pageTitle,
            'products' => $products,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'isTopPopularDefault' => $isTopPopularDefault && $role === 'customer',
            'sidebarCategories' => $sidebarCategories,
            'sidebarBrands' => $sidebarBrands,
            'selectedCategory' => $selectedCategory,
            'selectedBrands' => $selectedBrands,
            'sort' => $sort,
            'view' => $view,
            'search' => $search,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang memiliki akses untuk menambah produk.');
        }

        $categories = Category::orderBy('name')->get();
        $branches = Branch::orderBy('id')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'branches', 'suppliers', 'role'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim($validated['name']);
        $basePrice = (int) $validated['price'];
        $description = $validated['description'] ?? '';
        $shortDescription = ($validated['short_description'] ?? '') !== ''
            ? $validated['short_description']
            : Str::limit(strip_tags((string) $description), 140);

        $product = DB::transaction(function () use ($validated, $name, $basePrice, $description, $shortDescription, $request): Product {
            $sku = trim((string) ($validated['sku'] ?? ''));
            if ($sku === '') {
                $nextNum = ((int) Product::max('id')) + 1;
                $sku = 'PRD-'.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
                while (Product::where('sku', $sku)->exists()) {
                    $nextNum++;
                    $sku = 'PRD-'.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
                }
            }

            $imageUrl = ($validated['image'] ?? '') !== ''
                ? $validated['image']
                : 'https://images.unsplash.com/photo-1589939705384-5185137a7f0f?w=600&auto=format&fit=crop&q=80';

            $created = Product::create([
                'category_id' => (int) $validated['category_id'],
                'sku' => $sku,
                'name' => $name,
                'slug' => $this->generateUniqueSlug($name),
                'brand' => ($validated['brand'] ?? '') !== '' ? $validated['brand'] : 'Build n Fix',
                'size' => ($validated['size'] ?? '') !== '' ? $validated['size'] : (($validated['unit'] ?? '') !== '' ? $validated['unit'] : 'Unit'),
                'unit' => ($validated['unit'] ?? '') !== '' ? $validated['unit'] : 'Unit',
                'price' => $basePrice,
                'short_description' => $shortDescription,
                'description' => $description,
                'additional_info' => 'Tersedia pengiriman langsung ke lokasi proyek. Garansi kualitas dari Build n Fix.',
                'image' => $imageUrl,
                'gallery' => [$imageUrl],
                'status' => $validated['status'] ?? 'active',
                'popularity' => ((int) Product::max('popularity')) + 1,
            ]);

            $branches = Branch::orderBy('id')->get();
            $submittedStocks = (array) $request->input('stocks', []);
            $submittedPrices = (array) $request->input('prices', []);
            $legacyStockKeys = [
                'Serdam' => ['key' => 'stock_serdam', 'default' => 50],
                'Gajahmada' => ['key' => 'stock_gajahmada', 'default' => 40],
                'Kota Baru' => ['key' => 'stock_kotabaru', 'default' => 30],
            ];

            foreach ($branches as $index => $branch) {
                $legacy = $legacyStockKeys[$branch->name] ?? null;
                $stock = isset($submittedStocks[$branch->id])
                    ? (int) $submittedStocks[$branch->id]
                    : ($legacy ? (int) $request->input($legacy['key'], $legacy['default']) : 30);

                $branchPrice = isset($submittedPrices[$branch->id]) && (int) $submittedPrices[$branch->id] > 0
                    ? (int) $submittedPrices[$branch->id]
                    : $this->defaultBranchPrice($basePrice, $branch->name, $index);

                BranchStock::create([
                    'product_id' => $created->id,
                    'branch_id' => $branch->id,
                    'price' => $branchPrice,
                    'stock' => max(0, $stock),
                ]);
            }

            return $created;
        });

        if ($request->filled('supplier_id')) {
            $product->suppliers()->syncWithoutDetaching([(int) $request->input('supplier_id')]);
        }

        return redirect()->route('products.index')->with('success', 'Produk "'.$product->name.'" berhasil ditambahkan.');
    }

    public function show(string|int $categoryOrProduct, ?string $productSlug = null): View
    {
        $role = auth()->user()?->role ?? 'customer';
        $identifier = $productSlug ?? $categoryOrProduct;

        $product = Product::findByIdentifier($identifier);
        if (! $product) {
            abort(404, 'Produk tidak ditemukan');
        }

        $product->load(['category', 'branchStocks.branch']);

        if ($productSlug !== null && $product->category) {
            $requestedCat = strtolower((string) $categoryOrProduct);
            if ($requestedCat !== strtolower($product->category->slug)
                && $requestedCat !== (string) $product->category->id) {
                abort(404, 'Produk tidak ditemukan');
            }
        }

        $branches = Branch::orderBy('id')->get();
        $stocksByBranch = $product->branchStocks->keyBy('branch_id');

        $branchStocks = $branches->map(function (Branch $branch) use ($stocksByBranch, $product): array {
            /** @var BranchStock|null $record */
            $record = $stocksByBranch->get($branch->id);
            $stock = $record ? $record->stock : 0;
            $price = $record && $record->price > 0 ? $record->price : $product->price;

            if ($stock > 10) {
                $statusLabel = 'Tersedia';
                $statusColor = 'emerald';
            } elseif ($stock >= 1) {
                $statusLabel = 'Stok Menipis';
                $statusColor = 'amber';
            } else {
                $statusLabel = 'Stok Habis';
                $statusColor = 'rose';
            }

            return [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'short_address' => $branch->short_address,
                'city' => $branch->city,
                'price' => $price,
                'stock' => $stock,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
            ];
        })->values()->all();

        $relatedProducts = Product::active()
            ->where('category_id', '!=', $product->category_id)
            ->with(['category', 'branchStocks'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $suppliers = in_array($role, ['admin', 'owner'], true)
            ? $product->suppliers()->orderBy('name')->get()
            : collect();

        return view('products.show', [
            'role' => $role,
            'product' => $product,
            'category' => $product->category,
            'branchStocks' => $branchStocks,
            'relatedProducts' => $relatedProducts,
            'suppliers' => $suppliers,
        ]);
    }

    public function edit(string|int $id): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang dapat mengedit produk.');
        }

        $product = Product::findByIdentifier($id);
        if (! $product) {
            return redirect()->route('products.index')->with('error', 'Produk tidak ditemukan.');
        }

        $product->load('branchStocks');
        $categories = Category::orderBy('name')->get();
        $branches = Branch::orderBy('id')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $assignedSuppliers = $product->suppliers()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'branches', 'suppliers', 'assignedSuppliers', 'role'));
    }

    public function update(UpdateProductRequest $request, string|int $id): RedirectResponse
    {
        $product = Product::findByIdentifier($id);
        if (! $product) {
            return redirect()->route('products.index')->with('error', 'Produk tidak ditemukan.');
        }

        $validated = $request->validated();
        $name = trim($validated['name']);
        $basePrice = (int) $validated['price'];
        $description = $validated['description'] ?? $product->description;
        $shortDescription = ($validated['short_description'] ?? '') !== ''
            ? $validated['short_description']
            : ($description !== '' ? Str::limit(strip_tags((string) $description), 140) : $product->short_description);

        DB::transaction(function () use ($product, $validated, $name, $basePrice, $description, $shortDescription, $request): void {
            $updateData = [
                'name' => $name,
                'slug' => $this->generateUniqueSlug($name, $product->id),
                'category_id' => (int) $validated['category_id'],
                'brand' => ($validated['brand'] ?? '') !== '' ? $validated['brand'] : $product->brand,
                'size' => ($validated['size'] ?? '') !== '' ? $validated['size'] : (($validated['unit'] ?? '') !== '' ? $validated['unit'] : $product->size),
                'unit' => ($validated['unit'] ?? '') !== '' ? $validated['unit'] : $product->unit,
                'price' => $basePrice,
                'short_description' => $shortDescription,
                'description' => $description,
                'status' => $validated['status'] ?? $product->status,
            ];

            if (($validated['sku'] ?? '') !== '') {
                $updateData['sku'] = trim($validated['sku']);
            }

            if (($validated['image'] ?? '') !== '') {
                $updateData['image'] = $validated['image'];
            }

            $product->update($updateData);

            $submittedPrices = (array) $request->input('prices', []);
            if (count($submittedPrices) > 0) {
                foreach ($submittedPrices as $branchId => $branchPrice) {
                    if (! is_numeric($branchId) || ! is_numeric($branchPrice)) {
                        continue;
                    }

                    BranchStock::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'branch_id' => (int) $branchId,
                        ],
                        [
                            'price' => max(0, (int) $branchPrice),
                        ]
                    );
                }
            }
        });

        if ($request->filled('supplier_id')) {
            $product->suppliers()->syncWithoutDetaching([(int) $request->input('supplier_id')]);
        }

        return redirect()->route('products.index')->with('success', 'Produk "'.$product->name.'" berhasil diperbarui.');
    }

    public function destroy(string|int $id): RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('products.index')->with('error', 'Hanya Admin yang dapat menghapus produk.');
        }

        $product = Product::findByIdentifier($id);
        if (! $product) {
            return redirect()->route('products.index')->with('error', 'Gagal menghapus produk.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'produk';
        $slug = $base;
        $counter = 2;

        while (Product::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function defaultBranchPrice(int $basePrice, string $branchName, int $index): int
    {
        $step = $basePrice < 200000 ? 500 : 1000;

        if ($branchName === 'Serdam') {
            return $basePrice;
        }

        if ($branchName === 'Gajahmada') {
            return max($basePrice + $step, (int) (round(($basePrice * 1.025) / $step) * $step));
        }

        if ($branchName === 'Kota Baru') {
            $gajahmada = max($basePrice + $step, (int) (round(($basePrice * 1.025) / $step) * $step));

            return max($gajahmada + $step, (int) (round(($basePrice * 1.05) / $step) * $step));
        }

        return $basePrice + ($index * $step);
    }
}
