<?php

namespace App\Services;

use App\IncidentType;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Support\Facades\Validator;

class IncidentTypeService
{
    /**
     * @param array $data
     * @return IncidentType
     * @throws @StoreResourceFailedException
     * @throws @\Exception
     */
    public function create(array $data): IncidentType
    {
        $this->validate($data);

        $incidentType = new IncidentType();
        $incidentType = $this->hydrate($data, $incidentType);
        $incidentType->save();

        return $incidentType;
    }

    /**
     * @param IncidentType $incidentType
     * @param array $data
     * @return IncidentType
     */
    public function update(IncidentType $incidentType, array $data)
    {
        $this->validate($data, $incidentType->id);
        $incidentType = $this->hydrate($data, $incidentType);
        $incidentType->save();

        return $incidentType;
    }

    /**
     * @param array $data
     * @param null $id
     */
    public function validate(array $data, $id = null)
    {
        $rules = [
            'name' => 'required|max:254',
            'label' => 'required|max:254',
            'code' => 'required|unique:incident_types,code,' . $id,
            'status' => 'in:Active,Inactive'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException('Could not create a new incident type.', $validator->errors());
        }
    }

    /**
     * @param array $data
     * @param IncidentType $incidentType
     * @return IncidentType
     */
    public function hydrate(array $data, IncidentType $incidentType)
    {
        $incidentType->name = $data['name'];
        $incidentType->label = $data['label'];
        $incidentType->code = $data['code'];
        $incidentType->status = $data['status'];

        return $incidentType;
    }
}
