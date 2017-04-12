<?php namespace WebEd\Plugins\Blog\Models;

use WebEd\Base\Users\Models\User;
use WebEd\Plugins\Blog\Models\Contracts\BlogTagModelContract;
use WebEd\Base\Models\EloquentBase as BaseModel;

class BlogTag extends BaseModel implements BlogTagModelContract
{
    protected $table = 'blog_tags';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'order',
        'created_by',
        'updated_by',
    ];

    public $timestamps = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'posts_tags', 'tag_id', 'post_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modifier()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
