<?php namespace WebEd\Plugins\Blog\Http\Requests;

use WebEd\Base\Core\Http\Requests\Request;

class UpdatePostRequest extends Request
{
    public $rules = [
        'page_template' => 'string|max:255|nullable',
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255',
        'description' => 'string|max:1000',
        'content' => 'string',
        'thumbnail' => 'string|max:255',
        'keywords' => 'string|max:255',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
        'is_featured' => 'integer|in:0,1',
    ];
}
