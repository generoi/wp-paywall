<?php

use GeneroWP\Paywall\Paywall;

class PaywallBlockTest extends WP_UnitTestCase
{
    public function testPaywalledBlock()
    {
        $content = '
            Public content
            <!-- wp:wp-paywall/paywall -->
            Private content
            <!-- /wp:wp-paywall/paywall -->
        ';

        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_content' => $content,
        ]);

        $this->setupPost($postId);
        $content = apply_filters('the_content', get_the_content());

        $this->assertStringContainsString('Public content', $content, 'Public content is displayed when paywalled');
        $this->assertStringNotContainsString('Private content', $content, 'Private content is not displayed when paywalled');

        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTOUT_VALUE);
        $content = apply_filters('the_content', get_the_content());
        $this->assertStringContainsString('Public content', $content, 'Public content is displayed');
        $this->assertStringContainsString('Private content', $content, 'Private content is also displayed when opted out');
    }

    protected function setupPost(int $postId)
    {
        $GLOBALS['post'] = get_post($postId);
        setup_postdata($GLOBALS['post']);
    }
}
