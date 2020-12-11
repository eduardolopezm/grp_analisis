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
	if (document.querySelector(".selectTipoPolizaSeguros")) {
		dataObj = { 
	        option: 'mostrarTipoPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectTipoPolizaSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 0);
	}

	if (document.querySelector(".selectAseguradoraSeguros")) {
		dataObj = { 
	        option: 'mostrarAseguradoraPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectAseguradoraSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 0);
	}

	if (document.querySelector(".selectCoberturaSeguros")) {
		dataObj = { 
	        option: 'mostrarCoberturaPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectCoberturaSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 0);
	}
	
	// Datos botones
	// fnObtenerBotones('divBotones');

	// Datos de las adecuaciones
	fnObtenerRegistrosSuficiencia();
});

function fnMostrarDetallePoliza(type=0, transno=0, tipoPoliza=0) {
	// Ver detalle de la póliza
	console.log("type: "+type+" - transno: "+transno+" - tipoPoliza: "+tipoPoliza);

	muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacionDetalle',
	      type: type,
	      transno: transno,
	      tipoPoliza: tipoPoliza
	    };
	
	$.ajax({
		async:false,
	    cache:false,
	    method: "POST",
	    dataType:"json",
	    url: "modelo/gestionPolizasPanelModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			ocultaCargandoGeneral();
			// dataJson = data.contenido.datos.detallePoliza;
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(4, titulo, data.contenido.datos.detallePoliza);
		}else{
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la información de detalle para la Póliza '+transno+'</p>');
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

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

    var selectTipoPoliza = "";
	var selectTipoSuficiencia = document.getElementById('selectTipoPoliza');
    for ( var i = 0; i < selectTipoSuficiencia.selectedOptions.length; i++) {
        if (i == 0) {
            selectTipoPoliza = "'"+selectTipoSuficiencia.selectedOptions[i].value+"'";
        }else{
            selectTipoPoliza = selectTipoPoliza+", '"+selectTipoSuficiencia.selectedOptions[i].value+"'";
        }
    }

    var selectAseguradora = "";
	var selectObj = document.getElementById('selectAseguradora');
    for ( var i = 0; i < selectObj.selectedOptions.length; i++) {
        if (i == 0) {
            selectAseguradora = "'"+selectObj.selectedOptions[i].value+"'";
        }else{
            selectAseguradora = selectAseguradora+", '"+selectObj.selectedOptions[i].value+"'";
        }
    }

    var selectCobertura = "";
	var selectObj = document.getElementById('selectCobertura');
    for ( var i = 0; i < selectObj.selectedOptions.length; i++) {
        if (i == 0) {
            selectCobertura = "'"+selectObj.selectedOptions[i].value+"'";
        }else{
            selectCobertura = selectCobertura+", '"+selectObj.selectedOptions[i].value+"'";
        }
    }

    var folio = $("#txtFolio").val();
    var folioPoliza = $("#txtFolioPoliza").val();
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
	      tipoPoliza: selectTipoPoliza,
	      folio: folio,
	      folioPoliza: folioPoliza,
	      selectAseguradora: selectAseguradora,
	      selectCobertura: selectCobertura
	    };
	
	$.ajax({
		async:false,
	    cache:false,
	    method: "POST",
	    dataType:"json",
	    url: "modelo/gestionPolizasPanelModelo.php",
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

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 2, 3, 5, 6, 7, 8, 9, 10, 11];
			var columnasVisuales= [1, 2, 4, 5, 6, 7, 8, 9, 10, 11];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
		}else{
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la información de las Pólizas</p>');
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
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
	      url: "modelo/gestionPolizasPanelModelo.php",
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
	    		} else{
	    			funciones = 'fnCambiarEstatus('+info[key].statusid+')';
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