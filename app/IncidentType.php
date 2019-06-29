<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IncidentType
 * @package App
 * @SWG\Definition(
 *   @SWG\Xml(name="IncidentType")
 * )
 */
class IncidentType extends Model
{
    const ACTIVE = 'Active';
    const INACTIVE = 'Inactive';

    protected $fillable = ['name', 'label', 'code', 'status'];
    public $timestamps = false;

    public function incidents()
    {
        return $this->hasMany('App\Incident');
    }
}
