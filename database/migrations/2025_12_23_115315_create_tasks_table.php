<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_list_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('task_type', ['general', 'equipmentId', 'customerName', 'feature', 'bug', 'design'])->default('general');
            $table->enum('task_status', ['pending', 'in_progress', 'completed_pending_review', 'approved', 'unapproved', 'deployed'])->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('estimated_hours')->nullable();
            $table->integer('actual_hours')->nullable();
            $table->json('tags')->nullable();
            $table->text('feedback')->nullable();
            $table->integer('equipment_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->timestamps();
            
            $table->index(['task_list_id', 'created_at']);
            $table->index(['assigned_to', 'due_date']);
            $table->index(['priority', 'due_date']);
            $table->index('task_status');
            $table->index(['project_id', 'task_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
