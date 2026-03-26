<?php

namespace App\Support;

use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;

class ViteAssets
{
    /**
     * @var array<string, bool>
     */
    private static array $hotFileStatus = [];

    /**
     * @param  list<string>  $entries
     */
    public static function tags(array $entries, ?string $hotFile = null): HtmlString
    {
        $hotFile ??= public_path('hot');

        $vite = clone app(Vite::class);

        if (self::shouldUseHotServer($hotFile)) {
            return $vite->useHotFile($hotFile)($entries);
        }

        return $vite->useHotFile(self::disabledHotFilePath())($entries);
    }

    public static function shouldUseHotServer(?string $hotFile = null): bool
    {
        $hotFile ??= public_path('hot');

        if (array_key_exists($hotFile, self::$hotFileStatus)) {
            return self::$hotFileStatus[$hotFile];
        }

        if (! is_file($hotFile) || ! is_readable($hotFile)) {
            return self::$hotFileStatus[$hotFile] = false;
        }

        $url = trim((string) file_get_contents($hotFile));

        if ($url === '') {
            return self::$hotFileStatus[$hotFile] = false;
        }

        return self::$hotFileStatus[$hotFile] = self::canReachHotServer($url);
    }

    public static function resetCache(): void
    {
        self::$hotFileStatus = [];
    }

    private static function disabledHotFilePath(): string
    {
        return storage_path('framework/vite.hot.disabled');
    }

    private static function canReachHotServer(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $port = parse_url($url, PHP_URL_PORT);

        if (! is_string($host) || $host === '') {
            return false;
        }

        $transport = $scheme === 'https' ? 'tls' : 'tcp';
        $port = is_int($port) ? $port : ($scheme === 'https' ? 443 : 80);
        $formattedHost = str_contains($host, ':') ? "[{$host}]" : $host;

        $connection = @stream_socket_client(
            "{$transport}://{$formattedHost}:{$port}",
            $errorNumber,
            $errorMessage,
            0.2
        );

        if (! is_resource($connection)) {
            return false;
        }

        fclose($connection);

        return true;
    }
}
