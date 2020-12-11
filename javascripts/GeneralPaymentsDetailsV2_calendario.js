/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */


var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Facturas del día</h3>';
$( document ).ready(function(){
  //ordenar elementos 
/*
$(".selectMeses1").html($(".selectMeses1 option").sort(function (a, b) {
          return a.text == b.text ? 0 : a.text < b.text ? -1 : 1;
        }));
  */
 $("#pagosnuevosDetectados").click(function(){
         var mes=$( ".selectMeses1 option:selected" ).val(); 
         var anio=$( ".selectAnio  option:selected" ).val(); 
        fnCalendario(mes,anio);
        console.log("refresca");
    });

var meses={'01':'Enero','02':'Febrero','03':'Marzo',
           '04':'Abril','05':'Mayo','06':'Junio',
           '07':'Julio','08':'Agosto','09':'Septiembre',
           '10':'Octubre','11':'Noviembre','12':'Diciembre' };
  //$('.selectMeses1').append(contenido);
var arreglomeses=[];
for (var i in meses){
        if (meses.hasOwnProperty(i)) {
           arreglomeses.push({ label:meses[i], title: meses[i], value:i });
                    
        }
}
arreglomeses.sort(function (a, b) {
          var aValue = parseInt(a.value);
          var bValue = parseInt(b.value);
          // ASC
          return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
          // DESC
          //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
});        
        
       
        

/*$(".selectMeses1").html($(".selectMeses1 option").sort(function (a, b) {
          var aValue = parseInt(a.value);
          var bValue = parseInt(b.value);
          // ASC
          return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
          // DESC
          //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
        })); */
  //$('.selectMeses1').append(contenido);

var datos;
var totalDeDiasMes;
var nombreDiaInicioMes;
var mesAnio=fnObtenerMes();
var mesActual=mesAnio[0];
var anioActual=mesAnio[1];
//alert(mesActual);
//mesActual=mesActual.substr(1);//quito el cero 
//$(".selectMeses1").val(mesActual);

$("#selectMeses1").multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            $('#selectMeses1').multiselect('dataprovider', arreglomeses);
            $('#selectMeses1').selectpicker('val', mesActual);
            $('#selectMeses1').multiselect('refresh');
          
            $('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });
            $('.selectMeses1').css("display", "none");
 // var contenido = "";
 //    if (valorInicial == 1) {
 //        var contenido = "<option value='0'>Seleccionar...</option>";
 //    }
 //    for (var info in arreglomeses) {
 //        // var selected = "";
 //        // if (dataJson[info].value == valor) {
 //        //     selected = "selected";
 //        // }
 //        contenido += "<option value='" + arreglomeses[info].value + "' " + selected + ">" + arreglomeses[info].texto + "</option>";
 //    }
 //    // if (elementoClase == "") {
 //    //     return contenido;
 //    // } else {
 //        // Si trae nombre para los datos
 //        $("#selectMeses1").empty();
 //        $("#selectMeses1").append(contenido);
 //        $("#selectMeses1").multiselect('rebuild');
 //        $("#selectMeses1").multiselect({
 //             enableFiltering: true,
 //             filterBehavior: 'text',
 //             enableCaseInsensitiveFiltering: true,
 //             buttonWidth: '100%',
 //             numberDisplayed: 1,
 //             includeSelectAllOption: true
 //          });
    //}
fnCalendario(mesActual);


$(document).on('click','.eventoPagoFecha',function(e){
  e.stopPropagation();//detiene el evento del padre
  //if (e.target !== this){ //evita que la clase padre de dispare
  var dato=$(this).text();
  fnDatosDetalles(dato);
  //return;
  //}
});

 //$( ".eventoPagoFecha").draggable();
$('.selectMeses1').change(function(){
var mes=$( ".selectMeses1 option:selected" ).val(); 
var anio=$( ".selectAnio  option:selected" ).val(); 

fnCalendario(mes,anio);       
});
$('.selectAnio').change(function(){
var mes=$( ".selectMeses1 option:selected" ).val(); 
var anio=$( ".selectAnio  option:selected" ).val(); 

fnCalendario(mes,anio);       
});
$("#calendarioPestana").click(

/*
function(){    

$(".selectMeses1").html($(".selectMeses1 option").sort(function (a, b) {
          var aValue = parseInt(a.value);
          var bValue = parseInt(b.value);
          // ASC
          return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
          // DESC
          //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
        }));

$(".selectMeses1").val(mesActual);
fnCalendario(mesActual);
$('.multiselect dropdown-toggle btn btn-default').prop('title', mesActual);
  } */

);

function fnObtenerMes(){
var datos=[];
dataObj ={
proceso: 'getMesServidor',
};  
$.ajax({
method: "POST",
dataType:"json",
async:false,
cache:false,
url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
data:dataObj
})
.done(function( data ) {    
if(data.result){
        mes=data.contenido.MesActual;
        anio=data.contenido.anioActual;
        datos.push(mes);
        datos.push(anio);
      }
})
.fail(function(result) {
console.log("ERROR");
console.log( result );
});

return datos;
}

function fnCalendario(mes,anio){
muestraCargandoGeneral();  
$("#calendarioAbajo").empty();
datos=fnDatosConstruirCalendario(mes,anio);
totalDeDiasMes=datos[1];
nombreDiaInicioMes=datos[0];

fnCrearCalendario('calendarioAbajo');

var inhabilitardias=fnDiasFeriados(mes,anio);
fnDeshabilitarDias(inhabilitardias,nombreDiaInicioMes);

fnObtenerFechasPagos();
ocultaCargandoGeneral();
}

function fnDiasFeriados(mes,anio){
var  diasFeriados = new Array();  
dataObj ={ 
proceso: 'diasFeriadosDelMes',
mes:mes,
anio:anio
};
$.ajax({
  method: "POST",
  dataType:"json",
  url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
  async:false,
  cache:false,
  data:dataObj
})
.done(function( data ){
if(data.result){
  diasFeriados=data.contenido.DiasFeriadosDelMes; 
          
}
})
.fail(function(result) {
 console.log("ERROR");
console.log( result );
});
return diasFeriados;
}

//obtiene en que dia inicio el mes y cuantos dias tiene el mes
function fnDatosConstruirCalendario(mes,anio=0){
var  datos = new Array();  
dataObj = { 
proceso: 'datosConstruirCalendario',
mes:mes,
anio:anio
};
$.ajax({
  method: "POST",
  dataType:"json",
  url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
  async:false,
  data:dataObj
})
.done(function( data ){
  if(data.result){
    info=data.contenido.construirCalendario;
    datos.push(info[0].nombreDiaInicioMes); 
    datos.push(info[0].totalDeDiasMes); 
      }
})
.fail(function(result) {
 console.log("ERROR");
 console.log( result );
  });
return datos;
}

function fnObtenerFechasPagos(){
var datosCalendario="";
var mes=$( ".selectMeses1 option:selected" ).val();
var anio=$( ".selectAnio  option:selected" ).val(); 
dataObj = { 
proceso: 'calendario',
mes:mes,
anio:anio
};  
$.ajax({
  method: "POST",
  dataType:"json",
  url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
  async: false,
  data:dataObj
})
.done(function( data ){
if(data.result){
  datosCalendario=data.contenido.DatosCalendario; 
  //alert(datosCalendario);
  fnCrearEventos1(datosCalendario);
}
})
.fail(function(result) {
  console.log("ERROR");
  console.log( result );
  });
}
function fnCrearCalendario(id){
  $('#'+id).append('<br><br><table class="table table-bordered" id="tablaCalendario" > <thead id="encabezadoTablaCalendario" > </thead> <tbody id="tabla"> </tbody></table>');
  fnEncabezado();
  fnTabla();
  fnCrearDias(nombreDiaInicioMes);
  fnDeshabilitarFines();
}
function fnEncabezado(){
var dias=['Dom','Lun','Mar','Mie','Jue','Vie','Sab'];
var encabezado;
for (i in dias){
      encabezado+='<th class="col-md-1"><h4>'+dias[i]+'</h4></th>';
}
$("#encabezadoTablaCalendario").append(encabezado);
}

function fnTabla(){
var tabla;
var dias=0;

for (f=0;f<6;f++){
  tabla+='<tr >';
  for (c=0;c<7;c++){
  tabla+='<td class="diaDetalle" id="dias'+dias+'"> <div class="seccionArribaCalendario" id="'+(dias+1)+'"></div> <div class="seccionEnMedioCalendario"> </div> <div class="seccionInferiorCalendario"></div>  </td>';
  dias++;
  }
  tabla+='</tr>';
}

$("#tabla").append(tabla);
}
  
function deDondeComenzaraPintar(dia){
var inicioCalendario;
 switch(dia){ 
  case 'Sun':
  inicioCalendario=0;
  break;
  case 'Mon':
  inicioCalendario=1;
  break;
  case 'Tue':
  inicioCalendario=2;
  break;
  case 'Wed':
  inicioCalendario=3;
  break;
  case 'Thu':
  inicioCalendario=4;
  break;
  case 'Fri':
  inicioCalendario=5;
  break;
  case 'Sat':
  inicioCalendario=6;
  break;
  default:

}
return inicioCalendario;
}

function fnSemaforo(diasRestantes){
//tiene que ser en un array de los valores  y de ahi poner el mas alto en la primera posicion
  var valores =[2,1,0];
  var contador=0;
  var css;

  for(i=0;i<valores.length;i++){
    if(diasRestantes>valores[i]){
      contador++;
      }
  }

if(contador>=valores.length){
css='#bbf0a7';     
}

if(contador==2){
  css='yellow';
}

if(contador<2 || contador<=0 ){
css='red';
}

return css;

}
  


function fnCalcularIntervaloSemaforo(diaPago){
    var diasFalantes=0;
    dataObj = { 
          proceso: 'getFechaServidor',
   
        };
  
  $.ajax({
        method: "POST",
        dataType:"json",
        async:false,
        url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
        data:dataObj
    })
  .done(function( data ) {
    
      if(data.result){
        info=data.contenido.Fecha;
       
      /*var diaServidor= info[0].fechaDMY.substring(0,2);
      diaServidor= parseInt(diaServidor);
      diasFalantes= diaPago-diaServidor; */

      var fechaActual= info[0].fechaMdy;
      
      diaPago=diaPago.replace(/-/g,"/");
      var fecha1 = new Date(diaPago);
      fechaActual= fechaActual.replace(/-/g,"/");
      var fecha2 = new Date(fechaActual);
      
      //var timeDiff = Math.abs(fecha2.getTime() - fecha1.getTime());
      var timeDiff = (fecha1.getTime()-fecha2.getTime());
      

      var difDias = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
     // alert(difDias);
     diasFalantes=difDias;
        
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
  });

  return diasFalantes;

}
function fnCrearDias(inicioMes){
var comienzo= deDondeComenzaraPintar(inicioMes); 
for(f=0;f<totalDeDiasMes;f++){
  $('#dias'+(comienzo+f).toString()+' > .seccionArribaCalendario').append("<input type='hidden' value='"+(f+1)+" ' class='diaoriginal'> "+(f+1)+' <i class="fa fa-calendar" aria-hidden="true"></i>'+'<span class="glyphicon glyphicon-chevron-down"></span>');
}
}

function fnCrearEventos(dias,evento,inicioMes){ 
var comienzo= deDondeComenzaraPintar(inicioMes); 
for(f=0;f<=totalDeDiasMes;f++){
  for (i in dias){
    if (f==dias[i]){ 
     $('#dias'+(comienzo+(f-1)).toString()+' > .seccionEnMedioCalendario').append(evento);
    }
      
  }
}
}
function fnDeshabilitarDias(dias,inicioMes){ 
if(dias){
 var comienzo= deDondeComenzaraPintar(inicioMes); 
  for(f=0;f<=totalDeDiasMes;f++){
   for (i in dias){
    if (f==dias[i]){ 
    //$('#dias'+(comienzo+(f)).toString()).css("color:red;");
    $('#dias'+(comienzo+(f-1)).toString()).css({color: "red"});
    }      
   }
  }
}
}

function fnDeshabilitarFines(){ 
var dias=[0,6,7,13,14,20,21,27,28,34,35,41]; 
for(f=0;f<42;f++){
  for (i in dias){
    if (f==dias[i]){ 
     $('#dias'+f).css({color: "red"});
      }
    }
}

}

function fnCrearEventos1(datos){
var comienzo= deDondeComenzaraPintar(nombreDiaInicioMes); 
for(f=0;f<=totalDeDiasMes;f++){
  for (key in datos){
   if (f==datos[key].dia){ 
     var titulo=(datos[key].titulo).substring(0, 10)+'...'; // corto  leyenda dejo 10 primeras letras
     $('#dias'+(comienzo+(f-1)).toString()+' > .seccionEnMedioCalendario').append('<br><div class="eventoPagoFecha"><span class="glyphicon glyphicon-plus"></span> '+titulo+'</div>');

    
     $('#dias'+(comienzo+(f-1)).toString()+' > .seccionEnMedioCalendario').append("<br>");

     if ($('#dias'+(comienzo+(f-1)).toString()+' > .seccionInferiorCalendario').text().length > 0) {

      // para evitar  pintar de nuevo la leyenda

     }else{
       var diasFalantes= fnCalcularIntervaloSemaforo(datos[key].fechaTran);
      
        switch(true){

          case (diasFalantes==0):
               $('#dias'+(comienzo+(f-1)).toString()+' > .seccionInferiorCalendario').append(" <br><span class='glyphicon glyphicon-triangle-right'></span> Hoy es el d&iacute;a de pago. <br>");
          break;

          case (diasFalantes>0):
             $('#dias'+(comienzo+(f-1)).toString()+' > .seccionInferiorCalendario').append(" <br><span class='glyphicon glyphicon-triangle-right'></span> Faltan "+diasFalantes+ " d&iacute;a(s) para pago.<br>");
          break;

          case (diasFalantes<0):
          //$('#dias'+(comienzo+(f-1))).css({"background":"repeating-linear-gradient( -45deg, #ddd, #ddd 10px, #333 10px, #ddd 20px)"});
         // $('#dias'+(comienzo+(f-1))).css({"background":});
          
              $('#dias'+(comienzo+(f-1)).toString()+' > .seccionInferiorCalendario').append(" <br><span class='glyphicon glyphicon-triangle-right'></span> Retraso de "+(diasFalantes*(-1))+ " d&iacute;a(s) para pago.<br>");
          break;

        }

          css= fnSemaforo(diasFalantes);
        if(diasFalantes>=0){
            $('#dias'+(comienzo+(f-1)).toString()+' > .seccionEnMedioCalendario').css({ "border": "2px solid #a1a1a1", "border-radius": "5px 20px 5px","background-color":css});
            //$('#dias'+(comienzo+(f-1)).toString()+' > .seccionEnMedioCalendario').on('click',".seccionEnMedioCalendario",function(){ $(this).draggable();});

        }else{
         $('#dias'+(comienzo+(f-1))).css({"background":""});
          $('#dias'+(comienzo+(f-1)).toString()+' > .seccionEnMedioCalendario').css({"border": "2px solid #a1a1a1", "border-radius": "5px 20px 5px","background-color":"#ddd"});
        }
        
     }
   
   }//si es el mismo dia
      
  } 
} //fin for
$("#pagosnuevosDetectados").fadeOut("slow");
}// fin funcion

}); // fin ready
function fnDatosDetalles(dato){
  dataObj = { 
  proceso: 'detallesPagoProgramado',
  titulo:dato
  };
  muestraCargandoGeneral();
  $.ajax({
    method: "POST",
    dataType:"json",
    url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
    data:dataObj
  })
  .done(function( data ){
  if(data.result){
  info=data.contenido.DetallesPago;

  html='<component-text-label label="Operación: " id="txtTipoOperacion" name="txtTipoOperacion" placeholder="Operación" value="'+info[0].tipoOperacion+'" disabled="true"></component-text-label>'+
  '<br>'+
  '<component-text-label label="UR: " id="txtURG" name="txtURG" placeholder="UR" maxlength="12" value="'+info[0].urg+'" disabled="true"></component-text-label>'+
  '<br>'+
  '<component-text-label label="UE: " id="txtUE" name="txtURG" placeholder="UE" maxlength="12" value="'+info[0].ue+'" disabled="true"></component-text-label>'+
  '<br>'+
  '<component-textarea-label label="Observaciones: " id="txtObservaciones" name="txtObservaciones" placeholder="Observaciones" value="'+info[0].obs+'" disabled="true" rows="3"></component-textarea-label >'+
  '<br>'+   
  '<div class="col-xs-12 col-md-4"> <component-text-label label="Factura: " id="txtReferencia" name="txtReferencia" placeholder="Factura" value="'+info[0].reference+'" disabled="true"></component-text-label></div>'+
  '<div class="col-xs-12 col-md-4"><component-text-label label="Proveedor: " id="txtProveedor" name="txtProveedor" placeholder="Proveedor" value="'+info[0].proveedor+'" disabled="true"></component-text-label></div>'+
  '<div class="col-xs-12 col-md-4"><component-text-label label="Fecha: " id="txtProveedor" name="txtProveedor" placeholder="Proveedor" value="'+info[0].fecha+'" disabled="true"></component-text-label></div>'+

  '<br>'+    '<br>'+   '<br>'+    '<br>'+  
  '<div class="col-xs-12 col-md-4"><component-text-label label="Monto: " id="txtMonto" name="txtMonto" placeholder="Monto" value="$ '+info[0].monto+'" disabled="true"></component-text-label></div>'+
  '<div class="col-xs-12 col-md-4"><component-text-label label="IVA: " id="txtIva" name="txtIva" placeholder="IVA" value="$ '+info[0].iva+'" disabled="true"></component-text-label></div>'+
  '<div class="col-xs-12 col-md-4"><component-text-label label="Total: " id="txtTotal" name="txtTotal" placeholder="Total" value="$ '+info[0].total+'" disabled="true"></component-text-label></div>'+
  '<br><br>';

  muestraModalGeneral(4,titulo,html);
  fnEjecutarVueGeneral('ModalGeneral_Mensaje');
  $('#txtObservaciones').prop("disabled", true);
  ocultaCargandoGeneral();
  }else{
    ocultaCargandoGeneral();
  }
  })
  .fail(function(result) {
    console.log("ERROR");
    console.log( result );
  });

  var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información del Pago</h3>';
  $('#ModalInfoCalendario_Titulo').empty();
  $('#ModalInfoCalendario_Titulo').append(titulo);
  $('#ModalInfoCalendario').modal('show');
}


//$(document).on('click','.diaDetalle',function(){
$(document).on('click','.seccionArribaCalendario',function(){


dia=$(this).find('.diaoriginal').val(); //dia detalle
//alert(dia);
//dia=$(this).attr('id');
/*
dia=dia.replace('dias',"");
dia= parseInt(dia) +1; */

if(dia){
  //if($(this).find('.eventoPagoFecha').length>0){ //diadetalle
  if($(this).parent().find('.seccionEnMedioCalendario > .eventoPagoFecha').length>0){
  $("#btnGenerarCLC").show();
  $("#mensajesValidaciones").empty();

  fnFacturasDias(dia);
  }else{

  $("#mensajesValidaciones").empty();
  $('#ModalInfoDia_Titulo').empty();
  $("#btnGenerarCLC").hide();
  
  fnLimpiarTabla('divTablaDia', 'divDatosDia');
  var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Facturas del día</h3>';
  //$('#ModalInfoDia_Titulo').append(titulo);
  //muestraMensaje('No hay facturas que mostrar',3, 'mensajesValidaciones', 9000);
  //$("#mensajesValidaciones").append('<div class="col-xs-12 col-md-12 btn" style="background-color:#f2dede !important;color:#a94442;"><h4>No hay facturas que mostrar.</h4></div>');
  //$('#ModalInfoDia').modal('show');
  muestraModalGeneral(4,titulo,'<div class="col-xs-12 col-md-12 btn" style="background-color:#f2dede !important;color:#a94442;"><h4>No hay facturas que mostrar.</h4></div>');
  }
} //fin dia

});
/*
$(document).on('click','.seccionEnMedioCalendario',function(){
$(this).draggable();

}); */

function fnFacturasDias(dia){
  $("#ligaCLC").empty();
$("#ligaCLC").fadeOut();

 var d = new Date();
 var n = d.getMonth();
 n+=1;
 for(i=1;i<=9;i++){
 if(n==i){
  n="0"+i;
 }
}

//var mes=n;
var mes= $( ".selectMeses1 option:selected" ).val(); 
var anio=new Date().getFullYear();  
dia=anio+'-'+mes+'-'+dia;
var fecha = dia;

/*
dataObj = { 
  proceso: 'facturasDias',
  fecha:fecha 
}; */
dataObj = { 
  proceso: 'buscarDatos',
   dateDesde:fecha ,
   dateHasta:fecha 
};
 //proceso: 'buscarDatos',
         
muestraCargandoGeneral();
$.ajax({
  method: "POST",
  dataType:"json",
  url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
  data:dataObj
})
.done(function( data ) {    
if(data.result){
 // info= data.contenido.DatosDiasFactura;
if (data){
 
  html='<div name="divTablaDia" id="divTablaDia">'+        
                    '<div name="divDatosDia" id="divDatosDia"></div>'+
                '</div>';
        muestraModalGeneral(4,titulo,html);
        fnLimpiarTabla('divTablaDia', 'divDatosDia');
        fnAgregarGridv2(info,'divDatosDia','b');
        dataFuncionJason = data.contenido.DatosPagos;
        columnasNombres = data.contenido.columnasNombres;
        columnasNombresGrid = data.contenido.columnasNombresGrid;
        var columnasDescartarExportar = [0];
        /*fnLimpiarTabla('divTabla', 'divDatos');
        fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divDatos', ' ', 1, columnasDescartarExportar, false);
          */
        var columnasDescartarExportar = [];
        var columnasExcel=    [3,4,7,9,11,12,13,14,17,18]; //5 ue
        var columnasVisuales= [0,3,4,6,8,10,12,13,14,,17,18]; //5 ue
        nombreExcel=data.contenido.nombreExcel;
        fnLimpiarTabla('divTablaDia', 'divDatosDia');
        fnAgregarGrid_Detalle(dataFuncionJason, data.contenido.columnasNombres, data.contenido.columnasNombresGrid, 'divDatosDia', ' ', 1, columnasExcel, false,true, '', columnasVisuales, nombreExcel);
 
        $('#divTablaDia > #divDatosDia').jqxGrid({columnsheight:'35px'});
}else{
 // alert("No hay facturas.");
}
ocultaCargandoGeneral();
  var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Facturas del día</h3>';
  $('#ModalInfoDia_Titulo').empty();
  $('#ModalInfoDia_Titulo').append(titulo);
  $('#ModalInfoDia').modal('show');
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
function fnGeneraCLC(){
//$("#ModalInfoDia").modal('hide'); 
liga= fnGenerarArchivoLayoutSinModal(244,20,(transno=[8]),1,'CLCs','CLC_240_20_8');
$("#ligaCLC").empty();
$("#ligaCLC").append(liga);

//$("#ModalInfoDia").modal('show');
$("#ligaCLC").fadeIn(1200);


}

$(document).on('cellbeginedit', '#divTablaDia > #divDatosDia', function(event) {
   // $(this).jqxGrid('setcolumnproperty', 'checkPagos', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'id', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'un', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'requi', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'estado', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tipoPago', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fo2', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fact', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'prov', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ova', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ovg', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ovat', 'editable', false);
   // $(this).jqxGrid('setcolumnproperty', 'imprimir', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'obs', 'editable', false);
});

$(document).ready(function(){
    fnActivarDia();
});

function fnActivarDia(){
//   $('.diaDetalle').click(function(){
//     if($(this).hasClass("selecDiasCalendario")){
//   // if( if($(this).parent().find('.seccionEnMedioCalendario > .eventoPagoFecha').length>0) ){
// //
//    //if( $(this).parent().find(".selecDiasCalendario > .seccionArribaCalendario > .diaoriginal").length>0  ){

//     //$(this).find('.seccionEnMedioCalendario').css({"background-color":"#000 !important"});

//       $(this).removeClass( "selecDiasCalendario" );
//    }else {
//       $(this).addClass("selecDiasCalendario");

//      // $(this).find('.seccionEnMedioCalendario').css({"background-color":"#1B8810 !important"});
//    }
   
    //}
    
  //});
}
