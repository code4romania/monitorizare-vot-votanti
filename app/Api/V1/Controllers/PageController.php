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
	 * @SWG\Get(
	 *     path="/api/page",
	 *     summary="Fetch pages",
	 *     tags={"Pages"},
	 *     description="Fetch pages",
	 *     operationId="",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="limit",
	 *     type="integer",
	 *     description="pagination limit",
	 *     required=false
	 *   ),
 	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="token",
	 *     type="string",
	 *     description="authentication token",
	 *     required=false
	 *   ),
	 *     @SWG\Response(response=200, description="successful operation"),
	 *     @SWG\Response(response="401", description="unauthorized")
	 * )
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
	 * @SWG\Post(
	 *     path="/api/page",
	 *     summary="Store page",
	 *     tags={"Pages"},
	 *     description="Store page",
	 *     operationId="storePage",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="title",
	 *     type="string",
	 *     description="Page title",
	 *     required=true
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="status",
	 *     type="string",
	 *     description="Page status (Active|Inactive)",
	 *     required=true
	 *   ),
 	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="description",
	 *     type="string",
	 *     description="Page description",
	 *     required=true
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="limit",
	 *     type="integer",
	 *     description="pagination limit",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="token",
	 *     type="string",
	 *     description="authentication token",
	 *     required=false
	 *   ),
	 *     @SWG\Response(response=200, description="successful operation"),
	 *     @SWG\Response(response="401", description="unauthorized")
	 * )
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|max:200',
            'status' => 'in:Active,Inactive',
            'description' => 'required|max:500',
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
	 * @SWG\Put(
	 *     path="/api/page/{page}",
	 *     summary="Store page",
	 *     tags={"Pages"},
	 *     description="Store page",
	 *     operationId="storePage",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *   @SWG\Parameter(
	 *     in="path",
	 *     name="page",
	 *     type="number",
	 *     description="page id to update",
	 *     required=true,
	 *     collectionFormat="multi"
	 *   ),
 	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="title",
	 *     type="string",
	 *     description="Page title",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="status",
	 *     type="string",
	 *     description="Page status (Active|Inactive)",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="description",
	 *     type="string",
	 *     description="Page description",
	 *     required=false
	 *   ),

	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="limit",
	 *     type="integer",
	 *     description="pagination limit",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="token",
	 *     type="string",
	 *     description="authentication token",
	 *     required=false
	 *   ),
	 *     @SWG\Response(response=200, description="successful operation"),
	 *     @SWG\Response(response="401", description="unauthorized"),
	 *     @SWG\Response(response="422", description="store resource failed")
	 * )
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
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
            'description' => 'max:500',
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
	 * @SWG\Delete(
	 *     path="/api/page/{page}",
	 *     summary="Delete page",
	 *     tags={"Pages"},
	 *     description="Delete page",
	 *     operationId="",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *   @SWG\Parameter(
	 *     in="path",
	 *     name="page",
	 *     type="number",
	 *     description="page id to update",
	 *     required=true,
	 *     collectionFormat="multi"
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="limit",
	 *     type="integer",
	 *     description="pagination limit",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="token",
	 *     type="string",
	 *     description="authentication token",
	 *     required=false
	 *   ),
	 *     @SWG\Response(response=201, description="successful operation"),
	 *     @SWG\Response(response="401", description="unauthorized"),
	 *     @SWG\Response(response="404", description="Record does not exist"),
	 *     @SWG\Response(response="500", description="could_not_delete_page")
	 * )
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
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
	 * @SWG\Post(
	 *     path="/api/page/{page}/approve",
	 *     summary="Approve page",
	 *     tags={"Pages"},
	 *     description="Approve page",
	 *     operationId="",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *   @SWG\Parameter(
	 *     in="path",
	 *     name="page",
	 *     type="number",
	 *     description="page id to update",
	 *     required=true,
	 *     collectionFormat="multi"
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="limit",
	 *     type="integer",
	 *     description="pagination limit",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="token",
	 *     type="string",
	 *     description="authentication token",
	 *     required=false
	 *   ),
	 *     @SWG\Response(response=204, description="successful operation"),
	 *     @SWG\Response(response="401", description="unauthorized"),
	 *     @SWG\Response(response="404", description="Record does not exist"),
	 *     @SWG\Response(response="500", description="could_not_delete_page")
	 * )
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
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
	 * @SWG\Post(
	 *     path="/api/page/{page}/reject",
	 *     summary="Reject page",
	 *     tags={"Pages"},
	 *     description="Reject page",
	 *     operationId="",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *   @SWG\Parameter(
	 *     in="path",
	 *     name="page",
	 *     type="number",
	 *     description="page id to update",
	 *     required=true,
	 *     collectionFormat="multi"
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="limit",
	 *     type="integer",
	 *     description="pagination limit",
	 *     required=false
	 *   ),
	 *   @SWG\Parameter(
	 *     in="query",
	 *     name="token",
	 *     type="string",
	 *     description="authentication token",
	 *     required=false
	 *   ),
	 *     @SWG\Response(response=204, description="successful operation"),
	 *     @SWG\Response(response="401", description="unauthorized"),
	 *     @SWG\Response(response="404", description="Record does not exist"),
	 *     @SWG\Response(response="500", description="could_not_delete_page")
	 * )
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
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
