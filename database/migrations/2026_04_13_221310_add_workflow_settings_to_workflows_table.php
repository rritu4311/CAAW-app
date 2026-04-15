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
        Schema::table('workflows', function (Blueprint $table) {
            $table->enum('type', ['single', 'sequential', 'parallel', 'custom'])->default('custom')->after('name');
            $table->integer('deadline_hours')->nullable()->after('definition');
            $table->boolean('auto_route_next')->default(true)->after('deadline_hours');
            $table->boolean('require_comments')->default(false)->after('auto_route_next');
            $table->integer('send_reminder_hours')->default(24)->after('require_comments');
            $table->boolean('allow_rejection')->default(true)->after('send_reminder_hours');
            $table->boolean('is_active')->default(true)->after('allow_rejection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'deadline_hours',
                'auto_route_next',
                'require_comments',
                'send_reminder_hours',
                'allow_rejection',
                'is_active'
            ]);
        });
    }
};
