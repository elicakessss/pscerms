@extends('adviser.layout')

@section('title', 'Student Evaluation')
@section('page-title', 'Student Evaluation')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Leadership Evaluation Form</h1>
                <p class="text-gray-600 text-sm mt-1">Evaluate student leadership performance</p>
                <p class="text-blue-600 text-sm mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Adviser Evaluation: 50% | Length of Service: 15% (evaluated separately)
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Academic Year: {{ $council->academic_year ?? '2024-2025' }}</p>
                <p class="text-sm text-gray-600">Council: {{ $council->name ?? 'Student Council' }}</p>
            </div>
        </div>

        <!-- Student Information -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    @if($student->profile_picture ?? false)
                        <img src="{{ asset('storage/' . $student->profile_picture) }}"
                             alt="{{ $student->first_name }}"
                             class="w-12 h-12 rounded-full object-cover">
                    @else
                        <i class="fas fa-user text-green-600 text-lg"></i>
                    @endif
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $student->first_name ?? 'John' }} {{ $student->last_name ?? 'Doe' }}</h3>
                    <p class="text-sm text-gray-600">{{ $student->id_number ?? 'ID-2024-001' }}</p>
                    <p class="text-sm text-gray-600">Position: {{ $officer->position_title ?? 'President' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <form action="{{ route('adviser.evaluation.store') }}" method="POST" id="evaluationForm">
        @csrf
        <input type="hidden" name="council_id" value="{{ $council->id ?? 1 }}">
        <input type="hidden" name="evaluated_student_id" value="{{ $student->id ?? 1 }}">
        <input type="hidden" name="evaluator_type" value="adviser">

        <!-- Domain 1: Paulinian Leadership as Social Responsibility -->
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="bg-green-600 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">Domain 1: Paulinian Leadership as Social Responsibility</h2>
            </div>

            <!-- Strand 1 -->
            <div class="p-6 border-b">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 1: The Paulinian Leader submits himself/herself to professional growth and development</h3>

                <!-- Question 1.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.1 The Paulinian Leader organizes/co-organizes and/or serves as resource speaker in seminars and activities for the organization
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has organized/co-organized more than two seminars/activities (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q1" value="2.00" class="mr-2">
                            <span class="text-sm">Has organized/co-organized two seminars/activities (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Has organized/co-organized one seminar/activity (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q1" value="0.00" class="mr-2">
                            <span class="text-sm">Has not organized/co-organized seminars/activities (0.00)</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.2 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.2 The Paulinian Leader facilitates/co-facilitates seminars and activities for the organization
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q2" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has facilitated/co-facilitated more than two seminars/activities (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q2" value="2.00" class="mr-2">
                            <span class="text-sm">Has facilitated/co-facilitated two seminars/activities (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q2" value="1.00" class="mr-2">
                            <span class="text-sm">Has facilitated/co-facilitated one seminar/activity (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q2" value="0.00" class="mr-2">
                            <span class="text-sm">Has not facilitated/co-facilitated seminars/activities (0.00)</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.3 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.3 The Paulinian Leader participates in seminars and activities of the organization
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q3" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has participated in more than four seminars/activities (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q3" value="2.00" class="mr-2">
                            <span class="text-sm">Has participated in three to four seminars/activities (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q3" value="1.00" class="mr-2">
                            <span class="text-sm">Has participated in one to two seminars/activities (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q3" value="0.00" class="mr-2">
                            <span class="text-sm">Has not participated in any seminars/activities (0.00)</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.4 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.4 The Paulinian Leader attends to SPUP-organized seminars and activities related to the organization
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q4" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has attended to more than four seminars/activities (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q4" value="2.00" class="mr-2">
                            <span class="text-sm">Has attended to three to four seminars/activities (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q4" value="1.00" class="mr-2">
                            <span class="text-sm">Has attended to one to two seminars/activities (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand1_q4" value="0.00" class="mr-2">
                            <span class="text-sm">Has not attended to any seminars/activities (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Strand 2 -->
            <div class="p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 2: The Paulinian Leader is quality result-oriented</h3>

                <!-- Question 2.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        2.1 The Paulinian Leader ensures quality in all tasks/assignments given
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand2_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">Performs outstanding/excellent on the tasks/assignments (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand2_q1" value="2.00" class="mr-2">
                            <span class="text-sm">Performs very satisfactory on the tasks/assignments (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand2_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Performs satisfactory on the tasks/assignments (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain1_strand2_q1" value="0.00" class="mr-2">
                            <span class="text-sm">Performs but needs improvement on the tasks/assignments (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Domain 2: Paulinian Leadership as a Life of Service -->
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="bg-green-600 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">Domain 2: Paulinian Leadership as a Life of Service</h2>
            </div>

            <!-- Strand 1 -->
            <div class="p-6 border-b">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 1: The Paulinian Leader serves the organization, its members, and the university</h3>

                <!-- Question 1.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.1 The Paulinian Leader: a) performs related tasks outside the given assignment, b) initiates actions to solve issues among students and those that concern the organization/university, and c) participates in the aftercare during activities
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand1_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">All three indicators are met (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand1_q1" value="2.00" class="mr-2">
                            <span class="text-sm">Only two of the given indicators are met (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand1_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Only one of the given indicators is met (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand1_q1" value="0.00" class="mr-2">
                            <span class="text-sm">None of the indicators is met (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Strand 2 -->
            <div class="p-6 border-b">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 2: The Paulinian Leader actively participates in the activities of the organization and university</h3>

                <!-- Question 2.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        2.1 The Paulinian Leader shares in the organization's management and evaluation of the organization
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has participated in three or more varied organizational activities (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q1" value="2.00" class="mr-2">
                            <span class="text-sm">Has participated in two varied organizational activities (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Has participated in only one organizational activity (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q1" value="0.00" class="mr-2">
                            <span class="text-sm">Has not participated in any organizational activity (0.00)</span>
                        </label>
                    </div>
                </div>

                <!-- Question 2.2 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        2.2 The Paulinian Leader shares in the organization, management, and evaluation of projects/activities of the university
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q2" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has participated in three or more varied university activities (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q2" value="2.00" class="mr-2">
                            <span class="text-sm">Has participated in two varied university activities (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q2" value="1.00" class="mr-2">
                            <span class="text-sm">Has participated in only one university activity (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand2_q2" value="0.00" class="mr-2">
                            <span class="text-sm">Has not participated in any university activity (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Strand 3 -->
            <div class="p-6 border-b">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 3: The Paulinian Leader shows utmost commitment by participating in related activities</h3>

                <!-- Question 3.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        3.1 The Paulinian Leader attends regular meetings
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has attended 100% of regular meetings (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q1" value="2.00" class="mr-2">
                            <span class="text-sm">Has attended 90%-99% of regular meetings (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Has attended 80-89% of regular meetings (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q1" value="0.00" class="mr-2">
                            <span class="text-sm">Has attended less 79% of regular meetings (0.00)</span>
                        </label>
                    </div>
                </div>

                <!-- Question 3.2 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        3.2 The Paulinian Leader attends special/emergency meetings
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q2" value="3.00" class="mr-2" required>
                            <span class="text-sm">Has attended 90%-100% of all the meetings called (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q2" value="2.00" class="mr-2">
                            <span class="text-sm">Has attended 80%-89% of all the meetings called (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q2" value="1.00" class="mr-2">
                            <span class="text-sm">Has attended 70%-79% of all the meetings called (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain2_strand3_q2" value="0.00" class="mr-2">
                            <span class="text-sm">Has attended less than 70% of all meetings called (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Length of Service (15% Weight - Separate Component) -->
            <div class="p-6 bg-blue-50 border-t-2 border-blue-200">
                <h3 class="font-semibold text-gray-800 mb-2">Length of Service Evaluation</h3>
                <p class="text-sm text-blue-600 mb-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    This section is weighted separately at 15% and is independent from the adviser evaluation score (50%)
                </p>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Length of Service Assessment
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="length_of_service" value="3.00" class="mr-2" required>
                            <span class="text-sm">3 years and up (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="length_of_service" value="2.00" class="mr-2">
                            <span class="text-sm">2 years (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="length_of_service" value="1.00" class="mr-2">
                            <span class="text-sm">1 year (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="length_of_service" value="0.00" class="mr-2">
                            <span class="text-sm">Did not finish the term (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Domain 3: Paulinian Leader as Leading by Example (Discipline/Decorum) -->
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="bg-green-600 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">Domain 3: Paulinian Leader as Leading by Example (Discipline/Decorum)</h2>
            </div>

            <!-- Strand 1 -->
            <div class="p-6 border-b">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 1: The Paulinian Leader is a model of grooming and proper decorum</h3>

                <!-- Question 1.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.1 The Paulinian Leader: a) wears the correct uniform with its prescribed accessories (shoes, ID strap, undergarment, and bag), b) wears ID at all times while on campus, c) observes Silence Policy on corridors/offices, d) shows courtesy to the SPUP community, e) shows warmth and respect to visitors and guests of the University, f) models prescribed haircut (male) or hairstyle and accessories (female), and g) exhibits punctuality during meeting and activities
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">All seven indicators are met (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q1" value="2.00" class="mr-2">
                            <span class="text-sm">All first three indicators and any two of the remaining indicators are met (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Only the first three indicators are met (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q1" value="0.00" class="mr-2">
                            <span class="text-sm">Any of the indicators are not met (0.00)</span>
                        </label>
                    </div>
                </div>

                <!-- Question 1.2 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        1.2 The Paulinian Leader submits reports regularly
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q2" value="3.00" class="mr-2" required>
                            <span class="text-sm">Complete and before deadline (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q2" value="2.00" class="mr-2">
                            <span class="text-sm">Complete and on the deadline (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q2" value="1.00" class="mr-2">
                            <span class="text-sm">Complete but after deadline/incomplete but on the deadline (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand1_q2" value="0.00" class="mr-2">
                            <span class="text-sm">Incomplete and after the deadline/have not submitted any reports (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Strand 2 -->
            <div class="p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Strand 2: The Paulinian Leader complies with the Environmental Stewardship of the university</h3>

                <!-- Question 2.1 -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        2.1 The Paulinian Leader ensures cleanliness and orderliness of office/workplace
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand2_q1" value="3.00" class="mr-2" required>
                            <span class="text-sm">Clean beyond schedule and without being told (3.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand2_q1" value="2.00" class="mr-2">
                            <span class="text-sm">Cleans only on schedule upon a command (2.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand2_q1" value="1.00" class="mr-2">
                            <span class="text-sm">Joins cleaning but comes in late (1.00)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="domain3_strand2_q1" value="0.00" class="mr-2">
                            <span class="text-sm">Never cleans at all (0.00)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Submit Evaluation</h3>
                    <p class="text-sm text-gray-600 mt-1">Please review all responses before submitting</p>
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="window.history.back()"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        Submit Evaluation
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('evaluationForm');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Check if all required fields are filled
        const requiredFields = form.querySelectorAll('input[required]');
        let allFilled = true;

        requiredFields.forEach(field => {
            const name = field.getAttribute('name');
            const checked = form.querySelector(`input[name="${name}"]:checked`);
            if (!checked) {
                allFilled = false;
            }
        });

        if (!allFilled) {
            alert('Please answer all evaluation questions before submitting.');
            return;
        }

        // Confirm submission
        if (confirm('Are you sure you want to submit this evaluation? You will not be able to edit it after submission.')) {
            form.submit();
        }
    });
});
</script>

@endsection
