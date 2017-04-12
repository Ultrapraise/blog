<?php namespace WebEd\Plugins\Blog\Http\Controllers;

use Illuminate\Http\Request;
use WebEd\Base\Http\Controllers\BaseAdminController;
use WebEd\Base\Http\DataTables\AbstractDataTables;
use WebEd\Plugins\Blog\Http\DataTables\CategoriesListDataTable;
use WebEd\Plugins\Blog\Http\Requests\CreateCategoryRequest;
use WebEd\Plugins\Blog\Http\Requests\UpdateCategoryRequest;
use WebEd\Plugins\Blog\Repositories\CategoryRepository;
use WebEd\Plugins\Blog\Repositories\Contracts\CategoryRepositoryContract;
use Yajra\Datatables\Engines\BaseEngine;

class CategoryController extends BaseAdminController
{
    protected $module = 'webed-blog';

    /**
     * @var CategoryRepository
     */
    protected $repository;

    public function __construct(CategoryRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->middleware(function (Request $request, $next) {
            $this->getDashboardMenu($this->module . '-categories');

            $this->breadcrumbs
                ->addLink(trans('webed-blog::base.page_title'))
                ->addLink(trans('webed-blog::base.categories.page_title'), route('admin::blog.categories.index.get'));

            return $next($request);
        });
    }

    /**
     * @param AbstractDataTables|BaseEngine $dataTables
     * @return @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getIndex(CategoriesListDataTable $dataTables)
    {
        $this->setPageTitle(trans('webed-blog::base.categories.page_title'));

        $this->dis['dataTable'] = $dataTables->run();

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_BLOG_CATEGORIES, 'index.get', $dataTables)->viewAdmin('categories.index');
    }

    /**
     * @param AbstractDataTables|BaseEngine $dataTables
     * @return mixed
     */
    public function postListing(CategoriesListDataTable $dataTables)
    {
        $data = $dataTables->with($this->groupAction());

        return do_filter(BASE_FILTER_CONTROLLER, $data, WEBED_BLOG_CATEGORIES, 'index.post', $this);
    }

    /**
     * Handle group actions
     * @return array
     */
    protected function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['update-categories'])) {
                return [
                    'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, ['delete-categories'])) {
                        return [
                            'customActionMessage' => trans('webed-acl::base.do_not_have_permission'),
                            'customActionStatus' => 'danger',
                        ];
                    }
                    /**
                     * Delete items
                     */
                    $ids = do_filter(BASE_FILTER_BEFORE_DELETE, $ids, WEBED_BLOG_CATEGORIES);
                    $result = $this->repository->deleteCategory($ids);
                    do_action(BASE_ACTION_AFTER_DELETE, WEBED_BLOG_CATEGORIES, $ids, $result);
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_BLOG_CATEGORIES, 'create.get');

        $allCategories = get_categories();

        $selectArr = ['' => trans('webed-core::base.form.select') . '...',];
        foreach ($allCategories as $category) {
            $selectArr[$category->id] = $category->indent_text . $category->title;
        }
        $this->dis['categories'] = $selectArr;

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle(trans('webed-blog::base.categories.form.create_category'));
        $this->breadcrumbs->addLink(trans('webed-blog::base.categories.form.create_category'));

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_BLOG_CATEGORIES, 'create.get')->viewAdmin('categories.create');
    }

    public function postCreate(CreateCategoryRequest $request)
    {
        do_action(BASE_ACTION_BEFORE_CREATE, WEBED_BLOG_CATEGORIES, 'create.post');

        $data = $this->parseInputData($request);
        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createCategory($data);

        do_action(BASE_ACTION_AFTER_CREATE, WEBED_BLOG_CATEGORIES, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if (!$result) {
            return redirect()->back()->withInput();
        }

        if ($this->request->has('_continue_edit')) {
            return redirect()->to(route('admin::blog.categories.edit.get', ['id' => $result]));
        }

        return redirect()->to(route('admin::blog.categories.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getEdit($id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-blog::base.item_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_BLOG_CATEGORIES, 'edit.get');

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $categories = get_categories();
        $selectArr = ['' => trans('webed-core::base.form.select') . '...',];
        $childrenIds = $this->repository->getAllRelatedChildrenIds($item) ?: [];
        $childCategories = array_merge($childrenIds, [$id]);
        foreach ($categories as $category) {
            if (!in_array($category->id, $childCategories)) {
                $selectArr[$category->id] = $category->indent_text . $category->title;
            }
        }
        $this->dis['categories'] = $selectArr;

        $this->setPageTitle(trans('webed-blog::base.categories.edit_item') . ' #' . $item->id);
        $this->breadcrumbs->addLink(trans('webed-blog::base.categories.edit_item'));

        $this->dis['object'] = $item;

        return do_filter(BASE_FILTER_CONTROLLER, $this, WEBED_BLOG_CATEGORIES, 'edit.get', $id)->viewAdmin('categories.edit');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEdit(UpdateCategoryRequest $request, $id)
    {
        $item = $this->repository->find($id);

        if (!$item) {
            flash_messages()
                ->addMessages(trans('webed-blog::base.item_not_exists'), 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $data = $this->parseInputData($request);
        $data['updated_by'] = $this->loggedInUser->id;

        $item = do_filter(BASE_FILTER_BEFORE_UPDATE, $item, WEBED_BLOG_CATEGORIES, 'edit.post');

        $result = $this->repository->updateCategory($item, $data);

        do_action(BASE_ACTION_AFTER_UPDATE, WEBED_BLOG_CATEGORIES, $id, $result);

        $msgType = !$result ? 'danger' : 'success';
        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');

        flash_messages()
            ->addMessages($msg, $msgType)
            ->showMessagesOnSession();

        if ($this->request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::blog.categories.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter(BASE_FILTER_BEFORE_DELETE, $id, WEBED_BLOG_CATEGORIES);

        $result = $this->repository->deleteCategory($id);

        do_action(BASE_ACTION_AFTER_DELETE, WEBED_BLOG_CATEGORIES, $id, $result);

        $msg = $result ? trans('webed-core::base.form.request_completed') : trans('webed-core::base.form.error_occurred');
        $code = $result ? \Constants::SUCCESS_NO_CONTENT_CODE : \Constants::ERROR_CODE;
        return response()->json(response_with_messages($msg, !$result, $code), $code);
    }

    /**
     * @param \WebEd\Base\Http\Requests\Request $request
     * @return mixed
     */
    protected function parseInputData(\WebEd\Base\Http\Requests\Request $request)
    {
        $data = $request->get('category', []);
        if (!$data['slug']) {
            $data['slug'] = str_slug($data['title']);
        }
        return $data;
    }
}
