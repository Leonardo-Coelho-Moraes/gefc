<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace gefc\Modelo;

/**
 * Description of Busca
 *
 * @author Leonardo
 */
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use \PDO;
use gefc\Nucleo\Conexao;
class Busca {
public function busca(?int $pagina = null, ?int $limite = null, string $tabela, ?string $condicao = null, ?string $ordem = null): array {
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    $condicaoClause = ($condicao ? "WHERE $condicao" : '');
    $ordemClause = ($ordem ? "ORDER BY $ordem" : '');
    $limitClause = ($limite !== null) ? "LIMIT $limite OFFSET $inicio" : '';

    $query = "SELECT * FROM {$tabela} {$condicaoClause} {$ordemClause} {$limitClause}";

    $conn = Conexao::getInstancia();
    $stmt = $conn->query($query);
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $resultado;
}
public function buscaLimitada(?int $pagina = null, ?int $limite = null,string $itens, string $tabela, ?string $condicao = null, ?string $ordem = null): array {
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    $condicaoClause = ($condicao ? "WHERE $condicao" : '');
    $ordemClause = ($ordem ? "ORDER BY $ordem" : '');
    $limitClause = ($limite !== null) ? "LIMIT $limite OFFSET $inicio" : '';

    $query = "SELECT {$itens} FROM {$tabela} {$condicaoClause} {$ordemClause} {$limitClause}";

    $conn = Conexao::getInstancia();
    $stmt = $conn->query($query);
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $resultado;
}
public function buscarVenda(string $nome): array {
    $conn = Conexao::getInstancia();

    // Use um prepared statement para evitar a injeção de SQL
    $query = "SELECT * FROM registro_vendas WHERE nome_venda = :nome";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    $stmt->execute();

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $resultado;
}


public function buscaId(string $tabela, int $id): bool|object {
    $query = "SELECT * FROM {$tabela} WHERE id = :id";
    
    $conn = Conexao::getInstancia();
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = $stmt->fetch(PDO::FETCH_OBJ);
    
    return $resultado;
}

    public function buscaProdutoVenda(string $coluna, string $nome): array
{
    $query = "SELECT * FROM produtos WHERE $coluna LIKE :nome ORDER BY validade ASC LIMIT 50";

    $conn = Conexao::getInstancia();
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':nome', '%' . $nome . '%', PDO::PARAM_STR);
    $stmt->execute();

    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}


    
public function buscaSlug(string $tabela, string $slug): bool|object{
    // Use uma variável para armazenar o valor do slug com aspas simples
    $sol = '%' . $slug . '%'; // Adicione % como curinga, se necessário

    // Use uma consulta preparada para evitar SQL injection
    $query = "SELECT * FROM {$tabela} WHERE slug LIKE :sol";

    // Prepare a consulta
    $stmt = Conexao::getInstancia()->prepare($query);

    // Associe o valor do parâmetro :sol ao valor da variável $sol
    $stmt->bindParam(':sol', $sol, PDO::PARAM_STR);

    // Execute a consulta preparada
    $stmt->execute();

    // Obtenha o resultado como um objeto ou falso em caso de falha
    $resultado = $stmt->fetch();

    return $resultado;
}

}
