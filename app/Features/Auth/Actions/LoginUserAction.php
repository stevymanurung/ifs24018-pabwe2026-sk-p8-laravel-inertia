<?php
namespace App\Features\Auth\Actions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class LoginUserAction
{
 public function execute(string $identity,string $password,bool $remember=false): void {
  $field=filter_var($identity,FILTER_VALIDATE_EMAIL)?'email':'username';
  if(!Auth::attempt([$field=>$identity,'password'=>$password],$remember)) throw ValidationException::withMessages(['username'=>'Username/email atau password tidak sesuai.']);
  request()->session()->regenerate();
 }
}
