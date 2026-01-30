@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Edit Project')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('projects.show', $project->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Project</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Project</h1>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-4xl mx-auto px-6 py-8" x-data="projectForm()">
        <!-- Step Indicator -->
        <div class="flex items-center justify-center space-x-4 mb-8">
            <template x-for="step in [1,2,3,4]" :key="step">
                <div class="flex items-center">
                    <div 
                        :class="step === currentStep ? 'bg-blue-600 text-white' : step < currentStep ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600'"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-colors"
                    >
                        <span x-text="step < currentStep ? '✓' : step"></span>
                    </div>
                    <div 
                        x-show="step < 4" 
                        :class="step < currentStep ? 'bg-green-600' : 'bg-gray-200'"
                        class="w-12 h-1 mx-2 transition-colors"
                    ></div>
                </div>
            </template>
        </div>

        <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data" @submit.prevent="if (validateStep()) { $event.target.submit(); }">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 1" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Project Details</h2>
                        <p class="text-gray-600">Update the basic information about your project</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', $project->name) }}" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-300 bg-red-50 @enderror"
                            placeholder="Enter project name"
                        >
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Project Description *</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            required
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('description') border-red-300 bg-red-50 @enderror"
                            placeholder="Describe the project goals, scope, and key deliverables"
                        >{{ old('description', $project->description) }}</textarea>
                        @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select 
                                id="priority" 
                                name="priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                <option value="low" {{ old('priority', $project->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority', $project->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority', $project->priority) == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority', $project->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select 
                                id="status" 
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="planning" {{ old('status', $project->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                                <option value="on-hold" {{ old('status', $project->status) == 'on-hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">Budget (Optional)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">$</span>
                            <input 
                                type="text" 
                                id="budget" 
                                name="budget"
                                value="{{ old('budget', $project->budget) }}"
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="50,000"
                                    inputmode="decimal"
                            >
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date (Optional)</label>
                            <input 
                                type="date" 
                                id="start_date" 
                                name="start_date"
                                value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date (Optional)</label>
                            <input 
                                type="date" 
                                id="due_date" 
                                name="due_date"
                                value="{{ old('due_date', $project->due_date ? $project->due_date->format('Y-m-d') : '') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="client" class="block text-sm font-medium text-gray-700 mb-2">Client/Stakeholder (Optional)</label>
                        <input 
                            type="text" 
                            id="client" 
                            name="client"
                            value="{{ old('client', $project->client) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Client or stakeholder name"
                        >
                    </div>
                </div>

                <!-- Step 2: Team Assignment -->
                <div x-show="currentStep === 2" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Team Assignment</h2>
                        <p class="text-gray-600">Update the project manager and team members</p>
                    </div>

                    <div>
                        <label for="project_manager_id" class="block text-sm font-medium text-gray-700 mb-2">Project Manager *</label>
                        <select 
                            id="project_manager_id" 
                            name="project_manager_id"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('project_manager_id') border-red-300 bg-red-50 @enderror"
                        >
                            <option value="">Select project manager</option>
                            @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" {{ old('project_manager_id', $project->project_manager_id) == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }} - {{ ucfirst($manager->role) }}
                            </option>
                            @endforeach
                        </select>
                        @error('project_manager_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{
                        allSelected: false,
                        toggleAll() {
                            const boxes = this.$root.querySelectorAll(`input[name='team_members[]']`);
                            boxes.forEach(b => { b.checked = this.allSelected; });
                        },
                        syncAllSelected() {
                            const boxes = Array.from(this.$root.querySelectorAll(`input[name='team_members[]']`));
                            this.allSelected = boxes.length > 0 && boxes.every(b => b.checked);
                        },
                        init() {
                            this.$nextTick(() => this.syncAllSelected());
                        }
                    }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Team Members</label>
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center space-x-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    x-model="allSelected"
                                    @change="toggleAll()"
                                >
                                <span>Select All</span>
                            </label>
                            <button type="button" class="text-sm text-blue-600 hover:text-blue-800" @click="allSelected=false; toggleAll()">
                                Clear
                            </button>
                        </div>
                        <div class="border border-gray-300 rounded-lg p-4 max-h-80 overflow-y-auto">
                            <div class="space-y-3">
                                @foreach($users as $user)
                                <div class="flex items-center space-x-3">
                                    <input 
                                        type="checkbox" 
                                        id="user-{{ $user->id }}" 
                                        name="team_members[]" 
                                        value="{{ $user->id }}"
                                        {{ $project->teamMembers->contains($user->id) ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        @change="syncAllSelected()"
                                    >
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ ucfirst($user->role) }} • {{ $user->email }}</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Project Planning -->
                <div x-show="currentStep === 3" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Project Planning</h2>
                        <p class="text-gray-600">Define objectives, deliverables, and project tags</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Project Objectives -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Objectives</label>
                            <div class="space-y-3">
                                <template x-for="(objective, index) in objectives" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="text"
                                            :name="'objectives[' + index + ']'"
                                            x-model="objectives[index]"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            :placeholder="'Objective ' + (index + 1)"
                                        >
                                        <button
                                            type="button"
                                            x-show="objectives.length > 1"
                                            @click="removeObjective(index)"
                                            class="p-2 text-red-500 hover:text-red-700 transition-colors"
                                        >
                                            <i class="fas fa-minus w-4 h-4"></i>
                                        </button>
                                    </div>
                                </template>
                                <button
                                    type="button"
                                    @click="addObjective()"
                                    class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors"
                                >
                                    <i class="fas fa-plus w-4 h-4"></i>
                                    <span>Add Objective</span>
                                </button>
                            </div>
                        </div>

                        <!-- Key Deliverables -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Key Deliverables</label>
                            <div class="space-y-3">
                                <template x-for="(deliverable, index) in deliverables" :key="index">
                                    <div class="flex items-center space-x-2">
                                        <input
                                            type="text"
                                            :name="'deliverables[' + index + ']'"
                                            x-model="deliverables[index]"
                                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            :placeholder="'Deliverable ' + (index + 1)"
                                        >
                                        <button
                                            type="button"
                                            x-show="deliverables.length > 1"
                                            @click="removeDeliverable(index)"
                                            class="p-2 text-red-500 hover:text-red-700 transition-colors"
                                        >
                                            <i class="fas fa-minus w-4 h-4"></i>
                                        </button>
                                    </div>
                                </template>
                                <button
                                    type="button"
                                    @click="addDeliverable()"
                                    class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 transition-colors"
                                >
                                    <i class="fas fa-plus w-4 h-4"></i>
                                    <span>Add Deliverable</span>
                                </button>
                            </div>
                        </div>

                        <!-- Project Tags -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Tags</label>
                            <div class="flex items-center space-x-2 mb-3">
                                <input
                                    type="text"
                                    x-model="tagInput"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="Add tags (e.g., web, mobile, design)"
                                >
                                <button
                                    type="button"
                                    @click="addTag()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Add
                                </button>
                            </div>
                            <!-- Hidden inputs for tags -->
                            <template x-for="(tag, index) in tags" :key="index">
                                <input type="hidden" :name="'tags[' + index + ']'" :value="tag">
                            </template>
                            <!-- Display tags -->
                            <div x-show="tags.length > 0" class="flex flex-wrap gap-2">
                                <template x-for="(tag, index) in tags" :key="index">
                                    <div class="flex items-center space-x-1 bg-blue-100 text-blue-800 px-3 py-1 rounded-full">
                                        <span class="text-sm" x-text="tag"></span>
                                        <button
                                            type="button"
                                            @click="removeTag(index)"
                                            class="text-blue-600 hover:text-blue-800"
                                        >
                                            <i class="fas fa-times w-3 h-3"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Settings & Attachments -->
                <div x-show="currentStep === 4" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Project Settings</h2>
                        <p class="text-gray-600">Configure project settings and add any initial documents</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Task Configuration -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Task Configuration</h3>
                            <div class="space-y-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">General Tasks</label>
                                        <p class="text-xs text-gray-500">Standard project tasks with manual entry</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[taskTypes][general]"
                                        value="1"
                                        {{ isset($project->settings['taskTypes']['general']) && $project->settings['taskTypes']['general'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Equipment ID Tasks</label>
                                        <p class="text-xs text-gray-500">Tasks linked to specific equipment items</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[taskTypes][equipmentId]"
                                        value="1"
                                        {{ isset($project->settings['taskTypes']['equipmentId']) && $project->settings['taskTypes']['equipmentId'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Customer Tasks</label>
                                        <p class="text-xs text-gray-500">Tasks associated with specific customers</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[taskTypes][customerName]"
                                        value="1"
                                        {{ isset($project->settings['taskTypes']['customerName']) && $project->settings['taskTypes']['customerName'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Project Features -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Project Features</h3>
                            <div class="space-y-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">File Uploads</label>
                                        <p class="text-xs text-gray-500">Allow team members to upload files to tasks</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[allowFileUploads]"
                                        value="1"
                                        {{ isset($project->settings['allowFileUploads']) && $project->settings['allowFileUploads'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Task Approval Required</label>
                                        <p class="text-xs text-gray-500">Require manager approval for task completion</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[requireApproval]"
                                        value="1"
                                        {{ isset($project->settings['requireApproval']) && $project->settings['requireApproval'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Time Tracking</label>
                                        <p class="text-xs text-gray-500">Enable time tracking for tasks</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[enableTimeTracking]"
                                        value="1"
                                        {{ isset($project->settings['enableTimeTracking']) && $project->settings['enableTimeTracking'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Public Project</label>
                                        <p class="text-xs text-gray-500">Make project visible to all organization members</p>
                                    </div>
                                    <input
                                        type="checkbox"
                                        name="settings[publicProject]"
                                        value="1"
                                        {{ isset($project->settings['publicProject']) && $project->settings['publicProject'] ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between pt-8 border-t border-gray-200 mt-8">
                    <div>
                        <button 
                            type="button" 
                            x-show="currentStep > 1"
                            @click="currentStep--"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Previous
                        </button>
                    </div>

                    <div class="flex items-center space-x-4">
                        <a 
                            href="{{ route('projects.show', $project->id) }}"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </a>
                        
                        <button 
                            type="button"
                            x-show="currentStep < 4"
                            @click="nextStep()"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Next
                        </button>

                        <button 
                            type="submit"
                            x-show="currentStep === 4"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-save"></i>
                            <span>Update Project</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function projectForm() {
    return {
        currentStep: 1,
        priority: '{{ $project->priority }}',
        objectives: @json($project->objectives ?? ['']),
        deliverables: @json($project->deliverables ?? ['']),
        tags: @json($project->tags ?? []),
        tagInput: '',
        attachments: [],
        
        init() {
            // Ensure at least one empty field for objectives and deliverables
            if (this.objectives.length === 0) {
                this.objectives = [''];
            }
            if (this.deliverables.length === 0) {
                this.deliverables = [''];
            }
        },
        
        nextStep() {
            if (this.currentStep < 4) {
                this.currentStep++;
            }
        },
        
        validateStep() {
            if (this.currentStep !== 4) {
                return false;
            }
            return true;
        },
        
        getPriorityColor() {
            const colors = {
                low: 'bg-gray-100 text-gray-700 border-gray-300',
                medium: 'bg-yellow-100 text-yellow-700 border-yellow-300',
                high: 'bg-orange-100 text-orange-700 border-orange-300',
                urgent: 'bg-red-100 text-red-700 border-red-300'
            };
            return colors[this.priority] || colors.medium;
        },
        
        // Objectives
        addObjective() {
            this.objectives.push('');
        },
        
        removeObjective(index) {
            if (this.objectives.length > 1) {
                this.objectives.splice(index, 1);
            }
        },
        
        // Deliverables
        addDeliverable() {
            this.deliverables.push('');
        },
        
        removeDeliverable(index) {
            if (this.deliverables.length > 1) {
                this.deliverables.splice(index, 1);
            }
        },
        
        // Tags
        addTag() {
            if (this.tagInput.trim() && !this.tags.includes(this.tagInput.trim())) {
                this.tags.push(this.tagInput.trim());
                this.tagInput = '';
            }
        },
        
        removeTag(index) {
            this.tags.splice(index, 1);
        },
        
        // File attachments
        handleFileUpload(event) {
            const files = Array.from(event.target.files);
            this.attachments = [...this.attachments, ...files];
        },
        
        removeAttachment(index) {
            this.attachments.splice(index, 1);
            // Reset file input
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        }
    }
}
</script>
@endpush
@endsection

