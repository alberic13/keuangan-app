<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected CashAccount $account;
    protected ExpenseCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);
        $this->user = User::query()->firstOrCreate(
            ['username' => 'test_admin_expense'],
            [
                'name' => 'Admin Expense Test',
                'email' => 'admin_expense@local.test',
                'password' => 'password',
                'is_active' => true,
            ]
        );
        $this->user->assignRole('admin_keuangan');

        $this->account = CashAccount::query()->firstOrCreate(
            ['name' => 'Kas Operasional Test'],
            ['type' => 'cash', 'is_active' => true]
        );

        $this->category = ExpenseCategory::query()->firstOrCreate(
            ['code' => 'TEST-KAT-EXP'],
            ['name' => 'Kategori Uji Coba', 'is_active' => true]
        );
    }

    public function test_can_create_expense_with_supplier(): void
    {
        $response = $this->actingAs($this->user)->post('/expenses', [
            'transaction_date' => now()->toDateString(),
            'category_id' => $this->category->id,
            'payment_account_id' => $this->account->id,
            'amount' => 150000,
            'supplier' => 'CV Sumber Makmur',
            'description' => 'Beli ATK Kantor',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'category_id' => $this->category->id,
            'payment_account_id' => $this->account->id,
            'amount' => 150000,
            'supplier' => 'CV Sumber Makmur',
            'description' => 'Beli ATK Kantor',
        ]);
    }

    public function test_can_update_expense_supplier(): void
    {
        $expense = Expense::query()->create([
            'expense_no' => 'EXP-TEST-'.uniqid(),
            'transaction_date' => now()->toDateString(),
            'category_id' => $this->category->id,
            'payment_account_id' => $this->account->id,
            'amount' => 200000,
            'supplier' => 'Vendor Lama',
            'description' => 'Konsumsi Rapat',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put("/expenses/{$expense->id}", [
            'transaction_date' => now()->toDateString(),
            'category_id' => $this->category->id,
            'payment_account_id' => $this->account->id,
            'amount' => 250000,
            'supplier' => 'Toko Berkah Baru',
            'description' => 'Konsumsi Rapat Revisi',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 250000,
            'supplier' => 'Toko Berkah Baru',
        ]);
    }

    public function test_expenses_page_renders_supplier_input_and_table_column(): void
    {
        Expense::query()->create([
            'expense_no' => 'EXP-TEST-PAGE-'.uniqid(),
            'transaction_date' => now()->toDateString(),
            'category_id' => $this->category->id,
            'payment_account_id' => $this->account->id,
            'amount' => 75000,
            'supplier' => 'PT Mitra Sejati',
            'description' => 'Service AC',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/expenses');

        $response->assertOk();
        $response->assertSee('name="supplier"', false);
        $response->assertSee('PT Mitra Sejati');
    }

    public function test_expense_print_renders_supplier(): void
    {
        $expense = Expense::query()->create([
            'expense_no' => 'EXP-TEST-PRINT-'.uniqid(),
            'transaction_date' => now()->toDateString(),
            'category_id' => $this->category->id,
            'payment_account_id' => $this->account->id,
            'amount' => 80000,
            'supplier' => 'Toko Kertas Makmur',
            'description' => 'Kertas A4',
            'status' => 'posted',
            'created_by' => $this->user->id,
        ]);
        $expense->load(['category', 'paymentAccount']);

        $viewHtml = view('prints.expense', ['expense' => $expense])->render();
        $this->assertStringContainsString('Toko Kertas Makmur', $viewHtml);
        $this->assertStringContainsString('Penerima / Supplier', $viewHtml);

        $response = $this->actingAs($this->user)->get("/expenses/{$expense->id}/print");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
