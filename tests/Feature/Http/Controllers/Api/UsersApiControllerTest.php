<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\NeedsApiUser;

class UsersApiControllerTest extends TestCase
{
    use DatabaseTransactions;
    use NeedsApiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiUserWithPermissions(['viewUser']);
    }

    // ========================================
    // show() tests
    // ========================================

    #[Test]
    public function it_returns_authenticated_user(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson(route('api.api.users.get-user'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'first_name',
                'last_name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.id', $this->apiUser->id);
        $response->assertJsonPath('data.email', $this->apiUser->email);
    }

    #[Test]
    public function it_returns_403_when_user_lacks_permission_for_show(): void
    {
        $userWithoutPermission = User::factory()->create();
        Sanctum::actingAs($userWithoutPermission);

        $response = $this->getJson(route('api.api.users.get-user'));

        $response->assertForbidden();
    }

    #[Test]
    public function it_returns_401_when_not_authenticated(): void
    {
        $response = $this->getJson(route('api.api.users.get-user'));

        $response->assertUnauthorized();
    }

    // ========================================
    // deleteUserWp() tests
    // ========================================

    #[Test]
    public function it_deletes_user_by_wp_id(): void
    {
        $userToDelete = User::factory()->create(['wp_id' => 54321]);

        Sanctum::actingAs($this->apiUser);

        $response = $this->deleteJson(route('api.api.users.delete-user-wp'), [
            'wp_id' => 54321,
        ]);

        $response->assertNoContent();
        $this->assertSoftDeleted('users', ['wp_id' => 54321]);
    }

    #[Test]
    public function it_returns_404_when_deleting_nonexistent_user(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->deleteJson(route('api.api.users.delete-user-wp'), [
            'wp_id' => 99999,
        ]);

        $response->assertNotFound();
    }
}
