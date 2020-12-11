/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de Agosto del 2017
 */
//
var url= "modelo/panel_ordenes_compra_modelo.php";

var dataJsonNoCaptura = new Array();
var dataJsonNoCapturaSeleccionados = new Array();
var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono mas de una Requisición. Para realizar la operación solo seleccionar una requisición</p>';
var mensajeSinNoCaptura = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin selección de Requisición</p>';

$(document).ready(function () {
    if (document.querySelector(".selectEstatusOC")) {
        dataObj = {
            option: 'mostrarEstatusOC'
        };
        fnSelectGeneralDatosAjax('.selectEstatusOC', dataObj, url, 0);
    }

    // Datos botones
    fnObtenerBotones('divBotones');

    $("#btnBuscarOrdenes").click(function() {
        muestraCargandoGeneral();

        var pFechaini = $("#txtFechaInicio").val(),
            pFechafin = $("#txtFechaFin").val(),
            pDependencia = $("#selectRazonSocial").val(),
            pUnidadResposable = $("#selectUnidadNegocio").val(),
            pRequisicion= $("#txtNumeroRequisicion").val(),
            txtFolioCompra= $("#txtFolioCompra").val(),
            pCodigoProveedor= $("#txtCodigoProveedor").val(),
            pNombreProveedor= $("#txtNombreProveedor").val(),
            pEstatus= $("#selEstatusRequisicion").val();
            pFuncion= $("#PanelOrdenesCompra").data("funcion");

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
            folioCompra: txtFolioCompra,
            idproveedor: pCodigoProveedor,
            nomproveedor: pNombreProveedor,
            estatus: pEstatus,
            codigoExpediente: $("#codigoExpediente").val(),
            funcion: pFuncion,
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
            fnLimpiarTabla('divTabla', 'divCatalogo');

            if (data.result) {
                // Columnas para el GRID
    		    columnasNombres += "[";
    		    // columnasNombres += "{ name: 'seleccionar', type: 'string' },";
                columnasNombres += "{ name: 'id1', type: 'bool'},";
                columnasNombres += "{ name: 'numerorequisicion', type: 'string' },";
                columnasNombres += "{ name: 'idrequisicion', type: 'string' },";
                columnasNombres += "{ name: 'ordencompra', type: 'string' },";
    		    columnasNombres += "{ name: 'idproveedor', type: 'string' },";
    		    columnasNombres += "{ name: 'nombreproveedor', type: 'string' },";
    		    columnasNombres += "{ name: 'estatus', type: 'string' },";
    		    columnasNombres += "{ name: 'totalrequisicion', type: 'number' },";
                columnasNombres += "{ name: 'totalrequisicion2', type: 'string' },";
                columnasNombres += "{ name: 'fechaCaptura', type: 'string' },";
                columnasNombres += "{ name: 'fecharequerida', type: 'string' },";
                columnasNombres += "{ name: 'observaciones', type: 'string' },";
                columnasNombres += "{ name: 'imprimir', type: 'string' },";
                columnasNombres += "{ name: 'orderno', type: 'string' },";
                columnasNombres += "{ name: 'ligaGenerarOC', type: 'string' }";
                //columnasNombres += "{ name: 'compra', type: 'string' }";
    		    columnasNombres += "]";

    		    // Columnas para el GRID
    		    columnasNombresGrid += "[";
                // columnasNombresGrid += " { text: 'Sel', datafield: 'seleccionar', width: '3%', cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: '', datafield: 'id1', width: '3%', editable: true, cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
                columnasNombresGrid += " { text: 'Requisición', datafield: 'numerorequisicion', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true },";
                columnasNombresGrid += " { text: 'Requisición', datafield: 'idrequisicion', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Compra', datafield: 'ordencompra', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
    		    columnasNombresGrid += " { text: 'Código Proveedor', datafield: 'idproveedor', width: '6%', editable: false, hidden: false },";
    		    columnasNombresGrid += " { text: 'Nombre Proveedor', datafield: 'nombreproveedor', width: '16%', editable: false, cellsalign: 'left', align: 'center', hidden: false },";
    		    columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '9%', editable: false, cellsalign: 'center', align: 'center', hidden: false},";
    		    columnasNombresGrid += " { text: 'Total Requisición', datafield: 'totalrequisicion', width: '10%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: false"+colResumenTotal+"},";
                columnasNombresGrid += " { text: 'Total', datafield: 'totalrequisicion2', width: '10%', editable: false, cellsalign: 'right', align: 'center', cellsformat: 'C2', hidden: true},";
    			columnasNombresGrid += " { text: 'Fecha Cap.', datafield: 'fechaCaptura', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Fecha Req.', datafield: 'fecharequerida', width: '7%', editable: false, cellsalign: 'center', align: 'center', hidden: false },";
                columnasNombresGrid += " { text: 'Observaciones', datafield: 'observaciones', width: '23%', editable: false, cellsalign: 'left', align: 'center', hidden: false, rendered: tooltiprenderer },";
                columnasNombresGrid += " { text: 'Imprimir', datafield: 'imprimir', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer },";
                columnasNombresGrid += " { text: 'orderno', datafield: 'orderno', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true }, ";
                columnasNombresGrid += " { text: 'ligaGenerarOC', datafield: 'ligaGenerarOC', width: '5%', editable: false, cellsalign: 'center', align: 'center', hidden: true }";
                //columnasNombresGrid += " { text: 'Opción', datafield: 'compra', width: '7%', cellsalign: 'center', align: 'center', hidden: false, rendered: tooltiprenderer }";
                columnasNombresGrid += "]";

                // arreglo que guarda las columnas que se ocultan para exportar a excel
                var columnasExcel= [1, 3, 4, 5, 6, 8, 9, 10, 11];
                var columnasVisuales= [0, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12];

                info = data.contenido.datos;
                dataJsonNoCaptura = data.contenido.datos;
                var nombreExcel = data.contenido.nombreExcel;

                //fnAgregarGrid(info, columnasNombres, columnasNombresGrid, 'divCatalogo', 'nombreproveedor', 1);
                fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);
            }
            ocultaCargandoGeneral();
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
    });

    $("#btnBuscarOrdenes").click();
});

/**
 * Función para confirmar proceso de la orden de compra
 * @param  string namebutton Nombre del boton para mensaje
 * @return {[type]}            [description]
 */
function fnConfirmacionOrdenCompra(namebutton) {
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
        if (dataJsonNoCapturaSeleccionados[0].ligaGenerarOC != '') {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p>¿Está seguro de generar la orden de compra para la requisición '+dataJsonNoCapturaSeleccionados[0].requisitionno+'?</p>';
            muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnGenerarOrdenCompra()");
        } else {
            // Ya se ha generado la orden de compra
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La requisición '+dataJsonNoCapturaSeleccionados[0].requisitionno+' ya tiene la Orden de Compra '+dataJsonNoCapturaSeleccionados[0].realordeno+'</p>';
            muestraModalGeneral(3, titulo, mensaje);
        }
    }
}

/**
 * Función para confirmar proceso de rechazar
 * @param  string namebutton Nombre del boton para mensaje
 * @return {[type]}            [description]
 */
function fnConfirmacionRechazoOrdenCompra(namebutton, statusid) {
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
        // if (dataJsonNoCapturaSeleccionados[0].ligaGenerarOC != '') {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = "<p>¿Está seguro de rechazar  la requisición "+dataJsonNoCapturaSeleccionados[0].requisitionno+"?</p>";
            muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnRecharOrdenCompra('"+statusid+"')");
        // }
    }
}

/**
 * Función para inciar el proceso de compra
 * @return {[type]} [description]
 */
function fnGenerarOrdenCompra() {
    // Validar para rechazar
    dataObj = { 
        option: 'validarEstatusOrdenCompra',
        dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/panel_ordenes_compra_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            var link = document.getElementById("Link_NuevoGeneral");
            link.setAttribute("href", ""+dataJsonNoCapturaSeleccionados[0].ligaGenerarOC);
            link.click();
        }else{
            ocultaCargandoGeneral();
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
 * Función para validaciones de rechazo de la Orden de Compra
 * @param  String statusid Estatus para saber el proceso al que va a estar
 * @return {[type]}          [description]
 */
function fnRecharOrdenCompra(statusid) {
    // Validar para rechazar
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
        url: "modelo/panel_ordenes_compra_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            //dataJson = data.contenido.datos;
            //ocultaCargandoGeneral();
            fnRechazarOrdenesSeleccionadas(statusid);
        }else{
            ocultaCargandoGeneral();
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
 * Función para rechazar las requsiciones
 * @param  String statusid estatus del boton
 * @return {[type]}          [description]
 */
function fnRechazarOrdenesSeleccionadas(statusid) {
    dataObj = { 
        option: 'rechazarRequisiciones',
        dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
        statusid: statusid
    };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/panel_ordenes_compra_modelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            //dataJson = data.contenido.datos;
            //ocultaCargandoGeneral();
            $("#btnBuscarOrdenes").click();
        }else{
            //ocultaCargandoGeneral()
            $("#btnBuscarOrdenes").click();
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
            obj.ligaGenerarOC = dataJsonNoCaptura[key].ligaGenerarOC;
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
            obj.ligaGenerarOC = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'ligaGenerarOC');
            obj.realordeno = $('#divTabla > #divCatalogo').jqxGrid('getcellvalue', i, 'ordencompra');
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
          url: "modelo/panel_ordenes_compra_modelo.php",
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
                if (info[key].statusid == 1) {
                    funciones = 'fnConfirmacionOrdenCompra(\''+info[key].namebutton+'\')';
                } else{
                    funciones = 'fnConfirmacionRechazoOrdenCompra(\''+info[key].namebutton+'\', \''+info[key].statusid+'\')';
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