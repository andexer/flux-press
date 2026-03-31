<?php

namespace App\Services;

class PostService extends BaseService
{
    /**
     * Get latest posts.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function latestPosts(int $limit = 6): array
    {
        $query = new \WP_Query([
            'post_type' => 'post',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
        ]);

        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $postId = get_the_ID();
                $thumbnail = get_the_post_thumbnail_url($postId, 'medium_large');

                $posts[] = [
                    'id' => $postId,
                    'title' => get_the_title(),
                    'url' => get_permalink(),
                    'image' => $thumbnail ?: '',
                    'date' => get_the_date('M j, Y'),
                    'excerpt' => get_the_excerpt(),
                ];
            }

            wp_reset_postdata();
        }

        return $posts;
    }

    /**
     * Determine the correct title based on the current context.
     */
    public function getTitle(string $viewName): string
    {
        if ($viewName !== 'partials.page-header') {
            return get_the_title();
        }

        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }

            return __('Latest Posts', 'sage');
        }

        if (is_archive()) {
            return get_the_archive_title();
        }

        if (is_search()) {
            return sprintf(
                /* translators: %s is replaced with the search query */
                __('Search Results for %s', 'sage'),
                get_search_query()
            );
        }

        if (is_404()) {
            return __('Not Found', 'sage');
        }

        return get_the_title();
    }
}
