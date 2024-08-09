<?php

namespace GeneroWP\Paywall\Contracts;

interface AccessRule
{
    public function isAllowed(bool $isAllowed, ?int $postId): bool;
}
