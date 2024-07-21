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
use gefc\Nucleo\Mensagem;
use gefc\Nucleo\Helpers;
use gefc\Modelo\Inserir;
use gefc\Modelo\Atualizar;
use gefc\Nucleo\Conexao;
use PDO;
class EntradaModelo {
public function entrada(array $dados): void {
    $id = Helpers::validarNumero($dados['produto']);
    $quantidade = Helpers::validarNumero($dados['quantidade']);

    // Verificar se a quantidade é válida
    if ($quantidade <= 0) {
        $mensagem = (new Mensagem)->erro('A quantidade precisa ser maior ou igual a 1')->flash();
        Helpers::redirecionar('entrada/adicionar');
        return; // Encerre a função se a quantidade for inválida
    }
    
    $dadosArray = ['quantidade' => $quantidade];
    $query = "UPDATE produtos SET quantidade_estoque = quantidade_estoque + :quantidade WHERE id = :id";
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    $stmt->execute();
}
public function atualizar(array $dados, int $id): void {
      
        $busca = (new Busca())->buscaId('produtos',$dados['produto']);
       $dadosArray = [
           'produto' => $dados['produto'],
           'produto_nome' => $busca->nome,
           'quantidade' => $dados['quantidade'],
           'editado' => 1
          
       ];
       (new Atualizar())->atualizar('registro_entrada', "produto_id = ?,produto_nome = ?,quantidade = ?, editado = ?",$dadosArray ,$id); //observe que mudei de $id para id = $id
       //!!! muito importante, deve fazer uma equação pra edição da quantidade no estoque,
       
}

 public function entradaRegisto(array $dados): void {
       $resultados = Helpers::validadarDados($dados);
                
                $busca = (new Busca())->buscaId('produtos',$dados['produto']);
               $array = array('produto' => $resultados['produto'] ,'produto_nome' => $busca->nome ,'quantidade' =>   $resultados['quantidade']);
      if ($resultados['quantidade'] < 1) {
           $mensagem = (new Mensagem)->erro('A quantidade precisa ser maior ou igual 1')->flash();
           Helpers::redirecionar('entrada/adicionar');
           return;
        }
       (new Inserir())->inserir('registro_entrada', 'produto_id ,produto_nome, quantidade', $array);
      
}

   public function pesquisa(string $buscar, ?int $pagina, ?int $limite) {
    $conexao = Conexao::getInstancia();
    // Calcular o valor de início com base na página e no limite
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    $query = "SELECT * FROM registro_entrada
              WHERE (produto_nome LIKE :buscar)
              ORDER BY data DESC
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
