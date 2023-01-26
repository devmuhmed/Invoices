<?php

namespace Database\Seeders;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'invoices',
            'invoice-list',
            'paid-invoice',
            'unpaid-invoice',
            'partial-invoice',
            'archived-invoice',
            'print-invoice',
            'reports',
            'invoice-report',
            'customer-report',
            'users',
            'user-list',
            'user-accessability',
            'settings',
            'section',
            'product',

            'add-invoice',
            'delete-invoice',
            'edit-invoice',
            'change-paid-case-invoice',
            'export-excel',
            'add-attachment',
            'delete-attachment',

            'add-user',
            'edit-user',
            'delete-user',

            'show-permission',
            'add-permission',
            'edit-permission',
            'delete-permission',

            'add-section',
            'edit-section',
            'delete-section',

            'add-product',
            'edit-product',
            'delete-product',
            'notification',

        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
