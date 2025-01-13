<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasApiTokens;

class JobSeeker extends Model
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
    return $this->morphMany(Token::class, 'tokenable');
}
 
}
