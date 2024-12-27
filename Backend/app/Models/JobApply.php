<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApply extends Model
{

    protected $fillable = [
        'name',
        'email',
        'phoneNumber',
        'address',
        'coverlettter',
        'resume',
        'reference_id',
    ];
    /** @use HasFactory<\Database\Factories\JobApplyFactory> */
    use HasFactory;


    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }
}
