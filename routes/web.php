<?php
use Illuminate\Support\Facades\Route;
use App\Features\Auth\Controllers\AuthController;
use App\Features\Post\Controllers\PublicPostController;
use App\Features\Post\Controllers\PostController;
use App\Features\Post\Controllers\PostExcelController;
use App\Features\Post\Controllers\PostAiController;
use App\Features\Post\Controllers\AdminPostController;
use App\Features\Admin\Controllers\AdminUserController;
use App\Features\Profile\Controllers\ProfileController;
Route::get('/',[PublicPostController::class,'index'])->name('home');
Route::get('/articles',[PublicPostController::class,'index'])->name('articles.index');
Route::get('/articles/{slug}',[PublicPostController::class,'show'])->name('articles.show');
Route::middleware('guest')->group(function(){Route::get('/login',[AuthController::class,'loginPage'])->name('login');Route::post('/login',[AuthController::class,'login'])->name('login.store');Route::get('/register',[AuthController::class,'registerPage'])->name('register');Route::post('/register',[AuthController::class,'register'])->name('register.store');});
Route::middleware('auth')->group(function(){Route::post('/logout',[AuthController::class,'logout'])->name('logout');Route::get('/profile',[ProfileController::class,'edit'])->name('profile.edit');Route::put('/profile',[ProfileController::class,'update'])->name('profile.update');
 Route::prefix('dashboard/posts')->name('posts.')->middleware('role:member')->group(function(){Route::get('/',[PostController::class,'index'])->name('index');Route::get('/export',[PostExcelController::class,'export'])->name('export');Route::post('/import',[PostExcelController::class,'import'])->name('import');Route::post('/ai-generate',[PostAiController::class,'generate'])->name('ai.generate');Route::post('/ai-save',[PostAiController::class,'save'])->name('ai.save');Route::post('/',[PostController::class,'store'])->name('store');Route::get('/{post}',[PostController::class,'show'])->name('show')->middleware('post.owner');Route::put('/{post}',[PostController::class,'update'])->name('update')->middleware('post.owner');Route::delete('/{post}',[PostController::class,'destroy'])->name('destroy')->middleware('post.owner');Route::patch('/{post}/publish',[PostController::class,'publish'])->name('publish')->middleware('post.owner');Route::post('/{post}/cover',[PostController::class,'uploadCover'])->name('cover.store')->middleware('post.owner');Route::delete('/{post}/cover',[PostController::class,'deleteCover'])->name('cover.destroy')->middleware('post.owner');});
 Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function(){Route::get('/users',[AdminUserController::class,'index'])->name('users.index');Route::patch('/users/{user}/role',[AdminUserController::class,'updateRole'])->name('users.role');Route::delete('/users/{user}',[AdminUserController::class,'destroy'])->name('users.destroy');Route::get('/users/{user}/posts',[AdminPostController::class,'userPosts'])->name('users.posts');Route::get('/users/{user}/posts/export',[AdminPostController::class,'exportUser'])->name('users.posts.export');Route::get('/posts',[AdminPostController::class,'index'])->name('posts.index');Route::get('/posts/export',[AdminPostController::class,'export'])->name('posts.export');});
});
