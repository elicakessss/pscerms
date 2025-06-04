@extends('admin.layout')

@section('title', 'Edit Department')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <!-- Header -->
        <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white">Edit Department: {{ $department->name }}</h3>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.departments.show', $department) }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        View Department
                    </a>
                    <a href="{{ route('admin.departments.index') }}"
                       class="text-green-100 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Departments
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('admin.departments.update', $department) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Department Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Department Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $department->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., School of Computer Science"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department Abbreviation -->
                <div>
                    <label for="abbreviation" class="block text-sm font-medium text-gray-700 mb-2">
                        Department Abbreviation <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="abbreviation"
                           id="abbreviation"
                           value="{{ old('abbreviation', $department->abbreviation) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('abbreviation') border-red-500 @enderror"
                           placeholder="e.g., SCS"
                           maxlength="20"
                           style="text-transform: uppercase;"
                           required
                           @if($department->abbreviation === 'UNIWIDE') readonly @endif>
                    @error('abbreviation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @if($department->abbreviation === 'UNIWIDE')
                    <p class="text-amber-600 text-sm mt-1">
                        <i class="fas fa-lock mr-1"></i>
                        UNIWIDE abbreviation cannot be changed as it has special system functions.
                    </p>
                @else
                    <p class="text-gray-500 text-sm mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Use a short, unique abbreviation (max 20 characters). Will be converted to uppercase.
                    </p>
                @endif
                </div>
            </div>

            <!-- Department Statistics -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Department Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $department->students()->count() }}</div>
                        <div class="text-sm text-gray-600">Students</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $department->advisers()->count() }}</div>
                        <div class="text-sm text-gray-600">Advisers</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">{{ $department->councils()->count() }}</div>
                        <div class="text-sm text-gray-600">Councils</div>
                    </div>
                </div>
            </div>

            <!-- Warning for UNIWIDE -->
            @if($department->abbreviation === 'UNIWIDE')
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-amber-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-amber-800">Special Department</h3>
                            <div class="mt-2 text-sm text-amber-700">
                                <p>This is the UNIWIDE department which has special system functions:</p>
                                <ul class="list-disc list-inside mt-1 space-y-1">
                                    <li>Has unique council positions (President, Senators, Congressmen, Justices)</li>
                                    <li>Advisers can manage students from all departments</li>
                                    <li>Cannot be deleted from the system</li>
                                    <li>Abbreviation cannot be changed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.departments.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Department
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-uppercase abbreviation input (only if not UNIWIDE)
@if($department->abbreviation !== 'UNIWIDE')
document.getElementById('abbreviation').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});
@endif


</script>
@endsection
