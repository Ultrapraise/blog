<?php

use \WebEd\Plugins\Blog\Models\BlogTag;

if (!function_exists('get_posts_by_tag')) {
    /**
     * @param array|int $tagIds
     * @param array $params
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    function get_posts_by_tag($tagIds, array $params = [])
    {
        $params = array_merge([
            'status' => 'activated',
            'take' => null,
            'per_page' => 0,
            'current_paged' => 0,
            'order_by' => [
                'posts.order' => 'ASC'
            ],
            'select' => ['posts.*']
        ], $params);

        /**
         * @var \WebEd\Plugins\Blog\Repositories\PostRepository $postRepo
         */
        $postRepo = app(\WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract::class);
        $result = $postRepo
            ->where('posts.status', '=', array_get($params, 'status', 'activated'))
            ->whereBelongsToTags((array)$tagIds)
            ->select(array_get($params, 'select'))
            ->groupBy('posts.id')
            ->distinct()
            ->orderBy(array_get($params, 'order_by', []));

        if (array_get($params, 'take')) {
            return $result->take(array_get($params, 'take'))->get();
        }

        if (array_get($params, 'per_page')) {
            $result = $result->paginate(array_get($params, 'per_page'))
                ->setCurrentPaged(array_get($params, 'current_paged'));
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