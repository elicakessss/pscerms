@extends('admin.layout')

@section('title', 'Department Management - PSCERMS')
@section('page-title', 'Department Management')

@section('header-actions')
<div class="flex items-center space-x-4">
    <!-- Search Bar -->
    <form method="GET">
        <div class="relative">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search departments..."
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </form>

    <!-- Create Department Button -->
    <a href="{{ route('admin.departments.create') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Add Department
    </a>
</div>
@endsection

@section('content')
<!-- System Overview Section -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <!-- Total Departments -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-building text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Departments</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_departments'] }}</p>
            </div>
        </div>
    </div>

    <!-- Departments with Students -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-user-graduate text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">With Students</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['departments_with_students'] }}</p>
            </div>
        </div>
    </div>

    <!-- Departments with Advisers -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-chalkboard-teacher text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">With Advisers</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['departments_with_advisers'] }}</p>
            </div>
        </div>
    </div>

    <!-- Departments with Councils -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
                <i class="fas fa-users-cog text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">With Councils</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['departments_with_councils'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- All Departments Section -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">All Departments</h3>
        <p class="text-sm text-green-100 mt-1">View and manage all academic departments</p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
            <a href="{{ route('admin.departments.index') }}"
               class="py-4 px-6 text-sm font-medium border-b-2 border-green-500 text-green-600">
                <i class="fas fa-building mr-2"></i>
                All Departments ({{ $departments->total() }})
            </a>
        </nav>
    </div>

    <!-- Departments Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Abbreviation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Advisers</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Councils</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($departments as $department)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                                <div class="text-sm text-gray-500">Created {{ $department->created_at->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $department->abbreviation === 'UNIWIDE' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $department->abbreviation }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($department->abbreviation === 'UNIWIDE')
                                <span class="text-gray-400">—</span>
                            @else
                                {{ $department->students_count }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $department->advisers_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $department->councils_count }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.departments.show', $department) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>
                            <a href="{{ route('admin.departments.edit', $department) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </a>
                            @if($department->abbreviation !== 'UNIWIDE' && $department->students_count == 0 && $department->advisers_count == 0 && $department->councils_count == 0)
                                <button onclick="confirmDelete({{ $department->id }}, '{{ $department->name }}')"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-building text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium mb-2">No departments found</h3>
                                <p class="text-sm">Get started by creating your first department.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($departments->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $departments->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
                <p class="text-gray-600 mb-4">Are you sure you want to delete the department "<span id="deleteDepartmentName"></span>"? This action cannot be undone.</p>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end space-x-3">
                        <button type="button"
                                onclick="closeDeleteModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition-colors">
                            Delete Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(departmentId, departmentName) {
    document.getElementById('deleteDepartmentName').textContent = departmentName;
    document.getElementById('deleteForm').action = `/admin/departments/${departmentId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

</script>
@endsection
