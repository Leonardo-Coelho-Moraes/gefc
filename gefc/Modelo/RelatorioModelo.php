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
class RelatorioModelo {
    
    
public function buscaRegistros(array $dados)
{
    $acao = $dados['acao'];
    $mesAno = $dados['mes'];
    $produto = $dados['produto'];

    // Converta o valor do campo mes_ano para o formato 'yyyy-mm'
    $mesAnoFormatado = date('Y-m', strtotime($mesAno));

    // Escolha da tabela com base na ação
    $tabela = ($acao == 'Entrada') ? 'registro_entrada' : 'registro_vendas';

    if (empty($produto) && !empty($mesAno)) {
        $query = "SELECT {$tabela}.*, produtos.nome AS produto_nome, produtos.fornecedor AS produto_fornecedor, produtos.unidade_contagem AS produto_unidade
                  FROM {$tabela}
                  JOIN produtos ON {$tabela}.produto_id = produtos.id
                  WHERE DATE_FORMAT({$tabela}.data, '%Y-%m') = '{$mesAnoFormatado}'";
    } elseif (empty($mesAno) && !empty($produto)) {
        $query = "SELECT {$tabela}.*, produtos.nome AS produto_nome, produtos.fornecedor AS produto_fornecedor, produtos.unidade_contagem AS produto_unidade
                  FROM {$tabela}
                  JOIN produtos ON {$tabela}.produto_id = produtos.id
                  WHERE produtos.id = {$produto}";
    } elseif (!empty($mesAno) && !empty($produto)) {
        $query = "SELECT {$tabela}.*, produtos.nome AS produto_nome, produtos.fornecedor AS produto_fornecedor, produtos.unidade_contagem AS produto_unidade
                  FROM {$tabela}
                  JOIN produtos ON {$tabela}.produto_id = produtos.id
                  WHERE DATE_FORMAT({$tabela}.data, '%Y-%m') = '{$mesAnoFormatado}'
                  AND produtos.id = {$produto}";
    }
    elseif (empty($mesAno) && empty($produto)) {
        $query = "SELECT {$tabela}.*, produtos.nome AS produto_nome, produtos.fornecedor AS produto_fornecedor, produtos.unidade_contagem AS produto_unidade
                  FROM {$tabela}
                  JOIN produtos ON {$tabela}.produto_id = produtos.id
                  ";
    }
    else {
        // Caso em que nenhum parâmetro é especificado, você pode querer definir um comportamento padrão ou lançar um erro.
        return []; // Por exemplo, retornar um array vazio se nenhum parâmetro válido for fornecido.
    }

    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->execute();

    $resultado = $stmt->fetchAll();

    return $resultado;
}

public function buscaMedia(array $dados)
{
    $produto = $dados['produto'];
    $local = $dados['local'];
    $mesAno = $dados['data'];

    // Converta o valor do campo mes_ano para o formato 'yyyy-mm'
    $mesAnoFormatado = date('Y-m', strtotime($mesAno));

    // Query SQL para buscar os dados
    $query = "SELECT registro_vendas.*, produtos.nome AS produto_nome, produtos.fornecedor AS produto_fornecedor, produtos.unidade_contagem AS produto_unidade
              FROM registro_vendas
              JOIN produtos ON registro_vendas.produto_id = produtos.id
              WHERE DATE_FORMAT(registro_vendas.data, '%Y-%m') = '{$mesAnoFormatado}' 
                AND registro_vendas.local = '{$local}' 
                AND produtos.id = {$produto}";

    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->execute();

    $resultado = $stmt->fetchAll();

    return $resultado;
}






}
