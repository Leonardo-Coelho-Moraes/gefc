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

    public function armazenar(array $dados): void
    {
        $resultados = Helpers::validadarDados($dados);

        $dadosArray = ['nome' => Helpers::Mudar($dados['produto'], [';'], ','), 'slug' => Helpers::Mudar(Helpers::slug($resultados['produto']) . '-' . uniqid(), [',', '.', '%', '/',' ', '+', '-','(',')'], '_'), 'unidade_contagem' => $dados['unicont']];

        if (strlen($dadosArray['nome']) < 2) {
            $mensagem = (new Mensagem)->erro('Preencha todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
            Helpers::redirecionar('produtos/produto_cadastrar');
            return; // Importante adicionar um "return" aqui para sair da função em caso de erro
        }

        // Chamada à função inserir com uma consulta preparada
        (new Inserir())->inserir(
            'produtos',
            'nome, slug, unidade_contagem',
            $dadosArray
        );
    }

    public function atualizar(array $dados): void
    {
        // Tratamento dos dados
        $resultados = Helpers::validadarDados($dados);
        // Criação do array de dados
        $dadosArray = [
            $dados['produto_edit'],
            Helpers::Mudar(Helpers::slug($resultados['produto_edit']) . '-' . uniqid(), [',', '.', '%', '/', '?',' ', '+','-','(',')'], ''),
            $dados['unicont_edit'],
        ];



        // Chamada à função atualizar com uma consulta preparada
        (new Atualizar())->atualizar(
            'produtos',
            "nome = ?, slug = ?, unidade_contagem = ?",
            $dadosArray,
            $dados['produto_id']
        );
    }

    public function correcao()
    {
        $palavra = '?';
        $correcao = 'Fralda Descartável';
        $conexao = Conexao::getInstancia();

        $query = "SELECT id, nome FROM produtos WHERE nome LIKE '%$palavra%'";
        $stmt = $conexao->prepare($query);
        $stmt->execute();

        // Busca todos os resultados  
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($produtos as $produto) {
            // Para cada produto, atualizamos o nome  
            $novoNome = str_replace("$palavra", "$correcao", $produto['nome']);
            $updateQuery = "UPDATE produtos SET nome = :novoNome WHERE id = :id";

            $updateStmt = $conexao->prepare($updateQuery);
            $updateStmt->bindParam(':novoNome', $novoNome);
            $updateStmt->bindParam(':id', $produto['id']);

            $updateStmt->execute();
        }  
    }
 
public function pesquisa(string $buscar) {
    $conexao = Conexao::getInstancia();

    $query = "SELECT * FROM produtos
              WHERE nome LIKE :buscar ";

    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
        
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
    return $resultado;

}




   }

