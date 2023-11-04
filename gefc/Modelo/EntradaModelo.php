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
use gefc\Controlador\UsuarioControlador;
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
       $user = UsuarioControlador::usuario()->nome;
       $dadosArray = [
           'produto' => $dados['produto'],
           'quantidade' => $dados['quantidade'],
           'editado' => 1,
           'user' => $user
       ];
       (new Atualizar())->atualizar('registro_entrada', "produto_id = ?,quantidade = ?, editado = ?, user = ?",$dadosArray ,$id); //observe que mudei de $id para id = $id
       
}

 public function entradaRegisto(array $dados): void {
       $resultados = Helpers::validadarDados($dados);
                $user = UsuarioControlador::usuario()->nome;
               $array = array('produto' => $resultados['produto'] , 'quantidade' =>   $resultados['quantidade'], 'user' => $user);
      if ($resultados['quantidade'] < 1) {
           $mensagem = (new Mensagem)->erro('A quantidade precisa ser maior ou igual 1')->flash();
           Helpers::redirecionar('entrada/adicionar');
           return;
        }
       (new Inserir())->inserir('registro_entrada', 'produto_id , quantidade, user', $array);
      
}

   public function pesquisa(string $buscar, ?int $pagina, ?int $limite) {
    $conexao = Conexao::getInstancia();
    // Calcular o valor de início com base na página e no limite
    $inicio = ($pagina !== null && $limite !== null) ? (($pagina - 1) * $limite) : 0;

    $query = "SELECT * FROM registro_entrada
              WHERE (produto_id LIKE :buscar 
                     OR ano LIKE :buscar 
                     OR data LIKE :buscar 
                      OR hora LIKE :buscar 
                    )
              
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
