<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->role($request->role);
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('first_name')->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => ['users' => $users]]);
        }

        return view('users.index', compact('users'));
    }

    public function managers()
    {
        $managerRoleNames = ['admin', 'manager', 'master admin', 'master_admin', 'master-admin'];
        $managerRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn(DB::raw('LOWER(name)'), array_map('strtolower', $managerRoleNames))
            ->get();

        $managers = $managerRoles->isEmpty()
            ? collect()
            : User::role($managerRoles)->orderBy('first_name')->get();

        if (request()->expectsJson()) {
            return response()->json(['data' => ['managers' => $managers]]);
        }

        return view('users.managers', compact('managers'));
    }

    public function getTeamMembersWithTaskCounts()
    {
        $users = User::with('roles')
            ->orderBy('first_name')
            ->get()
            ->map(function ($user) {
                // Get all tasks assigned to user
                $allTasks = Task::where('assigned_to', $user->id)->get();

                $taskCount = $allTasks->count();
                $pendingCount = $allTasks->where('task_status', 'pending')->count();
                $inProgressCount = $allTasks->where('task_status', 'in_progress')->count();
                $unapprovedCount = $allTasks->where('task_status', 'unapproved')->count();
                $completedCount = $allTasks->whereIn('task_status', ['completed_pending_review', 'approved', 'deployed'])->count();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name ?? 'User',
                    'avatar_url' => $user->avatar ? Storage::url($user->avatar) : null,
                    'taskCount' => $taskCount,
                    'pendingCount' => $pendingCount,
                    'inProgressCount' => $inProgressCount,
                    'unapprovedCount' => $unapprovedCount,
                    'completedCount' => $completedCount,
                ];
            });

        return response()->json(['users' => $users]);
    }

    public function getUserTasks($userId)
    {
        $tasks = Task::with(['project:id,name', 'taskList:id,name'])
            ->where('assigned_to', $userId)
            ->orderByRaw("FIELD(task_status, 'unapproved', 'in_progress', 'pending', 'completed_pending_review', 'approved', 'deployed')")
            ->orderBy('due_date')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'task_status' => $task->task_status,
                    'priority' => $task->priority,
                    'project_id' => $task->project_id,
                    'project_name' => $task->project?->name ?? 'Unknown Project',
                    'due_date' => $task->due_date,
                ];
            });

        return response()->json(['tasks' => $tasks]);
    }

    public function updateAvatar(Request $request, $userId)
    {
        \Log::info('Avatar upload request received', [
            'user_id' => $userId,
            'auth_user_id' => auth()->id(),
            'has_avatar' => $request->has('avatar')
        ]);

        $user = User::findOrFail($userId);

        // Only allow users to update their own avatar
        if (auth()->id() !== (int)$userId) {
            \Log::warning('Unauthorized avatar upload attempt', [
                'auth_user_id' => auth()->id(),
                'target_user_id' => $userId
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'avatar' => 'required|string',
        ]);

        try {
            // Decode base64 image
            $avatarData = $request->avatar;
            
            // Extract the base64 encoded binary data
            if (preg_match('/^data:image\/(\w+);base64,/', $avatarData, $matches)) {
                $imageType = $matches[1];
                \Log::info('Image type detected: ' . $imageType);
                
                $avatarData = substr($avatarData, strpos($avatarData, ',') + 1);
                $avatarData = base64_decode($avatarData);

                if ($avatarData === false) {
                    \Log::error('Failed to decode base64 data');
                    return response()->json(['error' => 'Invalid base64 data'], 400);
                }

                // Delete old avatar if exists
                if ($user->avatar) {
                    \Log::info('Deleting old avatar: ' . $user->avatar);
                    Storage::disk('public')->delete($user->avatar);
                }

                // Save new avatar
                $filename = 'avatars/' . $userId . '_' . time() . '.' . $imageType;
                \Log::info('Saving new avatar: ' . $filename);
                
                Storage::disk('public')->put($filename, $avatarData);

                // Verify file was saved
                if (!Storage::disk('public')->exists($filename)) {
                    \Log::error('Avatar file was not saved to storage');
                    return response()->json(['error' => 'Failed to save avatar file'], 500);
                }

                // Update user
                $user->update(['avatar' => $filename]);
                \Log::info('User avatar updated successfully', ['filename' => $filename]);

                return response()->json([
                    'success' => true,
                    'message' => 'Avatar updated successfully',
                    'avatar_url' => Storage::url($filename)
                ]);
            }

            \Log::error('Invalid image format - regex did not match');
            return response()->json(['error' => 'Invalid image format'], 400);
        } catch (\Exception $e) {
            \Log::error('Avatar upload exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to upload avatar: ' . $e->getMessage()], 500);
        }
    }
}
