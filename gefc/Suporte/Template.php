<?php

namespace gefc\Suporte;
use gefc\Nucleo\Helpers;
use gefc\Controlador\UsuarioControlador;
use Twig\Lexer;
class Template
{
    private \Twig\Environment $twig;
    public function __construct(string $diretorio)
    {
        $this->twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader($diretorio));
        $this->twig->setLexer(new Lexer($this->twig, array($this->helpers())));

    }
    public function renderizar(string $view, array $dados){
        return $this->twig->render($view, $dados);
    }
    private function helpers():void{
        array(
            $this->twig->addFunction(new \Twig\TwigFunction('saudacao', function(){
                return Helpers::saudacao();
            })), $this->twig->addFunction(new \Twig\TwigFunction('url', function (string $url = null) {
                return Helpers::url($url);
            })),
                $this->twig->addFunction(new \Twig\TwigFunction('juntarLink', function (string $string = null) {
                return Helpers::juntarlink($string);
            })),
                    $this->twig->addFunction(new \Twig\TwigFunction('slug', function (string $string = null) {
                return Helpers::slug($string);
            })),
                     $this->twig->addFunction(new \Twig\TwigFunction('maiuscula', function (string $string = null) {
                return ucwords($string);
            })),
                         $this->twig->addFunction(new \Twig\TwigFunction('flash', function () {
                return Helpers::flash();
            })),
                      $this->twig->addFunction(new \Twig\TwigFunction('usuario', function () {
                return UsuarioControlador::usuario();
            })),
            $this->twig->addFunction(new \Twig\TwigFunction('nomeLocal', function () {
                return UsuarioControlador::NomeLocal();
            })),
            $this->twig->addFunction(new \Twig\TwigFunction('validade', function (string $data = null) {
                return Helpers::validade($data);
            })),
                              $this->twig->addFunction(new \Twig\TwigFunction('dataNumero', function (string $data = null) {
                return Helpers::converterDataNumero($data);
            })),  $this->twig->addFunction(new \Twig\TwigFunction('validadeProxima', function (string $data = null) {
                return Helpers::validadeProxima($data);
            })),
                  
        );
    }
}
