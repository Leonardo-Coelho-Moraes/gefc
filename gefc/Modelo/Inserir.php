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
use gefc\Nucleo\Conexao;
class Inserir {
 public function inserir(string $tabela,string $atualizar, array $dados ):  void {
 
 $interrogacoes = implode(', ', array_fill(0, count($dados), '?'));
    // Construa a consulta SQL com as interrogações
    $query = "INSERT INTO {$tabela} ({$atualizar}) VALUES ({$interrogacoes})";

     $stmt = Conexao::getInstancia()->prepare($query);
     $bindParams = [];

foreach ($dados as $valor) {
    $bindParams[] = $valor;
}
        

$stmt->execute($bindParams);
}


}
