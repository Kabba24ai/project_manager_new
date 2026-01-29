<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskListController extends Controller
{
    public function index($projectId)
    {
        $project = Project::findOrFail($projectId);

        // Check if user has access to this project
        $user = Auth::user();
        $userRoles = $user->roles ?? collect();
        $userRoleNames = $userRoles->pluck('name')->map(function($name) {
            return strtolower(str_replace([' ', '_', '-'], '', $name));
        })->toArray();
        $isMasterAdmin = in_array('masteradmin', $userRoleNames);
        
        $hasAccess = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        $taskLists = TaskList::with(['tasks.assignedUser'])
            ->where('project_id', $projectId)
            ->orderBy('order')
            ->get();

        if (request()->expectsJson()) {
            return response()->json(['data' => ['task_lists' => $taskLists]]);
        }

        return view('task-lists.index', compact('project', 'taskLists'));
    }

    public function create($projectId)
    {
        $project = Project::findOrFail($projectId);

        // Check if user has access to this project
        $user = Auth::user();
        $userRoles = $user->roles ?? collect();
        $userRoleNames = $userRoles->pluck('name')->map(function($name) {
            return strtolower(str_replace([' ', '_', '-'], '', $name));
        })->toArray();
        $isMasterAdmin = in_array('masteradmin', $userRoleNames);
        
        $hasAccess = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        return view('task-lists.create', compact('project'));
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        // Check if user can create task lists
        $user = Auth::user();
        $userRoles = $user->roles ?? collect();
        $userRoleNames = $userRoles->pluck('name')->map(function($name) {
            return strtolower(str_replace([' ', '_', '-'], '', $name));
        })->toArray();
        $isMasterAdmin = in_array('masteradmin', $userRoleNames);
        
        $hasAccess = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $taskList = TaskList::create([
            'project_id' => $projectId,
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? 'bg-gray-100',
            'order' => $request->order ?? 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task_list' => $taskList]], 201);
        }

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task list created successfully.');
    }

    public function show($projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        $taskList = TaskList::with(['tasks.assignedUser'])
            ->where('project_id', $projectId)
            ->findOrFail($id);

        // Check if user has access to this project
        $user = Auth::user();
        $userRoles = $user->roles ?? collect();
        $userRoleNames = $userRoles->pluck('name')->map(function($name) {
            return strtolower(str_replace([' ', '_', '-'], '', $name));
        })->toArray();
        $isMasterAdmin = in_array('masteradmin', $userRoleNames);
        
        $hasAccess = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        if (request()->expectsJson()) {
            return response()->json(['data' => ['task_list' => $taskList]]);
        }

        return view('task-lists.show', compact('project', 'taskList'));
    }

    public function edit($projectId, $id)
    {
        $project = Project::findOrFail($projectId);
        $taskList = TaskList::with('tasks')
            ->where('project_id', $projectId)
            ->findOrFail($id);

        // Check if user has access to this project
        $user = Auth::user();
        $userRoles = $user->roles ?? collect();
        $userRoleNames = $userRoles->pluck('name')->map(function($name) {
            return strtolower(str_replace([' ', '_', '-'], '', $name));
        })->toArray();
        $isMasterAdmin = in_array('masteradmin', $userRoleNames);
        
        $hasAccess = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        return view('task-lists.edit', compact('project', 'taskList'));
    }

    public function update(Request $request, $projectId, $id)
    {
        $taskList = TaskList::where('project_id', $projectId)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'order' => 'nullable|integer',
        ]);

        $taskList->update($request->only(['name', 'description', 'color', 'order']));

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task_list' => $taskList]]);
        }

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task list updated successfully.');
    }

    public function destroy($projectId, $id)
    {
        $taskList = TaskList::where('project_id', $projectId)->findOrFail($id);
        $taskList->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Task list deleted successfully']);
        }

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task list deleted successfully.');
    }
}
