<?php

namespace App\Http\Controllers;

use App\Models\TaskListTemplate;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskListTemplateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRoles = $user?->roles ?? collect();
        $userRoleNames = $userRoles->pluck('name')->map(function ($name) {
            return strtolower(str_replace([' ', '_', '-'], '', $name));
        })->toArray();
        $isMasterAdmin = in_array('masteradmin', $userRoleNames);

        $projectsQuery = Project::with(['taskLists.tasks'])
            ->orderBy('name');

        if (!$isMasterAdmin) {
            $projectsQuery->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('project_manager_id', $user->id)
                  ->orWhereHas('teamMembers', fn($sq) => $sq->where('users.id', $user->id));
            });
        }

        $projects = $projectsQuery->get();

        $templates = TaskListTemplate::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('task-list-templates.index', compact('templates', 'projects'));
    }

    public function getTemplatesApi()
    {
        $templates = TaskListTemplate::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:50',
            'tasks'       => 'nullable|array',
            'tasks.*.title'           => 'required|string|max:255',
            'tasks.*.priority'        => 'nullable|string|in:low,medium,high,urgent',
            'tasks.*.description'     => 'nullable|string|max:5000',
            'tasks.*.estimated_hours' => 'nullable|numeric|min:0|max:9999',
            'tasks.*.task_type'       => 'nullable|string|in:general,equipmentId,customerName',
        ]);

        $template = TaskListTemplate::create([
            'user_id'     => Auth::id(),
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? 'bg-blue-100',
            'tasks'       => $request->tasks ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['template' => $template]], 201);
        }

        return redirect()->route('task-list-templates.index')
            ->with('success', 'Task list template created successfully.');
    }

    public function update(Request $request, TaskListTemplate $taskListTemplate)
    {
        if ($taskListTemplate->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:50',
            'tasks'       => 'nullable|array',
            'tasks.*.title'           => 'required|string|max:255',
            'tasks.*.priority'        => 'nullable|string|in:low,medium,high,urgent',
            'tasks.*.description'     => 'nullable|string|max:5000',
            'tasks.*.estimated_hours' => 'nullable|numeric|min:0|max:9999',
            'tasks.*.task_type'       => 'nullable|string|in:general,equipmentId,customerName',
        ]);

        $taskListTemplate->update([
            'name'        => $request->name,
            'description' => $request->description,
            'color'       => $request->color ?? $taskListTemplate->color,
            'tasks'       => $request->tasks ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['template' => $taskListTemplate]]);
        }

        return redirect()->route('task-list-templates.index')
            ->with('success', 'Task list template updated successfully.');
    }

    public function destroy(TaskListTemplate $taskListTemplate)
    {
        if ($taskListTemplate->user_id !== Auth::id()) {
            abort(403);
        }

        $taskListTemplate->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('task-list-templates.index')
            ->with('success', 'Task list template deleted successfully.');
    }
}
