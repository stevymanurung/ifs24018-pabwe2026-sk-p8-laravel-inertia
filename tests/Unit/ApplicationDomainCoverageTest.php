<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationDomainCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_post_item_maps_all_fields(): void
    {
        $item = new GeneratedPostItem('Judul', 'Teknologi', 'Ringkasan', 'Konten');
        $this->assertSame([
            'title' => 'Judul',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkasan',
            'content' => 'Konten',
        ], $item->toArray());
    }

    public function test_category_enum_returns_all_values(): void
    {
        $this->assertSame([
            'Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum',
        ], PostCategory::values());
    }

    public function test_user_role_helpers_and_post_relationship(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $admin = User::factory()->admin()->create();

        $this->assertTrue($member->isMember());
        $this->assertFalse($member->isAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isMember());

        $post = $member->posts()->create([
            'title' => 'Artikel relasi',
            'slug' => 'artikel-relasi',
            'category' => 'Umum',
            'content' => 'Konten artikel yang cukup panjang.',
        ]);

        $this->assertSame($member->id, $member->posts()->first()->user_id);
        $this->assertSame($member->id, $post->user->id);
        $this->assertFalse((bool) $post->is_published);
        $this->assertNull($post->cover_url);
    }

    public function test_post_unique_slug_handles_empty_title_and_collisions(): void
    {
        $user = User::factory()->create();
        $user->posts()->create([
            'title' => 'Judul',
            'slug' => 'judul',
            'category' => 'Umum',
            'content' => 'Konten artikel yang cukup panjang.',
        ]);

        $service = app(PostService::class);
        $this->assertSame('artikel', $service->uniqueSlug('!!!'));
        $this->assertSame('judul-2', $service->uniqueSlug('Judul'));
    }

    public function test_post_policy_only_allows_the_owner_to_manage_a_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $post = $owner->posts()->create([
            'title' => 'Artikel policy',
            'slug' => 'artikel-policy',
            'category' => 'Umum',
            'content' => 'Konten artikel yang cukup panjang.',
        ]);
        $policy = new PostPolicy();

        $this->assertTrue($policy->manage($owner, $post));
        $this->assertFalse($policy->manage($admin, $post));
        $this->assertFalse($policy->manage($other, $post));
    }
}
