/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */


var modelo="modelo/configuracionProcesoCompraModelo.php";
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
     
function fnObtenerDatosComponentesFormulario(nombreformulario,proceso){

  var formData = new FormData(document.getElementById(nombreformulario));
  formData.append("proceso",proceso);
  return formData;
}
function  checarSiexisteTipoAdjudicacion(){
  
}

function fnAltaAdjudicacion(formulario){
  var contador=0; 
  var rangoinicial=parseInt( $('#rangoinicial').val()), rangotope=parseInt( $('#rangotope').val());
     $("#"+formulario+" :input").each(function(){
            datos=$(this).val();
            (datos=="") ? contador++ :''; 

          });

if(contador==0 && (rangoinicial<rangotope) ){

      //muestraModalGeneral();
      muestraCargandoGeneral();
    $.ajax({
      url: modelo,
      type: "post",
      dataType: "json",
      data: fnObtenerDatosComponentesFormulario(formulario,"altaTipoAdju"),
      cache: false,
      contentType: false,
      processData: false
  
  })
  .done(function(data) {

      if (data.result) {
        ocultaCargandoGeneral();
        info= data.contenido;
        muestraModalGeneral(4, titulo, '<div >' +info+ '</div>');
        // $("#typead").val($("#idadjudicacion").val());
        // $("#camposFormuAdjudicacion").removeAttr("style");
        // $("#datosFormuNuevaAdj").hide();
        $("#titulotipoAdju").append(" <h3>"+($("#descripcionadjudicacion").val())+"</h3>");
        //$( "#book" ).hide( "slow", function() 
          fnChecarTiposAdjudicacion();
          $("#datosFormuNuevaAdj").fadeOut("slow");

          $("#tablaTipos").fadeIn("slow");
        $('#nuevoTipoAdjudicacion').show();
      } else {
          ocultaCargandoGeneral();
      }

  })
  .fail(function(result) {
      console.log("ERROR");
      console.log(result);
      ocultaCargandoGeneral();

  });
}else{
  var mensaje='';
  (rangoinicial>=rangotope) ?   mensaje='El rango inicial debe ser menor que el tope .' :   mensaje='Hay campos vacios.';
    muestraModalGeneral(4, titulo,mensaje);
    contador=0;
}
//}// fin datos  verificar
}


function fnAltaCampo(formulario){
     // muestraModalGeneral();
    $.ajax({
      url: modelo,
      type: "post",
      dataType: "json",
      data: fnObtenerDatosComponentesFormulario(formulario,"altaCampo"),
      cache: false,
      contentType: false,
      processData: false
  
  })
  .done(function(data) {

      if (data.result) {
        ocultaCargandoGeneral();
        info= data.contenido;
 
      } else {
          ocultaCargandoGeneral();
      }

  })
  .fail(function(result) {
      console.log("ERROR");
      console.log(result);
      ocultaCargandoGeneral();

  });

//}// fin datos  verificar
}
$(document).ready(function(){
  $('#leercotizacion').click(function(){
  });
  
  $('#alta').click(function(){
    $(this).prop("disabled", true);
    $(this).hide();
    fnAltaAdjudicacion('adjudicacionFormu');
    $(this).show();
    $(this).prop("disabled", false);
  });
  $("#atras").click(function(){
    $("#tablaTipos").fadeIn("slow");
    $('#nuevoTipoAdjudicacion').show();
    $('#datosFormuNuevaAdj').fadeOut("slow");
  });

  $("#addCampo").click(function(){
    //fnAltaCampo("addCampoFormu");
  });
  $('#nuevoTipoAdjudicacion11').click(function(){

    html='<div id="datosFormuNuevaAdj">'+
    '<h4>Crear nueva adjudicación</h4>'+
    '<form method="post" action="" name="adjudicacionFormu" id="adjudicacionFormu">'+

        '<component-text-label label="Descripcion de tipo de adjudicacion" id="descripcionadjudicacion" Name="descripcionadjudicacion" placeholder="Descripcion del tipo de la adjudicacion" maxlength="50" value=""></component-text-label>'+

        '<component-decimales-label label="Rango incial:" id="rangoinicial" Name="rangoinicial" placeholder="Rango inicial" maxlength="50" value=""></component-decimales-label>'+
        '<component-decimales-label label="Rango tope:" id="rangotope" Name="rangotope" placeholder="Rango Tope" maxlength="50" value=""> </component-decimales-label>'+

        '<component-text-label label="Id de la adjudicacion" id="idadjudicacion" Name="idadjudicacion" placeholder="Id de la adjudicacion" maxlength="50" value=""></component-text-label>'+

        '<br>'+'<br>'+
    '</form>'+

    '<div class="text-center">'+
        '<button class="btn btn  bgc8" style="color:#fff;" id="alta">Crear adjudicación </button>'+
    '</div>'+
'</div>';
 muestraModalGeneral(4, titulo,html);
//fnEjecutarVueGeneral('adjudicacionFormu');
  });
  $('#nuevoTipoAdjudicacion').click(function(){

    $("#tablaTipos").fadeOut("slow");
    $('#nuevoTipoAdjudicacion').hide();
    $('#datosFormuNuevaAdj').fadeIn("slow");
  });
  
  fnChecarTiposAdjudicacion();
  fnFormatoSelectGeneral(".tipoCampoCl");
});

function fnChecarTiposAdjudicacion(){
    muestraCargandoGeneral();

    dataObj = {
      proceso: 'typeAdform'
  };
    /*$.ajax({
      url: modelo,
      method: "POST",
      dataType: "json",
      data:  dataObj,
      cache: false,
      contentType: false,

  
  }) */
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
            columnasNombres += "{ name: 'rangoinicial', type: 'string'},";
            columnasNombres += "{ name: 'rangotope', type: 'string' },";
            columnasNombres += "{ name: 'descripcion', type: 'string' },";
            columnasNombres += "{ name: 'fecha', type: 'string' },";
            columnasNombres += "{ name: 'usuario', type: 'string' },";
              
          
            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'Rango inicial', datafield: 'rangoinicial', width: '20%', cellsalign: 'center', align: 'center',hidden: false },";
            columnasNombresGrid += " { text: 'Rango tope', datafield: 'rangotope', width: '20%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Descripcion',datafield: 'descripcion', width: '40%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Fecha creación',datafield: 'fecha', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Usuario',datafield: 'usuario', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            
           
            //columnasNombresGrid += " { text: 'Total', datafield: 'cantidad', width: '19%', cellsalign: 'right', align: 'center',cellsformat: 'C2', hidden:false"+colRtotal+"}";
            columnasNombresGrid += "]";

            var columnasExcel = [0, 1, 2,3,4];
            var columnasVisuales = [0, 1, 2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosTipos', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
           

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



function fnFechaServidor(){
    var fecha=0;
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
      fecha=info[0].fechaDMY;    
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
  });

  return fecha;

}

$(document).on('cellbeginedit','#tablaTipos > #datosTipos', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'usuario', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'descripcion', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'rangoinicial', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'rangotope', 'editable', false);
    
});
