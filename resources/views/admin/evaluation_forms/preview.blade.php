@extends('admin.layout')

@section('title', 'Preview Evaluation Form - PSCERMS')
@section('page-title', 'Preview Evaluation Form')

@section('header-actions')
<div class="flex items-center space-x-4">
    <!-- Evaluator Type Selector -->
    <form method="GET" class="flex items-center space-x-2">
        <label class="text-white text-sm">View as:</label>
        <select name="type" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <option value="adviser" {{ $evaluatorType === 'adviser' ? 'selected' : '' }}>Adviser</option>
            <option value="peer" {{ $evaluatorType === 'peer' ? 'selected' : '' }}>Peer Evaluator</option>
            <option value="self" {{ $evaluatorType === 'self' ? 'selected' : '' }}>Self Evaluator</option>
        </select>
    </form>

    <!-- Back Button -->
    <a href="{{ route('admin.evaluation_forms.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Questions
    </a>
</div>
@endsection

@section('content')
<!-- Preview Info -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-400"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Evaluation Form Preview</h3>
            <p class="text-sm text-blue-700 mt-1">
                This is how the evaluation form appears to <strong>{{ ucfirst($evaluatorType) }}</strong> evaluators.
                Use the dropdown above to switch between different evaluator types.
            </p>
        </div>
    </div>
</div>

<!-- Evaluation Form Preview -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 bg-green-600 text-white rounded-t-lg">
        <h3 class="text-lg font-semibold text-white">{{ ucfirst($evaluatorType) }} Evaluation Form</h3>
        <p class="text-sm text-green-100 mt-1">Preview of questions visible to {{ $evaluatorType }} evaluators</p>
    </div>

    <div class="p-6">
        @forelse($filteredQuestions as $domain)
            <!-- Domain Section -->
            <div class="mb-8 border border-gray-200 rounded-lg">
                <div class="bg-green-600 text-white p-4 rounded-t-lg">
                    <h2 class="text-lg font-semibold">{{ $domain['name'] }}</h2>
                </div>

                @foreach($domain['strands'] as $strand)
                    <!-- Strand Section -->
                    <div class="border-b border-gray-200 last:border-b-0">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h3 class="text-md font-medium text-gray-800">{{ $strand['name'] }}</h3>
                        </div>

                        <div class="p-4 space-y-6">
                            @foreach($strand['questions'] as $index => $question)
                                <!-- Question -->
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        {{ $loop->parent->parent->index + 1 }}.{{ $loop->parent->index + 1 }}.{{ $loop->index + 1 }} {{ $question['text'] }}
                                    </label>

                                    <!-- Rating Options -->
                                    <div class="space-y-2">
                                        @foreach($question['rating_options'] as $option)
                                            <label class="flex items-center">
                                                <input type="radio" 
                                                       name="question_{{ $loop->parent->parent->parent->index }}_{{ $loop->parent->parent->index }}_{{ $loop->parent->index }}" 
                                                       value="{{ $option['value'] }}" 
                                                       class="mr-2 text-green-600 focus:ring-green-500"
                                                       disabled>
                                                <span class="text-sm">{{ $option['label'] }}</span>
                                            </label>
                                        @endforeach
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
                    <h3 class="text-lg font-medium mb-2">No questions available</h3>
                    <p class="text-sm text-gray-500">No questions are configured for {{ $evaluatorType }} evaluators.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Access Level Legend -->
<div class="mt-6 bg-gray-50 rounded-lg p-4">
    <h4 class="text-sm font-medium text-gray-800 mb-3">Access Level Information</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div class="flex items-center">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-2">
                Adviser
            </span>
            <span class="text-gray-600">Can see all questions</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 mr-2">
                Peer
            </span>
            <span class="text-gray-600">Limited access (no Domain 1)</span>
        </div>
        <div class="flex items-center">
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-2">
                Self
            </span>
            <span class="text-gray-600">Most limited access</span>
        </div>
    </div>
</div>
@endsection
