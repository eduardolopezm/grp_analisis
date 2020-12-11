var path = require('path');

module.exports = {
  entry: './index.js',
  output: {
    path: path.resolve(__dirname, './dist'),
    filename: 'bundle.js'
  },
  module: {
    loaders: [{
      test: /\.jsx?$/,

      // There is not need to run the loader through
      // vendors
      exclude: '/node_modules/',
      loader: 'babel-loader',
      uery: {
          presets: [__dirname + '/node_modules/babel-preset-react',__dirname + '/node_modules/babel-preset-es2015',__dirname + '/node_modules/babel-preset-stage-0']
          // presets: ['react','es2015', 'stage-0']
        }
    }]
  }
};