<?php

namespace App\Services;

use Illuminate\Support\Str;

class FileToFqn
{
    public static function convertFullPath(string $path, string $file): string
    {
        // '/mnt/c/code/server/careers.vodafoneziggo.com/' . 'vendor/spatie/media-library/src/Support/File.php'
        if (Str::contains($file, 'vendor')) {
            $file = Str::after($file, 'vendor/');
        } elseif (Str::contains($file, 'app')) {
            $file = 'app' . Str::after($file, 'app');
        } else {
            $file = str_replace($path, '', $file);
        }

        return self::convert($file);
    }

    public static function convert(string $file): string
    {
        // 'vendor/spatie/media-library/src/Support/File.php'
        $file = self::cleanupFile($file); // spatie/media-libary/Support/File.php

        return Str::of($file)
            ->replace('.php', '')
            ->replace('/', '\\')
            ->studly(); // 'Spatie\MediaLibrary\Support\File'
    }

    private static function findPackage(string $file): string
    {
        $pos = strpos($file, '/');
        $pos2 = strpos($file, '/', $pos + 1);

        return substr($file, 0, $pos2);
    }

    private static function cleanupFile(string $file)
    {
        if (Str::contains($file, 'app')) { // double
            $file = 'App' . Str::after($file, 'app');
        } else {
            if (Str::contains($file, 'vendor')) {
                $file = Str::after($file, 'vendor/');
            }
            $file = str_replace('src/', '', $file);
        }
        return $file;
    }
}
