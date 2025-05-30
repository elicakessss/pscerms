@extends('adviser.layout')

@section('title', 'Student Management')
@section('page-title', 'Student Management')

@section('header-actions')
    <div class="flex items-center space-x-4">
        <!-- Search Bar -->
        <form method="GET" class="flex items-center space-x-2">
            <div class="relative">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search students..."
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                Search
            </button>
        </form>

        <!-- Add Student Button -->
        <a href="{{ route('adviser.student_management.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Add New Student
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Header Section -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Students List</h3>
            <div class="text-sm text-gray-500">
                Showing {{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}
                @if(request('search'))
                    for "<strong>{{ request('search') }}</strong>"
                @endif
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-green-600 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $student->id_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                @if($student->profile_picture)
                                    <img class="h-10 w-10 rounded-full object-cover"
                                         src="{{ asset('storage/' . $student->profile_picture) }}"
                                         alt="{{ $student->first_name }}">
                                @else
                                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <i class="fas fa-user text-green-600"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $student->first_name }} {{ $student->last_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="text-sm text-gray-900">{{ $student->department->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $student->department->abbreviation ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $student->email }}
                    </td>
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
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
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

    <!-- Pagination -->
    @if(method_exists($students, 'links'))
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $students->links() }}
    </div>
    @endif

    <!-- Students Count -->
    @if($students->count() > 0)
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <p class="text-sm text-gray-700">
            Showing {{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}
            @if(request('search'))
                for "<strong>{{ request('search') }}</strong>"
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
