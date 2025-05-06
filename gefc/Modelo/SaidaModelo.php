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
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Conexao;
use gefc\Modelo\Busca;
use PDO;
use gefc\Modelo\Atualizar;
class SaidaModelo {
    
   
    
    public function venda(array $dados): void
    {
        // Verifique se os dados foram fornecidos corretamente
       
        foreach ($dados as $key => $value) {
            if (strpos($key, 'produto_id') === 0) {
                $index = str_replace('produto_id', '', $key);
                $produtoId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                abs($quantidade);
                $fornecedor = $dados['fornecedor' . $index];
                if ($quantidade > 0) {

                  
                    $query = "UPDATE produtos SET qnt1 = qnt1 - :qnt WHERE id = :id";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
                    $stmt->execute();

                 
                    if ($fornecedor == 'Fornecedor'){
                        $query = "UPDATE produtos SET qnt_for = qnt_for - :qnt WHERE id = :id";
                        $stmt = Conexao::getInstancia()->prepare($query);
                        $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                        $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
                        $stmt->execute();

                    }if($fornecedor == 'CEMA'){
                        $query = "UPDATE produtos SET qnt_cema = qnt_cema - :qnt WHERE id = :id";
                        $stmt = Conexao::getInstancia()->prepare($query);
                        $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                        $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
                        $stmt->execute();

                    }
                   
                }
            }
        }
    }


public function vendaRegistro(array $dados): void {
        // Verifica se há dados enviados
        $data = $dados['data'];
    $ano = date("Y");

        $query = "
    SELECT nome_saida
    FROM registro_saida_sem_local 
    WHERE id = (SELECT MAX(id) FROM registro_saida_sem_local)
";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $nomeVenda = $resultado['nome_saida'];
        $nomeVendaNumero = intval($nomeVenda) + 1;
        $usuario =  UsuarioControlador::usuario()->id;

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto_id') === 0) {
            $index = str_replace('produto_id', '', $key);
            $produtoId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
            $fornecedor = $dados['fornecedor' . $index];
                abs($quantidade);
             
            if ($quantidade > 0) {


                $query = "INSERT INTO registro_saida_sem_local (nome_saida, produto_id, quantidade, qnt_solicitada, local, data, usuario,fornecedor) 
                          VALUES (:nome_venda, :produtoId, :quantidade, :qnt_solicitada, :local, :data,:usuario,:fornecedor)";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':nome_venda', $nomeVendaNumero);
                    $stmt->bindParam(':produtoId', $produtoId);
                    $stmt->bindParam(':quantidade', $quantidade);
                    $stmt->bindParam(':qnt_solicitada', $quantidade);
                    $stmt->bindParam(':local', $dados['local']);
                    $stmt->bindParam(':data', $data);
                    $stmt->bindParam(':usuario', $usuario);
                    $stmt->bindParam(':fornecedor', $fornecedor);
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
        $stmt->bindParam(':qnt_solicitada', $quantidade);
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
    public function pesquisa(?string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '', ?string $local = '')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        registro_saida_sem_local.id AS registro_id,
        registro_saida_sem_local.nome_saida,
        registro_saida_sem_local.quantidade,

  produtos.unidade_contagem AS unidade,
        registro_saida_sem_local.qnt_solicitada,
        registro_saida_sem_local.data,
        registro_saida_sem_local.local,
     
        
        registro_saida_sem_local.fornecedor,
        
    produtos.tipo,
        registro_saida_sem_local.produto_id,
        produtos.nome,
        produtos.slug
    FROM 
        registro_saida_sem_local

    JOIN 
        produtos ON registro_saida_sem_local.produto_id = produtos.id
       

    WHERE 
        (registro_saida_sem_local.data >= :de AND registro_saida_sem_local.data <= :ate AND produtos.nome LIKE :produto AND registro_saida_sem_local.local LIKE :local)
        
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);



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
