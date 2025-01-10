<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasApiTokens;

class Employer extends Model
{
    use HasApiTokens;
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
    public function tokens()        
    {
        return $this->hasMany(Token::class);
    }
 
    
    }

  

