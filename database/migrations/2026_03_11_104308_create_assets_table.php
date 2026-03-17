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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('name');
            $table->string('file_path'); // S3 key
            $table->enum('file_type', ['image', 'video', 'pdf', 'doc']);
            $table->bigInteger('file_size')->nullable();
            $table->enum('status', [
                'draft',
                'in_review',
                'approved',
                'rejected',
                'changes_requested'
            ])->default('draft');
            $table->unsignedBigInteger('uploaded_by');
            $table->float('version')->default(1.0);
            $table->unsignedBigInteger('current_version_id')->nullable();
            $table->timestamps();

            // Foreign keys (optional but recommended)
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
