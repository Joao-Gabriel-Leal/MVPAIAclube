<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->foreignId('hero_banner_media_asset_id')->nullable()->after('card_prefix')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_featured_media_asset_id')->nullable()->after('hero_banner_media_asset_id')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_1_media_asset_id')->nullable()->after('gallery_featured_media_asset_id')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_2_media_asset_id')->nullable()->after('gallery_1_media_asset_id')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_3_media_asset_id')->nullable()->after('gallery_2_media_asset_id')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_4_media_asset_id')->nullable()->after('gallery_3_media_asset_id')->constrained('media_assets')->nullOnDelete();
            $table->foreignId('gallery_5_media_asset_id')->nullable()->after('gallery_4_media_asset_id')->constrained('media_assets')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profile_photo_media_asset_id')->nullable()->after('profile_photo_path')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_photo_media_asset_id');
        });

        Schema::table('club_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hero_banner_media_asset_id');
            $table->dropConstrainedForeignId('gallery_featured_media_asset_id');
            $table->dropConstrainedForeignId('gallery_1_media_asset_id');
            $table->dropConstrainedForeignId('gallery_2_media_asset_id');
            $table->dropConstrainedForeignId('gallery_3_media_asset_id');
            $table->dropConstrainedForeignId('gallery_4_media_asset_id');
            $table->dropConstrainedForeignId('gallery_5_media_asset_id');
        });
    }
};
