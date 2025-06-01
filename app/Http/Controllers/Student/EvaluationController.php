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

        return view('evaluation.self', compact('council', 'officer'));
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

        return view('evaluation.peer', [
            'council' => $council,
            'student' => $evaluatedStudent,
            'officer' => $officer
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

        // Get evaluation type specific rules
        $evaluationRules = $this->getValidationRules($request->evaluator_type);

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
     * Get validation rules based on evaluation type
     */
    private function getValidationRules($evaluatorType)
    {
        if ($evaluatorType === 'self') {
            return [
                // Domain 2 - Strand 1 and Strand 3
                'domain2_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain2_strand3_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain2_strand3_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                // Domain 3 - All strands
                'domain3_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain3_strand1_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain3_strand2_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            ];
        } else { // peer
            return [
                // Domain 2 - All strands
                'domain2_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain2_strand2_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain2_strand2_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain2_strand3_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain2_strand3_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                // Domain 3 - All strands
                'domain3_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain3_strand1_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
                'domain3_strand2_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            ];
        }
    }

    /**
     * Get evaluation responses based on evaluation type
     */
    private function getEvaluationResponses($validated)
    {
        if ($validated['evaluator_type'] === 'self') {
            return [
                // Domain 2 - Strand 1 and Strand 3
                ['section_name' => 'Domain 2 - Strand 1', 'question' => 'Performs tasks outside assignment, solves issues, participates in aftercare', 'answer' => $validated['domain2_strand1_q1']],
                ['section_name' => 'Domain 2 - Strand 3', 'question' => 'Attends regular meetings', 'answer' => $validated['domain2_strand3_q1']],
                ['section_name' => 'Domain 2 - Strand 3', 'question' => 'Attends special/emergency meetings', 'answer' => $validated['domain2_strand3_q2']],
                // Domain 3 - All strands
                ['section_name' => 'Domain 3 - Strand 1', 'question' => 'Model of grooming and proper decorum', 'answer' => $validated['domain3_strand1_q1']],
                ['section_name' => 'Domain 3 - Strand 1', 'question' => 'Submits reports regularly', 'answer' => $validated['domain3_strand1_q2']],
                ['section_name' => 'Domain 3 - Strand 2', 'question' => 'Ensures cleanliness and orderliness', 'answer' => $validated['domain3_strand2_q1']],
            ];
        } else { // peer
            return [
                // Domain 2 - All strands
                ['section_name' => 'Domain 2 - Strand 1', 'question' => 'Performs tasks outside assignment, solves issues, participates in aftercare', 'answer' => $validated['domain2_strand1_q1']],
                ['section_name' => 'Domain 2 - Strand 2', 'question' => 'Shares in organization management and evaluation', 'answer' => $validated['domain2_strand2_q1']],
                ['section_name' => 'Domain 2 - Strand 2', 'question' => 'Shares in university projects/activities management', 'answer' => $validated['domain2_strand2_q2']],
                ['section_name' => 'Domain 2 - Strand 3', 'question' => 'Attends regular meetings', 'answer' => $validated['domain2_strand3_q1']],
                ['section_name' => 'Domain 2 - Strand 3', 'question' => 'Attends special/emergency meetings', 'answer' => $validated['domain2_strand3_q2']],
                // Domain 3 - All strands
                ['section_name' => 'Domain 3 - Strand 1', 'question' => 'Model of grooming and proper decorum', 'answer' => $validated['domain3_strand1_q1']],
                ['section_name' => 'Domain 3 - Strand 1', 'question' => 'Submits reports regularly', 'answer' => $validated['domain3_strand1_q2']],
                ['section_name' => 'Domain 3 - Strand 2', 'question' => 'Ensures cleanliness and orderliness', 'answer' => $validated['domain3_strand2_q1']],
            ];
        }
    }
}
