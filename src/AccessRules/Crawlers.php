<?php

namespace GeneroWP\Paywall\AccessRules;

use Exception;
use GeneroWP\Paywall\Contracts\AccessRule;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * @see https://www.bing.com/webmasters/help/how-to-verify-bingbot-3905dc26
 * @see https://developers.google.com/search/docs/crawling-indexing/verifying-googlebot
 * @see https://www.bing.com/webmasters/help/which-crawlers-does-bing-use-8c184ec0
 * @see https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers
 */
class Crawlers implements AccessRule
{
    const GOOGLEBOT_IPS = 'https://developers.google.com/static/search/apis/ipranges/googlebot.json';

    const BINGBOT_IPS = 'https://www.bing.com/toolbox/bingbot.json';

    const REMOTE_TTL = DAY_IN_SECONDS;

    public function isAllowed(bool $isAllowed, ?int $postId): bool
    {
        if ($this->isBot()) {
            return true;
        }

        return $isAllowed;
    }

    public function isBot(?string $ip = null, ?string $ua = null): bool
    {
        $ip ??= $this->getIp();
        $ua ??= $_SERVER['HTTP_USER_AGENT'] ?? null;

        return match (true) {
            // Allows Bing and BingPreview
            str_contains($ua, 'bingbot/') => $this->isBingBot($ip),
            // Allows Googlebot, Googlebot-Image, Googlebot-News etc..
            str_contains($ua, 'Googlebot') => $this->isGoogleBot($ip),
            default => false,
        };
    }

    public function isBingBot(string $ip): bool
    {
        return IpUtils::checkIp($ip, $this->getIpRange(self::BINGBOT_IPS));
    }

    public function isGoogleBot(string $ip): bool
    {
        return IpUtils::checkIp($ip, $this->getIpRange(self::GOOGLEBOT_IPS));
    }

    protected function getIpRange(string $url)
    {
        $key = substr(md5($url), 0, 6);
        $ipRanges = get_option("paywall_iprange_$key");
        $lastChecked = get_option("paywall_timestamp_$key");

        $isStale = ! $ipRanges || ! is_numeric($lastChecked) || $lastChecked < (time() - self::REMOTE_TTL);
        if (! $isStale) {
            return $ipRanges;
        }

        try {
            $json = json_decode(file_get_contents($url), null, 512, JSON_THROW_ON_ERROR);
            foreach ($json->prefixes as $entry) {
                $ipRanges[] = $entry->ipv6Prefix ?? $entry->ipv4Prefix;
            }
            update_option("paywall_iprange_$key", $ipRanges, false);
            update_option("paywall_timestamp_$key", time(), false);
        } catch (Exception $e) {
            // If there's a cached version use it.
            if ($ipRanges) {
                error_log(sprintf('wp-paywall: %s', $e->getMessage()));

                return $ipRanges;
            }
            throw $e;
        }

        return $ipRanges;
    }

    protected function getIp(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (! isset($_SERVER[$key])) {
                continue;
            }
            foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }

        return '';
    }
}
