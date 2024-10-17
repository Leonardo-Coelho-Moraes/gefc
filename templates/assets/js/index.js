
   $(document).ready(function() {

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
       $('.editLote').on('click', function() {
        // Pega a linha da tabela correspondente ao botão clicado
        var row = $(this).closest('tr');

        // Captura os dados da linha
        var LoteId = row.data('id');
        var produto = row.data('produto');
        var nome = row.data('nome');
        
        
        var lote = row.data('lote');
        var quantidade = row.data('quantidade');
        var fornecedor = row.data('fornecedor');
        var preco = row.data('preco');
        var PrecoComercial = row.data('preco-comercial');
        var local = row.data('localizacao');
        var vencimento = row.data('vencimento');
        // Exibe os dados no formulário de edição
        $('#lote_id').val(LoteId);
      
      $('#select2-produto_edit-container').text(nome);
        $('#lote_edit').val(lote);
        $('#produto_edit').val(produto);
        $('#quantidade_edit').val(quantidade);
        $('#fornecedor_edit').val(fornecedor);
        $('#preco_edit').val(preco);
        $('#preco_comercial_edit').val(PrecoComercial);
         $('#localizacao_edit').val(local);
        $('#vencimento_edit').val(vencimento);
        $('#form_edit_lote').css('display', 'block');
    }); 

       
      $('.editProduto').on('click', function() {
        // Pega a linha da tabela correspondente ao botão clicado
        var row = $(this).closest('tr');

        // Captura os dados da linha
        var produtoId = row.data('id');
        var produtoNome = row.data('nome');
        var produtoCrit = row.data('crit');


        // Exibe os dados no formulário de edição
        $('#produto_id').val(produtoId);
        $('#produto_edit').val(produtoNome);
          $('#crit_edit').val(produtoCrit);
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