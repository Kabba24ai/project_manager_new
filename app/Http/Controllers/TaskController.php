<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function create($projectId)
    {
        $project = Project::with('taskLists')->findOrFail($projectId);

        // Check if user has access to this project
        $hasAccess = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        $users = User::with('roles')->get();
        $taskListId = request('task_list_id');
        
        // Load equipment categories with their equipment
        $equipmentCategories = \App\Models\ProductCategory::with(['equipment' => function ($query) {
            $query->orderBy('equipment_name');
        }])->orderBy('title')->get();
        
        // Transform to match the expected structure for the frontend
        $equipmentCategories = $equipmentCategories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->title,
                'equipment' => $category->equipment->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->equipment_name,
                        'available' => $item->current_status === 'available',
                    ];
                })
            ];
        });
        
        // Load customers
        $customers = \App\Models\Customer::where('status', 'Active')
            ->orderBy('company_name')
            ->orderBy('first_name')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->full_name,
                    'email' => $customer->email ?? '',
                    'phone' => $customer->phone ?? $customer->company_phone ?? '',
                ];
            });

        return view('tasks.create', compact('project', 'users', 'taskListId', 'equipmentCategories', 'customers'));
    }

    public function store(Request $request, $taskListId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'task_type' => 'nullable|in:general,equipmentId,customerName,feature,bug,design',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
            'tags' => 'nullable|array',
            'task_list_id' => 'nullable|exists:task_lists,id',
        ]);

        // Use task_list_id from request body if provided, otherwise use URL parameter
        $actualTaskListId = $request->task_list_id ?? $taskListId;
        $taskList = TaskList::with('project')->findOrFail($actualTaskListId);

        $task = Task::create([
            'project_id' => $taskList->project_id,
            'task_list_id' => $actualTaskListId,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority ?? 'medium',
            'task_type' => $request->task_type ?? 'general',
            'assigned_to' => $request->assigned_to,
            'created_by' => Auth::id(),
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'estimated_hours' => $request->estimated_hours,
            'tags' => $request->tags,
        ]);

        $task->load('assignedUser');

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task' => $task]], 201);
        }

        return redirect()->route('projects.show', $taskList->project_id)
            ->with('success', 'Task created successfully.');
    }

    public function show($id)
    {
        $task = Task::with(['assignedUser', 'creator', 'taskList', 'project', 'comments.author'])->findOrFail($id);

        // Check if user has access to this task's project
        $project = $task->project;
        $hasAccess = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        if (request()->expectsJson()) {
            return response()->json(['data' => ['task' => $task]]);
        }

        return view('tasks.show', compact('task'));
    }

    public function edit($id)
    {
        $task = Task::with(['taskList.project'])->findOrFail($id);

        // Check if user has access to this task's project
        $project = $task->project;
        $hasAccess = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        $users = User::with('roles')->get();
        $project = Project::with('taskLists')->findOrFail($task->project_id);

        return view('tasks.edit', compact('task', 'users', 'project'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'task_type' => 'nullable|in:general,equipmentId,customerName,feature,bug,design',
            'task_status' => 'nullable|in:pending,in_progress,completed_pending_review,completed_approved,unapproved,approved,deployed',
            'assigned_to' => 'nullable|exists:users,id',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
            'actual_hours' => 'nullable|numeric',
            'tags' => 'nullable|array',
            'feedback' => 'nullable|string',
            'task_list_id' => 'nullable|exists:task_lists,id',
        ]);

        $task->update($request->only([
            'title',
            'description',
            'priority',
            'task_type',
            'task_status',
            'assigned_to',
            'start_date',
            'due_date',
            'estimated_hours',
            'actual_hours',
            'tags',
            'feedback',
            'task_list_id',
        ]));

        $task->load('assignedUser');

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task' => $task]]);
        }

        return redirect()->back()->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed_pending_review,completed_approved,unapproved,approved,deployed',
        ]);

        $task->update(['task_status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task' => $task]]);
        }

        return redirect()->back()->with('success', 'Task status updated successfully.');
    }

    public function move(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'task_list_id' => 'required|exists:task_lists,id',
        ]);

        $task->update(['task_list_id' => $request->task_list_id]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task' => $task]]);
        }

        return redirect()->back()->with('success', 'Task moved successfully.');
    }

    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $projectId = $task->project_id;
        $task->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Task deleted successfully']);
        }

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task deleted successfully.');
    }
}
