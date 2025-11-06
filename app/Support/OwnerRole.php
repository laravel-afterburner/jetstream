<?php

namespace App\Support;

class OwnerRole extends Role
{
    /**
     * Create a new role instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('owner', 'Owner', ['*']);
    }
}

