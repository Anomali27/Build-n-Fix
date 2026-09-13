<?php

namespace App\Http\Controllers;

use App\Repositories\ProductRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $role = session('user.role', 'customer');

        if ($role === 'customer') {
            return redirect()->route('products.index');
        }

        $search = $request->get('q', '');
        $status = $request->get('status', 'all');

        $suppliers = SupplierRepository::getAll();

        if ($status !== 'all') {
            $suppliers = array_filter($suppliers, fn ($s) => ($s['status'] ?? '') === $status);
        }

        if (! empty($search)) {
            $q = strtolower(trim($search));
            $suppliers = array_filter($suppliers, function ($s) use ($q) {
                return str_contains(strtolower($s['name']), $q) ||
                       str_contains(strtolower($s['code'] ?? ''), $q) ||
                       str_contains(strtolower($s['contact_person'] ?? ''), $q) ||
                       str_contains(strtolower($s['city'] ?? ''), $q);
            });
        }

        // Hydrate product details for each supplier
        $allProducts = ProductRepository::getAll();
        $productsById = [];
        foreach ($allProducts as $p) {
            $productsById[$p['id']] = $p;
        }

        foreach ($suppliers as &$sup) {
            $sup['products'] = [];
            foreach ($sup['product_ids'] ?? [] as $pId) {
                if (isset($productsById[$pId])) {
                    $sup['products'][] = $productsById[$pId];
                }
            }
        }

        return view('suppliers.index', [
            'role' => $role,
            'suppliers' => $suppliers,
            'search' => $search,
            'status' => $status,
            'totalCount' => count(SupplierRepository::getAll()),
            'allProducts' => $allProducts,
        ]);
    }

    public function create()
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat menambah pemasok.');
        }

        $products = ProductRepository::getAll();

        return view('suppliers.create', [
            'role' => $role,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat menyimpan pemasok.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier = SupplierRepository::create([
            'name' => $request->input('name'),
            'contact_person' => $request->input('contact_person'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'city' => $request->input('city', 'Pontianak'),
            'address' => $request->input('address'),
            'status' => $request->input('status', 'active'),
            'product_ids' => (array) $request->input('product_ids', []),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok "'.$supplier['name'].'" berhasil ditambahkan.');
    }

    public function show(int|string $id)
    {
        $role = session('user.role', 'customer');

        if ($role === 'customer') {
            return redirect()->route('products.index');
        }

        $supplier = SupplierRepository::find($id);

        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Pemasok tidak ditemukan.');
        }

        $allProducts = ProductRepository::getAll();
        $suppliedProducts = [];
        foreach ($allProducts as $p) {
            if (in_array((int) $p['id'], $supplier['product_ids'] ?? [], true)) {
                $suppliedProducts[] = $p;
            }
        }

        return view('suppliers.show', [
            'role' => $role,
            'supplier' => $supplier,
            'suppliedProducts' => $suppliedProducts,
            'allProducts' => $allProducts,
        ]);
    }

    public function edit(int|string $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat mengedit pemasok.');
        }

        $supplier = SupplierRepository::find($id);

        if (! $supplier) {
            return redirect()->route('suppliers.index')->with('error', 'Pemasok tidak ditemukan.');
        }

        $products = ProductRepository::getAll();

        return view('suppliers.edit', [
            'role' => $role,
            'supplier' => $supplier,
            'products' => $products,
        ]);
    }

    public function update(Request $request, int|string $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat mengubah data pemasok.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
        ]);

        $updated = SupplierRepository::update((int) $id, [
            'name' => $request->input('name'),
            'contact_person' => $request->input('contact_person'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
            'status' => $request->input('status', 'active'),
            'product_ids' => (array) $request->input('product_ids', []),
        ]);

        if (! $updated) {
            return redirect()->route('suppliers.index')->with('error', 'Gagal memperbarui pemasok.');
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok "'.$updated['name'].'" berhasil diperbarui.');
    }

    public function destroy(int|string $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat menghapus pemasok.');
        }

        SupplierRepository::delete((int) $id);

        return redirect()->route('suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus.');
    }

    public function assignProduct(Request $request, int|string $id)
    {
        $role = session('user.role', 'customer');
        if ($role !== 'admin') {
            return redirect()->route('suppliers.index')->with('error', 'Hanya Admin yang dapat menghubungkan produk.');
        }

        $productId = (int) $request->input('product_id');
        SupplierRepository::assignProduct((int) $id, $productId);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke daftar pasokan pemasok.');
    }
}
