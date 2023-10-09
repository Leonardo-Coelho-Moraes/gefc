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
        $this->css = 'p-4 flex items-center justify-between  text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
    public function erro(string $mensagem):Mensagem {
        $this->css = 'p-4 flex items-center justify-between  text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
    public function alerta(string $mensagem):Mensagem {
        $this->css = 'p-4 flex items-center justify-between   text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
    public function informa(string $mensagem):Mensagem {
        $this->css = 'p-4 flex items-center justify-between  text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400';
        $this->texto = $this->filtrar($mensagem);
        return $this;
    }
   

    public function renderizar(): string {
        return "<div class='{$this->css}' >{$this->texto}</div>";
    }

    private function filtrar(string $mensagem): string {
        return filter_var($mensagem, FILTER_SANITIZE_SPECIAL_CHARS);
    }
    public  function flash():void {
        (new Sessao())->criar('flash', $this);
    }
}
