@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Task Description Templates')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{ 
    showAddForm: false, 
    editingTemplate: null,
    formData: {
        name: '',
        template_text: '',
        is_universal: false,
        task_types: []
    },
    editorInstance: null,
    resetForm() {
        this.formData = {
            name: '',
            template_text: '',
            is_universal: false,
            task_types: []
        };
        this.editingTemplate = null;
        if (window.templateEditor) {
            window.templateEditor.setData('');
        }
    },
    handleCancel() {
        this.resetForm();
        this.showAddForm = false;
    },
    handleEdit(template) {
        this.editingTemplate = template;
        this.formData = {
            name: template.name,
            template_text: template.template_text,
            is_universal: template.is_universal,
            task_types: template.task_types || []
        };
        this.showAddForm = true;
        setTimeout(() => {
            if (window.templateEditor) {
                window.templateEditor.setData(template.template_text || '');
            }
            document.getElementById('template_text').value = template.template_text || '';
        }, 200);
    },
    toggleTaskType(type) {
        const index = this.formData.task_types.indexOf(type);
        if (index > -1) {
            this.formData.task_types.splice(index, 1);
        } else {
            this.formData.task_types.push(type);
        }
    }
}">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left w-5 h-5"></i>
                        <span>Back</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <h1 class="text-2xl font-bold text-gray-900">Task Description Templates</h1>
                </div>
                <button 
                    x-show="!showAddForm" 
                    @click="showAddForm = true"
                    class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <i class="fas fa-plus w-4 h-4"></i>
                    <span>Add Template</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">
        
        <!-- Add/Edit Form -->
        <div x-show="showAddForm" x-cloak class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4" x-text="editingTemplate ? 'Edit Template' : 'Add New Template'"></h2>

            <form :action="editingTemplate ? '{{ url('task-templates') }}/' + editingTemplate.id : '{{ route('task-templates.store') }}'" method="POST" @submit="if (window.templateEditor) { document.getElementById('template_text').value = window.templateEditor.getData(); }">
                @csrf
                <input type="hidden" name="_method" x-bind:value="editingTemplate ? 'PUT' : 'POST'">
                
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Template Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            x-model="formData.name"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Enter a descriptive name (e.g., 'Bug Report Template', 'Feature Request')"
                        />
                    </div>

                    <div>
                        <label for="template_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Template Text *
                        </label>
                        <div id="template_text_editor" style="min-height: 120px;"></div>
                        <textarea
                            id="template_text"
                            name="template_text"
                            style="display: none;"
                        ></textarea>
                    </div>

                    <div>
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                name="is_universal"
                                x-model="formData.is_universal"
                                value="1"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="text-sm font-medium text-gray-700">
                                Universal Template (appears for all task types)
                            </span>
                        </label>
                    </div>

                    <div x-show="!formData.is_universal">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Apply to Task Types *
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="task_types[]"
                                    value="general"
                                    @change="toggleTaskType('general')"
                                    :checked="formData.task_types.includes('general')"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">General</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="task_types[]"
                                    value="equipmentId"
                                    @change="toggleTaskType('equipmentId')"
                                    :checked="formData.task_types.includes('equipmentId')"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Equipment ID</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input
                                    type="checkbox"
                                    name="task_types[]"
                                    value="customerName"
                                    @change="toggleTaskType('customerName')"
                                    :checked="formData.task_types.includes('customerName')"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <span class="text-sm text-gray-700">Customer</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t mt-4">
                    <button
                        type="button"
                        @click="handleCancel()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Back
                    </button>
                    <button
                        type="submit"
                        class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <i class="fas fa-save w-4 h-4"></i>
                        <span x-text="editingTemplate ? 'Update Template' : 'Create Template'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Templates List -->
        @if($templates->count() === 0)
        <div class="text-center bg-white rounded-lg border border-gray-200 p-12">
            <i class="fas fa-file-alt text-gray-300 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Templates Yet</h3>
            <p class="text-gray-500 mb-6">
                Create your first template to quickly add common task descriptions.
            </p>
            <button
                x-show="!showAddForm"
                @click="showAddForm = true"
                class="inline-flex items-center space-x-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                <i class="fas fa-plus w-4 h-4"></i>
                <span>Add Template</span>
            </button>
        </div>
        @else
        <div class="space-y-6">
            @php
                $groupedTemplates = [];
                
                foreach($templates as $template) {
                    if($template->is_universal) {
                        if(!isset($groupedTemplates['Universal'])) {
                            $groupedTemplates['Universal'] = [];
                        }
                        $groupedTemplates['Universal'][] = $template;
                    } else {
                        $taskTypes = $template->task_types ?? [];
                        if(empty($taskTypes)) {
                            // Template has no task types and is not universal → show under General
                            if(!isset($groupedTemplates['General'])) {
                                $groupedTemplates['General'] = [];
                            }
                            $groupedTemplates['General'][] = $template;
                        } else {
                            foreach($taskTypes as $taskType) {
                                $label = match($taskType) {
                                    'general' => 'General',
                                    'equipmentId' => 'Equipment ID',
                                    'customerName' => 'Customer',
                                    default => $taskType
                                };

                                if(!isset($groupedTemplates[$label])) {
                                    $groupedTemplates[$label] = [];
                                }

                                // Avoid duplicates within the same group
                                $exists = false;
                                foreach($groupedTemplates[$label] as $existingTemplate) {
                                    if($existingTemplate->id === $template->id) {
                                        $exists = true;
                                        break;
                                    }
                                }

                                if(!$exists) {
                                    $groupedTemplates[$label][] = $template;
                                }
                            }
                        }
                    }
                }
                
                // Sort each group by name
                foreach($groupedTemplates as $group => $templates) {
                    usort($groupedTemplates[$group], function($a, $b) {
                        return strcmp($a->name, $b->name);
                    });
                }
                
                // Sort groups
                ksort($groupedTemplates);
            @endphp

            @foreach($groupedTemplates as $group => $groupTemplates)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center space-x-2">
                    <span>{{ $group }}</span>
                    <span class="text-sm font-normal text-gray-500">({{ count($groupTemplates) }})</span>
                </h2>
                <div class="space-y-3">
                    @foreach($groupTemplates as $template)
                    <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <i class="fas fa-file-alt w-4 h-4 text-gray-400"></i>
                                    <p class="text-gray-900 font-semibold">{{ $template->name }}</p>
                                </div>
                                <div class="text-sm text-gray-600 mb-2 template-text-display">{!! $template->template_text !!}</div>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    @if($template->is_universal)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                        Universal
                                    </span>
                                    @else
                                    <span class="text-xs">
                                        Task Types: 
                                        @php
                                            $taskTypeLabels = array_map(function($type) {
                                                return match($type) {
                                                    'general' => 'General',
                                                    'equipmentId' => 'Equipment ID',
                                                    'customerName' => 'Customer',
                                                    default => $type
                                                };
                                            }, $template->task_types ?? []);
                                        @endphp
                                        {{ implode(', ', $taskTypeLabels) ?: 'None' }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                <button
                                    @click="handleEdit({{ json_encode([
                                        'id' => $template->id,
                                        'name' => $template->name,
                                        'template_text' => $template->template_text,
                                        'is_universal' => $template->is_universal,
                                        'task_types' => $template->task_types ?? []
                                    ]) }})"
                                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded"
                                    title="Edit template"
                                >
                                    <i class="fas fa-pencil-alt w-4 h-4"></i>
                                </button>
                                <form action="{{ route('task-templates.destroy', $template->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-2 text-gray-400 hover:text-red-600 transition-colors rounded"
                                        title="Delete template"
                                    >
                                        <i class="fas fa-trash w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<style>
/* CKEditor height */
.ck-editor__editable {
    min-height: 120px !important;
    max-height: 300px !important;
}

/* CKEditor link color */
.ck-editor__editable a {
    color: #2563eb !important;
    text-decoration: underline !important;
}

.ck-editor__editable a:hover {
    color: #1d4ed8 !important;
}

/* Template text display styling - match task description styles */
.template-text-display {
    line-height: 1.6;
}

.template-text-display p {
    margin-bottom: 0.5em;
}

.template-text-display p:last-child {
    margin-bottom: 0;
}

.template-text-display a {
    color: #2563eb;
    text-decoration: underline;
    transition: color 0.2s;
}

.template-text-display a:hover {
    color: #1d4ed8;
}

.template-text-display ul,
.template-text-display ol {
    margin-left: 1.5em;
    margin-bottom: 0.5em;
}

.template-text-display li {
    margin-bottom: 0.25em;
}

.template-text-display h1,
.template-text-display h2,
.template-text-display h3,
.template-text-display h4,
.template-text-display h5,
.template-text-display h6 {
    font-weight: 600;
    margin-top: 0.75em;
    margin-bottom: 0.5em;
}

.template-text-display h1 {
    font-size: 1.5em;
}

.template-text-display h2 {
    font-size: 1.3em;
}

.template-text-display h3 {
    font-size: 1.1em;
}

.template-text-display strong,
.template-text-display b {
    font-weight: 600;
}

.template-text-display em,
.template-text-display i {
    font-style: italic;
}

.template-text-display blockquote {
    border-left: 4px solid #e5e7eb;
    padding-left: 1em;
    margin-left: 0;
    margin-bottom: 0.5em;
    color: #6b7280;
}

.template-text-display table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 0.5em;
}

.template-text-display table th,
.template-text-display table td {
    border: 1px solid #e5e7eb;
    padding: 0.5em;
    text-align: left;
}

.template-text-display table th {
    background-color: #f9fafb;
    font-weight: 600;
}
</style>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
var templateEditor = null;

document.addEventListener('alpine:init', () => {
    // Watch showAddForm; when form opens, init CKEditor (mirrors tasks/create pattern)
    setTimeout(() => {
        const component = document.querySelector('[x-data]');
        if (!component || !window.Alpine) return;

        Alpine.$data(component).$watch('showAddForm', (value) => {
            if (!value) return;
            setTimeout(() => {
                if (templateEditor) return; // already initialised

                ClassicEditor
                    .create(document.querySelector('#template_text_editor'), {
                        toolbar: {
                            items: [
                                'heading', '|',
                                'bold', 'italic', 'underline', 'strikethrough', '|',
                                'bulletedList', 'numberedList', '|',
                                'link', 'blockQuote', 'insertTable', '|',
                                'undo', 'redo'
                            ]
                        },
                        placeholder: 'Enter the template description text...',
                        link: {
                            decorators: {
                                openInNewTab: {
                                    mode: 'manual',
                                    label: 'Open in a new tab',
                                    attributes: {
                                        target: '_blank',
                                        rel: 'noopener noreferrer'
                                    }
                                }
                            }
                        }
                    })
                    .then(editor => {
                        templateEditor = editor;
                        window.templateEditor = editor;

                        // Set height
                        const editorElement = editor.ui.getEditableElement();
                        if (editorElement) {
                            editorElement.style.minHeight = '120px';
                            editorElement.style.height = '120px';
                        }

                        // Load any pre-existing content (edit mode)
                        const initialContent = document.getElementById('template_text').value;
                        if (initialContent) {
                            editor.setData(initialContent);
                        }

                        // Sync to hidden textarea + Alpine on every change
                        editor.model.document.on('change:data', () => {
                            const data = editor.getData();
                            document.getElementById('template_text').value = data;
                            const comp = document.querySelector('[x-data]');
                            if (comp && window.Alpine) {
                                Alpine.$data(comp).formData.template_text = data;
                            }
                        });
                    })
                    .catch(error => {
                        console.error('CKEditor init error:', error);
                    });
            }, 100);
        });
    }, 0);
});
</script>
@endsection
