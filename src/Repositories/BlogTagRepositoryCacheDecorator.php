<?php namespace WebEd\Plugins\Blog\Repositories;

use WebEd\Base\Caching\Repositories\Eloquent\EloquentBaseRepositoryCacheDecorator;

use WebEd\Plugins\Blog\Repositories\Contracts\BlogTagRepositoryContract;

class BlogTagRepositoryCacheDecorator extends EloquentBaseRepositoryCacheDecorator  implements BlogTagRepositoryContract
{
    /**
     * @param $data
     * @return array
     */
    public function createTag(array $data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }

    /**
     * @param $id
     * @param $data
     * @return array
     */
    public function updateTag($id, array $data)
    {
        return $this->afterUpdate(__FUNCTION__, func_get_args());
    }
}
