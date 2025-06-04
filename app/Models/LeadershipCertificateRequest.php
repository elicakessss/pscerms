<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipCertificateRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'council_id',
        'certificate_type',
        'is_graduating',
        'status',
        'requested_at',
        'responded_at',
        'adviser_response',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_graduating' => 'boolean',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    /**
     * Get the student that made the request.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the council for which the certificate is requested.
     */
    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    /**
     * Scope to get pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get dismissed requests.
     */
    public function scopeDismissed($query)
    {
        return $query->where('status', 'dismissed');
    }
}
