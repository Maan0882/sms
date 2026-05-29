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
        if (!Schema::hasTable('mentors')) {
            Schema::create('mentors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->unsignedBigInteger('institution_id')->nullable();
 
                // Personal info
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email', 191)->unique();
                $table->string('phone', 20)->nullable();
                $table->string('avatar')->nullable();
 
                // Professional details
                $table->string('designation', 150)->nullable();
                $table->string('expertise', 255)->nullable();
                $table->text('bio')->nullable();
                $table->unsignedSmallInteger('max_students')->default(10);
 
                // Status
                $table->enum('status', ['active', 'inactive', 'on_leave'])->default('active');
 
                $table->softDeletes();
                $table->timestamps();
 
                $table->index(['institution_id', 'status']);
            });
        }
 
        // Pivot: mentor <-> program
        if (!Schema::hasTable('mentor_program')) {
            Schema::create('mentor_program', function (Blueprint $table) {
                $table->foreignId('mentor_id')->constrained()->cascadeOnDelete();
                $table->foreignId('program_id')->constrained()->cascadeOnDelete();
                $table->primary(['mentor_id', 'program_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_program');
        Schema::dropIfExists('mentors');
    }
};
