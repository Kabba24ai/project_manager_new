@extends('layouts.dashboard')

@section('title', 'Add New Task - Task Master K')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="taskForm()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('projects.show', $project->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left w-5 h-5"></i>
                    <span>Back to Project</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Add New Task</h1>
                <div class="h-6 w-px bg-gray-300"></div>
                <span class="text-gray-600">{{ $project->name }}</span>
                <template x-if="taskListId && getSelectedTaskList()">
                    <div class="flex items-center space-x-2">
                        <div class="h-6 w-px bg-gray-300"></div>
                        <span class="text-gray-500">to</span>
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
                You need to create at least one task list before you can add tasks. 
                Task lists help organize your work into categories like "To Do", "In Progress", etc.
            </p>
            <a href="{{ route('task-lists.create', $project->id) }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Create Task List First
            </a>
        </div>
        @else
        <!-- Task Form -->
        <form action="{{ route('tasks.store', $taskListId ?? $project->taskLists->first()->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="space-y-8">
                <!-- Step 1: Task Type Selection -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-6 h-6 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                        <h2 class="text-lg font-semibold text-gray-900">Select Task Type</h2>
                    </div>
                    <p class="text-gray-600 mb-6">Choose the type of task you want to create.</p>
                    
                    <input type="hidden" name="task_type" x-model="taskType">
                    
                    <div class="grid grid-cols-3 gap-4">
                        <!-- General Task -->
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
                        
                        <!-- Equipment ID Task -->
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
                        
                        <!-- Customer Name Task -->
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
                    </div>
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
                            value="{{ old('title') }}" 
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
                                    <span :class="equipmentId ? 'text-gray-900' : 'text-gray-500'">
                                        <span x-show="loadingEquipment">Loading equipment...</span>
                                        <span x-show="!loadingEquipment && equipmentId" x-text="getSelectedEquipmentName()"></span>
                                        <span x-show="!loadingEquipment && !equipmentId">Select equipment</span>
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
                                            class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-center justify-between"
                                        >
                                            <span class="text-gray-900" x-text="equipment.name"></span>
                                            <span 
                                                class="px-2 py-1 rounded-full text-xs"
                                                :class="equipment.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                x-text="equipment.available ? 'Available' : 'Rented'"
                                            ></span>
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
                                                <span x-text="customer.email"></span> • <span x-text="customer.phone"></span>
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
                            </div>
                            <textarea 
                                id="description" 
                                name="description" 
                                x-model="description"
                                required
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('description') border-red-300 bg-red-50 @enderror"
                                placeholder="Describe the task requirements and goals"
                            >{{ old('description') }}</textarea>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority, Assigned To, Sprint -->
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
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <div class="mt-1 px-2 py-1 rounded-full text-xs inline-block border" :class="getPriorityColor(priority)">
                                    <span x-text="priority.charAt(0).toUpperCase() + priority.slice(1)"></span> Priority
                                </div>
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
                                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                    </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Tasks can be assigned later if needed</p>
                            </div>

                            <div>
                                <label for="sprint" class="block text-sm font-medium text-gray-700 mb-2">Sprint (Coming Soon)</label>
                                <select 
                                    id="sprint" 
                                    name="sprint"
                                    disabled
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm opacity-50"
                                >
                                    <option value="">Sprint feature coming soon</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dates and Task List -->
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input 
                                    type="date" 
                                    id="start_date" 
                                    name="start_date"
                                    value="{{ old('start_date') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                >
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                                <input 
                                    type="date" 
                                    id="due_date" 
                                    name="due_date"
                                    value="{{ old('due_date') }}"
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
                                    <option value="{{ $list->id }}" {{ (old('task_list_id', $taskListId) == $list->id) ? 'selected' : '' }} data-color="{{ $list->color }}" data-name="{{ $list->name }}" data-description="{{ $list->description }}">
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
                        <h2 class="text-lg font-semibold text-gray-900">Attachments (Optional)</h2>
                    </div>
                    <p class="text-gray-600 mb-4">Add photos, videos, or PDF documents to support this task.</p>
                    
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
                            multiple
                            accept="image/*,video/*,.pdf"
                            @change="handleFileUpload($event)"
                            class="hidden"
                        />
                        
                        <p class="text-sm text-gray-500">
                            <strong>Supported formats:</strong> JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM, PDF<br />
                            <strong>Maximum size:</strong> 50MB per file • <strong>Multiple files:</strong> Supported
                        </p>

                        <!-- File List -->
                        <template x-if="attachments.length > 0">
                            <div class="space-y-3">
                                <h3 class="text-sm font-medium text-gray-900">
                                    Attached Files (<span x-text="attachments.length"></span>)
                                </h3>
                                <div class="space-y-2">
                                    <template x-for="(file, index) in attachments" :key="index">
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" :class="getFileTypeColor(file)">
                                                    <span x-html="getFileIcon(file)"></span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                                    <div class="flex items-center space-x-3 text-xs text-gray-500">
                                                        <span x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                                                        <span x-text="getFileType(file)"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeAttachment(index)"
                                                class="p-1 text-gray-400 hover:text-red-500 transition-colors rounded"
                                                title="Remove file"
                                            >
                                                <i class="fas fa-times w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a
                        href="{{ route('projects.show', $project->id) }}"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        x-bind:disabled="!canShowStep3()"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i class="fas fa-save"></i>
                        <span>Create Task</span>
                    </button>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

@push('scripts')
<script>
function taskForm() {
    return {
        taskType: '{{ old('task_type', '') }}',
        priority: '{{ old('priority', 'medium') }}',
        taskListId: '{{ old('task_list_id', $taskListId ?? '') }}',
        title: '{{ old('title', '') }}',
        description: '{{ old('description', '') }}',
        showTooltip: null,
        attachments: [],
        
        // Templates
        allTemplates: [],
        availableTemplates: [],
        loadingTemplates: false,
        selectedTemplateId: '',
        
        // Equipment
        equipmentCategories: @json($equipmentCategories ?? []),
        equipmentCategory: '',
        equipmentId: '',
        showEquipmentDropdown: false,
        loadingEquipment: false,
        attemptedSubmit: false,
        
        // Customers
        allCustomers: @json($customers ?? []),
        customerSearch: '',
        customerId: '',
        showCustomerDropdown: false,
        loadingCustomers: false,
        
        init() {
            // Load templates on initialization
            this.loadTemplates();
            
            // Watch for taskType changes to filter templates
            this.$watch('taskType', (newType) => {
                this.filterTemplates(newType);
            });
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
            this.title = equipment.name;
            this.showEquipmentDropdown = false;
        },
        
        getSelectedEquipmentName() {
            const allEquipment = [];
            this.equipmentCategories.forEach(category => {
                if (category.equipment) {
                    allEquipment.push(...category.equipment);
                }
            });
            const equipment = allEquipment.find(e => e.id == this.equipmentId);
            return equipment ? equipment.name : '';
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
        
        handleFileUpload(event) {
            const files = Array.from(event.target.files);
            const maxSize = 50 * 1024 * 1024; // 50MB
            
            files.forEach(file => {
                if (file.size <= maxSize) {
                    this.attachments.push(file);
                } else {
                    alert(`File ${file.name} is too large. Maximum size is 50MB.`);
                }
            });
        },
        
        removeAttachment(index) {
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
        }
    }
}
</script>
@endpush
@endsection
