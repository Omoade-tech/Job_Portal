<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'tokenable_id', 
        'tokenable_type', 
     
    ];

    /**
     * Define the polymorphic relationship.
     */
    public function tokenable()
    {
        return $this->morphTo();
    }
}