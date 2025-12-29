<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample users
        $admin = User::create([
            'unique_id' => 'EMP001',
            'employee_code' => 'E001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@taskmaster.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'Active',
            'start_date' => now()->subYears(2),
        ]);

        $manager = User::create([
            'unique_id' => 'EMP002',
            'employee_code' => 'E002',
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'sarah.johnson@taskmaster.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'status' => 'Active',
            'start_date' => now()->subYears(1),
        ]);

        $dev1 = User::create([
            'unique_id' => 'EMP003',
            'employee_code' => 'E003',
            'first_name' => 'Mike',
            'last_name' => 'Chen',
            'email' => 'mike.chen@taskmaster.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'status' => 'Active',
            'start_date' => now()->subMonths(8),
        ]);

        $dev2 = User::create([
            'unique_id' => 'EMP004',
            'employee_code' => 'E004',
            'first_name' => 'David',
            'last_name' => 'Kim',
            'email' => 'david.kim@taskmaster.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'status' => 'Active',
            'start_date' => now()->subMonths(6),
        ]);

        $designer = User::create([
            'unique_id' => 'EMP005',
            'employee_code' => 'E005',
            'first_name' => 'Emily',
            'last_name' => 'Rodriguez',
            'email' => 'emily.rodriguez@taskmaster.com',
            'password' => Hash::make('password'),
            'role' => 'designer',
            'status' => 'Active',
            'start_date' => now()->subMonths(10),
        ]);

        $dev3 = User::create([
            'unique_id' => 'EMP006',
            'employee_code' => 'E006',
            'first_name' => 'Lisa',
            'last_name' => 'Wang',
            'email' => 'lisa.wang@taskmaster.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'status' => 'Active',
            'start_date' => now()->subMonths(5),
        ]);

        // Create sample project
        $project = Project::create([
            'name' => 'E-commerce Platform Redesign',
            'description' => 'Complete overhaul of the existing e-commerce platform with modern UI/UX principles and improved performance.',
            'status' => 'active',
            'priority' => 'high',
            'start_date' => '2024-01-15',
            'due_date' => '2024-04-30',
            'budget' => 75000.00,
            'client' => 'TechCorp Solutions',
            'objectives' => [
                'Improve user experience and conversion rates by 25%',
                'Implement modern responsive design for all devices',
                'Optimize performance and reduce loading times by 40%'
            ],
            'deliverables' => [
                'New responsive website design and implementation',
                'Mobile-optimized user interface with PWA features',
                'Performance optimization report and implementation'
            ],
            'tags' => ['web', 'ecommerce', 'redesign', 'ui/ux', 'performance'],
            'created_by' => $admin->id,
            'project_manager_id' => $manager->id,
        ]);

        // Attach team members
        $project->teamMembers()->attach([
            $manager->id => ['role' => 'manager'],
            $dev1->id => ['role' => 'member'],
            $designer->id => ['role' => 'member'],
            $dev2->id => ['role' => 'member'],
        ]);

        // Create task lists
        $todoList = TaskList::create([
            'project_id' => $project->id,
            'name' => 'To Do',
            'description' => 'Tasks that are planned but not yet started',
            'color' => 'bg-gray-100',
            'order' => 1,
        ]);

        $inProgressList = TaskList::create([
            'project_id' => $project->id,
            'name' => 'In Progress',
            'description' => 'Tasks currently being worked on',
            'color' => 'bg-blue-100',
            'order' => 2,
        ]);

        $reviewList = TaskList::create([
            'project_id' => $project->id,
            'name' => 'Review',
            'description' => 'Tasks completed and awaiting review',
            'color' => 'bg-yellow-100',
            'order' => 3,
        ]);

        $doneList = TaskList::create([
            'project_id' => $project->id,
            'name' => 'Done',
            'description' => 'Completed and approved tasks',
            'color' => 'bg-green-100',
            'order' => 4,
        ]);

        // Create sample tasks
        Task::create([
            'project_id' => $project->id,
            'task_list_id' => $inProgressList->id,
            'title' => 'Create wireframes for homepage',
            'description' => 'Design wireframes for the new homepage layout including hero section, navigation, and footer components.',
            'priority' => 'high',
            'task_type' => 'design',
            'task_status' => 'in_progress',
            'assigned_to' => $designer->id,
            'created_by' => $admin->id,
            'start_date' => '2024-01-20',
            'due_date' => '2024-01-30',
            'estimated_hours' => 16,
            'tags' => ['wireframes', 'homepage', 'design'],
        ]);

        Task::create([
            'project_id' => $project->id,
            'task_list_id' => $todoList->id,
            'title' => 'Implement user authentication system',
            'description' => 'Develop secure user login, registration, and password reset functionality with OAuth integration.',
            'priority' => 'urgent',
            'task_type' => 'feature',
            'task_status' => 'pending',
            'assigned_to' => $dev1->id,
            'created_by' => $admin->id,
            'start_date' => '2024-02-01',
            'due_date' => '2024-02-15',
            'estimated_hours' => 32,
            'tags' => ['authentication', 'security', 'backend'],
        ]);

        Task::create([
            'project_id' => $project->id,
            'task_list_id' => $reviewList->id,
            'title' => 'Product catalog page optimization',
            'description' => 'Optimize product listing page for better performance and user experience with advanced filtering.',
            'priority' => 'medium',
            'task_type' => 'general',
            'task_status' => 'completed_pending_review',
            'assigned_to' => $dev2->id,
            'created_by' => $admin->id,
            'start_date' => '2024-01-25',
            'due_date' => '2024-02-10',
            'estimated_hours' => 24,
            'tags' => ['optimization', 'catalog', 'performance'],
        ]);

        Task::create([
            'project_id' => $project->id,
            'task_list_id' => $doneList->id,
            'title' => 'Set up project infrastructure',
            'description' => 'Configure development environment, version control, CI/CD pipeline, and deployment infrastructure.',
            'priority' => 'high',
            'task_type' => 'general',
            'task_status' => 'completed_approved',
            'assigned_to' => $dev1->id,
            'created_by' => $admin->id,
            'start_date' => '2024-01-10',
            'due_date' => '2024-01-20',
            'estimated_hours' => 16,
            'actual_hours' => 18,
            'tags' => ['infrastructure', 'devops', 'setup'],
        ]);
    }
}
