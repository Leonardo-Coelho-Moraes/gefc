

$(document).ready(function() {
    var contador = 1;
    

     $('#pedido').click(function (event) {
                 // Previne a atualização da página
                 event.preventDefault();
                 console.log('aqui');

                 // Pega os valores do select e do input number
                 var produtoPedidoFazer = $('#produtoPedidoFazer').val();
                 var quantidadePedidoFazer = $('#quantidadePedidoFazer').val();

                 var pedidoDividido = produtoPedidoFazer.split(';');

                 // Cria uma nova div e adiciona os campos input dentro dela
                 var novaDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
                 novaDiv.append('<input type="hidden" name="produto' + contador + '" value="' + pedidoDividido[0] + '" readonly>');
                 novaDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + contador + '" value = "' + pedidoDividido[1] + '"readonly > ');
                novaDiv.append('<input type="number" class="form-control w-25 mx-2" name="quantidade' +contador + '"value = "' + quantidadePedidoFazer + '"readonly > ');

                         var botaoDeletar = $('<button type="button" class="btn btn-danger">Excluir</button>'); botaoDeletar.click(function () {
                             $(this).parent().remove();
                         }); novaDiv.append(botaoDeletar);

                         // Adiciona a nova div dentro do formulário alvo
                         $('#pedidoForm').append(novaDiv);

                         // Incrementa o contador para os próximos campos
                         contador++;
                         $('#formularioPedido')[0].reset();
                     });

   
});