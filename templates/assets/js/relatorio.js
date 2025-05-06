$(document).ready(function () {
    var urlsite = $('#produtosLink').val();

let grid;
let gridProdutos;
let gridLotes;
const dataAtual = new Date();
const dia = String(dataAtual.getDate()).padStart(2, '0'); // Adiciona um zero à esquerda se necessário
const mes = String(dataAtual.getMonth() + 1).padStart(2, '0'); // Mês (0-11), então adicionamos 1
const ano = dataAtual.getFullYear(); // Ano completo

const dataFormatada = `${dia}/${mes}/${ano}`;

var tipos = $('#tiposLista');
var tiposArray = tipos.data('tipos');

$(document).on('click', '#enviarEntrada', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin
   
    let formulario = $('#formRelatorioEntradas').serialize();
    $.ajax({
                url: urlsite + 'relatorios/',
                method: 'POST', // Envia como POST, altere se necessário
                data: formulario,
                success: function (response) {
                    var data = JSON.parse(response);
                   if (data.length > 0) { // Verifica se o array de dados não está vazio
                     criarGrid(data); // Cria o grid se houver dados
                   } else {
                       // Se os dados estiverem vazios, limpa a div
                       $('#tabelaEntrada').html('<p>Não há Entradas</p>'); // Mensagem opcional para quando não há dados
                   }
                    


                },
                error: function () {
                    Swal.fire(
                        'Erro!',
                        'Houve um problema.',
                        'error'
                    );
                }
});
 

});
  function criarGrid(data) {
      // Limpa o grid anterior, se necessário
      const gridContainer = document.getElementById('tabelaEntrada'); // Altere o ID para o que você estiver usando
       gridContainer.innerHTML = ''; // Limpa o conteúdo anterior

       // Destruir o grid anterior se ele existir
       if (grid) {
           grid.destroy(); // Remove o grid existente
       }

      // Cria o Grid.js
     grid = new gridjs.Grid({
          columns: [{
                  id: 'cod',
                  name: 'COD'

              },
              {
                  id: 'lote',
                  name: 'Lote'
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
              }

              // Adicione mais colunas conforme necessário
          ],
          data: data.map(item => [
              item.lote_id, // Altere para os nomes reais das suas propriedades
              item.lote,
              item.nome,
              item.quantidade,
              item.fornecedor,
              item.data

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
$(document).on('click', '#imprimirEntrada', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin

    let formulario = $('#formRelatorioEntradas').serialize();
    $.ajax({
        url: urlsite + 'relatorios/',
        method: 'POST', // Envia como POST, altere se necessário
        data: formulario,
        success: function (response) {
            var data = JSON.parse(response);
            if (data.length > 0) { // Verifica se o array de dados não está vazio
                relatorioEntrada(data); // Cria o grid se houver dados
            } else {
                // Se os dados estiverem vazios, limpa a div
                $('#tabelaEntrada').html('<p>Não há Entradas</p>'); // Mensagem opcional para quando não há dados
            }



        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema.',
                'error'
            );
        }
    });


});
function relatorioEntrada(conteudo) {
    let novaJanela = window.open('', '_blank');
    let deEntrada = $('#deEntrada').val();
    let ateEntrada =  $('#ateEntrada').val();
 let registros = 0;
 let total = 0;

 let rows = conteudo.map(registro => {
     registros = registros + 1;
     total = total + registro.quantidade;
     return `
        <tr>
            <td style='border-right:1px solid black;'>${registro.lote_id}</td>
            <td style='border-right:1px solid black;'>${registro.lote}</td>
            <td style='border-right:1px solid black;'>${registro.nome} ${registro.unidade}</td>
            <td style='border-right:1px solid black;'>${registro.quantidade}</td>
            <td style='border-right:1px solid black;'>${registro.fornecedor}</td>
            <td style='border-right:1px solid black;'>${registro.data}</td>
         
        </tr>
    `;
 }).join('');


    novaJanela.document.write(`
        <html>
            <head>
                <title>Resultado da Pesquisa</title>
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
        <th style="border: 1px solid black;padding: .75rem;">Extrato de Entrada</th>
         <th style="border: 1px solid black;padding: .75rem;">Intervalo da Pesquisa:
           ${deEntrada} -
            ${ateEntrada}</th>
        <th style="border: 1px solid black;padding: .75rem;">Data Extração: ${dataFormatada}</th>
       
      </tr>
    </thead>

  </table>
               
                 <table style='border-collapse: collapse;'>
            <thead>
              <tr >
                <th style="border: 1px solid black;padding: .75rem;">Cod</th>
                <th style="border: 1px solid black;padding: .75rem;">Lote</th>
                <th style="border: 1px solid black;padding: .75rem;">Produto</th>
                <th style="border: 1px solid black;padding: .75rem;">Qnt</th>
                <th style="border: 1px solid black;padding: .75rem;">Forncedor</th>
                <th style="border: 1px solid black;padding: .75rem;">Data</th>
              
                
              

              </tr>
            </thead>
            <tbody>
             <tr>
            <td style='border-right:1px solid black;'>#</td>
            <td style='border-right:1px solid black;'></td>
            <td style='border-right:1px solid black;'>Registros: ${registros}</td>
            <td style='border-right:1px solid black;'></td>
            <td style='border-right:1px solid black;'>Total: ${total}</td>
            <td style='border-right:1px solid black;'></td>
         
            </tr>
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


$(document).on('click', '#enviarProduto', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin

    let formularioProdutos = $('#formProdutos').serialize();
    $.ajax({
        url: urlsite + 'relatorios/',
        method: 'POST', // Envia como POST, altere se necessário
        data: formularioProdutos,
        success: function (response) {
            var data = JSON.parse(response);
            if (data.length > 0) { // Verifica se o array de dados não está vazio
                criarGridProdutos(data); // Cria o grid se houver dados
            } else {
                // Se os dados estiverem vazios, limpa a div
                $('#tabelaProdutos').html('<p>Não há Entradas</p>'); // Mensagem opcional para quando não há dados
            }



        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema.',
                'error'
            );
        }
    });


});

function criarGridProdutos(data) {
    // Limpa o grid anterior, se necessário
    const gridContainer = document.getElementById('tabelaProdutos');
    gridContainer.innerHTML = ''; // Limpa o conteúdo anterior

    // Destruir o grid anterior se ele existir
    if (gridProdutos) {
        gridProdutos.destroy(); // Remove o grid existente
    }

    // Cria o Grid.js
    gridProdutos = new gridjs.Grid({
        columns: [{
                id: 'id',
                name: 'ID'
            },
            {
                id: 'nome',
                name: 'Nome'
            },
            {
                id: 'tipo',
                name: 'Tipo'
            },
            {
                id: 'todos',
                name: 'Todos'
            },
            {
                id: 'cmm',
                name: 'CMM'
            }, {
                id: 'cmq',
                name: 'CMQ'
            }, {
                id: 'cms',
                name: 'CMS'
            }
        ],
        data: data.map(item => {
           

            // Retorna os dados mapeados para o grid
            return [
                item.id,
                item.nome,
                item.tipo, // Nome do tipo mapeado
                item.total_quantidade,
                item.qnt_crit,
                 item.qnt_crit / 2,
                  item.qnt_crit / 4
            ];
        }),
        pagination: {
            limit: 30 // Limite de itens por página
        },
        search: true,
        resizable: true,
        sort: true,
        language: {
            search: {
                placeholder: 'Procurar...'
            },
            pagination: {
                previous: 'Anterior',
                next: 'Próximo',
                showing: 'Exibindo',
                results: () => 'resultados'
            }
        },
        style: {
            td: {
                padding: '3px 6px'
            }
        }
    }).render(gridContainer); // Renderiza no container do grid
}

$(document).on('click', '#imprimirProdutos', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin

    let formulario = $('#formProdutos').serialize();
    $.ajax({
        url: urlsite + 'relatorios/',
        method: 'POST', // Envia como POST, altere se necessário
        data: formulario,
        success: function (response) {
            var data = JSON.parse(response);
            if (data.length > 0) { // Verifica se o array de dados não está vazio
                relatorioProdutos(data); // Cria o grid se houver dados
            } else {
                // Se os dados estiverem vazios, limpa a div
                $('#tabelaProdutos').html('<p>Não há Entradas</p>'); // Mensagem opcional para quando não há dados
            }



        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema.',
                'error'
            );
        }
    });


});

function relatorioProdutos(conteudo) {
    let novaJanela = window.open('', '_blank');

   let rows = conteudo.map(registro => {
       // Separar os IDs de tipo
      
       return `
        <tr>
            <td style='border-right:1px solid black;'>${registro.id}</td>
            <td style='border-right:1px solid black;'>${registro.nome} ${registro.unidade}</td>
            <td style='border-right:1px solid black;'>${registro.tipo}</td>
            <td style='border-right:1px solid black;'>${registro.total_quantidade}</td>
            <td style='border-right:1px solid black;'>${registro.qnt_crit}</td>
            <td style='border-right:1px solid black;'>${registro.qnt_crit/2}</td>
            <td style='border-right:1px solid black;'>${registro.qnt_crit/4}</td> <!-- Adiciona o nomeTipo aqui -->
        </tr>
    `;
   }).join('');

    novaJanela.document.write(`
        <html>
            <head>
                <title>Resultado da Pesquisa</title>
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
        <th style="border: 1px solid black;padding: .75rem;">Extrato de Produtos</th>
        <th style="border: 1px solid black;padding: .75rem;">Data Extração: ${dataFormatada}</th>
       
      </tr>
    </thead>

  </table>
               
                 <table style='border-collapse: collapse;'>
            <thead>
              <tr >
                <th style="border: 1px solid black;padding: .75rem;">ID</th>
                <th style="border: 1px solid black;padding: .75rem;">Nome</th>
                <th style="border: 1px solid black;padding: .75rem;">Tipo</th>
                <th style="border: 1px solid black;padding: .75rem;">Total</th>
                <th style="border: 1px solid black;padding: .75rem;">CMM</th>
                <th style="border: 1px solid black;padding: .75rem;">CMQ</th>
              <th style="border: 1px solid black;padding: .75rem;">CMS</th>
                
              

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

$(document).on('click', '#enviarLotes', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin

    let formulario = $('#formLotes').serialize();
    $.ajax({
        url: urlsite + 'relatorios/',
        method: 'POST', // Envia como POST, altere se necessário
        data: formulario,
        success: function (response) {
            var data = JSON.parse(response);
            if (data.length > 0) { // Verifica se o array de dados não está vazio
                criarGridLotes(data); // Cria o grid se houver dados
            } else {
                // Se os dados estiverem vazios, limpa a div
                $('#tabelaLotes').html('<p>Não há Entradas</p>'); // Mensagem opcional para quando não há dados
            }



        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema.',
                'error'
            );
        }
    });


});

function criarGridLotes(data) {
    // Limpa o grid anterior, se necessário
    const gridContainer = document.getElementById('tabelaLotes');
    gridContainer.innerHTML = ''; // Limpa o conteúdo anterior

    // Destruir o grid anterior se ele existir
    if (gridLotes) {
        gridLotes.destroy(); // Remove o grid existente
    }

    // Cria o Grid.js
    gridLotes = new gridjs.Grid({
        columns: [{
                id: 'id',
                name: 'ID'
            },
            {
                id: 'lote',
                name: 'Lote'
            },
            {
                id: 'produto',
                name: 'Produto'
            },
            {
                id: 'qnt',
                name: 'QNT'
            },
            {
                id: 'fornecedor',
                name: 'Fornecedor'
            }, {
                id: 'validade',
                name: 'Validade'
            }
             
        ],
        data: data.map(item => {
            return [
                item.id,
                item.lote,
                item.nome, // Nome do tipo mapeado
                item.quantidade,
                item.fornecedor,
                item.vencimento
            ];
        }),
        pagination: {
            limit: 30 // Limite de itens por página
        },
        search: true,
        resizable: true,
        sort: true,
        language: {
            search: {
                placeholder: 'Procurar...'
            },
            pagination: {
                previous: 'Anterior',
                next: 'Próximo',
                showing: 'Exibindo',
                results: () => 'resultados'
            }
        },
        style: {
            td: {
                padding: '3px 6px'
            }
        }
    }).render(gridContainer); // Renderiza no container do grid
}

$(document).on('click', '#imprimirLotes', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do lin

    let formularioLotes = $('#formLotes').serialize();
    $.ajax({
        url: urlsite + 'relatorios/',
        method: 'POST', // Envia como POST, altere se necessário
        data: formularioLotes,
        success: function (response) {
            var data = JSON.parse(response);
            if (data.length > 0) { // Verifica se o array de dados não está vazio
                relatorioLotes(data); // Cria o grid se houver dados
            } else {
                // Se os dados estiverem vazios, limpa a div
                $('#tabelaLotes').html('<p>Não há Entradas</p>'); // Mensagem opcional para quando não há dados
            }



        },
        error: function () {
            Swal.fire(
                'Erro!',
                'Houve um problema.',
                'error'
            );
        }
    });


});

function relatorioLotes(conteudo) {
    let novaJanela = window.open('', '_blank');
    let registros = 0;
    let total = 0;

    let rows = conteudo.map(registro => {
        registros = registros + 1;
        total = total + registro.quantidade;
        return `
        <tr>
            <td style='border-right:1px solid black;'>${registro.id}</td>
            <td style='border-right:1px solid black;'>${registro.lote}</td>
            <td style='border-right:1px solid black;'>${registro.nome} ${registro.unidade}</td>
            <td style='border-right:1px solid black;'>${registro.quantidade}</td>
            <td style='border-right:1px solid black;'>${registro.fornecedor}</td>
            <td style='border-right:1px solid black;'>${registro.vencimento}</td>
         
        </tr>
    `;
    }).join('');

    novaJanela.document.write(`
        <html>
            <head>
                <title>Resultado da Pesquisa</title>
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
        <th style="border: 1px solid black;padding: .75rem;">Extrato de Lotes</th>
        <th style="border: 1px solid black;padding: .75rem;">Data Extração: ${dataFormatada}</th>
       
      </tr>
    </thead>

  </table>
               
                 <table style='border-collapse: collapse;'>
            <thead>
              <tr >
                <th style="border: 1px solid black;padding: .75rem;">ID</th>
                <th style="border: 1px solid black;padding: .75rem;">Lote</th>
                <th style="border: 1px solid black;padding: .75rem;">Nome</th>
                <th style="border: 1px solid black;padding: .75rem;">QNT</th>
                <th style="border: 1px solid black;padding: .75rem;">Fonecedor</th>
                <th style="border: 1px solid black;padding: .75rem;">Val</th>
             
                
              

              </tr>
            </thead>
            <tbody>
              <tr>
            <td style='border-right:1px solid black;'>#</td>
            <td style='border-right:1px solid black;'>Registros: ${registros}</td>
            <td style='border-right:1px solid black;'>Total: ${total}</td>
            <td style='border-right:1px solid black;'></td>
            <td style='border-right:1px solid black;'></td>
            <td style='border-right:1px solid black;'></td>
         
            </tr>
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