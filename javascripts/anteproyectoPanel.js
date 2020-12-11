/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */
//
var dataJsonNoCaptura = new Array();
var dataJsonNoCapturaSeleccionados = new Array();
var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = "Selecciono Folio con Estatus diferente, el Estatus debe ser igual";
var mensajeSinNoCaptura = "Sin selección de Folio";

var jsonDataRechazo = new Array();

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	// Obtener información de los Registros
	$("#btnBusqueda").click(function() {
		fnObtenerRegistros();
	});
	
	$("#btnBusqueda").click();
});

/**
 * Función para mostrar información en un modal
 * @param  {[type]} jsonData [description]
 * @return {[type]}          [description]
 */
function fnVerDetalleModalVisual(jsonData, mensajeProceso = '', detalle = 0) {
	// Visualizar información
	// console.log("jsonData: "+JSON.stringify(jsonData));

	var contenido = '';
	var style = 'style="text-align:center;"';
	
	contenido += '<table class="table table-bordered" name="tablaDetalleModal" id="tablaDetalleModal">';
	contenido += '<tbody>';

	// Encabezado
	contenido += '<tr class="header-verde">';
	contenido += '<td '+style+'></td>';
    contenido += '<td '+style+'>UR</td>';
    contenido += '<td '+style+'>UE</td>';
	contenido += '<td '+style+'>Capitulo</td>';
	contenido += '<td '+style+'>Estatus</td>';
	contenido += '<td '+style+'>Fecha Modificación</td>';
	contenido += '<td '+style+'>Usuario</td>';
	contenido += '<td '+style+'>Total</td>';
	contenido += '</tr>';

	jsonDataRechazo = jsonData;

	for (var key in jsonData) {
		if(jsonData[key].ue == null || jsonData[key].ue == ''){
			var ues = '';
		}else{
			var ues = jsonData[key].ue;
		}
		// Contenido
		contenido += '<tr>';
		var nombreCheck = 'check_'+jsonData[key].ur+'_'+jsonData[key].ue+'_'+jsonData[key].capitulo+'';
		if (jsonData[key].estatus != '1') {
			contenido += '<td '+style+'><input type="checkbox" id="'+nombreCheck+'" name="'+nombreCheck+'" value="0" placeholder="Detalle Captura" title="Detalle Captura" class="" /></td>';
		} else {
			contenido += '<td '+style+'></td>';
		}
        contenido += '<td '+style+'>'+jsonData[key].ur+'</td>';
        contenido += '<td '+style+'>'+ues+'</td>';
		contenido += '<td>'+jsonData[key].capituloNombre+'</td>';
        //contenido += '<td>'+jsonData[key].capitulo+' - '+jsonData[key].capituloNombre+'</td>';
		contenido += '<td '+style+'>'+jsonData[key].estatusNombre+'</td>';
		contenido += '<td '+style+'>'+jsonData[key].fechaMod+'</td>';
		contenido += '<td '+style+'>'+jsonData[key].usuarioNombre+'</td>';
		contenido += '<td style="text-align:right;">$'+formatoComas( redondeaDecimal(jsonData[key].importe)+"" )+'</td>';
		contenido += '</tr>';
	}

	contenido += '</tbody>';
	contenido += '</table>';

	if (detalle == 1) {
		contenido += '<div align="center">';
		contenido += '<button id="btnRechazarModalDetalle" name="btnRechazarModalDetalle" type="button" title="" onclick="fnRechazarDetalleAnte()" class="glyphicon glyphicon-arrow-left btn btn-default botonVerde" style="font-weight: bold;">&nbsp;Rechazar</button>';
		contenido += '</div>';
	}

	if (jsonData.length == 0) {
		// Si no tiene información
		contenido = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin información para visualizar</p>';
	}

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	muestraModalGeneral(4, titulo, contenido);

	if (mensajeProceso != '') {
		$("#ModalGeneral_Advertencia").empty();
		$("#ModalGeneral_Advertencia").append(mensajeProceso);
	}
}

function fnRechazarDetalleAnte() {
	// Rechzar captura
	jsonData = jsonDataRechazo;
	var infoRechazar = new Array();
	for (var key in jsonData) {
		var nombreCheck = 'check_'+jsonData[key].ur+'_'+jsonData[key].ue+'_'+jsonData[key].capitulo+'';
		if( $('#'+nombreCheck).prop('checked') ) {
			// Si encuentra uno seleccionado
			var obj = new Object();
			obj.type = jsonData[key].type;
			obj.transno = jsonData[key].transno;
			obj.ur = jsonData[key].ur;
			obj.ue = jsonData[key].ue;
			obj.capitulo = jsonData[key].capitulo;
			infoRechazar.push(obj);
		}
	}
	
	if (infoRechazar.length == 0) {
		// Si no selecciono
		var notificacion = '<div class="alert alert-danger alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>Sin selección de registros</p>' + '</div>';
		$("#ModalGeneral_Advertencia").empty();
		$("#ModalGeneral_Advertencia").append(notificacion);
	} else {
		$("#ModalGeneral_Advertencia").empty();
		
		// Realizar rechazo
		dataObj = { 
			option: 'rechazarDetalleAnte',
			dataJsonNoCapturaSeleccionados: infoRechazar,
		};
		$.ajax({
			async:false,
	        cache:false,
			method: "POST",
			dataType:"json",
			url: "modelo/anteproyectoPanelModelo.php",
			data:dataObj
		})
		.done(function( data ) {
			if(data.result){
				//ocultaCargandoGeneral();
				fnVerDetalleModal(data.contenido.type, data.contenido.transno, data.contenido.mensaje);
			}else{
				//ocultaCargandoGeneral();
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				muestraModalGeneral(4, titulo, data.contenido.mensaje);
			}
		})
		.fail(function(result) {
			ocultaCargandoGeneral();
			//console.log("ERROR");
			//console.log( result );
		});
	}
}

/**
 * Función para el detalle de la captura en un modal
 * @param  {[type]} type    Tipo de documento
 * @param  {[type]} transno Folio de transacción
 * @return {[type]}         [description]
 */
function fnVerDetalleModal(type, transno, mensajeProceso = '') {
	// Mostrar el detalle de la captura
	dataObj = { 
		option: 'detalleFolio',
		type: type,
		transno: transno
	};
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/anteproyectoPanelModelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			//ocultaCargandoGeneral();
			fnVerDetalleModalVisual(data.contenido.datos, mensajeProceso, data.contenido.detalle);
		}else{
			//ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al mostrar el detalle</p>';
			muestraModalGeneral(4, titulo, mensaje);
		}
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		//console.log("ERROR");
		//console.log( result );
	});
}

/**
 * Función para obtener los registros seleccionados del grid
 * @return {[type]} Información de los registros seleccionados
 */
function fnObtenerDatosSeleccionados() {
	// Funcion para obtener los renglones seleccionados de la tabla
	var dataJsonNoCapturaSeleccionados = new Array();

	var griddata = $('#divTabla > #divContenidoTabla').jqxGrid('getdatainformation');
	for (var i = 0; i < griddata.rowscount; i++) {
		var id = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'id1');
		if (id == true) {
			var obj = new Object();
        	obj.transno = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'nu_transno');
        	obj.type = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'nu_type');
        	obj.statusid = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'statusid');
        	dataJsonNoCapturaSeleccionados.push(obj);
		}
	}

	return dataJsonNoCapturaSeleccionados;
}

/**
 * Función para validar que los folios seleccionados sean del mismo estatus
 * @param  {[type]} jsonData Informacion de los folios seleccionados
 * @return {[type]}          [description]
 */
function fnValidarEstatusIguales(jsonData) {
	// Validar si tienen mismo estatus
	var mensaje = "";
	var estatus = "";
	for (var key in jsonData) {
		if (estatus == "") {
			estatus = jsonData[key].statusid;
		}else if (Number(estatus) != Number(jsonData[key].statusid)) {
			mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono Folio con Estatus diferente, el Estatus debe ser igual</p>';
		}
	}

	return mensaje;
}

/**
 * Función para autorizar los folios seleccionados
 * @param  {[type]} statusid Estatus de autorizacion
 * @return {[type]}          [description]
 */
function fnAutorizacionFolios(statusid ) {
	// Autorizar los folios seleccionados
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

	dataObj = { 
		option: 'autorizarEstatus',
		dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
		statusid: statusid
	};
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/anteproyectoPanelModelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			//Si trae informacion
			//ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
			// Mostrar información actualizada
			fnObtenerRegistros();
		}else{
			//ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
		}
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		//console.log("ERROR");
		//console.log( result );
	});
}

/**
 * Función para validaciones y mensaje de confirmación para autorizacion de folios seleccionados
 * @param  {[type]} statusid Estatus de autorizacion
 * @return {[type]}          [description]
 */
function fnConfirmacionAutorizacion(statusid) {
	// Función para generar el cvd del anteproyecto
	var mensaje = "";
	
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

	if (dataJsonNoCapturaSeleccionados.length == 0) {
		// No selecciono registros
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin selección de Folio</p>';
		muestraModalGeneral(4, titulo, mensaje);
		return false;
	}

	mensaje = fnValidarEstatusIguales(dataJsonNoCapturaSeleccionados);
	if (mensaje != '') {
		// Mostrar mensaje con errores
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensaje);
		return false;
	}
	
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = '<p>¿Desea Autorizar los Folios Seleccionados?</p>';
	muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnAutorizacionFolios('"+statusid+"')");	
}

/**
 * Función para obtener los registros y mostrar información
 * @return {[type]} [description]
 */
function fnObtenerRegistros() {
	//console.log("fnObtenerRegistros");
	var legalid = "";
	var selectRazonSocial = document.getElementById('selectRazonSocial');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            legalid = "'"+selectRazonSocial.selectedOptions[i].value+"'";
        }else{
            legalid = legalid+", '"+selectRazonSocial.selectedOptions[i].value+"'";
        }
    }

	var tagref = "";
	var selectUnidadNegocio = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            tagref = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            tagref = tagref+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
    }

    var ue = "";
	var selectUnidadEjecutora = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUnidadEjecutora.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
    }

    var tipoSuficiencia = "";
	var selectTipoSuficiencia = document.getElementById('selectTipoSuficiencia');
    for ( var i = 0; i < selectTipoSuficiencia.selectedOptions.length; i++) {
        if (i == 0) {
            tipoSuficiencia = "'"+selectTipoSuficiencia.selectedOptions[i].value+"'";
        }else{
            tipoSuficiencia = tipoSuficiencia+", '"+selectTipoSuficiencia.selectedOptions[i].value+"'";
        }
    }

    var folio = $("#txtFolioSuficiencia").val();

    var fechaDesde = $("#txtFechaDesde").val();
    var fechaHasta = $("#txtFechaHasta").val();

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
	      legalid: legalid,
	      tagref: tagref,
	      fechaDesde: fechaDesde,
	      fechaHasta: fechaHasta,
	      ue: ue,
	      tipoSuficiencia: tipoSuficiencia,
	      folio: folio
	    };
	
	$.ajax({
		async:false,
	    cache:false,
	    method: "POST",
	    dataType:"json",
	    url: "modelo/anteproyectoPanelModelo.php",
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

			fnLimpiarTabla('divTabla', 'divContenidoTabla');

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [1, 4, 5, 6, 8];
			var columnasVisuales= [0, 2, 3, 4, 5, 6, 8];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
		}else{
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la información</p>';
			muestraModalGeneral(3, titulo, mensaje);
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

/**
 * Función para obtener los botones de las operaciones a realizar
 * @param  {[type]} divMostrar [description]
 * @return {[type]}            [description]
 */
function fnObtenerBotones(divMostrar) {
	//Opcion para operacion
	dataObj = { 
	        option: 'obtenerBotones',
	        type: ''
	      };
	$.ajax({
		  async:false,
	      cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/anteproyectoPanelModelo.php",
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
	    		if (info[key].statusid == 5) {
	    			funciones = 'fnConfirmacionAutorizacion('+info[key].statusid+')';
	    		}
	    		contenido += '&nbsp;&nbsp;&nbsp; <component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
	    		//contenido += '&nbsp;&nbsp;&nbsp; \
	    		//<button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" onclick="'+funciones+'" class="btn btn-default botonVerde '+info[key].clases+'">'+info[key].namebutton+'</button>';
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