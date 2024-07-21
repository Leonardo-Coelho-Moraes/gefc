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
class VendaModelo {
    
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

                // Verifique se o produto já existe na tabela local_estoque
                $localId = intval($dados['local']);
                $query = "SELECT estoque FROM local_estoque WHERE produto_id = ? AND local_id = ?";
                $stmt = Conexao::getInstancia()->prepare($query);
                $stmt->execute([$produtoId, $localId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    // Produto já existe no local, atualize o estoque
                    $estoqueAtual = intval($result['estoque']);
                    $novoEstoque = $estoqueAtual + $quantidade;
                    $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE produto_id = ? AND local_id = ?";
                    $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                    $stmtUpdate->execute([$novoEstoque, $produtoId, $localId]);
                } else {
                    // Produto não existe no local, insira um novo registro
                    $insertQuery = "INSERT INTO local_estoque (local_id, produto_id, estoque) VALUES (?, ?, ?)";
                    $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
                    $stmtInsert->execute([$localId, $produtoId, $quantidade]);
                }
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
                // Insere os dados no banco de dados para este produto
                $array = array(
                    'nome_venda' => $nomeVenda,
                    'produto' => $produtoId,
                    'quantidade' => $quantidade,
                    'qnt_solicitada' => $qntSolic,
                    'local'=> $dados['local'],
                   
                );

                $query = "INSERT INTO registro_vendas (nome_venda, produto_id, quantidade, qnt_solicitada, local) 
                          VALUES (:nome_venda, :produto, :quantidade, :qnt_solicitada, :local)";

                try {
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':nome_venda', $array['nome_venda']);
                    $stmt->bindParam(':produto', $array['produto']);
                    $stmt->bindParam(':quantidade', $array['quantidade']);
                    $stmt->bindParam(':qnt_solicitada', $array['qnt_solicitada']);
                    $stmt->bindParam(':local', $array['local']);
                   
                    $stmt->execute();
                } catch (PDOException $e) {
                    echo 'Error: ' . $e->getMessage();
                }
            }
        }
    }
}


    
public function contaRegistros():int {
       $query = "SELECT COUNT(DISTINCT nome_venda) as total FROM registro_vendas";
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
        'editado' => 1
    ];

    // Atualize o primeiro conjunto de dados
    (new Atualizar())->atualizar('registro_vendas', "quantidade = ?, editado = ?", $dadosArray, $id);

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
    $produto = (new Busca())->buscaId('registro_vendas', $id);

        // Atualizar a quantidade do produto no estoque
        (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque + ?", [$produto->quantidade], $produto->produto_id);
        
        // Deletar o registro de venda na tabela 'registro_vendas'
        $query = "DELETE FROM registro_vendas WHERE id = :id";
        
        // Preparar e executar a query de delete
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    
}
    
public function deletarTudo(string $venda): void {
    // Buscar os registros de venda na tabela 'registro_vendas' usando o nome da venda fornecido
    $vendas = (new Busca())->busca(null, null, 'registro_vendas', "nome_venda = '{$venda}'", null);
    

    // Iterar sobre cada registro de venda encontrado
    foreach($vendas as $registro) {
        // Atualizar a quantidade de estoque do produto associado ao registro de venda
        (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque + ?", [$registro['quantidade']], $registro['produto_id']);
        
        // Deletar o registro de venda na tabela 'registro_vendas'
        $query = "DELETE FROM registro_vendas WHERE id = :id";
        
        // Preparar e executar a query de delete
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':id', $registro['id'], PDO::PARAM_INT);
        $stmt->execute();
    }
}
public function atualizarLocal(array $dados, string $venda): void {
    // Definir a query de atualização
    $query = "UPDATE registro_vendas SET local = :local WHERE nome_venda = :nome_venda";
    
    // Preparar e executar a query de atualização
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':local', $dados['local'], PDO::PARAM_STR);
    $stmt->bindParam(':nome_venda', $venda, PDO::PARAM_STR);
    $stmt->execute();
}
public function adicionarAVenda(array $dados, string $venda, string $local): void {
    $dataHora = $this->pesquisa($venda, 1, 300);

    $dadosArray = array('quantidade' => $dados['quantidade']);
    (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque - ?", $dadosArray, $dados['produto']);
    // Definir a query de inserção
    $query = "INSERT INTO registro_vendas (nome_venda, produto_id, quantidade, qnt_solicitada, local, data, hora) 
              VALUES (:nome_venda, :produto, :quantidade, :qnt_solicitada, :local, :data, :hora)";

    // Preparar a query
    $stmt = Conexao::getInstancia()->prepare($query);
    
    // Vincular os parâmetros
    $stmt->bindParam(':nome_venda', $venda);
    $stmt->bindParam(':produto', $dados['produto']);
    $stmt->bindParam(':quantidade', $dados['quantidade']);
    $stmt->bindParam(':qnt_solicitada', $dados['qntSolic']);
    $stmt->bindParam(':local', $local);
    $stmt->bindParam(':data', $dataHora[0]['data']);
    $stmt->bindParam(':hora', $dataHora[0]['hora']);
    
    // Executar a query
    $stmt->execute();
}




public function pesquisa(string $buscar, ?int $pagina, ?int $limite) {
    $conexao = Conexao::getInstancia();
    // Calcular o valor de início com base na página e no limite
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    // Consulta SQL com JOIN para buscar pelo nome do produto
    $query = "
        SELECT registro_vendas.*, produtos.nome AS nome_produto
        FROM registro_vendas
        JOIN produtos ON registro_vendas.produto_id = produtos.id
        WHERE (registro_vendas.nome_venda LIKE :buscar
               OR registro_vendas.ano LIKE :buscar
               OR registro_vendas.data LIKE :buscar
               OR registro_vendas.hora LIKE :buscar
               OR produtos.nome LIKE :buscar)  -- Buscar pelo nome do produto
        ORDER BY registro_vendas.data DESC
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


public function pesquisaEstoque(string $buscar, int $local) {
    $conexao = Conexao::getInstancia();
    $query = "
        SELECT local_estoque.*, produtos.nome AS nome_produto
        FROM local_estoque
        JOIN produtos ON local_estoque.produto_id = produtos.id
        WHERE produtos.nome LIKE :buscar AND local_estoque.local_id = :local";
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->bindValue(':local', $local, PDO::PARAM_INT);
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}


public function atualizarEstoque(int $estoque, int $id): void {
    // Definir a query de atualização
    $query = "UPDATE local_estoque SET estoque = :estoque WHERE id = :id";
    
    // Preparar e executar a query de atualização
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':estoque', $estoque, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
}
}
