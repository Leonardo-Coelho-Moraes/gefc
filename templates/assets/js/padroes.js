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
function registroPadrao(busca) {
    // Realiza a requisição AJAX
    $.ajax({
        url: urlsite + 'padroes', // URL do arquivo PHP que realizará a pesquisa
        method: 'POST', // Envia os dados via POST
        data: {
            pesquisa: busca
        }, // Passa o valor digitado
        success: function (response) {
            var data = JSON.parse(response);
            $('#grid').empty(); // Apaga todos os elementos da div

            if (data.length > 0) { // Verifica se o array de dados não está vazio
                criarGrid(data); // Cria o grid se houver dados
            } else {
                // Se os dados estiverem vazios, limpa a div
                $('#grid').html('<p>Ainda Sem Padrão</p>'); // Mensagem opcional para quando não há dados
            }
        },
        error: function () {
            $('#grid').html('<p>Erro ao buscar Lotes</p>'); // Exibe mensagem de erro
        }
    });
}

 
$('#botaoPesquisar').click(function () {
    let pesquisa = $('#pesquisarLocais').val(); // Obtém o valor selecionado no select



    if (pesquisa.length > 1) { // Verifica se o valor tem mais de 1 caractere
        registroPadrao(pesquisa); // Chama a função com o valor selecionado
    } else {
        registroPadrao(); // Limpa os resultados se o valor for vazio ou menor
    }
});

 
function criarGrid(data) {
    // Limpa o grid anterior, se necessário
    const gridContainer = document.getElementById('grid'); // Altere o ID para o que você estiver usando
    gridContainer.innerHTML = ''; // Remove o conteúdo anterior

    // Cria o Grid.js
    new gridjs.Grid({
        columns: [{
                id: 'id',
                name: 'Id'

            }
            , {
                id: 'produto_id',
                name: '# Produto'

            }, {
                id: 'produto_nome',
                name: 'Produto'

            },
            {
                id: 'padrao',
                name: 'Padrão'
            },
            {
                id: 'acao',
                name: '#',
                formatter: (_, row) => {
                    return gridjs.html(`
                       <div class='d-flex' style='gap:8px;'> <a data-id="${row.cells[0].data}" data-nome="${row.cells[2].data}" data-padrao="${row.cells[3].data}" data-produto_id="${row.cells[1].data}"  class="text-primary editPadrao"><i class="icone fa-solid fa-pen"></i></a>
                        <a class='deletarPadrao text-danger'
                data-link="padrao/deletar/${row.cells[0].data}"><i class="icone fa-solid fa-trash text-danger" ></i></a></div>
                    `);
                },
                width: 'auto'
            }
            // Adicione mais colunas conforme necessário
        ],
        data: data.map(item => [
            item.id,
            item.produto_id,
            item.nome, // Altere para os nomes reais das suas propriedades
            item.padrao,
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
        },
        style: {

            td: {
                'padding': '3px 6px'
            }
        }
    }).render(gridContainer); // Renderiza no container do grid
}

registroPadrao();

$(document).on('click', '.editPadrao', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin
          // Captura os dados da linha
          var registro = $(this).data('id');
          var padrao = $(this).data('padrao');
       
          var nome = $(this).data('nome');
          var produto_id = $(this).data('produto_id');


         
          // Exibe os dados no formulário de edição
          $('#registro_id').val(registro);
          let pesquisa = $('#pesquisarLocais').val();
 $('#local_edit').val(pesquisa);
         
          $('#quantidade_edit').val(padrao);
          var newOption = new Option(nome, produto_id, true, true);
          $('#produto_edit').append(newOption).trigger('change'); // Adiciona e dispara o change

          // Ajusta a barra de pesquisa com o nome
          $('#produto_edit').trigger('select2:select');

          // Seleciona a aba correta
          $('.nav-pills a[href="#tab_2"]').tab('show');
      
});

$(document).on('click', '.deletarPadrao', function (e) {
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

  $('#produto_edit').select2({

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

   $(document).on('click', '#editarPadraoBotao', function (e) {
       e.preventDefault(); // Impede o comportamento padrão do lin
       let formulario = $('#editarPadraoForm').serialize();
       envio(urlsite + 'padroes/', formulario)
        registroPadrao();
       $('#editarPadraoForm')[0].reset()
       $('.nav-pills a[href="#tab_1"]').tab('show')

   });
   $(document).on('click', '#cadastrarPadraoBotao', function (e) {
       e.preventDefault(); // Impede o comportamento padrão do lin
       let formulario = $('#cadastrarPadraoForm').serialize();
       console.log(formulario);
       envio(urlsite + 'padroes/', formulario)
registroPadrao();

       $('#cadastrarPadraoForm')[0].reset()
       $('.nav-pills a[href="#tab_3"]').tab('show')
      

   });






   

});