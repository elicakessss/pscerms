@extends('admin.layout')

@section('title', 'User Management - PSCERMS')
@section('page-title', 'User Management')

@section('header-actions')
    <div class="flex items-center space-x-4">
        <!-- Search Bar -->
        <form method="GET" class="flex items-center space-x-2">
            <input type="hidden" name="type" value="{{ $userType }}">
            <input type="hidden" name="department" value="{{ $department }}">
            <div class="relative">
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Search users..."
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                Search
            </button>
        </form>

        <!-- Add User Button -->
        <a href="{{ route('admin.user_management.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
            <i class="fas fa-plus"></i>
            <span>Add User</span>
        </a>
    </div>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow">
    <!-- Filters Section -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- User Type Tabs -->
            <div class="flex bg-gray-100 rounded-lg p-1">
                <a href="{{ request()->fullUrlWithQuery(['type' => 'all']) }}"
                   class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $userType === 'all' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    All
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'students']) }}"
                   class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $userType === 'students' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Students
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'advisers']) }}"
                   class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $userType === 'advisers' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Advisers
                </a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'admins']) }}"
                   class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $userType === 'admins' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    Administrators
                </a>
            </div>

            <!-- Department Filter -->
            @if($userType !== 'admins')
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Department:</label>
                <form method="GET" class="inline">
                    <input type="hidden" name="type" value="{{ $userType }}">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <select name="department" onchange="this.form.submit()"
                            class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $department == $dept->id ? 'selected' : '' }}>
                                {{ $dept->abbreviation }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Department Filter Buttons (for students only) -->
    @if($userType === 'students' || $userType === 'all')
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-wrap gap-2">
            @foreach($departments as $dept)
                <a href="{{ request()->fullUrlWithQuery(['department' => $dept->id, 'type' => 'students']) }}"
                   class="px-3 py-1.5 text-xs rounded-full border transition-colors {{ $department == $dept->id ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300 hover:border-green-500' }}">
                    {{ $dept->abbreviation }}
                </a>
            @endforeach
            @if($department)
                <a href="{{ request()->fullUrlWithQuery(['department' => null]) }}"
                   class="px-3 py-1.5 text-xs rounded-full bg-gray-500 text-white hover:bg-gray-600 transition-colors">
                    Clear Filter
                </a>
            @endif
        </div>
    </div>
    @endif

    <!-- Users Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-green-600 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">ID Number</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if(isset($user->profile_picture) && $user->profile_picture)
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ asset('storage/' . $user->profile_picture) }}"
                                             alt="{{ $user->full_name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-user text-green-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->id_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->department->abbreviation ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $user->user_type === 'Student' ? 'bg-blue-100 text-blue-800' :
                                   ($user->user_type === 'Adviser' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') }}">
                                {{ $user->user_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <!-- View Button -->
                            <a href="{{ route('admin.user_management.show', [strtolower(str_replace('Administrator', 'admin', $user->user_type)), $user->id]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>

                            <!-- Edit Button -->
                            <a href="{{ route('admin.user_management.edit', [strtolower(str_replace('Administrator', 'admin', $user->user_type)), $user->id]) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </a>

                            <!-- Delete Button -->
                            <form method="POST"
                                  action="{{ route('admin.user_management.destroy', [strtolower(str_replace('Administrator', 'admin', $user->user_type)), $user->id]) }}"
                                  class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
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
                                <h3 class="text-lg font-medium mb-2">No users found</h3>
                                <p class="text-sm">{{ $search ? 'Try adjusting your search criteria' : 'Get started by creating your first user' }}</p>
                                @if(!$search)
                                    <a href="{{ route('admin.user_management.create') }}"
                                       class="mt-4 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                        Add First User
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Users Count -->
    @if($users->count() > 0)
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
        <p class="text-sm text-gray-700">
            Showing {{ $users->count() }} user{{ $users->count() !== 1 ? 's' : '' }}
            @if($search)
                for "<strong>{{ $search }}</strong>"
            @endif
            @if($department)
                in <strong>{{ $departments->find($department)->abbreviation }}</strong>
            @endif
        </p>
    </div>
    @endif
</div>
@endsection
