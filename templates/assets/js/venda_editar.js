const CampoPreco = document.getElementById('preco');
const campoProduto = document.getElementById('option');
const resultados = document.getElementById('resultado');
resultados.style.display = 'none';

CampoPreco.disabled = true;


function adicionarAosCampos(id, texto, preco) {
    
    const resultados = document.getElementById('resultado');
resultados.style.display = 'none';
  campoProduto.value = id;
  campoProduto.textContent  = texto;
  campoPreco.value = preco;
   
 resultados.innerHTML = '';
}





$(document).ready(function(){
    $('#formProduto #pesquisa').keyup(function(){
        var pesquisa = $(this).val();
        var resultado = $('#resultado'); // Cache do elemento para uso posterior

        // Verifica se o comprimento do produto é pelo menos 3 caracteres
        if (pesquisa.length >= 3) {
            $.ajax({
                url: $('#formProduto').attr('data-url-produto'),
                method: 'POST',
                data: {
                    pesquisa: pesquisa
                },
                success: function (data) {
                    resultado.html(data);

                    resultado.css({
                        'display': 'flex',
                        'flex-direction': 'column',
                        'flex-wrap': 'wrap',
                        'cursor': 'pointer'
                    });

                    // Adiciona um evento de clique aos resultados para ocultar o elemento #resultado novamente
                    resultado.find('.seu-classe-de-resultado').click(function() {
                        resultado.css('display', 'none');
                        resultado.html('');
                    });

                    // Adiciona um evento de clique no documento para ocultar o elemento #resultado quando clicar fora dele
                    $(document).click(function(event) {
                        if (!resultado.is(event.target) && resultado.has(event.target).length === 0) {
                            resultado.css('display', 'none');
                            resultado.html('');
                        }
                    });
                }
            });
        } else {
            resultado.css('display', 'none');
            resultado.html('');
        }
    });
});
