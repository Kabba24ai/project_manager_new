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

    public function thumbnail(Attachment $attachment)
    {
        $attachment->load('attachable');

        // Authorization check (same as download)
        if (!$this->hasAccess($attachment)) {
            abort(403);
        }

        // If it's an image, try to generate/use thumbnail
        if ($attachment->isImage()) {
            // If thumbnail doesn't exist, generate it
            if (!$attachment->thumbnail_path || !Storage::disk('local')->exists($attachment->thumbnail_path)) {
                $this->generateImageThumbnail($attachment);
                $attachment->refresh(); // Reload to get updated thumbnail_path
            }

            // If thumbnail exists, use it
            if ($attachment->thumbnail_path && Storage::disk('local')->exists($attachment->thumbnail_path)) {
                return response()->file(Storage::disk('local')->path($attachment->thumbnail_path));
            }
        }

        // Fallback: serve original file
        $disk = Storage::disk('local')->exists($attachment->path) ? 'local' : 'public';

        if (!Storage::disk($disk)->exists($attachment->path)) {
            abort(404);
        }

        return response()->file(Storage::disk($disk)->path($attachment->path));
    }

    public function preview(Attachment $attachment)
    {
        $attachment->load('attachable');

        // Authorization check
        if (!$this->hasAccess($attachment)) {
            abort(403);
        }

        $disk = Storage::disk('local')->exists($attachment->path) ? 'local' : 'public';

        if (!Storage::disk($disk)->exists($attachment->path)) {
            abort(404);
        }

        // Serve file for preview
        return response()->file(Storage::disk($disk)->path($attachment->path));
    }

    protected function hasAccess(Attachment $attachment): bool
    {
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
            return false;
        }

        return $project->created_by === Auth::id()
            || $project->project_manager_id === Auth::id()
            || $project->teamMembers->contains('id', Auth::id());
    }

    public function generateImageThumbnail(Attachment $attachment)
    {
        try {
            $disk = Storage::disk('local')->exists($attachment->path) ? 'local' : 'public';
            $sourcePath = Storage::disk($disk)->path($attachment->path);

            if (!file_exists($sourcePath)) {
                \Log::warning("Thumbnail generation: Source file not found: {$sourcePath}");
                return;
            }

            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return;
            }

            // Create image resource based on type
            $sourceImage = match($imageInfo[2]) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
                IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
                IMAGETYPE_GIF => imagecreatefromgif($sourcePath),
                IMAGETYPE_WEBP => imagecreatefromwebp($sourcePath),
                default => null
            };

            if (!$sourceImage) {
                return;
            }

            // Calculate thumbnail dimensions (max 300px)
            $maxSize = 300;
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);

            if ($width > $height) {
                $newWidth = $maxSize;
                $newHeight = intval($height * ($maxSize / $width));
            } else {
                $newHeight = $maxSize;
                $newWidth = intval($width * ($maxSize / $height));
            }

            // Create thumbnail
            $thumbnail = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($imageInfo[2] == IMAGETYPE_PNG || $imageInfo[2] == IMAGETYPE_GIF) {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefilledrectangle($thumbnail, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save thumbnail
            $thumbnailPath = str_replace($attachment->filename, 'thumb_' . $attachment->filename, $attachment->path);
            $thumbnailFullPath = Storage::disk('local')->path($thumbnailPath);

            // Ensure directory exists
            $directory = dirname($thumbnailFullPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Save based on type
            switch($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    imagejpeg($thumbnail, $thumbnailFullPath, 85);
                    break;
                case IMAGETYPE_PNG:
                    imagepng($thumbnail, $thumbnailFullPath, 8);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($thumbnail, $thumbnailFullPath);
                    break;
                case IMAGETYPE_WEBP:
                    imagewebp($thumbnail, $thumbnailFullPath, 85);
                    break;
            }

            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);

            // Update attachment record
            $attachment->update(['thumbnail_path' => $thumbnailPath]);

        } catch (\Exception $e) {
            \Log::error('Thumbnail generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload a file temporarily before task creation
     */
    public function uploadTemp(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm,pdf'
        ]);

        $file = $request->file('file');
        $tempId = uniqid('temp_', true);
        $extension = $file->getClientOriginalExtension();
        $filename = $tempId . '.' . $extension;
        
        // Store in temp directory
        $path = $file->storeAs('temp_uploads', $filename, 'local');

        // Store metadata in session for later retrieval
        $tempFiles = session('temp_uploads', []);
        $tempFiles[$tempId] = [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
        session(['temp_uploads' => $tempFiles]);

        return response()->json([
            'success' => true,
            'tempId' => $tempId,
            'filename' => $file->getClientOriginalName(),
        ]);
    }
}

