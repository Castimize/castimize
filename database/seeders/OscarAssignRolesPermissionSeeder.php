<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Ranium\SeedOnce\Traits\SeedOnce;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class OscarAssignRolesPermissionSeeder extends Seeder
{
    use SeedOnce;

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(
            ['name' => 'assign-roles'],
            ['group' => 'Role']
        );

        // Super-admin should always have all permissions
        Role::findByName('super-admin')->givePermissionTo($permission);

        User::where('email', 'oscar@castimize.com')
            ->firstOrFail()
            ->givePermissionTo($permission);
    }
}
