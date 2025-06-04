@extends('adviser.layout')

@section('title', 'Leadership Certificate Request - PSCERMS')
@section('page-title', 'Leadership Certificate Request')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-2">Leadership Certificate Request</h2>
                    <p class="text-gray-600">
                        Review and respond to the leadership certificate request from {{ $request->student->first_name }} {{ $request->student->last_name }}.
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($request->status === 'approved') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($request->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Details -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Student Information -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Student Information</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Name:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->student->first_name }} {{ $request->student->last_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">ID Number:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->student->id_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Email:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->student->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Department:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->student->department->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Graduating:</span>
                            <span class="text-sm font-medium {{ $request->is_graduating ? 'text-green-600' : 'text-gray-900' }}">
                                {{ $request->is_graduating ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Certificate Information -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Certificate Information</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Certificate Type:</span>
                            <span class="text-sm font-medium text-gray-900">{{ ucfirst($request->certificate_type) }} Leadership Award</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Council:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->council->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Academic Year:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->council->academic_year }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Department:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->council->department->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Requested:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->requested_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($studentOfficer)
                <!-- Student Performance Results -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-trophy mr-2 text-yellow-600"></i>
                        Student Performance Results
                    </h4>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Position Held:</span>
                                    <span class="text-sm font-medium">{{ $studentOfficer->position_title }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Final Score:</span>
                                    <span class="text-sm font-medium">{{ number_format($studentOfficer->final_score, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Award Rank:</span>
                                    <span class="text-sm font-bold
                                        @if($studentOfficer->rank === 'Gold') text-yellow-600
                                        @elseif($studentOfficer->rank === 'Silver') text-gray-600
                                        @elseif($studentOfficer->rank === 'Bronze') text-orange-600
                                        @else text-red-600 @endif">
                                        {{ $studentOfficer->rank ?? 'Not Available' }}
                                    </span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Self Evaluation:</span>
                                    <span class="text-sm font-medium">{{ $studentOfficer->self_score ? number_format($studentOfficer->self_score, 2) : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Peer Evaluation:</span>
                                    <span class="text-sm font-medium">{{ $studentOfficer->peer_score ? number_format($studentOfficer->peer_score, 2) : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Adviser Evaluation:</span>
                                    <span class="text-sm font-medium">{{ $studentOfficer->adviser_score ? number_format($studentOfficer->adviser_score, 2) : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-yellow-100 rounded-md">
                            <p class="text-xs text-yellow-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                These performance results will be included in the leadership certificate as reference for the student's achievements.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <!-- No Performance Data -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-red-600"></i>
                        Performance Results
                    </h4>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            No performance evaluation data found for this student in the selected council. This may indicate that evaluations were not completed or scores were not calculated.
                        </p>
                    </div>
                </div>
            @endif
        </div>

            @if($request->status !== 'pending')
                <!-- Response Information -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-3">Response</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="text-sm font-medium {{ $request->status === 'approved' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Responded:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $request->responded_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                        @if($request->adviser_response)
                            <div class="mt-3">
                                <span class="text-sm text-gray-600">Response:</span>
                                <p class="text-sm text-gray-900 mt-1 p-3 bg-gray-50 rounded-lg">{{ $request->adviser_response }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    @if($request->status === 'pending')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>

                <div class="flex space-x-4">
                    <!-- Approve Button -->
                    <form action="{{ route('adviser.leadership_certificate.approve', $request) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                                onclick="return confirm('Are you sure you want to approve this certificate request?')">
                            <i class="fas fa-check mr-2"></i>
                            Approve Request
                        </button>
                    </form>

                    <!-- Dismiss Button -->
                    <button onclick="showDismissModal()"
                            class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Dismiss Request
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Back Button -->
    <div class="mt-6">
        <a href="{{ route('adviser.dashboard') }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>
</div>

<!-- Dismiss Modal -->
@if($request->status === 'pending')
<div id="dismissModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Dismiss Certificate Request</h3>
                <form action="{{ route('adviser.leadership_certificate.dismiss', $request) }}" method="POST">
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
function showDismissModal() {
    document.getElementById('dismissModal').classList.remove('hidden');
}

function hideDismissModal() {
    document.getElementById('dismissModal').classList.add('hidden');
    document.getElementById('reason').value = '';
}

// Close modal when clicking outside
document.getElementById('dismissModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDismissModal();
    }
});
</script>
@endif
@endsection
