$(document).ready(function () {
    var urlsite = $('#produtosLink').val();

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
            
                  
                 
                  

        const card = `

        <tr>
              <td>${item.id}</td>
                  <td style='max-width:400px;text-warp:warp;'>${item.nome}</td>
             <td>${item.tipo}</td>
                    <td>${item.total_quantidade}</td>
                   
                    <td><a data-id="${item.id}" class="btn btn-sm btn-success verMaisConteudo">Ver Mais</a>
                    <a data-id="${ item.id }"
                       data-nome="${ item.nome }"
                       data-crit="${item.qnt_crit }"  
                       data-qnt1="${item.total_quantidade }"
                    
                         data-preco="${item.preco }"
                          data-qnt_for="${item.qnt_for }"
                           data-qnt_cema="${item.qnt_cema }" data-tipo="${item.tipo }"
                       class="btn btn-sm btn-primary editarProduto">Editar</a>

                        <a class='deletarProduto btn-sm btn btn-danger'
                data-link="produtos/deletar/${item.id}">Deletar</a></td>

                
                    
            </tr>


            <div class="col-md-4"
            >
                <div class="card" style='width:400px;height:150px;position:relative;'>
                
                    <div class="card-body d-flex" >
                    <i  style='position:absolute;font-size:100px;z-index:4;opacity:0.2;bottom:0;left:0;' class="fa-solid fa-tablets"></i>
                    <div style='z-index:10;'> <p class="text-sm" >${item.id} - ${item.nome} Qnt: ${item.total_quantidade}</p>
                        <p class="text-sm" > </p>

                        <i data-id='${item.id}'  class="verMaisConteudo text-xl fa-solid fa-eye"></i>
</div>
                       
                       <div class='d-flex'
                       style='flex-direction: column;top: 0;position: absolute;width: 22px;height: 100%;right: 0;justify-content: space-evenly;padding-rigth:2px;padding-left:2px;' >
                        </div>
                </div>
                    </div>
                    
            </div>`;
        container.append(card); // Adiciona cada card ao container
    });
}



registroProdutos(' ');

 $('.criarProduto').click(function (e) {
    
      e.preventDefault();
        Swal.fire({
        title: 'Cadastrar Produto',
        html: `<form id="criarProdutoForm">
                
                <div class='d-flex flex-column'><label for="produto">Nome</label>
                <input id="produto" name="produto" type="text" class="swal2-input" required></div>
                <div class='d-flex flex-column'><label for="unicont">Unidade de Contagem</label>
<select name="unicont"
id="unicont"
class="swal2-input form-control" >
                  <option value="UNID">Unidade</option>
                  <option value="FRC">Frasco</option>
                  <option value="AMP">Ampola</option>
                  <option value="BIS">Bisnaga</option>
               
                </select></div>
                <div class='d-flex flex-column'> <label for="tipos">Tipo</label>
                <select name="tipos"
                id="tipos"
                class="swal2-input"
                >
                    <option value="Medicamento Básicos">Medicamento Básicos</option>
                  <option value="Controlado">Controlado</option>
                  <option value="Químico Cirúrgico">Químico Cirúrgico</option>
                  <option value="Laboratório">Laboratório</option>
                </select></div>
                <div class='d-flex flex-column'> <label for="crit_edit">CMM</label>
                <input name="crit" id="crit" type="number" class="swal2-input" required></div>   

                <div class='d-flex flex-column'> <label for="qnt1">Total QNT</label>
                <input name="qnt1" id="qnt1" type="number" class="swal2-input" required></div>  

          
                <div class='d-flex flex-column'> <label for="preco">Preço</label>
                <input name="preco" step='0.01' id="preco" type="number" class="swal2-input" required></div>  

                <div class='d-flex flex-column'> <label for="qnt_for">Qnt Fornecedor</label>
                <input name="qnt_for" id="qnt_for" type="number" class="swal2-input" required></div>  

                <div class='d-flex flex-column'> <label for="qnt_cema">Qnt CEMA</label>
                <input name="qnt_cema" id="qnt_cema" type="number" class="swal2-input" required></div>  
                
            </form>`,
        showCancelButton: true,
        confirmButtonText: 'Cadastrar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'w-50' // Adiciona uma classe personalizada ao popup
        },
        preConfirm: () => {
            // Faz o serialize do formulário
            return $('#criarProdutoForm').serialize();
        },
        didOpen: () => {
            // Inicializa o Select2 quando o modal é exibido
            $('#tipos').select2({
                dropdownParent: $('.swal2-container') // Isso garante que o Select2 funcione dentro do modal
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = result.value; // Dados do formulário retornados pelo Swal

            // Envia os dados via AJAX para o link fornecido
            $.ajax({
                url: urlsite + 'produtos/produto_cadastrar',
                method: 'POST', // Envia como POST, altere se necessário
                data: formData,
                success: function (response) {
                    // Exibe uma mensagem de sucesso
                    Swal.fire({
                        title: 'Sucesso',
                        text: `${response} cadastrado com sucesso!`,
                        icon: 'success'
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

  $('.criarTipo').click(function (e) {
     
      e.preventDefault();
      Swal.fire({
          title: 'Cadastrar Tipo Produto',
          html: `<form id="criarTipoForm">
                <div class='d-flex flex-column'><label for="tipoProduto">Nome</label>
                <input id="tipoProduto" name="tipoProduto" type="text" class="swal2-input" required></div>
              
            </form>`,
          showCancelButton: true,
          confirmButtonText: 'Cadastrar',
          cancelButtonText: 'Cancelar',
          customClass: {
              popup: 'w-50' // Adiciona uma classe personalizada ao popup
          },
          preConfirm: () => {
              // Faz o serialize do formulário
              return $('#criarTipoForm').serialize();
          }
      }).then((result) => {
          if (result.isConfirmed) {
              var formData = result.value; // Dados do formulário retornados pelo Swal

              // Envia os dados via AJAX para o link fornecido
              $.ajax({
                  url: urlsite + 'criarTipo',
                  method: 'POST', // Envia como POST, altere se necessário
                  data: formData,
                  success: function (response) {
                      // Exibe uma mensagem de sucesso
                      Swal.fire({
                          title: 'Sucesso',
                          text: `${response} cadastrado com sucesso!`,
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
    var qnt1 = $(this).data('qnt1');
    var preco = $(this).data('preco');
    var qnt_for = $(this).data('qnt_for');
    var qnt_cema = $(this).data('qnt_cema');
    var tipo = $(this).data('tipo');


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
                <select name="tipos_edit"
                id="tipos_edit"
                class="swal2-input form-control"
               >
                <option value="${tipo}">${tipo}</option>
                   <option value="Medicamento Básicos">Medicamento Básicos</option>
                  <option value="Controlado">Controlado</option>
                  <option value="Químico Cirúrgico">Químico Cirúrgico</option>
                  <option value="Laboratório">Laboratório</option>
                </select></div>
                <div class='d-flex flex-column'> <label for="crit_edit">CMM</label>
                <input name="crit_edit" id="crit_edit" type="number" class="swal2-input" value="${crit}" required></div>    

                 <div class='d-flex flex-column'> <label for="qnt1_edit">Total QNT</label>
                <input name="qnt1_edit" id="qnt1_edit" type="number" class="swal2-input" value="${qnt1}" required></div>  

                <div class='d-flex flex-column'> <label for="preco_edit">Preço</label>
                <input name="preco_edit" step='0.01' id="preco_edit" type="number" class="swal2-input"  value="${preco}" required></div>  

                <div class='d-flex flex-column'> <label for="qnt_for_edit">Qnt Fornecedor</label>
                <input name="qnt_for_edit" id="qnt_for_edit" type="number" class="swal2-input"  value="${qnt_for}" required></div>  

                <div class='d-flex flex-column'> <label for="qnt_cema_edit">Qnt CEMA</label>
                <input name="qnt_cema_edit" id="qnt_cema_edit" type="number" class="swal2-input"  value="${qnt_cema}" required></div>  
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
$(document).on('click', '.verMaisConteudo', function (e) {
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
              html: `<table class="text-sm table table-striped table-bordered table-hover">
              <thead>
              <tr>
                <th>COD</th>
              <th>Lote</th>
              <th>Quantidade</th>
              <th>Fornecedor</th>
              <th>Vencimento</th>
              </tr>
               </thead>
               <tbody>
               ${produto.map(item => `
            <tr>
               <td>${item.cod}</td>
               <td>${item.lote}</td>
               <td>${item.quantidade}</td>
               <td>${item.fornecedor}</td>
               <td>${item.vencimento}</td>
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


 



   

});