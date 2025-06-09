<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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
        'evaluation_instance_status',
        'evaluation_instance_started_at',
        'evaluation_instance_finalized_at',
        'adviser_id',
        'department_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'evaluation_instance_started_at' => 'datetime',
        'evaluation_instance_finalized_at' => 'datetime',
    ];

    /**
     * Boot the model and add event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Validate uniqueness before creating
        static::creating(function ($council) {
            static::validateUniqueness($council);
        });

        // Validate uniqueness before updating
        static::updating(function ($council) {
            static::validateUniqueness($council);
        });
    }

    /**
     * Validate that only one council exists per department per academic year
     */
    protected static function validateUniqueness($council)
    {
        $query = static::where('department_id', $council->department_id)
            ->where('academic_year', $council->academic_year);

        // Exclude current council if updating
        if ($council->exists) {
            $query->where('id', '!=', $council->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'academic_year' => 'A council already exists for this department and academic year.'
            ]);
        }
    }

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
               $this->evaluation_instance_status === 'not_started' &&
               $this->hasPeerEvaluatorsAssigned();
    }

    /**
     * Check if evaluation instance can be started
     */
    public function canStartEvaluationInstance()
    {
        return $this->status === 'active' &&
               $this->councilOfficers()->count() > 0 &&
               $this->evaluation_instance_status === 'not_started' &&
               $this->hasPeerEvaluatorsAssigned();
    }

    /**
     * Check if evaluation instance can be finalized
     */
    public function canFinalizeEvaluationInstance()
    {
        return $this->evaluation_instance_status === 'active' &&
               $this->allEvaluationsCompleted();
    }

    /**
     * Check if evaluation instance is active (evaluations can be edited)
     */
    public function isEvaluationInstanceActive()
    {
        return $this->evaluation_instance_status === 'active';
    }

    /**
     * Check if evaluation instance is finalized (evaluations are locked)
     */
    public function isEvaluationInstanceFinalized()
    {
        return $this->evaluation_instance_status === 'finalized';
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
        return $this->evaluation_instance_status !== 'not_started';
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
