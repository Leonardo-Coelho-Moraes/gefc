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
use PDO;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Conexao;
class Atualizar {
 public function atualizar(string $tabela,string $atualizar, array $dados, int $id ):  void {
    $query = "UPDATE {$tabela} SET {$atualizar} WHERE id = {$id}";
    
     $stmt = Conexao::getInstancia()->prepare($query);
     $bindParams = [];

foreach ($dados as $valor) {
    $bindParams[] = $valor;
}

$stmt->execute($bindParams);
}

public function atualizarSlug(string $tabela,string $atualizar, array $dados, string $slug ):  void {
    $query = "UPDATE {$tabela} SET {$atualizar} WHERE slug = {$slug}";
    
     $stmt = Conexao::getInstancia()->prepare($query);
     $bindParams = [];

foreach ($dados as $valor) {
    $bindParams[] = $valor;
}

$stmt->execute($bindParams);
}

}
