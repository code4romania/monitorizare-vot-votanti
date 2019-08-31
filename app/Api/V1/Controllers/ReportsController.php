<?php

namespace App\Api\V1\Controllers;

use JWTAuth;
use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    use Helpers;

    /**
     * @SWG\Get(
     *     path="/api/reports",
     *     summary="Fetch reports summary",
     *     tags={"Reports"},
     *     description="Fetch reports summary.",
     *     operationId="fetchReportsSummary",
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response=200,
     *         description="successful operation",
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Invalid tag value",
     *     ),
     *     security={
     *         {
     *             "monitorizare_auth": {"read:reports"}
     *         }
     *     }
     * )
     */
    public function index()
    {   
        return response()->json([
            'totalIncidents' => Reports::totalIncidents(),
            'incidentCountsByStatus' => Reports::totalIncidentCountsByStatus(),
            'incidentsByType' => Reports::totalIncidentsByType(),
            'incidentsByCounty' => Reports::countiesWithIncidents()
        ], 200);
    }
}
