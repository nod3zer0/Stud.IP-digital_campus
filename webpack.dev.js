const webpack = require('webpack');
const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const WebpackNotifierPlugin = require('webpack-notifier');
const path = require('path');

const statusesPaths = {
    success: path.join(__dirname, 'public/assets/images/favicon-64x64.png'),
    error: path.join(__dirname, 'public/assets/images/virtual.png'),
};

module.exports = merge(common, {
    mode: 'development',
    devtool: 'eval-cheap-module-source-map',
    plugins: [
        new webpack.WatchIgnorePlugin({
            paths:[
                /\.d\.[cm]ts$/
            ]
        }),
        new WebpackNotifierPlugin({
            appID: 'Stud.IP Webpack',
            title: function (params) {
                return `Build status is ${params.status}`;
            },
            timeout: false,
            hint: process.platform === 'linux' ? 'int:transient:1' : undefined,
            excludeWarnings: true,
            contentImage: statusesPaths,
        }),
    ],
});
