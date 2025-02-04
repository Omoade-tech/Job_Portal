<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApply extends Model
{
    use HasFactory;

    protected $table = 'job_applies';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'coverLetter', 
        'resume',
        'job_title',
        'job_portals_id',
        'job_seekers_id',
        'status', 
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING, 
    ];

    public function jobPortal()
    {
        return $this->belongsTo(JobPortal::class, 'job_portals_id'); 
    }

    public function jobSeeker()
    {
        return $this->belongsTo(JobSeeker::class, 'job_seekers_id'); 
    }
}
