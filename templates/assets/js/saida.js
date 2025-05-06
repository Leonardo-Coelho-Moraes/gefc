$(document).ready(function () {
    var counter = 1;
    var dataHospital = $('#dataEstoqueHospital');
    var estoques = dataHospital.data('estoques');
    


    function produtosSelecionar(estoques) {
        var $selecionadoCampo = $('#produtoSelecionar');
        $selecionadoCampo.empty(); // Limpa o selectField

        // Adiciona a opção padrão
        $selecionadoCampo.append('<option value="">Selecione um produto</option>');

        var validEstoques = estoques;
        // Adiciona os lotes válidos ao selectField
        validEstoques.forEach(function (estoque) {
            var optionValor = `${estoque.local_estoque_id};${estoque.produto_id};${estoque.nome};${estoque.estoque}`;
            var optionTexto = `COD:${estoque.produto_id}, ${estoque.nome} Qnt: ${estoque.estoque} ${estoque.unidade_contagem} `;
            var opcao = $('<option></option>').val(optionValor).text(optionTexto);
            $selecionadoCampo.append(opcao);
        });
    }





    // Preencher o selectField com os dados dos lotes
    produtosSelecionar(estoques);

    function atualizarCampoSelecionado(produto_id) {
        var $selecionadoCampo = $('#produtoSelecionar');
        $selecionadoCampo.empty(); // Limpa o selectField

        // Adiciona a opção padrão


        // Filtra os lotes se o código não estiver vazio
        var estoquesFiltrados = produto_id ? estoques.filter(function (estoque) {
            return estoque.produto_id == produto_id;
        }) : estoques; // Se cod estiver vazio, mostra todos os lotes


        // Adiciona os lotes ao selectField
        estoquesFiltrados.forEach(function (estoque) {
            var optionValor = `${estoque.local_estoque_id};${estoque.produto_id};${estoque.nome};${estoque.estoque}`;
            var optionTexto = `COD:${estoque.produto_id}, ${estoque.nome} Qnt: ${estoque.estoque} ${estoque.unidade_contagem}`;
            var opcao = $('<option></option>').val(optionValor).text(optionTexto);
            $selecionadoCampo.append(opcao);
        });
    }

    $('#codbarrasHospital').on('input', function () {
        var produto_id = $(this).val();
        atualizarCampoSelecionado(produto_id);
    });
    $('#produtoSelecionar').on('change', function () {
        var valorSelecionado = $(this).val();

        // Verifica se há valor selecionado
        if (valorSelecionado) {
            var valoresSeparados = valorSelecionado.split(';');

            // Define o valor máximo do campo number
            $('#quantidadeSelecionar').attr('max', valoresSeparados[3]);
        } else {
            // Se não houver seleção, pode definir o valor máximo como vazio ou algum valor padrão
            $('#quantidadeSelecionar').removeAttr('max');
        }
    });




    $('#dispensaHospital').click(function (event) {
        // Previne a atualização da página
        event.preventDefault();

        // Pega os valores do select e do input number
        var produtoSelecionar = $('#produtoSelecionar').val();
        var numberValue = $('#quantidadeSelecionar').val();

        var splitValues = produtoSelecionar.split(';');

        // Cria uma nova div e adiciona os campos input dentro dela
        var newDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
        newDiv.append('<input type="hidden" name="registro' + counter + '" value="' + splitValues[0] + '" readonly>');
        newDiv.append('<input type="hidden" name="produto_id' + counter + '" value="' + splitValues[1] + '" readonly>');
        newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + counter + '" value="' + splitValues[2] + '" readonly>');
        newDiv.append('<input type="number" class="form-control w-25 mx-2" name="quantidade' + counter + '" value="' + numberValue + '" readonly>');

        // Adiciona o botão de exclusão
        var deleteButton = $('<button type="button" class="btn btn-danger">Excluir</button>');
        deleteButton.click(function () {
            $(this).parent().remove();
        });
        newDiv.append(deleteButton);

        // Adiciona a nova div dentro do formulário alvo
        $('#pedidoForm').append(newDiv);


        // Incrementa o contador para os próximos campos
        counter++;
    });
});