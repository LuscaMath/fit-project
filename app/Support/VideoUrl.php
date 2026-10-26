<?php

namespace App\Support;

use Illuminate\Support\Str;

final class VideoUrl
{
    private const ALLOWED_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'm.youtube.com',
        'youtu.be',
        'vimeo.com',
        'www.vimeo.com',
        'player.vimeo.com',
    ];

    public static function isAllowed(?string $url): bool
    {
        if ($url === null || $url === '') {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && in_array(Str::lower($host), self::ALLOWED_HOSTS, true);
    }

    public static function embedUrl(?string $url): ?string
    {
        if (! self::isAllowed($url) || $url === null || $url === '') {
            return null;
        }

        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? null;

            return self::youtubeEmbedUrl($videoId);
        }

        if ($host === 'youtu.be') {
            return self::youtubeEmbedUrl(trim((string) parse_url($url, PHP_URL_PATH), '/'));
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            preg_match('#/(\d+)(?:$|/)#', (string) parse_url($url, PHP_URL_PATH), $matches);

            return isset($matches[1]) ? 'https://player.vimeo.com/video/'.$matches[1] : null;
        }

        return null;
    }

    private static function youtubeEmbedUrl(mixed $videoId): ?string
    {
        if (! is_string($videoId) || ! preg_match('/^[A-Za-z0-9_-]{6,}$/', $videoId)) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$videoId;
    }
}
