<?php
namespace App\Features\Post\Services;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
class PostService
{
 public function __construct(private PostRepositoryInterface $posts) {}
 public function listMine(User $user,array $filters=[]){ return $this->posts->userList($user,$filters); }
 public function create(User $user,array $data): Post { return $this->posts->create($user,$data); }
 public function update(Post $post,array $data): Post { return $this->posts->update($post,$data); }
 public function delete(Post $post): void { if($post->cover_image_path) Storage::disk('public')->delete($post->cover_image_path); $this->posts->delete($post); }
 public function storeCover(Post $post,$file): Post { if($post->cover_image_path) Storage::disk('public')->delete($post->cover_image_path); $path=$file->store('covers','public'); $post->update(['cover_image_path'=>$path]); return $post->refresh(); }
 public function removeCover(Post $post): void { if($post->cover_image_path) Storage::disk('public')->delete($post->cover_image_path); $post->update(['cover_image_path'=>null]); }
 public function togglePublish(Post $post): Post { $published=!$post->is_published; $post->update(['is_published'=>$published,'published_at'=>$published?now():null]); return $post->refresh(); }
}
