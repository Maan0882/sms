<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::updateOrCreate(
            ['email' => 'admin@iapes.com'],
            [
                'name'              => 'IAPES Admin',
                'password'          => bcrypt('Admin@123!'),
                'email_verified_at' => now(),
                'is_active'         => true,
            ]
        );

        $admin->assignRole($adminRole);

        $this->command->info('✅ Admin created: admin@iapes.com');
        $this->command->info('🔑 Password: Admin@123! — CHANGE IN PRODUCTION!');
    }

}
