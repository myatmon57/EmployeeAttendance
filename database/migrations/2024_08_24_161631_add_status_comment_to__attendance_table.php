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
        Schema::table('Attendance', function (Blueprint $table) {
            $table->boolean('status')->nullable()->default(0);
            $table->string('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Attendance', function (Blueprint $table) {
            if (Schema::hasColumn('Attendance', 'status')) {
                $table->dropColumn('status');
            }

            // Drop the 'comment' column if it exists
            if (Schema::hasColumn('Attendance', 'comment')) {
                $table->dropColumn('comment');
            }
        });
    }
};
