<?php namespace WebEd\Plugins\Blog\Http\Controllers;

use WebEd\Base\Core\Http\Controllers\BaseAdminController;
use WebEd\Plugins\Blog\Http\DataTables\BlogTagsListDataTable;
use WebEd\Plugins\Blog\Http\Requests\CreateBlogTagRequest;
use WebEd\Plugins\Blog\Http\Requests\UpdateBlogTagRequest;
use WebEd\Plugins\Blog\Repositories\BlogTagRepository;
use WebEd\Plugins\Blog\Repositories\Contracts\BlogTagRepositoryContract;
use Yajra\Datatables\Engines\BaseEngine;

class BlogTagController extends BaseAdminController
{
    protected $module = 'webed-blog';

    /**
     * @var BlogTagRepository
     */
    protected $repository;

    /**
     * @param BlogTagRepository $repository
     */
    public function __construct(BlogTagRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;

        $this->breadcrumbs->addLink('Blog')
            ->addLink('Tags', route('admin::blog.tags.index.get'));

        $this->getDashboardMenu('webed-blog-tags');
    }

    public function getIndex(BlogTagsListDataTable $blogTagsListDataTable)
    {
        $this->setPageTitle('Tags', 'All available blog tags');

        $this->dis['dataTable'] = $blogTagsListDataTable->run();

        return do_filter('blog.tags.index.get', $this)->viewAdmin('tags.index');
    }

    /**
     * Get data for DataTable
     * @param BlogTagsListDataTable|BaseEngine $blogTagsListDataTable
     * @return \Illuminate\Http\JsonResponse
     */
    public function postListing(BlogTagsListDataTable $blogTagsListDataTable)
    {
        $data = $blogTagsListDataTable->with($this->groupAction());

        return do_filter('datatables.blog.tags.index.post', $data, $this)
            ->make(true);
    }

    /**
     * Handle group actions
     * @return array
     */
    private function groupAction()
    {
        $data = [];
        if ($this->request->get('customActionType', null) === 'group_action') {
            if (!$this->userRepository->hasPermission($this->loggedInUser, ['edit-tags'])) {
                return [
                    'customActionMessage' => 'You do not have permission',
                    'customActionStatus' => 'danger',
                ];
            }

            $ids = (array)$this->request->get('id', []);
            $actionValue = $this->request->get('customActionValue');

            switch ($actionValue) {
                case 'deleted':
                    if (!$this->userRepository->hasPermission($this->loggedInUser, ['delete-tags'])) {
                        return [
                            'customActionMessage' => 'You do not have permission',
                            'customActionStatus' => 'danger',
                        ];
                    }
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
        $result = $this->repository->editWithValidate($id, $data);
        return response()->json($result, $result['response_code']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getCreate()
    {
        $this->setPageTitle('Create tag');
        $this->breadcrumbs->addLink('Create tag');

        $this->dis['object'] = $this->repository->getModel();

        $oldInputs = old();
        if ($oldInputs) {
            foreach ($oldInputs as $key => $row) {
                $this->dis['object']->$key = $row;
            }
        }

        return do_filter('blog.tags.create.get', $this)->viewAdmin('tags.create');
    }

    public function postCreate(CreateBlogTagRequest $request)
    {
        $data = $this->parseInputData();

        $data['created_by'] = $this->loggedInUser->id;

        $result = $this->repository->createTag($data);

        do_action('blog.tags.after-create.post', null, $result, $this);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($result['error']) {
            return redirect()->back()->withInput();
        }

        if ($request->has('_continue_edit')) {
            if (!$result['error']) {
                return redirect()->to(route('admin::blog.tags.edit.get', ['id' => $result['data']->id]));
            }
        }

        return redirect()->to(route('admin::blog.tags.index.get'));
    }

    public function getEdit($id)
    {
        $item = $this->repository->find($id);
        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This tag not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter('blog.tags.before-edit.get', $item);

        $this->assets
            ->addJavascripts([
                'jquery-ckeditor'
            ]);

        $this->setPageTitle('Edit tag', $item->title);
        $this->breadcrumbs->addLink('Edit tag');

        $this->dis['object'] = $item;

        return do_filter('blog.tags.edit.get', $this, $id)->viewAdmin('tags.edit');
    }

    public function postEdit(UpdateBlogTagRequest $request, $id)
    {
        $item = $this->repository->find($id);
        if (!$item) {
            $this->flashMessagesHelper
                ->addMessages('This tag not exists', 'danger')
                ->showMessagesOnSession();

            return redirect()->back();
        }

        $item = do_filter('blog.tags.before-edit.post', $item);

        $data = $this->parseInputData();

        $result = $this->repository->updateTag($item, $data);

        do_action('blog.tags.after-edit.post', $id, $result, $this);

        $msgType = $result['error'] ? 'danger' : 'success';

        $this->flashMessagesHelper
            ->addMessages($result['messages'], $msgType)
            ->showMessagesOnSession();

        if ($request->has('_continue_edit')) {
            return redirect()->back();
        }

        return redirect()->to(route('admin::blog.tags.index.get'));
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDelete($id)
    {
        $id = do_filter('blog.tags.before-delete.delete', $id);

        $result = $this->repository->delete($id);

        do_action('blog.tags.after-delete.delete', $id, $result, $this);

        return response()->json($result, $result['response_code']);
    }

    protected function parseInputData()
    {
        $data = [
            'status' => $this->request->get('status'),
            'title' => $this->request->get('title'),
            'slug' => ($this->request->get('slug') ? str_slug($this->request->get('slug')) : str_slug($this->request->get('title'))),
            'description' => $this->request->get('description'),
            'order' => $this->request->get('order'),
            'updated_by' => $this->loggedInUser->id,
        ];
        return $data;
    }
}
