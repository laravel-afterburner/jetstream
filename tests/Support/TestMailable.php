<?php

namespace Tests\Support;

use Illuminate\Mail\Mailable;

class TestMailable extends Mailable
{
    public function build()
    {
        return $this->to('failed@example.com')
            ->subject('Test')
            ->html('body');
    }
}
