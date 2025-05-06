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
use gefc\Modelo\LaudosModelo;
use gefc\Controlador\UsuarioControlador;
use gefc\Modelo\EntradaModelo;
use gefc\Modelo\LoteModelo;
use gefc\Modelo\ProdutoModelo;
use gefc\Modelo\SaidaModelo;
use gefc\Nucleo\Controlador;
use gefc\Modelo\VendaModelo;
use gefc\Modelo\Deletar;
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
        if ($this->nivel_user > 3) {

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
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['registro_id'])) {
            (new EntradaModelo())->atualizar($dados);
            echo 'Sucesso';
            exit;
        } elseif (isset($dados['loteAdd'])) {
            (new EntradaModelo())->entrada($dados);
            echo 'Sucesso';
            exit;
        } elseif (isset($dados['relatorio'])) {
            $registros = (new EntradaModelo())->pesquisa($dados['relatorio']);
            $lotesArray = json_encode($registros);
            echo $lotesArray;
            exit;
        } elseif(isset($dados["query"])) {
            
            $lotes = (new LoteModelo())->pesquisaEntrada($dados['query']);
            echo json_encode($lotes);
            exit;
        }
        elseif (isset($dados['entrada'])) {
            (new LoteModelo())->armazenarEntrada($dados);
            echo 'Sucesso';
            exit;
        } elseif (isset($dados["produto"])) {
    // Corrigindo a sintaxe da busca
    $produtos = (new Busca())->busca(null, null, 'produtos', "nome LIKE '%" . $dados['produto'] . "%'");
    echo json_encode($produtos);
    exit;
} elseif (isset($dados["produto"])) {
            // Corrigindo a sintaxe da busca
            $produtos = (new Busca())->busca(null, null, 'produtos', "nome LIKE '%" . $dados['produto'] . "%'");
            echo json_encode($produtos);
            exit;
        } elseif (isset($dados["lote"])) {
            // Corrigindo a sintaxe da busca
            $registros = (new EntradaModelo())->pesquisaEntrada($dados['lote']);
            $lotesArray = json_encode($registros);
            echo $lotesArray;
            exit;
        }


        echo $this->template->renderizar('entrada.html', ['titulo' => SITE_NOME . ' Entrada']);
    }


    public function produtos(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['produto_id'])) {
                (new ProdutoModelo())->atualizar($dados);
                echo 'Sucesso';
                exit;
        }elseif (isset($dados['mais'])) {
           $produto = (new ProdutoModelo())->pesquisaProduto($dados['mais']);
            $produtoArray = json_encode($produto);
            echo $produtoArray;
            exit;
        }
        $tipos = (new Busca())->busca(null, null, 'tipo_produto', '');
        $listatipos = json_encode($tipos);
        $pesquisa = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);
        if (isset($pesquisa)) {
        $produtos = (new ProdutoModelo())->pesquisa( $pesquisa);
            $lotesArray = json_encode($produtos);
            echo $lotesArray;
            exit;
        }
        echo $this->template->renderizar('produtos.html', ['titulo' => SITE_NOME . ' Produtos', 'tipos' => $listatipos]);
    }
     public function lotes(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $produto = filter_input(INPUT_POST, 'produto', FILTER_DEFAULT);
        if (isset($produto)) {
        (new LoteModelo())->armazenar($dados);
         exit;
            
        }
        $lote = filter_input(INPUT_POST, 'lote_id', FILTER_DEFAULT);
        if (isset($lote)) {
            if (isset($dados)) {
                (new LoteModelo())->atualizar($dados);
                echo $dados['lote_edit'] . " atualizado com Sucesso";
                exit;
            }
        }
        $pesquisa = filter_input(INPUT_POST, 'pesquisa', FILTER_DEFAULT);
        if (isset($pesquisa)) {
            $lotes = (new LoteModelo())->pesquisa($pesquisa);
            $lotesArray = json_encode($lotes);
            echo $lotesArray;
            exit;
        }
        echo $this->template->renderizar('lotes.html', ['titulo' => SITE_NOME . ' Lotes']);
    }

    public function padroes(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        $produto = filter_input(INPUT_POST, 'produto', FILTER_DEFAULT);
        if (isset($produto)) {
            (new LoteModelo())->armazenar($dados);
            exit;
        }
        

       
        echo $this->template->renderizar('padraoLocais.html', ['titulo' => SITE_NOME . ' Padrões', 'locais'=>$locais]);
    }

    public function pacienteLaudo(): void
    {
        $this->verificarPermissaoAdmin();
        //ok 100% testado!!!!
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
       
        $pacientes =  (new Busca())->busca(null, null, 'paciente_laudo', '','',null);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (isset($dados['nome'])) {
           
            (new LaudosModelo())->cadastrarPaciente($dados);
            $this->mensagem->Sucesso('Paciente '.$dados['nome'].' cadastrado com Sucesso!' )->flash();
            Helpers::redirecionar('pacientes/');
        }
        elseif (isset($dados['paciente_id'])) {
           
            (new LaudosModelo())->editarPaciente($dados);
            $this->mensagem->Sucesso('Paciente atualizado com Sucesso!' )->flash();
            Helpers::redirecionar('pacientes/');
        }
        

        echo $this->template->renderizar('pacientes_laudo.html', ['titulo' => SITE_NOME . ' Pacientes Laudo', 'pacientes' =>$pacientes, 'locais' =>$locais]);
    }
    public function deletarEntrada(int $id): void
    {
        $this->verificarPermissaoAdmin();
        
        (new Deletar())->deletar($id,'registro_entrada');
        $this->mensagem->sucesso('Entrada Deletada com Sucesso!')->flash();
        Helpers::redirecionar('entrada/');
    }

    public function deletarPaciente(int $id): void
    {
        $this->verificarPermissaoAdmin();
        
        (new Deletar())->deletar($id,'paciente_laudo');
        $this->mensagem->sucesso('Paciente Deletad com Sucesso!')->flash();
        Helpers::redirecionar('pacientes/');
    }
    public function deletarProdutos(int $id): void
    {
        $this->verificarPermissaoAdmin();
        (new Deletar())->deletar($id, 'produtos');
        $this->mensagem->sucesso('Produto Deletado com Sucesso!')->flash();
        Helpers::redirecionar('produtos/');
    }
    public function deletarSaida(int $id): void
    {
        $this->verificarPermissaoAdmin();
        (new Deletar())->deletar($id, 'registro_vendas');
        $this->mensagem->sucesso('Saída deletada, edite no seu estoque a diferença que você deletou!')->flash();
        Helpers::redirecionar('registro/vendas/');
    }
    public function deletarSaidaFora(int $id): void
    {
        $this->verificarPermissaoAdmin();
        (new Deletar())->deletar($id, 'registro_saida_sem_local');
        $this->mensagem->sucesso('Saída deletada, edite no seu estoque a diferença que você deletou!')->flash();
        Helpers::redirecionar('registro/saida/fora/');
    }
   

    public function venda(string $nome): void
    {
        $this->verificarPermissaoAdmin();
        $registros = (new VendaModelo())->pesquisaSaida($nome);
        $lotes = (new VendaModelo())->pesquisaFormulario();
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['adicionar_produto'])) {
            (new VendaModelo())->adicionarAVenda($dados);
            $this->mensagem->sucesso('Produto adiconado a venda com Sucesso.')->flash();
            Helpers::redirecionar('vendas/' . $nome);
        }
        elseif (isset($dados['registro_id'])) {
            (new VendaModelo())->atualizarVenda($dados);
            $this->mensagem->alerta('Registro Editado com Sucesso. Corrija a quantidade de estoque do produto pra mais ou para menos!')->flash();
            Helpers::redirecionar("vendas/".$nome);
        } 
        echo $this->template->renderizar('venda.html', ['titulo' => SITE_NOME . ' Saída: ' . $nome.' do CAF para '.$registros[0]['localNome'] , 'registros' => $registros, 'venda' => $nome, 'lotes' => $lotes ]);
    }

  
    public function venda_adicionar(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new VendaModelo())->venda($dados);
            (new VendaModelo())->vendaRegistro($dados);
            (new VendaModelo())->deletarProdutosSemEstoque();
            $this->mensagem->sucesso('Saída Feita com Sucesso!')->flash();
            Helpers::redirecionar('registro/vendas');

        }
    }

    public function registroVendas(): void
    {
        
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        $data = date('Y-m-d');
        $lotes = (new VendaModelo())->pesquisaFormulario();
        $lotesArray =json_encode($lotes);

        $pesquisa = (new VendaModelo())->pesquisa($data, $data, '', '','');

        // Chamar o modelo para realizar a pesquisa
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if(isset($_POST['relatorio'])){
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
           $pesquisa = (new VendaModelo())->pesquisa($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['localPesquisa'], $dados['fornecedorPesquisa'] );
        }
        echo $this->template->renderizar('registroVendas.html', ['titulo' => SITE_NOME . 'Saídas por Produto', 'locais' => $locais, 'lotes' => $lotes, 'pesquisas'=> $pesquisa, 'lotesarray' => $lotesArray, 'dados'=>$dados]);
    }
    public function Abastecer(): void
    {

        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', '', '', null);
        $lotes = (new LocalModelo())->pesquisaAbastecer();
        $lotesarray = json_encode($lotes);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) { 
                (new LocalModelo())->abastecer($dados);
                $this->mensagem->sucesso('Sucesso.')->flash();
                Helpers::redirecionar('abastecer/');
            
        }
        

        $produtos = (new Busca())->busca(null, null, 'produtos', "");
        echo $this->template->renderizar('abastecer.html', ['titulo' => SITE_NOME . 'Saídas por Produto', 'locais' => $locais, 'lotesarray' => $lotesarray, 'produtos' => $produtos]);
    }



    public function saidaFora(string $nome): void
    {
        $this->verificarPermissaoAdmin();
      
       
        $lotes = (new VendaModelo())->pesquisaFormulario();
        $registros = (new VendaModelo())->pesquisaSaidaSem($nome);
      

       
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['adicionar_produto'])) {
            (new SaidaModelo())->adicionarAVenda($dados);
            $this->mensagem->sucesso('Produto adiconado a venda com Sucesso.')->flash();
            Helpers::redirecionar('saidas/fora/' . $nome);
        } elseif (isset($dados['registro_id'])) {
            (new SaidaModelo())->atualizarVenda($dados);
            $this->mensagem->alerta('Registro Editado com Sucesso. Corrija a quantidade de estoque do produto pra mais ou para menos!')->flash();
            Helpers::redirecionar('saidas/fora/' . $nome);
        } 

        echo $this->template->renderizar('saidaFora.html', ['titulo' => SITE_NOME . ' Saída: ' . $nome.' do CAF para '.$registros[0]['local'], 'registros' => $registros,'saida' => $nome,'lotes' => $lotes]);
    }

    public function registroSaidaFora(): void
    {
        $this->verificarPermissaoAdmin();
        $data = date('Y-m-d');
        $lotes = (new VendaModelo())->pesquisaFormulario();
        $lotesArray = json_encode($lotes);
        $pesquisa = (new SaidaModelo())->pesquisa($data, $data, '', '','');
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        // Chamar o modelo para realizar a pesquisa
        if (isset($_POST['relatorio'])) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            $pesquisa = (new SaidaModelo())->pesquisa($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['localPesquisa'], $dados['fornecedorPesquisa']);
        }
        
        echo $this->template->renderizar('registroSaidaFora.html', ['titulo' => SITE_NOME . 'Saídas Fora Por Produto', 'pesquisas' => $pesquisa, 'lotesarray' =>$lotesArray, 'dados' => $dados]);
    }

    public function saidaForaAdicionar(): void
    {
        $this->verificarPermissaoAdmin();

        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new SaidaModelo())->venda($dados);
            (new SaidaModelo())->vendaRegistro($dados);
            $this->mensagem->sucesso('Saída Fora Feita com Sucesso!')->flash();
            Helpers::redirecionar('registro/saida/fora/');
        }
    }

  

    public function produtosCrit(): void
    {
        $this->verificarPermissaoAdmin();
        $produtos = (new ProdutoModelo())->pesquisaNivelCritico();
        $produtosCrit = json_encode($produtos);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
           (new ProdutoModelo())->definirNivel($dados);
            Helpers::redirecionar('produtosCrit');
          
        }
        echo $this->template->renderizar('produtosCrit.html', ['titulo' => SITE_NOME . ' Produtos Nível Crítico', 'produtos' => $produtosCrit]);
    }


  


   

    public function teste(): void
    {
        
        $lotes = (new LoteModelo())->pesquisa('1970-01-01', '2099-12-31', '', '');
        $lotesArray = json_encode($lotes);

       
        echo $this->template->renderizar('lotesgerar.html', ['titulo' => SITE_NOME . ' Lotes', 'lotes' => $lotes, 'lotesArray' => $lotesArray]);
    }

    public function produto_cadastrar(): void
    {
        //$this->verificarPermissaoAdmin();
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            (new ProdutoModelo())->armazenar($dados);
            echo $dados['produto'];
            exit;
        }
    }
    public function criarTipo(): void
    {
        //$this->verificarPermissaoAdmin();
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados['tipoProduto'])) {
            (new ProdutoModelo())->criarTipo($dados['tipoProduto']);
            echo $dados['tipoProduto'];
            exit;
            //Helpers::redirecionar('produtos');
        }
    }
  
   
    public function estoqueLocais(): void
    {
        $this->verificarPermissaoAdmin();
        $locais = (new Busca())->busca(null, null, 'locais', null);

        $estoque = [];  // Inicializa a variável $estoque
        if (isset($_POST['local'])) {
            $pesquisa = $_POST['local'];
            $estoque = (new LocalModelo())->estoqueLocal($pesquisa);
        }
        echo $this->template->renderizar('estoqueLocais.html', ['titulo' => SITE_NOME . ' Estoque',  'locais' => $locais, 'estoque' => $estoque]);
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

    public function entradaLocal(): void
    {

        $local = UsuarioControlador::usuario()->local;
        //$local = UsuarioControlador::usuario()->local;
        $nome = (new Busca())->buscaId('locais', $local);
        $recebidos = (new LocalModelo())->recebidos($local);
        $vendasAgrupadas = [];

        // Agrupa os registros pela venda (nome_venda) e data
        foreach ($recebidos as $registro) {
            $nomeVenda = $registro['nome_entrada'];

            $data = $registro['data'];


            if (!isset($vendasAgrupadas[$nomeVenda])) {
                $vendasAgrupadas[$nomeVenda] = [];
            }

            if (!isset($vendasAgrupadas[$nomeVenda][$data])) {
                $vendasAgrupadas[$nomeVenda][$data] = [];
            }

            $vendasAgrupadas[$nomeVenda][$data][] = $registro;
        }

        echo $this->template->renderizar('entradaLocal.html', ['titulo' => SITE_NOME . ' Entradas ' . $nome->nome, 'nome' => $nome,'vendasAgrupadas' => $vendasAgrupadas, 'local' => $local]);
    }
    public function confirmacaoLocal(string $entrada, int $local): void
    {

        $entradas = (new LocalModelo())->confirmacao($local, $entrada);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            
            (new LocalModelo())->confirmado($dados);
            $this->mensagem->sucesso('Produtos Confirmados com Sucesso')->flash();
            Helpers::redirecionar('local/entrada/');
        }


        echo $this->template->renderizar('confirmacao.html', ['titulo' => SITE_NOME . 'Confirmar Entrada', 'entradas' => $entradas, 'entrada' => $entrada,'local'=>$local ]);
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
        $data = date('Y-m-d');
        $local = UsuarioControlador::usuario()->local;
        $nome = (new Busca())->buscaId('locais', $local);
        $registros = (new LocalModelo())->saidasHospital($data, $data, '', '');
        if (isset($_POST['relatorio'])) {
            $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
            $registros = (new LocalModelo())->saidasHospital($dados['dePesquisa'], $dados['atePesquisa'], $dados['produtoPesquisa'], $dados['localPesquisa']);
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
        $usuario = UsuarioControlador::usuario()->nivel;
        if (isset($dados) AND $usuario == 1) {
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
