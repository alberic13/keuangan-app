<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BillingCycle;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BillingGenerationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Student $student;
    protected FeeType $feeType;
    protected FeeScheme $feeScheme;
    protected BillingCycle $billingCycle;
    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->billingService = app(BillingService::class);
        $this->user = User::query()->first() ?? User::factory()->create();

        $batch = Batch::query()->firstOrCreate(['year_label' => '2026/2027'], ['academic_year' => '2026/2027', 'is_active' => true]);
        $class = AcademicClass::query()->firstOrCreate(['name' => 'X-A'], ['level' => '10', 'is_active' => true]);

        $this->student = Student::query()->create([
            'nis' => 'TEST_NIS_'.uniqid(),
            'nisn' => 'TEST_NISN_'.uniqid(),
            'full_name' => 'Siswa Uji Coba',
            'batch_id' => $batch->id,
            'class_id' => $class->id,
            'student_type' => 'regular',
            'is_active' => true,
        ]);

        $this->feeType = FeeType::query()->firstOrCreate(
            ['code' => 'SPP_TEST_'.uniqid()],
            [
                'name' => 'SPP Testing',
                'category' => 'spp',
                'billing_frequency' => 'monthly',
                'installment_allowed' => false,
                'applies_to' => 'all',
                'is_active' => true,
            ]
        );

        $this->feeScheme = FeeScheme::query()->create([
            'fee_type_id' => $this->feeType->id,
            'nominal' => 350000,
            'effective_start' => now()->startOfYear(),
            'is_active' => true,
        ]);

        $this->billingCycle = BillingCycle::query()->create([
            'month' => 9,
            'year' => 2026,
            'period_label' => 'September 2026',
            'due_date' => now()->addDays(10),
            'status' => 'open',
        ]);
    }

    public function test_can_generate_invoices_for_active_student(): void
    {
        $result = $this->billingService->generate([
            'fee_type_id' => $this->feeType->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'reference_name' => null,
            'filters' => [
                'batch_id' => $this->student->batch_id,
            ],
        ], $this->user);

        $this->assertGreaterThanOrEqual(1, $result['generated']);

        $this->assertDatabaseHas('invoices', [
            'student_id' => $this->student->id,
            'fee_type_id' => $this->feeType->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'total_amount' => 350000,
            'status' => 'unpaid',
        ]);
    }

    public function test_skips_duplicate_invoice_generation(): void
    {
        $payload = [
            'fee_type_id' => $this->feeType->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'reference_name' => null,
            'filters' => [
                'batch_id' => $this->student->batch_id,
            ],
        ];

        $this->billingService->generate($payload, $this->user);
        $secondRun = $this->billingService->generate($payload, $this->user);

        $this->assertEquals(0, $secondRun['generated']);
        $this->assertGreaterThanOrEqual(1, $secondRun['skipped']);
    }

    public function test_cannot_void_invoice_with_payment(): void
    {
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-TEST-'.uniqid(),
            'student_id' => $this->student->id,
            'fee_type_id' => $this->feeType->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'total_amount' => 350000,
            'paid_amount' => 350000,
            'outstanding_amount' => 0,
            'status' => 'paid',
            'created_by' => $this->user->id,
        ]);

        $cashAccount = \App\Models\CashAccount::query()->firstOrCreate(
            ['name' => 'Kas Uji Coba'],
            ['type' => 'cash', 'is_active' => true]
        );
        $payment = \App\Models\Payment::query()->create([
            'payment_no' => 'PAY-TEST-'.uniqid(),
            'student_id' => $this->student->id,
            'cash_account_id' => $cashAccount->id,
            'total_amount' => 350000,
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
            'status' => 'success',
            'created_by' => $this->user->id,
        ]);

        $invoice->paymentItems()->create([
            'payment_id' => $payment->id,
            'amount' => 350000,
        ]);

        $this->expectException(ValidationException::class);
        $this->billingService->voidInvoice($invoice, $this->user);
    }
}
