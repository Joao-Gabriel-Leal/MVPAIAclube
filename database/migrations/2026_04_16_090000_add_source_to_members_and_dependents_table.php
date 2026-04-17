<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('status')->index();
        });

        Schema::table('dependents', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('dependents', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
