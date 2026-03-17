@extends('layouts.dashboard')

@section('title', 'Proj Mgr - ' . $sprint->name)

@section('content')
@php
    $taskCount        = $sprint->tasks->count();
    $doneCount        = $sprint->tasks->where('task_status', 'approved')->count();
    $visibleTaskCount = $taskCount - $doneCount; // default: hide completed is checked
    $progress         = $taskCount > 0 ? round(($doneCount / $taskCount) * 100) : 0;
    $statusColor = match($sprint->status) {
        'active'    => 'bg-green-100 text-green-800 border-green-200',
        'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
        default     => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    };
    $userRoleNames = auth()->user()
        ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->all()
        : [];
    $isMasterAdmin = in_array('master admin', $userRoleNames) || in_array('master_admin', $userRoleNames);
    $priorityOrder = ['urgent' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];

    $treeData = [];
    foreach ($projects as $proj) {
        $projNode = ['id' => $proj->id, 'name' => $proj->name, 'lists' => []];

        // Sort task lists alphabetically
        $sortedLists = $proj->taskLists->sortBy('name');

        foreach ($sortedLists as $list) {
            $listNode = ['id' => $list->id, 'name' => $list->name, 'tasks' => []];

            // Sort tasks: priority first, then alphabetically by title
            $sortedTasks = $list->tasks->sortBy([
                fn($a, $b) => ($priorityOrder[$a->priority ?? 'medium'] ?? 2)
                           <=> ($priorityOrder[$b->priority ?? 'medium'] ?? 2),
                fn($a, $b) => strcasecmp($a->title, $b->title),
            ]);

            foreach ($sortedTasks as $task) {
                $listNode['tasks'][] = [
                    'id'          => $task->id,
                    'title'       => $task->title,
                    'priority'    => $task->priority ?? 'medium',
                    'status'      => $task->task_status ?? 'pending',
                    'projectName' => $proj->name,
                    'listName'    => $list->name,
                ];
            }
            if (!empty($listNode['tasks'])) {
                $projNode['lists'][] = $listNode;
            }
        }
        if (!empty($projNode['lists'])) {
            $treeData[] = $projNode;
        }
    }

    // Sort projects alphabetically
    usort($treeData, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    // Group sprint tasks by project → task list (sorted alpha, tasks by priority then alpha)
    $grouped = $sprint->tasks
        ->sortBy([
            fn($a, $b) => ($priorityOrder[$a->priority ?? 'medium'] ?? 2)
                       <=> ($priorityOrder[$b->priority ?? 'medium'] ?? 2),
            fn($a, $b) => strcasecmp($a->title, $b->title),
        ])
        ->groupBy([
            fn($t) => $t->project?->name ?? 'No Project',
            fn($t) => $t->taskList?->name  ?? 'No Task List',
        ]);
    $grouped = $grouped->sortKeys();
    $grouped = $grouped->map(fn($lists) => $lists->sortKeys());
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
                    <span id="visibleTaskCount">{{ $visibleTaskCount }}</span> {{ Str::plural('task', $visibleTaskCount) }}
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

        <!-- ── Add Task Dual-Panel Picker ──────────────────────────────── -->
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
                    <span class="text-sm font-semibold text-gray-900">Add Tasks to Sprint</span>
                    <span class="text-xs text-gray-400">
                        (<span x-text="selectedTasks.length"></span> selected)
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Hide Completed -->
                    <label class="flex items-center gap-1.5 text-xs text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" x-model="hideTreeCompleted"
                               class="w-3.5 h-3.5 text-blue-600 rounded border-gray-300">
                        Hide Completed
                    </label>
                    <!-- Search -->
                    <div class="relative">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" x-model="treeSearch" placeholder="Search tasks…"
                               class="pl-7 pr-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-44">
                    </div>
                    <!-- Close -->
                    <button @click="showPanel = false"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Panel body: left = selected, right = tree -->
            <div class="flex" style="height:360px;">

                <!-- LEFT: Selected tasks staging area -->
                <div class="w-1/2 flex-shrink-0 flex flex-col border-r border-gray-100">
                    <div class="px-4 py-2 bg-gray-50 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Selected</span>
                        <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full"
                              x-text="selectedTasks.length"></span>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <!-- Empty state -->
                        <template x-if="selectedTasks.length === 0">
                            <div class="flex flex-col items-center justify-center h-full text-center text-gray-400 py-8">
                                <i class="fas fa-mouse-pointer text-2xl mb-2 text-gray-300"></i>
                                <p class="text-xs leading-relaxed">Click tasks on the right<br>to select them</p>
                            </div>
                        </template>
                        <!-- Selected task list -->
                        <template x-for="task in selectedTasks" :key="task.id">
                            <div class="flex items-start gap-2 px-2 py-2 rounded-lg bg-blue-50 border border-blue-100 mb-1.5">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 mt-1.5"
                                      :class="{
                                          'bg-green-500':  task.status === 'approved',
                                          'bg-blue-500':   task.status === 'in_progress',
                                          'bg-purple-500': task.status === 'deployed',
                                          'bg-red-400':    task.status === 'unapproved',
                                          'bg-gray-300':   !['approved','in_progress','deployed','unapproved'].includes(task.status)
                                      }"></span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-medium text-gray-800 truncate" x-text="task.title"></div>
                                    <div class="text-xs text-gray-400 truncate mt-0.5"
                                         x-text="task.projectName + ' › ' + task.listName"></div>
                                </div>
                                <button @click="deselectTask(task.id)"
                                        class="flex-shrink-0 text-gray-300 hover:text-red-500 transition-colors w-5 h-5 flex items-center justify-center rounded hover:bg-red-50 mt-0.5">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- RIGHT: Tree browser -->
                <div class="w-1/2 overflow-y-auto p-2">
                    <!-- No tasks available -->
                    <template x-if="filteredProjects().length === 0">
                        <div class="flex flex-col items-center justify-center h-full text-center text-gray-400">
                            <i class="fas fa-check-double text-2xl mb-2 text-green-400"></i>
                            <p class="text-sm">No tasks available to add</p>
                        </div>
                    </template>
                    <!-- Projects -->
                    <template x-for="proj in filteredProjects()" :key="proj.id">
                        <div class="mb-1">
                            <!-- Project row -->
                            <button @click="toggleProject(proj.id)"
                                    class="w-full flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                <i class="fas fa-chevron-right text-gray-400 text-xs transition-transform duration-150 flex-shrink-0"
                                   :class="{ 'rotate-90': isProjectOpen(proj.id) }"></i>
                                <i class="fas fa-project-diagram text-blue-400 text-xs flex-shrink-0"></i>
                                <span class="text-sm font-semibold text-gray-800 truncate flex-1" x-text="proj.name"></span>
                                <span class="text-xs text-gray-400 flex-shrink-0"
                                      x-text="proj.lists.reduce((s,l) => s + l.tasks.length, 0)"></span>
                            </button>
                            <!-- Task Lists -->
                            <div x-show="isProjectOpen(proj.id)">
                                <template x-for="list in proj.lists" :key="list.id">
                                    <div class="pl-6">
                                        <!-- List row -->
                                        <button @click="toggleList(list.id)"
                                                class="w-full flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors text-left">
                                            <i class="fas fa-chevron-right text-gray-300 text-xs transition-transform duration-150 flex-shrink-0"
                                               :class="{ 'rotate-90': isListOpen(list.id) }"></i>
                                            <i class="fas fa-list text-purple-400 text-xs flex-shrink-0"></i>
                                            <span class="text-xs font-medium text-gray-600 truncate flex-1" x-text="list.name"></span>
                                            <span class="text-xs text-gray-400 flex-shrink-0" x-text="list.tasks.length"></span>
                                        </button>
                                        <!-- Tasks -->
                                        <div x-show="isListOpen(list.id)" class="pl-4 border-l border-gray-100 ml-2 mb-1">
                                            <template x-for="task in list.tasks" :key="task.id">
                                                <div @click="selectTask(task)"
                                                     class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors group/task">
                                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                          :class="{
                                                              'bg-green-500':  task.status === 'approved',
                                                              'bg-blue-500':   task.status === 'in_progress',
                                                              'bg-purple-500': task.status === 'deployed',
                                                              'bg-red-400':    task.status === 'unapproved',
                                                              'bg-gray-300':   !['approved','in_progress','deployed','unapproved'].includes(task.status)
                                                          }"></span>
                                                    <span class="text-xs text-gray-700 truncate flex-1" x-text="task.title"></span>
                                                    <span class="text-xs px-1 py-0.5 rounded flex-shrink-0"
                                                          :class="{
                                                              'bg-red-100 text-red-600':      task.priority === 'urgent',
                                                              'bg-orange-100 text-orange-600':task.priority === 'high',
                                                              'bg-yellow-100 text-yellow-600':task.priority === 'medium',
                                                              'bg-gray-100 text-gray-500':    task.priority === 'low'
                                                          }"
                                                          x-text="task.priority.charAt(0).toUpperCase() + task.priority.slice(1)"></span>
                                                    <i class="fas fa-plus text-xs text-blue-400 opacity-0 group-hover/task:opacity-100 flex-shrink-0 transition-opacity"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Panel footer -->
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <span class="text-xs text-gray-500">
                    <span x-text="selectedTasks.length"></span> task(s) selected
                </span>
                <div class="flex items-center gap-2">
                    <button @click="selectedTasks = []"
                            x-show="selectedTasks.length > 0"
                            class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                        Clear
                    </button>
                    <button @click="bulkAddSubmit()"
                            :disabled="selectedTasks.length === 0"
                            :class="selectedTasks.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                            class="px-5 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg transition-colors flex items-center gap-2">
                        <i class="fas fa-plus text-xs"></i>
                        Add <span x-text="selectedTasks.length"></span> Task(s) to Sprint
                    </button>
                </div>
            </div>
        </div>
        <!-- ── END Dual-Panel Picker ────────────────────────────────────── -->

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
            $priBadge = fn($p) => match($p ?? 'medium') {
                'urgent' => 'bg-red-100 text-red-800 border-red-200',
                'high'   => 'bg-orange-100 text-orange-800 border-orange-200',
                'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                default  => 'bg-green-100 text-green-800 border-green-200',
            };
            $statusBadge = fn($s) => match($s ?? 'pending') {
                'pending'                  => 'bg-gray-500 text-white border-gray-600',
                'in_progress'              => 'bg-blue-100 text-blue-800 border-blue-200',
                'completed_pending_review' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'approved'                 => 'bg-green-100 text-green-800 border-green-200',
                'unapproved'               => 'bg-red-100 text-red-800 border-red-200',
                'deployed'                 => 'bg-purple-100 text-purple-800 border-purple-200',
                default                    => 'bg-gray-100 text-gray-800 border-gray-200',
            };
            $taskTypeEmoji = fn($t) => match($t ?? 'general') {
                'equipmentId'  => '🔧',
                'customerName' => '👤',
                default        => '📝',
            };
        @endphp

        <!-- GRID VIEW -->
        <div x-show="viewMode === 'grid'" x-transition>
            @foreach($grouped as $projectName => $lists)
            <div class="sprint-project-group mb-8">
                <!-- Project header -->
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-project-diagram text-blue-400 text-sm"></i>
                    <span class="text-sm font-bold text-gray-800">{{ $projectName }}</span>
                    <div class="flex-1 h-px bg-gray-300 ml-1"></div>
                </div>
                <div class="space-y-4 pl-3">
                @foreach($lists as $listName => $listTasks)
                @php $listColor = $listTasks->first()?->taskList?->color ?? 'bg-gray-50'; @endphp
                <div class="sprint-list-group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                    <!-- Task list card header (colored) -->
                    <div class="px-5 py-4 {{ $listColor }} flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">{{ $listName }}</h3>
                        <span class="text-xs text-gray-600 font-medium">
                            {{ $listTasks->count() }} {{ Str::plural('task', $listTasks->count()) }}
                        </span>
                    </div>
                    <!-- Tasks inside the card — grid of task cards -->
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($listTasks as $task)
                        @php
                            $isOverdue = $task->due_date && $task->due_date->isPast()
                                && !in_array($task->task_status ?? 'pending', ['approved','deployed']);
                            $descText  = $task->description ? Str::limit(strip_tags($task->description), 70) : null;
                        @endphp
                        <div class="task-card bg-gray-50 border border-gray-200 rounded-lg hover:shadow-md hover:bg-white transition-all group overflow-hidden"
                             data-task-id="{{ $task->id }}"
                             data-title="{{ strtolower($task->title) }}"
                             data-priority="{{ $task->priority ?? 'medium' }}"
                             data-status="{{ $task->task_status ?? 'pending' }}">
                            <div class="p-3">
                                <!-- Title row -->
                                <div class="flex items-start justify-between gap-1 mb-2">
                                    <div class="flex items-start gap-1.5 flex-1 min-w-0">
                                        <span class="flex-shrink-0 text-sm mt-0.5">{{ $taskTypeEmoji($task->task_type) }}</span>
                                        <a href="{{ route('tasks.show', $task->id) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors line-clamp-2">
                                            {{ $task->title }}
                                        </a>
                                    </div>
                                    <form action="{{ route('sprints.tasks.remove', [$sprint->id, $task->id]) }}" method="POST" class="flex-shrink-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Remove from sprint"
                                                class="opacity-0 group-hover:opacity-100 transition-opacity p-0.5 text-gray-300 hover:text-red-500">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                                <!-- Badges -->
                                <div class="flex flex-wrap gap-1 mb-2">
                                    <span class="px-1.5 py-0.5 rounded-full text-xs font-medium border {{ $priBadge($task->priority) }}">
                                        {{ ucfirst($task->priority ?? 'medium') }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded-full text-xs font-medium border {{ $statusBadge($task->task_status) }}">
                                        @if($task->task_status === 'completed_pending_review') Review
                                        @else {{ ucfirst(str_replace('_', ' ', $task->task_status ?? 'pending')) }}
                                        @endif
                                    </span>
                                </div>
                                @if($descText)
                                <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $descText }}</p>
                                @endif
                                <!-- Meta -->
                                <div class="flex items-center gap-2 text-xs text-gray-400">
                                    @if($task->assignedUser)
                                    <div class="flex items-center gap-1">
                                        <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->first_name, 0, 1)) }}</span>
                                        </div>
                                        <span class="truncate">{{ $task->assignedUser->first_name }}</span>
                                    </div>
                                    @endif
                                    @if($task->due_date)
                                    <div class="flex items-center gap-1 {{ $isOverdue ? 'text-red-500 font-medium' : '' }}">
                                        <i class="fas fa-calendar w-3 h-3"></i>
                                        <span>{{ $task->due_date->format('M j') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- LIST VIEW -->
        <div x-show="viewMode === 'list'" x-transition>
            @foreach($grouped as $projectName => $lists)
            <div class="sprint-project-group mb-8">
                <!-- Project header -->
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-project-diagram text-blue-400 text-sm"></i>
                    <span class="text-sm font-bold text-gray-800">{{ $projectName }}</span>
                    <div class="flex-1 h-px bg-gray-300 ml-1"></div>
                </div>
                @foreach($lists as $listName => $listTasks)
                @php $listColor = $listTasks->first()?->taskList?->color ?? 'bg-gray-50'; @endphp
                <div class="sprint-list-group mb-5 pl-3">
                    <!-- Task list section (matches project view style) -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Task list header with color -->
                        <div class="px-5 py-3 {{ $listColor }} border-b border-gray-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-list text-gray-500 text-xs"></i>
                                <span class="text-sm font-semibold text-gray-900">{{ $listName }}</span>
                            </div>
                            <span class="text-xs text-gray-600 bg-white bg-opacity-60 px-2 py-0.5 rounded-full font-medium">
                                {{ $listTasks->count() }} {{ Str::plural('task', $listTasks->count()) }}
                            </span>
                        </div>
                        <!-- Task rows -->
                        <div class="divide-y divide-gray-100">
                            @foreach($listTasks as $task)
                            @php
                                $isOverdue = $task->due_date && $task->due_date->isPast()
                                    && !in_array($task->task_status ?? 'pending', ['approved','deployed']);
                                $descText = $task->description ? Str::limit(strip_tags($task->description), 100) : null;
                            @endphp
                            <div class="task-card px-6 py-4 hover:bg-gray-50 transition-colors group"
                                 data-task-id="{{ $task->id }}"
                                 data-title="{{ strtolower($task->title) }}"
                                 data-priority="{{ $task->priority ?? 'medium' }}"
                                 data-status="{{ $task->task_status ?? 'pending' }}">
                                <div class="flex items-center justify-between relative">
                                    <div class="flex items-start space-x-4 flex-1 min-w-0">
                                        <!-- Task type emoji -->
                                        <div class="flex-shrink-0 pt-0.5">
                                            <span class="text-lg">{{ $taskTypeEmoji($task->task_type) }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between mb-1">
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('tasks.show', $task->id) }}"
                                                       class="hover:text-blue-600 transition-colors">{{ $task->title }}</a>
                                                </h3>
                                                <!-- Badges top-right -->
                                                <div class="flex items-center gap-1.5 absolute right-8 top-0 flex-shrink-0">
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium border {{ $priBadge($task->priority) }}">
                                                        {{ ucfirst($task->priority ?? 'medium') }}
                                                    </span>
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium border {{ $statusBadge($task->task_status) }}">
                                                        @if($task->task_status === 'completed_pending_review') Review
                                                        @else {{ ucfirst(str_replace('_', ' ', $task->task_status ?? 'pending')) }}
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                            @if($descText)
                                            <p class="text-sm text-gray-500 mb-2">{{ $descText }}</p>
                                            @endif
                                            <!-- Meta row -->
                                            <div class="flex items-center space-x-4 text-xs text-gray-500">
                                                @if($task->assignedUser)
                                                <div class="flex items-center space-x-1.5">
                                                    <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->first_name, 0, 2)) }}</span>
                                                    </div>
                                                    <span>{{ $task->assignedUser->first_name }} {{ $task->assignedUser->last_name }}</span>
                                                </div>
                                                @else
                                                <div class="flex items-center space-x-1 text-gray-400">
                                                    <i class="fas fa-users w-4 h-4"></i>
                                                    <span>Unassigned</span>
                                                </div>
                                                @endif
                                                @if($task->due_date)
                                                <div class="flex items-center space-x-1 {{ $isOverdue ? 'text-red-600 font-medium' : '' }}">
                                                    <i class="fas fa-calendar w-3 h-3"></i>
                                                    <span>Due: {{ $task->due_date->format('M j') }}</span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Hover actions -->
                                    <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                        <a href="{{ route('tasks.show', $task->id) }}"
                                           class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="View Task">
                                            <i class="fas fa-eye w-4 h-4"></i>
                                        </a>
                                        <form action="{{ route('sprints.tasks.remove', [$sprint->id, $task->id]) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Remove from sprint"
                                                    class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                                <i class="fas fa-unlink w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <!-- Filtered-out empty state -->
        <div id="filterEmpty" class="hidden py-12 text-center bg-white rounded-lg border border-gray-200 shadow-sm mt-4">
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
        hideTreeCompleted: true,
        viewMode: 'grid',
        selectedTasks: [],
        openProjects: [],
        openLists: [],
        treeData: @json($treeData),

        get selectedIds() {
            return this.selectedTasks.map(t => t.id);
        },

        filteredProjects() {
            const search       = this.treeSearch.toLowerCase().trim();
            const selectedIds  = this.selectedIds;
            const hideComplete = this.hideTreeCompleted;
            return this.treeData.map(proj => {
                const lists = proj.lists.map(list => {
                    const tasks = list.tasks.filter(task => {
                        if (selectedIds.includes(task.id))              return false;
                        if (hideComplete && task.status === 'approved') return false;
                        if (search && !task.title.toLowerCase().includes(search)) return false;
                        return true;
                    });
                    return { ...list, tasks };
                }).filter(list => list.tasks.length > 0);
                return { ...proj, lists };
            }).filter(proj => proj.lists.length > 0);
        },

        toggleProject(id) {
            const idx = this.openProjects.indexOf(id);
            if (idx === -1) this.openProjects.push(id);
            else            this.openProjects.splice(idx, 1);
        },
        isProjectOpen(id) { return this.openProjects.includes(id); },

        toggleList(id) {
            const idx = this.openLists.indexOf(id);
            if (idx === -1) this.openLists.push(id);
            else            this.openLists.splice(idx, 1);
        },
        isListOpen(id) { return this.openLists.includes(id); },

        selectTask(task) {
            if (!this.selectedIds.includes(task.id)) {
                this.selectedTasks.push(task);
            }
        },

        deselectTask(taskId) {
            this.selectedTasks = this.selectedTasks.filter(t => t.id !== taskId);
        },

        bulkAddSubmit() {
            if (this.selectedTasks.length === 0) return;
            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("sprints.tasks.bulkAdd", $sprint->id) }}';
            const csrf  = document.createElement('input');
            csrf.type   = 'hidden';
            csrf.name   = '_token';
            csrf.value  = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(csrf);
            this.selectedTasks.forEach(task => {
                const inp  = document.createElement('input');
                inp.type   = 'hidden';
                inp.name   = 'task_ids[]';
                inp.value  = task.id;
                form.appendChild(inp);
            });
            document.body.appendChild(form);
            form.submit();
        },

        init() {
            const saved = localStorage.getItem('sprintShowViewMode');
            if (saved === 'list') this.viewMode = 'list';
            this.$watch('viewMode', v => localStorage.setItem('sprintShowViewMode', v));
            this.$nextTick(() => filterSprintTasks());
        }
    };
}

function filterSprintTasks() {
    const search        = (document.getElementById('taskSearch')?.value || '').toLowerCase().trim();
    const priority      = document.getElementById('priorityFilter')?.value || '';
    const hideCompleted = document.getElementById('hideCompleted')?.checked ?? false;

    // Use a Set to deduplicate across grid/list renders
    const visibleIds = new Set();
    document.querySelectorAll('.task-card').forEach(card => {
        const title  = card.dataset.title    || '';
        const prio   = card.dataset.priority || '';
        const status = card.dataset.status   || '';
        const taskId = card.dataset.taskId;

        let show = true;
        if (search        && !title.includes(search)) show = false;
        if (priority      && prio !== priority)        show = false;
        if (hideCompleted && status === 'approved')    show = false;

        card.style.display = show ? '' : 'none';
        if (show && taskId) visibleIds.add(taskId);
    });

    // Hide task list group headers when all their tasks are filtered out
    document.querySelectorAll('.sprint-list-group').forEach(group => {
        const hasVisible = Array.from(group.querySelectorAll('.task-card'))
            .some(c => c.style.display !== 'none');
        group.style.display = hasVisible ? '' : 'none';
    });

    // Hide project group headers when all their task lists are filtered out
    document.querySelectorAll('.sprint-project-group').forEach(group => {
        const hasVisible = Array.from(group.querySelectorAll('.task-card'))
            .some(c => c.style.display !== 'none');
        group.style.display = hasVisible ? '' : 'none';
    });

    const visible = visibleIds.size;
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
