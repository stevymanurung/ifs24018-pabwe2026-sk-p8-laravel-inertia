<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Features\Post\Models\Post;
class PublicBlogTest extends TestCase
{
 use RefreshDatabase;
 public function test_guest_can_view_public_articles_but_not_drafts():void {
  $user=User::factory()->create();
  $user->posts()->create(['title'=>'Publik','slug'=>'publik','category'=>'Teknologi','content'=>'Isi artikel cukup panjang untuk dibaca.','is_published'=>true,'published_at'=>now()]);
  $user->posts()->create(['title'=>'Draft','slug'=>'draft','category'=>'Umum','content'=>'Isi draft yang tidak boleh muncul.','is_published'=>false]);
  $this->get('/articles')->assertInertia(fn($page)=>$page->component('Public/PostIndex')->has('posts.data',1)->where('posts.data.0.title','Publik'));
  $this->get('/articles/publik')->assertInertia(fn($page)=>$page->component('Public/PostShow')->where('post.title','Publik'));
  $this->get('/articles/draft')->assertNotFound();
 }
 public function test_public_navigation_and_auth_pages_are_available():void {
  $this->get('/')->assertInertia(fn($page)=>$page->component('Public/PostIndex'));
  $this->get('/login')->assertInertia(fn($page)=>$page->component('Auth/Login'));
  $this->get('/register')->assertInertia(fn($page)=>$page->component('Auth/Register'));
 }
}
