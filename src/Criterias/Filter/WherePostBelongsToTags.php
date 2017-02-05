<?php namespace WebEd\Plugins\Blog\Criterias\Filter;

use Illuminate\Database\Eloquent\Builder;
use WebEd\Base\Core\Repositories\Contracts\AbstractRepositoryContract;
use WebEd\Base\Core\Criterias\AbstractCriteria;
use WebEd\Plugins\Blog\Models\Post;

class WherePostBelongsToTags extends AbstractCriteria
{
     /**
      * @param Post|Builder $model
      * @param AbstractRepositoryContract $repository
      * @param array $crossData
      * @return mixed
      */
    public function apply($model, AbstractRepositoryContract $repository, array $crossData = [])
    {
        return $model->join('posts_tags', 'posts.id', '=', 'posts_tags.post_id')
            ->join('blog_tags', 'blog_tags.id', '=', 'posts_tags.tag_id')
            ->where('blog_tags.id', 'IN', $crossData['tagIds'])
            ->distinct()
            ->groupBy('posts.id')
            ->select('posts.*');
    }
}
