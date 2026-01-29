@extends('layouts.dashboard')

@section('title', 'Manage Lists - ' . $project->name)

@section('content')
<div class="min-h-screen bg-gray-50">
 <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="flex items-center space-x-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ 
                            $project->status === 'active' ? 'bg-green-100 text-green-800' :
                            ($project->status === 'completed' ? 'bg-blue-100 text-blue-800' :
                            ($project->status === 'on-hold' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                        }}">
                            {{ ucfirst($project->status) }}
                        </span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium border {{ 
                            $project->priority === 'urgent' ? 'bg-red-100 text-red-800 border-red-200' :
                            ($project->priority === 'high' ? 'bg-orange-100 text-orange-800 border-orange-200' :
                            ($project->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-green-100 text-green-800 border-green-200'))
                        }}">
                            {{ ucfirst($project->priority) }} priority
                        </span>
                    </div>
                </div>
                
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="h-0 w-0"></div>
                    <div class="h-0 w-px"></div>
                    <div>
                        <p class="text-gray-600 mt-1">{{ $project->description }}</p>
                        
                        <!-- Project Stats -->
                        <div class="flex items-center space-x-6 mt-3 text-sm text-gray-500">
                            @if($project->projectManager)
                            <div class="flex items-center space-x-1">
                                <i class="fas fa-user-tie w-4 h-4"></i>
                                <span>Manager: {{ $project->projectManager->name }}</span>
                            </div>
                            @endif
                            <div class="flex items-center space-x-1">
                                <i class="fas fa-calendar w-4 h-4"></i>
                                <span>Due: {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('M j') : 'NA' }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span>{{ $project->taskLists->sum(function($list) { return $list->tasks->where('task_status', 'approved')->count(); }) }}/{{ $project->taskLists->sum(function($list) { return $list->tasks->count(); }) }} tasks completed</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                @php
                                    $totalTasks = $project->taskLists->sum(function($list) { return $list->tasks->count(); });
                                    $completedTasks = $project->taskLists->sum(function($list) { return $list->tasks->where('task_status', 'approved')->count(); });
                                    $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                                @endphp
                                <span>{{ $progress }}% progress</span>
                            </div>
                            @if($project->createdBy)
                            <div class="flex items-center space-x-1">
                                <i class="fas fa-user-plus w-4 h-4"></i>
                                <span>Created by: {{ $project->createdBy->name }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Team Avatars -->
                    <div class="flex -space-x-2">
                        @foreach($project->teamMembers->sortBy('name')->take(4) as $member)
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-2 border-white text-xs font-medium text-white" title="{{ $member->name }}">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        @endforeach
                        @if($project->teamMembers->count() > 4)
                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center border-2 border-white text-xs font-medium text-gray-600">
                            +{{ $project->teamMembers->count() - 4 }}
                        </div>
                        @endif
                    </div>
                    
                    <a href="{{ route('task-lists.create', $project->id) }}" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus w-4 h-4"></i>
                        <span>Add List</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white max-w-6xl mx-auto px-6 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Manage Task Lists</h2>
                <p class="text-sm text-gray-500 mt-1">Drag and drop to reorder lists</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center space-x-2 text-green-800">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center space-x-2 text-red-800">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center space-x-2 text-red-800 mb-2">
                <i class="fas fa-exclamation-circle"></i>
                <span class="font-semibold">Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="reorderForm" action="{{ route('projects.update-lists-order', $project->id) }}" method="POST">
            @csrf
            
            <!-- Task Lists Container -->
            <div id="taskListsContainer" class="space-y-3">
                @foreach($project->taskLists as $taskList)
                <div 
                    class="task-list-item group relative rounded-lg border-2 transition-all cursor-move overflow-hidden {{ $taskList->color }}"
                    data-id="{{ $taskList->id }}"
                    data-order="{{ $taskList->order }}"
                >
                    <!-- Color Header Bar -->
                    <div class="h-2 w-full"></div>
                    
                    <div class="flex items-center justify-between p-5">
                        <!-- Drag Handle & Content -->
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-grip-vertical text-gray-500 cursor-grab active:cursor-grabbing text-xl hover:text-gray-700"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $taskList->name }}</h3>
                                @if($taskList->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $taskList->description }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2">
                            <a 
                                href="{{ route('task-lists.edit', [$project->id, $taskList->id]) }}"
                                class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                title="Edit list"
                                onclick="event.stopPropagation()"
                            >
                                <i class="fas fa-edit text-lg"></i>
                            </a>
                            <button 
                                type="button"
                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete list"
                                data-delete-url="{{ route('task-lists.destroy', [$project->id, $taskList->id]) }}"
                                onclick="handleDelete(this, event)"
                            >
                                <i class="fas fa-trash text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="task_lists[{{ $loop->index }}][id]" value="{{ $taskList->id }}" class="task-list-id">
                    <input type="hidden" name="task_lists[{{ $loop->index }}][order]" value="{{ $taskList->order }}" class="task-list-order">
                </div>
                @endforeach
            </div>

            @if($project->taskLists->isEmpty())
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-list text-4xl mb-3"></i>
                <p class="text-lg">No task lists yet</p>
                <p class="text-sm mt-1">Create a new task list to get started</p>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-center space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a
                    href="{{ route('projects.show', $project->id) }}"
                    class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                >
                    Cancel
                </a>
                <button
                    type="submit"
                    id="saveButton"
                    form="reorderForm"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <i class="fas fa-save"></i>
                    <span id="saveButtonText">Save Updates</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Include SortableJS from CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js" crossorigin="anonymous"></script>
<!-- Fallback CDN -->
<script>
if (typeof Sortable === 'undefined') {
    console.warn('Primary CDN failed, loading fallback...');
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"><\/script>');
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing Sortable...');
    
    const container = document.getElementById('taskListsContainer');
    console.log('Container found:', container);
    console.log('Container children:', container ? container.children.length : 0);
    
    if (container && container.children.length > 0) {
        // Check if Sortable is available
        if (typeof Sortable === 'undefined') {
            console.error('SortableJS library not loaded!');
            alert('Drag & Drop library failed to load. Please refresh the page.');
            return;
        }
        
        console.log('Initializing Sortable...');
        
        // Initialize Sortable
        const sortable = new Sortable(container, {
            animation: 150,
            handle: '.fa-grip-vertical',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            forceFallback: false,
            fallbackTolerance: 3,
            onStart: function (evt) {
                console.log('Drag started');
            },
            onEnd: function (evt) {
                console.log('Drag ended, updating order...');
                updateOrder();
            }
        });
        
        console.log('Sortable initialized successfully!', sortable);

        // Update order based on new positions
        function updateOrder() {
            const items = container.querySelectorAll('.task-list-item');
            console.log('Updating order for', items.length, 'items');
            items.forEach((item, index) => {
                const orderInput = item.querySelector('.task-list-order');
                const idInput = item.querySelector('.task-list-id');
                
                // Assign order based on position (0, 1, 2, 3, ...)
                orderInput.value = index;
                
                // Update the name attribute to maintain order
                orderInput.name = `task_lists[${index}][order]`;
                idInput.name = `task_lists[${index}][id]`;
                
                // Update the data attribute
                item.dataset.order = index;
            });
        }

        // Prevent form submission on drag
        container.addEventListener('dragstart', function(e) {
            if (e.target.tagName === 'FORM') {
                e.preventDefault();
            }
        });
    } else {
        console.warn('Container not found or empty');
    }
    
    // Handle form submission
    const form = document.getElementById('reorderForm');
    const saveButton = document.getElementById('saveButton');
    const saveButtonText = document.getElementById('saveButtonText');
    
    if (form) {
        console.log('Form found:', form);
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        form.addEventListener('submit', function(e) {
            console.log('Form submit event triggered!');
            
            // Get all form data
            const formData = new FormData(form);
            console.log('Form data entries:');
            for (let pair of formData.entries()) {
                console.log(pair[0], '=', pair[1]);
            }
            
            // Show loading state
            if (saveButton && saveButtonText) {
                saveButton.disabled = true;
                saveButtonText.textContent = 'Saving...';
                const icon = saveButton.querySelector('i');
                if (icon) {
                    icon.className = 'fas fa-spinner fa-spin';
                }
            }
            
            console.log('Form will now submit...');
            // Let the form submit normally - no e.preventDefault()
        });
        
        // Also listen for button click
        if (saveButton) {
            saveButton.addEventListener('click', function(e) {
                console.log('Save button click handler');
                console.log('Button type:', saveButton.type);
                console.log('Button form:', saveButton.form);
            });
        }
    } else {
        console.error('Form not found!');
    }
});

// Also check when Sortable script loads
window.addEventListener('load', function() {
    console.log('Window loaded. Sortable available:', typeof Sortable !== 'undefined');
});

// Handle delete button clicks
function handleDelete(button, event) {
    event.preventDefault();
    event.stopPropagation();
    
    if (confirm('Are you sure you want to delete this task list? All tasks in this list will also be deleted.')) {
        const deleteUrl = button.getAttribute('data-delete-url');
        console.log('Deleting task list:', deleteUrl);
        
        // Create a completely separate form
        const deleteForm = document.createElement('form');
        deleteForm.method = 'POST';
        deleteForm.action = deleteUrl;
        deleteForm.style.display = 'none';
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        deleteForm.appendChild(csrfInput);
        
        // Add DELETE method
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        deleteForm.appendChild(methodInput);
        
        // Append to body and submit
        document.body.appendChild(deleteForm);
        deleteForm.submit();
    }
}
</script>

<style>
/* Sortable styles */
.sortable-ghost {
    opacity: 0.4;
    background: #f3f4f6 !important;
}

.sortable-chosen {
    cursor: grabbing !important;
}

.sortable-drag {
    opacity: 1;
    cursor: grabbing !important;
}

/* Task list item styles */
.task-list-item {
    transition: all 0.2s ease;
    user-select: none;
}

.task-list-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.task-list-item.sortable-chosen {
    transform: scale(1.02);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    cursor: grabbing !important;
}

/* Make sure grip icon is visible and interactive */
.fa-grip-vertical {
    cursor: grab !important;
    pointer-events: all;
    z-index: 10;
}

.fa-grip-vertical:active {
    cursor: grabbing !important;
}

/* Ensure proper cursor on hover */
.task-list-item:hover .fa-grip-vertical {
    color: #4b5563;
}
</style>
@endsection
