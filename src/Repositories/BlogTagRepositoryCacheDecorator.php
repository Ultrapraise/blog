<?php namespace WebEd\Plugins\Blog\Repositories;

use WebEd\Base\Repositories\Eloquent\EloquentBaseRepositoryCacheDecorator;

use WebEd\Plugins\Blog\Repositories\Contracts\BlogTagRepositoryContract;
use WebEd\Base\Models\Contracts\BaseModelContract;

class BlogTagRepositoryCacheDecorator extends EloquentBaseRepositoryCacheDecorator implements BlogTagRepositoryContract
{
    /**
     * @param array $data
     * @return int
     */
    public function createBlogTag(array $data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param int|null|BaseModelContract $id
     * @param array $data
     * @return int
     */
    public function createOrUpdateBlogTag($id, array $data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param int|null|BaseModelContract $id
     * @param array $data
     * @return int
     */
    public function updateBlogTag($id, array $data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param int|BaseModelContract|array $id
     * @return bool
     */
    public function deleteBlogTag($id)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }
}
