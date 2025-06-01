@extends('adviser.layout')

@section('title', 'My Councils - PSCERMS')
@section('page-title', 'My Councils')

@section('content')
<!-- Success Message -->
@if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        {{ session('success') }}
    </div>
@endif

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

                    <!-- Action Button -->
                    <div class="pt-4 border-t border-gray-100">
                        <a href="{{ route('adviser.councils.show', $council) }}"
                           class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors inline-block">
                            <i class="fas fa-eye mr-2"></i>
                            View Details
                        </a>
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Councils Assigned</h3>
        <p class="text-gray-500 mb-6">You haven't been assigned to any councils yet. Contact your administrator to get assigned to a council.</p>
    </div>
@endif
@endsection
