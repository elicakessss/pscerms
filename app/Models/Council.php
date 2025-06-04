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
               !$this->hasEvaluations() &&
               $this->hasPeerEvaluatorsAssigned();
    }

    /**
     * Check if peer evaluators are assigned
     */
    public function hasPeerEvaluatorsAssigned()
    {
        $peerEvaluators = $this->councilOfficers()->where('is_peer_evaluator', true)->get();
        $level1Count = $peerEvaluators->where('peer_evaluator_level', 1)->count();
        $level2Count = $peerEvaluators->where('peer_evaluator_level', 2)->count();

        return $level1Count === 1 && $level2Count === 1;
    }

    /**
     * Get peer evaluators for this council
     */
    public function getPeerEvaluators()
    {
        return $this->councilOfficers()->where('is_peer_evaluator', true)->with('student')->get();
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

        // Calculate peer evaluations based on assigned peer evaluators
        $officers = $this->councilOfficers;
        $peerEvaluators = $officers->filter(function ($officer) {
            return $officer->is_peer_evaluator;
        });

        // Both peer evaluators evaluate all members (except themselves)
        $peerEvaluationsTotal = $peerEvaluators->count() * ($totalOfficers - 1);

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
