@extends('admin.layout')

@section('title', 'Edit User - PSCERMS')
@section('page-title', 'Edit User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <!-- Header -->
        <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Edit User: {{ $user->full_name }}</h3>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.user_management.show', [$type, $user->id]) }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        View Profile
                    </a>
                    <a href="{{ route('admin.user_management.index') }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.user_management.update', [$type, $user->id]) }}" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- User Type Display -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                <div class="flex items-center space-x-3">
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                        {{ $type === 'student' ? 'bg-blue-100 text-blue-800' :
                           ($type === 'adviser' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($type === 'admin' ? 'Administrator' : $type) }}
                    </span>
                    <span class="text-sm text-gray-500">(User type cannot be changed)</span>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- ID Number -->
                <div>
                    <label for="id_number" class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                    <input type="text"
                           id="id_number"
                           name="id_number"
                           value="{{ old('id_number', $user->id_number) }}"
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
                           value="{{ old('email', $user->email) }}"
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
                           value="{{ old('first_name', $user->first_name) }}"
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
                           value="{{ old('last_name', $user->last_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           required>
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Department (for students and advisers) -->
            @if(in_array($type, ['student', 'adviser']))
            <div class="mb-6">
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select id="department_id"
                        name="department_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        required>
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        @if($department->abbreviation !== 'UNIWIDE' || $type !== 'student')
                            <option value="{{ $department->id }}"
                                    {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }} ({{ $department->abbreviation }})
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

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

            <!-- Profile Picture and Description (for students and advisers) -->
            @if(in_array($type, ['student', 'adviser']))
            <!-- Current Profile Picture -->
            @if($user->profile_picture)
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Profile Picture</label>
                <div class="flex items-center space-x-4">
                    <img class="h-16 w-16 rounded-full object-cover border-2 border-gray-300"
                         src="{{ asset('storage/' . $user->profile_picture) }}"
                         alt="{{ $user->full_name }}">
                    <span class="text-sm text-gray-500">Upload a new image to replace this picture</span>
                </div>
            </div>
            @endif

            <!-- Profile Picture Upload -->
            <div class="mb-6">
                <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $user->profile_picture ? 'New Profile Picture' : 'Profile Picture' }} (Optional)
                </label>
                <div class="flex items-center space-x-4">
                    <div id="preview-container" class="hidden">
                        <img id="preview-image" class="h-16 w-16 rounded-full object-cover border-2 border-gray-300" alt="Preview">
                    </div>
                    <input type="file"
                           id="profile_picture"
                           name="profile_picture"
                           accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                </div>
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
                          placeholder="Brief description about the user...">{{ old('description', $user->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.user_management.show', [$type, $user->id]) }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile picture preview
    const profilePictureInput = document.getElementById('profile_picture');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');

    if (profilePictureInput) {
        profilePictureInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
            }
        });
    }
});
</script>
@endsection
