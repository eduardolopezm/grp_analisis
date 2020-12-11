/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de Agosto del 2017
 */
$(document).ready(function() {
    fnIniciaPanel();
    // Datos botones
    fnObtenerBotones_Funcion('divBotones', $("#Panelviaticos").data("funcion"));
    // prevencion de envio por submit
    $(document).on('click', 'button', function(e) { e.preventDefault(); });
    // comportamiento de buscar
    $('#btnSearch').on('click',function(){
        muestraCargandoGeneral();
        setTimeout(function(){
            var params = getParams('form-search');
            params.method = 'getSolicitudes';
            //muestraPrevioCargandoGeneral()
            $.ajaxSetup({async: false});
            $.post(window.urlGrp+'/modelo/viaticosModelo.php', params)
            .then(function(res){
                // limpiado de la tabla
                fnLlenaTabla();
                // llenado de la tabla
                window.tblDatosRoot = res.content;

                /*var output = '';
                for (var property in window.tblDatosRoot) {
                    output += property + ': ' + window.tblDatosRoot[property]+'; ';
                }
                console.log(output); */

                if(res.success){ fnLlenaTabla(tblDatosRoot); }
            });
        }, 500);
    });
    // se obtiene los datos de la solicitudes terminadas y en comprobacion
    $('#btnSearchTerminadas').on('click', function(){
        muestraCargandoGeneral();
        setTimeout(function(){
            var params = getParams('form-search-terminadas');
            params.method = 'obtenSolicitudesTerminadas';
            // params.selectEstatus = '5';
            //muestraPrevioCargandoGeneral()
            $.ajaxSetup({async: false});
            $.post(window.urlGrp+'/modelo/viaticosModelo.php', params)
            .then(function(res){
                // limpiado de la tabla
                fnLlenaTabla();
                // llenado de la tabla
                window.tblDatosRootTermiandas = res.content;
                if(res.success){ fnLlenaTablaTerminado(tblDatosRootTermiandas); }
            });
        }, 500);
    });
    // nuevo oficio de comision
    $('#btnNuevoficio').on('click', function() { window.location.href = 'altaOficioComision.php'; });
    // comprotamiento del cambio de fecha
    $('#form-search .componenteFeriadoAtras').on('dp.change',function(e){
        var $that = $(this),
            $input = $that.find('input[id]'),
            $fechaTermino = $('#form-search .componenteFeriadoAtras').eq(1);
        if($input.attr('id') == 'fechaIni'){
            window.minDate = e;
            $fechaTermino.data('DateTimePicker').minDate(window.minDate.date);
        }else if($input.attr('id') == 'fechaFin'){
            var fechaDesdeVal = $('#form-search #fechaIni').val(), fechaHastaVal = $('#form-search #fechaFin').val(), formatoDate = e.date._f;
            var fechaDesde = moment(fechaDesdeVal, formatoDate), fechaHasta = moment(fechaHastaVal, formatoDate);
            diferencias = fechaHasta.diff(fechaDesde,'days');
            if(diferencias < 0){
                muestraModalGeneral(3,window.tituloGeneral,'La fecha <strong> Hasta </strong>, no puede ser <strong>menor</strong> a la fecha <strong> Desde</strong>');
                $fechaTermino.find('input[id]').val(e.date._i);
                return false;
            }
        }
    });
    $('#form-search-terminadas .componenteFeriadoAtras').on('dp.change',function(e){
        var $that = $(this),
            $input = $that.find('input[id]'),
            $fechaTermino = $('#form-search-terminadas .componenteFeriadoAtras').eq(1);
        if($input.attr('id') == 'fechaIni'){
            window.minDate = e;
            $fechaTermino.data('DateTimePicker').minDate(window.minDate.date);
        }else if($input.attr('id') == 'fechaFin'){
            var fechaDesdeVal = $('#form-search-terminadas #fechaIni').val(), fechaHastaVal = $('#form-search-terminadas #fechaFin').val(), formatoDate = e.date._f;
            var fechaDesde = moment(fechaDesdeVal, formatoDate), fechaHasta = moment(fechaHastaVal, formatoDate);
            diferencias = fechaHasta.diff(fechaDesde,'days');
            console.log(diferencias, fechaHasta, fechaDesde);
            if(diferencias < 0){
                muestraModalGeneral(3,window.tituloGeneral,'La fecha <strong> Hasta </strong>, no puede ser <strong>menor</strong> a la fecha <strong> Desde</strong>');
                $fechaTermino.find('input[id]').val(e.date._i);
                return false;
            }
        }
    });
});

function fnIniciaPanel() {
    console.log('panel listo');
    window.propiedadesResize = {
                                    widthsEspecificos: {
                                        check:      "5%",
                                        ur:         "10%",
                                        ue:         "10%",
                                        solicitud:  "12%",
                                        imprimir:   "7%"
                                    },
                                    encabezadosADosRenglones: {
                                        solicitud:          "Oficio<br />No",
                                        fechaElaboracion:   "Fecha<br />Elaboración",
                                        fechaInicio:        "Fecha<br />Inicio",
                                        fechaFin:           "Fecha<br />Fin",
                                        tipoGasto:          "Tipo de<br />Viático",
                                        tipoSol:            "Tipo de<br />Comisión"
                                    },
                                    camposConWidthAdicional: [
                                        "objetivo"
                                    ]
                                };
    var url = window.location.href.split('/');
    url.splice(url.length - 1);
    window.urlGrp = url.join('/');
    var prefix = url.join('/') + '/modelo/viaticosModelo.php';
    window.tblDatosRoot = [];
    window.tblDatosRootTermiandas = [];
    //setAjaxSetup();
    fnLlenaTabla();
    fnLlenaTablaTerminado();
    llenarSelect(prefix);// contiene el autocarga
    window.tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

    // carga de botones de solicitudes sin terminar
    fnObtenerBotones_Funcion('areaBotones',nuFuncion);
    // comportamiento de botones de entrada
    $(document).on('click','#Rechazar, #Avanzar, #Autorizar, #Cancelar, #Solicitar',changeNewStatus);

    // comportamiento de botones de comprobación
    $(document).on('click','#rechazarTerminada, #avanzarTerminada, #autorizarTerminada, #cancelarTerminada',changeNewStatusTerminada);

    // solicitud de ejecución de cron para actualización de las solicitudes conforme al usuario logeado
    $.ajaxSetup({async: false});
    $.post(prefix,{ method:'cronSolicitudes' }).then(function(res){ console.log(res); });
}

function fnLlenaTabla(data=[]) {
    var el='datosViaticos', tabla='tablaViaticos', visualcolumn = [0,2,3,4,5,6,7,8,9,10,11,12,16], columntoexcel = [1,3,4,5,6,7,8,9,10,11,12],
        nameexcel='solicitud viaticos', datafields = '', columns ='';
    // datos y comportamiento de los datos
    datafields = [
        { name: 'check', type: 'bool'},
        { name: 'solicitud', type: 'string' },
        { name: 'fechaElaboracion', type: 'string' },
        { name: 'ur', type: 'string' },
        { name: 'ue', type: 'string' },
        { name: 'fechaInicio', type: 'string' },
        { name: 'fechaFin', type: 'string' },
        { name: 'objetivo', type: 'string' },
        { name: 'tipoGasto', type: 'string' },
        { name: 'tipoSol', type: 'string' },
        { name: 'monto', type: 'number' },// 7
        { name: 'status', type: 'string' },// 7
        { name: 'idfolio', type: 'number' },// 8
        { name: 'idStatus', type: 'number' },// 9
        { name: 'solString', type: 'string' },// 10
        { name: 'nUR', type: 'string' },// 11
        { name: 'nUE', type: 'string' },// 11
        { name: 'imprimir', type: 'string' }// 11
    ];

    //[{"solicitud":1,"fechaElaboracion":1,"ur":1,"fechaInicio":1,"fechaFin":1,"objetivo":1,"monto":1,"status":1,"nUR":1,"iFolio":1}]
    // titulos y estilos de las columnas
    columns = [
        { text:'Sel', datafield:'check', columntype: 'checkbox', width: '5%', cellsalign: 'center', align: 'center' },
        { text: 'Oficio No', datafield: 'solString', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'Oficio No', datafield: 'solicitud', editable: false, width: '15%', cellsalign: 'center', align: 'center' },
        
        
        { text: 'Fecha Elaboración', datafield: 'fechaElaboracion', editable: false, width: '15%', cellsalign: 'center', align: 'center' },
        { text: 'UR', datafield: 'ur', editable: false, width: '13%', cellsalign: 'center', align: 'center' },
        { text: 'UE', datafield: 'ue', editable: false, width: '13%', cellsalign: 'center', align: 'center' },
        { text: 'Fecha Inicio', datafield: 'fechaInicio', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Fecha Fin', datafield: 'fechaFin', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Objetivo', datafield: 'objetivo', editable: false, width: '30%', cellsalign: 'center', align: 'center' },
        { text: 'Tipo de Viático', datafield: 'tipoGasto', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Tipo de Comisión', datafield: 'tipoSol', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Monto', datafield: 'monto', editable: false, width: '10%', cellsalign: 'center', align: 'center', cellsformat: 'C2' },
        { text: 'Estatus', datafield: 'status', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        // columnas ocueltas
        { text: 'folioSol', datafield: 'idfolio', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'statusSol', datafield: 'idStatus', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'nUR', datafield: 'nUR', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'nUE', datafield: 'nUE', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'Imprimir', datafield: 'imprimir', editable: false, width: '10%', cellsalign: 'center', align: 'center' }
    ];

    // llamado de limpiesa de la tabla
    fnLimpiarTabla(el,tabla);
    // renderisado de la tabla
    /*if(data.length > 0) {
       for (var i = 0; i < data.length; i++) {
            newValue = "<a id='"+data[i].idfolio+ "' href='altaOficioComision.php?idFolio="+data[i].idfolio+"'> <u>"+data[i].solicitud+"</u></a>";
            data[i].solicitud = newValue;
       }

    }*/
    fnAgregarGrid_Detalle_nostring(data, datafields, columns, tabla, ' ', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);

    // resize del ancho de las columnas del Grid cada vez que hay cambio de página
    $("#"+tabla).bind("pagechanged", function (event) {
        if(typeof window.propiedadesResize != "undefined"){
            fnGridResize(tabla,window.propiedadesResize);
        }
    });
    if(typeof window.propiedadesResize != "undefined"){
        if($("#"+tabla).is(":visible")){
            fnGridResize(tabla,window.propiedadesResize);
        }
    }

    if(data.length){
        ocultaCargandoGeneralConTimeOut();
    }
}

function fnLlenaTablaTerminado(data=[]) {
    var el='datosViaticosTerminado', tabla='tablaViaticosTerminado', visualcolumn = [0,2,3,4,5,6,7,8,9,10,11,12,16], columntoexcel = [1,3,4,5,6,7,8,9,10,11,12],
        nameexcel='solicitud viaticos Terminadas', datafields = '', columns ='';
    // datos y comportamiento de los datos
        // { name: 'check', type: 'bool'},
    datafields = [
        { name: 'solicitud', type: 'string' },
        { name: 'fechaElaboracion', type: 'string' },
        { name: 'ur', type: 'string' },
        { name: 'ue', type: 'string' },
        { name: 'fechaInicio', type: 'string' },
        { name: 'fechaFin', type: 'string' },
        { name: 'objetivo', type: 'string' },
        { name: 'tipoGasto', type: 'string' },
        { name: 'tipoSol', type: 'string' },
        { name: 'monto', type: 'number' },// 7
        { name: 'montoComprobado', type: 'number' },// 7
        { name: 'status', type: 'string' },// 7
        { name: 'idfolio', type: 'number' },// 8
        { name: 'idStatus', type: 'number' },// 9
        { name: 'solString', type: 'string' },// 10
        { name: 'nUR', type: 'string' },// 11
        { name: 'nUE', type: 'string' },// 12
        { name: 'imprimir', type: 'string' },// 13
        
    ];

    //[{"solicitud":1,"fechaElaboracion":1,"ur":1,"fechaInicio":1,"fechaFin":1,"objetivo":1,"monto":1,"status":1,"nUR":1,"iFolio":1}]
    // titulos y estilos de las columnas
        // { text:'Sel', datafield:'check', columntype: 'checkbox', width: '3%', cellsalign: 'center', align: 'center' },
    columns = [
        { text:'Sel', datafield:'check', columntype: 'checkbox', width: '5%', cellsalign: 'center', align: 'center' },
        { text: 'Oficio No', datafield: 'solString', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'Oficio No', datafield: 'solicitud', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
   
        
        { text: 'Fecha Elaboración', datafield: 'fechaElaboracion', editable: false, width: '15%', cellsalign: 'center', align: 'center' },
        { text: 'UR', datafield: 'ur', editable: false, width: '13%', cellsalign: 'center', align: 'center' },
        { text: 'UE', datafield: 'ue', editable: false, width: '13%', cellsalign: 'center', align: 'center' },
        { text: 'Fecha Inicio', datafield: 'fechaInicio', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Fecha Fin', datafield: 'fechaFin', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Objetivo', datafield: 'objetivo', editable: false, width: '30%', cellsalign: 'justify', align: 'center' },
        { text: 'Tipo de Viático', datafield: 'tipoGasto', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Tipo de Comisión', datafield: 'tipoSol', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        { text: 'Monto', datafield: 'monto', editable: false, width: '10%', cellsalign: 'center', align: 'center', cellsformat: 'C2' },
        { text: 'Monto Comprobado', datafield: 'montoComprobado', editable: false, width: '10%', cellsalign: 'center', align: 'center', cellsformat: 'C2' },
        { text: 'Estatus', datafield: 'status', editable: false, width: '10%', cellsalign: 'center', align: 'center' },
        // columnas ocueltas
        { text: 'folioSol', datafield: 'idfolio', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'statusSol', datafield: 'idStatus', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'nUR', datafield: 'nUR', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'nUE', datafield: 'nUE', editable: false, cellsalign: 'center', hidden : true, align: 'center' },
        { text: 'Imprimir', datafield: 'imprimir', editable: false, width: '10%', cellsalign: 'center', align: 'center' }
    ];

    // llamado de limpiesa de la tabla
    fnLimpiarTabla(el,tabla);
    // renderisado de la tabla
    fnAgregarGrid_Detalle_nostring(data, datafields, columns, tabla, ' ', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);

    // resize del ancho de las columnas del Grid cada vez que hay cambio de página
    $("#"+tabla).bind("pagechanged", function (event) {
        if(typeof window.propiedadesResize != "undefined"){
            fnGridResize(tabla,window.propiedadesResize);
        }
    });
    if(typeof window.propiedadesResize != "undefined"){
        if($("#"+tabla).is(":visible")){
            fnGridResize(tabla,window.propiedadesResize);
        }
    }

    if(data.length){
        ocultaCargandoGeneralConTimeOut();
    }
}
/**
 * llenar select de estatus
 */
function llenarSelect(prefix) {
    params = {
        method: 'llenarEstatus'
    }
    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: prefix,
        data: params,
        async: false
    }).done(function(res) {
        // $('#selectEstatus').multiselect('dataprovider', res.content);
        $('.selectEstatus').multiselect('dataprovider', res.content);
        $('.selectEstatusTerminadas').multiselect('dataprovider', res.contentTerminadas);
        autoCarga();
    });
}

function autoCarga() {
    var params = getParams('form-search');
        params.method = 'getSolicitudes';
    // muestraPrevioCargandoGeneral()
    $.ajaxSetup({async: false});
    $.post(window.urlGrp+'/modelo/viaticosModelo.php', params)
    .then(function(res){
        // limpiado de la tabla
        fnLlenaTabla();
        // llenado de la tabla
        window.tblDatosRoot = res.content;
        if(res.success){ fnLlenaTabla(tblDatosRoot); }
        window.profile = res.profile;
        window.noPermitidos = res.noPermitidos;
    });
    // carga de la información de solicitudes comprobadas
    var paramsTerminado = getParams('form-search-terminadas');
        paramsTerminado.method = 'obtenSolicitudesTerminadas';
        // paramsTerminado.selectEstatus = '5';
    $.ajaxSetup({async: false});
    $.post(window.urlGrp+'/modelo/viaticosModelo.php', paramsTerminado)
    .then(function(res){
        if(res.content.length != 0){
            window.tblDatosRootTermiandas = res.content;
            fnLlenaTablaTerminado(res.content);
        }
    });
}

function setAjaxSetup() {
    // se define el comportamiento del evento de inicio y termino de tranzacción de ajax
    $(document).ajaxStart(muestraCargandoGeneral);
    $(document).ajaxStop(ocultaCargandoGeneral);
}

function changeNewStatus() {
    var el = $(this), selections = getSelects('tablaViaticos'), $flag = 0;
    var nombresSeleccion = getNombreSeleccion(selections,'solString');
    var mensajeEstandarizado = 'la'+( selections.length>1 ? 's' : '' )+' solicitud'+( selections.length>1 ? 'es' : '' )+' seleccionada'+( selections.length>1 ? 's' : '' );
    if(selections.length!=0){
        if(el.attr('id') == 'Solicitar'){
            var noChange = [4,5,6,7,8];
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(row.idStatus)!==-1){
                    $flag++;
                    muestraModalGeneral(3,window.tituloGeneral,'Los anexos con estatus En Comisión, Por Comprobar, Comprobado, Cancelado y Autorizado no pueden ser Autorizados.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if($flag == 0){
                var titulo = 'Confirmación', mensaje = '¿Estás seguro de autorizar '+mensajeEstandarizado+'?';
                // var titulo = 'Confirmación', mensaje = '¿Estás seguro de autorizar '+mensajeEstandarizado+'?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','autorizaGeneral()');
            }
        }
        // cancelacion
        else if(el.attr('id') == 'Cancelar'){
            var noCancel = window.noPermitidos, $flag=0;
            $.each(selections, function(ind, row) {
                if(noCancel.indexOf(row.idStatus)!==-1){
                    $flag++;
                    muestraModalGeneral(3,window.tituloGeneral,'No cuenta con los permisos para cancelar los anexos solicitados.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if($flag == 0){
                var titulo = 'Confirmación', mensaje = '¿Estás seguro de cancelar '+mensajeEstandarizado+'?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','cancelaGeneral()');
            }
        }
        // rechazo
        else if(el.attr('id') == 'Rechazar'){
            var noChange = window.noPermitidos, flag = 0;
            noChange.push(1);
            $.each(selections, function(index, row) {
                if(noChange.indexOf(row.idStatus)!==-1){
                    flag++;
                    muestraModalGeneral(3,window.tituloGeneral,'No puede rechazar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if(flag==0){
                var titulo = 'Confirmación', mensaje = '¿Estás seguro de rechazar '+mensajeEstandarizado+'?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','rechazaGeneral()');
                noChange.splice(noChange.indexOf(1),1);
            }
        }
        // avnazar
        else if(el.attr('id') == 'Avanzar'){
            var noChange = window.noPermitidos, flag = 0;
            $.each(selections, function(index, row) {
                if(noChange.indexOf(row.idStatus)!==-1){
                    flag++;
                    muestraModalGeneral(3,window.tituloGeneral,'No puede avanzar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if(flag==0){
                var titulo = 'Confirmación', mensaje = '¿Estás seguro de avanzar '+mensajeEstandarizado+'?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','avanzarGeneral()');
            }
        }
    }else{
        muestraModalGeneral(3,window.tituloGeneral,'No ha seleccionado ningún elemento.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
    }
}

function changeNewStatusTerminada() {
    var el = $(this), selections = getSelects('tablaViaticosTerminado'), $flag = 0, ids = [], titulo = 'Confirmación';
    var mensajeEstandarizado = 'la'+( selections.length>1 ? 's' : '' )+' solicitud'+( selections.length>1 ? 'es' : '' )+' seleccionada'+( selections.length>1 ? 's' : '' );
    // comprobación de datos seleccionados
    if(selections.length==0){
        muestraModalGeneral(3,window.tituloGeneral,'No ha seleccionado ningún elemento.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
        return false;
    }

    // acciones en caso de autorización
    if(el.attr('id') == 'autorizarTerminada'){
        var noChange = [4,6,7], mensaje = '¿Estás seguro de autorizar '+mensajeEstandarizado+'?';
        $.each(selections, function(ind, row) { ids.push(row.idfolio); if(noChange.indexOf(row.idStatus)!==-1){ $flag++; } });
        if($flag!=0){
            muestraModalGeneral(3,window.tituloGeneral,'Los anexos con estatus Por Asignar, Asignados y Cancelados no pueden ser Autorizados.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            return false;
        }
        muestraModalGeneralConfirmacion(3,titulo,mensaje,'','autorizaTerminadasGeneral()');
        if(ids.length){
            muestraCargandoGeneral();
            params = {
                ids: ids,
                method: 'montosAComprobar'
            };
            $.ajaxSetup({async: false});
            $.post(window.urlGrp+'/modelo/viaticosModelo.php', params)
            .done(function(res) {
                ocultaCargandoGeneral();
                if(res.success){
                    muestraModalGeneralConfirmacion(3,titulo,res.msg+"<br>¿Desea continuar?",'','autorizaTerminadasGeneral()');
                    return false;
                }
            });
        }
    }
    // cancelación
    else if(el.attr('id') == 'cancelarTerminada'){
        var noCancel = [6,7],mensaje = '¿Estás seguro de cancelar '+mensajeEstandarizado+'?';
        // comprobación
        $.each(selections, function(ind, row) { if(noCancel.indexOf(row.idStatus)!==-1){ $flag++; } });
        // envio de error
        if($flag != 0){
            muestraModalGeneral(3,window.tituloGeneral,'No cuenta con los permisos para cancelar los anexos solicitados.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            return false;
        }
        // envio de mensaje confirmación
        muestraModalGeneralConfirmacion(3,titulo,mensaje,'','cancelaGeneral(&apos;tablaViaticosTerminado&apos;)');
    }
    // rechazo
    else if(el.attr('id') == 'rechazarTerminada'){
        var noChange = [5,7], estotusPerfil = profile==11?6:(profile==10?10:5), mensaje = '¿Estás seguro de rechazar '+mensajeEstandarizado+'?';
        noChange.push(estotusPerfil);
        // comprobación
        $.each(selections, function(index, row) { if(noChange.indexOf(row.idStatus)!==-1){ $flag++; } });
        // envío de error
        if($flag != 0){
            muestraModalGeneral(3,window.tituloGeneral,'No puede rechazar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            return false;
        }
        // envío confirmación
        muestraModalGeneralConfirmacion(3,titulo,mensaje,'','rechazaTerminadasGeneral()');
    }
    // avnazar
    else if(el.attr('id') == 'avanzarTerminada'){
        var noChange = [6,7], mensaje = '¿Estás seguro de avanzar '+mensajeEstandarizado+'?',
            psh = profile==9?9:profile==10?10:11;
        noChange.push(psh);
        // comprobación
        $.each(selections, function(index, row) { if(noChange.indexOf(row.idStatus)!==-1){ $flag++; } });
        // envío de error
        if($flag){
            muestraModalGeneral(3,window.tituloGeneral,'No puede avanzar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            return false;
        }
        // envío de confirmación
        muestraModalGeneralConfirmacion(3,titulo,mensaje,'','avanzarTerminadasGeneral()');
    }
}

function getSelects(tbl,filedata='check') {
    var $tbl = $('#'+tbl), rows = [], len = i=0, infTbl;
    infTbl =  $tbl.jqxGrid('getdatainformation');
    len  = infTbl.rowscount;
    for (;i<len;i++) {
        var data = $tbl.jqxGrid('getrowdata',i);
        if(data[filedata]){ rows.push(data); }
    }
    return rows;
}

function getNombreSeleccion(datos, filedata='id') {
    var nombres = '';
    $.each(datos, function(index, val) {
        nombres += (index!=0?', ':'') + val[filedata];
    });
    return nombres;
}

function avanzarGeneral() {
    var selections = getSelects('tablaViaticos');
    /////muestraPrevioCargandoGeneral();
    var estotusPerfil = profile==9?2:(profile==10?3:(profile==11?8:8));
    var comprometido = estotusPerfil==8?1:0;
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type:estotusPerfil, tipoCambio : (profile==8 ? 1 : 0), comprometido: comprometido})
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        if(res.success){
            var tipoAl = 1, mensaje='', links = res.links, nuevoEstatus = res.nuevoEstatus;
            selections.forEach(function(el){
                mensaje += 'Se avanzó la solicitud '+el.solString+' '+(profile==9?'al Validador.':profile==10?'al Autorizador':'')+'<br />';
                tblDatosRoot[el.boundindex].idStatus = nuevoEstatus[el.idfolio][1];
                tblDatosRoot[el.boundindex].status = nuevoEstatus[el.idfolio][0];
                tblDatosRoot[el.boundindex].solicitud = links[el.idfolio];
            });
            fnLlenaTabla(tblDatosRoot);
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
        }
    });
}

function rechazaGeneral() {
    var selections = getSelects('tablaViaticos');
    /////muestraPrevioCargandoGeneral()
    var estotusPerfil = profile==11?2:(profile==10?1:1);
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type:estotusPerfil, tipoCambio:0})
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        var mensaje = '';
        if(res.success){
            var links = res.links, nuevoEstatus = res.nuevoEstatus;
            selections.forEach(function(el){
                mensaje += 'Se ha rechazado la solicitud '+el.solString+' '+(profile==11?'al Validador.':profile==10?'al Capturista':'')+'<br />';
                tblDatosRoot[el.boundindex].idStatus = nuevoEstatus[el.idfolio][1];
                tblDatosRoot[el.boundindex].status = nuevoEstatus[el.idfolio][0];
                tblDatosRoot[el.boundindex].solicitud = links[el.idfolio];
            });
            fnLlenaTabla(tblDatosRoot);
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
        }
    });
}

function cancelaGeneral(tbl='tablaViaticos') {
    var selections = getSelects(tbl);
    /////muestraPrevioCargandoGeneral();
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type: 7, tipoCambio:0, comprometido:0})
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        if(res.success){
            var links = res.links, mensaje='';
            if(tbl=='tablaViaticos'){
                selections.forEach(function(el){
                    mensaje += 'Se ha cancelado la solicitud '+el.solString+'. <br />';
                    tblDatosRoot[el.boundindex].idStatus = 7;
                    tblDatosRoot[el.boundindex].status = 'Cancelado';
                    tblDatosRoot[el.boundindex].solicitud = links[el.idfolio];
                });
                fnLlenaTabla(tblDatosRoot);
            }
            else{
                selections.forEach(function(el){
                    mensaje += 'Se ha cancelado la solicitud '+el.solString+'. <br />';
                    tblDatosRootTermiandas[el.boundindex].idStatus = 7;
                    tblDatosRootTermiandas[el.boundindex].status = 'Cancelado';
                    tblDatosRootTermiandas[el.boundindex].solicitud = links[el.idfolio];
                });
                fnLlenaTablaTerminado(tblDatosRootTermiandas);
            }
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
        }
    });
}

function autorizaGeneral() {
    var selections = getSelects('tablaViaticos');
    /////muestraPrevioCargandoGeneral();
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type: 8, tipoCambio: 1, comprometido:1 })
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        if(res.success){
            var links = res.links, mensaje='';
            selections.forEach(function(el){
                tblDatosRoot[el.boundindex].idStatus = 8;
                tblDatosRoot[el.boundindex].status = 'Solicitar';
                tblDatosRoot[el.boundindex].solicitud = links[el.idfolio];
                mensaje += 'La solicitud '+el.solString+' ha sido autorizada. <br />';
            });
            //fnLlenaTabla(tblDatosRoot);
            //// Código para cambiar el estatus de las autorizadas a por comprobar, copiado de fnIniciaPanel()
            var url = window.location.href.split('/');
            url.splice(url.length - 1);
            window.urlGrp = url.join('/');
            var prefix = url.join('/') + '/modelo/viaticosModelo.php';
            $.ajaxSetup({async: false});
            $.post(prefix,{ method:'cronSolicitudes' }).then(function(res){ console.log(res); });
            //// Código para cambiar el estatus de las autorizadas a por comprobar
            fnLlenaTabla();
            // muestraModalGeneral(3,'Operacion Exitosa','Se realizaron los cambios solicitados','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
        }else{
            muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
        }
    });
}

/********************************** MOVIMIENTOS DE COMPROBACIÓN **********************************/

function autorizaTerminadasGeneral() {
    var selections = getSelects('tablaViaticosTerminado');
    /////muestraPrevioCargandoGeneral();
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type: 6, tipoCambio: 0 })
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        if(res.success){
            var links = res.links, mensaje='';
            selections.forEach(function(el){
                tblDatosRootTermiandas[el.boundindex].idStatus = 6;
                tblDatosRootTermiandas[el.boundindex].status = 'Comprobado';
                tblDatosRootTermiandas[el.boundindex].solicitud = el.solString;
                mensaje += 'La solicitud '+el.solString+' ha sido completada. <br />';
            });
            fnLlenaTablaTerminado(tblDatosRootTermiandas);
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
        }else{
            muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
        }
    });
}

function rechazaTerminadasGeneral() {
    var selections = getSelects('tablaViaticosTerminado');
    /////muestraPrevioCargandoGeneral()
    var estotusPerfil = profile==11?9:(profile==10?5:5);
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type:estotusPerfil, tipoCambio:0})
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        var mensaje = '';
        if(res.success){
            var links = res.links, nuevoEstatus = res.nuevoEstatus;
            selections.forEach(function(el){
                mensaje += 'Se ha rechazado la solictud '+el.solString+' '+(profile==11?'al Validador.':profile==10?'al Capturista':'')+'<br />';
                tblDatosRootTermiandas[el.boundindex].idStatus = nuevoEstatus[el.idfolio][1];
                tblDatosRootTermiandas[el.boundindex].status = nuevoEstatus[el.idfolio][0];
                tblDatosRootTermiandas[el.boundindex].solicitud = links[el.idfolio];
            });
            fnLlenaTablaTerminado(tblDatosRootTermiandas);
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
        }
    });
}

function avanzarTerminadasGeneral() {
    var selections = getSelects('tablaViaticosTerminado');
    /////muestraPrevioCargandoGeneral();
    var estotusPerfil = (profile==9||profile==10)? profile : 6;
    $.ajaxSetup({async: false});
    $.post('modelo/viaticosModelo.php', {method:'updateStatus', rows:selections, type:estotusPerfil, tipoCambio : 0})
    .then(function(res){
        /////ocultaCargandoGeneral();
        /////$('div').removeClass("modal-backdrop");
        if(res.success){
            var tipoAl = 1, mensaje='', links = res.links, nuevoEstatus = res.nuevoEstatus;
            selections.forEach(function(el){
                mensaje += 'Se avanzó la solicitud '+el.solString+' '+(profile==9?'al Validador.':profile==10?'al Autorizador':'')+'<br />';
                tblDatosRootTermiandas[el.boundindex].idStatus = nuevoEstatus[el.idfolio][1];
                tblDatosRootTermiandas[el.boundindex].status = nuevoEstatus[el.idfolio][0];
                tblDatosRootTermiandas[el.boundindex].solicitud = links[el.idfolio];
            });
            fnLlenaTablaTerminado(tblDatosRootTermiandas);
            muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
        }
    });
}

/****************
 */
function renderTable(data = []) {
    var el = 'datos',
        columntoexcel = [1, 2, 3, 4, 5],
        visualcolumn = [0, 1, 2, 3, 4, , 5],
        nameexcel = 'anexo tecnico',
        columndata = '',
        columnname = '';
    // datos y comportamiento de los datos
    columndata = [{
        name: 'check',
        type: 'bool'
    }, {
        name: 'folio',
        type: 'string'
    }, {
        name: 'fecha',
        type: 'string'
    }, {
        name: 'ur',
        type: 'string'
    }, {
        name: 'descripcion',
        type: 'string'
    }, {
        name: 'status',
        type: 'string'
    }, ];
    // titulos y estilos de las columnas
    columnname = [{
        text: 'Selcción',
        datafield: 'check',
        columntype: 'checkbox',
        width: '5%',
        cellsalign: 'center',
        align: 'center'
    }, {
        text: 'Folio',
        datafield: 'folio',
        editable: false,
        width: '8%',
        cellsalign: 'center',
        align: 'center'
    }, {
        text: 'Fecha',
        datafield: 'fecha',
        editable: false,
        width: '10%',
        cellsalign: 'center',
        align: 'center'
    }, {
        text: 'UR',
        datafield: 'ur',
        editable: false,
        width: '25%',
        cellsalign: 'center',
        align: 'center'
    }, {
        text: 'Descripción',
        datafield: 'descripcion',
        editable: false,
        width: '42%',
        cellsalign: 'center',
        align: 'center'
    }, {
        text: 'Estatus',
        datafield: 'status',
        editable: false,
        width: '10%',
        cellsalign: 'center',
        align: 'center'
    }, ];
    // renderisado de la tabla
    fnAgregarGrid_Detalle(data, JSON.stringify(columndata), JSON.stringify(columnname), el, '', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);
}

// sobre escritura
function fnCambiarEstatus() {}
function delFadein() {}

function muestraPrevioCargandoGeneral() {
    $('.modal-backdrop.fade').removeClass('modal-backdrop in');
    muestraCargandoGeneral();
}

function ocultaCargandoGeneralConTimeOut(){
    setTimeout(function(){ ocultaCargandoGeneral(); }, 500);
}
