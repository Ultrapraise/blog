<?php namespace WebEd\Plugins\Blog\Http\Controllers\Front\Api;

use Illuminate\Http\Request;
use WebEd\Base\Core\Http\Controllers\BaseApiController;
use WebEd\Plugins\Blog\Repositories\Contracts\PostRepositoryContract;
use WebEd\Plugins\Blog\Repositories\PostRepository;

class PostController extends BaseApiController
{
    protected $module = 'webed-blog';

    /**
     * @var PostRepository
     */
    protected $repository;

    public function __construct(PostRepositoryContract $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = $this->repository->paginate(10)
            ->setCurrentPaged($this->request->get('page', 1))
            ->with('author')
            ->with('categories')
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
