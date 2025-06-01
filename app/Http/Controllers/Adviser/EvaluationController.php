<?php

namespace App\Http\Controllers\Adviser;

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
     * Show the evaluation form for a specific student
     */
    public function show(Council $council, Student $student)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if student is an officer in this council
        $officer = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $student->id)
            ->first();

        if (!$officer) {
            abort(404, 'Student is not an officer in this council.');
        }

        // Check if evaluation already exists
        $existingEvaluation = Evaluation::where('council_id', $council->id)
            ->where('evaluator_id', $adviser->id)
            ->where('evaluator_type', 'adviser')
            ->where('evaluated_student_id', $student->id)
            ->first();

        if ($existingEvaluation && $existingEvaluation->status === 'completed') {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'You have already completed the evaluation for this student.');
        }

        return view('evaluation.adviser', compact('council', 'student', 'officer'));
    }

    /**
     * Store the evaluation responses
     */
    public function store(Request $request)
    {
        $adviser = Auth::user();

        // Validate the request
        $validated = $request->validate([
            'council_id' => 'required|exists:councils,id',
            'evaluated_student_id' => 'required|exists:students,id',
            'evaluator_type' => 'required|in:adviser',
            // Domain 1 - Strand 1
            'domain1_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            'domain1_strand1_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            'domain1_strand1_q3' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            'domain1_strand1_q4' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Domain 1 - Strand 2
            'domain1_strand2_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Domain 2 - Strand 1
            'domain2_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Domain 2 - Strand 2
            'domain2_strand2_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            'domain2_strand2_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Domain 2 - Strand 3
            'domain2_strand3_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            'domain2_strand3_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Domain 3 - Strand 1
            'domain3_strand1_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            'domain3_strand1_q2' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Domain 3 - Strand 2
            'domain3_strand2_q1' => 'required|numeric|in:0.00,1.00,2.00,3.00',
            // Length of Service
            'length_of_service' => 'required|numeric|in:0.00,1.00,2.00,3.00',
        ]);

        $council = Council::findOrFail($validated['council_id']);

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if evaluation already exists
        $existingEvaluation = Evaluation::where('council_id', $validated['council_id'])
            ->where('evaluator_id', $adviser->id)
            ->where('evaluator_type', 'adviser')
            ->where('evaluated_student_id', $validated['evaluated_student_id'])
            ->first();

        if ($existingEvaluation && $existingEvaluation->status === 'completed') {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'You have already completed the evaluation for this student.');
        }

        DB::transaction(function () use ($validated, $adviser) {
            // Create or update the evaluation record
            $evaluation = Evaluation::updateOrCreate(
                [
                    'council_id' => $validated['council_id'],
                    'evaluator_id' => $adviser->id,
                    'evaluator_type' => 'adviser',
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

            // Store all evaluation responses
            $responses = [
                // Domain 1 - Strand 1
                ['section_name' => 'Domain 1 - Strand 1', 'question' => 'Organizes/co-organizes seminars and activities', 'answer' => $validated['domain1_strand1_q1']],
                ['section_name' => 'Domain 1 - Strand 1', 'question' => 'Facilitates/co-facilitates seminars and activities', 'answer' => $validated['domain1_strand1_q2']],
                ['section_name' => 'Domain 1 - Strand 1', 'question' => 'Participates in seminars and activities', 'answer' => $validated['domain1_strand1_q3']],
                ['section_name' => 'Domain 1 - Strand 1', 'question' => 'Attends SPUP-organized seminars and activities', 'answer' => $validated['domain1_strand1_q4']],
                // Domain 1 - Strand 2
                ['section_name' => 'Domain 1 - Strand 2', 'question' => 'Ensures quality in all tasks/assignments', 'answer' => $validated['domain1_strand2_q1']],
                // Domain 2 - Strand 1
                ['section_name' => 'Domain 2 - Strand 1', 'question' => 'Performs tasks outside assignment, solves issues, participates in aftercare', 'answer' => $validated['domain2_strand1_q1']],
                // Domain 2 - Strand 2
                ['section_name' => 'Domain 2 - Strand 2', 'question' => 'Shares in organization management and evaluation', 'answer' => $validated['domain2_strand2_q1']],
                ['section_name' => 'Domain 2 - Strand 2', 'question' => 'Shares in university projects/activities management', 'answer' => $validated['domain2_strand2_q2']],
                // Domain 2 - Strand 3
                ['section_name' => 'Domain 2 - Strand 3', 'question' => 'Attends regular meetings', 'answer' => $validated['domain2_strand3_q1']],
                ['section_name' => 'Domain 2 - Strand 3', 'question' => 'Attends special/emergency meetings', 'answer' => $validated['domain2_strand3_q2']],
                // Domain 3 - Strand 1
                ['section_name' => 'Domain 3 - Strand 1', 'question' => 'Model of grooming and proper decorum', 'answer' => $validated['domain3_strand1_q1']],
                ['section_name' => 'Domain 3 - Strand 1', 'question' => 'Submits reports regularly', 'answer' => $validated['domain3_strand1_q2']],
                // Domain 3 - Strand 2
                ['section_name' => 'Domain 3 - Strand 2', 'question' => 'Ensures cleanliness and orderliness', 'answer' => $validated['domain3_strand2_q1']],
                // Length of Service
                ['section_name' => 'Length of Service', 'question' => 'Length of Service Evaluation', 'answer' => $validated['length_of_service']],
            ];

            foreach ($responses as $response) {
                EvaluationForm::create([
                    'evaluation_id' => $evaluation->id,
                    'section_name' => $response['section_name'],
                    'question' => $response['question'],
                    'answer' => $response['answer'],
                ]);
            }
        });

        // Try to calculate scores if all evaluations are ready
        $evaluationService = new \App\Services\EvaluationService();
        $scoresCalculated = $evaluationService->calculateScoresIfReady($council);

        // Get evaluation progress
        $progress = $evaluationService->getEvaluationProgress($council);

        $message = 'Adviser evaluation submitted successfully!';

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

        return redirect()->route('adviser.councils.show', $council)
            ->with('success', $message);
    }
}
