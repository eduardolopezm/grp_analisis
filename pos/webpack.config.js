module.exports = {
	entry: './index.js',
	output: {
		path: __dirname,
		filename: 'bundle.js'
	},
	module: {
		loaders: [
			{
				test: /\.jsx?$/,
				loader: 'babel-loader',
				exclude: '/node_modules/',
				query: {
					presets: [__dirname + '/node_modules/babel-preset-react',__dirname + '/node_modules/babel-preset-es2015',__dirname + '/node_modules/babel-preset-stage-0']
					// presets: ['react','es2015', 'stage-0']
				}
			}
		]
	}
}