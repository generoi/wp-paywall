<?php

use GeneroWP\Paywall\Paywall;
use GeneroWP\Paywall\Plugin;

register_block_type(__DIR__.'/block.json', [
    'render_callback' => function (array $attributes, string $content, WP_Block $block) {
        /** @var int|null $postId */
        $postId = $attributes->postId ?? $block->context['postId'] ?? null;
        if (Paywall::isApplied($postId) && ! Paywall::hasAccess($postId)) {
            return Plugin::getInstance()->render('hidden', ['postId' => $postId]);
        }

        return Plugin::getInstance()->render('protected', ['content' => $content]);
    },
]);
