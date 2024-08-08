<?php

namespace GeneroWP\Paywall\Admin;

use GeneroWP\Paywall\Paywall;

class PostAdmin
{
    public function __construct()
    {
        add_action('admin_bar_menu', [$this, 'addAdminBarNode'], 100);

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
            'title' => $isApplied ? esc_attr__('Paywalled') : esc_attr__('No Paywall'),
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
}
