<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BillingCycle;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PrintViewsRenderingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Student $student;
    protected FeeType $feeType;
    protected BillingCycle $billingCycle;
    protected Invoice $invoice;
    protected CashAccount $cashAccount;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);
        $this->user = User::query()->firstOrCreate(
            ['username' => 'test_admin_print'],
            [
                'name' => 'Admin Print Test',
                'email' => 'admin_print@local.test',
                'password' => 'password',
                'is_active' => true,
            ]
        );
        $this->user->assignRole('admin_keuangan');

        $batch = Batch::query()->firstOrCreate(['year_label' => '2026/2027'], ['academic_year' => '2026/2027', 'is_active' => true]);
        $class = AcademicClass::query()->firstOrCreate(['name' => 'X-A'], ['level' => '10', 'is_active' => true]);

        $this->student = Student::query()->create([
            'nis' => 'TEST_NIS_'.uniqid(),
            'nisn' => 'TEST_NISN_'.uniqid(),
            'full_name' => 'Siswa Print Test',
            'batch_id' => $batch->id,
            'class_id' => $class->id,
            'student_type' => 'regular',
            'is_active' => true,
        ]);

        $this->feeType = FeeType::query()->firstOrCreate(
            ['code' => 'PRINT_TEST_'.uniqid()],
            [
                'name' => 'Biaya Print Test',
                'category' => 'activity',
                'billing_frequency' => 'monthly',
                'installment_allowed' => true,
                'applies_to' => 'all',
                'is_active' => true,
            ]
        );

        $this->billingCycle = BillingCycle::query()->create([
            'month' => 9,
            'year' => 2026,
            'period_label' => 'September 2026',
            'due_date' => now()->addDays(5),
            'status' => 'open',
        ]);

        $this->invoice = Invoice::query()->create([
            'invoice_no' => 'INV-PRINT-'.uniqid(),
            'student_id' => $this->student->id,
            'fee_type_id' => $this->feeType->id,
            'billing_cycle_id' => $this->billingCycle->id,
            'total_amount' => 250000,
            'paid_amount' => 0,
            'outstanding_amount' => 250000,
            'status' => 'unpaid',
            'created_by' => $this->user->id,
        ]);

        $this->cashAccount = CashAccount::query()->firstOrCreate(
            ['name' => 'Kas Operasional'],
            ['type' => 'cash', 'is_active' => true]
        );
    }

    public function test_can_render_invoice_print(): void
    {
        $response = $this->actingAs($this->user)->get("/invoices/{$this->invoice->id}/print");
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_render_payment_receipt_print(): void
    {
        $payment = Payment::query()->create([
            'payment_no' => 'PAY-PRINT-'.uniqid(),
            'student_id' => $this->student->id,
            'cash_account_id' => $this->cashAccount->id,
            'total_amount' => 250000,
            'payment_date' => now()->toDateString(),
            'method' => 'cash',
            'status' => 'success',
            'created_by' => $this->user->id,
        ]);

        $this->invoice->paymentItems()->create([
            'payment_id' => $payment->id,
            'amount' => 250000,
        ]);

        $response = $this->actingAs($this->user)->get("/payments/{$payment->id}/receipt");
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_render_expense_voucher_print(): void
    {
        $category = ExpenseCategory::query()->firstOrCreate(
            ['code' => 'KAT_TEST'],
            ['name' => 'Konsumsi Test', 'is_active' => true]
        );

        $expense = Expense::query()->create([
            'expense_no' => 'EXP-PRINT-'.uniqid(),
            'category_id' => $category->id,
            'payment_account_id' => $this->cashAccount->id,
            'transaction_date' => now()->toDateString(),
            'amount' => 150000,
            'description' => 'Konsumsi rapat panitia ujian',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get("/expenses/{$expense->id}/print");
        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    public function test_can_render_app_layout(): void
    {
        $view = view('layouts.app', [
            'pageTitle' => 'Test Page',
            'pageHeading' => 'Test Heading',
            'slot' => '<div>Content Test</div>',
        ])->render();

        $this->assertStringContainsString('E-Keuangan', $view);
        $this->assertStringContainsString('Content Test', $view);
        $this->assertStringContainsString('sidebarDesktopState', $view);
    }
}
