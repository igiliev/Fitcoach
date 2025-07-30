const settings = {
  'import/resolver': {
    node: {
      extensions:      ['.js', '.jsx', '.ts', '.tsx'],
      moduleDirectory: ['node_modules', 'src/'],
    },
    webpack: {
      config: './webpack.config.babel.js',
    },
  },
  'import/extensions': ['.js', '.jsx', '.ts', '.tsx'],
};

module.exports = { settings };
