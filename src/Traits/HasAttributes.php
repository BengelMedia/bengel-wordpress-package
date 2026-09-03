<?php

namespace Bengel\Wordpress\Traits;

use Bengel\Wordpress\Attributes\AddAction;
use Bengel\Wordpress\Attributes\AddFilter;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

trait HasAttributes
{
    protected function registerAnnotatedHooks(): void
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
                if ($instance->nonce_action && $instance->nonce_field) {
                    if (!isset($_POST[$instance->nonce_field]) || !wp_verify_nonce($_POST[$instance->nonce_field], $instance->nonce_action)) {
                        wp_die($instance->nonce_message ?? 'Invalid nonce.');
                    }
                }

                if ($instance->user_capability && $instance->user_message) {
                    if (!current_user_can($instance->user_capability)) {
                        wp_die($instance->user_message);
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
