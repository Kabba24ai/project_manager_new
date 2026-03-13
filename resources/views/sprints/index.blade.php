@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Sprints')

@section('content')
@php
    $totalSprints     = $sprints->count();
    $activeSprints    = $sprints->where('status', 'active')->count();
    $planningSprints  = $sprints->where('status', 'planning')->count();
    $completedSprints = $sprints->where('status', 'completed')->count();
@endphp

<div class="min-h-screen bg-gray-100"
     x-data="{
        viewMode: localStorage.getItem('sprintsViewMode') || 'grid',
        showFilters: false,
        setView(v) { this.viewMode = v; localStorage.setItem('sprintsViewMode', v); }
     }">

    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-1.5 text-gray-500 hover:text-gray-800 text-sm transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Dashboard</span>
                </a>
                <div class="h-5 w-px bg-gray-300"></div>
                <h1 class="text-xl font-bold text-gray-900">Sprints</h1>
            </div>
            <a href="{{ route('sprints.create') }}"
               class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-plus"></i>
                <span>New Sprint</span>
            </a>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-2.5 flex items-center space-x-5 text-sm">
            <span class="flex items-center space-x-1.5 text-gray-600">
                <i class="fas fa-running text-gray-400"></i>
                <span><strong class="text-gray-900">{{ $totalSprints }}</strong> {{ Str::plural('sprint', $totalSprints) }}</span>
            </span>
            <span class="flex items-center space-x-1.5 text-gray-600">
                <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                <span><strong class="text-green-600">{{ $activeSprints }}</strong> active</span>
            </span>
            <span class="flex items-center space-x-1.5 text-gray-600">
                <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                <span><strong class="text-yellow-600">{{ $planningSprints }}</strong> planning</span>
            </span>
            <span class="flex items-center space-x-1.5 text-gray-600">
                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                <span><strong class="text-blue-600">{{ $completedSprints }}</strong> completed</span>
            </span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-6 space-y-4">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center space-x-3">
            <i class="fas fa-check-circle text-green-500"></i>
            <span class="text-green-700 text-sm">{{ session('success') }}</span>
        </div>
        @endif

        @if($sprints->isEmpty())
        <!-- Empty state -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm py-20 text-center">
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

        <!-- Controls Bar -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3 flex items-center justify-between flex-wrap gap-3">
            <!-- Left: Hide Filters toggle -->
            <button @click="showFilters = !showFilters"
                    class="flex items-center space-x-2 px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                <i class="fas fa-filter text-xs"></i>
                <span x-text="showFilters ? 'Hide Filters' : 'Show Filters'"></span>
                <i class="fas text-xs" :class="showFilters ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>

            <!-- Right: Hide Completed + View toggles -->
            <div class="flex items-center space-x-3">
                <label class="flex items-center space-x-2 cursor-pointer select-none">
                    <input type="checkbox" id="hideCompleted" onchange="filterSprints()" checked
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-600 font-medium">Hide Completed</span>
                </label>

                <!-- Grid view -->
                <button @click="setView('grid')"
                        :class="viewMode === 'grid' ? 'bg-slate-700 text-white hover:bg-slate-800' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-grid w-5 h-5">
                        <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                        <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                        <rect width="7" height="7" x="3" y="14" rx="1"></rect>
                    </svg>
                    <span>Grid View</span>
                </button>

                <!-- List view -->
                <button @click="setView('list')"
                        :class="viewMode === 'list' ? 'bg-slate-700 text-white hover:bg-slate-800' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg transition-colors">
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

        <!-- Expandable Filter Panel -->
        <div x-show="showFilters"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <!-- Sprint Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sprint Name</label>
                    <input type="text" id="sprintSearch" placeholder="Search sprints..."
                           oninput="filterSprints()"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                    <select id="statusFilter" onchange="filterSprints()"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                        <option value="all">All Status</option>
                        <option value="planning">Planning</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <!-- Start Date Range -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Start Date</label>
                    <div class="flex items-center space-x-2">
                        <input type="date" id="dateFrom" onchange="filterSprints()"
                               class="w-full px-2 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                        <span class="text-gray-400 text-xs flex-shrink-0">to</span>
                        <input type="date" id="dateTo" onchange="filterSprints()"
                               class="w-full px-2 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                    </div>
                </div>

                <!-- Sort By + Reset -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sort By</label>
                    <div class="flex items-center space-x-2">
                        <select id="sortOrder" onchange="filterSprints()"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                            <option value="default">Default</option>
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name_asc">Name A–Z</option>
                            <option value="name_desc">Name Z–A</option>
                        </select>
                        <button onclick="resetFilters()"
                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors font-medium whitespace-nowrap">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- No results -->
        <div id="noResults" class="hidden bg-white rounded-xl border border-gray-200 shadow-sm py-12 text-center">
            <i class="fas fa-search text-gray-300 text-4xl mb-3"></i>
            <p class="text-gray-500 text-sm">No sprints match your filters.</p>
        </div>

        <!-- Grid View -->
        <div id="sprintsGrid" x-show="viewMode === 'grid'"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sprint-container">
            @foreach($sprints as $sprint)
            @php
                $statusColor = match($sprint->status) {
                    'active'    => 'bg-green-100 text-green-800',
                    'completed' => 'bg-blue-100 text-blue-800',
                    default     => 'bg-yellow-100 text-yellow-800',
                };
                $statusStripe = match($sprint->status) {
                    'active'    => 'bg-green-500',
                    'completed' => 'bg-blue-500',
                    default     => 'bg-yellow-400',
                };
                $taskCount = $sprint->tasks_count;
                $doneCount = $sprint->tasks->where('task_status', 'approved')->count();
                $progress  = $taskCount > 0 ? round(($doneCount / $taskCount) * 100) : 0;
            @endphp
            <div class="sprint-card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden"
                 data-name="{{ strtolower($sprint->name) }}"
                 data-status="{{ $sprint->status }}"
                 data-date="{{ $sprint->start_date->timestamp }}">
                <!-- Colored top stripe -->
                <div class="h-1.5 {{ $statusStripe }}"></div>
                <div class="p-5">
                    <!-- Name + status badge -->
                    <div class="flex items-start justify-between mb-2">
                        <a href="{{ route('sprints.show', $sprint->id) }}"
                           class="text-base font-bold text-gray-900 hover:text-blue-600 transition-colors leading-snug flex-1 mr-3">
                            {{ $sprint->name }}
                        </a>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold flex-shrink-0 {{ $statusColor }}">
                            {{ ucfirst($sprint->status) }}
                        </span>
                    </div>

                    @if($sprint->goal)
                    <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $sprint->goal }}</p>
                    @endif

                    <!-- Meta -->
                    <div class="flex items-center space-x-4 text-xs text-gray-500 mb-4">
                        <span><i class="fas fa-calendar-alt mr-1"></i>{{ $sprint->start_date->format('M d') }} – {{ $sprint->end_date->format('M d, Y') }}</span>
                        <span><i class="fas fa-tasks mr-1"></i>{{ $taskCount }} {{ Str::plural('task', $taskCount) }}</span>
                    </div>

                    <!-- Progress -->
                    @if($taskCount > 0)
                    <div class="mb-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $doneCount }} / {{ $taskCount }} done</span>
                            <span>{{ $progress }}%</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-1.5 rounded-full {{ $sprint->status === 'completed' ? 'bg-blue-500' : 'bg-green-500' }}"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-gray-400 italic mb-4">No tasks yet</p>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 pt-3 border-t border-gray-100">
                        <a href="{{ route('sprints.show', $sprint->id) }}"
                           class="flex items-center space-x-1.5 px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium">
                            <i class="fas fa-eye"></i><span>View</span>
                        </a>
                        <a href="{{ route('sprints.edit', $sprint->id) }}"
                           class="flex items-center space-x-1.5 px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                            <i class="fas fa-edit"></i><span>Edit</span>
                        </a>
                        <form action="{{ route('sprints.destroy', $sprint->id) }}" method="POST"
                              onsubmit="return confirm('Delete this sprint?')" class="ml-auto">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="flex items-center space-x-1.5 px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors font-medium">
                                <i class="fas fa-trash-alt"></i><span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- List View -->
        <div id="sprintsList" x-show="viewMode === 'list'" x-cloak
             class="space-y-3 sprint-container">
            @foreach($sprints as $sprint)
            @php
                $statusColor = match($sprint->status) {
                    'active'    => 'bg-green-100 text-green-800',
                    'completed' => 'bg-blue-100 text-blue-800',
                    default     => 'bg-yellow-100 text-yellow-800',
                };
                $statusStripe = match($sprint->status) {
                    'active'    => 'bg-green-500',
                    'completed' => 'bg-blue-500',
                    default     => 'bg-yellow-400',
                };
                $taskCount = $sprint->tasks_count;
                $doneCount = $sprint->tasks->where('task_status', 'approved')->count();
                $progress  = $taskCount > 0 ? round(($doneCount / $taskCount) * 100) : 0;
            @endphp
            <div class="sprint-card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow overflow-hidden"
                 data-name="{{ strtolower($sprint->name) }}"
                 data-status="{{ $sprint->status }}"
                 data-date="{{ $sprint->start_date->timestamp }}">
                <div class="flex">
                    <div class="w-1.5 flex-shrink-0 {{ $statusStripe }}"></div>
                    <div class="flex-1 px-5 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-1">
                                    <a href="{{ route('sprints.show', $sprint->id) }}"
                                       class="text-base font-bold text-gray-900 hover:text-blue-600 transition-colors">
                                        {{ $sprint->name }}
                                    </a>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $statusColor }}">
                                        {{ ucfirst($sprint->status) }}
                                    </span>
                                </div>
                                @if($sprint->goal)
                                <p class="text-sm text-gray-500 mb-2">{{ $sprint->goal }}</p>
                                @endif
                                <div class="flex items-center space-x-5 text-xs text-gray-500">
                                    <span><i class="fas fa-calendar-alt mr-1"></i>{{ $sprint->start_date->format('M d') }} – {{ $sprint->end_date->format('M d, Y') }}</span>
                                    <span><i class="fas fa-tasks mr-1"></i>{{ $taskCount }} {{ Str::plural('task', $taskCount) }}</span>
                                    <span><i class="fas fa-check-circle mr-1 text-green-500"></i>{{ $doneCount }} done</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4 flex-shrink-0">
                                <a href="{{ route('sprints.show', $sprint->id) }}"
                                   class="flex items-center space-x-1.5 px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors font-medium">
                                    <i class="fas fa-eye"></i><span>View</span>
                                </a>
                                <a href="{{ route('sprints.edit', $sprint->id) }}"
                                   class="flex items-center space-x-1.5 px-3 py-1.5 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                                    <i class="fas fa-edit"></i><span>Edit</span>
                                </a>
                                <form action="{{ route('sprints.destroy', $sprint->id) }}" method="POST"
                                      onsubmit="return confirm('Delete this sprint?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="flex items-center space-x-1.5 px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors font-medium">
                                        <i class="fas fa-trash-alt"></i><span>Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if($taskCount > 0)
                        <div class="mt-3">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span>{{ $progress }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-1.5 rounded-full {{ $sprint->status === 'completed' ? 'bg-blue-500' : 'bg-green-500' }}"
                                     style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @endif
    </div>
</div>

@push('scripts')
<script>
function filterSprints() {
    const search     = (document.getElementById('sprintSearch')?.value ?? '').toLowerCase();
    const status     = document.getElementById('statusFilter')?.value ?? 'all';
    const sort       = document.getElementById('sortOrder')?.value ?? 'default';
    const hideComp   = document.getElementById('hideCompleted')?.checked ?? false;
    const dateFrom   = document.getElementById('dateFrom')?.value ?? '';
    const dateTo     = document.getElementById('dateTo')?.value ?? '';

    const fromTs = dateFrom ? new Date(dateFrom).getTime() / 1000 : null;
    const toTs   = dateTo   ? new Date(dateTo).getTime()   / 1000 : null;

    const allCards = Array.from(document.querySelectorAll('.sprint-card'));
    let visible = 0;

    allCards.forEach(card => {
        const name      = card.dataset.name ?? '';
        const cardStatus = card.dataset.status ?? '';
        const cardDate   = parseInt(card.dataset.date ?? 0);

        const matchSearch = name.includes(search);
        const matchStatus = status === 'all' || cardStatus === status;
        const matchHide   = !hideComp || cardStatus !== 'completed';
        const matchFrom   = !fromTs || cardDate >= fromTs;
        const matchTo     = !toTs   || cardDate <= toTs;

        if (matchSearch && matchStatus && matchHide && matchFrom && matchTo) {
            card.style.display = '';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    // Sorting
    document.querySelectorAll('.sprint-container').forEach(container => {
        const cards = Array.from(container.querySelectorAll('.sprint-card'));
        cards.sort((a, b) => {
            if (sort === 'newest')    return parseInt(b.dataset.date) - parseInt(a.dataset.date);
            if (sort === 'oldest')    return parseInt(a.dataset.date) - parseInt(b.dataset.date);
            if (sort === 'name_asc')  return a.dataset.name.localeCompare(b.dataset.name);
            if (sort === 'name_desc') return b.dataset.name.localeCompare(a.dataset.name);
            return 0;
        });
        cards.forEach(c => container.appendChild(c));
    });

    const noResults = document.getElementById('noResults');
    if (noResults) noResults.classList.toggle('hidden', visible > 0);
}

function resetFilters() {
    const el = id => document.getElementById(id);
    if (el('sprintSearch'))  el('sprintSearch').value  = '';
    if (el('statusFilter'))  el('statusFilter').value  = 'all';
    if (el('sortOrder'))     el('sortOrder').value     = 'default';
    if (el('dateFrom'))      el('dateFrom').value      = '';
    if (el('dateTo'))        el('dateTo').value        = '';
    if (el('hideCompleted')) el('hideCompleted').checked = false;
    filterSprints();
}

document.addEventListener('DOMContentLoaded', () => filterSprints());
</script>
@endpush
@endsection
