<?php namespace WebEd\Plugins\Blog\Criterias\Filter;

use Illuminate\Database\Eloquent\Builder;
use WebEd\Base\Core\Repositories\Contracts\AbstractRepositoryContract;
use WebEd\Base\Core\Criterias\AbstractCriteria;
use WebEd\Plugins\Blog\Models\Post;

class WherePostBelongsToCategories extends AbstractCriteria
{
    /**
     * @var array
     */
    protected $categoryIds;

    /**
     * @var array
     */
    protected $groupBy;

    public function __construct(array $categoryIds, array $groupBy)
    {
        $this->categoryIds = $categoryIds;

        $this->groupBy = $groupBy;
    }

    /**
      * @param Post|Builder $model
      * @param AbstractRepositoryContract $repository
      * @return mixed
      */
    public function apply($model, AbstractRepositoryContract $repository)
    {
        return $model->join('posts_categories', 'posts.id', '=', 'posts_categories.post_id')
            ->join('categories', 'categories.id', '=', 'posts_categories.category_id')
            ->whereIn('categories.id', $this->categoryIds)
            ->distinct()
            ->groupBy($this->groupBy);
    }
}
