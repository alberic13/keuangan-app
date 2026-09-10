<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_export_report_as_xlsx_and_pdf(): void
    {
        Role::firstOrCreate(['name' => 'admin_keuangan', 'guard_name' => 'web']);
        $user = User::firstOrCreate(['username' => 'test_admin_export'], [
            'name' => 'Test Admin Export',
            'email' => 'admin_export@local.test',
            'password' => 'password',
            'is_active' => true,
        ]);
        $user->assignRole('admin_keuangan');

        $xlsxResponse = $this->actingAs($user)->get('/reports/export?type=daily-cash&format=xlsx&date=2026-09-10');
        $xlsxResponse->assertOk();
        $this->assertTrue(str_contains((string) $xlsxResponse->headers->get('content-disposition'), '.xlsx'));

        $pdfResponse = $this->actingAs($user)->get('/reports/export?type=daily-cash&format=pdf&date=2026-09-10');
        $pdfResponse->assertOk();
        $this->assertTrue(str_contains((string) $pdfResponse->headers->get('content-disposition'), '.pdf'));
    }
}
