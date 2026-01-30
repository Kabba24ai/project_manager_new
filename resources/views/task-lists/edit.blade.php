@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Edit Task List')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('projects.show', $project->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Project</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Task List</h1>
                <div class="h-6 w-px bg-gray-300"></div>
                <span class="text-gray-600">{{ $project->name }}</span>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="max-w-2xl mx-auto px-6 py-8">
        <form action="{{ route('task-lists.update', [$project->id, $taskList->id]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-edit text-3xl text-blue-600"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Edit Task List</h2>
                    <p class="text-gray-600">Update your task list details</p>
                </div>

                <div class="space-y-6">
                    <!-- Task List Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Task List Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $taskList->name) }}"
                            required
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-300 bg-red-50 @enderror"
                            placeholder="e.g., To Do, In Progress, Review, Done"
                        />
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Task List Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Short Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('description') border-red-300 bg-red-50 @enderror"
                            placeholder="Brief description of what tasks belong in this list"
                        >{{ old('description', $taskList->description) }}</textarea>
                        @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color Selection -->
                    <div x-data="{ selectedColor: '{{ old('color', $taskList->color) }}' }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            List Color
                        </label>
                        <input type="hidden" name="color" x-model="selectedColor">
                        <div class="grid grid-cols-4 gap-3">
                            @php
                                $colorOptions = [
                                    ['value' => 'bg-blue-100', 'label' => 'Blue'],
                                    ['value' => 'bg-green-100', 'label' => 'Green'],
                                    ['value' => 'bg-yellow-100', 'label' => 'Yellow'],
                                    ['value' => 'bg-red-100', 'label' => 'Red'],
                                    ['value' => 'bg-purple-100', 'label' => 'Purple'],
                                    ['value' => 'bg-indigo-100', 'label' => 'Indigo'],
                                    ['value' => 'bg-pink-100', 'label' => 'Pink'],
                                    ['value' => 'bg-gray-100', 'label' => 'Gray']
                                ];
                            @endphp
                            
                            @foreach($colorOptions as $color)
                            <button
                                type="button"
                                @click="selectedColor = '{{ $color['value'] }}'"
                                :class="selectedColor === '{{ $color['value'] }}' ? 'border-blue-500 shadow-md' : 'border-gray-200 hover:border-gray-300'"
                                class="p-3 rounded-lg border-2 transition-all duration-200"
                            >
                                <div class="w-full h-8 rounded {{ $color['value'] }} mb-2"></div>
                                <span class="text-xs font-medium text-gray-700">{{ $color['label'] }}</span>
                            </button>
                            @endforeach
                        </div>

                        <!-- Preview -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 mt-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Preview</h3>
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="px-4 py-3 rounded-t-lg border-b border-gray-200" :class="selectedColor">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900" id="preview-name">{{ $taskList->name }}</h4>
                                            <p class="text-sm text-gray-600 mt-1" id="preview-description" style="display: {{ $taskList->description ? 'block' : 'none' }}">{{ $taskList->description }}</p>
                                        </div>
                                        <span class="bg-white px-2 py-1 rounded-full text-xs font-medium text-gray-600">
                                            {{ $taskList->tasks->count() }} {{ Str::plural('task', $taskList->tasks->count()) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-4 text-center text-gray-500 text-sm">
                                    Tasks will appear here
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-200 mt-8">
                    <a
                        href="{{ route('projects.show', $project->id) }}"
                        class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
                    >
                        <i class="fas fa-save"></i>
                        <span>Update Task List</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Live preview update
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewName = document.getElementById('preview-name');
    const previewDescription = document.getElementById('preview-description');

    if (nameInput && previewName) {
        nameInput.addEventListener('input', function() {
            previewName.textContent = this.value || 'Task List Name';
        });
    }

    if (descriptionInput && previewDescription) {
        descriptionInput.addEventListener('input', function() {
            previewDescription.textContent = this.value;
            previewDescription.style.display = this.value ? 'block' : 'none';
        });
    }
});
</script>
@endpush
@endsection
