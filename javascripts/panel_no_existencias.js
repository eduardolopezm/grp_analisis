/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 30 de Noviembre del 2017
 */

/* variables globales inicio */
/* variables globales fin */

var url =  "modelo/panel_no_existencias_modelo.php";
/** Inicia document Ready */ 
$(document).ready(function () {
    if (document.querySelector(".selectEstatusNoExistencia")) {
        dataObj = { 
            option: 'mostrarEstatusNoExistencia'
        };
        fnSelectGeneralDatosAjax('.selectEstatusNoExistencia', dataObj, 'modelo/panel_no_existencias_modelo.php', 0, '');
    }

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

function fnMostrarNoExistenciasPanel() {
    //alert("fnObtenerNoExistencaPanel");
    //muestraCargandoGeneral();

    var pFechaini = $("#txtFechaInicio").val(),
        pFechafin = $("#txtFechaFin").val(),
        pDependencia = $("#selectRazonSocial").val(),
        pUnidadResposable = $("#selectUnidadNegocio").val(),
        pUnidadEjecutora = $("#selectUnidadEjecutora").val(),
        pNoExistencia= $("#txtNumeroNoExistecia").val(),
        selEstatusRequisicion=$("#selEstatusRequisicion").val();

        //pFuncion= $("#PanelRequisiciones").data("funcion");
    var columnasNombres= "", columnasNombresGrid= "";

    //Parametros para la extraccion de datos
    dataObj = { 
        option: 'mostrarNoExistencias',
        fechainicio: pFechaini,
        fechafin: pFechafin,
        dependencia: pDependencia,
        unidadres: pUnidadResposable,
        unidadeje: pUnidadEjecutora,
        noexistencia: pNoExistencia,
        selEstatusRequisicion: selEstatusRequisicion
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
            columnasNombres += "{ name: 'ue', type: 'string' },";
            columnasNombres += "{ name: 'idNoExistencia', type: 'integer' },";
            columnasNombres += "{ name: 'idNoExistenciaH', type: 'integer' },";
            columnasNombres += "{ name: 'idRequisicion', type: 'string' },";
            columnasNombres += "{ name: 'fechaRegistro', type: 'string' },";
            columnasNombres += "{ name: 'comments', type: 'string' },";
            columnasNombres += "{ name: 'qty', type: 'string' },";
            columnasNombres += "{ name: 'status', type: 'string' },";
            columnasNombres += "{ name: 'imprimir', type: 'string' }";
            columnasNombres += "]";

            // Columnas para el GRID
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'UR', datafield: 'tagref', width: '5%', cellsalign: 'center', align: 'center', hidden: false, editable: false, },";
            columnasNombresGrid += " { text: 'UE', datafield: 'ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false, editable: false, },";
            columnasNombresGrid += " { text: 'Folio', datafield: 'idNoExistencia', width: '5%', cellsalign: 'center', align: 'center', hidden: false, editable: false, },";
            columnasNombresGrid += " { text: 'Folio', datafield: 'idNoExistenciaH', width: '5%', cellsalign: 'center', align: 'center', hidden: true, editable: false, },";
            columnasNombresGrid += " { text: 'Requi', datafield: 'idRequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: true, editable: false, },";
            columnasNombresGrid += " { text: 'Fecha', datafield: 'fechaRegistro', width: '10%', cellsalign: 'center', align: 'center', hidden: false, editable: false, },";            
            columnasNombresGrid += " { text: 'Observaciones', datafield: 'comments', width: '45%', cellsalign: 'left', align: 'center', hidden: false, editable: false, },";
            columnasNombresGrid += " { text: 'Cantidad', datafield: 'qty', width: '10%',cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer, editable: false, },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'status', width: '10%', cellsalign: 'center', align: 'center', hidden: false, editable: false, },";
            columnasNombresGrid += " { text: 'Imprimir', datafield: 'imprimir', width: '10%',cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer, editable: false, }";
            columnasNombresGrid += "]";

            // arreglo que guarda las columnas que se ocultan para exportar a excel
            var columnasExcel= [0, 2, 3, 4, 5, 6];
            var columnasVisuales= [0, 1, 3, 4, 5, 6, 7];

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

        //ocultaCargandoGeneral();

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //ocultaCargandoGeneral();
    });
}

$(document).on('cellbeginedit','#divCatalogo',function(event){
    //var args = event.args;
    // $(this).jqxGrid('setcolumnproperty', args.datafield,'editable', false);
    $(this).jqxGrid('setcolumnproperty','idRequisicion','editable', false);
    $(this).jqxGrid('setcolumnproperty','fechaRegistro','editable',false);
    $(this).jqxGrid('setcolumnproperty','idNoExistencia','editable',false);
    $(this).jqxGrid('setcolumnproperty','orden','editable',false);
    $(this).jqxGrid('setcolumnproperty','itemdesc','editable',false);
    $(this).jqxGrid('setcolumnproperty','comments','editable',false);
    $(this).jqxGrid('setcolumnproperty','qty','editable',false);
    $(this).jqxGrid('setcolumnproperty','imprimir','editable',false);
}); 

$(document).on('cellselect','#divCatalogo', function (event) {
    var columna = event.args.datafield;
    
    if(columna=='idNoExistencia'){
        fila = event.args.rowindex;
        enlace=$('#divTabla > #divCatalogo').jqxGrid('getcellvalue',fila, 'idNoExistencia');
         //alert(enlace);
         //enlace1=jQuery(enlace).find('a').attr('href');
        var href = $('<div>').append(enlace).find('a:first').attr('href');
        window.open(href,"_self");
    }
});

/*function fnImprimirReporteNoExistencia(idNoExistencia) {
    // impresion

    datos = '';
    //idsolicitud
    dependencia = $('#selectRazonSocial option:selected').text(); //$('#selectRazonSocial').val();
    ur = $("#selectUnidadNegocio option:selected").text(); //$('#selectUnidadNegocio').val();
    almacen = $("#selectAlmacen option:selected").text(); //$("#selectAlmacen").val();

    datosreporte = fnDatosReporte(idsolicitud);
    datos = "noExistenciaImprimirSolicitud.php?PrintPDF=1&idNoExistencia=" + idNoExistencia ;

    $("#tablaSalidas").hide();
    $("#mostrarImpresion").empty();
    $("#mostrarImpresion").append('<div class="text-center"><button id="regresaTablaSalidas" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button></div><object data="' + datos + '" width="100%" height="450px" type="application/pdf"><embed src="' + datos + '" type="application/pdf" />     </object>');
    //ocultaCargandoGeneral();
    //var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    //muestraModalGeneral(4, titulo,'<object data="'+datos+'" width="100%" height="450px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');

    // fin impresion
    //$("#viewSolicitud").html('<object data="'+datos+'" width="60%" height="800px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');

}
*/