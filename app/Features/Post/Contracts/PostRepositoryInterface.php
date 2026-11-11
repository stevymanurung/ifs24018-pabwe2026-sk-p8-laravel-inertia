<?php
namespace App\Features\Post\Contracts;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface PostRepositoryInterface
{
 public function publicList(array $filters=[]): LengthAwarePaginator;
 public function userList(User $user, array $filters=[]): LengthAwarePaginator;
 public function create(User $user,array $data): Post;
 public function update(Post $post,array $data): Post;
 public function delete(Post $post): void;
}
