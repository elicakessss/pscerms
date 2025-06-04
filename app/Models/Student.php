<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'id_number',
        'first_name',
        'last_name',
        'email',
        'password',
        'department_id',
        'profile_picture',
        'description',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the council officers for the student.
     */
    public function councilOfficers()
    {
        return $this->hasMany(CouncilOfficer::class);
    }

    /**
     * Get the evaluations where this student is the evaluator.
     */
    public function evaluationsAsEvaluator()
    {
        return $this->hasMany(Evaluation::class, 'evaluator_id');
    }

    /**
     * Get the leadership certificate requests for the student.
     */
    public function leadershipCertificateRequests()
    {
        return $this->hasMany(LeadershipCertificateRequest::class);
    }

    /**
     * Check if student is already in a council for the given academic year.
     */
    public function isInCouncilForAcademicYear($academicYear)
    {
        return $this->councilOfficers()
            ->whereHas('council', function($query) use ($academicYear) {
                $query->where('academic_year', $academicYear);
            })
            ->exists();
    }

    /**
     * Get the council the student is in for the given academic year.
     */
    public function getCouncilForAcademicYear($academicYear)
    {
        $councilOfficer = $this->councilOfficers()
            ->whereHas('council', function($query) use ($academicYear) {
                $query->where('academic_year', $academicYear);
            })
            ->with('council')
            ->first();

        return $councilOfficer ? $councilOfficer->council : null;
    }

    /**
     * Get the evaluations where this student is being evaluated.
     */
    public function evaluationsAsEvaluated()
    {
        return $this->hasMany(Evaluation::class, 'evaluated_student_id');
    }
}
