<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class File
{
    public static function store($attachment, $path): string
    {
        return $attachment->store($path, 's3');
    }

    public static function get($path): string
    {
        return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(10));
    }

    public static function show($path)
    {
        try {
            $file_url = self::get($path);
            $headers = get_headers($file_url, 1);
            $mime_type = 'application/octet-stream';
            if (isset($headers['Content-Type'])) {
                $mime_type = is_array($headers['Content-Type']) ? $headers['Content-Type'][0] : $headers['Content-Type'];
            }
            $file_content = file_get_contents($file_url);

            return response($file_content)
                ->header('Content-Type', $mime_type)
                ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
        } catch (\Throwable $err) {
            throw $err;
        }
    }

    public static function delete($path)
    {
        return Storage::disk('s3')->delete($path);
    }
}
