/**
 * @type {import('@roots/bud').Config}
 */
export default async (app) => {
  app
    .entry('editor', ['@scripts/editor.jsx'])
    .entry('admin', ['@styles/admin'])
    .setPath({
      '@dist': `public`,
      '@scripts': `@src/scripts`,
      '@src': `resources`,
      '@styles': `@src/styles`,
    });

  app.when(app.isProduction, app.hash);
  app.build.items.precss.setLoader('minicss');
  app.hooks.action('build.before', (bud) => {
    bud.extensions
      .get('@roots/bud-extensions/mini-css-extract-plugin')
      .enable();
  });
};
