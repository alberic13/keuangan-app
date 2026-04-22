<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExpenseController extends Controller
{
    public function create(): View
    {
        return view('expenses.create', [
            'categories' => KategoriTransaksi::query()
                ->where('tipe', 'pengeluaran')
                ->orderBy('nama_kategori')
                ->get(),
            'submitters' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('bukti_nota')) {
            $data['bukti_nota'] = $request->file('bukti_nota')->store('nota', 'public');
        }

        Transaksi::create($data);

        return redirect()
            ->route('expenses.create')
            ->with('success', 'Transaksi kas keluar berhasil disimpan.');
    }
}
