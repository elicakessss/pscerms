@extends('admin.layout')

@section('title', 'Department Details - PSCERMS')
@section('page-title', 'Department Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Department Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('admin.departments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="md:flex">
        <!-- Department Icon and Basic Info -->
        <div class="md:w-1/3 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex flex-col items-center text-center">
                <!-- Department Icon -->
                <div class="h-32 w-32 rounded-full bg-green-100 flex items-center justify-center mb-4">
                    @if($department->abbreviation === 'UNIWIDE')
                        <i class="fas fa-star text-purple-600 text-5xl"></i>
                    @else
                        <i class="fas fa-building text-green-600 text-5xl"></i>
                    @endif
                </div>

                <h2 class="text-xl font-bold text-gray-800">{{ $department->name }}</h2>
                <p class="text-gray-600 mb-4">{{ $department->abbreviation }}</p>

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium mb-4">
                    @if($department->abbreviation === 'UNIWIDE')
                        <i class="fas fa-star mr-1"></i>
                        Special Department
                    @else
                        Academic Department
                    @endif
                </div>

                <!-- Statistics -->
                <div class="w-full space-y-3">
                    @if($department->abbreviation !== 'UNIWIDE')
                        <div class="flex items-center justify-between p-2 bg-blue-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-users text-blue-600 mr-2 text-sm"></i>
                                <span class="text-xs font-medium text-gray-700">Students</span>
                            </div>
                            <span class="text-sm font-bold text-blue-600">{{ $department->students->count() }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-chalkboard-teacher text-green-600 mr-2 text-sm"></i>
                            <span class="text-xs font-medium text-gray-700">Advisers</span>
                        </div>
                        <span class="text-sm font-bold text-green-600">{{ $department->advisers->count() }}</span>
                    </div>

                    <div class="flex items-center justify-between p-2 bg-purple-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-users-cog text-purple-600 mr-2 text-sm"></i>
                            <span class="text-xs font-medium text-gray-700">Councils</span>
                        </div>
                        <span class="text-sm font-bold text-purple-600">{{ $department->councils->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Department Information</h3>

            <div class="space-y-4">
                <!-- Special Department Notice -->
                @if($department->abbreviation === 'UNIWIDE')
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-star text-purple-400"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-purple-800">Special Department Functions</h4>
                                <div class="mt-2 text-sm text-purple-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Administrative department - no students are assigned here</li>
                                        <li>Has unique council positions (President, Senators, Representatives, Justices)</li>
                                        <li>Advisers can manage students from all academic departments</li>
                                        <li>UNIWIDE councils contain students from academic departments only</li>
                                        <li>Cannot be deleted from the system</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Created & Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                        <p class="text-gray-800">{{ $department->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                        <p class="text-gray-800">{{ $department->updated_at->format('F j, Y, g:i a') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Actions</h4>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.departments.edit', $department) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-edit mr-2"></i> Edit Department
                        </a>
                        @if($department->abbreviation !== 'UNIWIDE')
                            <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this department? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                    <i class="fas fa-trash mr-2"></i> Delete Department
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($department->abbreviation !== 'UNIWIDE')
<!-- Students Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-blue-600 mr-2"></i>
            Department Students
        </h3>

        @if($department->students->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($department->students->take(9) as $student)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <!-- Student Header -->
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-12 w-12 mr-3">
                                @if($student->profile_picture)
                                    <img class="h-12 w-12 rounded-full object-cover"
                                         src="{{ asset('storage/' . $student->profile_picture) }}"
                                         alt="{{ $student->first_name }}">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">
                                            {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $student->first_name }} {{ $student->last_name }}</h4>
                                <p class="text-xs text-gray-500">{{ $student->id_number }}</p>
                            </div>
                        </div>

                        <!-- Student Info -->
                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                Joined: {{ $student->created_at->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($department->students->count() > 9)
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        Showing 9 of {{ $department->students->count() }} students
                    </p>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-blue-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Students Assigned</h4>
                <p class="text-gray-400">This department doesn't have any students assigned yet.</p>
            </div>
        @endif
    </div>
</div>
@endif

<!-- Advisers Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chalkboard-teacher text-green-600 mr-2"></i>
            Department Advisers
        </h3>

        @if($department->advisers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($department->advisers as $adviser)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <!-- Adviser Header -->
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-12 w-12 mr-3">
                                @if($adviser->profile_picture)
                                    <img class="h-12 w-12 rounded-full object-cover"
                                         src="{{ asset('storage/' . $adviser->profile_picture) }}"
                                         alt="{{ $adviser->first_name }}">
                                @else
                                    <div class="h-12 w-12 rounded-full bg-green-500 flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">
                                            {{ substr($adviser->first_name, 0, 1) }}{{ substr($adviser->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $adviser->first_name }} {{ $adviser->last_name }}</h4>
                                <p class="text-xs text-gray-500">{{ $adviser->id_number }}</p>
                            </div>
                        </div>

                        <!-- Adviser Info -->
                        <div class="mb-3">
                            <p class="text-sm text-gray-600">{{ $adviser->email }}</p>
                        </div>

                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                Joined: {{ $adviser->created_at->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-chalkboard-teacher text-green-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Advisers Assigned</h4>
                <p class="text-gray-400">This department doesn't have any advisers assigned yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
