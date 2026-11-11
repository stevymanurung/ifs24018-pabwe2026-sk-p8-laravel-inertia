<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
class AdminAccessTest extends TestCase
{
 use RefreshDatabase;
 public function test_admin_can_view_users_and_all_posts():void{$admin=User::factory()->admin()->create();$this->actingAs($admin)->get('/admin/users')->assertOk();$this->actingAs($admin)->get('/admin/posts')->assertOk();}
 public function test_member_cannot_access_admin_routes():void{$member=User::factory()->create();$this->actingAs($member)->get('/admin/users')->assertForbidden();$this->actingAs($member)->get('/admin/posts')->assertForbidden();}
 public function test_admin_role_can_be_changed_but_not_self_deleted():void{$admin=User::factory()->admin()->create();$member=User::factory()->create();$this->actingAs($admin)->patch('/admin/users/'.$member->id.'/role',['role'=>'admin'])->assertRedirect();$this->assertSame('admin',$member->fresh()->role);$this->actingAs($admin)->delete('/admin/users/'.$admin->id)->assertStatus(422);}
}
