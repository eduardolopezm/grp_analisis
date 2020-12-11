/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author
 * @version 1.0
 */
//

var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = "Selecciono Folio con Estatus diferente, el Estatus debe ser igual";
var mensajeSinNoCaptura = "Sin selección de Folio";
var dataJsonNoCaptura = new Array();

$(document).ready(function() {
    load_UR_UE();
   // loadMonths();
    loadRegister();
    //fnFormatoSelectGeneral(".selectUnidadNegocio");
});


function load_UR_UE(){

    var tagref = "";
    var selectUnidadNegocio = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        if (i == 0) {
            tagref = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            tagref = tagref+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
    }

    var ue = "";
    var selectUnidadEjecutora = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUnidadEjecutora.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
    }
}

function loadMonths(){
    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/panel_conciliacion_bancaria_Modelo.php",{option:'listMonth'}).then(function(result) {

       var selectData = JSON.parse(result);

        $.each(selectData.meses.month,function(key, registro) {
            $("#selectMonths").append('<option value='+registro.u_mes+'>'+registro.mes+'</option>');
        });

        fnFormatoSelectGeneral(".selectMonths");
    });
}


function loadRegister(){


    var selectUR =  $('#selectUnidadNegocio').val();
    var selectUE =  $('#selectUnidadEjecutora').val();
    var selectMoh =  $('#selectMonths').val();

    var resultStringUR = '';
    var resultStringUE = '';
    var resultStringMTH = '';


    if(selectUR == '' || selectUR == null){
        resultStringUR = '';
    }else{
        for(var i=0; i<selectUR.length; i++){
            resultStringUR += "'"+selectUR[i]+"'"+",";
        }
        resultStringUR = resultStringUR.substring(0,resultStringUR.length -1);
    }
    //
    if(selectUE == '' || selectUE == null){
        resultStringUE = '';
    }else{
        for(var j=0; j<selectUE.length; j++){
            resultStringUE += "'"+selectUE[j]+"'"+",";
        }
        resultStringUE = resultStringUE.substring(0,resultStringUE.length -1);
    }
    //
    if(selectMoh == '' || selectMoh == null){
        resultStringMTH = '';
    }else{
        for(var k=0; k<selectMoh.length; k++){
            resultStringMTH += "'"+selectMoh[k]+"'"+",";
        }
        resultStringMTH = resultStringMTH.substring(0,resultStringMTH.length -1);
    }



    $.ajax({
        url: "modelo/panel_conciliacion_bancaria_Modelo.php",
        async: false,
        cache: false,
        method: "GET",
        dataType: "JSON",
        data: {
            option:'search',
            ur: resultStringUR,
            ue: resultStringUE,
            month: resultStringMTH,
            starDate: $('#txtFechaDesde').val(),
            endDate: $('#txtFechaHasta').val(),
            Folio: $('#txtFolio').val()
        }

    }).done(function(data){

          console.log(data);

        dataJson = data.contenido.datos;
        columnasNombres = data.contenido.columnasNombres;
        columnasNombresGrid = data.contenido.columnasNombresGrid;
        dataJsonNoCaptura = data.contenido.datos;

        fnLimpiarTabla('divTabla', 'divContenidoTabla');

        var nombreExcel = data.contenido.nombreExcel;
        var columnasExcel= [1,2,3,5,6,7,8]; // Columnas a Imprimir en Excel
        var columnasVisuales= [0,1,2,3,5,6,7,8,9,10]; // Columnas a Visualizar en Pantalla
        fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

    }).fail(function(error){
        console.log(error);
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+error+'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });

}