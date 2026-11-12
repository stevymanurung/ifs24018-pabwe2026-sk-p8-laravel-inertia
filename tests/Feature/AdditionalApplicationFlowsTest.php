<?php

namespace Tests\Feature;

use App\Models\User;
use App\Features\Post\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdditionalApplicationFlowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_login_and_logout_flows(): void
    {
        $this->post('/register', [
            'name' => 'Akun Baru',
            'username' => 'akunbaru',
            'email' => 'akunbaru@example.test',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ])->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'akunbaru', 'role' => 'member']);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->post('/login', [
            'username' => 'akunbaru@example.test',
            'password' => 'StrongPass123!',
        ])->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
    }

    public function test_profile_page_and_profile_update_validation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertOk();
        $this->actingAs($user)->put('/profile', [
            'name' => 'Nama Profil',
            'username' => $user->username,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect();
        $this->assertSame('Nama Profil', $user->fresh()->name);

        $this->actingAs($user)->put('/profile', [
            'name' => '',
            'username' => $user->username,
            'email' => $user->email,
        ])->assertSessionHasErrors('name');
    }

    public function test_member_post_crud_show_and_cover_endpoints(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user)->post('/dashboard/posts', [
            'title' => 'Artikel CRUD',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan',
            'content' => str_repeat('Konten yang cukup panjang. ', 2),
            'is_published' => false,
        ])->assertRedirect();

        $post = $user->posts()->firstOrFail();
        $this->actingAs($user)->get('/dashboard/posts/'.$post->id)->assertOk();
        $this->actingAs($user)->put('/dashboard/posts/'.$post->id, [
            'title' => 'Artikel CRUD Diperbarui',
            'category' => 'Pendidikan',
            'excerpt' => 'Ringkasan baru',
            'content' => str_repeat('Konten baru yang cukup panjang. ', 2),
            'is_published' => false,
        ])->assertRedirect();

        $this->actingAs($user)->post('/dashboard/posts/'.$post->id.'/cover', [
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertRedirect();
        $this->assertNotNull($post->fresh()->cover_image_path);
        $this->actingAs($user)->delete('/dashboard/posts/'.$post->id.'/cover')->assertRedirect();
        $this->assertNull($post->fresh()->cover_image_path);

        $this->actingAs($user)->delete('/dashboard/posts/'.$post->id)->assertRedirect();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_post_index_validation_and_ai_generate_save_flows(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard/posts')->assertOk();

        $this->actingAs($user)->post('/dashboard/posts/ai-generate', [
            'prompt' => 'Topik teknologi untuk artikel blog',
            'quantity' => 1,
        ])->assertSessionHasErrors('ai');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => json_encode(['articles' => [[
                    'title' => 'Draft dari AI',
                    'category' => 'Teknologi',
                    'excerpt' => 'Ringkasan AI',
                    'content' => str_repeat('Konten AI yang informatif. ', 2),
                ]]])]]],
            ], 200),
        ]);
        config(['services.openai.key' => 'test-key']);
        $this->actingAs($user)->post('/dashboard/posts/ai-generate', [
            'prompt' => 'Topik teknologi untuk artikel blog',
            'quantity' => 1,
        ])->assertRedirect()->assertSessionHas('aiGenerated');

        $this->actingAs($user)->post('/dashboard/posts/ai-save', [
            'articles' => [[
                'title' => 'Artikel AI Disimpan',
                'category' => 'Teknologi',
                'excerpt' => 'Ringkasan',
                'content' => str_repeat('Konten artikel AI tersimpan. ', 2),
            ]],
        ])->assertRedirect();
        $this->assertDatabaseHas('posts', ['title' => 'Artikel AI Disimpan', 'is_published' => false]);
    }

    public function test_admin_can_view_user_posts_and_admin_post_list(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();
        Post::create([
            'user_id' => $member->id,
            'title' => 'Artikel Admin',
            'slug' => 'artikel-admin',
            'category' => 'Umum',
            'content' => 'Konten artikel untuk halaman admin.',
        ]);

        $this->actingAs($admin)->get('/admin/posts')->assertOk();
        $this->actingAs($admin)->get('/admin/users/'.$member->id.'/posts')->assertOk();
        $this->actingAs($admin)->patch('/admin/users/'.$member->id.'/role', [
            'role' => 'unknown',
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->delete('/admin/users/'.$admin->id)->assertStatus(422);
    }
}
