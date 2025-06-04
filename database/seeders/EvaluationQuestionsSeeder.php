<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class EvaluationQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating default evaluation questions configuration...');

        // Define the default evaluation questions structure based on existing blade views
        $evaluationQuestions = [
            'domains' => [
                [
                    'name' => 'Domain 1: Paulinian Leadership as Social Responsibility',
                    'strands' => [
                        [
                            'name' => 'Strand 1: The Paulinian Leader submits himself/herself to professional growth and development',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader organizes/co-organizes and/or serves as resource speaker in seminars and activities for the organization',
                                    'access_levels' => ['adviser'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has not organized/co-organized seminars/activities (0.00)'],
                                        ['value' => 1, 'label' => 'Has organized/co-organized one seminar/activity (1.00)'],
                                        ['value' => 2, 'label' => 'Has organized/co-organized two seminars/activities (2.00)'],
                                        ['value' => 3, 'label' => 'Has organized/co-organized more than two seminars/activities (3.00)']
                                    ]
                                ],
                                [
                                    'text' => 'The Paulinian Leader facilitates/co-facilitates seminars and activities for the organization',
                                    'access_levels' => ['adviser'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has not facilitated/co-facilitated seminars/activities (0.00)'],
                                        ['value' => 1, 'label' => 'Has facilitated/co-facilitated one seminar/activity (1.00)'],
                                        ['value' => 2, 'label' => 'Has facilitated/co-facilitated two seminars/activities (2.00)'],
                                        ['value' => 3, 'label' => 'Has facilitated/co-facilitated more than two seminars/activities (3.00)']
                                    ]
                                ],
                                [
                                    'text' => 'The Paulinian Leader participates in seminars and activities of the organization',
                                    'access_levels' => ['adviser'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has not participated in any seminars/activities (0.00)'],
                                        ['value' => 1, 'label' => 'Has participated in one to two seminars/activities (1.00)'],
                                        ['value' => 2, 'label' => 'Has participated in three to four seminars/activities (2.00)'],
                                        ['value' => 3, 'label' => 'Has participated in more than four seminars/activities (3.00)']
                                    ]
                                ],
                                [
                                    'text' => 'The Paulinian Leader attends to SPUP-organized seminars and activities related to the organization',
                                    'access_levels' => ['adviser'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has not attended to any seminars/activities (0.00)'],
                                        ['value' => 1, 'label' => 'Has attended to one to two seminars/activities (1.00)'],
                                        ['value' => 2, 'label' => 'Has attended to three to four seminars/activities (2.00)'],
                                        ['value' => 3, 'label' => 'Has attended to more than four seminars/activities (3.00)']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Strand 2: The Paulinian Leader is quality result-oriented',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader ensures quality in all tasks/assignments given',
                                    'access_levels' => ['adviser'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Performs but needs improvement on the tasks/assignments (0.00)'],
                                        ['value' => 1, 'label' => 'Performs satisfactory on the tasks/assignments (1.00)'],
                                        ['value' => 2, 'label' => 'Performs very satisfactory on the tasks/assignments (2.00)'],
                                        ['value' => 3, 'label' => 'Performs outstanding/excellent on the tasks/assignments (3.00)']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'Domain 2: Paulinian Leadership as a Life of Service',
                    'strands' => [
                        [
                            'name' => 'Strand 1: The Paulinian Leader serves the organization, its members, and the university',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader: a) performs related tasks outside the given assignment, b) initiates actions to solve issues among students and those that concern the organization/university, and c) participates in the aftercare during activities',
                                    'access_levels' => ['adviser', 'peer', 'self'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'None of the indicators is met (0.00)'],
                                        ['value' => 1, 'label' => 'Only one of the given indicators is met (1.00)'],
                                        ['value' => 2, 'label' => 'Only two of the given indicators are met (2.00)'],
                                        ['value' => 3, 'label' => 'All three indicators are met (3.00)']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Strand 2: The Paulinian Leader actively participates in the activities of the organization and university',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader shares in the organization\'s management and evaluation of the organization',
                                    'access_levels' => ['adviser', 'peer'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has not participated in any organizational activity (0.00)'],
                                        ['value' => 1, 'label' => 'Has participated in only one organizational activity (1.00)'],
                                        ['value' => 2, 'label' => 'Has participated in two varied organizational activities (2.00)'],
                                        ['value' => 3, 'label' => 'Has participated in three or more varied organizational activities (3.00)']
                                    ]
                                ],
                                [
                                    'text' => 'The Paulinian Leader shares in the organization, management, and evaluation of projects/activities of the university',
                                    'access_levels' => ['adviser', 'peer'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has not participated in any university activity (0.00)'],
                                        ['value' => 1, 'label' => 'Has participated in only one university activity (1.00)'],
                                        ['value' => 2, 'label' => 'Has participated in two varied university activities (2.00)'],
                                        ['value' => 3, 'label' => 'Has participated in three or more varied university activities (3.00)']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Strand 3: The Paulinian Leader shows utmost commitment by participating in related activities',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader attends regular meetings',
                                    'access_levels' => ['adviser', 'peer', 'self'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has attended less 79% of regular meetings (0.00)'],
                                        ['value' => 1, 'label' => 'Has attended 80-89% of regular meetings (1.00)'],
                                        ['value' => 2, 'label' => 'Has attended 90%-99% of regular meetings (2.00)'],
                                        ['value' => 3, 'label' => 'Has attended 100% of regular meetings (3.00)']
                                    ]
                                ],
                                [
                                    'text' => 'The Paulinian Leader attends special/emergency meetings',
                                    'access_levels' => ['adviser', 'peer', 'self'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Has attended less than 70% of all meetings called (0.00)'],
                                        ['value' => 1, 'label' => 'Has attended 70%-79% of all the meetings called (1.00)'],
                                        ['value' => 2, 'label' => 'Has attended 80%-89% of all the meetings called (2.00)'],
                                        ['value' => 3, 'label' => 'Has attended 90%-100% of all the meetings called (3.00)']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'Domain 3: Paulinian Leader as Leading by Example (Discipline/Decorum)',
                    'strands' => [
                        [
                            'name' => 'Strand 1: The Paulinian Leader is a model of grooming and proper decorum',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader: a) wears the correct uniform with its prescribed accessories (shoes, ID strap, undergarment, and bag), b) wears ID at all times while on campus, c) observes Silence Policy on corridors/offices, d) shows courtesy to the SPUP community, e) shows warmth and respect to visitors and guests of the University, f) models prescribed haircut (male) or hairstyle and accessories (female), and g) exhibits punctuality during meeting and activities',
                                    'access_levels' => ['adviser', 'peer', 'self'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Any of the indicators are not met (0.00)'],
                                        ['value' => 1, 'label' => 'Only the first three indicators are met (1.00)'],
                                        ['value' => 2, 'label' => 'All first three indicators and any two of the remaining indicators are met (2.00)'],
                                        ['value' => 3, 'label' => 'All seven indicators are met (3.00)']
                                    ]
                                ],
                                [
                                    'text' => 'The Paulinian Leader submits reports regularly',
                                    'access_levels' => ['adviser', 'peer', 'self'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Incomplete and after the deadline/have not submitted any reports (0.00)'],
                                        ['value' => 1, 'label' => 'Complete but after deadline/incomplete but on the deadline (1.00)'],
                                        ['value' => 2, 'label' => 'Complete and on the deadline (2.00)'],
                                        ['value' => 3, 'label' => 'Complete and before deadline (3.00)']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Strand 2: The Paulinian Leader complies with the Environmental Stewardship of the university',
                            'questions' => [
                                [
                                    'text' => 'The Paulinian Leader ensures cleanliness and orderliness of office/workplace',
                                    'access_levels' => ['adviser', 'peer', 'self'],
                                    'rating_options' => [
                                        ['value' => 0, 'label' => 'Never cleans at all (0.00)'],
                                        ['value' => 1, 'label' => 'Joins cleaning but comes in late (1.00)'],
                                        ['value' => 2, 'label' => 'Cleans only on schedule upon a command (2.00)'],
                                        ['value' => 3, 'label' => 'Clean beyond schedule and without being told (3.00)']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // Create the config file
        $configPath = config_path('evaluation_questions.php');
        $configContent = "<?php\n\nreturn " . var_export($evaluationQuestions, true) . ";\n";

        File::put($configPath, $configContent);

        // Clear config cache to ensure the new configuration is loaded
        \Artisan::call('config:clear');

        $this->command->info('✅ Default evaluation questions configuration created successfully!');
        $this->command->info('📊 Created 3 domains with multiple strands and questions:');
        $this->command->info('   • Domain 1: Paulinian Leadership as Social Responsibility (5 questions - adviser only)');
        $this->command->info('   • Domain 2: Paulinian Leadership as a Life of Service (5 questions - mixed access)');
        $this->command->info('   • Domain 3: Paulinian Leader as Leading by Example (3 questions - all evaluators)');
        $this->command->info('🔒 Access levels properly configured for different evaluator types');
        $this->command->info('📝 Questions extracted from existing evaluation blade views for consistency');
    }
}
