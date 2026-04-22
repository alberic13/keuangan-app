<?php

namespace App\Http\Controllers;

use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $rows = $this->baseQuery($filters)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $fileName = 'laporan-keuangan-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Tanggal',
                'No Referensi',
                'Deskripsi',
                'Kategori',
                'Tipe',
                'Nominal',
                'Submitter',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->tanggal?->format('Y-m-d'),
                    $row->no_referensi,
                    $row->deskripsi_kegiatan,
                    $row->kategori?->nama_kategori,
                    $row->kategori?->tipe,
                    $row->nominal,
                    $row->submitter?->name,
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportExcel(Request $request): Response
    {
        $filters = $this->resolveFilters($request);
        $rows = $this->baseQuery($filters)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $summary = $this->buildSummary(clone $this->baseQuery($filters));
        $fileName = 'laporan-keuangan-' . now()->format('Ymd-His') . '.xls';

        return response()
            ->view('reports.excel', [
                'rows' => $rows,
                'filters' => $filters,
                'summary' => $summary,
            ])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
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
