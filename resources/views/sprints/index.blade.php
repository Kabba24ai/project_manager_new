@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Sprints')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    <h1 class="text-xl font-bold text-gray-900">Sprints</h1>
                </div>
                <a href="{{ route('sprints.create') }}"
                   class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    <i class="fas fa-plus"></i>
                    <span>New Sprint</span>
                </a>
            </div>
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

        @if($sprints->isEmpty())
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm py-20 text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-running text-2xl text-purple-500"></i>
            </div>
            <p class="text-gray-700 font-semibold text-lg">No sprints yet</p>
            <p class="text-gray-400 text-sm mt-1 mb-6">Create a sprint and assign tasks to it</p>
            <a href="{{ route('sprints.create') }}"
               class="inline-flex items-center space-x-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-plus"></i>
                <span>Create First Sprint</span>
            </a>
        </div>
        @else

        <div class="space-y-4">
            @foreach($sprints as $sprint)
            @php
                $statusColor = match($sprint->status) {
                    'active'    => 'bg-green-100 text-green-800 border-green-200',
                    'completed' => 'bg-blue-100 text-blue-800 border-blue-200',
                    default     => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                };
                $taskCount  = $sprint->tasks_count;
                $doneCount  = $sprint->tasks->where('task_status', 'approved')->count();
                $progress   = $taskCount > 0 ? round(($doneCount / $taskCount) * 100) : 0;
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="px-6 py-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3 mb-1">
                                <a href="{{ route('sprints.show', $sprint->id) }}"
                                   class="text-base font-semibold text-gray-900 hover:text-blue-600 transition-colors">
                                    {{ $sprint->name }}
                                </a>
                                <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $statusColor }}">
                                    {{ ucfirst($sprint->status) }}
                                </span>
                            </div>
                            @if($sprint->goal)
                            <p class="text-sm text-gray-500 mb-3">{{ $sprint->goal }}</p>
                            @endif
                            <div class="flex items-center space-x-5 text-xs text-gray-500">
                                <span><i class="fas fa-calendar-alt mr-1"></i>{{ $sprint->start_date->format('M d') }} – {{ $sprint->end_date->format('M d, Y') }}</span>
                                <span><i class="fas fa-tasks mr-1"></i>{{ $taskCount }} {{ Str::plural('task', $taskCount) }}</span>
                                <span><i class="fas fa-check-circle mr-1 text-green-500"></i>{{ $doneCount }} done</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                            <a href="{{ route('sprints.show', $sprint->id) }}"
                               class="px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                            <a href="{{ route('sprints.edit', $sprint->id) }}"
                               class="px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                            <form action="{{ route('sprints.destroy', $sprint->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this sprint? Tasks will be moved back to backlog.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors font-medium">
                                    <i class="fas fa-trash-alt mr-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    @if($taskCount > 0)
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-2 rounded-full {{ $sprint->status === 'completed' ? 'bg-blue-500' : 'bg-green-500' }} transition-all"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @endif
    </div>
</div>
@endsection
