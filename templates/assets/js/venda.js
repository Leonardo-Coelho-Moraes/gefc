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
       event.preventDefault();

       // Pega os valores do select e do input number
       var selectValue = $('#selectField').val();
       var numberValue = $('#numberField').val();
       var qntSolic = $('#qntSolic').val();
       var splitValues = selectValue.split(';');

       // Cria um objeto para armazenar os dados
       var item = {
           lote: splitValues[0],
           lote_nome: splitValues[1],
           produto_nome: splitValues[2],
           qntSolic: qntSolic,
           quantidade: numberValue
       };

       // Pega a lista do localStorage (ou cria uma nova se não existir)
       var itemList = JSON.parse(localStorage.getItem('itemList')) || [];
       itemList.push(item);

       // Salva a lista atualizada no localStorage
       localStorage.setItem('itemList', JSON.stringify(itemList));

       // Cria a nova div e adiciona no formulário (como você já faz)
       adicionarItemNoFormulario(item);

       // Incrementa o contador para os próximos campos
       counter++;

       $('#sourceForm')[0].reset();
       produtosSelect(lotes);
   });

   function adicionarItemNoFormulario(item) {
       var newDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
       newDiv.append('<input type="hidden" name="lote' + counter + '" value="' + item.lote + '" readonly>');
       newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="lote_nome' + counter + '" value="' + item.lote_nome + '" readonly>');
       newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + counter + '" value="' + item.produto_nome + '" readonly>');
       newDiv.append('<input type="number" class="form-control w-25 mx-2" name="qntSolic' + counter + '" value="' + item.qntSolic + '" readonly>');
       newDiv.append('<input type="number" class="form-control w-25 mx-2" name="quantidade' + counter + '" value="' + item.quantidade + '" readonly>');

       var deleteButton = $('<button type="button" class="btn btn-danger btn-sm">Excluir</button>');
       deleteButton.click(function () {
           $(this).parent().remove();
           removerItemDoLocalStorage(item.lote);
       });
       newDiv.append(deleteButton);

       $('#targetForm').append(newDiv);
   }
   var itemList = JSON.parse(localStorage.getItem('itemList')) || [];
   itemList.forEach(function (item) {
       adicionarItemNoFormulario(item);
       counter++; // Mantém o contador atualizado
   });

   function removerItemDoLocalStorage(lote) {
       var itemList = JSON.parse(localStorage.getItem('itemList')) || [];
       itemList = itemList.filter(function (item) {
           return item.lote !== lote;
       });
       localStorage.setItem('itemList', JSON.stringify(itemList));
   }
   $('#limparLista').click(function () {
       // Limpa o conteúdo do formulário
       $('#targetForm').empty();

       // Remove a lista do localStorage
       localStorage.removeItem('itemList');

       // Reseta o contador
       counter = 0;
   });



});