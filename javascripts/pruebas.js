/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var requisiciones=new Array();
var datosGuardar= [];
$(document).ready(function(){



fnDetalleConsolidacion();

  $("#buscar-articulo-requisicion").keyup(function(){
    if($(this).val()!=''){
    dataObj = { 
          proceso: 'buscar-articulo-requisicion',
          articulorequisicion: $(this).val(),
        };

    $.ajax({
    type: "POST",
    dataType:"json",
    url: "modelo/consolidaciones_modelo.php",
    data: dataObj,
    
    success: function(res){
     $("#sugerencia-articulo-requisicion").show();
     $("#sugerencia-articulo-requisicion").empty();
     $("#sugerencia-articulo-requisicion").append(res.contenido);
      
    }
    });

    }
  });

$('#buscarArticuloRequisiciones').click(function(){
fnArticuloEnRequisiiones($("#buscar-articulo-requisicion").val());
$("#sugerencia-articulo-requisicion").hide();
$("#sugerencia-articulo-requisicion").empty();
$("#articuloBuscadoEnRequisiciones").empty();
/*
$("#articuloBuscadoEnRequisiciones").append("<b>Artículo buscado: "+$("#buscar-articulo-requisicion").val()+"</b>");*/

});


$('#todoslasrequisciones').click(function(){

  muestraCargandoGeneral();
 requisiciones= fnChecarSeleccionados();
 if(requisiciones.length>0){
datosGuardar =fnCrearPreconsolidacion(requisiciones,-1);
}else{
  muestraMensaje('Error no ha selccionado niguna requisición',1, 'OperacionMensaje', 5000);
}
  
ocultaCargandoGeneral();

});

$('#guardarConsolidacion').click(function(){
    /*for(a=0;a<datosGuardar.length;a++){
        alert(datosGuardar[a]);
    } */
  //  muestraModalGeneralConfirmacion(4, 'Confirmación consolidación','¿Esta seguro de que desea hacer?','', 'fnEnviarYlimpiar(\''+datosGuardar+'\')');
   fnEnviarRequisicionesCon(datosGuardar);
     fnLimpiarTabla('divTablaArticulosParaConsolidar', 'divDatosRequisisiones');
      $("#datosPreConsolidadcion").empty();
      $('#guardarConsolidacion').hide();
      $('#todoslasrequisciones').hide();

});
   

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
        fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divDatosRequisisiones', ' ', 1, columnasDescartarExportar,false,true);
    //$('#divDatosRequisisiones').jqxGrid({      editable: true}); 
    $('#todoslasrequisciones').show();
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
  var cadena='';
  
  //input normal
  /*
   $("input:checkbox[class=n_requisision]:checked").each(function () {
          requisicionesCS.push($(this).val());
           $(this).attr('disabled', true);
        }); */

  var griddata = $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getdatainformation');
  var rows = [];
  for (var i = 0; i < griddata.rowscount; i++){
      id=  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',i, 'id1');
      if(id==true){
      requisicionesCS.push($('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',i, 'id'));
  
     /* $('#row'+i+'divDatosRequisisiones').hide(); */

      }
   }

   if(requisicionesCS.length>0){
    $('#guardarConsolidacion').show();
  }

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

function fnEnviarRequisicionesCon(requisicionesEnviar){

  dataObj = { 
          proceso: 'consolidar',
          requisiciones: requisicionesEnviar
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
        muestraMensaje(data.contenido,1, 'OperacionMensaje', 5000);

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

function fnCrearTabalaConso(numeroDeFilas=2,cantidadesTotales=["1","2"],codigosArticulos=["a1","a2"],descripciones=["algo","art"],requisicionesTabla,visible){
  var datosConsolidadosPre = new Array(); 
  var cadenaDatosPre1='';
 

  cadenaDatosPre1+='<table id="tablaPreview" class="table table-striped table-bordered">';
  cadenaDatosPre1+='<thead class="bgc8" style="color:#fff"><tr><th> </th><th data-field="cantidad">Cantidad</th><th data-field="articulo">Código</th><th data-field="descripcion">Descripción</th></tr></thead>';
  for(i=0;i<numeroDeFilas;i++){
    datos=requisicionesTabla[i].split("-");
    cadenaDatosPre1+='<tr><td class="detalleconsolidacion" id="muestra'+i+'"><span class="glyphicon glyphicon-plus"></span></td>';
    cadenaDatosPre1+='<td>'+cantidadesTotales[i]+'</td><td>'+codigosArticulos[i]+'</td><td>'+descripciones[i]+'</td>';
  
       //detalle
      var oculto='display:none;';
      if(visible==i){
        
        oculto='';
      } 
    cadenaDatosPre1+='<tr id="completo'+i+'" style="'+oculto+'"> <td colspan="4">';
    datos=datos[1].split(",");

    cadenaDatosPre1+='<table class="table table-striped table-bordered" >';
    cadenaDatosPre1+='<thead class="bgc8" style="color:#fff"><tr><th> </th><th data-field="cantidad">Requisición</th><th data-field="articulo">Cantidad</th><th data-field="descripcion">Código</th><th>Descripción</th></tr></thead>';  
    for(a=0;a<datos.length;a++){
      //cadenaDatosPre1+=datos[a] + '<br>';
     x= fnObtenerDatosArticulos(datos[a]);

     cadenaDatosPre1+='<tr>';
     cadenaDatosPre1+='<td class="quitarRequisicion" id="quitar'+datos[a]+'"><span id="padre'+i+'" class="glyphicon glyphicon-minus"></span></td>';
      for(d=0;d<x.length;d++){

        cadenaDatosPre1+='<td>'+x[d]+'</td>';
      }
      cadenaDatosPre1+='</tr>';
    }
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

}

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
    columnasNombresGrid1 += " { text: 'Cantidad Total', datafield: 'cantidad', width: '10%', cellsalign: 'center', align: 'center', cellsalign: 'center', hidden: false,editable: false  },";
    columnasNombresGrid1 += " { text: 'Artículo', datafield: 'articulo', width: '10%', cellsalign: 'right', hidden: false },";
    columnasNombresGrid1 += " { text: 'Descripción', datafield: 'descripcion', width: '80%', cellsalign: 'center', align: 'center', cellsalign: 'left',hidden: false,editable: false  },";
    columnasNombresGrid1 += "]";

    var columnasDescartarExportar1 = [9]; 
   // fnLimpiarTabla('vistaConsolidadaPosible', 'divDatosRequisisiones');
    fnAgregarGrid_Detalle(datosConsolidadosPre, columnasNombres1, columnasNombresGrid1,'datosPreConsolidadcion','',1,columnasDescartarExportar1, false,true); 
   

} 

function fnObtenerDatosArticulos(fila){

var info=[];
var datos = $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid ('getRowData', fila);
for (var i in datos) {
  if(i=='requi'||(i=='cantidad') || (i=='codigo')|| (i=='descripcion') ){
  info.push(datos[i]); 
  //alert(datos[i]);
  }else{

  }
}

return info;
 
}
function fnCrearPreconsolidacion(requisiciones,visible){
  //var totalElmentos=requisiciones.length;
  var codigosArticulos= new Array();

 //obtener codigo de articulo
 for(i=0;i<requisiciones.length;i++){
 datos=requisiciones[i].split("-");
 codigosArticulos.push(datos[0]);

 } 
 /*for(i=0;i<codigosArticulos.length;i++){
   alert(codigosArticulos[i]);
 } */
 //evitar codigo repetidos
 var codigosUnicos = [];
$.each(codigosArticulos, function(i, el){
    if($.inArray(el,codigosUnicos) === -1) codigosUnicos.push(el);
});
//crear  datos para enviar
var datosEnviar=[];
var datosPre=[];
var cadena='';
var cadenaDatosPre='';


var cantidadesTotales=[];
var codigosArticulos=[];
var descripciones=[];

for(i=0;i<codigosUnicos.length;i++){
  cadena=''; //para base de datos
   cadena+=codigosUnicos[i]+"-"; //para base de datos
  cadenaDatosPre='';
  cadenaDatosPre+=codigosUnicos[i]+"-"; //manda codigo de articulo
  suma=0;
  descripcion='';
   for(j=0;j<requisiciones.length;j++){
  
      if(requisiciones[j].includes(codigosUnicos[i])){
           datos=requisiciones[j].split("-");
           cadena+="'"+datos[1]+"',"; //para base de datos // numero de requisision
          cadenaDatosPre+=datos[2]+","; //para  numero de fila
         //info[] = fnObtenerDatosArticulos(datos[2]);
         
         suma+=  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',datos[2], 'cantidad');
         descripcion=$('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('getcellvalue',datos[2], 'descripcion');
      }
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
 fnCrearTabalaConso(codigosUnicos.length,cantidadesTotales,codigosArticulos,descripciones, datosPre,visible);
 return datosEnviar;
}
function fnDetalleConsolidacion(){ 
  /*$('#vistaConsolidadaPosible > #datosPreConsolidadcion').on('click', function () {
    
 }); */
 /*$("#vistaConsolidadaPosible > #datosPreConsolidadcion").on('click', function () {
    alert();
                var getselectedrowindexes = $('#jqxgrid').jqxGrid('getselectedrowindexes');
                if (getselectedrowindexes.length > 0)
                {
                    // returns the selected row's data.
                    var selectedRowData = $('#jqxgrid').jqxGrid('getrowdata', getselectedrowindexes[0]);
                     
                    for(i=0;i<selectedRowData.length;i++){
                      alert(selectedRowData[i]);

                    }
                }
             }); */
}

$(document).on('click','#datosPreConsolidadcion',function(){
 /* alert();
  var a= $(this).jqxGrid('getGridParam', 'selrow'); //(getInd(rowKey)); //('getrowdata', getselectedrowindexes[0]); //('getGridParam', 'selrow');
  alert(a); */
});

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
 datos=requisiciones[i].split("-");
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

datosGuardar= fnCrearPreconsolidacion(requisiciones,visible);

 ocultaCargandoGeneral();
    
});
$(document).on('click','#btnCerrarModalGeneral',function(){
   window.open("panel_ordenes_compra.php","_self");
});
$(document).on('click','#divTablaArticulosParaConsolidar > #divDatosRequisisiones',function(){

  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('setcolumnproperty', 'cantidad','editable', false);
});
$(document).ready(function(){

  $('#divTablaArticulosParaConsolidar > #divDatosRequisisiones').jqxGrid('setcolumnproperty', 'cantidad','editable', false);
});
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



