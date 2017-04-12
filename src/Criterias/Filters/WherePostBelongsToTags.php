<?php namespace WebEd\Plugins\Blog\Criterias\Filters;

use Illuminate\Database\Eloquent\Builder;
use WebEd\Base\Criterias\AbstractCriteria;
use WebEd\Base\Repositories\AbstractBaseRepository;
use WebEd\Base\Repositories\Contracts\AbstractRepositoryContract;
use WebEd\Plugins\Blog\Models\Post;

class WherePostBelongsToTags extends AbstractCriteria
{
    protected $tagIds;

    protected $groupBy;

    public function __construct(array $tagIds, array $groupBy)
    {
        $this->tagIds = $tagIds;

        $this->groupBy = $groupBy;
    }

     /**
      * @param Post|Builder $model
      * @param AbstractBaseRepository $repository
      * @return mixed
      */
    public function apply($model, AbstractRepositoryContract $repository)
    {
        return $model->join('posts_tags', 'posts.id', '=', 'posts_tags.post_id')
            ->join('blog_tags', 'blog_tags.id', '=', 'posts_tags.tag_id')
            ->whereIn('blog_tags.id', $this->tagIds)
            ->distinct()
            ->groupBy($this->groupBy);
    }
}
