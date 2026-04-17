<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('club_resource_id')->nullable()->constrained('club_resources')->nullOnDelete();
            $table->string('name');
            $table->string('category')->index();
            $table->string('unit', 30);
            $table->decimal('current_quantity', 10, 2)->default(0);
            $table->decimal('minimum_quantity', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
