<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Features\Post\Models\Post;
class User extends Authenticatable
{
 use HasFactory, Notifiable;
 protected $fillable = ['name','username','email','password','role'];
 protected $hidden = ['password','remember_token'];
 protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
 public function posts(): HasMany { return $this->hasMany(Post::class); }
 public function isAdmin(): bool { return $this->role === 'admin'; }
 public function isMember(): bool { return $this->role === 'member'; }
}
