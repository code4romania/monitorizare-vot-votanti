<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @SWG\Definition(
 *   @SWG\Xml(name="City")
 * )
 */
class City extends Model
{
	protected $fillable = [
		'county_id', 'name', 'siruta_code', 'el_circle_code'
	];
	
	public $timestamps = false;
	
	public function county()
	{
		return $this->belongsTo('App\County');
	}
	
	public function precincts()
	{
		return $this->hasMany('App\Precinct');
	}
}
