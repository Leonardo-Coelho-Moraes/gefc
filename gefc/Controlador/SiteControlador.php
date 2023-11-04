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
use gefc\Modelo\Inserir;
use gefc\Modelo\ProdutoModelo;
use gefc\Modelo\RegistrosModelo;
use gefc\Modelo\Atualizar;
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
         
           
           
           $this->user = UsuarioControlador::usuario()->nome;   
           
    }

    public function index(): void {
        
      if($this->nivel_user < 3){
      Helpers::redirecionar('entrada');
      
      }else{
           echo $this->template->renderizar('index.html', [ 'titulo' => SITE_NOME.' Página Inicial',]);
      }
       
   }

    public function entrada(): void {
        
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 2;
        $editado = (new Contar())->contar('registro_entrada', 'editado = 1');
        $registrosTotais = (new Contar())->contar('registro_entrada');
         $produtos = (new Busca())->buscaLimitada(null,null,'id,nome,peso,unidade_medida,fabricante,tipo_embalagem,slug,editado,deletado','produtos',null,'nome ASC',null);
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
       
         $produtos = (new Busca())->buscaLimitada(null,null,'id,nome,peso,unidade_medida,fabricante,tipo_embalagem,slug','produtos',null,'nome ASC',null);
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
            $this->mensagem->sucesso('Registro Editado com Sucesso. Quantidade de Venda e Estoque do Produto Editados!')->flash();
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
    
    public function deletarVendaInteira(string $venda): void {
         
         if($this->nivel_user > 2){
              $dadosArray = ['deletado' => 1];
       (new Atualizar())->atualizarVendaValor("deletado = ?", $dadosArray, $venda);
       $this->mensagem->sucesso('Venda deletada completamente!')->flash();
}
        Helpers::redirecionar('vendas');

    }
    
    public function registroVendas(): void {
    
    
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
        $editado = (new Contar())->contar('registro_vendas', 'editado = 1');
        $registrosTotais = (new Contar())->contar('registro_vendas','editado != 1');
         $produtos = (new Busca())->busca(null,null,'produtos',null,'nome ASC',null);
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
    
    public function produtos(): void {
            $categorias = (new Busca())->buscaLimitada(null,null,'categoria','categorias',null,'categoria ASC',null);
            $tipos = (new Busca())->buscaLimitada(null,null,'tipo','tipo_medicamento',null,'tipo ASC',null);
        $agora = strtotime(date('Y-m-d'));
        $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
        $limite = 30;
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
          $ordenação = filter_input_array(INPUT_POST, FILTER_DEFAULT);
          
     if (isset($ordenação)) {
            $produtos = (new ProdutoModelo())->pesquisa('',$pagina,$limite, $ordenação);   
           
        }
       

        echo $this->template->renderizar('produtos.html', [ 'titulo' => SITE_NOME.' Produtos', 'produtos' => $produtos,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas,'quantidade' =>$quantidade,'edicao' =>$edicao, 'deletado' => $deletado, 'agora'=> $agora, 'categorias'=> $categorias, 'tipos' => $tipos]);
    }

    public function produto_cadastrar(): void {
  $categorias = (new Busca())->buscaLimitada(null,null,'categoria','categorias',null,'categoria ASC',null);
            $tipos = (new Busca())->buscaLimitada(null,null,'tipo','tipo_medicamento',null,'tipo ASC',null);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->armazenar($dados);
             $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            Helpers::redirecionar('produtos');
        }

        echo $this->template->renderizar('formularios/cadastrarproduto.html', [ 'titulo' => 'SGE-SEMSA Produtos', 'categorias'=> $categorias, 'tipos' => $tipos]);
    }

    public function editar_produto(string $slug, int $id): void {
         $categorias = (new Busca())->buscaLimitada(null,null,'categoria','categorias',null,'categoria ASC',null);
            $tipos = (new Busca())->buscaLimitada(null,null,'tipo','tipo_medicamento',null,'tipo ASC',null);
         if($this->nivel_user > 2){
        $produtos = (new Busca())->buscaSlug('produtos',$slug);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->atualizar($dados, $id);
            $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            Helpers::redirecionar('produtos');
        }


        echo $this->template->renderizar('formularios/editarproduto.html', [ 'titulo' => SITE_NOME.' Produtos', 'produto' => $produtos, 'categorias'=> $categorias, 'tipos' => $tipos]);
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
    public function categorias(): void {
        if($this->usuario->nivel_acesso >2){
            $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
            $limite = 30;
            $deletado = (new Contar())->contar('categorias', 'deletado = 1');
            $categoriasTotais = (new Contar())->contar('categorias');
            $totalRegistros = (new Contar())->contar('categorias');
            $totalPaginas = ceil($totalRegistros / $limite);
            $categorias= (new Busca())->busca($pagina, $limite, 'categorias', 'deletado != 1 OR deletado IS NULL');
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            if (isset($dados)) {
                if( strlen($dados['categoria']) > 3)
                {
                    $criador = $this->user;
                $categoria = $dados['categoria'];
                $array= [$categoria, $criador];
                (new Inserir() )->inserir('categorias','categoria,criado_por', $array);
                Helpers::redirecionar('categorias');}
                else{
                    $this->mensagem->erro('Categoria precisa ter pelo menos 4 caracteres!')->flash();
                }
            }
            echo $this->template->renderizar('categorias.html', [ 'titulo' => SITE_NOME.' Categorias', 'paginaAtual' => $pagina,'totalPaginas' => $totalPaginas, 'categorias' => $categorias]);}
        else{ 
            Helpers::redirecionar('entrada');  
        }
   
    }
public function editarCategoria(int $id): void {
        if($this->usuario->nivel_acesso >2){
             $categoria = (new Busca())->buscaId('categorias',$id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $array=[$dados['categoria']];
            (new Atualizar())->atualizar('categorias', 'categoria = ?', $array, $id);
            $this->mensagem->sucesso('Categoria '. $categoria->categoria.' editado para '.$dados['categoria'].' com sucesso!')->flash();
            Helpers::redirecionar('categorias');
    }
     echo $this->template->renderizar('formularios/editarCategoria.html', [ 'titulo' => 'SGE-SEMSA Editar Categoria', 'categoria' => $categoria]);
        }
        else{ 
            Helpers::redirecionar('categorias');
            
        }
   
    }
    public function deletarCategoria(int $id): void {
        if($this->usuario->nivel_acesso >2){
      $deletado = 1;
$data = date('Y-m-d');
             $array = [$deletado,$data];
            (new Atualizar())->atualizar('categorias', 'deletado = ?,deletado_em = ?', $array, $id);
            $this->mensagem->sucesso('Categoria deletada com sucesso!')->flash();
            Helpers::redirecionar('categorias');
    }
        else{ 
            Helpers::redirecionar('categorias');
        }
   
    }
     public function medicamentos(): void {
        if($this->usuario->nivel_acesso >2){
            $pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
            $limite = 30;
            $deletado = (new Contar())->contar('tipo_medicamento', 'deletado = 1');
            $categoriasTotais = (new Contar())->contar('tipo_medicamento');
            $totalRegistros = (new Contar())->contar('tipo_medicamento');
            $totalPaginas = ceil($totalRegistros / $limite);
            $tipos= (new Busca())->busca($pagina, $limite, 'tipo_medicamento', 'deletado != 1 OR deletado IS NULL');
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            if (isset($dados)) {
                if( strlen($dados['tipo']) > 3)
                {
                    $criador = $this->user;
                $categoria = $dados['tipo'];
                $array= [$categoria, $criador];
                (new Inserir() )->inserir('tipo_medicamento','tipo,criado_por', $array);
                Helpers::redirecionar('medicamentos');}
                else{
                    $this->mensagem->erro('Tipo do Medicamento precisa ter pelo menos 4 caracteres!')->flash();
                }
            }
            echo $this->template->renderizar('medicamentos.html', [ 'titulo' => SITE_NOME.' Tipos Medicamentos', 'paginaAtual' => $pagina,'totalPaginas' => $totalPaginas, 'tipos' => $tipos]);}
        else{ 
            Helpers::redirecionar('entrada');  
        }
   
    }
public function editarMedicamento(int $id): void {
        if($this->usuario->nivel_acesso >2){
             $tipo = (new Busca())->buscaId('tipo_medicamento',$id);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $array=[$dados['tipo']];
            (new Atualizar())->atualizar('tipo_medicamento', 'tipo = ?', $array, $id);
            $this->mensagem->sucesso('Tipo Medicamento '. $tipo->tipo.' editado para '.$dados['tipo'].' com sucesso!')->flash();
            Helpers::redirecionar('medicamentos');
    }
     echo $this->template->renderizar('formularios/editarMedicamento.html', [ 'titulo' => 'SGE-SEMSA Editar Tipo Medicamento', 'tipo' => $tipo]);
        }
        else{ 
            Helpers::redirecionar('medicamentos');
            
        }
   
    }
    public function deletarMedicamento(int $id): void {
        if($this->usuario->nivel_acesso >2){
      $deletado = 1;
$data = date('Y-m-d');
             $array = [$deletado,$data];
            (new Atualizar())->atualizar('tipo_medicamento', 'deletado = ?,deletado_em = ?', $array, $id);
            $this->mensagem->sucesso('Tipo Medicamento deletado com sucesso!')->flash();
            Helpers::redirecionar('medicamentos');
    }
        else{ 
            Helpers::redirecionar('medicamentos');
        }
   
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
