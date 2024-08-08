<?php

namespace GeneroWP\Paywall;

class Plugin
{
    public $name = 'wp-paywall';

    public $file;

    public $path;

    public $url;

    protected static $instance;

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->file = realpath(__DIR__.'/../wp-paywall.php');
        $this->path = untrailingslashit(plugin_dir_path($this->file));
        $this->url = untrailingslashit(plugin_dir_url($this->file));

        add_action('plugins_loaded', [$this, 'init']);
        add_action('init', [$this, 'registerBlockTypes']);
        add_action('init', [$this, 'registerMeta']);
        add_action('enqueue_block_editor_assets', [$this, 'blockEditorAssets'], 0);
    }

    public function init()
    {
        new Paywall($this);
        new Admin\TermAdmin;
        new Admin\PostAdmin;
    }

    public function registerMeta(): void
    {
        foreach (['page', 'post'] as $postType) {
            register_post_meta($postType, Paywall::META_PAYWALL, [
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
            ]);
        }
    }

    public function registerBlockTypes(): void
    {
        include dirname(__DIR__).'/resources/scripts/blocks/paywall/paywall-block.php';
    }

    public function blockEditorAssets(): void
    {
        $publicDir = dirname(__DIR__).'/public/';
        $manifest = json_decode(file_get_contents($publicDir.'manifest.json'));
        $entrypoints = json_decode(file_get_contents($publicDir.'entrypoints.json'));

        $runtime = file_get_contents($publicDir.$manifest->{'runtime.js'});

        wp_enqueue_style(
            'wp-paywall/editor.css',
            $this->url.'/public/'.$manifest->{'editor.css'},
            [],
            null,
        );
        wp_enqueue_script(
            'wp-paywall/editor.js',
            $this->url.'/public/'.$manifest->{'editor.js'},
            $entrypoints->editor->dependencies,
            null,
        );
        wp_add_inline_script(
            'wp-paywall/editor.js',
            $runtime,
        );
    }

    public function loadTextdomain(): void
    {
        load_plugin_textdomain(
            $this->name,
            false,
            dirname(plugin_basename($this->file)).'/languages'
        );
    }

    public function render(string $view, array $args = []): string
    {
        ob_start();
        if (! get_template_part("wp-paywall/$view.php", null, $args)) {
            load_template(dirname(__DIR__)."/views/$view.php", true, $args);
        }

        return ob_get_clean();
    }
}
