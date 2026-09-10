<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BillingCycle;
use App\Models\CashAccount;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LivewireViewsRenderingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);
        $this->user = User::query()->firstOrCreate(
            ['username' => 'test_admin_livewire'],
            [
                'name' => 'Admin Livewire Test',
                'email' => 'admin_livewire@local.test',
                'password' => 'password',
                'is_active' => true,
            ]
        );
        $this->user->assignRole('admin_keuangan');

        $batch = Batch::query()->firstOrCreate(['year_label' => '2026/2027'], ['academic_year' => '2026/2027', 'is_active' => true]);
        $class = AcademicClass::query()->firstOrCreate(['name' => 'X-A'], ['level' => '10', 'is_active' => true]);

        $this->student = Student::query()->create([
            'nis' => 'TEST_LW_'.uniqid(),
            'nisn' => 'TEST_LWN_'.uniqid(),
            'full_name' => 'Siswa Livewire Test',
            'batch_id' => $batch->id,
            'class_id' => $class->id,
            'student_type' => 'regular',
            'is_active' => true,
        ]);
    }

    public function test_can_render_billing_page(): void
    {
        $response = $this->actingAs($this->user)->get('/billing');
        $response->assertOk();
        $response->assertSee('Manajemen Tagihan');
    }

    public function test_can_render_payments_page(): void
    {
        $response = $this->actingAs($this->user)->get('/payments');
        $response->assertOk();
        $response->assertSee('Pilih Siswa');
        $response->assertSee('Riwayat Pembayaran');
    }

    public function test_can_render_payment_form_page(): void
    {
        $response = $this->actingAs($this->user)->get('/payments/create?student_id='.$this->student->id);
        $response->assertOk();
        $response->assertSee('Form Pembayaran');
        $response->assertSee($this->student->full_name);
    }

    public function test_can_render_reports_page(): void
    {
        $response = $this->actingAs($this->user)->get('/reports');
        $response->assertOk();
        $response->assertSee('Laporan Uang Masuk & Keluar');
        $response->assertSee('Periode');
    }

    public function test_can_render_cash_ledger_view(): void
    {
        $rendered = view('livewire.cash-ledger-page', [
            'activeSection' => 'ledger',
            'accounts' => collect(),
            'categories' => collect(),
            'ledgerRows' => collect(),
            'summary' => ['income' => 0, 'expense' => 0, 'balance' => 0],
            'expenses' => collect(),
        ])->render();

        $this->assertStringContainsString('Kelola Arus Kas Sekolah', $rendered);
    }
}
