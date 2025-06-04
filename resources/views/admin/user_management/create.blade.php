@extends('admin.layout')

@section('title', 'Create User - PSCERMS')
@section('page-title', 'Create New User')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Add New User</h3>
                <a href="{{ route('admin.user_management.index') }}"
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Users
                </a>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.user_management.store') }}" enctype="multipart/form-data" class="p-6">
            @csrf

            <!-- User Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                <div class="grid grid-cols-3 gap-4">
                    <label class="relative">
                        <input type="radio" name="user_type" value="student" class="sr-only peer"
                               {{ old('user_type') === 'student' ? 'checked' : '' }} required>
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-colors">
                            <div class="text-center">
                                <i class="fas fa-user-graduate text-2xl text-blue-600 mb-2"></i>
                                <h4 class="font-medium text-gray-900">Student</h4>
                                <p class="text-sm text-gray-500">Regular student account</p>
                            </div>
                        </div>
                    </label>

                    <label class="relative">
                        <input type="radio" name="user_type" value="adviser" class="sr-only peer"
                               {{ old('user_type') === 'adviser' ? 'checked' : '' }} required>
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-colors">
                            <div class="text-center">
                                <i class="fas fa-chalkboard-teacher text-2xl text-purple-600 mb-2"></i>
                                <h4 class="font-medium text-gray-900">Adviser</h4>
                                <p class="text-sm text-gray-500">Faculty adviser account</p>
                            </div>
                        </div>
                    </label>

                    <label class="relative">
                        <input type="radio" name="user_type" value="admin" class="sr-only peer"
                               {{ old('user_type') === 'admin' ? 'checked' : '' }} required>
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-300 transition-colors">
                            <div class="text-center">
                                <i class="fas fa-user-shield text-2xl text-red-600 mb-2"></i>
                                <h4 class="font-medium text-gray-900">Administrator</h4>
                                <p class="text-sm text-gray-500">Admin account</p>
                            </div>
                        </div>
                    </label>
                </div>
                @error('user_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

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

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                           placeholder="user@example.com"
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

            <!-- Department (shown for students and advisers) -->
            <div id="department-field" class="mb-6" style="display: none;">
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select id="department_id"
                        name="department_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        @if($department->abbreviation !== 'UNIWIDE' || old('user_type') !== 'student')
                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->name }} ({{ $department->abbreviation }})
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password (6 digits)
                        <span class="text-gray-500 font-normal">(optional - defaults to 123456)</span>
                    </label>
                    <input type="password"
                           id="password"
                           name="password"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           placeholder="Leave blank for default (123456)"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <p class="mt-1 text-xs text-gray-500">If left blank, default password will be 123456</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No confirmation field needed for password -->
            </div>

            <!-- Profile Picture and Description (shown for students and advisers) -->
            <div id="additional-fields" style="display: none;">
                <!-- Profile Picture -->
                <div class="mb-6">
                    <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Profile Picture (Optional)</label>
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
                              placeholder="Brief description about the user...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.user_management.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userTypeInputs = document.querySelectorAll('input[name="user_type"]');
    const departmentField = document.getElementById('department-field');
    const additionalFields = document.getElementById('additional-fields');
    const departmentSelect = document.getElementById('department_id');
    const departmentOptions = departmentSelect.querySelectorAll('option');

    function toggleFields() {
        const selectedType = document.querySelector('input[name="user_type"]:checked')?.value;

        if (selectedType === 'student' || selectedType === 'adviser') {
            departmentField.style.display = 'block';
            additionalFields.style.display = 'block';
            departmentSelect.required = true;

            // Hide or show UNIWIDE option based on user type
            departmentOptions.forEach(option => {
                const optionText = option.textContent;
                if (optionText.includes('UNIWIDE')) {
                    option.style.display = selectedType === 'student' ? 'none' : '';
                }
            });
        } else {
            departmentField.style.display = 'none';
            additionalFields.style.display = 'none';
            departmentSelect.required = false;
        }
    }

    userTypeInputs.forEach(input => {
        input.addEventListener('change', toggleFields);
    });

    // Initial toggle based on any pre-selected value
    toggleFields();

    // Profile picture preview
    const profilePictureInput = document.getElementById('profile_picture');
    const previewContainer = document.getElementById('preview-container');
    const previewImage = document.getElementById('preview-image');

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
});
</script>
@endsection
