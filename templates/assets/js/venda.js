$(document).ready(function() {
    var counter = 1;
    $('#submitButton').click(function(event) {
        // Previne a atualização da página
        event.preventDefault();

        // Pega os valores do select e do input number
        var selectValue = $('#selectField').val();
        var numberValue = $('#numberField').val();
        var qntSolic = $('#qntSolic').val();
        var splitValues = selectValue.split(',');

        // Cria uma nova div e adiciona os campos input dentro dela
        var newDiv = $('<div class="flex border-b"></div>');
        newDiv.append('<input type="text" class="flex w-16 border-transparent outline-transparent" name="produto' + counter + '" value="' + splitValues[0] + '" readonly>');
        newDiv.append('<input type="text" class="flex w-full border-transparent outline-transparent" name="produto_nome' + counter + '" value="' + splitValues[1] + '" readonly>');
        newDiv.append('<input type="number" class="w-28 flex border-transparent outline-transparent" name="qntSolic' + counter + '" value="' + qntSolic + '" readonly>');
        newDiv.append('<input type="number" class="w-28 flex border-transparent outline-transparent" name="quantidade' + counter + '" value="' + numberValue + '" readonly>');
        
        // Adiciona o botão de exclusão
        var deleteButton = $('<button type="button" class="hover:text-red-600">Excluir</button>');
        deleteButton.click(function() {
            $(this).parent().remove();
        });
        newDiv.append(deleteButton);

        // Adiciona a nova div dentro do formulário alvo
        $('#targetForm').append(newDiv);

        // Incrementa o contador para os próximos campos
        counter++;
    });
    
    $('#pedirBotão').click(function(event) {
        // Previne a atualização da página
        event.preventDefault();

        // Pega os valores do select e do input number
        var produtoSelecionar = $('#produtoSelecionar').val();
        var numberValue = $('#quantidadeSelecionar').val();

        var splitValues = produtoSelecionar.split(',');

        // Cria uma nova div e adiciona os campos input dentro dela
        var newDiv = $('<div class="flex border-b"></div>');
        newDiv.append('<input type="text" class="flex w-16 border-transparent outline-transparent" name="produto' + counter + '" value="' + splitValues[0] + '" readonly>');
        newDiv.append('<input type="text" class="flex w-full border-transparent outline-transparent" name="produto_nome' + counter + '" value="' + splitValues[1] + '" readonly>');
        newDiv.append('<input type="number" class="w-28 flex border-transparent outline-transparent" name="quantidade' + counter + '" value="' + numberValue + '" readonly>');
        
        // Adiciona o botão de exclusão
        var deleteButton = $('<button type="button" class="hover:text-red-600">Excluir</button>');
        deleteButton.click(function() {
            $(this).parent().remove();
        });
        newDiv.append(deleteButton);

        // Adiciona a nova div dentro do formulário alvo
        $('#pedidoForm').append(newDiv);

        // Incrementa o contador para os próximos campos
        counter++;
    });
});