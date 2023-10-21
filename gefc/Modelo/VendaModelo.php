<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
namespace gefc\Modelo;
/**
 * Description of PostLocal
 *
 * @author Leonardo
 */
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Conexao;
use gefc\Modelo\Inserir;
use gefc\Modelo\Atualizar;
class VendaModelo {
    
    public function buscaQuantidade(string $nome): bool|object{
       
        $query= "SELECT * from produtos WHERE nome = {$nome}";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch();
        return $resultado;
    }
    
    public function venda(array $dados): void {
    // Verifique se os dados foram fornecidos corretamente
    if (empty($dados)) {
        $mensagem = (new Mensagem)->erro('Envie Algo')->flash();
           Helpers::redirecionar('venda/adicionar');
        return;
    }

    $i = 1;
    while (isset($dados['produto' . $i]) && isset($dados['quantidade' . $i])) {
        $produtoId = intval($dados['produto' . $i]);
        $quantidade = intval($dados['quantidade' . $i]);

        // Verifica se a quantidade é válida
        if ($quantidade > 0) {
            // Atualiza o banco de dados para o produto correspondente
            $dadosArray = array('quantidade' => $quantidade);
            (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque - ?", $dadosArray, $produtoId);
        }

        $i++;
    }
}

    public function quantidadeVenda(array $dados): void {
    // Verifique se os dados foram fornecidos corretamente
    if (empty($dados)) {
        $mensagem = (new Mensagem)->erro('Envie Algo')->flash();
           Helpers::redirecionar('venda/adicionar');
        return;
    }

    $i = 1;
    while (isset($dados['produto' . $i]) && isset($dados['quantidade' . $i])) {
        $produtoId = intval($dados['produto' . $i]);
        $quantidade = intval($dados['quantidade' . $i]);

        // Verifica se a quantidade é válida
        if ($quantidade > 0) {
            // Atualiza o banco de dados para o produto correspondente
            $dadosArray = array('quantidade' => $quantidade);
            (new Atualizar())->atualizar('produtos', "quantidade_saida = quantidade_saida + ?", $dadosArray, $produtoId);
        }

        $i++;
    }
}

public function vendaRegistro(array $dados): void {
    $nomeVenda = 'venda' . uniqid();
    $user = UsuarioControlador::usuario()->nome;

    // Inicializa o valor total da venda
    $valorTotalVenda = 0;

    // Itera sobre os dados para extrair os produtos e quantidades
    $i = 1;

    while (isset($dados['produto' . $i]) && isset($dados['quantidade' . $i])) {
        $produto = $dados['produto' . $i];
        $quantidade = intval($dados['quantidade' . $i]);

        // Busca o preço do produto no banco de dados usando o ID do produto
        $precoResultado = (new Busca())->buscaId('produtos', $produto);
        $precoProduto = $precoResultado->preco;

        // Calcula o preço total do produto
        $precoTotalProduto = $precoProduto * $quantidade;

        // Acumula o valor total deste produto ao valor total da venda
        $valorTotalVenda += $precoTotalProduto;

        $i++;
    }

    // Agora que temos o valor total da venda, atribuímos a todas as iterações
    $i = 1;
    while (isset($dados['produto' . $i]) && isset($dados['quantidade' . $i])) {
        $produto = $dados['produto' . $i];
        $quantidade = intval($dados['quantidade' . $i]);

        // Busca o preço do produto no banco de dados usando o ID do produto
        $precoResultado = (new Busca())->buscaId('produtos', $produto);
        $precoProduto = $precoResultado->preco;

        // Calcula o valor total da venda para este produto, considerando o desconto
        $valorVendaProduto = $valorTotalVenda - (isset($dados['desconto']) ? $dados['desconto'] : 0);

        // Garante que o valor total da venda não seja negativo
        $valorVendaProduto = max(0, $valorVendaProduto);

        // Insere os dados no banco de dados para este produto
        $array = array(
            'nome_venda' => $nomeVenda,
            'produto' => $produto,
            'quantidade' => $quantidade,
            'preco' => $precoProduto,  // Corrigido para pegar o preço do produto atual
            'desconto_total_venda' => isset($dados['desconto']) ? $dados['desconto'] : 0,
            'valor_venda' => $valorVendaProduto,
            'user' => $user
        );
   
        $query = "INSERT INTO registro_vendas (nome_venda, produto_id, quantidade, preco, desconto_total_venda, valor_venda, usuario) 
                  VALUES (:nome_venda, :produto, :quantidade, :preco, :desconto_total_venda, :valor_venda, :usuario)";

        try {
            $stmt = Conexao::getInstancia()->prepare($query);
            $stmt->bindParam(':nome_venda', $array['nome_venda']);
            $stmt->bindParam(':produto', $array['produto']);
            $stmt->bindParam(':quantidade', $array['quantidade']);
            $stmt->bindParam(':preco', $array['preco']);
            $stmt->bindParam(':desconto_total_venda', $array['desconto_total_venda']);
            $stmt->bindParam(':valor_venda', $array['valor_venda']);
            $stmt->bindParam(':usuario', $array['user']);
            $stmt->execute();
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
        }

        $i++;
    }
}

    
public function contaRegistros():int {
       $query = "SELECT COUNT(DISTINCT nome_venda) as total FROM registro_vendas";
    $stmt = Conexao::getInstancia()->query($query);
    $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
    return $resultado->total; // Acesse a propriedade diretamente
    }
    public function atualizar(array $dados, int $id):void {
    $produto = $dados['produto'];
$quantidade = $dados['quantidade'];
$quantidadeAnterior = $dados['quantidade_anterior'];
$preco =  $dados['preco'];
$venda = $dados['valor_venda'];
$quantidadeDiferenca = $quantidade - $quantidadeAnterior;
$precoAnterior = $dados['preco_anterior'];
$precoNovo = $quantidade * $preco;
$diferenca = $precoAnterior - $precoNovo;
$valorVenda = $venda + $diferenca;

     $dadosArray = ['preco' => $preco, 'quantidade' => $quantidade ,'valor_venda' => $valorVenda, 'editado' => 1];
     $dadosArray2 = ['quantidade_saida'=> $quantidadeDiferenca, 'quantidade_estoque' =>$quantidadeDiferenca ];
     
      (new Atualizar())->atualizar('registro_vendas', "preco = ? , quantidade = ? , valor_venda = ?, editado = ?", $dadosArray, $id);
      
    }
}
