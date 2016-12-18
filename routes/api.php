<?php use Illuminate\Routing\Router;

/**
 *
 * @var Router $router
 *
 */
Route::group(['prefix' => config('webed.api_route'), 'namespace' => 'Front\Api'], function (Router $router) {
    $router->resource('posts', 'PostController');
});
