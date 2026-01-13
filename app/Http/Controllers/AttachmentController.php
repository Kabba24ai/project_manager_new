<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function download(Attachment $attachment)
    {
        $attachment->load('attachable');

        // Authorization: user must have access to the related project
        $project = null;

        if ($attachment->attachable instanceof Project) {
            $project = $attachment->attachable->loadMissing('teamMembers');
        } elseif ($attachment->attachable instanceof Task) {
            $task = $attachment->attachable->loadMissing('project.teamMembers');
            $project = $task->project;
        } elseif ($attachment->attachable instanceof Comment) {
            $comment = $attachment->attachable->loadMissing('task.project.teamMembers');
            $project = $comment->task?->project;
        }

        if (!$project) {
            abort(404);
        }

        $hasAccess = $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());

        if (!$hasAccess) {
            abort(403);
        }

        // Try private local disk first, then public disk
        $disk = Storage::disk('local')->exists($attachment->path) ? 'local' : (Storage::disk('public')->exists($attachment->path) ? 'public' : null);
        if (!$disk) {
            abort(404);
        }

        return Storage::disk($disk)->download($attachment->path, $attachment->original_filename);
    }
}

