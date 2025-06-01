@extends('adviser.layout')

@section('title', 'Student Details')
@section('page-title', 'Student Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Student Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('adviser.student_management.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="md:flex">
        <!-- Profile Picture and Basic Info -->
        <div class="md:w-1/3 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex flex-col items-center text-center">
                @if($student->profile_picture)
                <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="{{ $student->first_name }}" class="h-32 w-32 object-cover rounded-full mb-4">
                @else
                <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <i class="fas fa-user text-gray-400 text-4xl"></i>
                </div>
                @endif

                <h2 class="text-xl font-bold text-gray-800">{{ $student->first_name }} {{ $student->last_name }}</h2>
                <p class="text-gray-600 mb-2">{{ $student->id_number }}</p>
                <p class="text-gray-600 mb-4">{{ $student->email }}</p>

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $student->department->abbreviation }} - {{ $student->department->name }}
                </div>
            </div>
        </div>

        <!-- Student Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h3>

            <div class="space-y-4">
                <!-- Description -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Description</h4>
                    <p class="text-gray-800">{{ $student->description ?? 'No description available.' }}</p>
                </div>

                <!-- Created & Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                        <p class="text-gray-800">{{ $student->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                        <p class="text-gray-800">{{ $student->updated_at->format('F j, Y, g:i a') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Actions</h4>
                    <div class="flex space-x-3">
                        <a href="{{ route('adviser.student_management.edit', $student->id) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-edit mr-2"></i> Edit Student
                        </a>
                        <form action="{{ route('adviser.student_management.destroy', $student->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-trash mr-2"></i> Delete Student
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Portfolio Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
            Leadership Experiences
        </h3>

        @if($completedCouncils->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($completedCouncils as $councilOfficer)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <!-- Council Header -->
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $councilOfficer->council->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $councilOfficer->council->academic_year }}</p>
                            </div>
                        </div>

                        <!-- Position and Department -->
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-700">{{ $councilOfficer->position_title }}</p>
                            @if($councilOfficer->council->department->id !== $student->department_id)
                                <p class="text-xs text-gray-500">{{ $councilOfficer->council->department->name }}</p>
                            @endif
                        </div>

                        <!-- Scores -->
                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Final Score:</span>
                                <span class="text-sm font-semibold text-gray-800">{{ number_format($councilOfficer->final_score, 2) }}</span>
                            </div>
                            @if($councilOfficer->rank)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Rank:</span>
                                    <div class="flex items-center">
                                        @php
                                            $rankingCategory = $councilOfficer->ranking_category;
                                            $gemColor = match($rankingCategory) {
                                                'Gold' => 'text-yellow-500',
                                                'Silver' => 'text-gray-500',
                                                'Bronze' => 'text-orange-600',
                                                default => 'text-gray-400'
                                            };
                                        @endphp
                                        <i class="fas fa-gem {{ $gemColor }} mr-1"></i>
                                        <span class="text-sm font-semibold text-gray-800">#{{ $councilOfficer->rank }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Completion Date -->
                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Completed: {{ $councilOfficer->completed_at->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trophy text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Completed Councils</h4>
                <p class="text-gray-400">This student's completed council evaluations will appear here once available.</p>
            </div>
        @endif
    </div>
</div>
@endsection
