<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    //
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'user_id', 
    ];

    /**
     * Define the relationship with the user (JobSeeker or Employer).
     */
    public function user()
    {
        return $this->belongsTo(User::class); // Adjust this if you're using polymorphic relationships
    }
}
