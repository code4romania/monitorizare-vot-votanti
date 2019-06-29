<?php

namespace App\Api\V1\Transformers;

class IncidentTypeTransformer extends Transformer
{
    /**
     * @param $incidentType
     * @return array
     */
    public function transform($incidentType)
    {
        return [
            'id' => $incidentType['id'],
            'name' => $incidentType['name'],
            'label' => $incidentType['label'],
            'code' => $incidentType['code'],
            'status' => $incidentType['status'],
        ];
    }
}
