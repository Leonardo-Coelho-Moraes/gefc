<?php

namespace gefc\Controlador;

/**
 * Description of SiteControlador
 *
 * @author Leonardo
 */
//editar para adicionar local
use FPDF;
use gefc\Modelo\Busca;
use gefc\Modelo\RelatorioModelo;
use gefc\Nucleo\Controlador;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Sessao;
use gefc\Controlador\UsuarioControlador;

class RelatorioControlador extends Controlador {
     private $sessao;
    protected $nivel_user;
    public function __construct() {
        parent::__construct('templates/site/views'); 
        $this->sessao = new Sessao();
        $this->nivel_user = UsuarioControlador::usuario()->nivel;
        
        
    }
    
    private function verificarPermissaoAdmin() {
        if ($this->nivel_user != 1) {
            $this->mensagem->erro('Você não tem permissão para acessar esta página!')->flash();
            Helpers::redirecionar('local');
    }}
    
    public function relatorio(): void {
        $this->verificarPermissaoAdmin();
        $relatorio = [];
        $produtos = (new Busca())->busca(null,null,'produtos',"",'nome ASC',null);
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($_POST['acao'])){
        $acao = $_POST['acao'];}
        else{$acao = '';
        }
        
        
        
        if (isset($dados)) {
            $relatorio = (new RelatorioModelo())->buscaRegistros($dados);
           
            if(empty($relatorio)){
              $this->mensagem->erro('Registros não exitem, consulte o banco de dados!')->flash();
            }
      
    }
    
        $this->sessao->criar('relatorio', $relatorio);
        $this->sessao->criar('acao', $acao);
        
        echo $this->template->renderizar('relatorio.html', [ 
        'titulo' => 'SGE-SEMSA Relatório',
        'relatorio' => $relatorio, 'produtos' => $produtos,'acao' => $acao]);
    }
  public function media(): void {
      $this->verificarPermissaoAdmin();
    // Inicializações
    $media = [];
    $resultado = [];
    $produtos = (new Busca())->busca(null, null, 'produtos', '', 'nome ASC', null);
    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

    // Verifica se os dados foram enviados via POST
    if (isset($dados)) {
        // Busca os dados de vendas com base nos filtros
        $media = (new RelatorioModelo())->buscaMedia($dados);

        // Verifica se há dados de vendas encontrados
        if (empty($media)) {
            $this->mensagem->erro('Registros não existem, consulte o banco de dados!')->flash();
        } else {
            // Calcula a média das quantidades vendidas
           
            $soma = 0;
            $soma2 = 0;
            foreach ($media as $item) {
                // Ajuste para acessar propriedades de um objeto stdClass
                $soma += $item->quantidade;
                $soma2 += $item->qnt_solicitada;
            }
            $resultado[] = $soma / count($media);
            $resultado[] = $soma2 / count($media);
        }
    }

    // Define os dados para a sessão
    $this->sessao->criar('media', $media);

    // Renderiza o template com os dados necessários
    echo $this->template->renderizar('media.php', [
        'titulo' => 'SGE-SEMSA Relatório',
        'media' => $media,
        'produtos' => $produtos,
        'resultado' => $resultado
    ]);
}

    
   public function imprimir(): void {
       $this->verificarPermissaoAdmin();
    $relatorio = $this->sessao->carregar()->relatorio;
     $acao = $this->sessao->carregar()->acao;

    echo $this->gerarTabela($relatorio, $acao);
}

private function gerarTabela($relatorio, $acao): string {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SGE-SEMSA Relatório</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 12px;
            word-break: break-word;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h3>SGE-SEMSA Relatório</h3>
   
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Ação</th>
                <th>Qnt.</th>';

    // Condicional para adicionar coluna de Qnt Solicitada se a ação for 'Saida'
    if ($acao == 'Saida') {
        $html .= '<th>Qnt Solicitada</th>';
    }

    $html .= '<th>Local</th>
                <th>Data - Hora</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($relatorio as $rela) {
        $html .= '<tr>
            <td>' . htmlspecialchars($rela->produto_nome) . ' - ' . htmlspecialchars($rela->produto_fornecedor) . ' - ' . htmlspecialchars($rela->produto_unidade) .'</td>
            <td>' . htmlspecialchars($acao) . '</td>
            <td>' . htmlspecialchars($rela->quantidade) . '</td>';

        // Verifica se a propriedade 'qnt_solicitada' existe antes de exibi-la
        if ($acao == 'Saida' && property_exists($rela, 'qnt_solicitada')) {
            $html .= '<td>' . htmlspecialchars($rela->qnt_solicitada) . '</td>';
        } 

        // Verifica se a propriedade 'local' existe antes de exibi-la
        if (property_exists($rela, 'local')) {
            $html .= '<td>' . htmlspecialchars($rela->local) . '</td>';
        } else {
            $html .= '<td></td>'; // Caso contrário, exibe uma célula vazia
        }

        $html .= '<td>' . htmlspecialchars($rela->data) . ' - ' . htmlspecialchars($rela->hora) . '</td>
        </tr>';
    }

    $html .= '</tbody>
    </table>
    <script>
        window.onload = function() {
            window.print(); // Abre a janela de impressão ao carregar a página
        };
    </script>
</body>
</html>';

    return $html;
}



public function gerarVenda($nome): string {
    $this->verificarPermissaoAdmin();
    // Busca os registros de venda pelo nome da venda
    $registros = (new Busca())->busca(null, null, 'registro_vendas', "nome_venda = '{$nome}'", null, null);

    // Busca todos os produtos para mapear IDs aos nomes e fornecedores
    $produtos = (new Busca())->buscaLimitada(null, null, 'id, nome, fornecedor, unidade_contagem', 'produtos', null, 'id ASC', null);

    // Início do HTML
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SGE-SEMSA Relatório</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 12px;
            word-break: break-word;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h3>SGE-SEMSA Relatório Saída: ' . htmlspecialchars($nome) . '</h3>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qnt.</th>
                <th>Qnt Solicitada</th>
                <th>Local</th>
                <th>Data/Hora.</th>
            
            </tr>
        </thead>
        <tbody>';

    // Itera sobre os registros de venda
    foreach ($registros as $registro) {
        // Encontra o produto correspondente ao ID do registro
        $produtoEncontrado = null;
        foreach ($produtos as $produto) {
            if ($registro['produto_id'] == $produto['id']) {
                $produtoEncontrado = $produto;
                break;
            }
        }

        // Se encontrou o produto correspondente, exibe na tabela
        if ($produtoEncontrado) {
            $html .= '<tr>
                    <td>' . htmlspecialchars($produtoEncontrado['nome']) . ' - ' . htmlspecialchars($produtoEncontrado['fornecedor']) . ' - ' . htmlspecialchars($produtoEncontrado['unidade_contagem']) . '</td>
              
                <td>' . htmlspecialchars($registro['quantidade']) . '</td>
                    <td>' . htmlspecialchars($registro['qnt_solicitada']) . '</td>
                    <td>' . htmlspecialchars($registro['local']) . '</td>
                    <td>' . htmlspecialchars($registro['data']) . ' '.htmlspecialchars($registro['hora']) . '</td>
               
            </tr>';
        }
    }

    // Finaliza o HTML
    $html .= '</tbody>
    </table>
</body>
<script>
    window.onload = function() {
        window.print(); // Abre a janela de impressão ao carregar a página
    };
</script>
</html>';

    return $html;
}

public function gerarSaida($saida): string {
    $this->verificarPermissaoAdmin();
    // Busca os registros de venda pelo nome da venda
    $registros = (new Busca())->busca(null, null, 'registro_saida_sem_local', "nome_saida = '{$saida}'", null, null);

    // Busca todos os produtos para mapear IDs aos nomes e fornecedores
    $produtos = (new Busca())->buscaLimitada(null, null, 'id, nome, fornecedor, unidade_contagem', 'produtos', null, 'id ASC', null);

    // Início do HTML
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SGE-SEMSA Relatório</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 12px;
            word-break: break-word;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h3>SGE-SEMSA Relatório Saída Fora: ' . htmlspecialchars($saida) . '</h3>
    <table>
        <thead>
            <tr>
                <th>Produto</th>
                <th>Qnt.</th>
                <th>Qnt Solicitada</th>
                <th>Local</th>
                <th>Data/Hora.</th>
            
            </tr>
        </thead>
        <tbody>';

    // Itera sobre os registros de venda
    foreach ($registros as $registro) {
        // Encontra o produto correspondente ao ID do registro
        $produtoEncontrado = null;
        foreach ($produtos as $produto) {
            if ($registro['produto_id'] == $produto['id']) {
                $produtoEncontrado = $produto;
                break;
            }
        }

        // Se encontrou o produto correspondente, exibe na tabela
        if ($produtoEncontrado) {
            $html .= '<tr>
                    <td>' . htmlspecialchars($produtoEncontrado['nome']) . ' - ' . htmlspecialchars($produtoEncontrado['fornecedor']) . ' - ' . htmlspecialchars($produtoEncontrado['unidade_contagem']) . '</td>
              
                <td>' . htmlspecialchars($registro['quantidade']) . '</td>
                    <td>' . htmlspecialchars($registro['qnt_solicitada']) . '</td>
                    <td>' . htmlspecialchars($registro['local']) . '</td>
                    <td>' . htmlspecialchars($registro['data'])  . '</td>
               
            </tr>';
        }
    }

    // Finaliza o HTML
    $html .= '</tbody>
    </table>
</body>
<script>
    window.onload = function() {
        window.print(); // Abre a janela de impressão ao carregar a página
    };
</script>
</html>';

    return $html;
}

    
    
}