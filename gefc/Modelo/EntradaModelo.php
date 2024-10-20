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
use gefc\Modelo\Inserir;
use gefc\Modelo\Atualizar;
use gefc\Nucleo\Conexao;
use PDO;
class EntradaModelo {
public function entrada(array $dados): void {
    $data = date('Y-m-d');
   (new Inserir())->inserir('registro_entrada', 'lote_id, quantidade, data', [$dados['loteAdd'], $dados['quantidadeAdd'], $data]);
   
    
    $query = "UPDATE lote SET quantidade = quantidade + :qnt WHERE id = :id";
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':qnt', $dados['quantidadeAdd'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $dados['loteAdd'], PDO::PARAM_INT);

    $stmt->execute();
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
    AND produtos.nome LIKE :produto 
    AND lote.fornecedor LIKE :fornecedor)
ORDER BY 
    registro_entrada.id DESC
LIMIT 30;

        
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
LIMIT 30;

        
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
