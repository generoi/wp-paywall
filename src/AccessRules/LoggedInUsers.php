<?php

namespace GeneroWP\Paywall\AccessRules;

use GeneroWP\Paywall\Contracts\AccessRule;

class LoggedInUsers implements AccessRule
{
    public function isAllowed(bool $isAllowed, ?int $postId): bool
    {
        if (is_user_logged_in()) {
            return true;
        }

        return $isAllowed;
    }
}
