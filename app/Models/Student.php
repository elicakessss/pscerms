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

    /**
     * Calculate length of service score based on completed council terms.
     *
     * @return float
     */
    public function calculateLengthOfService()
    {
        $completedCount = $this->getCompletedCouncilsCount();

        if ($completedCount == 0) {
            return 0.00; // Did not finish their term
        } elseif ($completedCount == 1) {
            return 1.00; // Finished one term
        } elseif ($completedCount == 2) {
            return 2.00; // Finished two terms
        } else {
            return 3.00; // Finished 3 or more terms
        }
    }

    /**
     * Get human-readable description of length of service.
     *
     * @return string
     */
    public function getLengthOfServiceDescription()
    {
        $score = $this->calculateLengthOfService();
        $count = $this->getCompletedCouncilsCount();

        switch ($score) {
            case 0.00:
                return 'Did not finish their term (0.00)';
            case 1.00:
                return 'Finished one term (1.00)';
            case 2.00:
                return 'Finished two terms (2.00)';
            case 3.00:
                return "Finished {$count} terms (3.00)";
            default:
                return 'Unknown (0.00)';
        }
    }

    /**
     * Get count of completed councils for this student.
     *
     * @return int
     */
    public function getCompletedCouncilsCount()
    {
        return $this->councilOfficers()
            ->whereHas('council', function($query) {
                $query->where('status', 'completed');
            })
            ->whereNotNull('completed_at')
            ->count();
    }

    /**
     * Get the latest UNIWIDE council term served by this student.
     *
     * @return \App\Models\CouncilOfficer|null
     */
    public function getLatestUniwideCouncilOfficer()
    {
        return $this->councilOfficers()
            ->whereHas('council', function($query) {
                $query->whereHas('department', function($subQuery) {
                    $subQuery->where('abbreviation', 'UNIWIDE');
                });
            })
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->with(['council.department'])
            ->orderBy('completed_at', 'desc')
            ->first();
    }

    /**
     * Get the latest departmental council term served by this student.
     *
     * @return \App\Models\CouncilOfficer|null
     */
    public function getLatestDepartmentalCouncilOfficer()
    {
        return $this->councilOfficers()
            ->whereHas('council', function($query) {
                $query->whereHas('department', function($subQuery) {
                    $subQuery->where('abbreviation', '!=', 'UNIWIDE');
                });
            })
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->with(['council.department'])
            ->orderBy('completed_at', 'desc')
            ->first();
    }

    /**
     * Check if student has served in UNIWIDE councils.
     *
     * @return bool
     */
    public function hasServedInUniwideCouncils()
    {
        return $this->councilOfficers()
            ->whereHas('council', function($query) {
                $query->whereHas('department', function($subQuery) {
                    $subQuery->where('abbreviation', 'UNIWIDE');
                });
            })
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->exists();
    }

    /**
     * Check if student has served in departmental councils.
     *
     * @return bool
     */
    public function hasServedInDepartmentalCouncils()
    {
        return $this->councilOfficers()
            ->whereHas('council', function($query) {
                $query->whereHas('department', function($subQuery) {
                    $subQuery->where('abbreviation', '!=', 'UNIWIDE');
                });
            })
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->exists();
    }
}
