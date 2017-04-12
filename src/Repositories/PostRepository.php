<?php namespace WebEd\Plugins\Blog\Repositories;

use Illuminate\Support\Collection;
use WebEd\Base\Models\Contracts\BaseModelContract;
use WebEd\Base\Repositories\Eloquent\EloquentBaseRepository;
use WebEd\Base\Caching\Services\Traits\Cacheable;
use WebEd\Base\Caching\Services\Contracts\CacheableContract;

use WebEd\Plugins\Blog\Models\Post;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;

class PostRepository extends EloquentBaseRepository implements PostRepositoryContract, CacheableContract
{
    use Cacheable;

    /**
     * @param array $data
     * @param array|null $categories
     * @param array|null $tags
     * @return int|null
     */
    public function createPost(array $data, array $categories = null, array $tags = null)
    {
        $result = $this->create($data);
        if ($result) {
            if ($categories !== null) {
                $this->syncCategories($result, $categories);
            }
            if ($tags !== null) {
                $this->syncTags($result, $tags);
            }
        }
        return $result;
    }

    /**
     * @param int|null|Post $id
     * @param array $data
     * @param array|null $categories
     * @param array|null $tags
     * @return int|null
     */
    public function createOrUpdatePost($id, array $data, array $categories = null, array $tags = null)
    {
        $result = $this->createOrUpdate($id, $data);
        if ($result) {
            if ($categories !== null) {
                $this->syncCategories($result, $categories);
            }
            if ($tags !== null) {
                $this->syncTags($result, $tags);
            }
        }
        return $result;
    }

    /**
     * @param int|null|Post $id
     * @param array $data
     * @return int
     */
    public function updatePost($id, array $data, array $categories = null, array $tags = null)
    {
        $result = $this->update($id, $data);
        if ($result) {
            if ($categories !== null) {
                $this->syncCategories($result, $categories);
            }
            if ($tags !== null) {
                $this->syncTags($result, $tags);
            }
        }
        return $result;
    }

    /**
     * @param int|Post|array $id
     * @return bool
     */
    public function deletePost($id)
    {
        return $this->delete($id);
    }

    /**
     * @param Post|int $model
     * @param array $categories
     * @return bool|null
     */
    public function syncCategories($model, array $categories)
    {
        $model = $model instanceof Post ? $model : $this->find($model);

        try {
            $model->categories()->sync($categories);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param Post|int $model
     * @return array
     */
    public function getRelatedCategoryIds($model)
    {
        $model = $model instanceof Post ? $model : $this->find($model);

        try {
            return $model->categories()->allRelatedIds()->toArray();
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param Post|int $model
     * @param array $tags
     * @return bool|null
     */
    public function syncTags($model, array $tags)
    {
        $model = $model instanceof Post ? $model : $this->find($model);

        try {
            $model->tags()->sync($tags);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param Post|int $model
     * @return array
     */
    public function getRelatedTagIds($model)
    {
        $model = $model instanceof Post ? $model : $this->find($model);

        try {
            return $model->tags()->allRelatedIds()->toArray();
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * @param array|int $categoryId
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|Collection
     */
    public function getPostsByCategory($categoryId, array $params = [])
    {
        $params = array_merge([
            'status' => 'activated',
            'take' => null,
            'per_page' => 0,
            'current_paged' => 1,
            'order_by' => [
                'posts.order' => 'ASC'
            ],
            'select' => [
                'posts.id', 'posts.title', 'posts.slug', 'posts.created_at', 'posts.updated_at',
                'posts.content', 'posts.description', 'posts.keywords', 'posts.order', 'posts.thumbnail'
            ],
            'group_by' => [
                'posts.id', 'posts.title', 'posts.slug', 'posts.created_at', 'posts.updated_at',
                'posts.content', 'posts.description', 'posts.keywords', 'posts.order', 'posts.thumbnail'
            ]
        ], $params);

        $result = $this->model
            ->join('posts_categories', 'posts.id', '=', 'posts_categories.post_id')
            ->join('categories', 'categories.id', '=', 'posts_categories.category_id')
            ->whereIn('categories.id', $categoryId)
            ->distinct()
            ->groupBy($params['groupBy']);

        foreach ($params['order_by'] as $by => $direction) {
            $result = $result->orderby($by, $direction);
        }

        if ($params['take']) {
            return $result->take($params['take'])->get();
        }

        if ($params['per_page']) {
            return $result->paginate($params['per_page'], ['*'], 'page', $params['current_paged']);
        }

        return $result->get();
    }
}
