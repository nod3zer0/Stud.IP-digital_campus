const express = require('express');
const path = require('path');
const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');

const config = require("./config/webpack.dev-server.config.json");

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map',
    target: 'web',
    devServer: {
        compress: true,
        port: config.port,
        historyApiFallback: true,
        https: config.protocol === 'https',
        headers: {
            'Access-Control-Allow-Origin': '*'
        }
    }
});
