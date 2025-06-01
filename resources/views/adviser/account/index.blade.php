@extends('adviser.layout')

@section('title', 'Account')
@section('page-title', 'Account')

@section('content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <div class="md:flex">
        <!-- Profile Picture and Basic Info -->
        <div class="md:w-1/3 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex flex-col items-center text-center">
                @if(auth()->user()->profile_picture)
                <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="{{ auth()->user()->first_name }}" class="h-32 w-32 object-cover rounded-full mb-4">
                @else
                <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <i class="fas fa-user text-gray-400 text-4xl"></i>
                </div>
                @endif

                <h2 class="text-xl font-bold text-gray-800">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h2>
                <p class="text-gray-600 mb-2">{{ auth()->user()->id_number }}</p>
                <p class="text-gray-600 mb-4">{{ auth()->user()->email }}</p>

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ auth()->user()->department->abbreviation ?? 'N/A' }} - {{ auth()->user()->department->name ?? 'Not Assigned' }}
                </div>
            </div>
        </div>

        <!-- Account Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Account Information</h3>

            <div class="space-y-4">
                <!-- Description -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Description</h4>
                    <p class="text-gray-800">{{ auth()->user()->description ?? 'No description available.' }}</p>
                </div>

                <!-- Created & Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                        <p class="text-gray-800">{{ auth()->user()->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                        <p class="text-gray-800">{{ auth()->user()->updated_at->format('F j, Y, g:i a') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Actions</h4>
                    <div class="flex space-x-3">
                        <a href="{{ route('adviser.account.edit') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-edit mr-2"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



