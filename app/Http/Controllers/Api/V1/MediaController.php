<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::query();
        if ($domainId = config('app.domain_id')) {
            $query->where('domain_id', $domainId);
        }
        $media = $query->orderBy('created_at', 'desc')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'filename' => $m->filename,
                'url' => Storage::disk('public')->url($m->path),
                'webp_url' => $m->webp_path ? Storage::disk('public')->url($m->webp_path) : null,
                'size' => $m->size,
                'mime' => $m->mime_type,
                'domain_id' => $m->domain_id,
                'created_at' => $m->created_at,
            ];
        });

        return response()->json(['data' => $media]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'folder' => 'nullable|string',
        ]);

        $domainId = config('app.domain_id');
        $folder = $request->get('folder', 'media');
        $domainFolder = $domainId ? "{$folder}/{$domainId}" : $folder;
        $path = $request->file('file')->store($domainFolder, 'public');
        $fullPath = Storage::disk('public')->path($path);

        // Konversi ke WebP
        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $path);
        $webpFullPath = Storage::disk('public')->path($webpPath);
        $imageInfo = @getimagesize($fullPath);

        if ($imageInfo && in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            $img = match ($imageInfo[2]) {
                IMAGETYPE_JPEG => @imagecreatefromjpeg($fullPath),
                IMAGETYPE_PNG => @imagecreatefrompng($fullPath),
                IMAGETYPE_GIF => @imagecreatefromgif($fullPath),
            };
            if ($img) {
                if ($imageInfo[2] === IMAGETYPE_PNG) {
                    imagealphablending($img, false);
                    imagesavealpha($img, true);
                }
                @imagewebp($img, $webpFullPath, 80);
                imagedestroy($img);
            }
        }

        $media = Media::create([
            'domain_id' => $domainId,
            'filename' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'webp_path' => file_exists($webpFullPath) ? $webpPath : null,
            'mime_type' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
            'user_id' => $request->user()?->id,
        ]);

        return response()->json([
            'message' => 'File uploaded',
            'data' => [
                'id' => $media->id,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'webp_url' => $webpPath ? Storage::disk('public')->url($webpPath) : null,
            ],
        ], 201);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate(['id' => 'required|exists:media,id']);

        $media = Media::findOrFail($request->id);
        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
