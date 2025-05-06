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
      return (new Busca())->buscaId('usuario', $sessao->usuarioId) ;
    }
    public static function NomeLocal() {
        $local = UsuarioControlador::usuario()->local;
        return  (new Busca())->buscaId('locais', $local); 
      }
    
     public function editar_usuario(int $id): void {
        $adm = $this->usuario()->nivel;
        if($adm == 1){
            $nivel_user = UsuarioControlador::usuario()->nivel;
            $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
            if ($nivel_user == 1) {
                $usuario = (new Busca())->buscaId('usuario', $id);

                $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
                if (isset($dados)) {
                    (new UserModelo())->atualizar($dados, $id);
                    $this->mensagem->sucesso('Usuário ' . $usuario->nome . ' editado com sucesso!')->flash();
                    Helpers::redirecionar('usuarios');
                }
                echo $this->template->renderizar('formularios/editarUsuario.html', ['titulo' => 'SGE-SEMSA Editar Usuário', 'usuario' => $usuario, 'locais' => $locais]);
            } else {
                $this->mensagem->erro('Tentativa de editar está fora de seu alcançe!')->flash();
                Helpers::redirecionar('entrada');
            }
        }
        

       
    }
    
    public function deletar_usuario(int $id): void {
       
    $nivel_user = UsuarioControlador::usuario()->nivel;
    if($nivel_user == 1){
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
