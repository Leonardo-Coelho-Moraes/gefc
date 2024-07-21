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
SimpleRouter::match(['get','post'],URL_SITE.'entrada/adicionar','SiteControlador@entrada_adicionar');
SimpleRouter::match(['get','post'],URL_SITE.'entrada/editar/{id}','SiteControlador@editar_entrada');

SimpleRouter::match(['get','post'],URL_SITE.'vendas/','SiteControlador@vendas');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/{nome}/','SiteControlador@venda');
SimpleRouter::match(['get','post'],URL_SITE.'registro/vendas','SiteControlador@registroVendas');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/editar/{venda}/{id}','SiteControlador@editar_venda');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/{venda}/local/','SiteControlador@editarLocal');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/adicionar/{venda}/{local}','SiteControlador@adicionarAVenda');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/deletar/{venda}/{id}','SiteControlador@deletar_venda');
SimpleRouter::match(['get','post'],URL_SITE.'vendas/deletar/{nomeVenda}','SiteControlador@deletarVendaInteira');
SimpleRouter::match(['get','post'],URL_SITE.'venda/adicionar','SiteControlador@venda_adicionar');
SimpleRouter::match(['get','post'],URL_SITE.'venda/editar/{id}','SiteControlador@venda_editar');
SimpleRouter::match(['get','post'],URL_SITE.'estoque/locais/','SiteControlador@estoqueLocais');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/','SiteControlador@saidasFora');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/{nome}/','SiteControlador@saidaFora');
SimpleRouter::match(['get','post'],URL_SITE.'registro/saida/fora','SiteControlador@registroSaidaFora');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/editar/{saida}/{id}','SiteControlador@editarSaidaFora');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/{saida}/local/','SiteControlador@editarLocalFora');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/adicionar/{saida}/{local}','SiteControlador@adicionarASaida');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/deletar/{saida}/{id}','SiteControlador@deletarSaida');
SimpleRouter::match(['get','post'],URL_SITE.'saida/fora/adicionar','SiteControlador@saidaForaAdicionar');
SimpleRouter::match(['get','post'],URL_SITE.'saidas/fora/deletar/{nomeVenda}','SiteControlador@deletarSaidaForaInteira');

SimpleRouter::match(['get','post'],URL_SITE.'pedido/fazer','SiteControlador@pedidoFazer');
SimpleRouter::match(['get','post'],URL_SITE.'pedidos/','SiteControlador@pedidos');
SimpleRouter::match(['get','post'],URL_SITE.'pedidos/{pedido}','SiteControlador@pedidoAtender');
SimpleRouter::match(['get','post'],URL_SITE.'pedidos/atender/{pedido}','SiteControlador@pedidoAtendido');


SimpleRouter::match(['get','post'],URL_SITE.'produtos','SiteControlador@produtos');
SimpleRouter::match(['get','post'],URL_SITE.'produtos/produto_cadastrar','SiteControlador@produto_cadastrar');
SimpleRouter::match(['get','post'],URL_SITE.'produtos/editar/{slug}/{id}','SiteControlador@editar_produto');
SimpleRouter::match(['get','post'],URL_SITE.'produtos/deletar/{slug}/{id}','SiteControlador@deletar_produto');
SimpleRouter::match(['get', 'post'], URL_SITE . 'produtos/{slug}/{id}', 'SiteControlador@produto');
SimpleRouter::match(['get','post'],URL_SITE.'local/','SiteControlador@local');
SimpleRouter::match(['get','post'],URL_SITE.'local/editar/estoque/{produto}/','SiteControlador@editarEstoqueLocal');



SimpleRouter::match(['get','post'],URL_SITE.'usuarios','SiteControlador@usuarios');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/editar/{id}','UsuarioControlador@editar_usuario');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/deletar/{id}','UsuarioControlador@deletar_usuario');

SimpleRouter::match(['get','post'],URL_SITE.'relatorio','RelatorioControlador@relatorio');
SimpleRouter::match(['get','post'],URL_SITE.'media','RelatorioControlador@media');
SimpleRouter::get(URL_SITE.'relatorio/download','RelatorioControlador@download');
SimpleRouter::get(URL_SITE.'relatorio/imprimir','RelatorioControlador@imprimir');
SimpleRouter::match(['get','post'],URL_SITE.'relatorio/imprimir/{nome}/','RelatorioControlador@gerarVenda');
SimpleRouter::match(['get','post'],URL_SITE.'saida/relatorio/imprimir/{saida}/','RelatorioControlador@gerarSaida');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios','SiteControlador@usuarios');
SimpleRouter::match(['get','post'],URL_SITE.'usuarios/editar/{id}','UsuarioControlador@editar_usuario');
SimpleRouter::get(URL_SITE.'proibido','SiteControlador@proibido');
SimpleRouter::get(URL_SITE.'erro404','SiteControlador@erro404');
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
