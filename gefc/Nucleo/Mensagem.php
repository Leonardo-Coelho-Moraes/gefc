<?php
namespace gefc\Nucleo;
/**
 * Description of Mensagem
 * Responsavel pela renderização e filtro das mensagens do sistema.
 * @copyright (c) 2023, Leonardo Coelho Moraes
 * @author Leonardo Coelho Moraes
 */
use gefc\Nucleo\Sessao;
class Mensagem {
   
    private $texto;
    public $css;
    
    public function __toString() {
        return $this->renderizar();
    }


    public function sucesso(string $mensagem):Mensagem {
        $this->css = 'alert alert-success d-flex align-items-center justify-content-between';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
    public function erro(string $mensagem):Mensagem {
        $this->css = 'alert alert-danger d-flex align-items-center justify-content-between';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
    public function alerta(string $mensagem):Mensagem {
        $this->css = 'alert alert-warning d-flex align-items-center justify-content-between';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
    public function informa(string $mensagem):Mensagem {
        $this->css = 'alert alert-primary d-flex align-items-center justify-content-between';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
   

    public function renderizar(): string {
        
        return " <div class='content'><div class='container-fluid'><div class='{$this->css}' role='alert'>
  {$this->texto}
  <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
</div></div></div>";
       
    }

    private function filtrar(string $mensagem): string {
        return filter_var($mensagem, FILTER_SANITIZE_SPECIAL_CHARS);
    }
    public  function flash():void {
        (new Sessao())->criar('flash', $this);
    }
    
}
