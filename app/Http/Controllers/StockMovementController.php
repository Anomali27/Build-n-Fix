<?php

namespace App\Http\Controllers;

use App\Repositories\StockMovementRepository;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $role = session('user.role', 'customer');

        if ($role === 'customer') {
            return redirect()->route('products.index');
        }

        $type = $request->get('type', 'all'); // in, out, adjustment, all
        $branch = $request->get('branch', 'all');
        $search = $request->get('q', '');

        $movements = StockMovementRepository::getFiltered([
            'type' => $type,
            'branch' => $branch,
            'search' => $search,
        ]);

        $allMovements = StockMovementRepository::getAll();
        $countIn = count(array_filter($allMovements, fn ($m) => ($m['type'] ?? '') === 'in'));
        $countOut = count(array_filter($allMovements, fn ($m) => ($m['type'] ?? '') === 'out'));
        $countAdjustment = count(array_filter($allMovements, fn ($m) => ($m['type'] ?? '') === 'adjustment'));

        return view('stock-movements.index', [
            'role' => $role,
            'movements' => $movements,
            'currentType' => $type,
            'currentBranch' => $branch,
            'search' => $search,
            'countIn' => $countIn,
            'countOut' => $countOut,
            'countAdjustment' => $countAdjustment,
            'totalCount' => count($allMovements),
        ]);
    }
}
