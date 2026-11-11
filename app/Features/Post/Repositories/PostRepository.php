<?php
namespace App\Features\Post\Repositories;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
class PostRepository implements PostRepositoryInterface
{
 public function publicList(array $filters=[]): LengthAwarePaginator {
  return Post::query()->with('user')->where('is_published',true)->when($filters['q']??null,function($q,$term){$q->where(fn($w)=>$w->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($term).'%'])->orWhereRaw('LOWER(content) LIKE ?', ['%'.mb_strtolower($term).'%'])->orWhereRaw('LOWER(excerpt) LIKE ?', ['%'.mb_strtolower($term).'%']));})->when($filters['category']??null,fn($q,$v)=>$q->where('category',$v))->latest('published_at')->paginate(9)->withQueryString();
 }
 public function userList(User $user,array $filters=[]): LengthAwarePaginator {
  return $user->posts()->latest()->when($filters['q']??null,fn($q,$v)=>$q->where(fn($w)=>$w->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($v).'%'])->orWhereRaw('LOWER(content) LIKE ?', ['%'.mb_strtolower($v).'%'])))->when(($filters['status']??'')==='published',fn($q)=>$q->where('is_published',true))->when(($filters['status']??'')==='draft',fn($q)=>$q->where('is_published',false))->paginate(10)->withQueryString();
 }
 public function create(User $user,array $data): Post { return $user->posts()->create($this->normalize($data)); }
 public function update(Post $post,array $data): Post { $post->update($this->normalize($data,$post)); return $post->refresh(); }
 public function delete(Post $post): void { $post->delete(); }
 private function normalize(array $data,?Post $post=null): array {
  if (isset($data['title'])) $data['slug']=Post::uniqueSlug($data['title'],$post?->id);
  if (($data['is_published']??false) && !($post?->is_published)) $data['published_at']=now();
  if (!($data['is_published']??false)) $data['published_at']=null;
  return $data;
 }
}
