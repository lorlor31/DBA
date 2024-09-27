const path = require( 'path' );


module.exports = ( env, argv ) => {

	const isProduction = argv.mode === 'production'

	return {
		entry: {
			frontend: path.resolve( __dirname, 'frontend.js' )
		},
		...( ! isProduction && { devtool: 'source-map' } ),
		mode        : isProduction ? 'production' : 'development',
		module      : {
			rules: [
				{
					exclude: /(node_modules|bower_components)/,
					use    : {
						loader : 'babel-loader',
						options: {
							presets: [ '@babel/preset-env' ]
						}
					}
				}
			]
		},
		optimization: {
			minimize: isProduction
		},
		output      : {
			filename: "[name].min.js",
			path    : path.resolve( __dirname, '..' )
		}
	}
}
