<?php namespace WebEd\Plugins\Blog\Repositories\Contracts;

use Illuminate\Support\Collection;
use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;

interface PostRepositoryContract
{
    /**
     * @param array $data
     * @param array|null $categories
     * @param array|null $tags
     * @return int|null
     */
    public function createPost(array $data, array $categories = null, array $tags = null);

    /**
     * @param int|null|PostModelContract $id
     * @param array $data
     * @param array|null $categories
     * @param array|null $tags
     * @return int|null
     */
    public function createOrUpdatePost($id, array $data, array $categories = null, array $tags = null);

    /**
     * @param int|null|PostModelContract $id
     * @param array $data
     * @return int
     */
    public function updatePost($id, array $data, array $categories = null, array $tags = null);

    /**
     * @param int|PostModelContract|array $id
     * @return bool
     */
    public function deletePost($id);

    /**
     * @param PostModelContract|int $model
     * @param array $categories
     * @return bool|null
     */
    public function syncCategories($model, array $categories);

    /**
     * @param PostModelContract|int $model
     * @return array
     */
    public function getRelatedCategoryIds($model);

    /**
     * @param PostModelContract|int $model
     * @param array $tags
     * @return bool|null
     */
    public function syncTags($model, array $tags);

    /**
     * @param PostModelContract|int $model
     * @return array
     */
    public function getRelatedTagIds($model);

    /**
     * @param array|int $categoryId
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getPostsByCategory($categoryId, array $params = []);
}
