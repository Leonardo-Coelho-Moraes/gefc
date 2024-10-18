$(document).ready(function () {
    var urlsite = $('#produtosLink').val();

    var tipos = $('#tiposLista');
    var tiposArray = tipos.data('tipos');

function registroProdutos(busca) {
    // Obtém o valor do link
    
     $.ajax({
         url: urlsite+'produtos', // URL do arquivo PHP que realizará a pesquisa
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
             $('#produtos').html('<p>Erro ao buscar Produtos</p>');
         }
     });
}
 
 $('#pesquisaProdutos').keyup(function () {
 var query = $(this).val(); // Obtém o valor digitado
    console.log(query);
 if (query.length > 1) { // Executa apenas se houver mais de um caractere digitado
    registroProdutos(query);
 } else {
    registroProdutos(''); // Limpa os resultados se o campo estiver vazio
 }
 });
 
function criarCards(dados) {
    const container = $('#produtos');
    container.empty(); // Limpa os cards anteriores

    dados.forEach(item => {
             // Divide os IDs de tipos separados por vírgulas em um array
             const tipo_ids = item.tipo ? item.tipo.split(',') : [];

             // Mapeia cada ID para o nome correspondente no tiposArray e junta os resultados
             const nomeTipo = tipo_ids
                 .map(tipo_id => {
                     const tipoEncontrado = tiposArray.find(tipo => tipo.id == tipo_id.trim());
                     return tipoEncontrado ? tipoEncontrado.nome : 'Desconhecido';
                 })
                 .join(', '); // Junta os nomes separados por vírgula

                 
                  
                  
                 
                  

        const card = `
            <div class="col-md-4"
            >
                <div class="card" style='width:400px;height:150px;position:relative;'>
                
                    <div class="card-body d-flex" >
                    <i  style='position:absolute;font-size:100px;z-index:4;opacity:0.2;bottom:0;left:0;' class="fa-solid fa-tablets"></i>
                    <div style='z-index:10;'> <p class="text-sm" > ${item.nome} Qnt: ${item.total_quantidade}</p>
                        <p class="text-sm" > ${nomeTipo}</p>

                        <i data-id='${item.id}'  class="verMais text-xl fa-solid fa-eye"></i>
</div>
                       
                       <div class='d-flex'
                       style='flex-direction: column;top: 0;position: absolute;width: 22px;height: 100%;right: 0;justify-content: space-evenly;padding-rigth:2px;padding-left:2px;' > <a data-id="${ item.id }"
                       data-nome="${ item.nome }"
                       data-crit="${item.qnt_crit }"
                       class="bg-primary text-white editarProduto d-flex h-100 justify-content-center
    align-items-center" style='border-top-left-radius: 9px;'><i class="icone fa-solid fa-pen" ></i></a>
                        <a class='deletarProduto text-white bg-danger d-flex h-100 justify-content-center
                        align-items-center'
                data-link="produtos/deletar/${item.id}" style='border-bottom-left-radius: 9px;'><i class="icone fa-solid fa-trash text-white" ></i></a></div>
                </div>
                    </div>
                    
            </div>`;
        container.append(card); // Adiciona cada card ao container
    });
}



registroProdutos(' ');


$(document).on('click', '.deletarProduto', function (e) {
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
                        text: 'Produto excluído com sucesso.',
                        icon: 'success',
                        confirmButtonText: 'Ok',
                        timer: 800
                    });
                     registroProdutos('');
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

$(document).on('click', '.editarProduto', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link

   
    var id = $(this).data('id'); // ID do item
    var nome = $(this).data('nome'); // Nome do item
    var crit = $(this).data('crit'); // Quantidade do item

    // Exibe o Swal com o formulário
    Swal.fire({
        title: 'Editar Produto',
        html: `<form id="editProduto">
                <input id="produto_id" value='${id}' type="hidden" name="produto_id">
                <div class='d-flex flex-column'><label for="produto_edit">Nome</label>
                <input id="produto_edit" name="produto_edit" type="text" class="swal2-input" value="${nome}" required></div>
                <div class='d-flex flex-column'><label for="unicont_edit">Unidade de Contagem</label>
<select name="unicont_edit"
id="unicont_edit"
class="swal2-input form-control" >
                  <option value="UNID">Unidade</option>
                  <option value="FRC">Frasco</option>
                  <option value="AMP">Ampola</option>
                  <option value="BIS">Bisnaga</option>
               
                </select></div>
                <div class='d-flex flex-column'> <label for="tipos_edit">Tipo</label>
                <select name="tipos_edit[]"
                id="tipos_edit"
                class="swal2-input"
                multiple="multiple" >
                    ${tiposArray.map(item => `<option value='${item.id}'>${item.nome}</option>`).join('')}
                </select></div>
                <div class='d-flex flex-column'> <label for="crit_edit">CMM</label>
                <input name="crit_edit" id="crit_edit" type="number" class="swal2-input" value="${crit}" required></div>    
            </form>`,
        showCancelButton: true,
        confirmButtonText: 'Atualizar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'w-50' // Adiciona uma classe personalizada ao popup
        },
        preConfirm: () => {
            // Faz o serialize do formulário
            return $('#editProduto').serialize();
        },
         didOpen: () => {
             // Inicializa o Select2 quando o modal é exibido
             $('#tipos_edit').select2({
                 dropdownParent: $('.swal2-container') // Isso garante que o Select2 funcione dentro do modal
             });
         }
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = result.value; // Dados do formulário retornados pelo Swal

            // Envia os dados via AJAX para o link fornecido
            $.ajax({
                url: urlsite+'produtos',
                method: 'POST', // Envia como POST, altere se necessário
                data: formData,
                success: function (response) {
                    // Exibe uma mensagem de sucesso
                    Swal.fire({
                        title: 'Sucesso',
                        text: 'Produto editado com Sucesso.',
                        icon: 'success',
                        timer: 2500
                    });

                    // Chame a função para atualizar a lista, se necessário
                    registroProdutos(''); // Atualiza a lista sem recarregar a página
                },
                error: function () {
                    Swal.fire(
                        'Erro!',
                        'Houve um problema ao atualizar a Produto.',
                        'error'
                    );
                }
            });
        }
    });
});


$(document).on('click', '.verMais', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link


    var id = $(this).data('id'); // ID do item
    
     // Quantidade do item
$.ajax({
    url: urlsite + 'produtos',
    method: 'POST', // Envia como POST, altere se necessário
    data: {
        mais: id
    },
    success: function (response) {
         var produto = JSON.parse(response);
          Swal.fire({
              title: produto[0].nome,
              html: `oi`,
            
              customClass: {
                  popup: 'w-50' // Adiciona uma classe personalizada ao popup
              },
              preConfirm: () => {
                  // Faz o serialize do formulário
                  return $('#editProduto').serialize();
              },
              didOpen: () => {
                  // Inicializa o Select2 quando o modal é exibido
                  $('#tipos_edit').select2({
                      dropdownParent: $('.swal2-container') // Isso garante que o Select2 funcione dentro do modal
                  });
              }
          })

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
        registroProdutos('');
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
            registroProdutos('');
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