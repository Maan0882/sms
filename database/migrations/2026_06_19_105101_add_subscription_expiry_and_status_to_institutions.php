<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->date('subscription_expires_at')->nullable()->after('subscription_id');
            $table->string('subscription_status')->default('active')->after('subscription_expires_at'); // active, pending_renewal, pending_cancellation, expired
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['subscription_expires_at', 'subscription_status']);
        });
    }
};
