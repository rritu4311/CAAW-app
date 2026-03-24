<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('asset_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('workflow_id')
                  ->constrained()
                  ->cascadeOnDelete();
            
            $table->foreignId('assigned_to')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'changes_requested'])
                  ->default('pending');
            
            $table->text('decision_reason')->nullable();
            $table->timestamp('decided_at')->nullable();
            
            $table->foreignId('decided_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            $table->integer('order')->default(1); // 1,2,3 for sequential approval order
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['asset_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['workflow_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
