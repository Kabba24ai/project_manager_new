<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Project;
use App\Models\Attachment;
use App\Models\ServiceCall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function create($projectId)
    {
        $project = Project::with(['taskLists', 'teamMembers'])->findOrFail($projectId);

        // Check if user has access to this project
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $hasAccess = $isMasterAdmin
            || $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        // Only allow assigning tasks to project team members (exclude creator/manager)
        $projectUserIds = $project->teamMembers
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $users = User::with('roles')
            ->whereIn('id', $projectUserIds)
            ->orderBy('first_name')
            ->get();
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
                        // "equipment_id" is the business/visible equipment identifier column
                        'equipment_id' => $item->equipment_id,
                        // Expose DB column name for frontend usage
                        'current_status' => $item->current_status,
                        // Backward-compatible alias
                        'status' => $item->current_status,
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
        $rules = [
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:102400|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf',
            'uploaded_files' => 'nullable|array',
            'uploaded_files.*' => 'string',
            'service_call_type' => 'nullable|in:none,customer_damage,field_service',
            'service_call_order_id' => 'nullable|string',
            'customer_id' => 'nullable|integer',
            'order_id' => 'nullable|string',
            'customer_type' => 'nullable|in:general,charge,refund',
        ];
        
        // Require customer_id and customer_type when task_type is customerName (order_id is optional)
        if ($request->task_type === 'customerName' || ($request->task_type === null && $task->task_type === 'customerName')) {
            $rules['customer_id'] = 'required|integer';
            $rules['customer_type'] = 'required|in:general,charge,refund';
            // order_id is optional, so keep it as nullable|string
        }
        
        $request->validate($rules);

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
            'customer_id' => $request->customer_id,
            'order_id' => $request->order_id,
            'customer_type' => $request->customer_type ?? 'general',
        ]);

        // Save attachments (optional)
        $files = $request->file('attachments', []);
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $ext = $file->getClientOriginalExtension();
            $storedName = (string) Str::uuid() . ($ext ? '.' . $ext : '');
            $storedPath = $file->storeAs("tasks/{$task->id}", $storedName, 'local');

            $attachment = Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task->id,
                'filename' => $storedName,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $storedPath,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);

            // Generate thumbnail for images
            if (in_array($attachment->mime_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                app(AttachmentController::class)->generateImageThumbnail($attachment);
            }
        }

        // Process temp uploaded files
        $uploadedFileIds = $request->input('uploaded_files', []);
        $tempFiles = session('temp_uploads', []);
        
        foreach ($uploadedFileIds as $tempId) {
            if (!isset($tempFiles[$tempId])) {
                continue;
            }

            $tempFile = $tempFiles[$tempId];
            $tempPath = $tempFile['path'];

            if (!Storage::disk('local')->exists($tempPath)) {
                continue;
            }

            // Generate new filename
            $ext = pathinfo($tempFile['original_name'], PATHINFO_EXTENSION);
            $storedName = (string) Str::uuid() . ($ext ? '.' . $ext : '');
            $newPath = "tasks/{$task->id}/{$storedName}";

            // Move from temp to permanent storage
            Storage::disk('local')->move($tempPath, $newPath);

            $attachment = Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task->id,
                'filename' => $storedName,
                'original_filename' => $tempFile['original_name'],
                'path' => $newPath,
                'mime_type' => $tempFile['mime_type'],
                'size' => $tempFile['size'],
                'uploaded_by' => Auth::id(),
            ]);

            // Generate thumbnail for images
            if (in_array($attachment->mime_type, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])) {
                app(AttachmentController::class)->generateImageThumbnail($attachment);
            }

            // Remove from temp files array
            unset($tempFiles[$tempId]);
        }

        // Update session
        session(['temp_uploads' => $tempFiles]);

        // Create service call if provided
        if ($request->filled('service_call_type') && $request->service_call_type !== 'none' && $request->filled('service_call_order_id')) {
            ServiceCall::create([
                'task_id' => $task->id,
                'service_type' => $request->service_call_type,
                'order_id' => $request->service_call_order_id,
                'notes' => '',
            ]);
        }

        $task->load('assignedUser');

        // Determine redirect based on submit action
        $submitAction = $request->input('submit_action', 'save_exit');
        
        if ($request->expectsJson() || $request->ajax()) {
            $redirectUrl = $submitAction === 'save' 
                ? route('tasks.show', $task->id)
                : route('projects.show', $taskList->project_id);
            
            return response()->json([
                'data' => ['task' => $task],
                'redirect' => $redirectUrl
            ], 201);
        }

        // For non-AJAX requests, redirect based on action
        if ($submitAction === 'save') {
            return redirect()->route('tasks.show', $task->id)
                ->with('success', 'Task created successfully.');
        }

        return redirect()->route('projects.show', $taskList->project_id)
            ->with('success', 'Task created successfully.');
    }

    public function show($id)
    {
        $task = Task::with([
            'assignedUser',
            'creator',
            'taskList',
            'project.teamMembers',
            'comments.author',
            'attachments.uploader',
            'serviceCall',
            'invoices.customer',
        ])->findOrFail($id);

        // Check if user has access to this task's project
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $project = $task->project;
        $projectSettings = $project->settings ?? [];
        $isPublicProject = $projectSettings['publicProject'] ?? false;

        $isTeamMember = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        $hasAccess = $isMasterAdmin || $isPublicProject || $isTeamMember;

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
        $task = Task::with(['taskList.project.teamMembers', 'attachments.uploader', 'serviceCall'])->findOrFail($id);

        // Check if user can edit this task (must be team member or master admin)
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $project = $task->project;
        $isTeamMember = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        $canEdit = $isMasterAdmin || $isTeamMember;

        if (!$canEdit) {
            abort(403, 'You do not have permission to edit this task.');
        }

        // Only allow assigning tasks to project team members (exclude creator/manager)
        $projectUserIds = $project->teamMembers
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $users = User::with('roles')
            ->whereIn('id', $projectUserIds)
            ->orderBy('first_name')
            ->get();
        
        // Ensure project has taskLists loaded
        $project->load('taskLists');
        
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
                        'equipment_id' => $item->equipment_id,
                        'current_status' => $item->current_status,
                        'status' => $item->current_status,
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

        return view('tasks.edit', compact('task', 'users', 'project', 'equipmentCategories', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $task = Task::with('project')->findOrFail($id);

        // Check if user can update this task (must be team member or master admin)
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $project = $task->project;
        $isTeamMember = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        $canUpdate = $isMasterAdmin || $isTeamMember;

        if (!$canUpdate) {
            return redirect()->back()->with('error', 'You do not have permission to update this task.');
        }

        $rules = [
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
            'project_id' => 'nullable|exists:projects,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:102400|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf',
            'customer_id' => 'nullable|integer',
            'order_id' => 'nullable|string',
            'customer_type' => 'nullable|in:general,charge,refund',
        ];
        
        // Require customer_id and customer_type when task_type is customerName (order_id is optional)
        if ($request->task_type === 'customerName' || ($request->task_type === null && $task->task_type === 'customerName')) {
            $rules['customer_id'] = 'required|integer';
            $rules['customer_type'] = 'required|in:general,charge,refund';
            // order_id is optional, so keep it as nullable|string
        }
        
        $request->validate($rules);

        // If moving to a different project, check access to target project
        if ($request->filled('project_id') && $request->project_id != $task->project_id) {
            $targetProject = Project::with('teamMembers')->findOrFail($request->project_id);
            
            $isTargetTeamMember = $targetProject->created_by === Auth::id()
                || $targetProject->project_manager_id === Auth::id()
                || $targetProject->teamMembers->contains('id', Auth::id());

            $canMoveToTarget = $isMasterAdmin || $isTargetTeamMember;

            if (!$canMoveToTarget) {
                return redirect()->back()->with('error', 'You do not have permission to move tasks to that project.');
            }
        }

        // Check if project requires approval and user is trying to change status to approved
        if ($request->filled('task_status') && $request->task_status === 'approved') {
            $project = $task->project;
            $settings = $project->settings ?? [];
            $requireApproval = $settings['requireApproval'] ?? false;

            if ($requireApproval) {
                // Only project manager can approve when requireApproval is true
                if (Auth::id() !== $project->project_manager_id) {
                    return redirect()->back()->with('error', 'Only the project manager can approve tasks for this project.');
                }
            }
        }

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
            'project_id',
            'customer_id',
            'order_id',
            'customer_type',
        ]));

        // Save new attachments (optional)
        $files = $request->file('attachments', []);
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $ext = $file->getClientOriginalExtension();
            $storedName = (string) Str::uuid() . ($ext ? '.' . $ext : '');
            $storedPath = $file->storeAs("tasks/{$task->id}", $storedName, 'local');

            Attachment::create([
                'attachable_type' => Task::class,
                'attachable_id' => $task->id,
                'filename' => $storedName,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $storedPath,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }

        $task->load('assignedUser');

        if ($request->expectsJson()) {
            return response()->json(['data' => ['task' => $task]]);
        }

        // If task was moved to a different project, redirect to that project
        if ($request->filled('project_id') && $request->project_id != $project->id) {
            return redirect()->route('projects.show', $request->project_id)
                ->with('success', 'Task moved successfully to ' . $task->project->name . '.');
        }

        return redirect("/tasks/{$task->id}")->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $task = Task::with('project.teamMembers')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed_pending_review,completed_approved,unapproved,approved,deployed',
        ]);

        // Check if user can update this task's status (must be team member or master admin)
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $project = $task->project;
        $isTeamMember = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        $canUpdate = $isMasterAdmin || $isTeamMember;

        if (!$canUpdate) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to update this task.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to update this task.');
        }

        // Check if project requires approval and user is trying to approve
        if ($request->status === 'approved') {
            $project = $task->project;
            $settings = $project->settings ?? [];
            $requireApproval = $settings['requireApproval'] ?? false;

            if ($requireApproval) {
                // Only project manager can approve when requireApproval is true
                if (Auth::id() !== $project->project_manager_id) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Only the project manager can approve tasks for this project.'
                        ], 403);
                    }
                    return redirect()->back()->with('error', 'Only the project manager can approve tasks for this project.');
                }
            }
        }

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
        $task = Task::with('project.teamMembers')->findOrFail($id);

        // Check if user can delete this task (must be team member or master admin)
        $user = Auth::user();
        $userRoles = $user->getRoleNames();
        $isMasterAdmin = $userRoles->contains(function ($role) {
            return strtolower($role) === 'master admin' || strtolower($role) === 'master_admin';
        });

        $project = $task->project;
        $isTeamMember = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        $canDelete = $isMasterAdmin || $isTeamMember;

        if (!$canDelete) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to delete this task.'], 403);
            }
            return redirect()->back()->with('error', 'You do not have permission to delete this task.');
        }

        $projectId = $task->project_id;
        $task->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Task deleted successfully']);
        }

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Task deleted successfully.');
    }
}
