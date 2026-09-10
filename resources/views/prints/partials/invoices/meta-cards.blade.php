<table class="meta-grid">
    <tr>
        <td>
            <div class="meta-card">
                <div class="meta-label">Nama Siswa</div>
                <div class="meta-value">{{ $invoice->student->full_name }}</div>
                <div class="meta-label" style="margin-top: 8px;">NIS / NISN</div>
                <div class="meta-value">{{ $invoice->student->nis ?: '-' }} / {{ $invoice->student->nisn ?: '-' }}</div>
            </div>
        </td>
        <td>
            <div class="meta-card">
                <div class="meta-label">Status Invoice</div>
                <div class="meta-value"><span class="pill {{ $statusClass }}">{{ $statusLabel }}</span></div>
                <div class="meta-label" style="margin-top: 8px;">Jatuh Tempo</div>
                <div class="meta-value">{{ $invoice->billingCycle?->due_date?->translatedFormat('d F Y') ?? '-' }}</div>
            </div>
        </td>
    </tr>
</table>

<p class="section-title">Identitas Siswa</p>
<table class="report-table">
    <tbody>
        <tr>
            <td style="width: 18%;"><strong>Kelas</strong></td>
            <td style="width: 32%;" colspan="3">{{ $invoice->student->classRoom->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Angkatan</strong></td>
            <td>{{ $invoice->student->batch->academic_year ?? '-' }}</td>
            <td><strong>Tipe Siswa</strong></td>
            <td>{{ ucfirst($invoice->student->student_type) }}</td>
        </tr>
    </tbody>
</table>
