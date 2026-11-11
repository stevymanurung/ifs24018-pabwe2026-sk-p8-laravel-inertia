<?php
namespace App\Features\Post\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Post\Models\Post;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Contracts\PostRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
class PublicPostController extends Controller
{
 public function __construct(private PostRepositoryInterface $posts){}
 public function index(Request $request){$filters=$request->only('q','category');return Inertia::render('Public/PostIndex',['posts'=>$this->posts->publicList($filters),'filters'=>$filters,'categories'=>PostCategory::values()]);}
 public function show(string $slug){$post=Post::with('user')->where('slug',$slug)->where('is_published',true)->firstOrFail();return Inertia::render('Public/PostShow',['post'=>$post,'related'=>Post::where('is_published',true)->where('category',$post->category)->whereKeyNot($post->id)->latest('published_at')->take(3)->get()]);}
}
