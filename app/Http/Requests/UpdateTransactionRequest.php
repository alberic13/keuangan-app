<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $transaction = $this->route('transaction');

        return [
            'tanggal' => ['required', 'date'],
            'no_referensi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('transaksi', 'no_referensi')->ignore($transaction?->id),
            ],
            'deskripsi_kegiatan' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'numeric', 'min:1'],
            'kategori_id' => ['required', 'integer', 'exists:kategori_transaksi,id'],
            'bukti_nota' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
