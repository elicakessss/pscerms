<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouncilOfficer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'council_id',
        'student_id',
        'position_title',
        'position_level',
        'is_peer_evaluator',
        'peer_evaluator_level',
        'self_score',
        'peer_score',
        'adviser_score',
        'final_score',
        'rank',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_peer_evaluator' => 'boolean',
        'self_score' => 'decimal:2',
        'peer_score' => 'decimal:2',
        'adviser_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the council that owns the council officer.
     */
    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    /**
     * Get the student that owns the council officer.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the ranking category based on final score
     */
    public function getRankingCategoryAttribute()
    {
        if (!$this->final_score) {
            return null;
        }

        if ($this->final_score >= 2.41) {
            return 'Gold';
        } elseif ($this->final_score >= 1.81) {
            return 'Silver';
        } elseif ($this->final_score >= 1.21) {
            return 'Bronze';
        } else {
            return 'Needs Improvement';
        }
    }

    /**
     * Check if this officer can be assigned as a peer evaluator
     */
    public function canBeAssignedAsPeerEvaluator()
    {
        // Can't assign if evaluations have already started
        return !$this->council->hasEvaluations();
    }

    /**
     * Get the peer evaluator level display text
     */
    public function getPeerEvaluatorLevelTextAttribute()
    {
        if (!$this->is_peer_evaluator) {
            return null;
        }

        return $this->peer_evaluator_level === 1 ? 'Level 1' : 'Level 2';
    }

    /**
     * Check if all evaluations are completed for this officer
     */
    public function hasAllEvaluationsCompleted()
    {
        $selfEvaluation = \App\Models\Evaluation::where('council_id', $this->council_id)
            ->where('evaluator_id', $this->student_id)
            ->where('evaluator_type', 'self')
            ->where('evaluated_student_id', $this->student_id)
            ->where('status', 'completed')
            ->exists();

        $adviserEvaluation = \App\Models\Evaluation::where('council_id', $this->council_id)
            ->where('evaluator_type', 'adviser')
            ->where('evaluated_student_id', $this->student_id)
            ->where('status', 'completed')
            ->exists();

        return $selfEvaluation && $adviserEvaluation;
    }

    /**
     * Get evaluation progress for this officer
     */
    public function getEvaluationProgress()
    {
        $selfCompleted = \App\Models\Evaluation::where('council_id', $this->council_id)
            ->where('evaluator_id', $this->student_id)
            ->where('evaluator_type', 'self')
            ->where('evaluated_student_id', $this->student_id)
            ->where('status', 'completed')
            ->exists();

        $peerEvaluations = \App\Models\Evaluation::where('council_id', $this->council_id)
            ->where('evaluator_type', 'peer')
            ->where('evaluated_student_id', $this->student_id)
            ->get();

        $peerCompleted = $peerEvaluations->where('status', 'completed')->count();
        $peerTotal = $peerEvaluations->count();

        $adviserCompleted = \App\Models\Evaluation::where('council_id', $this->council_id)
            ->where('evaluator_type', 'adviser')
            ->where('evaluated_student_id', $this->student_id)
            ->where('status', 'completed')
            ->exists();

        return [
            'self_completed' => $selfCompleted,
            'peer_completed' => $peerCompleted,
            'peer_total' => $peerTotal,
            'adviser_completed' => $adviserCompleted,
        ];
    }
}
