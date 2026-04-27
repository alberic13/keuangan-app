<?php

namespace App\Http\Controllers;

use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);
        $query = $this->baseQuery($filters);
        $summary = $this->buildSummary(clone $query);

        $transactions = (clone $query)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('reports.index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'filters' => $filters,
            'categories' => KategoriTransaksi::query()->orderBy('nama_kategori')->get(),
            'filterDescription' => $this->describeFilters($filters),
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
            'filterDescription' => $this->describeFilters($filters),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
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

        $content = view('reports.excel', [
            'rows' => $rows,
            'filters' => $filters,
            'summary' => $summary,
            'filterDescription' => $this->describeFilters($filters),
        ])->render();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $preset = $request->string('preset')->toString() ?: 'bulan_ini';
        $now = now();
        $startDate = $request->string('start_date')->toString() ?: $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $request->string('end_date')->toString() ?: $now->copy()->endOfMonth()->format('Y-m-d');

        if ($preset === 'hari_ini') {
            $startDate = $now->format('Y-m-d');
            $endDate = $now->format('Y-m-d');
        } elseif ($preset === 'bulan_ini') {
            $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        } elseif ($preset === 'tahun_ini') {
            $startDate = $now->copy()->startOfYear()->format('Y-m-d');
            $endDate = $now->copy()->endOfYear()->format('Y-m-d');
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        return [
            'preset' => in_array($preset, ['hari_ini', 'bulan_ini', 'tahun_ini', 'custom'], true) ? $preset : 'bulan_ini',
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
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

    private function describeFilters(array $filters): string
    {
        $parts = [
            'Periode ' . Carbon::parse($filters['start_date'])->translatedFormat('d M Y') . ' - ' . Carbon::parse($filters['end_date'])->translatedFormat('d M Y'),
        ];

        if ($filters['category_id']) {
            $category = KategoriTransaksi::query()->find($filters['category_id']);

            if ($category) {
                $parts[] = 'Kategori ' . $category->nama_kategori;
            }
        }

        if ($filters['type']) {
            $parts[] = 'Tipe ' . Str::title($filters['type']);
        }

        return implode(' | ', $parts);
    }
}
