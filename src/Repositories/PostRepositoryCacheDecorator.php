<?php namespace WebEd\Plugins\Blog\Repositories;

use WebEd\Base\Caching\Repositories\Eloquent\EloquentBaseRepositoryCacheDecorator;

use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Models\Post;
use WebEd\Plugins\Blog\Repositories\Contracts\BlogTagRepositoryContract;
use WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;

class PostRepositoryCacheDecorator extends EloquentBaseRepositoryCacheDecorator implements PostRepositoryContract
{
    /**
     * @param array $data
     * @return array
     */
    public function createPost($data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
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
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param PostModelContract $model
     * @param array $categories
     */
    public function syncCategories($model, $categories = null)
    {
        $result = call_user_func_array([$this->repository, __FUNCTION__], func_get_args());

        if (is_array($result) && isset($result['error']) && !$result['error']) {
            $this->getCacheInstance()->flushCache();

            /**
             * @var CategoryRepositoryCacheDecorator $categoryRepository
             */
            $categoryRepository = app(CategoryRepositoryContract::class);
            $categoryRepository->getCacheInstance()->flushCache();
        }
        return $result;
    }

    /**
     * @param Post $post
     * @return array
     */
    public function getRelatedCategoryIds(PostModelContract $post)
    {
        return $this->beforeGet(__FUNCTION__, func_get_args());
    }

    /**
     * @param Post $model
     * @param array $tags
     */
    public function syncTags($model, $tags = null)
    {
        $result = call_user_func_array([$this->repository, __FUNCTION__], func_get_args());

        if (is_array($result) && isset($result['error']) && !$result['error']) {
            $this->getCacheInstance()->flushCache();

            /**
             * @var BlogTagRepositoryCacheDecorator $blogTagRepository
             */
            $blogTagRepository = app(BlogTagRepositoryContract::class);
            $blogTagRepository->getCacheInstance()->flushCache();
        }
        return $result;
    }

    /**
     * @param Post $post
     * @return array
     */
    public function getRelatedTagIds(PostModelContract $post)
    {
        return $this->beforeGet(__FUNCTION__, func_get_args());
    }
}
