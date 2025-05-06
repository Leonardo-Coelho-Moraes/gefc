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
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Conexao;
use gefc\Modelo\Atualizar;
use gefc\Nucleo\Helpers;
use gefc\Modelo\Inserir;
class ReceitaModelo {
  
    public function pesquisaPaciente(?string $pesquisa= '')
    {


        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        pacientes.id,
        pacientes.nome,
        pacientes.sus
    FROM   
        pacientes  
   
    WHERE  
        (pacientes.nome LIKE :buscar OR pacientes.sus LIKE :buscar )  
    ORDER BY   
        pacientes.id DESC   
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':buscar', '%' . $pesquisa . '%', PDO::PARAM_STR);

        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
    public function pesquisaPrescritor(?string $pesquisa= '')
    {


        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        prescritores.id,
        prescritores.nome,
        prescritores.crm
    FROM   
        prescritores  
   
    WHERE  
        (prescritores.nome LIKE :buscar OR prescritores.crm LIKE :buscar )  
    ORDER BY   
        prescritores.id DESC   
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':buscar', '%' . $pesquisa . '%', PDO::PARAM_STR);

        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }


    public function cadReceita(array $dados)
    {
        $usuario = UsuarioControlador::usuario()->id;
        (new Inserir())->inserir(
            'numero_receita',
            'paciente_id	, local_id, prescritor_id, obs,data, usuario_id',
            [$dados['pacientePesquisa'], $dados['localPesquisa'], $dados['prescritorPesquisa'], $dados['obs'], $dados['dataReceita'], $usuario]
        );
        $receita = Conexao::getInstancia()->lastInsertId();
       Helpers::redirecionar('receita/'.$receita);

    }
    public function cadPaciente(array $dados)
    {
        $usuario = UsuarioControlador::usuario()->id;
        (new Inserir())->inserir(
            'pacientes',
            'nome, sus, endereco, numero,bairro, telefone,obs',
            [$dados['nome'], $dados['sus'], $dados['endereco'], $dados['numero'], $dados['bairro'], $dados['telefone'],$dados['observacao']]
        );
       
       Helpers::redirecionar('receitas/');

    }
    public function cadPrescritor(array $dados)
    {
        $usuario = UsuarioControlador::usuario()->id;
        (new Inserir())->inserir(
            'prescritores',
            'nome, crm',
            [$dados['nome'], $dados['crm']]
        );
       
       Helpers::redirecionar('receitas/');

    }
    public function editReceita(array $dados): void
    { 
       
        $dadosArray = [
            $dados['pacienteEdit'],
            $dados['localEdit'],
            $dados['prescritorEdit'],
            $dados['obsEdit'],
             $dados['data_receitaEdit']

        ];
        // Chamada à função atualizar com uma consulta preparada
        (new Atualizar())->atualizar(
            'numero_receita',
            "paciente_id = ?, local_id = ?, prescritor_id = ?, obs = ?, data=?",
            $dadosArray,
            $dados['registro_id']
        );
    }
    public function registarProdutoReceita(array $dados, int $id): void
    {
        foreach ($dados as $key => $value) {
            if (strpos($key, 'produto_id') === 0) {
                $index = str_replace('produto_id', '', $key);
                $produtoId = intval($value);
                $quantidade = isset($dados['quantidade' . $index]) ? intval($dados['quantidade' . $index]) : 0;
             
                    abs($quantidade);
                 
                if ($quantidade > 0) {
                    // Insere os dados no banco de dados para este produto
                    $array = array(
                        'produto' => $produtoId,
                        'quantidade' => $quantidade
                    );

                    $query = "INSERT INTO dispensa_receita (receita_id, medicacao_id, quantidade) 
                          VALUES (:receita, :produto, :quantidade)";

                   
                        $stmt = Conexao::getInstancia()->prepare($query);
                        $stmt->bindParam(':receita', $id);
                        $stmt->bindParam(':produto', $array['produto']);
                        $stmt->bindParam(':quantidade', $array['quantidade']);
                        $stmt->execute();

                    
                 
                  
                }
            }
        }
    }


    public function pesquisaReceita($id)
    {
       
       
        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        dispensa_receita.id,  
        dispensa_receita.medicacao_id,    
        dispensa_receita.quantidade,
        produtos.nome
    FROM   
        dispensa_receita  
    JOIN   
        produtos ON dispensa_receita.medicacao_id = produtos.id  

        WHERE dispensa_receita.receita_id = :id;

 
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function dadosReceita(int $id)
    {
       
       
        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        pacientes.nome AS paciente,  
        locais.nome AS local,  
        prescritores.nome AS prescritor,  
        numero_receita.data,
        prescritores.crm,
        numero_receita.obs,
        numero_receita.data_registro
    FROM   
        numero_receita  
    JOIN   
        pacientes ON numero_receita.paciente_id = pacientes.id 
        JOIN   
        locais ON numero_receita.local_id = locais.id 
        JOIN   
        prescritores ON numero_receita.prescritor_id = prescritores.id  
    
     WHERE numero_receita.id = :id;

    ";

        $stmt = $conexao->prepare($query);
       $stmt->bindValue(':id', $id, PDO::PARAM_INT); // Binding do limite como inteiro  
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }

    public function pesquisaReceitas(?string $dePesquisa = '2023-01-30', ?string $atePesquisa = '2099-12-31',?string $local = '', int $limite)
    {
       
       
        $conexao = Conexao::getInstancia();

        $query = "  
    SELECT  
        numero_receita.id,  
        pacientes.nome AS paciente,  
        locais.nome AS local,  
        prescritores.nome AS prescritor,  
        numero_receita.data,
        locais.id AS localId,
        prescritores.crm,
        numero_receita.obs
    FROM   
        numero_receita  
    JOIN   
        pacientes ON numero_receita.paciente_id = pacientes.id 
        JOIN   
        locais ON numero_receita.local_id = locais.id 
        JOIN   
        prescritores ON numero_receita.prescritor_id = prescritores.id  
    WHERE  
        ( numero_receita.data >= :de AND numero_receita.data <= :ate  AND   locais.nome LIKE :local)  
    ORDER BY numero_receita.id DESC
    LIMIT :limite  
    ";

        $stmt = $conexao->prepare($query);
        $stmt->bindValue(':de', $dePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':ate', $atePesquisa, PDO::PARAM_STR);
        $stmt->bindValue(':local', '%' . $local . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT); // Binding do limite como inteiro  
        $stmt->execute();

        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $resultado;
    }
   









   }

