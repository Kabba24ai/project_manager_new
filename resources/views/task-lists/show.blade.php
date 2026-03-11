@extends('layouts.dashboard')

@section('title', 'Proj Mgr - ' . $taskList->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('projects.show', $project->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Project</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <span class="text-gray-500 text-sm">{{ $project->name }}</span>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full inline-block {{ $taskList->color ?? 'bg-blue-100' }}"></span>
                        <h1 class="text-xl font-bold text-gray-900">{{ $taskList->name }}</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('task-lists.edit', [$project->id, $taskList->id]) }}"
                       class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                        <i class="fas fa-edit"></i>
                        <span>Edit List</span>
                    </a>
                    <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}"
                       class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-plus"></i>
                        <span>Add Task</span>
                    </a>
                </div>
            </div>
            @if($taskList->description)
            <p class="mt-2 text-sm text-gray-500 ml-0">{{ $taskList->description }}</p>
            @endif
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-6xl mx-auto px-6 py-8">

        @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center space-x-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-green-700 text-sm">{{ session('success') }}</span>
        </div>
        @endif

        <!-- Task list -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Tasks</h2>
                <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}"
                   class="flex items-center space-x-1 text-sm text-blue-600 hover:text-blue-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i>
                    <span>Add Task</span>
                </a>
            </div>

            @if($taskList->tasks->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tasks text-2xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium">No tasks yet</p>
                <p class="text-gray-400 text-sm mt-1">Add your first task to get started</p>
                <a href="{{ route('tasks.create', $project->id) }}?task_list_id={{ $taskList->id }}"
                   class="inline-flex items-center space-x-2 mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    <i class="fas fa-plus"></i>
                    <span>Add First Task</span>
                </a>
            </div>
            @else
            <ul class="divide-y divide-gray-100">
                @foreach($taskList->tasks as $task)
                <li class="px-6 py-4 hover:bg-gray-50 transition-colors">
                    <a href="{{ route('tasks.show', $task->id) }}" class="flex items-start space-x-4 group">
                        <!-- Status indicator -->
                        <div class="mt-1 flex-shrink-0">
                            @php
                                $statusColor = match($task->status ?? 'pending') {
                                    'approved'    => 'bg-green-500',
                                    'in_progress' => 'bg-blue-500',
                                    'review'      => 'bg-yellow-500',
                                    'rejected'    => 'bg-red-500',
                                    default       => 'bg-gray-300',
                                };
                            @endphp
                            <span class="w-2.5 h-2.5 rounded-full inline-block {{ $statusColor }}"></span>
                        </div>

                        <!-- Task info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors truncate">
                                    {{ $task->title }}
                                </p>
                                @if($task->priority)
                                @php
                                    $priorityClass = match($task->priority) {
                                        'urgent' => 'bg-red-100 text-red-700',
                                        'high'   => 'bg-orange-100 text-orange-700',
                                        'medium' => 'bg-yellow-100 text-yellow-700',
                                        default  => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="flex-shrink-0 text-xs px-2 py-0.5 rounded-full {{ $priorityClass }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-4 mt-1">
                                @if($task->assignedUser)
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-user mr-1"></i>{{ $task->assignedUser->first_name }} {{ $task->assignedUser->last_name }}
                                </span>
                                @endif
                                @if($task->due_date)
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}
                                </span>
                                @endif
                                <span class="text-xs capitalize px-2 py-0.5 rounded bg-gray-100 text-gray-600">
                                    {{ str_replace('_', ' ', $task->status ?? 'pending') }}
                                </span>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <div class="flex-shrink-0 text-gray-300 group-hover:text-blue-400 transition-colors mt-1">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection
