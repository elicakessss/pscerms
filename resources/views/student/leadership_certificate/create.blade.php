@extends('student.layout')

@section('title', 'Request Leadership Certificate - PSCERMS')
@section('page-title', 'Request Leadership Certificate')

@section('content')
<div class="max-w-4xl mx-auto">

    @if($campusCouncils->isEmpty() && $departmentalCouncils->isEmpty())
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
                            @if($campusCouncils->isNotEmpty())
                                <!-- Campus Leadership Award -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="certificate_type" value="campus"
                                               class="mt-1 text-green-600 focus:ring-green-500"
                                               {{ old('certificate_type') == 'campus' ? 'checked' : '' }}
                                               onchange="updateCouncilOptions()">
                                        <div>
                                            <div class="font-medium text-gray-900">Campus Leadership Award</div>
                                            <div class="text-sm text-gray-600">For UNIWIDE council participation</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Available councils: {{ $campusCouncils->count() }}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endif

                            @if($departmentalCouncils->isNotEmpty())
                                <!-- Departmental Leadership Award -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="certificate_type" value="departmental"
                                               class="mt-1 text-green-600 focus:ring-green-500"
                                               {{ old('certificate_type') == 'departmental' ? 'checked' : '' }}
                                               onchange="updateCouncilOptions()">
                                        <div>
                                            <div class="font-medium text-gray-900">Departmental Leadership Award</div>
                                            <div class="text-sm text-gray-600">For departmental council participation</div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Available councils: {{ $departmentalCouncils->count() }}
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

                <!-- Council Selection with Rank Results -->
                <div class="mb-6">
                    <label for="council_id" class="block text-sm font-medium text-gray-700 mb-2">Select Council</label>

                    @if($campusCouncils->isNotEmpty())
                        <!-- Campus Councils -->
                        <div id="campus_councils" class="hidden space-y-4">
                            <select name="council_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" onchange="showRankDetails('campus')">
                                <option value="">Select a UNIWIDE council...</option>
                                @foreach($campusCouncils as $councilData)
                                    <option value="{{ $councilData['council']->id }}" {{ old('council_id') == $councilData['council']->id ? 'selected' : '' }}>
                                        {{ $councilData['council']->name }} - {{ $councilData['council']->academic_year }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Rank Results for Campus Councils -->
                            @foreach($campusCouncils as $councilData)
                                <div id="rank_details_campus_{{ $councilData['council']->id }}" class="rank-details hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="font-medium text-blue-900 mb-3 flex items-center">
                                        <i class="fas fa-trophy mr-2"></i>
                                        Your Performance Results
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Position:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_position'] }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Final Score:</span>
                                                <span class="text-sm font-medium">{{ number_format($councilData['student_final_score'], 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Award Rank:</span>
                                                <span class="text-sm font-bold
                                                    @if($councilData['student_rank'] === 'Gold') text-yellow-600
                                                    @elseif($councilData['student_rank'] === 'Silver') text-gray-600
                                                    @elseif($councilData['student_rank'] === 'Bronze') text-orange-600
                                                    @else text-red-600 @endif">
                                                    {{ $councilData['student_rank'] ?? 'Not Available' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Self Score:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_self_score'] ? number_format($councilData['student_self_score'], 2) : 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Peer Score:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_peer_score'] ? number_format($councilData['student_peer_score'], 2) : 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Adviser Score:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_adviser_score'] ? number_format($councilData['student_adviser_score'], 2) : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-blue-100 rounded-md">
                                        <p class="text-xs text-blue-800">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            These results will be included in your leadership certificate as reference for your academic and professional achievements.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($departmentalCouncils->isNotEmpty())
                        <!-- Departmental Councils -->
                        <div id="departmental_councils" class="hidden space-y-4">
                            <select name="council_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" onchange="showRankDetails('departmental')">
                                <option value="">Select a departmental council...</option>
                                @foreach($departmentalCouncils as $councilData)
                                    <option value="{{ $councilData['council']->id }}" {{ old('council_id') == $councilData['council']->id ? 'selected' : '' }}>
                                        {{ $councilData['council']->name }} - {{ $councilData['council']->academic_year }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Rank Results for Departmental Councils -->
                            @foreach($departmentalCouncils as $councilData)
                                <div id="rank_details_departmental_{{ $councilData['council']->id }}" class="rank-details hidden bg-green-50 border border-green-200 rounded-lg p-4">
                                    <h4 class="font-medium text-green-900 mb-3 flex items-center">
                                        <i class="fas fa-trophy mr-2"></i>
                                        Your Performance Results
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Position:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_position'] }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Final Score:</span>
                                                <span class="text-sm font-medium">{{ number_format($councilData['student_final_score'], 2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Award Rank:</span>
                                                <span class="text-sm font-bold
                                                    @if($councilData['student_rank'] === 'Gold') text-yellow-600
                                                    @elseif($councilData['student_rank'] === 'Silver') text-gray-600
                                                    @elseif($councilData['student_rank'] === 'Bronze') text-orange-600
                                                    @else text-red-600 @endif">
                                                    {{ $councilData['student_rank'] ?? 'Not Available' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Self Score:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_self_score'] ? number_format($councilData['student_self_score'], 2) : 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Peer Score:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_peer_score'] ? number_format($councilData['student_peer_score'], 2) : 'N/A' }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-sm text-gray-600">Adviser Score:</span>
                                                <span class="text-sm font-medium">{{ $councilData['student_adviser_score'] ? number_format($councilData['student_adviser_score'], 2) : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-green-100 rounded-md">
                                        <p class="text-xs text-green-800">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            These results will be included in your leadership certificate as reference for your academic and professional achievements.
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @error('council_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
function updateCouncilOptions() {
    const certificateType = document.querySelector('input[name="certificate_type"]:checked');
    const campusDiv = document.getElementById('campus_councils');
    const departmentalDiv = document.getElementById('departmental_councils');

    // Hide all council options and rank details first
    if (campusDiv) campusDiv.classList.add('hidden');
    if (departmentalDiv) departmentalDiv.classList.add('hidden');

    // Hide all rank details
    document.querySelectorAll('.rank-details').forEach(detail => {
        detail.classList.add('hidden');
    });

    // Clear previous selections
    if (campusDiv) campusDiv.querySelector('select').selectedIndex = 0;
    if (departmentalDiv) departmentalDiv.querySelector('select').selectedIndex = 0;

    if (certificateType) {
        if (certificateType.value === 'campus' && campusDiv) {
            campusDiv.classList.remove('hidden');
        } else if (certificateType.value === 'departmental' && departmentalDiv) {
            departmentalDiv.classList.remove('hidden');
        }
    }
}

function showRankDetails(type) {
    // Hide all rank details first
    document.querySelectorAll('.rank-details').forEach(detail => {
        detail.classList.add('hidden');
    });

    // Get the selected council ID
    const selectElement = document.querySelector(`#${type}_councils select`);
    const selectedCouncilId = selectElement.value;

    if (selectedCouncilId) {
        // Show the rank details for the selected council
        const rankDetailsElement = document.getElementById(`rank_details_${type}_${selectedCouncilId}`);
        if (rankDetailsElement) {
            rankDetailsElement.classList.remove('hidden');
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCouncilOptions();
});
</script>
@endsection
