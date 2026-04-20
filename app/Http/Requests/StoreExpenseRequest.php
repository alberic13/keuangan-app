<?php

namespace App\Http\Requests;

use App\Models\KategoriTransaksi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'no_referensi' => ['required', 'string', 'max:255', Rule::unique('transaksi', 'no_referensi')],
            'deskripsi_kegiatan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'kategori_id' => ['required', 'integer', 'exists:kategori_transaksi,id'],
            'submitter_id' => ['required', 'integer', 'exists:users,id'],
            'bukti_nota' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $kategoriId = $this->integer('kategori_id');

            if (! $kategoriId) {
                return;
            }

            $kategori = KategoriTransaksi::find($kategoriId);

            if (! $kategori || $kategori->tipe !== 'pengeluaran') {
                $validator->errors()->add('kategori_id', 'Kategori yang dipilih harus bertipe pengeluaran.');
            }
        });
    }
}
