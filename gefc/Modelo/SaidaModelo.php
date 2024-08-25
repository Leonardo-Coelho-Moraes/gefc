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
use gefc\Nucleo\Conexao;
use gefc\Modelo\Busca;
use PDO;
use gefc\Modelo\Atualizar;
class SaidaModelo {
    
   
    
 public function venda(array $dados): void {

    foreach ($dados as $key => $value) {
        if (strpos($key, 'lote') === 0) {
            $index = str_replace('lote', '', $key);
            $loteId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                abs($quantidade);
                $query = "UPDATE lote SET quantidade = quantidade - :qnt WHERE id = :id";
                $stmt = Conexao::getInstancia()->prepare($query);
                $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':id', $loteId, PDO::PARAM_INT);
                $stmt->execute();
        }
    }
}


public function vendaRegistro(array $dados): void {
        // Verifica se há dados enviados
        $data = date("Y-m-d");
    $ano = date("Y");
    $nomeVenda = 'saida' . uniqid(). $ano;
   

    foreach ($dados as $key => $value) {
        if (strpos($key, 'lote') === 0) {
            $index = str_replace('lote', '', $key);
            $loteId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
            $qntSolic = isset($dados['qntSolic' . $index]) ? intval($dados['qntSolic' . $index]) : 0;
                abs($quantidade);
                abs($qntSolic);
            if ($quantidade > 0) {


                $query = "INSERT INTO registro_saida_sem_local (nome_saida, lote_id, quantidade, qnt_solicitada, local, data) 
                          VALUES (:nome_venda, :loteId, :quantidade, :qnt_solicitada, :local, :data)";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':nome_venda', $nomeVenda);
                    $stmt->bindParam(':loteId', $loteId);
                    $stmt->bindParam(':quantidade', $quantidade);
                    $stmt->bindParam(':qnt_solicitada', $qntSolic);
                    $stmt->bindParam(':local', $dados['local']);
                    $stmt->bindParam(':data', $data);
                    $stmt->execute();
               
            }
        }
    }
}


    

    

public function adicionarAVenda(array $dados): void {

        $quantidade = abs($dados['quantidade']);
        $qntSolic = abs($dados['qntSolic']);
        $query = "UPDATE lote SET quantidade = quantidade - :qnt WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':id', $dados['lote'], PDO::PARAM_INT);

        $stmt->execute();

        $query = "INSERT INTO registro_saida_sem_local (nome_saida, lote_id, quantidade, qnt_solicitada, local, data) VALUES (:nome_venda, :lote, :quantidade, :qnt_solicitada, :local_id, :datar)";

        // Preparar a query
        $stmt = Conexao::getInstancia()->prepare($query);

        // Vincular os parâmetros
        $stmt->bindParam(':nome_venda', $dados['venda']);
        $stmt->bindParam(':lote', $dados['lote']);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':qnt_solicitada', $qntSolic);
        $stmt->bindParam(':local_id', $dados['local']);
        $stmt->bindParam(':datar', $dados['data']);
        $stmt->execute();
}






public function pesquisaHospital(string $buscar) {
    $conexao = Conexao::getInstancia();
  

    // Consulta SQL com JOIN para buscar pelo nome do produto
    $query = "
        SELECT saida_hospital.*, produtos.nome AS nome_produto
        FROM saida_hospital
        JOIN produtos ON saida_hospital.produto_id = produtos.id
        WHERE (saida_hospital.saida LIKE :buscar

               OR  saida_hospital.data LIKE :buscar
                OR  saida_hospital.local LIKE :buscar
               
               OR produtos.nome LIKE :buscar)  -- Buscar pelo nome do produto
        ORDER BY saida_hospital.data DESC
       
    ";
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);

    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}
    public function pesquisa(?string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '', ?string $local = '', ?string $fornecedor = '')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        registro_saida_sem_local.id AS registro_id,
        registro_saida_sem_local.nome_saida,
        registro_saida_sem_local.quantidade,
        registro_saida_sem_local.qnt_solicitada,
        registro_saida_sem_local.data,
        registro_saida_sem_local.local,
        lote.lote,
        lote.fornecedor,
  
        lote.preco,
        lote.produto_id,
        produtos.nome,
        produtos.slug
    FROM 
        registro_saida_sem_local
    JOIN 
        lote ON registro_saida_sem_local.lote_id = lote.id
    JOIN 
        produtos ON lote.produto_id = produtos.id

    WHERE 
        (registro_saida_sem_local.data >= :de AND registro_saida_sem_local.data <= :ate AND produtos.nome LIKE :produto AND registro_saida_sem_local.local LIKE :local AND lote.fornecedor LIKE :fornecedor)
        
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);
        $stmt->bindValue(':fornecedor', '%' . $fornecedor . '%', PDO::PARAM_STR);



        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public function atualizarVenda(array $dados): void
    {

        $query = "UPDATE registro_saida_sem_local SET quantidade = :qnt WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':qnt', $dados['quantidade_editada'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $dados['registro_id'], PDO::PARAM_INT);

        $stmt->execute();
    }
}
