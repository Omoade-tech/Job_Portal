<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPortal extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'companyLogo',
        'companyName',
        'contract',
        'post',
        'salary',
        'description',
        'location',
        'responsibility',
    ];

    /**
     * Get a summary of the job posting.
     *
     * @return string
     */
    public function getJobSummary()
    {
        return "{$this->post} at {$this->companyName}, {$this->location} - {$this->contract} contract. Salary: {$this->salary}";
    }
    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }
}
