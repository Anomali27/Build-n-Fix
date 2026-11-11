<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\AssignProductRequest;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';

        if (! in_array($role, ['admin', 'owner'], true)) {
            return redirect()->route('products.index');
        }

        $search = (string) $request->get('q', '');
        $status = (string) $request->get('status', 'all');

        $suppliers = Supplier::query()
            ->with(['products' => fn ($query) => $query->orderBy('name')])
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status))
            ->search($search)
            ->orderBy('id')
            ->get();

        $allProducts = Product::query()->orderBy('name')->get();

        return view('suppliers.index', [
            'role' => $role,
            'suppliers' => $suppliers,
            'search' => $search,
            'status' => $status,
            'totalCount' => Supplier::count(),
            'allProducts' => $allProducts,
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat menambah pemasok.');
        }

        $products = Product::query()->orderBy('name')->get();

        return view('suppliers.create', [
            'role' => $role,
            'products' => $products,
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $supplier = DB::transaction(function () use ($validated): Supplier {
            $code = trim((string) ($validated['code'] ?? ''));
            if ($code === '') {
                $code = $this->generateUniqueCode();
            }

            $created = Supplier::create([
                'code' => $code,
                'name' => trim($validated['name']),
                'contact_person' => trim($validated['contact_person']),
                'phone' => trim($validated['phone']),
                'email' => $validated['email'] ?? null,
                'city' => ($validated['city'] ?? '') !== '' ? trim((string) $validated['city']) : 'Pontianak',
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'rating' => 5.0,
            ]);

            $productIds = array_map('intval', (array) ($validated['product_ids'] ?? []));
            if ($productIds !== []) {
                $created->products()->sync($productIds);
            }

            return $created;
        });

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok "'.$supplier->name.'" berhasil ditambahkan.');
    }

    public function show(int|string $id): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';

        if (! in_array($role, ['admin', 'owner'], true)) {
            return redirect()->route('products.index');
        }

        $supplier = Supplier::findByIdentifier($id);

        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Pemasok tidak ditemukan.');
        }

        $supplier->load(['products' => fn ($query) => $query->orderBy('name')]);
        $allProducts = Product::query()->orderBy('name')->get();

        return view('suppliers.show', [
            'role' => $role,
            'supplier' => $supplier,
            'suppliedProducts' => $supplier->products,
            'allProducts' => $allProducts,
        ]);
    }

    public function edit(int|string $id): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat mengedit pemasok.');
        }

        $supplier = Supplier::findByIdentifier($id);

        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Pemasok tidak ditemukan.');
        }

        $supplier->load('products');
        $products = Product::query()->orderBy('name')->get();

        return view('suppliers.edit', [
            'role' => $role,
            'supplier' => $supplier,
            'products' => $products,
        ]);
    }

    public function update(UpdateSupplierRequest $request, int|string $id): RedirectResponse
    {
        $supplier = Supplier::findByIdentifier($id);

        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Gagal memperbarui pemasok.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($supplier, $validated, $request): void {
            $updateData = [
                'name' => trim($validated['name']),
                'contact_person' => trim($validated['contact_person']),
                'phone' => trim($validated['phone']),
                'email' => $validated['email'] ?? null,
                'city' => ($validated['city'] ?? '') !== '' ? trim((string) $validated['city']) : $supplier->city,
                'address' => $validated['address'] ?? $supplier->address,
                'status' => $validated['status'] ?? $supplier->status,
            ];

            if (($validated['code'] ?? '') !== '') {
                $updateData['code'] = trim((string) $validated['code']);
            }

            $supplier->update($updateData);

            if ($request->has('product_ids')) {
                $productIds = array_map('intval', (array) ($validated['product_ids'] ?? []));
                $supplier->products()->sync($productIds);
            }
        });

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok "'.$supplier->name.'" berhasil diperbarui.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat menghapus pemasok.');
        }

        $supplier = Supplier::findByIdentifier($id);
        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Gagal menghapus pemasok.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus.');
    }

    public function assignProduct(AssignProductRequest $request, int|string $id): RedirectResponse
    {
        $supplier = Supplier::findByIdentifier($id);
        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Pemasok tidak ditemukan.');
        }

        $validated = $request->validated();
        $supplier->products()->syncWithoutDetaching([(int) $validated['product_id']]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke daftar pasokan pemasok.');
    }

    private function generateUniqueCode(): string
    {
        $nextNum = ((int) Supplier::max('id')) + 1;
        $code = 'SUP-'.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);

        while (Supplier::where('code', $code)->exists()) {
            $nextNum++;
            $code = 'SUP-'.str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
        }

        return $code;
    }
}
