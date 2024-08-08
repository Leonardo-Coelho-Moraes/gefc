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
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Conexao;
use PDO;
class LocalModelo {
    
  
    
 public function pedido(array $dados, int $local): void {
    // Verifique se os dados foram fornecidos corretamente
     $ano = date("Y_m_d");
    $pedido = 'pedido' . uniqid(). $ano;

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto') === 0) {
            $index = str_replace('produto', '', $key);
            $produtoId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;

            if ($quantidade > 0) {
                // Atualiza o banco de dados para o produto correspondente
                $query = "INSERT INTO pedidos (pedido, local, produto_id, qnt_solicitada) 
                          VALUES (:pedido, :local, :produto, :quantidade)";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':pedido',$pedido );
                    $stmt->bindParam(':local', $local);
                    $stmt->bindParam(':produto', $produtoId);
                    $stmt->bindParam(':quantidade', $quantidade);
                    
                    $stmt->execute();

                
            }
        }
    }
}
public function padrao(array $dados){
        $query = "INSERT INTO padrao_dispensa_hospital (produto_id, local, qnt) 
                          VALUES (:produto, :local, :qnt)";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':produto', $dados['produtopadrao']);
        $stmt->bindParam(':local', $dados['localpadrao']);
        $stmt->bindParam(':qnt', $dados['qntpadrao']);

        $stmt->execute();
}

public function saidaHospital(array $dados): void {
        // Verifique se os dados foram fornecidos corretamente
        $usuario = UsuarioControlador::usuario()->id;
     $ano = date("Y_m_d");
        $data = date("Y-m-d");
    $saida = 'saida' . uniqid(). $ano;

    foreach ($dados as $key => $value) {
        if (strpos($key, 'lote') === 0) {
            $index = str_replace('lote', '', $key);
            $LoteId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
            $registroId = $dados['registro' . $index];

            if ($quantidade > 0) {
                // Atualiza o banco de dados para o produto correspondente
                $query = "INSERT INTO saida_hospital (saida, local, lote_id, quantidade,data ,usuario) 
                          VALUES (:saida, :local, :lote, :quantidade,:data ,:user)";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':saida',$saida );
                    $stmt->bindParam(':local', $dados['local']);
                    $stmt->bindParam(':lote', $LoteId);
                    $stmt->bindParam(':quantidade', $quantidade);
                    $stmt->bindParam(':data', $data);
                    $stmt->bindParam(':user', $usuario);
                    $stmt->execute();
                    
                    $updateQuery = "UPDATE local_estoque SET estoque = estoque -  ? WHERE local_estoque.id = ?";
                    
                    $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                    $stmtUpdate->execute([$quantidade, $registroId]);
                    

                
            }
        }
    }
}

public function pesquisa(string $buscar) {
    $conexao = Conexao::getInstancia();
    $query = "
        SELECT pedidos.*, produtos.nome AS nome_produto
FROM pedidos
JOIN produtos ON pedidos.produto_id = produtos.id
WHERE (pedidos.pedido LIKE :buscar OR pedidos.data LIKE :buscar)
AND pedidos.exibir = 0
ORDER BY pedidos.data DESC
";
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}
public function atendido(string $pedido) {
    $sql = "UPDATE pedidos SET exibir = ? WHERE pedido = ?";
    $stmtUpdate = Conexao::getInstancia()->prepare($sql);
    $stmtUpdate->execute([1, $pedido]);
}

    public function pesquisaPedido(string $pedido)
    {
        // Certifique-se de que a classe Conexao está corretamente importada ou carregada
        $conexao = Conexao::getInstancia();

        $query = "
        SELECT
            pedidos.id AS pedido_id,
            pedidos.produto_id,
            pedidos.local,
            produtos.nome,
            pedidos.qnt_solicitada
        FROM 
            pedidos
        JOIN 
            produtos ON pedidos.produto_id = produtos.id
        WHERE 
            pedidos.pedido = :pedido
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':pedido', $pedido, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function atualizarPedido(array $dados): void
    {
        $data = date("Y-m-d");

        foreach ($dados as $key => $value) {
            if (strpos($key, 'lote') === 0) {
                $index = str_replace('lote', '', $key);
                $loteId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                $pedido = $dados['pedido' . $index];

                $query = "UPDATE pedidos SET quantidade = quantidade - :qnt, lote_id = :lote, atendido = :dat WHERE id = :id";
                $stmt = Conexao::getInstancia()->prepare($query);
                $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':lote', $loteId, PDO::PARAM_INT);
                $stmt->bindParam(':dat', $data, PDO::PARAM_STR); // corrigido para PDO::PARAM_STR
                $stmt->bindParam(':id', $pedido, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }
    public function pesquisaHospital()
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        local_estoque.id AS local_estoque_id,
        local_estoque.estoque,
        lote.lote,
        lote.cod,
        lote.id AS lote_id,
        lote.produto_id,
        lote.vencimento,
         lote.fornecedor,
        produtos.nome
    FROM 
        local_estoque
    JOIN 
        lote ON local_estoque.lote_id = lote.id
    JOIN 
        produtos ON lote.produto_id = produtos.id
    WHERE 
        local_estoque.local_id = 3
    ";

        $stmt = $conexao->prepare($query);


        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function pesquisaPadrao(string $local )
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        padrao_dispensa_hospital.produto_id,
        padrao_dispensa_hospital.local,
        padrao_dispensa_hospital.qnt,

        produtos.nome
    FROM 
        padrao_dispensa_hospital
    JOIN 
        produtos ON padrao_dispensa_hospital.produto_id = produtos.id
        WHERE (padrao_dispensa_hospital.local = :local)
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':local', $local, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function saidasHospital(?string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '', ?string $local = '', ?string $fornecedor = '')
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        saida_hospital.id AS saida_id,
        saida_hospital.saida,
        saida_hospital.local,
        saida_hospital.data,
        saida_hospital.quantidade,
        lote.preco,
        lote.vencimento,
         lote.fornecedor,
        produtos.nome,
        lote.fornecedor,
        usuario.nome AS username
    FROM 
        saida_hospital
    JOIN 
        lote ON saida_hospital.lote_id = lote.id
    JOIN 
        usuario ON saida_hospital.usuario = usuario.id
    JOIN 
        produtos ON lote.produto_id = produtos.id
         WHERE 
        (saida_hospital.data >= :de AND saida_hospital.data <= :ate AND produtos.nome LIKE :produto AND saida_hospital.local LIKE :local AND lote.fornecedor LIKE :fornecedor)
        
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);
        $stmt->bindValue(':fornecedor', '%' . $fornecedor . '%', PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function pesquisaPedir()
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        local_estoque.id AS local_estoque_id,
        produtos.nome,
        produtos.id
    FROM 
        local_estoque
    JOIN 
        lote ON local_estoque.lote_id = lote.id
    JOIN 
        produtos ON lote.produto_id = produtos.id
  
    ";

        $stmt = $conexao->prepare($query);


        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }




}
