<?php

use GeneroWP\Paywall\Paywall;

class PaywallAccessTest extends WP_UnitTestCase
{
    public function testUserLogin()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'public',
            'post_content' => 'Paywalled post',
        ]);

        $this->assertFalse(Paywall::hasAccess($postId), 'Signed out users do not have access to content');

        $userId = $this->factory()->user->create([]);
        wp_set_current_user($userId);

        $this->assertTrue(Paywall::hasAccess($postId), 'Signed in users have access to content');
    }
}
