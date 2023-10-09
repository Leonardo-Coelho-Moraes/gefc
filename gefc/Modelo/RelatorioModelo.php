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
    $produto =  Helpers::Mudar(Helpers::validarString($dados['produto']), [' '], '-');
    $fabricante =  Helpers::Mudar(Helpers::validarString($dados['fabricante']), [' '], '-');
    $fornecedor =  Helpers::Mudar(Helpers::validarString($dados['fornecedor']), [' '], '-');
    $lote =  $dados['lote'];
    $local = $dados['local'];

    
    // Converta o valor do campo mes_ano para o formato 'yyyy-mm'
    $mesAnoFormatado = date('Y-m', strtotime($mesAno));

    $query = "SELECT registros.*, produtos.nome AS nome, produtos.fabricante, produtos.fornecedor, produtos.lote, locais.nome AS local
              FROM registros
              LEFT JOIN produtos ON registros.produto_id = produtos.id
              LEFT JOIN locais ON registros.local_id = locais.id
              WHERE DATE_FORMAT(registros.data_hora, '%Y-%m') = :mesAno
              AND registros.acao = :acao";

    $params = [
        ':mesAno' => $mesAnoFormatado,
        ':acao' => $acao
    ];

    if (!empty($produto)) {
        $query .= " AND produtos.nome = :produto";
        $params[':produto'] = $produto;
    }
    if (!empty($fabricante)) {
        $query .= " AND produtos.fabricante = :fabricante";
        $params[':fabricante'] = $fabricante;
    }
    if (!empty($fornecedor)) {
        $query .= " AND produtos.fornecedor = :fornecedor";
        $params[':fornecedor'] = $fornecedor;
    }
    if (!empty($lote)) {
        $query .= " AND (produtos.lote) = :lote";
        $params[':lote'] = $lote;
    }
    if (!empty($local) && $acao === 'saida') {
        $query .= " AND locais.nome = :local";
        $params[':local'] = $local;
    }

    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->execute($params);

    $resultado = $stmt->fetchAll();

    return $resultado;
}



}
