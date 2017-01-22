<?php namespace WebEd\Plugins\Blog\Http\Controllers\Front;

use WebEd\Base\Core\Http\Controllers\BaseFrontController;
use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Models\Post;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;
use WebEd\Plugins\Blog\Repositories\PostRepository;

class PostController extends BaseFrontController
{
    /**
     * @param PostRepository $repository
     */
    public function __construct(PostRepositoryContract $repository)
    {
        parent::__construct();

        $this->themeController = themes_management()->getThemeController('Blog\Post');

        if (!$this->themeController) {
            echo 'You need to active a theme';
            die();
        }

        if (is_string($this->themeController)) {
            echo 'Class ' . $this->themeController . ' not exists';
            die();
        }

        $this->repository = $repository;
    }

    /**
     * @param Post $item
     * @return mixed
     */
    public function handle(PostModelContract $item)
    {
        /**
         * With post, we will active menu item by related categories
         */
        $this->dis['categoryIds'] = $item->categories()->getRelatedIds()->toArray();

        $this->setPageTitle($item->title);

        $this->dis['object'] = $item;

        $this->dis['author'] = $item->author;

        return $this->themeController->handle($item, $this->dis);
    }
}
