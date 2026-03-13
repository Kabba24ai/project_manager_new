@extends('layouts.dashboard')

@section('title', 'Proj Mgr - ' . $sprint->name)

@section('content')
@php
    $taskCount = $sprint->tasks->count();
    $doneCount = $sprint->tasks->where('task_status', 'approved')->count();
    $progress  = $taskCount > 0 ? round(($doneCount / $taskCount) * 100) : 0;
    $statusColor = match($sprint->status) {
        'active'    => 'bg-green-100 text-green-800 border-green-200',
        'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
        default     => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    };
    $userRoleNames = auth()->user()
        ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->all()
        : [];
    $isMasterAdmin = in_array('master admin', $userRoleNames) || in_array('master_admin', $userRoleNames);
    $treeTaskTotal = $projects->sum(fn($p) => $p->taskLists->sum(fn($l) => $l->tasks->count()));
@endphp

<div class="min-h-screen bg-gray-50" x-data="sprintBoard()">

    <!-- ═══ HEADER ════════════════════════════════════════════════════════ -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <!-- Row 1 -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('sprints.index') }}"
                       class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Sprints</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $sprint->name }}</h1>
                        <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $statusColor }}">
                            {{ ucfirst($sprint->status) }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    @if($isMasterAdmin)
                    <div class="relative" x-data="{ openMenu: false }">
                        <button @click="openMenu = !openMenu" @click.away="openMenu = false"
                                class="flex items-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                            <i class="fas fa-ellipsis-v"></i>
                            <span>Actions</span>
                            <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': openMenu }"></i>
                        </button>
                        <div x-show="openMenu" x-cloak x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <a href="{{ route('sprints.edit', $sprint->id) }}"
                               class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-edit w-4"></i><span>Edit Sprint</span>
                            </a>
                            <form action="{{ route('sprints.destroy', $sprint->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this sprint? Tasks will be moved back to backlog.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash-alt w-4"></i><span>Delete Sprint</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Row 2: goal + stats -->
            @if($sprint->goal)
            <p class="text-sm text-gray-500 mb-2">{{ $sprint->goal }}</p>
            @endif

            <div class="flex items-center flex-wrap gap-x-6 gap-y-1">
                <span class="text-xs text-gray-500">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ $sprint->start_date->format('M d, Y') }} – {{ $sprint->end_date->format('M d, Y') }}
                </span>
                <span class="text-xs text-gray-500">
                    <i class="fas fa-tasks mr-1"></i>
                    <span id="visibleTaskCount">{{ $taskCount }}</span> {{ Str::plural('task', $taskCount) }}
                </span>
                <span class="text-xs text-gray-500">
                    <i class="fas fa-check-circle mr-1 text-green-500"></i>{{ $doneCount }} completed
                </span>
                @if($taskCount > 0)
                <div class="flex items-center space-x-2">
                    <div class="w-32 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-1.5 rounded-full {{ $sprint->status === 'completed' ? 'bg-blue-500' : 'bg-green-500' }}"
                             style="width: {{ $progress }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $progress }}%</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- ═══ END HEADER ════════════════════════════════════════════════════ -->

    <!-- ═══ CONTENT ══════════════════════════════════════════════════════ -->
    <div class="max-w-7xl mx-auto px-6 py-6">

        @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center space-x-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-green-700 text-sm">{{ session('success') }}</span>
        </div>
        @endif

        <!-- ── Controls bar ─────────────────────────────────────────── -->
        <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
            <!-- Left: filters -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Hide Completed -->
                <label class="flex items-center space-x-2 px-3 py-2 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors text-sm shadow-sm">
                    <input type="checkbox" id="hideCompleted"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded"
                           checked
                           onchange="filterSprintTasks()">
                    <span class="font-medium text-gray-700">Hide Completed</span>
                </label>

                <!-- Search -->
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input id="taskSearch" type="text" placeholder="Search tasks…"
                           class="pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44"
                           oninput="filterSprintTasks()">
                </div>

                <!-- Priority filter -->
                <select id="priorityFilter"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="filterSprintTasks()">
                    <option value="">All Priority</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <!-- Right: Grid / List view toggle + Add Task -->
            <div class="flex items-center gap-2">
                <button @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-slate-700 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-colors text-sm font-medium shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/>
                        <rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
                    </svg>
                    Grid View
                </button>
                <button @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-slate-700 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50'"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-colors text-sm font-medium shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/>
                        <line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/>
                        <line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>
                    </svg>
                    List View
                </button>

                <!-- Add Task button — to the right of List View -->
                <button @click="showPanel = !showPanel"
                        :class="showPanel ? 'bg-blue-700 ring-2 ring-blue-300' : 'bg-blue-600 hover:bg-blue-700'"
                        class="flex items-center space-x-2 px-4 py-2 text-white rounded-lg transition-colors text-sm font-medium">
                    <i class="fas fa-plus"></i>
                    <span>Add Task</span>
                    <i class="fas fa-sitemap text-xs ml-1 opacity-75"></i>
                    <i class="fas fa-chevron-down text-xs ml-0.5 transition-transform"
                       :class="{ 'rotate-180': showPanel }"></i>
                </button>
            </div>
        </div>

        <!-- ── Add Task Tree Panel (drops below controls bar) ──────────── -->
        <div x-show="showPanel" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-5 bg-white rounded-xl border border-blue-200 shadow-sm overflow-hidden">

            <!-- Panel header -->
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-blue-50">
                <div class="flex items-center gap-2">
                    <i class="fas fa-sitemap text-blue-500 text-sm"></i>
                    <span class="text-sm font-semibold text-gray-900">Select tasks to add to this sprint</span>
                    @if($treeTaskTotal > 0)
                    <span class="text-xs text-gray-400">({{ $treeTaskTotal }} available)</span>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <!-- Search inside tree -->
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="treeSearch" placeholder="Search tasks…"
                               class="pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-52">
                    </div>
                    <button @click="showPanel = false"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Tree body -->
            @if($treeTaskTotal === 0)
            <div class="py-10 text-center text-gray-400 text-sm">
                <i class="fas fa-check-double text-2xl block mb-2 text-green-400"></i>
                All tasks are already in this sprint
            </div>
            @else
            <div class="overflow-y-auto max-h-72 py-2 px-2">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-4">
                    @foreach($projects as $proj)
                    @php
                        $listsWithTasks = $proj->taskLists->filter(fn($l) => $l->tasks->isNotEmpty());
                        if ($listsWithTasks->isEmpty()) continue;
                    @endphp
                    <div x-data="{ openProj: false }" class="mb-1">
                        <!-- Project row -->
                        <button @click="openProj = !openProj"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-left">
                            <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform duration-150 flex-shrink-0"
                               :class="{ 'rotate-90': openProj }"></i>
                            <i class="fas fa-project-diagram text-blue-400 text-xs flex-shrink-0"></i>
                            <span class="text-sm font-semibold text-gray-800 truncate flex-1">{{ $proj->name }}</span>
                            <span class="text-xs text-gray-400 flex-shrink-0">
                                {{ $listsWithTasks->sum(fn($l) => $l->tasks->count()) }}
                            </span>
                        </button>

                        <div x-show="openProj" x-cloak>
                            @foreach($proj->taskLists as $list)
                            @if($list->tasks->isNotEmpty())
                            <div x-data="{ openList: false }" class="pl-6">
                                <!-- Task List row -->
                                <button @click="openList = !openList"
                                        class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                    <i class="fas fa-chevron-right text-gray-300 text-xs transition-transform duration-150 flex-shrink-0"
                                       :class="{ 'rotate-90': openList }"></i>
                                    <i class="fas fa-list text-purple-400 text-xs flex-shrink-0"></i>
                                    <span class="text-xs font-medium text-gray-600 truncate flex-1">{{ $list->name }}</span>
                                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $list->tasks->count() }}</span>
                                </button>

                                <!-- Tasks -->
                                <div x-show="openList" x-cloak class="pl-4 border-l border-gray-100 ml-2 mb-1">
                                    @foreach($list->tasks as $task)
                                    @php
                                        $pBadge = match($task->priority ?? 'medium') {
                                            'urgent' => 'bg-red-100 text-red-600',
                                            'high'   => 'bg-orange-100 text-orange-600',
                                            'medium' => 'bg-yellow-100 text-yellow-600',
                                            default  => 'bg-gray-100 text-gray-500',
                                        };
                                    @endphp
                                    <div class="tree-task-item flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-blue-50 group/task transition-colors"
                                         data-tree-title="{{ strtolower($task->title) }}">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                                {{ match($task->task_status ?? 'pending') {
                                                    'approved'    => 'bg-green-500',
                                                    'in_progress' => 'bg-blue-500',
                                                    'deployed'    => 'bg-purple-500',
                                                    'unapproved'  => 'bg-red-400',
                                                    default       => 'bg-gray-300'
                                                } }}"></span>
                                            <span class="text-xs text-gray-700 truncate flex-1">{{ $task->title }}</span>
                                            <span class="text-xs px-1 py-0.5 rounded flex-shrink-0 {{ $pBadge }}">
                                                {{ ucfirst($task->priority ?? 'medium') }}
                                            </span>
                                        </div>
                                        <!-- Add to sprint -->
                                        <form action="{{ route('sprints.tasks.add', $sprint->id) }}"
                                              method="POST" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="task_id" value="{{ $task->id }}">
                                            <button type="submit" title="Add to sprint"
                                                    class="opacity-0 group-hover/task:opacity-100 transition-opacity text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium whitespace-nowrap">
                                                <i class="fas fa-plus mr-0.5"></i> Add
                                            </button>
                                        </form>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        <!-- ── END Tree Panel ──────────────────────────────────────────── -->

        <!-- ── Sprint Tasks ─────────────────────────────────────────────── -->

        <!-- Empty state (no tasks at all) -->
        @if($sprint->tasks->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-16 text-center">
            <i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i>
            <p class="text-gray-500 font-medium">No tasks in this sprint yet.</p>
            <p class="text-xs text-gray-400 mt-1">Click <strong>Add Task</strong> above to assign tasks to this sprint.</p>
        </div>
        @else

        @php
            // Build shared PHP vars per task (used in both grid & list)
        @endphp

        <!-- GRID VIEW -->
        <div x-show="viewMode === 'grid'" x-transition>
            <div id="taskGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($sprint->tasks as $task)
                @php
                    $tStatusStripe = match($task->task_status ?? 'pending') {
                        'approved'                 => 'bg-green-500',
                        'in_progress'              => 'bg-blue-500',
                        'completed_pending_review' => 'bg-yellow-500',
                        'deployed'                 => 'bg-purple-500',
                        'unapproved'               => 'bg-red-400',
                        default                    => 'bg-gray-300',
                    };
                    $tStatusBadge = match($task->task_status ?? 'pending') {
                        'approved'                 => 'bg-green-100 text-green-800',
                        'in_progress'              => 'bg-blue-100 text-blue-800',
                        'completed_pending_review' => 'bg-yellow-100 text-yellow-800',
                        'deployed'                 => 'bg-purple-100 text-purple-800',
                        'unapproved'               => 'bg-red-100 text-red-700',
                        default                    => 'bg-gray-100 text-gray-600',
                    };
                    $priorityBadge = match($task->priority ?? 'medium') {
                        'urgent' => 'bg-red-100 text-red-700 border-red-200',
                        'high'   => 'bg-orange-100 text-orange-700 border-orange-200',
                        'medium' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        default  => 'bg-gray-100 text-gray-600 border-gray-200',
                    };
                    $isOverdue = $task->due_date && $task->due_date->isPast()
                        && !in_array($task->task_status ?? 'pending', ['approved', 'deployed']);
                @endphp
                <div class="task-card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all overflow-hidden group"
                     data-title="{{ strtolower($task->title) }}"
                     data-priority="{{ $task->priority ?? 'medium' }}"
                     data-status="{{ $task->task_status ?? 'pending' }}">
                    <div class="h-1.5 {{ $tStatusStripe }}"></div>
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <a href="{{ route('tasks.show', $task->id) }}"
                               class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors leading-snug line-clamp-2 flex-1">
                                {{ $task->title }}
                            </a>
                            <form action="{{ route('sprints.tasks.remove', [$sprint->id, $task->id]) }}"
                                  method="POST" class="flex-shrink-0">
                                @csrf @method('DELETE')
                                <button type="submit" title="Remove from sprint"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-300 hover:text-red-500 text-xs w-6 h-6 flex items-center justify-center rounded hover:bg-red-50">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center flex-wrap gap-1.5 mb-3">
                            <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $priorityBadge }}">
                                {{ ucfirst($task->priority ?? 'medium') }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize {{ $tStatusBadge }}">
                                {{ str_replace('_', ' ', $task->task_status ?? 'pending') }}
                            </span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-gray-400">
                            @if($task->project)
                            <span class="flex items-center gap-1"><i class="fas fa-project-diagram text-gray-300"></i>{{ $task->project->name }}</span>
                            @endif
                            @if($task->taskList)
                            <span class="flex items-center gap-1"><i class="fas fa-list text-gray-300"></i>{{ $task->taskList->name }}</span>
                            @endif
                            @if($task->assignedUser)
                            <span class="flex items-center gap-1"><i class="fas fa-user text-gray-300"></i>{{ $task->assignedUser->first_name }}</span>
                            @endif
                            @if($task->due_date)
                            <span class="flex items-center gap-1 {{ $isOverdue ? 'text-red-500 font-medium' : '' }}">
                                <i class="fas fa-calendar text-gray-300"></i>
                                {{ $task->due_date->format('M d') }}
                                @if($isOverdue)<span class="text-red-400">(overdue)</span>@endif
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- LIST VIEW -->
        <div x-show="viewMode === 'list'" x-transition>
            <div id="taskList" class="space-y-2">
                @foreach($sprint->tasks as $task)
                @php
                    $tStatusStripe = match($task->task_status ?? 'pending') {
                        'approved'                 => 'bg-green-500',
                        'in_progress'              => 'bg-blue-500',
                        'completed_pending_review' => 'bg-yellow-500',
                        'deployed'                 => 'bg-purple-500',
                        'unapproved'               => 'bg-red-400',
                        default                    => 'bg-gray-300',
                    };
                    $tStatusBadge = match($task->task_status ?? 'pending') {
                        'approved'                 => 'bg-green-100 text-green-800',
                        'in_progress'              => 'bg-blue-100 text-blue-800',
                        'completed_pending_review' => 'bg-yellow-100 text-yellow-800',
                        'deployed'                 => 'bg-purple-100 text-purple-800',
                        'unapproved'               => 'bg-red-100 text-red-700',
                        default                    => 'bg-gray-100 text-gray-600',
                    };
                    $priorityBadge = match($task->priority ?? 'medium') {
                        'urgent' => 'bg-red-100 text-red-700 border-red-200',
                        'high'   => 'bg-orange-100 text-orange-700 border-orange-200',
                        'medium' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        default  => 'bg-gray-100 text-gray-600 border-gray-200',
                    };
                    $isOverdue = $task->due_date && $task->due_date->isPast()
                        && !in_array($task->task_status ?? 'pending', ['approved', 'deployed']);
                @endphp
                <div class="task-card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all overflow-hidden group flex items-stretch"
                     data-title="{{ strtolower($task->title) }}"
                     data-priority="{{ $task->priority ?? 'medium' }}"
                     data-status="{{ $task->task_status ?? 'pending' }}">
                    <!-- Left status stripe -->
                    <div class="w-1.5 flex-shrink-0 {{ $tStatusStripe }}"></div>
                    <div class="flex-1 px-5 py-3 flex items-center gap-4">
                        <!-- Title -->
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('tasks.show', $task->id) }}"
                               class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors truncate block">
                                {{ $task->title }}
                            </a>
                            <div class="flex items-center flex-wrap gap-x-3 gap-y-0.5 mt-0.5 text-xs text-gray-400">
                                @if($task->project)
                                <span class="flex items-center gap-1"><i class="fas fa-project-diagram text-gray-300"></i>{{ $task->project->name }}</span>
                                @endif
                                @if($task->taskList)
                                <span class="flex items-center gap-1"><i class="fas fa-list text-gray-300"></i>{{ $task->taskList->name }}</span>
                                @endif
                                @if($task->assignedUser)
                                <span class="flex items-center gap-1"><i class="fas fa-user text-gray-300"></i>{{ $task->assignedUser->first_name }}</span>
                                @endif
                                @if($task->due_date)
                                <span class="flex items-center gap-1 {{ $isOverdue ? 'text-red-500 font-medium' : '' }}">
                                    <i class="fas fa-calendar text-gray-300"></i>
                                    {{ $task->due_date->format('M d') }}
                                    @if($isOverdue)<span>(overdue)</span>@endif
                                </span>
                                @endif
                            </div>
                        </div>
                        <!-- Badges -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $priorityBadge }}">
                                {{ ucfirst($task->priority ?? 'medium') }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize {{ $tStatusBadge }}">
                                {{ str_replace('_', ' ', $task->task_status ?? 'pending') }}
                            </span>
                            <form action="{{ route('sprints.tasks.remove', [$sprint->id, $task->id]) }}"
                                  method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" title="Remove from sprint"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-300 hover:text-red-500 text-xs w-6 h-6 flex items-center justify-center rounded hover:bg-red-50">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Filtered-out empty state -->
        <div id="filterEmpty" class="hidden py-12 text-center bg-white rounded-xl border border-gray-200 shadow-sm mt-4">
            <i class="fas fa-search text-3xl text-gray-300 mb-3 block"></i>
            <p class="text-gray-500 text-sm">No tasks match the current filters.</p>
            <button onclick="resetFilters()"
                    class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-medium">Clear filters</button>
        </div>

        @endif
    </div>
    <!-- ═══ END CONTENT ══════════════════════════════════════════════════ -->
</div>

@push('scripts')
<script>
function sprintBoard() {
    return {
        showPanel: false,
        treeSearch: '',
        viewMode: 'grid',
        init() {
            const saved = localStorage.getItem('sprintShowViewMode');
            if (saved === 'grid' || saved === 'list') this.viewMode = saved;
            this.$watch('viewMode', v => localStorage.setItem('sprintShowViewMode', v));
            this.$watch('treeSearch', v => filterTreeTasks(v));
            this.$nextTick(() => filterSprintTasks());
        }
    };
}

function filterTreeTasks(search) {
    const q = (search || '').toLowerCase().trim();
    document.querySelectorAll('.tree-task-item').forEach(item => {
        const title = item.dataset.treeTitle || '';
        item.style.display = (!q || title.includes(q)) ? '' : 'none';
    });
}

function filterSprintTasks() {
    const search        = (document.getElementById('taskSearch')?.value || '').toLowerCase().trim();
    const priority      = document.getElementById('priorityFilter')?.value || '';
    const hideCompleted = document.getElementById('hideCompleted')?.checked ?? false;

    let visible = 0;
    document.querySelectorAll('.task-card').forEach(card => {
        const title  = card.dataset.title || '';
        const prio   = card.dataset.priority || '';
        const status = card.dataset.status || '';

        let show = true;
        if (search   && !title.includes(search))   show = false;
        if (priority && prio !== priority)          show = false;
        if (hideCompleted && status === 'approved') show = false;

        card.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const countEl = document.getElementById('visibleTaskCount');
    if (countEl) countEl.textContent = visible;

    const emptyEl = document.getElementById('filterEmpty');
    if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
}

function resetFilters() {
    ['taskSearch', 'priorityFilter'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    const h = document.getElementById('hideCompleted');
    if (h) h.checked = false;
    filterSprintTasks();
}
</script>
@endpush
@endsection
