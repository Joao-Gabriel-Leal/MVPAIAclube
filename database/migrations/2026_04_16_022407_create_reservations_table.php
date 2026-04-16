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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('club_resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('reserver');
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('guest_count')->default(0);
            $table->decimal('original_amount', 10, 2)->default(0);
            $table->decimal('charged_amount', 10, 2)->default(0);
            $table->string('status')->default('confirmed')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['club_resource_id', 'reservation_date']);
            $table->index(['member_id', 'reservation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
