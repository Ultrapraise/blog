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
     * @param null $parentId
     * @return array
     */
    function get_categories_with_children($parentId = null)
    {
        /**
         * @var \WebEd\Plugins\Blog\Repositories\CategoryRepository $repo
         */
        $repo = app(\WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract::class);
        $categories = $repo
            ->orderBy('order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->where('parent_id', '=', $parentId)->get();

        $result = [];
        foreach ($categories as $category) {
            $category->child_cats = get_categories_with_children($category->id);
            $result[] = $category;
        }
        return $result;
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
