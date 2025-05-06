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
use gefc\Nucleo\Helpers;
use gefc\Modelo\Atualizar;
use gefc\Controlador\UsuarioControlador;

class VendaModelo
{


    function zerarProduto()
    {
        $querySelect = "UPDATE produtos
SET 
    qnt1 = CASE WHEN qnt1 < 0 THEN 0 ELSE qnt1 END,

    qnt_for = CASE WHEN qnt_for < 0 THEN 0 ELSE qnt_for END,
    qnt_cema = CASE WHEN qnt_cema < 0 THEN 0 ELSE qnt_cema END
WHERE qnt1 < 0 OR qnt_for < 0 OR qnt_cema < 0;";
        $stmt = Conexao::getInstancia()->prepare($querySelect);
        $stmt->execute();

      
    }
    function deletarProdutosSemEstoque()
    {
        $querySelect = "SELECT id FROM local_estoque WHERE estoque = 0";
        $stmt = Conexao::getInstancia()->prepare($querySelect);
        $stmt->execute();

        $produtosSemEstoque = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($produtosSemEstoque)) {
            $queryDelete = "DELETE FROM local_estoque WHERE id = :id";
            $stmtDelete = Conexao::getInstancia()->prepare($queryDelete);

            foreach ($produtosSemEstoque as $id) {
                $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtDelete->execute();
            }
        } 
    }

    

    public function venda(array $dados, int $id, int $local): void
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

                    $query = "INSERT INTO registro_vendas (nome_venda, produto_id, quantidade, qnt_solicitada, fornecedor) 
                    VALUES (:nome_venda, :produtoId, :quantidade, :qnt_solicitada,:fornecedor)";
              $stmt = Conexao::getInstancia()->prepare($query);
              $stmt->bindParam(':nome_venda', $id);
              $stmt->bindParam(':produtoId', $produtoId);
              $stmt->bindParam(':quantidade', $quantidade);
              $stmt->bindParam(':qnt_solicitada', $quantidade);
              $stmt->bindParam(':fornecedor', $fornecedor);
              $stmt->execute();

           
                    $insertLocal = "INSERT INTO registro_recebimento_local (produto_id, nome_entrada, quantidade,local) VALUES (?, ?, ?,?)";
                    $stmtLocal = Conexao::getInstancia()->prepare($insertLocal);
                    $stmtLocal->execute([$produtoId, $id, $quantidade,$local ]);
                 
              if($dados['descontar'] == 1){
                $query = "UPDATE produtos SET qnt1 = qnt1 - :qnt WHERE id = :id";
                $stmt = Conexao::getInstancia()->prepare($query);
                $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
                $stmt->execute();

            }
                       

                 
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


    public function atualizarVenda(array $dados): void
    {

                    $query = "UPDATE registro_vendas SET quantidade = :qnt WHERE id = :id";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':qnt', $dados['quantidade_editada'], PDO::PARAM_INT);
                    $stmt->bindParam(':id', $dados['registro_id'], PDO::PARAM_INT);

                    $stmt->execute();    
    }


    public function dispensaRegistro(array $dados): void
    {
        $usuario = UsuarioControlador::usuario()->id;
        (new Inserir())->inserir(
            'num_dispensa',
            'data,usuario, local_id, obs',
            [$dados['data'], $usuario, $dados['local'], $dados['descricao']]
        );
        $dispensa = Conexao::getInstancia()->lastInsertId();
       Helpers::redirecionar('vendas/'.$dispensa);

        // Verifica se há dados enviados
          /* $nomeEntrada = 'e' . uniqid();
                    $insertLocal = "INSERT INTO registro_recebimento_local (produto_id, nome_entrada, quantidade,local) VALUES (?, ?, ?,?)";
                    $stmtLocal = Conexao::getInstancia()->prepare($insertLocal);
                    $stmtLocal->execute([$produtoId, $nomeEntrada, $quantidade, $array['local']]);
                  */
    }



    
    public function adicionarAVenda(array $dados): void
    {
        $data = date("Y-m-d");
        $nomeEntrada = 'entrada' . uniqid() . $data;
        $quantidade = abs($dados['quantidade']);
        $qntSolic = abs($dados['qntSolic']);

        $query = "UPDATE lote SET quantidade = quantidade - :qnt WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':id', $dados['lote'], PDO::PARAM_INT);

        $stmt->execute();

        $query = "UPDATE lote SET quantidade = CASE WHEN quantidade < 0 THEN 0 ELSE quantidade END WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':id', $dados['lote'], PDO::PARAM_INT);
        $stmt->execute();


        

        // Definir a query de inserção
        $query = "INSERT INTO registro_vendas (nome_venda, lote_id, quantidade, qnt_solicitada, local, data) VALUES (:nome_venda, :lote, :quantidade, :qnt_solicitada, :local_id, :datar)";

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

        $insertLocal = "INSERT INTO registro_recebimento_local (lote_id, nome_entrada, quantidade,local) VALUES (?, ?, ?,?)";
        $stmtLocal = Conexao::getInstancia()->prepare($insertLocal);
        $stmtLocal->execute([$dados['lote'], $nomeEntrada, $quantidade, $dados['local']]);
/*
        $localId = intval($dados['local']);
        $query = "SELECT estoque FROM local_estoque WHERE lote_id = ? AND local_id = ?";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute([$dados['lote'], $localId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Produto já existe no local, atualize o estoque
            $estoqueAtual = intval($result['estoque']);
            $novoEstoque = $estoqueAtual + $quantidade;
            $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE lote_id = ? AND local_id = ?";
            $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
            $stmtUpdate->execute([$novoEstoque, $dados['lote'], $localId]);
        } else {
            // Produto não existe no local, insira um novo registro
            $insertQuery = "INSERT INTO local_estoque (local_id, lote_id, estoque) VALUES (?, ?, ?)";
            $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
            $stmtInsert->execute([$localId, $dados['lote'], $quantidade]);
        }
            */
    }



    public function pesquisa( ? string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31' , ?string $local ='')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        num_dispensa.id,
        num_dispensa.data,
         num_dispensa.obs,
        locais.nome AS local
    FROM 
        num_dispensa
    JOIN 
        locais ON num_dispensa.local_id = locais.id
    WHERE 
        (num_dispensa.data >= :de AND num_dispensa.data <= :ate AND locais.nome LIKE :local )
        
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function pesquisaDadosSaida(int $id)
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        num_dispensa.id,
        num_dispensa.data,
        locais.nome AS local,
        locais.id AS localId,
        num_dispensa.obs,
        usuario.nome 


    FROM 
        num_dispensa
    JOIN 
        locais ON num_dispensa.local_id = locais.id
         JOIN 
        usuario ON num_dispensa.usuario = usuario.id
    WHERE 
        (num_dispensa.id = :id )
        
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function pesquisaSaida(int $saida)
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        registro_vendas.id AS registro_id,
        registro_vendas.quantidade,
        registro_vendas.qnt_solicitada,
        registro_vendas.fornecedor,
        produtos.nome,
         produtos.tipo,
      produtos.unidade_contagem AS unidade
      
    FROM 
        registro_vendas
    JOIN 
        produtos ON registro_vendas.produto_id = produtos.id
        
    WHERE 
        (registro_vendas.nome_venda = :saida)    
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
 
        $stmt->bindValue(':saida', $saida , PDO::PARAM_INT);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

    public function pesquisaSaidaSem(string $saida)
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
        registro_saida_sem_local.fornecedor,
      
        usuario.nome_completo AS nome_usuario,
           registro_saida_sem_local.usuario,
      
         produtos.unidade_contagem AS unidade,
                registro_saida_sem_local.data,
registro_saida_sem_local.produto_id,
   
        
    produtos.tipo,
        produtos.nome,
        registro_saida_sem_local.local
    FROM 
        registro_saida_sem_local
    JOIN 
        produtos ON registro_saida_sem_local.produto_id = produtos.id
         JOIN 
        usuario ON registro_saida_sem_local.usuario = usuario.id

    WHERE 
        (registro_saida_sem_local.nome_saida LIKE :saida)    
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);

        $stmt->bindValue(':saida', '%'.$saida. '%', PDO::PARAM_STR);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function pesquisaAno( ? string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31',?string $local ='')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        SUM(registro_vendas.quantidade) AS total,
    produtos.nome, 
    produtos.tipo
     
    FROM 
        registro_vendas
    JOIN 
   
        produtos ON registro_vendas.produto_id = produtos.id
    JOIN 
        locais ON registro_vendas.local = locais.id
    WHERE 
        (registro_vendas.data >= :de AND registro_vendas.data <= :ate AND locais.nome LIKE :local)
        GROUP BY
        produtos.nome;
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);
        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function EntradaAno( ? string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31',?string $local ='')
    {

        $conexao = Conexao::getInstancia();

        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        SUM(registro_entrada.quantidade) AS total,
    produtos.nome, produtos.tipo, lote.fornecedor
     
    FROM 
        registro_entrada
    JOIN 
   
        produtos ON registro_entrada.produto_id = produtos.id
   
    WHERE 
        (registro_entrada.data >= :de AND registro_entrada.data <= :ate)
      ;
        
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
  
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
        lote.fornecedor,
        lote.produto_id,
        produtos.nome,
        
        produtos.unidade_contagem,
        produtos.id
    FROM 
        lote
    JOIN 
        produtos ON lote.produto_id = produtos.id
        
    WHERE 
        lote.vencimento >= $data
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
            local_estoque.produto_id,
            produtos.nome,
             produtos.unidade_contagem AS unidade,
            produtos.slug
        FROM 
            local_estoque
        JOIN 
            produtos ON local_estoque.produto_id = produtos.id
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
