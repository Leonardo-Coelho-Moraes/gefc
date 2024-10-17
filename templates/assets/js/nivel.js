$(document).ready(function () {
    var counter = 1;
     var dataContainer = $('#data-container');
     var lotes = dataContainer.data('lotes');


    function produtosSelecionar(estoques) {
        var $selecionadoCampo = $('#produtoSelecionar');
        $selecionadoCampo.empty(); // Limpa o selectField

        // Adiciona a opção padrão
        $selecionadoCampo.append('<option value="">Selecione um produto</option>');

        var validEstoques = estoques;
        // Adiciona os lotes válidos ao selectField
        validEstoques.forEach(function (estoque) {
            var optionValor = `${estoque.id};${estoque.nome};`;
            var optionTexto = estoque.nome;
            var opcao = $('<option></option>').val(optionValor).text(optionTexto);
            $selecionadoCampo.append(opcao);
        });
    }





    // Preencher o selectField com os dados dos lotes
    produtosSelecionar(lotes);




    $('#submitButton').click(function (event) {
        // Previne a atualização da página
        event.preventDefault();

        // Pega os valores do select e do input number
        var produtoSelecionar = $('#produtoSelecionar').val();
       

        var splitValues = produtoSelecionar.split(';');

        // Cria uma nova div e adiciona os campos input dentro dela
        var newDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
        newDiv.append('<input type="hidden" name="produto' + counter + '" value="' + splitValues[0] + '" readonly>');
        newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + counter + '" value="' + splitValues[1] + '" readonly>');

        // Adiciona o botão de exclusão
        var deleteButton = $('<button type="button" class="btn btn-danger">Excluir</button>');
        deleteButton.click(function () {
            $(this).parent().remove();
        });
        newDiv.append(deleteButton);

        // Adiciona a nova div dentro do formulário alvo
        $('#targetForm').append(newDiv);


        // Incrementa o contador para os próximos campos
        counter++;
    });
});