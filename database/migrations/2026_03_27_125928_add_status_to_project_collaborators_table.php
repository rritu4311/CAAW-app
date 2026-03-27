<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_collaborators', function (Blueprint $table) {
            $table->string('status')
                  ->default('pending')
                  ->comment('pending, approved, rejected')
                  ->after('user_id'); // change position if needed
        });
    }

    public function down(): void
    {
        Schema::table('project_collaborators', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
