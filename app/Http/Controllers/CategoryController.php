<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';

        if (! in_array($role, ['admin', 'owner'], true)) {
            $query = array_filter([
                'category' => $request->query('category'),
                'q' => $request->query('q'),
                'sort' => $request->query('sort'),
                'view' => $request->query('view'),
            ], static fn (?string $value): bool => $value !== null && $value !== '');

            return redirect()->route('products.index', $query);
        }

        $selectedCategory = (string) $request->get('category', 'all');
        $sort = (string) $request->get('sort', 'terpopuler');
        $view = (string) $request->get('view', 'grid');
        $search = $request->get('q');

        $sidebarCategories = Category::query()
            ->withCount('products')
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

        $query = Category::query()
            ->withCount('products')
            ->search(is_string($search) ? $search : null);

        if ($selectedCategory !== '' && ! in_array(strtolower($selectedCategory), ['all', 'semua'], true)) {
            $query->where(function ($q) use ($selectedCategory): void {
                if (is_numeric($selectedCategory)) {
                    $q->where('id', (int) $selectedCategory);
                }

                $q->orWhere('slug', $selectedCategory)
                    ->orWhere('name', $selectedCategory);
            });
        }

        match (strtolower($sort)) {
            'a-z' => $query->orderBy('name', 'asc'),
            'z-a' => $query->orderBy('name', 'desc'),
            'jumlah-produk', 'count' => $query->orderBy('products_count', 'desc'),
            default => $query->orderBy('popular_rank', 'asc')->orderBy('id', 'asc'),
        };

        $categories = $query->get();
        $total = $categories->count();
        $from = $total > 0 ? 1 : 0;
        $to = $total;

        return view('categories.index', [
            'role' => $role,
            'categories' => $categories,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'sidebarCategories' => $sidebarCategories,
            'selectedCategory' => $selectedCategory,
            'sort' => $sort,
            'view' => $view,
            'search' => $search,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat menambah kategori.');
        }

        return view('categories.create', ['role' => $role]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $name = trim($validated['name']);
        $slug = $this->generateUniqueSlug($name);
        $nextRank = ((int) Category::max('popular_rank')) + 1;

        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
            'description' => $validated['description'] ?? '',
            'status' => $validated['status'],
            'image' => ($validated['image'] ?? '') !== ''
                ? $validated['image']
                : 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=600&auto=format&fit=crop&q=80',
            'popular_rank' => $nextRank,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "'.$category->name.'" berhasil ditambahkan.');
    }

    public function show(string|int $category): RedirectResponse
    {
        $categoryModel = Category::findByIdentifier($category);

        if (! $categoryModel) {
            return redirect()->route('products.index')->with('error', 'Kategori tidak ditemukan.');
        }

        return redirect()->route('products.index', ['category' => $categoryModel->slug]);
    }

    public function edit(string|int $category): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat mengubah kategori.');
        }

        $categoryModel = Category::findByIdentifier($category);

        if (! $categoryModel) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak ditemukan.');
        }

        return view('categories.edit', [
            'role' => $role,
            'category' => $categoryModel,
        ]);
    }

    public function update(UpdateCategoryRequest $request, string|int $category): RedirectResponse
    {
        $categoryModel = Category::findByIdentifier($category);
        if (! $categoryModel) {
            return redirect()->route('categories.index')->with('error', 'Gagal memperbarui kategori.');
        }

        $validated = $request->validated();
        $name = trim($validated['name']);

        $categoryModel->update([
            'name' => $name,
            'slug' => $this->generateUniqueSlug($name, $categoryModel->id),
            'description' => $validated['description'] ?? '',
            'status' => $validated['status'],
            'image' => ($validated['image'] ?? '') !== '' ? $validated['image'] : $categoryModel->image,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori "'.$categoryModel->name.'" berhasil diperbarui.');
    }

    public function destroy(string|int $category): RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('categories.index')->with('error', 'Hanya Admin yang dapat menghapus kategori.');
        }

        $categoryModel = Category::findByIdentifier($category);
        if (! $categoryModel) {
            return redirect()->route('categories.index')->with('error', 'Gagal menghapus kategori.');
        }

        $categoryModel->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'kategori';
        $slug = $base;
        $counter = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
