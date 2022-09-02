const path = require('path');
module.exports = {
	mode: 'production', // production
	entry: {
		'truelayer-for-woocommerce-admin': './assets/js/truelayer-for-woocommerce-admin.js',
	},

	output: {
		filename: '[name].min.js',
		path: path.resolve(__dirname, './assets/js'),
	},
	devtool: 'source-map',
	module: {
		rules: [
			{
				test: /\.m?js$/,
				exclude: /(node_modules|bower_components)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: ['@babel/preset-env'],
						plugins: ['@babel/plugin-proposal-object-rest-spread'],
					}
				}
			}
		],
	},
};