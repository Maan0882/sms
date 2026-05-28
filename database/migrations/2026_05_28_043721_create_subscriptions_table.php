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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name');                    // Basic, Pro, Enterprise
            $table->string('plan_code')->unique();          // basic, pro, enterprise
            $table->decimal('price', 10, 2)->default(0);   // monthly price
            $table->string('billing_cycle');                // monthly, yearly
            $table->integer('max_admins')->default(1);
            $table->integer('max_mentors')->default(5);
            $table->integer('max_students')->default(50);
            $table->boolean('is_active')->default(true);
            $table->date('expires_at')->nullable();
            $table->json('features')->nullable();           // extra features list
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('institution_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('institution_name');
            $table->string('contact_email');
            $table->string('status');                       // active, expired, cancelled, trial
            $table->date('started_at');
            $table->date('expires_at');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_subscriptions');
        Schema::dropIfExists('subscriptions');
    }
};
