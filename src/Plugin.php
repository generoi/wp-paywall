<?php

namespace GeneroWP\Paywall;

use stdClass;

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
        add_action('init', [$this, 'registerAssets']);
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

    public function registerAssets(): void
    {
        wp_register_style(
            'wp-paywall/admin.css',
            $this->asset($this->bundle('admin')->css[0])->url,
        );
    }

    public function blockEditorAssets(): void
    {
        $editorBundle = $this->bundle('editor');
        $runtime = file_get_contents($this->asset($editorBundle->js[0])->path);

        wp_enqueue_style(
            'wp-paywall/editor.css',
            $this->asset($editorBundle->css[0])->url,
            [],
            null,
        );
        wp_enqueue_script(
            'wp-paywall/editor.js',
            $this->asset($editorBundle->js[1])->url,
            $editorBundle->dependencies,
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
        $template = locate_template("wp-paywall/{$view}.php");
        if (! $template) {
            $template = dirname(__DIR__)."/views/{$view}.php";
        }
        $template = apply_filters('wp-paywall/template', $template, $args);

        ob_start();
        if ($template) {
            load_template($template, false, $args);
        }

        return ob_get_clean();
    }

    protected function bundle(string $bundle): stdClass
    {
        $publicDir = dirname(__DIR__).'/public/';
        static $entrypoints = null;
        if (! $entrypoints) {
            $entrypoints = json_decode(file_get_contents($publicDir.'entrypoints.json'));
        }

        return $entrypoints->{$bundle};
    }

    protected function asset(string $asset): stdClass
    {
        return (object) [
            'url' => $this->url.'/public/'.$asset,
            'path' => dirname(__DIR__).'/public/'.$asset,
        ];
    }
}
