<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User management
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            // Task management
            'tasks.view',
            'tasks.view-all',
            'tasks.create',
            'tasks.update',
            'tasks.update-status',
            'tasks.delete',
            'tasks.assign',
            // Reports
            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Employee – restricted access
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'tasks.view',
            'tasks.update-status',
        ]);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'tasks.view',
            'tasks.view-all',
            'tasks.create',
            'tasks.update',
            'tasks.update-status',
            'tasks.assign',
            'reports.view',
        ]);

        // Admin – all permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());
    }
}
