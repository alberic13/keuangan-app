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
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view_dashboard',
            'manage_students',
            'import_students',
            'manage_fees',
            'manage_billing',
            'manage_payments',
            'manage_cash',
            'view_reports',
            'view_audit_logs',
            'manage_users',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'admin_keuangan' => $permissions,
            'bendahara' => [
                'manage_payments',
                'manage_cash',
                'view_reports',
            ],
            'kepala_madrasah' => [
                'view_dashboard',
                'view_reports',
            ],
            'waka' => [
                'view_dashboard',
                'view_reports',
            ],
            'admin_tu' => [
                'view_dashboard',
                'manage_students',
                'view_reports',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($rolePermissions);
        }

        $users = [
            [
                'name' => 'Admin Keuangan',
                'username' => 'admin_keuangan',
                'email' => 'admin.keuangan@man2surakarta.sch.id',
                'password' => 'password123',
                'role' => 'admin_keuangan',
            ],
            [
                'name' => 'Bendahara Umum',
                'username' => 'bendahara',
                'email' => 'bendahara@man2surakarta.sch.id',
                'password' => 'password123',
                'role' => 'bendahara',
            ],
            [
                'name' => 'Kepala Madrasah',
                'username' => 'kepala_madrasah',
                'email' => 'kepala@man2surakarta.sch.id',
                'password' => 'password123',
                'role' => 'kepala_madrasah',
            ],
            [
                'name' => 'Waka Pimpinan',
                'username' => 'waka',
                'email' => 'waka@man2surakarta.sch.id',
                'password' => 'password123',
                'role' => 'waka',
            ],
            [
                'name' => 'Admin Tata Usaha',
                'username' => 'admin_tu',
                'email' => 'admintu@man2surakarta.sch.id',
                'password' => 'password123',
                'role' => 'admin_tu',
            ],
        ];

        $createdUsers = collect($users)->mapWithKeys(function (array $attributes) {
            $role = $attributes['role'];
            unset($attributes['role']);

            $user = User::query()->updateOrCreate(
                ['username' => $attributes['username']],
                array_merge($attributes, ['is_active' => true]),
            );

            $user->syncRoles([$role]);

            return [$user->username => $user];
        });


    }
}
