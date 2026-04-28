<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('team', 50)->nullable()->after('event_category_id');
            $table->unsignedInteger('reminder_minutes')->nullable()->after('team');
            $table->string('recurrence_pattern', 30)->nullable()->after('reminder_minutes');
            $table->date('recurrence_until')->nullable()->after('recurrence_pattern');
            $table->string('recurrence_group', 36)->nullable()->after('recurrence_until');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'team',
                'reminder_minutes',
                'recurrence_pattern',
                'recurrence_until',
                'recurrence_group',
            ]);
        });
    }
};
