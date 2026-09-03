<?php

namespace Bengel\Wordpress\Admin;

class AdminMenu {
    protected AdminPage $main_page;
    /** @var AdminPage[] */
    protected array $subpages = [];
    protected string $icon = 'dashicons-admin-generic';
    protected ?int $position = null;
    protected ?string $main_menu_title = null;

    private array $menu_suffixes = [];

    /** @var callable|null */
    private $enqueue_assets_callback = null;

    public function __construct(AdminPage $main_page) {
        $this->main_page = $main_page;
    }

    public function set_icon(string $icon): self {
        $this->icon = $icon;
        return $this;
    }

    public function set_position(int $position): self {
        $this->position = $position;
        return $this;
    }

    public function add_subpage(AdminPage $subpage): self {
        $this->subpages[] = $subpage;
        return $this;
    }

    public function set_main_menu_title(string $title): self {
        $this-> main_menu_title = $title;
        return $this;
    }

    public function on_enqueue_assets(callable $callback): self {
        $this->enqueue_assets_callback = $callback;
        return $this;
    }

    public function register(): void {
        add_action('admin_menu', [$this, 'mount_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function mount_menu(): void {
        // 1. Register main parent page
        $parent_hook = add_menu_page(
            $this->main_page->get_title(),
            $this->main_menu_title ?? $this->main_page->get_menu_title(),
            $this->main_page->get_capability(),
            $this->main_page->get_slug(),
            [$this->main_page, 'render'],
            $this->icon,
            $this->position
        );

        $this->menu_suffixes[] = $parent_hook;

        add_action("load-{$parent_hook}", [$this->main_page, 'on_load']);

        if($this->main_menu_title !== null) {
            $hook = add_submenu_page(
                $this->main_page->get_slug(),
                $this->main_page->get_title(),
                $this->main_page->get_menu_title(),
                $this->main_page->get_capability(),
                $this->main_page->get_slug(),
                [$this->main_page, 'render'],
                $this->icon,
            );
            $this->menu_suffixes[] = $hook;
        }

        // 2. Register children under the parent slug
        foreach ($this->subpages as $subpage) {
            $child_hook = add_submenu_page(
                $this->main_page->get_slug(),
                $subpage->get_title(),
                $subpage->get_menu_title(),
                $subpage->get_capability(),
                $subpage->get_slug(),
                [$subpage, 'render']
            );
            $this->menu_suffixes[] = $child_hook;
            add_action("load-{$child_hook}", [$subpage, 'on_load']);
        }
    }

    public function enqueue_assets(): void {
        // check if the current page is one of the registered menu pages
        $current_screen = get_current_screen();
        if ($current_screen && in_array($current_screen->id, $this->menu_suffixes)) {
            if ($this->enqueue_assets_callback) {
                call_user_func($this->enqueue_assets_callback);
            }
        }
    }
}
