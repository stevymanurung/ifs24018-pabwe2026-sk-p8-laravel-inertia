<?php
namespace App\Features\Profile\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Profile\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
class ProfileController extends Controller
{
 public function edit(Request $request){return Inertia::render('Profile/Edit',['user'=>$request->user()->only('name','username','email')]);}
 public function update(Request $request,ProfileService $service){$user=$request->user();$data=$request->validate(['name'=>['required','string','max:120'],'username'=>['required','alpha_dash','min:3','max:30','unique:users,username,'.$user->id],'email'=>['required','email','max:255','unique:users,email,'.$user->id],'password'=>['nullable','confirmed',Password::min(10)->letters()->mixedCase()->numbers()->symbols()]]);$service->update($user,$data);return back()->with('success','Profil diperbarui.');}
}
