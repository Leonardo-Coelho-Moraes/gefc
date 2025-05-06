$(document).ready(function () {
    var urlsite = $('#lotesLink').val();
function envio(link, dados) {

    $.ajax({
        url: link,
        method: 'POST', // Envia como POST, altere se necessário
        data: dados,
        success: function (response) {
            // Exibe uma mensagem de sucesso
            Swal.fire({
                title: 'Sucesso',
                text: response,
                icon: 'success',
                timer: 2500
            });


        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema.',
                'error'
            );
        }
    });
}
function registroLotes(busca) {
    // Obtém o valor do link
     $.ajax({
         url: urlsite+'lotes', // URL do arquivo PHP que realizará a pesquisa
         method: 'POST', // Envia os dados via POST
         data: {
             pesquisa: busca
         }, // Passa o valor digitado
         success: function (response) {
            var data = JSON.parse(response);
            criarCards(data);
            // criarCards(data) ;// Exibe os resultados na div
         },
         error: function () {
             $('#lotes').html('<p>Erro ao buscar Lotes</p>');
         }
     });
}
 
 $('#pesquisarLotes').keyup(function () {
 let pesquisa = $(this).val(); // Obtém o valor digitado
 if (pesquisa.length > 1) { // Executa apenas se houver mais de um caractere digitado
    registroLotes(pesquisa);
 } else {
    registroLotes(' '); // Limpa os resultados se o campo estiver vazio
 }
 });
 
function criarCards(dados) {
    const container = $('#lotes');
    container.empty(); // Limpa os cards anteriores

    dados.forEach(item => {

        const card = `
        <div class="callout callout-info">
            <h6><strong>${item.id}</strong> - ${item.nome}, ${item.fornecedor}</h6>
            <p>L:${item.lote} - Q:${item.quantidade} - Local:${item.localizacao} - V:${item.vencimento} <a
                       data-id="${ item.id }" data-nome="${ item.nome }" data-lote="${item.lote}"
                data-quantidade="${ item.quantidade }"
                data-fornecedor="${ item.fornecedor }"
                data-vencimento="${ item.vencimento }" data-localizacao="${item.localizacao}"
                data-produto="${item.produto_id}}"
                       class="btn btn-primary editarLote btn-sm">Editar</a></p>
          </div>
           `;
        container.append(card); // Adiciona cada card ao container
    });
}

registroLotes(' ');

$(document).on('click', '.editarLote', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin
          // Captura os dados da linha
          var LoteId = $(this).data('id');
          var produto = $(this).data('produto');
          var nome = $(this).data('nome');


          var lote = $(this).data('lote');
          var quantidade = $(this).data('quantidade');
          var fornecedor = $(this).data('fornecedor');
       
          var local = $(this).data('localizacao');
          var vencimento = $(this).data('vencimento');
          // Exibe os dados no formulário de edição
          $('#lote_id').val(LoteId);

          $('#select2-produto_edit-container').text(nome);
          $('#lote_edit').val(lote);
          $('#produto_edit').append(`<option value="${ produto }" selected>${nome }</option>`);
          $('#quantidade_edit').val(quantidade);
          $('#fornecedor_edit').val(fornecedor);
       
          $('#localizacao_edit').val(local);
          $('#vencimento_edit').val(vencimento);
          $('#form_edit_lote').css('display', 'block');
            $('.nav-pills a[href="#tab_2"]').tab('show');
      
});

  $('#produto_edit').select2({

      placeholder: 'Pesquise produtos',
      minimumInputLength: 2, // Mínimo de caracteres antes de iniciar a busca
      ajax: {
          url: urlsite + 'entrada', // URL para o script PHP que buscará os dados
          type: 'POST', // Mudando o método para POST
          dataType: 'json',
          delay: 80, // Adiciona um pequeno delay para evitar múltiplas requisições
          data: function (params) {
              return {
                  produto: params.term // O termo de busca digitado
              };

          },
          processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      return {
                          id: `${item.id}`,
                          text: item.nome // Exibe o nome do produto e o lote
                      }
                  })
              };
          }
      }
  });
   $('#produto').select2({

       placeholder: 'Pesquise produtos',
       minimumInputLength: 2, // Mínimo de caracteres antes de iniciar a busca
       ajax: {
           url: urlsite + 'entrada', // URL para o script PHP que buscará os dados
           type: 'POST', // Mudando o método para POST
           dataType: 'json',
           delay: 80, // Adiciona um pequeno delay para evitar múltiplas requisições
           data: function (params) {
               return {
                   produto: params.term // O termo de busca digitado
               };

           },
           processResults: function (data) {
               return {
                   results: $.map(data, function (item) {
                       return {
                           id: `${item.id}`,
                           text: item.nome // Exibe o nome do produto e o lote
                       }
                   })
               };
           }
       }
   });

   $(document).on('click', '#editarLoteBotao', function (e) {
       e.preventDefault(); // Impede o comportamento padrão do lin
       let formulario = $('#editarLoteForm').serialize();
       envio(urlsite + 'lotes/', formulario)
        registroLotes(' ');
       $('#editarLoteForm')[0].reset()
       $('.nav-pills a[href="#tab_1"]').tab('show')

   });
   $(document).on('click', '#cadastrarLoteBotao', function (e) {
       e.preventDefault(); // Impede o comportamento padrão do lin
       let formulario = $('#cadastrarProdutoForm').serialize();
       envio(urlsite + 'lotes/', formulario)

       $('#cadastrarProdutoForm')[0].reset()
       $('.nav-pills a[href="#tab_1"]').tab('show')
       registroLotes(' ');

   });






   

});