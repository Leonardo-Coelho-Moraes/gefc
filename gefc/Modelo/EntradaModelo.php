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
       (new Atualizar())->atualizar('registro_entrada', "produto_id = ?,quantidade = ?, editado = ?, user = ?",$dadosArray , $id);
       
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
public function contaRegistros() {
    $query = "SELECT COUNT(*) FROM registro_entrada";
    $stmt = Conexao::getInstancia()->prepare($query);
    $stmt->execute();

    // Use fetchColumn para obter o resultado da contagem diretamente
    $totalRegistros = $stmt->fetchColumn();
    return $totalRegistros;
}

   public function pesquisa(string $buscar) {
    // Use uma variável para armazenar o valor do slug com aspas simples
    $sol = '%' . $buscar . '%'; // Adicione % como curinga, se necessário

    // Use uma consulta preparada para evitar SQL injection
    $query = "SELECT * FROM registro_entrada WHERE data_hora LIKE :buscar ORDER BY data_hora DESC";

    // Prepare a consulta
    $stmt = Conexao::getInstancia()->prepare($query);

    // Associe o valor do parâmetro :buscar ao valor da variável $sol
    $stmt->bindParam(':buscar', $sol, PDO::PARAM_STR);

    // Execute a consulta preparada
    $stmt->execute();

    // Obtenha o resultado como um array associativo
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $resultado;
}



}
