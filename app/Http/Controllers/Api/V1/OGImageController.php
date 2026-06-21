<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OGImageController extends Controller
{
    public function generate(Request $request)
    {
        $title = $request->get('title', 'BlogCMS');
        $width = 1200;
        $height = 630;

        $img = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($img, 30, 41, 59);
        $accent = imagecolorallocate($img, 37, 99, 235);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        $mutedColor = imagecolorallocate($img, 148, 163, 184);

        imagefill($img, 0, 0, $bg);

        // Accent bar
        imagefilledrectangle($img, 0, 0, 8, $height, $accent);

        // Site name
        $siteName = config('app.name', 'BlogCMS');
        imagestring($img, 3, 40, 40, $siteName, $mutedColor);

        // Title - wrap long text
        $fontSize = 5;
        $maxChars = 50;
        $lines = explode("\n", wordwrap(Str::limit($title, 120), $maxChars, "\n"));
        $y = 180;
        foreach ($lines as $line) {
            imagestring($img, $fontSize, 40, $y, $line, $textColor);
            $y += 40;
        }

        // URL
        imagestring($img, 2, 40, $height - 50, url('/'), $mutedColor);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
