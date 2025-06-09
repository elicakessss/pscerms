@extends('adviser.layout')

@section('title', 'Council Details - PSCERMS')
@section('page-title', 'Council Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Council Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('adviser.councils.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to Councils
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

                <!-- Quick Stats -->
                <div class="w-full space-y-3">
                    <div class="flex items-center justify-between p-2 bg-blue-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-users text-blue-600 mr-2 text-sm"></i>
                            <span class="text-xs font-medium text-gray-700">Officers</span>
                        </div>
                        <span class="text-sm font-bold text-blue-600">{{ $council->councilOfficers->count() }}</span>
                    </div>

                    <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2 text-sm"></i>
                            <span class="text-xs font-medium text-gray-700">Filled</span>
                        </div>
                        <span class="text-sm font-bold text-green-600">{{ $allPositions->where('is_filled', true)->count() }}</span>
                    </div>

                    <div class="flex items-center justify-between p-2 bg-yellow-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-user-plus text-yellow-600 mr-2 text-sm"></i>
                            <span class="text-xs font-medium text-gray-700">Vacant</span>
                        </div>
                        <span class="text-sm font-bold text-yellow-600">{{ $allPositions->where('is_filled', false)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Council Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Council Information</h3>

            <div class="space-y-4">
                <!-- Department Information -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Department</h4>
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg mr-3">
                            <i class="fas fa-building text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-800 font-medium">{{ $council->department->name }}</p>
                            <p class="text-sm text-gray-500">{{ $council->department->abbreviation }}</p>
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
                        @if($council->status === 'active')
                            <form action="{{ route('adviser.councils.destroy', $council) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this council? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-trash mr-2"></i> Delete Council
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Council Management Section with Toggle -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <!-- Toggle Header -->
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center">
            <div class="inline-flex bg-gray-100 rounded-lg p-1">
                <button id="officers-toggle"
                        onclick="showSection('officers')"
                        class="toggle-btn flex items-center px-6 py-3 rounded-md text-sm font-medium transition-all duration-200 bg-white text-green-600 shadow-sm">
                    <i class="fas fa-users mr-2"></i>
                    Council Officers
                </button>
                <button id="evaluations-toggle"
                        onclick="showSection('evaluations')"
                        class="toggle-btn flex items-center px-6 py-3 rounded-md text-sm font-medium transition-all duration-200 text-gray-600 hover:text-gray-800">
                    <i class="fas fa-chart-line mr-2"></i>
                    Evaluation Progress
                </button>
            </div>
        </div>
    </div>

    <!-- Officers Section Content -->
    <div id="officers-section" class="section-content">
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
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Filled
                                                </span>
                                                @if($position['officer']->is_peer_evaluator)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-user-check mr-1"></i>
                                                        Peer Evaluator L{{ $position['officer']->peer_evaluator_level }}
                                                    </span>
                                                @endif
                                            </div>
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

                                            <!-- Peer Evaluator Assignment Dropdown -->
                                            @if(!$council->hasEvaluations() && !$council->isEvaluationInstanceFinalized())
                                                <div class="relative inline-block text-left">
                                                    <button type="button"
                                                            onclick="toggleDropdown('peer-dropdown-{{ $position['officer']->id }}')"
                                                            class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 transition-colors">
                                                        <i class="fas fa-user-cog mr-1"></i>
                                                        Peer Evaluator
                                                        <i class="fas fa-chevron-down ml-1"></i>
                                                    </button>

                                                    <div id="peer-dropdown-{{ $position['officer']->id }}"
                                                         class="hidden absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                                                        <div class="py-1">
                                                            @if($position['officer']->is_peer_evaluator)
                                                                <!-- Current Status -->
                                                                <div class="px-4 py-2 text-xs text-gray-500 border-b">
                                                                    Currently: Level {{ $position['officer']->peer_evaluator_level }}
                                                                </div>
                                                                <!-- Remove Assignment -->
                                                                <form action="{{ route('adviser.councils.remove_peer_evaluator', [$council, $position['officer']]) }}" method="POST">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                            onclick="return confirm('Are you sure you want to remove this peer evaluator assignment?')"
                                                                            class="w-full text-left px-4 py-2 text-xs text-red-700 hover:bg-red-50 flex items-center">
                                                                        <i class="fas fa-user-minus mr-2"></i>
                                                                        Remove Assignment
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <!-- Assign Level 1 -->
                                                                <form action="{{ route('adviser.councils.assign_peer_evaluator', [$council, $position['officer']]) }}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="peer_evaluator_level" value="1">
                                                                    <button type="submit"
                                                                            class="w-full text-left px-4 py-2 text-xs text-blue-700 hover:bg-blue-50 flex items-center">
                                                                        <i class="fas fa-user-plus mr-2"></i>
                                                                        Assign as Level 1
                                                                    </button>
                                                                </form>
                                                                <!-- Assign Level 2 -->
                                                                <form action="{{ route('adviser.councils.assign_peer_evaluator', [$council, $position['officer']]) }}" method="POST">
                                                                    @csrf
                                                                    <input type="hidden" name="peer_evaluator_level" value="2">
                                                                    <button type="submit"
                                                                            class="w-full text-left px-4 py-2 text-xs text-purple-700 hover:bg-purple-50 flex items-center">
                                                                        <i class="fas fa-user-plus mr-2"></i>
                                                                        Assign as Level 2
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Remove Button -->
                                            @if(!$council->isEvaluationInstanceFinalized())
                                                <button onclick="confirmRemoveOfficer({{ $position['officer']->id }}, '{{ $position['officer']->student->first_name }} {{ $position['officer']->student->last_name }}')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                                    <i class="fas fa-trash mr-1"></i>
                                                    Remove
                                                </button>
                                            @endif
                                        @else
                                            @if(!$council->isEvaluationInstanceFinalized())
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
                                                <div class="relative min-w-32">
                                                    <input type="hidden" name="student_id" class="selected-student-id" required>
                                                    <input type="text"
                                                           class="student-search-input text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-green-500 w-full"
                                                           placeholder="{{ $placeholder }}"
                                                           autocomplete="off">
                                                    <div class="search-results absolute z-10 w-full bg-white border border-gray-300 rounded-b shadow-lg hidden max-h-40 overflow-y-auto"></div>
                                                </div>
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                                    <i class="fas fa-plus mr-1"></i>
                                                    Assign
                                                </button>
                                            </form>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 bg-gray-400 text-white text-xs rounded cursor-not-allowed">
                                                    <i class="fas fa-lock mr-1"></i>
                                                    Locked
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($council->department->abbreviation === 'UNIWIDE')
                    @if(!$council->isEvaluationInstanceFinalized())
                        <!-- Dynamic Position Forms - Side by Side Layout -->
                        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Add Senator Section -->
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Add Senator Position</h4>
                            <form action="{{ route('adviser.councils.add_senator', $council) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="senator_title" class="block text-sm font-medium text-gray-700 mb-1">Position Title</label>
                                    <input type="text"
                                           name="position_title"
                                           id="senator_title"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('position_title') border-red-500 @enderror"
                                           value="Senator"
                                           readonly
                                           required>
                                    @error('position_title')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Position title is fixed as "Senator"</p>
                                </div>
                                <div>
                                    <label for="senator_student_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Student</label>
                                    <div class="relative">
                                        <input type="hidden" name="student_id" class="selected-student-id" required>
                                        <input type="text"
                                               id="senator_student_id"
                                               class="student-search-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                                               placeholder="Search for student..."
                                               autocomplete="off">
                                        <div class="search-results absolute z-10 w-full bg-white border border-gray-300 rounded-b shadow-lg hidden max-h-40 overflow-y-auto"></div>
                                    </div>
                                    @error('student_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Add Senator
                                    </button>
                                </div>
                            </form>
                            <p class="text-sm text-green-600 mt-2">Senator position has a fixed title and can be assigned to any student.</p>
                        </div>

                        <!-- Add Representative Section -->
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Add Representative Position</h4>
                            <form action="{{ route('adviser.councils.add_congressman', $council) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="congressman_title" class="block text-sm font-medium text-gray-700 mb-1">Position Title</label>
                                    <input type="text"
                                           name="position_title"
                                           id="congressman_title"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('position_title') border-red-500 @enderror"
                                           placeholder="Select a student to auto-populate"
                                           value="{{ old('position_title') }}"
                                           readonly
                                           required>
                                    @error('position_title')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Position title will be set automatically based on student's department</p>
                                </div>
                                <div>
                                    <label for="congressman_student_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Student</label>
                                    <div class="relative">
                                        <input type="hidden" name="student_id" class="selected-student-id" required>
                                        <input type="text"
                                               id="congressman_student_id"
                                               class="student-search-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                                               placeholder="Search for student..."
                                               autocomplete="off">
                                        <div class="search-results absolute z-10 w-full bg-white border border-gray-300 rounded-b shadow-lg hidden max-h-40 overflow-y-auto"></div>
                                    </div>
                                    @error('student_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Add Representative
                                    </button>
                                </div>
                            </form>
                            <p class="text-sm text-green-600 mt-2">Position title automatically updates based on selected student's department (e.g., SITE Representative).</p>
                        </div>

                        <!-- Add Justice Section -->
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Add Associate Justice Position</h4>
                            <form action="{{ route('adviser.councils.add_justice', $council) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="justice_title" class="block text-sm font-medium text-gray-700 mb-1">Position Title</label>
                                    <input type="text"
                                           name="position_title"
                                           id="justice_title"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('position_title') border-red-500 @enderror"
                                           value="Associate Justice"
                                           readonly
                                           required>
                                    @error('position_title')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">Position title is fixed as "Associate Justice"</p>
                                </div>
                                <div>
                                    <label for="justice_student_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Student</label>
                                    <div class="relative">
                                        <input type="hidden" name="student_id" class="selected-student-id" required>
                                        <input type="text"
                                               id="justice_student_id"
                                               class="student-search-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                                               placeholder="Search for student..."
                                               autocomplete="off">
                                        <div class="search-results absolute z-10 w-full bg-white border border-gray-300 rounded-b shadow-lg hidden max-h-40 overflow-y-auto"></div>
                                    </div>
                                    @error('student_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Add Associate Justice
                                    </button>
                                </div>
                            </form>
                            <p class="text-sm text-green-600 mt-2">Associate Justice position has a fixed title and can be assigned to any student.</p>
                        </div>

                        <!-- Add Coordinator Section -->
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Add Coordinator Position</h4>
                            <form action="{{ route('adviser.councils.add_coordinator', $council) }}" method="POST" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="coordinator_title" class="block text-sm font-medium text-gray-700 mb-1">Coordinator Type</label>
                                    <input type="text"
                                           name="coordinator_title"
                                           id="coordinator_title"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('coordinator_title') border-red-500 @enderror"
                                           placeholder="e.g., Academic, Sports, Logistics"
                                           value="{{ old('coordinator_title') }}"
                                           required>
                                    @error('coordinator_title')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">"Coordinator" will be automatically added to the title</p>
                                </div>
                                <div>
                                    <label for="coordinator_student_id" class="block text-sm font-medium text-gray-700 mb-1">Assign Student</label>
                                    <div class="relative">
                                        <input type="hidden" name="student_id" class="selected-student-id" required>
                                        <input type="text"
                                               id="coordinator_student_id"
                                               class="student-search-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                                               placeholder="Search for student..."
                                               autocomplete="off">
                                        <div class="search-results absolute z-10 w-full bg-white border border-gray-300 rounded-b shadow-lg hidden max-h-40 overflow-y-auto"></div>
                                    </div>
                                    @error('student_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-plus mr-1"></i>Add Coordinator
                                    </button>
                                </div>
                            </form>
                            <p class="text-sm text-green-600 mt-2">Enter the coordinator type (e.g., "Logistics") and "Coordinator" will be automatically added to create the full title.</p>
                        </div>
                    </div>
                    @else
                        <!-- Evaluation Instance Finalized - No Position Management -->
                        <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-lock text-gray-400 text-3xl mb-3"></i>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Position Management Locked</h4>
                                <p class="text-gray-600">
                                    The evaluation instance has been finalized. Position assignments and modifications are no longer allowed.
                                    Only viewing of existing positions is permitted.
                                </p>
                            </div>
                        </div>
                    @endif
                @else
                    @if(!$council->isEvaluationInstanceFinalized())
                        <!-- Add Coordinator Section for Non-UNIWIDE Councils -->
                        <div class="mt-8 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Add Coordinator Position</h4>
                        <form action="{{ route('adviser.councils.add_coordinator', $council) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label for="coordinator_title" class="block text-sm font-medium text-gray-700 mb-1">Coordinator Type</label>
                                    <input type="text"
                                           name="coordinator_title"
                                           id="coordinator_title"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('coordinator_title') border-red-500 @enderror"
                                           placeholder="e.g., Academic, Sports, Logistics"
                                           value="{{ old('coordinator_title') }}"
                                           required>
                                    @error('coordinator_title')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                    <p class="text-xs text-gray-500 mt-1">"Coordinator" will be automatically added to the title</p>
                                </div>
                                <div>
                                    <label for="coordinator_student_id_dept" class="block text-sm font-medium text-gray-700 mb-1">Assign Student</label>
                                    <div class="relative">
                                        <input type="hidden" name="student_id" class="selected-student-id" required>
                                        <input type="text"
                                               id="coordinator_student_id_dept"
                                               class="student-search-input w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('student_id') border-red-500 @enderror"
                                               placeholder="Search for student..."
                                               autocomplete="off">
                                        <div class="search-results absolute z-10 w-full bg-white border border-gray-300 rounded-b shadow-lg hidden max-h-40 overflow-y-auto"></div>
                                    </div>
                                    @error('student_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-plus mr-1"></i>Add Coordinator
                                </button>
                            </div>
                        </form>
                        <p class="text-sm text-green-600 mt-2">Enter the coordinator type (e.g., "Logistics") and "Coordinator" will be automatically added to create the full title.</p>
                    </div>
                    <!-- Updated Information Note -->
                    {{-- <div class="mt-8 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <h5 class="text-sm font-medium text-blue-800 mb-1">Uniwide Council Information:</h5>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li>• <strong>Position Order:</strong> Executive → Senate → Representatives → Justices → Coordinators</li>
                            <li>• <strong>Senators:</strong> Fixed position name "Senator"</li>
                            <li>• <strong>Representatives:</strong> Department-specific (e.g., "SITE Representative")</li>
                            <li>• <strong>Associate Justices:</strong> Fixed position name "Associate Justice"</li>
                            <li>• <strong>Coordinators:</strong> Custom prefix + "Coordinator" (e.g., "Logistics Coordinator")</li>
                            <li>• Students cannot be in multiple councils in the same academic year</li>
                        </ul>
                    </div> --}}
                    @else
                        <!-- Evaluation Instance Finalized - No Position Management -->
                        <div class="mt-8 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-lock text-gray-400 text-3xl mb-3"></i>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">Position Management Locked</h4>
                                <p class="text-gray-600">
                                    The evaluation instance has been finalized. Position assignments and modifications are no longer allowed.
                                    Only viewing of existing positions is permitted.
                                </p>
                            </div>
                        </div>
                    @endif
                @endif
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

    <!-- Evaluations Section Content -->
    <div id="evaluations-section" class="section-content hidden">
        <div class="p-6">
            @if($council->councilOfficers->count() > 0)
                <!-- Start Evaluation Instance Button -->
                @if($council->canStartEvaluationInstance())
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-medium text-blue-900">Ready to Start Evaluation Instance</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                This will start the evaluation instance for all {{ $council->councilOfficers->count() }} officers.
                                During the active instance, evaluations will be saved as drafts and can be edited.
                                You can finalize the instance when all evaluations are completed.
                            </p>
                            @php
                                $peerEvaluators = $council->getPeerEvaluators();
                                $level1PE = $peerEvaluators->where('peer_evaluator_level', 1)->first();
                                $level2PE = $peerEvaluators->where('peer_evaluator_level', 2)->first();
                            @endphp
                            <div class="mt-2 text-sm text-blue-600">
                                <strong>Assigned Peer Evaluators:</strong><br>
                                Level 1: {{ $level1PE ? $level1PE->student->first_name . ' ' . $level1PE->student->last_name : 'Not assigned' }}<br>
                                Level 2: {{ $level2PE ? $level2PE->student->first_name . ' ' . $level2PE->student->last_name : 'Not assigned' }}
                            </div>
                        </div>
                        <form id="start-evaluation-form-details" action="{{ route('adviser.councils.start_evaluations', $council) }}" method="POST" class="ml-4">
                            @csrf
                            <button type="button"
                                    onclick="confirmStartEvaluationDetails('{{ $council->name }}', {{ $council->councilOfficers->count() }})"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <i class="fas fa-play mr-2"></i>
                                Start Evaluation Instance
                            </button>
                        </form>
                    </div>
                </div>
                @elseif($council->councilOfficers->count() > 0 && !$council->hasPeerEvaluatorsAssigned())
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-lg font-medium text-yellow-900">Peer Evaluators Required</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                Before starting evaluations, you must assign exactly 2 officers as peer evaluators:
                                one as Level 1 and one as Level 2. Use the "PE Level 1" and "PE Level 2" buttons in the Officers section above.
                            </p>
                            @php
                                $peerEvaluators = $council->getPeerEvaluators();
                                $level1PE = $peerEvaluators->where('peer_evaluator_level', 1)->first();
                                $level2PE = $peerEvaluators->where('peer_evaluator_level', 2)->first();
                            @endphp
                            <div class="mt-2 text-sm text-yellow-600">
                                <strong>Current Status:</strong><br>
                                Level 1: {{ $level1PE ? $level1PE->student->first_name . ' ' . $level1PE->student->last_name . ' ✓' : 'Not assigned ✗' }}<br>
                                Level 2: {{ $level2PE ? $level2PE->student->first_name . ' ' . $level2PE->student->last_name . ' ✓' : 'Not assigned ✗' }}
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($council->isEvaluationInstanceActive())
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-edit text-green-600 mr-3"></i>
                            <div>
                                <h4 class="text-lg font-medium text-green-900">Evaluation Instance Active</h4>
                                <p class="text-sm text-green-700 mt-1">
                                    Evaluations are being saved as drafts and can be edited.
                                    @if($council->canFinalizeEvaluationInstance())
                                        All evaluations are completed - you can now finalize the instance.
                                    @else
                                        Complete all evaluations before finalizing the instance.
                                    @endif
                                </p>
                                <p class="text-xs text-green-600 mt-1">
                                    Started: {{ $council->evaluation_instance_started_at->format('M j, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                        <div class="ml-4 flex space-x-2">
                            @if($council->canFinalizeEvaluationInstance())
                                <form action="{{ route('adviser.councils.finalize_evaluations', $council) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Are you sure you want to finalize the evaluation instance? This will lock all evaluations and calculate final scores. This action cannot be undone.')"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        <i class="fas fa-lock mr-2"></i>
                                        Finalize Instance
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('adviser.councils.clear_evaluations', $council) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Are you sure you want to clear all evaluations? This will delete all evaluation data and reset the instance. This action cannot be undone.')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                    <i class="fas fa-trash mr-2"></i>
                                    Clear Evaluations
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @elseif($council->isEvaluationInstanceFinalized())
                <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-lock text-gray-600 mr-3"></i>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">Evaluation Instance Finalized</h4>
                                <p class="text-sm text-gray-700 mt-1">
                                    All evaluations are locked and final scores have been calculated.
                                    Evaluations can only be viewed, not edited.
                                </p>
                                <p class="text-xs text-gray-600 mt-1">
                                    Finalized: {{ $council->evaluation_instance_finalized_at->format('M j, Y g:i A') }}
                                </p>
                            </div>
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

                                            @if($council->isEvaluationInstanceFinalized())
                                                <!-- Finalized instance - only view -->
                                                @if($adviserEvaluation && $adviserEvaluation->status === 'completed')
                                                    <a href="{{ route('adviser.evaluation.edit', [$council, $officer->student]) }}"
                                                       class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        View
                                                    </a>
                                                @else
                                                    <span class="text-gray-400 text-sm">Not evaluated</span>
                                                @endif
                                            @elseif($council->isEvaluationInstanceActive())
                                                <!-- Active instance - can edit/evaluate -->
                                                @if($adviserEvaluation && $adviserEvaluation->status === 'completed')
                                                    <a href="{{ route('adviser.evaluation.edit', [$council, $officer->student]) }}"
                                                       class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Edit Draft
                                                    </a>
                                                @else
                                                    <a href="{{ route('adviser.evaluation.show', [$council, $officer->student]) }}"
                                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition-colors">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Evaluate
                                                    </a>
                                                @endif
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-sm">Start evaluation instance first</span>
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
// Toggle section functionality
function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.section-content').forEach(section => {
        section.classList.add('hidden');
    });

    // Reset all toggle buttons to inactive state
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('bg-white', 'text-green-600', 'shadow-sm');
        btn.classList.add('text-gray-600', 'hover:text-gray-800');
    });

    // Show selected section
    document.getElementById(sectionName + '-section').classList.remove('hidden');

    // Activate selected toggle button
    const activeBtn = document.getElementById(sectionName + '-toggle');
    activeBtn.classList.remove('text-gray-600', 'hover:text-gray-800');
    activeBtn.classList.add('bg-white', 'text-green-600', 'shadow-sm');
}

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

// Confirm start evaluation for details page
function confirmStartEvaluationDetails(councilName, officerCount) {
    showConfirmation(
        'Start Evaluation Instance',
        `This will start the evaluation instance for all ${officerCount} officers in ${councilName}. During the active instance, evaluations will be saved as drafts and can be edited. You can finalize the instance when all evaluations are completed.`,
        () => {
            document.getElementById('start-evaluation-form-details').submit();
        },
        'question'
    );
}

// Dropdown toggle functionality
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const isHidden = dropdown.classList.contains('hidden');

    // Close all other dropdowns first
    document.querySelectorAll('[id^="peer-dropdown-"]').forEach(dd => {
        if (dd.id !== dropdownId) {
            dd.classList.add('hidden');
        }
    });

    // Toggle the clicked dropdown
    if (isHidden) {
        dropdown.classList.remove('hidden');
    } else {
        dropdown.classList.add('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id^="peer-dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Student search functionality
function initializeStudentSearch() {
    const searchInputs = document.querySelectorAll('.student-search-input');

    searchInputs.forEach(input => {
        let searchTimeout;

        input.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            const resultsContainer = this.nextElementSibling;
            const hiddenInput = this.previousElementSibling;

            // Clear previous timeout
            clearTimeout(searchTimeout);

            if (searchTerm.length < 2) {
                resultsContainer.classList.add('hidden');
                hiddenInput.value = '';
                return;
            }

            // Debounce search
            searchTimeout = setTimeout(() => {
                fetch(`{{ route('adviser.councils.search_students', $council) }}?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(students => {
                        resultsContainer.innerHTML = '';

                        if (students.length === 0) {
                            resultsContainer.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">No students found</div>';
                        } else {
                            students.forEach(student => {
                                const div = document.createElement('div');
                                div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm border-b border-gray-100 last:border-b-0';
                                div.innerHTML = `
                                    <div class="font-medium text-gray-900">${student.name}</div>
                                    <div class="text-xs text-gray-500">${student.id_number}${student.department ? ' - ' + student.department : ''}</div>
                                `;

                                div.addEventListener('click', () => {
                                    input.value = student.name;
                                    hiddenInput.value = student.id;
                                    resultsContainer.classList.add('hidden');

                                    // Update representative title if this is the congressman input
                                    if (input.id === 'congressman_student_id' && student.department) {
                                        const titleInput = document.getElementById('congressman_title');
                                        if (titleInput) {
                                            titleInput.value = student.department + ' Representative';
                                        }
                                    }
                                });

                                resultsContainer.appendChild(div);
                            });
                        }

                        resultsContainer.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        resultsContainer.innerHTML = '<div class="px-3 py-2 text-sm text-red-500">Search error occurred</div>';
                        resultsContainer.classList.remove('hidden');
                    });
            }, 300);
        });

        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !input.nextElementSibling.contains(e.target)) {
                input.nextElementSibling.classList.add('hidden');
            }
        });

        // Clear selection when input is manually cleared
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete') {
                const hiddenInput = this.previousElementSibling;
                hiddenInput.value = '';
            }
        });
    });
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Show officers section by default
    showSection('officers');

    // Initialize student search functionality
    initializeStudentSearch();
});
</script>
@endsection
