<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_list_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('bg-blue-100');
            $table->json('tasks')->nullable(); // [{title, priority, description}]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_list_templates');
    }
};
