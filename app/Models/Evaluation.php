<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'council_id',
        'evaluator_id',
        'evaluator_type',
        'evaluated_student_id',
        'evaluation_type',
        'status',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the council that owns the evaluation.
     */
    public function council()
    {
        return $this->belongsTo(Council::class);
    }

    /**
     * Get the evaluator (student or adviser) that owns the evaluation.
     */
    public function evaluator()
    {
        if ($this->evaluator_type === 'adviser') {
            return $this->belongsTo(Adviser::class, 'evaluator_id');
        }
        return $this->belongsTo(Student::class, 'evaluator_id');
    }

    /**
     * Get the evaluated student.
     */
    public function evaluatedStudent()
    {
        return $this->belongsTo(Student::class, 'evaluated_student_id');
    }

    /**
     * Get the evaluation forms for the evaluation.
     */
    public function evaluationForms()
    {
        return $this->hasMany(EvaluationForm::class);
    }

    /**
     * Check if this evaluation can be edited
     */
    public function canBeEdited()
    {
        return $this->status === 'completed' && $this->council->isEvaluationInstanceActive();
    }

    /**
     * Check if this evaluation is in draft mode (can be edited)
     */
    public function isDraft()
    {
        return $this->council->isEvaluationInstanceActive();
    }

    /**
     * Check if this evaluation is finalized (cannot be edited)
     */
    public function isFinalized()
    {
        return $this->council->isEvaluationInstanceFinalized();
    }

    /**
     * Get existing responses formatted for form pre-population
     */
    public function getFormattedResponses()
    {
        $responses = [];
        foreach ($this->evaluationForms as $form) {
            // The section_name contains the field name (e.g., domain1_strand1_q1, length_of_service)
            $responses[$form->section_name] = $form->answer;
        }
        return $responses;
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClass()
    {
        return $this->status === 'completed'
            ? 'bg-green-100 text-green-800'
            : 'bg-yellow-100 text-yellow-800';
    }

    /**
     * Get status display text
     */
    public function getStatusDisplayText()
    {
        return $this->status === 'completed' ? 'Completed' : 'Pending';
    }
}
