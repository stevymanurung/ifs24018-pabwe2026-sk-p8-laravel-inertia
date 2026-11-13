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
            $this->assertArrayHasKey('login', $exception->errors());
        }
    }

    public function test_post_repository_filters_public_and_member_lists_and_normalizes_posts(): void
    {
        $owner = User::factory()->create();
        $public = $owner->posts()->create([
            'title' => 'Teknologi Modern',
            'slug' => 'teknologi-modern',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan modern',
            'content' => 'Konten teknologi yang informatif.',
            'is_published' => true,
            'published_at' => now(),
        ]);
        $owner->posts()->create([
            'title' => 'Draft Pendidikan',
            'slug' => 'draft-pendidikan',
            'category' => 'Pendidikan',
            'content' => 'Konten draft pendidikan.',
            'is_published' => false,
        ]);

        $repository = app(PostRepository::class);
        $this->assertSame(1, $repository->paginatePublished('modern', 'Teknologi', 10)->total());
        $this->assertSame(1, $repository->paginateForUser($owner->id, 'draft', 10)->total());
        $this->assertSame(['total' => 2, 'published' => 1, 'draft' => 1], $repository->statsForUser($owner->id));

        $created = $repository->create($owner->id, [
            'title' => 'Artikel Baru',
            'slug' => 'artikel-baru',
            'category' => 'Umum',
            'content' => 'Konten artikel baru.',
            'is_published' => true,
            'published_at' => now(),
        ]);
        $this->assertNotNull($created->published_at);

        $updated = $repository->update($created, [
            'title' => 'Judul Diubah',
        ]);
        $this->assertSame('artikel-baru', $updated->slug);
        $this->assertTrue($updated->is_published);

        $repository->delete($updated);
        $this->assertDatabaseMissing('posts', ['id' => $updated->id]);
        $this->assertSame('teknologi-modern', $public->fresh()->slug);
    }

    public function test_post_service_deletes_and_manages_cover_and_publication(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $post = $owner->posts()->create([
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

    public function test_profile_and_admin_services_update_profile_and_search_users(): void
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
        $users = $adminService->paginateUsers('Nama Password');
        $this->assertSame(1, $users->total());
        $this->assertSame($user->id, $users->first()->id);
    }

    public function test_openai_service_handles_missing_key_success_and_invalid_response(): void
    {
        config(['openai.api_key' => null]);
        try {
            app(OpenAiPostGeneratorService::class)->generate('topik teknologi', 1);
            $this->fail('Expected missing API key exception.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('OPENAI_API_KEY', $exception->getMessage());
        }

        config(['openai.api_key' => 'test-key', 'openai.model' => 'gpt-test']);
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
        $this->assertSame([
            'title' => 'Artikel AI',
            'category' => 'Umum',
            'excerpt' => 'Ringkasan',
            'content' => 'Isi artikel',
        ], $items[0]->toArray());
        $this->assertCount(1, $items);

        $this->expectException(\RuntimeException::class);
        app(OpenAiPostGeneratorService::class)->generate('topik teknologi', 1);
    }
}
