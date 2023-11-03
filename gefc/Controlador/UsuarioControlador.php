<?php

namespace gefc\Controlador;

/**
 * Description of SiteControlador
 *
 * @author Leonardo
 */
use gefc\Nucleo\Controlador;
use gefc\Nucleo\Helpers;
use gefc\Modelo\UserModelo;
use gefc\Nucleo\Sessao;
use gefc\Modelo\Busca;

class UsuarioControlador extends Controlador {
    

    public function __construct() {
        parent::__construct('templates/site/views');  
        
    }

   
     public static function usuario() {
      $sessao=  new Sessao();
      if(!$sessao->checar('usuarioId')){
          return null;
          
      }
      return (new Busca())->buscaId('usuarios', $sessao->usuarioId) ;
    }
    
     public function editar_usuario(int $id): void {
        $nivel_user = UsuarioControlador::usuario()->nivel_acesso;
    if($nivel_user > 2){
        $usuario = (new Busca())->buscaId('usuarios',$id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new UserModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Usuário '. $usuario->nome.' editado com sucesso!')->flash();
            Helpers::redirecionar('usuarios');
    }
     echo $this->template->renderizar('formularios/editarUsuario.html', [ 'titulo' => 'SGE-SEMSA Editar Usuário', 'usuario' => $usuario]);
        }
else{
    $this->mensagem->erro('Tentativa de editar está fora de seu alcançe!')->flash();
            Helpers::redirecionar('entrada');
}

       
    }
    
    public function deletar_usuario(int $id): void {
    $nivel_user = UsuarioControlador::usuario()->nivel_acesso;
    if($nivel_user > 2){
        (new UserModelo())->deletar($id);
        $this->mensagem->sucesso('Usuário deletado com sucesso')->flash();
        Helpers::redirecionar('usuarios');
       
    }
    else{
      $this->mensagem->erro('Tentativa de deletar está fora de seu alcançe')->flash();
       Helpers::redirecionar('usuarios');
       
    }
 } 
 
    
    
}
