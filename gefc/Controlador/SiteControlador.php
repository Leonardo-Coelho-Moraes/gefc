<?php

namespace gefc\Controlador;

/**
 * Description of SiteControlador
 *
 * @author Leonardo
 */

use gefc\Modelo\Busca;
use gefc\Modelo\Contar;
use gefc\Modelo\UserModelo;
use gefc\Modelo\LocalModelo;
use gefc\Controlador\UsuarioControlador;
use gefc\Modelo\EntradaModelo;
use gefc\Modelo\Inserir;
use gefc\Modelo\ProdutoModelo;
use gefc\Modelo\RegistrosModelo;
use gefc\Modelo\SaidaModelo;
use gefc\Modelo\Atualizar;
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
          
        $this->nivel_user = UsuarioControlador::usuario()->nivel;
           $this->sessao = new Sessao();
           $this->user = UsuarioControlador::usuario()->nome;    
    }
     private function verificarPermissaoAdmin() {
        if ($this->nivel_user != 1) {
            
            Helpers::redirecionar('local');
        }
    }
public function proibido(): void {
        echo $this->template->renderizar('proibição.html', [ 'titulo' => SITE_NOME]);
    }
    public function index(): void {
        
          Helpers::redirecionar('entrada') ;  
        
   }
  
    public function entrada(): void {
        $this->verificarPermissaoAdmin();
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 40;
        $editado = (new Contar())->contar('registro_entrada', 'editado = 1');
        $registrosTotais = (new Contar())->contar('registro_entrada');
         $produtos = (new Busca())->buscaLimitada(null,null,'id,nome,slug,editado,deletado, fornecedor','produtos',null,'nome ASC',null);
        $registros = (new EntradaModelo())->pesquisa('', $pagina, $limite);
if (isset($_POST['pesquisaEntrada'])) {
    $pesquisa = $_POST['pesquisaEntrada'];
    $registros = (new EntradaModelo())->pesquisa($pesquisa, $pagina, $limite);
    if(empty($registros)){
         $this->mensagem->erro($pesquisa ." não encontrado(a), abaixo a lista de todos os registros!Observe que pode por ano, data: 2023-10-16 ou COD. do produto!")->flash();
         $registros = (new EntradaModelo())->pesquisa('', $pagina, $limite);
    }
}
        $totalRegistros = (new Contar())->contar('registro_entrada');
        $totalPaginas = ceil($totalRegistros / $limite);
        echo $this->template->renderizar('entrada.html', [ 'titulo' => SITE_NOME.' Entrada', 'registros' => $registros,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'produtos'=> $produtos, 'editado' => $editado, 'total' => $registrosTotais]);
    }

    public function entrada_adicionar(): void {
        $this->verificarPermissaoAdmin();
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
        $this->verificarPermissaoAdmin();
        $registros = (new Busca())->buscaId('registro_entrada',$id);
         $produtos = (new Busca())->buscaId('produtos', $registros->produto_id);
      
        
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
$this->mensagem->alerta('Registro Editado com Sucesso. Corrija a quantidade de estoque do produto pra mais ou para menos!')->flash();
            (new EntradaModelo())->atualizar($dados, $id);
            Helpers::redirecionar('entrada');
        }
         echo $this->template->renderizar('formularios/editarentrada.html', [ 'titulo' => SITE_NOME.' Produtos', 'registros' => $registros, 'produtos' => $produtos]);
    }

    public function vendas(): void {
        $this->verificarPermissaoAdmin();

          $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 40;
  
    // Caso não tenha sido enviado um valor de pesquisa
    $registros = (new VendaModelo())->pesquisa('',$pagina,$limite);   

        $totalRegistros = (new VendaModelo())->contaRegistros();

        $totalPaginas = ceil($totalRegistros / $limite);
       
if (isset($_POST['pesquisaVendas'])) {
    $pesquisa = $_POST['pesquisaVendas'];
    $registros = (new VendaModelo())->pesquisa($pesquisa, $pagina, $limite);
    if(empty($registros)){
          $this->mensagem->erro($pesquisa ." não encontrado(a), abaixo a lista de todos os registros!Observe que pode por ano, data: 2023-10-16 ou nome da venda!")->flash();
         $registros = (new VendaModelo())->pesquisa('',$pagina,$limite);   
    }
}
        
        $vendasAgrupadas = [];

    // Agrupa os registros pela venda (nome_venda) e data
    foreach ($registros as $registro) {
           $nomeVenda = $registro['nome_venda'];
           
    $data = $registro['data']." ". $registro['hora'];
  

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
    
    public function venda(string $nome): void {
       $this->verificarPermissaoAdmin();
         $produtos = (new Busca())->buscaLimitada(null,null,'id,nome,slug,fornecedor','produtos',null,'id ASC',null);
         //$registros = (new Busca())->buscarVenda( $nome);
         $registros = (new Busca())->busca(null,null,'registro_vendas',"nome_venda =  '{$nome}' ",null,null);
         $local = $registros[0]['local'];
    
        echo $this->template->renderizar('venda.html', [ 'titulo' => SITE_NOME.' '.$nome, 'registros' => $registros,'produtos'=> $produtos, 'venda' => $nome, 'local' => $local]);
    }
    
    public function venda_adicionar(): void {
$this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null,null,'locais','','',null);
        $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)   ) {
            (new VendaModelo())->venda($dados);
           (new VendaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Saída Feita com Sucesso!')->flash();
            Helpers::redirecionar('venda/adicionar');
        }
          
        
        echo $this->template->renderizar('formularios/adicionarvenda.html', [ 'titulo' => SITE_NOME.' Produtos', 'produtos' => $produtos,'locais' => $locais]);
    }
    
    public function editar_venda(string $venda, int $id): void {
        $this->verificarPermissaoAdmin();
        $registros = (new Busca())->buscaId('registro_vendas',"$id");
        $produto = $registros->produto_id;
        $produtos = (new Busca())->buscaId('produtos', "$produto");
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Quantidade de Saída e Estoque do Produto Editados!')->flash();
            Helpers::redirecionar('vendas/'.$venda);
        }
          echo $this->template->renderizar('formularios/editarvenda.html', [ 'titulo' => SITE_NOME.' Produtos', 'registros' => $registros, 'produtos' => $produtos, 'venda' => $venda]);
    }
    
    public function editarLocal(string $venda): void {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null,null,'locais','','',null);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->atualizarLocal($dados, $venda);
            $this->mensagem->sucesso('Local Editado com Sucesso.')->flash();
            Helpers::redirecionar('vendas/'.$venda);
        }
          echo $this->template->renderizar('formularios/editarLocal.html', ['venda' => $venda, 'locais' => $locais]);
    }
    
    public function adicionarAVenda(string $venda, string $local): void {
        $this->verificarPermissaoAdmin();
         $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
         
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->adicionarAVenda($dados, $venda, $local);
            $this->mensagem->sucesso('Produto adiconado a venda com Sucesso.')->flash();
            Helpers::redirecionar('vendas/adicionar/'.$venda.'/'.$local);
        }
          echo $this->template->renderizar('formularios/adicionarAVenda.html', ['venda' => $venda, 'produtos' => $produtos, 'local' => $local]);
    }
    
    public function deletar_venda(string $venda, int $id): void {
         $this->verificarPermissaoAdmin();
        
        (new VendaModelo())->deletar($venda,$id);
        $this->mensagem->sucesso('Saída inserida na lista de deletados com sucesso!')->flash();
        Helpers::redirecionar('vendas');

    }
    
    public function deletarVendaInteira(string $venda): void {  
         $this->verificarPermissaoAdmin();
       (new VendaModelo())->deletarTudo($venda);
       $this->mensagem->sucesso('Saída deletada completamente e produtos readicionados ao estoque!')->flash();
       Helpers::redirecionar('vendas');


    }
    
    public function registroVendas(): void {
    $this->verificarPermissaoAdmin();
    $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 40;
        $editado = (new Contar())->contar('registro_vendas', 'editado = 1');
        $registrosTotais = (new Contar())->contar('registro_vendas','editado != 1');
       
         $produtos = (new Busca())->busca(null,null,'produtos',"",'nome ASC',null);
        $registros = (new VendaModelo())->pesquisa('', $pagina, $limite);
if (isset($_POST['pesquisaRegistroVenda'])) {
    $pesquisa = $_POST['pesquisaRegistroVenda'];
    $registros = (new VendaModelo())->pesquisa($pesquisa, $pagina, $limite);
    if(empty($registros)){
         $this->mensagem->erro($pesquisa ." não encontrado(a), abaixo a lista de todos os registros!Observe que pode por ano, data: 2023-10-16 ou COD. do produto, não busca nome do produto!")->flash();
         $registros = (new VendaModelo())->pesquisa('', $pagina, $limite);
    }
}
        $totalPaginas = ceil($registrosTotais / $limite);
        echo $this->template->renderizar('registroVendas.html', [ 'titulo' => SITE_NOME.' Registro Vendas', 'registros' => $registros,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'produtos'=> $produtos, 'editado' => $editado, 'total' => $registrosTotais]);
    }
      
    public function saidasFora(): void {
$this->verificarPermissaoAdmin();
          $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 40;
  
    // Caso não tenha sido enviado um valor de pesquisa
    $registros = (new SaidaModelo())->pesquisa('',$pagina,$limite);   

        $totalRegistros = (new SaidaModelo())->contaRegistros();

        $totalPaginas = ceil($totalRegistros / $limite);
       
if (isset($_POST['pesquisaVendas'])) {
    $pesquisa = $_POST['pesquisaVendas'];
    $registros = (new SaidaModelo())->pesquisa($pesquisa, $pagina, $limite);
    if(empty($registros)){
          $this->mensagem->erro($pesquisa ." não encontrado(a), abaixo a lista de todos os registros!Observe que pode por ano, data: 2023-10-16 ou nome da venda!")->flash();
         $registros = (new SaidaModelo())->pesquisa('',$pagina,$limite);   
    }
}
        
        $vendasAgrupadas = [];

    // Agrupa os registros pela venda (nome_venda) e data
    foreach ($registros as $registro) {
           $nomeVenda = $registro['nome_saida'];
           
    $data = $registro['data'];
  

        if (!isset($vendasAgrupadas[$nomeVenda])) {
            $vendasAgrupadas[$nomeVenda] = [];
        }

        if (!isset($vendasAgrupadas[$nomeVenda][$data])) {
            $vendasAgrupadas[$nomeVenda][$data] = [];
        }

        $vendasAgrupadas[$nomeVenda][$data][] = $registro;
    }

    echo $this->template->renderizar('saidasFora.html', [
        'titulo' => SITE_NOME.' Saídas Fora',
        'vendasAgrupadas' => $vendasAgrupadas,
        'paginaAtual' => $pagina,
        'totalPaginas' => $totalPaginas]);
    }
    
    public function saidaFora(string $nome): void {
       $this->verificarPermissaoAdmin();
         $produtos = (new Busca())->buscaLimitada(null,null,'id,nome,slug,fornecedor','produtos',null,'id ASC',null);
         //$registros = (new Busca())->buscarVenda( $nome);
         $registros = (new Busca())->busca(null,null,'registro_saida_sem_local',"nome_saida =  '{$nome}' ",null,null);
         $local = $registros[0]['local'];
    
        echo $this->template->renderizar('saidaFora.html', [ 'titulo' => SITE_NOME.' '.$nome, 'registros' => $registros,'produtos'=> $produtos, 'saida' => $nome, 'local' => $local]);
    }
    
    public function registroSaidaFora(): void {
     $this->verificarPermissaoAdmin();
    $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 40;
        
       $registrosTotais = (new Contar())->contar('registro_vendas','');
         $produtos = (new Busca())->busca(null,null,'produtos',"",'nome ASC',null);
        $registros = (new SaidaModelo())->pesquisa('', $pagina, $limite);
if (isset($_POST['pesquisaRegistroVenda'])) {
    $pesquisa = $_POST['pesquisaRegistroVenda'];
    $registros = (new SaidaModelo())->pesquisa($pesquisa, $pagina, $limite);
    if(empty($registros)){
         $this->mensagem->erro($pesquisa ." não encontrado(a), abaixo a lista de todos os registros!Observe que pode por ano, data: 2023-10-16 ou COD. do produto, não busca nome do produto!")->flash();
         $registros = (new SaidaModelo())->pesquisa('', $pagina, $limite);
    }
}
        $totalPaginas = ceil($registrosTotais / $limite);
        echo $this->template->renderizar('registroSaidaFora.html', [ 'titulo' => SITE_NOME.' Registro Saida Fora', 'registros' => $registros,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'produtos'=> $produtos]);
    }
    
    public function saidaForaAdicionar(): void {
         $this->verificarPermissaoAdmin();
         $locais = (new Busca())->busca(null, null, 'locais', null);
        $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
        
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)   ) {
            (new SaidaModelo())->venda($dados);
           (new SaidaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Saída Fora Feita com Sucesso!')->flash();
            Helpers::redirecionar('saida/fora/adicionar');
        }
          
        
        echo $this->template->renderizar('formularios/adicionarsaidafora.html', [ 'titulo' => SITE_NOME.' Saída Fora Adicionar', 'produtos' => $produtos, 'locais' => $locais]);
    }
    
    public function deletarSaidaForaInteira(string $venda): void {  
         $this->verificarPermissaoAdmin();
       (new SaidaModelo())->deletarTudo($venda);
       $this->mensagem->sucesso('Saída Fora deletada completamente e produtos readicionados ao estoque!')->flash();
       Helpers::redirecionar('saidas/fora/');


    }
    
    public function editarLocalFora(string $saida): void {
        $this->verificarPermissaoAdmin();
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new SaidaModelo())->atualizarLocal($dados, $saida);
            $this->mensagem->sucesso('Local Fora Editado com Sucesso.')->flash();
            Helpers::redirecionar('saidas/fora/'.$saida);
        }
          echo $this->template->renderizar('formularios/editarLocalFora.html', ['saida' => $saida]);
    }
    
    public function editarSaidaFora(string $saida, int $id): void {
        $this->verificarPermissaoAdmin();
        $registros = (new Busca())->buscaId('registro_saida_sem_local',"$id");
        $produto = $registros->produto_id;
        $produtos = (new Busca())->buscaId('produtos', "$produto");
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new SaidaModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Quantidade de Saída e Estoque do Produto Editados!')->flash();
            Helpers::redirecionar('saidas/fora/'.$saida);
        }
          echo $this->template->renderizar('formularios/editarSaidaFora.html', [ 'titulo' => SITE_NOME.'Editar Saída Fora', 'registros' => $registros, 'produtos' => $produtos, 'saida' => $saida]);
    }
    
    public function adicionarASaida(string $saida, string $local): void {
         $this->verificarPermissaoAdmin();
        $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
         
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new SaidaModelo())->adicionarAVenda($dados, $saida, $local);
            $this->mensagem->sucesso('Produto adiconado a venda com Sucesso.')->flash();
            Helpers::redirecionar('saidas/fora/adicionar/'.$saida.'/'.$local);
        }
          echo $this->template->renderizar('formularios/adicionarASaida.html', ['saida' => $saida, 'produtos' => $produtos, 'local' => $local]);
    }
    
    public function deletarSaida(string $saida, int $id): void {
        $this->verificarPermissaoAdmin();
        (new SaidaModelo())->deletar($saida,$id);
        $this->mensagem->sucesso('Saída inserida na lista de deletados com sucesso!')->flash();
        Helpers::redirecionar('saidas/fora');

    }
    
    public function produtos(): void {
         $this->verificarPermissaoAdmin(); 
        $agora = strtotime(date('Y-m-d'));
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 40;
        $produtos = (new ProdutoModelo())->pesquisa('', $pagina, $limite, null);
        $quantidade = (new Contar())->contar('produtos',"deletado = 0 OR deletado IS NULL");
        $edicao = (new Contar())->contar('produtos',"editado = 1");
        $deletado = (new Contar())->contar('produtos',"deletado = 1");
        $totalRegistros = (new Contar())->contar('produtos');
        $totalPaginas = ceil($totalRegistros / $limite);
       
       $pesquisa = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);
       
        if (isset($pesquisa)) {
            $produtos = (new ProdutoModelo())->pesquisa($pesquisa,$pagina,$limite,null);   
             if(empty($produtos)){
          $this->mensagem->erro($pesquisa ." não encontrado(a), abaixo a lista de todos os registros!")->flash();
         $produtos = (new ProdutoModelo())->pesquisa('', $pagina, $limite,null);
    }
        }
        echo $this->template->renderizar('produtos.html', [ 'titulo' => SITE_NOME.' Produtos', 'produtos' => $produtos,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas,'quantidade' =>$quantidade,'edicao' =>$edicao, 'deletado' => $deletado, 'agora'=> $agora]);
    }
    
    public function produto_cadastrar(): void {
        $this->verificarPermissaoAdmin();
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->armazenar($dados);
             $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            Helpers::redirecionar('produtos');
        }

        echo $this->template->renderizar('formularios/cadastrarproduto.html', [ 'titulo' => 'SGE-SEMSA Produtos']);
    }

    public function editar_produto(string $slug, int $id): void {
         
       $this->verificarPermissaoAdmin();
        $produtos = (new Busca())->buscaSlug('produtos',$slug);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            Helpers::redirecionar('produtos');
        }


        echo $this->template->renderizar('formularios/editarproduto.html', [ 'titulo' => SITE_NOME.' Produtos', 'produto' => $produtos]);
    
        }

    public function deletar_produto(string $slug, int $id): void {
        $this->verificarPermissaoAdmin();
        (new ProdutoModelo())->deletar($id);
        $this->mensagem->sucesso('Produto inserido na lista de deletados com sucesso!')->flash();
         Helpers::redirecionar('produtos');
    }
    
    public function produto(string $slug, int $id): void {
       $this->verificarPermissaoAdmin();
        $produto =   (new Busca())->buscaSlug('produtos',$slug);
        
     
        if (!$produto) {
            Helpers::redirecionar("erro404");
        }
        echo $this->template->renderizar('produto.html', [ 'titulo' => SITE_NOME.' ','produto' => $produto]);
      
    
    }
    public function estoqueLocais(): void {
        $this->verificarPermissaoAdmin();
         $locais = (new Busca())->busca(null, null, 'locais', null);
         
         $estoque = [];  // Inicializa a variável $estoque
    $produtos = [];
    if(isset($_POST['local'])){
    $pesquisa = $_POST['local'];
    $estoque = (new Busca())->busca(null,null,'local_estoque',"local_id = $pesquisa",'',null);
    $produtos = (new Busca())->buscaLimitada(null,null,'id,nome,slug,editado,deletado, fornecedor','produtos',null,'nome ASC',null);
    
    }
        echo $this->template->renderizar('estoqueLocais.html', [ 'titulo' => SITE_NOME.' Estoque', 'produtos'=> $produtos, 'locais' => $locais, 'estoque' => $estoque]);
    }
     public function local(): void {
    $local = UsuarioControlador::usuario()->local;
    $estoque = [];
    $nome = null;
   
    if ($local == 0) {
        $estoque = (new Busca())->busca(null, null, 'local_estoque', "", '', null);
    } else {
        $nome = (new Busca())->buscaId('locais', $local);
        
     
        $pesquisa = filter_input(INPUT_POST, 'pesquisaProduto', FILTER_SANITIZE_STRING);
        
        if ($pesquisa) {
            $estoque = (new VendaModelo())->pesquisaEstoque($pesquisa, $local);
            
            if (empty($estoque)) {
                $this->mensagem->erro("$pesquisa não encontrado(a), abaixo a lista de todos os registros!")->flash();
            }
        } else {
        
            $estoque = (new VendaModelo())->pesquisaEstoque('', $local);
        }
    }
    

    $produtos = (new Busca())->buscaLimitada(null, null, 'id,nome,slug,editado,deletado,fornecedor', 'produtos', null, 'nome ASC', null);

    echo $this->template->renderizar('local.html', [
        'titulo' => SITE_NOME . ' Estoque '. $nome->nome, 'registros' => $estoque, 'produtos' => $produtos, 'nome' => $nome, 'local' => $local
    ]);
}  
    public function editarEstoqueLocal(int $produto): void {
       
       $local = UsuarioControlador::usuario()->local;
       $nome = (new Busca())->buscaId('locais', $local);
       
         $registro = (new Busca())->buscaId('local_estoque', $produto);
         
         $produtos = (new Busca())->buscaId('produtos',$registro->produto_id);
      
       $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $estoque = $dados['estoque'];
            (new VendaModelo())->atualizarEstoque($estoque, $registro->id);
            $this->mensagem->sucesso("Estoque do Produto $produtos->nome Editado com Sucesso de $registro->estoque para $estoque produtos!")->flash();
            Helpers::redirecionar('local');
        }
         echo $this->template->renderizar('formularios/editarEstoqueLocal.html', [ 'titulo' => SITE_NOME.' Editar Estoque', 'produto' => $produtos, 'registro' => $registro, 'local' => $local, 'nome'=> $nome]);
    }
    
     public function pedidoFazer(): void {

        $local = UsuarioControlador::usuario()->local;
        $produtos = (new Busca())->busca(null,null,'produtos',"deletado != 1 OR deletado IS NULL ",'nome ASC',null);
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)   ) {
            (new LocalModelo())->pedido($dados, $local);
          
            $this->mensagem->sucesso(' pedido com Sucesso!')->flash();
            Helpers::redirecionar('pedido/fazer');
        }
          
        
        echo $this->template->renderizar('formularios/fazerPedido.html', [ 'titulo' => SITE_NOME.'Pedido', 'produtos' => $produtos]);
    }
    
    public function pedidos(): void {
        $this->verificarPermissaoAdmin();
         $locais = (new Busca())->busca(null,null,'locais','','',null);
    $registros = (new LocalModelo())->pesquisa('');   
      
if (isset($_POST['pesquisaPedidos'])) {
    $pesquisa = $_POST['pesquisaPedidos'];
    $registros = (new LocalModelo())->pesquisa($pesquisa);
    if(empty($registros)){
          $this->mensagem->erro($pesquisa ."pedido não econtrado!")->flash();
         $registros = (new LocalModelo())->pesquisa('');   
    }
}
        
        $vendasAgrupadas = [];

    // Agrupa os registros pela venda (nome_venda) e data
    foreach ($registros as $registro) {
           $nomeVenda = $registro['pedido'];
           
    $data = $registro['data'];
  

        if (!isset($vendasAgrupadas[$nomeVenda])) {
            $vendasAgrupadas[$nomeVenda] = [];
        }

        if (!isset($vendasAgrupadas[$nomeVenda][$data])) {
            $vendasAgrupadas[$nomeVenda][$data] = [];
        }

        $vendasAgrupadas[$nomeVenda][$data][] = $registro;
    }

    echo $this->template->renderizar('pedidos.html', ['titulo' => SITE_NOME.' Pedidos','vendasAgrupadas' => $vendasAgrupadas, 'locais' => $locais]);
    }
    
     public function pedidoAtender(string $pedido): void {
         $this->verificarPermissaoAdmin();
         $pedido = (new Busca())->busca(null, null, 'pedidos', "pedido = '{$pedido}'", '', null);
       $produtos = (new Busca())->buscaLimitada(null, null, 'id,nome', 'produtos', null, null, null);
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)   ) {
            (new VendaModelo())->venda($dados);
           (new VendaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Pedido Atendido')->flash();
            Helpers::redirecionar('pedidos/');
        }
          
        
        echo $this->template->renderizar('formularios/pedido.html', [ 'titulo' => SITE_NOME.'Pedido', 'produtos' => $produtos,'pedido'=> $pedido]);
    }
    public function pedidoAtendido(string $pedido): void {
        $this->verificarPermissaoAdmin();
            (new LocalModelo())->atendido($pedido);
            $this->mensagem->sucesso('Pedido Atendido')->flash();
            Helpers::redirecionar('pedidos/');
     
    }
    
    public function usuarios(): void {
        $this->verificarPermissaoAdmin();
     $usuarios = (new Busca())->busca(null,null,'usuario','','',null);
         $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
         $locais = (new Busca())->busca(null,null,'locais','','',null);
        if (isset($dados)) {
            (new UserModelo())->cadastro($dados);
            Helpers::redirecionar('usuarios');
        }

        echo $this->template->renderizar('usuarios.html', [ 'titulo' => SITE_NOME.' Usuários', 'usuarios' => $usuarios, 'locais' => $locais]);
       
   
    }
    public function erro404(): void {
        echo $this->template->renderizar('error404.html', [ 'titulo' => 'Página não Encontrada']);
    }
    public function sair(): void {
        $this->sessao->limpar('usuarioId');
        $this->mensagem->sucesso('Você deslogou do sistema!')->flash();
        Helpers::redirecionar('login');
       
    }
   
}
