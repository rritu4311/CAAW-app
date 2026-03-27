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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('name');
            $table->string('client_name')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->date('deadline')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // optional foreign keys (commented out until workspaces table exists)
            // $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('projects');
        Schema::enableForeignKeyConstraints();
    }
};
