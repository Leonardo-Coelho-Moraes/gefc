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
use PDOException;
class ProdutoModelo {

    public function armazenar(array $dados): void
    {

      $crit = intval($dados['crit']) * 2;
      $preco = floatval($dados['preco']);

        $dadosArray = ['nome' => Helpers::Mudar($dados['produto'], [';'], ','), 
        'slug' => Helpers::Mudar(Helpers::slug($dados['produto']) . '-' . uniqid(), [',', '.', '%', '/',':',' ', '+', '-','(',')'], '_'), 
        'unidade_contagem' => $dados['unicont'], 
         $dados['tipos'], $crit,
         $dados['qnt1'], $preco, $dados['qnt_for'], $dados['qnt_cema']

        ];

        if (strlen($dadosArray['nome']) < 2) {
            $mensagem = (new Mensagem)->erro('Preencha todos os campos corretamente, os números precisam ser maiores ou iguais a um e os nomes maiores ou iguais a 2 para as informações serem redundantes!')->flash();
            Helpers::redirecionar('produtos/produto_cadastrar');
            return; // Importante adicionar um "return" aqui para sair da função em caso de erro
        }

        // Chamada à função inserir com uma consulta preparada
        (new Inserir())->inserir(
            'produtos',
            'nome, slug, unidade_contagem, tipo, qnt_crit,qnt1,preco,qnt_for,qnt_cema',
            $dadosArray
        );
    }
    public function criarCodBarras(int $produto, string $cod){
        
        (new Inserir())->inserir(
            'cod_barras',
            'produto_id, cod_barras',
            [$produto,$cod]
        );
    }

    public function atualizar(array $dados): void
    { $preco = floatval($dados['preco']);
        // Tratamento dos dados
        $crit = intval($dados['crit_edit']);
        $dadosArray = [
            $dados['produto_edit'],
            Helpers::Mudar(Helpers::slug($dados['produto_edit']) . '-' . uniqid(), [',', '.', '%',':', '/', '?',' ', '+','-','(',')'], ''),
            $dados['unicont_edit'],
            $dados['tipos_edit'],
            $crit,
            $dados['qnt1_edit'], $preco, $dados['qnt_for_edit'], $dados['qnt_cema_edit']

        ];



        // Chamada à função atualizar com uma consulta preparada
        (new Atualizar())->atualizar(
            'produtos',
            "nome = ?, slug = ?, unidade_contagem = ?, tipo = ?, qnt_crit=?,qnt1=?,preco=?,qnt_for=?,qnt_cema=?",
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

    $query = "
   SELECT 
    produtos.id, 
    produtos.nome, 
    produtos.slug, 
    produtos.unidade_contagem, 
    produtos.tipo, 
    produtos.qnt_crit,
    produtos.qnt1 AS total_quantidade,
     produtos.preco,
      produtos.qnt_for,
       produtos.qnt_cema
FROM 
    produtos
WHERE 
    produtos.nome LIKE CONCAT('%', :buscar, '%') OR produtos.id LIKE CONCAT('%', :buscar, '%')
GROUP BY 
    produtos.id LIMIT 40;

";

    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);

        
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
    return $resultado;

}
public function pesquisaCodBarras(string $buscar) {
    $conexao = Conexao::getInstancia();

    $query = "
   SELECT 
    cod_barras.id,
    produtos.nome, 
   cod_barras.produto_id,
   cod_barras.cod_barras
FROM 
    produtos
     JOIN
            cod_barras ON produtos.id  = cod_barras.produto_id
WHERE 
    produtos.nome LIKE CONCAT('%', :buscar, '%') OR produtos.id LIKE CONCAT('%', :buscar, '%') OR  cod_barras.cod_barras LIKE CONCAT('%', :buscar, '%')
 LIMIT 100;

";

    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);

        
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
    return $resultado;

}


    public function pesquisaNivelCritico()
    {
        $conexao = Conexao::getInstancia();
        $query = "SELECT 
    produtos.id, 
    produtos.nome
  
FROM 
    produtos 
    WHERE produtos.crit_nivel = 0
";
        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function pesquisaCrit(?string $filtro = 'todos', ? string $pesquisa = '', ?string $tipo = '')
    {
        $conexao = Conexao::getInstancia();
    
        $query = "
      SELECT 
            p.id, 
            p.nome, 
            p.slug,
            p.unidade_contagem AS unidade, 
            p.tipo, 
            p.qnt_crit,
           p.qnt1 AS total_quantidade,
        
              p.qnt_for,
       p.qnt_cema
        FROM 
            produtos p
        LEFT JOIN 
            lote l ON p.id = l.produto_id 
        WHERE 
            1 = 1 AND p.tipo LIKE :tipo 
                
        "; 
    
        // Se houver uma pesquisa por nome de produto
        if (!empty($pesquisa)) {
            $query .= " AND p.nome LIKE :pesquisa OR p.id LIKE :pesquisa ";
        }
    
        // Adiciona o filtro específico
        if ($filtro == 'com_lote') {
            $query .= " AND l.produto_id IS NOT NULL ";
        }
    
        // Adiciona o HAVING para produtos críticos
        if ($filtro == 'critico') {
            $query .= " HAVING total_quantidade <= p.qnt_crit AND p.qnt_crit > 0 ";
        }
    
        // Agrupa os produtos
        $query .= " GROUP BY p.id, p.nome, p.slug, p.unidade_contagem, p.tipo, p.qnt_crit   ORDER BY   
        P.nome ASC; ";
    
        try {
            $stmt = $conexao->prepare($query);
    
            // Passa o valor da pesquisa, se houver
            if (!empty($pesquisa)) {
                $stmt->bindValue(':pesquisa', '%' . $pesquisa . '%', PDO::PARAM_STR);
            }
            $stmt->bindValue(':tipo', '%' . $tipo . '%', PDO::PARAM_STR);
           
    
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            return $resultado;
        } catch (PDOException $e) {
            // Caso ocorra algum erro, você pode capturar e logar a exceção
            echo "Erro: " . $e->getMessage();
            return [];
        }
    }
    
    public function criarTipo(string $tipo): void
    {
        (new Inserir())->inserir(
            'tipo_produto',
            'nome',
            [$tipo]
        );
    }


    public function definirNivel(array $dados): void
    {
        // Verifique se os dados foram fornecidos corretamente
        $nivel = $dados['nivel'];
        foreach ($dados as $key => $value) {
            if (strpos($key, 'produto') === 0) {
                $index = str_replace('produto', '', $key);
                $produtoId = intval($value);
                    $query = "UPDATE produtos SET crit_nivel = :crit_nivel WHERE id = :id";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':crit_nivel', $nivel, PDO::PARAM_INT);
                    $stmt->bindParam(':id', $produtoId, PDO::PARAM_INT);
                    $stmt->execute();

               

           
            }
        }
    }


    public function pesquisaProduto(?string $id)
    {
        $conexao = Conexao::getInstancia();

        $query = "
        SELECT  
         produtos.id,
            produtos.nome,
             produtos.unidade_contagem,
              produtos.tipo,
               produtos.qnt_crit,
                lote.produto_id,
                lote.id AS cod,
                lote.quantidade, 
                lote.lote,
                lote.fornecedor,
                lote.localizacao,
                lote.vencimento

        FROM 
            produtos
        JOIN 
            lote ON produtos.id  = lote.produto_id
        WHERE
            produtos.id = :id
    ";

        $stmt = $conexao->prepare($query);
        // Utilizando '%' para buscas parciais em todos os campos
        $stmt->bindValue(':id', $id, PDO::PARAM_STR);

        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }

   }

