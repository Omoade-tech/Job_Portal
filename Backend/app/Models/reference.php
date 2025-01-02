<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reference extends Model
{

    protected $fillable = [
        'name',
        'phoneNumber',
        'email',
    ];
    /** @use HasFactory<\Database\Factories\ReferenceFactory> */
    use HasFactory;


    public function jobApply()
    {
        return $this->hasMany(JobApply::class);
    }
}
