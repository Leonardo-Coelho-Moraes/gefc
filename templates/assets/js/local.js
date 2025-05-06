$(document).ready(function () {
    var urlsite = $('#entradaLink').val();
  



$(document).on('click', '#pedir', function (e) {
    e.preventDefault(); // Impede o comportamento padrão do link

   
$.ajax({
    url: urlsite + 'local', // URL do arquivo PHP que realizará a pesquisa
    method: 'POST', // Envia os dados via POST
    data: {
        pedir: 0
    }, // Passa o valor digitado
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
        $('#entradas').html('<p>Erro ao buscar dados</p>');
    }
});
   
   
});

});