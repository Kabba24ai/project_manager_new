<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['createdBy.roles', 'projectManager.roles', 'taskLists.tasks.assignedUser.roles', 'taskLists.tasks.creator.roles'])
            ->where(function ($q) {
                $q->where('created_by', Auth::id())
                    ->orWhere('project_manager_id', Auth::id())
                    ->orWhereHas('teamMembers', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
            });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->latest()->get();

        // Add computed fields
        $projects = $projects->map(function ($project) {
            $project->tasks_count = $project->taskLists->sum(function ($list) {
                return $list->tasks->count();
            });
            $project->completed_tasks = $project->taskLists->sum(function ($list) {
                return $list->tasks->where('task_status', 'approved')->count();
            });
            $project->progress_percentage = $project->tasks_count > 0
                ? round(($project->completed_tasks / $project->tasks_count) * 100)
                : 0;
            return $project;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'data' => ['projects' => $projects],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $projects->count(),
                    'total' => $projects->count(),
                ],
            ]);
        }

        return view('projects.index', compact('projects'));
    }

    public function show($id)
    {
        $project = Project::with([
            'createdBy',
            'projectManager',
            'teamMembers',
            'taskLists.tasks.assignedUser',
            'taskLists.tasks.creator',
            'taskLists.tasks.comments'
        ])->findOrFail($id);

        // Check if user has access to this project
        $hasAccess = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        if (request()->expectsJson()) {
            return response()->json(['data' => ['project' => $project]]);
        }

        return view('projects.show', compact('project'));
    }

    public function create()
    {
        $managers = User::role(['admin', 'manager'])->with('roles')->get();
        $users = User::with('roles')->get();

        return view('projects.create', compact('managers', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|in:active,planning,completed,on-hold,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'budget' => 'nullable|numeric',
            'client' => 'nullable|string|max:255',
            'project_manager_id' => 'nullable|exists:users,id',
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        ]);

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'priority' => $request->priority ?? 'medium',
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'budget' => $request->budget,
            'client' => $request->client,
            'created_by' => Auth::id(),
            'project_manager_id' => $request->project_manager_id ?? Auth::id(),
        ]);

        // Sync team members if provided
        if ($request->has('team_members')) {
            $project->teamMembers()->sync($request->team_members);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => ['project' => $project]], 201);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function edit($id)
    {
        $project = Project::with('teamMembers')->findOrFail($id);

        // Check if user can edit this project
        $canEdit = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id();

        if (!$canEdit) {
            abort(403);
        }

        $managers = User::role(['admin', 'manager'])->with('roles')->get();
        $users = User::with('roles')->get();

        return view('projects.edit', compact('project', 'managers', 'users'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Check if user can update this project
        $canUpdate = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id();

        if (!$canUpdate) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|in:active,planning,completed,on-hold,cancelled',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'start_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'budget' => 'nullable|numeric',
            'client' => 'nullable|string|max:255',
            'project_manager_id' => 'nullable|exists:users,id',
        ]);

        $project->update($request->only([
            'name',
            'description',
            'status',
            'priority',
            'start_date',
            'due_date',
            'budget',
            'client',
            'project_manager_id',
        ]));

        // Sync team members if provided
        if ($request->has('team_members')) {
            $project->teamMembers()->sync($request->team_members);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => ['project' => $project]]);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Only creator can delete
        if ($project->created_by !== Auth::id()) {
            abort(403);
        }

        $project->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Project deleted successfully']);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
