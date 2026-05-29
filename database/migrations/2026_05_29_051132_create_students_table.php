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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained('mentors')->nullOnDelete();
 
            // Identity
            $table->string('student_id', 50)->nullable()->unique(); // roll number
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 191)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
 
            // Address
            $table->string('address_line1', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 2)->nullable(); // ISO 3166-1 alpha-2
 
            // Enrollment
            $table->date('enrollment_date');
            $table->enum('enrollment_status', ['enrolled', 'pending', 'suspended', 'graduated', 'dropped'])
                  ->default('enrolled');
            $table->text('notes')->nullable();
 
            $table->softDeletes();
            $table->timestamps();
 
            $table->index(['institution_id', 'enrollment_status']);
            $table->index(['program_id', 'cohort_id']);
            $table->index('mentor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
