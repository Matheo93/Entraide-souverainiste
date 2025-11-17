const Encore = require('@symfony/webpack-encore');
const PreloadWebpackPlugin = require('preload-webpack-plugin');


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

	//css

	// index User Dashboard
	.addEntry('BASEJS', './assets/controllers/base.js')
	.addEntry('JQUERY', './assets/libs/jquery/jquery.min.js')
	.addEntry('JQUERYUI', './assets/libs/jquery/jquery-ui.min.js')
	.addEntry('MATERIALIZECSS', './assets/libs/materialize/sass/materialize.scss')
	.addEntry('MATERIALIZEJS', './assets/libs/materialize/js/bin/materialize.min.js')
	.addEntry('AJS', './assets/libs/ajs/ajs.js')



	.addEntry('AUTOCOMPLETEJS', './assets/libs/autocomplete/autocomplete.min.js')
	.addEntry('AUTOCOMPLETECSS', './assets/libs/autocomplete/autocomplete.min.css')
	.addEntry('LIBSCSS', './assets/css/libs.css')
	.addEntry('HEADERCSS', './assets/css/parts/header.css')
	.addEntry('FOOTERCSS', './assets/css/parts/footer.css')
	.addEntry('RESPONSIVE', './assets/css/parts/responsive.css')
	.addEntry('RESPONSIVEJS', './assets/controllers/pages/responsive.js')

	.addEntry('HOMECSS', './assets/css/pages/home/home.css')
	.addEntry('HOMEJS', './assets/controllers/pages/home.js')

	.addEntry('CONTACT', './assets/css/pages/contact/contact.css')

	.addEntry('SEARCHCSS', './assets/css/pages/search/search.css')
	.addEntry('SEARCHJS', './assets/controllers/pages/search.js')
	.addEntry('SHUFFLESEARCHJS', './assets/controllers/pages/shuffle-announces.js')
	.addEntry('SHUFFLELIB', './assets/libs/shuffle/shuffle.min.js')
	
	.addEntry('RGSTRCSS', './assets/css/pages/register/register.css')
	.addEntry('LOGIN', './assets/css/login/login.css')

	.addEntry('ADDANNOUNCEJS', './assets/controllers/pages/add-announce.js')


	.addEntry('ANCE', './assets/css/pages/annonces/add.css')
	.addEntry('SHOWANNONCE', './assets/css/pages/annonces/show.css')
	.addEntry('SHOWANNONCEJS', './assets/controllers/pages/announce.js')


	.addEntry('USERCSS', './assets/css/pages/user/index.css')
	.addEntry('USERJS', './assets/controllers/pages/user.js')
	

	// ADMIN
	.addEntry('MDLCSS', './assets/libs/mdl/material.min.css')
	.addEntry('MDLJS', './assets/libs/mdl/material.min.js')
	//.addEntry('FACSS', './assets/libs/fa/fa.min.css')
	.addEntry('MATERIALIZETEST', './assets/libs/materialize/materialize_test.min.css')
	.addEntry('MATERIALIZEICONS', './assets/libs/materialize/materialize_icons.min.css')
	
	.addEntry('MATERIALIZEJSORIGIN', './assets/libs/materialize/materialize.js')


	.addEntry('ADMIN_BASEJS', './assets/admin/js/base.js')
	.addEntry('ADMIN_BASECSS', './assets/admin/css/libs.css')
	.addEntry('ADMIN_MAINCSS', './assets/admin/css/main.css')
	.addEntry('ADMIN_MAINJS', './assets/admin/js/main.js')
	.addEntry('ADMIN_BUTTONSJS', './assets/admin/js/buttons.js')



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

	.configureFilenames({
		js: '[chunkhash].js',
		css: '[contenthash].css',
	})

	.configureBabel((config) => {
		config.plugins.push('@babel/plugin-proposal-class-properties');
	})

	// enables @babel/preset-env polyfills
	.configureBabelPresetEnv((config) => {
		config.useBuiltIns = 'usage';
		config.corejs = 3;
	})

	// enables Sass/SCSS support
	.enableSassLoader()

	// uncomment if you use TypeScript
	//.enableTypeScriptLoader()

	// uncomment if you use React
	//.enableReactPreset()

	// uncomment to get integrity="..." attributes on your script & link tags
	// requires WebpackEncoreBundle 1.4 or higher
	//.enableIntegrityHashes(Encore.isProduction())

	// uncomment if you're having problems with a jQuery plugin
	.autoProvideVariables({$: 'jquery',jQuery: 'jquery','window.jQuery': 'jquery', addFlash: 'addFlash'})
;

/*

Encore.copyFiles({
	to: "[path][hash:8].[ext]"
});
*/
module.exports = Encore.getWebpackConfig();