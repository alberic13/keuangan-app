@php
    $studentTypeLabels = [
        'regular' => 'Reguler',
        'full_day' => 'Full Day',
        'boarding' => 'Asrama',
    ];
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-headline font-bold">Form Pembayaran</h3>
            <p class="text-sm text-on-surface-variant">Pilih invoice yang mau dibayar dan isi nominal per invoice.</p>
        </div>
        <a class="inline-flex items-center justify-center rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-primary" href="{{ route('payments.index', request()->only(['student_search', 'student_class_id', 'student_batch_id', 'payment_search', 'payment_student_id', 'payment_method', 'payment_status', 'payment_date_from', 'payment_date_to'])) }}">
            Kembali Pilih Siswa
        </a>
    </div>

    @if ($selectedStudent)
        <form action="{{ route('payments.store') }}" class="space-y-6" enctype="multipart/form-data" method="POST">
            @csrf

            <input name="student_id" type="hidden" value="{{ $selectedStudent->id }}">
            @foreach (request()->only(['student_search', 'student_class_id', 'student_batch_id', 'payment_search', 'payment_student_id', 'payment_method', 'payment_status', 'payment_date_from', 'payment_date_to']) as $filterName => $filterValue)
                @if (filled($filterValue))
                    <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
                @endif
            @endforeach

            @include('livewire.partials.payments.student-card')
            @include('livewire.partials.payments.payment-inputs')
            @include('livewire.partials.payments.invoice-table')
        </form>
    @else
        <div class="rounded-xl bg-surface-container-lowest p-6 shadow-sm">
            <p class="text-sm text-on-surface-variant">Pilih siswa terlebih dahulu dari halaman pembayaran untuk memuat tagihan.</p>
        </div>
    @endif

    @include('livewire.partials.payments.form-scripts')
</div>
