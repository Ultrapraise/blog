<?php namespace WebEd\Plugins\Blog\Repositories;

use WebEd\Base\Caching\Repositories\AbstractRepositoryCacheDecorator;

use WebEd\Plugins\Blog\Repositories\Contracts\BlogTagRepositoryContract;

class BlogTagRepositoryCacheDecorator extends AbstractRepositoryCacheDecorator  implements BlogTagRepositoryContract
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
