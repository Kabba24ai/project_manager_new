@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Task' )

@section('content')
<div class="min-h-screen bg-gray-50" x-data="projectCopyLink()">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
                            <button 
                                type="button"
                                @click="copyProjectLink()"
                                class="text-blue-500 hover:text-blue-600 transition-colors relative"
                                :title="copiedProjectLink ? 'Copied successfully' : 'Copy project view link'"
                            >
                                <i class="fas fa-copy text-sm" :class="copiedProjectLink ? 'text-green-500' : ''"></i>
                                <span x-show="copiedProjectLink" x-transition class="absolute -top-8 left-1/2 transform bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">Copied successfully</span>
                            </button>
                        </div>
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
                    <a href="{{ route('task-templates.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium transition-colors flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100">
                        <i class="fas fa-copy"></i>
                        <span>Templates</span>
                    </a>
                    
                    @php
                        $userRoleNames = auth()->user()
                            ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->all()
                            : [];
                        $isMasterAdmin = in_array('master admin', $userRoleNames) || in_array('master_admin', $userRoleNames);
                        $canEdit = $isMasterAdmin || $project->created_by === auth()->id() || $project->project_manager_id === auth()->id();
                        $deleteAllowedRoles = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];
                        $canDeleteProject = !empty(array_intersect($userRoleNames, array_map('strtolower', $deleteAllowedRoles)));
                    @endphp
                    
                    @if($canEdit || $canDeleteProject)
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            @click.away="open = false"
                            class="flex items-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium"
                        >
                            <i class="fas fa-ellipsis-v"></i>
                            <span>Actions</span>
                            <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': open }"></i>
                        </button>
                        
                        <div 
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                        >
                            @if($canEdit)
                            <a href="{{ route('projects.edit', $project->id) }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-edit w-4"></i>
                                <span>Edit Project</span>
                            </a>
                            <a href="{{ route('projects.manage-lists', $project->id) }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                <i class="fas fa-list w-4"></i>
                                <span>Manage Lists</span>
                            </a>
                            @endif
                            
                            @if($canDeleteProject)
                            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash w-4"></i>
                                    <span>Delete Project</span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif
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
                                <span>Due: {{ $project->due_date ? \Carbon\Carbon::parse($project->due_date)->format('M j') : 'NA' }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span>{{ $project->taskLists->sum(function($list) { return $list->tasks->where('task_status', 'approved')->count(); }) }}/{{ $project->taskLists->sum(function($list) { return $list->tasks->count(); }) }} tasks completed</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                @php
                                    $totalTasks = $project->taskLists->sum(function($list) { return $list->tasks->count(); });
                                    $completedTasks = $project->taskLists->sum(function($list) { return $list->tasks->where('task_status', 'approved')->count(); });
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
                        @foreach($project->teamMembers->sortBy('name')->take(4) as $member)
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
    <div class="max-w-6xl mx-auto px-6 py-8">

    <!-- Project Attachments -->
    @if($project->attachments->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
                    <i class="fas fa-paperclip text-gray-500"></i>
                    <span>Project Documents</span>
                    <span class="text-sm font-normal text-gray-500">({{ $project->attachments->count() }})</span>
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($project->attachments as $attachment)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:border-blue-300 transition-colors">
                    <div class="flex items-center space-x-3 flex-1 min-w-0">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg {{ 
                            str_starts_with($attachment->mime_type, 'image/') ? 'bg-green-100 text-green-600' :
                            (str_starts_with($attachment->mime_type, 'video/') ? 'bg-purple-100 text-purple-600' :
                            ($attachment->mime_type === 'application/pdf' ? 'bg-red-100 text-red-600' :
                            (str_contains($attachment->mime_type, 'word') ? 'bg-blue-100 text-blue-600' :
                            (str_contains($attachment->mime_type, 'excel') || str_contains($attachment->mime_type, 'sheet') ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-600'))))
                        }}">
                            <i class="fas {{ 
                                str_starts_with($attachment->mime_type, 'image/') ? 'fa-image' :
                                (str_starts_with($attachment->mime_type, 'video/') ? 'fa-video' :
                                ($attachment->mime_type === 'application/pdf' ? 'fa-file-pdf' :
                                (str_contains($attachment->mime_type, 'word') ? 'fa-file-word' :
                                (str_contains($attachment->mime_type, 'excel') || str_contains($attachment->mime_type, 'sheet') ? 'fa-file-excel' : 'fa-file'))))
                            }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate" title="{{ $attachment->original_filename }}">
                                {{ $attachment->original_filename }}
                            </p>
                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                <span>{{ number_format($attachment->size / 1024 / 1024, 2) }} MB</span>
                                <span>•</span>
                                <span>{{ $attachment->uploader->name ?? 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>
                    <a 
                        href="{{ route('attachments.download', $attachment->id) }}" 
                        class="ml-2 p-2 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors"
                        title="Download"
                    >
                        <i class="fas fa-download"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Task Lists Container with Shared Alpine State -->
        <div x-data="{ 
            showFilters: false, 
            viewMode: 'grid',
            expandedTaskListId: null,
            hideCompletedLists: true,
            init() {
                const savedViewMode = localStorage.getItem('projectTaskListViewMode');
                if (savedViewMode === 'grid' || savedViewMode === 'list') {
                    this.viewMode = savedViewMode;
                } else {
                    this.viewMode = 'grid'; // Default to grid view
                }
                this.$watch('viewMode', value => {
                    localStorage.setItem('projectTaskListViewMode', value);
                });
            },
            toggleTaskList(taskListId) {
                if (this.expandedTaskListId === taskListId) {
                    this.expandedTaskListId = null;
                } else {
                    this.expandedTaskListId = taskListId;
                    // Scroll to the expanded task list section
                    setTimeout(() => {
                        const expandedSection = document.getElementById('expanded-task-lists-section');
                        if (expandedSection) {
                            expandedSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                        // Don't call filterProjectTasks() here - it would close the expanded section
                    }, 100);
                }
            }
        }">
        <!-- Task Filters Toggle Button and View Toggle -->
        <div class="mb-6 flex items-center justify-between gap-4">
            <button 
                @click="showFilters = !showFilters" 
                class="flex items-center space-x-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
            >
                <i class="fas fa-filter w-4 h-4"></i>
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                <i class="fas fa-chevron-down w-3 h-3 transition-transform" :class="{ 'rotate-180': showFilters }"></i>
            </button>
            
            <div class="flex gap-2 items-center">
                <label class="flex items-center space-x-2 px-4 py-2 bg-white border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                    <input 
                        type="checkbox" 
                        x-model="hideCompletedLists"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <span class="text-sm font-medium text-gray-700">Hide Completed Lists</span>
                </label>
                <button 
                    @click="viewMode = 'grid'"
                    :class="viewMode === 'grid' ? 'bg-slate-700 text-white hover:bg-slate-800' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-grid w-5 h-5">
                        <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                        <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                    </svg>
                    <span>Grid View</span>
                </button>
                <button 
                    @click="viewMode = 'list'"
                    :class="viewMode === 'list' ? 'bg-slate-700 text-white hover:bg-slate-800' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list w-5 h-5">
                        <line x1="8" x2="21" y1="6" y2="6"></line>
                        <line x1="8" x2="21" y1="12" y2="12"></line>
                        <line x1="8" x2="21" y1="18" y2="18"></line>
                        <line x1="3" x2="3.01" y1="6" y2="6"></line>
                        <line x1="3" x2="3.01" y1="12" y2="12"></line>
                        <line x1="3" x2="3.01" y1="18" y2="18"></line>
                    </svg>
                    <span>List View</span>
                </button>
            </div>
        </div>

        <!-- Task Filters -->
        <div x-show="showFilters" x-cloak x-transition class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-4 mb-4">
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
            <div class="flex flex-col lg:flex-row lg:items-end gap-4 mt-4">
                <!-- Assigned To Filter -->
                <div class="flex-1">
                    <label for="task_assigned_to_filter" class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                    <select
                        id="task_assigned_to_filter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        onchange="filterProjectTasks()"
                    >
                        <option value="">All Users</option>
                        <option value="unassigned">Unassigned</option>
                        @foreach($project->teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                        @endforeach
                    </select>
        </div>

                <!-- Task Type Filter -->
                <div class="flex-1">
                    <label for="task_type_filter" class="block text-sm font-medium text-gray-700 mb-1">Task Type</label>
                    <select
                        id="task_type_filter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        onchange="filterProjectTasks()"
                    >
                        <option value="">All Types</option>
                        <option value="general">📝 General</option>
                        <option value="equipmentId">🔧 Equipment ID</option>
                        <option value="customerName">👤 Customer</option>
                    </select>
                </div>

                <!-- Task Status Filter -->
                <div class="flex-1">
                    <label for="task_status_filter" class="block text-sm font-medium text-gray-700 mb-1">Task Status</label>
                    <select
                        id="task_status_filter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        onchange="filterProjectTasks()"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed_pending_review">Review</option>
                        <option value="unapproved">Unapproved</option>
                    </select>
                </div>

                <!-- Sort Tasks By Filter -->
                <div class="flex-1">
                    <label for="task_sort_filter" class="block text-sm font-medium text-gray-700 mb-1">Sort Tasks By</label>
                    <select
                        id="task_sort_filter"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        onchange="filterProjectTasks()"
                    >
                        <option value="">Default</option>
                        <option value="priority">Priority (Urgent → Low)</option>
                        <option value="alphabetical">Alphabetical (A → Z)</option>
                        <option value="due_date">Due Date (Earliest First)</option>
                        <option value="start_date">Start Date (Earliest First)</option>
                    </select>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Filters apply across all task lists on this project view.</p>
        </div>

        @if($project->taskLists->count() > 0)
        <!-- Task Lists with Tasks -->
        <div>
            <!-- Grid View -->
            <div id="grid-view-container" x-show="viewMode === 'grid'" x-transition>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($project->taskLists->sortBy('order') as $taskList)
                    @php
                        $allTasksApproved = $taskList->tasks->count() > 0 && $taskList->tasks->every(function($task) {
                            return $task->task_status === 'approved';
                        });
                        $shouldHideList = $taskList->tasks->count() === 0 || $allTasksApproved;
                    @endphp
                    <div 
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
                        data-task-list-id="{{ $taskList->id }}"
                        x-show="!hideCompletedLists || !{{ $shouldHideList ? 'true' : 'false' }}"
                        x-transition
                    >
                        <!-- Header with colored background -->
                        <div class="px-5 py-4 {{ $taskList->color }}">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $taskList->name }}</h3>
                            @if($taskList->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $taskList->description }}</p>
                            @endif
                        </div>
                        
                        <!-- Content -->
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium" data-tasklist-count="{{ $taskList->id }}" data-count-type="grid">
                                    {{ $taskList->tasks->count() }} {{ $taskList->tasks->count() === 1 ? 'task' : 'tasks' }}
                                </span>
                                <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}" 
                                   class="px-3 py-1 bg-gray-100 text-gray-700   rounded-lg hover:bg-blue-700 transition-colors hover:text-white text-sm font-medium flex items-center gap-1">
                                    <i class="fas fa-plus text-xs"></i>
                                    <span>Add Task</span>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Footer with View List button -->
                        <div class="px-5 pb-5">
                            <button 
                                @click="toggleTaskList({{ $taskList->id }})"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 text-sm font-medium"
                            >
                                <i class="fas fa-list"></i>
                                <span x-text="expandedTaskListId === {{ $taskList->id }} ? 'Hide List' : 'View List'"></span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Expanded Task List Details (shown below grid) -->
                <div id="expanded-task-lists-section" x-show="expandedTaskListId !== null" class="mt-8" x-transition>
                    @foreach($project->taskLists->sortBy('order') as $taskList)
                    @php
                        $allTasksApproved = $taskList->tasks->count() > 0 && $taskList->tasks->every(function($task) {
                            return $task->task_status === 'approved';
                        });
                    @endphp
                    <div 
                        x-show="expandedTaskListId === {{ $taskList->id }}" 
                        x-cloak
                        class="bg-white rounded-lg shadow-sm border border-gray-200 task-list-expanded"
                    >
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
                                        <label class="flex items-center space-x-2 px-3 py-1 cursor-pointer transition-colors">
                                            <input 
                                                type="checkbox" 
                                                id="show_approved_tasks_expanded_{{ $taskList->id }}"
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 show-approved-checkbox"
                                                onchange="filterProjectTasks()"
                                            >
                                            <span class="text-sm font-medium text-gray-700">Show Approved</span>
                                        </label>
                            <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}" class="flex items-center space-x-1 px-3 py-1 bg-white text-gray-700 rounded-lg hover:bg-green-50 transition-colors border border-gray-200 text-sm font-medium" title="Add task to {{ $taskList->name }}">
                                <i class="fas fa-plus w-4 h-4"></i>
                                <span>Add Task</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tasks -->
                <div class="divide-y divide-gray-100" data-tasklist-id="{{ $taskList->id }}">
                                @if($taskList->tasks->count() > 0)
                                    @php
                                        $priorityOrder = ['urgent' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
                                        $sortedTasks = $taskList->tasks->sortBy(function($task) use ($priorityOrder) {
                                            return $priorityOrder[$task->priority ?? 'medium'] ?? 2;
                        });
                    @endphp
                        @foreach($sortedTasks as $task)
                        <div
                            class="px-6 py-4 hover:bg-gray-50 transition-colors group project-task-row"
                            data-tasklist-id="{{ $taskList->id }}"
                            data-title="{{ strtolower($task->title) }}"
                            data-start-date="{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '' }}"
                            data-due-date="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}"
                                        data-assigned-to="{{ $task->assigned_to ?? '' }}"
                                        data-task-type="{{ $task->task_type ?? 'general' }}"
                                        data-task-status="{{ $task->task_status ?? 'pending' }}"
                                        data-priority="{{ $task->priority ?? 'medium' }}"
                        >
                                        <div class="flex items-center justify-between relative">
                                            <div class="flex items-center space-x-4 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        <span class="text-lg">
                                            @if($task->task_type === 'general')📝
                                            @elseif($task->task_type === 'equipmentId')🔧
                                            @elseif($task->task_type === 'customerName')👤
                                            @else📝
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-base font-medium text-gray-900">{{ $task->title }}</h3>
                                                        <div class="flex items-center space-x-2 absolute right-0 top-0">
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
                                                    </div>
                                                    <div class="text-sm text-gray-600 mb-2 task-description-preview">
                                                        {!! $task->description ?: '' !!}
                                                    </div>
                                        
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            @if($task->assignedUser)
                                            <div class="flex items-center space-x-2">
                                                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->name, 0, 2)) }}</span>
                                                </div>
                                                <span>{{ $task->assignedUser->name }}</span>
                                            </div>
                                            @else
                                                        @if($task->created_by === Auth::id())
                                                        <a href="{{ route('tasks.edit', $task->id) }}" onclick="event.stopPropagation()" class="flex items-center space-x-1 text-gray-500 hover:text-blue-600 transition-colors">
                                                <i class="fas fa-users w-4 h-4"></i>
                                                <span class="underline">Unassigned</span>
                                                        </a>
                                                        @else
                                                        <div class="flex items-center space-x-1 text-gray-400 cursor-not-allowed">
                                                            <i class="fas fa-users w-4 h-4"></i>
                                                            <span>Unassigned</span>
                                            </div>
                                                        @endif
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
                                            </div>

                                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button 
                                                    type="button"
                                                    x-data="{ copiedTaskUrl: false }"
                                                    @click="copyTaskUrl('{{ route('tasks.show', $task->id) }}', $event, $el); copiedTaskUrl = true; setTimeout(() => { copiedTaskUrl = false; }, 2000);"
                                                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors relative" 
                                                    :title="copiedTaskUrl ? 'Copied successfully' : 'Copy Task URL'"
                                                >
                                                    <i class="fas fa-copy w-4 h-4" :class="copiedTaskUrl ? 'text-green-500' : ''"></i>
                                                    <span x-show="copiedTaskUrl" x-transition class="absolute -top-8 left-1/2 transform bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-50">Copied successfully</span>
                                                </button>
                                    <a href="{{ route('tasks.show', $task->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="View Task">
                                        <i class="fas fa-eye w-4 h-4"></i>
                                    </a>
                                                @if($isMasterAdmin || ($canEdit && $task->created_by === Auth::id()))
                                    <a href="{{ route('tasks.edit', $task->id) }}" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="Edit Task">
                                        <i class="fas fa-edit w-4 h-4"></i>
                                    </a>
                                                <div class="relative" x-data="{ open: false, loaded: false }" x-effect="if (open && !loaded) { $el.querySelector('.move-to-content').innerHTML = buildMoveToDropdown({{ $task->id }}, {{ $task->task_list_id }}); loaded = true; }">
                                        <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="More Options">
                                            <i class="fas fa-ellipsis-v w-4 h-4"></i>
                                        </button>

                                                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 top-8 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10 min-w-[250px] max-h-[400px] overflow-y-auto">
                                                        <div class="px-3 py-2 text-xs text-gray-500 font-medium border-b border-gray-100 sticky top-0 bg-white">Move to:</div>
                                                        <div class="move-to-content">
                                                            <div class="px-3 py-4 text-center text-sm text-gray-400">Loading...</div>
                                                        </div>
                                                        <div class="border-t border-gray-100 mt-1"></div>
                                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                                                <i class="fas fa-trash mr-2"></i>Delete Task
                                                </button>
                                            </form>
                                                    </div>
                                                </div>
                                                @endif
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
            </div>
            
            <!-- List View -->
            <div id="list-view-container" x-show="viewMode === 'list'" x-transition>
                <div class="space-y-8">
                    @foreach($project->taskLists->sortBy('order') as $taskList)
                    @php
                        $allTasksApproved = $taskList->tasks->count() > 0 && $taskList->tasks->every(function($task) {
                            return $task->task_status === 'approved';
                        });
                        $shouldHideList = $taskList->tasks->count() === 0 || $allTasksApproved;
                    @endphp
                    <div 
                        class="bg-white rounded-lg shadow-sm border border-gray-200" 
                        id="task-list-{{ $taskList->id }}"
                        data-task-list-id="{{ $taskList->id }}"
                        x-show="!hideCompletedLists || !{{ $shouldHideList ? 'true' : 'false' }}"
                        x-transition
                    >
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
                                    <label class="flex items-center space-x-2 px-3 py-1  cursor-pointer  transition-colors">
                                        <input 
                                            type="checkbox" 
                                            id="show_approved_tasks_{{ $taskList->id }}"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 show-approved-checkbox"
                                            onchange="filterProjectTasks()"
                                        >
                                        <span class="text-sm font-medium text-gray-700">Show Approved</span>
                                    </label>
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
                    @if($taskList->tasks->count() > 0)
                        @php
                            $priorityOrder = ['urgent' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
                            $sortedTasks = $taskList->tasks->sortBy(function($task) use ($priorityOrder) {
                                return $priorityOrder[$task->priority ?? 'medium'] ?? 2;
                            });
                        @endphp
                        @foreach($sortedTasks as $task)
                        <div
                            class="px-6 py-4 hover:bg-gray-50 transition-colors group project-task-row"
                            data-tasklist-id="{{ $taskList->id }}"
                            data-title="{{ strtolower($task->title) }}"
                            data-start-date="{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '' }}"
                            data-due-date="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}"
                            data-assigned-to="{{ $task->assigned_to ?? '' }}"
                            data-task-type="{{ $task->task_type ?? 'general' }}"
                            data-task-status="{{ $task->task_status ?? 'pending' }}"
                            data-priority="{{ $task->priority ?? 'medium' }}"
                        >
                            <div class="flex items-center justify-between relative">
                                <div class="flex items-center space-x-4 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        <span class="text-lg">
                                            @if($task->task_type === 'general')📝
                                            @elseif($task->task_type === 'equipmentId')🔧
                                            @elseif($task->task_type === 'customerName')👤
                                            @else📝
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-base font-medium text-gray-900">{{ $task->title }}</h3>
                                            <div class="flex items-center space-x-2 absolute right-0 top-0">
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
                                            
                                        </div>
                                        <div class="text-sm text-gray-600 mb-2 task-description-preview">
                                            {!! $task->description ?: '' !!}
                                        </div>
                                        
                                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                                            @if($task->assignedUser)
                                            <div class="flex items-center space-x-2">
                                                <div class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->name, 0, 2)) }}</span>
                                                </div>
                                                <span>{{ $task->assignedUser->name }}</span>
                                            </div>
                                            @else
                                            @if($task->created_by === Auth::id())
                                            <a href="{{ route('tasks.edit', $task->id) }}" onclick="event.stopPropagation()" class="flex items-center space-x-1 text-gray-500 hover:text-blue-600 transition-colors">
                                                <i class="fas fa-users w-4 h-4"></i>
                                                <span class="underline">Unassigned</span>
                                            </a>
                                            @else
                                            <div class="flex items-center space-x-1 text-gray-400 cursor-not-allowed">
                                                <i class="fas fa-users w-4 h-4"></i>
                                                <span>Unassigned</span>
                                            </div>
                                            @endif
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
                    </div>

                                <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button 
                                        type="button"
                                        x-data="{ copiedTaskUrl: false }"
                                        @click="copyTaskUrl('{{ route('tasks.show', $task->id) }}', $event, $el); copiedTaskUrl = true; setTimeout(() => { copiedTaskUrl = false; }, 2000);"
                                        class="p-2 text-gray-400 hover:text-blue-600 transition-colors relative" 
                                        :title="copiedTaskUrl ? 'Copied successfully' : 'Copy Task URL'"
                                    >
                                        <i class="fas fa-copy w-4 h-4" :class="copiedTaskUrl ? 'text-green-500' : ''"></i>
                                        <span x-show="copiedTaskUrl" x-transition class="absolute -top-8 left-1/2 transform bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap z-50">Copied successfully</span>
                                    </button>
                                    <a href="{{ route('tasks.show', $task->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors" title="View Task">
                                        <i class="fas fa-eye w-4 h-4"></i>
                                    </a>
                                    @if($isMasterAdmin || ($canEdit && $task->created_by === Auth::id()))
                                    <a href="{{ route('tasks.edit', $task->id) }}" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="Edit Task">
                                        <i class="fas fa-edit w-4 h-4"></i>
                                    </a>
                                    <div class="relative" x-data="{ open: false, loaded: false }" x-effect="if (open && !loaded) { $el.querySelector('.move-to-content').innerHTML = buildMoveToDropdown({{ $task->id }}, {{ $task->task_list_id }}); loaded = true; }">
                                        <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" title="More Options">
                                            <i class="fas fa-ellipsis-v w-4 h-4"></i>
                                        </button>

                                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 top-8 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10 min-w-[250px] max-h-[400px] overflow-y-auto">
                                            <div class="px-3 py-2 text-xs text-gray-500 font-medium border-b border-gray-100 sticky top-0 bg-white">Move to:</div>
                                            <div class="move-to-content">
                                                <div class="px-3 py-4 text-center text-sm text-gray-400">Loading...</div>
                                            </div>
                                            <div class="border-t border-gray-100 mt-1"></div>
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-2"></i>Delete Task
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endif
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
            </div>
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
</div>

<style>
[x-cloak] { display: none !important; }

/* Task Description Preview Styling */
.task-description-preview {
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.task-description-preview p {
    margin-bottom: 0.5em;
}

.task-description-preview p:last-child {
    margin-bottom: 0;
}

.task-description-preview a {
    color: #2563eb;
    text-decoration: underline;
    transition: color 0.2s;
}

.task-description-preview a:hover {
    color: #1d4ed8;
}

.task-description-preview ul,
.task-description-preview ol {
    margin-left: 1em;
    margin-bottom: 0.5em;
}

.task-description-preview li {
    margin-bottom: 0.25em;
}

.task-description-preview h1,
.task-description-preview h2,
.task-description-preview h3,
.task-description-preview h4,
.task-description-preview h5,
.task-description-preview h6 {
    font-weight: 600;
    margin-top: 0.5em;
    margin-bottom: 0.25em;
    font-size: 1em;
}

.task-description-preview strong,
.task-description-preview b {
    font-weight: 600;
}

.task-description-preview em,
.task-description-preview i {
    font-style: italic;
}
</style>
@push('scripts')
<script>
function copyTaskUrl(url, event, element) {
    // Convert relative URL to absolute URL
    const absoluteUrl = url.startsWith('http') ? url : window.location.origin + url;
    
    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(absoluteUrl).then(function() {
            // Success feedback is handled by Alpine.js in the button
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            // Fallback to execCommand
            fallbackCopyText(absoluteUrl);
        });
    } else {
        // Use fallback method for older browsers or non-HTTPS
        fallbackCopyText(absoluteUrl);
    }
    
    function fallbackCopyText(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            const successful = document.execCommand('copy');
            if (!successful) {
                alert('Failed to copy URL. Please try again.');
            }
        } catch (err) {
            console.error('Fallback copy failed:', err);
            alert('Failed to copy URL. Please copy manually: ' + text);
        }
        
        document.body.removeChild(textArea);
    }
}

// ─── Dynamic "Move to" dropdown ────────────────────────────────────────────
// Store all project/tasklist data once instead of rendering it for every task row.
// This is critical for master admin who sees all projects.
const currentProjectId = {{ $project->id }};
@php
    $allProjectsJson = json_encode($allProjects->map(function($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'taskLists' => $p->taskLists->map(function($l) {
                return ['id' => $l->id, 'name' => $l->name, 'color' => $l->color ?? '#6B7280'];
            })->values()->toArray()
        ];
    })->values()->toArray());
@endphp
const allProjectsData = {!! $allProjectsJson !!};

function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(String(text)));
    return div.innerHTML;
}

function buildMoveToDropdown(taskId, currentTaskListId) {
    let html = '';
    allProjectsData.forEach(proj => {
        html += `<div class="border-b border-gray-100 last:border-0">`;
        html += `<div class="px-3 py-2 bg-gray-50 text-xs font-semibold text-gray-700 sticky top-8">${escapeHtml(proj.name)}`;
        if (proj.id === currentProjectId) html += ` <span class="text-blue-600">(Current)</span>`;
        html += `</div>`;
        if (proj.taskLists && proj.taskLists.length > 0) {
            proj.taskLists.forEach(list => {
                const isCurrent = list.id === currentTaskListId;
                const btnClass = isCurrent ? 'text-blue-600 bg-blue-50 font-medium' : 'text-gray-700';
                html += `<button type="button" class="w-full px-6 py-2 text-left text-sm hover:bg-gray-50 ${btnClass}" ${isCurrent ? 'disabled' : ''} onclick="moveTask(${taskId}, ${list.id}, ${proj.id}, ${JSON.stringify(proj.name)}, ${JSON.stringify(list.name)})">`;
                html += `<span class="inline-block w-3 h-3 rounded-full mr-2" style="background-color: ${escapeHtml(list.color || '#6B7280')}"></span>`;
                html += escapeHtml(list.name);
                if (isCurrent) html += ` <span class="ml-2">✓</span>`;
                html += `</button>`;
            });
        } else {
            html += `<div class="px-6 py-2 text-xs text-gray-400 italic">No task lists</div>`;
        }
        html += `</div>`;
    });
    return html;
}

function moveTask(taskId, taskListId, projectId, projectName, listName) {
    if (!confirm(`Move this task to ${projectName} - ${listName}?`)) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ url('/tasks') }}/' + taskId;
    form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}"><input type="hidden" name="_method" value="PUT"><input type="hidden" name="task_list_id" value="${taskListId}"><input type="hidden" name="project_id" value="${projectId}">`;
    document.body.appendChild(form);
    form.submit();
}
// ───────────────────────────────────────────────────────────────────────────

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
    // Only count rows from list view to avoid duplicates (tasks appear in both expanded and list sections)
    const rows = Array.from(document.querySelectorAll('#list-view-container .project-task-row'));
    const countsByList = {};
    
    // Initialize all task list IDs with 0 count
    rows.forEach(row => {
        const listId = row.dataset.tasklistId;
        if (listId && !countsByList[listId]) {
            countsByList[listId] = 0;
        }
    });
    
    // Count only visible rows (not hidden by filters)
    rows.forEach(row => {
        // Only check if row is hidden by display:none (set by filters)
        // Don't check offsetParent as rows might be in hidden Alpine sections
        if (row.style.display === 'none') return;
        
        const listId = row.dataset.tasklistId;
        if (!listId) return;
        
        countsByList[listId] += 1;
    });

    // Update all count elements
    Object.keys(countsByList).forEach(listId => {
        // Update all count elements (including grid view)
        const els = document.querySelectorAll(`[data-tasklist-count="${listId}"]`);
        if (els.length === 0) return;
        const visible = countsByList[listId];
        els.forEach(el => {
        el.textContent = `${visible} ${visible === 1 ? 'task' : 'tasks'}`;
        });
    });
}

function syncApprovedCheckboxes(taskListId) {
    const listCheckbox = document.getElementById(`show_approved_tasks_${taskListId}`);
    const gridCheckbox = document.getElementById(`show_approved_tasks_grid_${taskListId}`);
    const expandedCheckbox = document.getElementById(`show_approved_tasks_expanded_${taskListId}`);
    
    // Sync all checkboxes to match whichever one was changed
    const checkboxes = [listCheckbox, gridCheckbox, expandedCheckbox].filter(cb => cb !== null);
    if (checkboxes.length > 0) {
        const changedCheckbox = checkboxes.find(cb => cb === event?.target);
        if (changedCheckbox) {
            checkboxes.forEach(cb => {
                if (cb !== changedCheckbox) {
                    cb.checked = changedCheckbox.checked;
                }
            });
        }
    }
}

function filterProjectTasks() {
    const q = (document.getElementById('task_name_filter')?.value || '').trim().toLowerCase();
    const startFrom = document.getElementById('task_start_from')?.value || '';
    const startTo = document.getElementById('task_start_to')?.value || '';
    const dueFrom = document.getElementById('task_due_from')?.value || '';
    const dueTo = document.getElementById('task_due_to')?.value || '';
    const assignedTo = document.getElementById('task_assigned_to_filter')?.value || '';
    const taskType = document.getElementById('task_type_filter')?.value || '';
    const taskStatus = document.getElementById('task_status_filter')?.value || '';
    const sortBy = document.getElementById('task_sort_filter')?.value || '';

    // Check if any filters are active
    const hasActiveFilters = q || startFrom || startTo || dueFrom || dueTo || assignedTo || taskType || taskStatus;
    
    // If filters are active, close the expanded section
    if (hasActiveFilters) {
        // Find the Alpine component and set expandedTaskListId to null
        const alpineComponent = document.querySelector('[x-data*="expandedTaskListId"]');
        if (alpineComponent && window.Alpine) {
            try {
                const alpineData = window.Alpine.$data(alpineComponent);
                if (alpineData) {
                    alpineData.expandedTaskListId = null;
                }
            } catch (e) {
                console.warn('Could not close expanded section:', e);
            }
        }
    }

    let rows = Array.from(document.querySelectorAll('.project-task-row'));
    
    // Filter rows
    rows.forEach(row => {
        const title = row.dataset.title || '';
        const startDate = row.dataset.startDate || '';
        const dueDate = row.dataset.dueDate || '';
        const assignedUserId = row.dataset.assignedTo || '';
        const type = row.dataset.taskType || '';
        const status = row.dataset.taskStatus || '';
        const taskListId = row.dataset.tasklistId || '';
        
        // Check if approved tasks should be shown for this task list
        // Only check checkboxes that are currently visible (not hidden by Alpine x-show)
        const checkboxIds = [
            `show_approved_tasks_${taskListId}`,
            `show_approved_tasks_expanded_${taskListId}`,
            `show_approved_tasks_grid_${taskListId}`
        ];
        
        // Find first checkbox that exists – all are kept in sync by syncApprovedCheckboxes()
        let showApprovedCheckbox = null;
        for (const id of checkboxIds) {
            const checkbox = document.getElementById(id);
            if (checkbox) {
                showApprovedCheckbox = checkbox;
                break;
            }
        }
        
        const showApproved = showApprovedCheckbox ? showApprovedCheckbox.checked : false;

        const matchesName = !q || title.includes(q);
        const matchesStart = inRange(startDate, startFrom, startTo);
        const matchesDue = inRange(dueDate, dueFrom, dueTo);
        const matchesAssignedTo = !assignedTo || 
            (assignedTo === 'unassigned' && !assignedUserId) ||
            (assignedTo !== 'unassigned' && assignedUserId === assignedTo);
        const matchesType = !taskType || type === taskType;
        const matchesStatus = !taskStatus || status === taskStatus;
        const matchesApproved = showApproved || status !== 'approved';

        row.style.display = (matchesName && matchesStart && matchesDue && matchesAssignedTo && matchesType && matchesStatus && matchesApproved) ? '' : 'none';
    });

    // Sort visible rows
    if (sortBy) {
        const priorityOrder = { 'urgent': 0, 'high': 1, 'medium': 2, 'low': 3 };
        
        rows.sort((a, b) => {
            if (a.style.display === 'none' && b.style.display === 'none') return 0;
            if (a.style.display === 'none') return 1;
            if (b.style.display === 'none') return -1;

            switch(sortBy) {
                case 'priority':
                    const priorityA = priorityOrder[a.dataset.priority] ?? 999;
                    const priorityB = priorityOrder[b.dataset.priority] ?? 999;
                    return priorityA - priorityB;
                
                case 'alphabetical':
                    return (a.dataset.title || '').localeCompare(b.dataset.title || '');
                
                case 'due_date':
                    const dueA = a.dataset.dueDate || '9999-12-31';
                    const dueB = b.dataset.dueDate || '9999-12-31';
                    return dueA.localeCompare(dueB);
                
                case 'start_date':
                    const startA = a.dataset.startDate || '9999-12-31';
                    const startB = b.dataset.startDate || '9999-12-31';
                    return startA.localeCompare(startB);
                
                default:
                    return 0;
            }
        });

        // Reorder DOM elements within their task lists
        const taskListGroups = {};
        rows.forEach(row => {
            const taskListId = row.dataset.tasklistId;
            const container = row.parentElement;
            if (taskListId && container) {
                if (!taskListGroups[taskListId]) {
                    taskListGroups[taskListId] = { container: container, rows: [] };
                }
                taskListGroups[taskListId].rows.push(row);
            }
        });

        Object.values(taskListGroups).forEach(group => {
            group.rows.forEach(row => group.container.appendChild(row));
        });
    }

    updateTaskListCounts();
    hideEmptyTaskLists();
}

function hideEmptyTaskLists() {
    // Check if any filters are active
    const hasActiveFilters = document.getElementById('task_name_filter')?.value ||
        document.getElementById('task_start_from')?.value ||
        document.getElementById('task_start_to')?.value ||
        document.getElementById('task_due_from')?.value ||
        document.getElementById('task_due_to')?.value ||
        document.getElementById('task_assigned_to_filter')?.value ||
        document.getElementById('task_type_filter')?.value ||
        document.getElementById('task_status_filter')?.value;
    
    // If no filters are active, only clear display on containers hidden by the JS filter
    // (those with data-filtered-out). Do NOT touch others — Alpine.js x-show controls them.
    if (!hasActiveFilters) {
        const filteredOutContainers = document.querySelectorAll('[data-task-list-id][data-filtered-out]');
        filteredOutContainers.forEach(container => {
            container.removeAttribute('data-filtered-out');
            container.style.removeProperty('display');
        });
        return;
    }
    
    // Get all unique task list IDs from task rows
    const taskRows = Array.from(document.querySelectorAll('.project-task-row'));
    const taskListIds = new Set();
    
    taskRows.forEach(row => {
        const taskListId = row.dataset.tasklistId;
        if (taskListId) {
            taskListIds.add(taskListId);
        }
    });
    
    // Check each task list for visible tasks
    taskListIds.forEach(taskListId => {
        // Find all task rows for this task list
        const listTaskRows = Array.from(document.querySelectorAll(`.project-task-row[data-tasklist-id="${taskListId}"]`));
        
        // Count visible tasks (not hidden by display:none)
        const visibleTasks = listTaskRows.filter(row => row.style.display !== 'none');
        
        // Find all task list containers for this task list ID
        const taskListContainers = document.querySelectorAll(`[data-task-list-id="${taskListId}"]`);
        
        taskListContainers.forEach(container => {
            if (visibleTasks.length === 0) {
                // No visible tasks - hide the container by setting display none with !important
                container.style.setProperty('display', 'none', 'important');
                container.setAttribute('data-filtered-out', 'true');
            } else {
                // Has visible tasks - remove the forced hide and let Alpine.js x-show handle it
                container.removeAttribute('data-filtered-out');
                // Remove the display property we set
                container.style.removeProperty('display');
            }
        });
    });
    
    // Also handle task lists that have no tasks at all (not just filtered out)
    // This handles the case where a task list has no tasks in the database
    const allTaskListContainers = document.querySelectorAll('[data-task-list-id]');
    allTaskListContainers.forEach(container => {
        const taskListId = container.getAttribute('data-task-list-id');
        if (!taskListIds.has(taskListId)) {
            // This task list has no tasks at all - hide it when filters are active
            container.style.setProperty('display', 'none', 'important');
            container.setAttribute('data-filtered-out', 'true');
        }
    });
    
    // Note: We don't hide .task-list-expanded divs here - Alpine.js x-show handles them completely
}

function resetProjectTaskFilters() {
    const ids = ['task_name_filter', 'task_start_from', 'task_start_to', 'task_due_from', 'task_due_to', 'task_assigned_to_filter', 'task_type_filter', 'task_status_filter', 'task_sort_filter'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    filterProjectTasks();
}

document.addEventListener('DOMContentLoaded', function () {
    filterProjectTasks(); // Apply filters on load (hides approved tasks by default)
    updateTaskListCounts();
});

function projectCopyLink() {
    return {
        copiedProjectLink: false,
        
        copyProjectLink() {
            const projectUrl = window.location.href;
            
            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(projectUrl).then(() => {
                    this.copiedProjectLink = true;
                    setTimeout(() => {
                        this.copiedProjectLink = false;
                    }, 2000);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                    this.fallbackCopy(projectUrl);
                });
            } else {
                // Fallback for older browsers or non-HTTPS
                this.fallbackCopy(projectUrl);
            }
        },
        
        fallbackCopy(text) {
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-999999px';
            textarea.style.top = '-999999px';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.copiedProjectLink = true;
                    setTimeout(() => {
                        this.copiedProjectLink = false;
                    }, 2000);
                } else {
                    alert('Failed to copy link. Please copy manually: ' + text);
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                alert('Failed to copy link. Please copy manually: ' + text);
            } finally {
                document.body.removeChild(textarea);
            }
        }
    };
}
</script>
@endpush
@endsection
