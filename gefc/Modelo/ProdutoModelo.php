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
use gefc\Controlador\UsuarioControlador;
use gefc\Modelo\Atualizar;
use gefc\Modelo\Inserir;
class ProdutoModelo {
  
 public function armazenar(array $dados): void {
       // Tratamento dos dados
    $controlado = isset($dados['controlado']) ? $dados['controlado'] : 0;
    $cod = isset($dados['cod_barras']) ? $dados['cod_barras'] : '';
    $preco = floatval(str_replace(',', '.', $dados['preco']));
    $preco_compra = floatval(str_replace(',', '.', $dados['preco_compra']));
    $resultados = Helpers::validadarDados($dados);
    
       $dadosArray = ['nome' => $dados['produto'],'cod_barras' => $cod,'slug' => Helpers::Mudar($resultados['produto'].'-'.uniqid(), [',', '.', '%'], ''),'fabricante' => $resultados['fabricante'],'tipo_embalagem' => $resultados['embalagem'],'unidades' => $resultados['unidade_embalagem'],'tipo_medicamento' => $dados['tipo_medicamento'],'categoria' => $dados['categoria'],'controlado' => $controlado,'estoque' => $resultados['quantidade'],'lote' => $resultados['lote'],'validade' => $resultados['validade'],'preco' => $preco,'preco_compra' => $preco_compra,'fornecedor' => $resultados['fornecedor'],'peso' => $dados['peso'],'unidade_medida' => $dados['unidade_medida'],'observacao' => ($resultados['nota']) ? $resultados['nota'] : ''];

   if (strlen($dadosArray['nome']) < 2 || strlen($dadosArray['fabricante']) < 2 || $dadosArray['fornecedor'] < 1 || $dadosArray['estoque'] < 1 || strlen($dadosArray['lote']) < 1 || strlen($dadosArray['fornecedor']) < 2) {
        $mensagem = (new Mensagem)->erro('Preencha todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
        Helpers::redirecionar('produtos/produto_cadastrar');
        return; // Importante adicionar um "return" aqui para sair da função em caso de erro
    }

    // Chamada à função inserir com uma consulta preparada
    (new Inserir())->inserir(
        'produtos',
        'nome, cod_barras, slug, fabricante, tipo_embalagem, unidades_embalagem, tipo_medicamento, categoria, controlado, quantidade_estoque, lote, validade, preco, preco_compra, fornecedor, peso, unidade_medida, observacao',
        $dadosArray
    );
}

 public function atualizar(array $dados, int $id): void {
    // Tratamento dos dados
    $cod = isset($dados['cod_barras']) ? $dados['cod_barras'] : '';
    $controlado = isset($dados['controlado']) ? $dados['controlado'] : 0;

    $preco = floatval(str_replace(',', '.', $dados['preco']));
    $preco_compra = floatval(str_replace(',', '.', $dados['preco_compra']));

    $resultados = Helpers::validadarDados($dados);

    // Criação do array de dados
    $dadosArray = [
        $dados['produto'],
        $cod,
        Helpers::Mudar($resultados['produto'].'-'.uniqid(), [',', '.', '%'], ''),
        $resultados['fabricante'],
        $resultados['embalagem'],
        $resultados['unidade_embalagem'],
        $dados['tipo_medicamento'],
        $dados['categoria'],
        $controlado,
        $resultados['quantidade'],
        $resultados['lote'],
        $resultados['validade'],
        $preco,
        $preco_compra,
        $resultados['fornecedor'],
        $dados['peso'],
        $dados['unidade_medida'],
        $resultados['nota'] ?: null,
        1 // Editado
    ];

    // Verificação de campos inválidos
    if (strlen($dadosArray[0]) < 2 || strlen($dadosArray[3]) < 2 || strlen($dadosArray[4]) < 2 || $dadosArray[5] < 1 || $dadosArray[9] < 1 || strlen($dadosArray[10]) < 1 || strlen($dadosArray[14]) < 2) {
        $mensagem = (new Mensagem)->erro('Edite todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
        return; // Importante adicionar um "return" aqui para sair da função em caso de erro
    }

    // Chamada à função atualizar com uma consulta preparada
    (new Atualizar())->atualizar(
        'produtos',
        "nome = ?, cod_barras = ?, slug = ?, fabricante = ?, tipo_embalagem = ?, unidades_embalagem = ?, tipo_medicamento = ?, categoria = ?, controlado = ?, quantidade_estoque = ?, lote = ?, validade = ?, preco = ?, preco_compra = ?, fornecedor = ?, peso = ?, unidade_medida = ?, observacao = ?, editado = ?",
        $dadosArray,
        $id
    );
}

public function deletar(int $id): void {
    $deletado = 1;

    // Use uma consulta preparada para evitar SQL injection
    $query = "UPDATE produtos SET deletado = :deletado WHERE id = :id";
    $stmt = Conexao::getInstancia()->prepare($query);

    // Associe os valores dos parâmetros
    $stmt->bindParam(':deletado', $deletado, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute a consulta preparada
    $stmt->execute();
}

 public function pesquisa(string $buscar, ?int $pagina, ?int $limite, ?array $ordem = null) {
     $deletado =(UsuarioControlador::usuario()->nivel_acesso >2 ? "" : "AND (deletado != 1 OR deletado IS NULL)" ) ;
      $nome = isset($ordem['nome']) ? $ordem['nome'] : '';
    $controlado = isset($ordem['controlado']) ? $ordem['controlado'] : '';
    $estoque = isset($ordem['estoque']) ? $ordem['estoque'] : '';
    $preco = isset($ordem['preco']) ? $ordem['preco'] : '';
    $saida = isset($ordem['saida']) ? $ordem['saida'] : '';
    $validade = isset($ordem['validade']) ? $ordem['validade'] : 'Validade ASC';

    
   $categoria = '';
if (isset($ordem['categoria']) and !empty($ordem['categoria'])) {
    $OrdemCategoria =$ordem['categoria'];
    $categoria = "CASE WHEN categoria = '{$OrdemCategoria}' THEN 1 ELSE 2 END";
}
     $tipo_medicamento = '';
     if (isset($ordem['tipo']) and !empty($ordem['tipo'])) {
    $OrdemTipo =$ordem['tipo'];
    $tipo_medicamento = "CASE WHEN tipo_medicamento = '{$OrdemTipo}' THEN 1 ELSE 2 END";
}
     
     $arrayOrdem=[$categoria,$tipo_medicamento, $nome, $controlado, $estoque, $preco, $saida, $validade];
     $ordenação = "ORDER BY ";

$itensOrdenação = [];

foreach ($arrayOrdem as $item) {
    if (!empty($item)) {
        $itensOrdenação[] = $item;
    }
}

$ordenação .= implode(', ', $itensOrdenação);

// Verifica se há itens na ordenação e remove a vírgula final, se necessário
if (!empty($itensOrdenação)) {
    $ordenação = rtrim($ordenação, ',');
}
    $conexao = Conexao::getInstancia();

    // Calcular o valor de início com base na página e no limite
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    $query = "SELECT * FROM produtos 
              WHERE (nome LIKE :buscar 
                     OR fabricante LIKE :buscar 
                     OR tipo_embalagem LIKE :buscar 
                      OR categoria LIKE :buscar 
                      OR  tipo_medicamento LIKE :buscar 
                      OR  cod_barras LIKE :buscar   
                     OR unidades_embalagem LIKE :buscar
                     OR lote LIKE :buscar 
                     OR fornecedor LIKE :buscar)
             $deletado
              $ordenação
              LIMIT :limite OFFSET :inicio";  // Adicionado LIMIT e OFFSET
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}



   }

