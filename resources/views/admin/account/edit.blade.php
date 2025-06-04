@extends('admin.layout')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <!-- Header -->
        <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Edit Profile: {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h3>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.account.index') }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.account.update') }}" class="p-6">
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
                           value="{{ old('id_number', auth()->user()->id_number) }}"
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
                           value="{{ old('email', auth()->user()->email) }}"
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
                           value="{{ old('first_name', auth()->user()->first_name) }}"
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
                           value="{{ old('last_name', auth()->user()->last_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.account.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    Update Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Password Change Section -->
    <div class="bg-white rounded-lg shadow mt-6">
        <!-- Header -->
        <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
            <h3 class="text-lg font-medium text-white">Change Password</h3>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.account.update_password') }}" class="p-6">
            @csrf
            @method('PUT')

            <!-- Password Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password (6 digits)</label>
                    <input type="password"
                           id="current_password"
                           name="current_password"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           title="Password must be exactly 6 digits"
                           placeholder="Enter your current 6-digit password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password (6 digits)</label>
                    <input type="password"
                           id="password"
                           name="password"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           title="Password must be exactly 6 digits"
                           placeholder="Enter your new 6-digit password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="md:col-span-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           title="Password must be exactly 6 digits"
                           placeholder="Confirm your new 6-digit password"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Password must be exactly 6 digits (numbers only)</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.account.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
