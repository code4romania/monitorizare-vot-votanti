<?php

namespace App\Api\V1\Transformers;

class IncidentTransformer extends Transformer
{

    public function transform($incident)
    {
    	return [
            'name' => $incident['first_name'] . ' ' . $incident['last_name'],
            'incidentType' => $incident['type'],
            'description' => $incident['description'],
            'startDate' => $incident['start_date'],
            'endDate' => $incident['end_date']
        ];
    }
}