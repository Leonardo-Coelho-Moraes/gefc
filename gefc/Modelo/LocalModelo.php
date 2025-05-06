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
use gefc\Modelo\Inserir;

use PDO;
class LocalModelo {


    public function inserirLotesComSaidas()
    {
        $conexao = Conexao::getInstancia();

        // Consulta para selecionar os lotes que não estão na tabela entradas
        $query = "
        SELECT 
            lote.id,
            lote.quantidade
        FROM 
            lote
        LEFT JOIN 
            registro_entrada ON lote.id = registro_entrada.lote_id
        WHERE 
            registro_entrada.lote_id IS NULL
    ";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados (lotes que não têm entrada)
        $lotesFaltantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = '2024-08-01'; // Data fixa para inserção (pode ser dinâmica, se necessário)

        // Iterar sobre cada lote que não tem entrada
        foreach ($lotesFaltantes as $lote) {
            $loteId = $lote['id'];
            $quantidadeLote = $lote['quantidade'];

            // Verificar se esse lote tem saídas registradas nas duas tabelas e somar as quantidades
            $saidaQuery = "
            SELECT 
                COALESCE(SUM(rv.quantidade), 0) AS total_vendas,
                COALESCE(SUM(rssl.quantidade), 0) AS total_sem_local
            FROM 
                lote
            LEFT JOIN 
                registro_vendas rv ON lote.id = rv.lote_id
            LEFT JOIN 
                registro_saida_sem_local rssl ON lote.id = rssl.lote_id
            WHERE 
                lote.id = :lote_id
        ";

            // Preparação da query de pesquisa das saídas
            $saidaStmt = $conexao->prepare($saidaQuery);
            $saidaStmt->bindParam(':lote_id', $loteId, PDO::PARAM_INT);

            // Execução da pesquisa de saídas
            $saidaStmt->execute();

            // Obtenção dos totais de saídas das duas tabelas
            $resultSaidas = $saidaStmt->fetch(PDO::FETCH_ASSOC);
            $totalVendas = $resultSaidas['total_vendas'] ?? 0;
            $totalSemLocal = $resultSaidas['total_sem_local'] ?? 0;

            // Somar todas as saídas à quantidade do lote
            $quantidadeLote += ($totalVendas + $totalSemLocal);

            // Query de inserção na tabela entradas com a nova quantidade
            $insertQuery = "
            INSERT INTO registro_entrada (lote_id, quantidade, data, editado)
            VALUES (:lote_id, :quantidade, :data, 0)
        ";

            // Preparação da query de inserção
            $insertStmt = $conexao->prepare($insertQuery);
            $insertStmt->bindParam(':lote_id', $loteId, PDO::PARAM_INT);
            $insertStmt->bindParam(':quantidade', $quantidadeLote, PDO::PARAM_INT);
            $insertStmt->bindParam(':data', $data, PDO::PARAM_STR);

            // Execução da inserção
            $insertStmt->execute();
        }
    }




 public function pedido(array $dados, int $local): void {
    // Verifique se os dados foram fornecidos corretamente
     $ano = date("Y_m_d");
    $pedido = 'pedido' . uniqid(). $ano;

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto') === 0) {
            $index = str_replace('produto', '', $key);
            $produtoId = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;

            if ($quantidade > 0) {
                // Atualiza o banco de dados para o produto correspondente
                $query = "INSERT INTO pedidos (pedido, local, produto_id, qnt_solicitada) 
                          VALUES (:pedido, :local, :produto, :quantidade)";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':pedido',$pedido );
                    $stmt->bindParam(':local', $local);
                    $stmt->bindParam(':produto', $produtoId);
                    $stmt->bindParam(':quantidade', $quantidade);
                    
                    $stmt->execute();

                
            }
        }
    }
}
public function padrao(array $dados){
        $query = "INSERT INTO padrao_dispensa_hospital (produto_id, local, qnt) 
                          VALUES (:produto, :local, :qnt)";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':produto', $dados['produtopadrao']);
        $stmt->bindParam(':local', $dados['localpadrao']);
        $stmt->bindParam(':qnt', $dados['qntpadrao']);

        $stmt->execute();
}

public function saidaHospital(array $dados): void {
        // Verifique se os dados foram fornecidos corretamente
        $usuario = UsuarioControlador::usuario()->id;
        $data = date("Y-m-d");
        $saidaQuery = "INSERT INTO numero_saida_hospital () VALUES ()";
        $stmtSaida = Conexao::getInstancia()->prepare($saidaQuery);
        $stmtSaida->execute();
        $saida = Conexao::getInstancia()->lastInsertId();

    foreach ($dados as $key => $value) {
        if (strpos($key, 'produto_id') === 0) {
            $index = str_replace('produto_id', '', $key);
             $produto_id = intval($value);
            $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
            $registroId = $dados['registro' . $index];

            if ($quantidade > 0) {
                // Atualiza o banco de dados para o produto correspondente
                $query = "INSERT INTO saida_hospital (saida, local, produto_id, quantidade,data ,usuario) 
                          VALUES (:saida, :local, :produto, :quantidade,:data ,:user)";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':saida',$saida );
                    $stmt->bindParam(':local', $dados['local']);
                    $stmt->bindParam(':produto', $produto_id);
                    $stmt->bindParam(':quantidade', $quantidade);
                    $stmt->bindParam(':data', $data);
                    $stmt->bindParam(':user', $usuario);
                    $stmt->execute();
                    
                    $updateQuery = "UPDATE local_estoque SET estoque = estoque -  ? WHERE local_estoque.id = ?";

                    
                    $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                    $stmtUpdate->execute([$quantidade, $registroId]);

                    $query = "UPDATE local_estoque SET estoque = CASE WHEN estoque < 0 THEN 0 ELSE estoque END WHERE id = :id";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->bindParam(':id', $registroId, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    

                
            }
        }
    }
}

public function pesquisa(string $buscar) {
    $conexao = Conexao::getInstancia();
    $query = "
        SELECT pedidos.*, produtos.nome AS nome_produto
FROM pedidos
JOIN produtos ON pedidos.produto_id = produtos.id
WHERE (pedidos.pedido LIKE :buscar OR pedidos.data LIKE :buscar)
AND pedidos.exibir = 0
ORDER BY pedidos.data DESC
";
              
    $stmt = $conexao->prepare($query);
    $stmt->bindValue(':buscar', '%' . $buscar . '%', PDO::PARAM_STR);
    $stmt->execute();
    
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $resultado;
}
public function atendido(string $pedido) {
    $sql = "UPDATE pedidos SET exibir = ? WHERE pedido = ?";
    $stmtUpdate = Conexao::getInstancia()->prepare($sql);
    $stmtUpdate->execute([1, $pedido]);
}

    public function pesquisaPedido(string $pedido)
    {
        // Certifique-se de que a classe Conexao está corretamente importada ou carregada
        $conexao = Conexao::getInstancia();

        $query = "
        SELECT
            pedidos.id AS pedido_id,
            pedidos.produto_id,
            pedidos.local,
            produtos.nome,
            produtos.unidade_contagem,
            pedidos.qnt_solicitada
        FROM 
            pedidos
        JOIN 
            produtos ON pedidos.produto_id = produtos.id
        WHERE 
            pedidos.pedido = :pedido
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':pedido', $pedido, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function atualizarPedido(array $dados): void
    {
        $data = date("Y-m-d");

        foreach ($dados as $key => $value) {
            if (strpos($key, 'lote') === 0) {
                $index = str_replace('lote', '', $key);
                $loteId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                $pedido = $dados['pedido' . $index];

                $query = "UPDATE pedidos SET quantidade = quantidade - :qnt, lote_id = :lote, atendido = :dat WHERE id = :id";
                $stmt = Conexao::getInstancia()->prepare($query);
                $stmt->bindParam(':qnt', $quantidade, PDO::PARAM_INT);
                $stmt->bindParam(':lote', $loteId, PDO::PARAM_INT);
                $stmt->bindParam(':dat', $data, PDO::PARAM_STR); // corrigido para PDO::PARAM_STR
                $stmt->bindParam(':id', $pedido, PDO::PARAM_INT);
                $stmt->execute();
            }
        }
    }
    public function pesquisaHospital()
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        local_estoque.id AS local_estoque_id,
        local_estoque.estoque,
        local_estoque.produto_id,
        produtos.unidade_contagem,
        produtos.nome
    FROM 
        local_estoque
    JOIN 
        produtos ON local_estoque.produto_id = produtos.id
    WHERE 
        local_estoque.local_id = 3
    ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function pesquisaPadrao(string $local )
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        padrao_dispensa_hospital.produto_id,
        padrao_dispensa_hospital.local,
        padrao_dispensa_hospital.qnt,

        produtos.nome
    FROM 
        padrao_dispensa_hospital
    JOIN 
        produtos ON padrao_dispensa_hospital.produto_id = produtos.id
        WHERE (padrao_dispensa_hospital.local = :local)
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':local', $local, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function saidasHospital(?string $dePesquisa = '1970-01-01', ?string $atePesquisa = '2099-12-31', ?string $produto = '', ?string $local = '')
    {
        $conexao = Conexao::getInstancia();
/*SELECT 
    
        saida_hospital.local,
 
        produtos.nome,
      SUM(saida_hospital.quantidade) AS total_quantidade
      
    FROM 
        saida_hospital
   
    JOIN 
        produtos ON saida_hospital.produto_id = produtos.id
         WHERE 
        (saida_hospital.data >= :de AND saida_hospital.data <= :ate AND produtos.nome LIKE :produto AND saida_hospital.local LIKE :local )GROUP BY
    saida_hospital.local,
    produtos.nome
 */
        $query = "
    SELECT 
        saida_hospital.id AS saida_id,
        saida_hospital.saida,
        saida_hospital.local,
        saida_hospital.data,
        saida_hospital.quantidade,
        produtos.nome,
        produtos.unidade_contagem,
        usuario.nome AS username
    FROM 
        saida_hospital
    LEFT JOIN 
    usuario ON saida_hospital.usuario = usuario.id
    JOIN 
        produtos ON saida_hospital.produto_id = produtos.id
         WHERE 
        (saida_hospital.data >= :de AND saida_hospital.data <= :ate AND produtos.nome LIKE :produto AND saida_hospital.local LIKE :local )
        
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':produto', '%' . $produto . '%', PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function estoqueLocal(string $pesquisa)
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        local_estoque.id AS local_estoque_id,
        local_estoque.estoque,
        produtos.slug,
        local_estoque.produto_id,
        produtos.unidade_contagem,
        produtos.nome
    FROM 
        local_estoque
    JOIN 
        produtos ON local_estoque.produto_id = produtos.id
    WHERE 
        (local_estoque.local_id = :pesquisa)
        
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':pesquisa', $pesquisa, PDO::PARAM_INT);

        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function pesquisaPedir()
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        local_estoque.id AS local_estoque_id,
        produtos.nome,
        produtos.id
    FROM 
        local_estoque
    JOIN 
        lote ON local_estoque.lote_id = lote.id
    JOIN 
        produtos ON lote.produto_id = produtos.id
  
    ";

        $stmt = $conexao->prepare($query);


        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function recebidos()
    {
        $local = UsuarioControlador::usuario()->local;
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        registro_recebimento_local.id,
        registro_recebimento_local.data,
        registro_recebimento_local.nome_entrada
    FROM 
        registro_recebimento_local
    WHERE 
        (registro_recebimento_local.local = :local AND registro_recebimento_local.exibir = 0   )
         GROUP BY registro_recebimento_local.nome_entrada, registro_recebimento_local.data
    ";

        $stmt = $conexao->prepare($query);

        $stmt->bindValue(':local',$local, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function confirmacao( string $entrada)
    {
        $conexao = Conexao::getInstancia();

        $query = "
SELECT 
        registro_recebimento_local.id ,
        registro_recebimento_local.quantidade,
        registro_recebimento_local.nome_entrada,
        registro_recebimento_local.produto_id,
        produtos.nome
    FROM 
        registro_recebimento_local
    JOIN 
        produtos ON  registro_recebimento_local.produto_id = produtos.id
    WHERE 
        (  registro_recebimento_local.nome_entrada = :entrada)
    ";

        $stmt = $conexao->prepare($query);

      
        $stmt->bindValue(':entrada', $entrada, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function confirmado(array $dados): void
    {
        // Verifique se os dados foram fornecidos corretamente
        $local = UsuarioControlador::usuario()->local;
        $data = date('Y-m-d');
        foreach ($dados as $key => $value) {
            if (strpos($key, 'produto_id') === 0) {
                $index = str_replace('produto_id', '', $key);
                $produtoId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                abs($quantidade);
                $registro = isset($dados['registro' . $index]) ? intval($dados['registro' . $index]) : 0;

                if ($quantidade > 0) {
        
                (new Inserir())->inserir('entrada_ubs', 'produto_id, quantidade, data,local_id', [$produtoId, $quantidade, $data, $local]);
           
                $updateConQuery = "UPDATE registro_recebimento_local SET quantidade_confirmada = ?, comfirmado = ?, exibir = ? WHERE id = ?";
                $stmtUpdateCon = Conexao::getInstancia()->prepare($updateConQuery);
                $stmtUpdateCon->execute([$quantidade, 1,1, $registro]);
               
                $query = "SELECT estoque FROM local_estoque WHERE produto_id = ? AND local_id = ?";
                $stmt = Conexao::getInstancia()->prepare($query);
                $stmt->execute([$produtoId, $local]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($result) {
                    // Produto já existe no local, atualize o estoque
                    $estoqueAtual = intval($result['estoque']);
                    $novoEstoque = $estoqueAtual + $quantidade;
                    abs($novoEstoque);
                    $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE produto_id = ? AND local_id = ?";
                    $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                    $stmtUpdate->execute([$novoEstoque, $produtoId, $local]);
                } else {
                    // Produto não existe no local, insira um novo registro
                    $insertQuery = "INSERT INTO local_estoque (local_id, produto_id, estoque) VALUES (?, ?, ?)";
                    $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
                    $stmtInsert->execute([$local, $produtoId, $quantidade]);
                }
        
            
                       
        
                 
                   
                  
                   
                }
            }
        }
    
    }
    public function abastecer(array $dados): void
    {
        // Verifique se os dados foram fornecidos corretamente

        foreach ($dados as $key => $value) {
            if (strpos($key, 'produto_id') === 0) {
                $index = str_replace('produto_id', '', $key);
                $produto_id = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
             
                abs($quantidade);
                if ($quantidade > 0) {
                 
                    $localId = intval($dados['local']);
                    $query = "SELECT estoque FROM local_estoque WHERE produto_id = ? AND local_id = ?";
                    $stmt = Conexao::getInstancia()->prepare($query);
                    $stmt->execute([$produto_id, $localId]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        // Produto já existe no local, atualize o estoque
                        $estoqueAtual = intval($result['estoque']);
                        $novoEstoque = $estoqueAtual + $quantidade;
                        abs($novoEstoque);
                        $updateQuery = "UPDATE local_estoque SET estoque = ? WHERE produto_id = ? AND local_id = ?";
                        $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                        $stmtUpdate->execute([$novoEstoque, $produto_id, $localId]);
                    } else {
                        // Produto não existe no local, insira um novo registro
                        $insertQuery = "INSERT INTO local_estoque (local_id, produto_id, estoque) VALUES (?, ?, ?)";
                        $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
                        $stmtInsert->execute([$localId, $produto_id, $quantidade]);
                    }
                }
            }
        }
    }
    public function pesquisaAbastecer()
    {
        $conexao = Conexao::getInstancia();
        $query = "
        SELECT produtos.id, produtos.nome, produtos.unidade_contagem FROM produtos";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function adicionarPadrao(array $dados): void
    {
        $localId = intval($dados['local']);
        $query = "SELECT padrao FROM padrao_locais WHERE produto_id = ? AND local_id = ?";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute([$dados['produto'], $localId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Produto já existe no local, atualize o estoque
            echo 'Produto não adicionado, pois já existe nesse Local!!';
        } else {
            $insertQuery = "INSERT INTO padrao_locais (produto_id, padrao,local_id) VALUES (?, ?, ?)";
            $stmtInsert = Conexao::getInstancia()->prepare($insertQuery);
            $stmtInsert->execute([$dados['produto'], $dados['quantidade'], $dados['local']]);
            echo $dados['produto'] . ' adicionado padrão do Local';
        }

        
    }

    public function editarPadrao(array $dados)
    {

    $updateQuery = "UPDATE padrao_locais SET produto_id =?,padrao=?,local_id = ? WHERE id = ?";
    $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
    $stmtUpdate->execute([$dados['produto_edit'], $dados['quantidade_edit'], $dados['local_edit'],$dados['registro_id']]);
        echo 'Registro '.$dados['registro_id'] . ' editado com Sucesso';
    }


    public function pesquisaPadraoLocal(int $local)
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
    padrao_locais.id,
        padrao_locais.produto_id,
        padrao_locais.padrao,
        produtos.nome
    FROM 
        padrao_locais
    JOIN 
        produtos ON padrao_locais.produto_id = produtos.id
        WHERE (padrao_locais.local_id = :local)
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':local', $local, PDO::PARAM_STR);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function fazerMapa(int $local)
    {
        $data = date('Y-m-d'); // Melhor formato de data para o banco de dados
        $localId = $local;

        // Buscando os padrões para o local
        $query = "SELECT produto_id, padrao FROM padrao_locais WHERE local_id = ?";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->execute([$localId]);
        $resultadoPadrao = $stmt->fetchAll(PDO::FETCH_ASSOC); // Usar fetchAll para vários resultados

        // Inserindo o número do mapa
        $mapaQuery = "INSERT INTO numero_mapa (data, local_id) VALUES (?, ?)";
        $stmtMapa = Conexao::getInstancia()->prepare($mapaQuery);
        $stmtMapa->execute([$data, $localId]);
        $mapa = Conexao::getInstancia()->lastInsertId();

        // Iterando sobre os padrões encontrados
        foreach ($resultadoPadrao as $padrao) {
            // Aqui você pode acessar o produto_id e padrao
            $produtoId = $padrao['produto_id'];
            $quantidadePadrao = $padrao['padrao'];

            // Buscando o estoque atual
            $query = "SELECT estoque FROM local_estoque WHERE local_id = ? AND produto_id = ?";
            $stmt = Conexao::getInstancia()->prepare($query);
            $stmt->execute([$localId, $produtoId]);
            $resultadoEstoque = $stmt->fetch(PDO::FETCH_ASSOC);

            // Acessar o estoque diretamente
            $estoqueAtual = $resultadoEstoque['estoque'] ?? 0; // Usar valor padrão 0 se não houver estoque

            // Inserindo os dados no mapa
            $mapaQuery = "INSERT INTO mapas (mapa, produto_id, local_id, estoque_atual, padrao, data) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmtMapa = Conexao::getInstancia()->prepare($mapaQuery);
            $stmtMapa->execute([$mapa, $produtoId, $localId, $estoqueAtual, $quantidadePadrao, $data]);
        }
    }
    public function mapasPorData($busca)
    {
        // Consulta para obter os dados agrupados por data
        $query = "
      SELECT numero_mapa.data AS dataPedido, numero_mapa.local_id,numero_mapa.atendido, locais.nome, numero_mapa.id FROM numero_mapa JOIN locais ON numero_mapa.local_id = locais.id WHERE (locais.nome LIKE :busca OR numero_mapa.id LIKE :busca OR numero_mapa.data LIKE :busca)  GROUP BY data, local_id, locais.nome ORDER BY data DESC;
";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindValue(':busca', '%' . $busca . '%', PDO::PARAM_STR);
        $stmt->execute();

        // Retorna os resultados como array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function pesquisaMapa($id)
    {
        // Consulta para obter os dados agrupados por data
        $query = "
      SELECT 
    mapas.id,
        mapas.mapa,
        mapas.produto_id,
        mapas.lote_id,
        mapas.estoque_atual,
        mapas.padrao,
        mapas.quantidade,
        mapas.data,
        mapas.data_atendido,
        produtos.nome,
        mapas.local_id,
        locais.nome AS localNome

    FROM 
        mapas
        JOIN 
        produtos ON mapas.produto_id = produtos.id
        JOIN locais ON mapas.local_id = locais.id
        WHERE (mapas.mapa = :mapa)
";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindValue(':mapa', $id, PDO::PARAM_STR);
        $stmt->execute();

        // Retorna os resultados como array associativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lotesPesquisa($id)
    {

        $conexao = Conexao::getInstancia();
       
        // Construção da query com cláusula WHERE dinâmica
        $query = "
    SELECT 
        lote.id,
        lote.quantidade,
        lote.vencimento,
        lote.fornecedor,
        produtos.nome
        
    FROM 
        lote
    JOIN 
        produtos ON lote.produto_id = produtos.id
        
    WHERE 
        lote.produto_id = $id
";

        // Preparação da consulta
        $stmt = $conexao->prepare($query);

        // Execução da consulta
        $stmt->execute();

        // Obtenção dos resultados
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function mapaSaidaRegistro(array $dados): void
    {


        // Verifica se há dados enviados
        $data = date("Y-m-d");

            $updateQuery = "UPDATE numero_mapa SET atendido = ? WHERE id = ?";
            $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
            $stmtUpdate->execute([1, $dados['mapaNum']]);
        
       
        $nomeEntrada = 'e' . uniqid();
        $usuario =  UsuarioControlador::usuario()->id;

        foreach ($dados as $key => $value) {
            if (strpos($key, 'lote') === 0) {
                $index = str_replace('lote', '', $key);
                $loteId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
                $registro = $dados['registro' . $index];
                abs($quantidade);

                $updateQuery = "UPDATE mapas SET lote_id =?,quantidade=?,data_atendido = ? WHERE id = ?";
                $stmtUpdate = Conexao::getInstancia()->prepare($updateQuery);
                $stmtUpdate->execute([$loteId, $quantidade, $data, $registro]);
               

                if ($quantidade > 0) {
                    // Insere os dados no banco de dados para este produto
                  

                    $insertLocal = "INSERT INTO registro_recebimento_local (lote_id, nome_entrada, quantidade,local) VALUES (?, ?, ?,?)";
                    $stmtLocal = Conexao::getInstancia()->prepare($insertLocal);
                    $stmtLocal->execute([$loteId, $nomeEntrada, $quantidade, $dados['localMapa']]);
                }
            }
        }
    }
    public function totalEntradas()
    {
        $conexao = Conexao::getInstancia();
        $query = "
    SELECT 
    COALESCE(SUM(registro_entrada.quantidade)) AS total_entrada
FROM 
    registro_entrada    
    ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function totalSaidas()
    {
        $conexao = Conexao::getInstancia();
        $query = "
 SELECT 
   
    COALESCE(
        (SELECT SUM(registro_saida_sem_local.quantidade) FROM registro_saida_sem_local), 
        0
    ) 
    +
    COALESCE(
        (SELECT SUM(registro_vendas.quantidade) FROM registro_vendas), 
        0
    ) AS total_saidas
   
    ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function totalRegistroSaidasEntrada()
    {
        $conexao = Conexao::getInstancia();
        $query = "
 SELECT 
   
      (SELECT COUNT(*) FROM num_dispensa)
    AS total_saidas,
     (SELECT COUNT(*) FROM registro_entrada)
   AS total_entradas
    ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function maisSaidos()
    {
        $conexao = Conexao::getInstancia();
        $query = "
SELECT 
registro_vendas.produto_id,
produtos.nome,
COUNT(*) AS total_registros FROM registro_vendas 
JOIN produtos ON registro_vendas.produto_id = produtos.id
GROUP BY produto_id ORDER BY total_registros DESC LIMIT 10;
    ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function pedidosPorLocal()
    {
        $conexao = Conexao::getInstancia();
        $query = "
SELECT 
    locais.nome AS local, 
    COUNT(num_dispensa.id) AS total
FROM 
    num_dispensa
JOIN 
    locais ON num_dispensa.local_id = locais.id
GROUP BY 
    locais.nome;
    ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function pedidosPorLocalMeses($buscar)
    {
        $conexao = Conexao::getInstancia();
        $query = "
        SELECT 
            DATE_FORMAT(num_dispensa.data, '%Y-%m') AS mes,
            CASE MONTH(num_dispensa.data)
                WHEN 1 THEN 'Jan'
                WHEN 2 THEN 'Fev'
                WHEN 3 THEN 'Mar'
                WHEN 4 THEN 'Abr'
                WHEN 5 THEN 'Mai'
                WHEN 6 THEN 'Jun'
                WHEN 7 THEN 'Jul'
                WHEN 8 THEN 'Ago'
                WHEN 9 THEN 'Set'
                WHEN 10 THEN 'Out'
                WHEN 11 THEN 'Nov'
                WHEN 12 THEN 'Dez'
            END AS nome_mes,
            COUNT( num_dispensa.id) AS total
        FROM 
            num_dispensa
        JOIN 
            locais ON num_dispensa.local_id = locais.id
        WHERE
            locais.nome LIKE CONCAT('%', :local, '%') 
        GROUP BY 
            mes, nome_mes
        ORDER BY 
            mes;
        ";
    
        // Prepare and execute the query
        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':local', $buscar);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function EntradaMeses()
    {
        $conexao = Conexao::getInstancia();
        $query = "
       SELECT DATE_FORMAT(registro_entrada.data, '%Y-%m') AS mes, CASE MONTH(registro_entrada.data) WHEN 1 THEN 'Jan' WHEN 2 THEN 'Fev' WHEN 3 THEN 'Mar' WHEN 4 THEN 'Abr' WHEN 5 THEN 'Mai' WHEN 6 THEN 'Jun' WHEN 7 THEN 'Jul' WHEN 8 THEN 'Ago' WHEN 9 THEN 'Set' WHEN 10 THEN 'Out' WHEN 11 THEN 'Nov' WHEN 12 THEN 'Dez' END AS nome_mes, COUNT(*) AS total FROM registro_entrada GROUP BY mes, nome_mes ORDER BY mes;";
    
        // Prepare and execute the query
        $stmt = $conexao->prepare($query);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function relacaoTipos()
    {
        $conexao = Conexao::getInstancia();
        $query = "SELECT COUNT(tipo) AS total, tipo FROM produtos GROUP BY tipo;";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function totalProdutos()
    {
        $conexao = Conexao::getInstancia();
        $query = "SELECT COUNT(*) AS produto FROM produtos ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    public function totProCrit()
    {
        $conexao = Conexao::getInstancia();
        $query = "SELECT produtos.nome FROM produtos WHERE qnt1 = 0 ORDER BY nome ASC; ";

        $stmt = $conexao->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
    
    public function totalEstoque()
    {
        // Conexão com o banco de dados
        $conexao = Conexao::getInstancia();

        // Consulta SQL para somar as quantidades de lotes que vencem no mês atual
        $query = "
        SELECT 
            COALESCE(SUM(lote.quantidade), 0) AS total_estoque
        FROM 
            lote   
    ";

        // Preparando e executando a consulta
        $stmt = $conexao->prepare($query);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retornando o total de lotes vencidos no mês
        return $resultado['total_estoque'];
    }

    public function totalVencidosMes()
    {
        // Obtendo o primeiro e último dia do mês atual
        $dataInicioMes = date('Y-m-01'); // Primeiro dia do mês
        $dataFimMes = date('Y-m-t'); // Último dia do mês

        // Conexão com o banco de dados
        $conexao = Conexao::getInstancia();

        // Consulta SQL para somar as quantidades de lotes que vencem no mês atual
        $query = "
        SELECT   COUNT(DISTINCT produtos.id) AS total_produtos, 
    COALESCE(SUM(lote.quantidade), 0) AS total_vencidos    
FROM 
    produtos
LEFT JOIN 
    lote ON produtos.id = lote.produto_id
WHERE  
       lote.vencimento BETWEEN :dataInicioMes AND :dataFimMes
    ";

        // Preparando e executando a consulta
        $stmt = $conexao->prepare($query);
        $stmt->bindParam(':dataInicioMes', $dataInicioMes);
        $stmt->bindParam(':dataFimMes', $dataFimMes);
        $stmt->execute();

        // Obtendo o resultado
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retornando o total de lotes vencidos no mês
        return $resultado;
    }

    public function totalZerados()
    {
        $conexao = Conexao::getInstancia();

        // Consulta SQL para somar as quantidades de lotes que vencem no mês atual
        $query = "
        SELECT COUNT(DISTINCT produtos.id) AS total_produtos
FROM 
    produtos
LEFT JOIN 
    lote ON produtos.id = lote.produto_id
WHERE  
       lote.quantidade = 0 
    ";

        // Preparando e executando a consulta
        $stmt = $conexao->prepare($query);
        $stmt->execute();

        // Obtendo o resultado
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retornando o total de lotes vencidos no mês
        return $resultado['total_produtos'];
    }


    public function totalCrit()
    {
        $conexao = Conexao::getInstancia();

        $query = "
    SELECT 
        p.qnt_crit,
        COALESCE(SUM(l.quantidade), 0) AS total_quantidade
    FROM 
        produtos p
    LEFT JOIN 
        lote l ON p.id = l.produto_id 
    WHERE 
        1 = 1 GROUP BY p.id
        HAVING total_quantidade <= p.qnt_crit AND p.qnt_crit > 0
    "; 
       

        $stmt = $conexao->prepare($query);


        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $resultado;
    }
}
