<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\Response;

class MediaAssetController extends Controller
{
    public function show(MediaAsset $mediaAsset): Response
    {
        if (! $mediaAsset->isPublic()) {
            $user = auth()->user();

            if (! $user) {
                abort(403);
            }

            $canViewPrivateMedia = $user->isAdminMatrix()
                || $user->isAdminBranch()
                || $user->profile_photo_media_asset_id === $mediaAsset->id;

            if (! $canViewPrivateMedia) {
                abort(403);
            }
        }

        $content = $mediaAsset->binaryContent();

        return response($content, 200, [
            'Content-Type' => $mediaAsset->mime_type,
            'Content-Length' => (string) strlen($content),
            'Content-Disposition' => 'inline; filename="'.addslashes($mediaAsset->original_name).'"',
            'Cache-Control' => $mediaAsset->isPublic() ? 'public, max-age=86400' : 'private, max-age=3600',
            'ETag' => '"'.$mediaAsset->checksum.'"',
        ]);
    }
}
