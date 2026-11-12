<?php

namespace Tests\Feature;

use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Features\Auth\Actions\LoginUserAction;
use App\Features\Auth\Actions\RegisterUserAction;
use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Features\Post\Services\PostService;
use App\Features\Profile\Services\ProfileService;
use App\Features\Admin\Services\AdminUserService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationServicesCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_action_creates_member_with_hashed_password(): void
    {
        $user = app(RegisterUserAction::class)->execute([
            'name' => 'Pengguna Tes',
            'username' => 'penggunates',
            'email' => 'pengguna@example.test',
            'password' => 'Password123!',
        ]);

        $this->assertSame('member', $user->role);
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_login_action_accepts_email_or_username_and_remember_flag(): void
    {
        $this->withSession([]);
        request()->setLaravelSession($this->app['session.store']);

        $user = User::factory()->create([
            'email' => 'login@example.test',
            'username' => 'loginuser',
            'password' => Hash::make('Password123!'),
        ]);
        $action = app(LoginUserAction::class);

        $action->execute('login@example.test', 'Password123!', true);
        $this->assertAuthenticatedAs($user);
        auth()->logout();

        $action->execute('loginuser', 'Password123!');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_action_rejects_invalid_credentials(): void
    {
        User::factory()->create(['username' => 'validuser']);
        try {
            app(LoginUserAction::class)->execute('validuser', 'wrong-password');
            $this->fail('Expected validation exception.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('username', $exception->errors());
        }
    }

    public function test_post_repository_filters_public_and_member_lists_and_normalizes_posts(): void
    {
        $owner = User::factory()->create();
        $public = Post::create([
            'user_id' => $owner->id,
            'title' => 'Teknologi Modern',
            'slug' => 'teknologi-modern',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan modern',
            'content' => 'Konten teknologi yang informatif.',
            'is_published' => true,
            'published_at' => now(),
        ]);
        Post::create([
            'user_id' => $owner->id,
            'title' => 'Draft Pendidikan',
            'slug' => 'draft-pendidikan',
            'category' => 'Pendidikan',
            'content' => 'Konten draft pendidikan.',
            'is_published' => false,
        ]);

        $repository = app(PostRepository::class);
        $this->assertSame(1, $repository->publicList(['q' => 'modern', 'category' => 'Teknologi'])->total());
        $this->assertSame(1, $repository->userList($owner, ['status' => 'published'])->total());
        $this->assertSame(1, $repository->userList($owner, ['status' => 'draft'])->total());
        $this->assertSame(1, $repository->userList($owner, ['q' => 'draft'])->total());

        $created = $repository->create($owner, [
            'title' => 'Artikel Baru',
            'category' => 'Umum',
            'content' => 'Konten artikel baru.',
            'is_published' => true,
        ]);
        $this->assertNotNull($created->published_at);
        $this->assertSame('artikel-baru', $created->slug);

        $updated = $repository->update($created, [
            'title' => 'Judul Diubah',
            'is_published' => false,
        ]);
        $this->assertSame('judul-diubah', $updated->slug);
        $this->assertNull($updated->published_at);

        $repository->delete($updated);
        $this->assertDatabaseMissing('posts', ['id' => $updated->id]);
        $this->assertSame('teknologi-modern', $public->fresh()->slug);
    }

    public function test_post_service_deletes_and_manages_cover_and_publication(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $post = Post::create([
            'user_id' => $owner->id,
            'title' => 'Artikel Layanan',
            'slug' => 'artikel-layanan',
            'category' => 'Umum',
            'content' => 'Konten artikel layanan.',
        ]);
        $service = app(PostService::class);

        $service->storeCover($post, \Illuminate\Http\UploadedFile::fake()->image('cover.jpg'));
        $this->assertNotNull($post->fresh()->cover_image_path);
        $service->removeCover($post->fresh());
        $this->assertNull($post->fresh()->cover_image_path);

        $published = $service->togglePublish($post->fresh());
        $this->assertTrue($published->is_published);
        $this->assertNotNull($published->published_at);
        $draft = $service->togglePublish($published);
        $this->assertFalse($draft->is_published);
        $this->assertNull($draft->published_at);

        $service->delete($draft);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_profile_and_admin_services_update_search_roles_and_delete(): void
    {
        $user = User::factory()->create();
        app(ProfileService::class)->update($user, [
            'name' => 'Nama Baru',
            'username' => $user->username,
            'email' => $user->email,
        ]);
        $this->assertSame('Nama Baru', $user->fresh()->name);

        app(ProfileService::class)->update($user->fresh(), [
            'name' => 'Nama Password',
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'NewPassword123!',
        ]);
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));

        $adminService = app(AdminUserService::class);
        $this->assertSame(1, $adminService->list('Nama Password')->total());
        $adminService->changeRole($user->fresh(), 'admin');
        $this->assertSame('admin', $user->fresh()->role);
        $adminService->delete($user->fresh());
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_openai_service_handles_missing_key_success_and_invalid_response(): void
    {
        config(['services.openai.key' => null]);
        try {
            app(OpenAiPostGeneratorService::class)->generate('topik teknologi', 1);
            $this->fail('Expected missing API key exception.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('OPENAI_API_KEY', $exception->getMessage());
        }

        config(['services.openai.key' => 'test-key', 'services.openai.model' => 'gpt-test']);
        Http::fakeSequence('https://api.openai.com/*')
            ->push([
                'choices' => [['message' => ['content' => json_encode(['articles' => [[
                    'title' => ' Artikel AI ',
                    'category' => 'Tidak Valid',
                    'excerpt' => ' Ringkasan ',
                    'content' => ' Isi artikel ',
                ]]])]]],
            ], 200)
            ->push([
                'choices' => [['message' => ['content' => json_encode(['articles' => []])]]],
            ], 200);

        $items = app(OpenAiPostGeneratorService::class)->generate('topik teknologi', 2);
        $this->assertSame('Artikel AI', $items[0]['title']);
        $this->assertSame('Umum', $items[0]['category']);
        $this->assertSame('Ringkasan', $items[0]['excerpt']);
        $this->assertCount(1, $items);

        $this->expectException(\RuntimeException::class);
        app(OpenAiPostGeneratorService::class)->generate('topik teknologi', 1);
    }
}
