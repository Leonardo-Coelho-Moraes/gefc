
{%extends 'base.html'%}
{% block conteudo %}


<div class=" w-full  p-5  justify-start ">
    <a href="#" class="mb-3 font-medium text-gray-900">Média</a>
    <div class=" w-full   justify-center items-start gap-8 overflow-y-scroll   " style="max-height: 90vh;">
        <div class=" w-full flex flex-col juntify-center gap-3">
            
       
                <form class="flex flex-col w-full gap-1 items-start justify-start" action="{{url('media')}}" method="post">
                    <!-- media: qnt solicitada do produto para de local no -->
                    <p>Média Saídas:</p> Da(o)  <select name="produto" id="produto"  class="block w-full p-2  border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-blue-300 focus:ring-blue-500 focus:border-blue-500" >
                 <option value=""></option>
                  {%for produto in produtos%}
                  <option value="{{produto.id}}">{{produto.nome}} - {{produto.fornecedor}}</option>
                   {%endfor%}
 
</select> Da(o)<select id="local" class=' block p-2 pl-3 border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-blue-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700' name="local">
             <option value="Laudo">Laudo</option>
             <option value="Receita">Receita</option>
             <option value="Hospital">Hospital</option>
             <option value="Farma">Farma</option>
             <option value="Hemoam">Hemoam</option>
             <option value="Lacen">Lacen</option>
             <option value="Tropical">Tropical</option>
             <option value="UBS Itamaraty">UBS Itamaraty</option>
             <option value="UBS Santa Efigenia">UBS Santa Efigênia</option>
             <option value="UBS UBS Uniao">UBS União</option>
             <option value="UBS Ciganopoles">UBS Ciganopoles</option>
             <option value="UBS Urucu">UBS Urucu</option>
             <option value="UBS Espirito Santo">UBS Espirito Santo</option>
             <option value="UBS Taua Mirim">UBS Tauá Mirim</option>
             <option value="UBS Ribeirinha">UBS Ribeirinha</option>
             <option value="UBS Centro">UBS Centro</option>
             <option value="UBS Pera">UBS Pera</option>
             <option value="UBS Santa Helena">UBS Santa Helena</option>
             <option value="UBS Chagas Aguiar">UBS Chagas Aguiar</option>
             <option value="UBS Duque de Caixias">UBS Duque de Caixias</option>
             <option value="UBS Itapeua">UBS Itapéua</option>
             <option value="UBS Fluvial">UBS Fluvial</option>
             <option value="Outros">Outros</option>
</select> EM <input type="month" name="data" id="data" class="block w-40 p-2  border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-blue-300 focus:ring-blue-500 focus:border-blue-500" >
               
                  

                    
                      <input class="border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-blue-300 px-5 py-2.5" type="submit" value="Pesquisar">
               
                    
           
    </form>
              
               {{flash()}}
              
               
        
{%if media%}
<p>Média de Quantidade Dispensada é: {{resultado[0]}}</p>
<p>Média de Quantidade Solicitada é: {{resultado[1]}}</p>
  <div class="flex gap-2 items-center justify-start"> 
               {%if quantidade >0 %}   <div class="rounded-lg py-1 px-2 bg-green-800 w-max"><p class="text-sm text-white">Total de Registros: {{quantidade}}</p></div>{%endif%}
                 
            </div>
 <a href="{{url('relatorio/imprimir')}}"  class=" flex border border-gray-300 justify-center items-center rounded-lg hover:bg-gray-50 hover:border-blue-300 px-5 py-2.5" target="_blank">Imprimir</a>
      <div class="relative overflow-x-scroll ">
        <table class="w-full text-sm text-left text-gray-900">
          <thead class="text-xs text-gray-900 uppercase border-b  border-gray-700">
            <tr>
              
              <th scope="col" class="px-6 py-3">Nome</th>
               
               <th scope="col" class="px-6 py-3">Qnt</th>
               <th scope="col" class="px-6 py-3">Qnt Solicitada</th>
               <th scope="col" class="px-6 py-3">Local</th>

                <th scope="col" class="px-6 py-3">Data</th>
            
            </tr>
          </thead>
          <tbody>
              {%for rela in media%}
         
             <tr class="text-gray-700 text-md">
             
                  <td scope="row" class="px-6 py-4 max-w-[30vw] break-words">
                      {{maiuscula(rela.produto_nome)}} - {{maiuscula(rela.produto_fornecedor)}} - {{maiuscula(rela.produto_unidade)}}
                  </td>
                  
                  <td scope="row" class="px-6 py-4">
                      {{rela.quantidade}}
                  </td>
                  <td scope="row" class="px-6 py-4">
                      {{rela.qnt_solicitada}}
                  </td>
                   <td scope="row" class="px-6 py-4">
                      {{rela.local}}
                  </td>
            <td scope="row" class="px-6 py-4">
                      {{rela.data}} - {{rela.hora}}
                  </td>
             </tr>
            
              {%endfor%}
           
          </tbody>
        </table>
      </div>
{%endif%}
   
        </div>
        </div>
    </div>



{% endblock %}