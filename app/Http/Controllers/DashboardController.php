<?php

namespace App\Http\Controllers;

use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(): View
    {
        $incomeTotal = $this->sumByType('pemasukan');
        $expenseTotal = $this->sumByType('pengeluaran');
        $balanceTotal = $incomeTotal - $expenseTotal;

        $recentTransactions = Transaksi::query()
            ->with(['kategori', 'submitter'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'balanceTotal' => $balanceTotal,
            'recentTransactions' => $recentTransactions,
            'monthlyExpenses' => $this->buildMonthlyExpenses(),
            'categoryExpenses' => $this->buildCategoryExpenses($expenseTotal),
            'fundingSources' => $this->buildFundingSources($incomeTotal),
            'sppTotal' => $this->sumCategoryIncome('SPP'),
            'boardingStudentTotal' => $this->sumCategoryIncome('Siswa Boarding'),
        ]);
    }

    private function sumByType(string $type): float
    {
        return (float) Transaksi::query()
            ->join('kategori_transaksi', 'kategori_transaksi.id', '=', 'transaksi.kategori_id')
            ->where('kategori_transaksi.tipe', $type)
            ->sum('transaksi.nominal');
    }

    private function buildMonthlyExpenses(): Collection
    {
        $startMonth = Carbon::now()->startOfMonth()->subMonths(5);

        $rows = Transaksi::query()
            ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as bulan, SUM(nominal) as total")
            ->join('kategori_transaksi', 'kategori_transaksi.id', '=', 'transaksi.kategori_id')
            ->where('kategori_transaksi.tipe', 'pengeluaran')
            ->whereDate('tanggal', '>=', $startMonth)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        return collect(range(0, 5))->map(function (int $offset) use ($rows, $startMonth) {
            $month = $startMonth->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $total = (float) optional($rows->get($key))->total;

            return [
                'label' => $month->translatedFormat('M Y'),
                'total' => $total,
            ];
        });
    }

    private function buildFundingSources(float $incomeTotal): Collection
    {
        return KategoriTransaksi::query()
            ->where('tipe', 'pemasukan')
            ->withSum('transaksi as total_pemasukan', 'nominal')
            ->orderByDesc('total_pemasukan')
            ->get()
            ->map(function (KategoriTransaksi $kategori) use ($incomeTotal) {
                $total = (float) ($kategori->total_pemasukan ?? 0);

                return [
                    'nama' => $kategori->nama_kategori,
                    'total' => $total,
                    'percentage' => $incomeTotal > 0 ? round(($total / $incomeTotal) * 100, 1) : 0,
                ];
            });
    }

    private function sumCategoryIncome(string $categoryName): float
    {
        return (float) Transaksi::query()
            ->join('kategori_transaksi', 'kategori_transaksi.id', '=', 'transaksi.kategori_id')
            ->where('kategori_transaksi.tipe', 'pemasukan')
            ->where('kategori_transaksi.nama_kategori', $categoryName)
            ->sum('transaksi.nominal');
    }

    private function buildCategoryExpenses(float $expenseTotal): Collection
    {
        return KategoriTransaksi::query()
            ->where('tipe', 'pengeluaran')
            ->withSum('transaksi as total_pengeluaran', 'nominal')
            ->orderByDesc('total_pengeluaran')
            ->get()
            ->map(function (KategoriTransaksi $kategori) use ($expenseTotal) {
                $total = (float) ($kategori->total_pengeluaran ?? 0);

                return [
                    'nama' => $kategori->nama_kategori,
                    'total' => $total,
                    'percentage' => $expenseTotal > 0 ? round(($total / $expenseTotal) * 100, 1) : 0,
                ];
            });
    }
}

