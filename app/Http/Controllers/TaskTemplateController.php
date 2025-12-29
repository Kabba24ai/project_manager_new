<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = TaskTemplate::where('user_id', Auth::id());

        if ($request->has('task_type')) {
            $taskType = $request->task_type;
            $query->where(function ($q) use ($taskType) {
                $q->where('is_universal', true)
                    ->orWhereJsonContains('task_types', $taskType);
            });
        }

        $templates = $query->latest()->get();

        if ($request->expectsJson()) {
            return response()->json(['data' => ['templates' => $templates]]);
        }

        return view('task-templates.index', compact('templates'));
    }

    public function getTemplatesApi(Request $request)
    {
        $templates = TaskTemplate::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json(['templates' => $templates]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'template_text' => 'required|string',
            'is_universal' => 'nullable|boolean',
            'task_types' => 'nullable|array',
        ]);

        $template = TaskTemplate::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'template_text' => $request->template_text,
            'is_universal' => $request->is_universal ?? false,
            'task_types' => $request->task_types ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['template' => $template]], 201);
        }

        return redirect()->route('task-templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function update(Request $request, $id)
    {
        $template = TaskTemplate::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'template_text' => 'required|string',
            'is_universal' => 'nullable|boolean',
            'task_types' => 'nullable|array',
        ]);

        $template->update([
            'name' => $request->name,
            'template_text' => $request->template_text,
            'is_universal' => $request->is_universal ?? false,
            'task_types' => $request->task_types ?? [],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => ['template' => $template]]);
        }

        return redirect()->route('task-templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy($id)
    {
        $template = TaskTemplate::where('user_id', Auth::id())->findOrFail($id);
        $template->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('task-templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
