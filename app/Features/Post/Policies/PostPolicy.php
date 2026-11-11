<?php
namespace App\Features\Post\Policies;
use App\Features\Post\Models\Post;
use App\Models\User;
class PostPolicy
{
 public function view(User $user,Post $post): bool { return $user->isAdmin() || $post->user_id===$user->id; }
 public function update(User $user,Post $post): bool { return $user->isMember() && $post->user_id===$user->id; }
 public function delete(User $user,Post $post): bool { return $this->update($user,$post); }
}
