<?php

namespace Bengel\Wordpress\Utils;

class ViteAssetsResolver
{
    private ?array $manifest = null;

    public function __construct(
        public string $manifest_dir,
        public string $dist_url,
        public string $handle_prefix = 'bengel-core-',
    )
    {
    }

    private function get_manifest()
    {
        if ($this->manifest) return $this->manifest;
        $location = $this->manifest_dir . '/manifest.json';
        if (!file_exists($location)) {
            return null;
        }
        $contents = file_get_contents($location);
        $parsed = json_decode($contents, true);
        $this->manifest = $parsed;
        return $this->manifest;
    }

    public function resolve(string $name)
    {
        $manifest = self::get_manifest();
        foreach ($manifest as $src => $output) {
            if ($output['name'] === $name) {
                return $this->dist_url . '/' . $output['file'];
            }
        }
        return null;
    }

    private function enqueue_script_styles(string $name){
        $manifest = self::get_manifest();
        foreach ($manifest as $src => $output) {
            if ($output['name'] === $name) {
                if(isset($output['css']) && is_array($output['css'])){
                    $styles = $output['css'];
                    foreach($styles as $style) {
                        wp_enqueue_style($this->handle_prefix . $name . $style, $this->dist_url .'/'. $style);
                    }
                }
            }
        }
    }

    public function enqueue_script(string $name, ?array $data = null)
    {
        $handle = $this->handle_prefix . $name;
        wp_enqueue_script(
            $handle,
            self::resolve($name),
            [],
        );
        $this->enqueue_script_styles($name);
        if($data) {
            foreach($data as $object_name => $object_data) {
                wp_localize_script($handle, $object_name, $object_data);
            }
        }
    }

    public function enqueue_style(string $name)
    {
        wp_enqueue_style(
            $this->handle_prefix. $name,
            self::resolve($name),
        );
    }

    /**
     * @param array $names
     * @return void
     */
    public function enqueue_scripts(array $names)
    {
        foreach ($names as $key => $name) {
            // check if the key is a string
            if (is_string($key)) {
                $this->enqueue_script($key, $name );
            } else {
                $this->enqueue_script($name);
            }
        }
    }

    /**
     * @param string[] $names
     * @return void
     */
    public function enqueue_styles(array $names)
    {
        foreach ($names as $name) {
            $this->enqueue_style($name);
        }
    }

    /**
     * @param array{scripts?: array, styles?: string[]} $assets the scripts and styles that should be enqueued.
     * @return void
     */
    public function enqueue(array $assets)
    {
        if (isset($assets['scripts'])) {
            $this->enqueue_scripts($assets['scripts']);
        }
        if (isset($assets['styles'])) {
            $this->enqueue_styles($assets['styles']);
        }
    }
}
