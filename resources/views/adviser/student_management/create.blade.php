@extends('adviser.layout')

@section('title', 'Add New Student')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Add New Student</h1>
        <a href="{{ route('adviser.student_management.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-800">Student Information</h2>
        </div>

        <form action="{{ route('adviser.student_management.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- ID Number -->
                <div>
                    <label for="id_number" class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                    <input type="text"
                           id="id_number"
                           name="id_number"
                           value="{{ old('id_number') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="e.g., 2022-01-1366"
                           required>
                    @error('id_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select id="department_id"
                            name="department_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            {{ !$isUniwideAdviser ? 'disabled' : '' }}
                            required>
                        @if($isUniwideAdviser)
                            @foreach($departments as $department)
                                @if($department->abbreviation !== 'UNIWIDE')
                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }} ({{ $department->abbreviation }})
                                    </option>
                                @endif
                            @endforeach
                        @else
                            <!-- For non-UNIWIDE advisers, show only their department -->
                            <option value="{{ auth()->user()->department_id }}" selected>
                                {{ auth()->user()->department->name }} ({{ auth()->user()->department->abbreviation }})
                            </option>
                        @endif
                    </select>
                    @if(!$isUniwideAdviser)
                        <input type="hidden" name="department_id" value="{{ auth()->user()->department_id }}">
                    @endif
                    @error('department_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Personal Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text"
                           id="first_name"
                           name="first_name"
                           value="{{ old('first_name') }}"
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
                           value="{{ old('last_name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Contact Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password (6 digits)</label>
                    <input type="password"
                           id="password"
                           name="password"
                           value="{{ old('password') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="Leave blank for default (123456)"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           title="Password must be exactly 6 digits">
                    <p class="mt-1 text-xs text-gray-500">If left blank, default password will be 123456</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Profile Picture -->
            <div class="mb-6">
                <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                <input type="file"
                       id="profile_picture"
                       name="profile_picture"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                       accept="image/*">
                <p class="mt-1 text-xs text-gray-500">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB.</p>
                @error('profile_picture')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          placeholder="Additional information about the student">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg">
                    Create Student
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
