<?php

namespace App\Features\Ai\DTO;

use App\Features\Post\Enums\PostCategory;

final readonly class GeneratedPostItem
{
    public PostCategory $category;

    public function __construct(
        public string $title,
        string|PostCategory $category,
        public ?string $excerpt,
        public string $content,
    ) {
        $this->category = $category instanceof PostCategory
            ? $category
            : (PostCategory::tryFrom($category) ?? PostCategory::Umum);
    }

    /**
     * @return array{title: string, category: string, excerpt: string|null, content: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category->value,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
        ];
    }
}
