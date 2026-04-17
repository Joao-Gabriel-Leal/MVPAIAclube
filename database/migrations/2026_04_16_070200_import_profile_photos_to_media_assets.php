<?php

use App\Models\MediaAsset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (! Storage::disk('public')) {
            return;
        }

        DB::table('users')
            ->whereNull('profile_photo_media_asset_id')
            ->whereNotNull('profile_photo_path')
            ->orderBy('id')
            ->chunkById(50, function ($users): void {
                foreach ($users as $user) {
                    if (! $user->profile_photo_path || ! Storage::disk('public')->exists($user->profile_photo_path)) {
                        continue;
                    }

                    $contents = Storage::disk('public')->get($user->profile_photo_path);
                    $absolutePath = Storage::disk('public')->path($user->profile_photo_path);
                    $imageSize = @getimagesize($absolutePath);
                    $binaryStream = fopen($absolutePath, 'rb');

                    if ($binaryStream === false) {
                        continue;
                    }

                    $assetId = DB::table('media_assets')->insertGetId([
                        'context' => 'profile_photo',
                        'slot' => 'profile_photo',
                        'visibility' => MediaAsset::VISIBILITY_PRIVATE,
                        'original_name' => basename($user->profile_photo_path),
                        'mime_type' => Storage::disk('public')->mimeType($user->profile_photo_path) ?: 'application/octet-stream',
                        'size_bytes' => strlen($contents),
                        'width' => $imageSize !== false ? $imageSize[0] : null,
                        'height' => $imageSize !== false ? $imageSize[1] : null,
                        'checksum' => hash('sha256', $contents),
                        'content' => $binaryStream,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'profile_photo_media_asset_id' => $assetId,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')->update([
            'profile_photo_media_asset_id' => null,
            'updated_at' => now(),
        ]);

        DB::table('media_assets')
            ->where('context', 'profile_photo')
            ->where('slot', 'profile_photo')
            ->delete();
    }
};
