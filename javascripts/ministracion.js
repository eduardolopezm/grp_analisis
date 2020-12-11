var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

$(document).ready(function (){
	fnObtenerMinistracion();
    fnCargarBotones(nuFuncion);

    $('#btnNuevo').click(function(){
        window.location.href="solicitudMinistracion.php";
    });

    $('#btn-search').click(function (){
        muestraCargandoGeneral();
        fnObtenerMinistracion();
        ocultaCargandoGeneral();
        
        
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
            if(permisoValidador == '1'){
                noChange = [3,4,5,6];
            }else{
                noChange = [2,3,4,5,6]; 
                varPorValidar = "Por Validar,";
            }
            
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;

                    muestraModalGeneral(3,titulo,'La ministración con estatus '+varPorValidar+' Por Autorizar, Solicitado, Autorizado y/o Cancelado no se pueden avanzar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });
            if($flag == 0){
                var mensaje="";
                
                if(selections.length > 1){
                     mensaje = '¿Está seguro de avanzar las solicitudes de ministración seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de avanzar la solicitud de ministración seleccionada?';
                }
                
                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusMinistracion(\''+idPartidas+'\',\'avanzar\',\''+idFolio+'\')');
            }
        }else if (el.attr('id') == 'btnRechazar') {
            var noChange = [1,5,6];
            var contadorPorAutorizar=0;
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;
                    muestraModalGeneral(3,titulo,'La ministración con estatus Solicitado, Autorizado, Cancelado o Capturado no se pueden Rechazar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
                if(row.idEstatus == '3'){
                    contadorPorAutorizar++;
                }
            });

            if(contadorPorAutorizar >0){
                if(permisoValidador =='1'){
                    $flag++;
                    muestraModalGeneral(3,titulo,'No se pueden rechazar la ministracion con estatus Por Autorizar con un perfil Validador.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            }

            if($flag == 0){
                var mensaje="";
                
                if(selections.length > 1){
                     mensaje = '¿Está seguro de rechazar las solicitudes de ministración seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de rechazar la solicitud de ministración seleccionada?';
                }
                
                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusMinistracion(\''+idPartidas+'\',\'rechazar\',\''+idFolio+'\')');
            }
        }else if (el.attr('id') == 'btnSolicitar') {
            
            var noChange = [4,5,6];
            
            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;
                    muestraModalGeneral(3,titulo,'La ministración con estatus Solicitado, Autorizado o Cancelado no se pueden Solicitar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }

                // if(row.fecha_pago == ""){
                //     $flag++;
                //     muestraModalGeneral(3,titulo,'Es necesario la Fecha de Pago para solicitar','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                // }

            });
            
            if($flag == 0){
                
                var mensaje="";
                
                if(selections.length > 1){
                     mensaje = '¿Está seguro de solicitar las ministraciones seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de solicitar la ministración seleccionada?';
                }

                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusMinistracion(\''+idPartidas+'\',\'solicitar\',\''+idFolio+'\')');
            }
        }else if (el.attr('id') == 'btnCancelar') {
            
            if(permisoAutorizador == '1'){
                 noChange = [5,6];
            }else if(permisoValidador =='1'){
                noChange = [3,4,5,6]; 
            }else if(permisoCapturista =='1'){
                noChange = [2,3,4,5,6]; 
            }

            $.each(selections, function(ind, row) {
                if(noChange.indexOf(parseInt(row.idEstatus))!==-1){
                    $flag++;

                    if(permisoAutorizador == '1'){
                        muestraModalGeneral(3,titulo,'La ministración con estatus Autorizado y/o Cancelado no se pueden cancelar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }else if(permisoValidador =='1'){
                        muestraModalGeneral(3,titulo,'La ministración con estatus  Por Autorizar, Solicitado, Autorizado y/o Cancelado no se pueden cancelar por el perfil Validador.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }else if(permisoCapturista =='1'){
                        muestraModalGeneral(3,titulo,'La ministración con estatus Por Validar, Por Autorizar, Solicitado, Autorizado y/o Cancelado no se pueden cancelar por el perfil Capturista.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                    }
                    //muestraModalGeneral(3,titulo,'La ministración con estatus Solicitado, Por Autorizar, Autorizado y/o Cancelado no se pueden avanzar.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
                }
            });

            if($flag == 0){
                var mensaje="";
                
                if(selections.length > 1){
                     mensaje = '¿Está seguro de cancelar las solicitudes de ministración seleccionadas?';
                }else{
                     mensaje = '¿Está seguro de cancelar la solicitud de ministración seleccionada?';
                }

                // var titulo = 'Confirmación', mensaje = '¿Estas seguro de autorizar las solicitudes seleccionadas?';
                muestraModalGeneralConfirmacion(3,titulo,mensaje,'','fnModificarEstatusMinistracion(\''+idPartidas+'\',\'cancelar\',\''+idFolio+'\')');
            }
        }
    }else{
        muestraModalGeneral(3,tituloGeneral,'No ha seleccionado ningún elemento.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
    }
}

function fnModificarEstatusMinistracion(idPartida,accion,idFolio){
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/ministracionModelo.php",
        data:{
            option:'modificarEstatusMinistracion',
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
            fnObtenerMinistracion();
            
        } else {
            
        }
    }).fail(function(result) {
        
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
        url: "modelo/ministracionModelo.php",
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
            
        } else {
            
        }
    }).fail(function(result) {
        
    });
}

function fnObtenerMinistracion(){

	// muestraCargandoGeneral();

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/ministracionModelo.php",
        data:{
            option:'obtenerMinistracion',
            selectUnidadNegocio : $('#selectUnidadNegocio').val(),
            selectUnidadEjecutora : fnObtenerOption('selectUnidadEjecutora'),
            selectMesMinistracion : fnObtenerOption('selectMesMinistracion'),
            selectEstatusMinistracion : fnObtenerOption('selectEstatusMinistracion'),
            txtFolio : $('#txtFolio').val(),
            selectProgramaPresupuestal : fnObtenerOption('selectProgramaPresupuestal'),
            txtCLC : $('#txtCLC').val(),
            dateDesde : $('#dateDesde').val(),
            dateHasta : $('#dateHasta').val(),
            dateProgramacionPago : $('#dateProgramacionPago').val()
        },
        async:false,
        cache:false
    }).done(function(data) {
        if (data.result) {

        	datosJason = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;

            var nombreExcel = data.contenido.nombreExcel;

            var columnasExcel= [3,5,6,8,9,10,11,12,13,14];
            var columnasVisuales= [0,3,5,7,8,9,10,11,12,13,14,15,16];
            fnLimpiarTabla('divtabla', 'divDatos');
            //fnAgregarGrid_Detalle_nostring(data, datafields, columns, tabla, ' ', 1, columntoexcel, false, true, "", visualcolumn, nameexcel);
        	fnAgregarGrid_Detalle(datosJason, columnasNombres, columnasNombresGrid, 'divDatos', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);
            //ocultaCargandoGeneral();
            
        } else {
            //ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        //ocultaCargandoGeneral();
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

function fnCancelarMinistracion(){

}


function fnMostrarSpinner(){
    //setTimeout(function() {
    $('#ModalSpinerGeneral').show(); 
       
    //},10);

    
    console.log('entro');
}

function fnOcultarSpinner(){
    setTimeout(function() {
    $('#ModalSpinerGeneral').hide(); 
       
    }, 1000);
    
    console.log('salio');
}
