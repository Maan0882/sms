<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles & permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Define all permissions ─────────────────────────────────
        $permissions = [
            // ── User permissions ───────────────────────────────
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.impersonate',

            // ── Role permissions ───────────────────────────────
            'role.view',
            'role.create',
            'role.update',
            'role.delete',

            // ── Permission permissions ─────────────────────────
            'permission.view',
            'permission.assign',

            // ── Admin permissions ──────────────────────────────
            'admin.view',
            'admin.create',
            'admin.update',
            'admin.delete',

            // ── Mentor permissions ─────────────────────────────
            'mentor.view',
            'mentor.create',
            'mentor.update',
            'mentor.delete',

            // ── Student permissions ────────────────────────────
            'student.view',
            'student.create',
            'student.update',
            'student.delete',

            // ── System permissions ─────────────────────────────
            'settings.view',
            'settings.update',
            'audit_log.view',
            'audit_log.export',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── 2. Create roles & assign permissions ──────────────────────

        // Super Admin — gets all permissions via Gate::before (no need to list them)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Admin — can manage mentors & students
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'mentor.view', 'mentor.create', 'mentor.update', 'mentor.delete',
            'student.view', 'student.create', 'student.update', 'student.delete',
            'audit_log.view',
        ]);

        // Mentor — scoped access
        $mentorRole = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
        $mentorRole->syncPermissions([
            'student.view',
        ]);

        // Student — read-only own data
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // ── 3. Create the Super Admin user ────────────────────────────

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sms.local'],
            [
                'name'              => 'IAPES Super Admin',
                'password'          => bcrypt('SuperAdmin@123'), // Change immediately in production!
                'email_verified_at' => now(),
                'is_active'         => true,
            ]
        );

        $superAdmin->assignRole($superAdminRole);

        $this->command->info('✅ Super Admin created: superadmin@sms.local');
        $this->command->info('🔑 Default password: SuperAdmin@123 — CHANGE THIS IN PRODUCTION!');
        $this->command->info('🛡️  Roles created: super_admin, admin, mentor, student');
        $this->command->info('🔒 Permissions created: '.count($permissions).' permissions');

    }
}
