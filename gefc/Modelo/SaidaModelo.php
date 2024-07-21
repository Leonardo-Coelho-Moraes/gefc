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
use gefc\Modelo\Inserir;
use gefc\Modelo\Atualizar;
class SaidaModelo {
    
    public function buscaQuantidade(string $nome): bool|object{
       
        $query= "SELECT * from produtos WHERE nome = {$nome}";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch();
        return $resultado;
    }
    
 public function venda(array $dados): void {
    // Verifique se os dados foram fornecidos corretamente
    if (empty($dados)) {
        $mensagem = (new Mensagem)->erro('Envie Algo')->flash();
        Helpers::redirecionar('venda/adicionar');
        return;
    }

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto') === 0) {
            $index = str_replace('produto', '', $key);
            $produtoId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;

            if ($quantidade > 0) {
                // Atualiza o banco de dados para o produto correspondente
                $dadosArray = array('quantidade' => $quantidade);
                (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque - ?", $dadosArray, $produtoId);
            }
        }
    }
}


 

public function vendaRegistro(array $dados): void {
    // Verifica se há dados enviados
    if (empty($dados)) {
        // Trate aqui a situação de dados vazios, se necessário
        return;
    }
    $ano = date("Y");
    $nomeVenda = 'saida' . uniqid(). $ano;
   

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto') === 0) {
            $index = str_replace('produto', '', $key);
            $produtoId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
            $qntSolic = isset($dados['qntSolic' . $index]) ? intval($dados['qntSolic' . $index]) : 0;

            if ($quantidade > 0) {


                $query = "INSERT INTO registro_saida_sem_local (nome_saida, produto_id, quantidade, qnt_solicitada, local) 
                          VALUES (:nome_venda, :produto, :quantidade, :qnt_solicitada, :local)";

                try {
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':nome_venda', $nomeVenda);
                    $stmt->bindParam(':produto', $produtoId);
                    $stmt->bindParam(':quantidade', $quantidade);
                    $stmt->bindParam(':qnt_solicitada', $qntSolic);
                    $stmt->bindParam(':local', $dados['local']);
                    $stmt->execute();
                } catch (PDOException $e) {
                    echo 'Error: ' . $e->getMessage();
                }
            }
        }
    }
}


    
public function contaRegistros():int {
       $query = "SELECT COUNT(DISTINCT nome_saida) as total FROM registro_saida_sem_local";
    $stmt = Conexao::getInstancia()->query($query);
    $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
    return $resultado->total; // Acesse a propriedade diretamente
    }
    
public function atualizar(array $dados, int $id): void {
    $produto = $dados['produto'];
    $quant_anterior = $dados['quantidade_anterior'];
    $nova_quantidade = $dados['quantidade'];
    $diferencaQuantidade = $nova_quantidade - $quant_anterior;

    $dadosArray = [   
        'quantidade' => $nova_quantidade,
      
    ];

    // Atualize o primeiro conjunto de dados
    (new Atualizar())->atualizar('registro_saida_sem_local', "quantidade = ?", $dadosArray, $id);

    if ($diferencaQuantidade > 0) {
        // A quantidade aumentou, então subtraia a diferença do estoque
        (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque - ?", [$diferencaQuantidade], $produto);
    } elseif ($diferencaQuantidade < 0) {
        // A quantidade diminuiu, então adicione a diferença ao estoque
        (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque + ?", [abs($diferencaQuantidade)], $produto);
    }
}

    
public function deletar(string $venda, int $id): void {
    // Buscar o produto na tabela 'registro_vendas' usando o ID fornecido
    $produto = (new Busca())->buscaId('registro_saida_sem_local', $id);

        // Atualizar a quantidade do produto no estoque
        (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque + ?", [$produto->quantidade], $produto->produto_id);
        
        // Deletar o registro de venda na tabela 'registro_vendas'
        $query = "DELETE FROM registro_saida_sem_local WHERE id = :id";
        
        // Preparar e executar a query de delete
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    
}
    
public function deletarTudo(string $venda): void {
    // Buscar os registros de venda na tabela 'registro_vendas' usando o nome da venda fornecido
    $vendas = (new Busca())->busca(null, null, 'registro_saida_sem_local', "nome_saida = '{$venda}'", null);
    

    // Iterar sobre cada registro de venda encontrado
    foreach($vendas as $registro) {
        // Atualizar a quantidade de estoque do produto associado ao registro de venda
        (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque + ?", [$registro['quantidade']], $registro['produto_id']);
        
        // Deletar o registro de venda na tabela 'registro_vendas'
        $query = "DELETE FROM registro_saida_sem_local WHERE id = :id";
        
        // Preparar e executar a query de delete
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':id', $registro['id'], PDO::PARAM_INT);
        $stmt->execute();
    }
}
public function atualizarLocal(array $dados, string $venda): void {
    // Definir a query de atualização
    $query = "UPDATE registro_saida_sem_local SET local = :local WHERE nome_saida = :nome_venda";
    
    // Preparar e executar a query de atualização
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':local', $dados['local'], PDO::PARAM_STR);
    $stmt->bindParam(':nome_venda', $venda, PDO::PARAM_STR);
    $stmt->execute();
}
public function adicionarAVenda(array $dados, string $saida, string $local): void {
    $dataHora = $this->pesquisa($saida, 1, 300);

    $dadosArray = array('quantidade' => $dados['quantidade']);
    (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque - ?", $dadosArray, $dados['produto']);
    // Definir a query de inserção
    $query = "INSERT INTO registro_saida_sem_local (nome_saida, produto_id, quantidade, qnt_solicitada, local, data) 
              VALUES (:nome_venda, :produto, :quantidade, :qnt_solicitada, :local, :data)";

    // Preparar a query
    $stmt = Conexao::getInstancia()->prepare($query);
    
    // Vincular os parâmetros
    $stmt->bindParam(':nome_venda', $saida);
    $stmt->bindParam(':produto', $dados['produto']);
    $stmt->bindParam(':quantidade', $dados['quantidade']);
    $stmt->bindParam(':qnt_solicitada', $dados['qntSolic']);
    $stmt->bindParam(':local', $local);
    $stmt->bindParam(':data', $dataHora[0]['data']);
    
    // Executar a query
    $stmt->execute();
}




public function pesquisa(string $buscar, ?int $pagina, ?int $limite) {
    $conexao = Conexao::getInstancia();
    // Calcular o valor de início com base na página e no limite
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    // Consulta SQL com JOIN para buscar pelo nome do produto
    $query = "
        SELECT registro_saida_sem_local.*, produtos.nome AS nome_produto
        FROM registro_saida_sem_local
        JOIN produtos ON registro_saida_sem_local.produto_id = produtos.id
        WHERE (registro_saida_sem_local.nome_saida LIKE :buscar

               OR  registro_saida_sem_local.data LIKE :buscar
               
               OR produtos.nome LIKE :buscar)  -- Buscar pelo nome do produto
        ORDER BY registro_saida_sem_local.data DESC
        LIMIT :limite OFFSET :inicio
    ";
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}
}
