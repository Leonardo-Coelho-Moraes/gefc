$(document).ready(function () {
    var urlsite = $('#entradaLink').val();
function registroSaidas(busca) {
    // Obtém o valor do link
    
     $.ajax({
         url: urlsite+'entrada', // URL do arquivo PHP que realizará a pesquisa
         method: 'POST', // Envia os dados via POST
         data: {
             relatorio: busca
         }, // Passa o valor digitado
         success: function (response) {
            var data = JSON.parse(response);
             criarCards(data) ;// Exibe os resultados na div
         },
         error: function () {
             $('#entradas').html('<p>Erro ao buscar dados</p>');
         }
     });
}
 
 $('#pesquisaEntrada').keyup(function () {
 var query = $(this).val(); // Obtém o valor digitado
    console.log(query);
 if (query.length > 1) { // Executa apenas se houver mais de um caractere digitado
    registroSaidas(query);
 } else {
    registroSaidas(''); // Limpa os resultados se o campo estiver vazio
 }
 });
 
function criarCards(dados) {
    const container = $('#entradas');
    container.empty(); // Limpa os cards anteriores

    dados.forEach(item => {
        const card = `
  <tr>
              <td>${item.lote_id}</td>
                  <td style='max-width:400px;text-warp:warp;'>${item.nome} - ${item.fornecedor}</td>
                  <td>${item.lote}</td>
                    <td>${item.quantidade}</td>
                    <td>${item.data}</td>
                    <td><a data-id="${item.registro_id}" data-nome="${item.nome}" data-quantidade="${item.quantidade}" class="btn btn-primary editarEntrada">Editar</a>
                        <a class='deletarEntrada btn btn-danger'
                data-link="entrada/deletar/${item.registro_id}">Deletar</a></td>

                
                    
            </tr>`;
        container.append(card); // Adiciona cada card ao container
    });
}



function criarGrid(data) {
    // Limpa o grid anterior, se necessário
    const gridContainer = document.getElementById('grid'); // Altere o ID para o que você estiver usando
    gridContainer.innerHTML = ''; // Remove o conteúdo anterior

    // Cria o Grid.js
    new gridjs.Grid({
        columns: [
            {
                id: 'id',
                name: 'Id'

            }, {
                id: 'cod',
                name: 'COD'
               
            },
            {
                id: 'lote',
                name: 'Lote',
                 width: 'auto'
            },
            {
                id: 'produto',
                name: 'Produto',
                 width: '600px'
            },
            {
                id: 'qnt',
                name: 'Qnt'
            },
            {
                id: 'fornecedor',
                name: 'Fornecedor',
                 width: 'auto'
            },
            {
                id: 'data',
                name: 'Data'
            },
             {
                 id: 'acao',
                 name: '#',
                 formatter: (_, row) => {
                     return gridjs.html(`
                        <div style="display:flex; gap:8px;">
                            <a  class='deletarEntrada'
                data-link="entrada/deletar/${row.cells[0].data}"
                    class="text-danger" > <i class="fa-solid fa-trash" ></i> </a>
                <a  data-link="entrada/editar/${row.cells[0].data}"
                data-id="${row.cells[0].data}" data-nome="${row.cells[3].data}" data-quantidade="${row.cells[4].data}"
                    class="text-primary editarEntrada" > <i class="fa-solid fa-pen"></i> </a>
                    
                        </div>
                    `);
                 },
                 width: 'auto'
             }
            // Adicione mais colunas conforme necessário
        ],
        data: data.map(item => [
            item.registro_id,
            item.lote_id, // Altere para os nomes reais das suas propriedades
            item.lote,
            item.nome,
            item.quantidade,
            item.fornecedor,
            item.data,
            'ação'
            
            // Adicione mais propriedades conforme necessário
        ]),
        pagination: {
            limit: 30 // Limite de itens por página
        },
        search: true,
        resizable: true,
        sort: true,
        language: {
            'search': {
                'placeholder': 'Procurar...'
            },
            'pagination': {
                'previous': 'Anterior',
                'next': 'Próximo',
                'showing': 'Exibindo',
                'results': () => 'resultados'
            }
        }, style: {
           
            td: {
                'padding': '3px 6px'
            }
        }
    }).render(gridContainer); // Renderiza no container do grid
}


registroSaidas('');


$(document).on('click', '.deletarEntrada', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link

    var link = $(this).data('link'); // Obtém o link de exclusão do data-link

   
    Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Envia uma requisição GET para o link de exclusão
            $.ajax({
                url: urlsite + link,
                method: 'GET',
                success: function (response) {
                    // Supondo que o backend retorne uma mensagem de sucesso
                    Swal.fire({
                        title: 'Excluído',
                        text: 'O item foi excluído com sucesso.',
                        icon: 'success',
                        confirmButtonText: 'Ok',
                        timer: 800
                    });
                     registroSaidas('');
                },
                error: function () {
                    Swal.fire(
                        'Erro!',
                        'Houve um erro ao excluir o item.',
                        'error'
                    );
                }
            });
        }
    });
});

$(document).on('click', '.editarEntrada', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link

   
    var id = $(this).data('id'); // ID do item
    var nome = $(this).data('nome'); // Nome do item
    var quantidade = $(this).data('quantidade'); // Quantidade do item

    // Exibe o Swal com o formulário
    Swal.fire({
        title: 'Editar Entrada',
        html: `<form id="editEntrada">
                
                <input id="registro_id" name="registro_id" type="hidden" class="swal2-input" value="${id}" readonly>
                
                <label for="produto_nome">Nome</label>
                <input id="produto_nome" name="produto_nome" type="text" class="swal2-input" value="${nome}" readonly>
                
                <label for="quantidade_editada">Quantidade</label>
                <input name="quantidade_editada" id="quantidade_editada" type="number" class="swal2-input" value="${quantidade}" required>
            </form>`,
        showCancelButton: true,
        confirmButtonText: 'Atualizar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            // Faz o serialize do formulário
            return $('#editEntrada').serialize();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = result.value; // Dados do formulário retornados pelo Swal

            // Envia os dados via AJAX para o link fornecido
            $.ajax({
                url: urlsite+'entrada',
                method: 'POST', // Envia como POST, altere se necessário
                data: formData,
                success: function (response) {
                    // Exibe uma mensagem de sucesso
                    Swal.fire({
                        title: 'Sucesso',
                        text: 'Editado com Sucesso. Corrija a quantidade de estoque do produto pra mais ou para menos!',
                        icon: 'success',
                        timer: 2500
                    });

                    // Chame a função para atualizar a lista, se necessário
                    registroSaidas(''); // Atualiza a lista sem recarregar a página
                },
                error: function () {
                    Swal.fire(
                        'Erro!',
                        'Houve um problema ao atualizar a entrada.',
                        'error'
                    );
                }
            });
        }
    });
});


$(document).on('click', '#enviarEntradaLote', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link
    let formulario = $('#formEntradaLote').serialize();
    $.ajax({
    url: urlsite + 'entrada',
    method: 'POST', // Envia como POST, altere se necessário
    data: formulario,
    success: function (response) {
        // Exibe uma mensagem de sucesso
        Swal.fire({
            title: 'Sucesso',
            text: 'Lote Adicionado com Sucesso ao estoque.',
            icon: 'success',
            timer: 2500
        });

        // Chame a função para atualizar a lista, se necessário
        registroSaidas('');
        $('#formEntradaLote')[0].reset();// Atualiza a lista sem recarregar a página
    },
    error: function () {
        Swal.fire(
            'Erro!',
            'Houve um problema ao dar entrada.',
            'error'
        );
    }
});
 
            
        
   
});

$(document).on('click', '#cadastrar', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link
    let formulario = $('#formEntrada').serialize();
    $.ajax({
        url: urlsite + 'entrada',
        method: 'POST', // Envia como POST, altere se necessário  
        data: formulario,
        success: function (response) {
            // Exibe uma mensagem de sucesso
            Swal.fire({
                title: 'Sucesso',
                text: 'Entrada feita com Sucesso ao estoque.',
                icon: 'success',
                timer: 2500
            });

            // Chame a função para atualizar a lista, se necessário
            registroSaidas('');
            $('#formEntrada')[0].reset(); // Atualiza a lista sem recarregar a página
        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema ao dar entrada.',
                'error'
            );
        }
    });




});

  $('#produtoSelect').select2({
 
      placeholder: 'Pesquise por COD, produto ou lote',
      minimumInputLength: 2, // Mínimo de caracteres antes de iniciar a busca
      ajax: {
          url: urlsite +'entrada', // URL para o script PHP que buscará os dados
          type: 'POST', // Mudando o método para POST
          dataType: 'json',
          delay: 250, // Adiciona um pequeno delay para evitar múltiplas requisições
          data: function (params) {
              return {
                  query: params.term // O termo de busca digitado
              };
             
          },
          processResults: function (data) {
              return {
                  results: $.map(data, function (item) {
                      return {
                          id: item.id, // ID que será o valor selecionado
                          text: "Cod: "+ item.id + ' '+item.lote + ' - ' + item.nome + ' ' + item.fornecedor // Exibe o nome do produto e o lote
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
           delay: 250, // Adiciona um pequeno delay para evitar múltiplas requisições
           data: function (params) {
               return {
                   produto: params.term // O termo de busca digitado
               };

           },
           processResults: function (data) {
               return {
                   results: $.map(data, function (item) {
                       return {
                           id: `${item.id},${item.nome}`, 
                           text: item.nome // Exibe o nome do produto e o lote
                       }
                   })
               };
           }
       }
   });



$('#lote').keyup(function () {
    var query = $(this).val(); // Obtém o valor digitado
    
    if (query.length > 1) { // Executa apenas se houver mais de um caractere digitado
       $.ajax({
           url: urlsite + 'entrada', // URL do arquivo PHP que realizará a pesquisa
           method: 'POST', // Envia os dados via POST
           data: {
               lote: query
           }, // Passa o valor digitado
           success: function (response) {
                   var data = JSON.parse(response); // Tenta converter a resposta em JSON

                   if (data.length > 0) {
                    $.map(data, function (item) {
                        
                         
                        
                            $('#lotesExistentes').empty();
                            $('#lotesExistentes').addClass('d-flex');
                            $('#lotesExistentes').append(`<span>Já tem esse lote Cadastrado para:</span><span >${item.nome}</span>`);
                           
                        
                    })
                       
                      
                   }
                    else {
                        $('#lotesExistentes').empty();
                        
                    }
               
           },
           error: function () {
               $('#entradas').html('<p>Erro ao buscar dados</p>');
           }
       });
    }
});

});