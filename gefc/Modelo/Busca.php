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
 public function busca(?int $pagina = null,?int $limite = null, string $tabela, ?string $termo = null, ?string $ordem = null, ?string $limit = null): array {
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    $termoClause = ($termo ? "WHERE $termo" : '');
    $ordemClause = ($ordem ? "ORDER BY $ordem" : '');
    $limitClause = ($limit !== null) ? "LIMIT $limit" : (($pagina !== null && $limite !== null) ? "LIMIT $inicio, $limite" : '');

    $query = "SELECT * FROM {$tabela} {$termoClause} {$ordemClause} {$limitClause}";
    $stmt = Conexao::getInstancia()->query($query);
    $resultado = $stmt->fetchAll();

    return $resultado;
}
public function buscaId( string $tabela, int $id): bool|object{

        $query= "SELECT * from {$tabela} WHERE id = {$id}";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch();
        
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
