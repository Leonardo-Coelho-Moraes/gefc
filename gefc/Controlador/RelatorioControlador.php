<?php

namespace gefc\Controlador;

/**
 * Description of SiteControlador
 *
 * @author Leonardo
 */

use FPDF;
use gefc\Modelo\Busca;
use gefc\Modelo\RelatorioModelo;
use gefc\Nucleo\Controlador;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Sessao;
use gefc\Controlador\UsuarioControlador;

class RelatorioControlador extends Controlador {
     private $sessao;
     protected $user;

    public function __construct() {
        parent::__construct('templates/site/views'); 
        
        $this->user = UsuarioControlador::usuario()->nome; 
        $this->sessao = new Sessao();
    }
    public function relatorio(): void {
        
        $relatorio = [];
        $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        if (isset($dados)) {
            $relatorio = (new RelatorioModelo())->buscaRegistros($dados);
            if(empty($relatorio)){
              $this->mensagem->erro('Registros não exitem, consulte o banco de dados!')->flash();
            }
      
    }

        $this->sessao->criar('relatorio', $relatorio);
        echo $this->template->renderizar('relatorio.html', [ 
        'titulo' => 'SGE-SEMSA Relatório',
        'relatorio' => $relatorio]);
    }
    public function download(): void {
   
        $relatorio = $this->sessao->carregar()->relatorio;
        $data = date('d/m/Y H:i:s');
        if (!empty($relatorio)) {
            $pdf = new FPDF('L');
            $pdf->AddPage();

            // Adicione o texto "COARI - AM" à esquerda
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 1, utf8_decode('COARI - AM'), 0, 1, 'L');
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, utf8_decode('Arquivo gerado as: '. $data.' por usuário '. $this->user), 0, 1, 'L');
            $pdf->SetFont('Arial', 'B', 10);

            // Defina a posição vertical para a linha abaixo do texto
            $y = $pdf->GetY() + 5; // 10 é a altura do texto

            // Restante do seu código para a tabela
            $pageWidth = $pdf->GetPageWidth();
            $tableWidth = 60 + 19 + 40 + 30 + 18 + 47 + 12 + 36;

            // Calcula a posição central horizontal para a tabela
            $tableStartX = ($pageWidth - $tableWidth) / 2;
        
            // Define a posição vertical para a tabela
            $pdf->SetXY($tableStartX, $y);

            // Cabeçalho da tabela
            $pdf->Cell(60, 10, utf8_decode('Nome'), 1, 0, 'C', false, '', 14);
            $pdf->Cell(19, 10, utf8_decode('Ação'), 1, 0, 'C', false, '', 14);
            $pdf->Cell(40, 10, utf8_decode('Fabricante'), 1, 0, 'C', false, '', 14);
            $pdf->Cell(30, 10, utf8_decode('Fornecedor'), 1, 0, 'C', false, '', 14);
            $pdf->Cell(18, 10, utf8_decode('Qnt.'), 1, 0, 'C', false, '', 14);
          
            $pdf->Cell(19, 10, utf8_decode('Lote'), 1, 0, 'C', false, '', 14);
            $pdf->Cell(36, 10, utf8_decode('Data'), 1, 0, 'C', false, '', 14);
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 10);
            
            // Conteúdo da tabela
            foreach ($relatorio as $registro) {
                // Posiciona o cursor no centro horizontal da página
                $pdf->SetXY($tableStartX, $pdf->GetY());
                $pdf->Cell(60, 7, $this->validação($registro->nome, 30), 1);
                $pdf->Cell(19, 7, ucfirst($registro->acao), 1);
                $pdf->Cell(40, 7, $this->validação($registro->fabricante, 16), 1);
                $pdf->Cell(30, 7, $this->validação($registro->fornecedor, 12), 1);
                $pdf->Cell(18, 7, $registro->quantidade, 1);
           
                $pdf->Cell(19, 7, $registro->lote, 1);
                $pdf->Cell(36, 7, $registro->data_hora, 1);
                $pdf->Ln();
            }
             $pdf->Ln();
            $pdf->SetFont('Arial', '', 5);
            $pdf->Cell(0, 1, utf8_decode('Sistema Desenvolvido por Leonardo Coelho'), 0, 1, 'R');
            $pdf->Output();
            exit();
        } else {
        // Redirecionar ou mostrar uma mensagem de erro se os dados do relatório não estiverem disponíveis
        }
    }
    protected function validação(string $valor, int $tamanho) {
        return ucfirst(Helpers::slug(Helpers::tirarTraco(Helpers::reduzirTexto($valor, $tamanho)))) ;
    }
}