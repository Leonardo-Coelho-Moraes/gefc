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
use PDO;
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Conexao;
use gefc\Modelo\Atualizar;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Sessao;
class UserModelo {
     
   public function cadastro(array $dados): void {
       $nivel_user = UsuarioControlador::usuario()->nivel;
         $nivel =  Helpers::validarNumero($dados['nivel']);
         $local =  Helpers::validarNumero($dados['local']);
          // Verificar o nível de acesso do usuário atual
    
    
       $query = "INSERT INTO usuario ( nome, senha, nivel, local ) VALUES ( ? , ?, ?,?)";
    $stmt = Conexao::getInstancia()->prepare($query);
    // Verificar e ajustar os valores de marca e tipo

    $stmt->execute([$dados["usuariocad"], $dados["senha"], $nivel, $local]);
}

public function login(array $dados):bool {
   $usuario = $dados['nome'];
   $senha = $dados['senha'];
    
    $query = "SELECT * FROM `usuario` WHERE nome = :usuario AND senha = :senha";
    
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindValue(':usuario', $usuario, PDO::PARAM_STR);
     $stmt->bindValue(':senha', $senha, PDO::PARAM_STR);
    $stmt->execute();
   
    $resultado = $stmt->fetch();
    if(!$resultado ){
    $mensagem = (new Mensagem)->erro('Dados não exitem ou estão incorretos!')->flash();
        return false;
    }
    (new Sessao())->criar('usuarioId', $resultado->id);
       
Helpers::redirecionar('');

      return true;
      
}


  public function atualizar(array $dados, int $id): void {
      $resultados = Helpers::validadarDados($dados);
       $dadosArray = array(
    'nome' => $resultados['usuariocad'] ,
    'senha' => $resultados['senha'],
    'nivel_acesso' => $resultados['nivelacesso']
);
  
   
    (new Atualizar())->atualizar('usuarios', "nome = ?, senha = ?, nivel_acesso = ?", $dadosArray, $id);
}




}
