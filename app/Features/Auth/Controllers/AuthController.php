<?php
namespace App\Features\Auth\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Auth\Actions\LoginUserAction;
use App\Features\Auth\Actions\RegisterUserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
class AuthController extends Controller
{
 public function loginPage(){return Inertia::render('Auth/Login');}
 public function registerPage(){return Inertia::render('Auth/Register');}
 public function login(Request $request,LoginUserAction $action){$data=$request->validate(['username'=>['required','string'],'password'=>['required','string'],'remember'=>['nullable','boolean']]);$action->execute($data['username'],$data['password'],(bool)($data['remember']??false));return redirect()->intended(Auth::user()->isAdmin()?'/admin/users':'/dashboard/posts');}
 public function register(Request $request,RegisterUserAction $action){$data=$request->validate(['name'=>['required','string','max:120'],'username'=>['required','alpha_dash','min:3','max:30','unique:users,username'],'email'=>['required','email','max:255','unique:users,email'],'password'=>['required','confirmed',Password::min(10)->letters()->mixedCase()->numbers()->symbols()]]);$user=$action->execute($data);Auth::login($user);$request->session()->regenerate();return redirect('/dashboard/posts');}
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect('/');}
}
