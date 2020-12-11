var arregloRequis= new Array();
////
/** Inicia document Ready */ 
$(document).ready(function () {
    /**
     * Muestra la información del catalogo completo o de forma individual
     * @param  {String} ue Código del Registro para obtener la información
     */
   
    $('#selectUnidadNegocio').change(function(){
        $('#selectUnidadNegocio :selected').each(function(i, selected){
            if($(this).val() == ''){
                console.log($(this).val());
                $(this).removeAttr('selected');
            }
         });
         $('#selectUnidadNegocio').multiselect('rebuild');


    });

   
    
    $("#btnBuscarRequisiciones").click(function() {
        fnObtenerRequisicionesPanel();
    });

    $('#btnConsolidarRequisiciones').click(function(){

      datos=fnChecarSeleccionados('numerorequisicion');
     fnEnviarRequis(datos);
      
    });
   
    // Funcion que dispara evento con la opcion de ventana nueva para la captura
    // de requisicion
    $("#btnNuevaRequisicion").click(function (){
        window.open("Captura_Requisicion_V_4.php", "_self");
    });

    $("#divCatalogo").on('rowselect', function (event) {
        var rowindex = event.args.rowindex;
        //alert("Nada");
    });

    $("#btnBuscarRequisiciones").click();

    // Datos botones
    fnObtenerBotones_Funcion('divBotones', $("#PanelRequisiciones").data("funcion"));

    function fnChecarSeleccionados(celda){
        var requisicionesCS= new Array();
        var griddata = $('#divTabla > #divCatalogo').jqxGrid('getdatainformation');
        var cadena='';

        for (var i = 0; i < griddata.rowscount; i++){
            id=  $('#divTabla > #divCatalogo').jqxGrid('getcellvalue',i, 'id1');

            if(id==true){
                requisicionesCS.push($('#divTabla > #divCatalogo').jqxGrid('getcellvalue',i,celda));
            }
        }

       return requisicionesCS;
    }

    $('#Autorizar').click(function(){
        var iCntC = 0; // contador de cancelados
        var iCntA = 0; // contador de autorizados
        var iCntO = 0; // contador de originales
        var iCntCap = 0; // contador de capturados
        var iCntV = 0; // contador de validados
        var mensajeDiferencias = '';
        requis=  fnChecarSeleccionados('idrequisicionH');
        arregloRequis = requis;
        //console.log(requis);
        for(i in requis){
            //alert(requis[i]);
            //alert(requis[i]);
            idr = requis[i];
            statusReq = fnObtenerStatusReq(idr);
            mensajeDiferencias += fnObtenerDiferenciasRequisicion(idr);
            //alert("statusReq: "+statusReq);
            if(statusReq == 'Cancelado'){
                iCntC = iCntC + 1;
            }else if(statusReq == 'Autorizado'  ){
                iCntA = iCntA + 1;
            }else if(statusReq == 'Original'  ){
                iCntO = iCntO + 1;
            }else if(statusReq == 'Capturado'  ){
                iCntCap = iCntCap + 1;
            }else if(statusReq == 'Validar'  ){
                iCntV = iCntV + 1;
            }
        }

        if(iCntC > 0){
            muestraMensaje('No se puede autorizar una requisición ya cancelada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntA > 0){
            muestraMensaje('No se puede autorizar una requisición ya Autorizada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntO > 0){
            muestraMensaje('No se puede autorizar una Requisición en estatus Original', 3, 'divMensajeOperacion', 5000);
        } else if (mensajeDiferencias != '') {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Está seguro que desea autorizar las requisiciones seleccionadas? </p>';
            mensaje += mensajeDiferencias;
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnAutorizar()');
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Está seguro que desea autorizar las requisiciones seleccionadas? </p>';
            //muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnAutorizar('+requis+')');
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnAutorizar()');
        }
    });

    $('#Rechazar').click(function(){
        var userIDPerfil = fnObtenerPerfilUsr();
        var iCntC = 0; // contador de cancelados
        var iCntA = 0; // contador de Autorizados
        var iCntO = 0; // contador de originales
        var iCntCap = 0; // contador de CApturados
        var iCntV = 0; // contador de para validar por status y perfil
        var iCntPA = 0; // contador de para validar por status y perfil
        requis=  fnChecarSeleccionados('idrequisicionH');
        arregloRequis = requis;
        for(i in requis){
            idr = requis[i];
            statusReq = fnObtenerStatusReq(idr);
            if(statusReq == 'Cancelado'){
                iCntC = iCntC + 1;
            }else if(statusReq == 'Autorizado'  ){
                iCntA = iCntA + 1;
            }else if(statusReq == 'Original'  ){
                iCntO = iCntO + 1;
            }else if(statusReq == 'Capturado'  ){
                iCntCap = iCntCap + 1;
            }else if((statusReq == 'Validar' || statusReq == 'PorAutorizar') && userIDPerfil == 9 ){
                iCntV = iCntV + 1;
            }else if(statusReq == 'PorAutorizar' && userIDPerfil == 10 ){
                iCntPA = iCntPA + 1;
            }
        }
        if(iCntC > 0){
            muestraMensaje('No se puede rechazar una requisición ya cancelada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntA > 0){
            muestraMensaje('No se puede rechazar una requisición ya Autorizada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntO > 0){
            muestraMensaje('No se puede rechazar una requisición en estatus Original', 3, 'divMensajeOperacion', 5000);
        }else if( iCntCap > 0){
            muestraMensaje('No se puede rechazar una requisición Capturada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntV > 0){
            muestraMensaje('EL usuario capturista, no puede rechazar la requisición con estatus '+ statusReq, 3, 'divMensajeOperacion', 5000);
        }else if( iCntPA > 0){
            muestraMensaje('EL usuario validador, no puede rechazar la requisición con estatus '+ statusReq, 3, 'divMensajeOperacion', 5000);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Está seguro que desea rechazar las requisiciones seleccionadas? </p>';
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnRechazar()');
        }
    });

    $('#Cancelar').click(function(){
        var userIDPerfil = fnObtenerPerfilUsr();
        var iCntC = 0; // contador de cancelados
        var iCntA = 0; // contador de Autorizados
        var iCntO = 0; // contador de originales
        var iCntV = 0; // contador de para validar por status y perfil
        var iCntPA = 0; // contador de para validar por status y perfil
        requis=  fnChecarSeleccionados('idrequisicionH');
        arregloRequis = requis;
        //alert(arregloRequis);
        var statusReq= "";
        for(i in requis){
            //alert(requis[i]);
            idr = requis[i];
            statusReq = fnObtenerStatusReq(idr);
            //alert("statusReq: "+statusReq);
            if(statusReq == 'Cancelado'){
                iCntC = iCntC + 1;
            }else if(statusReq == 'Autorizado'  ){
                iCntA = iCntA + 1;
            }else if(statusReq == 'Original'  ){
                iCntO = iCntO + 1;
            }else if((statusReq == 'Validar' || statusReq == 'PorAutorizar') && userIDPerfil == 9 ){
                iCntV = iCntV + 1;
            }else if(statusReq == 'PorAutorizar' && userIDPerfil == 10 ){
                iCntPA = iCntPA + 1;
            }
        }
        if(iCntC > 0){
            muestraMensaje('No se puede cancelar una Requisición ya cancelada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntA > 0){
            muestraMensaje('No se puede cancelar una Requisición ya Autorizada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntO > 0){
            muestraMensaje('No se puede cancelar una Requisición en estatus Original', 3, 'divMensajeOperacion', 5000);
        }else if( iCntV > 0){
            muestraMensaje('EL usuario capturista, no puede cancelar la requisición con estatus '+ statusReq, 3, 'divMensajeOperacion', 5000);
        }else if( iCntPA > 0){
            muestraMensaje('EL usuario validador, no puede cancelar la requisición con estatus '+ statusReq, 3, 'divMensajeOperacion', 5000);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Está seguro que desea cancelar las requisiciones seleccionadas? </p>';
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnCancelar()');
        }
    });

    $('#Avanzar').click(function(){

        var userIDPerfil = fnObtenerPerfilUsr();
        var iCntC = 0; // contador de cancelados
        var iCntO = 0; // contador de originales
        var iCntA = 0; // contador de autorizados
        var iCntPA = 0; // contador de por autorizar
        var iCntV = 0; // contador de para validar por status y perfil
        requis= fnChecarSeleccionados('idrequisicionH');
        arregloRequis = requis;
        var statusReq= "";
        for(i in requis){
            idr = requis[i];
            statusReq = fnObtenerStatusReq(idr);
            if(statusReq == 'Cancelado'){
                iCntC = iCntC + 1;
            }else if(statusReq == 'Autorizado'  ){
                iCntA = iCntA + 1;
            }
            else if(statusReq == 'Original'  ){
                iCntO = iCntO + 1;
            }
            else if(statusReq == 'PorAutorizar'  ){
                iCntPA = iCntPA + 1;
            }else if((statusReq == 'Validar' || statusReq == 'PorAutorizar') && userIDPerfil == 9 ){
                iCntV = iCntV + 1;
            }
        }
        if(iCntC > 0){
            muestraMensaje('No se puede avanzar una Requisición ya cancelada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntA > 0){
            muestraMensaje('No se puede avanzar una Requisición ya Autorizada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntO > 0){
            muestraMensaje('No se puede avanzar una Requisición en estatus Original', 3, 'divMensajeOperacion', 5000);
        }else if( iCntPA > 0){
            muestraMensaje('No se puede avanzar una Requisición en estatus por autorizar', 3, 'divMensajeOperacion', 5000);
        }else if( iCntV > 0){
            muestraMensaje('EL usuario capturista, no puede avanzar la requisición con estatus '+ statusReq, 3, 'divMensajeOperacion', 5000);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Esta seguro que desea avanzar la(s) Requisición(es) seleccionada(s)? </p>';
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnAvanzar()');
        }
    });
    $('#btnProvSugeridos').click(function(){
        datos=fnChecarSeleccionados('numerorequisicion');
        fnEnviarRequisAProgSug(datos);
    });

    $('#Validar').addClass('hide');
});

function fnObtenerDiferenciasRequisicion(idReq) {
    // Valida si existen diferencias en la solictid del almacen
    var mensaje = '';

    dataObj = {
        option: 'diferenciasRequisicion',
        idReq: idReq
    };
    $.ajax({
    async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/PO_SelectOSPurchOrder_modelo.php",
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            mensaje = data.contenido;
        }else{
            // muestraMensaje('No se encontro ningún estatus ', 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });

    return mensaje;
}

function fnCambiarEstatus(status){
    
}

function fnObtenerRequisicionesPanel() {
    muestraCargandoGeneral();
    var pFechaini = $("#txtFechaInicio").val(),
        pFechafin = $("#txtFechaFin").val(),
        pDependencia = $("#selectRazonSocial").val(),
        pUnidadResposable = $("#selectUnidadNegocio").val(),
        pUnidadEjecutora = $("#selectUnidadEjecutora").val(),
        pRequisicion= $("#txtNumeroRequisicion").val(),
        pCodigoProveedor= $("#txtCodigoProveedor").val(),
        pNombreProveedor= $("#txtNombreProveedor").val(),
        pFuncion= $("#PanelRequisiciones").data("funcion");
    var pEstatus = ""
        ,colResumenTotal = '';
    var selEstatusRequisicion = document.getElementById('selEstatusRequisicion');

    for ( var i = 0; i < selEstatusRequisicion.selectedOptions.length; i++) {
        if (i == 0) {
            pEstatus = "'"+selEstatusRequisicion.selectedOptions[i].value+"'";
        }else{
            pEstatus = pEstatus+", '"+selEstatusRequisicion.selectedOptions[i].value+"'";
        }
    }
    var columnasNombres= "", columnasNombresGrid= "";
    // configurar columna de resumen
    // @NOTE:se comenta y solo se deja en blanco,
    // debido a la solicitud del usuario en la fecha 09.04.18
    // @NOTE: El usuario siempre si cambio de opinion y si quiere
    // que aparesca el total en la fecha 10.04.18 >:|
    // var colResumenTotal =''; 
    // @NOTE: Se agrega permiso para poder mostrar el total  de la sumatoria
    // en la columna de totales de requisició @date: 11.04.18
    if(sumatoriaTotal != "0"){
        colResumenTotal = ", aggregates: [{'<b>Total</b>' :"+
                            "function (aggregatedValue, currentValue) {"+
                                "var total = currentValue;"+
                                "return aggregatedValue + total;"+
                            "}"+
                        "}] ";
    }
    //Parametros para la extraccion de datos
    console.log(pUnidadResposable);
    dataObj = { 
        option: 'traeRequisiciones',
        fechainicio: pFechaini,
        fechafin: pFechafin,
        dependencia: pDependencia,
        unidadres: pUnidadResposable,
        unidadeje: pUnidadEjecutora,
        requisicion: pRequisicion,
        idproveedor: pCodigoProveedor,
        nomproveedor: pNombreProveedor,
        estatus: pEstatus,
        funcion: pFuncion,
        codigoExpediente: $("#codigoExpediente").val()
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/PO_SelectOSPurchOrder_modelo.php",
        data: dataObj

    }).done(function(data) {
        fnLimpiarTabla('divTabla', 'divCatalogo');
        if (data.result) {
            // Columnas para el GRID
            columnasNombres += "[";
            columnasNombres += "{ name: 'id1', type: 'bool'},";
            columnasNombres += "{ name: 'ur', type: 'string' },";
            columnasNombres += "{ name: 'ue', type: 'string' },";
            columnasNombres += "{ name: 'numerorequisicion', type: 'string' },";
            columnasNombres += "{ name: 'idrequisicion', type: 'string' },";
            columnasNombres += "{ name: 'idrequisicionH', type: 'integer' },";
            columnasNombres += "{ name: 'orddate', type: 'string' },";
            columnasNombres += "{ name: 'fecharequerida', type: 'string' },";
            columnasNombres += "{ name: 'observaciones', type: 'string' },";
            columnasNombres += "{ name: 'estatus', type: 'string' },";
            columnasNombres += "{ name: 'totalrequisicion', type: 'number' },";
            columnasNombres += "{ name: 'imprimir', type: 'string' }";
            columnasNombres += "]";
            // Columnas para el GRID
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'Sel', datafield:'id1',editable: true,columntype: 'checkbox', width: '3%', cellsalign: 'center', align: 'center'}, ";
            columnasNombresGrid += " { text: 'UR', datafield: 'ur', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'UE', datafield: 'ue', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Folio', editable: false, datafield: 'numerorequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
            // columnasNombresGrid += " { text: 'idrequisicion', editable: false, datafield: 'numerorequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'idreqH', datafield: 'idrequisicionH', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'Folio', datafield: 'idrequisicion', width: '5%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Fecha Req.', datafield: 'fecharequerida', width: '10%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Observaciones', datafield: 'observaciones', width: '47%',cellsalign: 'left', align: 'center', hidden: false, rendered: tooltiprenderer },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '8%', cellsalign: 'center', align: 'center', hidden: false},";
            columnasNombresGrid += " { text: 'Total Requisición', datafield: 'totalrequisicion', width: '12%', cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false"+colResumenTotal+"},";
            columnasNombresGrid += " { text: 'Imprimir', datafield: 'imprimir', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer }";
            columnasNombresGrid += "]";
            // arreglo que guarda las columnas que se ocultan para exportar a excel
            var columnasExcel= [1, 2, 3, 6, 7, 8, 9];
            var columnasVisuales= [0, 1, 2, 5, 6, 7, 8, 9, 10];
            info = data.contenido.datosCatalogo;
            fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, true, '', columnasVisuales, "Requisiciones");
            $("#divCatalogo").jqxGrid('sortby', 'idrequisicionH', 'desc');
            $("#divCatalogo").bind("sort", function (event) {
                var sortinformation = event.args.sortinformation;
                var sortdirection = sortinformation.sortdirection;
                var sortcolumn = sortinformation.sortcolumn;
                if (sortcolumn == "idrequisicion" && sortdirection.ascending) {
                    $("#divCatalogo").jqxGrid('sortby', 'idrequisicionH', 'desc');
                } else if (sortcolumn == "idrequisicion" && sortdirection.descending){
                    $("#divCatalogo").jqxGrid('sortby', 'idrequisicionH', 'asc');
                }
            });
            $("#divCatalogo").jqxGrid('setcolumnproperty','id1','editable', true);
            $("#divCatalogo").jqxGrid('setcolumnproperty','totalrequisicion','editable', false);
        }
        ocultaCargandoGeneral();
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        ocultaCargandoGeneral();
    });
}

function fnCancelar(){
    dataObj = {
        option: 'cancelarRequisicion',
        noReq: arregloRequis
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/PO_SelectOSPurchOrder_modelo.php",
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se cancelo la (s) Requisición (es) seleccionada (s)');
            fnObtenerRequisicionesPanel();
        }else{
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se cancelo ninguna Requisición ');
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    }); 
}

function fnAutorizar(){
    // Función para autorzar el estatus de una requisición
    // console.log("arregloRequis: "+JSON.stringify(arregloRequis));
    var todasBien = 1;
    for(i in arregloRequis){
        // Recorrer datos seleccionados y validar que no tenga errores
        idreq = arregloRequis[i];
        if (!fnValidarRequisicion(idreq)) {
            // si encuentra un error
            todasBien = 0;
        }
    }
    
    if(todasBien == 1){
        // todas estan bien
        todasBien = 1;
        for(i in arregloRequis) {
            idreq = arregloRequis[i];

            if (fnValidarRequisicion(idreq)) {
                $('#idTableReqValidacion').css('display','block');
                dataObj = {
                    option: 'autorizarRequisicion',
                    noReq: idreq
                };
                $.ajax({
                    async:false,
                    cache:false,
                    method: "POST",
                    dataType: "json",
                    url: "modelo/PO_SelectOSPurchOrder_modelo.php",
                    data: dataObj
                }).done(function(data) {
                    if (data.result) {
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se autorizarón la (s) Requisición (es) seleccionada (s)');
                        // fnObtenerRequisicionesPanel();
                    }else{
                        // Si hubo problema
                        todasBien = 0;
                        if(data.contenido == null || data.contenido == '' || data.contenido == 0 || data.contenido === 'undefined' ){
                             muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se autorizó ninguna Requisición ');
                        }else{
                            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', data.contenido);
                        }
                    }
                }).fail(function(result) {
                    console.log("ERROR");
                    console.log(result);
                }); 
            } else {
                // Si hubo problema
                todasBien = 0;
            }
        }

        if(todasBien == 1){
            // Mensaje de todo bien
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se autorizarón la (s) Requisición (es) seleccionada (s)');
        } 
        /*else {
            // Mensaje con errores
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se autorizó ninguna Requisición ');
        }*/

        // Recargar datos panel
        fnObtenerRequisicionesPanel();
    }
}

function fnAvanzar(){
    // Función para avanzar el estatus de una requisición
    // console.log("arregloRequis: "+JSON.stringify(arregloRequis));
    var todasBien = 1;
    for(i in arregloRequis){
        // Recorrer datos seleccionados y validar que no tenga errores
        idreq = arregloRequis[i];
        if (!fnValidarRequisicion(idreq)) {
            // si encuentra un error
            todasBien = 0;
        }
    }
    
    if(todasBien == 1){
        // todas estan bien
        todasBien = 1;
        for(i in arregloRequis){
            idreq = arregloRequis[i];
            if (fnValidarRequisicion(idreq)) {
                // Si valida core¡rectamente
                $('#idTableReqValidacion').css('display','block');
                dataObj = {
                    option: 'avanzarRequisicion',
                    noReq: idreq
                };  
                $.ajax({
                    async:false,
                    cache:false,
                    method: "POST",
                    dataType: "json",
                    url: "modelo/PO_SelectOSPurchOrder_modelo.php",
                    data: dataObj
                }).done(function(data) {
                    if (data.result) {
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se avanzó la (s) Requisición (es) seleccionada (s)');
                        // fnObtenerRequisicionesPanel();
                    }else{
                        // Si hubo problema
                        todasBien = 0;
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se avanzó ninguna Requisición.');
                    }
                }).fail(function(result) {
                    console.log("ERROR");
                    console.log(result);
                });
            } else {
                // Si hubo problema
                todasBien = 0;
            }
        }

        if(todasBien == 1){
            // Mensaje de todo bien
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se avanzó la (s) Requisición (es) seleccionada (s)');
        } else {
            // Mensaje con errores
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se avanzó ninguna Requisición.');
        }

        // Recargar datos panel
        fnObtenerRequisicionesPanel();
    }        
}

function fnRechazar(){
    dataObj = {
        option: 'rechazarRequisicion',
        noReq: arregloRequis
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/PO_SelectOSPurchOrder_modelo.php",
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se rechazaron la (s) Requisición (es) seleccionada (s)');
            fnObtenerRequisicionesPanel();
        }else{
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se rechazó ninguna Requisición');
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    }); 
}

function fnObtenerPerfilUsr(){
    var perfilUsr = "";
    var perfilid = "";
    dataObj = {
        option: 'buscarPerfilUsr'
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/PO_SelectOSPurchOrder_modelo.php",
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            dataPerfil = data.contenido.datos;
            for (var info in dataPerfil) {
                perfilUsr = dataPerfil[info].userid;
                perfilid = dataPerfil[info].profileid;
            }
        }else{
            console.log("No se encontro el perfil del usuario");
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
    return perfilid;
}

function fnObtenerStatusReq(idReq){
     var statusReq = "";
     var ordernoReq = "";
    dataObj = {
        option: 'statusRequisicion',
        idReq: idReq
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/PO_SelectOSPurchOrder_modelo.php",
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            dataReqStatus = data.contenido.datos;
            for (var info in dataReqStatus) {
                ordernoReq = dataReqStatus[info].orderno;
                statusReq = dataReqStatus[info].status;
            }
        }else{
            muestraMensaje('No se encontro ningún estatus ', 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
     return statusReq;
}

//Arturo Lopez Peña
$(document).on('cellbeginedit','#divCatalogo',function(event){
    //var args = event.args;
    // $(this).jqxGrid('setcolumnproperty', args.datafield,'editable', false);
    $(this).jqxGrid('setcolumnproperty','idrequisicion','editable', false);
    $(this).jqxGrid('setcolumnproperty','orddate','editable',false);
    $(this).jqxGrid('setcolumnproperty','fecharequerida','editable',false);
    $(this).jqxGrid('setcolumnproperty','observaciones','editable',false);
    $(this).jqxGrid('setcolumnproperty','estatus','editable',false);
    $(this).jqxGrid('setcolumnproperty','totalrequisicion',false);
  
}); 

$(document).on('cellselect','#divCatalogo', function (event) {
    var columna = event.args.datafield;
    
    if(columna=='idrequisicion'){
        fila = event.args.rowindex;
        enlace=$('#divTabla > #divCatalogo').jqxGrid('getcellvalue',fila, 'idrequisicion');
         //enlace1=jQuery(enlace).find('a').attr('href');
        var href = $('<div>').append(enlace).find('a:first').attr('href');
        window.open(href,"_self");
    }
}); 

function fnEnviarRequisAProgSug(requis){
    
    //     var formData = new FormData();

    //    /* formData.setAttribute("method", "post");
    //     formData.setAttribute("action", "procesoCompraV2.php");
    //     formData.setAttribute("target", "_self"); */

    //     for(a=0;a<requis.length;a++){
    //          formData.append("requis[" + a + "]",requis[a]);
    //          requis[a];
    //     }

    //     /*document.body.appendChild(formData);
    //     formData.submit(); */

    // var request = new XMLHttpRequest();
    // request.open("POST", "procesoCompraV2.php",true);
    // request.send(formData);

    //   request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //   request.send("fname=Henry&lname=Ford");
   
    dataObj = {
        proceso: 'enviarprovsug',
        requis: requis
    };
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/procesoCompraModeloV2.php",
        async: false,
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(4, titulo,data.contenido);
        } else {
            // ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log(result);
        ocultaCargandoGeneral();
    });
}

function fnEnviarRequis(datos){

  // console.log(datos);
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "consolidaciones.php");
    form.setAttribute("target", "_self");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "datosRequis");
    hiddenField.setAttribute("value", datos);
    form.appendChild(hiddenField);
    document.body.appendChild(form);
    form.submit();
    
}

//NO BORRAR SIRVE COMO REFERENCIA PARA NO VOLVER A INVESTIGAR ALGUNAS PROPIEDADES DEL JQXGRID
/*
$('#divTabla > #divCatalogo').on('rowClick', function (event) {
var args = event.args;
var row = args.row;
var key = args.key;
var dataField = args.dataField;
var clickEvent = args.originalEvent;
currentKeyClick = key;
currentDatafieldClick = dataField;
if (currentKeyClick == 8 && currentDatafieldClick == "idrequisicion") {
            alert();  
             //$('#divTabla > #divCatalogo').jqxGrid('setcolumnproperty', 'idrequisicion','editable', false);
       } else {
       
       }
   }); */

 //fin Arturo Lopez Peña

