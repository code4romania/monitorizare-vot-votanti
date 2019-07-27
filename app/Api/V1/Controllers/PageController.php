<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Transformers\PageTransformer;
use App\Http\Controllers\Controller;
use App\Page;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    use Helpers;

    /**
     * @var PageTransformer
     */
    private $pageTransformer;

    /**
     * @param PageTransformer $pageTransformer
     */
    public function __construct(PageTransformer $pageTransformer)
    {
        $this->pageTransformer = $pageTransformer;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $pages = Page::with('user')->paginate($limit);
        return response()->json([
            'data' => $this->pageTransformer->transformCollection($pages->all()),
            'paginator' => $this->getPaginator($pages)
        ], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|max:200',
            'status' => 'in:Active,Inactive',
            'content' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not create new page.', $validator->errors());
        }
        $request->merge([
            'status' => $request->get('status', 'Inactive'),
            'user_id' => $this->user()->id
        ]);

        try {
            $page = new Page($request->all());
            $page->save();
            return response()->json([
                'data' => $this->pageTransformer->transform($page)
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return $this->response->error('could_not_create_page', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function update(Request $request, int $id)
    {
        $rules = [
            'title' => 'max:200',
            'status' => 'in:Active,Inactive',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not edit page.', $validator->errors());
        }

        try {
            $page = Page::findOrFail($id);
            $page->update($request->all());
            return response()->json([
                'data' => $this->pageTransformer->transform($page)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_edit_page', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function destroy(int $id)
    {
        try {
            Page::findOrFail($id)->delete();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_delete_page', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function approve(int $id)
    {
        try {
            $page = Page::findOrFail($id);
            $page->setAttribute('status', Page::ACTIVE);
            $page->save();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_approve_page', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function reject(int $id)
    {
        try {
            $page = Page::findOrFail($id);
            $page->setAttribute('status', Page::INACTIVE);
            $page->save();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            return $this->response->error('could_not_reject_page', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
