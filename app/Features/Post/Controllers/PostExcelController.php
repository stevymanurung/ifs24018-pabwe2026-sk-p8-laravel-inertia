<?php
namespace App\Features\Post\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Post\Services\PostExcelService;
use Illuminate\Http\Request;
class PostExcelController extends Controller
{
 public function export(Request $request,PostExcelService $excel){return $excel->export($request->user()->posts(),'artikel-pribadi.xlsx');}
 public function import(Request $request,PostExcelService $excel){$data=$request->validate(['file'=>['required','file','mimes:xlsx,xls,csv','max:10240']]);try{$count=$excel->import($request->user(),$data['file']);}catch(\Throwable $e){return back()->withErrors(['file'=>'File Excel tidak dapat dibaca. Pastikan mengikuti template kolom.']);}return back()->with('success',"{$count} artikel berhasil diimpor.");}
}
