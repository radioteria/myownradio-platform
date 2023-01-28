/* eslint-disable @typescript-eslint/no-var-requires */
import { resolve } from 'path'
import webpack = require('webpack')
import WebpackAssetsManifest = require('webpack-assets-manifest')

export default (env: Record<string, string> = {}): webpack.Configuration => {
  return {
    watch: !!env.development,
    entry: './src/index.ts',
    mode: env.development ? 'development' : 'production',
    output: {
      path: resolve(__dirname, './public/assets'),
      filename: `[name].[contenthash].js`,
      publicPath: '/',
    },
    optimization: {
      usedExports: true,
      runtimeChunk: 'single',
      splitChunks: {
        chunks: 'all',
        cacheGroups: {
          externals: {
            test: /[\\/]node_modules[\\/]/,
            name: 'externals',
          },
        },
      },
    },
    resolve: {
      extensions: ['.ts', '.tsx', '.js', '.jsx'],
      modules: [
        resolve(__dirname, 'node_modules'),
        resolve(__dirname, 'bower_components'),
        resolve(__dirname, 'public/js'),
      ],
    },
    module: {
      strictExportPresence: true,
      rules: [
        {
          test: require.resolve('jquery'),
          loader: 'expose-loader',
          options: {
            exposes: ['$', 'jQuery'],
          },
        },
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
    plugins: [new WebpackAssetsManifest()],
    watchOptions: {
      ignored: /node_modules/,
    },
  }
}
