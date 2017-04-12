<?php namespace WebEd\Plugins\Blog\Http\Requests;

use WebEd\Base\Http\Requests\Request;

class CreateBlogTagRequest extends Request
{
    public function rules()
    {
        return [
            'tag.title' => 'string|max:255|required',
            'tag.slug' => 'string|max:255|unique:blog_tags,slug',
            'tag.status' => 'string|required|in:activated,disabled',
            'tag.order' => 'integer|min:0',
        ];
    }
}
