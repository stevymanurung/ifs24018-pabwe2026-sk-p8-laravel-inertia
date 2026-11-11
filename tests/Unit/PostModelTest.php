<?php
namespace Tests\Unit;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Features\Post\Models\Post;
use App\Models\User;
use App\Features\Post\Enums\PostCategory;
class PostModelTest extends TestCase
{
 use RefreshDatabase;
 public function test_post_belongs_to_user_and_slug_is_unique():void{$user=User::factory()->create();$post=Post::create(['user_id'=>$user->id,'title'=>'Hello World','slug'=>'hello-world','category'=>'Teknologi','content'=>'Konten panjang.']);$this->assertSame($user->id,$post->user->id);$this->assertSame('hello-world-2',Post::uniqueSlug('Hello World'));$this->assertSame('hello-world',Post::uniqueSlug('Hello World',$post->id));}
 public function test_category_enum_contains_six_categories():void{$this->assertCount(6,PostCategory::values());$this->assertContains('Gaya Hidup',PostCategory::values());}
}
