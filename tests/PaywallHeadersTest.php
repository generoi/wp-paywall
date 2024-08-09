<?php

use GeneroWP\Paywall\Paywall;

class PaywallHeaders extends WP_UnitTestCase
{
    public function testRobotsTag()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_content' => 'Paywalled post',
        ]);

        add_action(
            'wp_headers',
            fn (array $headers) => $this->assertEquals($headers['X-Robots-Tag'], 'noarchive'),
            101
        );

        $this->go_to(get_permalink($postId));
    }

    public function testPaywalledTag()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_content' => 'Paywalled post',
        ]);

        $hasPaywallAccess = fn (array $headers) => $this->assertEquals($headers['X-Paywall-Access'], 1, 'User has access');
        $hasNotPaywallAccess = fn (array $headers) => $this->assertEquals($headers['X-Paywall-Access'], 0, 'Use does not have access');
        $isNotPaywalled = fn (array $headers) => $this->assertArrayNotHasKey('X-Paywall-Access', $headers, 'Content is not paywalled');

        add_action('wp_headers', $hasNotPaywallAccess, 101);
        $this->go_to(get_permalink($postId));
        remove_action('wp_headers', $hasNotPaywallAccess, 101);

        add_action('wp_headers', $hasPaywallAccess, 101);
        $userId = $this->factory()->user->create([]);
        wp_set_current_user($userId);
        $this->go_to(get_permalink($postId));
        remove_action('wp_headers', $hasPaywallAccess, 101);

        add_action('wp_headers', $isNotPaywalled, 101);
        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTOUT_VALUE);
        $this->go_to(get_permalink($postId));
        remove_action('wp_headers', $isNotPaywalled, 101);

        $pageId = $this->factory()->post->create([
            'post_type' => 'page',
            'post_status' => 'public',
            'post_content' => 'Public post',
        ]);

        add_action('wp_headers', $isNotPaywalled, 101);
        $this->go_to(get_permalink($pageId));
        remove_action('wp_headers', $isNotPaywalled, 101);
    }
}
