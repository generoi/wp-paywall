<?php

namespace GeneroWP\Paywall\Admin;

use GeneroWP\Paywall\Paywall;

class PostAdmin
{
    public function __construct()
    {
        add_action('admin_bar_menu', [$this, 'addAdminBarNode'], 100);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        foreach (['post', 'page'] as $postType) {
            add_filter(sprintf('manage_edit-%s_columns', $postType), [$this, 'addColumn']);
            add_action(sprintf('manage_%s_posts_custom_column', $postType), [$this, 'populateColumn'], 10, 2);
        }
    }

    public function addAdminBarNode()
    {
        /**
         * @global \WP_Admin_Bar $wp_admin_bar
         */
        global $wp_admin_bar;

        if (! is_singular()) {
            return;
        }

        $isApplied = Paywall::isApplied(get_post());

        $wp_admin_bar->add_node([
            'id' => 'paywall',
            'parent' => 'top-secondary',
            'title' => sprintf(
                '<span class="dashicon dashicons dashicons-privacy %s"></span> <small>%s</small>',
                $isApplied ? 'is-applied' : '',
                $isApplied ? __('Paywalled') : __('Not paywalled')
            ),
        ]);

    }

    public function addColumn(array $columns)
    {
        $columns[Paywall::META_PAYWALL] = esc_html__('Paywall');

        return $columns;
    }

    public function populateColumn(string $column, int $postId): void
    {
        switch ($column) {
            case Paywall::META_PAYWALL:
                echo Paywall::isApplied($postId) ? esc_html__('Yes') : '';
        }
    }

    public function enqueueAssets(): void
    {
        if (! is_admin_bar_showing()) {
            return;
        }

        wp_enqueue_style('wp-paywall/admin.css');
    }
}
