@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Task List Templates')

@section('content')
@php
    $priorityOrder = ['urgent' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];

    // Tree for "Add Existing Tasks" picker: Project -> Task List -> Tasks
    $treeData = [];
    foreach ($projects as $proj) {
        $projNode = ['id' => $proj->id, 'name' => $proj->name, 'lists' => []];

        $sortedLists = $proj->taskLists->sortBy('name');
        foreach ($sortedLists as $list) {
            $listNode = ['id' => $list->id, 'name' => $list->name, 'tasks' => []];

            $sortedTasks = $list->tasks->sortBy([
                fn($t) => $priorityOrder[$t->priority ?? 'medium'] ?? 2,
                fn($t) => strtolower($t->title ?? ''),
            ]);

            foreach ($sortedTasks as $task) {
                $listNode['tasks'][] = [
                    'id'              => $task->id,
                    'title'           => $task->title ?? '',
                    'description'     => $task->description ?? '',
                    'priority'        => $task->priority ?? 'medium',
                    'estimated_hours' => $task->estimated_hours ?? '',
                    'task_type'       => $task->task_type ?? 'general',
                    'status'          => $task->task_status ?? 'pending',
                    'projectName'     => $proj->name,
                    'listName'        => $list->name,
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

    usort($treeData, fn($a, $b) => strcasecmp($a['name'], $b['name']));
@endphp

<div class="min-h-screen bg-gray-50" x-data="taskListTemplateManager()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left w-5 h-5"></i>
                        <span>Back</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <h1 class="text-2xl font-bold text-gray-900">Task List Templates</h1>
                </div>
                <button
                    x-show="!showAddForm"
                    @click="openAddForm()"
                    class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                    <i class="fas fa-plus w-4 h-4"></i>
                    <span>Add Template</span>
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-8">

        <!-- Add/Edit Form -->
        <div x-show="showAddForm" x-cloak class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900" x-text="editingTemplate ? 'Edit Template' : 'New Task List Template'"></h2>
                <button @click="handleCancel()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form @submit.prevent="submitForm()" class="p-6 space-y-6">
                <!-- Name + Description row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Template Name *</label>
                        <input type="text" x-model="formData.name" required
                               placeholder="e.g., Bug Tracker, Sprint Board, Onboarding"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <input type="text" x-model="formData.description"
                               placeholder="Brief description of this template"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm">
                    </div>
                </div>

                <!-- Color Selection — same 4-col grid style as task list create page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">List Color</label>
                    @php
                        $colorOptions = [
                            ['value' => 'bg-blue-100',   'label' => 'Blue'],
                            ['value' => 'bg-green-100',  'label' => 'Green'],
                            ['value' => 'bg-yellow-100', 'label' => 'Yellow'],
                            ['value' => 'bg-red-100',    'label' => 'Red'],
                            ['value' => 'bg-purple-100', 'label' => 'Purple'],
                            ['value' => 'bg-indigo-100', 'label' => 'Indigo'],
                            ['value' => 'bg-pink-100',   'label' => 'Pink'],
                            ['value' => 'bg-gray-100',   'label' => 'Gray'],
                        ];
                    @endphp
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($colorOptions as $color)
                        <button
                            type="button"
                            @click="formData.color = '{{ $color['value'] }}'"
                            :class="formData.color === '{{ $color['value'] }}' ? 'border-blue-500 shadow-md' : 'border-gray-200 hover:border-gray-300'"
                            class="p-3 rounded-lg border-2 transition-all duration-200"
                        >
                            <div class="w-full h-8 rounded {{ $color['value'] }} mb-2"></div>
                            <span class="text-xs font-medium text-gray-700">{{ $color['label'] }}</span>
                        </button>
                        @endforeach
                    </div>

                    <!-- Color preview strip -->
                    <div class="mt-4 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 flex items-center justify-between transition-all"
                             :class="formData.color">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-list text-gray-500 text-xs"></i>
                                <span class="text-sm font-semibold text-gray-900" x-text="formData.name || 'Template Name'"></span>
                            </div>
                            <span class="bg-white bg-opacity-70 text-xs font-medium text-gray-700 px-2 py-0.5 rounded-full"
                                  x-text="formData.tasks.length + ' tasks'"></span>
                        </div>
                    </div>
                </div>

                <!-- Task Builder -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">
                            Template Tasks
                            <span class="ml-1 text-xs text-gray-400 font-normal">(created when this template is applied)</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400" x-text="formData.tasks.length + ' task(s)'"></span>
                            <button type="button"
                                    @click="openExistingTasksPanel()"
                                    :class="showExistingTasksPanel ? 'bg-gray-100 text-gray-700' : 'bg-blue-600 text-white hover:bg-blue-700'"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors">
                                <i class="fas fa-layer-group text-xs"></i>
                                <span x-text="showExistingTasksPanel ? 'Choosing…' : 'Add Existing Tasks'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- ── Add Existing Tasks Dual-Panel Picker ──────────────────────────────── -->
                    <div x-show="showExistingTasksPanel" x-cloak
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
                                <span class="text-sm font-semibold text-gray-900">Add Tasks to Template</span>
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
                                <button type="button" @click="showExistingTasksPanel = false"
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
                                            <button type="button" @click="deselectTask(task.id)"
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
                                        <button type="button" @click="toggleProject(proj.id)"
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
                                                    <button type="button" @click="toggleList(list.id)"
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
                                <button type="button" @click="clearSelectedTasks()"
                                        x-show="selectedTasks.length > 0"
                                        class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors">
                                    Clear
                                </button>

                                <button type="button" @click="addSelectedTasksToTemplate()"
                                        :disabled="selectedTasks.length === 0"
                                        :class="selectedTasks.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                                        class="px-5 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg transition-colors flex items-center gap-2">
                                    <i class="fas fa-plus text-xs"></i>
                                    Add <span x-text="selectedTasks.length"></span> Task(s) to Template
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- ── END Dual-Panel Picker ────────────────────────────────────────── -->

                    <!-- Task cards — same style as project/sprint task rows -->
                    <div class="space-y-1.5" x-show="formData.tasks.length > 0">
                        <template x-for="(task, idx) in formData.tasks" :key="idx">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg hover:bg-white hover:shadow-sm transition-all group overflow-hidden">
                                <!-- Main row -->
                                <div class="flex items-start gap-3 px-4 py-3">
                                    <!-- Drag handle -->
                                    <i class="fas fa-grip-vertical text-gray-300 text-xs cursor-grab flex-shrink-0 mt-1"></i>
                                    <!-- Task type emoji -->
                                    <span class="text-base flex-shrink-0 mt-0.5"
                                          x-text="task.task_type === 'equipmentId' ? '🔧' : (task.task_type === 'customerName' ? '👤' : '📝')"></span>
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <!-- Title row -->
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <input type="text" x-model="task.title"
                                                   class="flex-1 text-sm font-medium text-gray-900 bg-transparent border-none outline-none focus:bg-white focus:border focus:border-blue-200 focus:px-2 focus:rounded transition-all min-w-0">
                                            <!-- Badges -->
                                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                                <span class="text-xs font-medium px-2 py-0.5 rounded-full border"
                                                      :class="{
                                                          'bg-red-100 text-red-800 border-red-200':       task.priority === 'urgent',
                                                          'bg-orange-100 text-orange-800 border-orange-200': task.priority === 'high',
                                                          'bg-yellow-100 text-yellow-800 border-yellow-200': task.priority === 'medium',
                                                          'bg-green-100 text-green-800 border-green-200':  task.priority === 'low'
                                                      }"
                                                      x-text="task.priority.charAt(0).toUpperCase() + task.priority.slice(1)"></span>
                                                <span class="text-xs font-medium px-2 py-0.5 rounded-full border bg-gray-500 text-white border-gray-600">Pending</span>
                                            </div>
                                        </div>
                                        <!-- Description preview -->
                                        <p x-show="task.description" class="text-xs text-gray-500 line-clamp-1 mb-1" x-text="task.description"></p>
                                        <!-- Meta -->
                                        <div class="flex items-center gap-3 text-xs text-gray-400">
                                            <span x-show="task.estimated_hours" class="flex items-center gap-1">
                                                <i class="fas fa-clock"></i>
                                                <span x-text="task.estimated_hours + 'h'"></span>
                                            </span>
                                            <span x-show="task.task_type !== 'general'" class="flex items-center gap-1">
                                                <span x-text="task.task_type === 'equipmentId' ? '🔧 Equipment' : '👤 Customer'"></span>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Expand / Remove -->
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                       
                                        <button type="button" @click="removeTask(idx)"
                                                class="p-1 text-gray-300 hover:text-red-500 transition-colors rounded opacity-0 group-hover:opacity-100">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Expanded edit panel -->
                                <div x-show="expandedTaskIdx === idx" x-cloak
                                     class="border-t border-gray-200 bg-white px-4 py-4 space-y-3">
                                    <!-- Task Type -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Task Type</label>
                                        <div class="flex gap-2">
                                            <button type="button" @click="task.task_type = 'general'"
                                                    :class="task.task_type === 'general' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'"
                                                    class="flex items-center gap-1.5 px-2.5 py-1.5 border-2 rounded-lg text-xs font-medium text-gray-700 transition-all">
                                                <span>📝</span> General
                                            </button>
                                            <button type="button" @click="task.task_type = 'equipmentId'"
                                                    :class="task.task_type === 'equipmentId' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'"
                                                    class="flex items-center gap-1.5 px-2.5 py-1.5 border-2 rounded-lg text-xs font-medium text-gray-700 transition-all">
                                                <span>🔧</span> Equipment
                                            </button>
                                            <button type="button" @click="task.task_type = 'customerName'"
                                                    :class="task.task_type === 'customerName' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'"
                                                    class="flex items-center gap-1.5 px-2.5 py-1.5 border-2 rounded-lg text-xs font-medium text-gray-700 transition-all">
                                                <span>👤</span> Customer
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Title -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Title *</label>
                                        <input type="text" x-model="task.title"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                    </div>
                                    <!-- Description -->
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Description</label>
                                        <textarea x-model="task.description" rows="3"
                                                  placeholder="Optional description or notes…"
                                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                                    </div>
                                    <!-- Priority · Estimated Hours -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Priority</label>
                                            <select x-model="task.priority"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                                <option value="low">Low</option>
                                                <option value="medium">Medium</option>
                                                <option value="high">High</option>
                                                <option value="urgent">Urgent</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Estimated Hours</label>
                                            <input type="number" x-model="task.estimated_hours" min="0" step="0.5"
                                                   placeholder="e.g. 2"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="button" @click="expandedTaskIdx = null"
                                                class="px-3 py-1.5 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            Done
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Empty tasks state -->
                    <div x-show="formData.tasks.length === 0"
                         class="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-gray-200 rounded-lg">
                        <i class="fas fa-tasks text-gray-300 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-400">No tasks yet. Click <strong>Add Existing Tasks</strong> to pick tasks for this template.</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <button type="button" @click="handleCancel()"
                            class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                        Back
                    </button>
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                        <i class="fas fa-save text-xs"></i>
                        <span x-text="editingTemplate ? 'Update Template' : 'Create Template'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Template List -->
        @if($templates->count() === 0)
        <div class="text-center bg-white rounded-xl border border-gray-200 p-16">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-layer-group text-blue-400 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Task List Templates Yet</h3>
            <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                Create templates to quickly scaffold task lists with predefined tasks and structure.
            </p>
            <button x-show="!showAddForm" @click="openAddForm()"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus text-sm"></i>
                <span>Create First Template</span>
            </button>
        </div>
        @else
        <div class="space-y-3">
            @foreach($templates as $template)
            @php
                $templateTasks = $template->tasks ?? [];
                $taskCount     = count($templateTasks);
                $priBadge = fn($p) => match($p ?? 'medium') {
                    'urgent' => 'bg-red-100 text-red-800 border-red-200',
                    'high'   => 'bg-orange-100 text-orange-800 border-orange-200',
                    'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                    default  => 'bg-green-100 text-green-800 border-green-200',
                };
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 hover:shadow-sm transition-shadow overflow-hidden">
                <!-- List row header -->
                <div class="flex items-center justify-between px-5 py-4 {{ $template->color ?? 'bg-blue-100' }}">
                    <!-- Left: name + description + task count -->
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <i class="fas fa-list text-gray-500 text-xs flex-shrink-0"></i>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-900 text-sm">{{ $template->name }}</span>
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                    {{ $taskCount }} {{ Str::plural('task', $taskCount) }}
                                </span>
                            </div>
                            @if($template->description)
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $template->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Right: task pill previews + actions -->
                    <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                        <!-- Edit / Delete -->
                        <button
                            type="button"
                            @click="handleEdit({{ json_encode([
                                'id'          => $template->id,
                                'name'        => $template->name,
                                'description' => $template->description,
                                'color'       => $template->color ?? 'bg-blue-100',
                                'tasks'       => $templateTasks,
                            ]) }})"
                            class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded"
                            title="Edit template">
                            <i class="fas fa-pencil-alt text-sm"></i>
                        </button>
                        <form action="{{ route('task-list-templates.destroy', $template->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this template? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-2 text-gray-400 hover:text-red-600 transition-colors rounded"
                                    title="Delete template">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Expandable task list (only when there are tasks) -->
                @if($taskCount > 0)
                <div class="border-t border-gray-100 divide-y divide-gray-50">
                    @foreach($templateTasks as $tTask)
                    @php
                        $typeEmoji = match($tTask['task_type'] ?? 'general') {
                            'equipmentId'  => '🔧',
                            'customerName' => '👤',
                            default        => '📝',
                        };
                    @endphp
                    <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-gray-50 transition-colors">
                        <span class="text-sm flex-shrink-0">{{ $typeEmoji }}</span>
                        <span class="flex-1 text-sm text-gray-800 truncate">{{ $tTask['title'] }}</span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full border flex-shrink-0 {{ $priBadge($tTask['priority'] ?? 'medium') }}">
                            {{ ucfirst($tTask['priority'] ?? 'medium') }}
                        </span>
                        @if(!empty($tTask['estimated_hours']))
                        <span class="text-xs text-gray-400 flex-shrink-0 flex items-center gap-1">
                            <i class="fas fa-clock"></i> {{ $tTask['estimated_hours'] }}h
                        </span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<script>
function taskListTemplateManager() {
    return {
        showAddForm: false,
        showExistingTasksPanel: false,
        treeSearch: '',
        hideTreeCompleted: true,
        selectedTasks: [],
        openProjects: [],
        openLists: [],
        treeData: @json($treeData),
        expandedTaskIdx: null,
        editingTemplate: null,
        formData: {
            name: '',
            description: '',
            color: 'bg-blue-100',
            tasks: [],
        },

        openAddForm() {
            this.resetForm();
            this.showAddForm = true;
        },

        resetForm() {
            this.formData = { name: '', description: '', color: 'bg-blue-100', tasks: [] };
            this.editingTemplate = null;
            this.showExistingTasksPanel = false;
            this.expandedTaskIdx = null;
            this.selectedTasks = [];
            this.openProjects = [];
            this.openLists = [];
            this.treeSearch = '';
            this.hideTreeCompleted = true;
        },

        handleCancel() {
            this.resetForm();
            this.showAddForm = false;
        },

        handleEdit(template) {
            this.editingTemplate = template;
            this.formData = {
                name:        template.name,
                description: template.description || '',
                color:       template.color || 'bg-blue-100',
                tasks:       template.tasks ? JSON.parse(JSON.stringify(template.tasks)) : [],
            };
            this.showAddForm = true;
            this.showExistingTasksPanel = false;
            this.selectedTasks = [];
            this.openProjects = [];
            this.openLists = [];
            this.treeSearch = '';
            this.$nextTick(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        },

        openExistingTasksPanel() {
            this.showExistingTasksPanel = true;
            this.expandedTaskIdx = null;
            this.selectedTasks = [];
            this.openProjects = [];
            this.openLists = [];
            this.treeSearch = '';
            this.hideTreeCompleted = true;
        },

        get selectedIds() {
            return this.selectedTasks.map(t => t.id);
        },

        get excludedIds() {
            // Exclude tasks already staged + tasks already added to the template (when we know their source_task_id)
            const set = new Set(this.selectedIds);
            this.formData.tasks.forEach(t => {
                if (t.source_task_id) set.add(t.source_task_id);
            });
            return Array.from(set);
        },

        filteredProjects() {
            const search = this.treeSearch.toLowerCase().trim();
            const excludedIds = this.excludedIds;
            const hideComplete = this.hideTreeCompleted;

            return this.treeData
                .map(proj => {
                    const lists = (proj.lists || []).map(list => {
                        const tasks = (list.tasks || []).filter(task => {
                            if (excludedIds.includes(task.id)) return false;
                            if (hideComplete && task.status === 'approved') return false;
                            if (search && !(task.title || '').toLowerCase().includes(search)) return false;
                            return true;
                        });
                        return { ...list, tasks };
                    }).filter(list => list.tasks.length > 0);

                    return { ...proj, lists };
                })
                .filter(proj => proj.lists.length > 0);
        },

        toggleProject(id) {
            const idx = this.openProjects.indexOf(id);
            if (idx === -1) this.openProjects.push(id);
            else this.openProjects.splice(idx, 1);
        },

        isProjectOpen(id) { return this.openProjects.includes(id); },

        toggleList(id) {
            const idx = this.openLists.indexOf(id);
            if (idx === -1) this.openLists.push(id);
            else this.openLists.splice(idx, 1);
        },

        isListOpen(id) { return this.openLists.includes(id); },

        selectTask(task) {
            if (this.excludedIds.includes(task.id)) return;
            this.selectedTasks.push(task);
        },

        deselectTask(taskId) {
            this.selectedTasks = this.selectedTasks.filter(t => t.id !== taskId);
        },

        clearSelectedTasks() {
            this.selectedTasks = [];
        },

        addSelectedTasksToTemplate() {
            if (this.selectedTasks.length === 0) return;

            this.selectedTasks.forEach(task => {
                this.formData.tasks.push({
                    source_task_id: task.id,
                    title: task.title,
                    description: task.description || '',
                    priority: task.priority || 'medium',
                    estimated_hours: task.estimated_hours ?? '',
                    task_type: task.task_type || 'general',
                });
            });

            this.selectedTasks = [];
            this.openProjects = [];
            this.openLists = [];
            this.showExistingTasksPanel = false;
        },

        toggleExpandTask(idx) {
            this.expandedTaskIdx = this.expandedTaskIdx === idx ? null : idx;
        },

        removeTask(idx) {
            this.formData.tasks.splice(idx, 1);
            if (this.expandedTaskIdx === idx) this.expandedTaskIdx = null;
        },

        submitForm() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = this.editingTemplate
                ? '{{ url('task-list-templates') }}/' + this.editingTemplate.id
                : '{{ route('task-list-templates.store') }}';
            form.style.display = 'none';

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const addHidden = (name, value) => {
                const el = document.createElement('input');
                el.type = 'hidden';
                el.name = name;
                el.value = value;
                form.appendChild(el);
            };

            addHidden('_token', csrf);
            if (this.editingTemplate) addHidden('_method', 'PUT');
            addHidden('name',        this.formData.name);
            addHidden('description', this.formData.description);
            addHidden('color',       this.formData.color);

            this.formData.tasks.forEach((task, i) => {
                addHidden(`tasks[${i}][title]`,            task.title);
                addHidden(`tasks[${i}][priority]`,         task.priority || 'medium');
                addHidden(`tasks[${i}][description]`,      task.description || '');
                addHidden(`tasks[${i}][estimated_hours]`,  task.estimated_hours ?? '');
                if (task.source_task_id !== undefined) {
                    addHidden(`tasks[${i}][source_task_id]`, task.source_task_id);
                }
                addHidden(`tasks[${i}][task_type]`,        task.task_type || 'general');
            });

            document.body.appendChild(form);
            form.submit();
        },
    };
}
</script>
@endsection
