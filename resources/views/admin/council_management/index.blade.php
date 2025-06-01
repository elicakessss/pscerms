@extends('admin.layout')

@section('title', 'Council Management - PSCERMS')
@section('page-title', 'Council Management')

@section('header-actions')
<div class="flex items-center space-x-4">
    <!-- Search Bar -->
    <form method="GET">
        <input type="hidden" name="status" value="{{ request('status') }}">
        <input type="hidden" name="department" value="{{ request('department') }}">
        <div class="relative">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search councils..."
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </form>

    <!-- Create Council Button -->
    <a href="{{ route('admin.council_management.create') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-plus mr-2"></i>Create Council
    </a>
</div>
@endsection

@section('content')
<!-- System Overview Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Total Councils -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-users-cog text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Councils</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_councils'] }}</p>
            </div>
        </div>
    </div>

    <!-- Active Councils -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Active Councils</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['active_councils'] }}</p>
            </div>
        </div>
    </div>

    <!-- Completed Councils -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-gray-100 rounded-lg">
                <i class="fas fa-flag-checkered text-gray-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Completed Councils</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_councils'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- All Councils Section -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">All Councils</h3>
        <p class="text-sm text-green-100 mt-1">View and manage all councils across departments</p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
            <a href="{{ request()->fullUrlWithQuery(['status' => '']) }}"
               class="py-4 px-6 text-sm font-medium border-b-2 transition-colors {{ request('status') === null || request('status') === '' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fas fa-users-cog mr-2"></i>
                All Councils ({{ $councils->total() }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'active']) }}"
               class="py-4 px-6 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'active' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fas fa-check-circle mr-2"></i>
                Active ({{ $stats['active_councils'] }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}"
               class="py-4 px-6 text-sm font-medium border-b-2 transition-colors {{ request('status') === 'completed' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                <i class="fas fa-flag-checkered mr-2"></i>
                Completed ({{ $stats['completed_councils'] }})
            </a>
            <div class="ml-auto flex items-center px-6">
                <label class="text-sm font-medium text-gray-700 mr-2">Department:</label>
                <form method="GET" class="inline">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <select name="department" onchange="this.form.submit()"
                            class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </nav>
    </div>

    <!-- Councils Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Council</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adviser</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Officers</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($councils as $council)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $council->name }}</div>
                                <div class="text-sm text-gray-500">Created {{ $council->created_at->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($council->adviser)
                                <div class="text-sm text-gray-900">{{ $council->adviser->first_name }} {{ $council->adviser->last_name }}</div>
                                <div class="text-sm text-gray-500">{{ $council->adviser->email }}</div>
                            @else
                                <div class="text-sm text-red-600">No Adviser Assigned</div>
                                <div class="text-sm text-gray-500">Please assign an adviser</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $council->academic_year }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($council->status === 'active')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Completed
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $council->councilOfficers->count() }} officers
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('admin.council_management.show', $council) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>
                            <a href="{{ route('admin.council_management.edit', $council) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                <i class="fas fa-edit mr-1"></i>
                                Edit
                            </a>
                            <button onclick="confirmDelete({{ $council->id }}, '{{ $council->name }}')"
                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users-cog text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium mb-2">No councils found</h3>
                                <p class="text-sm">Get started by creating your first council.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($councils->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $councils->appends(request()->query())->links() }}
        </div>
    @endif
</div>



<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirm Delete</h3>
                <p class="text-gray-600 mb-4">Are you sure you want to delete the council "<span id="deleteCouncilName"></span>"? This action cannot be undone.</p>
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
                            Delete Council
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(councilId, councilName) {
    document.getElementById('deleteCouncilName').textContent = councilName;
    document.getElementById('deleteForm').action = `/admin/council_management/${councilId}`;
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
