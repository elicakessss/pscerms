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

        // Get evaluation questions from config
        $evaluationConfig = config('evaluation_questions.domains');

        // Filter questions for adviser access
        $questions = [];
        foreach ($evaluationConfig as $domain) {
            $filteredDomain = $domain;
            $filteredDomain['strands'] = [];

            foreach ($domain['strands'] as $strand) {
                $filteredStrand = $strand;
                $filteredStrand['questions'] = [];

                foreach ($strand['questions'] as $question) {
                    if (in_array('adviser', $question['access_levels'])) {
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

        return view('evaluation.adviser', compact('council', 'student', 'officer', 'questions'));
    }

    /**
     * Store the evaluation responses
     */
    public function store(Request $request)
    {
        $adviser = Auth::user();

        // Get evaluation questions from config
        $evaluationConfig = config('evaluation_questions.domains');

        // Generate dynamic validation rules
        $validationRules = [
            'council_id' => 'required|exists:councils,id',
            'evaluated_student_id' => 'required|exists:students,id',
            'evaluator_type' => 'required|in:adviser',
            'length_of_service' => 'required|numeric|in:0.00,1.00,2.00,3.00', // Required for first-time students, calculated for others
        ];

        // Generate validation rules from config
        foreach ($evaluationConfig as $domainIndex => $domain) {
            foreach ($domain['strands'] as $strandIndex => $strand) {
                foreach ($strand['questions'] as $questionIndex => $question) {
                    if (in_array('adviser', $question['access_levels'])) {
                        $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                        $validationRules[$fieldName] = 'required|numeric|in:0.00,1.00,2.00,3.00';
                    }
                }
            }
        }

        $validated = $request->validate($validationRules);

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
            // Get the council to check evaluation instance status
            $council = \App\Models\Council::find($validated['council_id']);

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

            // Generate dynamic responses from config
            $responses = [];
            $evaluationConfig = config('evaluation_questions.domains');

            // Add evaluation questions responses
            foreach ($evaluationConfig as $domainIndex => $domain) {
                foreach ($domain['strands'] as $strandIndex => $strand) {
                    foreach ($strand['questions'] as $questionIndex => $question) {
                        if (in_array('adviser', $question['access_levels'])) {
                            $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);

                            $responses[] = [
                                'section_name' => $fieldName,
                                'question' => $question['text'],
                                'answer' => $validated[$fieldName]
                            ];
                        }
                    }
                }
            }

            // Add length of service (conditional logic)
            $evaluatedStudent = \App\Models\Student::findOrFail($validated['evaluated_student_id']);
            $completedCouncils = $evaluatedStudent->getCompletedCouncilsCount();
            $isFirstTimeStudent = $completedCouncils == 0;

            if ($isFirstTimeStudent) {
                // For first-time students, use the adviser's manual selection
                $lengthOfServiceScore = $validated['length_of_service'];
            } else {
                // For experienced students, automatically calculate
                $lengthOfServiceScore = $evaluatedStudent->calculateLengthOfService();
            }

            $responses[] = [
                'section_name' => 'Length of Service',
                'question' => 'Length of Service Evaluation',
                'answer' => $lengthOfServiceScore
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

        // Try to calculate scores if all evaluations are ready and instance is finalized
        $evaluationService = new \App\Services\EvaluationService();
        $scoresCalculated = false;
        if ($council->isEvaluationInstanceFinalized()) {
            $scoresCalculated = $evaluationService->calculateScoresIfReady($council);
        }

        // Get evaluation progress
        $progress = $evaluationService->getEvaluationProgress($council);

        $message = $council->isEvaluationInstanceActive()
            ? 'Adviser evaluation saved as draft successfully! You can edit this evaluation until the instance is finalized.'
            : 'Adviser evaluation submitted successfully!';

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

    /**
     * Show the evaluation edit form for a specific student
     */
    public function edit(Council $council, Student $student)
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

        // Get existing evaluation
        $evaluation = Evaluation::where('council_id', $council->id)
            ->where('evaluator_id', $adviser->id)
            ->where('evaluator_type', 'adviser')
            ->where('evaluated_student_id', $student->id)
            ->where('status', 'completed')
            ->first();

        if (!$evaluation) {
            return redirect()->route('adviser.evaluation.show', [$council, $student])
                ->with('error', 'No completed evaluation found to edit.');
        }

        // Get evaluation questions from config
        $evaluationConfig = config('evaluation_questions.domains');

        // Filter questions for adviser access
        $questions = [];
        foreach ($evaluationConfig as $domainIndex => $domain) {
            $filteredDomain = [
                'name' => $domain['name'],
                'strands' => []
            ];

            foreach ($domain['strands'] as $strandIndex => $strand) {
                $filteredStrand = [
                    'name' => $strand['name'],
                    'questions' => []
                ];

                foreach ($strand['questions'] as $questionIndex => $question) {
                    if (in_array('adviser', $question['access_levels'])) {
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

        // Get existing responses
        $existingResponses = $evaluation->getFormattedResponses();

        return view('evaluation.adviser', compact('council', 'student', 'officer', 'questions', 'evaluation', 'existingResponses'));
    }

    /**
     * Update an existing evaluation
     */
    public function update(Request $request, Evaluation $evaluation)
    {
        $adviser = Auth::user();

        // Check if this evaluation belongs to the current adviser
        if ($evaluation->evaluator_id !== $adviser->id || $evaluation->evaluator_type !== 'adviser') {
            abort(403, 'Unauthorized action.');
        }

        // Check if evaluation can be edited
        if (!$evaluation->canBeEdited()) {
            return redirect()->route('adviser.councils.show', $evaluation->council)
                ->with('error', 'This evaluation cannot be edited.');
        }

        // Get evaluation questions from config
        $evaluationConfig = config('evaluation_questions.domains');

        // Generate dynamic validation rules
        $validationRules = [
            'council_id' => 'required|exists:councils,id',
            'evaluated_student_id' => 'required|exists:students,id',
            'evaluator_type' => 'required|in:adviser',
            'length_of_service' => 'required|numeric|in:0.00,1.00,2.00,3.00', // Required for first-time students, calculated for others
        ];

        // Generate validation rules from config
        foreach ($evaluationConfig as $domainIndex => $domain) {
            foreach ($domain['strands'] as $strandIndex => $strand) {
                foreach ($strand['questions'] as $questionIndex => $question) {
                    if (in_array('adviser', $question['access_levels'])) {
                        $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                        $validationRules[$fieldName] = 'required|numeric|in:0.00,1.00,2.00,3.00';
                    }
                }
            }
        }

        $validated = $request->validate($validationRules);

        $council = Council::findOrFail($validated['council_id']);

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($validated, $adviser, $evaluation) {
            // Update the evaluation record
            $evaluation->update([
                'submitted_at' => now(),
            ]);

            // Delete existing evaluation forms for this evaluation
            EvaluationForm::where('evaluation_id', $evaluation->id)->delete();

            // Generate dynamic responses from config
            $responses = [];
            $evaluationConfig = config('evaluation_questions.domains');

            foreach ($evaluationConfig as $domainIndex => $domain) {
                foreach ($domain['strands'] as $strandIndex => $strand) {
                    foreach ($strand['questions'] as $questionIndex => $question) {
                        if (in_array('adviser', $question['access_levels'])) {
                            $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                            if (isset($validated[$fieldName])) {
                                $responses[] = [
                                    'section_name' => $fieldName,
                                    'question' => $question['text'],
                                    'answer' => $validated[$fieldName],
                                ];
                            }
                        }
                    }
                }
            }

            // Add length of service response (conditional logic)
            $evaluatedStudent = \App\Models\Student::findOrFail($validated['evaluated_student_id']);
            $completedCouncils = $evaluatedStudent->getCompletedCouncilsCount();
            $isFirstTimeStudent = $completedCouncils == 0;

            if ($isFirstTimeStudent) {
                // For first-time students, use the adviser's manual selection
                $lengthOfServiceScore = $validated['length_of_service'];
            } else {
                // For experienced students, automatically calculate
                $lengthOfServiceScore = $evaluatedStudent->calculateLengthOfService();
            }

            $responses[] = [
                'section_name' => 'length_of_service',
                'question' => 'Length of Service',
                'answer' => $lengthOfServiceScore,
            ];

            // Store the responses
            foreach ($responses as $response) {
                EvaluationForm::create([
                    'evaluation_id' => $evaluation->id,
                    'section_name' => $response['section_name'],
                    'question' => $response['question'],
                    'answer' => $response['answer'],
                ]);
            }
        });

        // Try to calculate scores if all evaluations are ready and instance is finalized
        $evaluationService = new \App\Services\EvaluationService();
        $scoresCalculated = false;
        if ($council->isEvaluationInstanceFinalized()) {
            $scoresCalculated = $evaluationService->calculateScoresIfReady($council);
        }

        // Get evaluation progress
        $progress = $evaluationService->getEvaluationProgress($council);

        $message = $council->isEvaluationInstanceActive()
            ? 'Adviser evaluation draft updated successfully! You can continue editing until the instance is finalized.'
            : 'Adviser evaluation updated successfully!';

        if ($scoresCalculated) {
            $message .= ' All evaluations are now complete and scores have been recalculated.';

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
