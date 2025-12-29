@extends('layouts.dashboard')

@section('title', 'Edit Project - Task Master K')

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
            <template x-for="step in [1,2,3]" :key="step">
                <div class="flex items-center">
                    <div 
                        :class="step === currentStep ? 'bg-blue-600 text-white' : step < currentStep ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600'"
                        class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium transition-colors"
                    >
                        <span x-text="step < currentStep ? '✓' : step"></span>
                    </div>
                    <div 
                        x-show="step < 3" 
                        :class="step < currentStep ? 'bg-green-600' : 'bg-gray-200'"
                        class="w-12 h-1 mx-2 transition-colors"
                    ></div>
                </div>
            </template>
        </div>

        <form action="{{ route('projects.update', $project->id) }}" method="POST" @submit="return validateStep()">
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
                            <input 
                                type="text" 
                                id="budget" 
                                name="budget"
                                value="{{ old('budget', $project->budget) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="$50,000"
                            >
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Team Members</label>
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

                <!-- Step 3: Review -->
                <div x-show="currentStep === 3" class="space-y-6">
                    <div class="text-center mb-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-2">Review & Update</h2>
                        <p class="text-gray-600">Review your changes before updating</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Project Updates</h3>
                        <p class="text-sm text-gray-600">Review all the information and click "Update Project" when ready.</p>
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
        
        nextStep() {
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },
        
        validateStep() {
            if (this.currentStep !== 3) {
                return false;
            }
            return true;
        }
    }
}
</script>
@endpush
@endsection

