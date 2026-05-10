<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected PaymentService $paymentService,
    ) {
    }

    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with(['student', 'cashAccount'])
            ->when($request->filled('student_id'), fn ($query) => $query->where('student_id', $request->integer('student_id')))
            ->when($request->filled('method'), fn ($query) => $query->where('method', $request->string('method')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('payment_date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('payment_date', '<=', $request->date('date_to')))
            ->latest('payment_date')
            ->paginate($request->integer('per_page', 15));

        return $this->success($payments);
    }

    public function store(Request $request)
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

        return $this->success(
            $this->paymentService->create($data, $request->user()),
            'Success',
            201,
        );
    }

    public function show(Payment $payment)
    {
        return $this->success($payment->load(['student.batch', 'student.classRoom', 'cashAccount', 'items.invoice.feeType']));
    }

    public function update(Request $request, Payment $payment)
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

        return $this->success($this->paymentService->update($payment, $data, $request->user()));
    }

    public function printReceipt(Payment $payment)
    {
        return $this->success([
            'url' => route('payments.receipt', $payment),
        ]);
    }
}
