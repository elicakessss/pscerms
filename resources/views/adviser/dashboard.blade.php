@extends('adviser.layout')

@section('title', 'Dashboard')

@section('content')
<!-- Greetings Container -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Welcome back, {{ $adviser->first_name }}!</h2>
        <p class="text-gray-600">
            @if($isUniwideAdviser)
                You're managing students across all departments as a Uniwide Adviser.
            @else
                You're managing students in {{ $adviser->department->name ?? 'your department' }}.
            @endif
        </p>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Quick Access Cards -->
    <div class="space-y-6">
        <!-- My Councils Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">My Councils</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users-cog text-green-600 text-lg"></i>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Councils:</span>
                        <span class="text-lg font-semibold text-gray-800">{{ $councils->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Active:</span>
                        <span class="text-sm font-medium text-green-600">{{ $councils->where('status', 'active')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Completed:</span>
                        <span class="text-sm font-medium text-gray-600">{{ $councils->where('status', 'completed')->count() }}</span>
                    </div>
                </div>

                <a href="{{ route('adviser.councils.index') }}"
                   class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors inline-block">
                    <i class="fas fa-eye mr-2"></i>
                    View All Councils
                </a>
            </div>
        </div>

        <!-- Student Management Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Student Management</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-green-600 text-lg"></i>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">
                            @if($isUniwideAdviser)
                                All Students:
                            @else
                                Department Students:
                            @endif
                        </span>
                        <span class="text-lg font-semibold text-gray-800">{{ $studentCount }}</span>
                    </div>
                    @if($isUniwideAdviser)
                        <p class="text-xs text-blue-600">Managing students across all departments</p>
                    @endif
                </div>

                <a href="{{ route('adviser.student_management.index') }}"
                   class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors inline-block">
                    <i class="fas fa-users mr-2"></i>
                    Manage Students
                </a>
            </div>
        </div>


    </div>

    <!-- Right Column - Evaluation Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Evaluation Progress</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-green-600 text-lg"></i>
                    </div>
                </div>

                <!-- Overall Progress Bar -->
                @if($totalEvaluations > 0)
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Overall Progress</span>
                            <span class="text-sm text-gray-600">{{ $completedEvaluations }}/{{ $totalEvaluations }} ({{ $overallProgress }}%)</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-green-600 h-3 rounded-full transition-all duration-300" style="width: {{ $overallProgress }}%"></div>
                        </div>
                    </div>
                @endif

                <!-- Pending Evaluations List -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">
                        Pending Adviser Evaluations
                        @if($pendingEvaluations->count() > 0)
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full ml-2">
                                {{ $pendingEvaluations->count() }}
                            </span>
                        @endif
                    </h4>

                    @if($pendingEvaluations->count() > 0)
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($pendingEvaluations as $evaluation)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-900">
                                                {{ $evaluation->evaluatedStudent->first_name }} {{ $evaluation->evaluatedStudent->last_name }}
                                            </h5>
                                            <p class="text-sm text-gray-600">{{ $evaluation->council->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $evaluation->council->department->name }} • {{ $evaluation->council->academic_year }}
                                            </p>
                                        </div>
                                        <div class="ml-4">
                                            <a href="{{ route('adviser.evaluation.show', [$evaluation->council, $evaluation->evaluatedStudent]) }}"
                                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                                <i class="fas fa-edit mr-1"></i>
                                                Evaluate
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                            </div>
                            <h5 class="text-lg font-medium text-gray-500 mb-2">All Caught Up!</h5>
                            <p class="text-gray-400">No pending evaluations at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Leadership Certificate Requests -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Leadership Certificate Requests</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-certificate text-green-600 text-lg"></i>
                    </div>
                </div>

                <div class="mb-4">
                    @if($pendingCertificateRequests->count() > 0)
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($pendingCertificateRequests as $certificateRequest)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-900">
                                                {{ $certificateRequest->student->first_name }} {{ $certificateRequest->student->last_name }}
                                            </h5>
                                            <p class="text-sm text-gray-600">{{ $certificateRequest->council->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ ucfirst($certificateRequest->certificate_type) }} Leadership Award •
                                                {{ $certificateRequest->council->academic_year }}
                                                @if($certificateRequest->is_graduating)
                                                    • <span class="text-green-600">Graduating</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                Requested {{ $certificateRequest->requested_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="ml-4 flex space-x-2">
                                            <a href="{{ route('adviser.leadership_certificate.show', $certificateRequest) }}"
                                               class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-xs hover:bg-blue-200 transition-colors">
                                                View
                                            </a>
                                            <form action="{{ route('adviser.leadership_certificate.approve', $certificateRequest) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200 transition-colors"
                                                        onclick="return confirm('Are you sure you want to approve this certificate request?')">
                                                    Approve
                                                </button>
                                            </form>
                                            <button onclick="showDismissModal({{ $certificateRequest->id }})"
                                                    class="px-3 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200 transition-colors">
                                                Dismiss
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-certificate text-gray-400 text-xl"></i>
                            </div>
                            <h5 class="text-lg font-medium text-gray-500 mb-2">No Certificate Requests</h5>
                            <p class="text-gray-400">No pending leadership certificate requests at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dismiss Modal -->
<div id="dismissModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Dismiss Certificate Request</h3>
                <form id="dismissForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Reason (Optional)</label>
                        <textarea name="reason" id="reason" rows="3"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-red-500 focus:border-red-500"
                                  placeholder="Provide a reason for dismissing this request..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideDismissModal()"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Dismiss Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDismissModal(requestId) {
    const modal = document.getElementById('dismissModal');
    const form = document.getElementById('dismissForm');
    form.action = `/adviser/leadership-certificate/${requestId}/dismiss`;
    modal.classList.remove('hidden');
}

function hideDismissModal() {
    const modal = document.getElementById('dismissModal');
    modal.classList.add('hidden');
    document.getElementById('reason').value = '';
}

// Close modal when clicking outside
document.getElementById('dismissModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDismissModal();
    }
});
</script>
@endsection
