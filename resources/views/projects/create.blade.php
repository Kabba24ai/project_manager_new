@extends('layouts.dashboard')

@section('title', 'Create Project - Task Master K')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Dashboard</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Create New Project</h1>
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

        <form action="{{ route('projects.store') }}" method="POST" @submit="return validateStep()">
            @csrf
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <!-- Step 1: Basic Information -->
                <div x-show="currentStep === 1" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Project Details</h2>
                        <p class="text-gray-600">Let's start with the basic information about your project</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name') }}" 
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
                        >{{ old('description') }}</textarea>
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
                                x-model="priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            <div class="mt-2 px-3 py-1 rounded-full text-xs inline-block border" :class="getPriorityColor()">
                                <span x-text="priority.charAt(0).toUpperCase() + priority.slice(1)"></span> Priority
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Initial Status</label>
                            <select 
                                id="status" 
                                name="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                <option value="active" selected>Active</option>
                                <option value="planning">Planning</option>
                                <option value="on-hold">On Hold</option>
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
                                    value="{{ old('budget') }}"
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
                                value="{{ old('start_date') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                        </div>

                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date (Optional)</label>
                            <input 
                                type="date" 
                                id="due_date" 
                                name="due_date"
                                value="{{ old('due_date') }}"
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
                            value="{{ old('client') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            placeholder="Client or stakeholder name"
                        >
                    </div>
                </div>

                <!-- Step 2: Team Assignment -->
                <div x-show="currentStep === 2" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Team Assignment</h2>
                        <p class="text-gray-600">Select the project manager and team members</p>
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
                            <option value="{{ $manager->id }}" {{ old('project_manager_id') == $manager->id ? 'selected' : '' }}>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Team Members *</label>
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

                <!-- Step 3: Project Summary -->
                <div x-show="currentStep === 3" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Review & Confirm</h2>
                        <p class="text-gray-600">Please review the project details before creating</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Summary</h3>
                        <p class="text-sm text-gray-600">Review all the information and click "Create Project" when ready.</p>
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
                            href="{{ route('dashboard') }}"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </a>
                        
                        <button 
                            type="button"
                            x-show="currentStep < 3"
                            @click="nextStep()"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Next
                        </button>

                        <button 
                            type="submit"
                            x-show="currentStep === 3"
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                        >
                            <i class="fas fa-save"></i>
                            <span>Create Project</span>
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
        priority: 'medium',
        
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },
        
        validateStep() {
            // Basic client-side validation
            if (this.currentStep !== 3) {
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
        }
    }
}
</script>
@endpush
@endsection

