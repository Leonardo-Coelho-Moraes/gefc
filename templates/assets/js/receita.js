$(document).ready(function () {
    
    var urlsite = $('#entradaLink').val();
   
    var counter = 1;
    $('#submitButton').click(function (event) {
        event.preventDefault();
    
        // Pega os valores do select e do input number
        var selectValue = $('#selectField').val();
        var numberValue = $('#numberField').val();
        var splitValues = selectValue.split(';');
    
        // Cria um objeto para armazenar os dados
        var item = {
            id: splitValues[0],
            produto_nome: splitValues[1],
            quantidade: numberValue,
          
        };

        adicionarItemNoFormulario(item);
    
        // Incrementa o contador para os próximos campos
        counter++;
    
        // Reseta o formulário
        $('#sourceForm')[0].reset();
        $('.select2-selection__rendered').html('Selecione Outro Produto!')
    
    });
    
    function adicionarItemNoFormulario(item) {
        var newDiv = $('<div class="form-group d-flex align-items-center border-bottom pb-2 mb-2"></div>');
        newDiv.append('<input type="hidden" name="produto_id' + counter + '" value="' + item.id + '" readonly>');
        newDiv.append('<input type="text" class="form-control-plaintext flex-grow-1" name="produto_nome' + counter + '" value="' + item.produto_nome + '" readonly>');
        newDiv.append('<input type="number" class="form-control w-25 mx-2" name="quantidade' + counter + '" value="' + item.quantidade + '" readonly>');
    
        var deleteButton = $('<button type="button" class="btn btn-danger btn-sm">Excluir</button>');
        deleteButton.click(function () {
            $(this).parent().remove();
          
        });
        newDiv.append(deleteButton);
    
        $('#targetForm').append(newDiv);
    }
    
    $('.editarReceita').on('click', function () {
        var row = $(this).closest('tr');
      

           // Captura os dados da linha
           var registroId = row.data('id');
      

           // Exibe os dados no parágrafo
           $('#registro_id').val(registroId);
        $('.select2-selection__rendered').html('Selecione Outro Produto!')
        $('#form_edit_receita').css('display', 'block');

      });

    $('#pacientePesquisa').select2({
 
        placeholder: 'Digite o Nome ou Cartão SUS',
        minimumInputLength: 3, // Mínimo de caracteres antes de iniciar a busca
        ajax: {
            url: urlsite +'receitas', // URL para o script PHP que buscará os dados
            type: 'POST', // Mudando o método para POST
            dataType: 'json',
            delay: 250, // Adiciona um pequeno delay para evitar múltiplas requisições
            data: function (params) {
                return {
                    pacientePesquisa: params.term // O termo de busca digitado
                };
               
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id, // ID que será o valor selecionado
                            text:  item.nome + ' '+item.sus 
                        }
                      
                    })
                };
               
            }
        }
    });

    
    $('#prescritorPesquisa').select2({
 
        placeholder: 'Digite o Nome ou CRM',
        minimumInputLength: 2, // Mínimo de caracteres antes de iniciar a busca
        ajax: {
            url: urlsite +'receitas', // URL para o script PHP que buscará os dados
            type: 'POST', // Mudando o método para POST
            dataType: 'json',
            delay: 250, // Adiciona um pequeno delay para evitar múltiplas requisições
            data: function (params) {
                return {
                    prescritorPesquisa: params.term // O termo de busca digitado
                };
               
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id, // ID que será o valor selecionado
                            text:  item.nome + ' '+item.crm 
                        }
                      
                    })
                };
               
            }
        }
    });

    $('#pacienteEdit').select2({
 
        placeholder: 'Digite o Nome ou Cartão SUS',
        minimumInputLength: 3, // Mínimo de caracteres antes de iniciar a busca
        ajax: {
            url: urlsite +'receitas', // URL para o script PHP que buscará os dados
            type: 'POST', // Mudando o método para POST
            dataType: 'json',
            delay: 250, // Adiciona um pequeno delay para evitar múltiplas requisições
            data: function (params) {
                return {
                    pacientePesquisa: params.term // O termo de busca digitado
                };
               
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id, // ID que será o valor selecionado
                            text:  item.nome + ' '+item.sus 
                        }
                      
                    })
                };
               
            }
        }
    });

    
    $('#prescritorEdit').select2({
 
        placeholder: 'Digite o Nome ou CRM',
        minimumInputLength: 2, // Mínimo de caracteres antes de iniciar a busca
        ajax: {
            url: urlsite +'receitas', // URL para o script PHP que buscará os dados
            type: 'POST', // Mudando o método para POST
            dataType: 'json',
            delay: 250, // Adiciona um pequeno delay para evitar múltiplas requisições
            data: function (params) {
                return {
                    prescritorPesquisa: params.term // O termo de busca digitado
                };
               
            },
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            id: item.id, // ID que será o valor selecionado
                            text:  item.nome + ' '+item.crm 
                        }
                      
                    })
                };
               
            }
        }
    });

  
});