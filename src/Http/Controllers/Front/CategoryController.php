<?php namespace WebEd\Plugins\Blog\Http\Controllers\Front;

use WebEd\Base\Core\Http\Controllers\BaseFrontController;
use WebEd\Plugins\Blog\Models\Category;
use WebEd\Plugins\Blog\Models\Contracts\CategoryModelContract;
use WebEd\Plugins\Blog\Repositories\CategoryRepository;
use WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;
use WebEd\Plugins\Blog\Repositories\PostRepository;

class CategoryController extends BaseFrontController
{
    /**
     * @var PostRepository
     */
    protected $postRepository;
    /**
     * @param CategoryRepository $repository
     * @param PostRepository $postRepository
     */
    public function __construct(
        CategoryRepositoryContract $repository,
        PostRepositoryContract $postRepository
    )
    {
        parent::__construct();

        $this->themeController = themes_management()->getThemeController('Blog\Category');

        if (!$this->themeController) {
            echo '<h2>You need to active a theme</h2>';
            die();
        }

        $this->repository = $repository;
        $this->postRepository = $postRepository;
    }

    /**
     * @param Category $item
     * @return mixed
     */
    public function handle(CategoryModelContract $item)
    {
        $this->setPageTitle($item->title);

        $this->dis['object'] = $item;

        $this->dis['allRelatedCategoryIds'] = array_unique(array_merge($this->repository->getAllRelatedChildrenIds($item), [$item->id]));

        $posts = $this->postRepository
            ->whereBelongsToCategories($this->dis['allRelatedCategoryIds'])
            ->paginate(5)
            ->get();

        $this->dis['posts'] = $posts;

        return $this->themeController->handle($item, $this->dis);
    }
}
