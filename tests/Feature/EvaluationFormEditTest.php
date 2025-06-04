<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EvaluationFormEditTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test admin
        $this->admin = Admin::create([
            'id_number' => 'ADMIN001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_admin_can_access_evaluation_forms_edit_page()
    {
        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('admin.evaluation_forms.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.evaluation_forms.edit');
        $response->assertViewHas('questions');
    }

    public function test_admin_can_update_evaluation_questions()
    {
        // Prepare test data
        $formData = [
            'domains' => [
                [
                    'name' => 'Updated Domain 1',
                    'strands' => [
                        [
                            'name' => 'Updated Strand 1',
                            'questions' => [
                                [
                                    'text' => 'Updated question text',
                                    'rating_options' => [
                                        ['value' => 1, 'label' => 'Poor'],
                                        ['value' => 2, 'label' => 'Fair'],
                                        ['value' => 3, 'label' => 'Good'],
                                        ['value' => 4, 'label' => 'Excellent']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->put(route('admin.evaluation_forms.update'), $formData);

        $response->assertRedirect(route('admin.evaluation_forms.index'));
        $response->assertSessionHas('success');

        // Verify the config file was updated
        $this->assertTrue(File::exists(config_path('evaluation_questions.php')));
    }

    public function test_validation_fails_with_invalid_data()
    {
        $invalidData = [
            'domains' => [
                [
                    'name' => '', // Empty name should fail
                    'strands' => []
                ]
            ]
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->put(route('admin.evaluation_forms.update'), $invalidData);

        $response->assertSessionHasErrors();
    }

    public function test_rating_options_must_have_four_values()
    {
        $invalidData = [
            'domains' => [
                [
                    'name' => 'Test Domain',
                    'strands' => [
                        [
                            'name' => 'Test Strand',
                            'questions' => [
                                [
                                    'text' => 'Test question',
                                    'rating_options' => [
                                        ['value' => 1, 'label' => 'Poor'],
                                        ['value' => 2, 'label' => 'Fair']
                                        // Missing 2 options - should fail
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->put(route('admin.evaluation_forms.update'), $invalidData);

        $response->assertSessionHasErrors();
    }

    public function test_config_file_structure_is_correct_after_update()
    {
        $formData = [
            'domains' => [
                [
                    'name' => 'Test Domain 1',
                    'strands' => [
                        [
                            'name' => 'Test Strand 1',
                            'questions' => [
                                [
                                    'text' => 'Test question 1',
                                    'rating_options' => [
                                        ['value' => 1, 'label' => 'Poor'],
                                        ['value' => 2, 'label' => 'Fair'],
                                        ['value' => 3, 'label' => 'Good'],
                                        ['value' => 4, 'label' => 'Excellent']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'name' => 'Test Domain 2',
                    'strands' => [
                        [
                            'name' => 'Test Strand 2.1',
                            'questions' => [
                                [
                                    'text' => 'Test question 2.1',
                                    'rating_options' => [
                                        ['value' => 1, 'label' => 'Never'],
                                        ['value' => 2, 'label' => 'Sometimes'],
                                        ['value' => 3, 'label' => 'Often'],
                                        ['value' => 4, 'label' => 'Always']
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Test Strand 2.2',
                            'questions' => [
                                [
                                    'text' => 'Test question 2.2',
                                    'rating_options' => [
                                        ['value' => 1, 'label' => 'Strongly Disagree'],
                                        ['value' => 2, 'label' => 'Disagree'],
                                        ['value' => 3, 'label' => 'Agree'],
                                        ['value' => 4, 'label' => 'Strongly Agree']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->put(route('admin.evaluation_forms.update'), $formData);

        $response->assertRedirect(route('admin.evaluation_forms.index'));

        // Clear config cache and reload
        \Artisan::call('config:clear');

        // Read the config file directly to verify structure
        $configPath = config_path('evaluation_questions.php');
        $this->assertTrue(File::exists($configPath));

        $configContent = include $configPath;
        $config = $configContent['domains'];

        $this->assertIsArray($config);
        $this->assertCount(2, $config);

        // Check first domain
        $this->assertEquals('Test Domain 1', $config[0]['name']);
        $this->assertCount(1, $config[0]['strands']);
        $this->assertEquals('Test Strand 1', $config[0]['strands'][0]['name']);
        $this->assertCount(1, $config[0]['strands'][0]['questions']);

        // Check access levels are properly set (Domain 1 should be adviser only)
        $this->assertEquals(['adviser'], $config[0]['strands'][0]['questions'][0]['access_levels']);

        // Check second domain, second strand (should be adviser + peer only)
        $this->assertEquals(['adviser', 'peer'], $config[1]['strands'][1]['questions'][0]['access_levels']);

        // Check rating options structure
        $ratingOptions = $config[0]['strands'][0]['questions'][0]['rating_options'];
        $this->assertCount(4, $ratingOptions);
        $this->assertEquals(1, $ratingOptions[0]['value']);
        $this->assertEquals('Poor', $ratingOptions[0]['label']);
    }
}
