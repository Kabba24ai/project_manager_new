<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SprintController extends Controller
{
    private function isMasterAdmin(): bool
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('name')->map(fn($r) => strtolower($r))->all();
        return in_array('master admin', $roles) || in_array('master_admin', $roles);
    }

    public function index()
    {
        $sprints = Sprint::withCount('tasks')
            ->with('tasks')
            ->orderByRaw("FIELD(status, 'active', 'planning', 'completed')")
            ->orderBy('start_date')
            ->get();

        return view('sprints.index', compact('sprints'));
    }

    public function create()
    {
        return view('sprints.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'goal'       => 'nullable|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'status'     => 'nullable|in:planning,active,completed',
        ]);

        $sprint = Sprint::create([
            'name'       => $validated['name'],
            'goal'       => $validated['goal'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date'   => $validated['end_date'],
            'status'     => $validated['status'] ?? 'planning',
            'project_id' => null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('sprints.show', $sprint->id)
            ->with('success', 'Sprint created successfully.');
    }

    public function show(Sprint $sprint)
    {
        $sprint->load(['tasks.assignedUser', 'tasks.taskList', 'tasks.project']);

        // Tasks already in this sprint (to exclude from tree)
        $sprintTaskIds = $sprint->tasks->pluck('id')->all();

        // Projects with task lists and their tasks NOT in this sprint
        $user = Auth::user();
        $taskQuery = function ($q) use ($sprintTaskIds) {
            $q->whereNotIn('id', $sprintTaskIds)->orderBy('title');
        };

        if ($this->isMasterAdmin()) {
            $projects = Project::with(['taskLists.tasks' => $taskQuery])
                ->orderBy('name')
                ->get();
        } else {
            $projects = Project::with(['taskLists.tasks' => $taskQuery])
                ->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhere('project_manager_id', $user->id)
                      ->orWhereHas('teamMembers', fn($sq) => $sq->where('users.id', $user->id));
                })
                ->orderBy('name')
                ->get();
        }

        return view('sprints.show', compact('sprint', 'projects'));
    }

    public function edit(Sprint $sprint)
    {
        return view('sprints.edit', compact('sprint'));
    }

    public function update(Request $request, Sprint $sprint)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'goal'       => 'nullable|string',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'status'     => 'required|in:planning,active,completed',
        ]);

        $sprint->update($validated);

        return redirect()->route('sprints.show', $sprint->id)
            ->with('success', 'Sprint updated successfully.');
    }

    public function destroy(Sprint $sprint)
    {
        // Remove sprint assignment from tasks
        Task::where('sprint_id', $sprint->id)->update(['sprint_id' => null]);

        $sprint->delete();

        return redirect()->route('sprints.index')
            ->with('success', 'Sprint deleted successfully.');
    }

    /**
     * Add a task to the sprint.
     */
    public function addTask(Request $request, Sprint $sprint)
    {
        $request->validate(['task_id' => 'required|exists:tasks,id']);

        $task = Task::findOrFail($request->task_id);
        $task->update(['sprint_id' => $sprint->id]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Task added to sprint.']);
        }

        return back()->with('success', 'Task added to sprint.');
    }

    /**
     * Remove a task from the sprint (back to backlog).
     */
    public function removeTask(Request $request, Sprint $sprint, Task $task)
    {
        if ($task->sprint_id !== $sprint->id) {
            abort(404);
        }

        $task->update(['sprint_id' => null]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Task removed from sprint.']);
        }

        return back()->with('success', 'Task moved back to backlog.');
    }
}
