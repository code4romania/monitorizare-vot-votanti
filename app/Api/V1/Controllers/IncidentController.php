<?php

namespace App\Api\V1\Controllers;

use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Controllers\Controller;
use App\Api\V1\Transformers\IncidentTransformer;

use App\Incident;
use App\User;

use App\County;
use Intervention\Image\ImageManager;
// use WebSocket;

class IncidentController extends Controller
{
    use Helpers;

    protected $incidentTransformer;

    function __construct(IncidentTransformer $incidentTransformer)
    {
        $this->incidentTransformer = $incidentTransformer;
    }

    /**
     * @SWG\Get(
     *     path="/api/incidents",
     *     summary="Fetch incidents",
     *     tags={"Incidents"},
     *     description="Fetch tags filtered by state.",
     *     operationId="findPetsByTags",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="tags",
     *         in="query",
     *         description="Tags to filter by",
     *         required=false,
     *         type="array",
     *         @SWG\Items(type="string"),
     *         collectionFormat="multi"
     *     ),
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
        $limit = Input::get('limit') ?: 20;
        $limit = min($limit, 200);
        $status = Input::get('status');
        $county = Input::get('county');
        $incidentType = Input::get('type');
        $map = Input::get('map');

        $query = Incident::with('type')
            ->with('county')
            ->with('precinct')
            ->orderBy('created_at', 'desc');
		if($map) {
			$countyAbroad = County::where('code', 'DI')->first();
			$countyAbroadId = $countyAbroad->id;
			if($map == 'country') {
				$query->where("county_id", '!=', $countyAbroadId);
			}
			if($map == 'abroad') {
				$query->where("county_id", $countyAbroadId);
			}
		}

        if ($status) {
            $statuses = explode(',', $status);
            $query->whereIn('status', $statuses);
        } else {
            $query->whereIn('status', array('Approved'));
        }

        if ($county) {
            $ids = explode(',', $county);
            $query->whereIn('county_id', $ids);
        }

        if($incidentType) {
        	$incidentTypes = explode(',', $incidentType);
        	$query->whereIn('incident_type_id', $incidentTypes);
        }

        $incidents = $query->paginate($limit);

        return response()->json([
            'data' => $this->incidentTransformer->transformCollection($incidents->all()),
            'paginator' => $this->getPaginator($incidents)
        ], 200);
    }

    /**
     * @SWG\Get(
     *     path="/api/incidents/{incidentId}",
     *     summary="Fetch incident by id",
     *     tags={"Incidents"},
     *     description="Fetch incident by id.",
     *     operationId="findIncidentById",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="incidentID",
     *         in="path",
     *         description="Incident ID",
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
    public function show($incidentId)
    {
        $incident = Incident::with('type')->find($incidentId);

        if (!$incident)
            return $this->notFoundResponse();

        return response()->json([
            'data' => $this->incidentTransformer->transform($incident)
        ], 200);
    }

    /**
     * @SWG\Post(path="/api/incidents",
     *   tags={"Incidents"},
     *   summary="Create an incident",
     *   description="",
     *   operationId="createIncident",
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="body",
     *     description="order placed for purchasing the pet",
     *     required=false,
     *     @SWG\Schema(ref="#/definitions/Incident")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="successful operation",
     *     @SWG\Schema(ref="#/definitions/Incident")
     *   ),
     *   @SWG\Response(response=400,  description="Invalid Incident")
     * )
     */
    public function store(Request $request)
    {
        $rules = [
            'first_name' => 'required|max:200',
            'last_name' => 'required|max:200',
            'incident_type_id' => 'required',
            'description' => 'required',
            'county_id' => 'required',
            'city_id' => 'required',
            'precinct_id' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not create new incident.', $validator->errors());
        }

        $catpcha_token = $request->input('recaptchaResponse');
        if (!$this->verifyCaptcha($catpcha_token)) {
            throw new StoreResourceFailedException('Invalid captcha.');
        }

        $file = Input::file('image');
        if($file != null) {
            $extension = strtolower(Input::file('image')->getClientOriginalExtension());
            if($extension != "jpg" && $extension != "png") {
                throw new \Exception("Image format is not supported!");
            }
        }

        $incident = new Incident($request->all());
        $incident->status = 'Pending';

        if($incident->save()) {
            // try {
            // 	$client = new Client(config('app.wsServerAddr'));
            // 	$client->send(json_encode(array("data" => Reports::countiesWithIncidents())));
            // } catch (WebSocket\Exception $e) {

            // }

        	if($file != null) {
            	try {
                	$imagePath = base_path().'/public/assets/media/images/';
                	$imageName = 'image_incident_'.$c=$incident->id.'.'.$extension;
                	$file->move($imagePath, $imageName);
                	$manager = new ImageManager();
                	$image = $manager->make($imagePath.$imageName);
                	$image->resize(1024, 1024*$image->height()/$image->width());
                	$image->save($imagePath.$imageName);
                	$incident->image_url = 'http://'.substr($request->root(), 7).'/assets/media/images/'.$imageName;
                	$incident->save();
            	} catch(Exception $e) {
            	    throw new Exception("Image could not be saved!");
            	}
        	}
            return $this->response->created();
        }
        else
            return $this->response->error('could_not_create_incident', 500);
    }

    public function approve($incidentId)
    {
        $incident = Incident::find($incidentId);
        if (!$incident)
            return $this->notFoundResponse();

        $incident->status = 'Approved';
        if ($incident->save())
            return $this->response->noContent();
        else
            return $this->response->error('could_not_update_incident', 500);
    }

    public function reject($incidentId)
    {
        $incident = Incident::find($incidentId);
        if (!$incident)
            return $this->notFoundResponse();

        $incident->status = 'Rejected';
        if ($incident->save())
            return $this->response->noContent();
        else
            return $this->response->error('could_not_update_incident', 500);
    }

    public function destroy($incidentId)
    {
        $incident = Incident::find($incidentId);

        if (!$incident)
            return $this->notFoundResponse();

        if ($incident->delete())
            return $this->response->noContent();
        else
            return $this->response->error('could_not_delete_incident', 500);
    }

    private function verifyCaptcha($token) {

        if (!$token) {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => env('RECAPTCHA_SECRET', '6LdLYg4UAAAAACq_l5nQTbwHX0BGGY1JhJw0fduW'),
            'response' => $token
        );

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result = json_decode($result);

        return $result->success;
    }
}
