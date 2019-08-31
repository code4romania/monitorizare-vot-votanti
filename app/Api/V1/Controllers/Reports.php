<?php

namespace App\Api\V1\Controllers;

use JWTAuth;
use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\DB;

use App\Api\V1\Transformers\CountiesWithIncidentsTransformer;

use App\County;
use App\Incident;

class Reports extends Controller
{

    use Helpers;

    public static function totalIncidents()
    {
        return Incident::where('status', 'Approved')->count();
    }

    public static function totalIncidentsAllStatuses()
    {
        return Incident::count();
    }

    public static function countiesWithIncidents()
    {
        $counties = County::orderBy('name')->get();
        $countyTransformer = new CountiesWithIncidentsTransformer();
        return $countyTransformer->transformCollection($counties->all());
    }

    public static function totalIncidentsByType()
    {
        $groupedIncidents = Incident::select(DB::raw('count(*) as count, incident_type_id'))
            ->where('status', 'Approved')
            ->groupBy('incident_type_id')
            ->with('type')
            ->get();
        return $groupedIncidents;
    }

    /**
     * Counts for each incident status ("Approved", "Pending", "Rejected")
     *
     * @return mixed
     */
    public static function totalIncidentsByStatus()
    {
        $groupedIncidents = Incident::select(DB::raw('count(*) as count, status'))
            ->groupBy('status')
            ->get();
        return $groupedIncidents;
    }
}
