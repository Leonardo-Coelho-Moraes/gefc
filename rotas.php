<?php
 use Pecee\SimpleRouter\SimpleRouter;
use gefc\Controlador\UsuarioControlador;
use gefc\Nucleo\Helpers;
try {
    SimpleRouter::setDefaultNamespace('gefc\Controlador');
SimpleRouter::get(URL_SITE,'SiteControlador@index');
SimpleRouter::get(URL_SITE . 'index.php', function() {
        Helpers::redirecionar();
    });

SimpleRouter::match(['get','post'],URL_SITE.'entrada/','SiteControlador@entrada');

SimpleRouter::match(['get','post'],URL_SITE.'vendas/','SiteControlador@vendas');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/{nome}/','SiteControlador@venda');
SimpleRouter::match(['get','post'],URL_SITE.'registro/vendas','SiteControlador@registroVendas');

SimpleRouter::match(['get','post'],URL_SITE.'venda/adicionar','SiteControlador@venda_adicionar');
SimpleRouter::match(['get','post'],URL_SITE.'venda/editar/{id}','SiteControlador@venda_editar');
SimpleRouter::match(['get','post'],URL_SITE.'estoque/locais/','SiteControlador@estoqueLocais');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/','SiteControlador@saidasFora');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/{nome}/','SiteControlador@saidaFora');
SimpleRouter::match(['get','post'],URL_SITE.'registro/saida/fora','SiteControlador@registroSaidaFora');

SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/adicionar/{saida}/{local}','SiteControlador@adicionarASaida');
SimpleRouter::match(['get','post'],URL_SITE.'saida/fora/adicionar','SiteControlador@saidaForaAdicionar');

SimpleRouter::match(['get','post'],URL_SITE.'pedido/fazer','SiteControlador@pedidoFazer');
SimpleRouter::match(['get','post'],URL_SITE.'pedidos/','SiteControlador@pedidos');
SimpleRouter::match(['get','post'],URL_SITE.'pedidos/{pedido}','SiteControlador@pedidoAtender');
SimpleRouter::match(['get','post'],URL_SITE.'pedidos/atender/{pedido}','SiteControlador@pedidoAtendido');

SimpleRouter::match(['get','post'],URL_SITE.'lotes','SiteControlador@lotes');

SimpleRouter::match(['get','post'],URL_SITE.'produtos','SiteControlador@produtos');
SimpleRouter::match(['get','post'],URL_SITE.'produtos/produto_cadastrar','SiteControlador@produto_cadastrar');
SimpleRouter::match(['get', 'post'], URL_SITE . 'produtos/{slug}/{id}', 'SiteControlador@produto');
SimpleRouter::match(['get','post'],URL_SITE.'local/','SiteControlador@local');
SimpleRouter::match(['get','post'],URL_SITE.'saida/hospital','SiteControlador@saidaHospital');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/hospital','SiteControlador@registroSaidasHospital');



SimpleRouter::match(['get','post'],URL_SITE.'usuarios','SiteControlador@usuarios');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/editar/{id}','UsuarioControlador@editar_usuario');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/deletar/{id}','UsuarioControlador@deletar_usuario');



SimpleRouter::match(['get','post'],URL_SITE.'usuarios','SiteControlador@usuarios');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/editar/{id}','UsuarioControlador@editar_usuario');
SimpleRouter::get(URL_SITE.'erro404','SiteControlador@erro404');
SimpleRouter::match(['get','post'],URL_SITE.'login','AdminLogin@login');
SimpleRouter::get(URL_SITE.'sair','SiteControlador@sair');

SimpleRouter::start();
} catch (Pecee\SimpleRouter\Exceptions\NotFoundHttpException $ex) {
    if(Helpers::localhost()){
        echo $ex;
        //  Helpers::redirecionar('erro404'); e exclua o else para redirecionar e não exibir erros!
    }
  
    else{
     Helpers::redirecionar('erro404');}
     
     
}
