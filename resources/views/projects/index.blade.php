@extends('layouts.dashboard')

@section('title', 'Dashboard - Task Master K')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <h1 class="text-xl font-semibold text-gray-900">Task Master K</h1>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-user text-gray-400"></i>
                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                    @if(!empty($authRoleName))
                        <span class="text-xs text-gray-500">({{ $authRoleName }})</span>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="inline ml-4">
                        @csrf
                        <button type="submit" class="px-3 py-1 text-sm text-red-600 hover:text-red-800 border border-red-300 rounded hover:bg-red-50 transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('task-templates.index') }}" class="flex items-center space-x-2 px-4 py-2 text-gray-600 hover:text-blue-600 border border-gray-300 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors">
                        <i class="fas fa-cog"></i>
                        <span>Manage Templates</span>
                    </a>
                    <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2 transition-colors">
                        <i class="fas fa-plus"></i>
                        <span>Add Project</span>
                    </a>
                </div>
            </div>
            <div class="team-mebers-bar">
                @include('projects.team-members-bar')
            </div>
            <!-- Projects Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Projects</h2>
                    <div class="flex bg-gray-100 rounded-lg p-1" x-data="{ activeTab: 'all' }">
                        <button
                            @click="activeTab = 'all'; filterProjects('all')"
                            :class="activeTab === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            All ({{ $projects->count() }})
                        </button>
                        <button
                            @click="activeTab = 'active'; filterProjects('active')"
                            :class="activeTab === 'active' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            Active ({{ $projects->where('status', 'active')->count() }})
                        </button>
                        <button
                            @click="activeTab = 'planning'; filterProjects('planning')"
                            :class="activeTab === 'planning' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            Planning ({{ $projects->where('status', 'planning')->count() }})
                        </button>
                        <button
                            @click="activeTab = 'on-hold'; filterProjects('on-hold')"
                            :class="activeTab === 'on-hold' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            On Hold ({{ $projects->where('status', 'on-hold')->count() }})
                        </button>
                        <button
                            @click="activeTab = 'completed'; filterProjects('completed')"
                            :class="activeTab === 'completed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            Completed ({{ $projects->where('status', 'completed')->count() }})
                        </button>
                    </div>
                </div>

                @if($projects->count() > 0)
                <!-- Projects Grid -->
                <div id="projects-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($projects as $project)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow project-card" data-status="{{ $project->status }}">
                        <div class="p-4 cursor-pointer" onclick="window.location.href='{{ route('projects.show', $project->id) }}'">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-base font-semibold text-gray-900">{{ $project->name }}</h3>
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $project->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $project->status }}
                                </span>
                            </div>
                            
                            <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $project->description }}</p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Progress</span>
                                    <span class="font-medium">{{ $project->completed_tasks ?? 0 }}/{{ $project->tasks_count ?? 0 }}</span>
                                </div>
                                
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $project->progress_percentage ?? 0 }}%"></div>
                                </div>
                                
                                <div class="text-right text-xs text-gray-500">
                                    {{ $project->progress_percentage ?? 0 }}% complete
                                </div>
                            </div>
                        </div>

                    </div>
                    @endforeach
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <i class="fas fa-plus text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Projects Yet</h3>
                    <p class="text-gray-500 mb-6">Get started by creating your first project</p>
                    <a href="{{ route('projects.create') }}" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-block">
                        Create Your First Project
                    </a>
                </div>
                @endif
            </div>

            <!-- Task Status View with Tabs -->
            @php
                // Get all tasks from all projects grouped by status
                $allTasks = collect();
                foreach($projects as $project) {
                    foreach($project->taskLists as $taskList) {
                        foreach($taskList->tasks as $task) {
                            $allTasks->push([
                                'task' => $task,
                                'project' => $project,
                                'taskList' => $taskList
                            ]);
                        }
                    }
                }

                $tasksByStatus = [
                    'pending' => $allTasks->where('task.task_status', 'pending'),
                    'in_progress' => $allTasks->where('task.task_status', 'in_progress'),
                    'review' => $allTasks->where('task.task_status', 'completed_pending_review'),
                    'unapproved' => $allTasks->whereIn('task.task_status', ['rejected', 'unapproved']),
                    'approved' => $allTasks->where('task.task_status', 'approved'),
                ];
            @endphp

            @if($allTasks->count() > 0)
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="{ 
                activeTaskTab: 'review', 
                activeProjectFilter: 'all',
                getTaskCount(status) {
                    const taskItems = document.querySelectorAll(`.task-item[data-task-status='${status}']`);
                    if (this.activeProjectFilter === 'all') return taskItems.length;
                    return Array.from(taskItems).filter(item => item.dataset.projectId === this.activeProjectFilter).length;
                }
            }">
                <!-- Project Filter -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Filter by Project:</h3>
                    </div>
                    <div class="flex bg-gray-100 rounded-lg p-1 flex-wrap gap-1">
                        <button
                            @click="activeProjectFilter = 'all'; filterTasksByProject('all')"
                            :class="activeProjectFilter === 'all' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            All Projects
                        </button>
                        @foreach($projects as $project)
                        <button
                            @click="activeProjectFilter = '{{ $project->id }}'; filterTasksByProject('{{ $project->id }}')"
                            :class="activeProjectFilter === '{{ $project->id }}' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors"
                        >
                            {{ $project->name }}
                        </button>
                        @endforeach
                    </div>
                </div>
                
                <!-- Task Status Tabs -->
                <div class="flex space-x-2 mb-6 border-b border-gray-200">
                    <button
                        @click="activeTaskTab = 'pending'"
                        :class="activeTaskTab === 'pending' ? 'bg-gray-100 text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium transition-colors"
                        data-status="pending"
                        x-text="'Pending (' + getTaskCount('pending') + ')'"
                    >
                    </button>
                    <button
                        @click="activeTaskTab = 'in_progress'"
                        :class="activeTaskTab === 'in_progress' ? 'bg-gray-100 text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium transition-colors"
                        data-status="in_progress"
                        x-text="'In Progress (' + getTaskCount('in_progress') + ')'"
                    >
                    </button>
                    <button
                        @click="activeTaskTab = 'review'"
                        :class="activeTaskTab === 'review' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium rounded transition-colors"
                        data-status="review"
                        x-text="'Review (' + getTaskCount('review') + ')'"
                    >
                    </button>
                    <button
                        @click="activeTaskTab = 'unapproved'"
                        :class="activeTaskTab === 'unapproved' ? 'bg-gray-100 text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium transition-colors"
                        data-status="unapproved"
                        x-text="'Unapproved (' + getTaskCount('unapproved') + ')'"
                    >
                    </button>
                    <button
                        @click="activeTaskTab = 'approved'"
                        :class="activeTaskTab === 'approved' ? 'bg-gray-100 text-gray-900 border-b-2 border-gray-900' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2 text-sm font-medium transition-colors"
                        data-status="approved"
                        x-text="'Approved (' + getTaskCount('approved') + ')'"
                    >
                    </button>
                </div>

                <!-- Task List for each tab -->
                @foreach(['pending', 'in_progress', 'review', 'unapproved', 'approved'] as $status)
                <div x-show="activeTaskTab === '{{ $status }}'" class="space-y-4">
                    @if($tasksByStatus[$status]->count() > 0)
                        @php
                            // Sort by project name (A-Z), then group by project
                            $tasksGroupedByProject = $tasksByStatus[$status]
                                ->sortBy(fn($i) => strtolower($i['project']->name ?? ''))
                                ->groupBy(fn($i) => $i['project']->id);
                            $priorityColors = [
                                'urgent' => 'bg-red-100 text-red-800',
                                'high' => 'bg-orange-100 text-orange-800',
                                'medium' => 'bg-yellow-100 text-yellow-800',
                                'low' => 'bg-green-100 text-green-800',
                            ];
                            $statusColors = [
                                'pending' => 'bg-gray-500 text-white',
                                'in_progress' => 'bg-blue-600 text-white',
                                'review' => 'bg-yellow-500 text-white',
                                'unapproved' => 'bg-red-600 text-white',
                                'approved' => 'bg-green-600 text-white',
                            ];
                        @endphp

                        @foreach($tasksGroupedByProject as $projectId => $projectTasks)
                        @php
                            $projectName = $projectTasks->first()['project']->name ?? 'Project';
                            // Sort tasks within each project (A-Z by task title)
                            $projectTasks = $projectTasks->sortBy(fn($i) => strtolower($i['task']->title ?? ''));
                        @endphp
                        <div class="border border-gray-200 rounded-lg overflow-hidden project-group" data-project-id="{{ $projectId }}">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-list text-gray-400"></i>
                                    <span class="text-sm font-semibold text-gray-900">{{ $projectName }}</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $projectTasks->count() }} {{ $projectTasks->count() === 1 ? 'task' : 'tasks' }}</span>
                            </div>

                            <div class="divide-y divide-gray-100">
                                @foreach($projectTasks as $item)
                                <div class="p-4 hover:bg-gray-50 transition-colors task-item" data-project-id="{{ $item['project']->id }}" data-task-status="{{ $status }}">
                                    <div class="flex justify-between items-start gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3 mb-2">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="text-sm font-semibold text-gray-700">Task:</span>
                                                        <span class="text-sm text-gray-900 truncate">{{ $item['task']->title }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-2 mt-1">
                                                        <span class="text-xs font-semibold text-gray-600">Task List:</span>
                                                        <span class="text-xs text-gray-700">{{ $item['taskList']->name }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2 flex-shrink-0">
                                                    @php
                                                        $p = $item['task']->priority ?? 'medium';
                                                        $priorityClass = $priorityColors[$p] ?? $priorityColors['medium'];
                                                        $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                                        $statusLabel = $status === 'review'
                                                            ? 'Review'
                                                            : ucfirst(str_replace('_', ' ', $item['task']->task_status ?? $status));
                                                    @endphp
                                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $priorityClass }}">
                                                        {{ ucfirst($p) }}
                                                    </span>
                                                    
                                                </div>
                                            </div>

                                    @php
                                        $lastComment = $item['task']->comments->sortByDesc('created_at')->first();
                                    @endphp
                                    @if($lastComment)
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-semibold text-gray-700">Last Comment:</span>
                                                <span class="text-xs text-gray-600">{{ $lastComment->user->name ?? 'Unknown' }}</span>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $lastComment->created_at->format('m/d/Y, h:i A') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-800 line-clamp-2">{{ $lastComment->content }}</p>
                                    </div>
                                    @endif
                                            <div class="mt-2 flex items-center space-x-2">
                                                @if($status === 'pending')
                                                <span class="text-xs text-gray-500">Created By: {{ $item['task']->creator->name ?? 'Unknown' }}</span>
                                                <span class="text-xs text-gray-500">Created: {{ $item['task']->created_at->format('m/d/Y, h:i A') }}</span>
                                                @else
                                                <span class="text-xs text-gray-500">Updated By: {{ $item['task']->creator->name ?? 'Unknown' }}</span>
                                                <span class="text-xs text-gray-500">Updated: {{ $item['task']->updated_at->format('m/d/Y, h:i A') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex space-x-2 ml-4">
                                            @if($status === 'review' || $status === 'approved' || $status === 'unapproved')
                                            @php
                                                $projectSettings = $item['project']->settings ?? [];
                                                $requireApproval = $projectSettings['requireApproval'] ?? false;
                                                $canApprove = !$requireApproval || (auth()->id() === $item['project']->project_manager_id);
                                            @endphp
                                            @if($status === 'review')
                                                @if($canApprove)
                                                <form action="{{ route('tasks.update-status', $item['task']->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-1">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Approve</span>
                                                    </button>
                                                </form>
                                                @endif
                                            <form action="{{ route('tasks.update-status', $item['task']->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="unapproved">
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-1">
                                                    <i class="fas fa-times-circle"></i>
                                                    <span>Unapprove</span>
                                                </button>
                                            </form>
                                            @elseif($status === 'approved')
                                            <form action="{{ route('tasks.update-status', $item['task']->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="unapproved">
                                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-1">
                                                    <i class="fas fa-times-circle"></i>
                                                    <span>Unapprove</span>
                                                </button>
                                            </form>
                                            @elseif($status === 'unapproved')
                                                @if($canApprove)
                                                <form action="{{ route('tasks.update-status', $item['task']->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-1">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span>Approve</span>
                                                    </button>
                                                </form>
                                                @endif
                                            @endif
                                            @endif
                                            <a href="{{ route('tasks.show', $item['task']->id) }}" class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-1">
                                                <i class="fas fa-eye"></i>
                                                <span>View Task</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-gray-500 py-8">No tasks in this status</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </main>
</div>

@push('scripts')
<script>
function filterProjects(status) {
    const projects = document.querySelectorAll('.project-card');
    projects.forEach(project => {
        if (status === 'all' || project.dataset.status === status) {
            project.style.display = '';
        } else {
            project.style.display = 'none';
        }
    });
}

function filterTasksByProject(projectId) {
    const taskItems = document.querySelectorAll('.task-item');
    taskItems.forEach(task => {
        if (projectId === 'all' || task.dataset.projectId === projectId) {
            task.style.display = '';
        } else {
            task.style.display = 'none';
        }
    });

    // Hide/show project group containers based on whether they have any visible tasks
    const groups = document.querySelectorAll('.project-group');
    groups.forEach(group => {
        const groupProjectId = group.dataset.projectId;
        if (projectId !== 'all' && groupProjectId !== projectId) {
            group.style.display = 'none';
            return;
        }
        const hasVisibleTask = Array.from(group.querySelectorAll('.task-item')).some(t => t.style.display !== 'none');
        group.style.display = hasVisibleTask ? '' : 'none';
    });
}

// Initialize - show all projects by default
document.addEventListener('DOMContentLoaded', function() {
    // Don't filter by default - show all projects
    // Show all tasks by default
    filterTasksByProject('all');
});
</script>
@endpush
@endsection

