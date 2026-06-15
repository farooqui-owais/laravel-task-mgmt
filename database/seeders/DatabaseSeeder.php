<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'System Admin',
                'password' => Hash::make('Password123!'),
            ]
        );
        $admin->assignRole('admin');

        // Manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name'     => 'Project Manager',
                'password' => Hash::make('Password123!'),
            ]
        );
        $manager->assignRole('manager');

        // Employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name'     => 'John Employee',
                'password' => Hash::make('Password123!'),
            ]
        );
        $employee->assignRole('employee');

        // Sample tasks
        \App\Models\Task::factory()->count(10)->create([
            'created_by'  => $admin->id,
            'assigned_to' => $employee->id,
        ]);
    }
}
