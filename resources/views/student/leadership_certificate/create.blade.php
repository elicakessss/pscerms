@extends('student.layout')

@section('title', 'Request Leadership Certificate - PSCERMS')
@section('page-title', 'Request Leadership Certificate')

@section('content')
<div class="max-w-4xl mx-auto">

    @if(!$canRequestCampus && !$canRequestDepartmental)
        <!-- No Completed Councils -->
        <div class="text-center py-12">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-certificate text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Completed Council Service</h3>
            <p class="text-gray-500 max-w-md mx-auto mb-4">
                You need to complete service in at least one council to request a leadership certificate.
            </p>
            <a href="{{ route('student.dashboard') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    @else
        <!-- Request Form -->
        <div class="bg-white rounded-lg shadow">
            <!-- Header -->
            <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-white">Request Leadership Certificate</h3>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('student.leadership_certificate.index') }}"
                           class="text-green-100 hover:text-white transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to Requests
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('student.leadership_certificate.store') }}" method="POST" class="p-6">
                @csrf

                    <!-- Certificate Type Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Certificate Type</label>
                        <div class="space-y-4">
                            @if($canRequestCampus)
                                <!-- Campus Leadership Award -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="certificate_type" value="campus"
                                               class="mt-1 text-green-600 focus:ring-green-500"
                                               {{ old('certificate_type') == 'campus' ? 'checked' : '' }}
                                               onchange="showTermDetails()">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Campus Leadership Award</div>
                                            <div class="text-sm text-gray-600">For UNIWIDE council participation</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Based on your latest UNIWIDE council term
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endif

                            @if($canRequestDepartmental)
                                <!-- Departmental Leadership Award -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="certificate_type" value="departmental"
                                               class="mt-1 text-green-600 focus:ring-green-500"
                                               {{ old('certificate_type') == 'departmental' ? 'checked' : '' }}
                                               onchange="showTermDetails()">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">Departmental Leadership Award</div>
                                            <div class="text-sm text-gray-600">For departmental council participation</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Based on your latest departmental council term
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endif
                        </div>
                        @error('certificate_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                <!-- Term Details Display -->
                <div class="mb-6">
                    @if($canRequestCampus && $latestUniwideOfficer)
                        <!-- Campus Term Details -->
                        <div id="campus_term_details" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-blue-900 mb-3 flex items-center">
                                <i class="fas fa-trophy mr-2"></i>
                                Campus Leadership Award - Latest Term Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Council:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->council->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Academic Year:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->council->academic_year ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Position:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->position_title ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Final Score:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->final_score ? number_format($latestUniwideOfficer->final_score, 2) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Award Rank:</span>
                                        <span class="text-sm font-bold
                                            @if($latestUniwideOfficer->ranking_category === 'Gold') text-yellow-600
                                            @elseif($latestUniwideOfficer->ranking_category === 'Silver') text-gray-600
                                            @elseif($latestUniwideOfficer->ranking_category === 'Bronze') text-orange-600
                                            @elseif($latestUniwideOfficer->ranking_category === 'Certificate') text-blue-600
                                            @else text-red-600 @endif">
                                            {{ $latestUniwideOfficer->ranking_category ?? 'Not Available' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Self Score:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->self_score ? number_format($latestUniwideOfficer->self_score, 2) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Peer Score:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->peer_score ? number_format($latestUniwideOfficer->peer_score, 2) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Adviser Score:</span>
                                        <span class="text-sm font-medium">{{ $latestUniwideOfficer->adviser_score ? number_format($latestUniwideOfficer->adviser_score, 2) : 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-blue-100 rounded-md">
                                <p class="text-xs text-blue-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    These results from your latest UNIWIDE council term will be included in your leadership certificate.
                                </p>
                            </div>
                        </div>
                    @endif

                    @if($canRequestDepartmental && $latestDepartmentalOfficer)
                        <!-- Departmental Term Details -->
                        <div id="departmental_term_details" class="hidden bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-green-900 mb-3 flex items-center">
                                <i class="fas fa-trophy mr-2"></i>
                                Departmental Leadership Award - Latest Term Details
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Council:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->council->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Academic Year:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->council->academic_year ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Position:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->position_title ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Final Score:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->final_score ? number_format($latestDepartmentalOfficer->final_score, 2) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Award Rank:</span>
                                        <span class="text-sm font-bold
                                            @if($latestDepartmentalOfficer->ranking_category === 'Gold') text-yellow-600
                                            @elseif($latestDepartmentalOfficer->ranking_category === 'Silver') text-gray-600
                                            @elseif($latestDepartmentalOfficer->ranking_category === 'Bronze') text-orange-600
                                            @elseif($latestDepartmentalOfficer->ranking_category === 'Certificate') text-blue-600
                                            @else text-red-600 @endif">
                                            {{ $latestDepartmentalOfficer->ranking_category ?? 'Not Available' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Self Score:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->self_score ? number_format($latestDepartmentalOfficer->self_score, 2) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Peer Score:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->peer_score ? number_format($latestDepartmentalOfficer->peer_score, 2) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Adviser Score:</span>
                                        <span class="text-sm font-medium">{{ $latestDepartmentalOfficer->adviser_score ? number_format($latestDepartmentalOfficer->adviser_score, 2) : 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-green-100 rounded-md">
                                <p class="text-xs text-green-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    These results from your latest departmental council term will be included in your leadership certificate.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Graduation Confirmation -->
                <div class="mb-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="is_graduating" value="1"
                                   class="mt-1 text-green-600 focus:ring-green-500"
                                   {{ old('is_graduating') ? 'checked' : '' }}>
                            <div>
                                <div class="font-medium text-gray-900">Graduation Confirmation</div>
                                <div class="text-sm text-gray-600">I confirm that I am graduating and eligible to receive this leadership certificate.</div>
                            </div>
                        </label>
                    </div>
                    @error('is_graduating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('student.leadership_certificate.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                        Submit Request
                    </button>
                </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
function showTermDetails() {
    const certificateType = document.querySelector('input[name="certificate_type"]:checked');
    const campusDetails = document.getElementById('campus_term_details');
    const departmentalDetails = document.getElementById('departmental_term_details');

    // Hide all term details first
    if (campusDetails) campusDetails.classList.add('hidden');
    if (departmentalDetails) departmentalDetails.classList.add('hidden');

    if (certificateType) {
        if (certificateType.value === 'campus' && campusDetails) {
            campusDetails.classList.remove('hidden');
        } else if (certificateType.value === 'departmental' && departmentalDetails) {
            departmentalDetails.classList.remove('hidden');
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    showTermDetails();
});
</script>
@endsection
