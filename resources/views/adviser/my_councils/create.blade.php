@extends('adviser.layout')

@section('title', 'Create Council - PSCERMS')
@section('page-title', 'Create New Council')

@section('content')
<!-- Back Button -->
<div class="mb-6">
    <a href="{{ route('adviser.councils.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>
        Back to My Councils
    </a>
</div>

<!-- Council Creation Form -->
<div class="bg-white rounded-lg shadow p-6">
    <form action="{{ route('adviser.councils.store') }}" method="POST">
        @csrf

        <!-- Department Info (Read-only) -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Council Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <p class="text-gray-900 font-medium">{{ $adviser->department->name }}</p>
                    <p class="text-sm text-gray-500">{{ $adviser->department->abbreviation }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adviser</label>
                    <p class="text-gray-900 font-medium">{{ $adviser->first_name }} {{ $adviser->last_name }}</p>
                    <p class="text-sm text-gray-500">{{ $adviser->id_number }}</p>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Council Name</label>
                <p class="text-gray-900 font-medium">Paulinian Student Government - {{ $adviser->department->abbreviation }}</p>
                <p class="text-sm text-gray-500">Council name is automatically generated based on your department</p>
            </div>
        </div>

        <!-- Academic Year -->
        <div class="mb-6">
            <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-2">
                Academic Year <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="academic_year"
                   name="academic_year"
                   value="{{ old('academic_year', date('Y') . '-' . (date('Y') + 1)) }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('academic_year') border-red-500 @enderror"
                   placeholder="e.g., 2024-2025"
                   pattern="\d{4}-\d{4}"
                   title="Academic year must be in YYYY-YYYY format (e.g., 2024-2025)"
                   required>
            @error('academic_year')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
            <p class="text-sm text-gray-500 mt-1">
                Format: YYYY-YYYY (e.g., 2024-2025). Must be consecutive years.
            </p>
        </div>

        <!-- Important Notes -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                <div>
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Important Notes:</h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Each department can only have one council per academic year</li>
                        <li>• The council will be automatically assigned to you as the adviser</li>
                        <li>• You can start assigning officers after creating the council</li>
                        <li>• Academic year format must be YYYY-YYYY (consecutive years)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('adviser.councils.index') }}"
               class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Council
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const academicYearInput = document.getElementById('academic_year');

    academicYearInput.addEventListener('input', function() {
        const value = this.value;
        const pattern = /^\d{4}-\d{4}$/;

        if (pattern.test(value)) {
            const years = value.split('-');
            const startYear = parseInt(years[0]);
            const endYear = parseInt(years[1]);

            if (endYear !== startYear + 1) {
                this.setCustomValidity('Academic year must be consecutive years (e.g., 2024-2025)');
            } else {
                this.setCustomValidity('');
            }
        } else if (value.length > 0) {
            this.setCustomValidity('Academic year must be in YYYY-YYYY format');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
@endsection
