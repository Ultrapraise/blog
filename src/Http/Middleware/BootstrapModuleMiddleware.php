<?php namespace WebEd\Plugins\Blog\Http\Middleware;

use \Closure;
use WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract;

class BootstrapModuleMiddleware
{
    public function __construct()
    {

    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  array|string $params
     * @return mixed
     */
    public function handle($request, Closure $next, ...$params)
    {
        \AdminBar::registerLink('Post', route('admin::blog.posts.create.get'), 'add-new');
        \AdminBar::registerLink('Category', route('admin::blog.categories.create.get'), 'add-new');

        /**
         * Register to dashboard menu
         */
        \DashboardMenu::registerItem([
            'id' => 'webed-blog-posts',
            'priority' => 2,
            'parent_id' => null,
            'heading' => 'Blog',
            'title' => trans('webed-blog::base.admin_menu.posts'),
            'font_icon' => 'icon-book-open',
            'link' => route('admin::blog.posts.index.get'),
            'css_class' => null,
            'permissions' => ['view-posts'],
        ])->registerItem([
            'id' => 'webed-blog-categories',
            'priority' => 2.1,
            'parent_id' => null,
            'title' => trans('webed-blog::base.admin_menu.categories'),
            'font_icon' => 'fa fa-sitemap',
            'link' => route('admin::blog.categories.index.get'),
            'css_class' => null,
            'permissions' => ['view-categories'],
        ])->registerItem([
            'id' => 'webed-blog-tags',
            'priority' => 2.2,
            'parent_id' => null,
            'title' => trans('webed-blog::base.admin_menu.tags'),
            'font_icon' => 'icon-tag',
            'link' => route('admin::blog.tags.index.get'),
            'css_class' => null,
            'permissions' => ['view-tags'],
        ]);

        /**
         * Register menu widget
         */
        menus_management()->registerWidget(trans('webed-blog::base.categories.categories'), 'category', function () {
            $categories = get_categories_with_children();
            return $this->parseMenuWidgetData($categories);
        });

        /**
         * Register menu link type
         */
        menus_management()->registerLinkType('category', function ($id) {
            $category = app(CategoryRepositoryContract::class)
                ->where('id', '=', $id)
                ->first();
            if (!$category) {
                return null;
            }
            return [
                'model_title' => $category->title,
                'url' => route('front.web.resolve-blog.get', ['slug' => $category->slug]),
            ];
        });

        return $next($request);
    }

    protected function parseMenuWidgetData($categories)
    {
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'title' => $category->title,
                'children' => $this->parseMenuWidgetData($category->child_cats)
            ];
        }
        return $result;
    }
}
