<?php

namespace Bengel\Wordpress\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class RestRoute
{
    /**
     * @param string $route_namespace
     * @param string $route
     * @param array|string $methods
     * @param string|null $permission_callback
     * @param array|null $args
     * @param bool $show_in_index
     * @param bool|array|null $allow_batch
     * @param string|array|null $schema
     * @param bool $override
     */
    public function __construct(
        public string $route_namespace,
        public string $route,
        public mixed $methods,
        public ?string $permission_callback = null,
        public ?array $args = [],
        public bool $show_in_index = true,
        public mixed $allow_batch = null,
        public mixed $schema = null,
        public bool $override = false,
    ){}
}
