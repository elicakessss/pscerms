@extends('admin.layout')

@section('title', 'Council Details - PSCERMS')
@section('page-title', 'Council Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Council Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('admin.council_management.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

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

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $council->department->abbreviation }} - {{ $council->department->name }}
                </div>
            </div>
        </div>

        <!-- Council Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Council Information</h3>

            <div class="space-y-4">
                <!-- Adviser Information -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Adviser</h4>
                    @if($council->adviser)
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <i class="fas fa-user-tie text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-gray-800 font-medium">{{ $council->adviser->first_name }} {{ $council->adviser->last_name }}</p>
                                <p class="text-sm text-gray-500">{{ $council->adviser->email }}</p>
                                <p class="text-sm text-gray-500">ID: {{ $council->adviser->id_number }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center">
                            <div class="p-2 bg-red-100 rounded-lg mr-3">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div>
                                <p class="text-red-600 font-medium">No Adviser Assigned</p>
                                <p class="text-sm text-gray-500">Please assign an adviser to this council</p>
                            </div>
                        </div>
                    @endif
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
                        <a href="{{ route('admin.council_management.edit', $council) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-edit mr-2"></i> Edit Council
                        </a>
                        @if($council->adviser)
                            <a href="mailto:{{ $council->adviser->email }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-envelope mr-2"></i> Email Adviser
                            </a>
                        @endif
                        <form action="{{ route('admin.council_management.destroy', $council) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this council? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-trash mr-2"></i> Delete Council
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Officers Section -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-users text-green-600 mr-2"></i>
            Council Officers
        </h3>

        @if($council->councilOfficers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($council->councilOfficers->sortBy('position_level') as $officer)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
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
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $officer->student->first_name }} {{ $officer->student->last_name }}</h4>
                                <p class="text-xs text-gray-500">{{ $officer->student->id_number }}</p>
                            </div>
                        </div>

                        <!-- Position and Level -->
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-700">{{ $officer->position_title }}</p>
                            <div class="mt-1">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $officer->position_level === '1' ? 'bg-purple-100 text-purple-800' : ($officer->position_level === '2' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $officer->position_level === '1' ? 'Executive' : ($officer->position_level === '2' ? 'Vice Executive' : 'Officer') }}
                                </span>
                            </div>
                        </div>

                        <!-- Department -->
                        @if($officer->student->department)
                            <div class="pt-2 border-t border-gray-200">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-building mr-1"></i>
                                    {{ $officer->student->department->name }}
                                    @if($officer->student->department->abbreviation)
                                        ({{ $officer->student->department->abbreviation }})
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-green-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Officers Assigned</h4>
                <p class="text-gray-400">This council doesn't have any officers assigned yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
