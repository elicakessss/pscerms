@extends('adviser.layout')

@section('title', 'Student Management')
@section('page-title', 'Student Management')

@section('header-actions')
    <div class="flex items-center space-x-4">
        <!-- Search Bar -->
        <form method="GET">
            @if($isUniwideAdviser)
                <input type="hidden" name="department_filter" value="{{ request('department_filter') }}">
            @endif
            <div class="relative">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search students..."
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
        </form>

        <!-- Add Student Button -->
        <a href="{{ route('adviser.student_management.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Add Student</span>
        </a>
    </div>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Total Students Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-graduation-cap text-blue-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Students</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $students->count() }}</p>
            </div>
        </div>
    </div>

    @if($isUniwideAdviser)
        <!-- Active Students Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">All Departments</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $departments->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Department Filter Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Current Filter</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @if(request('department_filter') && request('department_filter') !== 'all')
                            @php
                                $selectedDept = $departments->find(request('department_filter'));
                            @endphp
                            {{ $selectedDept ? $selectedDept->abbreviation : 'All' }}
                        @else
                            All Departments
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Department Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-building text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Department</p>
                    <p class="text-lg font-semibold text-gray-900">{{ auth()->user()->department->abbreviation }}</p>
                </div>
            </div>
        </div>

        <!-- Search Status Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Search Status</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ request('search') ? 'Filtered' : 'All Students' }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Student Management Section -->
<div class="bg-white rounded-lg shadow">
    <!-- Header Section -->
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">All Students</h3>
        <p class="text-sm text-green-100 mt-1">
            View and manage students
            @if($isUniwideAdviser)
                across all departments
            @else
                in {{ auth()->user()->department->name }}
            @endif
        </p>
    </div>

    @if($isUniwideAdviser)
        <!-- Department Filter for Uniwide Advisers -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex">
                <a href="{{ request()->fullUrlWithQuery(['department_filter' => 'all']) }}"
                   class="py-4 px-6 text-sm font-medium border-b-2 transition-colors {{ request('department_filter', 'all') === 'all' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-users mr-2"></i>
                    All Students ({{ $students->count() }})
                </a>
                @foreach($departments as $dept)
                    <a href="{{ request()->fullUrlWithQuery(['department_filter' => $dept->id]) }}"
                       class="py-4 px-6 text-sm font-medium border-b-2 transition-colors {{ request('department_filter') == $dept->id ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-building mr-2"></i>
                        {{ $dept->abbreviation }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endif

    <!-- Students Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                    @if($isUniwideAdviser)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($student->profile_picture)
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ asset('storage/' . $student->profile_picture) }}"
                                             alt="{{ $student->first_name }} {{ $student->last_name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-user text-green-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $student->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $student->id_number }}
                        </td>
                        @if($isUniwideAdviser)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $student->department->abbreviation ?? 'N/A' }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <!-- View Button -->
                            <a href="{{ route('adviser.student_management.show', $student->id) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>

                            <!-- Edit Button -->
                            <a href="{{ route('adviser.student_management.edit', $student->id) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </a>

                            <!-- Delete Button -->
                            <form method="POST"
                                  action="{{ route('adviser.student_management.destroy', $student->id) }}"
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this student?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isUniwideAdviser ? '4' : '3' }}" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-graduation-cap text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium mb-2">No students found</h3>
                                <p class="text-sm">{{ request('search') ? 'Try adjusting your search criteria' : 'Get started by creating your first student' }}</p>
                                @if(!request('search'))
                                    <a href="{{ route('adviser.student_management.create') }}"
                                       class="mt-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                        Add First Student
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Students Count -->
    @if($students->count() > 0)
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <p class="text-sm text-gray-700">
            Showing {{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}
            @if(request('search'))
                for "<strong>{{ request('search') }}</strong>"
            @endif
            @if($isUniwideAdviser && request('department_filter') && request('department_filter') !== 'all')
                @php
                    $selectedDept = $departments->find(request('department_filter'));
                @endphp
                @if($selectedDept)
                    in <strong>{{ $selectedDept->abbreviation }}</strong>
                @endif
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
