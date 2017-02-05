<?php namespace WebEd\Plugins\Blog\Criterias\Filter;

use Illuminate\Database\Eloquent\Builder;
use WebEd\Base\Core\Repositories\Contracts\AbstractRepositoryContract;
use WebEd\Base\Core\Criterias\AbstractCriteria;
use WebEd\Plugins\Blog\Models\Post;

class WherePostBelongsToCategories extends AbstractCriteria
{
     /**
      * @param Post|Builder $model
      * @param AbstractRepositoryContract $repository
      * @param array $crossData
      * @return mixed
      */
    public function apply($model, AbstractRepositoryContract $repository, array $crossData = [])
    {
        return $model->join('posts_categories', 'posts.id', '=', 'posts_categories.post_id')
            ->join('categories', 'categories.id', '=', 'posts_categories.category_id')
            ->whereIn('categories.id', $crossData['categoryIds'])
            ->distinct()
            ->groupBy($crossData['groupBy'])
            ->select('posts.*');
    }
}
