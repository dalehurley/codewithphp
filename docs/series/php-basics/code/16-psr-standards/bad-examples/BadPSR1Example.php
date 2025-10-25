<?php

// ❌ No declare(strict_types=1)
// ❌ No namespace

// ❌ Outputs before class definition (side effects)
echo "Loading user class...";

// ❌ Class name uses snake_case instead of PascalCase
class user_account
{
    // ❌ Method name uses snake_case instead of camelCase
    public function get_user_name()
    {
        return $this->user_name;
    }

    // ❌ Property uses snake_case instead of camelCase
    private $user_name = 'default';

    // ❌ Constant uses camelCase instead of UPPER_CASE
    public const maxLoginAttempts = 5;

    // ❌ Method name doesn't start with verb
    public function username($name)
    {
        $this->user_name = $name;
    }

    // ❌ Boolean method doesn't use is/has/can prefix
    public function active()
    {
        return TRUE; // ❌ Using uppercase TRUE instead of true
    }
}

// ❌ Multiple classes in one file
class helper_functions
{
    // ❌ No visibility modifier
    function doSomething()
    {
        // Implementation
    }
}

// ❌ More side effects (output)
print "Classes loaded successfully";

// ❌ PHP closing tag (should be omitted)
