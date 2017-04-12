<?php

use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Models\Post;

if (!function_exists('get_post_link')) {
    /**
     * @param Post $post
     * @return string
     */
    function get_post_link(PostModelContract $post)
    {
        return route('front.web.resolve-blog.get', ['slug' => $post->slug]);
    }
}

if (!function_exists('get_posts_by_category')) {
    /**
     * @param array|int $categoryIds
     * @param array $params
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    function get_posts_by_category($categoryIds, array $params = [])
    {
        /**
         * @var \WebEd\Plugins\Blog\Repositories\PostRepository $postRepo
         */
        $postRepo = app(\WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract::class);

        return $postRepo->getPostsByCategory($categoryIds);
    }
}