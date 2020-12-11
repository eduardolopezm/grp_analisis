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
var mensajeEstatusDiferentes = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono No. de Captura con Estatus diferente, el Estatus debe ser igual</p>';
var mensajeSinNoCaptura = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin selección de No. de Captura</p>';
var estatusGenerarLayout = 6;
var funcionGenerarLayout = 2263;
var typeGenerarLayout = 250;
var tipoLayout = 2;

$( document ).ready(function() {
	if (document.querySelector(".selectTipoDocumentoAdecuaciones")) {
		// Inicio Tipo de Documento Adecuaciones
		//Opcion para operacion
		dataObj = { 
			option: 'mostrarTipoAdecuacion'
		};

		fnSelectGeneralDatosAjax('.selectTipoDocumentoAdecuaciones', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 0);
	}

	if (document.querySelector(".selectEstatusAdecuaciones")) {
		// Inicio Tipo de Documento Adecuaciones
		//Opcion para operacion
		dataObj = { 
			option: 'mostrarSelectEstatus'
		};

		fnSelectGeneralDatosAjax('.selectEstatusAdecuaciones', dataObj, 'modelo/GLBudgetsByTagV2_Panel_modelo.php', 0);
	}

	if (document.querySelector(".selectTipoSolicitud")) {
		fnTipoDeSolicitud('', '.selectTipoSolicitud');
	}

	// Datos botones
	fnObtenerBotones('divBotones');

	// Datos de las adecuaciones
	fnObtenerAdecuaciones();
});

function fnGeneralAutorizarAdecuacionLocal(noCaptura, type, nameSicop, nameMap, datosAdecuaciones=1) {
	var pSicop = $("#"+nameSicop).val();
	var fMap = $("#"+nameMap).val();

	if (pSicop.trim() == "" || fMap.trim() == "") {
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar la información (P SICOP, F MAP)</p>';
		if (!$('#ModalGeneral').is(':hidden')) {
	        muestraMensajeTiempo(mensaje, 1, 'ModalGeneral_Advertencia', 5000);
	    }else{
	        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	        muestraModalGeneral(3, titulo, mensaje);    
	    }
		return true;
	}

	//console.log("pSicop: "+pSicop+" - fMap: "+fMap+" - noCaptura: "+noCaptura+" - type: "+type);
	
	var dataJsonNoCaptura = new Array();
	var obj = new Object();
	obj.transno = noCaptura;
	obj.type = type;
	dataJsonNoCaptura.push(obj);

	//Opcion para operacion
	dataObj = { 
		option: 'autorizarAdecuacion',
		pSicop: pSicop,
		fMap: fMap,
		noCaptura: dataJsonNoCaptura
	};
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
		}else{
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
		}

		if (datosAdecuaciones == 1) {
			// Datos de las adecuaciones
			fnObtenerAdecuaciones();
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});
}

function fnAutorizarAdecuacionModal(button, noCaptura, type) {
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    var contenido = '<h3>Folio '+noCaptura+'</h3>\
        <div class="col-md-6">\
            <div class="form-inline row">\
                <div class="col-md-3">\
                    <span><label></label>Proceso SICOP: </span>\
                </div>\
                <div class="col-md-9"><input type="text" id="txtProcesoSicop" name="txtProcesoSicop" placeholder="Proceso SICOP" title="Proceso SICOP" class="form-control" onpaste="return false" style="width: 100%;" /></div>\
            </div>\
        </div>\
        <div class="col-md-6">\
            <div class="form-inline row">\
                <div class="col-md-3">\
                    <span><label></label>Folio MAP: </span>\
                </div>\
                <div class="col-md-9"><input type="text" id="txtFolioMap" name="txtFolioMap" placeholder="Folio MAP" title="Folio MAP" class="form-control" onpaste="return false" style="width: 100%;" /></div>\
            </div>\
        </div><br><br>\
        <div class="col-md-12" align="center">\
            <button type="button" id="btnAutorizarAdecuacion" name="btnAutorizarAdecuacion" class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk" \
            onclick="fnGeneralAutorizarAdecuacionLocal('+noCaptura+', '+type+', \'txtProcesoSicop\', \'txtFolioMap\', 1)">Guardar</button>\
        </div><br><br>';
	
	muestraModalGeneral(4, titulo, contenido);
}

function fnTipoDeSolicitud(nomTipoAdecuacion, nombreSelect, incial=1) {
	// Inicio Tipo de Solicitud
	var tipo = "";
	// if ($("#"+nomTipoAdecuacion).val() != "" && $("#"+nomTipoAdecuacion).val() != '0') {
	// 	tipo = $("#"+nomTipoAdecuacion).val();
	// }
	if (document.getElementById(nomTipoAdecuacion)) {
		var selectTipo = document.getElementById(nomTipoAdecuacion);
	    for ( var i = 0; i < selectTipo.selectedOptions.length; i++) {
	        //console.log( unidadesnegocio.selectedOptions[i].value);
	        if (i == 0) {
	            tipo = "'"+selectTipo.selectedOptions[i].value+"'";
	        }else{
	            tipo = tipo+", '"+selectTipo.selectedOptions[i].value+"'";
	        }
	    }
	}
	//console.log("tipoAdecuacion: "+tipoAdecuacion);
	//Opcion para operacion
	dataObj = { 
		option: 'mostrarTipoSolicitud',
		tipoAdecuacion: tipo
	};
	//Obtener datos de las bahias
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/GLBudgetsByTagV2_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			dataJsonTipoSolicitud = data.contenido.datos;
			//console.log("dataJsonTipoSolicitud: "+JSON.stringify(dataJsonTipoSolicitud));
			if (incial == 1) {
				//$(nombreSelect).append( fnCrearDatosSelect(dataJsonTipoSolicitud) );
				$(nombreSelect).append( fnCrearDatosSelect(dataJsonTipoSolicitud, '', '', 0) );
				$(nombreSelect).multiselect('rebuild');
			}else{
				fnCrearDatosSelect(dataJsonTipoSolicitud, nombreSelect, '', 0);
			}
		}else{
			//console.log("ERROR Modelo");
			//console.log( JSON.stringify(data) ); 
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});
	// Fin Tipo de Solicitud
}

function fnCambioTipoAdecuacion(select) {
	// Recargar datos del Tipo de Solicitud
	fnTipoDeSolicitud(select.id, '.selectTipoSolicitud', 2);
}

function fnArchivosLayout(button, noCaptura) {
	console.log("fnArchivosLayout");
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Archivos Layout</p></h3>';
	var contenido = '<p>Folio '+noCaptura+'</p>\
		<component-layouts-generados id="Archivos_'+noCaptura+'" funcion="'+funcionGenerarLayout+'" tipo="'+typeGenerarLayout+'" trans="'+noCaptura+'" > </component-layouts-generados>';
	contenido = '<p>Folio '+noCaptura+'</p>\
	<iframe class="" src="datos_archivos_layout.php?funcion='+funcionGenerarLayout+'&type='+typeGenerarLayout+'&transno='+noCaptura+'" \
	width="100%" height="350" frameBorder="0" style="max-width: 100%; height: auto;"></iframe>';
	//contenido = '';
	muestraModalGeneral(4, titulo, contenido);
	//fnEjecutarVueGeneral();
}

function fnVentanaAdecuaciones() {
	var Link_Adecuaciones = document.getElementById("Link_Adecuaciones");
    Link_Adecuaciones.click();
}

function fnObtenerAdecuaciones() {
	//console.log("fnObtenerAdecuaciones");
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

    var tipoAdecuacion = "";
	var selectTipoDoc = document.getElementById('selectTipoDoc');
    for ( var i = 0; i < selectTipoDoc.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            tipoAdecuacion = "'"+selectTipoDoc.selectedOptions[i].value+"'";
        }else{
            tipoAdecuacion = tipoAdecuacion+", '"+selectTipoDoc.selectedOptions[i].value+"'";
        }
    }

    var estatus = "";
	var selectEstatus = document.getElementById('selectEstatus');
    for ( var i = 0; i < selectEstatus.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            estatus = "'"+selectEstatus.selectedOptions[i].value+"'";
        }else{
            estatus = estatus+", '"+selectEstatus.selectedOptions[i].value+"'";
        }

        if (selectEstatus.selectedOptions[i].value == '1') {
            estatus = estatus+", '2'";
        }
    }

    var tipoSolicitud = "";
	var selectTipoSolicitud = document.getElementById('selectTipoSolicitud');
    for ( var i = 0; i < selectTipoSolicitud.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            tipoSolicitud = selectTipoSolicitud.selectedOptions[i].value;
        }else{
            tipoSolicitud = tipoSolicitud+", "+selectTipoSolicitud.selectedOptions[i].value;
        }
    }

    var folio = $("#txtFolio").val();
    var transno = $("#txtNoCaptura").val();
    var noOficio = "";//$("#txtNoOficio").val();
    var fechaDesde = $("#txtFechaDesde").val();
    var fechaHasta = $("#txtFechaHasta").val();

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerAdecuaciones',
	      legalid: legalid,
	      tagref: tagref,
	      tipoAdecuacion: tipoAdecuacion,
	      estatus: estatus,
	      tipoSolicitud: tipoSolicitud,
	      folio: folio,
	      transno: transno,
	      noOficio: noOficio,
	      fechaDesde: fechaDesde,
	      fechaHasta: fechaHasta
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
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
			var columnasExcel= [2, 4, 5, 7, 9, 10, 11, 12];
			var columnasVisuales= [0, 3, 4, 5, 7, 9, 10, 11, 12, 13];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

			// fnEjecutarVueGeneral();
		}else{
			ocultaCargandoGeneral();
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function fnCambioRazonSocial() {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	var legalid = "";
	var selectRazonSocial = document.getElementById('selectRazonSocial');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            legalid = selectRazonSocial.selectedOptions[i].value;
        }else{
            legalid = legalid+", "+selectRazonSocial.selectedOptions[i].value;
        }
    }

    muestraCargandoGeneral();
    
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
	  //console.log("Bien");
	  if(data.result){
	      //Si trae informacion
	      
	      dataJson = data.contenido.datos;
	      //console.log( "dataJson: " + JSON.stringify(dataJson) );
	      //alert(JSON.stringify(dataJson));
	      var contenido = "";
	      for (var info in dataJson) {
	        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
	      }
		$('#selectUnidadNegocio').empty();
		$('#selectUnidadNegocio').append(contenido);
		$('#selectUnidadNegocio').multiselect('rebuild');
		ocultaCargandoGeneral();
	  }else{
	  	ocultaCargandoGeneral();
	      // console.log("ERROR Modelo");
	      // console.log( JSON.stringify(data) ); 
	  }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
	// Fin Unidad de Negocio
}

function fnCambioUnidadNegocio() {
	//console.log("fnCambioRazonSocial");
	tagref = $("#selectUnidadNegocio").val();
}

function fnValidarProcesoCambiarEstatus() {
	// Deshabilitar Botones
	// for (var key in dataObjDatosBotones) {
	// 	$("#"+dataObjDatosBotones[key].namebutton).prop("disabled", true);	
	// }

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
        	obj.reglasValidadas = dataJsonNoCaptura[key].reglasValidadas;
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
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
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

function fnCambiarEstatus(statusid) {
	// console.log("fnEstatusValidar - statusid: "+statusid+" - estatusDiferentes: "+estatusDiferentes+" - seleccionoCaptura: "+seleccionoCaptura);
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();
	// console.log("dataJsonNoCapturaSeleccionados: "+JSON.stringify(dataJsonNoCapturaSeleccionados));
	
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var realizarProceso = 1;
		if (statusid != '0') {
			// Validar que no selecciono en captura
			for (var key in dataJsonNoCapturaSeleccionados) {
				if (dataJsonNoCapturaSeleccionados[key].reglasValidadas == '1') {
					realizarProceso = 0;
				}
			}
		}

		if (realizarProceso == 1) {
			//console.log("dataJsonNoCapturaSeleccionados: "+JSON.stringify(dataJsonNoCapturaSeleccionados)); 
			//muestraCargandoGeneral();
			dataObj = { 
				option: 'validarDisponibleNoCaptura',
				dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
				statusid: statusid
			};
			//Obtener datos de las bahias
			$.ajax({
				async:false,
	            cache:false,
				method: "POST",
				dataType:"json",
				url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
				data:dataObj
			})
			.done(function( data ) {
				//console.log("Bien");
				if(data.result){
					//Si trae informacion
					//dataJson = data.contenido.datos;
					//ocultaCargandoGeneral();
					fnActualizarEstatus(statusid);
				}else{
					ocultaCargandoGeneral();
					var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Validaciones</p></h3>';
					muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);
				}
			})
			.fail(function(result) {
				//ocultaCargandoGeneral();
				//console.log("ERROR");
				//console.log( result );
			});
		}else{
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono No. Captura Sin Reglas Validadas</p>');
		}
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
	//Obtener datos de las bahias
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			//ocultaCargandoGeneral();
			//if (Number(estatusGenerarLayout) == Number(data.contenido.estatus)) {
			if (Number(data.contenido.generaLayout) == 1) {
				// Generar Layotu
				fnGenerarLayout(data.contenido.datos, data.contenido.tipoLayout, data.contenido.mensaje);
			}else{
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				muestraModalGeneral(4, titulo, data.contenido.mensaje);
			}
			// Datos de las adecuaciones
			fnObtenerAdecuaciones();
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

function fnGenerarLayout(jsonData, tipoLayout, mensajeActualizacion) {
	//console.log("datos Layout: "+JSON.stringify(jsonData));
	var obj = new Object();
	obj.transno = 34;
	//fnGenerarArchivoLayout(funcionGenerarLayout, typeGenerarLayout, jsonData, tipoLayout);
	var link = fnGenerarArchivoLayoutSinModal(funcionGenerarLayout, typeGenerarLayout, jsonData, tipoLayout, 'Layout_Adec', 'Layout Adecuaciones');
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	muestraModalGeneral(4, titulo, mensajeActualizacion + link);
}

function fnConfirmacionEstatusAdecuacion(statusid, namebutton){
	// Obtener registros seleccionados
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();
    // console.log("dataJsonNoCapturaSeleccionados: "+JSON.stringify(dataJsonNoCapturaSeleccionados));
    // console.log("statusid: "+statusid);
	
	// Realiza mensaje de confirmacion para cambiar el estatus de la adecuacion
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = "";
	var realizarProceso = 1;
	var valEstatus = 0;
	if (statusid != '0') {
		// Validar que no selecciono en captura
		for (var key in dataJsonNoCapturaSeleccionados) {
			if (dataJsonNoCapturaSeleccionados[key].reglasValidadas == '1') {
				realizarProceso = 0;
			}
			// || dataJsonNoCapturaSeleccionados[key].statusid == '6'
			if ((dataJsonNoCapturaSeleccionados[key].statusid == '0'
				|| dataJsonNoCapturaSeleccionados[key].statusid == '7') && statusid != '97') {
				// Estatus no permitidos
				valEstatus = 1;
			}
		}
	}
	
	if (realizarProceso == 0) {
		// Selecciono sin reglas validadas
		muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono No. Captura Sin Reglas Validadas</p>');
		return true;
	}else if (valEstatus == 1) {
		// Selecciono estatus no permitido
		muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el estatus actual no es posible realizar la operación</p>');
		return true;
	}else if (dataJsonNoCapturaSeleccionados.length == 0) {
		// Vacio
		muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar un Folio para realizar el proceso</p>');
		return true;
	}else if (dataJsonNoCapturaSeleccionados.length > 1 && statusid == '97') {
		// Si es generarl Excel y se elige mas de una opción
		muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar solo un folio para descargar el Excel</p>');
		return true;
	}else if (dataJsonNoCapturaSeleccionados.length == 1) {
		// Selección 1
		mensaje = "<p>¿Está seguro de "+namebutton+" la Adecuación Presupuestal seleccionada?</p>";
		mensaje += "<p>Folio: <b>";
	}else {
		// Selección varios
		mensaje = "<p>¿Está seguro de "+namebutton+" las Adecuaciones Presupuestales seleccionadas?</p>";
		mensaje += "<p>Folios: <b>";
	}
	
	var num = 1;
	for (var key in dataJsonNoCapturaSeleccionados) {
		if (num == 1) {
			mensaje += " "+dataJsonNoCapturaSeleccionados[key].transno;
		}else{
			mensaje += ", "+dataJsonNoCapturaSeleccionados[key].transno;
		}
		num ++;
	}
	mensaje += "</b>";
	mensaje += "</p>";
	if (statusid == 6 && permisoUsarLayoutGeneral == '1') {
		mensaje += '<h5>Al generar el Layout se puede descargar en el Panel de Layout</h5>';
	}

	if (statusid == '97') {
		// Generar Excel
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnExcelPresupuestoGeneral('"+dataJsonNoCapturaSeleccionados[0].type+"', '"+dataJsonNoCapturaSeleccionados[0].transno+"', 'Excel')");
	} else {
		// Realizar proceso
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"')");
	}
}

/**
 * Función para obtener los registros seleccionados en la tabla
 * @return {[type]} Json con información seleccionada
 */
function fnObtenerDatosSeleccionados() {
    // Funcion para obtener los renglones seleccionados de la tabla
    var dataJsonNoCapturaSeleccionados = new Array();

    var griddata = $('#divTabla > #divContenidoTabla').jqxGrid('getdatainformation');
    var estatus = "";
    estatusDiferentes = 0;
	seleccionoCaptura = 0;
    for (var i = 0; i < griddata.rowscount; i++) {
        var id = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'id1');
        if (id == true) {
            var obj = new Object();
            obj.transno = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'transno');
            obj.type = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'type');
            obj.statusid = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'statusid');
            obj.reglasValidadas = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'reglasValidadas');
            dataJsonNoCapturaSeleccionados.push(obj);

            seleccionoCaptura = 1;
        	if (estatus == "") {
        		estatus = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'statusid');
        	}else if ((estatus) != ($('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'statusid'))) {
        		estatusDiferentes = 1;
        	}
        }
    }

    return dataJsonNoCapturaSeleccionados;
}

/**
 * Función para mostrar los botones del panel
 * @param  {[type]} divMostrar Id del div a donde se van agregar los botones
 * @return {[type]}            [description]
 */
function fnObtenerBotones(divMostrar) {
	//Opcion para operacion
	dataObj = { 
	        option: 'obtenerBotones',
	        type: ''
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
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
	    		var funciones = 'fnConfirmacionEstatusAdecuacion('+info[key].statusid+', \''+info[key].namebutton+'\')';
	    		contenido += '&nbsp;&nbsp;&nbsp; <component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
	    	}
	    	$('#'+divMostrar).append(contenido);
	    	fnEjecutarVueGeneral('divBotones');
	    }else{
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvieron los botones para realizar las operaciones</p>');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}