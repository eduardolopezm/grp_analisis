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
    
    $("#autorizarTerminada").click(function (){
        fnConfirmarAutorizacion();
    });
    $("#rechazarTerminada").click(function (){
      fnConfirmarRechazada(); 
    });
    

    fnObtenerInformacion();
    $("#btnBusqueda").click(function() {
    	fnObtenerInformacion();
    })

});

/******************
 *    Metodos     *
* ***************/

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
	    url: "modelo/StockLocTransferReceiveModelo.php",
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
			var columnasExcel= [  2, 3,4,5,6,7,8,9,10,11];
			var columnasVisuales= [  1, 2, 3,4,5,6,7,8,9,10,11];
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

function fnConfirmarRechazada(){
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
    if (status =="Recibido" ){
        muestraMensaje('No se puede rechazar la transferencia con estatus Recibido', 3, 'divMensajeOperacion', 5000);
    }else{
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Desea rechazar las entradas seleccionadas?', '', 'fnRechazarEntrada()');
    }
}
function fnRechazarEntrada(){
    let arraySalidas = fnValidarDatos(); 
    dataObj = { 
        option: 'RechazarEntrada',
        arraySalidas: arraySalidas,
    };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/StockLocTransferReceiveModelo.php",
      data: dataObj
    })
    .done(function( data ) {

        if(data.result) {
           
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se rechazo con éxito las entradas seleccionadas</p>');
            fnObtenerInformacion();
        }
    })
    .fail(function(result) {
        
    });
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
    if (status =="Recibido" ){
        muestraMensaje('No se puede autorizar la transferencia con estatus Recibido', 3, 'divMensajeOperacion', 5000);
    }else{
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Desea autorizar las entradas seleccionadas?', '', 'fnAutorizarEntrada()');
    }   
}

function fnAutorizarEntrada(){

    let arraySalidas = fnValidarDatos(); 
    dataObj = { 
        option: 'AutorizarEntrada',
        arraySalidas: arraySalidas,
    };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/StockLocTransferReceiveModelo.php",
      data: dataObj
    })
    .done(function( data ) {

        if(data.result) {
           
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se recibieron con éxito las transferencias realizadas</p>');
            fnObtenerInformacion();
        }
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
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>No ha seleccionado una Salida autorizada para recibir.</p>';
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
       url: "modelo/StockLocTransferReceiveModelo.php",
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