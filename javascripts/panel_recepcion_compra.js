/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 * @Fecha 01 de Diciembre del 2017
 */

var dataJsonNoCaptura = new Array();
var dataJsonNoCapturaSeleccionados = new Array();
var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono mas de una Requisición. Para realizar la operación solo seleccionar una requisición</p>';
var mensajeSinNoCaptura = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin selección de Requisición</p>';

var url= "modelo/panel_recepcion_compra_modelo.php";

$(document).ready(function () {
    // Datos botones
    fnObtenerBotones('divBotones');

    /**
     * Muestra la información del catalogo completo o de forma individual
     * @param  {String} ue Código del Registro para obtener la información
     */
    $("#btnBuscarOrdenes").click(function() {
        muestraCargandoGeneral();

        var pFechaini = $("#txtFechaInicio").val(),
            pFechafin = $("#txtFechaFin").val(),
            pDependencia = $("#selectRazonSocial").val(),
            pUnidadResposable = $("#selectUnidadNegocio").val(),
            pRequisicion= $("#txtNumeroRequisicion").val(),
            pCodigoProveedor= $("#txtCodigoProveedor").val(),
            pNombreProveedor= $("#txtNombreProveedor").val(),
            pEstatus= '',
            pFuncion= $("#PanelOrdenesCompra").data("funcion"),
            noOrdenCompra = $("#txtOrdenCompra").val();

        var ue = $("#selectUnidadEjecutora").val();

        var columnasNombres= "", columnasNombresGrid= "";

        // configurar columna de resumen
        var colResumenTotal= ", aggregates: [{'<b>Total</b>' :"+
                            "function (aggregatedValue, currentValue) {"+
                                "var total = currentValue;"+
                                "return aggregatedValue + total;"+
                            "}"+
                        "}] ";

        //Parametros para la extraccion de datos
        dataObj = { 
            option: 'traeOrdenesCompras',
            fechainicio: pFechaini,
            fechafin: pFechafin,
            dependencia: pDependencia,
            unidadres: pUnidadResposable,
            requisicion: pRequisicion,
            idproveedor: pCodigoProveedor,
            nomproveedor: pNombreProveedor,
            estatus: pEstatus,
            funcion: pFuncion,
            noOrdenCompra: noOrdenCompra,
            ue: ue
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
            //console.log("Bien");
            
            ocultaCargandoGeneral();

            fnLimpiarTabla('divTabla', 'divCatalogo');

            if (data.result) {
                // Columnas para el GRID
    		    columnasNombres += "[";
    		    // columnasNombres += "{ name: 'seleccionar', type: 'string' },";
                columnasNombres += "{ name: 'id1', type: 'bool'},";
                columnasNombres += "{ name: 'tagdescription', type: 'string' },";
                columnasNombres += "{ name: 'uedescription', type: 'string' },";
                columnasNombres += "{ name: 'idrequisicion', type: 'string' },";
                columnasNombres += "{ name: 'ordencompra', type: 'string' },";
                columnasNombres += "{ name: 'fechaCaptura', type: 'string' },";
                columnasNombres += "{ name: 'fecharequerida', type: 'string' },";
                columnasNombres += "{ name: 'fechaRecepcion', type: 'string' },";
    		    columnasNombres += "{ name: 'idproveedor', type: 'string' },";
    		    columnasNombres += "{ name: 'nombreproveedor', type: 'string' },";
    		    columnasNombres += "{ name: 'estatus', type: 'string' },";
    		    columnasNombres += "{ name: 'totalrequisicion', type: 'float' },";
                columnasNombres += "{ name: 'totalrequisicion2', type: 'string' },";
                columnasNombres += "{ name: 'observaciones', type: 'string' },";
                columnasNombres += "{ name: 'impresion', type: 'string' },";
                columnasNombres += "{ name: 'orderno', type: 'string' },";
                columnasNombres += "{ name: 'numerorequisicion', type: 'string' },";
                columnasNombres += "{ name: 'urlProceso', type: 'string' }";
                //columnasNombres += "{ name: 'operacion', type: 'string' }";
    		    columnasNombres += "]";

    		    // Columnas para el GRID
    		    columnasNombresGrid += "[";
                // columnasNombresGrid += " { text: 'Sel', datafield: 'seleccionar', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: '', datafield: 'id1', width: '3%', editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
                columnasNombresGrid += " { text: 'UR', datafield: 'tagdescription', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false},";
                columnasNombresGrid += " { text: 'UE', datafield: 'uedescription', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false},";
                columnasNombresGrid += " { text: 'Requisición', datafield: 'idrequisicion', width: '6%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Orden Compra', datafield: 'ordencompra', width: '6%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    		    columnasNombresGrid += " { text: 'Fecha Cap.', datafield: 'fechaCaptura', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Fecha Req.', datafield: 'fecharequerida', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Fecha Rec.', datafield: 'fechaRecepcion', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    		    columnasNombresGrid += " { text: 'Código Proveedor', datafield: 'idproveedor',align: 'center', width: '7%', editable: false, hidden: false },";
    		    columnasNombresGrid += " { text: 'Nombre Proveedor', datafield: 'nombreproveedor',align: 'center', width: '15%', editable: false, hidden: false },";
    		    columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false},";
    		    columnasNombresGrid += " { text: 'Total', datafield: 'totalrequisicion', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false"+colResumenTotal+"},";
                columnasNombresGrid += " { text: 'Total', datafield: 'totalrequisicion2', width: '11%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true},";
    			columnasNombresGrid += " { text: 'Observaciones', datafield: 'observaciones', width: '17%', editable: false, hidden: false, rendered: tooltiprenderer, align: 'center' },";
                columnasNombresGrid += " { text: 'Imprimir', datafield: 'impresion', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer },";
                columnasNombresGrid += " { text: 'orderno', datafield: 'orderno', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
                columnasNombresGrid += " { text: 'numerorequisicion', datafield: 'numerorequisicion', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
                columnasNombresGrid += " { text: 'urlProceso', datafield: 'urlProceso', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
                //columnasNombresGrid += " { text: 'Opción', datafield: 'operacion', width: '5%', cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer }";
                columnasNombresGrid += "]";

                // arreglo que guarda las columnas que se ocultan para exportar a excel
                var columnasExcel= [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13];
                var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 14];
                var nombreExcel = data.contenido.nombreExcel;

                info = data.contenido.datos;
                dataJsonNoCaptura = data.contenido.datos;

                //fnAgregarGrid(info, columnasNombres, columnasNombresGrid, 'divCatalogo', 'nombreproveedor', 1);
                fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
    });
    // Funcion que dispara evento con la opcion de ventana nueva para la captura
    // de requisicion
    $("#btnNuevaOrden").click(function (){
        window.open("PO_Header.php?NewOrder=Yes", "_blank");
    });

    $("#btnBuscarOrdenes").click();

    $('#btnConsolidarRequisiciones').click(function(){
        window.open("consolidaciones.php","_self");
    });
});

/**
 * Función para confirmar proceso de la orden de compra
 * @param  string namebutton Nombre del boton para mensaje
 * @return {[type]}            [description]
 */
function fnConfirmacionOrdenCompra(statusid, namebutton) {
    // Obtener registros seleccionados
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();
    
    if (dataJsonNoCapturaSeleccionados.length > 1) {
        // Selecciono varias requsiciones
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensajeEstatusDiferentes);
    }else if (dataJsonNoCapturaSeleccionados.length == 0) {
        // No selecciono requisición
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else{
        // Selecciono una requisición
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = "<p>¿Está seguro de "+namebutton+" a la requisición "+dataJsonNoCapturaSeleccionados[0].requisitionno+"?</p>";
        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnGenerarOperacionValidacion("+statusid+")");
    }
}

/**
 * Función para confirmar proceso de la orden de compra
 * @param  string namebutton Nombre del boton para mensaje
 * @return {[type]}            [description]
 */
function fnConfirmacionReversarOrdenCompra(statusid, namebutton) {
    // Obtener registros seleccionados
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();
    
    if (dataJsonNoCapturaSeleccionados.length > 1) {
        // Selecciono varias requsiciones
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensajeEstatusDiferentes);
    }else if (dataJsonNoCapturaSeleccionados.length == 0) {
        // No selecciono requisición
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else{
        // Selecciono una requisición
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = "<p>¿Está seguro de "+namebutton+" la requisición "+dataJsonNoCapturaSeleccionados[0].requisitionno;
        mensaje +=  ". Proveedor "+dataJsonNoCapturaSeleccionados[0].idproveedor+" - "+dataJsonNoCapturaSeleccionados[0].nombreproveedor+"?</p>";
        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnValidacionReversa("+statusid+")");
    }
}

/**
 * Función para validar reversa de autorizacion o recepecion de la orden de compra
 * @return {[type]} [description]
 */
function fnValidacionReversa(statusid) {
    // Validar para operacion
    dataObj = { 
        option: 'validarReversaOrdenCompra',
        dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
        statusid: statusid
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/panel_recepcion_compra_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            //dataJson = data.contenido.datos;
            //ocultaCargandoGeneral();
            // fnGenerarProcesoCompra(dataJsonNoCapturaSeleccionados[0].urlProceso);
            if (Number(data.contenido.tipoReversa) == Number(1) || Number(data.contenido.tipoReversa) == Number(2)) {
                // Si es reversar autorizacion de compra o recepcion
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneralConfirmacion(4, titulo, data.contenido.mensajeErrores, "", "fnReversarInformacion("+statusid+", "+data.contenido.tipoReversa+")");
            } else {
                // Tiene Factura de Pago
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);
            }
        }else{
            //ocultaCargandoGeneral();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);
        }
    })
    .fail(function(result) {
        //ocultaCargandoGeneral();
        //console.log("ERROR");
        //console.log( result );
    });
}

/**
 * Función para generar la reversa autorizacion o recepcion de la orden de compra
 * @param  {[type]} statusid    Estatus para validar 99 es rechazo
 * @param  {[type]} tipoReversa Tipo de reversa, 1 - Orden de compra autorizada, 2 - Recepción de orden de compra
 * @return {[type]}             [description]
 */
function fnReversarInformacion(statusid, tipoReversa) {
    // Funcion para hacer proceso de reversa
    // console.log("statusid: "+statusid+" - tipoReversa: "+tipoReversa);
    // Validar para operacion
    dataObj = { 
        option: 'reversaOrdenCompra',
        dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
        statusid: statusid,
        tipoReversa: tipoReversa
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/panel_recepcion_compra_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            //dataJson = data.contenido.datos;
            //ocultaCargandoGeneral();
            // fnGenerarProcesoCompra(dataJsonNoCapturaSeleccionados[0].urlProceso);
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);

            $("#btnBuscarOrdenes").click();
        }else{
            //ocultaCargandoGeneral();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);
        }
    })
    .fail(function(result) {
        //ocultaCargandoGeneral();
        //console.log("ERROR");
        //console.log( result );
    });
}

/**
 * Función para inciar el proceso de compra
 * @return {[type]} [description]
 */
function fnGenerarOperacionValidacion(statusid) {
    // Validar para operacion
    dataObj = { 
        option: 'validarEstatusOrdenCompra',
        dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
        statusid: statusid
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/panel_recepcion_compra_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            //dataJson = data.contenido.datos;
            //ocultaCargandoGeneral();
            fnGenerarProcesoCompra(dataJsonNoCapturaSeleccionados[0].urlProceso);
        }else{
            //ocultaCargandoGeneral();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);
        }
    })
    .fail(function(result) {
        //ocultaCargandoGeneral();
        //console.log("ERROR");
        //console.log( result );
    });
}

/**
 * Función para ejecutar el link del proceso (Recibir o Facturar)
 * @param  String url Url para ejecutar el proceso
 * @return {[type]}     [description]
 */
function fnGenerarProcesoCompra(url) {
    var link = document.getElementById("Link_NuevoGeneral");
    link.setAttribute("href", ""+url);
    link.click();
}

/**
 * Función para validar que solo seleccione una requisición, y las seleccionadas las agrega al array
 * @return {[type]} [description]
 */
function fnValidarProcesoCambiarEstatus() {
    // Comparar check Seleccionados
    var estatus = "";
    estatusDiferentes = 0;
    seleccionoCaptura = 0;
    dataJsonNoCapturaSeleccionados = new Array();
    var numRegistros = 0;
    for (var key in dataJsonNoCaptura) {
        var check = $("#checkbox_"+dataJsonNoCaptura[key].orderno).prop('checked');
        if (check) {
            var obj = new Object();
            obj.orderno = dataJsonNoCaptura[key].orderno;
            obj.requisitionno = dataJsonNoCaptura[key].numerorequisicion;
            obj.urlProceso = dataJsonNoCaptura[key].urlProceso;
            dataJsonNoCapturaSeleccionados.push(obj);
            seleccionoCaptura = 1;
            numRegistros ++;
        }
    }
    
    if (numRegistros > 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensajeEstatusDiferentes);
    }
}

/**
 * Función para obtener los registros seleccionados en la tabla
 * @return {[type]} Json con información seleccionada
 */
function fnObtenerDatosSeleccionados() {
    // Funcion para obtener los renglones seleccionados de la tabla
    var dataJsonNoCapturaSeleccionados = new Array();

    var griddata = $('#divTabla > #divCatalogo').jqxGrid('getdatainformation');
    for (var i = 0; i < griddata.rowscount; i++) {
        var id = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'id1');
        if (id == true) {
            var obj = new Object();
            obj.orderno = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'orderno');
            obj.requisitionno = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'numerorequisicion');
            obj.urlProceso = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'urlProceso');
            obj.idproveedor = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'idproveedor');
            obj.nombreproveedor = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'nombreproveedor');
            obj.ordencompra = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'ordencompra');
            
            dataJsonNoCapturaSeleccionados.push(obj);
        }
    }

    return dataJsonNoCapturaSeleccionados;
}

/**
 * Función que trae los botones configurados para las operaciones
 * @param  string divMostrar Mandar el nombre del div en el cual se van a pintar los botones
 * @return {[type]}            [description]
 */
function fnObtenerBotones(divMostrar) {
    //Opcion para operacion
    dataObj = { 
            option: 'obtenerBotones',
            type: ''
          };
    $.ajax({
          method: "POST",
          dataType:"json",
          url: "modelo/panel_recepcion_compra_modelo.php",
          data:dataObj
      })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            info = data.contenido.datos;
            // dataObjDatosBotones = data.contenido.datos;
            //console.log("presupuesto: "+JSON.stringify(info));
            var contenido = '';
            for (var key in info) {
                var funciones = '';
                if (info[key].statusid == 99) {
                    // Funcion para reversar
                    funciones = 'fnConfirmacionReversarOrdenCompra(\''+info[key].statusid+'\', \''+info[key].namebutton+'\')';
                } else {
                    funciones = 'fnConfirmacionOrdenCompra(\''+info[key].statusid+'\', \''+info[key].namebutton+'\')';
                }
                contenido += '&nbsp;&nbsp;&nbsp; <component-button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
            }
            $('#'+divMostrar).append(contenido);
            fnEjecutarVueGeneral('divBotones');
        }else{
            muestraMensaje('No se obtuvieron los botones para realizar las operaciones', 3, 'divMensajeOperacion', 5000);
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}
