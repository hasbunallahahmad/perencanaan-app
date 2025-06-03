<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Cek apakah role sudah ada
            $superAdminRole = Role::where('name', 'super_admin')->first();

            if (!$superAdminRole) {
                $superAdminRole = Role::create([
                    'name' => 'super_admin',
                    'guard_name' => 'web'
                ]);
                $this->command->info('Role super_admin created.');
            } else {
                $this->command->info('Role super_admin already exists.');
            }

            // Cek apakah user sudah ada
            $superAdmin = User::where('email', 'admin@admin.com')->first();

            if (!$superAdmin) {
                $superAdmin = User::create([
                    'name' => 'Super Admin',
                    'email' => 'admin@admin.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now()
                ]);
                $this->command->info('Super Admin user created.');
            } else {
                $this->command->info('Super Admin user already exists.');
            }

            // Assign role jika belum ada
            if (!$superAdmin->hasRole('super_admin')) {
                $superAdmin->assignRole($superAdminRole);
                $this->command->info('Role assigned to Super Admin.');
            }

            // Berikan semua permissions
            $permissions = Permission::all();
            if ($permissions->count() > 0 && !$superAdmin->hasAllPermissions($permissions)) {
                $superAdmin->givePermissionTo($permissions);
                $this->command->info('All permissions granted to Super Admin.');
            }

            $this->command->info('Setup completed successfully!');
            $this->command->line('Email: admin@admin.com');
            $this->command->line('Password: password');
        } catch (\Exception $e) {
            $this->command->error('Error: ' . $e->getMessage());
        }
    }
}
