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
        Schema::create('table_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('file_path');
            $table->enum('file_type', ['image', 'video']);
            $table->boolean('is_fake')->nullable();
            $table->float('confidence_score')->nullable();
            $table->timestamp('check_date')->nullable();
            $table->text('report_reason')->nullable();
            $table->timestamp('report_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_files');
    }
};
