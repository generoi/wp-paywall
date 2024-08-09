<?php

use GeneroWP\Paywall\AccessRules\Crawlers;
use GeneroWP\Paywall\Paywall;

class PaywallCrawlerTest extends WP_UnitTestCase
{
    public function testBotAccess()
    {
        $postId = $this->factory()->post->create([
            'post_type' => 'post',
            'post_status' => 'publish',
            'post_content' => 'Paywalled post',
        ]);

        $this->assertFalse(Paywall::hasAccess($postId), 'Non-bots no not have access');
        $_SERVER['HTTP_USER_AGENT'] = 'Googlebot-Image/1.0';
        $_SERVER['HTTP_CLIENT_IP'] = '66.249.66.96';
        $this->assertTrue(Paywall::hasAccess($postId), 'Bots have access');
        unset($_SERVER['HTTP_USER_AGENT']);
        unset($_SERVER['HTTP_CLIENT_IP']);
    }

    public function testGoogleBot()
    {
        $crawlers = new Crawlers;
        $googleBotIp = '66.249.66.96';

        $this->assertTrue(
            $crawlers->isBot(
                $googleBotIp,
                'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            ),
            'Detects GoogleBot Smartphone',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $googleBotIp,
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Googlebot/2.1; +http://www.google.com/bot.html) Chrome/W.X.Y.Z Safari/537.36'
            ),
            'Detects GoogleBot Desktop #1',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $googleBotIp,
                'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
            ),
            'Detects GoogleBot Desktop #2',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $googleBotIp,
                'Googlebot/2.1 (+http://www.google.com/bot.html)'
            ),
            'Detects GoogleBot Desktop #3',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $googleBotIp,
                'Googlebot-Image/1.0',
            ),
            'Detects GoogleBot Image',
        );
    }

    public function testBingBot()
    {
        $crawlers = new Crawlers;
        $bingBotIp = '51.105.67.0';

        $this->assertTrue(
            $crawlers->isBot(
                $bingBotIp,
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/'
            ),
            'Detects Bingbot #1',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $bingBotIp,
                'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'
            ),
            'Detects Bingbot #2',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $bingBotIp,
                'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/W.X.Y.Z Mobile Safari/537.36  (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'
            ),
            'Detects Bingbot #3',
        );

        $this->assertTrue(
            $crawlers->isBot(
                $bingBotIp,
                'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/W.X.Y.Z Safari/537.36',
            ),
            'Detects BingBotPreview',
        );
    }
}
