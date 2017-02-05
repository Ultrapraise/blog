<?php namespace WebEd\Plugins\Blog\Repositories;

use WebEd\Base\Caching\Services\Traits\Cacheable;
use WebEd\Base\Core\Repositories\Eloquent\EloquentBaseRepository;
use WebEd\Base\Caching\Services\Contracts\CacheableContract;

use WebEd\Plugins\Blog\Models\Category;
use WebEd\Plugins\Blog\Models\Contracts\CategoryModelContract;
use WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract;

class CategoryRepository extends EloquentBaseRepository implements CategoryRepositoryContract, CacheableContract
{
    use Cacheable;

    protected $rules = [
        'parent_id' => 'integer|min:0|nullable',
        'page_template' => 'string|max:255|nullable',
        'title' => 'string|max:255|required',
        'slug' => 'string|max:255|alpha_dash|unique:categories',
        'description' => 'string|max:1000|nullable',
        'content' => 'string|nullable',
        'thumbnail' => 'string|max:255|nullable',
        'keywords' => 'string|max:255|nullable',
        'status' => 'string|required|in:activated,disabled',
        'order' => 'integer|min:0',
        'created_by' => 'integer|min:0|required',
        'updated_by' => 'integer|min:0|required',
    ];

    protected $editableFields = [
        'parent_id',
        'title',
        'page_template',
        'slug',
        'description',
        'content',
        'thumbnail',
        'keywords',
        'status',
        'order',
        'created_by',
        'updated_by',
    ];

    /**
     * @param $data
     * @return array
     */
    public function createCategory(array $data)
    {
        return $this->editWithValidate(0, $data, true);
    }

    /**
     * @param $id
     * @param $data
     * @return array
     */
    public function updateCategory($id, array $data)
    {
        $this->unsetEditableFields('created_by');
        return $this->editWithValidate($id, $data, false, true);
    }

    /**
     * @param $id
     * @param bool $justId
     * @return array
     */
    public function getChildren($id, $justId = true)
    {
        if ($id instanceof CategoryModelContract) {
            $model = $id;
        } else {
            $model = $this->find($id);
        }
        if (!$model) {
            return null;
        }

        $children = $model->children();
        if ($justId === true) {
            $result = [];
            $children = $children->select('id')->get();
            foreach ($children as $child) {
                $result[] = $child->id;
            }
            return $result;
        }
        return $children->get();
    }

    /**
     * @param $id
     * @return Category
     */
    public function getParent($id)
    {
        if ($id instanceof CategoryModelContract) {
            $model = $id;
        } else {
            $model = $this->find($id);
        }
        if (!$model) {
            return null;
        }

        return $model->parent()->first();
    }

    /**
     * @param $id
     * @return array|null
     */
    public function getAllRelatedChildrenIds($id)
    {
        if ($id instanceof CategoryModelContract) {
            $model = $id;
        } else {
            $model = $this->find($id);
        }
        if (!$model) {
            return null;
        }

        $result = [];

        $children = $model->children();
        $children = $children->select('id')->get();
        foreach ($children as $child) {
            $result[] = $child->id;
            $result = array_merge($this->getAllRelatedChildrenIds($child), $result);
        }
        return array_unique($result);
    }
}
