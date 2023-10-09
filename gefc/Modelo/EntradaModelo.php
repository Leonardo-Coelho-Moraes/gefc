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
use gefc\Controlador\UsuarioControlador;
use gefc\Modelo\Inserir;
use gefc\Modelo\Atualizar;
use gefc\Nucleo\Conexao;
class EntradaModelo {
    public function entrada(array $dados): void {
      $id =  Helpers::validarNumero($dados['produto']);
      $quantidade =  Helpers::validarNumero($dados['quantidade']);
      $dadosArray = array('quantidade' => $quantidade);
      if ($quantidade <= 0) {
        $mensagem = (new Mensagem)->erro('A quantidade precisa ser maior ou igual 1')->flash();
        Helpers::redirecionar('entrada/adicionar');
      }
      (new Atualizar())->atualizar('produtos', "quantidade_estoque = quantidade_estoque + ?", $dadosArray, $id);
}
 public function entradaRegisto(array $dados): void {
       $resultados = Helpers::validadarDados($dados);
                $user = UsuarioControlador::usuario()->nome;
               $array = array('produto' => $resultados['produto'] , 'quantidade' =>   $resultados['quantidade'], 'user' => $user);
      if ($resultados['quantidade'] < 1) {
           $mensagem = (new Mensagem)->erro('A quantidade precisa ser maior ou igual 1')->flash();
           Helpers::redirecionar('entrada/adicionar');
           return;
        }
       (new Inserir())->inserir('registros', 'produto_id , quantidade, user', $array);
      
}
public function contaRegistros() {
       $query = "SELECT COUNT(*) as total FROM registros";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
        return $resultado->total; // Acesse a propriedade diretamente
    }
    public function pesquisa(string $buscar) {
        
       $query = "SELECT * FROM registros WHERE data_hora LIKE '%{$buscar}%' ORDER BY data_hora DESC";
 $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetchAll(); // Use fetch() em vez de fetchAll()
        return $resultado; 

    }


}
