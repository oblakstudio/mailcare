<?php

namespace App\Events;

use App\Email;
use Illuminate\Queue\SerializesModels;

class EmailReceived
{
    use SerializesModels;

    public $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }
}
