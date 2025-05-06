$(document).ready(function () {
    var counter = 1;
    var dataContainer = $('#data-container');
    var lotes = dataContainer.data('lotes');
    
    $('#submitButton').click(function (event) {
        event.preventDefault();
    
        // Pega os valores do select e do input number
        var selectValue = $('#selectField').val();
        var numberValue = $('#numberField').val();
        var forne = $('#fornecedor').val();
        var splitValues = selectValue.split(';');
    
        // Cria um objeto para armazenar os dados
        var item = {
            id: splitValues[0],
            produto_nome: splitValues[1],
            forne: forne,
            quantidade: numberValue,
            lote: splitValues[0] + '-' + Date.now() // Adiciona um identificador único para o item
        };
    
        // Pega a lista do localStorage (ou cria uma nova se não existir)
       
    
        // Cria a nova div e adiciona no formulário
        adicionarItemNoFormulario(item);
    
        // Incrementa o contador para os próximos campos
        counter++;
    
        // Reseta o formulário
        $('#sourceForm')[0].reset();
        $('.select2-selection__rendered').html('Selecione Outro Produto!')
        produtosSelect(lotes);
    });
    
    function adicionarItemNoFormulario(item) {
        var newDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
        newDiv.append('<input type="hidden" name="produto_id' + counter + '" value="' + item.id + '" readonly>');
        newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + counter + '" value="' + item.produto_nome + '" readonly>');
        newDiv.append('<input type="text" class="form-control w-25 mx-2" name="fornecedor' + counter + '" value="' + item.forne + '" readonly>');
        newDiv.append('<input type="number" class="form-control w-25 mx-2" name="quantidade' + counter + '" value="' + item.quantidade + '" readonly>');
    
        var deleteButton = $('<button type="button" class="btn btn-danger btn-sm">Excluir</button>');
        deleteButton.click(function () {
            $(this).parent().remove();
          
        });
        newDiv.append(deleteButton);
    
        $('#targetForm').append(newDiv);
    }
    
 
    
   
    
    $('#limparLista').click(function () {
        // Limpa o conteúdo do formulário
        $('#targetForm').empty();
    
        // Remove a lista do localStorage
       
    
        // Reseta o contador
        counter = 1;
    });



});