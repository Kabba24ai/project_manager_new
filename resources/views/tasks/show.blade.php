@extends('layouts.dashboard')

@section('title',  'Proj Mgr - ' . $task->title)

@section('content')
@php
    $userRoleNames = auth()->user()
        ? auth()->user()->roles->pluck('name')->map(fn($r) => strtolower($r))->all()
        : [];
    $isMasterAdmin = in_array('master admin', $userRoleNames) || in_array('master_admin', $userRoleNames);
    $isTeamMember = $task->project->created_by === auth()->id()
        || $task->project->project_manager_id === auth()->id()
        || $task->project->teamMembers->contains('id', auth()->id());
    $canEditTask = $isMasterAdmin || $isTeamMember;
@endphp
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('projects.show', $task->project_id) }}" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Project</span>
                    </a>
                    <div class="h-6 w-px bg-gray-300"></div>
                    
                </div>
               
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-6xl mx-auto px-6 py-8" x-data="taskChangesManager()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Description -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center space-x-3">
                        <span class="text-lg">
                            @if($task->task_type === 'general')📝
                            @elseif($task->task_type === 'equipmentId')🔧
                            @elseif($task->task_type === 'customerName')👤
                            @else📝
                            @endif
                        </span>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $task->title }}</h1>
                        
                    </div>
                    <p class="text-gray-700 whitespace-pre-line">{{ $task->description }}</p>
                </div>

                <!-- Attachments -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" x-data="{ 
                    previewUrl: null, 
                    previewType: null, 
                    previewName: null,
                    closePreview() {
                        // Pause and reset video if it's playing
                        const videoElement = this.$refs.previewVideo;
                        if (videoElement) {
                            videoElement.pause();
                            videoElement.currentTime = 0;
                        }
                        this.previewUrl = null;
                    }
                }">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Attachments</h2>
                        @if($task->attachments->count() > 0)
                            <span class="text-sm text-gray-500">{{ $task->attachments->count() }}</span>
                        @endif
                    </div>

                    @if($task->attachments->count() > 0)
                        <!-- Grid Layout for Attachments -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach($task->attachments as $attachment)
                                <div class="group relative bg-gray-50 rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                                    <!-- Thumbnail/Preview Area -->
                                    <div class="aspect-square bg-gray-100 flex items-center justify-center relative">
                                        @if($attachment->isImage())
                                            <!-- Image Thumbnail -->
                                            <img 
                                                src="{{ route('attachments.thumbnail', $attachment->id) }}" 
                                                alt="{{ $attachment->original_filename }}"
                                                class="w-full h-full object-cover cursor-pointer"
                                                @click="previewUrl = '{{ route('attachments.preview', $attachment->id) }}'; previewType = 'image'; previewName = '{{ $attachment->original_filename }}'"
                                            >
                                            <div 
                                                class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer"
                                                @click="previewUrl = '{{ route('attachments.preview', $attachment->id) }}'; previewType = 'image'; previewName = '{{ $attachment->original_filename }}'"
                                            >
                                                <i class="fas fa-search-plus text-white text-2xl pointer-events-none"></i>
                                            </div>
                                        @elseif($attachment->isVideo())
                                            <!-- Video Thumbnail -->
                                            <div 
                                                id="video-thumb-{{ $attachment->id }}"
                                                class="relative w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 to-gray-800 cursor-pointer group-hover:from-gray-800 group-hover:to-gray-700 transition-all overflow-hidden"
                                                @click="previewUrl = '{{ route('attachments.preview', $attachment->id) }}'; previewType = 'video'; previewName = '{{ $attachment->original_filename }}'"
                                                data-video-url="{{ route('attachments.preview', $attachment->id) }}"
                                            >
                                                <canvas id="canvas-{{ $attachment->id }}" class="absolute inset-0 w-full h-full object-cover" style="display: none;"></canvas>
                                                <img id="thumb-img-{{ $attachment->id }}" class="absolute inset-0 w-full h-full object-cover" style="display: none;">
                                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-40 z-10">
                                                    <i class="fas fa-play-circle text-white text-5xl mb-2 group-hover:scale-110 transition-transform drop-shadow-lg"></i>
                                                    <span class="text-white text-xs font-medium drop-shadow">Click to play</span>
                                                </div>
                                            </div>
                                        @elseif($attachment->isPdf())
                                            <!-- PDF Icon -->
                                            <div class="flex flex-col items-center justify-center text-red-600">
                                                <i class="fas fa-file-pdf text-4xl mb-2"></i>
                                                <span class="text-xs">PDF</span>
                                            </div>
                                        @else
                                            <!-- Generic File Icon -->
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <i class="fas fa-file text-4xl mb-2"></i>
                                                <span class="text-xs">FILE</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- File Info -->
                                    <div class="p-2">
                                        <div class="text-xs font-medium text-gray-900 truncate" title="{{ $attachment->original_filename }}">
                                            {{ $attachment->original_filename }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            @php
                                                $sizeInBytes = $attachment->size ?? 0;
                                                $sizeInKB = $sizeInBytes / 1024;
                                                $sizeInMB = $sizeInKB / 1024;
                                            @endphp
                                            @if($sizeInMB >= 1)
                                                {{ number_format($sizeInMB, 2) }} MB
                                            @else
                                                {{ number_format($sizeInKB, 1) }} KB
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a 
                                            href="{{ route('attachments.download', $attachment->id) }}" 
                                            class="px-2 py-1 bg-white rounded shadow-sm text-xs text-gray-700 hover:bg-gray-100"
                                            title="Download"
                                        >
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No attachments.</p>
                    @endif

                    <!-- Preview Modal -->
                    <div 
                        x-show="previewUrl" 
                        x-cloak
                        class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4"
                        @click.self="closePreview()"
                    >
                        <div class="relative max-w-6xl w-full">
                            <!-- Close Button -->
                            <button 
                                @click="closePreview()"
                                class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors"
                            >
                                <i class="fas fa-times text-2xl"></i>
                            </button>

                            <!-- File Name -->
                            <div class="text-white text-center mb-4" x-text="previewName"></div>

                            <!-- Preview Content -->
                            <div class="bg-white rounded-lg overflow-hidden">
                                <!-- Image Preview -->
                                <template x-if="previewType === 'image'">
                                    <img :src="previewUrl" alt="Preview" class="w-full h-auto max-h-[80vh] object-contain">
                                </template>

                                <!-- Video Preview -->
                                <template x-if="previewType === 'video'">
                                    <video 
                                        x-ref="previewVideo"
                                        :src="previewUrl" 
                                        controls 
                                        autoplay
                                        playsinline
                                        class="w-full h-auto max-h-[80vh] bg-black"
                                    >
                                        Your browser does not support the video tag.
                                    </video>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Status Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Status</h2>
                    <div class="flex items-center gap-3 overflow-x-auto">
                        @php
                            $statuses = [
                                ['value' => 'pending', 'label' => 'Pending', 'icon' => 'clock', 'activeColor' => 'bg-gray-500 text-white border-gray-500'],
                                ['value' => 'in_progress', 'label' => 'In Progress', 'icon' => 'spinner', 'activeColor' => 'bg-blue-600 text-white border-blue-600'],
                                ['value' => 'completed_pending_review', 'label' => 'Review', 'icon' => 'eye', 'activeColor' => 'bg-yellow-500 text-white border-yellow-500'],
                                ['value' => 'unapproved', 'label' => 'Unapproved', 'icon' => 'times-circle', 'activeColor' => 'bg-red-600 text-white border-red-600'],
                                ['value' => 'approved', 'label' => 'Approved', 'icon' => 'check-circle', 'activeColor' => 'bg-green-600 text-white border-green-600'],
                            ];

                            // Check if user can approve tasks for this project
                            $projectSettings = $task->project->settings ?? [];
                            $requireApproval = $projectSettings['requireApproval'] ?? false;
                            $canApprove = !$requireApproval || (auth()->id() === $task->project->project_manager_id);
                        @endphp
                        
                        @foreach($statuses as $status)
                            @if($status['value'] === 'approved' && !$canApprove)
                                @continue
                            @endif
                            <button 
                                type="button" 
                                @click="setStatus('{{ $status['value'] }}')" 
                                :class="{'{{ $status['activeColor'] }}': stagedStatus === '{{ $status['value'] }}', 'bg-white text-gray-600 border-gray-200 hover:border-gray-300 hover:bg-gray-50': stagedStatus !== '{{ $status['value'] }}'}"
                                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors border-2 whitespace-nowrap"
                            >
                                <i class="fas fa-{{ $status['icon'] }} mr-2"></i>
                                {{ $status['label'] }}
                                <span x-show="stagedStatus === '{{ $status['value'] }}' && stagedStatus !== '{{ $task->task_status }}'" class="ml-2 text-xs">(pending)</span>
                            </button>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-3">Click any status to stage changes. Click "Save & Exit" to save all changes.</p>
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" x-data="{ 
                    previewUrl: null, 
                    previewType: null, 
                    previewName: null,
                    closePreview() {
                        // Pause and reset video if it's playing
                        const videoElement = this.$refs.previewVideo;
                        if (videoElement) {
                            videoElement.pause();
                            videoElement.currentTime = 0;
                        }
                        this.previewUrl = null;
                    }
                }">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Activity & Comments ({{ $task->comments->count() }})
                        </h2>
                    </div>

                    <!-- Comments List -->
                    <div class="space-y-6 mb-8">
                        @forelse($task->comments as $comment)
                        <div class="flex space-x-4" x-data="{ 
                            isEditing: false, 
                            editContent: '{{ addslashes($comment->content) }}',
                            isSaving: false
                        }">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-sm font-medium text-white">{{ strtoupper(substr($comment->author->name, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-900">{{ $comment->author->name }}</span>
                                        @if($comment->user_id === auth()->id())
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                @click="isEditing = true" 
                                                x-show="!isEditing"
                                                class="text-gray-500 hover:text-blue-600 transition-colors"
                                                title="Edit comment"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                                @click="if(confirm('Are you sure you want to delete this comment?')) { deleteComment({{ $comment->id }}) }"
                                                x-show="!isEditing"
                                                class="text-gray-500 hover:text-red-600 transition-colors"
                                                title="Delete comment"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- View Mode -->
                                    <div x-show="!isEditing">
                                        <p class="text-sm text-gray-700 mb-2">{{ $comment->content }}</p>
                                        
                                        @if($comment->attachments->count() > 0)
                                        <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            @foreach($comment->attachments as $attachment)
                                            <div class="group relative bg-gray-50 rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                                                <!-- Thumbnail/Preview Area -->
                                                <div class="aspect-square bg-gray-100 flex items-center justify-center relative">
                                                    @if($attachment->isImage())
                                                        <!-- Image Thumbnail -->
                                                        <img 
                                                            src="{{ route('attachments.thumbnail', $attachment->id) }}" 
                                                            alt="{{ $attachment->original_filename }}"
                                                            class="w-full h-full object-cover cursor-pointer"
                                                            @click="previewUrl = '{{ route('attachments.preview', $attachment->id) }}'; previewType = 'image'; previewName = '{{ $attachment->original_filename }}'"
                                                        >
                                                        <div 
                                                            class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100 cursor-pointer"
                                                            @click="previewUrl = '{{ route('attachments.preview', $attachment->id) }}'; previewType = 'image'; previewName = '{{ $attachment->original_filename }}'"
                                                        >
                                                            <i class="fas fa-search-plus text-white text-2xl pointer-events-none"></i>
                                                        </div>
                                                    @elseif($attachment->isVideo())
                                                        <!-- Video Thumbnail -->
                                                        <div 
                                                            class="relative w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 to-gray-800 cursor-pointer group-hover:from-gray-800 group-hover:to-gray-700 transition-all overflow-hidden"
                                                            @click="previewUrl = '{{ route('attachments.preview', $attachment->id) }}'; previewType = 'video'; previewName = '{{ $attachment->original_filename }}'"
                                                        >
                                                            <div class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-40 z-10">
                                                                <i class="fas fa-play-circle text-white text-5xl mb-2 group-hover:scale-110 transition-transform drop-shadow-lg"></i>
                                                                <span class="text-white text-xs font-medium drop-shadow">Click to play</span>
                                                            </div>
                                                        </div>
                                                    @elseif($attachment->isPdf())
                                                        <!-- PDF Icon -->
                                                        <div class="flex flex-col items-center justify-center text-red-600">
                                                            <i class="fas fa-file-pdf text-4xl mb-2"></i>
                                                            <span class="text-xs">PDF</span>
                                                        </div>
                                                    @else
                                                        <!-- Generic File Icon -->
                                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                                            <i class="fas fa-file text-4xl mb-2"></i>
                                                            <span class="text-xs">FILE</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- File Info -->
                                                <div class="p-2">
                                                    <div class="text-xs font-medium text-gray-900 truncate" title="{{ $attachment->original_filename }}">
                                                        {{ $attachment->original_filename }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $attachment->size > 1048576 ? number_format($attachment->size / 1048576, 2) . ' MB' : number_format($attachment->size / 1024, 1) . ' KB' }}
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <a 
                                                        href="{{ route('attachments.download', $attachment->id) }}" 
                                                        class="px-2 py-1 bg-white rounded shadow-sm text-xs text-gray-700 hover:bg-gray-100"
                                                        title="Download"
                                                    >
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        
                                        <div class="flex justify-end mt-2">
                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Edit Mode -->
                                    <div x-show="isEditing" x-cloak>
                                        <textarea 
                                            x-model="editContent"
                                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"
                                            rows="3"
                                        ></textarea>
                                        <div class="flex items-center justify-end space-x-2 mt-2">
                                            <button 
                                                @click="isEditing = false; editContent = '{{ addslashes($comment->content) }}'"
                                                :disabled="isSaving"
                                                class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition-colors"
                                            >
                                                Cancel
                                            </button>
                                            <button 
                                                @click="updateComment({{ $comment->id }})"
                                                :disabled="!editContent.trim() || isSaving"
                                                class="px-4 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <span x-show="!isSaving">Save</span>
                                                <span x-show="isSaving">
                                                    <i class="fas fa-spinner fa-spin"></i> Saving...
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-comment text-5xl text-gray-300 mb-4"></i>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">No comments yet</h4>
                            <p class="text-sm">Start the conversation by adding the first comment!</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Add Comment Form -->
                    <div class="border-t border-gray-200 pt-6">
                            <div class="flex space-x-4">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-medium text-white">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                </div>
                                <div class="flex-1">
                                    <textarea
                                    x-model="newComment"
                                        placeholder="Add a comment..."
                                        class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        rows="3"
                                    ></textarea>
                                    
                                    <!-- File Attachments Section -->
                                    <div class="mt-4 space-y-4">
                                        <!-- Upload Buttons -->
                                        <div class="flex flex-wrap gap-3">
                                            <button
                                                type="button"
                                                @click="$refs.commentFileInput.click()"
                                                class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                                            >
                                                <i class="fas fa-upload w-4 h-4"></i>
                                                <span>Upload Files</span>
                                            </button>
                                            
                                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                                <div class="flex items-center space-x-1">
                                                    <span class="text-lg">🖼️</span>
                                                    <span>Photos</span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <span class="text-lg">🎥</span>
                                                    <span>Videos</span>
                                                </div>
                                                <div class="flex items-center space-x-1">
                                                    <span class="text-lg">📄</span>
                                                    <span>PDFs</span>
                                                </div>
                                            </div>
                                        </div>

                                        <input
                                            x-ref="commentFileInput"
                                            type="file"
                                            multiple
                                            accept="image/*,video/*,.pdf"
                                            @change="handleCommentFileUpload($event)"
                                            class="hidden"
                                        />
                                        
                                        <p class="text-sm text-gray-500">
                                            <strong>Supported formats:</strong> JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM, PDF<br />
                                            <strong>Maximum size:</strong> 100MB per file • <strong>Multiple files:</strong> Supported
                                        </p>

                                        <!-- File List -->
                                        <template x-if="commentAttachments.length > 0">
                                            <div class="space-y-3">
                                                <h3 class="text-sm font-medium text-gray-900">
                                                    Attached Files (<span x-text="commentAttachments.length"></span>)
                                                </h3>
                                                <div class="space-y-2">
                                                    <template x-for="(file, index) in commentAttachments" :key="index">
                                                        <div class="p-3 bg-gray-50 rounded-lg border" :class="file.error ? 'border-red-300 bg-red-50' : 'border-gray-200'">
                                                            <div class="flex items-center justify-between mb-2">
                                                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0" :class="getCommentFileTypeColor(file)">
                                                                        <span x-html="getCommentFileIcon(file)"></span>
                                                                    </div>
                                                                    <div class="flex-1 min-w-0">
                                                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                                                        <div class="flex items-center space-x-3 text-xs text-gray-500">
                                                                            <span x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                                                                            <span x-text="getCommentFileType(file)"></span>
                                                                            <span x-show="file.uploaded" class="text-green-600 font-medium">✓ Uploaded</span>
                                                                            <span x-show="file.error" class="text-red-600 font-medium">✗ Failed</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <button
                                                                    type="button"
                                                                    @click="removeCommentAttachment(index)"
                                                                    class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors"
                                                                    title="Remove file"
                                                                    :disabled="file.uploading"
                                                                >
                                                                    <i class="fas fa-times text-2xl"></i>
                                                                </button>
                                                            </div>
                                                            
                                                            <!-- Progress Bar -->
                                                            <div x-show="file.uploading" class="mt-2">
                                                                <div class="flex items-center justify-between mb-1">
                                                                    <span class="text-xs text-gray-600">Uploading...</span>
                                                                    <span class="text-xs font-semibold text-blue-600" x-text="file.progress + '%'"></span>
                                                                </div>
                                                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                                    <div 
                                                                        class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                                                                        :style="'width: ' + file.progress + '%'"
                                                                    ></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                
                                <div class="flex items-center justify-between mt-4">
                                    <a 
                                        href="{{ route('projects.show', $task->project_id) }}" 
                                        class="flex items-center space-x-2 px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
                                    >
                                        <i class="fas fa-times"></i>
                                        <span>Cancel</span>
                                    </a>
                                    
                                    <div class="flex items-center space-x-3">
                                        <button
                                            type="button"
                                            @click="addComment"
                                            :disabled="!newComment.trim() || isSavingComment || commentAttachments.some(f => f.uploading)"
                                            class="flex items-center space-x-2 px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i class="fas" :class="isSavingComment ? 'fa-spinner fa-spin' : 'fa-comment'"></i>
                                            <span x-text="isSavingComment ? 'Saving...' : (commentAttachments.some(f => f.uploading) ? 'Uploading files...' : 'Add Comment')"></span>
                                        </button>
                                        
                                        <button
                                            type="button"
                                            @click="saveAllChanges"
                                            :disabled="!hasChanges"
                                            class="flex items-center space-x-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i class="fas fa-save"></i>
                                            <span>Save & Exit</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preview Modal for Comment Attachments -->
                    <div 
                        x-show="previewUrl" 
                        x-cloak
                        @keydown.escape.window="closePreview()"
                        class="fixed inset-0 bg-black/95 backdrop-blur-sm z-[100] flex items-center justify-center p-4"
                        @click.self="closePreview()"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                    >
                        <div class="relative w-full max-w-7xl mx-auto">
                            <!-- Header Bar -->
                            <div class="flex items-center  mb-4 px-2">
                                <div class="text-white text-center mb-4 space-x-2 w-full">
                                    <i class="fas fa-file-alt text-white/80"></i>
                                    <span class="text-white font-medium" x-text="previewName"></span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    
                                    <button 
                                        @click="closePreview()"
                                        class="absolute -top-12 right-0 text-white hover:text-gray-300 transition-colors"
                                        title="Close (ESC)"
                                    >
                                        <i class="fas fa-times text-2xl"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Preview Content -->
                            <div class="relative bg-gray-900/50 rounded-xl overflow-hidden shadow-2xl"
                                 x-transition:enter="transition ease-out duration-300 delay-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100">
                                <!-- Image Preview -->
                                <template x-if="previewType === 'image'">
                                    <div class="flex items-center justify-center min-h-[300px] max-h-[85vh] p-4">
                                        <img 
                                            :src="previewUrl" 
                                            :alt="previewName"
                                            class="max-w-full max-h-full w-auto h-auto object-contain rounded-lg shadow-2xl"
                                            @click.stop
                                        >
                                    </div>
                                </template>
                                
                                <!-- Video Preview -->
                                <template x-if="previewType === 'video'">
                                    <div class="flex items-center justify-center min-h-[300px] max-h-[85vh] bg-black">
                                        <video 
                                            x-ref="previewVideo"
                                            :src="previewUrl" 
                                            controls
                                            autoplay
                                            class="max-w-full max-h-[85vh] w-auto h-auto rounded-lg"
                                            @click.stop
                                        >
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                </template>
                                
                                <!-- PDF Preview -->
                                <template x-if="previewType === 'pdf'">
                                    <div class="flex items-center justify-center min-h-[300px] max-h-[85vh] bg-gray-100">
                                        <iframe 
                                            :src="previewUrl" 
                                            class="w-full h-[85vh] border-0"
                                            @click.stop
                                        ></iframe>
                                    </div>
                                </template>
                            </div>
                            
                           
                        </div>
                    </div>
                </div>

                <!-- Service Call Section -->
                @if($task->serviceCall)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center space-x-3 mb-6">
                        <i class="fas fa-tools text-blue-600 text-xl"></i>
                        <h2 class="text-lg font-semibold text-gray-900">Service Call</h2>
                    </div>

                    <div x-data="serviceCallInfo()" x-init="loadServiceCallOrder()">
                        <div class="space-y-6">
                            <!-- Service Type (Read-only) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Service Type
                                </label>
                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center space-x-2 opacity-50 cursor-not-allowed">
                                        <input
                                            type="radio"
                                            value="none"
                                            disabled
                                            @if($task->serviceCall->service_type === 'none') checked @endif
                                            class="w-4 h-4 text-blue-600 border-gray-300"
                                        />
                                        <span class="text-sm text-gray-700">None</span>
                                    </label>

                                    <label class="flex items-center space-x-2 @if($task->serviceCall->service_type !== 'customer_damage') opacity-50 @endif cursor-not-allowed">
                                        <input
                                            type="radio"
                                            value="customer_damage"
                                            disabled
                                            @if($task->serviceCall->service_type === 'customer_damage') checked @endif
                                            class="w-4 h-4 text-blue-600 border-gray-300"
                                        />
                                        <span class="text-sm text-gray-700">Customer Related Damage</span>
                                    </label>

                                    <label class="flex items-center space-x-2 @if($task->serviceCall->service_type !== 'field_service') opacity-50 @endif cursor-not-allowed">
                                        <input
                                            type="radio"
                                            value="field_service"
                                            disabled
                                            @if($task->serviceCall->service_type === 'field_service') checked @endif
                                            class="w-4 h-4 text-blue-600 border-gray-300"
                                        />
                                        <span class="text-sm text-gray-700">Customer Related - Field Service Required</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Order Details Section -->
                            <div class="space-y-4 pt-4 border-t">
                                <!-- Loading State -->
                                <div x-show="loading" class="flex items-center justify-center py-8">
                                    <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mr-3"></i>
                                    <span class="text-gray-600">Loading order details...</span>
                                </div>

                                <!-- Order Details (after loaded) -->
                                <div x-show="!loading && orderDetails" x-cloak class="space-y-4">
                                    <!-- Customer & Order ID (Read-only) -->
                                    

                                    <!-- Selected Order Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="font-medium text-gray-700">Order ID:</p>
                                                <p class="text-gray-900" x-text="orderDetails?.orderNumber"></p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Email:</p>
                                                <p class="text-blue-600" x-text="orderDetails?.customer?.email || 'N/A'"></p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Customer Name:</p>
                                                <p class="text-gray-900" x-text="orderDetails?.customer ? (orderDetails.customer.firstName + ' ' + orderDetails.customer.lastName) : 'N/A'"></p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Phone:</p>
                                                <p class="text-gray-900" x-text="formatPhone(orderDetails?.customer?.phone)"></p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Company:</p>
                                                <p class="text-gray-900" x-text="orderDetails?.customer?.company || 'N/A'"></p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Billing Address:</p>
                                                <p class="text-gray-900" x-text="orderDetails?.billingAddress || 'N/A'"></p>
                                                <template x-if="orderDetails?.billingAddress && orderDetails?.billingAddress !== 'N/A'">
                                                    <a :href="'https://maps.google.com/?q=' + encodeURIComponent(orderDetails?.billingAddress)"
                                                       target="_blank" 
                                                       class="text-blue-600 text-xs hover:underline inline-block mt-1">
                                                        See on maps
                                                    </a>
                                                </template>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Product:</p>
                                                <p class="text-gray-900" x-text="orderDetails?.product"></p>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-700">Delivery Info:</p>
                                                <template x-if="orderDetails?.shippingAddress === orderDetails?.billingAddress && orderDetails?.billingAddress && orderDetails?.billingAddress !== 'N/A'">
                                                    <p class="text-gray-900 italic">Same as billing information</p>
                                                </template>
                                                <template x-if="orderDetails?.shippingAddress !== orderDetails?.billingAddress || !orderDetails?.billingAddress || orderDetails?.billingAddress === 'N/A'">
                                                    <p class="text-gray-900" x-text="orderDetails?.shippingAddress || 'N/A'"></p>
                                                </template>
                                                <template x-if="orderDetails?.shippingAddress && orderDetails?.shippingAddress !== 'N/A' && orderDetails?.shippingAddress !== orderDetails?.billingAddress">
                                                    <a :href="'https://maps.google.com/?q=' + encodeURIComponent(orderDetails?.shippingAddress)"
                                                       target="_blank" 
                                                       class="text-blue-600 text-xs hover:underline inline-block mt-1">
                                                        See on maps
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Service Notes (if any) -->
                                    @if($task->serviceCall->notes)
                                    <div class="pt-2">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Notes</label>
                                        <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-3">
                                            <p class="text-sm text-gray-900 whitespace-pre-line">{{ $task->serviceCall->notes }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Error State -->
                                <div x-show="!loading && error" x-cloak class="text-center py-8">
                                    <i class="fas fa-exclamation-triangle text-3xl text-red-600 mb-3"></i>
                                    <p class="text-red-600" x-text="error"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Invoice Section (Only shown if task has service call) -->
                @if($task->serviceCall)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="{ showInvoiceSection: false }">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-file-invoice-dollar text-blue-600 text-xl"></i>
                            <h2 class="text-lg font-semibold text-gray-900">Invoices</h2>
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $task->invoices->count() }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                type="button"
                                @click="showInvoiceSection = !showInvoiceSection"
                                class="text-gray-600 hover:text-gray-900 transition-colors"
                            >
                                <i class="fas" :class="showInvoiceSection ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Invoice List (Collapsible) -->
                    <div x-show="showInvoiceSection" x-cloak class="space-y-4">
                        <!-- Create Invoice Form (Embedded) -->
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            @include('tasks.invoices.create_embedded')
                        </div>

                        <!-- Existing Invoices List -->
                        @if($task->invoices->count() > 0)
                        <div class="space-y-3 mt-6">
                            <h3 class="text-md font-semibold text-gray-900 mb-3">Existing Invoices</h3>
                            @foreach($task->invoices as $invoice)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h4 class="font-semibold text-gray-900">{{ $invoice->invoice_number }}</h4>
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ 
                                                $invoice->invoice_status === 'paid' ? 'bg-green-100 text-green-800' :
                                                ($invoice->invoice_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')
                                            }}">
                                                {{ ucfirst($invoice->invoice_status) }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                            <div><span class="font-medium">Customer:</span> {{ $invoice->customer->full_name ?? 'N/A' }}</div>
                                            <div><span class="font-medium">Total:</span> ${{ number_format($invoice->total, 2) }}</div>
                                            <div><span class="font-medium">Date:</span> {{ $invoice->invoice_date->format('M d, Y') }}</div>
                                            <div><span class="font-medium">Due:</span> {{ $invoice->due_date->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                    <a
                                        href="{{ route('tasks.invoices.show', [$task->id, $invoice->id]) }}"
                                        class="ml-4 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm"
                                    >
                                        View
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Task Details</h2>
                    
                    <div class="space-y-4">
                        <!-- Assigned To -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Assigned To</label>
                            @if($task->assignedUser)
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-medium text-blue-600">{{ strtoupper(substr($task->assignedUser->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $task->assignedUser->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($task->assignedUser->role) }}</p>
                                </div>
                            </div>
                            @else
                            <p class="text-sm text-gray-500">Unassigned</p>
                            @endif
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Priority</label>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ 
                                $task->priority === 'urgent' ? 'bg-red-100 text-red-800 border-red-200' :
                                ($task->priority === 'high' ? 'bg-orange-100 text-orange-800 border-orange-200' :
                                ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 border-yellow-200' : 'bg-green-100 text-green-800 border-green-200'))
                            }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>

                        <!-- Dates -->
                        @if($task->start_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                            <p class="text-sm text-gray-900">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($task->start_date)->format('M j, Y') }}
                            </p>
                        </div>
                        @endif

                        @if($task->due_date)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Due Date</label>
                            <p class="text-sm {{ \Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'approved' ? 'text-red-600' : 'text-gray-900' }}">
                                <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                {{ \Carbon\Carbon::parse($task->due_date)->format('M j, Y') }}
                                @if(\Carbon\Carbon::parse($task->due_date)->isPast() && $task->status !== 'approved')
                                <span class="text-xs">(Overdue)</span>
                                @endif
                            </p>
                        </div>
                        @endif

                        <!-- Estimated Hours -->
                        @if($task->estimated_hours)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Estimated Hours</label>
                            <p class="text-sm text-gray-900">
                                <i class="fas fa-clock mr-2 text-gray-400"></i>
                                {{ $task->estimated_hours }} hours
                            </p>
                        </div>
                        @endif

                        <!-- Created By -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Created By</label>
                            <p class="text-sm text-gray-900">{{ $task->creator->name }}</p>
                            <p class="text-xs text-gray-500">{{ $task->created_at->diffForHumans() }}</p>
                        </div>

                        <!-- Task Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Task Type</label>
                            @php
                                $taskTypeLabels = [
                                    'general' => 'General',
                                    'equipmentId' => 'Equipment ID',
                                    'customerName' => 'Customer Name',
                                    'feature' => 'Feature',
                                    'bug' => 'Bug',
                                    'design' => 'Design',
                                ];
                            @endphp
                            <p class="text-sm text-gray-900">{{ $taskTypeLabels[$task->task_type] ?? ucfirst($task->task_type) }}</p>
                        </div>

                        <!-- Project -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Project</label>
                            <a href="{{ route('projects.show', $task->project_id) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                {{ $task->project->name }}
                            </a>
                        </div>

                        <!-- Task List -->
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Task List</label>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $task->taskList->color }}">
                                {{ $task->taskList->name }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($canEditTask)
                    <div class="mt-6 pt-6 border-t border-gray-200  inline-grid grid-cols-2 space-x-2 align-items-center">
                        <a href="{{ route('tasks.edit', $task->id) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-edit mr-2"></i>Edit Task
                        </a>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-trash mr-2"></i>Delete Task
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

@push('scripts')
<script>
function taskChangesManager() {
    return {
        stagedStatus: '{{ $task->task_status }}',
        originalStatus: '{{ $task->task_status }}',
        newComment: '',
        isSavingComment: false,
        commentAttachments: [],
        
        get hasChanges() {
            return this.stagedStatus !== this.originalStatus;
        },
        
        setStatus(status) {
            this.stagedStatus = status;
        },
        
        handleCommentFileUpload(event) {
            const files = Array.from(event.target.files);
            const maxSize = 100 * 1024 * 1024; // 100MB
            
            for (const file of files) {
                if (file.size > maxSize) {
                    alert(`File ${file.name} is too large. Maximum size is 100MB.`);
                    continue;
                }
                
                // Add file to commentAttachments with uploading status
                const fileObj = {
                    file: file,
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    uploading: true,
                    progress: 0,
                    uploaded: false,
                    error: false,
                    tempId: null
                };
                this.commentAttachments.push(fileObj);
                const fileIndex = this.commentAttachments.length - 1;
                
                // Upload file immediately
                this.uploadCommentFile(file, fileIndex);
            }
            
            // Reset file input
            event.target.value = '';
        },
        
        uploadCommentFile(file, fileIndex) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    this.commentAttachments[fileIndex].progress = percentComplete;
                }
            });
            
            // Handle completion
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        this.commentAttachments[fileIndex].uploading = false;
                        this.commentAttachments[fileIndex].uploaded = true;
                        this.commentAttachments[fileIndex].tempId = response.tempId;
                    } catch (error) {
                        this.commentAttachments[fileIndex].uploading = false;
                        this.commentAttachments[fileIndex].error = true;
                    }
                } else {
                    this.commentAttachments[fileIndex].uploading = false;
                    this.commentAttachments[fileIndex].error = true;
                }
            });
            
            // Handle errors
            xhr.addEventListener('error', () => {
                console.error('Upload error');
                this.commentAttachments[fileIndex].uploading = false;
                this.commentAttachments[fileIndex].error = true;
            });
            
            // Send request
            xhr.open('POST', '/api/upload-temp-file', true);
            xhr.send(formData);
        },
        
        removeCommentAttachment(index) {
            const file = this.commentAttachments[index];
            this.commentAttachments.splice(index, 1);
        },
        
        getCommentFileTypeColor(file) {
            if (file.type.startsWith('image/')) return 'bg-green-100 text-green-600';
            if (file.type.startsWith('video/')) return 'bg-purple-100 text-purple-600';
            if (file.type === 'application/pdf') return 'bg-red-100 text-red-600';
            return 'bg-gray-100 text-gray-600';
        },
        
        getCommentFileIcon(file) {
            if (file.type.startsWith('image/')) return '🖼️';
            if (file.type.startsWith('video/')) return '🎥';
            if (file.type === 'application/pdf') return '📄';
            return '📎';
        },
        
        getCommentFileType(file) {
            if (file.type.startsWith('image/')) return 'Photo';
            if (file.type.startsWith('video/')) return 'Video';
            if (file.type === 'application/pdf') return 'PDF';
            return 'File';
        },
        
        async addComment() {
            if (!this.newComment.trim()) return;
            
            // Check if any files are still uploading
            const stillUploading = this.commentAttachments.some(file => file.uploading);
            if (stillUploading) {
                alert('Please wait for all files to finish uploading.');
                return;
            }
            
            // Check if any files failed to upload
            const failedUploads = this.commentAttachments.some(file => file.error);
            if (failedUploads) {
                alert('Some files failed to upload. Please remove them and try again.');
                return;
            }
            
            this.isSavingComment = true;
            
            try {
                // Collect uploaded file IDs
                const uploadedFileIds = this.commentAttachments
                    .filter(file => file.uploaded && file.tempId)
                    .map(file => file.tempId);

                const response = await fetch('{{ route('comments.store', $task->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: this.newComment.trim(),
                        uploaded_files: uploadedFileIds
                    })
                });
                
                if (response.ok) {
                    const responseData = await response.json();
                    console.log('Comment saved successfully:', responseData);
                    // Reload the page to show the new comment
                    window.location.reload();
                } else {
                    const errorData = await response.text();
                    console.error('Failed to save comment:', response.status, errorData);
                    throw new Error('Failed to save comment: ' + response.status);
                }
            } catch (error) {
                console.error('Error saving comment:', error);
                alert('Failed to save comment. Please try again. Check console for details.');
                this.isSavingComment = false;
            }
        },
        
        async updateComment(commentId) {
            const commentElement = event.target.closest('[x-data]');
            const editContent = Alpine.$data(commentElement).editContent;
            
            if (!editContent.trim()) return;
            
            Alpine.$data(commentElement).isSaving = true;
            
            try {
                const response = await fetch(`/tasks/{{ $task->id }}/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: editContent.trim()
                    })
                });
                
                if (response.ok) {
                    // Reload the page to show the updated comment
                    window.location.reload();
                } else {
                    throw new Error('Failed to update comment');
                }
            } catch (error) {
                console.error('Error updating comment:', error);
                alert('Failed to update comment. Please try again.');
                Alpine.$data(commentElement).isSaving = false;
            }
        },
        
        async deleteComment(commentId) {
            try {
                const response = await fetch(`/tasks/{{ $task->id }}/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // Reload the page to remove the deleted comment
                    window.location.reload();
                } else {
                    throw new Error('Failed to delete comment');
                }
            } catch (error) {
                console.error('Error deleting comment:', error);
                alert('Failed to delete comment. Please try again.');
            }
        },
        
        async saveAllChanges() {
            if (!this.hasChanges) return;
            
            try {
                // Show loading state
                const saveBtn = event.target;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Saving...</span>';
                
                // Save status change if different
                if (this.stagedStatus !== this.originalStatus) {
                    await fetch('{{ route('tasks.update-status', $task->id) }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            status: this.stagedStatus
                        })
                    });
                }
                
                // Redirect to dashboard page
                window.location.href = '{{ route('dashboard') }}';
            } catch (error) {
                console.error('Error saving changes:', error);
                alert('Failed to save changes. Please try again.');
                // Re-enable button
                const saveBtn = event.target;
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> <span>Save & Exit</span>';
            }
        }
    }
}

// Generate video thumbnails on page load
document.addEventListener('DOMContentLoaded', function() {
    const videoContainers = document.querySelectorAll('[data-video-url]');
    
    videoContainers.forEach(container => {
        const videoUrl = container.dataset.videoUrl;
        const attachmentId = container.id.replace('video-thumb-', '');
        const canvas = document.getElementById('canvas-' + attachmentId);
        const thumbImg = document.getElementById('thumb-img-' + attachmentId);
        
        if (!videoUrl || !canvas || !thumbImg) return;
        
        // Create a temporary video element
        const video = document.createElement('video');
        video.crossOrigin = 'anonymous';
        video.muted = true;
        video.playsInline = true;
        
        video.addEventListener('loadeddata', function() {
            // Seek to 1 second or 10% of video duration
            const seekTime = Math.min(1, video.duration * 0.1);
            video.currentTime = seekTime;
        });
        
        video.addEventListener('seeked', function() {
            try {
                // Set canvas size to match video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Draw video frame to canvas
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Convert canvas to image
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                thumbImg.src = dataUrl;
                thumbImg.style.display = 'block';
                
                // Clean up
                video.remove();
            } catch (error) {
                console.error('Error generating video thumbnail:', error);
                // Fallback: keep the play icon visible
            }
        });
        
        video.addEventListener('error', function(e) {
            console.error('Error loading video for thumbnail:', e);
            // Fallback: keep the play icon visible
        });
        
        // Start loading the video
        video.src = videoUrl;
        video.load();
    });
});

function serviceCallInfo() {
    return {
        loading: false,
        orderDetails: null,
        error: null,

        formatPhone(phone) {
            if (!phone) return 'N/A';
            // Remove all non-digit characters
            const cleaned = ('' + phone).replace(/\D/g, '');
            // Format as US phone number (XXX) XXX-XXXX
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                return '(' + match[1] + ') ' + match[2] + '-' + match[3];
            }
            // Return original if it doesn't match expected format
            return phone;
        },

        async loadServiceCallOrder() {
            const orderId = '{{ $task->serviceCall->order_id ?? '' }}';
            if (!orderId) {
                this.error = 'No order ID associated with this service call.';
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const response = await fetch(`/api/orders/search?q=${encodeURIComponent(orderId)}`);
                if (!response.ok) {
                    throw new Error('Failed to fetch order details');
                }

                const data = await response.json();
                
                if (data.orders && data.orders.length > 0) {
                    // Find the exact match by order number
                    this.orderDetails = data.orders.find(order => order.orderNumber === orderId) || data.orders[0];
                } else {
                    this.error = 'Order not found';
                }
            } catch (err) {
                console.error('Error loading service call order:', err);
                this.error = 'Failed to load order details. Please try again later.';
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection

