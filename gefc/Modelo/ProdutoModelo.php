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
       // Tratamento dos dados
   
    $cod = isset($dados['cod_barras']) ? $dados['cod_barras'] : '';
    $resultados = Helpers::validadarDados($dados);
    
       $dadosArray = ['nome' => $dados['produto'],'cod_barras' => $cod,'slug' => Helpers::Mudar($resultados['produto'].'-'.uniqid(), [',', '.', '%','/'], '_'),'estoque' => $resultados['quantidade'],'validade' => $resultados['validade'],'fornecedor' => $resultados['fornecedor'],'unidade_contagem' => $dados['unicont']];

   if (strlen($dadosArray['nome']) < 2 ||  $dadosArray['fornecedor'] < 1 || $dadosArray['estoque'] < 1 ) {
        $mensagem = (new Mensagem)->erro('Preencha todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
        Helpers::redirecionar('produtos/produto_cadastrar');
        return; // Importante adicionar um "return" aqui para sair da função em caso de erro
    }

    // Chamada à função inserir com uma consulta preparada
    (new Inserir())->inserir(
        'produtos',
        'nome, cod_barras, slug, quantidade_estoque, validade, fornecedor, unidade_contagem',
        $dadosArray
    );
}

 public function atualizar(array $dados, int $id): void {
    // Tratamento dos dados
    $cod = isset($dados['cod_barras']) ? $dados['cod_barras'] : '';
    $resultados = Helpers::validadarDados($dados);
    // Criação do array de dados
    $dadosArray = [
        $dados['produto'],
        $cod,
        Helpers::Mudar($resultados['produto'].'-'.uniqid(), [',', '.', '%'], ''),      
        $resultados['quantidade'],
        $resultados['validade'],
        $resultados['fornecedor'],
        $dados['unicont'],
        1 //editado
    ];

 

    // Chamada à função atualizar com uma consulta preparada
    (new Atualizar())->atualizar(
        'produtos',
        "nome = ?, cod_barras = ?, slug = ?, quantidade_estoque = ?,validade = ?, fornecedor = ?, unidade_contagem = ?, editado = ?",
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
     $deletado ='' ;
      $nome = isset($ordem['nome']) ? $ordem['nome'] : '';
   
    $saida = isset($ordem['saida']) ? $ordem['saida'] : '';
    $validade = isset($ordem['validade']) ? $ordem['validade'] : 'Validade ASC';

    
  
$arrayOrdem=[$nome,$saida, $validade];
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
                 OR fornecedor = :fornecedor
                 OR cod_barras = :cod_barras)
          $deletado
          $ordenação
          LIMIT :limite OFFSET :inicio";  // Adicionado LIMIT e OFFSET
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->bindParam(':cod_barras', $buscar, PDO::PARAM_STR);
    $stmt->bindParam(':fornecedor', $buscar, PDO::PARAM_STR);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}



   }

