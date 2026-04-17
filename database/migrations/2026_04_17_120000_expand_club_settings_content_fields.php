<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->string('login_title')->nullable();
            $table->text('login_subtitle')->nullable();
            $table->string('home_about_title')->nullable();
            $table->string('home_gallery_title')->nullable();
            $table->text('home_gallery_subtitle')->nullable();
            $table->string('home_branches_title')->nullable();
            $table->text('home_branches_subtitle')->nullable();
            $table->string('home_plans_title')->nullable();
            $table->text('home_plans_subtitle')->nullable();
            $table->string('home_final_cta_title')->nullable();
            $table->text('enrollment_intro')->nullable();
            $table->text('enrollment_notice')->nullable();
            $table->foreignId('recommended_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('site_email')->nullable();
            $table->string('site_phone', 30)->nullable();
            $table->string('site_whatsapp', 30)->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('club_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recommended_plan_id');
            $table->dropColumn([
                'login_title',
                'login_subtitle',
                'home_about_title',
                'home_gallery_title',
                'home_gallery_subtitle',
                'home_branches_title',
                'home_branches_subtitle',
                'home_plans_title',
                'home_plans_subtitle',
                'home_final_cta_title',
                'enrollment_intro',
                'enrollment_notice',
                'site_email',
                'site_phone',
                'site_whatsapp',
                'instagram_url',
                'facebook_url',
                'seo_title',
                'seo_description',
            ]);
        });
    }
};
