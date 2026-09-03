<?php

namespace Bengel\Wordpress\Admin;

use Bengel\Wordpress\Traits\HasWpHookAttributes;

abstract class AdminPage {
    use HasWpHookAttributes;

    abstract public function get_slug(): string;
    abstract public function get_title(): string;
    abstract public function render(): void;

    final public function __construct() {
        $this->registerAnnotatedHooks();
    }

    public function get_menu_title(): string {
        return $this->get_title();
    }

    public function get_capability(): string {
        return 'manage_options';
    }

    public function on_load(): void {
        // Optional hook for register_setting(), add_meta_box(), enqueues, etc.
    }

    public function redirect(array $args = [])
    {
        wp_redirect(add_query_arg($args, admin_url('admin.php?page=' . $this->get_slug())));
        exit;
    }
}
