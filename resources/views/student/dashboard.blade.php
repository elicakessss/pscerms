@extends('student.layout')

@section('title', 'Dashboard - PSCERMS')
@section('page-title', 'Dashboard')

@section('content')
<!-- Greetings Container -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="p-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-2">Welcome back, {{ $student->first_name }}!</h2>
        <p class="text-gray-600">
            @if($currentCouncil)
                You're currently serving in {{ $currentCouncil->name }} as {{ $currentOfficer->position_title }}.
            @else
                You're not currently assigned to any active council.
            @endif
        </p>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Quick Access Cards -->
    <div class="space-y-6">
        <!-- My Council Preview Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">My Council Preview</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-users-cog text-green-600 text-lg"></i>
                    </div>
                </div>

                @if($currentCouncil)
                    <div class="space-y-3 mb-4">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $currentCouncil->name }}</p>
                            <p class="text-xs text-gray-600">{{ $currentCouncil->department->name }}</p>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Position:</span>
                            <span class="text-sm font-medium text-green-600">{{ $currentOfficer->position_title }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Academic Year:</span>
                            <span class="text-sm font-medium text-gray-800">{{ $currentCouncil->academic_year }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Status:</span>
                            <span class="text-sm font-medium text-blue-600">{{ ucfirst($currentCouncil->status) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('student.councils.index') }}"
                       class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg text-sm font-medium transition-colors inline-block">
                        <i class="fas fa-eye mr-2"></i>
                        View Council Details
                    </a>
                @else
                    <div class="text-center py-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-users-cog text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">No active council assignment</p>
                        <p class="text-xs text-gray-400">You'll see your council information here when assigned.</p>
                    </div>
                @endif
            </div>
        </div>


    </div>

    <!-- Right Column - Evaluation Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">Evaluation Section</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-green-600 text-lg"></i>
                    </div>
                </div>

                <!-- Unfinished Evaluations -->
                @if($selfEvaluations->count() > 0 || $peerEvaluations->count() > 0)
                    <div class="space-y-6">
                        <!-- Self Evaluations -->
                        @if($selfEvaluations->count() > 0)
                            <div>
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-user-check text-blue-600 mr-2"></i>
                                    Self Evaluations
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full ml-2">
                                        {{ $selfEvaluations->count() }}
                                    </span>
                                </h4>
                                <div class="space-y-3">
                                    @foreach($selfEvaluations as $evaluation)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h5 class="font-medium text-gray-900">{{ $evaluation->council->name }}</h5>
                                                    <p class="text-sm text-gray-600">{{ $evaluation->council->department->name }}</p>
                                                    <p class="text-xs text-gray-500">Academic Year: {{ $evaluation->council->academic_year }}</p>
                                                </div>
                                                <div class="ml-4">
                                                    <a href="{{ route('student.evaluation.self', $evaluation->council) }}"
                                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Start Self Evaluation
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Peer Evaluations -->
                        @if($peerEvaluations->count() > 0)
                            <div>
                                <h4 class="text-md font-medium text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-users text-green-600 mr-2"></i>
                                    Peer Evaluations
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full ml-2">
                                        {{ $peerEvaluations->count() }}
                                    </span>
                                </h4>
                                <div class="space-y-3">
                                    @foreach($peerEvaluations as $evaluation)
                                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <h5 class="font-medium text-gray-900">
                                                        Evaluate: {{ $evaluation->evaluatedStudent->first_name }} {{ $evaluation->evaluatedStudent->last_name }}
                                                    </h5>
                                                    <p class="text-sm text-gray-600">{{ $evaluation->council->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $evaluation->council->department->name }}</p>
                                                </div>
                                                <div class="ml-4">
                                                    <a href="{{ route('student.evaluation.peer', [$evaluation->council, $evaluation->evaluatedStudent]) }}"
                                                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors">
                                                        <i class="fas fa-edit mr-1"></i>
                                                        Start Peer Evaluation
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                        </div>
                        <h5 class="text-lg font-medium text-gray-500 mb-2">All Evaluations Complete!</h5>
                        <p class="text-gray-400">You have no pending evaluations at the moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


