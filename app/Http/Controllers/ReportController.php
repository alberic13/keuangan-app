<?php

namespace App\Http\Controllers;

use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $query = $this->baseQuery($filters);

        $transactions = (clone $query)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('reports.index', [
            'transactions' => $transactions,
            'summary' => $this->buildSummary(clone $query),
            'filters' => $filters,
            'categories' => KategoriTransaksi::query()->orderBy('nama_kategori')->get(),
        ]);
    }

    public function export(Request $request): Response
    {
        $filters = $this->resolveFilters($request);
        $rows = $this->baseQuery($filters)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();
        $summary = $this->buildSummary(clone $this->baseQuery($filters));

        $fileName = 'laporan-keuangan-' . now()->format('Ymd-His') . '.pdf';

        $pdf = Pdf::loadView('reports.pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'summary' => $summary,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }

    private function resolveFilters(Request $request): array
    {
        $period = $request->string('period')->toString() ?: 'monthly';
        $startDate = $request->string('start_date')->toString() ?: now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->string('end_date')->toString() ?: now()->endOfMonth()->format('Y-m-d');

        if ($period === 'daily' && $request->filled('date')) {
            $startDate = $request->string('date')->toString();
            $endDate = $request->string('date')->toString();
        }

        if ($period === 'yearly' && $request->filled('year')) {
            $year = (int) $request->input('year');
            $startDate = Carbon::create($year, 1, 1)->format('Y-m-d');
            $endDate = Carbon::create($year, 12, 31)->format('Y-m-d');
        }

        return [
            'period' => in_array($period, ['daily', 'monthly', 'yearly'], true) ? $period : 'monthly',
            'date' => $request->string('date')->toString() ?: now()->format('Y-m-d'),
            'year' => $request->integer('year') ?: (int) now()->format('Y'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'category_id' => $request->integer('category_id') ?: null,
            'type' => $request->string('type')->toString() ?: null,
        ];
    }

    private function baseQuery(array $filters): Builder
    {
        return Transaksi::query()
            ->with(['kategori', 'submitter'])
            ->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']])
            ->when($filters['category_id'], fn (Builder $query, int $categoryId) => $query->where('kategori_id', $categoryId))
            ->when($filters['type'], function (Builder $query, string $type) {
                $query->whereHas('kategori', fn (Builder $subQuery) => $subQuery->where('tipe', $type));
            });
    }

    private function buildSummary(Builder $query): array
    {
        $rows = $query->get();

        $income = (float) $rows
            ->filter(fn (Transaksi $transaksi) => optional($transaksi->kategori)->tipe === 'pemasukan')
            ->sum('nominal');

        $expense = (float) $rows
            ->filter(fn (Transaksi $transaksi) => optional($transaksi->kategori)->tipe === 'pengeluaran')
            ->sum('nominal');

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => $rows->count(),
        ];
    }
}
