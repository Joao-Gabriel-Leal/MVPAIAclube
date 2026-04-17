<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('id');
            $table->string('hero_title')->nullable()->after('card_prefix');
            $table->text('hero_subtitle')->nullable()->after('hero_title');
            $table->text('about_text')->nullable()->after('hero_subtitle');
            $table->string('primary_color', 7)->nullable()->after('about_text');
            $table->string('secondary_color', 7)->nullable()->after('primary_color');
            $table->string('accent_color', 7)->nullable()->after('secondary_color');
            $table->foreignId('logo_media_asset_id')->nullable()->after('accent_color')->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('logo_media_asset_id');
            $table->dropColumn([
                'brand_name',
                'hero_title',
                'hero_subtitle',
                'about_text',
                'primary_color',
                'secondary_color',
                'accent_color',
            ]);
        });
    }
};
