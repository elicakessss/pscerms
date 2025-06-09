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
                @if($council->isEvaluationInstanceActive())
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <i class="fas fa-edit mr-1"></i>
                        Draft Mode - Can be edited until finalized
                    </div>
                @elseif($council->isEvaluationInstanceFinalized())
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-lock mr-1"></i>
                        Finalized - Cannot be edited
                    </div>
                @endif
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
    <form action="{{ isset($evaluation) ? route('adviser.evaluation.adviser_update', $evaluation) : route('adviser.evaluation.adviser_store') }}" method="POST" id="evaluationForm">
        @csrf
        @if(isset($evaluation))
            @method('PUT')
        @endif
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
                                            @php
                                                $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                                                $storedValue = isset($existingResponses[$fieldName]) ? trim((string)$existingResponses[$fieldName]) : '';
                                                $optionValue = trim((string)($option['value'] . '.00'));
                                                $isChecked = $storedValue !== '' && $storedValue === $optionValue;
                                            @endphp
                                            <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                                <input type="radio"
                                                       name="domain{{ $domainIndex + 1 }}_strand{{ $strandIndex + 1 }}_q{{ $questionIndex + 1 }}"
                                                       value="{{ $option['value'] }}.00"
                                                       class="mt-1 text-green-600 focus:ring-green-500"
                                                       {{ $isChecked ? 'checked' : '' }}
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
            <div class="p-6">
                @php
                    $completedCouncils = $student->getCompletedCouncilsCount();
                    $isFirstTimeStudent = $completedCouncils == 0;
                @endphp

                @if($isFirstTimeStudent)
                    <!-- Manual selection for first-time students -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-yellow-800 mb-2">First-Time Council Participant</h3>
                                <p class="text-sm text-yellow-700">
                                    This student is participating in their first council. Please determine if they have successfully finished their current term.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Did this student successfully finish their current council term?
                        </label>

                        <div class="space-y-2">
                            @php
                                $lengthOfServiceValue = isset($existingResponses['length_of_service']) ? trim((string)$existingResponses['length_of_service']) : '';
                            @endphp

                            <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="length_of_service" value="0.00" class="mt-1 text-green-600 focus:ring-green-500" {{ $lengthOfServiceValue === '0.00' ? 'checked' : '' }} required>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">No - Did not finish their term (0.00)</div>
                                    <div class="text-xs text-gray-500">Student did not successfully complete their first council term</div>
                                </div>
                            </label>

                            <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="length_of_service" value="1.00" class="mt-1 text-green-600 focus:ring-green-500" {{ $lengthOfServiceValue === '1.00' ? 'checked' : '' }} required>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">Yes - Finished one term (1.00)</div>
                                    <div class="text-xs text-gray-500">Student successfully completed their first council term</div>
                                </div>
                            </label>
                        </div>
                    </div>
                @else
                    <!-- Automatic calculation for experienced students -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-600 mt-1"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">Automatic Length of Service Calculation</h3>
                                <p class="text-sm text-blue-700 mb-3">
                                    This student has previous council experience. Their length of service is automatically calculated based on completed council terms.
                                </p>
                                <div class="bg-white rounded-lg p-3 border border-blue-200">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Student's Length of Service:</span>
                                        <span class="text-lg font-semibold text-green-600">
                                            {{ $student->getLengthOfServiceDescription() }}
                                        </span>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        Based on {{ $completedCouncils }} completed council term(s)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Hidden field to store the calculated length of service -->
                    <input type="hidden" name="length_of_service" value="{{ $student->calculateLengthOfService() }}">
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex justify-between items-center">
                <a href="{{ route('adviser.councils.show', $council) }}"
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Council
                </a>
                @if($council->isEvaluationInstanceFinalized())
                    <span class="bg-gray-400 text-white px-6 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                        <i class="fas fa-lock mr-2"></i>Evaluation Finalized
                    </span>
                @else
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        @if($council->isEvaluationInstanceActive())
                            <i class="fas fa-save mr-2"></i>{{ isset($evaluation) ? 'Update Draft' : 'Save as Draft' }}
                        @else
                            <i class="fas fa-save mr-2"></i>{{ isset($evaluation) ? 'Update Evaluation' : 'Submit Evaluation' }}
                        @endif
                    </button>
                @endif
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
        const isEditing = form.querySelector('input[name="_method"]') !== null;
        const action = isEditing ? 'update' : 'submit';
        const message = `Are you sure you want to ${action} this evaluation?`;

        if (confirm(message)) {
            form.submit();
        }
    });
});
</script>

@endsection
