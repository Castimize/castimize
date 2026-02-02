<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait NeedsApiUser
{
    protected User $apiUser;

    protected Role $apiRole;

    /**
     * Set up an API user with the specified permissions.
     *
     * @param  array<string>  $permissions  List of permission names to grant
     */
    protected function setUpApiUserWithPermissions(array $permissions = []): void
    {
        $this->apiRole = Role::firstOrCreate(['name' => 'api-user', 'guard_name' => 'web']);

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
            $this->apiRole->givePermissionTo($permission);
        }

        $this->apiUser = User::factory()->create();
        $this->apiUser->assignRole($this->apiRole);
    }
}
