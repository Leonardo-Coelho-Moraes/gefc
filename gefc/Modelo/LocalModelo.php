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
class LocalModelo {
    
  
    
 public function pedido(array $dados, int $local): void {
    // Verifique se os dados foram fornecidos corretamente
     $ano = date("Y_m_d");
    $pedido = 'pedido' . uniqid(). $ano;
    if (empty($dados)) {
        $mensagem = (new Mensagem)->erro('Envie Algo')->flash();
        Helpers::redirecionar('pedido/fazer');
        return;
    }

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto') === 0) {
            $index = str_replace('produto', '', $key);
            $produtoId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;

            if ($quantidade > 0) {
                // Atualiza o banco de dados para o produto correspondente
                $query = "INSERT INTO pedidos (pedido, local, produto_id, quantidade) 
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
    $data = date("Y-m-d");
    $sql = "UPDATE pedidos SET exibir = ?, atendido = ? WHERE pedido = ?";
    $stmtUpdate = Conexao::getInstancia()->prepare($sql);
    $stmtUpdate->execute([1, $data, $pedido]);
}


}
