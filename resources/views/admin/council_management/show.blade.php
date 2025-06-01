@extends('admin.layout')

@section('title', 'Council Details - PSCERMS')
@section('page-title', 'Council Details')

@section('header-actions')
<div class="flex items-center space-x-4">
    <a href="{{ route('admin.council_management.edit', $council) }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-edit mr-2"></i>Edit Council
    </a>
    <a href="{{ route('admin.council_management.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to List
    </a>
</div>
@endsection

@section('content')
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
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500">Created</h4>
                <p class="text-lg font-semibold text-gray-900 mt-1">{{ $council->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Department and Adviser Information -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <!-- Department -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Department</h3>
        </div>
        <div class="p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-lg font-semibold text-gray-900">{{ $council->department->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $council->department->abbreviation }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Adviser -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Adviser</h3>
        </div>
        <div class="p-6">
            @if($council->adviser)
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-user-tie text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-900">{{ $council->adviser->first_name }} {{ $council->adviser->last_name }}</h4>
                        <p class="text-sm text-gray-500">{{ $council->adviser->email }}</p>
                        <p class="text-sm text-gray-500">ID: {{ $council->adviser->id_number }}</p>
                    </div>
                </div>
            @else
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-red-600">No Adviser Assigned</h4>
                        <p class="text-sm text-gray-500">Please assign an adviser to this council</p>
                        <a href="{{ route('admin.council_management.edit', $council) }}" class="text-sm text-blue-600 hover:text-blue-800">Edit Council</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Officers Section -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">
            <i class="fas fa-users mr-2"></i>
            Council Officers ({{ $council->councilOfficers->count() }})
        </h3>
        <p class="text-sm text-green-100 mt-1">View all assigned officers and their positions</p>
    </div>

    <div class="p-6">

            @if($council->councilOfficers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($council->councilOfficers->sortBy('position_level') as $officer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($officer->student->profile_picture)
                                                    <img class="h-10 w-10 rounded-full object-cover"
                                                         src="{{ asset('storage/' . $officer->student->profile_picture) }}"
                                                         alt="{{ $officer->student->first_name }}">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-400"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $officer->student->first_name }} {{ $officer->student->last_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $officer->student->id_number }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $officer->position_title }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $officer->position_level === '1' ? 'bg-purple-100 text-purple-800' : ($officer->position_level === '2' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $officer->position_level === '1' ? 'Executive' : ($officer->position_level === '2' ? 'Vice Executive' : 'Officer') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="text-sm text-gray-900">{{ $officer->student->department->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">{{ $officer->student->department->abbreviation ?? '' }}</div>
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
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Officers Assigned</h3>
                    <p class="text-gray-500">This council doesn't have any officers assigned yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
