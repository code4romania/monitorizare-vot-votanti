<?php

namespace App\Api\V1\Controllers;

use App\City;
use App\Helpers\PrecinctImporter;
use App\Precinct;
use App\Api\V1\Transformers\PrecinctTransformer;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SplFileObject;

class PrecinctController extends Controller
{
    use Helpers;
    private $precinctTransformer;

    private $validationRules = [
        'city_id' => 'required|integer|exists:cities,id',
        'precinct_no' => 'required|integer',
        'siruta_code' => 'required|integer',
        'circ_no' => 'required|integer',
        'headquarter' => 'required|string|max:200',
        'address' => 'required|string|max:200'
    ];

    const IMPORT_KEY_NAME = "import_file";

    /**
     * PrecinctController constructor.
     */
    public function __construct()
    {
        $this->precinctTransformer = new PrecinctTransformer();
    }


    public function list()
    {
        $precincts = Precinct::get();

        return response()->json(['data' => $this->precinctTransformer->transformCollection($precincts->all())]);
    }

    public function listPerCity($cityId)
    {
        $precincts = Precinct::where('city_id', intval($cityId))->get();

        return response()->json(['data' => $this->precinctTransformer->transformCollection($precincts->all())]);
    }


    /**
     * @SWG\Post(path="/api/precinct",
     *   tags={"Precincts"},
     *   summary="Create a precinct",
     *   description="",
     *   operationId="createPrecinct",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="create a new precinct",
     *     required=false,
     *     @SWG\Schema(ref="#/definitions/Precinct")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(ref="#/definitions/Precinct")
     *   ),
     *   @SWG\Response(response=400,  description="Invalid Precinct"),
     *   @SWG\Response(response=409,  description="Duplicate Precinct")
     * )
     */
    public function store(Request $request)
    {
        $data = $request->only(array_keys($this->validationRules));
        $validator = Validator::make($data, $this->validationRules);
        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not create new precinct.', $validator->errors());
        }
        try {
            $city = City::findOrFail($data['city_id']);
            $precinct = Precinct::where(
                [
                    'precinct_no' => $data['precinct_no'],
                    'city_id' => $data['city_id']
                ]
            )->first();
            if ($precinct != null){
                return response()->json([
                    'error' => ['message' => 'A precinct with that number already exists in that city']
                ], 409);
            }
            $data['county_id'] = $city->county_id;
            $precinct = new Precinct($data);
            $precinct->save();
            return response()->json([
                'data' => $precinct->toArray()
            ], Response::HTTP_CREATED);

        } catch (\Exception $ex){
            $this->response->error('could_not_create_precinct', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\PUT(path="/api/precinct/",
     *   tags={"Precincts"},
     *   summary="Update a precinct",
     *   description="",
     *   operationId="updatePrecinct",
     *   produces={"application/json"},
     *     @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="precinct id",
     *     required=true
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="update a precinct",
     *     required=false,
     *     @SWG\Schema(ref="#/definitions/Precinct")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(ref="#/definitions/Precinct")
     *   ),
     *   @SWG\Response(response=400,  description="Invalid Precinct"),
     *   @SWG\Response(response=404,  description="Invalid Precinct"),
     *   @SWG\Response(response=409,  description="Duplicate Precinct")
     * )
     */
    public function update(Request $request, int $id)
    {
        $data = $request->only(array_keys($this->validationRules));

        $validator = Validator::make($data, $this->validationRules);
        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not update precinct.', $validator->errors());
        }

        try {
            $precinct = Precinct::findOrFail($id);
            $newPrecinct = Precinct::where(
                [
                    'precinct_no' => $data['precinct_no'],
                    'city_id' => $data['city_id']
                ]
            )->first();
            if ($newPrecinct != null && $newPrecinct->id != $precinct->id){
                return response()->json([
                    'error' => ['message' => 'A precinct with that number already exists in that city']
                ], 409);
            }
            $precinct->update($data);
            return response()->json([
                'data' => $precinct->toArray()
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $modelNotFoundException) {
                return $this->notFoundResponse();
        } catch (\Exception $ex){
            $this->response->error('could_not_update_precinct', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * @SWG\DELETE(path="/api/precinct/",
     *   tags={"Precincts"},
     *   summary="Delete a precinct",
     *   description="",
     *   operationId="deletePrecinct",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="precinct id",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=204,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=404,  description="Invalid Precinct")
     * )
     */
    public function destroy(int $id)
    {
        try {
            Precinct::findOrFail($id)->delete();
            return $this->response->noContent();
        } catch (ModelNotFoundException $modelNotFoundException) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            $this->response->error('could_not_delete_precinct', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @SWG\GET(path="/api/precinct/",
     *   tags={"Precincts"},
     *   summary="Show a precinct",
     *   description="",
     *   operationId="showAPrecinct",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="id",
     *     type="integer",
     *     description="precinct id",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     *   @SWG\Response(response=404,  description="Invalid Precinct")
     * )
     */
    public function show($id)
    {
        $precinct = Precinct::find($id);
        if (!$precinct) {
            return $this->notFoundResponse();
        }
        return response()->json(['data' => $precinct]);
    }

    /**
     * @SWG\GET(path="/api/precinct",
     *   tags={"Precincts"},
     *   summary="Show all precincts",
     *   description="",
     *   operationId="showAllPrecincts",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="query",
     *     name="limit",
     *     type="integer",
     *     description="pagination limit",
     *     required=false
     *   ),
     *     @SWG\Parameter(
     *     in="query",
     *     name="page",
     *     type="integer",
     *     description="current page",
     *     required=false
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     * )
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $precincts = Precinct::paginate($limit);
        return response()->json([
            'data' => $precincts->all(),
            'paginator' => $this->getPaginator($precincts)
        ], Response::HTTP_OK);
    }

    /**
     * @SWG\POST(path="/api/import/precincts",
     *   tags={"Precincts"},
     *   summary="Import precincts",
     *   description="Allows the import of either csv or xlsx imports",
     *   operationId="importPrecincts",
     *   produces={"application/json"},
     *  @SWG\Parameter(
     *     in="formData",
     *     name="import_file",
     *     type="file",
     *     description="CSV or xls file containing precincts",
     *     required=true,
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation"
     *   ),
     * )
     */
    public function import(Request $request)
    {
        $acceptedMimeTypes = [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/octet-stream',
            'application/json'
        ];

        if (!$request->hasFile(self::IMPORT_KEY_NAME) || !in_array( $request->file(self::IMPORT_KEY_NAME)->getClientMimeType(),$acceptedMimeTypes)) {
            throw new StoreResourceFailedException('Could not execute import.', ["Import file has the wrong format"]);
        }

        set_time_limit(180);
        $requestFile = $request->file(self::IMPORT_KEY_NAME);

        $storedFile = $requestFile->move("precinct_imports", $requestFile->getClientOriginalName());
        $file = new SplFileObject($storedFile->getRealPath());
        $importer = new PrecinctImporter();
        try {
            $importer->importFromFile($file, true);
        } catch (\Exception $ex) {
            $this->response->error($ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
