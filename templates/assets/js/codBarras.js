$(document).ready(function () {
    var urlsite = $('#produtosLink').val();

function registroProdutos(busca) {
    // Obtém o valor do link
    
     $.ajax({
         url: urlsite+'codbarras', // URL do arquivo PHP que realizará a pesquisa
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
             $('#produtos').html('<p>Erro ao buscar Código</p>');
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
              <td>${item.cod_barras}</td>
                  <td style='max-width:400px;text-warp:warp;'>${item.nome}</td>
          
                   
                    <td>
                    <a data-id="${ item.produto_id }"
                       data-nome="${ item.nome }"
                          data-cod="${ item.cod_barras }"
                      
                    
                       class="btn btn-sm btn-primary editarCod">Editar</a>

                        <a class='deletarProduto btn-sm btn btn-danger'
                data-link="codbarras/deletar/${item.id}">Deletar</a></td>

                
                    
            </tr>`;
        container.append(card); // Adiciona cada card ao container
    });
}



registroProdutos(' ');

$(document).on('click', '#enviarCod', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link
    let formulario = $('#formCadCod').serialize();
    $.ajax({
    url: urlsite + 'codbarras',
    method: 'POST', // Envia como POST, altere se necessário
    data: formulario,
    success: function (response) {
        // Exibe uma mensagem de sucesso
        Swal.fire({
            title: 'Sucesso',
            text: 'Códigos de Barras Criado com Sucesso!',
            icon: 'success',
            timer: 2500
        });

        // Chame a função para atualizar a lista, se necessário
        registroProdutos('');
        $('#formCadCod')[0].reset();// Atualiza a lista sem recarregar a página
        $('.select2-selection__rendered').html('Selecione um Produto!');
    },
    error: function () {
        Swal.fire(
            'Erro!',
            'Houve um problema ao Cadastrar o Código.',
            'error'
        );
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
                        text: 'Cod excluído com sucesso.',
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





 



   

});