<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

use App\Api\V1\Transformers\PrecinctTransformerIncidentsPerPrecinct;
use App\Api\V1\Transformers\CountyTransformerIncidentsPerCountyOpening;
use App\Api\V1\Transformers\CountyTransformerIncidentsPerCountyCounting;
use App\Api\V1\Transformers\PrecinctTransformerIncidentsPerPrecinctOpening;
use App\Api\V1\Transformers\PrecinctTransformerIncidentsTypePerPrecinct;
use App\Api\V1\Transformers\IncidentTypeTransformerReport;

use App\County;
use App\Incident;
use App\User;
use App\Precinct;
use App\IncidentType;

class ReportingController extends Controller
{
	use Helpers;

	/**
	 * Get incidents total.
	 */
	public function incidentsTotal()
	{
		return response()->json(['data' => ['totalIncidents' => Reports::totalIncidents()]]);
	}

	/**
	 * Get incidents number per county.
	 */
	public function incidentsPerCounty()
	{
		return response()->json(['data' => Reports::countiesWithIncidents()]);
	}

	/**
	 * Get the number of incidents per precinct.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function incidentsPerPrecinct() {
		$limit = Input::get('limit') ?: 20;
		$limit = min($limit, 200);
		
		$precincts = Precinct::select(DB::raw('precincts.*, count(*) as `aggregate`'))
		->join('incidents', 'precincts.id', '=', 'incidents.precinct_id')
		->groupBy('precinct_id')
		->orderBy('aggregate', 'desc')
		->paginate($limit);
		
		$precinctTransformerIPP = new PrecinctTransformerIncidentsPerPrecinct();
		
		return response()->json([
				'data' => $precinctTransformerIPP->transformCollection($precincts->all()),
				'paginator' => $this->getPaginator($precincts)
		], 200);
	}
	
	/**
	 * Get the number of incidents for opening per county.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function incidentsOpeningPerCounty() {
		$counties = County::get();
		$countyTransformerIPCO = new CountyTransformerIncidentsPerCountyOpening();
		
		return response()->json(['data' => $countyTransformerIPCO->transformCollection($counties->all())]);
	}
	
	/**
	 * Get the number of incidents for opening per precinct.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function incidentsOpeningPerPrecinct() {
		return $this->getIncidentsByType('OPN');
	}
	
	/**
	 * Get the number of incidents for counting per county.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function incidentsCountingPerCounty() {
		$counties = County::get();
		$countyTransformerIPCC = new CountyTransformerIncidentsPerCountyCounting();
	
		return response()->json(['data' => $countyTransformerIPCC->transformCollection($counties->all())]);
	}
	
	/**
	 * Get the number of incidents for counting per precinct.
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function incidentsCountingPerPrecinct() {
		return $this->getIncidentsByType('CNT');
	}
	
	private function getIncidentsByType($type) {
		$limit = Input::get('limit') ?: 20;
		$limit = min($limit, 200);
		
		$precincts = Precinct::select(DB::raw('precincts.*, count(*) as `aggregate`'))
		->where('incidents.incident_type_id', $this->getIncidentTypeId($type))
		->join('incidents', 'precincts.id', '=', 'incidents.precinct_id')
		->groupBy('precinct_id')
		->orderBy('aggregate', 'desc')
		->paginate($limit);
		
		$precinctTransformerITPP = new PrecinctTransformerIncidentsTypePerPrecinct();
		
		return response()->json([
				'data' => $precinctTransformerITPP->transformCollection($precincts->all()),
				'paginator' => $this->getPaginator($precincts)
		], 200);
	}
	
	/**
	 * Get the number of incidents per incident type per county
	 */
	public function incidentTypesPerCountyTops() {
		$incidentTypeTransformerR = new IncidentTypeTransformerReport();
		$output = array('data' => array());
		$incidentTypes = IncidentType::all();
		foreach ($incidentTypes as $incidentType) {
			$incidentTypeStructures = Incident::select(DB::raw('incidents.*, count(*) as `incidents_no`'))
											   ->where('incident_type_id', $incidentType->id)
											   ->groupBy('county_id')
											   ->orderBy('incidents_no', 'desc')
											   ->get();
			
			array_push($output['data'], array($incidentType->name => array("first" => $incidentTypeTransformerR->transformCollection($incidentTypeStructures->take(5)->all()),
																		   "last"  => $incidentTypeTransformerR->transformCollection($incidentTypeStructures->reverse()->take(5)->all()))));
		}
		
		return response()->json($output);
	}
	
	/**
	 * Get all the reports in one endpoint
	 */
	public function all() {
		$incidentsTotal = json_decode($this->incidentsTotal()->content(), true);
		$incidentsTotal['sesizari'] = $incidentsTotal['data'];
		unset($incidentsTotal['data']);
		
		$incidentsPerCounty = json_decode($this->incidentsPerCounty()->content(), true);
		$incidentsPerCounty['sesizari-judete'] = $incidentsPerCounty['data'];
		unset($incidentsPerCounty['data']);
		
		$incidentsOpeningPerCounty = json_decode($this->incidentsOpeningPerCounty()->content(), true);
		$incidentsOpeningPerCounty['sesizari-deschidere-judete'] = $incidentsOpeningPerCounty['data'];
		unset($incidentsOpeningPerCounty['data']);
		
		$incidentsCountingPerCounty = json_decode($this->incidentsCountingPerCounty()->content(), true);
		$incidentsCountingPerCounty['sesizari-numarare-judete'] = $incidentsCountingPerCounty['data'];
		unset($incidentsCountingPerCounty['data']);
		
		$incidentTypesPerCountyTops = json_decode($this->incidentTypesPerCountyTops()->content(), true);
		$incidentTypesPerCountyTops['sesizari-tip-judete'] = $incidentTypesPerCountyTops['data'];
		unset($incidentTypesPerCountyTops['data']);
		
		$output['data'] = array_merge($incidentsTotal, $incidentsPerCounty, $incidentsOpeningPerCounty, $incidentsCountingPerCounty, $incidentTypesPerCountyTops);
		return response()->json($output);      
	}
	
	/*
	
	$api->get('statistici/sesizari-tip-judete',   	   'App\Api\V1\Controllers\ReportingController@incidentTypesPerCountyTops'); */
    
	private function getIncidentTypeId($typeCode) {
		$type = IncidentType::where('code', $typeCode)->first();
		return $type->id;
	}
}
    
