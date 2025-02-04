<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar izin yang akan dibuat
        $permissions = [
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
        ];

        // Buat izin-izin tersebut
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat peran 'teacher' dan berikan izin yang sesuai
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacherRole->givePermissionTo([
            'view courses',
            'create courses',
            'edit courses',
            'delete courses',
        ]);

        // Buat peran 'student' dan berikan izin 'view courses' saja
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentRole->givePermissionTo([
            'view courses',
        ]);

        // Membuat pengguna superadmin
        $user = User::create([
            'name' => 'dafa',
            'email' => 'dafa@admin.com',
            'password' => bcrypt('dafa123'),
        ]);

        // Menetapkan peran 'teacher' ke pengguna
        $user->assignRole($teacherRole);
    }
}
