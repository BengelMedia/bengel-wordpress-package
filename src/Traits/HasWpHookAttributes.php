<?php

namespace Bengel\Wordpress\Traits;

use Bengel\Wordpress\Attributes\AddAction;
use Bengel\Wordpress\Attributes\AddFilter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

trait HasWpHookAttributes
{
    private function registerAnnotatedHooks(): void
    {
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE) as $method) {
            $acceptedArgs = $method->getNumberOfParameters();

            // Register Actions
            foreach ($method->getAttributes(AddAction::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                /** @var AddAction $instance */
                $instance = $attribute->newInstance();
                $this->__register_add_action($method, $instance);
            }

            // Register Filters
            foreach ($method->getAttributes(AddFilter::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                /** @var AddFilter $instance */
                $instance = $attribute->newInstance();
                $this->__register_add_filter($method, $instance);
            }
        }
    }

    private function __register_add_action(ReflectionMethod $method, AddAction $instance): void
    {

        add_action(
            $instance->hook,
            function (...$args) use ($method, $instance) {
                if ($instance->nonce) {
                    if (!isset($_POST[$instance->nonce['field']]) || !wp_verify_nonce($_POST[$instance->nonce['field']], $instance->nonce['action'])) {
                        wp_die($instance->nonce['message'] ?? 'Invalid nonce.');
                    }
                }

                if ($instance->capability) {
                    if (!current_user_can($instance->capability['capability'])) {
                        wp_die($instance->capability['message']);
                    }
                }

                return $method->invokeArgs($this, $args);
            },
            $instance->priority,
            $instance->acceptedArgs ?? $method->getNumberOfParameters(),
        );
    }

    private function __register_add_filter(ReflectionMethod $method, AddFilter $instance): void
    {
        add_filter(
            $instance->hook,
            [$this, $method->getName()],
            $instance->priority,
            $instance->acceptedArgs ?? $method->getNumberOfParameters(),
        );
    }
}
