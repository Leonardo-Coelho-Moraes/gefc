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
  
 public function armazenarEntrada(array $dados): void {
    (new Inserir())->inserir(
        'lote',
        'lote, produto_id, quantidade, fornecedor,localizacao, vencimento',
        [$dados['lote'],$dados['produto'],$dados['quantidade'],$dados['fornecedor'],$dados['localizacao'], $dados['vencimento']]
    );

        $queryBusca = "SELECT MAX(id) AS recente FROM lote" ;
        $stmt1 = Conexao::getInstancia()->prepare($queryBusca);
        $stmt1->execute();

        $resultado = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $recente = $resultado[0]['recente'];
        $data = date('Y-m-d');
        (new Inserir())->inserir('registro_entrada', 'lote_id, quantidade, data', [$recente, $dados['quantidade'], $dados['dataEntrada']]);
        if ($dados['deposito'] == '1'){
            $query = "UPDATE produtos SET qnt1 = qnt1 + :qnt WHERE id = :id";
            $stmt = Conexao::getInstancia()->prepare($query);
            $stmt->bindParam(':qnt', $dados['quantidade'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $dados['produto'], PDO::PARAM_INT);
            $stmt->execute();
        }

     /*   if ($dados['fornecedor'] == 'CEMA'){
            $query = "UPDATE produtos SET qnt_cema = qnt_cema + :qnt WHERE id = :id";
            $stmt = Conexao::getInstancia()->prepare($query);
            $stmt->bindParam(':qnt', $dados['quantidade'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $dados['produto'], PDO::PARAM_INT);
            $stmt->execute();
        }elseif($dados['fornecedor'] == 'Fornecedor'){
            $query = "UPDATE produtos SET qnt_for = qnt_for + :qnt WHERE id = :id";
            $stmt = Conexao::getInstancia()->prepare($query);
            $stmt->bindParam(':qnt', $dados['quantidade'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $dados['produto'], PDO::PARAM_INT);
            $stmt->execute();
        }*/
}


    public function zerarLotes(): void
    {
        // 1. Buscar todos os registros da tabela lote
        $queryBuscar = "SELECT id, quantidade FROM lote";
        $stmt = Conexao::getInstancia()->prepare($queryBuscar);
        $stmt->execute();
        $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Filtrar lotes onde a quantidade é menor que 0
        $lotesParaAtualizar = array_filter($lotes, function ($lote) {
            return $lote['quantidade'] < 0;
        });

        // 3. Atualizar a quantidade para 0 onde necessário
        if (!empty($lotesParaAtualizar)) {
            $idsParaAtualizar = array_column($lotesParaAtualizar, 'id');
            $queryAtualizar = "UPDATE lote SET quantidade = 0 WHERE id IN (" . implode(',', $idsParaAtualizar) . ")";
            $stmt = Conexao::getInstancia()->prepare($queryAtualizar);
            $stmt->execute();
        }
    }


    public function armazenar(array $dados)
    {
        (new Inserir())->inserir(
            'lote',
            'lote, produto_id, quantidade, fornecedor,localizacao, vencimento',
            [$dados['lote'], $dados['produto'], $dados['quantidade'], $dados['fornecedor'], $dados['localizacao'], $dados['vencimento']]
        );
        $saida = Conexao::getInstancia()->lastInsertId();
        echo $saida." é o cod do novo Lote.";

    }

 public function atualizar(array $dados): void {

    (new Atualizar())->atualizar(
        'lote', "lote = ?, produto_id = ?, quantidade = ?, fornecedor = ?,localizacao =?, vencimento = ?",
        [$dados['lote_edit'],$dados['produto_edit'],$dados['quantidade_edit'],$dados['fornecedor_edit'],$dados['localizacao_edit'], $dados['vencimento_edit']],
        $dados['lote_id']
    );
}



    public function pesquisaRelatorio(?string $dePesquisa = '2023-01-30', ?string $atePesquisa = '2099-12-31', ?string $codPesquisa = '', ?string $produto = '', ?string $fornecedor = '', int $limite = 10, int $max = 1)
    {
       
       
        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        lote.id,  
        lote.lote,  
        produtos.id AS produto_id,  
        produtos.nome,  
        lote.quantidade,  
        lote.fornecedor,  
         produtos.unidade_contagem AS unidade,
        lote.vencimento,  
        lote.localizacao,  
        lote.produto_id,   
        produtos.slug  
    FROM   
        lote  
    JOIN   
        produtos ON lote.produto_id = produtos.id  
    WHERE  
        (lote.id = :cod AND  lote.vencimento >= :de AND lote.vencimento <= :ate AND  produtos.nome LIKE :produto OR  produtos.id LIKE :produto AND lote.fornecedor LIKE :fornecedor AND lote.quantidade >= :max)  
    ORDER BY   
        produtos.nome ASC 
    LIMIT :limite  
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':cod', $codPesquisa , PDO::PARAM_INT);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        $stmt->bindValue(':fornecedor', '%' . $fornecedor . '%', PDO::PARAM_STR);
        $stmt->bindValue(':max', $max, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT); // Binding do limite como inteiro  
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function pesquisa(?string $pesquisa= '')
    {


        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        lote.id,  
        lote.lote,  
        produtos.id AS produto_id,  
        produtos.nome,  
        lote.quantidade,  
        lote.fornecedor,  
       
         produtos.unidade_contagem AS unidade,
     
        lote.vencimento,  
        lote.localizacao,  
        lote.produto_id,   
        produtos.slug  
    FROM   
        lote  
    JOIN   
        produtos ON lote.produto_id = produtos.id  
    WHERE  
        (produtos.nome LIKE :buscar OR lote.id LIKE :buscar OR lote.lote LIKE :buscar OR  lote.fornecedor LIKE :buscar )  
    ORDER BY   
        lote.id DESC   
    LIMIT 200
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':buscar', '%' . $pesquisa . '%', PDO::PARAM_STR);

        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }




    public function pesquisaEntrada(?string $busca = '')
    {
        $conexao = Conexao::getInstancia();

        $query = "
        SELECT  
            lote.id,
            lote.lote,
            lote.fornecedor,
            produtos.nome
        FROM 
            lote
        JOIN 
            produtos ON lote.produto_id = produtos.id 
        WHERE
            lote.lote LIKE :busca OR 
            produtos.nome LIKE :busca OR 
            lote.id LIKE :busca
    ";

        $stmt = $conexao->prepare($query);
        // Utilizando '%' para buscas parciais em todos os campos
        $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);

        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }





   }

