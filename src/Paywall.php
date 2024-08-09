<?php

namespace GeneroWP\Paywall;

use GeneroWP\Paywall\AccessRules\Crawlers;
use GeneroWP\Paywall\AccessRules\LoggedInUsers;
use WP_Post;
use Yoast\WP\SEO\Context\Meta_Tags_Context;

/**
 * @see https://developers.google.com/search/docs/appearance/structured-data/paywalled-content
 * @see https://schema.org/isAccessibleForFree
 */
class Paywall
{
    public const META_PAYWALL = 'paywall';

    public const OPTIN_VALUE = 'optin';

    public const OPTOUT_VALUE = 'optout';

    public function __construct(protected Plugin $plugin)
    {
        add_filter('wpseo_schema_webpage', [$this, 'setPaywalledCreativeWork'], 10, 2);
        add_action('wp_headers', [$this, 'addHeaders'], 100);
        add_filter('the_content', [$this, 'filterContent'], PHP_INT_MAX);
    }

    public static function options(): array
    {
        return [
            self::OPTIN_VALUE,
            self::OPTOUT_VALUE,
        ];
    }

    public static function hasAccess(WP_Post|int|null $postId = null): bool
    {
        $postId = $postId instanceof WP_Post ? $postId->ID : $postId;
        $hasPreAccess = apply_filters('wp-paywall/has-access', null, $postId);
        if ($hasPreAccess !== null) {
            return $hasPreAccess;
        }

        $accessRules = apply_filters('wp-paywall/access-rules', [
            Crawlers::class,
            LoggedInUsers::class,
        ]);

        return array_reduce(
            $accessRules,
            fn (bool $isAllowed, string $rule) => (new $rule)->isAllowed($isAllowed, $postId),
            false
        );
    }

    public static function isApplied(WP_Post|int|null $postId = null): bool
    {
        $postId = $postId instanceof WP_Post ? $postId->ID : $postId;
        $isPreApplied = apply_filters('wp-paywall/is-applied', null, $postId);
        if ($isPreApplied !== null) {
            return $isPreApplied;
        }

        if (! $postId) {
            if (! is_singular()) {
                return false;
            }
            $postId = get_the_ID();
        }

        $meta = get_post_meta($postId, self::META_PAYWALL, true);
        if (in_array($meta, [self::OPTIN_VALUE, self::OPTOUT_VALUE])) {
            return $meta === self::OPTIN_VALUE;
        }
        $categories = get_the_terms($postId, 'category') ?: [];
        foreach ($categories as $category) {
            $terms = [
                $category->term_id,
                ...get_ancestors($category->term_id, 'category', 'taxonomy'),
            ];
            foreach ($terms as $termId) {
                $meta = get_term_meta($termId, self::META_PAYWALL, true);
                if (in_array($meta, [self::OPTIN_VALUE, self::OPTOUT_VALUE])) {
                    return $meta === self::OPTIN_VALUE;
                }
            }
        }

        return in_array(get_post_type($postId), ['post']);
    }

    public function setPaywalledCreativeWork(array $data, Meta_Tags_Context $context): array
    {
        if (! $context->post || ! self::isApplied($context->post)) {
            return $data;
        }

        $data['isAccessibleForFree'] = false;
        $data['hasPart'] = [
            '@type' => 'WebPageElement',
            'isAccessibleForFree' => false,
            'cssSelector' => '.is-paywalled',
        ];

        return $data;
    }

    public function addHeaders(array $headers): array
    {
        if (! self::isApplied()) {
            return $headers;
        }
        $headers['Vary'] = 'X-Paywall-Access';
        $headers['X-Robots-Tag'] = 'noarchive';
        $headers['X-Paywall-Access'] = self::hasAccess() ? 1 : 0;

        return $headers;
    }

    public function filterContent(string $content): string
    {
        $postId = get_the_ID();
        if (has_block('wp-paywall/paywall', $postId)) {
            return $content;
        }
        if (! self::isApplied($postId)) {
            return $content;
        }
        if (! self::hasAccess($postId)) {
            return $this->plugin->render('hidden', ['postId' => $postId]);
        }

        return $this->plugin->render('protected', ['content' => $content]);
    }
}
