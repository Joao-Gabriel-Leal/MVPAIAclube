<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class CardPublicUrl
{
    public static function buildValidationUrl(string $token, ?Request $request = null, ?array $candidateIps = null): string
    {
        $relativePath = URL::route('cards.show', $token, false);
        $publicBaseUrl = static::resolveBaseUrl($request, $candidateIps);

        if ($publicBaseUrl) {
            return $publicBaseUrl.$relativePath;
        }

        return URL::route('cards.show', $token);
    }

    public static function resolveBaseUrl(?Request $request = null, ?array $candidateIps = null): ?string
    {
        $configuredBaseUrl = trim((string) config('app.card_public_base_url'));

        if ($configuredBaseUrl !== '') {
            return rtrim($configuredBaseUrl, '/');
        }

        $request ??= app()->bound('request') ? request() : null;

        if (! $request) {
            return null;
        }

        if (! static::isLoopbackHost($request->getHost())) {
            return rtrim($request->getSchemeAndHttpHost(), '/');
        }

        $localNetworkIp = static::resolveLocalNetworkIp($candidateIps);

        if (! $localNetworkIp) {
            return null;
        }

        return static::formatBaseUrl($request->getScheme(), $localNetworkIp, $request->getPort());
    }

    public static function resolveLocalNetworkIp(?array $candidateIps = null): ?string
    {
        $candidateIps ??= static::discoverCandidateIps();

        foreach (array_unique($candidateIps) as $ip) {
            if (static::isPrivateIpv4($ip)) {
                return $ip;
            }
        }

        return null;
    }

    public static function isLoopbackHost(string $host): bool
    {
        return in_array(strtolower($host), ['localhost', '127.0.0.1', '0.0.0.0', '::1', '[::1]'], true);
    }

    protected static function discoverCandidateIps(): array
    {
        $hostname = gethostname();

        if (! $hostname) {
            return [];
        }

        $candidateIps = gethostbynamel($hostname) ?: [];
        $singleIp = gethostbyname($hostname);

        if ($singleIp !== $hostname) {
            $candidateIps[] = $singleIp;
        }

        return array_values(array_filter($candidateIps, fn (mixed $ip) => is_string($ip) && $ip !== ''));
    }

    protected static function isPrivateIpv4(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        if (static::isLoopbackHost($ip) || str_starts_with($ip, '169.254.')) {
            return false;
        }

        [$firstOctet, $secondOctet] = array_map('intval', explode('.', $ip, 3));

        return $firstOctet === 10
            || ($firstOctet === 172 && $secondOctet >= 16 && $secondOctet <= 31)
            || ($firstOctet === 192 && $secondOctet === 168);
    }

    protected static function formatBaseUrl(string $scheme, string $host, int $port): string
    {
        $isStandardPort = ($scheme === 'https' && $port === 443) || ($scheme === 'http' && $port === 80);
        $portSegment = $isStandardPort ? '' : ':'.$port;

        return $scheme.'://'.$host.$portSegment;
    }
}
