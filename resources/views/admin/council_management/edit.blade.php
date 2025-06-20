@extends('admin.layout')

@section('title', 'Edit Council - PSCERMS')
@section('page-title', 'Edit Council')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
            <h3 class="text-lg font-semibold text-white">Edit Council</h3>
            <p class="text-sm text-green-100 mt-1">Update the council details</p>
        </div>

        <form action="{{ route('admin.council_management.update', $council) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Council Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Council Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $council->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., Paulinian Student Government - SITE"
                           readonly>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-500 mt-1">Council name will be automatically updated based on the selected department.</p>
                </div>

                <!-- Academic Year -->
                <div>
                    <label for="academic_year" class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                    <input type="text"
                           id="academic_year"
                           name="academic_year"
                           value="{{ old('academic_year', $council->academic_year) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('academic_year') border-red-500 @enderror"
                           placeholder="e.g., 2024-2025"
                           required>
                    @error('academic_year')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="status"
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('status') border-red-500 @enderror"
                            required>
                        <option value="">Select Status</option>
                        <option value="active" {{ old('status', $council->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ old('status', $council->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                    <select id="department_id"
                            name="department_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('department_id') border-red-500 @enderror"
                            required
                            onchange="updateCouncilName(); filterAdvisers();">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}"
                                    data-abbreviation="{{ $department->abbreviation }}"
                                    {{ old('department_id', $council->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adviser -->
                <div>
                    <label for="adviser_id" class="block text-sm font-medium text-gray-700 mb-2">Adviser</label>
                    <select id="adviser_id"
                            name="adviser_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('adviser_id') border-red-500 @enderror"
                            required>
                        <option value="">Select Adviser</option>
                        @foreach($advisers as $adviser)
                            <option value="{{ $adviser->id }}"
                                    data-department="{{ $adviser->department_id }}"
                                    {{ old('adviser_id', $council->adviser_id) == $adviser->id ? 'selected' : '' }}>
                                {{ $adviser->first_name }} {{ $adviser->last_name }} ({{ $adviser->department->abbreviation }})
                            </option>
                        @endforeach
                    </select>
                    @error('adviser_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.council_management.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                    <i class="fas fa-save mr-2"></i>Update Council
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateCouncilName() {
    const departmentSelect = document.getElementById('department_id');
    const nameInput = document.getElementById('name');
    const selectedOption = departmentSelect.options[departmentSelect.selectedIndex];

    if (selectedOption && selectedOption.value !== '') {
        const abbreviation = selectedOption.getAttribute('data-abbreviation');
        nameInput.value = `Paulinian Student Government - ${abbreviation}`;
    } else {
        nameInput.value = '';
    }
}

function filterAdvisers() {
    const departmentSelect = document.getElementById('department_id');
    const adviserSelect = document.getElementById('adviser_id');
    const selectedDepartment = departmentSelect.value;
    const currentAdviser = adviserSelect.value;

    // Show/hide advisers based on selected department
    const adviserOptions = adviserSelect.querySelectorAll('option');
    adviserOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = 'block'; // Always show the "Select Adviser" option
        } else {
            const adviserDepartment = option.getAttribute('data-department');
            if (selectedDepartment === '' || adviserDepartment === selectedDepartment) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
                // If the currently selected adviser is being hidden, reset selection
                if (option.value === currentAdviser) {
                    adviserSelect.value = '';
                }
            }
        }
    });
}

// Initialize the filter on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCouncilName();
    filterAdvisers();
});
</script>
@endsection
