@extends('adviser.layout')

@section('title', 'Edit Student')
@section('page-title', 'Edit Student')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <!-- Header -->
        <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Edit Student: {{ $student->first_name }} {{ $student->last_name }}</h3>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('adviser.student_management.show', $student) }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        View Profile
                    </a>
                    <a href="{{ route('adviser.student_management.index') }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Students
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('adviser.student_management.update', $student) }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- ID Number -->
                <div>
                    <label for="id_number" class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                    <input type="text"
                           id="id_number"
                           name="id_number"
                           value="{{ old('id_number', $student->id_number) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('id_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $student->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text"
                           id="first_name"
                           name="first_name"
                           value="{{ old('first_name', $student->first_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input type="text"
                           id="last_name"
                           name="last_name"
                           value="{{ old('last_name', $student->last_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Password Fields -->
            <div class="mb-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password (6 digits)
                        <span class="text-gray-500 font-normal">(leave blank to keep current)</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           placeholder="Enter 6-digit password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="mt-1 text-xs text-gray-500">Enter a 6-digit password</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('adviser.student_management.show', $student) }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    Update Student
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
