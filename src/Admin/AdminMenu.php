<?php

namespace Bengel\Wordpress\Admin;

class AdminMenu {
    protected AdminPage $main_page;
    /** @var AdminPage[] */
    protected array $subpages = [];
    protected string $icon = 'dashicons-admin-generic';
    protected ?int $position = null;

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

    public function register(): void {
        add_action('admin_menu', [$this, 'mount_menu']);
    }

    public function mount_menu(): void {
        // 1. Register main parent page
        $parent_hook = add_menu_page(
            $this->main_page->get_title(),
            $this->main_page->get_menu_title(),
            $this->main_page->get_capability(),
            $this->main_page->get_slug(),
            [$this->main_page, 'render'],
            $this->icon,
            $this->position
        );

        add_action("load-{$parent_hook}", [$this->main_page, 'on_load']);

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

            add_action("load-{$child_hook}", [$subpage, 'on_load']);
        }
    }
}
