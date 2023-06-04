<?php

namespace Bnoordsij\ClassTrees\Services;

use Illuminate\Support\Str;

class FqnToFile
{
    public const PACKAGE_NAMES = [
        'modules' => 'laravel-modules',
    ];

    public static function convert(string $path, string $fqn): string
    {
        // 'Spatie\MediaLibrary\Support\File'
        if (Str::startsWith(strtolower($fqn), 'app')) {
            $file = 'app' . substr($fqn, 3);
        } elseif (Str::startsWith(strtolower($fqn), 'illuminate')) {
            $file = 'vendor/laravel/framework/src/' . $fqn;
        } else {
            $package = $folder = self::findPackage($fqn);
            $folder = 'vendor/' . str_replace('\\-', '\\', Str::kebab(Str::camel($folder))) . '/src/Code'; // vendor/spatie\media-library/src
            $folder = self::replacePackageNames($folder);

            $file = str_replace($package, $folder, $fqn);
        }

        $file = str_replace('\\', '/', $file) . '.php'; // vendor/spatie/media-library/src/Support/File.php

        return Str::finish($path, '/') . $file;
        // '/mnt/c/code/server/careers.vodafoneziggo.com/' . 'vendor/spatie/media-library/src/Support/File.php'
    }

    public static function findPackage(string $fqn): string
    {
        if (Str::startsWith(strtolower($fqn), 'app')) {
            return 'App';
        }

        $pos = strpos($fqn, '\\');
        $pos2 = strpos($fqn, '\\', $pos + 1);

        return substr($fqn, 0, $pos2);
    }

    private static function replacePackageNames(string $folder): string
    {
        foreach (self::PACKAGE_NAMES as $key => $value) {
            $folder = str_replace($key, $value, $folder);
        }

        return $folder;
    }
}
