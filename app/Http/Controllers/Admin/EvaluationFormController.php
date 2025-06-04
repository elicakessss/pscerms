<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class EvaluationFormController extends Controller
{
    /**
     * Display a listing of evaluation questions grouped by domain and strand.
     */
    public function index(Request $request)
    {
        $evaluationConfig = config('evaluation_questions.domains');
        $search = $request->get('search');
        $domainFilter = $request->get('domain');

        // Filter questions based on search and domain
        $filteredQuestions = collect($evaluationConfig)->filter(function($domain) use ($search, $domainFilter) {
            if ($domainFilter && $domain['name'] !== $domainFilter) {
                return false;
            }

            if ($search) {
                $searchLower = strtolower($search);
                // Search in domain name, strand names, and question texts
                $domainMatch = str_contains(strtolower($domain['name']), $searchLower);
                $strandMatch = collect($domain['strands'])->some(function($strand) use ($searchLower) {
                    return str_contains(strtolower($strand['name']), $searchLower);
                });
                $questionMatch = collect($domain['strands'])->some(function($strand) use ($searchLower) {
                    return collect($strand['questions'])->some(function($question) use ($searchLower) {
                        return str_contains(strtolower($question['text']), $searchLower);
                    });
                });

                return $domainMatch || $strandMatch || $questionMatch;
            }

            return true;
        });

        // Get all domains for filter dropdown
        $domains = collect($evaluationConfig)->pluck('name');

        // Calculate statistics
        $totalQuestions = 0;
        $adviserQuestions = 0;
        $peerQuestions = 0;
        $selfQuestions = 0;

        foreach ($evaluationConfig as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    $totalQuestions++;
                    if (in_array('adviser', $question['access_levels'])) $adviserQuestions++;
                    if (in_array('peer', $question['access_levels'])) $peerQuestions++;
                    if (in_array('self', $question['access_levels'])) $selfQuestions++;
                }
            }
        }

        $stats = [
            'total_questions' => $totalQuestions,
            'total_domains' => count($evaluationConfig),
            'adviser_questions' => $adviserQuestions,
            'peer_questions' => $peerQuestions,
            'self_questions' => $selfQuestions,
        ];

        return view('admin.evaluation_forms.index', compact('filteredQuestions', 'domains', 'stats'));
    }

    /**
     * Show the form for editing the evaluation questions configuration.
     */
    public function edit()
    {
        $questions = config('evaluation_questions.domains');
        return view('admin.evaluation_forms.edit', compact('questions'));
    }

    /**
     * Update the evaluation questions configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'domains' => 'required|array|min:1',
            'domains.*.name' => 'required|string|max:255',
            'domains.*.strands' => 'required|array|min:1',
            'domains.*.strands.*.name' => 'required|string|max:255',
            'domains.*.strands.*.questions' => 'required|array|min:1',
            'domains.*.strands.*.questions.*.text' => 'required|string',
            'domains.*.strands.*.questions.*.access_levels' => 'required|array|min:1',
            'domains.*.strands.*.questions.*.access_levels.*' => 'required|in:adviser,peer,self',
            'domains.*.strands.*.questions.*.rating_options' => 'required|array|size:4',
            'domains.*.strands.*.questions.*.rating_options.*.value' => 'required|integer|min:0|max:3',
            'domains.*.strands.*.questions.*.rating_options.*.label' => 'required|string|max:255',
        ]);

        try {
            // Build the configuration array from form data
            $configData = [];

            foreach ($request->domains as $domainIndex => $domainData) {
                $domain = [
                    'name' => $domainData['name'],
                    'strands' => []
                ];

                foreach ($domainData['strands'] as $strandIndex => $strandData) {
                    $strand = [
                        'name' => $strandData['name'],
                        'questions' => []
                    ];

                    foreach ($strandData['questions'] as $questionIndex => $questionData) {
                        $question = [
                            'text' => $questionData['text'],
                            'access_levels' => $questionData['access_levels'] ?? ['adviser'], // Use submitted access levels
                            'rating_options' => []
                        ];

                        foreach ($questionData['rating_options'] as $optionData) {
                            $question['rating_options'][] = [
                                'value' => (int) $optionData['value'],
                                'label' => $optionData['label']
                            ];
                        }

                        $strand['questions'][] = $question;
                    }

                    $domain['strands'][] = $strand;
                }

                $configData[] = $domain;
            }

            // Save to config file
            $configPath = config_path('evaluation_questions.php');
            $configFileContent = "<?php\n\nreturn [\n    'domains' => " . var_export($configData, true) . "\n];\n";
            File::put($configPath, $configFileContent);

            // Clear config cache
            \Artisan::call('config:clear');

            return redirect()->route('admin.evaluation_forms.index')
                ->with('success', 'Evaluation questions updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['domains' => 'Error saving configuration: ' . $e->getMessage()]);
        }
    }

    /**
     * Preview evaluation form for different user types
     */
    public function preview(Request $request)
    {
        $evaluatorType = $request->get('type', 'adviser');
        $evaluationConfig = config('evaluation_questions.domains');

        // Filter questions based on evaluator type
        $filteredQuestions = [];
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
                $filteredQuestions[] = $filteredDomain;
            }
        }

        return view('admin.evaluation_forms.preview', compact('filteredQuestions', 'evaluatorType'));
    }
}
