<?php
namespace App\Features\Profile\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class ProfileService
{
 public function update(User $user,array $data):void{$user->fill(['name'=>$data['name'],'username'=>$data['username'],'email'=>$data['email']]);if(!empty($data['password']))$user->password=Hash::make($data['password']);$user->save();}
}
