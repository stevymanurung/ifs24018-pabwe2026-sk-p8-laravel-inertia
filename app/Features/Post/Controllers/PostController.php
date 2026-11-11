<?php
namespace App\Features\Post\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Post\Services\PostService;
use App\Features\Post\Models\Post;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
class PostController extends Controller
{
 public function __construct(private PostService $service){}
 public function index(Request $request){$posts=$this->service->listMine($request->user(),$request->only('q','status'));$all=$request->user()->posts();return Inertia::render('Post/Index',['posts'=>$posts,'filters'=>$request->only('q','status'),'categories'=>PostCategory::values(),'stats'=>['total'=>(clone $all)->count(),'published'=>(clone $all)->where('is_published',true)->count(),'draft'=>(clone $all)->where('is_published',false)->count()]]);}
 public function store(Request $request){$data=$this->validatePost($request);unset($data['cover']);$post=$this->service->create($request->user(),$data);if($request->hasFile('cover'))$this->service->storeCover($post,$request->file('cover'));return back()->with('success','Artikel berhasil dibuat.');}
 public function show(Post $post){$this->authorize('view',$post);return Inertia::render('Post/Show',['post'=>$post->load('user')]);}
 public function update(Request $request,Post $post){$this->authorize('update',$post);$data=$this->validatePost($request);unset($data['cover']);$this->service->update($post,$data);if($request->hasFile('cover'))$this->service->storeCover($post,$request->file('cover'));return back()->with('success','Artikel berhasil diperbarui.');}
 public function destroy(Post $post){$this->authorize('delete',$post);$this->service->delete($post);return redirect()->route('posts.index')->with('success','Artikel berhasil dihapus.');}
 public function publish(Post $post){$this->authorize('update',$post);$this->service->togglePublish($post);return back()->with('success','Status publikasi diperbarui.');}
 public function uploadCover(Request $request,Post $post){$this->authorize('update',$post);$data=$request->validate(['cover'=>['required','image','mimes:jpg,jpeg,png,webp','max:5120']]);$this->service->storeCover($post,$data['cover']);return back()->with('success','Cover berhasil diunggah.');}
 public function deleteCover(Post $post){$this->authorize('update',$post);$this->service->removeCover($post);return back()->with('success','Cover dihapus.');}
 private function validatePost(Request $request):array{return $request->validate(['title'=>['required','string','max:180'],'category'=>['required','in:'.implode(',',PostCategory::values())],'excerpt'=>['nullable','string','max:600'],'content'=>['required','string','min:20'],'is_published'=>['nullable','boolean'],'cover'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']]) + ['is_published'=>(bool)$request->boolean('is_published')];}
}
