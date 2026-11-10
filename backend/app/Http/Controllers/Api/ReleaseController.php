<?php

namespace App\Http\Controllers\Api;

use App\Models\ApplicationRelease;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReleaseController {
    public function getLatest(Request $request): JsonResponse {
        $latest = ApplicationRelease::where('is_published', true)
            ->orderBy('version_code', 'desc')
            ->first();

        if (!$latest) {
            return response()->json([
                'has_update' => false,
                'message' => 'No published releases available'
            ], 200);
        }

        $clientVersionCode = (int) $request->query('version_code', 0);
        $hasUpdate = $latest->version_code > $clientVersionCode;

        return response()->json([
            'has_update' => $hasUpdate,
            'version_name' => $latest->version_name,
            'version_code' => $latest->version_code,
            'download_url' => $latest->download_url,
            'sha256_checksum' => $latest->sha256_checksum,
            'file_size_bytes' => $latest->file_size_bytes,
            'release_notes' => $latest->release_notes,
            'is_mandatory' => $latest->is_mandatory
        ], 200);
    }

    public function onGitHubBuildComplete(Request $request): JsonResponse {
        $secret = $request->header('X-Webhook-Secret');
        if ($secret !== config('app.github_webhook_secret', env('GITHUB_WEBHOOK_SECRET'))) {
            return response()->json(['error' => 'Unauthorized webhook signature'], 403);
        }

        $validated = $request->validate([
            'version_name' => 'required|string',
            'version_code' => 'required|integer',
            'sha256' => 'required|string|size:64',
            'download_url' => 'required|url',
            'commit' => 'required|string'
        ]);

        $release = ApplicationRelease::updateOrCreate(
            ['version_code' => $validated['version_code']],
            [
                'version_name' => $validated['version_name'],
                'download_url' => $validated['download_url'],
                'sha256_checksum' => $validated['sha256'],
                'release_type' => 'RELEASE',
                'release_notes' => "Automated build from commit: {$validated['commit']}",
                'is_mandatory' => false,
                'is_published' => true
            ]
        );

        return response()->json([
            'status' => 'REGISTERED',
            'release_id' => $release->id,
            'version_code' => $release->version_code
        ], 201);
    }
}
