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
        Schema::create('asset_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->float('version_number');
            $table->string('name');
            $table->string('file_path');
            $table->enum('file_type', ['image', 'video', 'pdf', 'doc']);
            $table->bigInteger('file_size')->nullable();
            $table->string('hash')->nullable();
            $table->enum('status', [
                'draft',
                'in_review',
                'approved',
                'rejected',
                'changes_requested'
            ])->default('draft');
            $table->unsignedBigInteger('uploaded_by');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('asset_id')
                  ->references('id')
                  ->on('assets')
                  ->onDelete('cascade');

            $table->foreign('uploaded_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_versions');
    }
};
