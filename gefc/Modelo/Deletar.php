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
class Deletar {
    public function deletar(int $id, string $tabela): bool
    {
        // Constrói a query para deletar
        $query = "DELETE FROM {$tabela} WHERE id = :id";

        // Obtém a conexão
        $conn = Conexao::getInstancia();

        // Prepara a query
        $stmt = $conn->prepare($query);

        // Faz o bind do parâmetro :id
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Executa a query
        return $stmt->execute();
    }


}
