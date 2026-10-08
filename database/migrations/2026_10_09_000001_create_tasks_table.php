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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('lecturer')->nullable();
            $table->date('deadline_date');
            $table->time('deadline_time');
            $table->string('priority', 10)->default('medium');
            $table->unsignedInteger('reminder_minutes')->default(1440);
            $table->string('status', 20)->default('not_started');
            $table->timestamp('reminded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'deadline_date']);
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
