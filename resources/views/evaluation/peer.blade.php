@extends('student.layout')

@section('title', 'Peer Evaluation - PSCERMS')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Leadership Peer Evaluation Form</h1>
                <p class="text-gray-600 text-sm mt-1">Evaluate your peer's leadership performance</p>
                <p class="text-blue-600 text-sm mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Peer Evaluation: 20% | Available sections: Domain 2 (complete) + Domain 3 (complete)
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
    <form action="{{ isset($evaluation) ? route('student.evaluation.student_update', $evaluation) : route('student.evaluation.student_store') }}" method="POST" id="evaluationForm">
        @csrf
        @if(isset($evaluation))
            @method('PUT')
        @endif
        <input type="hidden" name="council_id" value="{{ $council->id ?? 1 }}">
        <input type="hidden" name="evaluated_student_id" value="{{ $student->id ?? 1 }}">
        <input type="hidden" name="evaluator_type" value="peer">

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

        <!-- Form Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        @if($council->isEvaluationInstanceActive())
                            {{ isset($evaluation) ? 'Update Draft' : 'Save as Draft' }}
                        @else
                            {{ isset($evaluation) ? 'Update Peer Evaluation' : 'Submit Peer Evaluation' }}
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        @if($council->isEvaluationInstanceActive())
                            Your responses will be saved as a draft and can be edited until the adviser finalizes the evaluation instance.
                        @else
                            Please review all responses before {{ isset($evaluation) ? 'updating' : 'submitting' }}
                        @endif
                    </p>
                </div>
                <div class="flex space-x-4">
                    <button type="button" onclick="window.history.back()"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    @if($council->isEvaluationInstanceFinalized())
                        <span class="px-6 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed">
                            Evaluation Finalized
                        </span>
                    @else
                        <button type="submit"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            @if($council->isEvaluationInstanceActive())
                                {{ isset($evaluation) ? 'Update Draft' : 'Save as Draft' }}
                            @else
                                {{ isset($evaluation) ? 'Update Peer Evaluation' : 'Submit Peer Evaluation' }}
                            @endif
                        </button>
                    @endif
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
        const isEditing = form.querySelector('input[name="_method"]') !== null;
        const action = isEditing ? 'update' : 'submit';
        const message = `Are you sure you want to ${action} this peer evaluation?`;

        if (confirm(message)) {
            form.submit();
        }
    });
});
</script>

@endsection
