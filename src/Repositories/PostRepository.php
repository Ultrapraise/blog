<?php namespace WebEd\Plugins\Blog\Repositories;

use WebEd\Base\Core\Repositories\AbstractBaseRepository;
use WebEd\Base\Caching\Services\Contracts\CacheableContract;

use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Models\Post;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;

class PostRepository extends AbstractBaseRepository implements PostRepositoryContract, CacheableContract
{
    protected $rules = [
        'page_template' => 'string|max:255|nullable',
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|alpha_dash|unique:posts',
        'description' => 'string|max:1000',
        'content' => 'string',
        'thumbnail' => 'string|max:255',
        'keywords' => 'string|max:255',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
        'is_featured' => 'integer|in:0,1',
        'created_by' => 'integer|min:0|required',
        'updated_by' => 'integer|min:0|required',
    ];

    protected $editableFields = [
        'title',
        'page_template',
        'slug',
        'description',
        'content',
        'thumbnail',
        'keywords',
        'status',
        'order',
        'is_featured',
        'created_by',
        'updated_by',
    ];

    /**
     * @param array $data
     * @return array
     */
    public function createPost($data)
    {
        return $this->updatePost(0, $data, true, true);
    }

    /**
     * @param $id
     * @param $data
     * @param bool $allowCreateNew
     * @param bool $justUpdateSomeFields
     * @return array
     */
    public function updatePost($id, $data, $allowCreateNew = false, $justUpdateSomeFields = true)
    {
        return $this->_updatePost($id, $data, $allowCreateNew, $justUpdateSomeFields);
    }

    private function _updatePost($id, $data, $allowCreateNew = false, $justUpdateSomeFields = true)
    {
        if (!$allowCreateNew) {
            $this->unsetEditableFields('created_by');
        }

        $categories = array_get($data, 'categories', null);

        if ($categories !== null) {
            unset($data['categories']);
        }

        $tags = array_get($data, 'tags', null);

        if ($tags !== null) {
            unset($data['tags']);
        }

        $result = $this->editWithValidate($id, $data, $allowCreateNew, $justUpdateSomeFields);

        if ($result['error']) {
            return $result;
        }

        $resultSync = $this->syncCategories($result['data'], $categories);
        if ($resultSync !== null) {
            $result['messages'][] = $resultSync;
        }

        $resultSyncTags = $this->syncTags($result['data'], $tags);
        if ($resultSyncTags !== null) {
            $result['messages'][] = $resultSyncTags;
        }

        return $result;
    }

    /**
     * @param PostModelContract $model
     * @param array $categories
     */
    public function syncCategories($model, $categories = null)
    {
        if ($categories === null) {
            return null;
        }

        try {
            $model->categories()->sync((array)$categories);
            $message = 'Sync categories completed.';
        } catch (\Exception $exception) {
            $message = 'Some error occurred when sync categories.';
        }
        return $message;
    }

    /**
     * @param array $categoryIds
     * @return $this
     */
    public function whereBelongsToCategories(array $categoryIds)
    {
        return $this->join('posts_categories', 'posts.id', '=', 'posts_categories.post_id')
            ->join('categories', 'categories.id', '=', 'posts_categories.category_id')
            ->where('categories.id', 'IN', $categoryIds)
            ->distinct()
            ->groupBy('posts.id')
            ->select('posts.*');
    }

    /**
     * @param Post $post
     * @return array
     */
    public function getRelatedCategoryIds(PostModelContract $post)
    {
        try {
            return $post->categories()
                ->getRelatedIds()->toArray();
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param Post $model
     * @param array $tags
     */
    public function syncTags($model, $tags = null)
    {
        if ($tags === null) {
            return null;
        }

        try {
            $model->tags()->sync((array)$tags);
            $message = 'Sync tags completed.';
        } catch (\Exception $exception) {
            $message = 'Some error occurred when sync tags.';
        }
        return $message;
    }

    /**
     * @param array $tagIds
     * @return $this
     */
    public function whereBelongsToTags(array $tagIds)
    {
        return $this->join('posts_tags', 'posts.id', '=', 'posts_tags.post_id')
            ->join('blog_tags', 'blog_tags.id', '=', 'posts_tags.tag_id')
            ->where('blog_tags.id', 'IN', $tagIds)
            ->distinct()
            ->groupBy('posts.id')
            ->select('posts.*');
    }

    /**
     * @param Post $post
     * @return array
     */
    public function getRelatedTagIds(PostModelContract $post)
    {
        try {
            return $post->tags()
                ->getRelatedIds()->toArray();
        } catch (\Exception $exception) {
            return [];
        }
    }
}
