<?php

namespace App\Traits;

trait Helpers
{
    function superAdminCheck()
    {
        return auth()->user()->is_super_admin == 1;
    }
}
