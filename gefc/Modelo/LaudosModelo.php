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
class LaudosModelo {
    
    public function cadastrarPaciente(array $dados): void {
        (new Inserir())->inserir(
            'paciente_laudo',
            'nome, local, data_nas, sus,endereco,contato',
            [$dados['nome'],$dados['local'],$dados['data_nas'],$dados['sus'], $dados['endereco'], $dados['contato']]);
    
    }
    public function editarPaciente(array $dados): void {       
        $query = "UPDATE paciente_laudo SET nome = :nome, local = :local, data_nas = :data_nas, sus = :sus, endereco = :endereco, contato =:contato WHERE id = :id";
        $stmt = Conexao::getInstancia()->prepare($query);
        $stmt->bindParam(':nome', $dados['nome_edit']);
        $stmt->bindParam(':local', $dados['local_edit']);
        $stmt->bindParam(':data_nas', $dados['data_nas_edit']);
        $stmt->bindParam(':sus', $dados['sus_edit']);
        $stmt->bindParam(':endereco', $dados['endereco_edit']);
        $stmt->bindParam(':contato', $dados['contato_edit']);
        $stmt->bindParam(':id', $dados['paciente_id']);

        $stmt->execute();
    
    }
    



   }

