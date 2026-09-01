<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\KategoriTransaksi;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function create(): View
    {
        return view('expenses.create', [
            'categories' => KategoriTransaksi::query()
                ->where('tipe', 'pengeluaran')
                ->orderBy('nama_kategori')
                ->get(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['submitter_id'] = $request->user()?->id
            ?? User::query()->where('email', 'admin@man2.test')->value('id');

        if ($request->hasFile('bukti_nota')) {
            $data['bukti_nota'] = $request->file('bukti_nota')->store('nota', 'public');
        }

        Transaksi::create($data);

        return redirect()
            ->route('expenses.create')
            ->with('success', 'Transaksi kas keluar berhasil disimpan.');
    }

    public function edit(Transaksi $transaction): View
    {
        return view('expenses.edit', [
            'transaction' => $transaction->load(['kategori', 'submitter']),
            'categories' => KategoriTransaksi::query()->orderBy('tipe')->orderBy('nama_kategori')->get(),
            'returnUrl' => request('return_url', route('reports.index')),
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaksi $transaction): RedirectResponse
    {
        $data = $request->validated();

        $data['nominal'] = $transaction->nominal;

        if ($request->hasFile('bukti_nota')) {
            if ($transaction->bukti_nota) {
                Storage::disk('public')->delete($transaction->bukti_nota);
            }

            $data['bukti_nota'] = $request->file('bukti_nota')->store('nota', 'public');
        }

        $data['submitter_id'] = $request->user()?->id ?? $transaction->submitter_id;

        $transaction->update($data);

        return redirect($this->resolveReturnUrl($request))
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaksi $transaction): RedirectResponse
    {
        $returnUrl = $this->resolveReturnUrl(request());

        if ($transaction->bukti_nota) {
            Storage::disk('public')->delete($transaction->bukti_nota);
        }

        $transaction->delete();

        return redirect($returnUrl)
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    private function resolveReturnUrl($request): string
    {
        $returnUrl = $request->input('return_url');

        if (is_string($returnUrl) && str_starts_with($returnUrl, url('/'))) {
            return $returnUrl;
        }

        return route('reports.index');
    }
}
