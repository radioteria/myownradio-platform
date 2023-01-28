/* eslint-disable @typescript-eslint/no-var-requires */
import { resolve } from 'path'
import webpack = require('webpack')
import WebpackAssetsManifest = require('webpack-assets-manifest')

export default (env: Record<string, string> = {}): webpack.Configuration => {
  return {
    watch: !!env.development,
    entry: {
      vendors: ['react', 'react-dom'],
      app: './src/index.ts',
    },
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
      new WebpackAssetsManifest() as unknown as webpack.WebpackPluginInstance,
    ],
    watchOptions: {
      ignored: /node_modules/,
    },
  }
}
