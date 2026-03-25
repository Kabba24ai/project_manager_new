@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Add Task List')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="createTaskList()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('projects.show', $project->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Project</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Add Task List</h1>
                <div class="h-6 w-px bg-gray-300"></div>
                <span class="text-gray-600">{{ $project->name }}</span>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-2xl mx-auto px-6 py-8">
        <form action="{{ route('task-lists.store', $project->id) }}" method="POST">
            @csrf
            <input type="hidden" name="template_id" x-model="selectedTemplateId">

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-list text-3xl text-blue-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Create New Task List</h2>
                    <p class="text-gray-600">Organize your project tasks with custom task lists</p>
                </div>

                @php $templates = $taskListTemplates ?? collect(); @endphp

                <!-- Template Selection -->
                @if($templates->count() > 0)
                <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-layer-group text-green-600 text-sm"></i>
                        <span class="text-sm font-semibold text-gray-800">Use a Template</span>
                        <span class="text-xs text-gray-500">(optional — auto-fills the form and creates tasks)</span>
                    </div>

                    <!-- Template grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">
                        <!-- None option -->
                        <button type="button" @click="applyTemplate(null)"
                                :class="selectedTemplateId === null ? 'border-gray-400 bg-white shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border-2 transition-all text-left">
                            <div class="w-6 h-6 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-ban text-gray-400 text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-600 font-medium">No Template</span>
                            <i x-show="selectedTemplateId === null" class="fas fa-check text-xs text-gray-500 ml-auto"></i>
                        </button>

                        @foreach($templates as $tpl)
                        @php $tplTasks = $tpl->tasks ?? []; @endphp
                        <button type="button" @click="applyTemplate({{ json_encode(['id' => $tpl->id, 'name' => $tpl->name, 'description' => $tpl->description, 'color' => $tpl->color ?? 'bg-blue-100', 'tasks' => $tplTasks]) }})"
                                :class="selectedTemplateId === {{ $tpl->id }} ? 'border-blue-500 shadow-sm' : 'border-gray-200 hover:border-blue-300'"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg border-2 transition-all text-left {{ $tpl->color ?? 'bg-blue-100' }} bg-opacity-40">
                            <div class="w-6 h-6 bg-white bg-opacity-70 rounded flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-layer-group text-gray-600 text-xs"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-800 truncate">{{ $tpl->name }}</div>
                                <div class="text-xs text-gray-500">{{ count($tplTasks) }} {{ Str::plural('task', count($tplTasks)) }}</div>
                            </div>
                            <i x-show="selectedTemplateId === {{ $tpl->id }}" class="fas fa-check text-xs text-blue-600 flex-shrink-0"></i>
                        </button>
                        @endforeach
                    </div>

                    <!-- Selected template tasks preview -->
                    <div x-show="selectedTemplateId !== null && templateTasks.length > 0" x-cloak
                         class="bg-white rounded-lg border border-green-200 p-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                            Tasks to be created (<span x-text="templateTasks.length"></span>)
                        </p>
                        <div class="space-y-1 max-h-36 overflow-y-auto">
                            <template x-for="(t, i) in templateTasks" :key="i">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                          :class="{
                                              'bg-red-500':    t.priority === 'urgent',
                                              'bg-orange-400': t.priority === 'high',
                                              'bg-yellow-400': t.priority === 'medium',
                                              'bg-gray-300':   t.priority === 'low'
                                          }"></span>
                                    <span class="text-gray-700 truncate flex-1" x-text="t.title"></span>
                                    <span class="text-xs text-gray-400" x-text="t.priority"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-6">
                    <!-- Task List Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Task List Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            x-model="formName"
                            value="{{ old('name') }}"
                            required
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-300 bg-red-50 @enderror"
                            placeholder="e.g., To Do, In Progress, Review, Done"
                        />
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Task List Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Short Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            x-model="formDescription"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('description') border-red-300 bg-red-50 @enderror"
                            placeholder="Brief description of what tasks belong in this list"
                        >{{ old('description') }}</textarea>
                        @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color Selection -->
                    <div x-data="{ selectedColor: '{{ old('color', 'bg-blue-100') }}' }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            List Color
                        </label>
                        <input type="hidden" name="color" x-model="selectedColor">
                        <div class="grid grid-cols-4 gap-3">
                            @php
                                $colorOptions = [
                                    ['value' => 'bg-blue-100',   'label' => 'Blue'],
                                    ['value' => 'bg-green-100',  'label' => 'Green'],
                                    ['value' => 'bg-yellow-100', 'label' => 'Yellow'],
                                    ['value' => 'bg-red-100',    'label' => 'Red'],
                                    ['value' => 'bg-purple-100', 'label' => 'Purple'],
                                    ['value' => 'bg-indigo-100', 'label' => 'Indigo'],
                                    ['value' => 'bg-pink-100',   'label' => 'Pink'],
                                    ['value' => 'bg-gray-100',   'label' => 'Gray'],
                                ];
                            @endphp

                            @foreach($colorOptions as $color)
                            <button
                                type="button"
                                @click="selectedColor = '{{ $color['value'] }}'"
                                :class="selectedColor === '{{ $color['value'] }}' ? 'border-blue-500 shadow-md' : 'border-gray-200 hover:border-gray-300'"
                                class="p-3 rounded-lg border-2 transition-all duration-200"
                                x-init="$watch('$root.templateColor', val => { if (val) selectedColor = val; })"
                            >
                                <div class="w-full h-8 rounded {{ $color['value'] }} mb-2"></div>
                                <span class="text-xs font-medium text-gray-700">{{ $color['label'] }}</span>
                            </button>
                            @endforeach
                        </div>

                        <!-- Preview -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Preview</h3>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="px-4 py-3 rounded-t-lg border-b border-gray-200" :class="selectedColor">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900" id="preview-name">Task List Name</h4>
                                            <p class="text-sm text-gray-600 mt-1" id="preview-description"></p>
                                        </div>
                                        <span class="bg-white px-2 py-1 rounded-full text-xs font-medium text-gray-600">
                                            0 tasks
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4 text-center text-gray-500 text-sm">
                                    Tasks will appear here
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-200 mt-8">
                    <a
                        href="{{ route('projects.show', $project->id) }}"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Back
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                    >
                        <i class="fas fa-save"></i>
                        <span x-text="selectedTemplateId ? 'Create List + Tasks' : 'Create Task List'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function createTaskList() {
    return {
        selectedTemplateId: null,
        templateColor: null,
        templateTasks: [],
        formName: '{{ old('name') }}',
        formDescription: '{{ old('description') }}',

        applyTemplate(template) {
            if (!template) {
                this.selectedTemplateId = null;
                this.templateColor = null;
                this.templateTasks = [];
                return;
            }
            this.selectedTemplateId = template.id;
            this.templateTasks     = template.tasks || [];
            // Pre-fill the form fields
            if (template.name)        this.formName        = template.name;
            if (template.description) this.formDescription = template.description;
            if (template.color)       this.templateColor   = template.color;

            // Sync name/description to the actual DOM inputs (for the live preview)
            this.$nextTick(() => {
                const nameInput = document.getElementById('name');
                const descInput = document.getElementById('description');
                if (nameInput) {
                    nameInput.dispatchEvent(new Event('input'));
                }
                if (descInput) {
                    descInput.dispatchEvent(new Event('input'));
                }
            });
        },
    };
}

// Live preview update
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');

    if (nameInput && previewName) {
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Task List Name';
        });
    }

    if (descriptionInput && previewDescription) {
        descriptionInput.addEventListener('input', function() {
            previewDescription.textContent = this.value;
            previewDescription.style.display = this.value ? 'block' : 'none';
        });
    }
});
</script>
@endpush
@endsection
