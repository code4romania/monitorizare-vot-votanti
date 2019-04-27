<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    public const ACTIVE = 'Active';
    public const INACTIVE = 'Inactive';
    protected $fillable = [
        'title', 'user_id', 'status', 'fields'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
