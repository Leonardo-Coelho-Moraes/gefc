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
       $nivel_user = UsuarioControlador::usuario()->nivel_acesso;
         $nivel =  Helpers::validarNumero($dados['nivelacesso']);
          // Verificar o nível de acesso do usuário atual
    if ($nivel_user == 2) {
        // Se o nível de acesso do usuário atual for igual a 2,
        // limitar o nível que pode ser adicionado a 2 no máximo
        $nivel = min($nivel, 2);
    } elseif ($nivel_user > 2) {
        // Se o nível de acesso do usuário atual for maior que 2,
        // permitir adicionar até 3 no máximo
        $nivel = min($nivel, 3);
    }
    
       $query = "INSERT INTO usuarios ( `nome`, `senha`, `nivel_acesso` ) VALUES ( ? , ?, ?)";
    $stmt = Conexao::getInstancia()->prepare($query);
    // Verificar e ajustar os valores de marca e tipo

    $stmt->execute([$dados["usuariocad"], $dados["senha"], $nivel]);
}

public function login(array $dados):bool {
   $usuario = $dados['usuario'];
   $senha = $dados['senha'];
    
    $query = "SELECT * FROM `usuarios` WHERE nome = :usuario AND senha = :senha";
    
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
    
  
    $this->hora($resultado->id);
Helpers::redirecionar('');

      return true;
      
}
private function hora($id):void {
    
  $data = date('Y-m-d H:i:s');

    $query = "UPDATE usuarios SET ultimo_acesso = ? WHERE id = ?";
    $stmt = Conexao::getInstancia()->prepare($query);
    $result = $stmt->execute([$data, $id]);

   
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
public function deletar(int $id): void {
    $deletado = 1;
    $data = date('Y-m-d H:i:s');
    $query = "UPDATE usuarios SET deletado = ?, deletado_em = ? WHERE id = ?";
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->execute([$deletado, $data, $id]);
}



}
