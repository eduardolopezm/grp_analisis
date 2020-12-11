/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var requisiciones=new Array();
var datosGuardar= [];
var articulosConso=[];
 var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
function fnPartidasAutorizadasRequis(){
    dataObj = { 
          proceso: 'partidasAutorizadas'
        };
  muestraCargandoGeneral();
  $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/consolidaciones_modelo.php",
        data:dataObj
    })
  .done(function( data ) {
      if(data.result){
        
        fnFormatoSelect('#partidasSelect',data.contenido);
        ocultaCargandoGeneral();
      }else{
        ocultaCargandoGeneral();
      }
  })
  .fail(function(result) {
    console.log("ERROR");
    console.log( result );
    ocultaCargandoGeneral();
  });
}
function fnFormatoSelect(id,options){
   $(id).multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
           $(id).multiselect('dataprovider', options);
            
      $('.multiselect-container').css({ 'max-height': "220px" });
      $('.multiselect-container').css({ 'overflow-y': "scroll" });
             
}
$(document).ready(function(){

fnPartidasAutorizadasRequis();
var d = new Date();
var n = d.getMonth();
         n+=1;
  for(i=1;i<=9;i++){
    if(n==i){
        n="0"+i;
  }
}
var mes=n;
var anio=new Date().getFullYear();  

$("#dateDesde").val('01-'+mes+'-'+anio); 
$("#dateHasta").val('31-'+mes+'-'+anio); 
   
  // inicio  fin autoacompletador de input
  // $("#buscar-articulo-requisicion").keyup(function(){
  //   if($(this).val()!=''){
  //   dataObj = { 
  //         proceso: 'buscar-articulo-requisicion',
  //         articulorequisicion: $(this).val(),
  //       };

  //   $.ajax({
  //   type: "POST",
  //   dataType:"json",
  //   url: "modelo/consolidaciones_modelo.php",
  //   data: dataObj,
    
  //   success: function(res){
  //    $("#sugerencia-articulo-requisicion").show();
  //    $("#sugerencia-articulo-requisicion").empty();
  //    $("#sugerencia-articulo-requisicion").append(res.contenido);
      
  //   }
  //   });

  //   }
  // });// fin autoacompletador de input

$('#filtrarBtn').click(function(){
  partida=$("#partidasSelect").val();

  if(partida!='-1'){
    fnArticuloEnRequisiiones(partida);
  }else{
    muestraModalGeneral(4, titulo,'Seleccione una opción');
  }
// $("#sugerencia-articulo-requisicion").hide();
// $("#sugerencia-articulo-requisicion").empty();
// $("#articuloBuscadoEnRequisiciones").empty();
/*
$("#articuloBuscadoEnRequisiciones").append("<b>Artículo buscado: "+$("#buscar-articulo-requisicion").val()+"</b>");*/

});


$('#AgregarAPreConsolidacionBtn').click(function(){

  muestraCargandoGeneral();
 requisiciones= fnChecarSeleccionados();
 if(requisiciones.length>0){
  ad=fnCrearPreconsolidacion(requisiciones,-1);
  datosGuardar =ad[0];
  articulosConso=ad[1];
}else{
  muestraMensaje('Error no ha selccionado niguna requisición',1, 'OperacionMensaje', 5000);
}
  
ocultaCargandoGeneral();

});

$('#guardarConsolidacion').click(function(e){
   console.log('');
    /*for(a=0;a<datosGuardar.length;a++){
        alert(datosGuardar[a]);
    } */
  //  muestraModalGeneralConfirmacion(4, 'Confirmación consolidación','¿Esta seguro de que desea hacer?','', 'fnEnviarYlimpiar(\''+datosGuardar+'\')');
   // for(a=0;a<articulosConso.length;a++){
   //   // console.log("ad24"+articulosConso[a]);
   // }
   e.stopImmediatePropagation();
   $('#guardarConsolidacion').prop('disabled', true);
   $('#guardarConsolidacion').hide();
    
   if(datosGuardar.length>0 && articulosConso.length>0){
        
        
        $("#ModalGeneral1").modal('show');
        $("#ModalGeneral1_Mensaje").empty();
        $("#ModalGeneral1_Mensaje").append('¿Está seguro de guardar la consolidación?');

        $("#confirmacionModalGeneral1").click(function(e){
        e.stopImmediatePropagation(e);
        $('#confirmacionModalGeneral1').prop('disabled', true);
        $('#confirmacionModalGeneral1').hide();

        $("#ModalGeneral1").modal('hide'); 
        fnEnviarRequisicionesCon(datosGuardar,articulosConso);
        datosGuardar=[];
        articulosConso=[];
        $("#datosPreConsolidadcion").empty();
        
        $('#confirmacionModalGeneral1').prop('disabled', false);
        $('#confirmacionModalGeneral1').show();

       });
     }else{
         muestraModalGeneral(4,titulo,"No hay consolidación hecha.",'');
     }
      $('#guardarConsolidacion').prop('disabled',false);
      $('#guardarConsolidacion').show();
  // fnLimpiarTabla('divTablaArticulosParaConsolidar', 'divDatosRequisisiones');
   //   $("#datosPreConsolidadcion").empty();
    //  $('#guardarConsolidacion').hide();
    //  $('#AgregarAPreConsolidacionBtn').hide();

});
   
// $('#btnFiltrarConsolidaciones').click(function(){
//  /*   var dateDesde  = $('#dateDesde').val();
//     var dateHasta  = $('#dateHasta').val();
// */
//       dataObj = { 
//           proceso: 'consolidacionesHechas',
//           desde:$('#dateDesde').val(),
//           hasta:$('#dateHasta').val(),
//           partida: $('#txtPartida').val()
   
//         };
  
//     $.ajax({
//           method: "POST",
//           dataType:"json",
//           url: "modelo/consolidaciones_modelo.php",
//           data:dataObj
//       })
//     .done(function( data ) {
      
//         if(data.result){

//             columnasNombres = data.contenido.columnasNombres;
//             columnasNombresGrid = data.contenido.columnasNombresGrid;
//             var columnasDescartarExportar = [0];
//             fnLimpiarTabla('divTablaConsolidadas', 'divDatosConsolidadas');
//             var columnasExcel = [1, 3, 4, 5, 6];
//             var columnasVisuales = [0, 1, 2, 4, 5, 6];
//             nombreExcel = data.contenido.nombreExcel;

//           fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'divDatosConsolidadas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
//         //$('#divTablaConsolidadas > #divDatosConsolidas').jqxGrid({columnsheight:'35px'});
//         }
//     })
//     .fail(function(result) {
//       console.log("ERROR");
//         console.log( result );
//     });

// });
//
}); //fin documenr ready

function fnEnviarYlimpiar(datosGuardar){
   
}

function fnSelectArticulo(valor) {


$("#sugerencia-articulo-requisicion").hide();
$("#articuloAbuscar").empty();
$("#articuloAbuscar").val(valor);


$("#buscar-articulo-requisicion").val(""+valor);
}

function fnArticuloEnRequisiiones(idArticulo){
   fnLimpiarTabla('divTablaArticulosParaConsolidar', 'divDatosRequisisiones');
  dataObj = { 
          proceso: 'buscarArticuloEnRequisisiones',
          idArticulo: idArticulo
        };
  muestraCargandoGeneral();
  $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/consolidaciones_modelo.php",
        data:dataObj
    })
  .done(function( data ) {

      if(data.result){
      dataFuncionJason = data.contenido.datos;
      columnasNombres = data.contenido.columnasNombres;
      columnasNombresGrid = data.contenido.columnasNombresGrid;
      var columnasDescartarExportar = [2, 3];
        fnLimpiarTabla('divTablaArticulosParaConsolidar', 'divDatosRequisisiones');
        var columnasExcel = [2,3,4,5,6,7];
        var columnasVisuales = [0,2,3,4,5,6,7];
        nombreExcel = data.contenido.nombreExcel;
        //fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divDatosRequisisiones', ' ', 1, columnasDescartarExportar,false,true);
     
        fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'divDatosRequisisiones', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
     //$('#divDatosRequisisiones').jqxGrid({      editable: true}); 
    $('#AgregarAPreConsolidacionBtn').show();
/*
        $('#divDatosRequisisiones').jqxGrid({    editmode: 'click'}); 
        $('#divDatosRequisisiones').jqxGrid({      editable: true}); 
        $('#divDatosRequisisiones').jqxGrid({     selectionmode: 'singlecell'}); 
        */
        //bueno para checkbox
      /*  dataFuncionJason = data.contenido.datos;
        var data = dataFuncionJason;
        var aColumnasSource = eval(columnasNombres);
        var source = {
        datatype: "json",
        datafields: aColumnasSource,
        localdata: data
    };

        var dataAdapter = new $.jqx.dataAdapter(source);
        $("#divDatosRequisisiones").jqxGrid(
            {
               theme: 'estilogrp',
                source: dataAdapter,
                columnsresize: true,
                editable: true,
                selectionmode: 'singlecell',
                editmode: 'click',
                columns: eval(columnasNombresGrid)
                  
                  
                  
                  
            });*/
        
        ocultaCargandoGeneral();
      }else{
        ocultaCargandoGeneral();
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      ocultaCargandoGeneral();
  });
}

function fnChecarSeleccionados(){
  var requisicionesCS= new Array();
  var griddata = $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getdatainformation');
  var cadena='';
  var rows = [];

  for (var i = 0; i < griddata.rowscount; i++){
      id=  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',i, 'id1');
      
      if(id==true){
        requisicionesCS.push($('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',i, 'id'));// trae  clave-articulo y posicion
      }
   }

  if(requisicionesCS.length>0){ 
    $('#guardarConsolidacion').show();
  }
  // for(a in requisicionesCS ){

  //   console.log("partidas--cheseleccionados"+requisicionesCS);

  // }

   return requisicionesCS;
}


function fnCoincidenciasArray(coincidencia,array){
    var contadorCoincidencia=0;
    var existeConcidencia=false;

    for(i=0;i<array.length;i++){
      if(coincidencia==array[i]){
        contadorCoincidencia++;
      }
    }
    if(contadorCoincidencia>0){
      existeConcidencia=true;
    }else{ 
      existeConcidencia=false;
    }
    return existeConcidencia;
}

function fnEnviarRequisicionesCon(requisicionesEnviar,articulosRequi){

/*var codigosUnicos1 = [];
$.each(codigosArticulos1, function(i, el){
    if($.inArray(el,codigosUnicos1) === -1) codigosUnicos1.push(el);
});*/

  dataObj = { 
          proceso: 'consolidar',
          requisiciones: requisicionesEnviar,
          codigos:articulosRequi
        };
  muestraCargandoGeneral();
  $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/consolidaciones_modelo.php",
        data:dataObj
    })
  .done(function( data ) {
      if(data.result){
        
        /*info=data.contenido.datosConsolidados;
     
        fnLimpiarTabla('divTablaConsolidas', 'divDatosConsolidados');
        fnAgregarGridv2(info, 'divDatosConsolidados','b'); */
        
          
        muestraModalGeneral(4, titulo,data.contenido,'');
           
          setTimeout(function(){
            $("#filtrarBtn").trigger( "click");
            
          },1000);

        ocultaCargandoGeneral();
      }else{
        ocultaCargandoGeneral();
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      ocultaCargandoGeneral();
  });
}

function fnCrearPreTablaConsolidacion(numeroDeFilas=2,cantidadesTotales=["1","2"],codigosArticulos=["a1","a2"],descripciones=["algo","art"],requisicionesTabla,visible){
  var datosConsolidadosPre = new Array(); 
  var cadenaDatosPre1='';
  var codigoArt= [];
  var temporal;
  cadenaDatosPre1+='<table id="tablaPreview" class="table table-striped table-bordered">';
  cadenaDatosPre1+='<thead class="bgc8" style="color:#fff"><tr><th> </th><th data-field="cantidad">Cantidad</th><th data-field="articulo">Partida</th><th data-field="descripcion">Descripción partida</th></tr></thead>';
  for(i=0;i<numeroDeFilas;i++){
   
    datos=requisicionesTabla[i].split("@");
    
    cadenaDatosPre1+='<tr><td class="detalleconsolidacion" id="muestra'+i+'"><span class="glyphicon glyphicon-plus"></span></td>';
    cadenaDatosPre1+='<td>'+cantidadesTotales[i]+'</td><td>'+codigosArticulos[i]+'</td><td>'+descripciones[i]+'</td>';
    
    
      var oculto='display:none;';
      if(visible==i){
        
        oculto='';
      } 
    cadenaDatosPre1+='<tr id="completo'+i+'" style="'+oculto+'"> <td colspan="4">';
    datos=datos[1].split(",");

   
    cadenaDatosPre1+='<table class="table table-striped table-bordered" >';
    cadenaDatosPre1+='<thead class="bgc8" style="color:#fff"><tr><th> </th><th data-field="cantidad">Requisición</th><th data-field="articulo">Cantidad</th><th data-field="descripcion">Código</th><th>Descripción</th></tr></thead>';  
    for(a=0;a<datos.length;a++){
    
    //console.log("datos--"+datos[a]); // numero  de fila de requisicion
     temporal='';
     x= fnObtenerDatosArticulos(datos[a]); // obtiene dato  de la requisicion
       
     cadenaDatosPre1+='<tr>';
     cadenaDatosPre1+='<td class="quitarRequisicion" id="quitar'+datos[a]+'"><span id="padre'+i+'" class="glyphicon glyphicon-minus"></span></td>';
      

      // for(d=0;d<p.length;d++){
      
      //   cadenaDatosPre1+='<td>'+p[d]+'</td>';
      // }
     
   
      for(d=0;d<x.length;d++){   
     // cadenaDatosPre1+='<td>'+x[d]+'</td>';
       switch(d){
                  //0             //1             //2             //3                   //4             //5
        //   // (i=='requi' || (i=='cantidad') || (i=='codigo') || (i=='descripcion')|| (i=='precio') || (i=='clavepre') 
          case 0:
            temporal+=x[d]+"@"; //numerp requi
            cadenaDatosPre1+='<td>'+x[d]+'</td>';
          break;
          case 1:
            temporal+=x[d]+"@"; //cantidad
            cadenaDatosPre1+='<td>'+x[d]+'</td>';
          break;
           case 2:
            temporal+=x[d]+"@"; //codigo
            cadenaDatosPre1+='<td>'+x[d]+'</td>';
          break;

         case 3:
            temporal+=x[d]+"@"; //descripcion
            cadenaDatosPre1+='<td>'+x[d]+'</td>'; // descripcion
          break;
        case 4:
           temporal+=x[d]+"@"; //precio
          break;
        case 5:
               temporal+=x[d]+"@"; //clave
          break;
        case 6:
             temporal+=x[d]; //no
          break;
      }
      // fin
        // if(d==0){
        //  // codigoArticulos.push(x[d]);
        //  temporal+=x[d]+"@";
        // }
        // if(d==2){
        //  // codigoArticulos.push(x[d]);
        //  temporal+=x[d];
        // }
        
      }// fin primer for
      //console.log(temporal);
       codigoArt.push(temporal);
      cadenaDatosPre1+='</tr>';
    }// fin segndo  for
   cadenaDatosPre1+='</table>';

   cadenaDatosPre1+='</td></tr>';
   //detalle

    
    cadenaDatosPre1+='</tr>';
  
  }
 cadenaDatosPre1+='</table>';

$("#datosPreConsolidadcion").empty();
$("#datosPreConsolidadcion").append(cadenaDatosPre1); 
$("#datosPreConsolidadcion").focus();
  /*for(i=0;i<numeroDeFilas;i++){
var filas={};
    filas['cantidad']=cantidadesTotales[i];
    filas['articulo']=codigosArticulos[i];
    filas['descripcion']=descripciones[i];
    
    datosConsolidadosPre[i]=filas; 
}
  $(function () {
    $('#tablePreConsolidadcion').bootstrapTable({
        data: datosConsolidadosPre
    });
}); */
     return  codigoArt;
}

 

function fnObtenerDatosArticulos(fila){

var pintar=[];
var info=[];
var datos = $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid ('getRowData', fila);
//console.log("-----------");
for (var i in datos) {
   if(i=='requi' || (i=='cantidad') || (i=='codigo') || (i=='descripcion')|| (i=='precio') || (i=='clavepre') || (i=='no') ){
    //console.log(datos[i]);
  pintar.push(datos[i]); 
  //alert(datos[i]);
  }
  // if(i=='requi' || (i=='cantidad') || (i=='codigo') || (i=='descripcion')|| (i=='precio') || (i=='clavepre') ){
  //   //console.log(datos[i]);
  // info.push(datos[i]); 
  // //alert(datos[i]);
  // }
}

//para mover requisicion del primer lugar
/*totales =(info.length)-1;
tempo=info[0];
//console.log(totales);
var retorno=[];
for (a=0; a<info.length;a++){
  //console.log(a +"-->"+info[a]);
  if(a<totales){
      retorno.push(info[(a+1)]);
  }else{
    retorno.push(tempo);
  } 


}*/
//console.log("+++++++++++++++-----------"+ info);
 var retorno=[];
 retorno.push(pintar);
 retorno.push(12);
 
 return pintar;
 
}
function fnCrearPreconsolidacion(requisiciones,visible){

  var codigosArticulos= new Array();
 //obtener codigo de articulo
 for(i=0;i<requisiciones.length;i++){
 datos=requisiciones[i].split("@");// separa  codigo articulo  y posicion
 codigosArticulos.push(datos[0]); // ya es el codigo  ahora es la partida

 } 
 /*for(i=0;i<codigosArticulos.length;i++){
   alert(codigosArticulos[i]);
 } */
 //evitar codigo repetidos // ahora  quita tolas las partidas repetidas
 var codigosUnicos = [];
$.each(codigosArticulos, function(i, el){
    if($.inArray(el,codigosUnicos) === -1) codigosUnicos.push(el);
});
//crear  datos para enviar
var datosEnviar=[];
var datosConsolidados=[];
var datosPre=[];
var cadena='';
var cadenaDatosPre='';


var cantidadesTotales=[];
var codigosArticulos=[];
var descripciones=[];

for(i=0;i<codigosUnicos.length;i++){
  cadena=''; //para base de datos
  cadena+=codigosUnicos[i]+"@"; //partida
  cadenaDatosPre='';
  cadenaDatosPre+=codigosUnicos[i]+"@"; //manda codigo de articulo
  suma=0;
  descripcion='';
   for(j=0;j<requisiciones.length;j++){
  
      if(requisiciones[j].includes(codigosUnicos[i])){
           datos=requisiciones[j].split("@");
           //console.log(datos);
           cadena+="'"+datos[1]+"',"; //para base de datos // numero de requisision
          cadenaDatosPre+=datos[2]+","; //para  numero de fila
         //info[] = fnObtenerDatosArticulos(datos[2]);
         
         suma+=  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',datos[2], 'cantidad');
         //descripcion=$('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',datos[2], 'descripcion');
        descripcion=$('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',datos[2], 'descpartida');
        
        
      }
   //     console.log(" se for --");
   // console.log(cadena);
   }
   //datos para preconsolidacion
  codigosArticulos.push(codigosUnicos[i]);
  cantidadesTotales.push(suma);
  descripciones.push(descripcion);
  //fin datos para preconsolidacion

   cadena=cadena.slice(0, -1); //para base de datos
  cadenaDatosPre= cadenaDatosPre.slice(0, -1); //para numero de fila
   
   datosEnviar.push(cadena);
   datosPre.push(cadenaDatosPre);
   // console.log(" pimer for --");
   // console.log(cadena);
 }

// para base de datos
/*
 for(x=0;x<datosEnviar.length;x++){
alert(datosEnviar[x]);
 } */
// para numeros de fila
 /*for(x=0;x<datosPre.length;x++){
alert(datosPre[x]);
 } */
//suma articulos

 //fnCrearTablaPreConsolidacion(codigosUnicos.length,cantidadesTotales,codigosArticulos,descripciones);

 itemsArticulos= fnCrearPreTablaConsolidacion(codigosUnicos.length,cantidadesTotales,codigosArticulos,descripciones, datosPre,visible);
 datosConsolidados.push(datosEnviar);
 datosConsolidados.push(itemsArticulos);
 
 return datosConsolidados;

}


$(document).on('click','.detalleconsolidacion',function(){
 var amostrar =  $(this).attr('id');
 amostrar=amostrar.replace("muestra","");
 $('#completo'+amostrar).toggle();
        /*var $current=$(this).parent().parent().index();
        if($(this).hasClass('clicked'))
        {
            var next=$current+1;
            $('table tr:eq('+next+')').remove();
            $(this).removeClass('clicked');
        }else{
        $(this).addClass('clicked');
   var $current= $(this).parent().parent().index();
       $("table tr:eq("+$current+")").after("<tr ><td colspan='4'><div style='border: 0.1px solid #1B693F;'>Detalle</div></td></tr>");
        } */
    
});

$(document).on('click','.quitarRequisicion',function(){

  muestraCargandoGeneral();
 var quitar =  $(this).attr('id');
 quitar=quitar.replace("quitar","");
 var requi=[];
 var datos=[];
 var requiQ=[];

//obtener fila-requi
 for(i=0;i<requisiciones.length;i++){
 datos=requisiciones[i].split("@");
 requi.push(datos[2]);
 }

 for(d=0;d<requi.length;d++){
    if(requi[d] === quitar) {
      $('#row'+quitar+'divDatosRequisisiones').show();
       requiQ.push(requisiciones[d]);
       }
    }

  for(j=0;j<requisiciones.length;j++){
  
      if(requisiciones[j]==requiQ[0]){
         requisiciones.splice(j,1);
      }
    }

var visible =  $(this).find('span').attr('id');
visible=visible.replace("padre","");
ad=fnCrearPreconsolidacion(requisiciones,visible);
datosGuardar= ad[0];
articulosConso=ad[1];

 ocultaCargandoGeneral();
    
});
$(document).on('click','#btnCerrarModalGeneral',function(){
   //window.open("panel_ordenes_compra.php","_self");
});
$(document).ready(function(){
 //$("#btnFiltrarConsolidaciones").trigger( "click"); 
});

$(document).on('cellbeginedit', '#divDatosRequisisiones', function(event) {
    $(this).jqxGrid('setcolumnproperty', 'requi', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'cantidad', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'codigo', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'descripcion', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'descpartida', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'precio', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'clavepre', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'partida', 'editable', false);
});
    
function fnRegresarPanelRequisicion(){
    location.replace("./PO_SelectOSPurchOrder.php");
}
/*
$(document).on('click','#divDatosRequisisiones > .jqx-grid-cell > .jqx-checkbox-default',function(){


    $('#todoslasrequiscione').show();

}); */
//$(document).on('cellendedit','#divTablaArticulosParaConsolidar > #divDatosRequisisiones',function(event){
   
//  $('#divDatosRequisisiones').jqxGrid({      editable: true}); 
 // $('#divDatosRequisisiones').jqxGrid({ editable: true}); 
 //  var args = event.args;
   //if(args.value==true){
    //alert(args.value);
    //id=  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',args.rowindex, 'id');
    //requisiciones.push(id);
   //}
    
/*
v=  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',datos[2], 'id1');
alert(v); */

  // events
            /*$(this).on('cellbeginedit', function (event) {
                var args = event.args;
               // $("#cellbegineditevent").text("Event Type: cellbeginedit, Column: " + args.datafield + ", Row: " + (1 + args.rowindex) + ", Value: " + args.value);
               alert(args.value);
            }); */

            //$(this).on('cellendedit', function (event) {
               
               // $("#cellendeditevent").text("Event Type: cellendedit, Column: " + args.datafield + ", Row: " + (1 + args.rowindex) + ", Value: " + args.value);
                  
            //}); 
//events 

//});

/*
function  fnCrearTablaPreConsolidacion(numeroDeFilas=2,cantidadesTotales=["1","2"],codigosArticulos=["art1","art2"],descripciones=["ejemplo1","ejemplo2"]){
 fnLimpiarTabla('vistaConsolidadaPosible', 'datosPreConsolidadcion');
 var datosConsolidadosPre = new Array(); 

  for(i=0;i<numeroDeFilas;i++){
 var filas={};
    

    filas['cantidad']=cantidadesTotales[i];
    filas['articulo']=codigosArticulos[i];
    filas['descripcion']=descripciones[i];
    
    datosConsolidadosPre[i]=filas;
  
     
  }

    var columnasNombres1 ='';
    columnasNombres1 += "[";
    columnasNombres1 += "{ name: 'cantidad', type: 'number' },";
    columnasNombres1 += "{ name: 'articulo', type: 'string' },";
    columnasNombres1 += "{ name: 'descripcion', type: 'string' },";
    columnasNombres1 += "]";


    var columnasNombresGrid1='';
    columnasNombresGrid1 += "[";
    columnasNombresGrid1 += " { text: 'Cantidad Total', datafield: 'cantidad', width: '10%', cellsalign: 'center', align: 'center', cellsalign: 'center', hidden: false },";
    columnasNombresGrid1 += " { text: 'Artículo', datafield: 'articulo', width: '10%', cellsalign: 'right', hidden: false },";
    columnasNombresGrid1 += " { text: 'Descripción', datafield: 'descripcion', width: '80%', cellsalign: 'center', align: 'center', cellsalign: 'left',hidden: false },";
    columnasNombresGrid1 += "]";

    var columnasDescartarExportar1 = [9]; 
   // fnLimpiarTabla('vistaConsolidadaPosible', 'divDatosRequisisiones');
    fnAgregarGrid_Detalle(datosConsolidadosPre, columnasNombres1, columnasNombresGrid1,'datosPreConsolidadcion','',1,columnasDescartarExportar1, false,true); 
   

}*/




