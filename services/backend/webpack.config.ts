/* eslint-disable @typescript-eslint/no-var-requires */
import { resolve } from 'path'
import webpack = require('webpack')
import WebpackAssetsManifest = require('webpack-assets-manifest')

export default (env: Record<string, string> = {}): webpack.Configuration => {
  void env

  return {
    entry: {
      vendors: ['react', 'react-dom'],
      Index: './src/Index/index.tsx',
    },
    mode: 'development',
    output: {
      path: resolve(__dirname, './public/assets'),
      filename: `[name].[contenthash].js`,
      publicPath: '/',
    },
    optimization: {
      runtimeChunk: 'single',
      splitChunks: {
        cacheGroups: {
          externals: {
            test: /[\\/]node_modules[\\/]/,
            name: 'externals',
            chunks: 'all',
          },
        },
      },
    },
    resolve: {
      extensions: ['.ts', '.tsx', '.js', '.jsx'],
      modules: [resolve(__dirname, 'node_modules')],
    },
    module: {
      strictExportPresence: true,
      rules: [
        {
          oneOf: [
            {
              test: /\.tsx?$/,
              exclude: /node_modules/,
              use: ['ts-loader'],
            },
          ],
        },
      ],
    },
    plugins: [
      // @todo Workaround as there is no types compatible with Webpack 5 yet.
      (new WebpackAssetsManifest() as unknown) as webpack.WebpackPluginInstance,
    ],
  }
}
