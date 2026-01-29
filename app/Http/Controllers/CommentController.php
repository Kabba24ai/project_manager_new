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
            $tempFiles = session('temp_uploads', []);
            
            foreach ($request->uploaded_files as $tempId) {
                // Get file info from session
                if (isset($tempFiles[$tempId])) {
                    $fileData = $tempFiles[$tempId];
                    
                    if (\Storage::disk('local')->exists($fileData['path'])) {
                        $fileName = time() . '_' . uniqid() . '.' . pathinfo($fileData['original_name'], PATHINFO_EXTENSION);
                        $permanentPath = 'attachments/' . $fileName;
                        
                        // Copy file to permanent location using Storage facade
                        \Storage::disk('local')->put($permanentPath, \Storage::disk('local')->get($fileData['path']));
                        
                        // Create attachment record
                        $comment->attachments()->create([
                            'filename' => $fileName,
                            'original_filename' => $fileData['original_name'],
                            'path' => $permanentPath,
                            'mime_type' => $fileData['mime_type'],
                            'size' => $fileData['size'],
                            'uploaded_by' => Auth::id(),
                        ]);
                        
                        // Clean up temp file using Storage facade
                        \Storage::disk('local')->delete($fileData['path']);
                        
                        // Remove from session
                        unset($tempFiles[$tempId]);
                    }
                }
            }
            
            // Update session
            session(['temp_uploads' => $tempFiles]);
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
