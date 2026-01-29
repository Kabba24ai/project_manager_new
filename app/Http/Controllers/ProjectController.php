<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $query = Project::with(['createdBy.roles', 'projectManager.roles', 'taskLists.tasks.assignedUser.roles', 'taskLists.tasks.creator.roles', 'taskLists.tasks.comments.user']);

        // Master admin can see all projects
        if (!$isMasterAdmin) {
            $query->where(function ($q) {
                $q->where(function ($subQ) {
                    // User is part of the project
                    $subQ->where('created_by', Auth::id())
                        ->orWhere('project_manager_id', Auth::id())
                        ->orWhereHas('teamMembers', function ($teamQ) {
                            $teamQ->where('user_id', Auth::id());
                        });
                })
                ->orWhere(function ($subQ) {
                    // Or project is public
                    $subQ->whereRaw("JSON_EXTRACT(settings, '$.publicProject') = true");
                });
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderBy('name')->get();

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

        // Read the role via Spatie (uses roles + model_has_roles tables internally)
        $authRoleName = Auth::user()?->getRoleNames()?->first() ?? '';
        return view('projects.index', compact('projects', 'authRoleName'));
    }

    public function show($id)
    {
        $project = Project::with([
            'createdBy',
            'projectManager',
            'teamMembers',
            'taskLists.tasks.assignedUser',
            'taskLists.tasks.creator',
            'taskLists.tasks.comments',
            'attachments.uploader'
        ])->findOrFail($id);

        // Check if user has access to this project
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $projectSettings = $project->settings ?? [];
        $isPublicProject = $projectSettings['publicProject'] ?? false;

        $isTeamMember = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        $hasAccess = $isMasterAdmin || $isPublicProject || $isTeamMember;

        if (!$hasAccess) {
            abort(403, 'You do not have permission to view this project.');
        }

        if (request()->expectsJson()) {
            return response()->json(['data' => ['project' => $project]]);
        }

        return view('projects.show', compact('project'));
    }

    public function create()
    {
        // Resolve roles from DB first to avoid RoleDoesNotExist exceptions
        $managerRoleNames = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];
        $managerRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $managerRoleNames))
            ->get();

        $managers = $managerRoles->isEmpty()
            ? collect()
            : User::role($managerRoles)->with('roles')->orderBy('first_name')->get();
        $users = User::with('roles')->orderBy('first_name')->get();

        return view('projects.create', compact('managers', 'users'));
    }

    public function store(Request $request)
    {
        // Allow formatting like "$50,000" in UI; store numeric value in DB
        if ($request->filled('budget')) {
            $request->merge([
                'budget' => preg_replace('/[^\d.\-]/', '', (string) $request->budget),
            ]);
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
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
            'objectives' => 'nullable|array',
            'deliverables' => 'nullable|array',
            'tags' => 'nullable|array',
            'settings' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:102400', // 100MB max
        ]);

        // Filter out empty objectives and deliverables
        $objectives = $request->objectives ? array_values(array_filter($request->objectives, fn($v) => !empty(trim($v)))) : [];
        $deliverables = $request->deliverables ? array_values(array_filter($request->deliverables, fn($v) => !empty(trim($v)))) : [];
        $tags = $request->tags ?? [];
        
        // Process settings
        $settings = [
            'taskTypes' => [
                'general' => $request->input('settings.taskTypes.general', false) ? true : false,
                'equipmentId' => $request->input('settings.taskTypes.equipmentId', false) ? true : false,
                'customerName' => $request->input('settings.taskTypes.customerName', false) ? true : false,
            ],
            'allowFileUploads' => $request->input('settings.allowFileUploads', false) ? true : false,
            'requireApproval' => $request->input('settings.requireApproval', false) ? true : false,
            'enableTimeTracking' => $request->input('settings.enableTimeTracking', false) ? true : false,
            'publicProject' => $request->input('settings.publicProject', false) ? true : false,
        ];

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'priority' => $request->priority ?? 'medium',
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'budget' => $request->budget,
            'client' => $request->client,
            'objectives' => $objectives,
            'deliverables' => $deliverables,
            'tags' => $tags,
            'settings' => $settings,
            'created_by' => Auth::id(),
            'project_manager_id' => $request->project_manager_id ?? Auth::id(),
        ]);

        // Sync team members if provided
        if ($request->has('team_members')) {
            $project->teamMembers()->sync($request->team_members);
        }

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '_' . $originalName;
                $path = $file->storeAs('projects/' . $project->id, $filename, 'public');
                
                $project->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $originalName,
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
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
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $canEdit = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id();

        if (!$canEdit) {
            abort(403, 'You do not have permission to edit this project.');
        }

        $managerRoleNames = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];
        $managerRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $managerRoleNames))
            ->get();

        $managers = $managerRoles->isEmpty()
            ? collect()
            : User::role($managerRoles)->with('roles')->orderBy('first_name')->get();
        $users = User::with('roles')->orderBy('first_name')->get();

        return view('projects.edit', compact('project', 'managers', 'users'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Check if user can update this project
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $canUpdate = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id();

        if (!$canUpdate) {
            abort(403, 'You do not have permission to update this project.');
        }

        // Allow formatting like "$50,000" in UI; store numeric value in DB
        if ($request->filled('budget')) {
            $request->merge([
                'budget' => preg_replace('/[^\d.\-]/', '', (string) $request->budget),
            ]);
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
            'objectives' => 'nullable|array',
            'deliverables' => 'nullable|array',
            'tags' => 'nullable|array',
            'settings' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:102400', // 100MB max
        ]);

        // Filter out empty objectives and deliverables
        $objectives = $request->objectives ? array_values(array_filter($request->objectives, fn($v) => !empty(trim($v)))) : [];
        $deliverables = $request->deliverables ? array_values(array_filter($request->deliverables, fn($v) => !empty(trim($v)))) : [];
        $tags = $request->tags ?? [];
        
        // Process settings
        $settings = [
            'taskTypes' => [
                'general' => $request->input('settings.taskTypes.general', false) ? true : false,
                'equipmentId' => $request->input('settings.taskTypes.equipmentId', false) ? true : false,
                'customerName' => $request->input('settings.taskTypes.customerName', false) ? true : false,
            ],
            'allowFileUploads' => $request->input('settings.allowFileUploads', false) ? true : false,
            'requireApproval' => $request->input('settings.requireApproval', false) ? true : false,
            'enableTimeTracking' => $request->input('settings.enableTimeTracking', false) ? true : false,
            'publicProject' => $request->input('settings.publicProject', false) ? true : false,
        ];

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'priority' => $request->priority ?? 'medium',
            'start_date' => $request->start_date,
            'due_date' => $request->due_date,
            'budget' => $request->budget,
            'client' => $request->client,
            'project_manager_id' => $request->project_manager_id,
            'objectives' => $objectives,
            'deliverables' => $deliverables,
            'tags' => $tags,
            'settings' => $settings,
        ]);

        // Sync team members if provided
        if ($request->has('team_members')) {
            $project->teamMembers()->sync($request->team_members);
        }

        // Handle file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . uniqid() . '_' . $originalName;
                $path = $file->storeAs('projects/' . $project->id, $filename, 'public');
                
                $project->attachments()->create([
                    'filename' => $filename,
                    'original_filename' => $originalName,
                    'path' => $path,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'uploaded_by' => Auth::id(),
                ]);
            }
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

        $user = Auth::user();
        $allowedRoles = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];

        // Only users with admin/manager/master-admin roles can delete projects
        if (
            !$user
            || $user->roles->isEmpty()
            || empty(array_intersect(
                array_map('strtolower', $user->roles->pluck('name')->all()),
                array_map('strtolower', $allowedRoles)
            ))
        ) {
            abort(403);
        }

        $project->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Project deleted successfully']);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function manageLists(Project $project)
    {
        $project->load([
            'taskLists' => function($query) {
                $query->orderBy('order');
            },
            'teamMembers',
            'projectManager',
            'createdBy'
        ]);

        return view('projects.manage-lists', compact('project'));
    }

    public function updateListsOrder(Request $request, Project $project)
    {
        try {
            \Log::info('Update lists order request received', [
                'project_id' => $project->id,
                'task_lists' => $request->task_lists
            ]);

            $request->validate([
                'task_lists' => 'required|array',
                'task_lists.*.id' => 'required|exists:task_lists,id',
                'task_lists.*.order' => 'required|integer'
            ]);

            foreach ($request->task_lists as $index => $listData) {
                \App\Models\TaskList::where('id', $listData['id'])
                    ->update([
                        'order' => $listData['order']
                    ]);
                    
                \Log::info('Updated task list', [
                    'id' => $listData['id'],
                    'order' => $listData['order']
                ]);
            }

            \Log::info('Task lists order updated successfully');

            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Task lists order updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error updating task lists order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update task lists order: ' . $e->getMessage());
        }
    }
}
