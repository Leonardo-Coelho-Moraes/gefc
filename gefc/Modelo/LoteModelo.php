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
use PDO;
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Conexao;
use gefc\Modelo\Atualizar;
use gefc\Modelo\Inserir;
class LoteModelo {
  
 public function armazenar(array $dados): void {
    (new Inserir())->inserir(
        'lote',
        'lote, produto_id, quantidade, fornecedor,preco,preco_comercial,localizacao, vencimento',
        [$dados['lote'],$dados['produto'],$dados['quantidade'],$dados['fornecedor'], $dados['preco'], $dados['preco_comercial'], $dados['localizacao'], $dados['vencimento']]
    );
}

 public function atualizar(array $dados): void {

    (new Atualizar())->atualizar(
        'lote', "lote = ?, produto_id = ?, quantidade = ?, fornecedor = ?, preco =?,preco_comercial =?,localizacao =?, vencimento = ?",
        [$dados['lote_edit'],$dados['produto_edit'],$dados['quantidade_edit'],$dados['fornecedor_edit'], $dados['preco_edit'], $dados['preco_comercial_edit'], $dados['localizacao_edit'], $dados['vencimento_edit']],
        $dados['lote_id']
    );
}



public function pesquisa(?string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '', ?string $fornecedor = '') {
    $conexao = Conexao::getInstancia();

    $query = "
    SELECT  
    lote.id,
        
        lote.lote,
        produtos.id AS produto_id,
         produtos.nome,
         lote.quantidade,
         lote.fornecedor,
         lote.preco,
         lote.preco_comercial,
         lote.vencimento,
         lote.localizacao,
         lote.produto_id, 
         produtos.slug
        FROM 
        lote
        JOIN 
         produtos ON lote.produto_id = produtos.id
        WHERE
        (lote.vencimento >= :de AND lote.vencimento <= :ate AND produtos.nome LIKE :produto AND lote.fornecedor LIKE :fornecedor)
        ORDER BY lote.vencimento ASC
              ";

    $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        $stmt->bindValue(':fornecedor', '%' . $fornecedor . '%', PDO::PARAM_STR);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}




   }

