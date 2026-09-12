<?php

namespace App\Repositories;

class ReportRepositories
{
    /**
     * Get consolidated sales report data for all branches or a single branch.
     *
     * @param string $branch 'all' | 'Serdam' | 'Gajahmada' | 'Kota Baru'
     * @param string $period 'this_month' | 'last_month' | 'this_year'
     */
    public static function getSalesReport(string $branch = 'all', string $period = 'this_month'): array
    {
        // Monthly trend for 2026 across 3 branches
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep'];

        $salesSerdam = [42000000, 48500000, 53200000, 61000000, 58000000, 64500000, 72000000, 78500000, 84200000];
        $salesGajahmada = [38000000, 41200000, 46000000, 52300000, 49800000, 55000000, 61200000, 66000000, 71500000];
        $salesKotaBaru = [29000000, 32000000, 35500000, 41000000, 39500000, 44200000, 48000000, 51500000, 56800000];

        // Total sales this month (September)
        $currentMonthSerdam = end($salesSerdam);
        $currentMonthGajahmada = end($salesGajahmada);
        $currentMonthKotaBaru = end($salesKotaBaru);
        $totalAllBranches = $currentMonthSerdam + $currentMonthGajahmada + $currentMonthKotaBaru;

        // Transactions count
        $txSerdam = 342;
        $txGajahmada = 285;
        $txKotaBaru = 210;
        $totalTx = $txSerdam + $txGajahmada + $txKotaBaru;

        // Top selling products
        $topProducts = [
            [
                'name' => 'Semen Portland 40 kg Tiga Roda',
                'category' => 'Semen, Pasir & Mortar',
                'sold_qty' => 1420,
                'revenue' => 82360000,
                'growth' => '+14.5%',
            ],
            [
                'name' => 'Besi Beton Polos 10 mm x 12 m SNI',
                'category' => 'Besi, Baja & Logam',
                'sold_qty' => 890,
                'revenue' => 69420000,
                'growth' => '+18.2%',
            ],
            [
                'name' => 'Cat Tembok Dulux Catylac 5 kg',
                'category' => 'Cat & Pelapis',
                'sold_qty' => 420,
                'revenue' => 65100000,
                'growth' => '+8.7%',
            ],
            [
                'name' => 'Keramik Lantai Roman 40x40',
                'category' => 'Keramik, Granit & Lantai',
                'sold_qty' => 630,
                'revenue' => 50400000,
                'growth' => '+11.3%',
            ],
            [
                'name' => 'Pipa PVC Rucika AW 1/2 inch',
                'category' => 'Pipa & Sanitari',
                'sold_qty' => 780,
                'revenue' => 24960000,
                'growth' => '+6.2%',
            ],
        ];

        // Daily breakdown for current month (last 7 days)
        $dailyLabels = ['03 Sep', '04 Sep', '05 Sep', '06 Sep', '07 Sep', '08 Sep', '09 Sep'];
        $dailySerdam = [11200000, 12500000, 9800000, 14200000, 13100000, 15400000, 16800000];
        $dailyGajahmada = [9500000, 10200000, 8900000, 11800000, 11200000, 12900000, 13800000];
        $dailyKotaBaru = [7100000, 8300000, 6800000, 9200000, 8700000, 9900000, 10800000];

        $branchSummary = [
            'Serdam' => [
                'branch_name' => 'Cabang Serdam',
                'revenue' => $currentMonthSerdam,
                'transactions' => $txSerdam,
                'share_percentage' => round(($currentMonthSerdam / $totalAllBranches) * 100, 1),
                'growth' => '+12.4%',
                'monthly_data' => $salesSerdam,
                'daily_data' => $dailySerdam,
            ],
            'Gajahmada' => [
                'branch_name' => 'Cabang Gajahmada',
                'revenue' => $currentMonthGajahmada,
                'transactions' => $txGajahmada,
                'share_percentage' => round(($currentMonthGajahmada / $totalAllBranches) * 100, 1),
                'growth' => '+9.8%',
                'monthly_data' => $salesGajahmada,
                'daily_data' => $dailyGajahmada,
            ],
            'Kota Baru' => [
                'branch_name' => 'Cabang Kota Baru',
                'revenue' => $currentMonthKotaBaru,
                'transactions' => $txKotaBaru,
                'share_percentage' => round(($currentMonthKotaBaru / $totalAllBranches) * 100, 1),
                'growth' => '+10.2%',
                'monthly_data' => $salesKotaBaru,
                'daily_data' => $dailyKotaBaru,
            ],
        ];

        return [
            'selected_branch' => $branch,
            'selected_period' => $period,
            'months' => $months,
            'daily_labels' => $dailyLabels,
            'total_revenue' => $totalAllBranches,
            'total_transactions' => $totalTx,
            'avg_order_value' => round($totalAllBranches / $totalTx),
            'branch_summary' => $branchSummary,
            'sales_serdam' => $salesSerdam,
            'sales_gajahmada' => $salesGajahmada,
            'sales_kotabaru' => $salesKotaBaru,
            'daily_serdam' => $dailySerdam,
            'daily_gajahmada' => $dailyGajahmada,
            'daily_kotabaru' => $dailyKotaBaru,
            'top_products' => $topProducts,
        ];
    }
}
