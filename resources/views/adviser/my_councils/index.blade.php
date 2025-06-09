@extends('adviser.layout')

@section('title', 'My Councils - PSCERMS')
@section('page-title', 'My Councils')

@section('header-actions')
    <a href="{{ route('adviser.councils.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Create New Council</span>
    </a>
@endsection

@section('content')

<!-- Success Message -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

<!-- Error Messages -->
@if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <!-- Total Councils Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users-cog text-blue-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Councils</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_councils'] }}</p>
            </div>
        </div>
    </div>

    <!-- Active Councils Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Active Councils</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_councils'] }}</p>
            </div>
        </div>
    </div>

    <!-- Completed Councils Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-flag-checkered text-yellow-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Completed Councils</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['completed_councils'] }}</p>
            </div>
        </div>
    </div>

    <!-- Pending Evaluations Card -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-orange-600"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Pending Evaluations</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['pending_evaluations'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Councils Grid -->
@if($councils->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($councils as $council)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300">
                <div class="p-6">
                    <!-- Header with Icon and Status -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas fa-users-cog text-green-600 text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $council->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $council->academic_year }}</p>
                            </div>
                        </div>
                        <!-- Status Badge -->
                        @if($council->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Completed
                            </span>
                        @endif
                    </div>

                    <!-- Council Information -->
                    <div class="space-y-3 mb-4">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-calendar-alt text-gray-400 mr-3 w-4"></i>
                            <span class="font-medium">Academic Year:</span>
                            <span class="ml-2">{{ $council->academic_year }}</span>
                        </div>

                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users text-gray-400 mr-3 w-4"></i>
                            <span class="font-medium">Officers:</span>
                            <span class="ml-2">{{ $council->councilOfficers->count() }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex space-x-2">
                            <a href="{{ route('adviser.councils.show', $council) }}"
                               class="flex-1 bg-green-600 hover:bg-green-700 text-white py-1.5 px-2 rounded text-xs font-medium transition-colors flex items-center justify-center">
                                <i class="fas fa-eye mr-1"></i>
                                View
                            </a>

                            @if($council->canStartEvaluations())
                                <form id="start-evaluation-form-{{ $council->id }}" action="{{ route('adviser.councils.start_evaluations', $council) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="button"
                                            onclick="confirmStartEvaluation({{ $council->id }}, '{{ $council->name }}', {{ $council->councilOfficers->count() }})"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-1.5 px-2 rounded text-xs font-medium transition-colors">
                                        <i class="fas fa-play mr-1"></i>
                                        Start Evaluation
                                    </button>
                                </form>
                            @elseif($council->canFinalizeEvaluationInstance())
                                <form id="finalize-evaluation-form-{{ $council->id }}" action="{{ route('adviser.councils.finalize_evaluations', $council) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="button"
                                            onclick="confirmFinalizeEvaluation({{ $council->id }}, '{{ $council->name }}')"
                                            class="w-full bg-purple-600 hover:bg-purple-700 text-white text-center py-1.5 px-2 rounded text-xs font-medium transition-colors">
                                        <i class="fas fa-lock mr-1"></i>
                                        Finalize
                                    </button>
                                </form>
                            @elseif($council->isEvaluationInstanceFinalized())
                                <span class="flex-1 bg-gray-100 text-gray-600 text-center py-1.5 px-2 rounded text-xs font-medium cursor-not-allowed">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Evaluations Finalized
                                </span>
                            @elseif($council->hasEvaluations())
                                <span class="flex-1 bg-gray-100 text-gray-600 text-center py-1.5 px-2 rounded text-xs font-medium cursor-not-allowed">
                                    <i class="fas fa-clock mr-1"></i>
                                    Evaluations In Progress
                                </span>
                            @else
                                <span class="flex-1 bg-gray-100 text-gray-600 text-center py-1.5 px-2 rounded text-xs font-medium cursor-not-allowed">
                                    <i class="fas fa-users mr-1"></i>
                                    Need Officers
                                </span>
                            @endif

                            <button onclick="confirmDelete('{{ $council->id }}', '{{ $council->name }}')"
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-1.5 px-2 rounded text-xs font-medium transition-colors">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-users-cog text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Councils Created</h3>
        <p class="text-gray-500 mb-6">You haven't created any councils yet. Click the "Create New Council" button to get started.</p>
    </div>
@endif

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Confirm Council Deletion</h3>
                        <p class="text-sm text-gray-500">This action cannot be undone</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-gray-700">Are you sure you want to delete the council:</p>
                    <p class="font-semibold text-gray-900 mt-1" id="councilNameToDelete"></p>
                    <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            This will permanently delete the council, all its officers, and any associated evaluations and evaluation data. This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </button>
                    <button type="button" onclick="submitDelete()"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-trash mr-1"></i>
                        Delete Council
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
let councilToDelete = null;

function confirmDelete(councilId, councilName) {
    councilToDelete = councilId;
    document.getElementById('councilNameToDelete').textContent = councilName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    councilToDelete = null;
}

function submitDelete() {
    if (councilToDelete) {
        const form = document.getElementById('deleteForm');
        form.action = `/adviser/councils/${councilToDelete}`;
        form.submit();
    }
}

// Confirm start evaluation with modern modal
function confirmStartEvaluation(councilId, councilName, officerCount) {
    showConfirmation(
        'Start Evaluations',
        `This will create evaluation instances for all ${officerCount} officers in ${councilName}. Each officer will receive self-evaluation, peer evaluations from executives, and adviser evaluation.`,
        () => {
            document.getElementById(`start-evaluation-form-${councilId}`).submit();
        },
        'question'
    );
}

// Confirm finalize evaluation with modern modal
function confirmFinalizeEvaluation(councilId, councilName) {
    showConfirmation(
        'Finalize Evaluations',
        `This will finalize all evaluations for ${councilName}. Once finalized, evaluations cannot be edited and final scores will be calculated. This action cannot be undone.`,
        () => {
            document.getElementById(`finalize-evaluation-form-${councilId}`).submit();
        },
        'warning'
    );
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

@endsection
