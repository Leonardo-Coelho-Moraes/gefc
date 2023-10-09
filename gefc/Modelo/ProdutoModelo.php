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
use PDO;
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use gefc\Nucleo\Conexao;
use gefc\Modelo\Atualizar;
use gefc\Modelo\Inserir;
class ProdutoModelo {
  
 public function armazenar(array $dados): void {
       if (isset($dados['controlado'])) {

    $controlado = $dados['controlado'];

} else {
   $controlado = 0;
}
if (isset($dados['cod_barras'])) {

    $cod = $dados['cod_barras'];

} else {
   $cod = '';
}
     $preco = str_replace(',', '.', $dados['preco']);
$preco_compra = str_replace(',', '.', $dados['preco_compra']);
    $resultados = Helpers::validadarDados($dados);
       $dadosArray = array(
    'nome' => $dados['produto'],
           'cod_barras' => $cod,
    'slug' => Helpers::Mudar($resultados['produto'].'-'. uniqid(), [',','.','%'],''),
    'fabricante' => $resultados['fabricante'],
    'tipo_embalagem' => $resultados['embalagem'],
    'unidades' => $resultados['unidade_embalagem'],
    'tipo_medicamento' =>  $dados['tipo_medicamento'],       
    'categoria' =>  $dados['categoria'],
    'controlado' => $controlado,
    'estoque' => $resultados['quantidade'],
    'lote' => $resultados['lote'],
    'validade' => $resultados['validade'],
    'preco' => floatval($preco),
    'preco_compra' => floatval($preco_compra),
    'fornecedor' => $resultados['fornecedor'],
    'peso' => $dados['peso'],
    'unidade_medida' => $dados['unidade_medida'],
    'observacao' => ($resultados['nota'] ? $resultados['nota'] : '')
);
    // Verificação de campos inválidos
    if (strlen($dadosArray['nome']) < 2 || strlen($dadosArray['fabricante']) < 2 ||  $dadosArray['fornecedor'] < 1  || $dadosArray['estoque'] < 1 || strlen($dadosArray['lote']) < 1 || strlen($dadosArray['fornecedor']) < 2) {
        $mensagem = (new Mensagem)->erro('Preencha todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
        Helpers::redirecionar('produtos/produto_cadastrar');
        return; // Importante adicionar um "return" aqui para sair da função em caso de erro
    }
    (new Inserir())->inserir('produtos', "nome, cod_barras, slug, fabricante, tipo_embalagem, unidades_embalagem, tipo_medicamento, categoria, controlado , quantidade_estoque, lote, validade, preco, preco_compra, fornecedor,peso,unidade_medida, observacao", $dadosArray);
}

   public function atualizar(array $dados, int $id): void {
       if (isset($dados['cod_barras'])) {

    $cod = $dados['cod_barras'];

} else {
   $cod = '';
}
       if (isset($dados['controlado'])) {

    $controlado = $dados['controlado'];

} else {
   $controlado = 0;
}

         $preco = str_replace(',', '.', $dados['preco']);
         
$preco_compra = str_replace(',', '.', $dados['preco_compra']);
       $resultados = Helpers::validadarDados($dados);
       $dadosArray = array(
    'nome' => $dados['produto'],
    'cod_barras' => $cod,
    'slug' => Helpers::Mudar($resultados['produto'].'-'. uniqid(), [',','.','%'],''),
    'fabricante' => $resultados['fabricante'],
    'tipo_embalagem' => $resultados['embalagem'],
    'unidades' => $resultados['unidade_embalagem'],
    'tipo_medicamento' =>  $dados['tipo_medicamento'],       
    'categoria' =>  $dados['categoria'],
    'controlado' =>  $controlado,
    'estoque' => $resultados['quantidade'],
    'lote' => $resultados['lote'],
    'validade' => $resultados['validade'],
    'preco' => floatval($preco),
    'preco_compra' => floatval($preco_compra),
    'fornecedor' => $resultados['fornecedor'],
    'peso' => $dados['peso'],
    'unidade_medida' => $dados['unidade_medida'],
    'observacao' => ($resultados['nota']? $resultados['nota'] :null),
    'editado' => 1
);
    // Verificação de campos inválidos
    if (strlen($dadosArray['nome']) < 2 || strlen($dadosArray['fabricante']) < 2 || strlen($dadosArray['tipo_embalagem']) < 2 || $dadosArray['unidades'] < 1 || $dadosArray['estoque'] < 1 || strlen($dadosArray['lote']) < 1 || strlen($dadosArray['fornecedor']) < 2) {
        $mensagem = (new Mensagem)->erro('Edite todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
       // Helpers::redirecionar('produtos/editar/'.$dadosArray['slug'].'/'.$id);
        return; // Importante adicionar um "return" aqui para sair da função em caso de erro
    }
    
        (new Atualizar())->atualizar('produtos', "nome = ?,cod_barras =?, slug = ?, fabricante = ?, tipo_embalagem = ?, unidades_embalagem = ?,tipo_medicamento = ?, categoria = ?, controlado = ?, quantidade_estoque = ?, lote = ?, validade = ?,  preco = ?, preco_compra = ?, fornecedor = ?, peso = ?, unidade_medida = ?, observacao = ?, editado = ?", $dadosArray, $id);
    
}
public function deletar(int $id): void {
    $deletado = 1;
    $query = "UPDATE produtos SET deletado = $deletado WHERE `produtos`.`id` = {$id}";
     $stmt = Conexao::getInstancia()->prepare($query);

    $stmt->execute();
    
}
 public function contaRegistros() {
        $query = "SELECT COUNT(*) as total FROM produtos";
        $stmt = Conexao::getInstancia()->query($query);
        $resultado = $stmt->fetch(); // Use fetch() em vez de fetchAll()
        return $resultado->total; // Acesse a propriedade diretamente
    }
   public function pesquisa(string $buscar) {
    $conexao = Conexao::getInstancia();
    
    $query = "SELECT * FROM produtos 
              WHERE nome LIKE :buscar 
              OR fabricante LIKE :buscar 
              OR tipo_embalagem LIKE :buscar 
              OR unidades_embalagem LIKE :buscar 
              OR tipo_produtos LIKE :buscar 
              OR modelo LIKE :buscar 
              OR lote LIKE :buscar 
              OR fornecedor LIKE :buscar 
              ORDER BY nome DESC";
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}


   }

