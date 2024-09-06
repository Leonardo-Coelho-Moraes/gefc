// webpack.config.js
const path = require('path');

module.exports = {
    entry: './templates/assets/js/index.js', // Arquivo de entrada
    output: {
        filename: 'bundle.js', // Nome do arquivo de saída
        path: path.resolve(__dirname, 'dist'), // Diretório de saída
    },
    module: {
        rules: [
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader'],
            },
        ],
    },
    mode: 'development', // Modo de desenvolvimento
};
