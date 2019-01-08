var Encore = require('@symfony/webpack-encore');

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
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .createSharedEntry('layout', './assets/js/layout.js')
    .addEntry('rep_log_react', './assets/js/rep_log_react.js')
    .addEntry('login', './assets/js/login.js')
    //.addEntry('layout', './assets/js/layout.js')


    .copyFiles({
        from: './assets/static',

        // optional target path, relative to the output dir
        to: 'static/[path][name].[ext]',

        // only copy files matching this pattern
        pattern: /\.(png|jpg|jpeg)$/
    })
    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    //.enableSingleRuntimeChunk()


    /* FEATURE CONFIG
    *
    * Enable & configure other features below. For a full
    * list of features, see:
    * https://symfony.com/doc/current/frontend.html#adding-more-features
    */
    .enableBuildNotifications()
    .cleanupOutputBeforeBuild()
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .enableSourceMaps(!Encore.isProduction())

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    .enableReactPreset()

    .configureBabel((babelConfig) => {
        if (Encore.isProduction()) {
            babelConfig.plugins.push(
                'transform-react-remove-prop-types'
            );
        }

        babelConfig.plugins.push('transform-object-rest-spread');
    })
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
