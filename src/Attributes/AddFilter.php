<?php

namespace Bengel\Wordpress\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AddFilter
{
    public function __construct(
        public string $hook,
        public int    $priority = 10,
        public ?int   $acceptedArgs = null
    )
    {
    }
}
