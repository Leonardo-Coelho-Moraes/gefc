$(document).ready(function () {
    var counter = 1;
    var dataContainer = $('#data-container');
    var lotes = dataContainer.data('lotes');



    function produtosSelect(lotes) {
        var $selectField = $('#selectField');
        $selectField.empty(); // Limpa o selectField

        // Adiciona a opção padrão
        $selectField.append('<option value="">Selecione um produto</option>');

        var validLotes = lotes;
        // Adiciona os lotes válidos ao selectField
        validLotes.forEach(function (lote) {
            var optionValue = `${lote.lote_id};${lote.lote};${lote.nome};${lote.quantidade}`;
            var optionText = `COD: ${lote.lote_id}, L:${lote.lote} - ${lote.nome} Qnt.: ${lote.quantidade} ${lote.unidade_contagem}  V:${lote.vencimento} ${lote.fornecedor}`;
            var option = $('<option></option>').val(optionValue).text(optionText);
            $selectField.append(option);
        });
    }





    // Preencher o selectField com os dados dos lotes
    produtosSelect(lotes);

    function updateSelectField(lote_id) {
        var $selectField = $('#selectField');
        $selectField.empty(); // Limpa o selectField

        // Adiciona a opção padrão


        // Filtra os lotes se o código não estiver vazio
        var filteredLotes = lote_id ? lotes.filter(function (lote) {
            return lote.lote_id == lote_id;
        }) : lotes; // Se cod estiver vazio, mostra todos os lotes


        // Adiciona os lotes ao selectField
        filteredLotes.forEach(function (lote) {
            var optionValue = `${lote.lote_id};${lote.lote};${lote.nome};${lote.quantidade}`;
            var optionText = `COD: ${lote.lote_id}, L:${lote.lote} - ${lote.nome} Qnt.: ${lote.quantidade} ${lote.unidade_contagem} V:${lote.vencimento} ${lote.fornecedor}`;
            var option = $('<option></option>').val(optionValue).text(optionText);
            $selectField.append(option);
        });
    }





    $('#cod_barras').on('input', function () {
        var lote_id = $(this).val();
        updateSelectField(lote_id);
    });
    $('#selectField').on('change', function () {
        var selectedValue = $(this).val();

        // Verifica se há valor selecionado
        if (selectedValue) {
            var splitValues = selectedValue.split(';');

            // Define o valor máximo do campo number
            $('#numberField').attr('max', splitValues[3]);
        } else {
            // Se não houver seleção, pode definir o valor máximo como vazio ou algum valor padrão
            $('#numberField').removeAttr('max');
        }
    });




    // Limpa o campo cod_barras quando uma opção é selecionada

    // Limpa o campo cod_barras quando uma opção é selecionada



    $('#submitButton').click(function (event) {
        // Previne a atualização da página
        event.preventDefault();


        // Pega os valores do select e do input number
        var selectValue = $('#selectField').val();
        var numberValue = $('#numberField').val();
        var qntSolic = $('#qntSolic').val();
        var splitValues = selectValue.split(';');

        // Cria uma nova div e adiciona os campos input dentro dela
        var newDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
        newDiv.append('<input type="hidden"  name="lote' + counter + '" value="' + splitValues[0] + '" readonly>');
        newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="lote_nome' + counter + '" value="' + splitValues[1] + '" readonly>');
        newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + counter + '" value="' + splitValues[2] + '" readonly>');
        newDiv.append('<input type="number" class="form-control w-25 mx-2" name="qntSolic' + counter + '" value="' + qntSolic + '" readonly>');
        newDiv.append('<input type="number" class="form-control w-25 mx-2" name="quantidade' + counter + '" value="' + numberValue + '" readonly>');

        // Adiciona o botão de exclusão
        var deleteButton = $('<button type="button" class="btn btn-danger btn-sm">Excluir</button>');
        deleteButton.click(function () {
            $(this).parent().remove();
        });
        newDiv.append(deleteButton);

        // Adiciona a nova div dentro do formulário alvo
        $('#targetForm').append(newDiv);


        // Incrementa o contador para os próximos campos
        counter++;

        $('#sourceForm')[0].reset();
        produtosSelect(lotes);
    });




});