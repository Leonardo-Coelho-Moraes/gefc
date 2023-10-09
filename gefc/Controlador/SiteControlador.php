<?php

namespace gefc\Controlador;

/**
 * Description of SiteControlador
 *
 * @author Leonardo
 */

use gefc\Controlador\UsuarioControlador;
use gefc\Modelo\Busca;
use gefc\Modelo\Contar;
use gefc\Modelo\EntradaModelo;

use gefc\Modelo\ProdutoModelo;
use gefc\Modelo\RegistrosModelo;
use gefc\Modelo\UserModelo;
use gefc\Nucleo\Controlador;
use gefc\Modelo\VendaModelo;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Sessao;
class SiteControlador extends Controlador {
     private $sessao;
     protected $usuario;
     protected $user;
     protected $nivel_user;


     public function __construct() {
        parent::__construct('templates/site/views');
        $this->usuario = UsuarioControlador::usuario();
        if(!$this->usuario)
        {
            $this->mensagem->erro('Faça o login para acessar o sistema!')->flash();
             Helpers::redirecionar('login');
             $limpar = (new Sessao())->limpar('usuarioId');
        }
          
        $this->nivel_user = UsuarioControlador::usuario()->nivel_acesso;
           $this->sessao = new Sessao();
           (new UsuarioControlador())->limpar_usuario();
           
           
           $this->user = UsuarioControlador::usuario()->nome;   
           
    }

    public function index(): void {
        
        if($this->usuario->nivel_acesso >3){
        echo $this->template->renderizar('index.html', [ 'titulo' => SITE_NOME.' Dashboard']);}
        else{
        Helpers::redirecionar('entrada');}
    }

    public function entrada(): void {
      
        
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
         $produtos = (new Busca())->busca(null,null,'produtos',null,'nome ASC',null);
         
          
          
          
        $registros = (new Busca())->busca($pagina, $limite,'registros', "", 'data_hora DESC');
        $totalRegistros = (new EntradaModelo())->contaRegistros();
        $totalPaginas = ceil($totalRegistros / $limite);
     
        

        echo $this->template->renderizar('entrada.html', [ 'titulo' => SITE_NOME.' Entrada', 'registros' => $registros,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'produtos'=> $produtos]);
    }

    public function entrada_adicionar(): void {
        $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new EntradaModelo())->entrada($dados);
            (new EntradaModelo())->entradaRegisto($dados);
            $this->mensagem->sucesso('Entrada Adicionada com Sucesso!')->flash();
            Helpers::redirecionar('entrada/adicionar');
        }
        echo $this->template->renderizar('formularios/adicionarentrada.html', [ 'titulo' => SITE_NOME.' Produtos', 'produtos' => $produtos]);
    }

    public function editar_entrada(int $id): void {
         if($this->nivel_user > 2){
       $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
        $registros = (new Busca())->buscaId('registros',$id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
$this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            (new RegistrosModelo())->atualizar($dados, $id);
            Helpers::redirecionar('entrada');
        }
         echo $this->template->renderizar('formularios/editarentrada.html', [ 'titulo' => SITE_NOME.' Produtos', 'registros' => $registros, 'produtos' => $produtos]);}
         else{
    
            Helpers::redirecionar('entrada');
}
    }

    public function vendas(): void {
        
          $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
       // !!!! concertar a paginação!!!!!
        $registros = (new Busca())->busca($pagina, $limite,'registro_vendas', '', '');
        $totalRegistros = (new VendaModelo())->contaRegistros();

        $totalPaginas = ceil($totalRegistros / $limite);

        $vendasAgrupadas = [];

    // Agrupa os registros pela venda (nome_venda) e data
    foreach ($registros as $registro) {
        $nomeVenda = $registro->nome_venda;
        $data = $registro->data;

        if (!isset($vendasAgrupadas[$nomeVenda])) {
            $vendasAgrupadas[$nomeVenda] = [];
        }

        if (!isset($vendasAgrupadas[$nomeVenda][$data])) {
            $vendasAgrupadas[$nomeVenda][$data] = [];
        }

        $vendasAgrupadas[$nomeVenda][$data][] = $registro;
    }

    echo $this->template->renderizar('vendas.html', [
        'titulo' => SITE_NOME.' Vendas',
        'vendasAgrupadas' => $vendasAgrupadas,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas]);
    }

    public function venda_adicionar(): void {
         

  
        $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)   ) {
            (new VendaModelo())->venda($dados);
            (new VendaModelo())->quantidadeVenda($dados);
           (new VendaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Venda Feita com Sucesso!')->flash();
            Helpers::redirecionar('venda/adicionar');
        }
          
        
        echo $this->template->renderizar('formularios/adicionarvenda.html', [ 'titulo' => SITE_NOME.' Produtos', 'produtos' => $produtos]);
    }
    public function buscarCod(): void {
        $codigoBarras = filter_input(INPUT_POST, 'busca', FILTER_SANITIZE_STRING);

    if ($codigoBarras) {
    $produtoEncontrado = (new Busca())->busca(null, null, 'produtos', "cod_barras = '$codigoBarras'", 'validade ASC', null);
   header('Content-Type: application/json');

if (!empty($produtoEncontrado) && is_array($produtoEncontrado)) {
    echo json_encode($produtoEncontrado[0]);
} else {
    echo json_encode(null); // Se nenhum resultado foi encontrado
}
    }
       
    }

    public function venda_editar(int $id): void {
          if($this->nivel_user > 2){
      $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
       
        $registros = (new Busca())->buscaId('registros',$id);
        
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {

            (new RegistrosModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos e quantidade de saídas!')->flash();
         
            Helpers::redirecionar('venda');
        }
          echo $this->template->renderizar('formularios/editarvenda.html', [ 'titulo' => SITE_NOME.' Produtos', 'registros' => $registros, 'produtos' => $produtos]);}
          else{
  
            Helpers::redirecionar('venda');
}
    }

    public function produtos(): void {
        $agora = strtotime(date('Y-m-d'));
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
         $produtos = (new Busca())->busca($pagina, $limite,'produtos', "deletado != 1 OR deletado IS NULL ",'validade ASC');
           $quantidade = (new Contar())->contar('produtos',"deletado = 0 OR deletado IS NULL");
          $edicao = (new Contar())->contar('produtos',"editado = 1");
          
          $deletado = (new Contar())->contar('produtos',"deletado = 1");
        $totalRegistros = (new ProdutoModelo())->contaRegistros();
        $totalPaginas = ceil($totalRegistros / $limite);
       

        echo $this->template->renderizar('produtos.html', [ 'titulo' => SITE_NOME.' Produtos', 'produtos' => $produtos,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas,'quantidade' =>$quantidade,'edicao' =>$edicao, 'deletado' => $deletado, 'agora'=> $agora]);
    }

    public function produto_cadastrar(): void {

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->armazenar($dados);
             $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            Helpers::redirecionar('produtos');
        }

        echo $this->template->renderizar('formularios/cadastrarproduto.html', [ 'titulo' => 'SGE-SEMSA Produtos']);
    }

    public function editar_produto(string $slug, int $id): void {
         if($this->nivel_user > 2){
        $produtos = (new Busca())->buscaSlug('produtos',$slug);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            Helpers::redirecionar('produtos');
        }


        echo $this->template->renderizar('formularios/editarproduto.html', [ 'titulo' => SITE_NOME.' Produtos', 'produto' => $produtos]);
    }
     else{
   
            Helpers::redirecionar('produtos');
}
        }

    public function deletar_produto(string $slug, int $id): void {
        
         if($this->nivel_user > 2){
        (new ProdutoModelo())->deletar($id);
        $this->mensagem->sucesso('Produto inserido na lista de deletados com sucesso!')->flash();
         Helpers::redirecionar('produtos');}
    }
    
    public function produto(string $slug, int $id): void {
       
        $produto =   (new Busca())->buscaSlug('produtos',$slug);
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
      
          $registros = (new Busca())->busca($pagina, $limite,'registros',"produto_id = $id",null);
        $totalRegistros = (new RegistrosModelo())->contaRegistrosIdProduto($id);

       
        $totalPaginas = ceil($totalRegistros / $limite);
        if (!$produto) {
            Helpers::redirecionar("erro404");
        }
        echo $this->template->renderizar('produto.html', [ 'titulo' => SITE_NOME.' ', 'registros' => $registros, 'produto' => $produto,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'total' => $totalRegistros]);
      
    }


    public function registros(): void {
       $produtos = (new Busca())->busca(null,null,'produtos',null,'nome ASC',null);
       
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
        $registros = (new Busca())->busca($pagina, $limite,'registros','','id DESC');
        
        $totalRegistros = (new RegistrosModelo())->contaRegistros();

        $totalPaginas = ceil($totalRegistros / $limite);
        $quantidadeEntradas = (new Contar())->contar('registros',"acao = 'entrada'");
         $quantidadeSaidas = (new Contar())->contar('registros',"acao = 'saida'");
         $edicao = (new Contar())->contar('registros',"editado = 1");
         $soma =  (new RegistrosModelo())->somarQuantidades('registros', null);
        echo $this->template->renderizar('registros.html', [ 'titulo' => SITE_NOME.' Registros', 'registros' => $registros, 'produtos' => $produtos, 
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'quantidadeEntradas' => $quantidadeEntradas, 'quantidadeSaidas' => $quantidadeSaidas, 'edicao' => $edicao, 'soma'=>$soma]);
    }

    public function usuarios(): void {
        if($this->usuario->nivel_acesso >1){
     $usuarios = (new Busca())->busca(null,null,'usuarios','deletado = 0','criado_em DESC',null);
         $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new UserModelo())->cadastro($dados);
            Helpers::redirecionar('usuarios');
        }

        echo $this->template->renderizar('usuarios.html', [ 'titulo' => SITE_NOME.' Usuários', 'usuarios' => $usuarios]);}
        else{ 
             
            Helpers::redirecionar('entrada');}
   
    }

   
   
    
    public function erro404(): void {
        echo $this->template->renderizar('error404.html', [ 'titulo' => 'Página não Encontrada']);
    }

    public function buscarRegistros(): void {
      $produtos = (new Busca())->busca(null,null,'produtos',null,'nome ASC',null);
        
        $buscar = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);
        if (isset($buscar)) {
            $pesquisas = (new EntradaModelo())->pesquisa($buscar);
           
        }
        echo $this->template->renderizar('buscar.html', [ 'titulo' => 'Página não Encontrada', 'pesquisas'=>$pesquisas,'produtos'=> $produtos]);
    }
    
     public function buscarProdutos(): void {
        
        $buscar = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);
        if (isset($buscar)) {
            $pesquisas = (new ProdutoModelo())->pesquisa($buscar);
           
        }
        echo $this->template->renderizar('buscarProdutos.html', [ 'titulo' => 'Página não Encontrada', 'produtos'=>$pesquisas]);
    }
    public function sair(): void {
        $this->sessao->limpar('usuarioId');
         $this->mensagem->sucesso('Você deslogou do sistema!')->flash();
        Helpers::redirecionar('login');
       
    }
}
