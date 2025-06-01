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
}
