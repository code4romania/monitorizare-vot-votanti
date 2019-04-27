<?php

namespace App\Api\V1\Transformers;

class IncidentTransformer extends Transformer
{

    public function transform($incident)
    {
        return [
                'id' => $incident['id'],
                'name' => $incident['first_name'] . ' ' . $incident['last_name'],
                'incidentType' => $incident['type'],
                'description' => $incident['description'],
                'county' => $incident->county,
                'city' => $incident->city,
                'precinct' => [
                        'id' => $incident['precinct']['id'],
                        'electoralCircleId' => $incident['precinct']['circ_no'],
                        'precinctNumber' => $incident['precinct']['precinct_no'],
                        'address' => $incident['precinct']['address']
                ],
                'status' => $incident['status'],
                'createdAt' => $incident['created_at']->toDateTimeString(),
                'image' => url($incident['image_url']),
        ];
    }
}
