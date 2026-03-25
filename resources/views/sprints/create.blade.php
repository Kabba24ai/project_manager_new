@extends('layouts.dashboard')

@section('title', 'Proj Mgr - Create Sprint')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('sprints.index') }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Sprints</span>
                </a>
                <div class="h-6 w-px bg-gray-300"></div>
                <h1 class="text-xl font-bold text-gray-900">Create Sprint</h1>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="max-w-2xl mx-auto px-6 py-8">
        <form action="{{ route('sprints.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-running text-3xl text-purple-500"></i>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-1">New Sprint</h2>
                    <p class="text-gray-500 text-sm">Define a time-boxed period and assign tasks to track</p>
                </div>

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Sprint Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               placeholder="e.g., Sprint 1, Week 1, Alpha Release"
                               class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-300 bg-red-50 @enderror">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Goal -->
                    <div>
                        <label for="goal" class="block text-sm font-medium text-gray-700 mb-1">Sprint Goal</label>
                        <textarea id="goal" name="goal" rows="3"
                                  placeholder="What should be achieved in this sprint?"
                                  class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('goal') border-red-300 bg-red-50 @enderror">{{ old('goal') }}</textarea>
                        @error('goal')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date <span class="text-red-500">*</span></label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required
                                   class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('start_date') border-red-300 bg-red-50 @enderror">
                            @error('start_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-red-500">*</span></label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                                   class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('end_date') border-red-300 bg-red-50 @enderror">
                            @error('end_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status"
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                            <option value="planning" {{ old('status', 'planning') === 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active"   {{ old('status') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="completed"{{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-8 border-t border-gray-200 mt-8">
                    <a href="{{ route('sprints.index') }}"
                       class="px-5 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors text-sm">
                        Back
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium flex items-center space-x-2">
                        <i class="fas fa-save"></i>
                        <span>Create Sprint</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
