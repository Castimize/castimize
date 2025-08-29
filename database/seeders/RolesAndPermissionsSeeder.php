<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Ranium\SeedOnce\Traits\SeedOnce;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $collection = collect([
            'Customer',
            'Manufacturer',
            'Contact',
            'Model',
            'Pricing',
            'Payment',
            'Order',
            'Invoice',
            'Complaint',
            'Api',
            'Team',
            'User',
            'Role',
            'Permission',
            // ... // List all your Models you want to have Permissions for.
        ]);

        $collection->each(function ($item, $key) {
            // create permissions for each collection item
            Permission::create(['group' => $item, 'name' => 'viewAny'.$item]);
            Permission::create(['group' => $item, 'name' => 'view'.$item]);
            Permission::create(['group' => $item, 'name' => 'update'.$item]);
            Permission::create(['group' => $item, 'name' => 'create'.$item]);
            Permission::create(['group' => $item, 'name' => 'delete'.$item]);
            Permission::create(['group' => $item, 'name' => 'destroy'.$item]);
        });

        // Create a Super-Admin Role and assign all Permissions
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::whereNotIn('group', ['Role', 'Permission'])->get());
        $roleCustomerSupport = Role::create(['name' => 'customer-support']);
        $roleCustomerSupport->givePermissionTo(Permission::whereIn('group', ['Customer', 'Manufacturer', 'Order', 'Complaint', 'Team'])->get());
        $roleManufacturer = Role::create(['name' => 'manufacturer']);
        $roleManufacturer->givePermissionTo(Permission::whereIn('group', ['Manufacturer', 'Order', 'Invoice', 'Payment', 'Complaint'])->get());
        $roleCustomer = Role::create(['name' => 'customer']);
        $roleCustomer->givePermissionTo(Permission::whereIn('group', ['Customer', 'Order', 'Invoice', 'Payment', 'Complaint'])->get());
        $roleApi = Role::create(['name' => 'api']);
        $roleApi->givePermissionTo(Permission::whereIn('group', ['Api'])->get());

        // Give Users Super-Admin Role (System user and dev)
        $user = User::where('email', 'matthijs.bon1@gmail.com')->first();
        $user->assignRole('super-admin');
        $user = User::where('email', 'matthbon@hotmail.com')->first();
        $user->assignRole('super-admin');

        // Give Users Admin Role
        $user = User::where('email', 'oscar@castimize.com')->first();
        $user->assignRole('admin');
        $user = User::where('email', 'robin@castimize.com')->first();
        $user->assignRole('admin');
        $user = User::where('email', 'koen@castimize.com')->first();
        $user->assignRole('admin');
    }
}
