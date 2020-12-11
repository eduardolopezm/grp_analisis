/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var modelo="modelo/procesoCompraModeloV2.php";
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';

function fnListaProvedoresSugeridos(){
    muestraCargandoGeneral();

    dataObj = {
      proceso: 'requisProvSug'
  };
       $.ajax({
            method: "POST",
            dataType: "json",
            url: modelo,
            async: false,
            data:  dataObj
        })

  .done(function(data) {

      if (data.result) {

        fnLimpiarTabla('tablaTipos', 'datosTipos');


            columnasNombres = '';
            columnasNombres += "[";
            columnasNombres += "{ name: 'checkBoxProv', type: 'bool'},";
            columnasNombres += "{ name: 'id', type: 'string' },";
            columnasNombres += "{ name: 'requi', type: 'string'},";
            columnasNombres += "{ name: 'estatus', type: 'string'},";
            
            columnasNombres += "{ name: 'fecha', type: 'string' },";
            /*columnasNombres += "{ name: 'descripcion', type: 'string' },";
            columnasNombres += "{ name: '', type: 'string' },";*/
              
          
            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'checkBoxProv', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'Folio',datafield: 'id', width: '20%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Numero requisicion', datafield: 'requi', width: '20%', cellsalign: 'center', align: 'center',hidden: false },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '20%', cellsalign: 'center', align: 'center',hidden: false },";
            
            columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '37%', cellsalign: 'center', align: 'center', hidden: false },";
            /*columnasNombresGrid += " { text: '',datafield: '', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            */
            columnasNombresGrid += "]";

            var columnasExcel = [ 1, 2,3,4];
            var columnasVisuales = [0, 1, 2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosTipos', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
           
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

        // $("#dateDesde").val('01-'+mes+'-'+anio); 
        // $("#dateHasta").val('31-'+mes+'-'+anio); 
        ocultaCargandoGeneral();
       
 
      } else {
          ocultaCargandoGeneral();
      }

  })
  .fail(function(result) {
      console.log("ERROR");
      console.log(result);
      ocultaCargandoGeneral();

  });
}
function fnDatosRequisicion(requisicion,divMostrar){
    muestraCargandoGeneral();
    atras='<br><div class="btn bgc8 cerrarDetalle" style="color:white;">'+
    ' <span class="glyphicon glyphicon-remove" ></span> Atras </div>';
    dataObj = { 
          proceso: 'getRequi',
          requisicion:requisicion
   
        };
  
  $.ajax({
        method: "POST",
        dataType:"json",
        async:false,
        url: modelo,
        cache:false,
        data:dataObj
    })
  .done(function( data ) {
      var datosTabla='';
      var td="<td>";
      var tdf="</td>";
       
    
      if(data.result){
        $("#tablaTipos").fadeOut( "slow");
        $("#botones").hide();
        datosTabla+='<br> <br><table class="table table-striped table-bordered" id="tablaDatosRequisicion" >'+
       '<thead class="bgc8" style="color:#fff">'+
                  '<tr>'+
                  
                  '<th>Partida</th>'+
                  '<th>Renglon</th>'+ 
                  '<th>Descripción</th>'+
                  '<th>Id partida</th>'+
                  '<th>Artículo</th>'+
                  '<th>Unidad</th>'+
                  '<th>Precio</th>'+
                  '<th>Cantidad</th>'+
                  '<th>Total</th>'+
                  '<th>Existencia</th>'+
                  '<th>Orden</th>'+
                 /* '<th>Clave presupuestal</th>'+
                  '<th>Descripción presupuestal</th>'+ */
                
                  '</tr></thead><tbody>'; 
       
        datos=data.contenido.requisicion; 
        datosRequiArray=[];
        datosRequiArray=datos;
        for (a in datos){
           datosTabla+='<tr>';
           datosTabla+= td+  datos[a].idPartida   + tdf;
           datosTabla+=  td+  datos[a].renglon     + tdf;
           datosTabla+=  td+  datos[a].descPartida + tdf;
           datosTabla+=  td+  datos[a].idItem      + tdf;
           datosTabla+=  td+  datos[a].descItem    + tdf;
           datosTabla+=  td+  datos[a].unidad      + tdf;
          // datosTabla+=  td+  datos[a].tipo        + tdf;
           datosTabla+=  td+  datos[a].precio      + tdf;
           datosTabla+=  td+  datos[a].cantidad    +  tdf;
           datosTabla+=  td+  datos[a].total       + tdf;
           datosTabla+=  td+  datos[a].existencia  + tdf;
           datosTabla+=  td+  datos[a].orden       + tdf;
          /* datosTabla+=  td+  datos[a].clavePresupuestal + tdf;
           datosTabla+=  td+  datos[a].descLarga; */

           datosTabla+='</tr>';

        }

        datosTabla+='</tbody></table>';
        $(divMostrar).empty();
     

        $(divMostrar).append(datosTabla);
        partidas=[];
        $("#tablaDatosRequisicion tbody tr").each(function() { 
         partida= $(this).find("td:eq(" + 0 + ")").html();
         partidas.push(partida);
         });

        fnProvSug(partidas,provSug,requisicion,atras);

        ocultaCargandoGeneral();
      }


  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      $(divMostrar).append(atras);
  });

}

$(document).ready(function(){
	fnListaProvedoresSugeridos();

  fnFijarFecha();
  $('#btnCargarCotizacionDiv').click(function(){
   // $('#divCargarCotizacion').removeAttr('style');
   $('#divCargarCotizacion').show();
    $("#tablaTipos").hide("slow");
    $("#botones").hide("slow");
  });

  $('#btnGuardarProvSug').click(function(){
      var partidas=[];
      var articulos=[];
      var descripciones=[];
      var cotizaciones=[];

      requi=$('#numeroRequi').val();
      provedor=$('#idProveedor').html();
      //console.log("prov "+provedor);
      //console.log("nrequi "+numero);
      
      $("#datosTablaCotizacion tbody tr").each(function () {
   //=$(this).eq(1).val();
       
         partidas.push($(this).find('.cotizacionPartida').html());
         articulos.push($(this).find('.cotizacionArticulo').html());
         descripciones.push($(this).find('.cotizacionDescripcion').html());
         cotizaciones.push($(this).find('.cotizacionProve').html());
       //console.log(partida+" "+articulo+" "+cotizacion);
      
    });
      
       fnGuardarDatosCotizacion(partidas,articulos,descripciones,cotizaciones,requi,provedor);


  });

  $('#btnCuadroComparativo').click(function(){
     requis= fnChecarSeleccionados('requi');

     if(requis.length>1){
          muestraModalGeneral(4, titulo,"Solo debe seleccionar una requisición para este proceso");

     }else if(requis.length==1){
          
         fnTraerDatosCuadroCompativo(requis[0]);
          /*if(retorno==false ){ */
            
         // }
     }else if(requis.length==0){
         muestraModalGeneral(4, titulo,"Debe seleccionar una requisición");
     }
  });

  $("#btnCargarContrato").click(function(){
     requis= fnChecarSeleccionados('requi');
      if(requis.length>1){
          muestraModalGeneral(4, titulo,"Solo debe seleccionar una requisición para este proceso");

     }else if(requis.length==1){
      estatus= fnChecarSeleccionadosNormal('estatus');
     // Contrato cargado
     console.log("Estatus del contrato.."+estatus+" de la requi"+requis);
     if( estatus!='Contrato cargado'){
        var contrato1="Contrato";
          contrato=fnCrearTemplateArchivos(contrato1,requis[0]);
          $("#divCargarContrato").empty();
          $("#divCargarContrato").append(contrato);
                
           
         $('#divCargarContrato').show();
         $("#tablaTipos").hide("slow");
         $("#botones").hide("slow");
          }else{
            muestraModalGeneral(4, titulo,"El contrato ya fue cargado");
          }
     }else if(requis.length==0){
         muestraModalGeneral(4, titulo,"Debe seleccionar una requisición");
     }

  });
fnCargarArchivosTemplate();
//
});
function fnGuardarDatosCotizacion(partidas,articulos,descripciones,cotizaciones,requi,provedor){
  muestraCargandoGeneral();
     dataObj = { 
          proceso: 'guardarDatosCotizacion',
          requi:requi,
          provedor:provedor,
          partidas:partidas,
          articulos:articulos,
          descripciones:descripciones,
          cotizaciones:cotizaciones
        };
  
  $.ajax({
        method: "POST",
        dataType:"json",
        async:false,
        url: modelo,
        data:dataObj
    })
  .done(function( data ) {
   
      if(data.result){
      ocultaCargandoGeneral();
      muestraModalGeneral(4, titulo,data.contenido);

      $(".datosCotizacionExcel").empty();
      $("#divGuardarCotizacion").hide();
      }else{
        $(".datosCotizacionExcel").empty();
        $("#divGuardarCotizacion").hide();
        muestraModalGeneral(4, titulo,"No se guardaron los datos intente de nuevo");
        ocultaCargandoGeneral();
      }
  })
  .fail(function(result) {
     $(".datosCotizacionExcel").empty();
     $("#divGuardarCotizacion").hide();
        muestraModalGeneral(4, titulo,"No se guardaron los datos intente de nuevo");
    console.log("ERROR");
      console.log( result );
      ocultaCargandoGeneral();
  });
}

function  fnTraerDatosCuadroCompativo(requi){
  var existe=false;
    muestraCargandoGeneral();
     dataObj = { 
          proceso: 'traerDatosCuadroComparativo',
          requi:requi
        
        };
  
  $.ajax({
        method: "POST",
        dataType:"json",
        async:false,
        url: modelo,
        data:dataObj
    })
  .done(function( data ) {
   
      if(data.result){
       datos=data.contenido.datosPorArticulo; 
      if(datos.length>0){

    
      ocultaCargandoGeneral();
      //muestraModalGeneral(4, titulo,data.contenido);
      datosTabla='';
      datosInfo=[];
   
      td="<td>";
      tdf="</td>";
       datosTabla+='<br>'+
        '<table class="table table-striped table-bordered" id="tablaDatosRequisicion" >'+
       '<thead class="bgc8" style="color:#fff">'+
                  '<tr>'+
                  
                  '<th>Proveedor</th>'+
                  '<th>Partida</th>'+ 
                  '<th>Codigo artículo</th>'+
                  '<th>Descripción</th>'+
                  '<th>Cotización</th>'+
                 
                 /* '<th>Clave presupuestal</th>'+
                  '<th>Descripción presupuestal</th>'+ */
                
                  '</tr></thead><tbody>'; 
     
       
        for (a in datos){
           datosTabla+='<tr>';
           datosTabla+= td+  datos[a].provedor   + tdf;
           datosTabla+=  td+  datos[a].partida     + tdf;
           datosTabla+=  td+  datos[a].articulo + tdf;
           datosTabla+=  td+  datos[a].descripcion      + tdf;
           datosTabla+=  td+  datos[a].cotizacion    + tdf;
         

           datosTabla+='</tr>';


        }

        datosTabla+='</tbody></table>';
        
        datosTabla1='';
        montos=data.contenido.montoTotalRequi;
        datosTabla1+='';
        datosTabla1+='<br><br>'+
                      '<table class="table table-striped table-bordered" id="tablaDatosRequisicion" >'+
                     '<thead class="bgc8" style="color:#fff">'+
                      '<tr>'+
                  '<th>Proveedor</th>'+
                  '<th>Monto cotizado para requisición </th>'+
                    '</tr></thead><tbody>'; 
         for (a in montos){
           datosTabla1+='<tr>';
           datosTabla1+= td+  montos[a].provedor   + tdf;
           datosTabla1+=  td+  montos[a].montoTotal    + tdf;
           datosTabla1+='</tr>';
           datosInfo.push(montos[a].montoTotal+"-"+  montos[a].provedor);
           

        }
          datosTabla1+='</tbody></table>';
          //la informacion viene en monto asc entonces el monto mayor es el utlimp
          totalItems=datosInfo.length;
          ultimo=datosInfo[totalItems-1];
          ultimo=ultimo.split("-");
          montoGrande=ultimo[0];
          barras='';
          for(a=0;a<(datosInfo.length);a++){
            cantidad=datosInfo[a].split("-");

           porcentaje= fnCalcularPorcentaje(montoGrande,cantidad[0]);
           barras+='<dd class="percentage percentage-'+porcentaje+'" > <span class="text">'+datosInfo[a]+'</span>  </dd>';
           
           console.log((datosInfo[a]) +" "+ porcentaje);
          }

           atras='<br><div class="btn bgc8 cerrarDetalle" style="color:white;">'+
       ' <span class="glyphicon glyphicon-remove" ></span> Atras </div>';
          
          //console.log(montolGrande);
          chart='';

chart='<br><div  class="col-md-12 text-center"><div class="col-md-12 text-left"><!-- chart-->'+
 '<h2 class="flot"><i class="fa fa-pie-chart" aria-hidden="true"></i></h2>'+
  '<!--  <h1>Performance</h1>'+
   '  <small class="ash">Lorem ipsum dolor sit amet, conset</small><br>-->'+
'<dl>'+
  '<dt> '+
  '</dt>'+barras+'</dl>'+
'</div></div><br><!--fin chart-->';

        $("#datosCuadroComparativo").empty();
        $("#datosCuadroComparativo").append(datosTabla1+chart+datosTabla+atras);



        $("#tablaTipos").fadeOut( "slow");
        $("#botones").fadeOut("slow");
        existe=true;
      }else{
        ocultaCargandoGeneral();
        ocultaCargandoGeneral();
        muestraModalGeneral(4, titulo,"No existe información  para crear cuardro comparativo.");
      }
      }else{
       

       
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      ocultaCargandoGeneral();
      $("#datosCuadroComparativo").append("Sin datos para cuadro  comparativo "+atras);
  });

  return existe;
}
function fnCalcularPorcentaje(montoGrande,cantidad){
 cantidad=parseInt(cantidad);
 montoGrande=parseInt(montoGrande);
 porcentaje=((cantidad*100)/montoGrande) ;
 porcentaje=Math.trunc(porcentaje);
 return porcentaje;
}
function  fnProvSug(partidas,divMostrar,requisicion,atras,tipo="total"){
  var tabla='';
    dataObj = { 
          proceso: 'obtenerProvedoresSugeridos',
          tipo:tipo,  //
          partidas:partidas
   
        };
  
  $.ajax({
        method: "POST",
        dataType:"json",
        async:false,
        url: modelo,
        data:dataObj
    })
  .done(function( data ) {
   
      if(data.result){
          proveSug=  data.contenido.datosPro;
       
          tabla+='<br><h3 class="bgc8" style="color:#fff;"><span class="glyphicon glyphicon-chevron-right"></span>Proveedores Sugeridos para la requisición '+requisicion +'</span><br></h3>';
          tabla+='<table id="tablaPreview" class="table table-striped table-bordered">';
          tabla+="<thead><th></th><th>Partida</th> <th>Nombre proveedor</th>  <th>Id proveedor  </th> <th>Email</th> </thead>"
        
          for(a  in   proveSug ){
              tabla+="<tr>";
             // tabla+='<td>'+'<input type="checkbox" class="provedoresPart" name="provedores[]" data-prove="'+proveSug[a].idsup +'" /> </td>';
              
              tabla+='<td> </td>';
              tabla+='<td>'+proveSug[a].partida+' </td>';
              tabla+='<td>'+proveSug[a].nombre+' </td>';
              tabla+='<td>'+proveSug[a].idsup+' </td>';
              tabla+='<td>'+proveSug[a].email+' </td>';
              
              tabla+="</tr>";   

          }
        
          tabla+='</table>';
    
        $(divMostrar).empty();
        $(divMostrar).append(tabla+atras);

        //$('#botones').append('<button  type="button" id="btnSolCoti" name="btnSolCoti" class=" glyphicon glyphicon-save btn btn-default botonVerde">Avanzar</button>');
           
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      $(divMostrar).append('No existen proveedores sugeridos '+atras);
  });


}



$(document).on('cellbeginedit', '#datosTipos', function(event) {
   // $(this).jqxGrid('setcolumnproperty', 'checkBoxProv', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'id', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'requi', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'estatus', 'editable', false);
     
});

$(document).on('cellselect', '#tablaTipos > #datosTipos', function(event) {
   requi= event.args.datafield;
    if (requi == 'requi') {
        fila = event.args.rowindex;
        requi = $('#tablaTipos > #datosTipos').jqxGrid('getcellvalue', fila, 'requi');
        requi = $('<div>').append(requi).find('u:first').html();
        fnDatosRequisicion(requi,'#datosRequi');

        
    }
});


$(document).on('click','#btnSolicitarCotizacion',function(){
  
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Desea solicitar la cotización de los requisiciones seleccionada?', '', 'fnObtenerRequis()');
});
$(document).on('click','.cerrarDetalle',function(){
  $("#tablaTipos").fadeIn( "slow");
  $("#botones").show();
  $("#datosRequi").empty();
  $("#provSug").empty();
  $("#divCargarCotizacion").hide();
  $("#divCargarContrato").hide();


  $("#datosCuadroComparativo").empty();
  //$("#datosCuadroComparativo").();
  
  
});

function fnChecarSeleccionados(celda){
        var requisicionesCS= new Array();
        var griddata = $('#tablaTipos > #datosTipos').jqxGrid('getdatainformation');
        var cadena='';

        for (var i = 0; i < griddata.rowscount; i++){
            check=  $('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i, 'checkBoxProv');

            if(check==true){
             // alert($('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i,celda));
            requi= $('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i,celda)
             requi = $('<div>').append(requi).find('u:first').html();
               requisicionesCS.push(requi);
            }
        }

       return requisicionesCS;
}
function fnChecarSeleccionadosNormal(celda){
        var requisicionesCS= new Array();
        var griddata = $('#tablaTipos > #datosTipos').jqxGrid('getdatainformation');
        var cadena='';

        for (var i = 0; i < griddata.rowscount; i++){
            check=  $('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i, 'checkBoxProv');

            if(check==true){
             // alert($('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i,celda));
            requi= $('#tablaTipos > #datosTipos').jqxGrid('getcellvalue',i,celda)
              requisicionesCS.push(requi);
             
            }
        }
// SI  VEN codigo repetido fue por las prisas ustedes disculpen la mala praxis
       return requisicionesCS;
}


function fnObtenerRequis(){
/*
 var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Desea solicitar la cotización de los requisiciones seleccionada?', '', 'fnObtenerRequis()');
*/
muestraCargandoGeneral();
 requis= fnChecarSeleccionados('requi');
 /*for(a=0;a<requis.length; a++){
 console.log(requis[a]);
 } */

    dataObj = { 
          proceso: 'enviarCotizacionV2',
          requis:requis
        };
  
  $.ajax({
        method: "POST",
        dataType:"json",
        async:false,
        url: 'includes/Subir_Archivos.php',//modelo,
        data:dataObj
    })
  .done(function( data ) {
   
      if(data.result){
      ocultaCargandoGeneral();
         muestraModalGeneral(4, titulo,"Solicitud de cotización hecha.");

      }else{
        ocultaCargandoGeneral();
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      ocultaCargandoGeneral();
  });

  /*provSug=[];
   $("input:checkbox[class=provedoresPart]:checked").each(function () {
            id= $(this).val();
            prove=  $(this).attr('data-prove');
            provSug.push(prove);
           
        }); */
}

function  fnExisteEnProcesoCompra(requis){


}


function fnFijarFecha(){
    dataObj = { 
            proceso: 'getFechaServidor',
          };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
            info=data.contenido.Fecha;
            $("#dateDesde").val(""+info[0].fechaDMY); 
            $("#dateHasta").val(""+info[0].fechaDMY); 
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

$('#btnregresar').click(function() {
  window.open("PO_SelectOSPurchOrder.php", "_self");
});



 function fnDatosArchivosContrato(id){
    //$(document).on('click','.datosArchivos',function(){
    $(document).on('change','#cargarMultiples'+id,function(){
      
    //$('#cargarMultiples').change(function(){
        archivosNopermitidos= new Array();
        archivosNopermitidos=[];
        archivosTotales=0;
        var agregarFilas='<table class="table table-striped border" id="tablaDetallesArchivos" style="border:solid 1px #eee;"><thead class="bgc8" style="color:#fff;"><th>Nombre</th><th>Tamaño</th> <th>Tipo Archivo </th> <th> </th> </thead><tbody>';
        var filasArchivos='';
        for(var ad=0; ad< this.files.length; ad++){
            var file = this.files[ad];
            nombre = file.name;
            tamanio = file.size;
            tipo = file.type;
            filasArchivos+='<tr class="filasArchivos"> <td>'+ nombre+'</td> <td> <b>Tamaño:</b>'+ tamanio+'</td> <td> <b>Tipo:</b> '+ tipo+'</td> <td class="text-center"> <span class="quitarArchivos"><input type="hidden" name="nombrearchivoCLS" value="'+nombre+'" >    <span  class="btn bgc8" style="color:#fff;">    <span class="glyphicon glyphicon-remove"></span></sapn> </span> </td></tr> ';
            archivosTotales++;
        }
        agregarFilas+=filasArchivos;
        agregarFilas+='   </tbody></table>';
        $('#muestraAntesdeEnviar'+id).empty();
        $('#muestraAntesdeEnviar'+id).append(agregarFilas);
        $('#enviarArchivosMultiples'+id).show();
    });

   // fnDatosArchivosMultiples
}

$(document).on('click','.sendFilesCl',function(){
muestraCargandoGeneral();
atributo=  $(this).attr('id');
id= atributo.replace("enviarArchivosMultiples","");
nombreArchivo='';  

/*$('#cargarMultiples'+id).each(function() {
    nombreArchivo=$(this).val();
});*/
nombreArchivo=$("#cargarMultiples"+id).eq(0).val();
nombreArchivo=nombreArchivo.replace(/\\/g, '|');
nombrearchivo=nombreArchivo.replace("C:|fakepath|",'');

        var v=$("#cargarMultiples"+id).val();
        if(v!=''){
            //e.preventDefault();
            var form_data = new FormData();
            var ins = document.getElementById('cargarMultiples'+id).files.length;
            for (var x = 0; x < ins; x++) {
                form_data.append("archivos[]", document.getElementById('cargarMultiples'+id).files[x]);
                //nombreArchivo=$('cargarMultiples'+id).val();
            }

            form_data.append('nopermitidos',archivosNopermitidos);
            form_data.append('funcion',$("#funcionArchivos"+id).val());
            form_data.append('tipo',$("#tipoArchivo"+id).val());
            form_data.append('trans',$("#transnoArchivo"+id).val());
            form_data.append('esmultiple',$("#esMultiple"+id).val());

            var tipo =$("#tipoArchivo"+id).val();
            console.log("tipoArchivos: "+tipo);
            $.ajax({
                url: "includes/Subir_Archivos.php",
                dataType: 'json', //retorno servidor  recuerda que es json
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
            })
                .done(function(res){
                    if(res){
                        contadorArchivoSubir =0;
                        $('#cargarMultiples'+id).val('');
                        $("#muestraAntesdeEnviar"+id).empty();
                        datos =res.contenido;
                       
                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                        muestraModalGeneral(4, titulo,datos);
                        cargarContrato($("#transnoArchivo"+id).val());
                        $('#enviarArchivosMultiples'+id).hide();
                        $('#cuadroDialogoCarga'+id).remove();
                        
                        $('#yaCargado'+id).append('<span><b>Archivo subido:</b></span> <input type="text" id=yaCargadoIn"'+id+'"  value="'+nombreArchivo+'"  maxlength="50" onpaste="return false" class="text-left form-control" disabled>');

                        //fnDatosDeArchivosSubidos();
                        $('#yaCargadoContrato'+id).hide();
                        $("#tablaTipos").fadeIn( "slow");
                        $("#botones").show();
                        $("#datosRequi").empty();
                        $("#provSug").empty();
                        $("#divCargarCotizacion").hide();
                        $("#divCargarContrato").hide();


                        $("#datosCuadroComparativo").empty();

                        ocultaCargandoGeneral();
                    }else{
                        ocultaCargandoGeneral();
                    }
                })
                  .fail(function(res) {
            console.log("ERROR");
            console.log( res);
            ocultaCargandoGeneral();
                });

        }else{
            ocultaCargandoGeneral();
           // muestraMensaje("Seleccione algún archivo.",3, 'mensajeArchivos', 5000);
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,"Seleccione algún archivo.");
        }

});


function fnCrearTemplateArchivos(id,requi){
//-2424 contrato
       componenteHtml='<div class="cargarArchivosTemplate'+id+'"">'+
    '<input id="#esMultiple'+id+'" name="esMultipleCLS" value="0" type="hidden">'+
    
    '<input id="funcionArchivos'+id+'" name="funcionArchivos" value="2340" type="hidden">'+
    '<input id="tipoArchivo'+id+'"     name="tipoArchivo" value="2340" type="hidden">'+
    '<input id="transnoArchivo'+id+'"  name="transnoArchivo" value="'+requi+'" type="hidden">'+
    '<div id="mensajeArchivos">'+
    '</div>'+
    '<div id="subirArchivos" class="col-md-12">'+
        '<div class="col-md-12" style="color: rgb(255, 255, 255) !important;">'+
            '<div class="col-md-6">'+'<div id="inputFile'+id+'">'+

            '</div>'+
                '<div id="yaCargado'+id+'"> </div>'+
                '<button id="cuadroDialogoCarga'+id+'" class="cargarArchivoBtn btn bgc8" type="button">'+'<span class="glyphicon glyphicon-file">'+

      '</span>'+                    "Cargar archivo(s)"+                '</button> '+
      '<div class="btn bgc8 cerrarDetalle" style="color:white;">'+
    ' <span class="glyphicon glyphicon-remove" ></span> Atras </div>'+
                '<br>'+ '<br>'+
                '<button id="enviarArchivosMultiples'+id+'" class="sendFilesCl btn bgc8" style="display: none;">'+
                    "Subir"+
                '</button>'+ '<br>'+ '<br> '+
            '</div>'+
            '<br>'+'</div>'+ '<div id="muestraAntesdeEnviar'+id+'" class="col-md-12 col-xs-12">'+

    '</div>'+
        '<br>'+ '<br>'+
    '</div>'+
    '<div id="enlaceDescarga" class="col-md-12 col-xs-12">'+

    '</div>'+
    '<div id="accionesArchivos" style="color: rgb(255, 255, 255) !important; display: none;">'+
        '<div class="col-md-3">'+'<button id="eliminarMultiples" onclick="fnBorrarConfirmaArch()" class="btn bgc8">'+"Eliminar"+'</button>'+
            '<br>'+
        '</div>'+
        '<div class="col-md-3">'+
            '<button id="descargarMultiples" onclick="fnProcesosArchivosSubidos('+"descargar"+')" class="btn bgc8">'+"Descargar"+'</button>'+
            '<br>'+'</div>'+
    '</div>'+

    '<div class="modal fade" id="ModalBorrarArchivos"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'+
        '<div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">'+
            '<div class="modal-content">'+
                '<div class="navbar navbar-inverse navbar-static-top">'+
                    '<div class="col-md-lg menu-usuario">'+
                        '<span class="glyphicon glyphicon-remove" data-dismiss="modal">'+'</span>'+
                    '</div>'+
                    '<div id="navbar" class="navbar-collapse collapse">'+
                        '<div class="nav navbar-nav">'+
                            '<div class="title-header">'+
                                '<div id="ModalBorrarArchivos_Titulo" >'+'</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="linea-verde">'+'</div>'+
                '</div>'+
                '<div class="modal-body" id="ModalBorrarArchivos_Mensaje">'+
                    '<div class="col-md-9" id="listaBorrarArchivos" >'+
                        '<h3>'+"¿Desea borrar los archivos seleccionados?"+'</h3>'+
                    '</div>'+
                '</div>'+
                '<br>'+ '<br>'+ '<br>'+
                '<div class="modal-footer">'+
                    '<div class="col-xs-6 col-md-6 text-right">'+
                        '<div id="procesandoPagoEspere">'+ '</div>'+ '<br>'+


                        '<button id="btnConfirmarEliminar" name="btnConfirmarEliminar" type="button" title="" class="btn btn-default botonVerde"  onclick="fnProcesosArchivosSubidos('+"eliminar"+')" >'+
                            +"Eliminar"+
                        '</button>'+

                        '<button id="btnCerrarConfirma" name="ElementoDefault" type="button" title="" onclick="" class="btn btn-default botonVerde" data-dismiss="modal" style="font-weight: bold;">'+"&nbsp;Cancelar"+'</button>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>'+
    '</div>'+
'</div>';

return componenteHtml;
}

function  fnCargarArchivosTemplate(){
 // fnCargarArchivos();
  $(document).on('click','.cargarArchivoBtn',function(){
  id=  $(this).attr('id');
  id1= id.replace("cuadroDialogoCarga","");
  //id1=$("#inputFile"+id1).attr('id');

   
    $("#inputFile"+id1).empty();
    var m=$("#esMultiple"+id1).val();
    if(m!=0){
    $("#inputFile"+  id1).append(' <input type="file"  class="btn bgc8"  name="archivos[]"   id="cargarMultiples'+id1+'"  multiple="multiple" style="display: none;" />');
    }else{
        $("#inputFile"+id1).append(' <input type="file"  class="btn bgc8"  name="archivos[]"  id="cargarMultiples'+id1+'" style="display: none;" />');
    }

    $("#cargarMultiples"+id1).click();

    fnDatosArchivosContrato(id1); 
    //fnSubirArchivos(id1);


  });
}

function cargarContrato(requi){

   dataObj = { 
            proceso: 'cargaContrato',
            requi:requi
          };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: "modelo/procesoCompraModeloV2.php",
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
              setTimeout(function(){ fnListaProvedoresSugeridos(); }, 1);
        
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
  //cargaContrato
}

$(document).on('click','#btnCerrarModalGeneral',function(){
    ocultaCargandoGeneral();
   $('div').removeClass("modal-backdrop");
   if (document.getElementById("ModalSpinerGeneral")){
   // document.getElementById("ModalSpinerGeneral").remove();
   $('#ModalSpinerGeneral').modal('hide');
    }
});