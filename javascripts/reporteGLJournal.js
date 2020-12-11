/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */
////
var dataJsonNoCaptura = new Array();
var dataJsonNoCapturaSeleccionados = new Array();
var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = "Selecciono Folio con Estatus diferente, el Estatus debe ser igual";
var mensajeSinNoCaptura = "Sin selección de Folio";

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	// Obtener información a mostrar
	fnObtenerInformacion();
});

function fnVentanaNuevo() {
	var Link_Adecuaciones = document.getElementById("Link_NuevoGeneral");
    Link_Adecuaciones.click();
}

function fnObtenerInformacion() {
	//console.log("fnObtenerInformacion");
    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerPolizas',
	      legalid: $("#cborazon").val(),
	      tagref: $("#cbounidadnegocio").val(),
	      tipoPoliza: $("#cbotipopoliza").val(),
	      noPoliza: $("#txtpolizano").val(),
	      noPolizaFolio: $("#txtpolizanoFolio").val(),
	      fechaDesde: $("#txtFechaDesde").val(),
	      fechaHasta: $("#txtFechaHasta").val(),
	      ue: $("#selectUnidadEjecutora").val()
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/reporteGLJournal_modelo.php",
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
			
			//console.log( "dataJson: " + JSON.stringify(dataJson) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [2, 3, 4, 5, 6, 8, 10, 11, 13];
			var columnasVisuales= [0, 2, 3, 4, 5, 7, 8, 10, 11, 13, 14];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);

			// fnEjecutarVueGeneral();
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

function fnValidarProcesoCambiarEstatus() {
	// Comparar check Seleccionados
	var estatus = "";
	estatusDiferentes = 0;
	seleccionoCaptura = 0;
	dataJsonNoCapturaSeleccionados = new Array();
	for (var key in dataJsonNoCaptura) {
		//console.log("transno: "+dataJsonNoCaptura[key].transno);
		var check = $("#checkbox_"+dataJsonNoCaptura[key].transno).prop('checked');
        if (check) {
        	var obj = new Object();
        	obj.transno = dataJsonNoCaptura[key].transno;
        	obj.type = dataJsonNoCaptura[key].type;
        	obj.statusid = dataJsonNoCaptura[key].statusid;
        	dataJsonNoCapturaSeleccionados.push(obj);
        	seleccionoCaptura = 1;
        	if (estatus == "") {
        		//estatus = dataJsonNoCaptura[key].statusid;
        		estatus = dataJsonNoCaptura[key].statusname;
        	//}else if (Number(estatus) != Number(dataJsonNoCaptura[key].statusid)) {
        	}else if ((estatus) != (dataJsonNoCaptura[key].statusname)) {
        		estatusDiferentes = 1;
        	}
        }
	}
	
	if (estatusDiferentes == 1) {
		muestraMensaje(mensajeEstatusDiferentes, 3, 'divMensajeOperacion', 5000);
	}else{
		//console.log("Todo Bien");
		// Habilitar boton
		// for (var key in dataObjDatosBotones) {
		// 	if (estatus == dataObjDatosBotones[key].statusid) {
		// 		$("#"+dataObjDatosBotones[key].namebutton).prop("disabled", false);
		// 	}
		// }
	}
}

function fnConfirmarAutorizacion(statusid) {
	// Confirmar Autotrizacion de Suficiencia
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = '<h4>¿Desea Autorizar los Folios Seleccionados?</h4>';
	muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"')");
}

function fnCambiarEstatus(statusid) {
	//console.log("fnEstatusValidar - statusid: "+statusid+" - estatusDiferentes: "+estatusDiferentes);
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		fnActualizarEstatus(statusid);
	}
}

function fnActualizarEstatus(statusid) {
	//console.log("fnActualizarEstatus");
	//muestraCargandoGeneral();
	dataObj = { 
		option: 'actualizarEstatus',
		dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
		statusid: statusid
	};
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/reporteGLJournal_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			//ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
			// Datos de las adecuaciones
			fnObtenerInformacion();
		}else{
			//ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
		}
	})
	.fail(function(result) {
		//ocultaCargandoGeneral();
		//console.log("ERROR");
		//console.log( result );
	});
}

function fnObtenerBotones(divMostrar) {
	//Opcion para operacion
	dataObj = { 
	        option: 'obtenerBotones',
	        type: ''
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/reporteGLJournal_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info = data.contenido.datos;
	    	dataObjDatosBotones = data.contenido.datos;
	    	//console.log("presupuesto: "+JSON.stringify(info));
	    	var contenido = '';
	    	for (var key in info) {
	    		var funciones = '';
	    		// if (info[key].statusid == 0) {
	    		// 	funciones = 'fnConfirmacionCancelarAdecuacion('+info[key].statusid+')';
	    		// }else 
	    		if (info[key].statusid == 1) {
	    			funciones = 'fnConfirmarAutorizacion('+info[key].statusid+')';
	    		} else{
	    			funciones = 'fnCambiarEstatus('+info[key].statusid+')';
	    		}
	    		contenido += '&nbsp;&nbsp;&nbsp; <component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
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