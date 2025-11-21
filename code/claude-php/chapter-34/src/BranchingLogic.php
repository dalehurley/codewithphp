<?php

declare(strict_types=1);

namespace App;

class BranchingLogic
{
    public function branch(callable $condition, callable $trueBranch, callable $falseBranch)
    {
        return fn($input) => $condition($input) ? $trueBranch($input) : $falseBranch($input);
    }
}
