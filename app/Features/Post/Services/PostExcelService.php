<?php
namespace App\Features\Post\Services;
use App\Features\Post\Models\Post;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
class PostExcelService
{
 public function export($query,string $filename='artikel-blog.xlsx'): StreamedResponse {
  $rows=$query->with('user')->orderByDesc('created_at')->get();
  $sheet=(new Spreadsheet())->getActiveSheet();$sheet->fromArray([['Judul','Slug','Kategori','Ringkasan','Konten','Status','Penulis','Tanggal Publikasi','Dibuat']],null,'A1');
  $r=2;foreach($rows as $p){$sheet->fromArray([[$p->title,$p->slug,$p->category,$p->excerpt,$p->content,$p->is_published?'Published':'Draft',$p->user?->username,$p->published_at?->format('Y-m-d H:i:s'),$p->created_at?->format('Y-m-d H:i:s')]],null,'A'.$r++);}
  foreach(range('A','I') as $col)$sheet->getColumnDimension($col)->setAutoSize(true);
  return response()->streamDownload(function()use($sheet){(new Xlsx($sheet->getParent()))->save('php://output');},$filename,['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
 }
 public function import(User $user,$file): int {
  $sheet=IOFactory::load($file->getRealPath())->getActiveSheet();$rows=$sheet->toArray();$count=0;
  foreach(array_slice($rows,1) as $row){$title=trim((string)($row[0]??''));$content=trim((string)($row[4]??''));if($title===''||$content==='')continue;$category=in_array($row[2]??'',\App\Features\Post\Enums\PostCategory::values(),true)?$row[2]:'Umum';$published=strtolower((string)($row[5]??''))==='published';$user->posts()->create(['title'=>$title,'slug'=>Post::uniqueSlug($title),'category'=>$category,'excerpt'=>$row[3]??null,'content'=>$content,'is_published'=>$published,'published_at'=>$published?now():null]);$count++;}
  return $count;
 }
}
