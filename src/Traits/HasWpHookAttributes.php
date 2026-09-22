<?php

namespace Bengel\Wordpress\Traits;

use Bengel\Wordpress\Attributes\AddAction;
use Bengel\Wordpress\Attributes\AddFilter;
use Bengel\Wordpress\Attributes\RestRoute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

trait HasWpHookAttributes
{
    private function registerAnnotatedHooks(): void
    {
        $reflection = new ReflectionClass($this);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE) as $method) {

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

            // register rest routes
            foreach($method->getAttributes(RestRoute::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                /** @var RestRoute $instance */
                $instance = $attribute->newInstance();
                $this->__register_rest_route($method, $instance);
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

    private function __register_rest_route(ReflectionMethod $method, RestRoute $instance): void {
        add_action('rest_api_init', function() use ($method, $instance) {
            $args = [
                'callback' => [$this, $method->getName()]
            ];
            if($instance->methods) {
                $args['methods'] = $instance->methods;
            }
            if($instance->permission_callback) {
                $args['permission_callback'] = [$this, $instance->permission_callback];
            }
            if($instance->allow_batch) {
                $args['allow_batch'] = $instance->allow_batch;
            }
            if($instance->schema) {
                if(is_array($instance->schema)) {
                    $args['schema'] = $instance->schema;
                } else {
                    $args['schema'] = [$this, $instance->schema];
                }
            }
            if($instance->show_in_index) {
                $args['show_in_index'] = $instance->show_in_index;
            }
            register_rest_route($instance->route_namespace, $instance->route, $args, $instance->override);
        });
    }
}
