@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Edit Sprint')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('sprints.show', $sprint->id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Sprint</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-xl font-bold text-gray-900">Edit Sprint</h1>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-2xl mx-auto px-6 py-8">
        <form action="{{ route('sprints.update', $sprint->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8">
                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Sprint Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $sprint->name) }}" required
                               class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-300 bg-red-50 @enderror">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Goal -->
                    <div>
                        <label for="goal" class="block text-sm font-medium text-gray-700 mb-1">Sprint Goal</label>
                        <textarea id="goal" name="goal" rows="3"
                                  class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('goal') border-red-300 bg-red-50 @enderror">{{ old('goal', $sprint->goal) }}</textarea>
                        @error('goal')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" id="start_date" name="start_date"
                                   value="{{ old('start_date', $sprint->start_date->format('Y-m-d')) }}" required
                                   class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('start_date') border-red-300 bg-red-50 @enderror">
                            @error('start_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                            <input type="date" id="end_date" name="end_date"
                                   value="{{ old('end_date', $sprint->end_date->format('Y-m-d')) }}" required
                                   class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('end_date') border-red-300 bg-red-50 @enderror">
                            @error('end_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="planning" {{ old('status', $sprint->status) === 'planning'  ? 'selected' : '' }}>Planning</option>
                            <option value="active"   {{ old('status', $sprint->status) === 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="completed"{{ old('status', $sprint->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-200 mt-8">
                    <a href="{{ route('sprints.show', $sprint->id) }}"
                       class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
