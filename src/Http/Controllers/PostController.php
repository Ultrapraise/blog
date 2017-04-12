<?php namespace WebEd\Plugins\Blog\Http\Controllers;

use Illuminate\Http\Request;
use WebEd\Base\Http\Controllers\BaseAdminController;
use WebEd\Base\Http\DataTables\AbstractDataTables;
use WebEd\Plugins\Blog\Http\DataTables\PostsListDataTable;
use WebEd\Plugins\Blog\Http\Requests\CreatePostRequest;
use WebEd\Plugins\Blog\Http\Requests\UpdatePostRequest;
use WebEd\Plugins\Blog\Repositories\BlogTagRepository;
use WebEd\Plugins\Blog\Repositories\Contracts\BlogTagRepositoryContract;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;
use WebEd\Plugins\Blog\Repositories\PostRepository;
use Yajra\Datatables\Engines\BaseEngine;

class PostController extends BaseAdminController
{
    protected $module = 'webed-blog';

    /**
     * @var PostRepository
     */
    protected $repository;

    /**
     * @param PostRepository $repository
     */
    public function __construct(PostRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->middleware(function (Request $request, $next) {
            $this->getDashboardMenu($this->module . '-posts');

            $this->breadcrumbs
                ->addLink(trans('webed-blog::base.page_title'))
                ->addLink(trans('webed-blog::base.posts.page_title'), route('admin::blog.posts.index.get'));

            return $next($request);
        });
    }

    /**
     * @param AbstractDataTables|BaseEngine $dataTables
     * @return @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(PostsListDataTable $dataTables)
    {
        $this->setPageTitle(trans('webed-blog::base.posts.page_title'));

        $this->dis['dataTable'] = $dataTables->run();

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_BLOG_POSTS, 'index.get', $dataTables)->viewAdmin('posts.index');
    }

    /**
     * @param AbstractDataTables|BaseEngine $dataTables
     * @return mixed
     */
    public function postListing(PostsListDataTable $dataTables)
    {
        $data = $dataTables->with($this->groupAction());

        return do_filter(BASE_FILTER_CONTROLLER, $data, WEBED_BLOG_POSTS, 'index.post', $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    protected function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['your-permission'])) {
                return [
                    'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, ['your-permission'])) {
                        return [
                            'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                            'customActionStatus' => 'danger',
                        ];
                    }
                    $ids = do_filter(BASE_FILTER_BEFORE_DELETE, $ids, WEBED_BLOG_POSTS);

                    $result = $this->repository->deletePost($ids);

                    do_action(BASE_ACTION_AFTER_DELETE, WEBED_BLOG_POSTS, $ids, $result);
                    break;
                case 'activated':
                case 'disabled':
                    $result = $this->repository->updateMultiple($ids, [
                        'status' => $actionValue,
                    ]);
                    break;
                default:
                    return [
                        'customActionMessage' => trans('webed-core::errors.' . \Constants::METHOD_NOT_ALLOWED . '.message'),
                        'customActionStatus' => 'danger'
                    ];
                    break;
            }
            $data['customActionMessage'] = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
            $data['customActionStatus'] = !$result ? 'danger' : 'success';

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
        $result = $this->repository->update($id, $data);
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    /**
     * @param BlogTagRepository $blogTagRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate(BlogTagRepositoryContract $blogTagRepository)
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_BLOG_POSTS, 'create.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor',
                'jquery-select2'
            ])
            ->addStylesheets(['jquery-select2']);

        $this->dis['categories'] = get_categories_with_children();
        $this->dis['tags'] = $blogTagRepository
            ->get(['id', 'title'])
            ->pluck('title', 'id')
            ->toArray();

        $this->setPageTitle(trans('webed-blog::base.posts.form.create_post'));
        $this->breadcrumbs->addLink(trans('webed-blog::base.posts.form.create_post'));

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_BLOG_POSTS, 'create.get')->viewAdmin('posts.create');
    }

    public function postCreate(CreatePostRequest $request)
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_BLOG_POSTS, 'create.post');

        $data = $this->parseData($request);
        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createPost($data, $request->get('categories', []), $request->get('tags', []));

        do_action(BASE_ACTION_AFTER_CREATE, WEBED_BLOG_POSTS, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if (!$result) {
            return redirect()->back()->withInput();
        }

        if ($this->request->has('_continue_edit')) {
            return redirect()->to(route('admin::blog.posts.create.get', ['id' => $result]));
        }

        return redirect()->to(route('admin::blog.posts.index.get'));
    }

    /**
     * @param BlogTagRepository $blogTagRepository
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit(BlogTagRepositoryContract $blogTagRepository, $id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-blog::base.item_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_BLOG_POSTS, 'edit.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor',
                'jquery-select2'
            ])
            ->addStylesheets(['jquery-select2']);

        $this->dis['categories'] = get_categories_with_children();
        $this->dis['selectedCategories'] = $this->repository->getRelatedCategoryIds($item);

        $this->dis['tags'] = $blogTagRepository
            ->get(['id', 'title'])
            ->pluck('title', 'id')
            ->toArray();
        $this->dis['selectedTags'] = $this->repository->getRelatedTagIds($item);

        $this->setPageTitle(trans('webed-blog::base.posts.form.edit_item') . ' #' . $item->id);
        $this->breadcrumbs->addLink(trans('webed-blog::base.posts.form.edit_item'));

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_BLOG_POSTS, 'edit.get', $id)->viewAdmin('posts.edit');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdatePostRequest $request, $id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-blog::base.item_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_BLOG_POSTS, 'edit.post');

        $data = $this->parseData($request);
        $data['updated_by'] = $this->loggedInUser->id;

        $result = $this->repository->updatePost($item, $data, $request->get('categories', []), $request->get('tags', []));

        do_action(BASE_ACTION_AFTER_UPDATE, WEBED_BLOG_POSTS, $id, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if ($this->request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::blog.posts.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter(BASE_FILTER_BEFORE_DELETE, $id, WEBED_BLOG_POSTS);

        $result = $this->repository->delete($id);

        do_action(BASE_ACTION_AFTER_DELETE, WEBED_BLOG_POSTS, $id, $result);

        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    protected function parseData(\WebEd\Base\Http\Requests\Request $request)
    {
        $data = $request->get('post');
        if (!$data['slug']) {
            $data['slug'] = str_slug($data['title']);
        }
        return $data;
    }
}