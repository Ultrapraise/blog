<?php

use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Models\Post;

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
