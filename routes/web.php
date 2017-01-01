<?php use Illuminate\Routing\Router;

/**
 *
 * @var Router $router
 *
 */

$adminRoute = config('webed.admin_route');

$moduleRoute = 'blog';

/**
 * Admin routes
 */
$router->group(['prefix' => $adminRoute . '/' . $moduleRoute], function (Router $router) {
    /**
     * Posts
     */
    $router->group(['prefix' => 'posts'], function (Router $router) {
        $router->get('', 'PostController@getIndex')
            ->name('admin::blog.posts.index.get')
            ->middleware('has-permission:view-posts');

        $router->post('', 'PostController@postListing')
            ->name('admin::blog.posts.index.post')
            ->middleware('has-permission:view-posts');

        $router->get('create', 'PostController@getCreate')
            ->name('admin::blog.posts.create.get')
            ->middleware('has-permission:create-posts');

        $router->post('create', 'PostController@postCreate')
            ->name('admin::blog.posts.create.post')
            ->middleware('has-permission:create-posts');

        $router->get('edit/{id}', 'PostController@getEdit')
            ->name('admin::blog.posts.edit.get')
            ->middleware('has-permission:edit-posts');

        $router->post('edit/{id}', 'PostController@postEdit')
            ->name('admin::blog.posts.edit.post')
            ->middleware('has-permission:edit-posts');

        $router->post('update-status/{id}/{status}', 'PostController@postUpdateStatus')
            ->name('admin::blog.posts.update-status.post')
            ->middleware('has-permission:edit-posts');

        $router->delete('{id}', 'PostController@deleteDelete')
            ->name('admin::blog.posts.delete.delete')
            ->middleware('has-permission:delete-posts');
    });

    /**
     * Categories
     */
    $router->group(['prefix' => 'categories'], function (Router $router) {
        $router->get('', 'CategoryController@getIndex')
            ->name('admin::blog.categories.index.get')
            ->middleware('has-permission:view-categories');

        $router->post('', 'CategoryController@postListing')
            ->name('admin::blog.categories.index.post')
            ->middleware('has-permission:view-categories');

        $router->get('create', 'CategoryController@getCreate')
            ->name('admin::blog.categories.create.get')
            ->middleware('has-permission:create-categories');

        $router->post('create', 'CategoryController@postCreate')
            ->name('admin::blog.categories.create.post')
            ->middleware('has-permission:create-categories');

        $router->get('edit/{id}', 'CategoryController@getEdit')
            ->name('admin::blog.categories.edit.get')
            ->middleware('has-permission:edit-categories');

        $router->post('edit/{id}', 'CategoryController@postEdit')
            ->name('admin::blog.categories.edit.post')
            ->middleware('has-permission:edit-categories');

        $router->post('update-status/{id}/{status}', 'CategoryController@postUpdateStatus')
            ->name('admin::blog.categories.update-status.post')
            ->middleware('has-permission:edit-categories');

        $router->delete('{id}', 'CategoryController@deleteDelete')
            ->name('admin::blog.categories.delete.delete')
            ->middleware('has-permission:delete-categories');
    });
});

/**
 * Front site routes
 */
$router->get(config('webed-blog.front_url_prefix') . '/{slug}.html', 'Front\ResolveBlogController@handle')
    ->name('front.web.resolve-blog.get');
