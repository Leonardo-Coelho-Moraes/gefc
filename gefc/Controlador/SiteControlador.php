<?php

namespace gefc\Controlador;

/**
 * Description of SiteControlador
 *
 * @author Leonardo
 */
// mostrar todos os dados do lote, pallet
use gefc\Modelo\Busca;
use gefc\Modelo\Contar;
use gefc\Modelo\UserModelo;
use gefc\Modelo\LocalModelo;
use gefc\Controlador\UsuarioControlador;
use gefc\Modelo\EntradaModelo;
use gefc\Modelo\LoteModelo;
use gefc\Modelo\ProdutoModelo;
use gefc\Modelo\SaidaModelo;
use gefc\Nucleo\Controlador;
use gefc\Modelo\VendaModelo;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Sessao;

class SiteControlador extends Controlador
{
    private $sessao;
    protected $usuario;
    protected $user;
    protected $nivel_user;



    public function __construct()
    {
        parent::__construct('templates/site/views');
        $this->usuario = UsuarioControlador::usuario();
        if (!$this->usuario) {
            $this->mensagem->erro('Faça o login para acessar o sistema!')->flash();
            Helpers::redirecionar('login');
            $limpar = (new Sessao())->limpar('usuarioId');
        }

        $this->nivel_user = UsuarioControlador::usuario()->nivel;
        $this->sessao = new Sessao();
        $this->user = UsuarioControlador::usuario()->nome;
    }
    private function verificarPermissaoAdmin()
    {
        if ($this->nivel_user != 1) {

            Helpers::redirecionar('local');
        }
    }
   
    public function index(): void
    {

        Helpers::redirecionar('entrada');
    }

    public function entrada(): void
    {
        $this->verificarPermissaoAdmin();
        //ok 100% testado!!!!
        $lotes = (new Busca())->busca(null, null, 'lote', '', null);
        $produtos = (new Busca())->buscaLimitada(null, null, 'id,nome,slug', 'produtos', null, 'nome ASC');
        $registros = (new EntradaModelo())->pesquisa('1970-01-01', '2099-12-31', '', '');
       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['registro_id'])) {
            (new EntradaModelo())->atualizar($dados);
            $this->mensagem->alerta('Registro Editado com Sucesso. Corrija a quantidade de estoque do produto pra mais ou para menos!')->flash();
            Helpers::redirecionar('entrada/');
        } elseif (isset($dados['lote'])) {
            (new EntradaModelo())->entrada($dados);

            $this->mensagem->sucesso('Entrada Adicionada com Sucesso!')->flash();
            Helpers::redirecionar('entrada/');
        }
        elseif (isset($_POST['relatorio'])) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            $registros = (new EntradaModelo())->pesquisa($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['fornecedorPesquisa']);
        }

        echo $this->template->renderizar('entrada.html', ['titulo' => SITE_NOME . ' Entrada', 'registros' => $registros, 'produtos' => $produtos, 'lotes' => $lotes]);
    }

    public function vendas(): void
    {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        $lotes = (new VendaModelo())->pesquisaFormulario();
        $lotesArray = json_encode($lotes);

        $registros = (new Busca())->busca(null, null, 'registro_vendas', "", null, null);

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
            'titulo' => SITE_NOME . ' Saídas',
            'vendasAgrupadas' => $vendasAgrupadas, 'locais' => $locais, 'lotesarray' => $lotesArray
        ]);
    }

    public function venda(string $nome): void
    {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', "", null);
        $lotes = (new Busca())->busca(null, null, 'lote', 'quantidade > 0', null, null);
        $produtos = (new Busca())->busca(null, null, 'produtos', "", 'nome ASC', null);
        //$registros = (new Busca())->buscarVenda( $nome);
        $registros = (new Busca())->busca(null, null, 'registro_vendas', "nome_venda =  '{$nome}' ", null, null);


        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['adicionar_produto'])) {
            (new VendaModelo())->adicionarAVenda($dados);
            $this->mensagem->sucesso('Produto adiconado a venda com Sucesso.')->flash();
            Helpers::redirecionar('vendas/' . $nome);
        }
        

        $localId = $registros[0]['local'];
        if (!empty($localId)) {
            $local = (new Busca())->busca(null, null, 'locais', "id = $localId", null);
        } else {
            $local = [];
        }


        echo $this->template->renderizar('venda.html', ['titulo' => SITE_NOME . ' ' . $nome, 'registros' => $registros, 'produtos' => $produtos, 'venda' => $nome, 'local' => $local, 'locais' => $locais, 'lotes' => $lotes]);
    }

    public function venda_adicionar(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->venda($dados);
            (new VendaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Saída Feita com Sucesso!')->flash();
            Helpers::redirecionar('vendas');
        }
    }

    public function registroVendas(): void
    {
        
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);

        $lotes = (new VendaModelo())->pesquisaFormulario();
        $lotesArray =json_encode($lotes);

        $pesquisa = (new VendaModelo())->pesquisa('1970-01-01', '2099-12-31', '', '','');

        // Chamar o modelo para realizar a pesquisa
        if(isset($_POST['relatorio'])){
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
           $pesquisa = (new VendaModelo())->pesquisa($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['localPesquisa'], $dados['fornecedorPesquisa'] );
        }
        echo $this->template->renderizar('registroVendas.html', ['titulo' => SITE_NOME . 'Saídas por Produto', 'locais' => $locais, 'lotes' => $lotes, 'pesquisas'=> $pesquisa, 'lotesarray' => $lotesArray]);
    }

    public function saidasFora(): void
    {
        $this->verificarPermissaoAdmin();
       
        $registros = (new Busca())->busca(null, null, 'registro_saida_sem_local', "", null, null);

        $lotes = (new VendaModelo())->pesquisaFormulario();
        $lotesArray = json_encode($lotes);
        

        $vendasAgrupadas = [];

        // Agrupa os registros pela venda (nome_venda) e data
        foreach ($registros as $registro) {
            $nomeVenda = $registro['nome_saida'];
            $data = $registro['data'];
            $local = $registro['local'];

            if (!isset($vendasAgrupadas[$nomeVenda])) {
                $vendasAgrupadas[$nomeVenda] = [];
            }

            if (!isset($vendasAgrupadas[$nomeVenda][$data])) {
                $vendasAgrupadas[$nomeVenda][$data] = [];
            }

            if (!isset($vendasAgrupadas[$nomeVenda][$data][$local])) {
                $vendasAgrupadas[$nomeVenda][$data][$local] = [];
            }

            $vendasAgrupadas[$nomeVenda][$data][$local][] = $registro;
        }


        echo $this->template->renderizar('saidasFora.html', [
            'titulo' => SITE_NOME . ' Saídas Fora Por Nome',
            'vendasAgrupadas' => $vendasAgrupadas, 'lotesarray' => $lotesArray
        ]);
    }

    public function saidaFora(string $nome): void
    {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', "", null);
        $lotes = (new Busca())->busca(null, null, 'lote', 'quantidade > 0', null, null);
        $produtos = (new Busca())->busca(null, null, 'produtos', "", '', null);
        $registros = (new Busca())->busca(null, null, 'registro_saida_sem_local', "nome_saida =  '{$nome}' ", null, null);
        $local = $registros[0]['local'];

       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['adicionar_produto'])) {
            (new SaidaModelo())->adicionarAVenda($dados);
            $this->mensagem->sucesso('Produto adiconado a venda com Sucesso.')->flash();
            Helpers::redirecionar('saidas/fora/' . $nome);
        }

        echo $this->template->renderizar('saidaFora.html', ['titulo' => SITE_NOME . ' ' . $nome, 'registros' => $registros, 'produtos' => $produtos, 'saida' => $nome, 'local' => $local, 'locais' => $locais, 'lotes' => $lotes]);
    }

    public function registroSaidaFora(): void
    {
        $this->verificarPermissaoAdmin();

        $lotes = (new VendaModelo())->pesquisaFormulario();
        $lotesArray = json_encode($lotes);
        $pesquisa = (new SaidaModelo())->pesquisa('1970-01-01', '2099-12-31', '', '','');

        // Chamar o modelo para realizar a pesquisa
        if (isset($_POST['relatorio'])) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            $pesquisa = (new SaidaModelo())->pesquisa($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['localPesquisa'], $dados['fornecedorPesquisa']);
        }
        
        echo $this->template->renderizar('registroSaidaFora.html', ['titulo' => SITE_NOME . 'Saídas Fora Por Produto', 'pesquisas' => $pesquisa, 'lotesarray' => $lotesArray]);
    }

    public function saidaForaAdicionar(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new SaidaModelo())->venda($dados);
            (new SaidaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Saída Fora Feita com Sucesso!')->flash();
            Helpers::redirecionar('saidas/fora/');
        }
    }

    public function produtos(): void
    {
        $this->verificarPermissaoAdmin();
        

        $produtoId = filter_input(INPUT_POST, 'produto_id', FILTER_DEFAULT);
        if (isset($produtoId)) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            if (isset($dados)) {
                (new ProdutoModelo())->atualizar($dados);
                $this->mensagem->sucesso('Registro Editado com Sucesso. Lembre de atualizar a quantidade do estoque em produtos!')->flash();
            }
        }



        $agora = strtotime(date('Y-m-d'));
        $produtos = (new ProdutoModelo())->pesquisa('');

        $pesquisa = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);

        if (isset($pesquisa)) {
            $produtos = (new ProdutoModelo())->pesquisa($pesquisa);
            if (empty($produtos)) {
                $this->mensagem->erro($pesquisa . " não encontrado(a), abaixo a lista de todos os registros!")->flash();
                $produtos = (new ProdutoModelo())->pesquisa('');
            }
        }
        echo $this->template->renderizar('produtos.html', ['titulo' => SITE_NOME . ' Produtos', 'produtos' => $produtos, 'agora' => $agora]);
    }

    public function lotes(): void
    {
        $this->verificarPermissaoAdmin();

        $produto = filter_input(INPUT_POST, 'produto', FILTER_DEFAULT);
        if (isset($produto)) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            if (isset($dados)) {
                (new LoteModelo())->armazenar($dados);
                $this->mensagem->sucesso('Lote Adicionado com Sucesso.')->flash();
                Helpers::redirecionar('lotes');
            }
        }
        $lote = filter_input(INPUT_POST, 'lote_id', FILTER_DEFAULT);
        if (isset($lote)) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            if (isset($dados)) {
                (new LoteModelo())->atualizar($dados);
                $this->mensagem->sucesso('Lote Atualizado com Sucesso.')->flash();
                Helpers::redirecionar('lotes');
            }
        }
        $lotes = (new LoteModelo())->pesquisa('1970-01-01', '2099-12-31', '', '');
        if (isset($_POST['relatorio'])) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            $lotes = (new LoteModelo())->pesquisa($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'],  $dados['fornecedorPesquisa']);
        }
      
        $produtos = (new Busca())->busca(null,null,'produtos',"produtos.slug != ''");
        echo $this->template->renderizar('lotes.html', ['titulo' => SITE_NOME . ' Lotes', 'lotes' => $lotes,'produtos'=> $produtos]);
    }

    public function produto_cadastrar(): void
    {
        //$this->verificarPermissaoAdmin();
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->armazenar($dados);
            $this->mensagem->sucesso("Produto " . $dados['produto'] . " Cadastrado")->flash();
            Helpers::redirecionar('lotes');
            //Helpers::redirecionar('produtos');
        }
    }
  
    public function produto(string $slug, int $id): void
    {
        $this->verificarPermissaoAdmin();
        $produto =   (new Busca())->buscaSlug('produtos', $slug);
        $produtoID = $produto->id;
       
        $lotes = (new Busca())->busca(null, null, 'lote', "quantidade > 0 and produto_id = '$produtoID'", null, null);


        if (!$produto) {
            Helpers::redirecionar("erro404");
        }
        echo $this->template->renderizar('produto.html', ['titulo' => SITE_NOME . "$produto->nome", 'produto' => $produto,'lotes' => $lotes]);
    }
    public function estoqueLocais(): void
    {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', null);

        $estoque = [];  // Inicializa a variável $estoque
        $produtos = [];
        $lotes = [];
        if (isset($_POST['local'])) {
            $pesquisa = $_POST['local'];
            $estoque = (new Busca())->busca(null, null, 'local_estoque', "local_id = $pesquisa", '', null);
            $produtos = (new Busca())->buscaLimitada(null, null, 'id,nome,slug', 'produtos', null, 'nome ASC', null);
            $lotes = (new Busca ())->busca(null,null,'lote','', null,null);
        }
        echo $this->template->renderizar('estoqueLocais.html', ['titulo' => SITE_NOME . ' Estoque', 'produtos' => $produtos, 'locais' => $locais, 'estoque' => $estoque, 'lotes' => $lotes]);
    }
    public function local(): void
    {
        $local = UsuarioControlador::usuario()->local;
        $nome = (new Busca())->buscaId('locais', $local);
        $estoque = (new VendaModelo())->pesquisaEstoque($local);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['estoque'])) {   
            (new VendaModelo())->atualizarEstoque($dados);
            $this->mensagem->sucesso("Estoque do Produto Editado.")->flash();
            Helpers::redirecionar('local');
        }
         echo $this->template->renderizar('local.html', ['titulo' => SITE_NOME . ' Estoque ' . $nome->nome, 'registros' => $estoque, 'nome'=> $nome]);
    }
   
    public function pedidoFazer(): void
    {
        $local = UsuarioControlador::usuario()->local;
        $nome = (new Busca())->buscaId('locais', $local);
        $produtos = (new Busca())->busca(null, null, 'produtos', "", 'nome ASC');
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new LocalModelo())->pedido($dados, $local);

            $this->mensagem->sucesso(' Pedido Feito com Sucesso!')->flash();
            Helpers::redirecionar('pedido/fazer');
        }

        echo $this->template->renderizar('formularios/fazerPedido.html', ['titulo' => SITE_NOME . 'Pedido', 'produtos' => $produtos, 'nome' => $nome]);
    }

    public function pedidos(): void
    {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        $registros = (new LocalModelo())->pesquisa('');

        if (isset($_POST['pesquisaPedidos'])) {
            $pesquisa = $_POST['pesquisaPedidos'];
            $registros = (new LocalModelo())->pesquisa($pesquisa);
            if (empty($registros)) {
                $this->mensagem->erro($pesquisa . "pedido não econtrado!")->flash();
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

        echo $this->template->renderizar('pedidos.html', ['titulo' => SITE_NOME . ' Pedidos', 'vendasAgrupadas' => $vendasAgrupadas, 'locais' => $locais]);
    }

    public function pedidoAtender(string $pedido): void
    {
        $this->verificarPermissaoAdmin();
        
        $produtos = (new LocalModelo())->pesquisaPedido("$pedido");
        $lotes = (new Busca())->busca(null, null, 'lote', 'quantidade > 0', null, null, null);
     
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->venda($dados);
            (new VendaModelo())->vendaRegistro($dados);
            (new LocalModelo())->atualizarPedido($dados);
            $this->mensagem->sucesso('Pedido Atendido')->flash();
            Helpers::redirecionar('pedidos/');
        }


        echo $this->template->renderizar('formularios/pedido.html', ['titulo' => SITE_NOME . 'Pedido', 'produtos' => $produtos, 'pedido' => $pedido,'lotes' => $lotes]);
    }
    //ok funcionando
    public function pedidoAtendido(string $pedido): void
    {
        $this->verificarPermissaoAdmin();
        (new LocalModelo())->atendido($pedido);
        $this->mensagem->sucesso('Pedido Atendido')->flash();
        Helpers::redirecionar('pedidos/');
    }
    public function saidaHospital(): void
    {
        $produtos = (new Busca())->busca(null,null,'produtos');
        $local = UsuarioControlador::usuario()->local;
        $nome = (new Busca())->buscaId('locais', $local);
        if ($local == 3) {
           
            $estoque  = (new LocalModelo())->pesquisaHospital();
            $pesquisaPadrao  = (new LocalModelo())->pesquisaPadrao('Sala de Medicação');
            if(isset($_POST['localSelecionado'])){
                $local = $_POST['localSelecionado'];
                $pesquisaPadrao  = (new LocalModelo())->pesquisaPadrao($local);
            }
          
            $lotesArray = json_encode($estoque);
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            if (isset($_POST['padrao'])) {
                $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
                (new LocalModelo())->padrao($dados);
            }
            if (isset($dados)) {
                (new LocalModelo())->saidaHospital($dados);

                $this->mensagem->sucesso(' Saída feita com sucesso!')->flash();
                Helpers::redirecionar('saida/hospital');
            }
            


            echo $this->template->renderizar('formularios/saidaHospital.html', ['titulo' => SITE_NOME . 'Saída Hospital','estoques' => $estoque, 'nome' => $nome, 'lotesarray' => $lotesArray, 'produtos' => $produtos, 'padrao' => $pesquisaPadrao]);
        } else {
            Helpers::redirecionar('local');
        }
    }

    public function registroSaidasHospital(): void
    {

        $local = UsuarioControlador::usuario()->local;
        $nome = (new Busca())->buscaId('locais', $local);
        $registros = (new LocalModelo())->saidasHospital('1970-01-01', '2099-12-31', '', '', '');
        if (isset($_POST['relatorio'])) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            $registros = (new LocalModelo())->saidasHospital($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['localPesquisa'], $dados['fornecedorPesquisa']);
        }
        echo $this->template->renderizar('saidasHospital.html', [
            'titulo' => SITE_NOME . ' Registro Saídas Hospital', 'registros' => $registros, 'nome'=> $nome
        ]);
    }
    public function usuarios(): void
    {
        $this->verificarPermissaoAdmin();
        $usuarios = (new Busca())->busca(null, null, 'usuario', '', '', null);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        if (isset($dados)) {
            (new UserModelo())->cadastro($dados);
            Helpers::redirecionar('usuarios');
        }

        echo $this->template->renderizar('usuarios.html', ['titulo' => SITE_NOME . ' Usuários', 'usuarios' => $usuarios, 'locais' => $locais]);
    }
    public function erro404(): void
    {
        echo $this->template->renderizar('error404.html', ['titulo' => 'Página não Encontrada']);
    }
    public function sair(): void
    {
        $this->sessao->limpar('usuarioId');
        $this->mensagem->sucesso('Você deslogou do sistema!')->flash();
        Helpers::redirecionar('login');
    }
}
