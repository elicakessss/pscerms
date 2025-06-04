@extends('adviser.layout')

@section('title', 'Leadership Evaluation - PSCERMS')

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
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-lg">
                            {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($student->last_name ?? 'T', 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $student->first_name ?? 'Sample' }} {{ $student->last_name ?? 'Student' }}</h3>
                        <p class="text-sm text-gray-600">Student ID: {{ $student->student_id ?? '2021-12345' }}</p>
                        <p class="text-sm text-gray-600">Position: {{ $officer->position_title ?? 'President' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <form action="{{ route('adviser.evaluation.adviser_store') }}" method="POST" id="evaluationForm">
        @csrf
        <input type="hidden" name="council_id" value="{{ $council->id ?? 1 }}">
        <input type="hidden" name="evaluated_student_id" value="{{ $student->id ?? 1 }}">
        <input type="hidden" name="evaluator_type" value="adviser">

        @foreach($questions as $domainIndex => $domain)
            <!-- Domain Section -->
            <div class="bg-white rounded-lg shadow-sm border mb-6">
                <div class="bg-green-600 text-white p-4 rounded-t-lg">
                    <h2 class="text-lg font-semibold">{{ $domain['name'] }}</h2>
                </div>

                @foreach($domain['strands'] as $strandIndex => $strand)
                    <!-- Strand Section -->
                    <div class="border-b border-gray-200 last:border-b-0">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="text-md font-medium text-gray-800">{{ $strand['name'] }}</h3>
                        </div>

                        <div class="p-4 space-y-6">
                            @foreach($strand['questions'] as $questionIndex => $question)
                                <!-- Question -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        {{ $domainIndex + 1 }}.{{ $strandIndex + 1 }}.{{ $questionIndex + 1 }} {{ $question['text'] }}
                                    </label>

                                    <div class="space-y-2">
                                        @foreach($question['rating_options'] as $option)
                                            <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                                <input type="radio"
                                                       name="domain{{ $domainIndex + 1 }}_strand{{ $strandIndex + 1 }}_q{{ $questionIndex + 1 }}"
                                                       value="{{ $option['value'] }}.00"
                                                       class="mt-1 text-green-600 focus:ring-green-500"
                                                       required>
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $option['label'] }}
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <!-- Length of Service Section -->
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="bg-green-600 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">Length of Service Evaluation</h2>
            </div>

            <div class="p-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Length of Service Evaluation
                    </label>

                    <div class="space-y-2">
                        <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="length_of_service" value="0.00" class="mt-1 text-green-600 focus:ring-green-500" required>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">Less than 6 months (0.00)</div>
                            </div>
                        </label>
                        <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="length_of_service" value="1.00" class="mt-1 text-green-600 focus:ring-green-500" required>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">6 months to 1 year (1.00)</div>
                            </div>
                        </label>
                        <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="length_of_service" value="2.00" class="mt-1 text-green-600 focus:ring-green-500" required>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">1 to 2 years (2.00)</div>
                            </div>
                        </label>
                        <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="length_of_service" value="3.00" class="mt-1 text-green-600 focus:ring-green-500" required>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">More than 2 years (3.00)</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex justify-between items-center">
                <a href="{{ route('adviser.councils.show', $council) }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Council
                </a>
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-save mr-2"></i>Submit Evaluation
                </button>
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
