<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *   @SWG\Xml(name="Precinct")
 * )
 */
class Precinct extends Model
{
    protected $fillable = ['city_id','siruta_code','circ_no','precinct_no', 'headquarter', 'address'];

	public function incidents()
	{
		return $this->hasMany('App\Incident');
	}
	
	public function county()
	{
		return $this->belongsTo('App\County');
	}
	
	public function city()
	{
		return $this->belongsTo('App\City');
	}

    public function setCityIdAttribute($value)
    {
        $city = City::findOrFail($value);
        $this->attributes['city_id'] = intval($value);
        $this->attributes['county_id'] = $city->county_id;
    }
}
