<?php
namespace App\Features\Post\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class Post extends Model
{
 protected $fillable=['user_id','title','slug','category','excerpt','content','cover_image_path','is_published','published_at'];
 protected function casts(): array { return ['is_published'=>'boolean','published_at'=>'datetime']; }
 public function user(): BelongsTo { return $this->belongsTo(User::class); }
 public function getCoverUrlAttribute(): ?string { return $this->cover_image_path ? asset('storage/'.$this->cover_image_path) : null; }
 public static function uniqueSlug(string $title, ?int $ignoreId=null): string {
  $base=Str::slug($title) ?: 'artikel'; $slug=$base; $i=2;
  while (static::query()->where('slug',$slug)->when($ignoreId,fn($q)=>$q->whereKeyNot($ignoreId))->exists()) $slug=$base.'-'.$i++;
  return $slug;
 }
}
