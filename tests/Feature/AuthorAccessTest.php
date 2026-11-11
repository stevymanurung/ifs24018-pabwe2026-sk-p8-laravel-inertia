<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Features\Post\Models\Post;
class AuthorAccessTest extends TestCase
{
 use RefreshDatabase;
 public function test_member_can_create_and_manage_own_posts():void{$user=User::factory()->create();$this->actingAs($user)->post('/dashboard/posts',['title'=>'Artikel Pertama','category'=>'Teknologi','excerpt'=>'Ringkasan','content'=>str_repeat('Konten artikel. ',4),'is_published'=>false])->assertRedirect();$post=$user->posts()->firstOrFail();$this->actingAs($user)->patch('/dashboard/posts/'.$post->id.'/publish')->assertRedirect();$this->assertTrue($post->fresh()->is_published);$this->actingAs($user)->delete('/dashboard/posts/'.$post->id)->assertRedirect();$this->assertDatabaseMissing('posts',['id'=>$post->id]);}
 public function test_member_cannot_access_other_members_posts():void{$one=User::factory()->create();$two=User::factory()->create();$post=Post::create(['user_id'=>$two->id,'title'=>'Milik orang','slug'=>'milik-orang','category'=>'Umum','content'=>'Konten artikel yang cukup panjang untuk tes.']);$this->actingAs($one)->get('/dashboard/posts/'.$post->id)->assertForbidden();$this->actingAs($one)->put('/dashboard/posts/'.$post->id,['title'=>'Hack','category'=>'Umum','content'=>'Konten yang cukup panjang untuk validasi.'])->assertForbidden();}
 public function test_admin_cannot_open_member_dashboard():void{$admin=User::factory()->admin()->create();$this->actingAs($admin)->get('/dashboard/posts')->assertForbidden();}
}
