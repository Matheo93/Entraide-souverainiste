import '../public/assets/css/parts/responsive.css';
import '../public/assets/libs/mdl/material.min.css';
import '../public/assets/libs/fa/css/all.min.css';
import '../public/assets/libs/materialize/materialize_test.min.css';
import '../public/assets/libs/materialize/materialize-icons.min.css';


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