<?php
namespace App\Features\Ai\Services;
use App\Features\Ai\Contracts\AiClientInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;
class OpenAiPostGeneratorService implements AiClientInterface
{
 public function generate(string $prompt,int $quantity):array {
  $key=config('services.openai.key');if(!$key)throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi.');
  $response=Http::withToken($key)->timeout(90)->post('https://api.openai.com/v1/chat/completions',['model'=>config('services.openai.model','gpt-4o-mini'),'temperature'=>0.7,'response_format'=>['type'=>'json_object'],'messages'=>[['role'=>'system','content'=>'Buat artikel blog berbahasa Indonesia. Balas JSON object dengan properti articles berupa array objek title, category (Teknologi/Pendidikan/Gaya Hidup/Bisnis/Opini/Umum), excerpt, content Markdown.'],['role'=>'user','content'=>"Buat {$quantity} draft artikel tentang: {$prompt}. Pastikan isi informatif dan orisinal."]]]);
  $response->throw();$content=$response->json('choices.0.message.content');$decoded=json_decode($content,true);$items=$decoded['articles']??[];if(!is_array($items)||count($items)===0)throw new RuntimeException('Respons AI tidak memiliki daftar artikel yang valid.');
  return array_slice(array_map(fn($item)=>['title'=>trim($item['title']??'Artikel AI'),'category'=>in_array($item['category']??'',\App\Features\Post\Enums\PostCategory::values(),true)?$item['category']:'Umum','excerpt'=>trim($item['excerpt']??''),'content'=>trim($item['content']??'')],$items),0,$quantity);
 }
}
