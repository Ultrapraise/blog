<?php

use \WebEd\Plugins\Blog\Models\BlogTag;

if (!function_exists('get_posts_by_tag')) {
    /**
     * @param array|int $categoryIds
     * @param array $params
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    function get_posts_by_tag($tagId, array $params = [])
    {
        $params = array_merge([
            'status' => 'activated',
            'take' => null,
            'per_page' => 0,
            'current_paged' => 1,
            'order_by' => [
                'posts.order' => 'ASC'
            ],
            'select' => [
                'posts.id', 'posts.title', 'posts.slug', 'posts.created_at', 'posts.updated_at',
                'posts.content', 'posts.description', 'posts.keywords', 'posts.order', 'posts.thumbnail'
            ],
            'group_by' => [
                'posts.id', 'posts.title', 'posts.slug', 'posts.created_at', 'posts.updated_at',
                'posts.content', 'posts.description', 'posts.keywords', 'posts.order', 'posts.thumbnail'
            ]
        ], $params);

        /**
         * @var \WebEd\Plugins\Blog\Repositories\PostRepository $postRepo
         */
        $postRepo = app(\WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract::class);
        $result = $postRepo
            ->where('posts.status', '=', $params['status'])
            ->pushCriteria(new WebEd\Plugins\Blog\Criterias\Filter\WherePostBelongsToTags((array)$tagId, $params['group_by']))
            ->select($params['select'])
            ->orderBy($params['order_by']);

        if ($params['take']) {
            return $result->take($params['take'])->get();
        }

        if ($params['per_page']) {
            return $result->paginate($params['per_page'], ['*'], 'page', $params['current_paged']);
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
