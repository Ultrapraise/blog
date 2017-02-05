<?php namespace WebEd\Plugins\Blog\Http\Requests;

use WebEd\Base\Core\Http\Requests\Request;

class CreateBlogTagRequest extends Request
{
    public $rules = [
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|nullable',
        'description' => 'string|max:1000|nullable',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
    ];
}
