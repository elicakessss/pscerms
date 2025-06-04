<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use App\Models\Council;
use App\Models\Student;
use App\Models\CouncilOfficer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    /**
     * Show the self-evaluation form
     */
    public function showSelf(Council $council)
    {
        $student = Auth::user();

        // Check if student is an officer in this council
        $officer = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$officer) {
            abort(404, 'You are not an officer in this council.');
        }

        // Check if self-evaluation already exists
        $existingEvaluation = Evaluation::where('council_id', $council->id)
            ->where('evaluator_id', $student->id)
            ->where('evaluator_type', 'self')
            ->where('evaluated_student_id', $student->id)
            ->first();

        if ($existingEvaluation && $existingEvaluation->status === 'completed') {
            return redirect()->route('student.dashboard')
                ->with('error', 'You have already completed your self-evaluation for this council.');
        }

        // Get filtered questions for self evaluation
        $questions = $this->getFilteredQuestions('self');

        return view('evaluation.self', compact('council', 'officer', 'questions'));
    }

    /**
     * Show the peer evaluation form
     */
    public function showPeer(Council $council, Student $evaluatedStudent)
    {
        $student = Auth::user();

        // Check if current student is an officer in this council
        $evaluatorOfficer = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$evaluatorOfficer) {
            abort(404, 'You are not an officer in this council.');
        }

        // Check if evaluated student is an officer in this council
        $officer = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $evaluatedStudent->id)
            ->first();

        if (!$officer) {
            abort(404, 'The student you are trying to evaluate is not an officer in this council.');
        }

        // Prevent self-evaluation through peer form
        if ($student->id === $evaluatedStudent->id) {
            return redirect()->route('student.evaluation.self', $council)
                ->with('error', 'Please use the self-evaluation form to evaluate yourself.');
        }

        // Check if peer evaluation already exists
        $existingEvaluation = Evaluation::where('council_id', $council->id)
            ->where('evaluator_id', $student->id)
            ->where('evaluator_type', 'peer')
            ->where('evaluated_student_id', $evaluatedStudent->id)
            ->first();

        if ($existingEvaluation && $existingEvaluation->status === 'completed') {
            return redirect()->route('student.dashboard')
                ->with('error', 'You have already completed the peer evaluation for this student.');
        }

        // Get filtered questions for peer evaluation
        $questions = $this->getFilteredQuestions('peer');

        return view('evaluation.peer', [
            'council' => $council,
            'student' => $evaluatedStudent,
            'officer' => $officer,
            'questions' => $questions
        ]);
    }

    /**
     * Store the evaluation responses (both self and peer)
     */
    public function store(Request $request)
    {
        $student = Auth::user();

        // Base validation rules
        $baseRules = [
            'council_id' => 'required|exists:councils,id',
            'evaluated_student_id' => 'required|exists:students,id',
            'evaluator_type' => 'required|in:self,peer',
        ];

        // Get evaluation type specific rules that match exactly what the form shows
        $evaluationRules = $this->getValidationRulesForEvaluatorType($request->evaluator_type);

        $validated = $request->validate(array_merge($baseRules, $evaluationRules));

        $council = Council::findOrFail($validated['council_id']);

        // Check if current student is an officer in this council
        $evaluatorOfficer = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$evaluatorOfficer) {
            abort(403, 'You are not an officer in this council.');
        }

        // Additional validation for peer evaluation
        if ($validated['evaluator_type'] === 'peer' && $student->id === $validated['evaluated_student_id']) {
            return redirect()->back()
                ->with('error', 'You cannot evaluate yourself using the peer evaluation form.');
        }

        // Check if evaluation already exists
        $existingEvaluation = Evaluation::where('council_id', $validated['council_id'])
            ->where('evaluator_id', $student->id)
            ->where('evaluator_type', $validated['evaluator_type'])
            ->where('evaluated_student_id', $validated['evaluated_student_id'])
            ->first();

        if ($existingEvaluation && $existingEvaluation->status === 'completed') {
            return redirect()->route('student.dashboard')
                ->with('error', 'You have already completed this evaluation.');
        }

        DB::transaction(function () use ($validated, $student) {
            // Create or update the evaluation record
            $evaluation = Evaluation::updateOrCreate(
                [
                    'council_id' => $validated['council_id'],
                    'evaluator_id' => $student->id,
                    'evaluator_type' => $validated['evaluator_type'],
                    'evaluated_student_id' => $validated['evaluated_student_id'],
                ],
                [
                    'evaluation_type' => 'rating',
                    'status' => 'completed',
                    'submitted_at' => now(),
                ]
            );

            // Delete existing evaluation forms for this evaluation
            EvaluationForm::where('evaluation_id', $evaluation->id)->delete();

            // Store evaluation responses based on type
            $responses = $this->getEvaluationResponses($validated);

            foreach ($responses as $response) {
                EvaluationForm::create([
                    'evaluation_id' => $evaluation->id,
                    'section_name' => $response['section_name'],
                    'question' => $response['question'],
                    'answer' => $response['answer'],
                ]);
            }
        });

        $evaluationType = $validated['evaluator_type'] === 'self' ? 'self-evaluation' : 'peer evaluation';

        // Try to calculate scores if all evaluations are ready
        $evaluationService = new \App\Services\EvaluationService();
        $scoresCalculated = $evaluationService->calculateScoresIfReady($council);

        // Get evaluation progress
        $progress = $evaluationService->getEvaluationProgress($council);

        $message = ucfirst($evaluationType) . ' submitted successfully!';

        if ($scoresCalculated) {
            $message .= ' All evaluations are now complete and scores have been calculated.';

            // Check if council was completed
            $council->refresh();
            if ($council->status === 'completed') {
                $message .= ' The council evaluation process has been completed and the council is now closed.';
            }
        } else {
            $remaining = $progress['evaluations_total'] - $progress['evaluations_completed'];
            $message .= " Progress: {$progress['evaluations_completed']}/{$progress['evaluations_total']} evaluations completed ({$progress['completion_percentage']}%).";
            if ($remaining > 0) {
                $message .= " {$remaining} evaluations remaining.";
            }
        }

        return redirect()->route('student.dashboard')
            ->with('success', $message);
    }

    /**
     * Get filtered questions for a specific evaluator type
     * This ensures consistency between form display and validation
     */
    private function getFilteredQuestions($evaluatorType)
    {
        $evaluationConfig = config('evaluation_questions.domains');
        $questions = [];

        foreach ($evaluationConfig as $domain) {
            $filteredDomain = $domain;
            $filteredDomain['strands'] = [];

            foreach ($domain['strands'] as $strand) {
                $filteredStrand = $strand;
                $filteredStrand['questions'] = [];

                foreach ($strand['questions'] as $question) {
                    if (in_array($evaluatorType, $question['access_levels'])) {
                        $filteredStrand['questions'][] = $question;
                    }
                }

                if (!empty($filteredStrand['questions'])) {
                    $filteredDomain['strands'][] = $filteredStrand;
                }
            }

            if (!empty($filteredDomain['strands'])) {
                $questions[] = $filteredDomain;
            }
        }

        return $questions;
    }

    /**
     * Get validation rules based on filtered questions for evaluator type
     * This ensures validation matches exactly what the form shows
     */
    private function getValidationRulesForEvaluatorType($evaluatorType)
    {
        $validationRules = [];
        $filteredQuestions = $this->getFilteredQuestions($evaluatorType);

        foreach ($filteredQuestions as $domainIndex => $domain) {
            foreach ($domain['strands'] as $strandIndex => $strand) {
                foreach ($strand['questions'] as $questionIndex => $question) {
                    $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                    $validationRules[$fieldName] = 'required|numeric|in:0.00,1.00,2.00,3.00';
                }
            }
        }

        return $validationRules;
    }

    /**
     * Get evaluation responses based on evaluation type
     */
    private function getEvaluationResponses($validated)
    {
        $responses = [];
        $evaluatorType = $validated['evaluator_type'];

        // Generate dynamic responses from config
        $evaluationConfig = config('evaluation_questions.domains');
        foreach ($evaluationConfig as $domainIndex => $domain) {
            foreach ($domain['strands'] as $strandIndex => $strand) {
                foreach ($strand['questions'] as $questionIndex => $question) {
                    if (in_array($evaluatorType, $question['access_levels'])) {
                        $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                        $sectionName = 'Domain ' . ($domainIndex + 1) . ' - Strand ' . ($strandIndex + 1);

                        if (isset($validated[$fieldName])) {
                            $responses[] = [
                                'section_name' => $sectionName,
                                'question' => $question['text'],
                                'answer' => $validated[$fieldName]
                            ];
                        }
                    }
                }
            }
        }

        return $responses;
    }
}
