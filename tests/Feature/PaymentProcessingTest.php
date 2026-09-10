<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BillingCycle;
use App\Models\CashAccount;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentProcessingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Student $student;
    protected CashAccount $cashAccount;
    protected FeeType $feeTypeSpp;
    protected FeeType $feeTypeActivity;
    protected BillingCycle $billingCycle;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = app(PaymentService::class);
        $this->user = User::query()->first() ?? User::factory()->create();

        $batch = Batch::query()->firstOrCreate(['year_label' => '2026/2027'], ['academic_year' => '2026/2027', 'is_active' => true]);
        $class = AcademicClass::query()->firstOrCreate(['name' => 'X-A'], ['level' => '10', 'is_active' => true]);

        $this->student = Student::query()->create([
            'nis' => 'TEST_NIS_'.uniqid(),
            'nisn' => 'TEST_NISN_'.uniqid(),
            'full_name' => 'Siswa Bayar Uji',
            'batch_id' => $batch->id,
            'class_id' => $class->id,
            'student_type' => 'regular',
            'is_active' => true,
        ]);

        $this->cashAccount = CashAccount::query()->firstOrCreate(
            ['name' => 'Kas Uji Coba'],
            ['type' => 'cash', 'is_active' => true]
        );

        $this->feeTypeSpp = FeeType::query()->firstOrCreate(
            ['code' => 'SPP_PAY_'.uniqid()],
            [
                'name' => 'SPP Testing',
                'category' => 'spp',
                'billing_frequency' => 'monthly',
                'installment_allowed' => false,
                'applies_to' => 'all',
                'is_active' => true,
            ]
        );

        $this->feeTypeActivity = FeeType::query()->firstOrCreate(
            ['code' => 'ACT_PAY_'.uniqid()],
            [
                'name' => 'Kegiatan Testing',
                'category' => 'activity',
                'billing_frequency' => 'one_time',
                'installment_allowed' => true,
                'applies_to' => 'all',
                'is_active' => true,
            ]
        );

        $this->billingCycle = BillingCycle::query()->create([
            'month' => 9,
            'year' => 2026,
            'period_label' => 'September 2026',
            'due_date' => now()->addDays(10),
            'status' => 'open',
        ]);
    }

    public function test_can_pay_invoice_and_creates_cash_ledger_entry(): void
    {
        $invoice = Invoice::query()->create([
            'invoice_no' => 'INV-PAY-'.uniqid(),
            'student_id' => $this->student->id,
            'fee_type_id' => $this->feeTypeActivity->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'outstanding_amount' => 500000,
            'status' => 'unpaid',
            'created_by' => $this->user->id,
        ]);

        $payment = $this->paymentService->create([
            'student_id' => $this->student->id,
            'cash_account_id' => $this->cashAccount->id,
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
            'items' => [
                [
                    'invoice_id' => $invoice->id,
                    'amount' => 200000,
                ],
            ],
        ], $this->user);

        $this->assertEquals(200000, $payment->total_amount);

        // Invoice status becomes partial
        $invoice->refresh();
        $this->assertEquals('partial', $invoice->status);
        $this->assertEquals(200000, $invoice->paid_amount);
        $this->assertEquals(300000, $invoice->outstanding_amount);

        // Auto ledger entry with direction 'in'
        $this->assertDatabaseHas('cash_ledger_entries', [
            'source_type' => 'payment',
            'source_id' => $payment->id,
            'direction' => 'in',
            'amount' => 200000,
            'account_id' => $this->cashAccount->id,
        ]);
    }

    public function test_cannot_pay_spp_partially(): void
    {
        $invoiceSpp = Invoice::query()->create([
            'invoice_no' => 'INV-SPP-'.uniqid(),
            'student_id' => $this->student->id,
            'fee_type_id' => $this->feeTypeSpp->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'total_amount' => 350000,
            'paid_amount' => 0,
            'outstanding_amount' => 350000,
            'status' => 'unpaid',
            'created_by' => $this->user->id,
        ]);

        $this->expectException(ValidationException::class);

        $this->paymentService->create([
            'student_id' => $this->student->id,
            'cash_account_id' => $this->cashAccount->id,
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
            'items' => [
                [
                    'invoice_id' => $invoiceSpp->id,
                    'amount' => 100000, // Not full amount
                ],
            ],
        ], $this->user);
    }
}
