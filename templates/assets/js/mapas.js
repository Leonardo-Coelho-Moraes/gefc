$(document).ready(function () {
    var urlsite = $('#entradaLink').val();
  let Hoje = new Date();
  let ano = Hoje.getFullYear();
  let mes = String(Hoje.getMonth() + 1).padStart(2, '0'); // Janeiro é 0, então adicionamos 1
  let dia = String(Hoje.getDate()).padStart(2, '0');

  let dataAtual = `${ano}-${mes}-${dia}`;
function registroMapas(busca) {
    // Obtém o valor do link
    
     $.ajax({
         url: urlsite+'mapas', // URL do arquivo PHP que realizará a pesquisa
         method: 'POST', // Envia os dados via POST
         data: {
             mapas: busca
         }, // Passa o valor digitado
         success: function (response) {
            var data = JSON.parse(response);
             criarGrid(data) ;// Exibe os resultados na div
         },
         error: function () {
             $('#mapas').html('<p>Erro ao buscar dados</p>');
         }
     });
}
$('#pesquisaMapa').keyup(function () {
    $('#mapas').html('');
    var query = $(this).val(); // Obtém o valor digitado
    if (query.length > 3) { // Executa apenas se houver mais de um caractere digitado
        registroMapas(query);
    } else {
        registroMapas(dataAtual); // Limpa os resultados se o campo estiver vazio
    }
});
registroMapas(dataAtual);
function pesquisarLotes(id) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: urlsite + 'mapas', // URL do arquivo PHP que realizará a pesquisa
            method: 'POST', // Envia os dados via POST
            data: {
                pesquisarLote: id
            }, // Passa o valor digitado
            success: function (response) {
                var data = JSON.parse(response);
                resolve(data); // Retorna os dados resolvendo a promise
            },
            error: function () {
                reject('Erro ao buscar dados'); // Rejeita a promise em caso de erro
            }
        });
    });
}
let grid;
function criarGrid(data) {
    // Limpa o grid anterior, se necessário
    const gridContainer = document.getElementById('mapas'); // Altere o ID para o que você estiver usando
    
   if (grid) {
       grid.destroy(); // Destrói a instância existente
   }
    // Cria o Grid.js
   grid = new gridjs.Grid({
        columns: [
            {
                id: 'id',
                name: 'Id'

            }, {
                id: 'local',
                name: 'Local'
               
            },
            {
                id: 'data',
                name: 'Data'
            },
             {
                 id: 'acao',
                 name: '#',
                 formatter: (_, row) => {
                     const atendido = row.cells[5].data; // Pega o valor da coluna "atendido"
                     if (atendido == 1) {
                         // Se atendido for 1, exibe o link para "verMapa"
                         return gridjs.html(`
                            <div style="display:flex; gap:8px;">
                                <a class="btn btn-sm btn-primary verMapa"
                                data-id='${row.cells[0].data}'
                                data-local='${row.cells[1].data}' >
                                    <i class="fa-solid fa-eye"></i> Ver Mapa
                                </a>
                            </div>
                        `);
                     } else {
                         // Se atendido for 0, exibe o link para "atenderMapa"
                         return gridjs.html(`
                            <div style="display:flex; gap:8px;">
                                <a class="btn btn-sm btn-primary atenderMapa" data-local='${row.cells[4].data}' data-id='${row.cells[0].data}'>
                                    <i class="fa-solid fa-user-check"></i> Atender Mapa
                                </a>
                            </div>
                        `);
                     }
                 }
             }, {
                 id: 'oculta', // Nova coluna oculta
                 name: 'Oculta',
                 hidden: true // Essa opção oculta a coluna diretamente (você também pode usar CSS)
             },
             {
                 id: 'atendido', // Nova coluna oculta
                 name: 'atendido',
                 hidden: true // Essa opção oculta a coluna diretamente (você também pode usar CSS)
             }
            // Adicione mais colunas conforme necessário
        ],
        data: data.map(item => [
            item.id,
            item.nome, // Altere para os nomes reais das suas propriedades
            item.dataPedido,
            'ação',
            item.local_id,
            item.atendido
            
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



$(document).on('click', '#pedir', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link

   
$.ajax({
    url: urlsite + 'mapas', // URL do arquivo PHP que realizará a pesquisa
    method: 'POST', // Envia os dados via POST
    data: {
        pedir: 10
    }, // Passa o valor digitado
    success: function (response) {
           // Exibe uma mensagem de sucesso
           Swal.fire({
               title: 'Sucesso',
               text: 'Pedido Feito com Sucesso',
               icon: 'success',
               timer: 2500
           });
    },
    error: function () {
        $('#entradas').html('<p>Erro ao buscar dados</p>');
    }
});
   
   
});
$(document).on('click', '.atenderMapa', async function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link

    var id = $(this).data('id');
     var local = $(this).data('local');
    let container = $('#formAtenderMapa');
  

    try {
        const response = await $.ajax({
            url: urlsite + 'mapas', // URL do arquivo PHP que realizará a pesquisa
            method: 'POST', // Envia os dados via POST
            data: {
                mapa: id
            }
        });

        let dados = JSON.parse(response);
        let contador = 0;

        for (const item of dados) {
            let lotes = await pesquisarLotes(item.produto_id); // Espera os lotes serem carregados
            let sugestao = 0;

            if (item.padrao >= item.estoque_atual) {
                sugestao = item.padrao - item.estoque_atual;
            }

            contador++;

            // Gerar as opções para o select com os lotes
            let loteOptions = '';
            lotes.forEach(lote => {
                loteOptions += `<option value="${lote.id}">Cod: ${lote.id} ${lote.nome}, Qnt: ${lote.quantidade} ${lote.fornecedor} ${lote.vencimento}</option>`;
            });

            // Adicionar o mapa com o select preenchido com os lotes
            const mapa = `
                <div class="form-group d-flex align-items-center border-bottom pb-2 mb-2">
                    <input type="hidden" name='registro${contador}' value='${item.id}' readonly>
                    <select class="form-control" name='lote${contador}'>
                        ${loteOptions}
                    </select>
                    <input type='text' value='${item.estoque_atual}' class='form-control' style='width: min-content;' readonly>
                    <input type='text' style='width: min-content;' value='${item.padrao}' class='form-control ' readonly>
                    <input type="number" class="form-control mx-2" name='quantidade${contador}' value='${sugestao}'>
                </div>
            `;
            container.append(mapa);
        }
        let campos = `<input type="hidden" name='mapaNum' id='mapaNum' value='${id}' readonly>
        <input type="hidden" name='localMapa' id='localMapa' value='${local}' readonly>`
        container.append(campos);
        $('.nav-pills a[href="#tab_2"]').tab('show');
    } catch (error) {
        $('#tab_2').html('<p>Erro ao buscar dados</p>');
    }
});


$(document).on('click', '.verMapa', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link


    var id = $(this).data('id'); // ID do item
var local = $(this).data('local');
    // Quantidade do item
    $.ajax({
        url: urlsite + 'mapas',
        method: 'POST', // Envia como POST, altere se necessário
        data: {
            mapa: id
        },
        success: function (response) {
            var mapa = JSON.parse(response);
            Swal.fire({
                title: id + ' ' + local + ` <button class="btn btn-primary" data-local='${local}' data-id='${id}' id="imprimirMapa"><i class="fa-solid fa-print"></i></button>`,
                html: `<table class="text-sm table table-striped table-bordered table-hover">
              <thead>
              <tr>
                <th>id</th>
              <th>Produto</th>
              <th>Lote</th>
              <th>Padrão</th>
              <th>Estoque</th>
              <th>Atentido</th>
              <th>Data</th>
              </tr>
               </thead>
               <tbody>
               ${mapa.map(item => `
            <tr>
               <td>${item.id}</td>
               <td>${item.nome}</td>
               <td>${item.lote_id}</td>
               <td>${item.padrao}</td>
               <td>${item.estoque_atual}</td>
               <td>${item.quantidade}</td>
               <td>${item.data_atendido}</td>
               </tr>
               `).join('')
               }
               </tbody>
              </table>`,
                customClass: {
                    popup: 'w-50' // Adiciona uma classe personalizada ao popup
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

$(document).on('click', '#imprimirMapa', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin

    var id = $(this).data('id'); // ID do item
  
    // Quantidade do item
    $.ajax({
        url: urlsite + 'mapas',
        method: 'POST', // Envia como POST, altere se necessário
        data: {
            mapa: id
        },
        success: function (response) {
            var mapa = JSON.parse(response);
         relatorioMapa(mapa)

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

function relatorioMapa(conteudo) {
    let novaJanela = window.open('', '_blank');

    let rows = conteudo.map(registro => {
    
        return `
        <tr>
            <td style='border-right:1px solid black;'>${registro.id}</td>
            <td style='border-right:1px solid black;'>${registro.lote_id}</td>
            <td style='border-right:1px solid black;'>${registro.nome}</td>
            <td style='border-right:1px solid black;'>${registro.padrao}</td>
            <td style='border-right:1px solid black;'>${registro.estoque_atual}</td>
            <td style='border-right:1px solid black;'>${registro.quantidade}</td>
             <td style='border-right:1px solid black;'>${registro.data_atendido}</td>
         
        </tr>
    `;
    }).join('');


    novaJanela.document.write(`
        <html>
            <head>
                <title>Mapa</title>
                     <link rel="stylesheet" href="${urlsite}templates/assets/css/adminlte.min.css">
                   <style>

  * {
    font-family: "Inter", sans-serif;
  }
</style>
            </head>
            <body>
                <table style="border: 1px solid black; width: 100%; border-collapse: collapse;" >
  <thead>
    <tr>
      <th scope="col" style="border: 1px solid black; text-align: center;padding: .75rem;">
        <img src="${urlsite}templates/assets/img/cafmini.jpg" style="width: 200px; display: block; margin: auto;">
      </th>
      <th scope="col" style="border: 1px solid black;padding: .75rem;">
        <h3 style="text-align:center;">Prefeitura Municipal de Coari</h3>
        <h4 style="text-align:center;">Secretaria Municipal de Saúde</h4>
        <h4 style="text-align:center;">Central de Abastecimento Farmacêutico</h4>
      </th>
      <th scope="col" style="border: 1px solid black; text-align: center;padding: .75rem;">
        <img src="${urlsite}templates/assets/img/coari.png" style="width: 200px; display: block; margin: auto;">
      </th>
    </tr>
  </thead>
</table>

<table style="border: 1px solid black; margin-bottom: 50px;margin-top:20px;border-collapse: collapse;width:100%;" >
    <thead>
      <tr>
        <th style="border: 1px solid black;padding: .75rem; text:center;"> ${conteudo[0].localNome} - ${dataAtual}</th>
       
       
      </tr>
    </thead>

  </table>
               
                 <table style='border-collapse: collapse;'>
            <thead>
              <tr >
                <th style="border: 1px solid black;padding: .75rem;">Id</th>
                <th style="border: 1px solid black;padding: .75rem;">Produto</th>
                <th style="border: 1px solid black;padding: .75rem;">Lote</th>
                <th style="border: 1px solid black;padding: .75rem;">Padrão</th>
                <th style="border: 1px solid black;padding: .75rem;">Estoque</th>
                <th style="border: 1px solid black;padding: .75rem;">Atendido</th>
                <th style="border: 1px solid black;padding: .75rem;">Data</th>
              </tr>
            </thead>
            <tbody>
            
                ${rows}
            </tbody>
          </table>
                <script>
                    window.onload = function() {
                        window.print();
                    };
                </script>
            </body>
        </html>
    `);

    novaJanela.document.close(); // Fecha o documento para renderizar
}
});