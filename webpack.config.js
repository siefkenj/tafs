var path = require("path");
var webpack = require("webpack");
const CopyWebpackPlugin = require("copy-webpack-plugin");

module.exports = {
    entry: "./src/main.js",
    output: {
        path: path.resolve(__dirname, "./dist"),
        publicPath: "/dist/",
        filename: "build.js"
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ["vue-style-loader", "css-loader"]
            },
            {
                test: /\.vue$/,
                loader: "vue-loader",
                options: {
                    loaders: {}
                    // other vue-loader options go here
                }
            },
            {
                test: /\.js$/,
                loader: "babel-loader",
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpg|gif|svg)$/,
                loader: "file-loader",
                options: {
                    name: "[name].[ext]?[hash]"
                }
            }
        ]
    },
    plugins: [
        new CopyWebpackPlugin([
            { from: "./handle_request.php", to: "./" },
            { from: "./get_info.php", to: "./" },
            { from: "./student_survey.php", to: "./" },
            { from: "./index.html", to: "./" },
            { from: "./get_query_generators.php", to: "./" },
            { from: "./survey_query_generators.php", to: "./" },
            { from: "./post_info.php", to: "./" },
            { from: "./post_query_generators.php", to: "./" }
        ])
    ],
    resolve: {
        alias: {
            vue$: "vue/dist/vue.esm.js"
        },
        extensions: ["*", ".js", ".vue", ".json"]
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true,
        overlay: true
    },
    performance: {
        hints: false
    },
    devtool: "#eval-source-map"
};

if (process.env.NODE_ENV === "production") {
    module.exports.devtool = "#source-map";
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            "process.env": {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                warnings: false
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ]);
}
