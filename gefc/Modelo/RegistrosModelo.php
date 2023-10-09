<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
namespace gefc\Modelo;
/**
 * Description of PostLocal
 *
 * @author Leonardo
 */
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Conexao;
class RegistrosModelo {


public function atualizar(array $dados, int $id): void {
    $resultados = Helpers::validadarDados($dados);
    $user = UsuarioControlador::usuario()->nome;
    $local = isset($resultados['locaios($dados);
    $s']) ? $resultados['locais'] : 6;
    
               
               $dadosArray = array('produto' => $resultados['produto'] , 'locais' => $local , 'quantidade' =>   $resultados['quantidade'], 'editado' => 1, 'user' => $user);
               
     
     if ($resultados['produto'] < 0 ||  $resultados['quantidade'] < 1 ) {
        $mensagem = (new Mensagem)->erro('Erro Verificar os dados enviador em forma de int')->flash();
        Helpers::redirecionar('registros');
        return; // Importante adicionar um "return" aqui para sair da função em caso de erro
    }
   (new Atualizar())->atualizar('registros', "produto_id = ?, local_id = ?, quantidade = ?, editado = ?, user =?", $dadosArray, $id);
}

    public function contaRegistros() {
        $query = "SELECT COUNT(*) as total FROM registros";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
        return $resultado->total; // Acesse a propriedade diretamente
    }
    public function contaRegistrosIdLocal($id) {
        $query = "SELECT COUNT(*) as total FROM registros WHERE local_id = {$id}";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
        return $resultado->total; // Acesse a propriedade diretamente
    }
  public function contaRegistrosIdProduto($id) {
        $query = "SELECT COUNT(*) as total FROM registros WHERE produto_id = {$id}";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
        return $resultado->total; // Acesse a propriedade diretamente
    }
 
public function calculaPorcentagemMudanca(int $id) {
    // Calcular o total de registros para o mês atual
    $queryAtual = "SELECT COUNT(*) as total FROM registros WHERE local_id = {$id} AND MONTH(data_hora) = MONTH(CURRENT_DATE()) AND YEAR(data_hora) = YEAR(CURRENT_DATE())";
    $stmtAtual = Conexao::getInstancia()->query($queryAtual);
    $resultadoAtual = $stmtAtual->fetch();
    $totalAtual = $resultadoAtual['total'];

    // Calcular o total de registros para o mês anterior
    $queryAnterior = "SELECT COUNT(*) as total FROM registros WHERE local_id = {$id} AND MONTH(data_hora) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(data_hora) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)";
    $stmtAnterior = Conexao::getInstancia()->query($queryAnterior);
    $resultadoAnterior = $stmtAnterior->fetch();
    $totalAnterior = $resultadoAnterior['total'];

    // Calcular a diferença entre os totais
    $diferenca = $totalAtual - $totalAnterior;

    // Calcular a porcentagem de mudança
    $porcentagem = ($diferenca / $totalAnterior) * 100;

    return $porcentagem;
}
public function somarQuantidades(string $tabela, ?string $termo = null): int {
    $termoClause = ($termo ? "WHERE $termo" : '');

    $query = "SELECT SUM(quantidade) as total_quantidade FROM {$tabela} {$termoClause}";
    $stmt = Conexao::getInstancia()->query($query);

    // Use fetchColumn() para obter diretamente o valor da soma
    $resultado = $stmt->fetchColumn();

    return (int) $resultado; // Certifique-se de converter para int
}

}
