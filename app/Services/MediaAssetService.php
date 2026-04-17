<?php

namespace App\Services;

use App\Models\ClubSetting;
use App\Models\MediaAsset;
use App\Models\User;
use App\Support\ClubMediaSlots;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MediaAssetService
{
    public function createFromUpload(
        UploadedFile $file,
        string $context,
        string $visibility,
        ?string $slot = null,
    ): MediaAsset {
        $path = $file->getRealPath();
        $contents = $path ? file_get_contents($path) : false;

        if ($contents === false) {
            throw new RuntimeException('Nao foi possivel ler o arquivo enviado.');
        }

        $binaryStream = $path ? fopen($path, 'rb') : false;

        if ($binaryStream === false) {
            throw new RuntimeException('Nao foi possivel abrir o arquivo enviado em modo binario.');
        }

        $imageSize = $path ? @getimagesize($path) : false;

        return MediaAsset::query()->create([
            'context' => $context,
            'slot' => $slot,
            'visibility' => $visibility,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'size_bytes' => $file->getSize() ?: strlen($contents),
            'width' => $imageSize !== false ? $imageSize[0] : null,
            'height' => $imageSize !== false ? $imageSize[1] : null,
            'checksum' => hash('sha256', $contents),
            'content' => $binaryStream,
        ]);
    }

    public function replaceHomeSlot(ClubSetting $clubSetting, string $slot, UploadedFile $file): void
    {
        $definition = ClubMediaSlots::definition($slot);
        $field = $definition['field'];
        $relation = $definition['relation'];

        $clubSetting->loadMissing($relation);
        $previousAsset = $clubSetting->{$relation};
        $asset = $this->createFromUpload($file, $definition['context'], $definition['visibility'], $slot);

        $clubSetting->{$field} = $asset->id;
        $clubSetting->save();

        $this->deleteIfOrphaned($previousAsset);
    }

    public function replaceClubLogo(ClubSetting $clubSetting, UploadedFile $file): void
    {
        $clubSetting->loadMissing('logoMedia');
        $previousAsset = $clubSetting->logoMedia;
        $asset = $this->createFromUpload($file, 'branding', MediaAsset::VISIBILITY_PUBLIC, 'logo');

        $clubSetting->logo_media_asset_id = $asset->id;
        $clubSetting->save();

        $this->deleteIfOrphaned($previousAsset);
    }

    public function removeHomeSlot(ClubSetting $clubSetting, string $slot): void
    {
        $definition = ClubMediaSlots::definition($slot);
        $field = $definition['field'];
        $relation = $definition['relation'];

        $clubSetting->loadMissing($relation);
        $previousAsset = $clubSetting->{$relation};
        $clubSetting->{$field} = null;
        $clubSetting->save();

        $this->deleteIfOrphaned($previousAsset);
    }

    public function removeClubLogo(ClubSetting $clubSetting): void
    {
        $clubSetting->loadMissing('logoMedia');
        $previousAsset = $clubSetting->logoMedia;
        $clubSetting->logo_media_asset_id = null;
        $clubSetting->save();

        $this->deleteIfOrphaned($previousAsset);
    }

    public function replaceUserProfilePhoto(User $user, UploadedFile $file): void
    {
        $user->loadMissing('profilePhotoMedia');
        $previousAsset = $user->profilePhotoMedia;
        $oldProfilePhotoPath = $user->profile_photo_path;
        $asset = $this->createFromUpload($file, 'profile_photo', MediaAsset::VISIBILITY_PRIVATE, 'profile_photo');

        $user->profile_photo_media_asset_id = $asset->id;
        $user->save();

        if ($oldProfilePhotoPath && Storage::disk('public')->exists($oldProfilePhotoPath)) {
            Storage::disk('public')->delete($oldProfilePhotoPath);
            $user->forceFill(['profile_photo_path' => null])->save();
        }

        $this->deleteIfOrphaned($previousAsset);
    }

    public function deleteIfOrphaned(?MediaAsset $asset): void
    {
        if (! $asset || ! $asset->exists) {
            return;
        }

        $isUsedByClubSettings = ClubSetting::query()
            ->where(function ($query) use ($asset) {
                $query->orWhere('logo_media_asset_id', $asset->id);

                foreach (ClubMediaSlots::foreignKeyColumns() as $column) {
                    $query->orWhere($column, $asset->id);
                }
            })
            ->exists();

        $isUsedByUsers = User::query()
            ->where('profile_photo_media_asset_id', $asset->id)
            ->exists();

        if (! $isUsedByClubSettings && ! $isUsedByUsers) {
            $asset->delete();
        }
    }

    public function importLocalProfilePhotosFromDisk(): int
    {
        if (! Storage::disk('public')) {
            return 0;
        }

        $imported = 0;

        User::query()
            ->whereNull('profile_photo_media_asset_id')
            ->whereNotNull('profile_photo_path')
            ->orderBy('id')
            ->chunkById(50, function ($users) use (&$imported) {
                foreach ($users as $user) {
                    $path = $user->profile_photo_path;

                    if (! $path || ! Storage::disk('public')->exists($path)) {
                        continue;
                    }

                    $contents = Storage::disk('public')->get($path);
                    $absolutePath = Storage::disk('public')->path($path);
                    $imageSize = @getimagesize($absolutePath);
                    $binaryStream = fopen($absolutePath, 'rb');

                    if ($binaryStream === false) {
                        continue;
                    }

                    $asset = MediaAsset::query()->create([
                        'context' => 'profile_photo',
                        'slot' => 'profile_photo',
                        'visibility' => MediaAsset::VISIBILITY_PRIVATE,
                        'original_name' => basename($path),
                        'mime_type' => Storage::disk('public')->mimeType($path) ?: 'application/octet-stream',
                        'size_bytes' => strlen($contents),
                        'width' => $imageSize !== false ? $imageSize[0] : null,
                        'height' => $imageSize !== false ? $imageSize[1] : null,
                        'checksum' => hash('sha256', $contents),
                        'content' => $binaryStream,
                    ]);

                    DB::transaction(function () use ($user, $asset): void {
                        $user->forceFill([
                            'profile_photo_media_asset_id' => $asset->id,
                        ])->save();
                    });

                    $imported++;
                }
            });

        return $imported;
    }
}
