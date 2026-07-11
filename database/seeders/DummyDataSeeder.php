<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BillingCycle;
use App\Models\CashAccount;
use App\Models\ExpenseCategory;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Services\BillingService;
use App\Services\ExpenseService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the dummy database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('username', 'admin_keuangan')->first();
        $bendahara = User::query()->where('username', 'bendahara')->first();

        if (!$admin || !$bendahara) {
            $this->command->error('Admin Keuangan or Bendahara user not found. Please run DatabaseSeeder first.');
            return;
        }

        $batches = collect([
            ['year_label' => '2024', 'academic_year' => '2024/2025', 'is_active' => false],
            ['year_label' => '2025', 'academic_year' => '2025/2026', 'is_active' => true],
            ['year_label' => '2026', 'academic_year' => '2026/2027', 'is_active' => true],
        ])->mapWithKeys(fn (array $item) => [
            $item['year_label'] => Batch::query()->updateOrCreate(
                ['year_label' => $item['year_label']],
                $item,
            ),
        ]);

        $classes = collect([
            ['name' => 'X-A', 'level' => 'X', 'is_active' => true],
            ['name' => 'XI-MIPA 1', 'level' => 'XI', 'is_active' => true],
            ['name' => 'XI-IPS 1', 'level' => 'XI', 'is_active' => true],
            ['name' => 'XII-MIPA 1', 'level' => 'XII', 'is_active' => true],
            ['name' => 'XII-IPS 1', 'level' => 'XII', 'is_active' => true],
        ])->mapWithKeys(fn (array $item) => [
            $item['name'] => AcademicClass::query()->updateOrCreate(
                ['name' => $item['name']],
                $item,
            ),
        ]);

        $studentRows = [
            ['nis' => '2026001', 'nisn' => '9981000001', 'full_name' => 'Ahmad Fulan', 'class' => 'X-A', 'batch' => '2026', 'student_type' => 'boarding', 'is_active' => true],
            ['nis' => '2026002', 'nisn' => '9981000002', 'full_name' => 'Siti Nur Aini', 'class' => 'X-A', 'batch' => '2026', 'student_type' => 'regular', 'is_active' => true],
            ['nis' => '2025001', 'nisn' => '9981000003', 'full_name' => 'Rizky Setiawan', 'class' => 'XI-MIPA 1', 'batch' => '2025', 'student_type' => 'boarding', 'is_active' => true],
            ['nis' => '2025002', 'nisn' => '9981000004', 'full_name' => 'Aulia Rahmah', 'class' => 'XI-IPS 1', 'batch' => '2025', 'student_type' => 'full_day', 'is_active' => true],
            ['nis' => '2024001', 'nisn' => '9981000005', 'full_name' => 'Bima Pradana', 'class' => 'XII-MIPA 1', 'batch' => '2024', 'student_type' => 'regular', 'is_active' => true],
            ['nis' => '2024002', 'nisn' => '9981000006', 'full_name' => 'Laila Zahra', 'class' => 'XII-IPS 1', 'batch' => '2024', 'student_type' => 'boarding', 'is_active' => true],
            ['nis' => '2025003', 'nisn' => '9981000007', 'full_name' => 'Muhammad Naufal', 'class' => 'XI-MIPA 1', 'batch' => '2025', 'student_type' => 'boarding', 'is_active' => true],
            ['nis' => '2023001', 'nisn' => '9981000008', 'full_name' => 'Dewi Puspitasari', 'class' => 'XII-IPS 1', 'batch' => '2024', 'student_type' => 'regular', 'is_active' => false],
        ];

        $students = collect($studentRows)->mapWithKeys(function (array $student) use ($batches, $classes) {
            $record = Student::query()->updateOrCreate(
                ['nis' => $student['nis']],
                [
                    'nisn' => $student['nisn'],
                    'full_name' => $student['full_name'],
                    'class_id' => $classes[$student['class']]->id,
                    'batch_id' => $batches[$student['batch']]->id,
                    'student_type' => $student['student_type'],
                    'is_active' => $student['is_active'],
                    'enrollment_date' => Carbon::createFromDate((int) $student['batch'], 7, 1)->toDateString(),
                    'exit_date' => $student['is_active'] ? null : Carbon::createFromDate(2026, 3, 31)->toDateString(),
                ],
            );

            return [$record->nis => $record];
        });

        $cashAccountDefinitions = [
            [
                'lookup' => ['name' => 'Kas Utama'],
                'data' => ['name' => 'Kas Utama', 'type' => 'cash', 'account_number' => null, 'account_holder' => 'MAN 2 Surakarta', 'is_active' => true],
            ],
            [
                'lookup' => ['account_number' => '5250005255'],
                'data' => ['name' => 'BSI - 5250005255', 'type' => 'bank', 'account_number' => '5250005255', 'account_holder' => 'Komite MAN 2 Surakarta', 'is_active' => true],
            ],
            [
                'lookup' => ['account_number' => '003640599068'],
                'data' => ['name' => 'Danamon - 003640599068', 'type' => 'bank', 'account_number' => '003640599068', 'account_holder' => 'Komite Madrasah aliyah negeri 2 surakarta', 'is_active' => false],
            ],
        ];

        $cashAccounts = collect($cashAccountDefinitions)->mapWithKeys(function (array $definition) {
            $account = CashAccount::query()->updateOrCreate($definition['lookup'], $definition['data']);

            return [$definition['data']['name'] => $account];
        });

        $expenseCategories = collect([
            ['code' => 'ATK', 'name' => 'ATK', 'is_active' => true],
            ['code' => 'UTIL', 'name' => 'Listrik & Internet', 'is_active' => true],
            ['code' => 'HON', 'name' => 'Honorarium', 'is_active' => true],
            ['code' => 'MAINT', 'name' => 'Pemeliharaan', 'is_active' => true],
            ['code' => 'CONS', 'name' => 'Konsumsi', 'is_active' => true],
            ['code' => 'TRANS', 'name' => 'Transportasi', 'is_active' => true],
        ])->mapWithKeys(fn (array $item) => [
            $item['code'] => ExpenseCategory::query()->updateOrCreate(
                ['code' => $item['code']],
                $item,
            ),
        ]);

        $feeTypes = collect([
            [
                'code' => 'SPP',
                'name' => 'SPP',
                'category' => 'spp',
                'installment_allowed' => false,
                'billing_frequency' => 'monthly',
                'applies_to' => 'all',
                'is_active' => true,
            ],
            [
                'code' => 'UKG',
                'name' => 'Uang Kegiatan',
                'category' => 'activity',
                'installment_allowed' => true,
                'billing_frequency' => 'one_time',
                'applies_to' => 'full_day',
                'is_active' => true,
            ],
            [
                'code' => 'UMK',
                'name' => 'Uang Makan Asrama',
                'category' => 'meal',
                'installment_allowed' => false,
                'billing_frequency' => 'monthly',
                'applies_to' => 'boarding',
                'is_active' => true,
            ],
        ])->mapWithKeys(fn (array $item) => [
            $item['code'] => FeeType::query()->updateOrCreate(
                ['code' => $item['code']],
                $item,
            ),
        ]);

        foreach ([
            ['fee_type' => 'SPP', 'batch' => null, 'nominal' => 400000, 'effective_start' => '2026-01-01'],
            ['fee_type' => 'UKG', 'batch' => null, 'nominal' => 600000, 'effective_start' => '2026-01-01'],
            ['fee_type' => 'UMK', 'batch' => null, 'nominal' => 450000, 'effective_start' => '2026-01-01'],
        ] as $scheme) {
            FeeScheme::query()->updateOrCreate(
                [
                    'fee_type_id' => $feeTypes[$scheme['fee_type']]->id,
                    'batch_id' => $scheme['batch'] ? $batches[$scheme['batch']]->id : null,
                    'effective_start' => $scheme['effective_start'],
                ],
                [
                    'nominal' => $scheme['nominal'],
                    'effective_end' => null,
                    'is_active' => true,
                ],
            );
        }

        $currentCycleDate = now()->startOfMonth();
        $previousCycleDate = now()->copy()->subMonth()->startOfMonth();

        $billingCycles = collect([
            $previousCycleDate,
            $currentCycleDate,
        ])->mapWithKeys(function (Carbon $date) {
            $cycle = BillingCycle::query()->updateOrCreate(
                ['month' => $date->month, 'year' => $date->year],
                [
                    'period_label' => $date->translatedFormat('F Y'),
                    'due_date' => $date->copy()->day(10)->toDateString(),
                    'status' => 'open',
                ],
            );

            return [$date->format('Y-m') => $cycle];
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('cash_ledger_entries')->truncate();
        DB::table('payment_items')->truncate();
        DB::table('payments')->truncate();
        DB::table('expenses')->truncate();
        DB::table('invoices')->truncate();
        DB::table('import_log_rows')->truncate();
        DB::table('import_logs')->truncate();
        DB::table('audit_logs')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $billingService = app(BillingService::class);
        $paymentService = app(PaymentService::class);
        $expenseService = app(ExpenseService::class);

        $billingService->generate([
            'fee_type_id' => $feeTypes['SPP']->id,
            'billing_cycle_id' => $billingCycles[$previousCycleDate->format('Y-m')]->id,
            'filters' => ['student_type' => 'all'],
            'reference_name' => null,
        ], $admin);

        $billingService->generate([
            'fee_type_id' => $feeTypes['SPP']->id,
            'billing_cycle_id' => $billingCycles[$currentCycleDate->format('Y-m')]->id,
            'filters' => ['student_type' => 'all'],
            'reference_name' => null,
        ], $admin);

        $billingService->generate([
            'fee_type_id' => $feeTypes['UMK']->id,
            'billing_cycle_id' => $billingCycles[$currentCycleDate->format('Y-m')]->id,
            'filters' => ['student_type' => 'all'],
            'reference_name' => null,
        ], $admin);

        $billingService->generate([
            'fee_type_id' => $feeTypes['UKG']->id,
            'billing_cycle_id' => $billingCycles[$currentCycleDate->format('Y-m')]->id,
            'filters' => ['student_type' => 'all'],
            'reference_name' => 'Kegiatan Semester Ganjil 2026/2027',
        ], $admin);

        $ahmadInvoices = Invoice::query()
            ->where('student_id', $students['2026001']->id)
            ->with('feeType')
            ->get()
            ->keyBy(fn (Invoice $invoice) => $invoice->feeType->code.'-'.$invoice->billing_cycle_id.($invoice->reference_name ? '-'.$invoice->reference_name : ''));

        $rizkyInvoices = Invoice::query()
            ->where('student_id', $students['2025001']->id)
            ->with('feeType')
            ->get()
            ->keyBy(fn (Invoice $invoice) => $invoice->feeType->code.'-'.$invoice->billing_cycle_id.($invoice->reference_name ? '-'.$invoice->reference_name : ''));

        $auliaInvoices = Invoice::query()
            ->where('student_id', $students['2025002']->id)
            ->with('feeType')
            ->get()
            ->keyBy(fn (Invoice $invoice) => $invoice->feeType->code.'-'.$invoice->billing_cycle_id.($invoice->reference_name ? '-'.$invoice->reference_name : ''));

        $currentCycleId = $billingCycles[$currentCycleDate->format('Y-m')]->id;
        $previousCycleId = $billingCycles[$previousCycleDate->format('Y-m')]->id;

        $paymentService->create([
            'student_id' => $students['2026001']->id,
            'payment_date' => now()->subDays(8)->toDateString(),
            'method' => 'cash',
            'cash_account_id' => $cashAccounts['Kas Utama']->id,
            'bank_reference' => null,
            'notes' => 'Pembayaran SPP dan uang makan bulan berjalan.',
            'items' => [
                [
                    'invoice_id' => $ahmadInvoices['SPP-'.$currentCycleId]->id,
                    'amount' => (int) $ahmadInvoices['SPP-'.$currentCycleId]->outstanding_amount,
                ],
                [
                    'invoice_id' => $ahmadInvoices['UMK-'.$currentCycleId]->id,
                    'amount' => (int) $ahmadInvoices['UMK-'.$currentCycleId]->outstanding_amount,
                ],
            ],
        ], $bendahara);

        $paymentService->create([
            'student_id' => $students['2025001']->id,
            'payment_date' => now()->subDays(5)->toDateString(),
            'method' => 'bank_transfer',
            'cash_account_id' => $cashAccounts['BSI - 5250005255']->id,
            'bank_reference' => 'TRF-BSI-5250005255',
            'notes' => 'Transfer manual diverifikasi bendahara.',
            'items' => [
                [
                    'invoice_id' => $rizkyInvoices['SPP-'.$previousCycleId]->id,
                    'amount' => (int) $rizkyInvoices['SPP-'.$previousCycleId]->outstanding_amount,
                ],
            ],
        ], $bendahara);

        $paymentService->create([
            'student_id' => $students['2025002']->id,
            'payment_date' => now()->subDays(2)->toDateString(),
            'method' => 'cash',
            'cash_account_id' => $cashAccounts['Kas Utama']->id,
            'bank_reference' => null,
            'notes' => 'Pembayaran cicilan uang kegiatan.',
            'items' => [
                [
                    'invoice_id' => $auliaInvoices['UKG-'.$currentCycleId.'-Kegiatan Semester Ganjil 2026/2027']->id,
                    'amount' => 150000,
                ],
            ],
        ], $bendahara);

        $expenseService->create([
            'transaction_date' => now()->subDays(4)->toDateString(),
            'category_id' => $expenseCategories['ATK']->id,
            'payment_account_id' => $cashAccounts['Kas Utama']->id,
            'amount' => 275000,
            'description' => 'Pembelian ATK bendahara bulan ini.',
        ], $bendahara);

        $expenseService->create([
            'transaction_date' => now()->subDays(1)->toDateString(),
            'category_id' => $expenseCategories['UTIL']->id,
            'payment_account_id' => $cashAccounts['BSI - 5250005255']->id,
            'amount' => 950000,
            'description' => 'Pembayaran listrik dan internet operasional.',
        ], $bendahara);
    }
}
