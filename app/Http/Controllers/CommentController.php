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
        ]);

        $comment = Comment::create([
            'task_id' => $taskId,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

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
