@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Edit Task')


@section('content')
<div class="min-h-screen bg-gray-50" x-data="taskForm">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('projects.show', $project->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left w-5 h-5"></i>
                    <span>Back to Project</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Task</h1>
                <div class="h-6 w-px bg-gray-300"></div>
                <span class="text-gray-600">{{ $project->name }}</span>
                <template x-if="taskListId && getSelectedTaskList()">
                    <div class="flex items-center space-x-2">
                        <div class="h-6 w-px bg-gray-300"></div>
                        <span class="text-gray-500">in</span>
                        <div class="px-3 py-1 rounded-lg text-sm font-medium" :class="getSelectedTaskList()?.color">
                            <span x-text="getSelectedTaskList()?.name"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-4xl mx-auto px-6 py-8">
        @if($project->taskLists->count() === 0)
        <!-- No Task Lists Message -->
        <div class="text-center bg-white rounded-lg border border-gray-200 p-12">
            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-flag text-3xl text-yellow-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">No Task Lists Available</h3>
            <p class="text-gray-500 mb-6">
                You need to create at least one task list before you can edit tasks. 
                Task lists help organize your work into categories like "To Do", "In Progress", etc.
            </p>
            <a href="{{ route('task-lists.create', $project->id) }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Create Task List First
            </a>
        </div>
        @else
        <!-- Task Form -->
        <form x-ref="taskForm" action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-8">
                <!-- Step 1: Task Type Selection -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                        <h2 class="text-lg font-semibold text-gray-900">Select Task Type</h2>
                    </div>
                    <p class="text-gray-600 mb-6">Choose the type of task.</p>
                    
                    <input type="hidden" name="task_type" x-model="taskType">
                    
                    @php
                        // If project has no settings, enable all task types by default
                        $hasSettings = isset($project->settings['taskTypes']);
                        
                        $enabledTaskTypes = [];
                        $generalEnabled = $hasSettings 
                            ? (isset($project->settings['taskTypes']['general']) && $project->settings['taskTypes']['general'])
                            : true;
                        $equipmentEnabled = $hasSettings 
                            ? (isset($project->settings['taskTypes']['equipmentId']) && $project->settings['taskTypes']['equipmentId'])
                            : true;
                        $customerEnabled = $hasSettings 
                            ? (isset($project->settings['taskTypes']['customerName']) && $project->settings['taskTypes']['customerName'])
                            : true;
                        
                        if ($generalEnabled) $enabledTaskTypes[] = 'general';
                        if ($equipmentEnabled) $enabledTaskTypes[] = 'equipmentId';
                        if ($customerEnabled) $enabledTaskTypes[] = 'customerName';
                        
                        $enabledCount = count($enabledTaskTypes);
                        $gridClass = $enabledCount === 3 ? 'grid-cols-3' : ($enabledCount === 2 ? 'grid-cols-2' : 'grid-cols-1');
                    @endphp
                    
                    <div class="grid {{ $gridClass }} gap-4">
                        <!-- General Task -->
                        @if($generalEnabled)
                        <div class="relative" @mouseenter="showTooltip = 'general'" @mouseleave="showTooltip = null">
                            <button
                                type="button"
                                @click="taskType = 'general'"
                                :class="taskType === 'general' ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-200 hover:border-blue-300 hover:shadow-sm'"
                                class="w-full p-4 border-2 rounded-lg transition-all duration-200 text-center"
                            >
                                <div :class="taskType === 'general' ? 'bg-blue-500' : 'bg-gray-300'" class="w-3 h-3 rounded-full mx-auto mb-2"></div>
                                <h3 class="font-semibold text-gray-900">General</h3>
                            </button>
                            
                            <div x-show="showTooltip === 'general'" x-cloak class="absolute z-20 bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-3 bg-gray-900 text-white text-sm rounded-lg shadow-lg">
                                <div class="text-center">
                                    Standard project task with manually entered details. Perfect for development, design, or administrative tasks.
                                </div>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Equipment ID Task -->
                        @if($equipmentEnabled)
                        <div class="relative" @mouseenter="showTooltip = 'equipment'" @mouseleave="showTooltip = null">
                            <button
                                type="button"
                                @click="taskType = 'equipmentId'"
                                :class="taskType === 'equipmentId' ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-200 hover:border-blue-300 hover:shadow-sm'"
                                class="w-full p-4 border-2 rounded-lg transition-all duration-200 text-center"
                            >
                                <div :class="taskType === 'equipmentId' ? 'bg-blue-500' : 'bg-gray-300'" class="w-3 h-3 rounded-full mx-auto mb-2"></div>
                                <h3 class="font-semibold text-gray-900">Equipment ID</h3>
                            </button>
                            
                            <div x-show="showTooltip === 'equipment'" x-cloak class="absolute z-20 bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-3 bg-gray-900 text-white text-sm rounded-lg shadow-lg">
                                <div class="text-center">
                                    Task related to specific rental equipment. Links directly to your equipment inventory and rental system.
                                </div>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Customer Name Task -->
                        @if($customerEnabled)
                        <div class="relative" @mouseenter="showTooltip = 'customer'" @mouseleave="showTooltip = null">
                            <button
                                type="button"
                                @click="taskType = 'customerName'"
                                :class="taskType === 'customerName' ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-200 hover:border-blue-300 hover:shadow-sm'"
                                class="w-full p-4 border-2 rounded-lg transition-all duration-200 text-center"
                            >
                                <div :class="taskType === 'customerName' ? 'bg-blue-500' : 'bg-gray-300'" class="w-3 h-3 rounded-full mx-auto mb-2"></div>
                                <h3 class="font-semibold text-gray-900">Customer</h3>
                            </button>
                            
                            <div x-show="showTooltip === 'customer'" x-cloak class="absolute z-20 bottom-full left-1/2 transform -translate-x-1/2 mb-2 w-64 p-3 bg-gray-900 text-white text-sm rounded-lg shadow-lg">
                                <div class="text-center">
                                    Task linked to a specific customer. Perfect for customer support, follow-ups, or customer-specific projects.
                                </div>
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    @if(count($enabledTaskTypes) === 0)
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-3xl mb-2"></i>
                        <p>No task types are enabled for this project. Please contact the project manager to enable task types in project settings.</p>
                    </div>
                    @endif
                </div>

                <!-- Step 2: Task Details (changes based on task type) -->
                <div x-show="taskType" x-cloak class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">2</div>
                        <h2 class="text-lg font-semibold text-gray-900" x-text="getStep2Title()"></h2>
                    </div>
                    
                    <!-- General Task: Title Input -->
                    <div x-show="taskType === 'general'">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
                            <input 
                                type="text" 
                                id="title" 
                            name="title" 
                                value="{{ old('title', $task->title) }}"
                            x-model="title"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('title') border-red-300 bg-red-50 @enderror"
                                placeholder="Enter task title"
                            >
                            @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    <!-- Equipment ID: Equipment Selection -->
                    <div x-show="taskType === 'equipmentId'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="equipment_category" class="block text-sm font-medium text-gray-700 mb-2">
                                Equipment Category <span x-show="loadingEquipment">(Loading...)</span><span x-show="!loadingEquipment">(Optional Filter)</span>
                            </label>
                            <select
                                id="equipment_category"
                                x-model="equipmentCategory"
                                @change="equipmentId = ''; title = ''"
                                :disabled="loadingEquipment"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:opacity-50"
                            >
                                <option value="" x-text="loadingEquipment ? 'Loading...' : 'All Categories'"></option>
                                <template x-for="category in equipmentCategories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label for="equipment_item" class="block text-sm font-medium text-gray-700 mb-2">
                                Equipment Item *
                            </label>
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="showEquipmentDropdown = !showEquipmentDropdown"
                                    :disabled="loadingEquipment"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-left focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors flex items-center justify-between disabled:opacity-50"
                                    :class="{ 'border-red-300 bg-red-50': !equipmentId && attemptedSubmit }"
                                >
                                    <span :class="(() => {
                                        if (equipmentId) return 'text-gray-900';
                                        const allEquipment = [];
                                        equipmentCategories.forEach(category => {
                                            if (category.equipment) {
                                                allEquipment.push(...category.equipment);
                                            }
                                        });
                                        const matchingEquipment = allEquipment.find(e => title === (e.name + ' - ' + (e.equipment_id ? e.equipment_id : e.id)));
                                        return matchingEquipment ? 'text-gray-900' : 'text-gray-500';
                                    })()">
                                        <span x-show="loadingEquipment">Loading equipment...</span>
                                        <template x-if="!loadingEquipment">
                                            <span x-text="(() => {
                                                const allEquipment = [];
                                                equipmentCategories.forEach(category => {
                                                    if (category.equipment) {
                                                        allEquipment.push(...category.equipment);
                                                    }
                                                });
                                                // First try to find by equipmentId
                                                if (equipmentId) {
                                                    const equipment = allEquipment.find(e => String(e.id) === String(equipmentId));
                                                    if (equipment) {
                                                        return equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id);
                                                    }
                                                }
                                                // Then try to find by title match
                                                const matchingEquipment = allEquipment.find(e => title === (e.name + ' - ' + (e.equipment_id ? e.equipment_id : e.id)));
                                                if (matchingEquipment) {
                                                    return matchingEquipment.name + ' - ' + (matchingEquipment.equipment_id ? matchingEquipment.equipment_id : matchingEquipment.id);
                                                }
                                                return 'Select equipment';
                                            })()"></span>
                                        </template>
                                    </span>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </button>
                                
                                <div x-show="showEquipmentDropdown" 
                                     @click.away="showEquipmentDropdown = false"
                                     x-cloak
                                     class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="equipment in getFilteredEquipment()" :key="equipment.id">
                                        <button
                                            type="button"
                                            @click="selectEquipment(equipment)"
                                            class="w-full px-4 py-3 text-left transition-colors flex items-center justify-between"
                                            :class="String(equipmentId) === String(equipment.id) || title === (equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id)).trim() ? 'bg-blue-50 border-l-4 border-blue-500' : 'hover:bg-gray-50'"
                                        >
                                            <span class="text-gray-900" x-text="equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id)"></span>
                                            <div class="flex items-center space-x-2">
                                                <span x-show="String(equipmentId) === String(equipment.id) || title === (equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id)).trim()" class="text-blue-600 font-semibold text-sm">✓ Selected</span>
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-semibold"
                                                    :class="{
                                                        'bg-green-100 text-green-800': (equipment.current_status || '').toLowerCase() === 'available',
                                                        'bg-blue-100 text-blue-800': (equipment.current_status || '').toLowerCase() === 'rented',
                                                        'bg-yellow-100 text-yellow-800': (equipment.current_status || '').toLowerCase() === 'maintenance',
                                                        'bg-red-100 text-red-800': (equipment.current_status || '').toLowerCase() === 'damaged',
                                                        'bg-gray-100 text-gray-800': !['available','rented','maintenance','damaged'].includes((equipment.current_status || '').toLowerCase()),
                                                    }"
                                                    x-text="(() => { const s = (equipment.current_status || 'unknown').toString(); return s.charAt(0).toUpperCase() + s.slice(1); })()"
                                                ></span>
                                            </div>
                                        </button>
                                    </template>
                                    <div x-show="getFilteredEquipment().length === 0" class="px-4 py-3 text-center text-gray-500">
                                        No equipment available
                                    </div>
                                </div>
                                
                                <input type="hidden" name="equipment_id" x-model="equipmentId">
                                <input type="hidden" name="title" x-model="title">
                            </div>
                        </div>
                    </div>

                    <!-- Customer Name: Customer Search -->
                    <div x-show="taskType === 'customerName'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Customer *
                            </label>
                            <div class="relative" @click.away="showCustomerDropdown = false">
                                <div class="relative">
                                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                    <input
                                        type="text"
                                        x-model="customerSearch"
                                        @input="showCustomerDropdown = true; customerId = ''"
                                        @focus="showCustomerDropdown = true"
                                        :disabled="loadingCustomers"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                        :class="{ 'border-red-300 bg-red-50': !customerId && attemptedSubmit }"
                                        :placeholder="loadingCustomers ? 'Loading customers...' : 'Search customers...'"
                                    />
                                </div>
                                
                                <div x-show="showCustomerDropdown && getFilteredCustomers().length > 0 && !loadingCustomers" 
                                     x-cloak
                                     class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="customer in getFilteredCustomers()" :key="customer.id">
                                        <button
                                            type="button"
                                            @click="selectCustomer(customer)"
                                            class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors"
                                        >
                                            <div class="font-medium text-gray-900" x-text="customer.name"></div>
                                            <div class="text-sm text-gray-500">
                                                <span x-text="customer.email"></span> • <span x-text="formatPhone(customer.phone)"></span>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                                
                                <input type="hidden" name="customer_id" x-model="customerId">
                                <input type="hidden" name="title" x-model="title">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Task Information -->
                <div x-show="canShowStep3()" x-cloak class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <h2 class="text-lg font-semibold text-gray-900">Task Information</h2>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Description -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                                <div class="flex items-center space-x-2">
                                    <select
                                        x-model="selectedTemplateId"
                                        @change="handleTemplateSelect()"
                                        :disabled="loadingTemplates || availableTemplates.length === 0"
                                        class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <option value="">
                                            <span x-text="loadingTemplates ? 'Loading...' : (availableTemplates.length === 0 ? 'No templates available' : 'Choose Default Description')"></span>
                                        </option>
                                        <template x-for="template in availableTemplates.sort((a, b) => a.name.localeCompare(b.name))" :key="template.id">
                                            <option :value="template.id" x-text="template.name"></option>
                                        </template>
                                    </select>
                                    <button
                                        type="button"
                                        @click="handleSaveAsTemplate()"
                                        class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors flex items-center space-x-1"
                                    >
                                        <i class="fas fa-plus w-3 h-3"></i>
                                        <span>Save as Template</span>
                                    </button>
                                </div>
                            </div>
                            <div id="description-editor-container"></div>
                            <textarea 
                                id="description" 
                                name="description" 
                                x-model="description"
                                required
                                rows="4"
                                style="display: none;"
                            >{{ old('description', $task->description) }}</textarea>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p x-show="descriptionError" class="mt-2 text-sm text-red-600" x-text="descriptionError"></p>
                        </div>

                        <!-- Priority, Status, Assigned To -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select 
                                    id="priority"
                                    name="priority"
                                    x-model="priority"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <div class="mt-1 px-2 py-1 rounded-full text-xs inline-block border" :class="getPriorityColor(priority)">
                                    <span x-text="priority.charAt(0).toUpperCase() + priority.slice(1)"></span> Priority
                                </div>
                            </div>

                            <div>
                                <label for="task_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                @php
                                    $projectSettings = $task->project->settings ?? [];
                                    $requireApproval = $projectSettings['requireApproval'] ?? false;
                                    $canApprove = !$requireApproval || (auth()->id() === $task->project->project_manager_id);
                                @endphp
                                <select 
                                    name="task_status" 
                                    id="task_status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                >
                                    <option value="pending" {{ old('task_status', $task->task_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('task_status', $task->task_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed_pending_review" {{ old('task_status', $task->task_status) == 'completed_pending_review' ? 'selected' : '' }}>Review</option>
                                    @if($canApprove)
                                    <option value="approved" {{ old('task_status', $task->task_status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    @endif
                                    <option value="unapproved" {{ old('task_status', $task->task_status) == 'unapproved' ? 'selected' : '' }}>Unapproved</option>
                                </select>
                            </div>

                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
                                <select 
                                    id="assigned_to"
                                    name="assigned_to"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                >
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}@if($user->roles->count() > 0) ({{ ucfirst($user->roles->first()->name) }})@endif
                                    </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Tasks can be assigned later if needed</p>
                            </div>
                        </div>

                        <!-- Dates, Task List, Hours -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input 
                                    type="date" 
                                    id="start_date"
                                    name="start_date"
                                    value="{{ old('start_date', $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                >
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                                <input 
                                    type="date" 
                                    id="due_date"
                                    name="due_date"
                                    value="{{ old('due_date', $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                >
                        </div>

                            <div>
                                <label for="task_list_id" class="block text-sm font-medium text-gray-700 mb-2">Task List *</label>
                                <select 
                                    id="task_list_id" 
                                    name="task_list_id"
                                    x-model="taskListId"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm @error('task_list_id') border-red-300 bg-red-50 @enderror"
                                >
                                    <option value="">Select task list</option>
                                    @foreach($project->taskLists as $list)
                                    <option value="{{ $list->id }}" {{ (old('task_list_id', $task->task_list_id) == $list->id) ? 'selected' : '' }} data-color="{{ $list->color }}" data-name="{{ $list->name }}" data-description="{{ $list->description }}">
                                        {{ $list->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('task_list_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                
                                <!-- Show selected task list preview -->
                                <template x-if="taskListId && getSelectedTaskList()">
                                    <div class="mt-2 p-2 bg-gray-50 rounded border">
                                        <div class="text-xs px-2 py-1 rounded" :class="getSelectedTaskList()?.color">
                                            <strong x-text="getSelectedTaskList()?.name"></strong>
                                            <div class="text-gray-600 mt-1" x-show="getSelectedTaskList()?.description" x-text="getSelectedTaskList()?.description"></div>
                            </div>
                        </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: File Attachments (Optional) -->
                <div x-show="canShowStep3()" x-cloak class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">4</div>
                        <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
                    </div>
                    <p class="text-gray-600 mb-4">Add photos, videos, or PDF documents to support this task.</p>

                    <!-- Existing Attachments -->
                    @if($task->attachments->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-900 mb-3">
                            Existing Attachments ({{ $task->attachments->count() }})
                        </h3>
                        <div class="space-y-2">
                            @foreach($task->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0 
                                            @if(str_starts_with($attachment->mime_type, 'image/')) bg-green-100 text-green-600
                                            @elseif(str_starts_with($attachment->mime_type, 'video/')) bg-purple-100 text-purple-600
                                            @elseif($attachment->mime_type === 'application/pdf') bg-red-100 text-red-600
                                            @else bg-gray-100 text-gray-600
                                            @endif">
                                            @if(str_starts_with($attachment->mime_type, 'image/')) 🖼️
                                            @elseif(str_starts_with($attachment->mime_type, 'video/')) 🎥
                                            @elseif($attachment->mime_type === 'application/pdf') 📄
                                            @else 📎
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->original_filename }}</p>
                                            <div class="flex items-center space-x-3 text-xs text-gray-500">
                                                <span>{{ $attachment->size > 1048576 ? number_format($attachment->size / 1048576, 2) . ' MB' : number_format($attachment->size / 1024, 1) . ' KB' }}</span>
                                            @if($attachment->uploader)
                                                    <span>• Uploaded by {{ $attachment->uploader->first_name }} {{ $attachment->uploader->last_name }}</span>
                                            @endif
                                                <span>• {{ $attachment->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    </div>
                                    <a href="{{ route('attachments.download', $attachment->id) }}" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors flex-shrink-0">
                                            <i class="fas fa-download mr-2"></i>Download
                                        </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="space-y-4">
                        <!-- Upload Buttons -->
                        <div class="flex flex-wrap gap-3">
                            <button
                                type="button"
                                @click="$refs.fileInput.click()"
                                class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                <i class="fas fa-upload w-4 h-4"></i>
                                <span>Upload Files</span>
                            </button>
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <div class="flex items-center space-x-1">
                                    <span class="text-lg">🖼️</span>
                                    <span>Photos</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="text-lg">🎥</span>
                                    <span>Videos</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="text-lg">📄</span>
                                    <span>PDFs</span>
                                </div>
                            </div>
                        </div>

                        <input
                            x-ref="fileInput"
                            type="file"
                            name="attachments[]"
                            multiple
                            accept="image/*,video/*,.pdf"
                            @change="handleFileUpload($event)"
                            class="hidden"
                        />
                        
                        <p class="text-sm text-gray-500">
                            <strong>Supported formats:</strong> JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM, PDF<br />
                            <strong>Maximum size:</strong> 100MB per file • <strong>Multiple files:</strong> Supported
                        </p>

                        <!-- File List -->
                        <template x-if="attachments.length > 0">
                            <div class="space-y-3">
                                <h3 class="text-sm font-medium text-gray-900">
                                    New Attachments (<span x-text="attachments.length"></span>)
                                </h3>
                                <div class="space-y-2">
                                    <template x-for="(file, index) in attachments" :key="index">
                                        <div class="p-3 bg-gray-50 rounded-lg border" :class="file.error ? 'border-red-300 bg-red-50' : 'border-gray-200'">
                                            <div class="flex items-center justify-between mb-2">
                                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0" :class="getFileTypeColor(file)">
                                                        <span x-html="getFileIcon(file)"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                                                            <span x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                                                            <span x-text="getFileType(file)"></span>
                                                            <span x-show="file.uploaded" class="text-green-600 font-medium">✓ Uploaded</span>
                                                            <span x-show="file.error" class="text-red-600 font-medium">✗ Failed</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="removeAttachment(index)"
                                                    class="p-1 text-gray-400 hover:text-red-500 transition-colors rounded flex-shrink-0"
                                                    title="Remove file"
                                                    :disabled="file.uploading"
                                                >
                                                    <i class="fas fa-times w-4 h-4"></i>
                                                </button>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div x-show="file.uploading" class="mt-2">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-xs text-gray-600">Uploading...</span>
                                                    <span class="text-xs font-semibold text-blue-600" x-text="file.progress + '%'"></span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                    <div 
                                                        class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                                                        :style="'width: ' + file.progress + '%'"
                                                    ></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Step 5: Service Call - Only for Equipment ID tasks -->
                <div x-show="taskType === 'equipmentId' && canShowStep3()" x-cloak class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">5</div>
                        <h2 class="text-lg font-semibold text-gray-900">Service Call</h2>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Service Type
                            </label>
                            <div class="flex items-center space-x-6">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="service_call_type"
                                        value="none"
                                        x-model="serviceCallType"
                                        @change="clearServiceCallData()"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">None</span>
                                </label>

                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="service_call_type"
                                        value="customer_damage"
                                        x-model="serviceCallType"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">Customer Related Damage</span>
                                </label>

                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input
                                        type="radio"
                                        name="service_call_type"
                                        value="field_service"
                                        x-model="serviceCallType"
                                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700">Customer Related - Field Service Required</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="serviceCallType !== 'none'" class="space-y-4 pt-4 border-t">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Customer (Optional Filter)
                                        <span x-show="selectedServiceCallCustomer" class="ml-2 text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded">
                                            Filter Active
                                        </span>
                                    </label>
                                    <div class="relative" @click.away="showServiceCallCustomerDropdown = false">
                                        <div class="relative">
                                            <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                            <input
                                                type="text"
                                                x-model="serviceCallCustomerSearch"
                                                @input.debounce.300ms="handleServiceCallCustomerSearch()"
                                                @focus="showServiceCallCustomerDropdown = true; if (serviceCallCustomerSearch.length >= 2) handleServiceCallCustomerSearch()"
                                                class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                :class="{ 'border-blue-400 bg-blue-50': selectedServiceCallCustomer }"
                                                placeholder="Filter by customer (name, email)..."
                                            />
                                            <button
                                                type="button"
                                                x-show="serviceCallCustomerSearch.length > 0"
                                                @click="serviceCallCustomerSearch = ''; selectedServiceCallCustomer = null; serviceCallCustomers = []; showServiceCallCustomerDropdown = false; if (serviceCallOrders.length > 0) showServiceCallOrderDropdown = true;"
                                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>

                                        <div x-show="showServiceCallCustomerDropdown || loadingServiceCallCustomers" 
                                             x-cloak
                                             class="absolute z-[9999] w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                            <!-- Loading state -->
                                            <div x-show="loadingServiceCallCustomers" class="px-4 py-3 text-center text-gray-500">
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                Searching customers...
                                            </div>
                                            
                                            <!-- No results state -->
                                            <div x-show="!loadingServiceCallCustomers && serviceCallCustomerSearch.length >= 2 && serviceCallCustomers.length === 0" class="px-4 py-3 text-center text-gray-500">
                                                No customers found
                                            </div>
                                            
                                            <!-- Results -->
                                            <template x-for="(customer, index) in serviceCallCustomers" :key="'customer-' + customer.id + '-' + index">
                                                <button
                                                    type="button"
                                                    @click="handleServiceCallCustomerSelect(customer)"
                                                    class="w-full px-4 py-3 text-left hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-0"
                                                >
                                                    <div class="font-medium text-gray-900" x-text="customer.firstName + ' ' + customer.lastName"></div>
                                                    <div class="text-sm text-gray-500">
                                                        <span x-text="customer.email"></span>
                                                        <span x-show="customer.company" class="ml-2">• <span x-text="customer.company"></span></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Order ID *
                                    </label>
                                    <div class="relative" @click.away="showServiceCallOrderDropdown = false">
                                        <div class="relative">
                                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                            <input
                                                type="text"
                                                x-model="serviceCallOrderSearch"
                                                @input.debounce.300ms="handleServiceCallOrderSearch()"
                                                @focus="showServiceCallOrderDropdown = true; if (serviceCallOrderSearch.length >= 2) handleServiceCallOrderSearch()"
                                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                :placeholder="loadingServiceCallOrders ? 'Loading orders...' : 'Search Order ID...'"
                                            />
                                        </div>

                                        <div x-show="!selectedServiceCallOrder && ((selectedServiceCallCustomer && serviceCallOrders.length > 0) || loadingServiceCallOrders || (showServiceCallOrderDropdown && serviceCallOrderSearch.length >= 2))" 
                                             x-cloak
                                             class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                            <!-- Loading state -->
                                            <div x-show="loadingServiceCallOrders" class="px-4 py-3 text-center text-gray-500">
                                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                                Loading orders...
                                            </div>
                                            
                                            <!-- No results state -->
                                            <div x-show="!loadingServiceCallOrders && serviceCallOrderSearch.length >= 2 && getFilteredOrders().length === 0" class="px-4 py-3 text-center text-gray-500">
                                                No orders found
                                            </div>
                                            
                                            <!-- Results -->
                                            <template x-for="order in getFilteredOrders()" :key="order.id">
                                                <button
                                                    type="button"
                                                    @click="handleServiceCallOrderSelect(order)"
                                                    class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0"
                                                >
                                                    <div class="font-medium text-gray-900" x-text="order.orderNumber"></div>
                                                    <div class="text-sm text-gray-500">
                                                        <span x-text="order.customer?.name"></span> • <span x-text="order.product"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                    <input type="hidden" name="service_call_order_id" x-model="serviceCallOrderId">
                                </div>
                            </div>

                            <div x-show="selectedServiceCallOrder" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="font-medium text-gray-700">Order ID:</p>
                                        <p class="text-gray-900" x-text="selectedServiceCallOrder?.orderNumber"></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Email:</p>
                                        <p class="text-blue-600" x-text="selectedServiceCallOrder?.customer?.email || 'N/A'"></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Customer Name:</p>
                                        <p class="text-gray-900" x-text="selectedServiceCallOrder?.customer?.name"></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Phone:</p>
                                        <p class="text-gray-900" x-text="formatPhone(selectedServiceCallOrder?.customer?.phone)"></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Company:</p>
                                        <p class="text-gray-900" x-text="selectedServiceCallOrder?.customer?.company || 'N/A'"></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Billing Address:</p>
                                        <p class="text-gray-900" x-text="selectedServiceCallOrder?.billingAddress || 'N/A'"></p>
                                        <template x-if="selectedServiceCallOrder?.billingAddress && selectedServiceCallOrder?.billingAddress !== 'N/A'">
                                            <a :href="'https://maps.google.com/?q=' + encodeURIComponent(selectedServiceCallOrder?.billingAddress)"
                                               target="_blank" 
                                               class="text-blue-600 text-xs hover:underline inline-block mt-1">
                                                See on maps
                                            </a>
                                        </template>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Product:</p>
                                        <p class="text-gray-900" x-text="selectedServiceCallOrder?.product"></p>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-700">Delivery Info:</p>
                                        <template x-if="selectedServiceCallOrder?.shippingAddress === selectedServiceCallOrder?.billingAddress && selectedServiceCallOrder?.billingAddress && selectedServiceCallOrder?.billingAddress !== 'N/A'">
                                            <p class="text-gray-900 italic">Same as billing information</p>
                                        </template>
                                        <template x-if="selectedServiceCallOrder?.shippingAddress !== selectedServiceCallOrder?.billingAddress || !selectedServiceCallOrder?.billingAddress || selectedServiceCallOrder?.billingAddress === 'N/A'">
                                            <p class="text-gray-900" x-text="selectedServiceCallOrder?.shippingAddress || 'N/A'"></p>
                                        </template>
                                        <template x-if="selectedServiceCallOrder?.shippingAddress && selectedServiceCallOrder?.shippingAddress !== 'N/A' && selectedServiceCallOrder?.shippingAddress !== selectedServiceCallOrder?.billingAddress">
                                            <a :href="'https://maps.google.com/?q=' + encodeURIComponent(selectedServiceCallOrder?.shippingAddress)"
                                               target="_blank" 
                                               class="text-blue-600 text-xs hover:underline inline-block mt-1">
                                                See on maps
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <!-- Form Submission Progress Bar -->
                <div x-show="uploading" x-cloak class="mb-4">
                    <div class="bg-white rounded-lg border border-green-200 p-4 shadow-md">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-circle-notch fa-spin text-green-600"></i>
                                <span class="text-sm font-medium text-gray-700" x-text="uploadStatus"></span>
                            </div>
                            <span class="text-sm font-semibold text-green-600" x-text="uploadProgress + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div 
                                class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full transition-all duration-300 ease-out"
                                :style="'width: ' + uploadProgress + '%'"
                            ></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-600">
                            Please wait while we update your task...
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a 
                        href="{{ route('projects.show', $project->id) }}"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        :class="{ 'pointer-events-none opacity-50': uploading }"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit"
                        x-bind:disabled="!canShowStep3() || uploading"
                        @click.prevent="submitWithProgress"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas" :class="uploading ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="uploading ? 'Updating...' : 'Update Task'"></span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>

    <!-- Save Template Modal -->
    <div x-show="showSaveTemplateModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
         @click.self="showSaveTemplateModal = false">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Save as Default Description</h3>
                <button
                    @click="showSaveTemplateModal = false"
                    class="text-gray-400 hover:text-gray-600"
                >
                    <i class="fas fa-times w-5 h-5"></i>
                </button>
</div>

            <div class="space-y-4">
                <div>
                    <label for="templateName" class="block text-sm font-medium text-gray-700 mb-2">
                        Template Name *
                    </label>
                    <input
                        type="text"
                        id="templateName"
                        x-model="templateSaveData.name"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="e.g., 'Equipment Maintenance', 'Customer Follow-up'"
                    />
                </div>

                <div>
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            x-model="templateSaveData.is_universal"
                            @change="if (templateSaveData.is_universal) templateSaveData.task_types = []"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <span class="text-sm font-medium text-gray-700">
                            Universal Template (appears for all task types)
                        </span>
                    </label>
                </div>

                <div x-show="!templateSaveData.is_universal">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Apply to Task Types *
                    </label>
                    <div class="space-y-2">
                        @if($generalEnabled)
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                value="general"
                                x-model="templateSaveData.task_types"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-700">General</span>
                        </label>
                        @endif
                        @if($equipmentEnabled)
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                value="equipmentId"
                                x-model="templateSaveData.task_types"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-700">Equipment ID</span>
                        </label>
                        @endif
                        @if($customerEnabled)
                        <label class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                value="customerName"
                                x-model="templateSaveData.task_types"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                            />
                            <span class="text-sm text-gray-700">Customer</span>
                        </label>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <p class="text-xs font-medium text-gray-700 mb-1">Preview:</p>
                    <div class="text-sm text-gray-600" x-html="description || 'No description entered yet'"></div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <button
                        @click="showSaveTemplateModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveTemplate()"
                        :disabled="savingTemplate"
                        class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas" :class="savingTemplate ? 'fa-spinner fa-spin' : 'fa-save'"></i>
                        <span x-text="savingTemplate ? 'Saving...' : 'Save Template'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }

/* CKEditor Link Styling */
.ck-editor__editable a {
    color: #2563eb !important;
    text-decoration: underline !important;
}

.ck-editor__editable a:hover {
    color: #1d4ed8 !important;
}
</style>

@push('scripts')
<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
<script>
let descriptionEditor = null;

// Initialize CKEditor after Alpine.js is ready
document.addEventListener('alpine:init', () => {
    // Wait a bit for the DOM to be ready
    setTimeout(() => {
        ClassicEditor
            .create(document.querySelector('#description-editor-container'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'fontSize', 'fontColor', 'fontBackgroundColor', '|',
                        'bulletedList', 'numberedList', '|',
                        'alignment', '|',
                        'link', 'blockQuote', 'insertTable', '|',
                        'undo', 'redo'
                    ]
                },
                placeholder: 'Describe the task requirements and goals',
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
                descriptionEditor = editor;
                
                // Set initial content
                const initialContent = document.querySelector('#description').value;
                if (initialContent) {
                    editor.setData(initialContent);
                }
                
                // Sync CKEditor content to Alpine.js and hidden textarea
                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
                    if (alpineComponent && alpineComponent.description !== undefined) {
                        alpineComponent.description = data;
                    }
                    document.querySelector('#description').value = data;
                });
            })
            .catch(error => {
                console.error('Error initializing CKEditor:', error);
            });
    }, 100);
});
</script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('taskForm', () => ({
        taskType: {!! json_encode(old('task_type', $task->task_type)) !!},
        priority: {!! json_encode(old('priority', $task->priority ?? 'medium')) !!},
        taskListId: {!! json_encode(old('task_list_id', $task->task_list_id)) !!},
        title: {!! json_encode(old('title', $task->title)) !!},
        description: {!! json_encode(old('description', $task->description)) !!},
        descriptionError: '',
        showTooltip: null,
        attachments: [],
        
        // Templates
        allTemplates: [],
        availableTemplates: [],
        loadingTemplates: false,
        selectedTemplateId: '',
        showSaveTemplateModal: false,
        savingTemplate: false,
        templateSaveData: {
            name: '',
            is_universal: false,
            task_types: []
        },
        
        // Equipment
        equipmentCategories: @json($equipmentCategories ?? []),
        equipmentCategory: '',
        equipmentId: {!! json_encode(old('equipment_id', $task->equipment_id)) !!},
        selectedEquipmentName: '', // Store the equipment name to display
        showEquipmentDropdown: false,
        loadingEquipment: false,
        attemptedSubmit: false,
        
        // Customers
        allCustomers: @json($customers ?? []),
        customerSearch: {!! json_encode($task->customer_id ? ($customers->firstWhere('id', $task->customer_id)->name ?? '') : '') !!},
        customerId: {!! json_encode(old('customer_id', $task->customer_id)) !!},
        showCustomerDropdown: false,
        loadingCustomers: false,
        
        // Upload Progress
        uploading: false,
        uploadProgress: 0,
        uploadStatus: 'Preparing upload...',
        uploadedFiles: [],
        
        // Service Call
        serviceCallType: {!! json_encode(old('service_call_type', $task->serviceCall ? $task->serviceCall->service_type : 'none')) !!},
        serviceCallOrderSearch: {!! json_encode(old('service_call_order_id', $task->serviceCall ? $task->serviceCall->order_id : '')) !!},
        serviceCallCustomerSearch: '',
        serviceCallCustomers: [],
        selectedServiceCallCustomer: null,
        loadingServiceCallCustomers: false,
        showServiceCallCustomerDropdown: false,
        serviceCallOrders: [],
        selectedServiceCallOrder: null,
        loadingServiceCallOrders: false,
        showServiceCallOrderDropdown: false,
        serviceCallOrderId: {!! json_encode(old('service_call_order_id', $task->serviceCall ? $task->serviceCall->order_id : '')) !!},
        
        async init() {
            // Load templates on initialization
            this.loadTemplates();
            
            // Watch for taskType changes to filter templates
            this.$watch('taskType', (newType) => {
                this.filterTemplates(newType);
            });
            
            // If task has equipment_id, find and set the category and title
            @if($task->equipment_id)
            if (this.equipmentId && this.equipmentCategories && this.equipmentCategories.length > 0) {
                // Find which category contains this equipment
                for (const category of this.equipmentCategories) {
                    if (category.equipment) {
                        const equipment = category.equipment.find(e => String(e.id) === String(this.equipmentId));
                        if (equipment) {
                            this.equipmentCategory = category.id;
                            // Create the expected equipment label format
                            const equipmentLabel = equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id);
                            // Compare task title with equipment label
                            if (this.title && this.title.trim() === equipmentLabel.trim()) {
                                // Title matches equipment label format, set the equipment name to display
                                this.selectedEquipmentName = equipmentLabel;
                                this.title = equipmentLabel;
                            } else if (!this.title) {
                                // No title set, use equipment label
                                this.selectedEquipmentName = equipmentLabel;
                                this.title = equipmentLabel;
                            } else {
                                // Title doesn't match, don't show equipment name
                                this.selectedEquipmentName = '';
                            }
                            break;
                        }
                    }
                }
            }
            @endif
            
            // Load existing service call order if exists
            @if($task->serviceCall && $task->serviceCall->order_id)
            this.serviceCallOrderSearch = {!! json_encode($task->serviceCall->order_id) !!};
            await this.loadServiceCallOrder();
            @endif
        },
        
        async loadServiceCallOrder() {
            if (!this.serviceCallOrderSearch) return;
            
            this.loadingServiceCallOrders = true;
            try {
                const response = await fetch(`/api/orders/search?q=${encodeURIComponent(this.serviceCallOrderSearch)}`);
                if (response.ok) {
                    const data = await response.json();
                    this.serviceCallOrders = data.orders || [];
                    const order = this.serviceCallOrders.find(o => o.orderNumber === this.serviceCallOrderSearch);
                    if (order) {
                        this.handleServiceCallOrderSelect(order);
                    }
                }
            } catch (error) {
                console.error('Failed to load service call order:', error);
            } finally {
                this.loadingServiceCallOrders = false;
            }
        },
        
        async loadTemplates() {
            this.loadingTemplates = true;
            try {
                const response = await fetch('/api/task-templates');
                const data = await response.json();
                this.allTemplates = data.templates || [];
                this.filterTemplates(this.taskType);
            } catch (error) {
                console.error('Failed to load templates:', error);
                this.allTemplates = [];
                this.availableTemplates = [];
            } finally {
                this.loadingTemplates = false;
            }
        },
        
        getFilteredEquipment() {
            if (!this.equipmentCategories || this.equipmentCategories.length === 0) {
                return [];
            }
            
            let allEquipment = [];
            this.equipmentCategories.forEach(category => {
                if (category.equipment) {
                    allEquipment = allEquipment.concat(category.equipment);
                }
            });
            
            // Filter by category if selected
            if (this.equipmentCategory) {
                const selectedCategory = this.equipmentCategories.find(c => c.id == this.equipmentCategory);
                return selectedCategory ? selectedCategory.equipment : [];
            }
            
            return allEquipment;
        },
        
        selectEquipment(equipment) {
            this.equipmentId = equipment.id;
            const equipmentLabel = equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id);
            this.title = equipmentLabel;
            this.selectedEquipmentName = equipmentLabel;
            this.showEquipmentDropdown = false;
        },
        
        getSelectedEquipmentName() {
            if (!this.equipmentId) return '';
            const allEquipment = [];
            this.equipmentCategories.forEach(category => {
                if (category.equipment) {
                    allEquipment.push(...category.equipment);
                }
            });
            const equipment = allEquipment.find(e => String(e.id) === String(this.equipmentId));
            if (!equipment) return '';
            
            // Create the equipment label format
            const equipmentLabel = equipment.name + ' - ' + (equipment.equipment_id ? equipment.equipment_id : equipment.id);
            
            // Only show equipment label if title matches equipment label format
            if (this.title && this.title.trim() === equipmentLabel.trim()) {
                return equipmentLabel;
            }
            
            // If title doesn't match, return empty to show "Select equipment"
            return '';
        },
        
        formatPhone(phone) {
            if (!phone) return 'N/A';
            // Remove all non-digit characters
            const cleaned = ('' + phone).replace(/\D/g, '');
            // Format as US phone number (XXX) XXX-XXXX
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                return '(' + match[1] + ') ' + match[2] + '-' + match[3];
            }
            // Return original if it doesn't match expected format
            return phone;
        },
        
        getFilteredCustomers() {
            if (!this.allCustomers || this.allCustomers.length === 0) {
                return [];
            }
            
            if (!this.customerSearch) {
                return this.allCustomers;
            }
            
            const searchLower = this.customerSearch.toLowerCase();
            return this.allCustomers.filter(customer => {
                return customer.name.toLowerCase().includes(searchLower) ||
                       customer.email.toLowerCase().includes(searchLower) ||
                       customer.phone.toLowerCase().includes(searchLower);
            });
        },
        
        selectCustomer(customer) {
            this.customerId = customer.id;
            this.customerSearch = customer.name;
            this.title = `Task for ${customer.name}`;
            this.showCustomerDropdown = false;
        },
        
        filterTemplates(taskType) {
            if (!taskType) {
                this.availableTemplates = [];
                return;
            }
            
            this.availableTemplates = this.allTemplates.filter(template => {
                if (template.is_universal) return true;
                if (!template.task_types) return false;
                return template.task_types.includes(taskType);
            });
            
            // Reset selected template if it's not available for this task type
            if (this.selectedTemplateId) {
                const isAvailable = this.availableTemplates.some(t => t.id.toString() === this.selectedTemplateId);
                if (!isAvailable) {
                    this.selectedTemplateId = '';
                }
            }
        },
        
        handleTemplateSelect() {
            if (!this.selectedTemplateId) return;
            
            const template = this.availableTemplates.find(t => t.id.toString() === this.selectedTemplateId);
            if (template) {
                this.description = template.template_text;
                // Update CKEditor if it's initialized
                if (descriptionEditor) {
                    descriptionEditor.setData(template.template_text);
                }
            }
        },
        
        handleSaveAsTemplate() {
            // Get description from CKEditor if available, otherwise from Alpine.js
            const description = descriptionEditor ? descriptionEditor.getData() : this.description;
            if (!description || !description.trim()) {
                alert('Please enter a description first');
                return;
            }
            
            // Reset template save data
            this.templateSaveData = {
                name: '',
                is_universal: false,
                task_types: this.taskType ? [this.taskType] : []
            };
            
            this.showSaveTemplateModal = true;
        },
        
        async saveTemplate() {
            if (!this.templateSaveData.name || !this.templateSaveData.name.trim()) {
                alert('Please enter a template name');
                return;
            }
            
            if (!this.templateSaveData.is_universal && this.templateSaveData.task_types.length === 0) {
                alert('Please select at least one task type or mark as universal');
                return;
            }
            
            this.savingTemplate = true;
            
            try {
                // Get description from CKEditor if available, otherwise from Alpine.js
                const description = descriptionEditor ? descriptionEditor.getData() : this.description;
                
                const response = await fetch('/api/task-templates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: this.templateSaveData.name,
                        template_text: description,
                        is_universal: this.templateSaveData.is_universal,
                        task_types: this.templateSaveData.task_types
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to save template');
                }
                
                const data = await response.json();
                
                // Reload templates
                await this.loadTemplates();
                
                // Close modal
                this.showSaveTemplateModal = false;
                
                // Reset template save data
                this.templateSaveData = {
                    name: '',
                    is_universal: false,
                    task_types: []
                };
                
                alert('Template saved successfully!');
            } catch (error) {
                console.error('Failed to save template:', error);
                alert('Failed to save template. Please try again.');
            } finally {
                this.savingTemplate = false;
            }
        },
        
        getStep2Title() {
            const titles = {
                'general': 'General Task Details',
                'equipmentId': 'Equipment Selection',
                'customerName': 'Customer Selection'
            };
            return titles[this.taskType] || 'Task Details';
        },
        
        canShowStep3() {
            if (this.taskType === 'general' && this.title) return true;
            if (this.taskType === 'equipmentId' && this.title) return true;
            if (this.taskType === 'customerName' && this.title) return true;
            return false;
        },
        
        getPriorityColor(priority) {
            const colors = {
                'urgent': 'bg-red-100 text-red-800 border-red-200',
                'high': 'bg-orange-100 text-orange-800 border-orange-200',
                'medium': 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'low': 'bg-green-100 text-green-800 border-green-200'
            };
            return colors[priority] || colors['medium'];
        },
        
        getSelectedTaskList() {
            if (!this.taskListId) return null;
            const select = document.getElementById('task_list_id');
            const option = select?.querySelector(`option[value="${this.taskListId}"]`);
            if (!option) return null;
    return {
                id: this.taskListId,
                name: option.dataset.name,
                description: option.dataset.description,
                color: option.dataset.color
            };
        },
        
        async handleFileUpload(event) {
            const files = Array.from(event.target.files);
            const maxSize = 100 * 1024 * 1024; // 100MB
            
            for (const file of files) {
                if (file.size > maxSize) {
                    alert(`File ${file.name} is too large. Maximum size is 100MB.`);
                    continue;
                }
                
                // Add file to attachments with uploading status
                const fileObj = {
                    file: file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    uploading: true,
                    progress: 0,
                    uploaded: false,
                    error: false,
                    tempId: null
                };
                this.attachments.push(fileObj);
                const fileIndex = this.attachments.length - 1;
                
                // Upload file immediately
                this.uploadFile(file, fileIndex);
            }
            
            // Reset file input
            event.target.value = '';
            
            // Scroll to bottom to show the "Update Task" button
            setTimeout(() => {
                window.scrollTo({
                    top: document.body.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        },
        
        uploadFile(file, fileIndex) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    this.attachments[fileIndex].progress = percentComplete;
                }
            });
            
            // Handle completion
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        this.attachments[fileIndex].uploading = false;
                        this.attachments[fileIndex].uploaded = true;
                        this.attachments[fileIndex].tempId = response.tempId;
                        this.uploadedFiles.push(response.tempId);
                    } catch (error) {
                        console.error('Upload response error:', error);
                        this.attachments[fileIndex].uploading = false;
                        this.attachments[fileIndex].error = true;
                    }
                } else {
                    console.error('Upload failed:', xhr.status);
                    this.attachments[fileIndex].uploading = false;
                    this.attachments[fileIndex].error = true;
                }
            });
            
            // Handle errors
            xhr.addEventListener('error', () => {
                console.error('Upload error');
                this.attachments[fileIndex].uploading = false;
                this.attachments[fileIndex].error = true;
            });
            
            // Send request
            xhr.open('POST', '/api/upload-temp-file', true);
            xhr.send(formData);
        },
        
        removeAttachment(index) {
            const file = this.attachments[index];
            
            // Remove from uploadedFiles array if it was uploaded
            if (file.tempId) {
                const uploadedIndex = this.uploadedFiles.indexOf(file.tempId);
                if (uploadedIndex > -1) {
                    this.uploadedFiles.splice(uploadedIndex, 1);
                }
            }
            
            this.attachments.splice(index, 1);
        },
        
        getFileTypeColor(file) {
            if (file.type.startsWith('image/')) return 'bg-green-100 text-green-600';
            if (file.type.startsWith('video/')) return 'bg-purple-100 text-purple-600';
            if (file.type === 'application/pdf') return 'bg-red-100 text-red-600';
            return 'bg-gray-100 text-gray-600';
        },
        
        getFileIcon(file) {
            if (file.type.startsWith('image/')) return '🖼️';
            if (file.type.startsWith('video/')) return '🎥';
            if (file.type === 'application/pdf') return '📄';
            return '📎';
        },
        
        getFileType(file) {
            if (file.type.startsWith('image/')) return 'Photo';
            if (file.type.startsWith('video/')) return 'Video';
            if (file.type === 'application/pdf') return 'PDF';
            return 'File';
        },
        
        async handleServiceCallCustomerSearch() {
            const searchTerm = this.serviceCallCustomerSearch.trim();
            
            if (!searchTerm || searchTerm.length < 2) {
                this.serviceCallCustomers = [];
                this.selectedServiceCallCustomer = null;
                this.showServiceCallCustomerDropdown = false;
                
                if (this.serviceCallOrders.length > 0) {
                    this.showServiceCallOrderDropdown = true;
                }
                return;
            }
            
            this.loadingServiceCallCustomers = true;
            try {
                const response = await fetch(`/api/customers/search?q=${encodeURIComponent(searchTerm)}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                this.serviceCallCustomers = data.customers || [];
                this.showServiceCallCustomerDropdown = this.serviceCallCustomers.length > 0;
            } catch (error) {
                console.error('Failed to search customers:', error);
                this.serviceCallCustomers = [];
                this.showServiceCallCustomerDropdown = false;
            } finally {
                this.loadingServiceCallCustomers = false;
            }
        },
        
        async handleServiceCallCustomerSelect(customer) {
            this.selectedServiceCallCustomer = customer;
            this.serviceCallCustomerSearch = customer.firstName + ' ' + customer.lastName;
            this.showServiceCallCustomerDropdown = false;
            
            // Automatically load orders for the selected customer
            this.loadingServiceCallOrders = true;
            this.showServiceCallOrderDropdown = true;
            
            try {
                const response = await fetch(`/api/customers/${customer.id}/orders`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                this.serviceCallOrders = data.orders || [];
                
                if (this.serviceCallOrders.length === 0) {
                    this.showServiceCallOrderDropdown = false;
                }
            } catch (error) {
                console.error('Failed to load customer orders:', error);
                this.serviceCallOrders = [];
                this.showServiceCallOrderDropdown = false;
            } finally {
                this.loadingServiceCallOrders = false;
            }
        },
        
        async handleServiceCallOrderSearch() {
            const searchTerm = this.serviceCallOrderSearch.trim();
            
            if (!searchTerm || searchTerm.length < 2) {
                this.serviceCallOrders = [];
                this.selectedServiceCallOrder = null;
                this.serviceCallOrderId = '';
                this.showServiceCallOrderDropdown = false;
                return;
            }
            
            this.loadingServiceCallOrders = true;
            try {
                const url = `/api/orders/search?q=${encodeURIComponent(searchTerm)}`;
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                this.serviceCallOrders = data.orders || [];
                this.showServiceCallOrderDropdown = this.serviceCallOrders.length > 0;
            } catch (error) {
                console.error('Failed to search orders:', error);
                this.serviceCallOrders = [];
                this.showServiceCallOrderDropdown = false;
            } finally {
                this.loadingServiceCallOrders = false;
            }
        },
        
        getFilteredOrders() {
            if (!this.selectedServiceCallCustomer) {
                return this.serviceCallOrders;
            }
            
            const customerEmail = this.selectedServiceCallCustomer.email.toLowerCase();
            const customerFirstName = (this.selectedServiceCallCustomer.firstName || '').toLowerCase();
            const customerLastName = (this.selectedServiceCallCustomer.lastName || '').toLowerCase();
            
            const filtered = this.serviceCallOrders.filter(order => {
                const customer = order.customer;
                if (!customer) return false;
                
                const orderEmail = (customer.email || '').toLowerCase();
                const orderFirstName = (customer.firstName || '').toLowerCase();
                const orderLastName = (customer.lastName || '').toLowerCase();
                const orderName = (customer.name || '').toLowerCase();
                const fullCustomerName = (customerFirstName + ' ' + customerLastName).trim().toLowerCase();
                
                return orderEmail === customerEmail || 
                       (orderFirstName === customerFirstName && orderLastName === customerLastName) ||
                       orderName === fullCustomerName;
            });
            
            return filtered;
        },
        
        handleServiceCallOrderSelect(order) {
            this.selectedServiceCallOrder = order;
            this.serviceCallOrderId = order.orderNumber;
            this.serviceCallOrderSearch = order.orderNumber;
            this.showServiceCallOrderDropdown = false;
            
            // Scroll to bottom to show customer info and Update Task button
            setTimeout(() => {
                window.scrollTo({
                    top: document.body.scrollHeight,
                    behavior: 'smooth'
                });
            }, 100);
        },
        
        clearServiceCallData() {
            this.serviceCallOrderSearch = '';
            this.serviceCallCustomerSearch = '';
            this.serviceCallCustomers = [];
            this.selectedServiceCallCustomer = null;
            this.showServiceCallCustomerDropdown = false;
            this.serviceCallOrders = [];
            this.selectedServiceCallOrder = null;
            this.serviceCallOrderId = '';
            this.showServiceCallOrderDropdown = false;
        },
        
        submitWithProgress() {
            // Clear previous errors
            this.descriptionError = '';
            
            // Get description from CKEditor if available, otherwise from Alpine.js
            const description = descriptionEditor ? descriptionEditor.getData() : this.description;
            const descriptionText = description ? description.replace(/<[^>]*>/g, '').trim() : '';
            
            // Validate description
            if (!descriptionText) {
                this.descriptionError = 'Task description is required.';
                // Focus on description field
                if (descriptionEditor) {
                    descriptionEditor.focus();
                } else {
                    const descriptionField = document.getElementById('description');
                    if (descriptionField) {
                        descriptionField.focus();
                        descriptionField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                return;
            }
            
            // Update Alpine.js description and hidden textarea
            this.description = description;
            if (document.querySelector('#description')) {
                document.querySelector('#description').value = description;
            }
            
            // Check if any files are still uploading
            const stillUploading = this.attachments.some(file => file.uploading);
            if (stillUploading) {
                alert('Please wait for all files to finish uploading.');
                return;
            }
            
            // Sync CKEditor content to hidden textarea before form submission
            if (descriptionEditor) {
                const editorData = descriptionEditor.getData();
                this.description = editorData;
                document.querySelector('#description').value = editorData;
            }
            
            // Get form and create FormData
            const form = this.$refs.taskForm;
            const formData = new FormData(form);
            
            // Add uploaded file IDs to form data
            this.uploadedFiles.forEach((tempId, index) => {
                formData.append('uploaded_files[]', tempId);
            });
            
            // Show progress bar
            this.uploading = true;
            this.uploadProgress = 0;
            this.uploadStatus = 'Preparing to update task...';
            
            // Simulate initial progress for better UX
            setTimeout(() => {
                if (this.uploadProgress < 10) {
                    this.uploadProgress = 10;
                }
            }, 100);
            
            // Create XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    this.uploadProgress = percentComplete;
                    
                    if (percentComplete < 30) {
                        this.uploadStatus = 'Submitting task data...';
                    } else if (percentComplete < 70) {
                        this.uploadStatus = 'Processing attachments...';
                    } else if (percentComplete < 100) {
                        this.uploadStatus = 'Finalizing task update...';
                    } else {
                        this.uploadStatus = 'Processing... Almost done!';
                    }
                }
            });
            
            // Handle completion
            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    this.uploadProgress = 100;
                    this.uploadStatus = 'Task updated successfully! Redirecting...';
                    // Parse response to get redirect URL
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            // Fallback: redirect to projects page
                            window.location.href = '{{ route('projects.show', $project->id) }}';
                        }
                    } catch (e) {
                        // If not JSON, try to redirect to projects page
                        window.location.href = '{{ route('projects.show', $project->id) }}';
                    }
                } else {
                    this.uploadStatus = 'Failed to update task. Please try again.';
                    this.uploading = false;
                    alert('Failed to update task: ' + xhr.statusText);
                }
            });
            
            // Handle errors
            xhr.addEventListener('error', () => {
                this.uploadStatus = 'Error updating task. Please check your connection.';
                this.uploading = false;
                alert('Error updating task. Please check your connection and try again.');
            });
            
            // Send request
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.send(formData);
        }
    }));
});
</script>
@endpush
@endsection
