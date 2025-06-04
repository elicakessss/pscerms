@extends('admin.layout')

@section('title', 'Edit Evaluation Questions - PSCERMS')
@section('page-title', 'Edit Evaluation Questions')
@section('page-subtitle', 'Manage evaluation structure, questions, and access levels')

@section('header-actions')
<div class="flex space-x-3">
    <a href="{{ route('admin.evaluation_forms.index') }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        <i class="fas fa-arrow-left mr-2"></i>Back to Questions
    </a>
</div>
@endsection

@section('content')
<!-- Enhanced Form Interface -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Edit Evaluation Questions</h3>
        <p class="text-sm text-gray-600 mt-1">Manage evaluation structure, questions, and access levels</p>
    </div>

    <form action="{{ route('admin.evaluation_forms.update') }}" method="POST" class="p-6" id="evaluationForm">
        @csrf
        @method('PUT')

        <!-- Domains Container -->
        <div id="domainsContainer">
            @foreach($questions as $domainIndex => $domain)
                <div class="domain-item mb-8 border border-gray-200 rounded-lg" data-domain-index="{{ $domainIndex }}">
                    <!-- Domain Header -->
                    <div class="bg-green-600 text-white p-4 rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <input type="text"
                                   name="domains[{{ $domainIndex }}][name]"
                                   value="{{ $domain['name'] }}"
                                   class="bg-transparent border-0 text-white text-lg font-semibold placeholder-green-200 focus:outline-none focus:ring-0 flex-1"
                                   placeholder="Domain Name"
                                   required>
                            <div class="flex items-center space-x-2">
                                <span class="text-green-200 text-sm">Domain {{ $domainIndex + 1 }}</span>
                                @if(count($questions) > 1)
                                    <button type="button" onclick="removeDomain({{ $domainIndex }})"
                                            class="text-red-200 hover:text-red-100 p-1">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Strands Container -->
                    <div class="p-4">
                        <div class="strands-container" data-domain-index="{{ $domainIndex }}">
                            @foreach($domain['strands'] as $strandIndex => $strand)
                                <div class="strand-item mb-6 border border-gray-100 rounded-lg" data-strand-index="{{ $strandIndex }}">
                                    <!-- Strand Header -->
                                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <input type="text"
                                                   name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][name]"
                                                   value="{{ $strand['name'] }}"
                                                   class="bg-transparent border-0 text-gray-800 font-medium placeholder-gray-400 focus:outline-none focus:ring-0 flex-1"
                                                   placeholder="Strand Name"
                                                   required>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" onclick="addQuestion({{ $domainIndex }}, {{ $strandIndex }})"
                                                        class="text-blue-600 hover:text-blue-700 text-sm">
                                                    <i class="fas fa-plus mr-1"></i>Add Question
                                                </button>
                                                @if(count($domain['strands']) > 1)
                                                    <button type="button" onclick="removeStrand({{ $domainIndex }}, {{ $strandIndex }})"
                                                            class="text-red-600 hover:text-red-700 text-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Questions Container -->
                                    <div class="questions-container p-4 space-y-4" data-domain-index="{{ $domainIndex }}" data-strand-index="{{ $strandIndex }}">
                                        @foreach($strand['questions'] as $questionIndex => $question)
                                            <div class="question-item border border-gray-200 rounded-lg p-4" data-question-index="{{ $questionIndex }}">
                                                <div class="flex items-start justify-between mb-3">
                                                    <label class="block text-sm font-medium text-gray-700">
                                                        Question {{ $domainIndex + 1 }}.{{ $strandIndex + 1 }}.{{ $questionIndex + 1 }}
                                                    </label>
                                                    @if(count($strand['questions']) > 1)
                                                        <button type="button" onclick="removeQuestion({{ $domainIndex }}, {{ $strandIndex }}, {{ $questionIndex }})"
                                                                class="text-red-600 hover:text-red-700 text-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>

                                                <!-- Question Text -->
                                                <div class="mb-3">
                                                    <textarea name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][questions][{{ $questionIndex }}][text]"
                                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                                              rows="2"
                                                              placeholder="Enter question text"
                                                              required>{{ $question['text'] }}</textarea>
                                                </div>

                                                <!-- Access Levels -->
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Who can see this question?</label>
                                                    <div class="flex space-x-4">
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][questions][{{ $questionIndex }}][access_levels][]"
                                                                   value="adviser"
                                                                   {{ in_array('adviser', $question['access_levels'] ?? []) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                            <span class="ml-2 text-sm text-gray-700">Adviser</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][questions][{{ $questionIndex }}][access_levels][]"
                                                                   value="peer"
                                                                   {{ in_array('peer', $question['access_levels'] ?? []) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                            <span class="ml-2 text-sm text-gray-700">Peer</span>
                                                        </label>
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][questions][{{ $questionIndex }}][access_levels][]"
                                                                   value="self"
                                                                   {{ in_array('self', $question['access_levels'] ?? []) ? 'checked' : '' }}
                                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                                            <span class="ml-2 text-sm text-gray-700">Self</span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Rating Options -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating Scale</label>
                                                    <div class="grid grid-cols-2 gap-2">
                                                        @foreach($question['rating_options'] as $optionIndex => $option)
                                                            <div class="flex items-center space-x-2">
                                                                <input type="number"
                                                                       name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][questions][{{ $questionIndex }}][rating_options][{{ $optionIndex }}][value]"
                                                                       value="{{ $option['value'] }}"
                                                                       class="w-12 border border-gray-300 rounded px-2 py-1 text-sm text-center"
                                                                       min="0" max="3" required readonly>
                                                                <input type="text"
                                                                       name="domains[{{ $domainIndex }}][strands][{{ $strandIndex }}][questions][{{ $questionIndex }}][rating_options][{{ $optionIndex }}][label]"
                                                                       value="{{ $option['label'] }}"
                                                                       class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm"
                                                                       placeholder="Option label" required>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Add Strand Button -->
                        <button type="button" onclick="addStrand({{ $domainIndex }})"
                                class="w-full border-2 border-dashed border-gray-300 rounded-lg p-4 text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add Strand
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Add Domain Button -->
        <button type="button" onclick="addDomain()"
                class="w-full border-2 border-dashed border-blue-300 rounded-lg p-4 text-blue-500 hover:border-blue-400 hover:text-blue-600 transition-colors mb-6">
            <i class="fas fa-plus mr-2"></i>Add Domain
        </button>

        <!-- Enhanced Information -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Enhanced Admin Controls</h3>
                    <div class="text-sm text-blue-700 mt-1">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Add/remove domains, strands, and questions using the buttons</li>
                            <li>Control who sees each question with access level checkboxes</li>
                            <li>Rating values (0-3) are fixed for scoring consistency</li>
                            <li>Changes apply to new evaluations after saving</li>
                            <li>At least one access level must be selected per question</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.evaluation_forms.index') }}"
               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-save mr-2"></i>Save Questions
            </button>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let domainCounter = {{ count($questions) }};
    let strandCounters = {};
    let questionCounters = {};

    // Initialize counters
    @foreach($questions as $domainIndex => $domain)
        strandCounters[{{ $domainIndex }}] = {{ count($domain['strands']) }};
        questionCounters[{{ $domainIndex }}] = {};
        @foreach($domain['strands'] as $strandIndex => $strand)
            questionCounters[{{ $domainIndex }}][{{ $strandIndex }}] = {{ count($strand['questions']) }};
        @endforeach
    @endforeach

    console.log('Evaluation form initialized:', {
        domainCounter: domainCounter,
        strandCounters: strandCounters,
        questionCounters: questionCounters
    });

    // Test that functions are accessible
    console.log('Functions available:', {
        addDomain: typeof window.addDomain,
        addQuestion: typeof window.addQuestion,
        addStrand: typeof window.addStrand
    });

    // Make functions global so they can be called from onclick handlers
    window.addDomain = function() {
        console.log('addDomain called');
        const container = document.getElementById('domainsContainer');

        if (!container) {
            console.error('domainsContainer not found');
            return;
        }

        const domainIndex = domainCounter++;
        console.log('Creating domain with index:', domainIndex);

        strandCounters[domainIndex] = 1;
        questionCounters[domainIndex] = {0: 1};

    const domainHtml = `
        <div class="domain-item mb-8 border border-gray-200 rounded-lg" data-domain-index="${domainIndex}">
            <div class="bg-green-600 text-white p-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <input type="text"
                           name="domains[${domainIndex}][name]"
                           value="New Domain"
                           class="bg-transparent border-0 text-white text-lg font-semibold placeholder-green-200 focus:outline-none focus:ring-0 flex-1"
                           placeholder="Domain Name"
                           required>
                    <div class="flex items-center space-x-2">
                        <span class="text-green-200 text-sm">Domain ${domainIndex + 1}</span>
                        <button type="button" onclick="removeDomain(${domainIndex})"
                                class="text-red-200 hover:text-red-100 p-1">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="strands-container" data-domain-index="${domainIndex}">
                    <div class="strand-item mb-6 border border-gray-100 rounded-lg" data-strand-index="0">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <input type="text"
                                       name="domains[${domainIndex}][strands][0][name]"
                                       value="New Strand"
                                       class="bg-transparent border-0 text-gray-800 font-medium placeholder-gray-400 focus:outline-none focus:ring-0 flex-1"
                                       placeholder="Strand Name"
                                       required>
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="addQuestion(${domainIndex}, 0)"
                                            class="text-blue-600 hover:text-blue-700 text-sm">
                                        <i class="fas fa-plus mr-1"></i>Add Question
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="questions-container p-4 space-y-4" data-domain-index="${domainIndex}" data-strand-index="0">
                            ${createQuestionHtml(domainIndex, 0, 0)}
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addStrand(${domainIndex})"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-4 text-gray-500 hover:border-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add Strand
                </button>
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', domainHtml);
        updateDomainNumbers();
    };

    window.removeDomain = function(domainIndex) {
    if (confirm('Are you sure you want to remove this domain? All strands and questions within it will be deleted.')) {
        const domainElement = document.querySelector(`[data-domain-index="${domainIndex}"]`);
        domainElement.remove();
            delete strandCounters[domainIndex];
            delete questionCounters[domainIndex];
            updateDomainNumbers();
        }
    };

    window.addStrand = function(domainIndex) {
    const container = document.querySelector(`[data-domain-index="${domainIndex}"] .strands-container`);
    const strandIndex = strandCounters[domainIndex]++;

    if (!questionCounters[domainIndex]) {
        questionCounters[domainIndex] = {};
    }
    questionCounters[domainIndex][strandIndex] = 1;

    const strandHtml = `
        <div class="strand-item mb-6 border border-gray-100 rounded-lg" data-strand-index="${strandIndex}">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <input type="text"
                           name="domains[${domainIndex}][strands][${strandIndex}][name]"
                           value="New Strand"
                           class="bg-transparent border-0 text-gray-800 font-medium placeholder-gray-400 focus:outline-none focus:ring-0 flex-1"
                           placeholder="Strand Name"
                           required>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="addQuestion(${domainIndex}, ${strandIndex})"
                                class="text-blue-600 hover:text-blue-700 text-sm">
                            <i class="fas fa-plus mr-1"></i>Add Question
                        </button>
                        <button type="button" onclick="removeStrand(${domainIndex}, ${strandIndex})"
                                class="text-red-600 hover:text-red-700 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="questions-container p-4 space-y-4" data-domain-index="${domainIndex}" data-strand-index="${strandIndex}">
                ${createQuestionHtml(domainIndex, strandIndex, 0)}
            </div>
        </div>
    `;

        container.insertAdjacentHTML('beforeend', strandHtml);
    };

    window.removeStrand = function(domainIndex, strandIndex) {
    const strandsInDomain = document.querySelectorAll(`[data-domain-index="${domainIndex}"] .strand-item`);
    if (strandsInDomain.length <= 1) {
        alert('Each domain must have at least one strand.');
        return;
    }

    if (confirm('Are you sure you want to remove this strand? All questions within it will be deleted.')) {
            const strandElement = document.querySelector(`[data-domain-index="${domainIndex}"] [data-strand-index="${strandIndex}"]`);
            strandElement.remove();
            delete questionCounters[domainIndex][strandIndex];
        }
    };

    window.addQuestion = function(domainIndex, strandIndex) {
        console.log('addQuestion called with:', { domainIndex, strandIndex });
        const container = document.querySelector(`[data-domain-index="${domainIndex}"] [data-strand-index="${strandIndex}"] .questions-container`);

        if (!container) {
            console.error('Container not found for:', { domainIndex, strandIndex });
            return;
        }

        if (!questionCounters[domainIndex] || !questionCounters[domainIndex][strandIndex]) {
            console.error('Question counter not found for:', { domainIndex, strandIndex });
            return;
        }

        const questionIndex = questionCounters[domainIndex][strandIndex]++;
        console.log('Creating question with index:', questionIndex);

        const questionHtml = createQuestionHtml(domainIndex, strandIndex, questionIndex);
        container.insertAdjacentHTML('beforeend', questionHtml);
        console.log('Question added successfully');
    };

    window.removeQuestion = function(domainIndex, strandIndex, questionIndex) {
    const questionsInStrand = document.querySelectorAll(`[data-domain-index="${domainIndex}"] [data-strand-index="${strandIndex}"] .question-item`);
    if (questionsInStrand.length <= 1) {
        alert('Each strand must have at least one question.');
        return;
    }

        if (confirm('Are you sure you want to remove this question?')) {
            const questionElement = document.querySelector(`[data-domain-index="${domainIndex}"] [data-strand-index="${strandIndex}"] [data-question-index="${questionIndex}"]`);
            questionElement.remove();
        }
    };

    function createQuestionHtml(domainIndex, strandIndex, questionIndex) {
    return `
        <div class="question-item border border-gray-200 rounded-lg p-4" data-question-index="${questionIndex}">
            <div class="flex items-start justify-between mb-3">
                <label class="block text-sm font-medium text-gray-700">
                    Question ${domainIndex + 1}.${strandIndex + 1}.${questionIndex + 1}
                </label>
                <button type="button" onclick="removeQuestion(${domainIndex}, ${strandIndex}, ${questionIndex})"
                        class="text-red-600 hover:text-red-700 text-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="mb-3">
                <textarea name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][text]"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                          rows="2"
                          placeholder="Enter question text"
                          required>New Question</textarea>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Who can see this question?</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][access_levels][]"
                               value="adviser" checked
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Adviser</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][access_levels][]"
                               value="peer" checked
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Peer</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][access_levels][]"
                               value="self" checked
                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Self</span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating Scale</label>
                <div class="grid grid-cols-2 gap-2">
                    <div class="flex items-center space-x-2">
                        <input type="number" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][0][value]" value="0" class="w-12 border border-gray-300 rounded px-2 py-1 text-sm text-center" min="0" max="3" required readonly>
                        <input type="text" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][0][label]" value="Poor (0.00)" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Option label" required>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="number" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][1][value]" value="1" class="w-12 border border-gray-300 rounded px-2 py-1 text-sm text-center" min="0" max="3" required readonly>
                        <input type="text" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][1][label]" value="Fair (1.00)" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Option label" required>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="number" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][2][value]" value="2" class="w-12 border border-gray-300 rounded px-2 py-1 text-sm text-center" min="0" max="3" required readonly>
                        <input type="text" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][2][label]" value="Good (2.00)" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Option label" required>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="number" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][3][value]" value="3" class="w-12 border border-gray-300 rounded px-2 py-1 text-sm text-center" min="0" max="3" required readonly>
                        <input type="text" name="domains[${domainIndex}][strands][${strandIndex}][questions][${questionIndex}][rating_options][3][label]" value="Excellent (3.00)" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm" placeholder="Option label" required>
                    </div>
                </div>
            </div>
        </div>
        `;
    }

    function updateDomainNumbers() {
        const domains = document.querySelectorAll('.domain-item');
        domains.forEach((domain, index) => {
            const numberSpan = domain.querySelector('.text-green-200');
            if (numberSpan) {
                numberSpan.textContent = `Domain ${index + 1}`;
            }
        });
    }

    // Form validation
    document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    let hasError = false;

    // Check that each question has at least one access level
    const questions = document.querySelectorAll('.question-item');
    questions.forEach(question => {
        const checkboxes = question.querySelectorAll('input[type="checkbox"]:checked');
        if (checkboxes.length === 0) {
            hasError = true;
            alert('Each question must have at least one access level selected (Adviser, Peer, or Self).');
            return false;
        }
    });

        if (hasError) {
            e.preventDefault();
        }
    });
});
</script>

@endsection
