@extends('admin.layout')

@section('title', 'View User - PSCERMS')
@section('page-title', 'User Details')

@section('content')
<!-- Current User Notice -->
@if($type === 'admin' && auth('admin')->check() && auth('admin')->id() == $user->id)
<div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-green-600"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-green-800">
                <strong>This is your account.</strong> You are currently viewing your own profile information.
            </p>
        </div>
    </div>
</div>
@endif

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">User Details</h1>
    <div class="flex space-x-3">
        <a href="{{ route('admin.user_management.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> Back to List
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm overflow-hidden border {{ ($type === 'admin' && auth('admin')->check() && auth('admin')->id() == $user->id) ? 'border-green-300 ring-2 ring-green-100' : 'border-gray-200' }}">
    <div class="md:flex">
        <!-- Profile Picture and Basic Info -->
        <div class="md:w-1/3 bg-gray-50 p-6 border-b md:border-b-0 md:border-r border-gray-200">
            <div class="flex flex-col items-center text-center">
                @if(isset($user->profile_picture) && $user->profile_picture)
                <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="{{ $user->full_name }}" class="h-32 w-32 object-cover rounded-full mb-4">
                @else
                <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                    <i class="fas fa-user text-gray-400 text-4xl"></i>
                </div>
                @endif

                <div class="flex items-center justify-center space-x-2 mb-2">
                    <h2 class="text-xl font-bold text-gray-800">{{ $user->full_name }}</h2>
                    @if($type === 'admin' && auth('admin')->check() && auth('admin')->id() == $user->id)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                            <i class="fas fa-user-check mr-1"></i>
                            Me
                        </span>
                    @endif
                </div>
                <p class="text-gray-600 mb-2">{{ $user->id_number }}</p>
                <p class="text-gray-600 mb-4">{{ $user->email }}</p>

                <div class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    @if(isset($user->department))
                        {{ $user->department->abbreviation }} - {{ $user->department->name }}
                    @else
                        {{ ucfirst($type === 'admin' ? 'Administrator' : $type) }}
                    @endif
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="md:w-2/3 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">User Information</h3>

            <div class="space-y-4">
                <!-- Description -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Description</h4>
                    <p class="text-gray-800">
                        @if(isset($user->description) && $user->description)
                            {{ $user->description }}
                        @else
                            No description available.
                        @endif
                    </p>
                </div>

                <!-- Created & Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                        <p class="text-gray-800">{{ $user->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                        <p class="text-gray-800">{{ $user->updated_at->format('F j, Y, g:i a') }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-3">Actions</h4>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.user_management.edit', [$type, $user->id]) }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-edit mr-2"></i> Edit User
                        </a>
                        <a href="mailto:{{ $user->email }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-envelope mr-2"></i> Send Email
                        </a>
                        @unless($type === 'admin' && auth('admin')->check() && auth('admin')->id() == $user->id)
                        <form action="{{ route('admin.user_management.destroy', [$type, $user->id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-trash mr-2"></i> Delete User
                            </button>
                        </form>
                        @else
                        <div class="bg-gray-100 text-gray-500 px-4 py-2 rounded-lg text-sm cursor-not-allowed">
                            <i class="fas fa-shield-alt mr-2"></i> Cannot Delete Own Account
                        </div>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Portfolio Section (Only for Students) -->
@if($type === 'student')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
            Leadership Experiences
        </h3>

        @if($completedCouncils->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($completedCouncils as $councilOfficer)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-md transition-shadow">
                        <!-- Council Header -->
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 text-sm">{{ $councilOfficer->council->name }}</h4>
                                <p class="text-xs text-gray-500">{{ $councilOfficer->council->academic_year }}</p>
                            </div>
                        </div>

                        <!-- Position and Department -->
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-700">{{ $councilOfficer->position_title }}</p>
                            @if($councilOfficer->council->department->id !== $user->department_id)
                                <p class="text-xs text-gray-500">{{ $councilOfficer->council->department->name }}</p>
                            @endif
                        </div>

                        <!-- Scores -->
                        <div class="space-y-2 mb-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">Final Score:</span>
                                <span class="text-sm font-semibold text-gray-800">{{ number_format($councilOfficer->final_score, 2) }}</span>
                            </div>
                            @if($councilOfficer->rank)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Award:</span>
                                    <div class="flex items-center">
                                        @php
                                            $rankingCategory = $councilOfficer->ranking_category;
                                            $gemColor = match($rankingCategory) {
                                                'Gold' => 'text-yellow-500',
                                                'Silver' => 'text-gray-500',
                                                'Bronze' => 'text-orange-600',
                                                'Certificate' => 'text-blue-500',
                                                default => 'text-gray-400'
                                            };
                                        @endphp
                                        <i class="fas fa-gem {{ $gemColor }} mr-1"></i>
                                        <span class="text-sm font-semibold text-gray-800">{{ $rankingCategory }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Recommendations (Admin Only) -->
                        @if($councilOfficer->final_score)
                            @php
                                $scoreService = app(\App\Services\ScoreCalculationService::class);
                                $recommendations = $scoreService->getRecommendationText($councilOfficer->final_score);
                            @endphp
                            <div class="mb-3">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <h5 class="text-xs font-medium text-blue-800 mb-2 flex items-center">
                                        <i class="fas fa-lightbulb mr-1"></i>
                                        Recommendations
                                    </h5>
                                    <ul class="space-y-1">
                                        @foreach($recommendations as $recommendation)
                                            <li class="text-xs text-blue-700 flex items-start">
                                                <i class="fas fa-circle text-blue-400 text-xs mt-1 mr-2 flex-shrink-0" style="font-size: 4px;"></i>
                                                <span>{{ $recommendation }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <!-- Completion Date -->
                        <div class="pt-2 border-t border-gray-200">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Completed: {{ $councilOfficer->completed_at->format('M j, Y') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trophy text-gray-400 text-2xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-500 mb-2">No Completed Councils</h4>
                <p class="text-gray-400">This student's completed council evaluations will appear here once available.</p>
            </div>
        @endif
    </div>
</div>
@endif
@endsection
