# wp-paywall

> A paywall plugin

By default article content is behind the paywall but can be manually _opted-out_ on a per-post setting. Pages are public but like article they can be _opted-in_ manually.

If a page/post has manually configured a setting it will take precedence over everything else but categories can also set a default state for all content tagged with the category. This rule is inherited so if you tag a child-term it traverse ancestors until a preference is found. If nothing is found it defaults to the post-type rules where `post` is paywalled and `page` is not.

Out of the box paywalled content will show the login form but you can override this template in your theme.

Additionally there is a _Paywalled content_ block which can be used to manually tailor which section of a page is behind the paywall while everything outside of it will be public. _Note that using this block does NOT mark a page as paywalled, it only adjusts the content IF paywalled._

There's a integration with Yoast to output rich schema data according to [Google's specifications](https://developers.google.com/search/docs/appearance/structured-data/paywalled-content). There's also a `X-Robots-Tag: noarchive` HTTP header sent.

To allow reverse proxies to differentiate paywelled content there's a `Vary: X-Paywall-Accepted` header and a `X-Paywall-Access: 0|1` added to the response. For now this is also where bot whitelisting would happen for now.

_TODO: Allow reverse proxies to handle the authentication._

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
