<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role',
        'name',
        'email',
        'password',
        'phoneNumber',
        'age',
        'sex',
        'status',
        'address',
        'city',
        'state',
        'country',
    ];

    /**
     * Automatically hash the password before saving.
     *
     * @param string $value
     * @return void
     */
   
   
   
   

    
}
