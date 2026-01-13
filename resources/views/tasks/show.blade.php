@extends('layouts.dashboard')

@section('title', $task->title . ' - Task Master K')

@section('content')
@php
    $userRoleNames = auth()->user()
        ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->all()
        : [];
    $isMasterAdmin = in_array('master admin', $userRoleNames) || in_array('master_admin', $userRoleNames);
    $isTeamMember = $task->project->created_by === auth()->id()
        || $task->project->project_manager_id === auth()->id()
        || $task->project->teamMembers->contains('id', auth()->id());
    $canEditTask = $isMasterAdmin || $isTeamMember;
@endphp
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('projects.show', $task->project_id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Project</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="flex items-center space-x-3">
                        <span class="text-lg">
                            @if($task->task_type === 'general')📝
                            @elseif($task->task_type === 'equipmentId')🔧
                            @elseif($task->task_type === 'customerName')👤
                            @elseif($task->task_type === 'feature')✨
                            @elseif($task->task_type === 'bug')🐛
                            @elseif($task->task_type === 'design')🎨
                            @else📝
                            @endif
                        </span>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $task->title }}</h1>
                        <span class="px-2 py-1 rounded-full text-xs font-medium border {{ 
                            $task->priority === 'urgent' ? 'bg-red-100 text-red-800 border-red-200' :
                            ($task->priority === 'high' ? 'bg-orange-100 text-orange-800 border-orange-200' :
                            ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-green-100 text-green-800 border-green-200'))
                        }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Status Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium border transition-colors {{ $task->taskList->color }} hover:shadow-sm">
                            <span>{{ $task->taskList->name }}</span>
                            <i class="fas fa-chevron-down w-4 h-4"></i>
                        </button>
                        
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-20 min-w-[140px]">
                            @foreach($task->project->taskLists as $list)
                            <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="inline w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="task_list_id" value="{{ $list->id }}">
                                <button type="submit" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 transition-colors {{ $task->task_list_id === $list->id ? 'bg-blue-50 text-blue-600' : 'text-gray-700' }}">
                                    <div class="flex items-center justify-between">
                                        <span>{{ $list->name }}</span>
                                        @if($task->task_list_id === $list->id)<span class="text-blue-600">✓</span>@endif
                                    </div>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    
                    @if($canEditTask)
                    <a href="{{ route('tasks.edit', $task->id) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Edit Task
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-6 py-8" x-data="taskChangesManager()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Description</h2>
                    <p class="text-gray-700 whitespace-pre-line">{{ $task->description }}</p>
                </div>

                <!-- Attachments -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
                        @if($task->attachments->count() > 0)
                            <span class="text-sm text-gray-500">{{ $task->attachments->count() }}</span>
                        @endif
                    </div>

                    @if($task->attachments->count() > 0)
                        <div class="space-y-3">
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
                        <p class="text-sm text-gray-500">No attachments.</p>
                    @endif
                </div>

                <!-- Task Status Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Status</h2>
                    <div class="flex items-center gap-3 overflow-x-auto">
                        @php
                            $statuses = [
                                ['value' => 'pending', 'label' => 'Pending', 'icon' => 'clock', 'activeColor' => 'bg-gray-500 text-white border-gray-500'],
                                ['value' => 'in_progress', 'label' => 'In Progress', 'icon' => 'spinner', 'activeColor' => 'bg-blue-600 text-white border-blue-600'],
                                ['value' => 'completed_pending_review', 'label' => 'Review', 'icon' => 'eye', 'activeColor' => 'bg-yellow-500 text-white border-yellow-500'],
                                ['value' => 'unapproved', 'label' => 'Unapproved', 'icon' => 'times-circle', 'activeColor' => 'bg-red-600 text-white border-red-600'],
                                ['value' => 'approved', 'label' => 'Approved', 'icon' => 'check-circle', 'activeColor' => 'bg-green-600 text-white border-green-600'],
                            ];

                            // Check if user can approve tasks for this project
                            $projectSettings = $task->project->settings ?? [];
                            $requireApproval = $projectSettings['requireApproval'] ?? false;
                            $canApprove = !$requireApproval || (auth()->id() === $task->project->project_manager_id);
                        @endphp
                        
                        @foreach($statuses as $status)
                            @if($status['value'] === 'approved' && !$canApprove)
                                @continue
                            @endif
                            <button 
                                type="button" 
                                @click="setStatus('{{ $status['value'] }}')" 
                                :class="{'{{ $status['activeColor'] }}': stagedStatus === '{{ $status['value'] }}', 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50': stagedStatus !== '{{ $status['value'] }}'}"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors border-2 whitespace-nowrap"
                            >
                                <i class="fas fa-{{ $status['icon'] }} mr-2"></i>
                                {{ $status['label'] }}
                                <span x-show="stagedStatus === '{{ $status['value'] }}' && stagedStatus !== '{{ $task->task_status }}'" class="ml-2 text-xs">(pending)</span>
                            </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Click any status to stage changes. Click "Save & Exit" to save all changes.</p>
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Activity & Comments ({{ $task->comments->count() }})
                        </h2>
                    </div>

                    <!-- Comments List -->
                    <div class="space-y-6 mb-8">
                        @forelse($task->comments as $comment)
                        <div class="flex space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-white">{{ strtoupper(substr($comment->author->name, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-900">{{ $comment->author->name }}</span>
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $comment->content }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div x-show="stagedComments.length === 0" class="text-center py-12 text-gray-500">
                            <i class="fas fa-comment text-5xl text-gray-300 mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">No comments yet</h4>
                            <p class="text-sm">Start the conversation by adding the first comment!</p>
                        </div>
                        @endforelse
                        
                        <!-- Staged Comments (Temporary) -->
                        <template x-for="(comment, index) in stagedComments" :key="index">
                            <div class="flex space-x-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-medium text-white">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</span>
                                                <span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 text-xs rounded-full font-medium">Pending</span>
                                            </div>
                                            <button @click="removeStagedComment(index)" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <p class="text-sm text-gray-700" x-text="comment"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Add Comment Form -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-white">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1">
                                <textarea
                                    x-model="newComment"
                                    placeholder="Add a comment..."
                                    class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                    rows="3"
                                ></textarea>
                                
                                <div class="flex items-center justify-between mt-4">
                                    <a 
                                        href="{{ route('projects.show', $task->project_id) }}" 
                                        class="flex items-center space-x-2 px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                                        <i class="fas fa-times"></i>
                                        <span>Cancel</span>
                                    </a>
                                    
                                    <div class="flex items-center space-x-3">
                                        <button
                                            type="button"
                                            @click="addComment"
                                            :disabled="!newComment.trim()"
                                            class="flex items-center space-x-2 px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i class="fas fa-comment"></i>
                                            <span>Add Comment</span>
                                        </button>
                                        
                                        <button
                                            type="button"
                                            @click="saveAllChanges"
                                            :disabled="!hasChanges"
                                            class="flex items-center space-x-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i class="fas fa-save"></i>
                                            <span>Save & Exit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Details</h2>
                    
                    <div class="space-y-4">
                        <!-- Assigned To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Assigned To</label>
                            @if($task->assignedUser)
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $task->assignedUser->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($task->assignedUser->role) }}</p>
                                </div>
                            </div>
                            @else
                            <p class="text-sm text-gray-500">Unassigned</p>
                            @endif
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Priority</label>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ 
                                $task->priority === 'urgent' ? 'bg-red-100 text-red-800 border-red-200' :
                                ($task->priority === 'high' ? 'bg-orange-100 text-orange-800 border-orange-200' :
                                ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-green-100 text-green-800 border-green-200'))
                            }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>

                        <!-- Dates -->
                        @if($task->start_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                            <p class="text-sm text-gray-900">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($task->start_date)->format('M j, Y') }}
                            </p>
                        </div>
                        @endif

                        @if($task->due_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Due Date</label>
                            <p class="text-sm {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'approved' ? 'text-red-600' : 'text-gray-900' }}">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}
                                @if(\Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'approved')
                                <span class="text-xs">(Overdue)</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        <!-- Estimated Hours -->
                        @if($task->estimated_hours)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estimated Hours</label>
                            <p class="text-sm text-gray-900">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                {{ $task->estimated_hours }} hours
                            </p>
                        </div>
                        @endif

                        <!-- Created By -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created By</label>
                            <p class="text-sm text-gray-900">{{ $task->creator->name }}</p>
                            <p class="text-xs text-gray-500">{{ $task->created_at->diffForHumans() }}</p>
                        </div>

                        <!-- Task Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Task Type</label>
                            @php
                                $taskTypeLabels = [
                                    'general' => 'General',
                                    'equipmentId' => 'Equipment ID',
                                    'customerName' => 'Customer Name',
                                    'feature' => 'Feature',
                                    'bug' => 'Bug',
                                    'design' => 'Design',
                                ];
                            @endphp
                            <p class="text-sm text-gray-900">{{ $taskTypeLabels[$task->task_type] ?? ucfirst($task->task_type) }}</p>
                        </div>

                        <!-- Project -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Project</label>
                            <a href="{{ route('projects.show', $task->project_id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                {{ $task->project->name }}
                            </a>
                        </div>

                        <!-- Task List -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Task List</label>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $task->taskList->color }}">
                                {{ $task->taskList->name }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($canEditTask)
                    <div class="mt-6 pt-6 border-t border-gray-200 space-y-2">
                        <a href="{{ route('tasks.edit', $task->id) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit Task
                        </a>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Task
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

@push('scripts')
<script>
function taskChangesManager() {
    return {
        stagedStatus: '{{ $task->task_status }}',
        originalStatus: '{{ $task->task_status }}',
        stagedComments: [],
        newComment: '',
        
        get hasChanges() {
            return this.stagedStatus !== this.originalStatus || this.stagedComments.length > 0;
        },
        
        setStatus(status) {
            this.stagedStatus = status;
        },
        
        addComment() {
            if (this.newComment.trim()) {
                this.stagedComments.push(this.newComment.trim());
                this.newComment = '';
            }
        },
        
        removeStagedComment(index) {
            this.stagedComments.splice(index, 1);
        },
        
        async saveAllChanges() {
            if (!this.hasChanges) return;
            
            try {
                // Show loading state
                const saveBtn = event.target;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Saving...</span>';
                
                // Save status change if different
                if (this.stagedStatus !== this.originalStatus) {
                    await fetch('{{ route('tasks.update-status', $task->id) }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            status: this.stagedStatus
                        })
                    });
                }
                
                // Save all staged comments
                for (const comment of this.stagedComments) {
                    await fetch('{{ route('comments.store', $task->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            content: comment
                        })
                    });
                }
                
                // Redirect to project page
                window.location.href = '{{ route('projects.show', $task->project_id) }}';
            } catch (error) {
                console.error('Error saving changes:', error);
                alert('Failed to save changes. Please try again.');
                // Re-enable button
                const saveBtn = event.target;
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> <span>Save & Exit</span>';
            }
        }
    }
}
</script>
@endpush
@endsection

