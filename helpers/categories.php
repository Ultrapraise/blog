<?php

use WebEd\Plugins\Blog\Models\Contracts\CategoryModelContract;
use WebEd\Plugins\Blog\Models\Category;

if (!function_exists('get_categories')) {
    /**
     * @param array $args
     * @param string $indent
     * @return array|mixed
     */
    function get_categories(array $args = [])
    {
        $select = array_get($args, 'select', '*');
        $indent = array_get($args, 'indent', '——');

        /**
         * @var \WebEd\Plugins\Blog\Repositories\CategoryRepository $repo
         */
        $repo = app(\WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract::class);
        $categories = $repo
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->select($select)
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

if (!function_exists('get_category_link')) {
    /**
     * @param Category $category
     * @return string
     */
    function get_category_link(CategoryModelContract $category)
    {
        return route('front.web.resolve-blog.get', ['slug' => $category->slug]);
    }
}
