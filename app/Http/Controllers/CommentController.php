<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index($taskId)
    {
        $comments = Comment::with('user')
            ->where('task_id', $taskId)
            ->orderBy('created_at')
            ->get();

        if (request()->expectsJson()) {
            return response()->json(['data' => ['comments' => $comments]]);
        }

        return view('comments.index', compact('comments', 'taskId'));
    }

    public function store(Request $request, $taskId)
    {
        $request->validate([
            'content' => 'required|string',
            'uploaded_files' => 'nullable|array',
            'uploaded_files.*' => 'string',
        ]);

        $comment = Comment::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        // Handle uploaded file attachments
        if ($request->has('uploaded_files') && is_array($request->uploaded_files)) {
            $tempSessionFiles = session('temp_uploads', []);

            foreach ($request->uploaded_files as $tempId) {
                // Prefer JSON sidecar (concurrent-safe), fall back to session
                $sidecarPath = 'temp_uploads/' . $tempId . '.json';
                if (\Storage::disk('local')->exists($sidecarPath)) {
                    $fileData = json_decode(\Storage::disk('local')->get($sidecarPath), true);
                } elseif (isset($tempSessionFiles[$tempId])) {
                    $fileData = $tempSessionFiles[$tempId];
                } else {
                    continue;
                }

                if (!\Storage::disk('local')->exists($fileData['path'])) {
                    \Storage::disk('local')->delete($sidecarPath);
                    continue;
                }

                $ext = pathinfo($fileData['original_name'], PATHINFO_EXTENSION);
                $fileName = (string) \Illuminate\Support\Str::uuid() . ($ext ? '.' . $ext : '');
                $permanentPath = 'attachments/' . $fileName;

                \Storage::disk('local')->move($fileData['path'], $permanentPath);
                \Storage::disk('local')->delete($sidecarPath);

                $comment->attachments()->create([
                    'filename' => $fileName,
                    'original_filename' => $fileData['original_name'],
                    'path' => $permanentPath,
                    'mime_type' => $fileData['mime_type'],
                    'size' => $fileData['size'],
                    'uploaded_by' => Auth::id(),
                ]);

                unset($tempSessionFiles[$tempId]);
            }

            session(['temp_uploads' => $tempSessionFiles]);
        }

        $comment->load('user');

        if ($request->expectsJson()) {
            return response()->json(['data' => ['comment' => $comment]], 201);
        }

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    public function update(Request $request, $taskId, $id)
    {
        $comment = Comment::where('task_id', $taskId)->findOrFail($id);

        // Only the comment owner can update
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update(['content' => $request->content]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['comment' => $comment]]);
        }

        return redirect()->back()->with('success', 'Comment updated successfully.');
    }

    public function destroy($taskId, $id)
    {
        $comment = Comment::where('task_id', $taskId)->findOrFail($id);

        // Only the comment owner can delete
        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Comment deleted successfully']);
        }

        return redirect()->back()->with('success', 'Comment deleted successfully.');
    }
}
