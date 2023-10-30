<?php
 use Pecee\SimpleRouter\SimpleRouter;
use gefc\Nucleo\Helpers;
try {
    SimpleRouter::setDefaultNamespace('gefc\Controlador');
SimpleRouter::get(URL_SITE,'SiteControlador@index');
SimpleRouter::get(URL_SITE . 'index.php', function() {
        Helpers::redirecionar();
    });

SimpleRouter::match(['get','post'],URL_SITE.'entrada/','SiteControlador@entrada');
SimpleRouter::match(['get','post'],URL_SITE.'entrada/adicionar','SiteControlador@entrada_adicionar');
SimpleRouter::match(['get','post'],URL_SITE.'entrada/editar/{id}','SiteControlador@editar_entrada');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/','SiteControlador@vendas');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/{nome}/','SiteControlador@venda');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/editar/{venda}/{id}','SiteControlador@editar_venda');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/deletar/{venda}/{id}','SiteControlador@deletar_venda');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/deletar/{nomeVenda}','SiteControlador@deletarVendaInteira');
SimpleRouter::match(['get','post'],URL_SITE.'venda/adicionar','SiteControlador@venda_adicionar');
SimpleRouter::match(['get','post'],URL_SITE.'venda/editar/{id}','SiteControlador@venda_editar');
SimpleRouter::match(['get','post'],URL_SITE.'produtos','SiteControlador@produtos');

SimpleRouter::match(['get','post'],URL_SITE.'produtos/produto_cadastrar','SiteControlador@produto_cadastrar');
SimpleRouter::match(['get','post'],URL_SITE.'produtos/editar/{slug}/{id}','SiteControlador@editar_produto');
SimpleRouter::match(['get','post'],URL_SITE.'produtos/deletar/{slug}/{id}','SiteControlador@deletar_produto');
SimpleRouter::match(['get', 'post'], URL_SITE . 'produtos/{slug}/{id}', 'SiteControlador@produto');

SimpleRouter::match(['get','post'],URL_SITE.'usuarios','SiteControlador@usuarios');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/editar/{id}','UsuarioControlador@editar_usuario');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/deletar/{id}','UsuarioControlador@deletar_usuario');
SimpleRouter::match(['get','post'],URL_SITE.'relatorio','RelatorioControlador@relatorio');
SimpleRouter::get(URL_SITE.'relatorio/download','RelatorioControlador@download');
SimpleRouter::get(URL_SITE.'erro404','SiteControlador@erro404');
SimpleRouter::post(URL_SITE.'buscar','SiteControlador@buscarRegistros');
SimpleRouter::post(URL_SITE.'buscarProdutos','SiteControlador@buscarProdutos');

SimpleRouter::post(URL_SITE.'buscarCod','SiteControlador@buscarCod');
SimpleRouter::post(URL_SITE.'buscarId','SiteControlador@buscarId');
SimpleRouter::match(['get','post'],URL_SITE.'login','AdminLogin@login');
SimpleRouter::get(URL_SITE.'sair','SiteControlador@sair');

SimpleRouter::start();
} catch (Pecee\SimpleRouter\Exceptions\NotFoundHttpException $ex) {
    if(Helpers::localhost()){
        echo $ex;
    }
  
    else{
     Helpers::redirecionar('erro404');}
     
     
}
