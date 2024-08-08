<?php

/*
 *padrão singleton
 */

namespace gefc\Nucleo;

/**
 * Description of conecxao
 * classe totalmente protegida
 * @author Leonardo
 */
use PDO;
use PDOException;

class Conexao {

    private static $instancia;

    public static function getInstancia(): PDO {
        if (empty(self::$instancia)) {
            try {
                self::$instancia = new PDO('mysql:host='.DB_HOST.';port=3306;dbname='.DB_NOME.'',DB_USUARIO,DB_SENHA,[
                
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    //converte em objeto ai voce pode acessar assim '->pessoas' ao inves de ['pessoas']
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                    //faz o texto vir igual ao banco de dados
                    PDO::ATTR_CASE => PDO::CASE_NATURAL, 
                    //faz o charset ser igual ao do banco de dados
                    PDO::MYSQL_ATTR_INIT_COMMAND =>"SET NAMES utf8"
                ]);
                
            } catch (PDOException $ex) {
                die("Erro de Conexão".$ex->getMessage());
            }
           
        }
         return self::$instancia;
    }
    protected function __construct() {
        
    }
    private function __clone():void {
        
    }
}
