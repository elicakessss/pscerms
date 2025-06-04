@extends('admin.layout')

@section('title', 'Evaluation Forms Management - PSCERMS')
@section('page-title', 'Evaluation Forms Management')

@section('header-actions')
<div class="flex items-center space-x-4">
    <!-- Search Bar -->
    <form method="GET">
        <div class="relative">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search questions..."
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
        </div>
    </form>

    <!-- Domain Filter -->
    <form method="GET" class="flex items-center space-x-2">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <select name="domain" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="">All Domains</option>
            @foreach($domains as $domainName)
                <option value="{{ $domainName }}" {{ request('domain') === $domainName ? 'selected' : '' }}>
                    {{ $domainName }}
                </option>
            @endforeach
        </select>
    </form>

    <!-- Preview Button -->
    <a href="{{ route('admin.evaluation_forms.preview', 'adviser') }}"
       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-eye mr-2"></i>Preview
    </a>

    <!-- Edit Questions Button -->
    <a href="{{ route('admin.evaluation_forms.edit') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-edit mr-2"></i>Edit
    </a>
</div>
@endsection

@section('content')
<!-- Statistics Overview -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
    <!-- Total Questions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
                <i class="fas fa-question-circle text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Questions</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_questions'] }}</p>
            </div>
        </div>
    </div>

    <!-- Total Domains -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
                <i class="fas fa-layer-group text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Domains</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_domains'] }}</p>
            </div>
        </div>
    </div>

    <!-- Adviser Questions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
                <i class="fas fa-chalkboard-teacher text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Adviser</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['adviser_questions'] }}</p>
            </div>
        </div>
    </div>

    <!-- Peer Questions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
                <i class="fas fa-users text-orange-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Peer</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['peer_questions'] }}</p>
            </div>
        </div>
    </div>

    <!-- Self Questions -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
            <div class="p-2 bg-indigo-100 rounded-lg">
                <i class="fas fa-user text-indigo-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Self</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['self_questions'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Evaluation Questions Section -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">Evaluation Questions</h3>
        <p class="text-sm text-green-100 mt-1">Manage evaluation form structure and questions</p>
    </div>

    <!-- Questions by Domain and Strand -->
    <div class="p-6">
        @forelse($filteredQuestions as $domain)
            <div class="mb-8 border border-gray-200 rounded-lg">
                <!-- Domain Header -->
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-800">{{ $domain['name'] }}</h4>
                </div>

                <!-- Strands -->
                @foreach($domain['strands'] as $strand)
                    <div class="border-b border-gray-100 last:border-b-0">
                        <!-- Strand Header -->
                        <div class="bg-gray-25 px-4 py-2 border-b border-gray-100">
                            <h5 class="text-md font-medium text-gray-700">{{ $strand['name'] }}</h5>
                        </div>

                        <!-- Questions -->
                        <div class="p-4">
                            @foreach($strand['questions'] as $index => $question)
                                <div class="flex items-start justify-between p-3 border border-gray-200 rounded-lg mb-3 last:mb-0">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 mb-2">{{ $question['text'] }}</p>

                                        <!-- Access Levels -->
                                        <div class="flex items-center space-x-2 mb-2">
                                            @foreach($question['access_levels'] as $level)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ $level === 'adviser' ? 'bg-purple-100 text-purple-800' :
                                                       ($level === 'peer' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800') }}">
                                                    {{ ucfirst($level) }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <!-- Rating Options Count -->
                                        <p class="text-xs text-gray-500">
                                            {{ count($question['rating_options']) }} rating options available
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-12">
                <div class="flex flex-col items-center">
                    <i class="fas fa-clipboard-list text-4xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium mb-2">No evaluation questions found</h3>
                    <p class="text-sm text-gray-500 mb-4">Use the Edit Questions button to configure evaluation questions.</p>
                    <a href="{{ route('admin.evaluation_forms.edit') }}"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Questions
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
