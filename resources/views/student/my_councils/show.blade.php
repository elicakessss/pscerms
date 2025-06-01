@extends('student.layout')

@section('title', 'Council Details - PSCERMS')
@section('page-title', $council->name)

@section('content')
<!-- Success Message -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<!-- Council Information Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <div class="flex items-start justify-between mb-6">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $council->name }}</h1>
                <div class="flex items-center space-x-6 text-sm text-gray-600">
                    <span class="flex items-center">
                        <i class="fas fa-building mr-2"></i>
                        {{ $council->department->name }}
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        {{ $council->academic_year }}
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        {{ $officers->count() }} Officers
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Status Badge -->
                @if($council->status === 'active')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-2"></i>
                        Active
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-archive mr-2"></i>
                        Completed
                    </span>
                @endif
                <!-- Back Button -->
                <a href="{{ route('student.councils.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to My Councils
                </a>
            </div>
        </div>

        <!-- Council Adviser -->
        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Council Adviser</h3>
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    @if($council->adviser->profile_picture)
                        <img src="{{ asset('storage/' . $council->adviser->profile_picture) }}"
                             alt="{{ $council->adviser->first_name }}"
                             class="h-12 w-12 rounded-full object-cover">
                    @else
                        <i class="fas fa-chalkboard-teacher text-blue-600 text-lg"></i>
                    @endif
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-gray-900">
                        {{ $council->adviser->first_name }} {{ $council->adviser->last_name }}
                    </h4>
                    <p class="text-sm text-gray-600">{{ $council->adviser->department->name }}</p>
                    <p class="text-sm text-gray-500">{{ $council->adviser->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- My Position Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">My Position</h3>
        <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-user-tie text-green-600 text-lg"></i>
            </div>
            <div>
                <h4 class="text-lg font-semibold text-gray-900">{{ $studentOfficer->position_title }}</h4>
                @if($studentOfficer->position_level <= 2)
                    <p class="text-sm text-green-600 font-medium">Leadership Position</p>
                @endif
                <p class="text-sm text-gray-500">Level {{ $studentOfficer->position_level }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Council Officers -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Council Officers</h3>

        @if($officers->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Officer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($officers as $officer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-3">
                                            @if($officer->student->profile_picture)
                                                <img src="{{ asset('storage/' . $officer->student->profile_picture) }}"
                                                     alt="{{ $officer->student->first_name }}"
                                                     class="h-10 w-10 rounded-full object-cover">
                                            @else
                                                <i class="fas fa-user text-gray-500"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $officer->student->first_name }} {{ $officer->student->last_name }}
                                                @if($officer->student_id === auth()->user()->id)
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        You
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">{{ $officer->student->id_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $officer->position_title }}</div>
                                    @if($officer->position_level <= 2)
                                        <div class="text-sm text-green-600 font-medium">Leadership</div>
                                    @else
                                        <div class="text-sm text-gray-500">Level {{ $officer->position_level }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $officer->student->department->abbreviation }}</div>
                                    <div class="text-sm text-gray-500">{{ $officer->student->department->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $officer->student->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="mailto:{{ $officer->student->email }}"
                                       class="text-green-600 hover:text-green-900 mr-3">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Officers Assigned</h4>
                <p class="text-gray-400">Council officers will appear here once they are assigned by the adviser.</p>
            </div>
        @endif
    </div>
</div>
@endsection
