<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentManagementController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'payment_date' => ['required', 'date', 'after_or_equal:today'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer'])],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_id' => ['required', 'exists:invoices,id'],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ]);

        if ($request->input('method') === 'bank_transfer' && ! $request->hasFile('payment_proof')) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Bukti pembayaran wajib diunggah untuk metode transfer manual.',
            ]);
        }

        $this->paymentService->create($data, $request->user());

        return $this->redirectBackWithMessage($request, 'Pembayaran berhasil disimpan.');
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'payment_date' => ['required', 'date', 'after_or_equal:today'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer'])],
            'cash_account_id' => ['required', 'exists:cash_accounts,id'],
            'payment_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
            'notes' => ['nullable', 'string'],
            'edited_reason' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.invoice_id' => ['required', 'exists:invoices,id'],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ]);

        if (
            $request->input('method') === 'bank_transfer'
            && ! $request->hasFile('payment_proof')
            && ! $payment->payment_proof_drive_id
        ) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Bukti pembayaran wajib diunggah untuk metode transfer manual.',
            ]);
        }

        $payment = $this->paymentService->update($payment, $data, $request->user());

        return redirect()
            ->route('payments.index', array_filter([
                'student_id' => $payment->student_id,
                'student_search' => $request->string('student_search')->toString(),
                'payment_search' => $request->string('payment_search')->toString(),
                'payment_student_id' => $request->string('payment_student_id')->toString(),
                'payment_method' => $request->string('payment_method')->toString(),
                'payment_status' => $request->string('payment_status')->toString(),
                'payment_date_from' => $request->string('payment_date_from')->toString(),
                'payment_date_to' => $request->string('payment_date_to')->toString(),
            ], fn ($value) => filled($value)))
            ->with('status', 'Pembayaran berhasil diperbarui.');
    }

    public function printReceipt(Payment $payment)
    {
        $payment->load(['student.batch', 'student.classRoom', 'student.major', 'cashAccount', 'items.invoice.feeType']);

        $quarterA4Landscape = [0, 0, 419.53, 297.64];

        return Pdf::loadView('prints.receipt', [
            'payment' => $payment,
        ])
            ->setPaper($quarterA4Landscape, 'portrait')
            ->stream($payment->payment_no.'.pdf');
    }
}
