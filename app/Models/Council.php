<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Council extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'academic_year',
        'status',
        'adviser_id',
        'department_id',
    ];

    /**
     * Get the department that owns the council.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the adviser that owns the council.
     */
    public function adviser()
    {
        return $this->belongsTo(Adviser::class);
    }

    /**
     * Get the council officers for the council.
     */
    public function councilOfficers()
    {
        return $this->hasMany(CouncilOfficer::class);
    }

    /**
     * Get the evaluations for the council.
     */
    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Check if evaluations can be started
     */
    public function canStartEvaluations()
    {
        return $this->status === 'active' &&
               $this->councilOfficers()->count() > 0 &&
               !$this->hasEvaluations();
    }

    /**
     * Check if council has any evaluations
     */
    public function hasEvaluations()
    {
        return $this->evaluations()->exists();
    }

    /**
     * Check if all evaluations are completed
     */
    public function allEvaluationsCompleted()
    {
        if (!$this->hasEvaluations()) {
            return false;
        }

        $totalEvaluations = $this->evaluations()->count();
        $completedEvaluations = $this->evaluations()->where('status', 'completed')->count();

        return $totalEvaluations > 0 && $totalEvaluations === $completedEvaluations;
    }

    /**
     * Get evaluation progress for this council
     */
    public function getEvaluationProgress()
    {
        $totalOfficers = $this->councilOfficers()->count();

        if ($totalOfficers === 0) {
            return [
                'self_completed' => 0,
                'self_total' => 0,
                'peer_completed' => 0,
                'peer_total' => 0,
                'adviser_completed' => 0,
                'adviser_total' => 0,
            ];
        }

        // Calculate peer evaluations based on position levels
        $officers = $this->councilOfficers;
        $level1Officers = $officers->filter(function ($officer) {
            return $this->getPositionLevel($officer->position_title) === 1;
        });
        $level2Officers = $officers->filter(function ($officer) {
            return $this->getPositionLevel($officer->position_title) === 2;
        });

        // Level 1 officers evaluate all members (except themselves)
        // Level 2 officers evaluate only Level 1 officers
        $peerEvaluationsTotal = ($level1Officers->count() * ($totalOfficers - 1)) +
                               ($level2Officers->count() * $level1Officers->count());

        return [
            'self_completed' => $this->evaluations()->where('evaluator_type', 'self')->where('status', 'completed')->count(),
            'self_total' => $totalOfficers,
            'peer_completed' => $this->evaluations()->where('evaluator_type', 'peer')->where('status', 'completed')->count(),
            'peer_total' => $peerEvaluationsTotal,
            'adviser_completed' => $this->evaluations()->where('evaluator_type', 'adviser')->where('status', 'completed')->count(),
            'adviser_total' => $totalOfficers,
        ];
    }

    /**
     * Get position level based on title
     * 1 = President/Governor
     * 2 = Vice President/Vice Governor
     * 10 = Others
     */
    private function getPositionLevel($positionTitle)
    {
        // Check for Vice positions first (more specific)
        $level2Positions = ['Vice President', 'Vice Governor'];
        foreach ($level2Positions as $position) {
            if (stripos($positionTitle, $position) !== false) {
                return 2;
            }
        }

        // Then check for main positions
        $level1Positions = ['President', 'Governor'];
        foreach ($level1Positions as $position) {
            if (stripos($positionTitle, $position) !== false) {
                return 1;
            }
        }

        return 10;
    }
}
