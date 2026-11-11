<?php
namespace App\Features\Post\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AdminPostController extends Controller
{
 public function index(Request $request){$posts=Post::with('user')->latest()->when($request->q,fn($q,$v)=>$q->where(fn($w)=>$w->whereRaw('LOWER(title) LIKE ?', ['%'.mb_strtolower($v).'%'])->orWhereRaw('LOWER(content) LIKE ?', ['%'.mb_strtolower($v).'%'])))->paginate(15)->withQueryString();return Inertia::render('Admin/PostList',['posts'=>$posts,'filters'=>$request->only('q')]);}
 public function export(PostExcelService $excel){return $excel->export(Post::query(),'semua-artikel.xlsx');}
 public function userPosts(int $user){$owner=\App\Models\User::findOrFail($user);return Inertia::render('Admin/UserPostList',['owner'=>$owner->only('id','name','username'),'posts'=>$owner->posts()->latest()->paginate(15)]);}
 public function exportUser(int $user,PostExcelService $excel){$owner=\App\Models\User::findOrFail($user);return $excel->export($owner->posts(),'artikel-'.$owner->username.'.xlsx');}
}
