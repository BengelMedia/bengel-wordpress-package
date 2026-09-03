<?php

namespace Bengel\Wordpress\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AddAction
{
    public function __construct(
        public string  $hook,
        public int     $priority = 10,
        public ?int    $acceptedArgs = null,
        public ?string $nonce_action = null,
        public ?string $nonce_field = null,
        public ?string $nonce_message = null,
        public ?string $user_capability = null,
        public ?string $user_message = null
    )
    {
    }
}
