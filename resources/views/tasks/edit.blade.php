@extends('layouts.dashboard')

@section('title', 'Edit Task - Task Master K')

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
                <h1 class="text-2xl font-bold text-gray-900">Edit Task</h1>
                <div class="h-6 w-px bg-gray-300"></div>
                <span class="text-gray-600">{{ $project->name }}</span>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-4xl mx-auto px-6 py-8">
        <form action="{{ route('tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Task Information</h2>
                    
                    <div class="space-y-4">
                        <!-- Task Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
                            <input 
                                type="text" 
                                name="title" 
                                id="title" 
                                value="{{ old('title', $task->title) }}"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('title') border-red-300 bg-red-50 @enderror"
                                placeholder="Enter task title"
                            >
                            @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Task Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea 
                                name="description" 
                                id="description" 
                                required
                                rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('description') border-red-300 bg-red-50 @enderror"
                                placeholder="Describe what needs to be done"
                            >{{ old('description', $task->description) }}</textarea>
                            @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Task List Selection -->
                        <div>
                            <label for="task_list_id" class="block text-sm font-medium text-gray-700 mb-2">Task List *</label>
                            <select 
                                name="task_list_id" 
                                id="task_list_id"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                            >
                                @foreach($project->taskLists as $list)
                                <option value="{{ $list->id }}" {{ old('task_list_id', $task->task_list_id) == $list->id ? 'selected' : '' }}>
                                    {{ $list->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Grid: Priority, Status, Assigned User -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                                <select 
                                    name="priority" 
                                    id="priority"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                    <option value="low" {{ old('priority', $task->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $task->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
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
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                    <option value="pending" {{ old('task_status', $task->task_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('task_status', $task->task_status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed_pending_review" {{ old('task_status', $task->task_status) == 'completed_pending_review' ? 'selected' : '' }}>Review</option>
                                    @if($canApprove)
                                    <option value="approved" {{ old('task_status', $task->task_status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    @endif
                                    <option value="unapproved" {{ old('task_status', $task->task_status) == 'unapproved' ? 'selected' : '' }}>Unapproved</option>
                                    <option value="deployed" {{ old('task_status', $task->task_status) == 'deployed' ? 'selected' : '' }}>Deployed</option>
                                </select>
                            </div>

                            <div>
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                                <select 
                                    name="assigned_to" 
                                    id="assigned_to"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Grid: Task Type, Start Date, Due Date -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="task_type" class="block text-sm font-medium text-gray-700 mb-2">Task Type</label>
                                <select 
                                    name="task_type" 
                                    id="task_type"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                                    <option value="general" {{ old('task_type', $task->task_type) == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="equipmentId" {{ old('task_type', $task->task_type) == 'equipmentId' ? 'selected' : '' }}>Equipment ID</option>
                                    <option value="customerName" {{ old('task_type', $task->task_type) == 'customerName' ? 'selected' : '' }}>Customer</option>
                                    <option value="feature" {{ old('task_type', $task->task_type) == 'feature' ? 'selected' : '' }}>Feature</option>
                                    <option value="bug" {{ old('task_type', $task->task_type) == 'bug' ? 'selected' : '' }}>Bug</option>
                                    <option value="design" {{ old('task_type', $task->task_type) == 'design' ? 'selected' : '' }}>Design</option>
                                </select>
                            </div>

                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input 
                                    type="date" 
                                    name="start_date" 
                                    id="start_date"
                                    value="{{ old('start_date', $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                                <input 
                                    type="date" 
                                    name="due_date" 
                                    id="due_date"
                                    value="{{ old('due_date', $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                >
                            </div>
                        </div>

                        <!-- Grid: Estimated Hours, Actual Hours -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-2">Estimated Hours</label>
                                <input 
                                    type="number" 
                                    name="estimated_hours" 
                                    id="estimated_hours"
                                    step="0.5"
                                    value="{{ old('estimated_hours', $task->estimated_hours) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="e.g., 8"
                                >
                            </div>

                            <div>
                                <label for="actual_hours" class="block text-sm font-medium text-gray-700 mb-2">Actual Hours</label>
                                <input 
                                    type="number" 
                                    name="actual_hours" 
                                    id="actual_hours"
                                    step="0.5"
                                    value="{{ old('actual_hours', $task->actual_hours) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="e.g., 10"
                                >
                            </div>
                        </div>

                        <!-- Feedback -->
                        <div>
                            <label for="feedback" class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                            <textarea 
                                name="feedback" 
                                id="feedback" 
                                rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                                placeholder="Add any feedback or notes"
                            >{{ old('feedback', $task->feedback) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Attachments -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
                        <span class="text-sm text-gray-500">{{ $task->attachments->count() }}</span>
                    </div>

                    @if($task->attachments->count() > 0)
                        <div class="space-y-3 mb-4">
                            @foreach($task->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">
                                            {{ $attachment->original_filename }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ number_format(($attachment->size ?? 0) / 1024, 1) }} KB
                                            @if($attachment->uploader)
                                                • Uploaded by {{ $attachment->uploader->name }}
                                            @endif
                                            • {{ $attachment->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <a href="{{ route('attachments.download', $attachment->id) }}" class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-download mr-2"></i>Download
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-4">No attachments.</p>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Add Attachments</label>
                        <input
                            type="file"
                            name="attachments[]"
                            multiple
                            accept="image/*,video/*,.pdf"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                        <p class="mt-2 text-xs text-gray-500">Supported: images, videos, PDF • Max 50MB each</p>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <a 
                        href="{{ route('projects.show', $project->id) }}"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </a>
                    
                    <button 
                        type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                    >
                        <i class="fas fa-save"></i>
                        <span>Update Task</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function taskForm() {
    return {
        // Empty Alpine.js component for potential future enhancements
    }
}
</script>
@endpush
@endsection

