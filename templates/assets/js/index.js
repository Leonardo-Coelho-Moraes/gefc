
   $(document).ready(function() {
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
    $('.editar-paciente').on('click', function() {
      // Pega a linha da tabela correspondente ao botão clicado
      var row = $(this).closest('tr');

      // Captura os dados da linha
      var id = row.data('id');
      var nome = row.data('nome');
      var local = row.data('local');
      var nas = row.data('nas');
      var sus = row.data('sus');
      var endereco = row.data('endereco');
      var contato = row.data('contato');

      // Exibe os dados no formulário de edição
      $('#paciente_id').val(id);
    

      $('#nome_edit').val(nome);
      $('#local_edit').val(local);
      $('#data_nas_edit').val(nas);
      $('#sus_edit').val(sus);
      $('#endereco_edit').val(endereco);
      $('#contato_edit').val(contato);
      $('#form_edit_paciente').css('display', 'block');
  }); 
     
       $('.editarEstoqueLocal').on('click', function() {
        // Pega a linha da tabela correspondente ao botão clicado
        
        var row = $(this).closest('tr');

        // Captura os dados da linha
        var registroID = row.data('id');
        var produtoNOme = row.data('produto');
        console.log('certo');
        // Exibe os dados no formulário de edição
        $('#id').val(registroID);
        $('#produto').val(produtoNOme);
        $('#form_edit_estoque').css('display', 'block');
    }); 
      


        
         $('.editarSaida').on('click', function () {
           // Pega a linha da tabela correspondente ao botão clicado
           var row = $(this).closest('tr');
          console.log('oi');

           // Captura os dados da linha
           var registroId = row.data('id');
           var produtoNome = row.data('nome');
           var lote = row.data('lote');
           var quantidade = row.data('quantidade');

           // Exibe os dados no parágrafo
           $('#registro_id').val(registroId);
           $('#produtoNome').val('COD:'+lote + " - " + produtoNome);
           $('#quantidade_editada').val(quantidade);
           $('#form_edit_saida').css('display', 'block');

         });
      });