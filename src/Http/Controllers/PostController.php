<?php namespace WebEd\Plugins\Blog\Http\Controllers;

use WebEd\Base\Core\Http\Controllers\BaseAdminController;
use WebEd\Plugins\Blog\Http\DataTables\PostsListDataTable;
use WebEd\Plugins\Blog\Models\Contracts\PostModelContract;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;
use WebEd\Plugins\Blog\Repositories\PostRepository;
use Yajra\Datatables\Engines\BaseEngine;

class PostController extends BaseAdminController
{
    protected $module = 'webed-blog';

    /**
     * PostController constructor.
     * @param PostRepository $repository
     */
    public function __construct(PostRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->breadcrumbs->addLink('Blog')
            ->addLink('Posts', route('admin::blog.posts.index.get'));

        $this->getDashboardMenu('webed-blog-posts');
    }

    public function getIndex(PostsListDataTable $postsListDataTable)
    {
        $this->setPageTitle('Posts', 'All available blog posts');

        $this->dis['dataTable'] = $postsListDataTable->run();

        return do_filter('blog.posts.index.get', $this)->viewAdmin('index-posts');
    }

    /**
     * Get data for DataTable
     * @param PostsListDataTable|BaseEngine $postsListDataTable
     * @return \Illuminate\Http\JsonResponse
     */
    public function postListing(PostsListDataTable $postsListDataTable)
    {
        $data = $postsListDataTable->with($this->groupAction());

        return do_filter('datatables.blog.posts.index.post', $data, $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    private function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if(!$this->userRepository->hasPermission($this->loggedInUser, 'edit-posts')) {
                return [
                    'customActionMessage' => 'You do not have permission',
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if(!$this->userRepository->hasPermission($this->loggedInUser, 'delete-posts')) {
                        return [
                            'customActionMessage' => 'You do not have permission',
                            'customActionStatus' => 'danger',
                        ];
                    }
                    /**
                     * Delete pages
                     */
                    $result = $this->deleteDelete($ids);
                    break;
                case 'activated':
                case 'disabled':
                    $result = $this->repository->updateMultiple($ids, [
                        'status' => $actionValue,
                    ], true);
                    break;
                default:
                    $result = [
                        'messages' => 'Method not allowed',
                        'error' => true
                    ];
                    break;
            }
            $data['customActionMessage'] = $result['messages'];
            $data['customActionStatus'] = $result['error'] ? 'danger' : 'success';

        }
        return $data;
    }

    /**
     * Update page status
     * @param $id
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUpdateStatus($id, $status)
    {
        $data = [
            'status' => $status
        ];
        $result = $this->repository->updatePost($id, $data);
        return response()->json($result, $result['response_code']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle('Create post');
        $this->breadcrumbs->addLink('Create post');

        $this->dis['currentId'] = 0;

        $this->dis['allCategories'] = get_categories_with_children();

        $this->dis['object'] = $this->repository->getModel();
        $oldInputs = old();
        if ($oldInputs) {
            foreach ($oldInputs as $key => $row) {
                $this->dis['object']->$key = $row;
            }
        }

        return do_filter('blog.posts.create.get', $this)->viewAdmin('edit-posts');
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit($id)
    {
        $id = do_filter('blog.posts.before-edit.get', $id);
        /**
         * @var PostModelContract $item
         */
        $item = $this->repository->find($id);
        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This post not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->dis['allCategories'] = get_categories_with_children();
        $this->dis['categories'] = $item->categories()->getRelatedIds()->toArray();

        $this->setPageTitle('Edit post', $item->title);
        $this->breadcrumbs->addLink('Edit post');

        $this->dis['object'] = $item;
        $this->dis['currentId'] = $id;

        return do_filter('blog.posts.edit.get', $this, $id)->viewAdmin('edit-posts');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit($id = null)
    {
        $data = [
            'page_template' => $this->request->get('page_template', null),
            'status' => $this->request->get('status'),
            'title' => $this->request->get('title'),
            'slug' => ($this->request->get('slug') ? str_slug($this->request->get('slug')) : str_slug($this->request->get('title'))),
            'keywords' => $this->request->get('keywords'),
            'description' => $this->request->get('description'),
            'content' => $this->request->get('content'),
            'thumbnail' => $this->request->get('thumbnail'),
            'order' => $this->request->get('order'),
            'updated_by' => $this->loggedInUser->id,
            'categories' => $this->request->get('categories', []),
        ];

        if ((int)$id < 1) {
            $result = $this->createPost($data);
        } else {
            $id = do_filter('blog.posts.before-edit.post', $id);

            $result = $this->updatePost($id, $data);
        }

        do_action('blog.posts.after-edit.post', $id, $result, $this);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if($result['error']) {
            if((int)$id < 1) {
                return redirect()->back()->withInput();
            }
            return redirect()->back();
        }

        if ($this->request->has('_continue_edit')) {
            if ((int)$id < 1) {
                if (!$result['error']) {
                    return redirect()->to(route('admin::blog.posts.edit.get', ['id' => $result['data']->id]));
                }
            }
            return redirect()->back();
        }

        return redirect()->to(route('admin::blog.posts.index.get'));
    }

    /**
     * @param array $data
     * @return array
     */
    private function createPost(array $data)
    {
        if(!$this->userRepository->hasPermission($this->loggedInUser, 'create-posts')) {
            return redirect()->to(route('admin::error', ['code' => 403]));
        }

        $data['created_by'] = $this->loggedInUser->id;

        return $this->repository->createPost($data);
    }

    /**
     * @param $id
     * @param array $data
     * @return array
     */
    private function updatePost($id, array $data)
    {
        return $this->repository->updatePost($id, $data);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter('blog.posts.before-delete.delete', $id);

        $result = $this->repository->delete($id);

        do_action('blog.posts.after-delete.delete', $id, $result, $this);

        return response()->json($result, $result['response_code']);
    }
}
