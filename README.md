# wp-paywall

> A paywall plugin

## Development

Install dependencies

    composer install
    npm install

Run the tests

    npm run test

Build assets

    # Minified assets which are to be committed to git
    npm run build:production

    # Watch for changes and re-compile while developing the plugin
    npm run start

## Translations

    wp i18n make-pot . languages/wp-paywall.pot
    wp i18n make-mo languages/
