# wp-paywall

> A paywall plugin

## Development

Install dependencies

    composer install
    npm install

Run the tests

    npm run lint
    composer lint

    # Setup WP-ENV
    npm -g i @wordpress/env
    wp-env start

    # Run unit tests
    wp-env run tests-cli --env-cwd=wp-content/plugins/wp-paywall ./vendor/bin/phpunit

    # With Xdebug
    wp-env stop
    wp-env start --xdebug
    wp-env run tests-cli --env-cwd=wp-content/plugins/wp-paywall ./vendor/bin/phpunit

Build assets

    # Minified assets which are to be committed to git
    npm run build:production

    # Watch for changes and re-compile while developing the plugin
    npm run start

## Translations

    wp i18n make-pot . languages/wp-paywall.pot
    wp i18n make-mo languages/
