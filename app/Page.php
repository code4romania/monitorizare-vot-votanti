<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    const ACTIVE = 'Active';
    const INACTIVE = 'Inactive';

    protected $fillable = ['title', 'status', 'description', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
