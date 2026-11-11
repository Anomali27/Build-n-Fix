<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $role = auth()->user()?->role ?? 'customer';

        if ($role === 'customer') {
            return redirect()->route('products.index');
        }

        $type = (string) $request->get('type', 'all');
        $branch = (string) $request->get('branch', 'all');
        $search = (string) $request->get('q', '');

        $movements = StockMovement::query()
            ->with(['product', 'branch', 'user'])
            ->ofType($type !== 'all' ? $type : null)
            ->forBranch($branch !== 'all' ? $branch : null)
            ->search($search !== '' ? $search : null)
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->get();

        $counts = StockMovement::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw("SUM(CASE WHEN type = 'in' THEN 1 ELSE 0 END) as in_count")
            ->selectRaw("SUM(CASE WHEN type = 'out' THEN 1 ELSE 0 END) as out_count")
            ->selectRaw("SUM(CASE WHEN type = 'adjustment' THEN 1 ELSE 0 END) as adjustment_count")
            ->first();

        return view('stock-movements.index', [
            'role' => $role,
            'movements' => $movements,
            'branches' => Branch::query()->orderBy('id')->get(),
            'currentType' => $type,
            'currentBranch' => $branch,
            'search' => $search,
            'countIn' => (int) ($counts?->in_count ?? 0),
            'countOut' => (int) ($counts?->out_count ?? 0),
            'countAdjustment' => (int) ($counts?->adjustment_count ?? 0),
            'totalCount' => (int) ($counts?->total_count ?? 0),
        ]);
    }
}
