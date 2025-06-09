@extends(auth()->guard('student')->check() ? 'student.layout' : 'adviser.layout')

@section('title', 'View Evaluation - PSCERMS')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">View {{ ucfirst($evaluation->evaluator_type) }} Evaluation</h1>
                <p class="text-gray-600 text-sm mt-1">Read-only view of submitted evaluation</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Academic Year: {{ $evaluation->council->academic_year }}</p>
                <p class="text-sm text-gray-600">Council: {{ $evaluation->council->name }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $evaluation->getStatusBadgeClass() }}">
                    {{ $evaluation->getStatusDisplayText() }}
                </span>
            </div>
        </div>

        <!-- Student Information -->
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-lg">
                            {{ strtoupper(substr($evaluation->evaluatedStudent->first_name, 0, 1)) }}{{ strtoupper(substr($evaluation->evaluatedStudent->last_name, 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ $evaluation->evaluatedStudent->first_name }} {{ $evaluation->evaluatedStudent->last_name }}</h3>
                        <p class="text-sm text-gray-600">Student ID: {{ $evaluation->evaluatedStudent->id_number }}</p>
                        <p class="text-sm text-gray-600">Position: {{ $officer->position_title ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Submitted: {{ $evaluation->submitted_at ? $evaluation->submitted_at->format('M d, Y g:i A') : 'Not submitted' }}</p>
                    <p class="text-sm text-gray-600">Evaluator: {{ $evaluation->evaluator_type === 'self' ? 'Self' : ucfirst($evaluation->evaluator_type) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Responses -->
    @php
        $groupedForms = $evaluation->evaluationForms->groupBy('section_name');
    @endphp

    @foreach($groupedForms as $sectionName => $forms)
        <div class="bg-white rounded-lg shadow-sm border mb-6">
            <div class="bg-green-600 text-white p-4 rounded-t-lg">
                <h2 class="text-lg font-semibold">{{ $sectionName }}</h2>
            </div>

            <div class="p-6 space-y-6">
                @foreach($forms as $form)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            {{ $form->question }}
                        </label>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-blue-600 mr-2"></i>
                                <span class="text-sm font-medium text-blue-900">
                                    @if($sectionName === 'Length of Service')
                                        @switch($form->answer)
                                            @case('0.00')
                                                Did not finish their term (0.00)
                                                @break
                                            @case('1.00')
                                                Finished one term (1.00)
                                                @break
                                            @case('2.00')
                                                Finished two terms (2.00)
                                                @break
                                            @case('3.00')
                                                Finished 3 or more terms (3.00)
                                                @break
                                            @default
                                                {{ $form->answer }}
                                        @endswitch
                                    @else
                                        @switch($form->answer)
                                            @case('0.00')
                                                Needs Improvement (0.00)
                                                @break
                                            @case('1.00')
                                                Satisfactory (1.00)
                                                @break
                                            @case('2.00')
                                                Very Satisfactory (2.00)
                                                @break
                                            @case('3.00')
                                                Outstanding (3.00)
                                                @break
                                            @default
                                                {{ $form->answer }}
                                        @endswitch
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Evaluation Actions</h3>
                <p class="text-sm text-gray-600 mt-1">Available actions for this evaluation</p>
            </div>
            <div class="flex space-x-4">
                <button type="button" onclick="window.history.back()"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </button>

                @if($evaluation->canBeEdited())
                    <a href="{{ route(auth()->guard('student')->check() ? 'student.evaluation.edit' : 'adviser.evaluation.edit', $evaluation) }}"
                       class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Evaluation
                    </a>
                @endif

                @if($evaluation->canBeDeleted())
                    <form method="POST" action="{{ route(auth()->guard('student')->check() ? 'student.evaluation.destroy' : 'adviser.evaluation.destroy', $evaluation) }}"
                          class="inline" onsubmit="return confirm('Are you sure you want to delete this evaluation? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Delete Evaluation
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>



@endsection
