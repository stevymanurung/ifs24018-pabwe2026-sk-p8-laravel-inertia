<?php
namespace App\Features\Admin\Controllers;
use App\Http\Controllers\Controller;
use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
class AdminUserController extends Controller
{
 public function index(Request $request,AdminUserService $service){return Inertia::render('Admin/UserList',['users'=>$service->list((string)$request->query('q')),'filters'=>$request->only('q')]);}
 public function updateRole(Request $request,User $user,AdminUserService $service){abort_if($request->user()->id===$user->id,422,'Role akun sendiri tidak dapat diubah.');$data=$request->validate(['role'=>['required','in:admin,member']]);$service->changeRole($user,$data['role']);return back()->with('success','Role pengguna diperbarui.');}
 public function destroy(Request $request,User $user,AdminUserService $service){abort_if($request->user()->id===$user->id,422,'Akun sendiri tidak dapat dihapus.');$service->delete($user);return back()->with('success','Pengguna dihapus.');}
}
