<?php

namespace App\Livewire;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class PaymentsPage extends Component
{
    public function render()
    {
        $legacySearch = trim((string) request('search'));
        $studentSearch = trim((string) request('student_search', $legacySearch));
        $paymentSearch = trim((string) request('payment_search', $legacySearch));
        $paymentStudentId = request('payment_student_id');
        $paymentMethod = request('payment_method');
        $paymentStatus = request('payment_status');
        $paymentDateFrom = request('payment_date_from');
        $paymentDateTo = request('payment_date_to');
        $studentClassId = request('student_class_id');
        $studentBatchId = request('student_batch_id');

        $students = Student::query()
            ->with(['batch', 'classRoom', 'major'])
            ->where('is_active', true)
            ->when($studentSearch !== '', fn (Builder $query) => $this->applyStudentSearch($query, $studentSearch))
            ->when($studentClassId, fn (Builder $query) => $query->where('class_id', $studentClassId))
            ->when($studentBatchId, fn (Builder $query) => $query->where('batch_id', $studentBatchId))
            ->orderBy('full_name')
            ->limit(50)
            ->get();

        $studentFilterOptions = Student::query()
            ->where('is_active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nis', 'nisn']);

        return view('livewire.payments-page', [
            'students' => $students,
            'studentFilterOptions' => $studentFilterOptions,
            'classOptions' => AcademicClass::query()->where('is_active', true)->orderBy('level')->orderBy('name')->get(),
            'batchOptions' => Batch::query()->orderByDesc('year_label')->get(),
            'payments' => Payment::query()
                ->with(['student', 'cashAccount'])
                ->when($paymentSearch !== '', function (Builder $query) use ($paymentSearch) {
                    $query->where(function (Builder $paymentQuery) use ($paymentSearch) {
                        $paymentQuery
                            ->where('payment_no', 'like', "%{$paymentSearch}%")
                            ->orWhere('notes', 'like', "%{$paymentSearch}%")
                            ->orWhere('bank_reference', 'like', "%{$paymentSearch}%")
                            ->orWhere('payment_proof_name', 'like', "%{$paymentSearch}%")
                            ->orWhereHas('student', fn (Builder $studentQuery) => $this->applyStudentSearch($studentQuery, $paymentSearch))
                            ->orWhereHas('cashAccount', function (Builder $accountQuery) use ($paymentSearch) {
                                $accountQuery
                                    ->where('name', 'like', "%{$paymentSearch}%")
                                    ->orWhere('account_number', 'like', "%{$paymentSearch}%")
                                    ->orWhere('account_holder', 'like', "%{$paymentSearch}%");
                            });
                    });
                })
                ->when($paymentStudentId, fn (Builder $query) => $query->where('student_id', $paymentStudentId))
                ->when(in_array($paymentMethod, ['cash', 'bank_transfer'], true), fn (Builder $query) => $query->where('method', $paymentMethod))
                ->when(in_array($paymentStatus, ['posted', 'edited'], true), fn (Builder $query) => $query->where('status', $paymentStatus))
                ->when($paymentDateFrom, fn (Builder $query) => $query->whereDate('payment_date', '>=', $paymentDateFrom))
                ->when($paymentDateTo, fn (Builder $query) => $query->whereDate('payment_date', '<=', $paymentDateTo))
                ->latest('payment_date')
                ->latest('id')
                ->limit(50)
                ->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Pembayaran',
            'pageHeading' => 'Pembayaran',
            'activeNav' => 'payments',
            'searchPlaceholder' => 'Cari siswa, nomor bukti, akun, atau catatan...',
        ]);
    }

    protected function applyStudentSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $studentQuery) use ($search) {
            $studentQuery
                ->where('full_name', 'like', "%{$search}%")
                ->orWhere('nis', 'like', "%{$search}%")
                ->orWhere('nisn', 'like', "%{$search}%")
                ->orWhereHas('batch', function (Builder $batchQuery) use ($search) {
                    $batchQuery->where('academic_year', 'like', "%{$search}%")
                        ->orWhere('year_label', 'like', "%{$search}%");
                })
                ->orWhereHas('classRoom', function (Builder $classQuery) use ($search) {
                    $classQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%");
                })
                ->orWhereHas('major', function (Builder $majorQuery) use ($search) {
                    $majorQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
        });
    }
}
