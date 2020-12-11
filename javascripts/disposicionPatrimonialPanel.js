/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var dataJsonNoCaptura = new Array();
var dataJsonNoCapturaSeleccionados = new Array();
var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Selecciono Folio con Estatus diferente, el Estatus debe ser igual</p>';
var mensajeSinNoCaptura = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin selección de Folio</p>';

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

    if (document.querySelector(".selectEstatusCompromiso")) {
		//Opcion para operacion
		dataObj = { 
			option: 'mostrarSelectEstatus'
		};

		fnSelectGeneralDatosAjax('.selectEstatusCompromiso', dataObj, 'modelo/disposicionPatrimonialPanelModelo.php', 0);
	}

	// Datos de la tabla
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

    var estatusCompromiso = "";
	var selectEstatusCompromiso = document.getElementById('selectEstatusCompromiso');
    for ( var i = 0; i < selectEstatusCompromiso.selectedOptions.length; i++) {
        if (i == 0) {
            estatusCompromiso = "'"+selectEstatusCompromiso.selectedOptions[i].value+"'";
        }else{
            estatusCompromiso = estatusCompromiso+", '"+selectEstatusCompromiso.selectedOptions[i].value+"'";
        }
    }

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerSificiencia',
	      legalid: legalid,
	      tagref: tagref,
	      fechaDesde: $("#txtFechaDesde").val(),
	      fechaHasta: $("#txtFechaHasta").val(),
	      ue: ue,
	      tipoSuficiencia: tipoSuficiencia,
	      folio: $("#txtFolioSuficiencia").val(),
	      txtArticulo: $("#txtArticulo").val(),
	      selectEstatusCompromiso: estatusCompromiso
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/disposicionPatrimonialPanelModelo.php",
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
			var columnasExcel= [1, 2, 3, 4, 6, 7, 9, 11];
			var columnasVisuales= [0, 1, 2, 3, 5, 6, 7, 9, 11, 12];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

			// fnEjecutarVueGeneral();
			//$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
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
 * Función para obtener los registros seleccionados en la tabla
 * @return {[type]} Json con información seleccionada
 */
function fnObtenerDatosSeleccionados() {
    // Funcion para obtener los renglones seleccionados de la tabla
    var dataJsonNoCapturaSeleccionados = new Array();

    var estatus = "";
    seleccionoCaptura = 0;
    var griddata = $('#divTabla > #divContenidoTabla').jqxGrid('getdatainformation');
    for (var i = 0; i < griddata.rowscount; i++) {
        var id = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'id1');
        if (id == true) {
            var obj = new Object();
            obj.transno = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'nu_transno');
            obj.type = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'nu_type');
            obj.statusid = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'statusid');
            dataJsonNoCapturaSeleccionados.push(obj);
            seleccionoCaptura = 1;
            if (estatus == "") {
        		estatus = obj.statusid;
        	}else if (Number(estatus) != Number(obj.statusid)) {
        		estatusDiferentes = 1;
        	}
        }
    }

    return dataJsonNoCapturaSeleccionados;
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
	    url: "modelo/disposicionPatrimonialPanelModelo.php",
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

function fnConfirmacionCancelarAdecuacion(statusid) {
	// Función para confirmación de la cancelación
	// Obtener registros seleccionados
	estatusDiferentes=0;
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Desea Cancelar los Folios Seleccionados?</p>';
		var estatustype="";

		$.each(dataJsonNoCapturaSeleccionados,function(index, el) {
            estatustype = el.statusid;
        });

        if(estatustype == "0" || estatustype == "4"){
			mensaje = '<p>Los folios seleccionados con estatus Autorizado y Cancelado no se puedes cancelar</p>';

			if (dataJsonNoCapturaSeleccionados.length == 1) {
				mensaje = '<p>El folio seleccionado con estatus Autorizado y/o Cancelado no se puede cancelar</p>';
			}

			muestraModalGeneral(3, titulo, mensaje);
        	return false;
        }

		if (dataJsonNoCapturaSeleccionados.length == 1) {
			mensaje = '<p>¿Desea Cancelar el Folio Seleccionado?</p>';
		}
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"','1')");
	}
}

function fnConfirmarAutorizacionSuficiencia(statusid) {
	// Confirmar Autotrizacion
	// Obtener registros seleccionados
	estatusDiferentes=0;
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Desea Autorizar los Folios Seleccionados?</p>';
		
		$.each(dataJsonNoCapturaSeleccionados,function(index, el) {
            estatustype = el.statusid;
        });

        if(estatustype == "0"){
			mensaje = '<p>Los folios seleccionados con estatus Cancelado no se pueden autorizar</p>';

			if (dataJsonNoCapturaSeleccionados.length == 1) {
				mensaje = '<p>El folio seleccionado con estatus  Cancelado no se puede autorizar</p>';
			}

			muestraModalGeneral(3, titulo, mensaje);
        	return false;
        }


		if (dataJsonNoCapturaSeleccionados.length == 1) {
			mensaje = '<p>¿Desea Autorizar el Folio Seleccionado?</p>';
		}
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"',0,1)");
	}
}

function fnActualizarEstatus(statusid) {
	// console.log("fnActualizarEstatus");
	muestraCargandoGeneral();
	// return true;
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
		url: "modelo/disposicionPatrimonialPanelModelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.mensaje);
			// Datos de las adecuaciones
			fnObtenerRegistrosSuficiencia();
		}else{
			ocultaCargandoGeneral();
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
	// Función para confirmación cambio de estatus
	// Obtener registros seleccionados
	estatusDiferentes=0;
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Está Seguro de Avanzar los Folios Seleccionados?</p>';
		if (dataJsonNoCapturaSeleccionados.length == 1) {
			mensaje = '<p>¿Está Seguro de Avanzar el Folio Seleccionado?</p>';
		}
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCambiarEstatus('"+statusid+"')");
	}
}

function fnCambiarEstatus(statusid,cancelacion=0, autorizar=0) {
	//console.log("fnEstatusValidar - statusid: "+statusid+" - estatusDiferentes: "+estatusDiferentes);
	muestraCargandoGeneral();
	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var realizarProceso = 1;

		if (statusid == 99) {
			// Si es rechazar no validar informacion / autorizar
			fnActualizarEstatus(statusid);
		}else if (realizarProceso == 1) {
			// console.log("dataJsonNoCapturaSeleccionados: "+JSON.stringify(dataJsonNoCapturaSeleccionados));
			muestraCargandoGeneral();
			dataObj = { 
				option: 'validarDisponibleNoCaptura',
				dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
				statusid: statusid,
				cancelar : cancelacion,
				autorizar:autorizar
			};
			//Obtener datos de las bahias
			$.ajax({
				async:false,
	            cache:false,
				method: "POST",
				dataType:"json",
				url: "modelo/disposicionPatrimonialPanelModelo.php",
				data:dataObj
			})
			.done(function( data ) {
				//console.log("Bien");
				if(data.result){
					//Si trae informacion
					//dataJson = data.contenido.datos;
					ocultaCargandoGeneral();
					//console.log('pruebas');
					fnActualizarEstatus(statusid);
				}else{
					ocultaCargandoGeneral();
					var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Validaciones</p></h3>';
					muestraModalGeneral(4, titulo, data.contenido.mensajeErrores);
				}
			})
			.fail(function(result) {
				ocultaCargandoGeneral();
				//console.log("ERROR");
				//console.log( result );
			});
		}
	}
}

function fnConfirmarRechazoSuficiencia(statusid) {
	// Función para confirmación cambio de estatus de la suficiencia presupuestal
	// Obtener registros seleccionados
	estatusDiferentes=0;
	dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

	if (seleccionoCaptura == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
	}else if (estatusDiferentes == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
	}else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Está Seguro de Rechazar los Folios Seleccionados?</p>';


		$.each(dataJsonNoCapturaSeleccionados,function(index, el) {
            estatustype = el.statusid;
        });

        if(estatustype == "0" || estatustype == "4"){
			mensaje = '<p>Los folios seleccionados con estatus Autorizado y Cancelado no se pueden rechazar</p>';

			if (dataJsonNoCapturaSeleccionados.length == 1) {
				mensaje = '<p>El folio seleccionado con estatus Autorizado y/o Cancelado no se puede rechazar</p>';
			}

			muestraModalGeneral(3, titulo, mensaje);
        	return false;
        }


		if (dataJsonNoCapturaSeleccionados.length == 1) {
			mensaje = '<p>¿Está Seguro de Rechazar el Folio Seleccionado?</p>';
		}
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
	      url: "modelo/disposicionPatrimonialPanelModelo.php",
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
	    		} else {
	    			funciones = 'fnConfirmarEstatusSuficiencia('+info[key].statusid+')';
	    		}
	    		contenido += '&nbsp;&nbsp;&nbsp; <component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
	    		//contenido += '&nbsp;&nbsp;&nbsp; \
	    		//<button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" onclick="'+funciones+'" class="btn btn-default botonVerde '+info[key].clases+'">'+info[key].namebutton+'</button>';
	    	}
	    	$('#'+divMostrar).append(contenido);
	    	fnEjecutarVueGeneral('divBotones');
	    }else{
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvieron los botones para realizar las operaciones</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}