<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use App\Services\PostService;

class Post extends Composer
{
    /**
     * @var PostService
     */
    protected PostService $postService;

    /**
     * Create a new composer.
     *
     * @param  PostService  $postService
     * @return void
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.page-header',
        'partials.content',
        'partials.content-*',
    ];

    /**
     * Retrieve the post title.
     */
    public function title(): string
    {
        return $this->postService->getTitle($this->view->name());
    }

    /**
     * Retrieve the pagination links.
     */
    public function pagination(): string
    {
        return wp_link_pages([
            'echo' => 0,
            'before' => '<p>'.__('Pages:', 'flux-press'),
            'after' => '</p>',
        ]);
    }
}
