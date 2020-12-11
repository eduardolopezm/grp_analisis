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

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	// Datos de las adecuaciones
	fnObtenerRegistrosSuficiencia();
});

function fnObtenerRegistrosSuficiencia() {
	//console.log("fnObtenerRegistrosSuficiencia");
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
	      option: 'obtenerSificiencia',
	      legalid: legalid,
	      tagref: tagref,
	      fechaDesde: fechaDesde,
	      fechaHasta: fechaHasta,
	      ue: ue,
	      tipoSuficiencia: tipoSuficiencia,
	      folio: folio
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/suficiencia_manual_panel_modelo.php",
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
			var columnasExcel= [1, 2, 4, 5, 6, 7, 8, 10];
			var columnasVisuales= [0, 1, 3, 4, 5, 6, 7, 8, 9, 14];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);

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

function fnValidarProcesoCambiarEstatus() {
	// Comparar check Seleccionados
	//console.log("dataJsonNoCaptura: "+JSON.stringify(dataJsonNoCaptura));
	var estatus = "";
	estatusDiferentes = 0;
	seleccionoCaptura = 0;
	dataJsonNoCapturaSeleccionados = new Array();
	for (var key in dataJsonNoCaptura) {
		//console.log("transno: "+dataJsonNoCaptura[key].transno);
		var check = $("#checkbox_"+dataJsonNoCaptura[key].nu_transno).prop('checked');
        if (check) {
        	var obj = new Object();
        	obj.transno = dataJsonNoCaptura[key].nu_transno;
        	obj.type = dataJsonNoCaptura[key].nu_type;
        	obj.statusid = dataJsonNoCaptura[key].statusid;
        	dataJsonNoCapturaSeleccionados.push(obj);
        	seleccionoCaptura = 1;
        	if (estatus == "") {
        		estatus = dataJsonNoCaptura[key].statusid; 
        	}else if (Number(estatus) != Number(dataJsonNoCaptura[key].statusid)) {
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

function fnVentanaSuficienciaManual() {
	var Link_Adecuaciones = document.getElementById("Link_NuevoGeneral");
    Link_Adecuaciones.click();
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
	    url: "modelo/suficiencia_manual_panel_modelo.php",
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
	var tagref = $("#selectUnidadNegocio").val();
}

function fnConfirmacionCancelarAdecuacion(statusid) {
	// Función para confirmación de la cancelación
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Desea Cancelar los Folios Seleccionados?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnActualizarEstatus('"+statusid+"')");
	}
}

function fnConfirmarAutorizacionSuficiencia(statusid) {
	// Confirmar Autotrizacion de Suficiencia
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Desea Autorizar los Folios Seleccionados?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnActualizarEstatus('"+statusid+"')");
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
		url: "modelo/suficiencia_manual_panel_modelo.php",
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
			fnObtenerRegistrosSuficiencia();
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

function fnConfirmarEstatusSuficiencia(statusid) {
	// Función para confirmación cambio de estatus de la suficiencia presupuestal
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Está Seguro de Avanzar la Suficiencia Presupuestal seleccionada?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"')");
	}
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
		var realizarProceso = 1;

		if (statusid == 99 || statusid == 0) {
			// Si es rechazar no validar informacion
			fnActualizarEstatus(statusid);
		}else if (realizarProceso == 1) {
			//console.log("dataJsonNoCapturaSeleccionados: "+JSON.stringify(dataJsonNoCapturaSeleccionados)); 
			//muestraCargandoGeneral();
			dataObj = { 
				option: 'validarDisponibleNoCaptura',
				dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados
			};
			//Obtener datos de las bahias
			$.ajax({
				async:false,
	            cache:false,
				method: "POST",
				dataType:"json",
				url: "modelo/suficiencia_manual_panel_modelo.php",
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
		}
	}
}

function fnConfirmarRechazoSuficiencia(statusid) {
	// Función para confirmación cambio de estatus de la suficiencia presupuestal
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Está Seguro de Rechazar la Suficiencia Presupuestal seleccionada?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"')");
	}
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
	      url: "modelo/suficiencia_manual_panel_modelo.php",
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
	    		if (info[key].statusid == 0) {
	    			funciones = 'fnConfirmacionCancelarAdecuacion('+info[key].statusid+')';
	    		}else if (info[key].statusid == 4) {
	    			funciones = 'fnConfirmarAutorizacionSuficiencia('+info[key].statusid+')';
	    		}else if (info[key].statusid == 99) {
	    			funciones = 'fnConfirmarRechazoSuficiencia('+info[key].statusid+')';
	    		} else{
	    			funciones = 'fnConfirmarEstatusSuficiencia('+info[key].statusid+')';
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