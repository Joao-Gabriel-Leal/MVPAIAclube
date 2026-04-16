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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('dependent_limit')->default(0);
            $table->unsignedSmallInteger('guest_limit_per_reservation')->default(0);
            $table->unsignedSmallInteger('free_reservations_per_month')->default(0);
            $table->string('extra_reservation_discount_type')->default('none');
            $table->decimal('extra_reservation_discount_value', 10, 2)->default(0);
            $table->boolean('dependents_inherit_benefits')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });

        Schema::dropIfExists('plans');
    }
};
