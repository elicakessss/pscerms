@extends('admin.layout')

@section('title', 'View User - PSCERMS')
@section('page-title', 'User Details')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">User Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('admin.user_management.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="md:flex">
        <!-- Profile Picture and Basic Info -->
        <div class="md:w-1/3 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex flex-col items-center text-center">
                @if(isset($user->profile_picture) && $user->profile_picture)
                <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->full_name }}" class="h-32 w-32 object-cover rounded-full mb-4">
                @else
                <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <i class="fas fa-user text-gray-400 text-4xl"></i>
                </div>
                @endif

                <h2 class="text-xl font-bold text-gray-800">{{ $user->full_name }}</h2>
                <p class="text-gray-600 mb-2">{{ $user->id_number }}</p>
                <p class="text-gray-600 mb-4">{{ $user->email }}</p>

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    @if(isset($user->department))
                        {{ $user->department->abbreviation }} - {{ $user->department->name }}
                    @else
                        {{ ucfirst($type === 'admin' ? 'Administrator' : $type) }}
                    @endif
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">User Information</h3>

            <div class="space-y-4">
                <!-- Description -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Description</h4>
                    <p class="text-gray-800">
                        @if(isset($user->description) && $user->description)
                            {{ $user->description }}
                        @else
                            No description available.
                        @endif
                    </p>
                </div>

                <!-- Created & Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                        <p class="text-gray-800">{{ $user->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                        <p class="text-gray-800">{{ $user->updated_at->format('F j, Y, g:i a') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Actions</h4>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.user_management.edit', [$type, $user->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-edit mr-2"></i> Edit User
                        </a>
                        <a href="mailto:{{ $user->email }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-envelope mr-2"></i> Send Email
                        </a>
                        <form action="{{ route('admin.user_management.destroy', [$type, $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-trash mr-2"></i> Delete User
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
