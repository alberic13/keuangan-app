<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);
        $this->admin = User::firstOrCreate(['username' => 'test_admin_fee'], [
            'name' => 'Test Admin Fee',
            'email' => 'admin_fee_test@local.test',
            'password' => 'password',
            'is_active' => true,
        ]);
        $this->admin->assignRole('admin_keuangan');
    }

    public function test_admin_can_create_fee_type_and_scheme(): void
    {
        $batch = Batch::query()->firstOrCreate(['year_label' => '2026/2027'], ['academic_year' => '2026/2027', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post('/fee-types', [
            'code' => 'SPP-'.uniqid(),
            'name' => 'SPP Bulanan Testing',
            'category' => 'spp',
            'billing_frequency' => 'monthly',
            'applies_to' => 'all',
        ]);

        $response->assertRedirect();

        $feeType = FeeType::latest('id')->first();

        $schemeResponse = $this->actingAs($this->admin)->post('/fee-schemes', [
            'fee_type_id' => $feeType->id,
            'batch_id' => $batch->id,
            'nominal' => 250000,
            'effective_start' => '2026-07-01',
            'effective_end' => '2027-06-30',
        ]);

        $schemeResponse->assertRedirect();
        $this->assertDatabaseHas('fee_schemes', [
            'fee_type_id' => $feeType->id,
            'nominal' => 250000,
        ]);
    }

    public function test_overlapping_fee_scheme_is_rejected(): void
    {
        $batch = Batch::query()->firstOrCreate(['year_label' => '2026/2027'], ['academic_year' => '2026/2027', 'is_active' => true]);
        $feeType = FeeType::query()->create([
            'code' => 'FEE-'.uniqid(),
            'name' => 'Fee Overlap Test',
            'category' => 'spp',
            'billing_frequency' => 'monthly',
            'applies_to' => 'all',
            'is_active' => true,
        ]);

        FeeScheme::create([
            'fee_type_id' => $feeType->id,
            'batch_id' => $batch->id,
            'nominal' => 200000,
            'effective_start' => '2026-01-01',
            'effective_end' => '2026-12-31',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/fee-schemes', [
            'fee_type_id' => $feeType->id,
            'batch_id' => $batch->id,
            'nominal' => 250000,
            'effective_start' => '2026-06-01',
            'effective_end' => '2027-05-31',
        ]);

        $response->assertSessionHasErrors('effective_start');
    }
}
