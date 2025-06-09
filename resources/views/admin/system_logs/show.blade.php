@extends('admin.layout')

@section('title', 'Log Details - PSCERMS')
@section('page-title', 'Log Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('admin.system_logs.index') }}" 
           class="inline-flex items-center text-green-600 hover:text-green-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to System Logs
        </a>
    </div>

    <!-- Log Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-gray-800">Log Entry #{{ $systemLog->id }}</h3>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $systemLog->action_color }} bg-gray-100">
                    <i class="{{ $systemLog->action_icon }} mr-2"></i>
                    {{ $systemLog->formatted_action }}
                </span>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h4>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Date & Time</dt>
                            <dd class="text-sm text-gray-900">{{ $systemLog->performed_at->format('F j, Y \a\t g:i:s A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                            <dd class="text-sm text-gray-900">{{ $systemLog->formatted_action }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="text-sm text-gray-900">{{ $systemLog->description }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h4 class="text-lg font-medium text-gray-900 mb-4">User Information</h4>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User</dt>
                            <dd class="text-sm text-gray-900">
                                @if($systemLog->user_name)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-gray-500 text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $systemLog->user_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $systemLog->formatted_user_type }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">System</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User ID</dt>
                            <dd class="text-sm text-gray-900">{{ $systemLog->user_id ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Type</dt>
                            <dd class="text-sm text-gray-900">{{ $systemLog->formatted_user_type ?? 'System' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Entity Information -->
            @if($systemLog->entity_type)
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Entity Information</h4>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Entity Type</dt>
                        <dd class="text-sm text-gray-900">{{ ucfirst($systemLog->entity_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Entity ID</dt>
                        <dd class="text-sm text-gray-900">{{ $systemLog->entity_id ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Entity Name</dt>
                        <dd class="text-sm text-gray-900">{{ $systemLog->entity_name ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
            @endif

            <!-- Technical Information -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Technical Information</h4>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                        <dd class="text-sm text-gray-900 font-mono">{{ $systemLog->ip_address ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">User Agent</dt>
                        <dd class="text-sm text-gray-900 break-all">{{ $systemLog->user_agent ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Data Changes -->
            @if($systemLog->old_values || $systemLog->new_values)
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Data Changes</h4>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @if($systemLog->old_values)
                    <div>
                        <h5 class="text-md font-medium text-red-700 mb-3">
                            <i class="fas fa-minus-circle mr-2"></i>
                            Previous Values
                        </h5>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <pre class="text-sm text-red-800 whitespace-pre-wrap">{{ json_encode($systemLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif

                    @if($systemLog->new_values)
                    <div>
                        <h5 class="text-md font-medium text-green-700 mb-3">
                            <i class="fas fa-plus-circle mr-2"></i>
                            New Values
                        </h5>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <pre class="text-sm text-green-800 whitespace-pre-wrap">{{ json_encode($systemLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Timestamps -->
            <div>
                <h4 class="text-lg font-medium text-gray-900 mb-4">Timestamps</h4>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Performed At</dt>
                        <dd class="text-sm text-gray-900">{{ $systemLog->performed_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="text-sm text-gray-900">{{ $systemLog->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="text-sm text-gray-900">{{ $systemLog->updated_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Log ID: {{ $systemLog->id }}
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.system_logs.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-list mr-2"></i>
                        Back to Logs
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
