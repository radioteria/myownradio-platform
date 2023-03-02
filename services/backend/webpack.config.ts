/* eslint-disable @typescript-eslint/no-var-requires */
import { resolve } from 'path'
import webpack = require('webpack')
import WebpackAssetsManifest = require('webpack-assets-manifest')
import getCSSModuleLocalIdent = require('react-dev-utils/getCSSModuleLocalIdent')
import MiniCssExtractPlugin = require('mini-css-extract-plugin')

export default (env: Record<string, string> = {}): webpack.Configuration => {
  return {
    watch: !!env.development,
    entry: './src/index.tsx',
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
        {
          test: [/\.module.(s(a|c)ss)$/, /\.module.css$/],
          include: resolve(__dirname, 'src'),
          use: [
            env['production'] ? MiniCssExtractPlugin.loader : 'style-loader',
            {
              loader: 'css-loader',
              options: {
                modules: {
                  getLocalIdent: getCSSModuleLocalIdent,
                },
                sourceMap: true,
              },
            },
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: {
                  plugins: ['autoprefixer', 'postcss-csso'],
                },
                sourceMap: true,
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: true,
              },
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
