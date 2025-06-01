@extends('adviser.layout')

@section('title', 'Council Details - PSCERMS')
@section('page-title', $council->name)

@section('header-actions')
<div class="flex items-center space-x-4">
    <a href="{{ route('adviser.councils.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Councils
    </a>
</div>
@endsection

@section('content')
<!-- Success Message -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<!-- Council Information -->
<div class="bg-white rounded-lg shadow mb-6">
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">Council Information</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500">Council Name</h4>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $council->name }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Academic Year</h4>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $council->academic_year }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Status</h4>
                <div class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $council->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-1 {{ $council->status === 'active' ? 'bg-green-400' : 'bg-gray-400' }}"></div>
                        {{ ucfirst($council->status) }}
                    </span>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Department</h4>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $council->department->abbreviation }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<div class="bg-white rounded-lg shadow">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
            <button onclick="showTab('officers')"
                    id="officers-tab"
                    class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 active">
                <i class="fas fa-users mr-2"></i>
                Officers ({{ $council->councilOfficers->count() }})
            </button>
            <button onclick="showTab('evaluations')"
                    id="evaluations-tab"
                    class="tab-button py-4 px-6 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300">
                <i class="fas fa-chart-line mr-2"></i>
                Evaluations ({{ $council->councilOfficers->whereNotNull('final_score')->count() }})
            </button>
        </nav>
    </div>

    <!-- Officers Tab Content -->
    <div id="officers-content" class="tab-content">
        <div class="p-6">
            @if($council->councilOfficers->count() > 0 || $allPositions->count() > 0)
                <!-- Officers Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-900">{{ $allPositions->count() }}</div>
                        <div class="text-sm text-blue-700">Total Positions</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-900">{{ $allPositions->where('is_filled', true)->count() }}</div>
                        <div class="text-sm text-green-700">Filled Positions</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-900">{{ $allPositions->where('is_filled', false)->count() }}</div>
                        <div class="text-sm text-yellow-700">Vacant Positions</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-900">
                            {{ $allPositions->count() > 0 ? number_format(($allPositions->where('is_filled', true)->count() / $allPositions->count()) * 100, 1) : '0.0' }}%
                        </div>
                        <div class="text-sm text-purple-700">Fill Rate</div>
                    </div>
                </div>

                <!-- Officers Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Officer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($allPositions as $position)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($position['is_filled'])
                                                    @if($position['officer']->student->profile_picture)
                                                        <img class="h-10 w-10 rounded-full object-cover"
                                                             src="{{ asset('storage/' . $position['officer']->student->profile_picture) }}"
                                                             alt="{{ $position['officer']->student->first_name }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-700">
                                                                {{ substr($position['officer']->student->first_name, 0, 1) }}{{ substr($position['officer']->student->last_name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                @if($position['is_filled'])
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $position['officer']->student->first_name }} {{ $position['officer']->student->last_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ $position['officer']->student->id_number }}</div>
                                                @else
                                                    <div class="text-sm font-medium text-gray-500">Vacant</div>
                                                    <div class="text-sm text-gray-400">No assignment</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @php
                                                $displayTitle = $position['display_title'] ?? $position['title'];
                                                // Clean up councilor titles to show just "Councilor"
                                                if (str_contains($displayTitle, 'Councilor')) {
                                                    $displayTitle = 'Councilor';
                                                }
                                            @endphp
                                            {{ $displayTitle }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $position['branch'] ?? 'Executive' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($position['is_filled'])
                                            <div class="text-sm text-gray-900">{{ $position['officer']->student->department->name ?? 'N/A' }}</div>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($position['is_filled'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Filled
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-user-plus mr-1"></i>
                                                Vacant
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        @if($position['is_filled'])
                                            <!-- View Button -->
                                            <a href="{{ route('adviser.student_management.show', $position['officer']->student) }}"
                                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                                <i class="fas fa-eye mr-1"></i>
                                                View
                                            </a>

                                            <!-- Remove Button -->
                                            <button onclick="confirmRemoveOfficer({{ $position['officer']->id }}, '{{ $position['officer']->student->first_name }} {{ $position['officer']->student->last_name }}')"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                                <i class="fas fa-trash mr-1"></i>
                                                Remove
                                            </button>
                                        @else
                                            <form action="{{ route('adviser.councils.assign_officer', $council) }}" method="POST" class="flex items-center space-x-2">
                                                @csrf
                                                <input type="hidden" name="position_title" value="{{ $position['title'] }}">
                                                @php
                                                    // Get the required department for this position
                                                    $requiredDept = null;
                                                    if ($council->department->abbreviation === 'UNIWIDE') {
                                                        // Extract department from position title
                                                        if (str_contains($position['title'], 'SASTE')) {
                                                            $requiredDept = 'SASTE';
                                                        } elseif (str_contains($position['title'], 'SBAHM')) {
                                                            $requiredDept = 'SBAHM';
                                                        } elseif (str_contains($position['title'], 'SITE')) {
                                                            $requiredDept = 'SITE';
                                                        } elseif (str_contains($position['title'], 'SNAHS')) {
                                                            $requiredDept = 'SNAHS';
                                                        }
                                                    }
                                                    $placeholder = $requiredDept ? "Select {$requiredDept} student" : "Select Student";
                                                @endphp
                                                <select name="student_id" class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-green-500 min-w-32" required>
                                                    <option value="">{{ $placeholder }}</option>
                                                    @foreach($availableStudents as $student)
                                                        @if(!$requiredDept || $student->department->abbreviation === $requiredDept)
                                                            <option value="{{ $student->id }}">
                                                                {{ $student->first_name }} {{ $student->last_name }}
                                                                @if($council->department->abbreviation === 'UNIWIDE' && !$requiredDept)
                                                                    ({{ $student->department->abbreviation }})
                                                                @endif
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                                    <i class="fas fa-plus mr-1"></i>
                                                    Assign
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Add Coordinator Section -->
                <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Add Coordinator Position</h4>
                    <form action="{{ route('adviser.councils.add_coordinator', $council) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label for="coordinator_title" class="block text-sm font-medium text-gray-700 mb-1">Position Title</label>
                                <input type="text"
                                       name="coordinator_title"
                                       id="coordinator_title"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('coordinator_title') border-red-500 @enderror"
                                       placeholder="e.g., Academic Coordinator, Sports Coordinator"
                                       value="{{ old('coordinator_title') }}"
                                       required>
                                @error('coordinator_title')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="coordinator_student_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Student</label>
                                <select name="student_id"
                                        id="coordinator_student_id"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                                        required>
                                    <option value="">Select Student</option>
                                    @foreach($availableStudents as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->first_name }} {{ $student->last_name }} ({{ $student->id_number }})
                                            @if($council->department->abbreviation === 'UNIWIDE')
                                                - {{ $student->department->abbreviation }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-plus mr-1"></i>Add Coordinator
                            </button>
                        </div>
                    </form>
                    <p class="text-sm text-blue-600 mt-2">Create custom coordinator positions and assign them to students immediately.</p>
                    @if($council->department->abbreviation === 'UNIWIDE')
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h5 class="text-sm font-medium text-yellow-800 mb-1">Uniwide Council Constraints:</h5>
                            <ul class="text-xs text-yellow-700 space-y-1">
                                <li>• Senators: 3 students per department (12 total)</li>
                                <li>• Congressman & Justices: 2 students per department</li>
                                <li>• Executive positions: No department restrictions</li>
                                <li>• Students cannot be in multiple councils in the same academic year</li>
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Positions Available</h3>
                    <p class="text-gray-500">This council doesn't have any positions set up yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Evaluations Tab Content -->
    <div id="evaluations-content" class="tab-content hidden">
        <div class="p-6">
            @if($council->councilOfficers->count() > 0)
                <!-- Start Evaluations Button -->
                @if($council->canStartEvaluations())
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-medium text-blue-900">Ready to Start Evaluations</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                This will create evaluation instances for all {{ $council->councilOfficers->count() }} officers.
                                Each officer will receive self-evaluation, peer evaluations from executives, and adviser evaluation.
                            </p>
                        </div>
                        <form action="{{ route('adviser.councils.start_evaluations', $council) }}" method="POST" class="ml-4">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Are you sure you want to start evaluations? This will create evaluation instances for all officers.')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <i class="fas fa-play mr-2"></i>
                                Start Evaluations
                            </button>
                        </form>
                    </div>
                </div>
                @elseif($council->hasEvaluations())
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-3"></i>
                            <div>
                                <h4 class="text-lg font-medium text-green-900">Evaluations Started</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Evaluation instances have been created. Students and advisers can now complete their evaluations.
                                </p>
                            </div>
                        </div>
                        <div class="ml-4">
                            <form action="{{ route('adviser.councils.clear_evaluations', $council) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Are you sure you want to clear all evaluations? This will delete all evaluation data and cannot be undone.')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-trash mr-2"></i>
                                    Clear Evaluations
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                <!-- Evaluation Summary -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-900">{{ $council->councilOfficers->count() }}</div>
                        <div class="text-sm text-blue-700">Total Officers</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-900">{{ $council->councilOfficers->whereNotNull('final_score')->count() }}</div>
                        <div class="text-sm text-green-700">Completed Evaluations</div>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-900">{{ $council->councilOfficers->whereNull('final_score')->count() }}</div>
                        <div class="text-sm text-yellow-700">Pending Evaluations</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-900">
                            {{ $council->councilOfficers->whereNotNull('final_score')->count() > 0 ? number_format($council->councilOfficers->whereNotNull('final_score')->avg('final_score'), 2) : '0.00' }}
                        </div>
                        <div class="text-sm text-purple-700">Average Score</div>
                    </div>
                </div>

                <!-- Evaluation Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Officer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Self Evaluation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peer Evaluation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adviser Evaluation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($council->councilOfficers->sortBy('rank') as $officer)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-gray-700">
                                                        {{ substr($officer->student->first_name, 0, 1) }}{{ substr($officer->student->last_name, 0, 1) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $officer->student->first_name }} {{ $officer->student->last_name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $officer->position_title }}</div>
                                        <div class="text-sm text-gray-500">{{ $officer->position_level }}</div>
                                    </td>
                                    @php
                                        $evaluationProgress = $officer->getEvaluationProgress();
                                    @endphp
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($evaluationProgress['self_completed'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Completed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($evaluationProgress['peer_total'] > 0)
                                            @if($evaluationProgress['peer_completed'] == $evaluationProgress['peer_total'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Completed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ $evaluationProgress['peer_completed'] }}/{{ $evaluationProgress['peer_total'] }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-minus mr-1"></i>
                                                N/A
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($evaluationProgress['adviser_completed'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Completed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($officer->rank)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                #{{ $officer->rank }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($council->hasEvaluations())
                                            @php
                                                $adviserEvaluation = $council->evaluations()
                                                    ->where('evaluator_id', auth()->id())
                                                    ->where('evaluator_type', 'adviser')
                                                    ->where('evaluated_student_id', $officer->student_id)
                                                    ->first();
                                            @endphp

                                            @if($adviserEvaluation && $adviserEvaluation->status === 'completed')
                                                <span class="text-green-600 text-sm">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Evaluated
                                                </span>
                                            @else
                                                <a href="{{ route('adviser.evaluation.show', [$council, $officer->student]) }}"
                                                   class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                                    <i class="fas fa-edit mr-1"></i>
                                                    Evaluate
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-sm">Start evaluations first</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Evaluations Available</h3>
                    <p class="text-gray-500">Assign officers first to see their evaluation status.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Officer Modal -->
<div id="editOfficerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Officer Position</h3>
                <form id="editOfficerForm" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="edit_position_title" class="block text-sm font-medium text-gray-700 mb-2">Position Title</label>
                        <input type="text" name="position_title" id="edit_position_title" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                    </div>

                    <div class="mb-4">
                        <label for="edit_position_level" class="block text-sm font-medium text-gray-700 mb-2">Position Level</label>
                        <select name="position_level" id="edit_position_level" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" required>
                            <option value="Executive">Executive</option>
                            <option value="Officer">Officer</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditOfficerModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Update Officer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Remove Officer Modal -->
<div id="removeOfficerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Remove Officer</h3>
                <p class="text-gray-600 mb-4">Are you sure you want to remove "<span id="removeOfficerName"></span>" from the council? This action cannot be undone.</p>
                <form id="removeOfficerForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRemoveOfficerModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            Remove Officer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.tab-button.active {
    color: #059669;
    border-color: #059669;
}
</style>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');

    // Add active class to selected tab button
    document.getElementById(tabName + '-tab').classList.add('active');
}

// No longer needed - positions are pre-populated

// Edit officer functionality
function editOfficer(officerId, positionTitle, positionLevel) {
    document.getElementById('edit_position_title').value = positionTitle;
    document.getElementById('edit_position_level').value = positionLevel;
    document.getElementById('editOfficerForm').action = `/adviser/councils/{{ $council->id }}/officers/${officerId}`;
    document.getElementById('editOfficerModal').classList.remove('hidden');
}

function closeEditOfficerModal() {
    document.getElementById('editOfficerModal').classList.add('hidden');
}

// Remove officer functionality
function confirmRemoveOfficer(officerId, officerName) {
    document.getElementById('removeOfficerName').textContent = officerName;
    document.getElementById('removeOfficerForm').action = `/adviser/councils/{{ $council->id }}/officers/${officerId}`;
    document.getElementById('removeOfficerModal').classList.remove('hidden');
}

function closeRemoveOfficerModal() {
    document.getElementById('removeOfficerModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('editOfficerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditOfficerModal();
    }
});

document.getElementById('removeOfficerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRemoveOfficerModal();
    }
});

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    showTab('officers'); // Show officers tab by default
});
</script>
@endsection
