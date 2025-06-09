@extends('student.layout')

@section('title', 'Certificate Requests - PSCERMS')
@section('page-title', 'Certificate Requests')

@section('content')
<!-- Header with Action Button -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Certificate Requests</h1>
        <p class="text-gray-600 mt-1">Track the status of your leadership certificate requests.</p>
    </div>
    <a href="{{ route('student.leadership_certificate.create') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-flex items-center">
        <i class="fas fa-plus mr-2"></i>
        New Request
    </a>
</div>

@if($requests->isEmpty())
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-certificate text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Certificate Requests</h3>
        <p class="text-gray-500 max-w-md mx-auto mb-4">
            You haven't submitted any leadership certificate requests yet. Submit your first request to get started.
        </p>
        <a href="{{ route('student.leadership_certificate.create') }}"
           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Submit Your First Request
        </a>
    </div>
@else
    <!-- Requests Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($requests as $request)
            @if($request->council)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300">
                    <div class="p-6">
                        <!-- Header with Icon and Status -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-certificate text-yellow-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ ucfirst($request->certificate_type) }} Leadership Award</h3>
                                    <p class="text-sm text-gray-500">{{ $request->council->academic_year ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($request->status === 'approved') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>

                        <!-- Request Information -->
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-users text-gray-400 mr-3 w-4"></i>
                                <span class="font-medium">Council:</span>
                                <span class="ml-2 truncate">{{ $request->council->name ?? 'N/A' }}</span>
                            </div>

                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-chalkboard-teacher text-gray-400 mr-3 w-4"></i>
                                <span class="font-medium">Adviser:</span>
                                <span class="ml-2">{{ $request->council->adviser ? $request->council->adviser->first_name . ' ' . $request->council->adviser->last_name : 'N/A' }}</span>
                            </div>

                            @if($request->is_graduating)
                                <div class="flex items-center text-sm text-green-600">
                                    <i class="fas fa-graduation-cap text-green-500 mr-3 w-4"></i>
                                    <span class="font-medium">Graduating Student</span>
                                </div>
                            @endif
                        </div>
            @else
                <!-- Handle requests with missing council data -->
                <div class="bg-white rounded-lg shadow-sm border border-red-200 hover:shadow-md transition-shadow duration-300">
                    <div class="p-6">
                        <!-- Header with Icon and Status -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ ucfirst($request->certificate_type) }} Leadership Award</h3>
                                    <p class="text-sm text-red-500">Council data unavailable</p>
                                </div>
                            </div>
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Data Error
                            </span>
                        </div>

                        <!-- Request Information -->
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center text-sm text-red-600">
                                <i class="fas fa-exclamation-circle text-red-400 mr-3 w-4"></i>
                                <span class="font-medium">Council data is missing or has been deleted.</span>
                            </div>

                            @if($request->is_graduating)
                                <div class="flex items-center text-sm text-green-600">
                                    <i class="fas fa-graduation-cap text-green-500 mr-3 w-4"></i>
                                    <span class="font-medium">Graduating Student</span>
                                </div>
                            @endif
                        </div>

                        <!-- Request Status -->
                        <div class="flex items-center text-sm text-gray-500 mb-4">
                            <i class="fas fa-calendar mr-2"></i>
                            <span>Requested: {{ $request->requested_at->format('M j, Y') }}</span>
                        </div>

                        @if($request->responded_at)
                            <div class="flex items-center text-sm text-gray-500 mb-4">
                                <i class="fas fa-calendar-check mr-2"></i>
                                <span>Responded: {{ $request->responded_at->format('M j, Y') }}</span>
                            </div>
                        @endif

                        @if($request->adviser_response && $request->status !== 'pending')
                            <!-- Adviser Response -->
                            <div class="pt-4 border-t border-gray-100">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Adviser Response:</h4>
                                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                    {{ $request->adviser_response }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endif
@endsection
