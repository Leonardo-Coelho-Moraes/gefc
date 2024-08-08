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


use gefc\Nucleo\Conexao;
use gefc\Modelo\Busca;
use PDO;
use gefc\Modelo\Atualizar;

class VendaModelo
{

    

    public function venda(array $dados): void
    {
        // Verifique se os dados foram fornecidos corretamente
       
        foreach ($dados as $key => $value) {
            if (strpos($key, 'lote') === 0) {
                $index = str_replace('lote', '', $key);
                $loteId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;

                if ($quantidade > 0) {

                    $query = "UPDATE lote SET quantidade = quantidade - :qnt WHERE id = :id";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $loteId, PDO::PARAM_INT);

                    $stmt->execute();

                    // Verifique se o produto já existe na tabela local_estoque
                    $localId = intval($dados['local']);
                    $query = "SELECT estoque FROM local_estoque WHERE lote_id = ? AND local_id = ?";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->execute([$loteId, $localId]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        // Produto já existe no local, atualize o estoque
                        $estoqueAtual = intval($result['estoque']);
                        $novoEstoque = $estoqueAtual + $quantidade;
                        $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE lote_id = ? AND local_id = ?";
                        $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                        $stmtUpdate->execute([$novoEstoque, $loteId, $localId]);
                    } else {
                        // Produto não existe no local, insira um novo registro
                        $insertQuery = "INSERT INTO local_estoque (local_id, lote_id, estoque) VALUES (?, ?, ?)";
                        $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
                        $stmtInsert->execute([$localId, $loteId, $quantidade]);
                    }
                }
            }
        }
    }





    public function vendaRegistro(array $dados): void
    {
        // Verifica se há dados enviados
        $data = date("Y-m-d");
        $ano = date("Y");
        $nomeVenda = 'saida' . uniqid() . $ano;


        foreach ($dados as $key => $value) {
            if (strpos($key, 'lote') === 0) {
                $index = str_replace('lote', '', $key);
                $loteId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                $qntSolic = isset($dados['qntSolic' . $index]) ? intval($dados['qntSolic' . $index]) : 0;

                if ($quantidade > 0) {
                    // Insere os dados no banco de dados para este produto
                    $array = array(
                        'nome_venda' => $nomeVenda,
                        'lote' => $loteId,
                        'quantidade' => $quantidade,
                        'qnt_solicitada' => $qntSolic,
                        'local' => $dados['local'],
                        'data' => $data

                    );

                    $query = "INSERT INTO registro_vendas (nome_venda, lote_id, quantidade, qnt_solicitada, local, data) 
                          VALUES (:nome_venda, :lote, :quantidade, :qnt_solicitada, :local, :data)";

                   
                        $stmt = Conexao::getInstancia()->prepare($query);
                        $stmt->bindParam(':nome_venda', $array['nome_venda']);
                        $stmt->bindParam(':lote', $array['lote']);
                        $stmt->bindParam(':quantidade', $array['quantidade']);
                        $stmt->bindParam(':qnt_solicitada', $array['qnt_solicitada']);
                        $stmt->bindParam(':local', $array['local']);
                    $stmt->bindParam(':data', $array['data']);

                        $stmt->execute();
                  
                }
            }
        }
    }



    
    public function adicionarAVenda(array $dados): void
    {

        $query = "UPDATE lote SET quantidade = quantidade - :qnt WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':qnt', $dados['quantidade'], PDO::PARAM_INT);
        $stmt->bindParam(':id', $dados['lote'], PDO::PARAM_INT);

        $stmt->execute();

        // Definir a query de inserção
        $query = "INSERT INTO registro_vendas (nome_venda, lote_id, quantidade, qnt_solicitada, local, data) VALUES (:nome_venda, :lote, :quantidade, :qnt_solicitada, :local_id, :datar)";

        // Preparar a query
        $stmt = Conexao::getInstancia()->prepare($query);

        // Vincular os parâmetros
        $stmt->bindParam(':nome_venda', $dados['venda']);
        $stmt->bindParam(':lote', $dados['lote']);
        $stmt->bindParam(':quantidade', $dados['quantidade']);
        $stmt->bindParam(':qnt_solicitada', $dados['qntSolic']);
        $stmt->bindParam(':local_id', $dados['local']);
        $stmt->bindParam(':datar', $dados['data']);
        $stmt->execute();



        $localId = intval($dados['local']);
        $query = "SELECT estoque FROM local_estoque WHERE lote_id = ? AND local_id = ?";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute([$dados['lote'], $localId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Produto já existe no local, atualize o estoque
            $estoqueAtual = intval($result['estoque']);
            $novoEstoque = $estoqueAtual + $dados['quantidade'];
            $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE lote_id = ? AND local_id = ?";
            $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
            $stmtUpdate->execute([$novoEstoque, $dados['lote'], $localId]);
        } else {
            // Produto não existe no local, insira um novo registro
            $insertQuery = "INSERT INTO local_estoque (local_id, lote_id, estoque) VALUES (?, ?, ?)";
            $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
            $stmtInsert->execute([$localId, $dados['lote'], $dados['quantidade']]);
        }
    }



    public function pesquisa( ? string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '' , ?string $local ='', ?string $fornecedor = '')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        registro_vendas.id AS registro_id,
        registro_vendas.nome_venda,
        registro_vendas.quantidade,
        registro_vendas.qnt_solicitada,
        registro_vendas.data,
        lote.lote,
        lote.cod,
        lote.fornecedor,
        lote.preco,
        lote.produto_id,
        produtos.nome,
        locais.nome AS local,
        produtos.slug
    FROM 
        registro_vendas
    JOIN 
        lote ON registro_vendas.lote_id = lote.id
    JOIN 
        produtos ON lote.produto_id = produtos.id
    JOIN 
        locais ON registro_vendas.local = locais.id
    WHERE 
        (registro_vendas.data >= :de AND registro_vendas.data <= :ate AND produtos.nome LIKE :produto AND locais.nome LIKE :local AND lote.fornecedor LIKE :fornecedor)
        
        
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


    public function pesquisaFormulario()
    {

        $conexao = Conexao::getInstancia();
        $data = date('Y-m-d');
        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        lote.id AS lote_id,
        lote.lote,
        lote.quantidade,
        lote.vencimento,
        lote.cod,
        lote.fornecedor,
        lote.produto_id,
        produtos.nome,
        produtos.id
    FROM 
        lote
    JOIN 
        produtos ON lote.produto_id = produtos.id
    WHERE 
        lote.quantidade > 0 
        AND lote.vencimento >= $data
";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }





    public function pesquisaEstoque(int $local)
    {
        // Certifique-se de que a classe Conexao está corretamente importada ou carregada
        $conexao = Conexao::getInstancia();

        $query = "
        SELECT 
            local_estoque.id AS local_estoque_id,
            local_estoque.estoque,
            lote.lote,
            lote.produto_id,
            lote.vencimento,
            lote.fornecedor,
            produtos.nome,
            produtos.slug
        FROM 
            local_estoque
        JOIN 
            lote ON local_estoque.lote_id = lote.id
        JOIN 
            produtos ON lote.produto_id = produtos.id
        WHERE 
            local_estoque.local_id = :local
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':local', $local, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

   



    public function atualizarEstoque(array $dados): void
    {
        // Definir a query de atualização
        $query = "UPDATE local_estoque SET estoque = :estoque WHERE id = :id";

        // Preparar e executar a query de atualização
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':estoque', $dados['estoque'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $dados['id'], PDO::PARAM_STR);
        $stmt->execute();
    }
}
