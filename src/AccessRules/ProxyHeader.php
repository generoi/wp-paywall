<?php

namespace GeneroWP\Paywall\AccessRules;

use GeneroWP\Paywall\Contracts\AccessRule;

class ProxyHeader implements AccessRule
{
    public function isAllowed(bool $isAllowed, ?int $postId): bool
    {
        $request = absint($_SERVER['HTTP_X_PAYWALL_ACCESS'] ?? 0);
        if ($request === 1) {
            return true;
        }

        return $isAllowed;
    }
}
