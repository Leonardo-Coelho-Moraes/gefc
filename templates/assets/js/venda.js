$(document).ready(function() {
  console.log('jQuery está funcionando!');
});
const buscaInput = document.getElementById('busca');

  
$(document).ready(function() {
  $.ajax({
    url: 'https://jsonplaceholder.typicode.com/posts/1',
    method: 'GET',
    success: function(data) {
      console.log('Requisição AJAX bem-sucedida:', data);
    },
    error: function(xhr, status, error) {
      console.error('Erro na requisição AJAX:', error);
    }
  });
});

function adicionarNoCampo(codigo, nome, preco) {
  const campo = document.getElementById('produto');
  campo.dataset.id = codigo;
  campo.dataset.nome = nome;
  campo.dataset.preco = preco;
  campo.value = nome;  // Correção: use assignment instead of calling a function
   $('#resultado').css('display', 'none');
             $('#resultado').html('');
}


$(document).ready(function(){
    $('#formProduto #produto').keyup(function(){
        var produto = $(this).val();
        if (produto !== ""){
            $.ajax({
                url: $('#formProduto').attr('data-url-produto'),
                method: 'POST',
                data:{
                   produto: produto
                },
                success: function (data){
                    $('#resultado').html(data);
                      $('#resultado').css('display', 'flex');
                }
            });
        } else{
            $('#resultado').css('display', 'none');
             $('#resultado').html('');
        }
    });
    });



const listaProdutos = [];
let totalVendas = 0;
$(document).ready(function(){
    $("#formCod #cod").keyup(function(){
        var cod = $(this).val();
        if (cod !== ""){
            $.ajax({
                url: $('#formCod').attr('data-url-codigo'),
                method: 'POST',
                data: {
                    cod: cod
                },
                dataType: 'json',  // Espera receber JSON
                success: function (data) {
                    // Acesso às propriedades do produto
                    var produtoId = data.id;
                    var produtoNome = data.nome;
                    var produtoPreco = data.preco;

                    adicionarProdutoPorBarras(produtoId, produtoNome, produtoPreco);
                    $('#cod').val('');
                }
            });
        } 
    });
});

function adicionarProdutoPorBarras(id, nome, valor) {
 
  const produto = id;
  const nomeProduto = nome;
  const preco = valor;
  const quantidade = 1;

  if (!produto || isNaN(quantidade) || quantidade <= 0) {
    alert('Por favor, selecione um produto e insira uma quantidade válida.');
    return;
  }

  // Verifica se o produto já existe na lista
  const existingItem = listaProdutos.find(item => item.produto === produto);
  if (existingItem) {
    // Atualiza a quantidade do produto existente
    existingItem.quantidade += quantidade;
  } else {
    // Adiciona o novo item à lista de produtos
    const item = { produto, quantidade, nomeProduto, preco };
    listaProdutos.push(item);
  }

  // Atualiza a lista de produtos exibida na página
  atualizarListaProdutos();
}
function adicionarProduto() {
  const produtoInput = document.getElementById('produto');

  const produto = produtoInput.dataset.id;
  const nomeProduto = produtoInput.dataset.nome;
  const preco = produtoInput.dataset.preco;
  const quantidade = parseInt(document.getElementById('quantidade').value);

  if (!produto || isNaN(quantidade) || quantidade <= 0) {
    alert('Por favor, insira um produto e uma quantidade válida.');
    return;
  }

  // Verifica se o produto já existe na lista
  const existingItem = listaProdutos.find(item => item.produto === produto);
  if (existingItem) {
    // Atualiza a quantidade do produto existente
    existingItem.quantidade += quantidade;
  } else {
    // Adiciona o novo item à lista de produtos
    const item = { produto, quantidade, nomeProduto, preco };
    listaProdutos.push(item);
  }

  // Atualiza a lista de produtos exibida na página
  atualizarListaProdutos();
}



function atualizarListaProdutos() {
    const listaProdutosElement = document.getElementById('listaProdutos');
    listaProdutosElement.innerHTML = '';

    let valorTotalVenda = 0;

    listaProdutos.forEach((item, index) => {
        const divItem = document.createElement('div');
        divItem.classList.add('mb-2', 'flex', 'justify-between', 'items-center');

        const selectProduto = document.createElement('select');
        selectProduto.classList.add('bg-gray-50', 'border', 'border-gray-300', 'text-gray-900', 'text-sm', 'rounded-lg', 'focus:ring-blue-500', 'focus:border-blue-500', 'block', 'w-9/12', 'p-1', 'px-2', 'dark:bg-gray-700', 'dark:border-gray-600', 'dark:placeholder-gray-400', 'dark:text-white', 'dark:focus:ring-blue-500', 'dark:focus:border-blue-500');
        selectProduto.name = `produto${index + 1}`;

        const optionProduto = document.createElement('option');
        optionProduto.value = item.produto;
        optionProduto.textContent = item.nomeProduto;

        selectProduto.appendChild(optionProduto);

        const quantidadeInput = document.createElement('input');
        quantidadeInput.type = 'number';
        quantidadeInput.value = item.quantidade;
        quantidadeInput.min = 1;
        quantidadeInput.classList.add('bg-gray-50', 'border', 'border-gray-300', 'text-gray-900', 'text-sm', 'rounded-lg', 'focus:ring-blue-500', 'focus:border-blue-500', 'block', 'w-1/12', 'p-1', 'px-2', 'dark:bg-gray-700', 'dark:border-gray-600', 'dark:placeholder-gray-400', 'dark:text-white', 'dark:focus:ring-blue-500', 'dark:focus:border-blue-500');
        quantidadeInput.name = `quantidade${index + 1}`;

        const precoInput = document.createElement('input');
        precoInput.type = 'text';
        precoInput.value = (item.preco * item.quantidade).toFixed(2) + 'R$';
        precoInput.readOnly = true;
        precoInput.classList.add('bg-gray-50', 'border', 'border-gray-300', 'text-gray-900', 'text-sm', 'rounded-lg', 'focus:ring-blue-500', 'focus:border-blue-500', 'block', 'w-1/12', 'p-1', 'px-2', 'dark:bg-gray-700', 'dark:border-gray-600', 'dark:placeholder-gray-400', 'dark:text-white', 'dark:focus:ring-blue-500', 'dark:focus:border-blue-500');

        divItem.appendChild(selectProduto);
        divItem.appendChild(precoInput);
        divItem.appendChild(quantidadeInput);

        listaProdutosElement.appendChild(divItem);
    });

    // Adicionar um elemento para mostrar o valor total da venda
    const valorTotalVendaElement = document.getElementById('valorTotalVenda');
const descontoInput = document.getElementById('desconto');

    // Adicionar event listener para campos de quantidade
    const quantidadeInputs = document.querySelectorAll('input[type="number"]');
    quantidadeInputs.forEach(input => {
        input.addEventListener('input', () => {
            valorTotalVenda = calcularValorTotalVenda(parseFloat(descontoInput.value) || 0);
            valorTotalVendaElement.innerText = `Valor Total da Venda: ${valorTotalVenda.toFixed(2)}R$ (Desconto: ${(parseFloat(descontoInput.value) || 0).toFixed(2)}R$)`;
        });
    });

    descontoInput.addEventListener('input', () => {
        const desconto = parseFloat(descontoInput.value) || 0;
        valorTotalVenda = calcularValorTotalVenda(desconto);
        valorTotalVendaElement.innerText = `Valor Total da Venda: ${valorTotalVenda.toFixed(2)}R$ (Desconto: ${desconto.toFixed(2)}R$)`;
    });

    // Função para calcular o valor total da venda
    function calcularValorTotalVenda(desconto) {
        let total = 0;
        listaProdutos.forEach((item, index) => {
            const quantidade = parseInt(document.querySelector(`input[name="quantidade${index + 1}"]`).value, 10);
            const precoProduto = item.preco;
            const precoTotalProduto = precoProduto * quantidade;
            total += precoTotalProduto;
        });

        // Aplica o desconto
        total -= desconto;

        return total < 0 ? 0 : total;
    }

    valorTotalVenda = calcularValorTotalVenda(0);
    valorTotalVendaElement.innerText = `Valor Total da Venda: ${valorTotalVenda.toFixed(2)}R$ (Desconto: 0.00R$)`;
}

// Chamada inicial para configurar o valor total da venda
atualizarListaProdutos();
function enviarFormulario() {
    const formulario = document.getElementById('listaProdutos');
    if (formulario) {
        // Obtém o valor do desconto
        const descontoInput = document.getElementById('desconto');
        const desconto = parseFloat(descontoInput.value) || 0;

        // Cria um elemento de input para o desconto e adiciona ao formulário
        const descontoFormInput = document.createElement('input');
        descontoFormInput.type = 'hidden';
        descontoFormInput.name = 'desconto';
        descontoFormInput.value = desconto;
        formulario.appendChild(descontoFormInput);

        // Submete o formulário
        formulario.submit();
        console.log('sucesso');
    } else {
        console.error('Formulário não encontrado.');
    }
}