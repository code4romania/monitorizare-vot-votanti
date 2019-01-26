<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *   @SWG\Xml(name="Incident")
 * )
 */
class Incident extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'county_id', 'city_id', 'incident_type_id', 'precinct_id', 'description', 'image_url'
    ];

    // One to many inverse relation to IncidentType model
    public function type()
    {
    	return $this->belongsTo('App\IncidentType', 'incident_type_id', 'id');
    }

    // One to many inverse relation to County model
    public function county()
    {
    	return $this->belongsTo('App\County');
    }

    // One to many inverse relation to City model
    public function city()
    {
        return $this->belongsTo('App\City');
    }

    // One to many inverse relation to Precinct model
    public function precinct()
    {
        return $this->belongsTo('App\Precinct');
    }
}
