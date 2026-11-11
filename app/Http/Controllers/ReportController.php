<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $role = $request->user()?->role ?? 'customer';

        if ($role === 'customer') {
            return redirect()->route('products.index');
        }

        $branch = (string) $request->get('branch', 'all');
        $period = (string) $request->get('period', 'this_year');

        $userBranch = $request->user()?->branch;
        if ($role === 'admin' && $userBranch && $userBranch !== 'all' && ! $request->has('branch')) {
            $branch = $userBranch;
        }

        $reportData = Order::buildSalesReport($branch, $period);

        return view('reports.index', array_merge($reportData, [
            'role' => $role,
            'currentBranch' => $branch,
            'currentPeriod' => $period,
            'userBranch' => $userBranch,
        ]));
    }
}
