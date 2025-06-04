@extends('admin.layout')

@section('title', 'Add New Department')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="bg-green-600 text-white p-6 rounded-lg shadow-lg mb-6">
        <div class="flex items-center">
            <a href="{{ route('admin.departments.index') }}"
               class="text-white hover:text-green-200 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold">Add New Department</h1>
                <p class="text-green-100 mt-2">Create a new academic department</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Department Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Department Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror"
                       placeholder="e.g., School of Computer Science"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
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
                       value="{{ old('abbreviation') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('abbreviation') border-red-500 @enderror"
                       placeholder="e.g., SCS"
                       maxlength="20"
                       style="text-transform: uppercase;"
                       required>
                @error('abbreviation')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-sm mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Use a short, unique abbreviation (max 20 characters). Will be converted to uppercase.
                </p>
            </div>



            <!-- Important Note -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Important Information</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>New departments will use the standard council positions (Governor, Vice Governor, Councilors, etc.)</li>
                                <li>Only the UNIWIDE department has unique positions (President, Senators, Congressmen, Justices)</li>
                                <li>Department abbreviations must be unique across the system</li>
                                <li>Once created, you can assign advisers and students to this department</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.departments.index') }}"
                   class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Create Department
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-uppercase abbreviation input
document.getElementById('abbreviation').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});


</script>
@endsection
