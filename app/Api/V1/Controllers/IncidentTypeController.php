<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Transformers\IncidentTypeTransformer;
use App\Services\IncidentTypeService;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response;
use Illuminate\Database\QueryException;
use JWTAuth;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use App\IncidentType;

class IncidentTypeController extends Controller
{
    use Helpers;

    /**
     * @var IncidentTypeTransformer
     */
    private $incidentTypeTransformer;

    /**
     * @param IncidentTypeTransformer $incidentTypeTransformer
     */
    public function __construct(IncidentTypeTransformer $incidentTypeTransformer)
    {
        $this->incidentTypeTransformer = $incidentTypeTransformer;
    }

    /**
	 * @SWG\Get(
	 *     path="/api/incidents/types",
	 *     summary="Fetch incidents types",
	 *     tags={"IncidentTypes"},
	 *     description="Fetch incident types.",
	 *     operationId="findIncidentTypes",
	 *     consumes={"application/json"},
	 *     produces={"application/json"},
	 *     @SWG\Response(
	 *         response=200,
	 *         description="successful operation",
	 *         @SWG\Schema(
	 *             type="array",
	 *             @SWG\Items(ref="#/definitions/Incident")
	 *         ),
	 *     ),
	 *     @SWG\Response(
	 *         response="400",
	 *         description="Invalid tag value",
	 *     ),
	 *     security={
	 *         {
	 *             "monitorizare_auth": {"read:incidents"}
	 *         }
	 *     }
	 * )
	 */
    public function index()
    {
    	return response()->json([
            'data' => IncidentType::get()
        ], 200);
    }

    /**
     * @SWG\POST(
     *     path="/api/incidents/types",
     *     summary="Create a new incident type",
     *     tags={"IncidentTypes"},
     *     description="Create a new incident type.",
     *     operationId="storeIncidentType",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="label",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="string",
     *                  enum="['Active', 'Inactive']"
     *              )
     *          ),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="successful operation",
     *         @SWG\Schema(
     *             @SWG\Items(ref="#/definitions/IncidentType")
     *         )
     *     ),
     *     @SWG\Response(
     *         response="422",
     *         description="Fields are missing or code is not unique.",
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="internal server error"
     *     )
     * )
     * @param Request $request
     * @param IncidentTypeService $incidentTypeService
     * @return \Dingo\Api\Http\Response
     */
    public function store(Request $request, IncidentTypeService $incidentTypeService)
    {
        try {
            /** @var IncidentType $incidentType */
            $incidentType = $incidentTypeService->create($request->all());
        } catch (StoreResourceFailedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->response->error(
                'could_not_create_incident_type',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->response->created(
            null,
            ['data' => $this->incidentTypeTransformer->transform($incidentType)]
        );
    }

    /**
     * @SWG\Get(
     *     path="/api/incidents/{incidentTypeId}",
     *     summary="Fetch an incident type by id",
     *     tags={"IncidentTypes"},
     *     description="Fetch an incident type by id.",
     *     operationId="findIncidentTypeById",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="incidentTypeId",
     *         in="path",
     *         description="Incident type id",
     *         required=true,
     *         type="number",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/IncidentType")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Incident type not found",
     *     )
     * )
     * @param $incidentTypeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($incidentTypeId)
    {
        $incidentType = IncidentType::query()->find($incidentTypeId);

        if (!$incidentType)
            return $this->notFoundResponse();

        return response()->json([
            'data' => $this->incidentTypeTransformer->transform($incidentType)
        ]);
    }

    /**
     * @SWG\PUT(
     *     path="/api/incidents/types/{incidentTypeId}",
     *     summary="Update a new incident type",
     *     tags={"IncidentTypes"},
     *     description="Update a new incident type.",
     *     operationId="updateIncidentType",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *          in="body",
     *          name="body",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(
     *                  property="name",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="label",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="code",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="status",
     *                  type="string",
     *                  enum="['Active', 'Inactive']"
     *              )
     *          ),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *         @SWG\Schema(ref="#/definitions/IncidentType")
     *     ),
     *     @SWG\Response(
     *         response="422",
     *         description="Fields are missing or code is not unique.",
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="The incident type could not be found.",
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="internal server error"
     *     )
     * )
     *
     * @param Request $request
     * @param $incidentTypeId
     * @param IncidentTypeService $incidentTypeService
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $incidentTypeId, IncidentTypeService $incidentTypeService)
    {
        $incidentType = IncidentType::find($incidentTypeId);
        if (!$incidentType)
            return $this->notFoundResponse();

        try {
            $incidentType = $incidentTypeService->update($incidentType, $request->all());
        } catch (StoreResourceFailedException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            $this->response->error(
                'could_not_update_incident_type',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json([
            'data' => $this->incidentTypeTransformer->transform($incidentType)
        ]);
    }

    /**
     * @SWG\Delete(
     *     path="/api/incidents/{incidentTypeId}",
     *     summary="Delete an incident type by id",
     *     tags={"IncidentTypes"},
     *     description="Delete an incident type by id.",
     *     operationId="deleteIncidentTypeById",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="incidentTypeId",
     *         in="path",
     *         description="Incident type id",
     *         required=true,
     *         type="number",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi"
     *     ),
     *     @SWG\Response(
     *         response=204,
     *         description="successful operation",
     *         @SWG\Schema(
     *             type="array",
     *             @SWG\Items(ref="#/definitions/IncidentType")
     *         ),
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Incident type not found",
     *     ),
     *     @SWG\Response(
     *         response="409",
     *         description="Incident type cannot be deleted because there are some incidents with this type.",
     *     )
     * )
     *
     * @param $incidentTypeId
     * @return Response|\Illuminate\Http\JsonResponse
     */
    public function destroy($incidentTypeId)
    {
        $incidentType = IncidentType::find($incidentTypeId);
        if (!$incidentType)
            return $this->notFoundResponse();

        try {
            if (!$incidentType->delete()) {
                $this->response->error(
                    'could_not_delete_incident_type',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        } catch (\Exception $exception) {
            if ($exception instanceof QueryException) {
                $this->response->error(
                    'incident_type_still_in_use_by_incidents',
                    Response::HTTP_CONFLICT
                );
            }

            $this->response->error(
                'could_not_delete_incident_type',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->response->noContent();
    }
}
