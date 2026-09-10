<?php

namespace App\Http\Controllers;

use App\Repositories\ReportRepositories;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $role = session('user.role', 'customer');

        if ($role === 'customer') {
            return redirect()->route('products.index');
        }

        $branch = $request->get('branch', 'all');
        $period = $request->get('period', 'this_month');

        // If user is branch admin, prioritize or default their branch
        $userBranch = session('user.branch');
        if ($role === 'admin' && $userBranch && $userBranch !== 'all' && ! $request->has('branch')) {
            $branch = $userBranch;
        }

        $reportData = ReportRepositories::getSalesReport($branch, $period);

        return view('reports.index', array_merge($reportData, [
            'role' => $role,
            'currentBranch' => $branch,
            'currentPeriod' => $period,
            'userBranch' => $userBranch,
        ]));
    }
}
