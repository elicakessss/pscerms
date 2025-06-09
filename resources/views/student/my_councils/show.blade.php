@extends('student.layout')

@section('title', 'Council Details - PSCERMS')
@section('page-title', 'Council Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Council Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('student.councils.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Councils
        </a>
    </div>
</div>

<!-- Success Message -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="md:flex">
        <!-- Council Icon and Basic Info -->
        <div class="md:w-1/3 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex flex-col items-center text-center">
                <!-- Council Icon -->
                <div class="h-32 w-32 rounded-full bg-green-100 flex items-center justify-center mb-4">
                    <i class="fas fa-users text-green-600 text-5xl"></i>
                </div>

                <h2 class="text-xl font-bold text-gray-800">{{ $council->name }}</h2>
                <p class="text-gray-600 mb-2">{{ $council->academic_year }}</p>

                <div class="mb-4">
                    @if($council->status === 'active')
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                            Completed
                        </span>
                    @endif
                </div>

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium mb-4">
                    {{ $council->department->abbreviation }} - {{ $council->department->name }}
                </div>

                <!-- My Position -->
                <div class="w-full p-3 bg-blue-50 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">My Position</h4>
                    <p class="text-sm font-semibold text-blue-900">{{ $studentOfficer->position_title }}</p>
                    @if($studentOfficer->position_level <= 2)
                        <p class="text-xs text-blue-600">Leadership Position</p>
                    @else
                        <p class="text-xs text-blue-600">Level {{ $studentOfficer->position_level }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Council Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Council Information</h3>

            <div class="space-y-4">
                <!-- Adviser Information -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Council Adviser</h4>
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            @if($council->adviser->profile_picture)
                                <img src="{{ asset('storage/' . $council->adviser->profile_picture) }}"
                                     alt="{{ $council->adviser->first_name }}"
                                     class="h-8 w-8 rounded-full object-cover">
                            @else
                                <i class="fas fa-chalkboard-teacher text-blue-600"></i>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-800 font-medium">{{ $council->adviser->first_name }} {{ $council->adviser->last_name }}</p>
                            <p class="text-sm text-gray-500">{{ $council->adviser->email }}</p>
                            <p class="text-sm text-gray-500">{{ $council->adviser->department->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Council Stats -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Council Statistics</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-2 bg-green-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-users text-green-600 mr-2 text-sm"></i>
                                <span class="text-xs font-medium text-gray-700">Total Officers</span>
                            </div>
                            <span class="text-sm font-bold text-green-600">{{ $officers->count() }}</span>
                        </div>
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-calendar text-blue-600 mr-2 text-sm"></i>
                                <span class="text-xs font-medium text-gray-700">Academic Year</span>
                            </div>
                            <span class="text-sm font-bold text-blue-600">{{ $council->academic_year }}</span>
                        </div>
                    </div>
                </div>

                <!-- Created & Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                        <p class="text-gray-800">{{ $council->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                        <p class="text-gray-800">{{ $council->updated_at->format('F j, Y, g:i a') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Actions</h4>
                    <div class="flex space-x-3">
                        <a href="mailto:{{ $council->adviser->email }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-envelope mr-2"></i> Email Adviser
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Council Officers -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-green-600 mr-2"></i>
            Council Officers
        </h3>

        @if($officers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($officers as $officer)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow {{ $officer->student_id === auth()->user()->id ? 'ring-2 ring-blue-500 bg-blue-50' : '' }}">
                        <!-- Officer Header -->
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-12 w-12 mr-3">
                                @if($officer->student->profile_picture)
                                    <img class="h-12 w-12 rounded-full object-cover"
                                         src="{{ asset('storage/' . $officer->student->profile_picture) }}"
                                         alt="{{ $officer->student->first_name }}">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">
                                    {{ $officer->student->first_name }} {{ $officer->student->last_name }}
                                    @if($officer->student_id === auth()->user()->id)
                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            You
                                        </span>
                                    @endif
                                </h4>
                                <p class="text-xs text-gray-500">{{ $officer->student->id_number }}</p>
                            </div>
                        </div>

                        <!-- Position and Level -->
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-700">{{ $officer->position_title }}</p>
                            <div class="mt-1">
                                @if($officer->position_level <= 2)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Leadership Position
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Level {{ $officer->position_level }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Department and Contact -->
                        <div class="space-y-2">
                            @if($officer->student->department)
                                <div class="flex items-center text-xs text-gray-500">
                                    <i class="fas fa-building mr-1"></i>
                                    {{ $officer->student->department->name }}
                                    ({{ $officer->student->department->abbreviation }})
                                </div>
                            @endif
                            <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                <span class="text-xs text-gray-500">{{ $officer->student->email }}</span>
                                <a href="mailto:{{ $officer->student->email }}"
                                   class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-envelope text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-green-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Officers Assigned</h4>
                <p class="text-gray-400">Council officers will appear here once they are assigned by the adviser.</p>
            </div>
        @endif
    </div>
</div>

<!-- Evaluations Section -->
@if($council->hasEvaluations())
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-line text-green-600 mr-2"></i>
            My Evaluations
        </h3>

        <div class="space-y-4">
            @php
                $student = auth()->user();

                // Get self-evaluation
                $selfEvaluation = \App\Models\Evaluation::where('council_id', $council->id)
                    ->where('evaluator_id', $student->id)
                    ->where('evaluator_type', 'self')
                    ->where('evaluated_student_id', $student->id)
                    ->first();

                // Get peer evaluations where this student is the evaluator
                $peerEvaluations = \App\Models\Evaluation::where('council_id', $council->id)
                    ->where('evaluator_id', $student->id)
                    ->where('evaluator_type', 'peer')
                    ->with('evaluatedStudent')
                    ->get();
            @endphp

            <!-- Self Evaluation -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-gray-800">Self Evaluation</h4>
                        <p class="text-sm text-gray-600">Evaluate your own leadership performance</p>
                    </div>
                    <div>
                        @if($selfEvaluation && $selfEvaluation->status === 'completed')
                            <a href="{{ route('student.evaluation.self.edit', $council) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </a>
                        @elseif($selfEvaluation)
                            <a href="{{ route('student.evaluation.self', $council) }}"
                               class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Evaluate
                            </a>
                        @else
                            <span class="text-gray-400 text-sm">Not available</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Peer Evaluations -->
            @if($peerEvaluations->count() > 0)
                @foreach($peerEvaluations as $peerEvaluation)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-gray-800">
                                    Peer Evaluation - {{ $peerEvaluation->evaluatedStudent->first_name }} {{ $peerEvaluation->evaluatedStudent->last_name }}
                                </h4>
                                <p class="text-sm text-gray-600">Evaluate your fellow officer's performance</p>
                            </div>
                            <div>
                                @if($peerEvaluation->status === 'completed')
                                    <a href="{{ route('student.evaluation.peer.edit', [$council, $peerEvaluation->evaluatedStudent]) }}"
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                        <i class="fas fa-edit mr-1"></i>
                                        Edit
                                    </a>
                                @else
                                    <a href="{{ route('student.evaluation.peer', [$council, $peerEvaluation->evaluatedStudent]) }}"
                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                        <i class="fas fa-edit mr-1"></i>
                                        Evaluate
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endif
@endsection
