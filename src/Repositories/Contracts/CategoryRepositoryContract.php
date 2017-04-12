<?php namespace WebEd\Plugins\Blog\Repositories\Contracts;

use Illuminate\Support\Collection;
use WebEd\Base\Models\Contracts\BaseModelContract;

interface CategoryRepositoryContract
{
    /**
     * @param array $data
     * @return int
     */
    public function createCategory(array $data);

    /**
     * @param int|null|BaseModelContract $id
     * @param array $data
     * @return int
     */
    public function createOrUpdateCategory($id, array $data);

    /**
     * @param int|null|BaseModelContract $id
     * @param array $data
     * @return int
     */
    public function updateCategory($id, array $data);

    /**
     * @param int|BaseModelContract|array $id
     * @return bool
     */
    public function deleteCategory($id);

    /**
     * @param array $select
     * @param array $orderBy
     * @return Collection
     */
    public function getCategories(array $select, array $orderBy);
}
