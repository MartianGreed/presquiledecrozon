const Encore = require('@symfony/webpack-encore');
const TsconfigPathsPlugin = require('tsconfig-paths-webpack-plugin');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  // only needed for CDN's or sub-directory deploy
  //.setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry('app', './assets/app.js')
  .addEntry('styles', './assets/styles/main.scss')
  .copyFiles({
    from: './assets/images',
    to: 'images/[path][name].[hash:8].[ext]',
  })

  .copyFiles({
    from: './assets/fonts',
    to: 'fonts/[path][name].[hash:8].[ext]',
  })

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge('./assets/controllers.json')

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // will require an extra script tag for runtime.js
  // but, you probably want this, unless you're building a single-page app
  .enableSingleRuntimeChunk()

  /*
   * FEATURE CONFIG
   *
   * Enable & configure other features below. For a full
   * list of features, see:
   * https://symfony.com/doc/current/frontend.html#adding-more-features
   */
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  // enables hashed filenames (e.g. app.abc123.css)
  .enableVersioning(Encore.isProduction())

  .configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
    config.presets.push('@babel/preset-typescript');
  })

  // enables @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
  })

  // enables Sass/SCSS support
  .enableSassLoader()
  .enablePostCssLoader()

  // uncomment if you use TypeScript
  .enableTypeScriptLoader()

  // uncomment if you use React
  //.enableReactPreset()

  // uncomment to get integrity="..." attributes on your script & link tags
  // requires WebpackEncoreBundle 1.4 or higher
  .enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()
;

let config = Encore.getWebpackConfig();

config.resolve.plugins = [new TsconfigPathsPlugin({ configFile: './tsconfig.json' })];

if (Encore.isProduction()) {
  var buff = Buffer.from(process.env.PLATFORM_VARIABLES, 'base64');
  var env = JSON.parse(buff.toString('utf-8'));

  Encore.setPublicPath(env.CDN_HOST + '/static/build');
  Encore.setManifestKeyPrefix('build/');
}


module.exports = config;
