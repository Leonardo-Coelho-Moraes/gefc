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
use gefc\Modelo\Busca;
use gefc\Modelo\LocalModelo;
class AdminLogin extends Controlador {
    

    public function __construct() {
        parent::__construct('templates/site/views');         
    }

   
     public function login(): void {
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            if ($this->ChecarDados($dados)){
              $usuario = (new UserModelo())->login($dados);
              if($usuario){
                  Helpers::redirecionar('login');
              }
            }
                
           
        }
        echo $this->template->renderizar('login.html', []);
    }
    private function ChecarDados(array $dados):bool {
        if(empty($dados['nome'])){
            $this->mensagem->alerta('Campo usuario é obrigatorio!')->flash();
            return false;}
        if(empty($dados['senha'])){
              $this->mensagem->alerta('Campo senha é obrigatorio!')->flash();
            return false;}
           
        return true;
    }

    public function dash(): void
    {
      
        $total_entradas = (new LocalModelo())->totalEntradas();
        $total_saidas = (new LocalModelo())->totalSaidas();
        $total_vencidos = (new LocalModelo())->totalVencidosMes();
        $total_estoque = (new LocalModelo())->totalEstoque();
        $zerados = (new LocalModelo())->totalZerados();
        $crit = (new LocalModelo())->totalCrit();


        echo $this->template->renderizar('dash.html', [ ]);
    }
    public function dashReceitas(): void
    {
      
   


        echo $this->template->renderizar('dashReceita.html',[]);
    }
    public function dashPedidos(): void
    {
      
        $total_registos_saida = (new LocalModelo())->totalRegistroSaidasEntrada();
        $maisSaidos = (new LocalModelo())->maisSaidos();
        $pedidos = (new LocalModelo())->pedidosPorLocal();
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        
        $pedidosMeses = (new LocalModelo())->pedidosPorLocalMeses('');
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['localMensal'])) {
            $pedidosMeses = (new LocalModelo())->pedidosPorLocalMeses($dados['localMensal']);
            }
                
           
        

        echo $this->template->renderizar('dashPedidos.html',['entrada_saida'=> $total_registos_saida,'maisSaidos'=> $maisSaidos, 'pedidos' =>$pedidos,'pedidosMeses'=> $pedidosMeses, 'locais' =>$locais]);
    }
    public function dashCaf(): void
    {
      
        $relacao = (new LocalModelo())->relacaoTipos();
        $produtos = (new LocalModelo())->totalProdutos();
        $zerados = (new LocalModelo())->totProCrit();
        $entradaMeses = (new LocalModelo())->EntradaMeses();
       
        

        echo $this->template->renderizar('dashCaf.html',['relacao'=> $relacao,'produtos'=> $produtos, 'zerados' =>$zerados,'entradaMeses'=> $entradaMeses]);
    }


}
