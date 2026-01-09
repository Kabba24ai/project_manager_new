@extends('layouts.dashboard')

@section('title', $project->name . ' - Task Master K')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Projects</span>
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
                <div class="flex items-center space-x-3">
                    @if($project->created_by === auth()->id() || $project->project_manager_id === auth()->id())
                    <a href="{{ route('projects.edit', $project->id) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2 transition-colors text-sm">
                        <i class="fas fa-edit"></i>
                        <span>Edit Project</span>
                    </a>
                    @endif
                    @php
                        $deleteAllowedRoles = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];
                        $userRoleNames = auth()->user()
                            ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->all()
                            : [];
                        $canDeleteProject = !empty(array_intersect($userRoleNames, array_map('strtolower', $deleteAllowedRoles)));
                    @endphp
                    @if($canDeleteProject)
                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center space-x-2 transition-colors text-sm">
                            <i class="fas fa-trash"></i>
                            <span>Delete Project</span>
                        </button>
                    </form>
                    @endif
                    <!-- <button class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors flex items-center space-x-2">
                        <i class="fas fa-users"></i>
                        <span>Team</span>
                    </button> -->
                    <a href="{{ route('task-templates.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors flex items-center space-x-2">
                        <i class="fas fa-copy"></i>
                        <span>Templates</span>
                    </a>
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
                                <span>Due: {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('M j') : 'No due date' }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span>{{ $project->taskLists->sum(function($list) { return $list->tasks->where('status', 'completed')->count(); }) }}/{{ $project->taskLists->sum(function($list) { return $list->tasks->count(); }) }} tasks completed</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                @php
                                    $totalTasks = $project->taskLists->sum(function($list) { return $list->tasks->count(); });
                                    $completedTasks = $project->taskLists->sum(function($list) { return $list->tasks->where('status', 'completed')->count(); });
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
                        @foreach($project->teamMembers->take(4) as $member)
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

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Task Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <div class="flex-1">
                    <label for="task_name_filter" class="block text-sm font-medium text-gray-700 mb-1">Task Name</label>
                    <input
                        id="task_name_filter"
                        type="text"
                        placeholder="Search by task title..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        oninput="filterProjectTasks()"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <div class="flex items-center gap-2">
                            <input id="task_start_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" onchange="filterProjectTasks()" />
                            <span class="text-gray-400 text-sm">to</span>
                            <input id="task_start_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" onchange="filterProjectTasks()" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <div class="flex items-center gap-2">
                            <input id="task_due_from" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" onchange="filterProjectTasks()" />
                            <span class="text-gray-400 text-sm">to</span>
                            <input id="task_due_to" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" onchange="filterProjectTasks()" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors" onclick="resetProjectTaskFilters()">
                        Reset
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Filters apply across all task lists on this project view.</p>
        </div>

        @if($project->taskLists->count() > 0)
        <!-- Task Lists with Tasks -->
        <div class="space-y-8">
            @foreach($project->taskLists->sortBy('order') as $taskList)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <!-- Task List Header -->
                <div class="px-6 py-4 rounded-t-lg border-b border-gray-200 {{ $taskList->color }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ $taskList->name }}</h2>
                            @if($taskList->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $taskList->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="bg-white px-3 py-1 rounded-full text-sm font-medium text-gray-600" data-tasklist-count="{{ $taskList->id }}">
                                {{ $taskList->tasks->count() }} {{ $taskList->tasks->count() === 1 ? 'task' : 'tasks' }}
                            </span>
                            <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}" class="flex items-center space-x-1 px-3 py-1 bg-white text-gray-700 rounded-lg hover:bg-green-50 transition-colors border border-gray-200 text-sm font-medium" title="Add task to {{ $taskList->name }}">
                                <i class="fas fa-plus w-4 h-4"></i>
                                <span>Add Task</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tasks -->
                <div class="divide-y divide-gray-100" data-tasklist-id="{{ $taskList->id }}">
                    @php
                        // Sort alphabetically by task title (A-Z), case-insensitive
                        $sortedTasks = $taskList->tasks->sortBy(function($task) {
                            return strtolower($task->title ?? '');
                        });
                    @endphp
                    
                    @if($sortedTasks->count() > 0)
                        @foreach($sortedTasks as $task)
                        <div
                            class="px-6 py-4 hover:bg-gray-50 transition-colors group project-task-row"
                            data-tasklist-id="{{ $taskList->id }}"
                            data-title="{{ strtolower($task->title) }}"
                            data-start-date="{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '' }}"
                            data-due-date="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}"
                        >
                            <div class="flex items-center justify-between">
                                <a href="{{ route('tasks.show', $task->id) }}" class="flex items-center space-x-4 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
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
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h3 class="text-base font-medium text-gray-900">{{ $task->title }}</h3>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium border flex-shrink-0 {{ 
                                                $task->priority === 'urgent' ? 'bg-red-100 text-red-800 border-red-200' :
                                                ($task->priority === 'high' ? 'bg-orange-100 text-orange-800 border-orange-200' :
                                                ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-green-100 text-green-800 border-green-200'))
                                            }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium border flex-shrink-0 {{ 
                                                $task->task_status === 'pending' ? 'bg-gray-500 text-white border-gray-600' :
                                                ($task->task_status === 'in_progress' ? 'bg-blue-100 text-blue-800 border-blue-200' :
                                                ($task->task_status === 'completed_pending_review' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' :
                                                ($task->task_status === 'approved' ? 'bg-green-100 text-green-800 border-green-200' :
                                                ($task->task_status === 'unapproved' ? 'bg-red-100 text-red-800 border-red-200' :
                                                ($task->task_status === 'deployed' ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-gray-100 text-gray-800 border-gray-200')))))
                                            }}">
                                                @if($task->task_status === 'completed_pending_review')
                                                    Review
                                                @else
                                                    {{ ucfirst(str_replace('_', ' ', $task->task_status)) }}
                                                @endif
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($task->description, 100) }}</p>
                                        
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            @if($task->assignedUser)
                                            <div class="flex items-center space-x-2">
                                                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->name, 0, 2)) }}</span>
                                                </div>
                                                <span>{{ $task->assignedUser->name }}</span>
                                            </div>
                                            @else
                                            <div class="flex items-center space-x-1 text-gray-500">
                                                <i class="fas fa-users w-4 h-4"></i>
                                                <span class="underline">Unassigned</span>
                                            </div>
                                            @endif
                                            
                                            @if($task->due_date)
                                            <div class="flex items-center space-x-1 {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'Done' ? 'text-red-600' : '' }}">
                                                <i class="fas fa-calendar w-3 h-3"></i>
                                                <span>Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M j') }}</span>
                                            </div>
                                            @endif
                                            
                                            @if($task->comments->count() > 0)
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-comment w-3 h-3"></i>
                                                <span>{{ $task->comments->count() }}</span>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </a>

                                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="View Task">
                                        <i class="fas fa-eye w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('tasks.edit', $task->id) }}" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="Edit Task">
                                        <i class="fas fa-edit w-4 h-4"></i>
                                    </a>
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="More Options">
                                            <i class="fas fa-ellipsis-v w-4 h-4"></i>
                                        </button>

                                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 top-8 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10 min-w-[160px]">
                                            <div class="px-3 py-1 text-xs text-gray-500 font-medium">Move to:</div>
                                            @foreach($project->taskLists as $targetList)
                                            <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="task_list_id" value="{{ $targetList->id }}">
                                                <button type="submit" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 {{ $task->task_list_id === $targetList->id ? 'text-blue-600 bg-blue-50' : 'text-gray-700' }}" {{ $task->task_list_id === $targetList->id ? 'disabled' : '' }}>
                                                    {{ $targetList->name }} @if($task->task_list_id === $targetList->id)✓@endif
                                                </button>
                                            </form>
                                            @endforeach
                                            
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                                    Delete Task
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                    <div class="px-6 py-12 text-center text-gray-500">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-plus w-6 h-6 text-gray-400 flex items-center justify-center"></i>
                        </div>
                        <p class="text-sm">No tasks in {{ $taskList->name }}</p>
                        <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium inline-block">
                            Add a task
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State - No Task Lists -->
        <div class="text-center py-16 bg-white rounded-lg border border-gray-200">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-list text-4xl text-blue-600"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">No Task Lists Yet</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">
                Get started by creating your first task list to organize your project tasks. 
                You can create lists like "To Do", "In Progress", "Review", or any custom workflow that fits your project.
            </p>
            <a href="{{ route('task-lists.create', $project->id) }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Create Your First Task List
            </a>
        </div>
        @endif
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
@push('scripts')
<script>
function parseISODate(value) {
    if (!value) return null;
    const d = new Date(value + 'T00:00:00');
    return isNaN(d.getTime()) ? null : d;
}

function inRange(dateValue, fromValue, toValue) {
    const d = parseISODate(dateValue);
    const from = parseISODate(fromValue);
    const to = parseISODate(toValue);

    if (!from && !to) return true;
    if (!d) return false;
    if (from && d < from) return false;
    if (to && d > to) return false;
    return true;
}

function updateTaskListCounts() {
    const rows = Array.from(document.querySelectorAll('.project-task-row'));
    const countsByList = {};
    rows.forEach(row => {
        const listId = row.dataset.tasklistId;
        if (!countsByList[listId]) countsByList[listId] = { visible: 0, total: 0 };
        countsByList[listId].total += 1;
        if (row.style.display !== 'none') countsByList[listId].visible += 1;
    });

    Object.keys(countsByList).forEach(listId => {
        const el = document.querySelector(`[data-tasklist-count="${listId}"]`);
        if (!el) return;
        const visible = countsByList[listId].visible;
        el.textContent = `${visible} ${visible === 1 ? 'task' : 'tasks'}`;
    });
}

function filterProjectTasks() {
    const q = (document.getElementById('task_name_filter')?.value || '').trim().toLowerCase();
    const startFrom = document.getElementById('task_start_from')?.value || '';
    const startTo = document.getElementById('task_start_to')?.value || '';
    const dueFrom = document.getElementById('task_due_from')?.value || '';
    const dueTo = document.getElementById('task_due_to')?.value || '';

    const rows = Array.from(document.querySelectorAll('.project-task-row'));
    rows.forEach(row => {
        const title = row.dataset.title || '';
        const startDate = row.dataset.startDate || '';
        const dueDate = row.dataset.dueDate || '';

        const matchesName = !q || title.includes(q);
        const matchesStart = inRange(startDate, startFrom, startTo);
        const matchesDue = inRange(dueDate, dueFrom, dueTo);

        row.style.display = (matchesName && matchesStart && matchesDue) ? '' : 'none';
    });

    updateTaskListCounts();
}

function resetProjectTaskFilters() {
    const ids = ['task_name_filter', 'task_start_from', 'task_start_to', 'task_due_from', 'task_due_to'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    filterProjectTasks();
}

document.addEventListener('DOMContentLoaded', function () {
    updateTaskListCounts();
});
</script>
@endpush
@endsection
