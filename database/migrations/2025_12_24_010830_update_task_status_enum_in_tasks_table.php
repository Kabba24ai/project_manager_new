<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, modify the enum column to include both old and new values
        DB::statement("ALTER TABLE tasks MODIFY COLUMN task_status ENUM('pending', 'in_progress', 'completed_pending_review', 'completed_approved', 'approved', 'unapproved', 'deployed') DEFAULT 'pending'");
        
        // Then update existing 'completed_approved' values to 'approved'
        DB::statement("UPDATE tasks SET task_status = 'approved' WHERE task_status = 'completed_approved'");
        
        // Finally, remove 'completed_approved' from the enum
        DB::statement("ALTER TABLE tasks MODIFY COLUMN task_status ENUM('pending', 'in_progress', 'completed_pending_review', 'approved', 'unapproved', 'deployed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'completed_approved' back to the enum
        DB::statement("ALTER TABLE tasks MODIFY COLUMN task_status ENUM('pending', 'in_progress', 'completed_pending_review', 'completed_approved', 'approved', 'unapproved', 'deployed') DEFAULT 'pending'");
        
        // Revert 'approved' values back to 'completed_approved'
        DB::statement("UPDATE tasks SET task_status = 'completed_approved' WHERE task_status = 'approved'");
        
        // Remove 'approved' from the enum
        DB::statement("ALTER TABLE tasks MODIFY COLUMN task_status ENUM('pending', 'in_progress', 'completed_pending_review', 'completed_approved', 'unapproved', 'deployed') DEFAULT 'pending'");
    }
};
