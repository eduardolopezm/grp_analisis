var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

$(document).ready(function (){
    fnObtenerRadicacion();
    fnCargarBotones(nuFuncion);

    $('#btnNuevo').click(function(){
        window.location.href="solicitudRadicacion.php";
    });

    $('#btn-search').click(function (){
        fnObtenerRadicacion();
    });

});

$(document).on('click','#btnSolicitar, #btnAvanzar, #btnAvanzarCapturista, #btnRechazar, #btnCancelar',changeNewStatus);

function changeNewStatus() {
    var el = $(this), selections = getSelects('divDatos','idCHK'), $flag = 0;
    var idPartidas = getNombreSeleccion(selections,'idPartida');
    var idFolio = getNombreSeleccion(selections,'idFolio');
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>'; 
    var varPorValidar = "";

    if(selections.length!=0){
        if(el.attr('id') == 'btnAvanzar' || el.attr('id') == 'btnAvanzarCapturista'){

            var noChange=[];
            if(permisoValidador =='1'){
                 noChange = [3,4,5,6];
            }else{
                noChange = [2,3,4,5,6]; 
                varPorValidar = "Por Validar,";
            }
            
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;
                    muestraModalGeneral(3,titulo,'La radicación con estatus '+varPorValidar+' Por Autorizar, Solicitado, Autorizado y/o Cancelado no se pueden avanzar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if($flag == 0){
                var titulo = 'Confirmación', mensaje = '';
                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';

                if(selections.length > 1){
                     mensaje = '¿Está seguro de avanzar las radicaciones seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de avanzar la radicación seleccionada?';
                }

                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusRadicacion(\''+idPartidas+'\',\'avanzar\',\''+idFolio+'\')');
            }
        }else if (el.attr('id') == 'btnRechazar') {
            var noChange = [1,4,5,6];
            var contadorPorAutorizar=0;
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){

                    if(permisoAutorizadorOficinaCentral<=0){
                        $flag++;
                        muestraModalGeneral(3,titulo,'La radicación con estatus Solicitado, Autorizado, Cancelado o Capturado no se pueden Rechazar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }
                }
                if(row.idEstatus == '3'){
                    contadorPorAutorizar++;
                }
            });

            if(contadorPorAutorizar >0){
                if(permisoValidador =='1'){
                    $flag++;
                    muestraModalGeneral(3,titulo,'No se pueden rechazar la radicacion con estatus Por Autorizar con un perfil Validador.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            }

            if($flag == 0){
                var mensaje = '';
                
                if(selections.length > 1){
                     mensaje = '¿Está seguro de rechazar las radicaciones seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de rechazar la radicación seleccionada?';
                }

                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusRadicacion(\''+idPartidas+'\',\'rechazar\',\''+idFolio+'\')');
            }
        }else if (el.attr('id') == 'btnSolicitar') {
            var noChange = [4,5,6];
            
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;
                    muestraModalGeneral(3,titulo,'La radicación con estatus  Solicitado, Autorizado o Cancelado no se pueden Solicitar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }

                // if(row.fecha_pago == ""){
                //     $flag++;
                //     muestraModalGeneral(3,titulo,'Es necesario la Fecha de Pago para solicitar','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                // }

            });
            
            if($flag == 0){
                var mensaje = '';

                if(selections.length > 1){
                     mensaje = '¿Está seguro de solicitar las radicaciones seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de solicitar la radicación seleccionada?';
                }
                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusRadicacion(\''+idPartidas+'\',\'solicitar\',\''+idFolio+'\')');
            }
        }else if (el.attr('id') == 'btnCancelar') {
            var noChange = [];

            if(permisoAutorizador == '1'){
                 noChange = [5,6];
            }else if(permisoValidador =='1'){
                noChange = [3,4,5,6]; 
            }else if(permisoCapturista =='1'){
                noChange = [2,3,4,5,6]; 
            }
            console.log('afuera');

            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;
                    console.log('1');
                    if(permisoAutorizador == '1'){
                        console.log('2');
                        muestraModalGeneral(3,titulo,'La ministración con estatus Autorizado y/o Cancelado no se pueden cancelar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }else if(permisoValidador =='1'){
                        console.log('3');
                        muestraModalGeneral(3,titulo,'La ministración con estatus  Por Autorizar, Solicitado, Autorizado y/o Cancelado no se pueden cancelar por el perfil Validador.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }else if(permisoCapturista =='1'){
                        console.log('4');
                        muestraModalGeneral(3,titulo,'La ministración con estatus Por Validar, Por Autorizar, Solicitado, Autorizado y/o Cancelado no se pueden cancelar por el perfil Capturista.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }
                    //muestraModalGeneral(3,titulo,'La ministración con estatus Solicitado, Por Autorizar, Autorizado y/o Cancelado no se pueden avanzar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });

            if($flag == 0){
                var mensaje = '';

                if(selections.length > 1){
                     mensaje = '¿Está seguro de cancelar las radicaciones seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de cancelar la radicación seleccionada?';
                }

                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusRadicacion(\''+idPartidas+'\',\'cancelar\',\''+idFolio+'\')');
            }
        }

    }else{
        muestraModalGeneral(3,tituloGeneral,'No ha seleccionado ningún elemento.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
    }
}

function fnModificarEstatusRadicacion(idPartida,accion,idFolio){
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/radicacionModelo.php",
        data:{
            option:'modificarEstatusRadicacion',
            accion:accion,
            idFolios:idFolio,
            Partidas:idPartida
        },
        async:false,
        cache:false
    }).done(function(data) {
        if (data.result) {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>'; 
            muestraModalGeneral(3,titulo,data.Mensaje);   
            fnObtenerRadicacion();
            ocultaCargandoGeneral();
        } else {
            ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    }); 
}

function getNombreSeleccion(datos, filedata='id') {
    var nombres = '';
    $.each(datos, function(index, val) {
        nombres += (index!=0?', ':'') + val[filedata];
    });
    return nombres;
}

function getSelects(tbl,filedata='idCHK') {
    var $tbl = $('#'+tbl), rows = [], len = i=0, infTbl;
    infTbl =  $tbl.jqxGrid('getdatainformation');
    len  = infTbl.rowscount;
    for (;i<len;i++) {
        var data = $tbl.jqxGrid('getrowdata',i);
        if(data[filedata]){ rows.push(data); }
    }
    return rows;
}

function fnCargarBotones(nuFuncion){
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/radicacionModelo.php",
        data:{
            option:'obtenerBotones',
            funcion : nuFuncion
        },
        async:false,
        cache:false
    }).done(function(data) {
        if (data.result) {
            $('#divCargarBotones').empty();
            $('#divCargarBotones').html(data.Mensaje);
            fnEjecutarVueGeneral('divCargarBotones');            
            ocultaCargandoGeneral();
        } else {
            ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    });
}

function fnObtenerRadicacion(){
    muestraCargandoGeneral();

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/radicacionModelo.php",
        data:{
            option:'obtenerRadicacion',
            selectUnidadNegocio : $('#selectUnidadNegocio').val(),
            selectUnidadEjecutora : fnObtenerOption('selectUnidadEjecutora'),
            selectMesRadicacion : fnObtenerOption('selectMesMinistracion'),
            selectEstatusRadicacion : fnObtenerOption('selectEstatusMinistracion'),
            txtFolio : $('#txtFolio').val(),
            selectProgramaPresupuestal : fnObtenerOption('selectProgramaPresupuestal'),
            dateDesde : $('#dateDesde').val(),
            dateHasta : $('#dateHasta').val()
        },
        async:false,
        cache:false
    }).done(function(data) {
        if (data.result) {

            datosJason = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;

            var nombreExcel = data.contenido.nombreExcel;

            var columnasExcel= [3,4,5,6,8,9,10,11,12];
            var columnasVisuales= [0,3,4,5,7,8,9,10,11,12];

            fnLimpiarTabla('divtabla', 'divDatos');
            //fnAgregarGrid_Detalle_nostring(data, datafields, columns, tabla, ' ', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);
            fnAgregarGrid_Detalle(datosJason, columnasNombres, columnasNombresGrid, 'divDatos', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);

            ocultaCargandoGeneral();
            
            // if ( $.isEmptyObject(datosJason) ) {
            //     var Mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontraron registros.</p>';
            //     muestraModalGeneral(3,tituloGeneral,Mensaje);  
            // }
            
        } else {
            ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
    });
}

function fnObtenerOption(componenteSelect, intComillas = 0){
    var valores = "";
    var comillas="'";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {
            if(intComillas == 1){
                comillas="";
            }

            // Que no se opcion por default
            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+", "+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }

    return valores;
}
