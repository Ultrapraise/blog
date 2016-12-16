<?php

if (!function_exists('get_posts_by_category')) {
    /**
     * @param array|string $categoryIds
     * @param array $params
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    function get_posts_by_category($categoryIds, array $params = [])
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
            ->whereBelongsToCategories((array)$categoryIds)
            ->orderBy(array_get($params, 'order_by', []));

        if (array_get($params, 'per_page')) {
            $result = $result->paginate(array_get($params, 'per_page'))
                ->setCurrentPaged(array_get($params, 'current_page'));
        }

        return $result->get();
    }
}
