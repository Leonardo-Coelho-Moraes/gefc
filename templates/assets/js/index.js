
   $(document).ready(function() {

    
     
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

        var lote = row.data('lote');
        var quantidade = row.data('quantidade');
        var fornecedor = row.data('fornecedor');
        var preco = row.data('preco');
        var PrecoComercial = row.data('preco-comercial');
        var local = row.data('localizacao');
        var vencimento = row.data('vencimento');
        // Exibe os dados no formulário de edição
        $('#lote_id').val(LoteId);
      
        $('#lote_edit').val(lote);
        
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


        // Exibe os dados no formulário de edição
        $('#produto_id').val(produtoId);
        $('#produto_edit').val(produtoNome);
    });
        // Captura o clique no botão "Editar"
        
        $('.editar-btn').on('click', function() {
          // Pega a linha da tabela correspondente ao botão clicado
          var row = $(this).closest('tr');

          // Captura os dados da linha
          var registroId = row.data('id');
          var produtoNome = row.data('produto-nome');
          var lote = row.data('lote');
          var quantidade = row.data('quantidade');

          // Exibe os dados no parágrafo
          $('#registro_id').val(registroId);
          $('#produtoNome').val(lote+" - "+produtoNome);
          $('#quantidade_editada').val(quantidade);
          $('#form_edit_entrada').css('display', 'block');

        });
      });