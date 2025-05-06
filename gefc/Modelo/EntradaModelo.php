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

use gefc\Modelo\Inserir;
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Conexao;
use PDO;
class EntradaModelo {
public function entrada(array $dados): void {
    $data = date('Y-m-d');
   (new Inserir())->inserir('registro_entrada', 'lote_id, quantidade, data', [$dados['loteAdd'], $dados['quantidadeAdd'], $dados['dataEntradaL']]);
   
    
    $query = "UPDATE lote SET quantidade = quantidade + :qnt WHERE id = :id";
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':qnt', $dados['quantidadeAdd'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $dados['loteAdd'], PDO::PARAM_INT);
    $stmt->execute();

    $queryProdutoId = "SELECT produto_id FROM lote WHERE id = :id";
$stmtProdutoId = Conexao::getInstancia()->prepare($queryProdutoId);
$stmtProdutoId->bindParam(':id', $dados['loteAdd'], PDO::PARAM_INT);
$stmtProdutoId->execute();

$produtoId = $stmtProdutoId->fetchColumn();

$queryFornecedor = "SELECT fornecedor FROM lote WHERE id = :id";
$stmtFornecedor = Conexao::getInstancia()->prepare($queryFornecedor);
$stmtFornecedor->bindParam(':id', $dados['loteAdd'], PDO::PARAM_INT);
$stmtFornecedor->execute();

$fornecedor= $stmtFornecedor->fetchColumn();
    
    if ($dados['deposito'] == '1'){
      
        $query = "UPDATE produtos SET qnt1 = qnt1 + :qnt WHERE id = :id";
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':qnt', $dados['quantidadeAdd'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
    $stmt->execute();
    }
/*
    if ($fornecedor == 'Fornecedor'){
        $query = "UPDATE produtos SET qnt_for = qnt_for + :qnt WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':qnt', $dados['quantidadeAdd'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
        $stmt->execute();
    }elseif($fornecedor == 'CEMA'){
        $query = "UPDATE produtos SET qnt_cema = qnt_cema + :qnt WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':qnt', $dados['quantidadeAdd'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
        $stmt->execute();
    }*/
}
public function entradaLocal(array $dados): void {
    $local = UsuarioControlador::usuario()->local;
 
   
   // Verifique se os dados foram fornecidos corretamente
       
   foreach ($dados as $key => $value) {
    if (strpos($key, 'produto_id') === 0) {
        $index = str_replace('produto_id', '', $key);
        $produtoId = intval($value);
        $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
        abs($quantidade);
       
        if ($quantidade > 0) {

        (new Inserir())->inserir('entrada_ubs', 'produto_id, quantidade, data,local_id', [$produtoId, $quantidade, $dados['dataEntrada'], $local]);
   
           
      
      if($dados['adicionar'] == 1){

       
        $query = "SELECT estoque FROM local_estoque WHERE produto_id = ? AND local_id = ?";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute([$produtoId, $local]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Produto já existe no local, atualize o estoque
            $estoqueAtual = intval($result['estoque']);
            $novoEstoque = $estoqueAtual + $quantidade;
            abs($novoEstoque);
            $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE produto_id = ? AND local_id = ?";
            $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
            $stmtUpdate->execute([$novoEstoque, $produtoId, $local]);
        } else {
            // Produto não existe no local, insira um novo registro
            $insertQuery = "INSERT INTO local_estoque (local_id, produto_id, estoque) VALUES (?, ?, ?)";
            $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
            $stmtInsert->execute([$local, $produtoId, $quantidade]);
        }

    }
               

         
           
          
           
        }
    }
}
}
public function atualizar(array $dados): void {
    // Define a query de atualização
    $query = "UPDATE registro_entrada SET quantidade = :quantidade, editado = :editado WHERE id = :id";
    
    // Prepara a query para execução
    $stmt = Conexao::getInstancia()->prepare($query);
    $editado = 1;
    // Vincula os parâmetros
    $stmt->bindParam(':quantidade', $dados['quantidade_editada'], PDO::PARAM_INT);
    $stmt->bindParam(':editado', $editado, PDO::PARAM_INT);
    $stmt->bindParam(':id', $dados['registro_id'], PDO::PARAM_INT);
    
    // Executa a query
    $stmt->execute();
}

public function atualizarLocal(array $dados): void {
    // Define a query de atualização
    $query = "UPDATE entrada_ubs SET quantidade = :quantidade, editado = :editado WHERE id = :id";
    
    // Prepara a query para execução
    $stmt = Conexao::getInstancia()->prepare($query);
    $editado = 1;
    // Vincula os parâmetros
    $stmt->bindParam(':quantidade', $dados['quantidade_editada'], PDO::PARAM_INT);
    $stmt->bindParam(':editado', $editado, PDO::PARAM_INT);
    $stmt->bindParam(':id', $dados['registro_id'], PDO::PARAM_INT);
    
    // Executa a query
    $stmt->execute();
}



    public function pesquisaRelatorio(?string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '',  ?string $fornecedor = '')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
    registro_entrada.id AS registro_id,
    registro_entrada.quantidade,
    registro_entrada.data,
    lote.lote,
    registro_entrada.lote_id,
    lote.fornecedor,
    lote.vencimento,
    lote.produto_id,
    produtos.nome,
    produtos.unidade_contagem AS unidade,
    produtos.slug
FROM 
    registro_entrada
JOIN 
    lote ON registro_entrada.lote_id = lote.id
JOIN 
    produtos ON lote.produto_id = produtos.id
WHERE 
    (registro_entrada.data >= :de 
    AND registro_entrada.data <= :ate 
    AND produtos.nome LIKE :produto OR produtos.id LIKE :produto
    AND lote.fornecedor LIKE :fornecedor )
ORDER BY 
    registro_entrada.id ASC
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        
        $stmt->bindValue(':fornecedor', '%' . $fornecedor . '%', PDO::PARAM_STR);



        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public function pesquisaEntradaLocal(?string $busca = '')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
    entrada_ubs.id AS registro_id,
    entrada_ubs.quantidade,
    entrada_ubs.data,
    entrada_ubs.produto_id,
    produtos.nome
FROM 
    entrada_ubs
JOIN 
    produtos ON entrada_ubs.produto_id = produtos.id
WHERE 
    (
    produtos.nome LIKE :busca 
   )
ORDER BY 
    entrada_ubs.id DESC
LIMIT 100;

        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }


    public function pesquisa(?string $busca = '')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
    registro_entrada.id AS registro_id,
    registro_entrada.quantidade,
    registro_entrada.data,
    lote.lote,
    registro_entrada.lote_id,
    lote.fornecedor,
    lote.vencimento,
     produtos.unidade_contagem AS unidade,
    lote.produto_id,
    produtos.nome,
    produtos.slug
FROM 
    registro_entrada
JOIN 
    lote ON registro_entrada.lote_id = lote.id
JOIN 
    produtos ON lote.produto_id = produtos.id
WHERE 
    (
    produtos.nome LIKE :busca 
   )
ORDER BY 
    registro_entrada.id DESC
LIMIT 100;

        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public function pesquisaEntrada(?string $busca = '')
    {
        $conexao = Conexao::getInstancia();

        $query = "
        SELECT  
            lote.id,
            produtos.nome
        FROM 
            lote
        JOIN 
            produtos ON lote.produto_id = produtos.id 
        WHERE
            lote.lote = :busca
    ";

        $stmt = $conexao->prepare($query);
        // Utilizando '%' para buscas parciais em todos os campos
        $stmt->bindValue(':busca', $busca, PDO::PARAM_STR);

        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    


}
