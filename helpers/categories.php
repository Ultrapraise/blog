<?php

if (!function_exists('get_categories')) {
    /**
     * @param array $args
     * @param string $indent
     * @return array|mixed
     */
    function get_categories(array $args = [])
    {
        $indent = array_get($args, 'indent', '——');

        /**
         * @var \WebEd\Plugins\Blog\Repositories\CategoryRepository $repo
         */
        $repo = app(\WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract::class);
        $categories = $repo
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->select(array_get($args, 'select', ['*']))
            ->get();

        $categories = sort_item_with_children($categories);

        foreach ($categories as $category) {
            $indentText = '';
            $depth = (int)$category->depth;
            for ($i = 0; $i < $depth; $i++) {
                $indentText .= $indent;
            }
            $category->indent_text = $indentText;
        }

        return $categories;
    }
}

if (!function_exists('get_categories_with_children')) {
    /**
     * @return array
     */
    function get_categories_with_children()
    {
        /**
         * @var \WebEd\Plugins\Blog\Repositories\CategoryRepository $repo
         */
        $repo = app(\WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract::class);
        $categories = $repo
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->get();

        /**
         * @var \WebEd\Base\Core\Support\SortItemsWithChildrenHelper $sortHelper
         */
        $sortHelper = app(\WebEd\Base\Core\Support\SortItemsWithChildrenHelper::class);
        $sortHelper
            ->setChildrenProperty('child_cats')
            ->setItems($categories);

        return $sortHelper->sort();
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
            ->pushCriteria(new WebEd\Plugins\Blog\Criterias\Filter\WherePostBelongsToCategories((array)$categoryIds, $params['group_by']))
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
