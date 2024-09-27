const path = require( 'path' );
const WooCommerceDependencyExtractionWebpackPlugin = require( '@woocommerce/dependency-extraction-webpack-plugin' );
module.exports = {
	devtool: 'source-map',
	entry: {
		//'build/modals': './assets/js/src/modals/index.js',
		//'build/frontend': './assets/js/src/frontend/index.js',
		//'build/admin': './assets/js/src/admin/index.js',
		'build/wc-blocks/image-replacement/index': './includes/wc-blocks/assets/js/image-replacement/index.js',
	},
	mode: 'production',
	module: {
		rules: [
			{
				exclude: /(node_modules|bower_components|owl)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env' ],
					},
				},
			},
		],
	},
	plugins  : [
		new WooCommerceDependencyExtractionWebpackPlugin()
	],
	optimization: {
		minimize: false,
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname ),
	},
};
