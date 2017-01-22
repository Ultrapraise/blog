<?php

use \WebEd\Plugins\Blog\Models\BlogTag;

if (!function_exists('get_posts_by_tag')) {
    /**
     * @param array|string $tagIds
     * @param array $params
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    function get_posts_by_tag($tagIds, array $params = [])
    {
        $params = array_merge([
            'status' => 'activated',
            'per_page' => 0,
            'current_page' => 0,
            'order_by' => [
                'order' => 'ASC'
            ],
        ], $params);
        /**
         * @var \WebEd\Plugins\Blog\Repositories\PostRepository $postRepo
         */
        $postRepo = app(\WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract::class);
        $result = $postRepo
            ->where('posts.status', '=', array_get($params, 'status', 'activated'))
            ->whereBelongsToTags((array)$tagIds)
            ->orderBy(array_get($params, 'order_by', []));

        if (array_get($params, 'per_page')) {
            $result = $result->paginate(array_get($params, 'per_page'))
                ->setCurrentPaged(array_get($params, 'current_page'));
        }

        return $result->get();
    }
}

if (!function_exists('get_tag_link')) {
    /**
     * @param BlogTag $tag
     * @return string
     */
    function get_tag_link(BlogTag $tag)
    {
        return route('front.web.blog.tags.get', ['slug' => $tag->slug]);
    }
}