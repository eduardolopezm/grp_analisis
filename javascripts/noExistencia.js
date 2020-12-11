/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 30 de Noviembre del 2017
 */

/* variables globales inicio */
var url ="modelo/noExistencia_modelo.php";
var idReqG = idRequisicionGeneral;
var idNoEG = idNoExistenciaGeneral;
/* variables globales fin */


/** Inicia document Ready */ 
$(document).ready(function () {
    $("#selectUnidadNegocio").prop("readonly", true);
    $("#selectUnidadNegocio").attr('disabled', 'disabled');

    fnNoExistenciaDetalle(idReqG, idNoEG);
    fnObtenerStatusReq(idReqG);
    /**
     * Muestra la información del catalogo completo o de forma individual
     * @param  {String} ue Código del Registro para obtener la información
     */
    $("#btnBuscarNoExistencia").click(function() {
        fnMostrarNoExistenciasPanel();
    });
    $("#divCatalogo").on('rowselect', function (event) {
        var rowindex = event.args.rowindex;
        //alert("Nada");
    });

    $("#btnBuscarNoExistencia").click();

});

function fnCambiarEstatus(status){
    
}

/*function fnNoExistenciaDetalle(idReq, idNoExist){
    muestraCargandoGeneral();
    var columnasNombres= "", columnasNombresGrid= "";

    //Parametros para la extraccion de datos
    dataObj = { 
        option: 'detalleNoExistencias',
        idReq: idReq,
        idNoExist: idNoExist
    };
    //Se manda a llamar metodo que trae los datos
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj

    }).done(function(data) {
        fnLimpiarTabla('divTabla', 'divCatalogo');
        if (data.result) {
            // Columnas para el GRID
            columnasNombres += "[";
            columnasNombres += "{ name: 'tagref', type: 'string' },";
            columnasNombres += "{ name: 'idNoExistencia', type: 'string' },";
            columnasNombres += "{ name: 'idRequisicion', type: 'string' },";
            columnasNombres += "{ name: 'orden', type: 'string' },";
            columnasNombres += "{ name: 'item', type: 'string' },";
            columnasNombres += "{ name: 'itemdesc', type: 'string' },";
            columnasNombres += "{ name: 'qty', type: 'string' }";
            columnasNombres += "]";

            // Columnas para el GRID
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'UR', datafield: 'tagref', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Folio', datafield: 'idNoExistencia', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Requi', datafield: 'idRequisicion', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: '#', datafield: 'orden', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Producto', datafield: 'item', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Descripción', datafield: 'itemdesc', width: '50%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Cantidad', datafield: 'qty', width: '10%',cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer }";
            columnasNombresGrid += "]";

            // arreglo que guarda las columnas que se ocultan para exportar a excel
            var columnasExcel= [];
            var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];

            info = data.contenido.datos;

            fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divCatalogo', '', 1, columnasExcel, false,true, "", columnasVisuales, "NoExistencia");

            $("#divCatalogo").jqxGrid('sortby', 'tagref', 'desc');
            //$("#divCatalogo").jqxGrid('sortby', 'idRequisicion', 'desc');
            //$("#divCatalogo").jqxGrid('sortby', 'orden', 'asc');

            $("#divCatalogo").bind("sort", function (event) {
                var sortinformation = event.args.sortinformation;
                var sortdirection = sortinformation.sortdirection;
                var sortcolumn = sortinformation.sortcolumn;

                //console.log("sort:"+sortdirection);

                if (sortcolumn == "idRequisicion" && sortdirection.ascending) {
                    $("#divCatalogo").jqxGrid('sortby', 'idRequisicion', 'desc');
                } else if (sortcolumn == "idRequisicion" && sortdirection.descending){
                    $("#divCatalogo").jqxGrid('sortby', 'idRequisicion', 'asc');
                }
            });
            $("#divCatalogo").bind("sort", function (event) {
                var sortinformation = event.args.sortinformation;
                var sortdirection = sortinformation.sortdirection;
                var sortcolumn = sortinformation.sortcolumn;

                //console.log("sort:"+sortdirection);

                if (sortcolumn == "tagref" && sortdirection.ascending) {
                    $("#divCatalogo").jqxGrid('sortby', 'tagref', 'desc');
                } else if (sortcolumn == "tagref" && sortdirection.descending){
                    $("#divCatalogo").jqxGrid('sortby', 'tagref', 'asc');
                }
            });
            $("#divCatalogo").bind("sort", function (event) {
                var sortinformation = event.args.sortinformation;
                var sortdirection = sortinformation.sortdirection;
                var sortcolumn = sortinformation.sortcolumn;

                //console.log("sort:"+sortdirection);

                if (sortcolumn == "orden" && sortdirection.ascending) {
                    $("#divCatalogo").jqxGrid('sortby', 'orden', 'desc');
                } else if (sortcolumn == "tagref" && sortdirection.descending){
                    $("#divCatalogo").jqxGrid('sortby', 'orden', 'asc');
                }
            });
            //$("#divCatalogo").jqxGrid('setcolumnproperty','id1','editable',true);
        }

        ocultaCargandoGeneral();

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        ocultaCargandoGeneral();
    });
}*/

function fnNoExistenciaDetalle(idReq, idNoExist){
    var noExistView = $('#idtxtNumNoExistencia').val();
    var tablaNoExistencia = $(document.createElement('table')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });
    $(tablaNoExistencia).empty();
    $(tablaNoExistencia).addClass('tableHeaderVerde');
    $(tablaNoExistencia).attr('id','tablaNoExistenciaDetalle');
    $(tablaNoExistencia).attr('border','1');
    $(tablaNoExistencia).attr('bordercolor','#DDDDDD');
    $(tablaNoExistencia).append('<tr>'+
              '<th class="w10p text-center">Folio</th>'+
              '<th class="w10p text-center">Requisición</th>'+
              '<th class="w10p text-center">Renglón</th>'+
              '<th class="w10p text-center">Producto</th>'+
              '<th class="w50p">Descripción</th>'+
              '<th class="w10p text-center">Cantidad</th>'+
            '</tr>');
    dataObj = {
        option: 'detalleNoExistencias',
        idReq: idReq,
        idNoExist: idNoExist
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if(data.contenido){
            dataNE = data.contenido.datos;
            console.log(dataNE);
                var idrequi = "";
                var norequi = "";
                var tagref = "";
                var ue = "";
                var comments = "";
                var fechaRegistro = "";
                var item = "";
                var itemdesc = "";
                var orden = "";
                var cantidad = "";
                var iddependencia = "";
                var dependencia = "";
                var idunidadNegocio = "";
                var unidadNegocio = "";
                var idunidadEjecutora = "";
                var unidadEjecutora = "";

                for (var info in dataNE) {
                    idnoE = dataNE[info].idNoExistencia;
                    idrequi = dataNE[info].idRequisicion;
                    norequi = dataNE[info].noRequisition;
                    fechaRegistro = dataNE[info].fechaRegistro;
                    tagref = dataNE[info].tagref;
                    ue = dataNE[info].ue;
                    qty = dataNE[info].qty;
                    orden = dataNE[info].orden;
                    item = dataNE[info].item;
                    itemdesc = dataNE[info].itemdesc;
                    comments = dataNE[info].comments;
                    iddependencia = dataNE[info].iddependencia;
                    dependencia = dataNE[info].dependencia;
                    idunidadNegocio = dataNE[info].idunidadNegocio;
                    unidadNegocio = dataNE[info].unidadNegocio;
                    idunidadEjecutora = dataNE[info].idunidadEjecutora;
                    unidadEjecutora = dataNE[info].unidadEjecutora;

                    $(tablaNoExistencia).append('<tr><td class="text-center">'+idnoE+'</td><td class="text-center">'+ norequi +'</td><td class="text-center">'+ orden +'</td><td class="text-center">'+item+'</td><td>'+itemdesc+'</td><td class="text-center">'+qty+'</td></tr>');
                }

            $("#main-noExistencia").append(tablaNoExistencia);
            $('#selectRazonSocial').empty();
            $('#selectRazonSocial').html("<option value="+iddependencia+">"+dependencia+"</option>");
            $("#selectRazonSocial").multiselect('rebuild');
            $("#selectRazonSocial").prop("readonly", true);
            $("#selectRazonSocial").attr('disabled', 'disabled'); 
            $('#selectUnidadNegocio').empty();
            $('#selectUnidadNegocio').html("<option value="+idunidadNegocio+">"+unidadNegocio+"</option>");
            $("#selectUnidadNegocio").multiselect('rebuild');
            $("#selectUnidadNegocio").prop("readonly", true);
            $("#selectUnidadNegocio").attr('disabled', 'disabled');    
            $('#selectUnidadEjecutora').empty();
            $('#selectUnidadEjecutora').html("<option value="+idunidadEjecutora+">"+unidadEjecutora+"</option>");  
            $("#selectUnidadEjecutora").multiselect('rebuild');
            $("#selectUnidadEjecutora").prop("readonly", true);
            $("#selectUnidadEjecutora").attr('disabled', 'disabled');  
            $('#idFechaElaboracion').val(fechaRegistro);
            $("#idFechaElaboracion").prop("readonly", true);
            $("#idFechaElaboracion").attr('disabled', 'disabled');  
            $('#txtAreaObs').html(comments);
            $("#txtAreaObs").prop("readonly", true);
            $("#txtAreaObs").attr('disabled', 'disabled');    
            $('#idtxtNoExitenciaView').html(noExistView);
        }else{
            console.log("error");
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });

}

function fnRegresarPanelNoExistencia(){
    location.replace("./panel_no_existencias.php");
}

function fnObtenerStatusReq(idReq){
    var sR = "";
    dataObj = {
            option: 'buscarStatusReq',
            idReq: idReq
        };
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                dataStatus = data.contenido.datos;
                var statusReq = "";
                for (var info in dataStatus) {
                    statusReq = dataStatus[info].status;
                }
                //statusReq = dataStatus;
                $("#statusReq").html(statusReq);
                $("#statusReq").prop("readonly", true);
                $("#statusReq").attr('disabled', 'disabled');
                $("#idStatusReq").val(""+statusReq);
                sR = statusReq;
                
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
        return sR;
}

$(document).on('cellbeginedit','#divCatalogo',function(event){
    //var args = event.args;
    // $(this).jqxGrid('setcolumnproperty', args.datafield,'editable', false);
    $(this).jqxGrid('setcolumnproperty','idRequisicion','editable', false);
    $(this).jqxGrid('setcolumnproperty','idNoExistencia','editable',false);
    $(this).jqxGrid('setcolumnproperty','orden','editable',false);
    $(this).jqxGrid('setcolumnproperty','itemdesc','editable',false);
    $(this).jqxGrid('setcolumnproperty','qty',false);
  
}); 

$(document).on('cellselect','#divCatalogo', function (event) {
    var columna = event.args.datafield;
    
    if(columna=='idrequisicion'){
        fila = event.args.rowindex;
        enlace=$('#divTabla > #divCatalogo').jqxGrid('getcellvalue',fila, 'idRequisicion');
         //alert(enlace);
         //enlace1=jQuery(enlace).find('a').attr('href');
        var href = $('<div>').append(enlace).find('a:first').attr('href');
        window.open(href,"_self");
    }
});

