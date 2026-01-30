<div x-data="teamMembersBar()" x-init="loadTeamMembers()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>

    <!-- Team Members Grid -->
    <div x-show="!loading" class="flex flex-wrap gap-6">
        <template x-for="member in teamMembers" :key="member.id">
            <div @click="handleAvatarClick(member)" class="flex flex-col items-center space-y-2 cursor-pointer group">
                <div class="relative">
                    <!-- Avatar Image or Initials -->
                    <template x-if="member.avatar_url">
                        <img 
                            :src="member.avatar_url" 
                            :alt="member.name"
                            class="w-16 h-16 rounded-full object-cover border-2 border-gray-200 group-hover:border-blue-500 transition-colors"
                        />
                    </template>
                    <template x-if="!member.avatar_url">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center border-2 border-gray-200 group-hover:border-blue-500 transition-colors">
                            <span class="text-lg font-medium text-white" x-text="getInitials(member.name)"></span>
                        </div>
                    </template>

                    <!-- Upload Button (only for current user) -->
                    <template x-if="currentUserId === member.id">
                        <button
                            @click.stop="handleUploadClick(member)"
                            class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1 rounded-full hover:bg-blue-700 transition-colors shadow-md"
                            title="Upload avatar"
                        >
                            <i class="fas fa-upload w-3 h-3"></i>
                        </button>
                    </template>

                    <!-- Task Count Badge -->
                   
                </div>

                <!-- Member Info -->
                <div class="text-center">
                    <div class="text-sm font-medium text-gray-900" x-text="member.name"></div>
                    <div class="text-xs text-gray-500" x-text="member.role"></div>
                    <template x-if="member.taskCount > 0">
                        <div class="text-xs text-blue-600 font-medium mt-1">
                            <span x-text="member.taskCount"></span> task<span x-text="member.taskCount !== 1 ? 's' : ''"></span>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Member Tasks Modal -->
    <div x-show="selectedMember && !showUploadModal" 
         x-cloak
         @click.self="closeTasksModal()"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[80vh] overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="selectedMember ? selectedMember.name + '\'s Tasks' : ''"></h3>
                    <button @click="closeTasksModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times w-5 h-5"></i>
                    </button>
                </div>

                <!-- Member Info -->
                <div class="flex items-center space-x-4">
                    <template x-if="selectedMember">
                        <template x-if="selectedMember.avatar_url">
                            <img 
                                :src="selectedMember.avatar_url" 
                                :alt="selectedMember.name"
                                class="w-16 h-16 rounded-full object-cover border-2 border-gray-200"
                            />
                        </template>
                    </template>
                    <template x-if="selectedMember && !selectedMember.avatar_url">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center border-2 border-gray-200">
                            <span class="text-xl font-medium text-white" x-text="getInitials(selectedMember.name)"></span>
                        </div>
                    </template>
                    <template x-if="selectedMember">
                        <div>
                            <div class="text-sm text-gray-500" x-text="selectedMember.email"></div>
                            <div class="text-sm text-gray-500" x-text="selectedMember.role"></div>
                            <div class="text-xs text-blue-600 font-medium mt-1">
                                <span x-text="selectedMember.taskCount"></span> total task<span x-text="selectedMember.taskCount !== 1 ? 's' : ''"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tasks List -->
            <div class="flex-1 overflow-y-auto p-6">
                <!-- Loading Tasks -->
                <div x-show="loadingTasks" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>

                <!-- No Tasks -->
                <div x-show="!loadingTasks && memberTasks.length === 0" class="text-center py-12">
                    <p class="text-gray-500">No tasks assigned</p>
                </div>

                <!-- Tasks Grouped by Project -->
                <div x-show="!loadingTasks && memberTasks.length > 0" class="space-y-6">
                    <template x-for="project in getGroupedTasks()" :key="project.projectId">
                        <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                            <!-- Project Header -->
                            <div class="bg-white px-6 py-4 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-semibold text-gray-900" x-text="project.projectName"></h4>
                                    <div class="flex items-center space-x-3 text-sm font-medium">
                                        <span class="px-2 py-1 rounded bg-red-100 text-red-700 border border-red-200">Unapproved (<span x-text="project.unapprovedCount"></span>)</span>
                                        <span class="px-2 py-1 rounded bg-blue-100 text-blue-700 border border-blue-200">In Progress (<span x-text="project.inProgressCount"></span>)</span>
                                        <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 border border-gray-200">Pending (<span x-text="project.pendingCount"></span>)</span>
                                        <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700 border border-yellow-200">Review (<span x-text="project.reviewCount"></span>)</span>
                                        <span class="px-2 py-1 rounded bg-green-100 text-green-700 border border-green-200">Completed (<span x-text="project.completedCount"></span>)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tasks -->
                            <div class="p-4 space-y-2">
                                <template x-for="task in project.tasks" :key="task.id">
                                    <a
                                        :href="'/tasks/' + task.id"
                                        @click="closeTasksModal()"
                                        class="block w-full text-left px-4 py-3 rounded-lg border transition-all group"
                                        :class="{
                                            'bg-red-50 border-red-200 hover:bg-red-100': task.task_status === 'unapproved',
                                            'bg-blue-50 border-blue-200 hover:bg-blue-100': task.task_status === 'in_progress',
                                            'bg-gray-50 border-gray-200 hover:bg-gray-100': task.task_status === 'pending',
                                            'bg-yellow-50 border-yellow-200 hover:bg-yellow-100': task.task_status === 'completed_pending_review',
                                            'bg-green-50 border-green-200 hover:bg-green-100': ['approved', 'deployed'].includes(task.task_status)
                                        }"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-base font-medium text-gray-900 group-hover:text-blue-600" x-text="task.title"></p>
                                            </div>
                                            <i class="fas fa-external-link-alt w-4 h-4 text-gray-400 group-hover:text-blue-600 ml-3 flex-shrink-0"></i>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Avatar Modal -->
    <div x-show="showUploadModal && selectedMember" 
         x-cloak
         @click.self="closeUploadModal()"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Upload Profile Picture</h3>
                <button @click="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times w-5 h-5"></i>
                </button>
            </div>

            <div class="mb-6 text-center">
                <!-- Current Avatar Preview -->
                <template x-if="selectedMember">
                    <template x-if="selectedMember.avatar_url">
                        <img 
                            :src="selectedMember.avatar_url" 
                            :alt="selectedMember.name"
                            class="w-32 h-32 rounded-full object-cover border-2 border-gray-200 mx-auto mb-4"
                        />
                    </template>
                </template>
                <template x-if="selectedMember && !selectedMember.avatar_url">
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center border-2 border-gray-200 mx-auto mb-4">
                        <span class="text-3xl font-medium text-white" x-text="getInitials(selectedMember.name)"></span>
                    </div>
                </template>

                <p class="text-sm text-gray-500 mb-4">
                    Choose a profile picture (max 5MB, image files only)
                </p>

                <label class="cursor-pointer inline-block">
                    <input
                        x-ref="fileInput"
                        type="file"
                        accept="image/*"
                        @change="handleFileUpload($event)"
                        :disabled="uploadingAvatar"
                        class="hidden"
                    />
                    <span class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 inline-flex items-center space-x-2 transition-colors"
                          :class="{ 'opacity-50 cursor-not-allowed': uploadingAvatar }">
                        <template x-if="!uploadingAvatar">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-upload w-4 h-4"></i>
                                <span>Choose File</span>
                            </div>
                        </template>
                        <template x-if="uploadingAvatar">
                            <div class="flex items-center space-x-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                                <span>Uploading...</span>
                            </div>
                        </template>
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

@push('scripts')
<script>
function teamMembersBar() {
    return {
        teamMembers: [],
        loading: true,
        selectedMember: null,
        showUploadModal: false,
        uploadingAvatar: false,
        memberTasks: [],
        loadingTasks: false,
        currentUserId: {{ Auth::id() }},

        async loadTeamMembers() {
            try {
                this.loading = true;
                const response = await fetch('/api/team-members');
                const data = await response.json();
                this.teamMembers = data.users || [];
            } catch (error) {
                console.error('Failed to load team members:', error);
                this.teamMembers = [];
            } finally {
                this.loading = false;
            }
        },

        async loadMemberTasks(userId) {
            try {
                this.loadingTasks = true;
                const response = await fetch(`/api/users/${userId}/tasks`);
                const data = await response.json();
                this.memberTasks = data.tasks || [];
            } catch (error) {
                console.error('Failed to load member tasks:', error);
                this.memberTasks = [];
            } finally {
                this.loadingTasks = false;
            }
        },

        getInitials(name) {
            return name
                .split(' ')
                .map(n => n[0])
                .join('')
                .toUpperCase()
                .slice(0, 2);
        },

        handleAvatarClick(member) {
            this.selectedMember = member;
            this.loadMemberTasks(member.id);
        },

        handleUploadClick(member) {
            this.selectedMember = member;
            this.showUploadModal = true;
        },

        async handleFileUpload(event) {
            const file = event.target.files?.[0];
            if (!file || !this.selectedMember) {
                console.error('No file or no member selected');
                return;
            }

            console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }

            if (!file.type.startsWith('image/')) {
                alert('Please upload an image file');
                return;
            }

            try {
                this.uploadingAvatar = true;
                console.log('Starting upload for user ID:', this.selectedMember.id);
                
                const reader = new FileReader();
                reader.onloadend = async () => {
                    try {
                        const base64String = reader.result;
                        console.log('Base64 encoded, sending to server...');
                        
                        const response = await fetch(`/api/users/${this.selectedMember.id}/avatar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ avatar: base64String })
                        });

                        const responseData = await response.json();
                        console.log('Server response:', responseData);

                        if (!response.ok) {
                            throw new Error(responseData.error || 'Failed to upload avatar');
                        }

                        console.log('Upload successful, reloading team members...');
                        await this.loadTeamMembers();
                        this.closeUploadModal();
                        alert('Avatar updated successfully!');
                    } catch (error) {
                        console.error('Upload error:', error);
                        alert('Failed to upload avatar: ' + error.message);
                        this.uploadingAvatar = false;
                    }
                };
                
                reader.onerror = () => {
                    console.error('FileReader error');
                    alert('Failed to read file');
                    this.uploadingAvatar = false;
                };
                
                reader.readAsDataURL(file);
            } catch (error) {
                console.error('Failed to upload avatar:', error);
                alert('Failed to upload avatar: ' + error.message);
                this.uploadingAvatar = false;
            }
        },

        closeTasksModal() {
            this.selectedMember = null;
            this.memberTasks = [];
        },

        closeUploadModal() {
            this.showUploadModal = false;
            this.uploadingAvatar = false;
            this.selectedMember = null;
            // Reset file input
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        getGroupedTasks() {
            const grouped = {};
            
            this.memberTasks.forEach(task => {
                const projectKey = task.project_name || 'Unknown Project';
                if (!grouped[projectKey]) {
                    grouped[projectKey] = {
                        projectId: task.project_id,
                        projectName: projectKey,
                        tasks: [],
                        unapprovedCount: 0,
                        inProgressCount: 0,
                        pendingCount: 0,
                        reviewCount: 0,
                        completedCount: 0
                    };
                }
                
                // Add task
                grouped[projectKey].tasks.push(task);
                
                // Count by status
                if (task.task_status === 'unapproved') {
                    grouped[projectKey].unapprovedCount++;
                } else if (task.task_status === 'in_progress') {
                    grouped[projectKey].inProgressCount++;
                } else if (task.task_status === 'pending') {
                    grouped[projectKey].pendingCount++;
                } else if (task.task_status === 'completed_pending_review') {
                    grouped[projectKey].reviewCount++;
                } else if (['approved', 'deployed'].includes(task.task_status)) {
                    grouped[projectKey].completedCount++;
                }
            });

            // Sort tasks by status priority
            Object.values(grouped).forEach(project => {
                project.tasks.sort((a, b) => {
                    const statusOrder = { 
                        'unapproved': 0, 
                        'in_progress': 1, 
                        'pending': 2, 
                        'completed_pending_review': 3,
                        'approved': 4,
                        'deployed': 5
                    };
                    return (statusOrder[a.task_status] || 999) - (statusOrder[b.task_status] || 999);
                });
            });

            return Object.values(grouped);
        }
    };
}
</script>
@endpush
