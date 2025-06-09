@extends('admin.layout')

@section('title', 'Dashboard - PSCERMS')
@section('page-title', 'Dashboard')

@section('content')
<!-- Greetings Container -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Welcome to PSCERMS Admin Panel</h2>
        <p class="text-gray-600">
            Manage users, councils, and monitor evaluation progress across the entire system.
        </p>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Management Cards -->
    <div class="space-y-6">
        <!-- Council Management Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Council Management</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users-cog text-green-600 text-lg"></i>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Councils:</span>
                        <span class="text-lg font-semibold text-gray-800">{{ $councilCounts['total_councils'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Active:</span>
                        <span class="text-sm font-medium text-green-600">{{ $councilCounts['active_councils'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Completed:</span>
                        <span class="text-sm font-medium text-gray-600">{{ $councilCounts['completed_councils'] }}</span>
                    </div>
                </div>

                <a href="{{ route('admin.council_management.index') }}"
                   class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors inline-block">
                    <i class="fas fa-cog mr-2"></i>
                    Manage Councils
                </a>
            </div>
        </div>

        <!-- User Management Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">User Management</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users text-green-600 text-lg"></i>
                    </div>
                </div>

                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total Users:</span>
                        <span class="text-lg font-semibold text-gray-800">{{ $userCounts['total_users'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Students:</span>
                        <span class="text-sm font-medium text-green-600">{{ $userCounts['students'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Advisers:</span>
                        <span class="text-sm font-medium text-green-700">{{ $userCounts['advisers'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Admins:</span>
                        <span class="text-sm font-medium text-green-800">{{ $userCounts['admins'] }}</span>
                    </div>
                </div>

                <a href="{{ route('admin.user_management.index') }}"
                   class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors inline-block">
                    <i class="fas fa-users mr-2"></i>
                    Manage Users
                </a>
            </div>
        </div>

        <!-- System Logs Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">System Logs</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-file-alt text-green-600 text-lg"></i>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    <p class="text-sm text-gray-600">Monitor system activity</p>
                    <p class="text-xs text-gray-500">View logs and system events</p>
                </div>

                <a href="{{ route('admin.system_logs.index') }}" class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors block">
                    <i class="fas fa-eye mr-2"></i>
                    View Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column - Evaluation Progress Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Evaluation Progress</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600 text-lg"></i>
                    </div>
                </div>

                <!-- Active Councils with Evaluation Progress -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 mb-4">
                        Active Councils
                        @if($activeCouncils->count() > 0)
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full ml-2">
                                {{ $activeCouncils->count() }}
                            </span>
                        @endif
                    </h4>

                    @if($evaluationProgress && count($evaluationProgress) > 0)
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            @foreach($evaluationProgress as $item)
                                @php
                                    $council = $item['council'];
                                    $progress = $item['progress'];
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-900">{{ $council->name }}</h5>
                                            <p class="text-sm text-gray-600">{{ $council->department->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                Academic Year: {{ $council->academic_year }} •
                                                Adviser: {{ $council->adviser->first_name }} {{ $council->adviser->last_name }}
                                            </p>
                                        </div>
                                        <div class="ml-4 text-right">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ $progress['completion_percentage'] }}%
                                            </span>
                                            <p class="text-xs text-gray-500">
                                                {{ $progress['evaluations_completed'] }}/{{ $progress['evaluations_total'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                             style="width: {{ $progress['completion_percentage'] }}%"></div>
                                    </div>

                                    <!-- Progress Details -->
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ $progress['total_officers'] }} officers •
                                        {{ $progress['scores_calculated'] }} scores calculated
                                        @if($progress['is_ready_for_completion'])
                                            <span class="text-green-600 font-medium">• Ready for completion</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($activeCouncils->count() > 0)
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-play-circle text-green-600 text-2xl"></i>
                            </div>
                            <h5 class="text-lg font-medium text-gray-500 mb-2">Evaluations Not Started</h5>
                            <p class="text-gray-400">{{ $activeCouncils->count() }} active councils haven't started evaluations yet.</p>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users-cog text-green-400 text-2xl"></i>
                            </div>
                            <h5 class="text-lg font-medium text-gray-500 mb-2">No Active Councils</h5>
                            <p class="text-gray-400">Create councils to start tracking evaluation progress.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
