//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;
window.renglon = 1;
window.municipios = [];
window.municipioEntidad = [];
window.clavesGeneral = [];
window.clavesCombustibles = [];
window.peajeTransporte = [];
window.cantPernoctaOld = 0;
window.datosPaises = [];
window.tipoDivisa = 'MX';

$( window ).load(function() {
   
    if (document.querySelector(".selectTipoProducto")) {
		dataObj = { 
	        option: 'mostrarTipoProducto'
	    };
		fnSelectGeneralDatosAjax('.selectTipoProducto', dataObj, 'modelo/StockLocTransferModelo.php', 0);
	}

    fnObtenerInformacion();
    $("#btnBusqueda").click(function() {
    	fnObtenerInformacion();
    });

    $("#btNuevaTransferencia").click(function (){
        window.open("StockLocTransfer_V.php", "_self");
    });

    $("#autorizarTerminada").click(function (){
        fnConfirmarAutorizacion();
    });

    $('#cancelarTerminada').click(function(){
        var userIDPerfil = fnObtenerPerfilUsr();
        var iCntC = 0; // contador de cancelados
        var iCntO = 0; // contador de originales
        var iCntA = 0; // contador de autorizados
        var iCntPA = 0; // contador de por autorizar
        var iCntV = 0; // contador de para validar por status y perfil
        let arraySalidas = fnValidarDatos(); 
        arraySalidas  = arraySalidas.arraySalidas; 
        let reference = ''; 
        var statusReq= "";
        let etiquetaStatus = ''; 
        console.log("arraySalidas",arraySalidas)
        for(i in arraySalidas){
            
            reference = arraySalidas[i];
            status    = fnObtenerStatus(reference);
            console.log("status",status)
            if(status == 'Cancelado'){
                iCntC = iCntC + 1;
            }else if(status == 'Recibido'  ){
                iCntA = iCntA + 1;
            }
            else if(status == 'Original'  ){
                iCntO = iCntO + 1;
            }
            else if(status == 'PorAutorizar'  ){
                iCntPA = iCntPA + 1;
            }else if((status == 'Validar' || status == 'PorAutorizar') && userIDPerfil == 9 ){
                iCntV = iCntV + 1;
            }
        }
        if (status == 'Validar')
            status = 'Por Validar'; 
        if(iCntC > 0){
            muestraMensaje('No se puede cancelar la transferencia ya cancelada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntA > 0){
            muestraMensaje('No se puede cancelar la transferencia ya Recibida', 3, 'divMensajeOperacion', 5000);
        }else if( iCntO > 0){
            muestraMensaje('No se puede cancelar la transferencia en estatus Original', 3, 'divMensajeOperacion', 5000);
        }else if( iCntPA > 0){
            muestraMensaje('No se puede cancelar la transferencia en estatus por autorizar', 3, 'divMensajeOperacion', 5000);
        }else if( iCntV > 0){
            muestraMensaje('EL usuario capturista, no puede avanzar la requisición con estatus '+ status, 3, 'divMensajeOperacion', 5000);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Esta seguro que desea cancelar la(s) Transferencia(s) seleccionada(s)? </p>';
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnCancelar()');
        }

    });


    $('#rechazarTerminada').click(function(){
        var userIDPerfil = fnObtenerPerfilUsr();
            var iCntC = 0; // contador de cancelados
            var iCntO = 0; // contador de originales
            var iCntA = 0; // contador de autorizados
            var iCntPA = 0; // contador de por autorizar
            var iCntV = 0; // contador de para validar por status y perfil
            let arraySalidas = fnValidarDatos(); 
            arraySalidas  = arraySalidas.arraySalidas; 
            let reference = ''; 
            var statusReq= "";
            let etiquetaStatus = ''; 
            for(i in arraySalidas){
                reference = arraySalidas[i];
                status    = fnObtenerStatus(reference);
                if (status == 'Capturada'){
                    muestraMensaje('No se puede rechazar la transferencia que ha sido capturada', 3, 'divMensajeOperacion', 5000);
                }
                if (status == 'Recibido'){
                    muestraMensaje('No se puede rechazar la transferencia que ha sido recibida', 3, 'divMensajeOperacion', 5000);
                }
                else if (status == 'Por Entregar'){
                    muestraMensaje('No se puede rechazar la transferencia que está por entregar', 3, 'divMensajeOperacion', 5000);
                }else{
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    var mensaje = '<p> ¿Está seguro que desea rechazar las salidas seleccionadas? </p>';
                    muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnRechazar()');4
                }
            }
    });
    $('#Avanzar').click(function(){

        var userIDPerfil = fnObtenerPerfilUsr();
        var iCntC = 0; // contador de cancelados
        var iCntO = 0; // contador de originales
        var iCntA = 0; // contador de autorizados
        var iCntPA = 0; // contador de por autorizar
        var iCntV = 0; // contador de para validar por status y perfil
        let arraySalidas = fnValidarDatos(); 
        arraySalidas  = arraySalidas.arraySalidas; 
        let reference = ''; 
        var statusReq= "";
        let etiquetaStatus = ''; 
        console.log("arraySalidas",arraySalidas)
        for(i in arraySalidas){
            
            reference = arraySalidas[i];
            status    = fnObtenerStatus(reference);
            console.log("status",status)
            if(status == 'Cancelado'){
                iCntC = iCntC + 1;
            }else if(status == 'Recibido'  ){
                iCntA = iCntA + 1;
            }
            else if(status == 'Original'  ){
                iCntO = iCntO + 1;
            }
            else if(status == 'PorAutorizar'  ){
                iCntPA = iCntPA + 1;
            }else if((status == 'Validar' || status == 'PorAutorizar') && userIDPerfil == 9 ){
                iCntV = iCntV + 1;
            }
        }
        if (status == 'Validar')
            status = 'Por Validar'; 
        if(iCntC > 0){
            muestraMensaje('No se puede avanzar la transferencia ya cancelada', 3, 'divMensajeOperacion', 5000);
        }else if( iCntA > 0){
            muestraMensaje('No se puede avanzar la transferencia ya Recibida', 3, 'divMensajeOperacion', 5000);
        }else if( iCntO > 0){
            muestraMensaje('No se puede avanzar la transferencia en estatus Original', 3, 'divMensajeOperacion', 5000);
        }else if( iCntPA > 0){
            muestraMensaje('No se puede avanzar la transferencia en estatus por autorizar', 3, 'divMensajeOperacion', 5000);
        }else if( iCntV > 0){
            muestraMensaje('EL usuario capturista, no puede avanzar la requisición con estatus '+ status, 3, 'divMensajeOperacion', 5000);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p> ¿Esta seguro que desea avanzar la(s) Transferencia(s) seleccionada(s)? </p>';
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnAvanzar()');
        }
    });


});


/******************
 *    Metodos     *
* ***************/

/**
 * Obtener datos para llenar los select
 * @param  string prefix prefijo de la url
 * @return jquery
 */


function fnCrearSelectCero(dataJson, elementoClase = "", valor = "", valorInicial = 1) {
  var contenido = "";
  if (valorInicial == 1) {
      var contenido = "<option value='Se'>Seleccionar...</option>";
  }
  for (var info in dataJson) {
      var selected = "";
      if (dataJson[info].value == valor) {
          selected = "selected";
      }
      contenido += "<option value='" + dataJson[info].value + "' " + selected + ">" + dataJson[info].texto + "</option>";
  }
  if (elementoClase == "") {
      return contenido;
  } else {
      // Si trae nombre para los datos
      $(elementoClase).empty();
      $(elementoClase).append(contenido);
      $(elementoClase).multiselect('rebuild');
  }
}


function fnObtenerInformacion() {
    var ur = "";
	var selectUr = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUr.selectedOptions.length; i++) {
        if (i == 0) {
            ur = "'"+selectUr.selectedOptions[i].value+"'";
        }else{
            ur = ur+", '"+selectUr.selectedOptions[i].value+"'";
        }
    }

	var ue = "";
	var selectUe = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUe.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUe.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUe.selectedOptions[i].value+"'";
        }
    }

    var tipoBien = "";
	var selectBien = document.getElementById('selectTipoProducto');
    for ( var i = 0; i < selectBien.selectedOptions.length; i++) {
        if (i == 0) {
            tipoBien = "'"+selectBien.selectedOptions[i].value+"'";
        }else{
            tipoBien = tipoBien+", '"+selectBien.selectedOptions[i].value+"'";
        }
    }
    var status = "";
	var selectStatus = document.getElementById('selectStatus');
    for ( var i = 0; i < selectStatus.selectedOptions.length; i++) {
        if (i == 0) {
            status = "'"+selectStatus.selectedOptions[i].value+"'";
        }else{
            status = status+", '"+selectStatus.selectedOptions[i].value+"'";
        }
    }

    var numeroTransferencia  = $("#txtNumeroTransferencia").val();
    var pFechaini = $("#txtFechaInicio").val();
    var pFechafin = $("#txtFechaFin").val();
    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
          ur:ur,
          ue:ue,
          tipoBien:tipoBien,
          numeroTransferencia:numeroTransferencia,
          status:status,
          fechainicio: pFechaini,
          fechafin: pFechafin,
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/StockLocTransferModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			ocultaCargandoGeneral();
			dataJson = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			dataJsonNoCaptura = data.contenido.datos;
			
			console.log( "dataJson: " + JSON.stringify(columnasNombresGrid) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [  2, 3,4,6,7,8,9,10,11];
			var columnasVisuales= [ 1, 2, 3,5,6,7,8,9,10,11,12];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
			// fnEjecutarVueGeneral();
			//$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
		}else{
			ocultaCargandoGeneral();
			muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function eliminar (idMonto,descripction,jerarquia,zonaEconomica){
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar la jerarquía '+descripction+'?', '', 'fnEliminarClave(\''+idMonto+'\',\''+descripction+'\','+jerarquia+',\''+zonaEconomica+'\')');
}

function fnTImeEliminar(idMonto,descripction,jerarquia,zonaEconomica){
    dataObj = { 
        option: 'eliminarInformacion',
        idMonto: idMonto,  
        jerarquia: jerarquia,
        zonaEconomica:zonaEconomica
    };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/ABC_Jerarquias_Modelo.php",
      data:dataObj
  })
  .done(function( data ) {
      if(data.result){
          //Si trae informacion
          ocultaCargandoGeneral();
          var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
          muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el registro '+descripction+' del Catálogo Jerarquías con éxito </p>');
          //setTimeout(function(){ $('.modal-backdrop').remove();}, 1500);
          fnObtenerInformacion();
        }else{
          
          muestraMensaje('No es posible eliminar la jerarquía '+descripction+' ya que se encuentra vinculada a otro registro', 3, 'divMensajeOperacion', 5000); 
      }
  })
  .fail(function(result) {
    ocultaCargandoGeneral();
  });

}
function fnEliminarClave(idMonto,descripction,jerarquia,zonaEconomica){
    muestraCargandoGeneral();
    setTimeout(function(){fnTImeEliminar(idMonto,descripction,jerarquia,zonaEconomica);}, 2000);
    
}
function getSelects(tbl,filedata='agregados') {
    var $tbl = $('#'+tbl), rows = [], len = i=0, infTbl;
    infTbl =  $tbl.jqxGrid('getdatainformation');
    len  = infTbl.rowscount;
    for (;i<len;i++) {
        var data = $tbl.jqxGrid('getrowdata',i);
        if(data[filedata]){ rows.push(data); }
    }
    return rows;
}

function fnConfirmarAutorizacion (){
    let arraySalidas = fnValidarDatos(); 
    arraySalidas  = arraySalidas.arraySalidas; 
    let reference = ''; 
    var statusReq= "";
    let etiquetaStatus = ''; 
    console.log("arraySalidas",arraySalidas)
    for(i in arraySalidas){
            
            reference = arraySalidas[i];
            status    = fnObtenerStatus(reference);
    }
    if (status =="Por Entregar" ){
        muestraMensaje('No se puede autorizar la transferencia con estatus Por Entregar', 3, 'divMensajeOperacion', 5000);
    }else{
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Desea autorizar las salidas seleccionadas?', '', 'fnAutorizarSalida()');
    }   
}

function fnAutorizarSalida(){

    let arraySalidas = fnValidarDatos(); 
    dataObj = { 
        option: 'AutorizarSalida',
        arraySalidas: arraySalidas,
    };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/StockLocTransferModelo.php",
      data: dataObj
    })
    .done(function( data ) {

           
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se autorizaron las salidas seleccionadas</p>');
            fnObtenerInformacion();
        
    })
    .fail(function(result) {
        
    });


}

function fnValidarDatos(){

    var msg         = ""; 
    var validar     = false; 
    var datos       = [];
    var arraySalidas = []; 
    var selections = getSelects('divContenidoTabla','sel');
    var count      = 0;
    
    $.each(selections, function(ind, row) { 
        console.log("row",row); 
        arraySalidas[count]= row.idRow; 
        count++; 
    });

    var totalSalidas = arraySalidas.length; 

    
    if  (totalSalidas == 0){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No ha seleccionado una Salida para autorizar.</p>';
    }
    if(msg == ''){
        validar = true;
        datos = { 'totalSalidas':totalSalidas, 'arraySalidas':arraySalidas}
    }else{
        
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datos;
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
function fnObtenerStatus(reference){
    var statusReq = "";
    var ordernoReq = "";
   dataObj = {
       option: 'statusTransferencia',
       reference: reference
   };
   $.ajax({
       async:false,
       cache:false,
       method: "POST",
       dataType: "json",
       url: "modelo/StockLocTransferModelo.php",
       data: dataObj
   }).done(function(data) {
       if (data.result) {
           dataReqStatus = data.contenido.datos;
           for (var info in dataReqStatus) {

               status = dataReqStatus[info].status;
           }
       }else{
           muestraMensaje('No se encontro ningún estatus ', 3, 'divMensajeOperacion', 5000);
       }
   }).fail(function(result) {
       console.log("ERROR");
       console.log(result);
   });
    return status;
}


function fnRechazar(){
    let arraySalidas = fnValidarDatos(); 
    arraySalidas  = arraySalidas.arraySalidas; 
    let reference = ''; 
    var statusReq= "";
    let etiquetaStatus = ''; 

    dataObj = {
        option: 'rechazarSalida',
        arraySalidas: arraySalidas, 
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/StockLocTransferModelo.php",
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se rechazaron la (s) Salida (s) seleccionada (s)');
            fnObtenerInformacion();
        }else{
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se rechazó ninguna Requisición');
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    }); 
}

function fnCancelar(){

    let arraySalidas = fnValidarDatos(); 
        arraySalidas  = arraySalidas.arraySalidas; 
        let reference = ''; 
        var statusReq= "";
        let todasBien =0; 
        console.log("arraySalidas",arraySalidas)
        for(i in arraySalidas){

            reference = arraySalidas[i];
                dataObj = {
                    option: 'cancelar',
                    reference: reference
                };  
                $.ajax({
                    async:false,
                    cache:false,
                    method: "POST",
                    dataType: "json",
                    url: "modelo/StockLocTransferModelo.php",
                    data: dataObj
                }).done(function(data) {
                    if (data.result) {
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se avanzó la (s) Requisición (es) seleccionada (s)');
                        // fnObtenerRequisicionesPanel();
                        todasBien++; 
                    }else{
                        // Si hubo problema
                        todasBien = 0;
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se avanzó ninguna Requisición.');
                    }
                }).fail(function(result) {
                    console.log("ERROR");
                    console.log(result);
                });
            
        }

        if(todasBien == 1){
            // Mensaje de todo bien
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se cancelo la (s) transferencias (es) seleccionada (s)');
        } else {
            // Mensaje con errores
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se cancelo ninguna transferencia.');
        }

        fnObtenerInformacion();
}

function fnAvanzar(){
    
    let arraySalidas = fnValidarDatos(); 
        arraySalidas  = arraySalidas.arraySalidas; 
        let reference = ''; 
        var statusReq= "";
        let todasBien =0; 
        console.log("arraySalidas",arraySalidas)
        for(i in arraySalidas){

            reference = arraySalidas[i];
                dataObj = {
                    option: 'avanzarTransferencia',
                    reference: reference
                };  
                $.ajax({
                    async:false,
                    cache:false,
                    method: "POST",
                    dataType: "json",
                    url: "modelo/StockLocTransferModelo.php",
                    data: dataObj
                }).done(function(data) {
                    if (data.result) {
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se avanzó la (s) Requisición (es) seleccionada (s)');
                        // fnObtenerRequisicionesPanel();
                        todasBien++; 
                    }else{
                        // Si hubo problema
                        todasBien = 0;
                        // muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se avanzó ninguna Requisición.');
                    }
                }).fail(function(result) {
                    console.log("ERROR");
                    console.log(result);
                });
            
        }

        if(todasBien == 1){
            // Mensaje de todo bien
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', 'Se avanzó la (s) transferencias (es) seleccionada (s)');
        } else {
            // Mensaje con errores
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se avanzó ninguna transferencia.');
        }

        fnObtenerInformacion();
}
