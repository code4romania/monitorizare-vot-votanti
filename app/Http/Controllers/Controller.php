<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

/**
 * @SWG\Info(title="Monitorizare Vot", version="1.0")
 */

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    /**
     * @param $items
     * @return array
     */
    public function getPaginator($items): array
    {
        return [
            'total' => $items->total(),
            'currentPage' => $items->currentPage(),
            'lastPage' => $items->lastPage(),
            'limit' => $items->perPage(),
            'previousPage' => $items->previousPageUrl(),
            'nextPage' => $items->nextPageUrl()
        ];
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function notFoundResponse()
    {
        return response()->json([
            'error' => ['message' => 'Record does not exist']
        ], 404);
    }
}
