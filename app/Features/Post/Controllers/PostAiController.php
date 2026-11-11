<?php
namespace App\Features\Post\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Models\Post;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
class PostAiController extends Controller
{
 public function generate(Request $request,AiClientInterface $ai){$data=$request->validate(['prompt'=>['required','string','min:10','max:1000'],'quantity'=>['required','integer','min:1','max:5']]);try{$items=$ai->generate($data['prompt'],(int)$data['quantity']);}catch(\Throwable $e){return back()->withErrors(['ai'=>$e->getMessage()]);}return back()->with('aiGenerated',$items);}
 public function save(Request $request){$data=$request->validate(['articles'=>['required','array','min:1','max:5'],'articles.*.title'=>['required','string','max:180'],'articles.*.category'=>['required','in:'.implode(',',PostCategory::values())],'articles.*.excerpt'=>['nullable','string','max:600'],'articles.*.content'=>['required','string','min:20']]);DB::transaction(function()use($data,$request){foreach($data['articles'] as $item)$request->user()->posts()->create([...$item,'slug'=>Post::uniqueSlug($item['title']),'is_published'=>false]);});return back()->with('success','Draft hasil AI berhasil disimpan.');}
}
