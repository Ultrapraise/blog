<?php namespace WebEd\Plugins\Blog\Http\DataTables;

use WebEd\Base\Core\Http\DataTables\AbstractDataTables;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;

class PostsListDataTable extends AbstractDataTables
{
    protected $repository;

    public function __construct(PostRepositoryContract $repository)
    {
        $this->repository = $repository;

        $this->repository->select('id', 'created_at', 'title', 'page_template', 'status', 'order');

        parent::__construct();
    }

    /**
     * @return string
     */
    public function run()
    {
        $this->setAjaxUrl(route('admin::blog.posts.index.post'), 'POST');

        $this
            ->addHeading('title', 'Title', '25%')
            ->addHeading('page_template', 'Page template', '15%')
            ->addHeading('status', 'Status', '10%')
            ->addHeading('sort_order', 'Sort order', '10%')
            ->addHeading('created_at', 'Created at', '10%')
            ->addHeading('actions', 'Actions', '20%');

        $this
            ->addFilter(1, form()->text('title', '', [
                'class' => 'form-control form-filter input-sm',
                'placeholder' => 'Search...'
            ]))
            ->addFilter(2, form()->text('page_template', '', [
                'class' => 'form-control form-filter input-sm',
                'placeholder' => 'Search...'
            ]))
            ->addFilter(3, form()->select('status', [
                '' => '',
                'activated' => 'Activated',
                'disabled' => 'Disabled',
            ], null, ['class' => 'form-control form-filter input-sm']));

        $this->withGroupActions([
            '' => 'Select' . '...',
            'deleted' => 'Deleted',
            'activated' => 'Activated',
            'disabled' => 'Disabled',
        ]);

        $this->setColumns([
            ['data' => 'id', 'name' => 'id', 'searchable' => false, 'orderable' => false],
            ['data' => 'title', 'name' => 'title'],
            ['data' => 'page_template', 'name' => 'page_template'],
            ['data' => 'status', 'name' => 'status'],
            ['data' => 'order', 'name' => 'order', 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'searchable' => false],
            ['data' => 'actions', 'name' => 'actions', 'searchable' => false, 'orderable' => false],
        ]);

        return $this->view();
    }

    /**
     * @return $this
     */
    protected function fetch()
    {
        $this->fetch = datatable()->of($this->repository)
            ->editColumn('id', function ($item) {
                return form()->customCheckbox([['id[]', $item->id]]);
            })
            ->editColumn('status', function ($item) {
                return html()->label($item->status, $item->status);
            })
            ->addColumn('actions', function ($item) {
                /*Edit link*/
                $activeLink = route('admin::blog.posts.update-status.post', ['id' => $item->id, 'status' => 'activated']);
                $disableLink = route('admin::blog.posts.update-status.post', ['id' => $item->id, 'status' => 'disabled']);
                $deleteLink = route('admin::blog.posts.delete.delete', ['id' => $item->id]);

                /*Buttons*/
                $editBtn = link_to(route('admin::blog.posts.edit.get', ['id' => $item->id]), 'Edit', ['class' => 'btn btn-sm btn-outline green']);
                $activeBtn = ($item->status != 'activated') ? form()->button('Active', [
                    'title' => 'Active this item',
                    'data-ajax' => $activeLink,
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline blue btn-sm ajax-link',
                    'type' => 'button',
                ]) : '';
                $disableBtn = ($item->status != 'disabled') ? form()->button('Disable', [
                    'title' => 'Disable this item',
                    'data-ajax' => $disableLink,
                    'data-method' => 'POST',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline yellow-lemon btn-sm ajax-link',
                    'type' => 'button',
                ]) : '';
                $deleteBtn = form()->button('Delete', [
                    'title' => 'Delete this item',
                    'data-ajax' => $deleteLink,
                    'data-method' => 'DELETE',
                    'data-toggle' => 'confirmation',
                    'class' => 'btn btn-outline red-sunglo btn-sm ajax-link',
                    'type' => 'button',
                ]);

                return $editBtn . $activeBtn . $disableBtn . $deleteBtn;
            });

        return $this;
    }
}