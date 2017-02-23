<?php

use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Models\Post;

if (!function_exists('get_posts_by_category')) {
    /**
     * @param array|int $categoryIds
     * @param array $params
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    function get_posts_by_category($categoryIds, array $params = [])
    {
        $params = array_merge([
            'status' => 'activated',
            'take' => null,
            'per_page' => 0,
            'current_paged' => 0,
            'order_by' => [
                'posts.order' => 'ASC'
            ],
            'select' => [
                'posts.id', 'posts.title', 'posts.slug', 'posts.created_at', 'posts.updated_at',
                'posts.content', 'posts.description', 'posts.keywords', 'posts.order', 'posts.thumbnail'
            ]
        ], $params);

        /**
         * @var \WebEd\Plugins\Blog\Repositories\PostRepository $postRepo
         */
        $postRepo = app(\WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract::class);
        $result = $postRepo
            ->where('posts.status', '=', array_get($params, 'status', 'activated'))
            ->pushCriteria(new WebEd\Plugins\Blog\Criterias\Filter\WherePostBelongsToCategories($categoryIds, $params['select']))
            ->select(array_get($params, 'select'))
            ->orderBy(array_get($params, 'order_by', []));

        if (array_get($params, 'take')) {
            return $result->take(array_get($params, 'take'))->get();
        }

        if (array_get($params, 'per_page')) {
            return $result->paginate(array_get($params, 'per_page'), ['*'], 'page', array_get($params, 'current_paged'));
        }

        return $result->get();
    }
}

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
