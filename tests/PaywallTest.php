<?php

use GeneroWP\Paywall\Paywall;

class PaywallTest extends WP_UnitTestCase
{
    public function testPaywalledPosts()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'public',
            'post_content' => 'Paywalled post',
        ]);

        $this->assertTrue(Paywall::isApplied($postId), 'By default posts are paywalled.');

        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTIN_VALUE);
        $this->assertTrue(Paywall::isApplied($postId), 'Still paywalled with optin setting');

        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTOUT_VALUE);
        $this->assertFalse(Paywall::isApplied($postId), 'Not paywalled with optout setting');
    }

    public function testPaywalledPages()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'page',
            'post_status' => 'public',
            'post_content' => 'Paywalled post',
        ]);
        $this->assertFalse(Paywall::isApplied($postId), 'By default pages are public.');

        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTOUT_VALUE);
        $this->assertFalse(Paywall::isApplied($postId), 'Still public with manual opt-out setting');

        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTIN_VALUE);
        $this->assertTrue(Paywall::isApplied($postId), 'Paywalled with optin setting');
    }

    public function testPaywalledContent()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'public',
            'post_content' => 'Paywalled post',
        ]);

        $this->setupPost($postId);
        $content = apply_filters('the_content', get_the_content());

        $this->assertStringContainsString('You need to have an active subscription', $content);
        $this->assertStringNotContainsString('Paywalled post', $content);

        update_post_meta($postId, Paywall::META_PAYWALL, Paywall::OPTOUT_VALUE);
        $content = apply_filters('the_content', get_the_content());
        $this->assertStringContainsString('Paywalled post', $content);
    }

    public function testPaywalledCategoryInheritance()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'public',
            'post_content' => 'Paywalled post',
        ]);

        $publicParentCategory = $this->factory()->category->create([
            'taxonomy' => 'category',
            'name' => 'Parent Public',
        ]);
        $publicChildCategory = $this->factory()->category->create([
            'taxonomy' => 'category',
            'name' => 'Child Public',
            'parent' => $publicParentCategory,
        ]);
        $privateChildCategory = $this->factory()->category->create([
            'taxonomy' => 'category',
            'name' => 'Child Private',
            'parent' => $publicParentCategory,
        ]);

        update_term_meta($publicParentCategory, Paywall::META_PAYWALL, Paywall::OPTOUT_VALUE);
        update_term_meta($privateChildCategory, Paywall::META_PAYWALL, Paywall::OPTIN_VALUE);

        $this->assertTrue(Paywall::isApplied($postId), 'By default posts are paywalled.');

        wp_set_object_terms($postId, [$publicParentCategory], 'category');
        $this->assertFalse(Paywall::isApplied($postId), 'Opt-out category is public');

        wp_set_object_terms($postId, [$publicChildCategory], 'category');
        $this->assertFalse(Paywall::isApplied($postId), 'Opt-out category is inherited');

        wp_set_object_terms($postId, [$privateChildCategory], 'category');
        $this->assertTrue(Paywall::isApplied($postId), 'Opt-in child category is paywalled.');
    }

    protected function setupPost(int $postId)
    {
        $GLOBALS['post'] = get_post($postId);
        setup_postdata($GLOBALS['post']);
    }
}
