/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)

import '../public/assets/libs/fonts/inter.css';
import '../public/assets/libs/fonts/icons.css';
import '../public/assets/libs/libs.css';
import '../public/assets/css/parts/header.css';
import '../public/assets/css/parts/login.css';
import '../public/assets/css/parts/footer.css';
import '../public/assets/libs/materialize/materialize.css';
import '../public/assets/libs/autocomplete/dist/css/autoComplete.css';



import '../public/assets/css/parts/responsive.css';





// start the Stimulus application
//import './bootstrap';



const $ = require('jquery');

// create global $ and jQuery variables
global.$ = global.jQuery = $;




module: {
	rules: [{
	  test: require.resolve('jquery'),
	  use: [
		{
			loader: 'expose-loader',
			options: 'jQuery'
		},{
			loader: 'expose-loader',
			options: '$'
		}]
	}]
}