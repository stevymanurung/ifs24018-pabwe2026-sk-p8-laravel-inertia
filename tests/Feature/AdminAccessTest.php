<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_and_all_posts(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/posts')->assertOk();
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts')->assertForbidden();
    }

    public function test_admin_routes_are_read_only(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->patch('/admin/users/'.$member->id.'/role', ['role' => 'admin'])
            ->assertNotFound();
        $this->actingAs($admin)->delete('/admin/users/'.$admin->id)->assertNotFound();

        $this->assertSame('member', $member->fresh()->role);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
