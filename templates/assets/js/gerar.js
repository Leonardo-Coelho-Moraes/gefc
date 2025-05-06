$(document).ready(function () {
    var dataHospital = $('#data-container');
    var produtos = dataHospital.data('lotes');
    console.log(produtos); // Certifique-se de que isso retorna um array de produtos

    var $input = $('#barcode-input');
    var $list = $('#barcode-list');

    // Função para processar os lotes
    function processBarcodes(barcodes) {
        barcodes.forEach(function (barcode) {
            // Remove espaços em branco e verifica se o lote existe
            var lote = barcode.trim();
            if (lote) {
                // Corrigido: usa o 'lote' do barcode atual em vez de produto.id
                var produto = produtos.find(p => String(p.id) === lote); // Convertendo id para string
                if (produto) {
                    $list.append("<li style='display: flex;'>COD: " + produto.id + ' - Produto: ' + produto.nome + "   Quantidade:  <input type='number'></li>");
                } else {
                    // Caso o lote não seja encontrado
                    $list.append('<li>Lote: ' + lote + ' - Produto não encontrado</li>');
                }
            }
        });
        $input.val(''); // Limpa o campo de entrada
        $input.focus(); // Foca novamente no campo de entrada
    }

    // Event handler para detectar a tecla Enter
    $input.on('keydown', function (event) {
        if (event.key === 'Enter') {
            var inputValue = $input.val().trim();
            if (inputValue) {
                // Divide a entrada usando espaço ou tabulação
                var barcodes = inputValue.split(/\s+/);
                processBarcodes(barcodes);
            }
            event.preventDefault(); // Previne o comportamento padrão do Enter
        }
    });
});
