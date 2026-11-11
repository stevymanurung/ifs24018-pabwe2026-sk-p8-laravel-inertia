<?php
namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
 public function run():void {User::firstOrCreate(['username'=>env('ADMIN_USERNAME','admin')],['name'=>env('ADMIN_NAME','Administrator'),'email'=>env('ADMIN_EMAIL','admin@example.com'),'password'=>Hash::make(env('ADMIN_PASSWORD','ChangeMe123!')),'role'=>'admin']);}
}
