<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $collection = collect([
            'Invoice',
            'Client',
            'Contact',
            'Payment',
            'Team',
            'User',
            'Role',
            'Permission',
            // ... // List all your Models you want to have Permissions for.
        ]);

        $collection->each(function ($item, $key) {
            // create permissions for each collection item
            Permission::create(['group' => $item, 'name' => 'viewAny' . $item]);
            Permission::create(['group' => $item, 'name' => 'view' . $item]);
            Permission::create(['group' => $item, 'name' => 'update' . $item]);
            Permission::create(['group' => $item, 'name' => 'create' . $item]);
            Permission::create(['group' => $item, 'name' => 'delete' . $item]);
            Permission::create(['group' => $item, 'name' => 'destroy' . $item]);
        });

        // Create a Super-Admin Role and assign all Permissions
        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'supplier']);

        // Give User Super-Admin Role
         $user = User::where('email', 'matthbon@hotmail.com')->first(); // Change this to your email.
         $user->assignRole('super-admin');

         // Give Users Admin Role
        $user = User::where('email', 'oscar@castimize.com')->first(); // Change this to your email.
        $user->assignRole('admin');
        $user = User::where('email', 'robin@castimize.com')->first(); // Change this to your email.
        $user->assignRole('admin');
        $user = User::where('email', 'koen@castimize.com')->first(); // Change this to your email.
        $user->assignRole('admin');
    }
}
