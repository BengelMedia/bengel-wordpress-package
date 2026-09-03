<?php

namespace Bengel\Wordpress\Utils;

use Bengel\Wordpress\Traits\HasWpHookAttributes;

abstract class Singleton {
    use HasWpHookAttributes;

    private static array $instanced = [];

    private function __construct() {
       $this->registerAnnotatedHooks();
       $this->onLoad();
    }

    public static function get(): static{
        $class = static::class;
        if (!isset(self::$instanced[$class])) {
            self::$instanced[$class] = new static();
        }
        return self::$instanced[$class];
    }

    protected function onLoad():void {}
}
