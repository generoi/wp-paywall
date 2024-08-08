<?php

namespace GeneroWP\Paywall\Admin;

use GeneroWP\Paywall\Paywall;
use WP_Term;

class TermAdmin
{
    public function __construct()
    {
        foreach (['category'] as $taxonomy) {
            add_action(sprintf('%s_add_form_fields', $taxonomy), [$this, 'addFields']);
            add_action(sprintf('%s_edit_form_fields', $taxonomy), [$this, 'addFields'], 10, 2);
            add_action(sprintf('created_%s', $taxonomy), [$this, 'onSave']);
            add_action(sprintf('edited_%s', $taxonomy), [$this, 'onSave']);
            add_filter(sprintf('manage_edit-%s_columns', $taxonomy), [$this, 'addColumn']);
            add_filter(sprintf('manage_%s_custom_column', $taxonomy), [$this, 'populateColumn'], 10, 3);
        }
    }

    public function addFields(WP_Term|string $term, ?string $taxonomy = null): void
    {
        // Add new term page
        if (! $taxonomy) {
            $taxonomy = $term;
            $term = null;
        }

        $paywall = $term ? get_term_meta($term->term_id, Paywall::META_PAYWALL, true) : '';
        ?>
        <tr class="form-field">
            <th><label for="paywall"><?php echo esc_html__('Paywall', 'wp-paywall'); ?></label></th>
            <td>
                <p>
                    <label>
                        <input name="paywall" type="radio" value="" <?php echo ! in_array($paywall, Paywall::options()) ? 'checked' : ''; ?> />
                        <?php echo esc_html__('Default based on parent terms and post type', 'wp-paywall'); ?>
                    </label>
                </p>
                <p>
                    <label>
                        <input name="paywall" type="radio" value="<?php echo Paywall::OPTIN_VALUE; ?>" <?php checked($paywall, Paywall::OPTIN_VALUE); ?> />
                        <?php echo esc_html__('Require paywall to access content within this category.', 'wp-paywall'); ?>
                    </label>
                </p>
                <p>
                    <label>
                        <input name="paywall" type="radio" value="<?php echo Paywall::OPTOUT_VALUE; ?>" <?php checked($paywall, Paywall::OPTOUT_VALUE); ?> />
                        <?php echo esc_html__('Opt-out of paywall if otherwise enabled in parent terms or post type.', 'wp-paywall'); ?>
                    </label>
                </p>
            </td>
        </tr>
        <?php
    }

    public function onSave(int $termId): void
    {
        update_term_meta(
            $termId,
            Paywall::META_PAYWALL,
            sanitize_key($_POST['paywall'] ?? ''),
        );
    }

    public function addColumn(array $columns)
    {
        $columns[Paywall::META_PAYWALL] = esc_html__('Paywall');

        return $columns;
    }

    public function populateColumn(string $output, string $column, int $termId): string
    {
        switch ($column) {
            case Paywall::META_PAYWALL:
                return match (get_term_meta($termId, Paywall::META_PAYWALL, true)) {
                    Paywall::OPTIN_VALUE => esc_html__('Yes'),
                    Paywall::OPTOUT_VALUE => esc_html__('Opt-out'),
                    default => '',
                };
        }

        return $output;
    }
}
