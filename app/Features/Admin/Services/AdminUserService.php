<?php
namespace App\Features\Admin\Services;
use App\Models\User;
class AdminUserService
{
 public function list(string $search=''){return User::query()->withCount('posts')->when($search,fn($q)=>$q->where(fn($w)=>$w->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%'])->orWhereRaw('LOWER(username) LIKE ?', ['%'.mb_strtolower($search).'%'])->orWhereRaw('LOWER(email) LIKE ?', ['%'.mb_strtolower($search).'%'])))->orderBy('name')->paginate(15)->withQueryString();}
 public function changeRole(User $user,string $role): void { $user->update(['role'=>$role]); }
 public function delete(User $user): void { $user->delete(); }
}
