<?php

namespace Bengel\Wordpress\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class AddAction
{
    /**
     * @param string $hook
     * @param int $priority
     * @param int|null $acceptedArgs
     * @param array{action: string, field: string, message?:string}|null $nonce
     * @param array{capability: string, message: string}|null $capability
     */
    public function __construct(
        public string  $hook,
        public int     $priority = 10,
        public ?int    $acceptedArgs = null,
        public ?array $nonce = null,
        public ?array $capability = null
    )
    {
    }
}
