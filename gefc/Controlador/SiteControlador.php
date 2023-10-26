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
        $editado = (new Contar())->contar('registro_entrada', 'editado = 1');
        $registrosTotais = (new Contar())->contar('registro_entrada');
         $produtos = (new Busca())->busca(null,null,'produtos',null,'nome ASC',null);
        $registros = (new Busca())->busca($pagina, $limite,'registro_entrada', "", 'data_hora DESC');
        $totalRegistros = (new EntradaModelo())->contaRegistros();
        $totalPaginas = ceil($totalRegistros / $limite);
     
        

        echo $this->template->renderizar('entrada.html', [ 'titulo' => SITE_NOME.' Entrada', 'registros' => $registros,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'produtos'=> $produtos, 'editado' => $editado, 'total' => $registrosTotais]);
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
        $registros = (new Busca())->buscaId('registro_entrada',$id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
$this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            (new EntradaModelo())->atualizar($dados, $id);
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
        $registros = (new Busca())->busca($pagina, $limite,'registro_vendas', null, null);
        $totalRegistros = (new VendaModelo())->contaRegistros();

        $totalPaginas = ceil($totalRegistros / $limite);

        $vendasAgrupadas = [];

    // Agrupa os registros pela venda (nome_venda) e data
    foreach ($registros as $registro) {
           $nomeVenda = $registro['nome_venda'];
    $data = $registro['data'];

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
       
         $produtos = (new Busca())->busca(null,null,'produtos',null,'nome ASC',null);
         //$registros = (new Busca())->buscarVenda( $nome);
         $registros = (new Busca())->busca(null,null,'registro_vendas',"nome_venda =  '{$nome}' AND deletado != 1 OR deletado IS NULL ",null,null);
       $desconto = $registros[0]['desconto_total_venda'];
       $valorVenda = $registros[0]['valor_venda'];
       $vendedor = $registros[0]['usuario'];
        echo $this->template->renderizar('venda.html', [ 'titulo' => SITE_NOME.' '.$nome, 'registros' => $registros,
           'produtos'=> $produtos, 'venda' => $nome, 'desconto' => $desconto,'valorVenda' => $valorVenda, 'vendedor' => $vendedor]);
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
        $codigoBarras = filter_input(INPUT_POST, 'cod', FILTER_DEFAULT);

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
     public function buscarId(): void {
    $id = filter_input(INPUT_POST, 'produto', FILTER_DEFAULT);

if ($id === null) {
    // Nenhum valor 'produto' foi enviado via POST, verifique se há um valor 'pesquisa'
    $id = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);

    if ($id !== null) {
        if (isset($id)) {
        $produtos = (new Busca())->buscaProdutoVenda('nome', $id);

        foreach ($produtos as $produto) {
            $preco = $produto['preco']; // Corrigido para acessar a propriedade 'preco' de um array associativo
            $fabricante = Helpers::tirarTraco($produto['fabricante']);
            $texto = $produto['nome']." - ".$fabricante." - ".$produto['lote']." - V:".$produto['validade']." - ".$produto['tipo_embalagem']; 
            // Verifica se o preco é null, vazio, "0.00" ou 0
            if ($preco === null || $preco === '' || $preco === '0.00' || $preco === 0) {
                $preco = 0;
            }
         
           echo "<p class='hover:text-blue-500' onclick=\"adicionarAosCampos('{$produto['id']}', '{$texto}', '{$preco}')\">{$texto} - {$preco}R$</p>";

         
        }
    }
    } 
} else {
   if (isset($id)) {
        $produtos = (new Busca())->buscaProdutoVenda('nome', $id);

        foreach ($produtos as $produto) {
            $preco = $produto['preco']; // Corrigido para acessar a propriedade 'preco' de um array associativo

            // Verifica se o preco é null, vazio, "0.00" ou 0
            if ($preco === null || $preco === '' || $preco === '0.00' || $preco === 0) {
                $preco = 0;
            }

            echo "<p class='hover:text-blue-500' onclick=\"adicionarNoCampo('{$produto['id']}', '{$produto['nome']}', '{$preco}')\">{$produto['nome']}-{$preco}R$</p>";
        }
    }
}

   
}



    public function editar_venda(string $venda, int $id): void {
          if($this->nivel_user > 2){
        $registros = (new Busca())->buscaId('registro_vendas',"$id");
        $produto = $registros->produto_id;
         $produtos = (new Busca())->buscaId('produtos', "$produto");
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos e quantidade de saídas!')->flash();
            Helpers::redirecionar('vendas/'.$venda);
        }
          echo $this->template->renderizar('formularios/editarvenda.html', [ 'titulo' => SITE_NOME.' Produtos', 'registros' => $registros, 'produtos' => $produtos, 'venda' => $venda]);}
          else{
            Helpers::redirecionar('vendas/'.$venda);
}
    }
    
    public function deletar_venda(string $venda, int $id): void {
         
         if($this->nivel_user > 2){
        (new VendaModelo())->deletar($venda,$id);
        $this->mensagem->sucesso('Venda inserida na lista de deletados com sucesso!')->flash();
        Helpers::redirecionar('vendas');}

    }

    public function produtos(): void {
        $agora = strtotime(date('Y-m-d'));
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
         $produtos = (new ProdutoModelo())->pesquisa('', $pagina, $limite);
           $quantidade = (new Contar())->contar('produtos',"deletado = 0 OR deletado IS NULL");
          $edicao = (new Contar())->contar('produtos',"editado = 1");
          
          $deletado = (new Contar())->contar('produtos',"deletado = 1");
        $totalRegistros = (new Contar())->contar('produtos');
        $totalPaginas = ceil($totalRegistros / $limite);
       $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $produtos = (new ProdutoModelo())->pesquisa($dados['pesquisa'],$pagina,$limite);   
        }

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
      
          $registros = (new Busca())->busca($pagina, $limite,'registro_vendas',"produto_id = $id",null);
        $totalRegistros = (new Contar())->contar('registro_vendas', "produto_id = $id");

       
        $totalPaginas = ceil($totalRegistros / $limite);
        if (!$produto) {
            Helpers::redirecionar("erro404");
        }
        echo $this->template->renderizar('produto.html', [ 'titulo' => SITE_NOME.' ', 'registros' => $registros, 'produto' => $produto,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas, 'total' => $totalRegistros]);
      
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

   
    
    public function sair(): void {
        $this->sessao->limpar('usuarioId');
         $this->mensagem->sucesso('Você deslogou do sistema!')->flash();
        Helpers::redirecionar('login');
       
    }
}
