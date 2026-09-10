@php
    $paymentStatusLabels = [
        'posted' => 'Tersimpan',
        'edited' => 'Diedit',
    ];

    $studentFilters = array_filter([
        'student_search' => request('student_search'),
        'student_class_id' => request('student_class_id'),
        'student_batch_id' => request('student_batch_id'),
    ], fn ($value) => filled($value));

    $paymentFilters = array_filter([
        'payment_search' => request('payment_search'),
        'payment_student_id' => request('payment_student_id'),
        'payment_method' => request('payment_method'),
        'payment_status' => request('payment_status'),
        'payment_date_from' => request('payment_date_from'),
        'payment_date_to' => request('payment_date_to'),
    ], fn ($value) => filled($value));

    $currentFilters = array_merge($studentFilters, $paymentFilters);
@endphp

<div class="space-y-8">
    @include('livewire.partials.payments.student-filter')

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
        @include('livewire.partials.payments.history-filter')
        @include('livewire.partials.payments.history-table')
    </div>
</div>
