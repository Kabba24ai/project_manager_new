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
    resetForm() {
        this.formData = {
            name: '',
            template_text: '',
            is_universal: false,
            task_types: []
        };
        this.editingTemplate = null;
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

            <form :action="editingTemplate ? '{{ url('task-templates') }}/' + editingTemplate.id : '{{ route('task-templates.store') }}'" method="POST">
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
                        <textarea
                            id="template_text"
                            name="template_text"
                            x-model="formData.template_text"
                            rows="4"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                            placeholder="Enter the template description text (e.g., 'Order Parts', 'Contact Customer')"
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
                        Cancel
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
                            
                            // Check if template already exists in this group
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
                                <p class="text-sm text-gray-600 mb-2">{{ $template->template_text }}</p>
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
@endsection

