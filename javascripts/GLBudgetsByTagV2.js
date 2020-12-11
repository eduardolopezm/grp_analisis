/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */
//
var dataJsonMeses = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

// Nombre de la vista para la tabla de Reduccion y Ampliacion
var tablaReducciones = "tablaReducciones";
var tablaAmpliaciones = "tablaAmpliaciones";

// Datos de la busqueda en Reduccion y Ampliacion
var banderaRealizarBusqueda = 1;
var datosPresupuestosBusqueda = {};
var datosPresupuestosBusquedaReducciones = new Array();
var datosPresupuestosBusquedaAmpliaciones = new Array();
var datosPresupuestosBusquedaFiltros = new Array();
var idClavePresupuestoReducciones = 0;
var idClavePresupuestoAmpliaciones = 0;
var datosReducciones = new Array();
var datosAmpliaciones = new Array();
var datosEliminarRedAmp = new Array();

// Identificador para el panel, cambiar tambien en la vista fnObtenerPresupuestoBusqueda()
var panelReducciones = 1; 
var panelAmpliaciones = 2;
// Totales de Reduccion y Ampliacion
var totalReducciones = 0;
var totalAmpliaciones = 0;
var decimales = 2;

var type = 250;
// Se declara enla vista
//var transno = <?php echo $_SESSION['noCaptura']; ?>;

// Filtro Generales
var legalid = "";
var tagref = "";
var tipoAdecuacion = "";

var ramoDefault = "08";
var tRegDefault = "9";
var jusRDefault = "099";

var errorValidacion = 0;
var validacionComponente = 0;
// Estatus para guardar en la adecuacion 1 en captura, 2 Validada
var statusGuardar = 1;
var modificoEncabezado = 0;

// Inicio Tipo de Documento Adecuaciones por Partida
var dataJsonTipoDocReduccion = new Array();
var dataJsonTipoDocAmpliacion = new Array();

var dataJsonRamo = new Array();
var dataJsonRazonSocial = new Array();
// Tipo de Adecuacion
var dataJsonTipoAdecuacion = new Array();
var dataJsonTipoSolicitud = new Array();

// Numero de Linea en Reduccion y Ampliacion
var numLineaReducciones = 1;
var numLineaAmpliaciones = 1;

// Nombres de los elementos
var nombreElementosFiltroReducciones = new Array();
var nombreElementosFiltroAmpliaciones = new Array();
// Datos de los filtros Reduccion y Ampliacion
var dataJsonFiltrosReduccion = new Array();
var dataJsonFiltrosAmpliacion = new Array();
// Nombre de la vista para los filtros
var divFiltroReduccion = "divFiltrosReduccion";
var divFiltroAmpliacion = "divFiltrosAmpliacion";

// Titulo del mensaje de validaciones
var tituloModalValidaciones = "Validaciones";

$( document ).ready(function() {
	if (document.querySelector(".selectTipoDocumentoAdecuaciones")) {
		dataObj = { 
	        option: 'mostrarTipoAdecuacion'
	    };
		fnSelectGeneralDatosAjax('.selectTipoDocumentoAdecuaciones', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php');
	}
	if (document.querySelector(".selectTipoSolicitud")) {
		fnTipoDeSolicitud('', '.selectTipoSolicitud');
	}
	if (document.querySelector(".selectRamo")) {
		dataObj = { 
	        option: 'mostrarRamo'
	    };
		fnSelectGeneralDatosAjax('.selectRamo', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, ramoDefault);
	}
	if (document.querySelector(".selectTipoReg")) {
		dataObj = { 
	        option: 'mostrarTipoReg'
	    };
		fnSelectGeneralDatosAjax('.selectTipoReg', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, tRegDefault);
	}
	if (document.querySelector(".selectJusR")) {
		dataObj = { 
	        option: 'mostrarJusR'
	    };
		fnSelectGeneralDatosAjax('.selectJusR', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, jusRDefault);
	}
	if (document.querySelector(".selectConcR23")) {
		dataObj = { 
	        option: 'mostrarConcR23'
	    };
		fnSelectGeneralDatosAjax('.selectConcR23', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1);

		// Validacion ConcR23
		fnSelectConcR23();
	}
	if (document.querySelector(".selectUnidadNegocioSinRes")) {
		dataObj = { 
	        option: 'mostrarURSinRestricccion'
	    };
		fnSelectGeneralDatosAjax('.selectUnidadNegocioSinRes', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1);
	}

	// Inicio Dependencia
	dataObj = { 
	      option: 'mostrarRazonSocial'
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
	      dataJsonRazonSocial = data.contenido.datos;
	  }else{
	      //console.log("ERROR Modelo");
	      //console.log( JSON.stringify(data) ); 
	  }
	})
	.fail(function(result) {
	  //console.log("ERROR");
	  //console.log( result );
	});
	// Fin Dependencia

	//Opcion para operacion
	dataObj = { 
		option: 'mostrarTipoDocumentoPartida'
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
		if(data.result){
			dataJsonTipoDocReduccion = data.contenido.datosReduccion;
			dataJsonTipoDocAmpliacion = data.contenido.datosAmpliacion;
			$('.selectTipoAdecuacionReduccion').append( fnCrearDatosSelect(dataJsonTipoDocReduccion) );
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});
	// Fin Tipo de Documento Adecuaciones por Partida

	// Datos botones
	fnObtenerBotones('divBotones');

	// Datos lista de busqueda
	fnObtenerPresupuestoBusqueda();

	//Obtener Datos de un No. Captura
	fnObtenerPresupuestoNoCaptura();

	// Obtener los filtros para la busqueda
	fnObtenerFiltrosClave();

	// Deshabilitra pagiga si se va a autorizar
	fnDeshabilitaPagAdecuaciones();
});

function fnCambioInfoCaptura() {
	modificoEncabezado = 1; // Si hay cambios poner 1 para validar primero
}

function fnDeshabilitaPagAdecuaciones() {
	// Se va autorizar y deshabilitar pagina
	if (autorizarGeneral == 1) {
		// Deshabilitar
		$("#txtCtrInt").prop("disabled", true);
		$("#txtDicUpi").prop("disabled", true);
		$("#txtFechaCaptura").prop("disabled", true);
		$("#txtFechaApl").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", true);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		$("#txtBuscarReducciones").prop("disabled", true);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#txtJustificacion").prop("disabled", true);

		$('#selectRamoCr').multiselect('disable');
		$('#selectUnidadNegocio').multiselect('disable');
		$('#selectRamoRec').multiselect('disable');
		$('#selectUnidadNegocioRec').multiselect('disable');
		$('#selectUnidadEjecutora').multiselect('disable');
		$('#txtTipoReg').multiselect('disable');
		$('#txtJusR').multiselect('disable');

		$('#selectTipoDoc').multiselect('disable');
		$('#selectTipoSolicitud').multiselect('disable');
		$('#selectConcR23').multiselect('disable');
	}

	// Habilitar
	if (permisoEditarEstCapturado == 1) {
		$("#txtProcesoSicop").prop("disabled", false);
		$("#txtFolioMap").prop("disabled", false);
		$("#txtFechaApl").prop("disabled", false);
		$("#txtJustificacion").prop("disabled", false);
	}
	if (soloActFoliosAutorizada == 1) {
		$("#txtFechaApl").prop("disabled", true);
		$("#txtJustificacion").prop("disabled", true);
	}
}

function fnSelectConcR23() {
	// console.log("*****fnSelectConcR23******");
	// console.log("selectTipoDoc: "+$('#selectTipoDoc').val());
	// console.log("selectTipoSolicitud: "+$('#selectTipoSolicitud').val());
	var habilitar = 0;
	if ($('#selectTipoDoc').val() == '6' && $('#selectTipoSolicitud').val() == '3') {
		// Validacion tipo ER
		habilitar = 1;
	}else if ($('#selectTipoDoc').val() == '6' && $('#selectTipoSolicitud').val() == '4') {
		// Validacion tipo EA
		habilitar = 1;
	}else if ($('#selectTipoDoc').val() == '6' && $('#selectTipoSolicitud').val() == '5') {
		// Validacion tipo EI
		habilitar = 1;
	}
	
	if (habilitar == 1) {
        $(".selectConcR23").multiselect('rebuild');
        $('#selectConcR23').multiselect('enable');
	}else{
		if (autorizarGeneral != 1) {
			$('#selectConcR23').val(null);
			$(".selectConcR23").multiselect('rebuild');
        	$('#selectConcR23').multiselect('disable');
		}

		if (autorizarGeneral == 1) {
			$('#selectConcR23').multiselect('disable');
		}
	}
}

function fnObtenerFiltrosClave() {
	muestraCargandoGeneral();
	//Opcion para operacion
	dataObj = { 
		option: 'datosConfiguracionClave'
	};
	
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
			ocultaCargandoGeneral();
			//Si trae informacion
			dataJsonFiltrosReduccion = data.contenido.datos;
			dataJsonFiltrosAmpliacion = data.contenido.datos;
			fnCrearFiltrosClave(divFiltroReduccion, dataJsonFiltrosReduccion, panelReducciones);
			fnCrearFiltrosClave(divFiltroAmpliacion, dataJsonFiltrosAmpliacion, panelAmpliaciones);
		}else{
			ocultaCargandoGeneral();
			//ocultaCargandoGeneral();
			// muestraMensaje('No se pudo traer la Configuración', 3, 'divMensajeClaveNueva', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo traer la Configuración para filtros de la Clave Presupuestal</p>';
			muestraModalGeneral(3, titulo, mensaje);
		}
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		//ocultaCargandoGeneral();
		//console.log("ERROR");
		//console.log( result );
	});
}

function fnCrearFiltrosClave(divFormulario, dataJson, panel) {
	var contenido = '';

	for (var key in dataJson) {
		var elementoMostrar = "";
		var nombreElemento = 'config_clave_nueva_'+panel+'_'+dataJson[key].nombre;

		var obj = new Object();
		obj.nombre = nombreElemento;
		obj.valor = "";
		obj.campoPresupuesto = dataJson[key].campoPresupuesto;
		obj.tabla = dataJson[key].tabla;
		obj.campo = dataJson[key].campo;
		obj.idClavePresupuesto = dataJson[key].idClavePresupuesto;

		if (panel == panelReducciones) {
			nombreElementosFiltroReducciones.push(obj);
		}else if (panel == panelAmpliaciones) {
			nombreElementosFiltroAmpliaciones.push(obj);
		}
		// onchange="fnObtenerPresupuestoBusqueda(\''+panel+'\')"
		if (dataJson[key].campoPresupuesto == 'anho') {
			var año = "";
			for (var key2 in dataJson[key].infoSelect) {
				año = dataJson[key].infoSelect[key2].value;
			}
			elementoMostrar += '<input type="text" id="'+nombreElemento+'" name="'+nombreElemento+'" value="'+año+'" placeholder="Año" title="Año" class="form-control" onpaste="return false" onkeypress="return soloNumeros(event)" maxlength="4" style="width: 100%;" />';
		}else{
			elementoMostrar += '<select id="'+nombreElemento+'" name="'+nombreElemento+'" class="form-control '+nombreElemento+'">';
			elementoMostrar += '<option value="-1">Seleccionar...</option>';

			for (var key2 in dataJson[key].infoSelect) {
				//console.log("value: "+dataJson[key].infoSelect[key2].value+" - texto: "+dataJson[key].infoSelect[key2].texto);
				elementoMostrar += '<option value="'+dataJson[key].infoSelect[key2].value+'">'+dataJson[key].infoSelect[key2].texto+'</option>';
			}

			elementoMostrar += '</select>';
		}

		contenido += '\
					<div class="col-md-12">\
						<div class="form-inline row">\
							<div class="col-md-3">\
								<span><label>'+dataJson[key].nombre+': </label></span>\
							</div>\
							<div class="col-md-9">'+elementoMostrar+'</div>\
						</div>\
					</div><br><br>';
	}

	if (contenido == "") {
		contenido += '<p class="text-danger">Sin Configuración</p>';
	}

	contenido = '<div class="panel-body">'+contenido+'</div>'; // Cierre de panel-body
	
	$('#'+divFormulario).empty();
	$('#'+divFormulario).append(contenido);

	if (panel == panelReducciones) {
		for (var key in nombreElementosFiltroReducciones) {
			fnFormatoSelectGeneral("."+nombreElementosFiltroReducciones[key].nombre);
		}
	}else if (panel == panelAmpliaciones) {
		for (var key in nombreElementosFiltroAmpliaciones) {
			fnFormatoSelectGeneral("."+nombreElementosFiltroAmpliaciones[key].nombre);
		}
	}
	
	//ocultaCargandoGeneral();
}

function fnCambioRamo(nomRamo, nomDependencia) {
	//console.log("fnCambioRamo");
	// Inicio Ramo
	
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarRazonSocial',
	      ramo: $("#"+nomRamo).val()
	    };

	fnSelectGeneralDatosAjax('#'+nomDependencia, dataObj, 'modelo/GLBudgetsByTagV2_modelo.php');

	// Datos lista de busqueda
	//fnObtenerPresupuestoBusqueda();
}

function fnCambioRazonSocial(nomRazonSocial, nomUnidadNegocio) {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	legalid = $("#"+nomRazonSocial).val();
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };

	fnSelectGeneralDatosAjax('#'+nomUnidadNegocio, dataObj, 'modelo/GLBudgetsByTagV2_modelo.php');

	// Datos lista de busqueda
	//fnObtenerPresupuestoBusqueda();
}

function fnCambioUnidadNegocio() {
	//console.log("fnCambioRazonSocial");
	tagref = $("#selectUnidadNegocio").val();
	// Datos lista de busqueda
	//fnObtenerPresupuestoBusqueda();

	if (tagref != '-1') {
        //$('.selectTipoDocumentoAdecuaciones').append( fnCrearDatosSelect(dataJsonTipoAdecuacion, '.selectTipoDocumentoAdecuaciones', '') );
        //fnCrearDatosSelect(dataJsonTipoAdecuacion, '.selectTipoDocumentoAdecuaciones', '');
	}
}

function fnTipoDeSolicitud(nomTipoAdecuacion, nombreSelect, incial=1) {
	// Inicio Tipo de Solicitud
	var tipo = "";
	if ($("#"+nomTipoAdecuacion).val() != "" && $("#"+nomTipoAdecuacion).val() != '0') {
		tipo = $("#"+nomTipoAdecuacion).val();
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
				$(nombreSelect).append( fnCrearDatosSelect(dataJsonTipoSolicitud) );
				$(nombreSelect).multiselect('rebuild');
			}else{
				fnCrearDatosSelect(dataJsonTipoSolicitud, nombreSelect, '');
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

function fnPresupuestoCaptura(estatus, msjvalidaciones="") {
	//console.log("fnPresupuestoCaptura");
	// console.log("datosReducciones: "+JSON.stringify(datosReducciones));
	// console.log("datosAmpliaciones: "+JSON.stringify(datosAmpliaciones));

	muestraCargandoGeneral();
	var datosCapturaReducciones = new Array();
	var datosCapturaAmpliaciones = new Array();

	//Generar datos Reducciones
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJson2 = datosReducciones[key];
			var obj = new Object();
			obj.accountcode = dataJson2[key2].accountcode;
			obj.idClavePresupuesto = dataJson2[key2].idClavePresupuesto;
			obj.Enero = dataJson2[key2].EneroSel;
			obj.Febrero = dataJson2[key2].FebreroSel;
			obj.Marzo = dataJson2[key2].MarzoSel;
			obj.Abril = dataJson2[key2].AbrilSel;
			obj.Mayo = dataJson2[key2].MayoSel;
			obj.Junio = dataJson2[key2].JunioSel;
			obj.Julio = dataJson2[key2].JulioSel;
			obj.Agosto = dataJson2[key2].AgostoSel;
			obj.Septiembre = dataJson2[key2].SeptiembreSel;
			obj.Octubre = dataJson2[key2].OctubreSel;
			obj.Noviembre = dataJson2[key2].NoviembreSel;
			obj.Diciembre = dataJson2[key2].DiciembreSel;
			obj.tipoAfectacion = dataJson2[key2].tipoAfectacion;
			obj.año = dataJson2[key2].año;
			obj.partida_esp = dataJson2[key2].partida_esp;
			obj.datosClave = dataJson2[key2].datosClave;
			datosCapturaReducciones.push(obj);
		}
	}

	//Generar datos Ampliaciones
	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJson2 = datosAmpliaciones[key];
			var obj = new Object();
			obj.accountcode = dataJson2[key2].accountcode;
			obj.idClavePresupuesto = dataJson2[key2].idClavePresupuesto;
			obj.Enero = dataJson2[key2].EneroSel;
			obj.Febrero = dataJson2[key2].FebreroSel;
			obj.Marzo = dataJson2[key2].MarzoSel;
			obj.Abril = dataJson2[key2].AbrilSel;
			obj.Mayo = dataJson2[key2].MayoSel;
			obj.Junio = dataJson2[key2].JunioSel;
			obj.Julio = dataJson2[key2].JulioSel;
			obj.Agosto = dataJson2[key2].AgostoSel;
			obj.Septiembre = dataJson2[key2].SeptiembreSel;
			obj.Octubre = dataJson2[key2].OctubreSel;
			obj.Noviembre = dataJson2[key2].NoviembreSel;
			obj.Diciembre = dataJson2[key2].DiciembreSel;
			obj.tipoAfectacion = dataJson2[key2].tipoAfectacion;
			obj.año = dataJson2[key2].año;
			obj.partida_esp = dataJson2[key2].partida_esp;
			obj.datosClave = dataJson2[key2].datosClave;
			datosCapturaAmpliaciones.push(obj);
		}
	}

	// console.log("datosCapturaReducciones: "+JSON.stringify(datosCapturaReducciones));
	// console.log("datosCapturaAmpliaciones: "+JSON.stringify(datosCapturaAmpliaciones));
	// return true;

	legalid = $('#selectRazonSocial').val();
	tagref = $('#selectUnidadNegocio').val();
	tipoAdecuacion = $('#selectTipoDoc').val();
	statusname = "";

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

	if (tagref == '-1' || tagref == '0') {
		ocultaCargandoGeneral();
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UR para continuar con el proceso</p>';
        muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if ($('#selectUnidadEjecutora').val() == '-1' || $('#selectUnidadEjecutora').val() == '0') {
		ocultaCargandoGeneral();
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UE para continuar con el proceso</p>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
	}
	
	if ($('#selectTipoDoc').val() == '0' || $('#selectTipoSolicitud').val() == '0') {
		ocultaCargandoGeneral();
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar Clase y Tipo de Solicitud</p>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
	}

	if (datosCapturaReducciones.length == 0 && datosCapturaAmpliaciones.length == 0) {
		// Validar que tenga claves cuando no se tenga folio
		ocultaCargandoGeneral();
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Claves para Reducciones y/o Ampliaciones</p>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
	}

	var mensaje = fnValidarSeleccionTipoAfectacion(1);
	if (mensaje != '') {
		ocultaCargandoGeneral();
        muestraModalGeneral(3, titulo, mensaje);
        return true;
	}

	if (datosEliminarRedAmp.length > 0 && transno != 0) {
		// Validar si existen registros para eliminar antes del guardado
		// console.log("entra if de eliminar datosEliminarRedAmp");
		for (var key in datosEliminarRedAmp) {
			// datosEliminarRedAmp[key].num
			var res = fnQuitarRenglonDatosArray(datosEliminarRedAmp[key].type, datosEliminarRedAmp[key].transno, datosEliminarRedAmp[key].clave, datosEliminarRedAmp[key].tipo, datosEliminarRedAmp[key].tipoAdecuacion);
		}
	}
	// console.log("datosEliminarRedAmp: "+JSON.stringify(datosEliminarRedAmp));
	
	modificoEncabezado = 0;

	//Opcion para operacion
	dataObj = { 
	        option: 'capturaPresupuesto',
	        datosCapturaReducciones: datosCapturaReducciones, //JSON.parse(JSON.stringify(datosCapturaReducciones)), 
			datosCapturaAmpliaciones: datosCapturaAmpliaciones,
			type: type,
			transno: transno,
			legalid: legalid,
			tagref: tagref,
			estatus: statusGuardar,
			tipoAdecuacion: tipoAdecuacion,
			noOficio: '', //$('#txtNoOficio').val(),
			fechaAplicacion: $('#txtFechaApl').val(),
			centroContable: $('#txtCentroContable').val(),
			tipoReg: $('#txtTipoReg').val(),
			jusR: $('#txtJusR').val(),
			dicatenUpi: $('#txtDicUpi').val(),
			controlInterno: $('#txtCtrInt').val(),
			justificacion: $('#txtJustificacion').val(),
			tipoSolicitud: $('#selectTipoSolicitud').val(),
			concR23: $('#selectConcR23').val(),
			procesoSicop: $('#txtProcesoSicop').val(),
			txtFolioMap: $('#txtFolioMap').val(),
			tagrefReceptora: $('#selectUnidadNegocioRec').val(),
			estatusAdecuacionGeneral: estatusAdecuacionGeneral,
			ueCreadora: $('#selectUnidadEjecutora').val()
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GLBudgetsByTagV2_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	transno = data.contenido.datos.transno;
	    	if (transno != 0) {
	    		$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.datos.transno);
				$('#txtEstatus').empty();
				$('#txtEstatus').append(data.contenido.datos.statusname);

				// Validacion ConcR23
				fnSelectConcR23();

				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, data.Mensaje);
	    	}
	    	if (transno != 0 && 1 == 2) {
		    	if (msjvalidaciones != "") {
					var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> '+tituloModalValidaciones+'</p></h3>';
					muestraModalGeneral(4, titulo, msjvalidaciones);

					muestraMensajeTiempo(data.Mensaje, 1, 'ModalGeneral_Advertencia', 5000);
				}else if (!$('#ModalGeneral').is(':hidden')) {
		    		muestraMensajeTiempo(data.Mensaje, 1, 'ModalGeneral_Advertencia', 5000);
		    	}else{
		    		//muestraMensajeTiempo(data.Mensaje, 1, 'divMensajeOperacion', 5000);
		    		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    		muestraModalGeneral(3, titulo, data.Mensaje);
		    	}

		    	fnValidaciones("");
	    	}
	    }else{
	    	//Obtener Datos de un No. Captura
			//fnObtenerPresupuestoNoCaptura();
			ocultaCargandoGeneral();

			if (msjvalidaciones != "") {
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> '+tituloModalValidaciones+'</p></h3>';
				muestraModalGeneral(4, titulo, msjvalidaciones);

				muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
			}else if (!$('#ModalGeneral').is(':hidden')) {
	    		muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
	    	}else{
	    		//muestraMensajeTiempo(data.Mensaje, 3, 'divMensajeOperacion', 5000);
	    		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, data.Mensaje);
	    	}
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnObtenerPresupuestoEnter(evento, idTabla, panel) {
	if (evento.keyCode == 13) {
        //console.log("valor: "+evento.target.value+" - idTabla: "+idTabla+ " - panel: "+panel);
        var datos = evento.target.value.split(",");
        if (datos[0] != "") {
        	//console.log("datosBsucar: "+JSON.stringify(datosPresupuestosBusqueda));
        	for (var key in datosPresupuestosBusqueda) {
				if (datosPresupuestosBusqueda[key].valorLista == datos[0]) {
					if (panel == panelReducciones) {
						$('#txtBuscarReducciones').val("");
					}else if (panel == panelAmpliaciones) {
						$('#txtBuscarApliaciones').val("");
					}
					fnObtenerPresupuesto(datosPresupuestosBusqueda[key].accountcode, idTabla, panel, datos[1], 'Nuevo');
					break;
				}
			}
        }
        return false;
    }
}

function fnObtenerPresupuestoNoCaptura() {
	dataObj = { 
	        option: 'cargarInfoNoCaptura',
			type: type,
			transno: transno,
			datosClave: '1',
			datosClaveAdecuacion: '1'
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
	    	//muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
	    	dataJson=data.contenido.datos;
	    	//console.log("datos: "+JSON.stringify(dataJson));
	    	
	    	if (dataJson != null) {
	    		$('#'+tablaReducciones+' tbody').empty();
		    	$('#'+tablaAmpliaciones+' tbody').empty();

		    	if (data.contenido.transno != 0) {
		    		$('#txtNoCaptura').empty();
					$('#txtNoCaptura').append(data.contenido.transno);
					$('#txtEstatus').empty();
					$('#txtEstatus').append(data.contenido.statusname);
					statusGuardar = data.contenido.estatus;
					$('#txtFechaCaptura').val("");
					if (data.contenido.fechaCaptura == "" || data.contenido.fechaCaptura == null) {
						$('#txtFechaCaptura').val(""+fechaActualAde);
					}else{
						$('#txtFechaCaptura').val(""+data.contenido.fechaCaptura);
					}
					// $('#txtNoOficio').val("");
					// $('#txtNoOficio').val(""+data.contenido.noOficio);
					$('#txtFolio').empty();
					$('#txtFolio').append(data.contenido.folio);
					$('#txtFechaApl').val("");
					if (data.contenido.fechaAplicacion == "" || data.contenido.fechaAplicacion == null) {
						$('#txtFechaApl').val(""+fechaActualAde);
					}else{
						$('#txtFechaApl').val(""+data.contenido.fechaAplicacion);
					}
					$('#txtCentroContable').val("");
					$('#txtCentroContable').val(""+data.contenido.centroContable);
					
					if (data.contenido.tipoReg != "") {
						$('#txtTipoReg').val(''+data.contenido.tipoReg);
						$('#txtTipoReg').multiselect('rebuild');
					}
					
					if (data.contenido.jusR != "") {
						$('#txtJusR').val(''+data.contenido.jusR);
						$('#txtJusR').multiselect('rebuild');
					}

					$('#txtDicUpi').val("");
					$('#txtDicUpi').val(""+data.contenido.dicatenUpi);
					$('#txtCtrInt').val("");
					$('#txtCtrInt').val(""+data.contenido.controlInterno);
					$('#txtJustificacion').val("");
					$('#txtJustificacion').val(""+data.contenido.justificacion);
					// console.log("concR23: "+data.contenido.concR23);
					if (data.contenido.concR23 != '0' && data.contenido.concR23 != "") {
						$('#selectConcR23').val(''+data.contenido.concR23);
						$('#selectConcR23').multiselect('rebuild');
					}

					$('#txtProcesoSicop').val("");
					$('#txtProcesoSicop').val(""+data.contenido.procesoSicop);

					$('#txtFolioMap').val("");
					$('#txtFolioMap').val(""+data.contenido.txtFolioMap);

			    	if (data.contenido.tipoAdecuacion != 0 && data.contenido.tipoAdecuacion != "") {
			    		dataObj = { 
					        option: 'mostrarTipoAdecuacion'
					    };
						fnSelectGeneralDatosAjax('#selectTipoDoc', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, data.contenido.tipoAdecuacion);
						// $('#selectTipoDoc').multiselect('disable');
					}
					
					if (data.contenido.tipoSolicitud != '0' && data.contenido.tipoSolicitud != "") {
						dataObj = { 
							option: 'mostrarTipoSolicitud',
							tipoAdecuacion: $('#selectTipoDoc').val()
						};

						fnSelectGeneralDatosAjax('#selectTipoSolicitud', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, data.contenido.tipoSolicitud);
						// $('#selectTipoSolicitud').multiselect('disable');
					}

					if (data.contenido.legalid != "") {
						legalid = data.contenido.legalid;
						$('#selectRazonSocial').selectpicker('val', ''+data.contenido.legalid);
						$("#selectRazonSocial").multiselect("refresh");
						$(".selectRazonSocial").css("display", "none");
					}

					if (data.contenido.tagref != "") {
						tagref = data.contenido.tagref;
						$('#selectUnidadNegocio').val(''+tagref);
						$('#selectUnidadNegocio').multiselect('rebuild');
					}
					
					if (data.contenido.tagrefReceptora != "") {
						$('#selectUnidadNegocioRec').val(''+data.contenido.tagrefReceptora);
						$('#selectUnidadNegocioRec').multiselect('rebuild');
					}

					if (data.contenido.ueCreadora != "") {
						$('#selectUnidadEjecutora').val(''+data.contenido.ueCreadora);
						$('#selectUnidadEjecutora').multiselect('rebuild');
					}

					// Validacion ConcR23
					fnSelectConcR23();
				}

		    	idClavePresupuestoReducciones = 0;
				idClavePresupuestoAmpliaciones = 0;
				datosReducciones = new Array();
				datosAmpliaciones = new Array();
				totalReducciones = 0;
				totalAmpliaciones = 0;

		    	for (var key in dataJson) {
					for (var key2 in dataJson[key]) {
						var dataJson2 = dataJson[key];
		    			if (dataJson2[key2].tipoMovimiento == "Reduccion") {
		    				fnMostrarPresupuesto(dataJson2, tablaReducciones, panelReducciones);
		    				datosReducciones.push(dataJson2);
		    			}else{
		    				fnMostrarPresupuesto(dataJson2, tablaAmpliaciones, panelAmpliaciones);
		    				datosAmpliaciones.push(dataJson2);
		    			}
		    		}
		    	}

		    	// Calcular totales por clave y renglon
				fnCalcularTotalesClaveRenglon();

		    	// Datos lista de busqueda
				//fnObtenerPresupuestoBusqueda();
		    	fnValidaciones("");
	    	}
	    }else{
	    	//muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo traer la Información de la Adecuación Presupuestal</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnObtenerPresupuesto(clavePresupuesto, idTabla, panel, tipoAfectacion="", nuevo="") {
	//console.log("fnObtenerPresupuesto");
	//muestraCargandoGeneral();
	var tipoMovimiento = "";
	if (panel == panelReducciones) {
		tipoMovimiento = "Reduccion";
	}else if (panel == panelAmpliaciones) {
		tipoMovimiento = "Ampliacion";
	}
	//Opcion para operacion
	dataObj = { 
	        option: 'obtenerPresupuesto',
	        clave: clavePresupuesto,
			account: '',
			legalid: '',
			datosClave: '1',
			datosClaveAdecuacion: '1',
			tipoAfectacion: tipoAfectacion,
			type: type,
			transno: transno,
			tipoMovimiento: tipoMovimiento
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
	    	info=data.contenido.datos;
	    	//console.log("presupuesto: "+JSON.stringify(info));
    		var validacionClave = false;
	    	var clave = "";
			for (var key in info) {
				clave = info[key].accountcode;
			}
	    	if (panel == panelReducciones) {
	    		validacionClave = fnValidarClave(clave, datosReducciones, panel, "Ya existe la Clave Presupuestal "+clave+" en el panel de Reducciones linea "+$("#Renglon_"+clave+"_"+panel).html());
	    		if (validacionClave) {
	    			datosReducciones.push(info);
	    		}
	    	}else if (panel == panelAmpliaciones) {
	    		validacionClave = fnValidarClave(clave, datosAmpliaciones, panel, "Ya existe la Clave Presupuestal "+clave+" en el panel de Ampliaciones linea "+$("#Renglon_"+clave+"_"+panel).html());
	    		if (validacionClave) {
	    			datosAmpliaciones.push(info);
	    		}
	    	}
	    	//console.log("validacionClave: "+validacionClave);
	    	if (validacionClave) {
	    		fnMostrarPresupuesto(info, idTabla, panel);
	    		if (nuevo == 'Nuevo' && panel == panelReducciones && 
	    			($('#selectTipoDoc').val() == 2 || $('#selectTipoDoc').val() == 4
	    				|| ($('#selectTipoDoc').val() == 6 && $('#selectTipoSolicitud').val() == 1)
	    				|| ($('#selectTipoDoc').val() == 7 && $('#selectTipoSolicitud').val() == 1)
	    				|| ($('#selectTipoDoc').val() == 9 && $('#selectTipoSolicitud').val() == 1)
	    			)
	    		) {
	    			// Validacion tipo 2
	    			// Validacion tipo 4
	    			// Validacion tipo EC
					// Validacion tipo IC
					// Validacion tipo MC
	    			//console.log("agregar datos a ampliaciones");
	    			//ocultaCargandoGeneral();
		    		fnObtenerPresupuesto(clave, tablaAmpliaciones, panelAmpliaciones);
	    		}
	    	}
	    	fnValidaciones("");
	    	//ocultaCargandoGeneral();
	    }else{
	    	//ocultaCargandoGeneral();
	    	// muestraMensaje(data.Mensaje, 3, 'divMensajeOperacion', 5000);
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, data.Mensaje);
	    }
	})
	.fail(function(result) {
		//ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnCambioTipoReduccion(select) {
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJson2 = datosReducciones[key];
			dataJson2[key2].tipoAfectacion = select.value;
		}
	}

	//fnPresupuestoCaptura(statusGuardar);
}

function fnCambioTipoAfectacion(clavePresupuesto, panel, tipoAfectacion) {
	// Cuando cambia tipo de afectacion para validacion de adiciones
	// console.log("clavePresupuesto: "+clavePresupuesto+" - panel: "+panel+" - tipoAfectacion: "+tipoAfectacion);

	var dataJson = new Array();
	if (panel == panelReducciones) {
		dataJson = dataJsonTipoDocReduccion;
	}else if (panel == panelAmpliaciones) {
		dataJson = dataJsonTipoDocAmpliacion;
	}

	var claveNuevaAdicion = 0;
	for (var key in dataJson) {
		// Recorrer Reducciones o Amplicaciones
		if (dataJson[key].value == tipoAfectacion && dataJson[key].nu_claveNueva == 1) {
			// console.log(dataJson[key].value+": "+dataJson[key].nu_claveNueva);
			claveNuevaAdicion = 1;
		}
	}

	return claveNuevaAdicion;
}

function fnGuardarSeleccionado(clavePresupuesto, input, panel, inputSelect) {
	//console.log("clavePresupuesto: "+clavePresupuesto+" - panel: "+panel);
	//console.log("caja: "+input.name+" - "+input.value);
	
	statusGuardar = 1; // Si hay cambios poner 1 para validar primero
	
	var dataJson = new Array();
	var tipoMovimiento = "";
	if (panel == panelReducciones) {
		dataJson = datosReducciones;
		if(inputSelect == 'input') {
			totalReducciones = 0;
		}
		tipoMovimiento = "Reduccion";
	}else if (panel == panelAmpliaciones) {
		dataJson = datosAmpliaciones;
		if(inputSelect == 'input') {
			totalAmpliaciones = 0;
		}
		tipoMovimiento = "Ampliacion";
	}

	var claveNuevaAdicion = 0;
	var claveNuevaAdicionAnt = '';
	var claveNueva = '';

	//console.log("dataJson antes: "+JSON.stringify(dataJson));
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clavePresupuesto) {
				if(inputSelect == 'input') {
					// Cambio un input
					var nombreInput = input.name.split("_");
					for (var mes in dataJsonMeses ) {
						// Nombres de los mes
		                var nombreMes = dataJsonMeses[mes];
		                if (nombreMes == nombreInput[2]) {
		                	//console.log("nombreMes: "+nombreMes+" disponible: "+dataJson2[key2][nombreMes]+" - input: "+input.value);
		                	if ((parseFloat(dataJson2[key2][nombreMes]) < parseFloat(input.value != "" ? input.value : 0)) && panel == panelReducciones) {
								muestraMensaje('En '+nombreMes+' el disponible es '+dataJson2[key2][nombreMes]+' para la clave '+clavePresupuesto, 3, 'divMensajeOperacionReducciones', 5000);
		                		$('#'+clavePresupuesto+"_"+panel+"_"+nombreMes).val(""+dataJson2[key2][nombreMes+"Sel"]);
		                	}else{
		                		dataJson2[key2][nombreMes+"Sel"] = (input.value != "" ? input.value : 0);
		                	}
		                	break;
		                }
		            }
				}else{
					// Cambio el select, tipo de afectacion
					dataJson2[key2].tipoAfectacion = input.value;
					claveNuevaAdicion = fnCambioTipoAfectacion(clavePresupuesto, panel, input.value);
					if (claveNuevaAdicion == 1) {
						// Es clave nueva
						var claveSeparada = dataJson2[key2].accountcode.split("-");
						// console.log("claveSeparada: "+JSON.stringify(claveSeparada));
						
						for (var elemento = 0; elemento < claveSeparada.length; elemento ++) {
							if (claveNueva == '') {
								claveNueva = anioActualAdecuacion;//'0000';
							} else {
								claveNueva = claveNueva + '-' + claveSeparada[elemento];
							}
						}
						// Reemplazar nueva clave
						claveNuevaAdicionAnt = dataJson2[key2].accountcode;
						dataJson2[key2].accountcode = claveNueva;
						// console.log("claveNuevaAdicionAnt: "+claveNuevaAdicionAnt);
						// console.log("claveNueva: "+claveNueva);
						var nombreInput = input.name.split("_");
						for (var mes in dataJsonMeses ) {
							// Nombres de los mes
			                var nombreMes = dataJsonMeses[mes];
			                dataJson2[key2][nombreMes] = 0;
			                // dataJson2[key2][nombreMes+"Sel"] = 0;
			            }
					}
				}
			}

			if(inputSelect == 'input') {
				var total = 0;
				for (var mes in dataJsonMeses ) {
					// Nombres de los mes
					var nombreMes = dataJsonMeses[mes];
					total = parseFloat(total) + parseFloat(dataJson2[key2][nombreMes+"Sel"]);
				}
				if (panel == panelReducciones) {
					totalReducciones += parseFloat(total);
				}else if (panel == panelAmpliaciones) {
					totalAmpliaciones += parseFloat(total);
				}
			}
		}
	}
	//console.log("dataJson despues: "+JSON.stringify(dataJson));
	if (panel == panelReducciones && inputSelect == 'input') {
		datosReducciones = dataJson;
		fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
	}else if (panel == panelAmpliaciones && inputSelect == 'input') {
		datosAmpliaciones = dataJson;
		fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
	}

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();

	if (claveNuevaAdicion == 1) {
		// Eliminar registro anterior
		if (transno != '0') {
			var tipo = "";
			if (panel == panelReducciones) {
				tipo = "Reduccion";
			}else if (panel == panelAmpliaciones) {
				tipo = "Ampliacion";
			}
			
			// Si tiene folio
			dataObj = { 
			        option: 'eliminaPresupuesto',
					type: type,
					transno: transno,
					clave: claveNuevaAdicionAnt,
					tipo: tipo,
					tipoAdecuacion: $('#selectTipoDoc').val(),
					claveNuevaAdicion: claveNuevaAdicion,
					claveNueva: claveNueva
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
					
			    }
			})
			.fail(function(result) {
				console.log("ERROR");
			    console.log( result );
			});
		}

		fnRecargarDatosPaneles();

		fnObtenerPresupuestoBusqueda();
		datosPresupuestosBusquedaReducciones = datosPresupuestosBusquedaFiltros;
		datosPresupuestosBusquedaAmpliaciones = datosPresupuestosBusquedaFiltros;
	}

	// Actualizar cada operacion
	//fnPresupuestoCaptura(statusGuardar);
	fnValidaciones("");
}

function fnMostrarTotalAmpRed(divNombre, total) {
	total = new Intl.NumberFormat("en-US").format(total);
	$('#'+divNombre).empty();
	$('#'+divNombre).html(""+total);
}

function fnGuardarSeleccionadoClaveNueva(clavePresupuesto, input, panel, nombreElementoClave) {
	// Guardar seleccion de la clave nueva
	
	statusGuardar = 1; // Si hay cambios poner 1 para validar primero

	var dataJson = new Array();
	var tipoMovimiento = "";
	if (panel == panelReducciones) {
		dataJson = datosReducciones;
	}else if (panel == panelAmpliaciones) {
		dataJson = datosAmpliaciones;
	}
	var claveNuevaAdicion = anioActualAdecuacion;//'0000';
	// console.log("dataJson clave Nueva antes: "+JSON.stringify(dataJson));
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			// console.log("************");
			// console.log("clave: "+dataJson2[key2].accountcode);
			// console.log("clave corta: "+dataJson2[key2].claveCorta);
			// console.log("clave larga: "+dataJson2[key2].claveLarga);
			// Separar la clave para obtener posiciones
			var cortaSeparada = dataJson2[key2].claveCorta.split("-");
			var largaSeparada = dataJson2[key2].claveLarga.split("-");
			// console.log("cortaSeparada: "+JSON.stringify(cortaSeparada));
			// console.log("largaSeparada: "+JSON.stringify(largaSeparada));
			if (dataJson2[key2].accountcode == clavePresupuesto) {
				// console.log("clave antes cambio: "+dataJson2[key2].accountcode);
				var datosClaveCorta = '';
				var datosClaveLarga = '';
				for (var key22 in dataJson2[key2].datosClave) {
					if (dataJson2[key2].datosClave[key22].nombre == nombreElementoClave) {
						// Si el elemento es igual al que cambio en la caja de texto
						// console.log("entra if "+dataJson2[key2].datosClave[key22].nombre);
						dataJson2[key2].datosClave[key22].valor = input.value;

						// Cambiar clave Corta
						for (var x = 0; x < cortaSeparada.length; x++) {
							if ((x+1) == dataJson2[key2].datosClave[key22].nu_clave_corta_orden) {
								cortaSeparada[x] = input.value;
							}
							if (datosClaveCorta == '') {
								datosClaveCorta = cortaSeparada[x];
							} else {
								datosClaveCorta += '-'+cortaSeparada[x];
							}
						}

						// Cambiar clave Larga
						for (var x = 0; x < largaSeparada.length; x++) {
							if ((x+1) == dataJson2[key2].datosClave[key22].nu_clave_larga_orden) {
								largaSeparada[x] = input.value;
							}
							if (datosClaveLarga == '') {
								datosClaveLarga = largaSeparada[x];
							} else {
								datosClaveLarga += '-'+largaSeparada[x];
							}
						}
					}
					// Generar clave nueva
					claveNuevaAdicion = claveNuevaAdicion + '-' + dataJson2[key2].datosClave[key22].valor;
				}
				// console.log("clave despues cambio: "+claveNuevaAdicion);
				// console.log("datosClaveCorta: "+datosClaveCorta);
				// console.log("datosClaveLarga: "+datosClaveLarga);
				dataJson2[key2].accountcode = claveNuevaAdicion;
				dataJson2[key2].claveCorta = datosClaveCorta;
				dataJson2[key2].claveLarga = datosClaveLarga;
			}
		}
	}
	// console.log("dataJson clave Nueva despues: "+JSON.stringify(dataJson));
	if (panel == panelReducciones) {
		datosReducciones = dataJson;
	}else if (panel == panelAmpliaciones) {
		datosAmpliaciones = dataJson;
	}

	fnRecargarDatosPaneles();
}

function fnMostrarPresupuesto(dataJson, idTabla, panel) {
	var encabezado = '';
	var contenido = '';
	var enca = 1;
	var style = 'style="text-align:center;"';
	var styleMeses = 'style="text-align:center;"';
	var nombreSelect = "";
	var tipoAfectacion = "";
	var claveNuevaAfectacion = 0;

	// numLineaReducciones
	// numLineaAmpliaciones
	// console.log("panel: "+panel);
	// console.log("dataJson: "+JSON.stringify(dataJson));
	for (var key in dataJson) {

		tipoAfectacion = dataJson[key].tipoAfectacion;

		var total = 0;
		for (var mes in dataJsonMeses ) {
			// Nombres de los mes
			var nombreMes = dataJsonMeses[mes];
			total = parseFloat(total) + parseFloat(dataJson[key][nombreMes+"Sel"]);
		}

		if (panel == panelReducciones) {
			if (idClavePresupuestoReducciones != dataJson[key].idClavePresupuesto) {
				idClavePresupuestoReducciones = dataJson[key].idClavePresupuesto;
				enca = 0;
			}
			totalReducciones += parseFloat(total);
		}else if (panel == panelAmpliaciones) {
			if (idClavePresupuestoAmpliaciones != dataJson[key].idClavePresupuesto) {
				idClavePresupuestoAmpliaciones = dataJson[key].idClavePresupuesto;
				enca = 0;
			}
			totalAmpliaciones += parseFloat(total);
		}

		if (enca == 0) {
			encabezado += '<tr class="header-verde"><td></td><td></td>';
		}
		
		// Se va autorizar y deshabilitar pagina
		if (panel == panelAmpliaciones && ($('#selectTipoDoc').val() == 2 || $('#selectTipoDoc').val() == 4
			|| ($('#selectTipoDoc').val() == 6 && $('#selectTipoSolicitud').val() == 1)
			|| ($('#selectTipoDoc').val() == 7 && $('#selectTipoSolicitud').val() == 1)
			|| ($('#selectTipoDoc').val() == 9 && $('#selectTipoSolicitud').val() == 1)
			)) {
			// Validacion tipo 2
			// Validacion tipo 4
			// Validacion tipo EC
			// Validacion tipo IC
			// Validacion tipo MC
			contenido += '<td></td>';
		}else{
			if (autorizarGeneral == 0) {
				contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\')"></button></td>';
			}else{
				contenido += '<td></td>';
			}
		}

		var deshabilitarElemento = '';
		if (autorizarGeneral == 1){
			// Se va autorizar y deshabilitar pagina
			deshabilitarElemento = ' disabled="true" ';
		}
		
		//Cargar datos presupuesto
		var numeroRegistrosClave = 1;
		for (var key2 in dataJson[key].datosClave) {
			if (numeroRegistrosClave == 2) {
				if (enca == 0) {
					//if (panel == panelAmpliaciones) {
						encabezado += '<td '+styleMeses+'>Tipo</td>';
					//}
				}

				//if (panel == panelAmpliaciones) {
					nombreSelect = "selectTipoDoc_"+panel+"_"+dataJson[key].accountcode+"";
					contenido += '<td><select '+deshabilitarElemento+' id="'+nombreSelect+'" name="'+nombreSelect+'" class="form-control selectTipoDocumentoAdecuaciones" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'select\')"></select></td>';
				//}
			}
			if (enca == 0) {
				encabezado += '<td '+style+'>'+dataJson[key].datosClave[key2].nombre+'</td>';
			}

			// Validar si es Adición (Clave Nueva)
			var dataJsonAfectacion = new Array();
			if (panel == panelReducciones) {
				dataJsonAfectacion = dataJsonTipoDocReduccion;
			}else if (panel == panelAmpliaciones) {
				dataJsonAfectacion = dataJsonTipoDocAmpliacion;
			}
			for (var keyAfectacion in dataJsonAfectacion) {
				// Recorrer Reducciones o Amplicaciones
				if (dataJsonAfectacion[keyAfectacion].value == tipoAfectacion && dataJsonAfectacion[keyAfectacion].nu_claveNueva == 1) {
					claveNuevaAfectacion = 1;
				}
			}

			var nombreInputPresupuestoClaveNueva = "inputClaveNueva_"+panel+"_"+dataJson[key].accountcode+"";
			contenido += '<td '+style+'>';
			if (claveNuevaAfectacion == 1) {
				contenido += '<div><input '+deshabilitarElemento+' type="text" class="form-control" name="'+nombreInputPresupuestoClaveNueva+'" id="'+nombreInputPresupuestoClaveNueva+'" value="'+dataJson[key].datosClave[key2].valor+'" style="width: 80px;" onchange="fnGuardarSeleccionadoClaveNueva(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \''+dataJson[key].datosClave[key2].nombre+'\')" /></div>';
			} else {
				contenido += '<div>'+dataJson[key].datosClave[key2].valor+'</div>';
			}
			contenido += '</td>';

			numeroRegistrosClave ++;
		}

		if (numeroRegistrosClave == 1 || numeroRegistrosClave == 2) {
			// Si no bienen datos de clave o solo agrego un elemento agregar el tipo de afectacion
			if (enca == 0) {
				//if (panel == panelAmpliaciones) {
					encabezado += '<td '+styleMeses+'>Tipo</td>';
				//}
			}

			//if (panel == panelAmpliaciones) {
				nombreSelect = "selectTipoDoc_"+panel+"_"+dataJson[key].accountcode+"";
				contenido += '<td><select '+deshabilitarElemento+' id="'+nombreSelect+'" name="'+nombreSelect+'" class="form-control selectTipoDocumentoAdecuaciones" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'select\')"></select></td>';
			//}
		}

		if (enca == 0) {
			// Columna para total por clave, encabezado
			encabezado += '<td '+styleMeses+'>Total</td>';
			for (var mes in dataJsonMeses ) {
				// Nombres de los mes para el encabezado
                var nombreMes = dataJsonMeses[mes];
                encabezado += '<td '+styleMeses+'>'+nombreMes+'</td>';
            }
		}

		// Columna para total por clave, información
		var nombreDivTotal = 'divTotal_'+panel+'_'+dataJson[key].accountcode;
		contenido += '<td '+style+' id="'+nombreDivTotal+'">$ '+formatoComas( redondeaDecimal( 0 ) )+'</td>';

		var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_"; // No cambiar estructura de nombre o cambiar tambien en fnGuardarSeleccionado()
		for (var mes in dataJsonMeses ) {
			// Informacion meses para seleccion
			var nombreMes = dataJsonMeses[mes];
			var nombreMesSel = dataJsonMeses[mes]+"Sel";
			contenido += '<td '+style+'>$ '+formatoComas( redondeaDecimal( dataJson[key][nombreMes] ) )+' <br> <input '+deshabilitarElemento+' type="text" min="0" class="form-control" name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" style="width: 80px;" onkeypress="return fnsoloDecimalesGeneral(event, this)" onBlur="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" /></td>';
		}

		if (enca == 0) {
			encabezado += '</tr>';
		}

		if (panel == panelReducciones) {
			contenido = '<td id="Renglon_'+dataJson[key].accountcode+'_'+panel+'" name="Renglon_'+dataJson[key].accountcode+'_'+panel+'">'+numLineaReducciones+'</td>' + contenido;
			numLineaReducciones = parseFloat(numLineaReducciones) + 1;
		}else if (panel == panelAmpliaciones) {
			contenido = '<td id="Renglon_'+dataJson[key].accountcode+'_'+panel+'" name="Renglon_'+dataJson[key].accountcode+'_'+panel+'">'+numLineaAmpliaciones+'</td>' + contenido;
			numLineaAmpliaciones = parseFloat(numLineaAmpliaciones) + 1;
		}

		contenido = encabezado + '<tr>' + contenido + '</tr>';

		enca = 1;
	}
	
	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
	fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
	
	$('#'+idTabla+' tbody').append(contenido);

	// if (panel == panelReducciones && tipoAfectacion != "" && $('#cmbTipoReduccion').val() == '0') {
	// 	fnCrearDatosSelect(dataJsonTipoDocReduccion, '#cmbTipoReduccion', tipoAfectacion);
	// 	fnFuncionSelect();
	// }

	// Aplicar cambios del select
	// console.log("dataJsonTipoDocReduccion: "+JSON.stringify(dataJsonTipoDocReduccion));
	// console.log("dataJsonTipoDocAmpliacion: "+JSON.stringify(dataJsonTipoDocAmpliacion));
	if (panel == panelReducciones) {
	 	fnCrearDatosSelect(dataJsonTipoDocReduccion, '#'+nombreSelect, tipoAfectacion);
	}else if (panel == panelAmpliaciones) {
		fnCrearDatosSelect(dataJsonTipoDocAmpliacion, '#'+nombreSelect, tipoAfectacion);
		fnFuncionSelect();
	}
	
	if (autorizarGeneral == 1 || claveNuevaAfectacion == 1){
		// Se va autorizar y deshabilitar pagina
		$('#'+nombreSelect).multiselect('disable');
	}
}

/**
 * Función para calcular los totales por clave presupuestal y por mes
 * @return {[type]} [description]
 */
function fnCalcularTotalesClaveRenglon() {
	// Calcular totales por clave y renglon
	var mesesTotales = new Array();
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			// console.log("clave: "+dataJsonReducciones[key2].accountcode);
			var totalClave = 0;
			var num = 1;
			for (var mes in dataJsonMeses ) {
				// Nombres de los mes
	            var nombreMes = dataJsonMeses[mes];
	            var nombreMesSel = dataJsonMeses[mes]+"Sel";
	            if (!Number(mesesTotales[num])) {
	            	mesesTotales[num] = 0;
	            }
	            mesesTotales[num] = parseFloat(mesesTotales[num]) + parseFloat(dataJsonReducciones[key2][nombreMesSel]);
	            num ++;
	            totalClave = parseFloat(totalClave) + parseFloat(dataJsonReducciones[key2][nombreMesSel]);
	        }
	        // Mostrar el total por clave presupuestal
	        var nombreDivTotal = 'divTotal_'+panelReducciones+'_'+dataJsonReducciones[key2].accountcode;
	        $("#"+nombreDivTotal).empty();
	        $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );
		}
	}

	if (datosReducciones.length == 0) {
		// No existen registros poner en 0 total general
		totalReducciones = 0;
		fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
	}
	
	// Generar Contenido HTML para visualizar
	var encabezado = '<tr class="header-verde">';
	var contenido = '</tr>';
	encabezado += '<td>Total</td>';
	contenido += '<td>$ '+formatoComas( redondeaDecimal( totalReducciones ) )+'</td>';
	var num = 1;
	for (var mes in dataJsonMeses ) {
		var nombreMes = dataJsonMeses[mes];
		if (mesesTotales[num] == '' || mesesTotales[num] == null || typeof mesesTotales[num] == 'undefined') {
			// Si no tiene información
			mesesTotales[num] = 0;
		}
		encabezado += '<td>'+nombreMes+'</td>';
		contenido += '<td>$ '+formatoComas( redondeaDecimal( mesesTotales[num] ) )+'</td>';
		num ++;
	}
	encabezado += '</tr>';
	contenido += '</tr>';
	$('#tablaReduccioneTotales tbody').empty();
	$('#tablaReduccioneTotales tbody').append(encabezado + contenido);
	
	mesesTotales = new Array();
	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			// console.log("clave: "+dataJsonAmpliaciones[key2].accountcode);
			var totalClave = 0;
			var num = 1;
			for (var mes in dataJsonMeses ) {
				// Nombres de los mes
	            var nombreMes = dataJsonMeses[mes];
	            var nombreMesSel = dataJsonMeses[mes]+"Sel";
	            if (!Number(mesesTotales[num])) {
	            	mesesTotales[num] = 0;
	            }
	            mesesTotales[num] = parseFloat(mesesTotales[num]) + parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
	            num ++;
	            totalClave = parseFloat(totalClave) + parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
	        }
	        // Mostrar el total por clave presupuestal
	        var nombreDivTotal = 'divTotal_'+panelAmpliaciones+'_'+dataJsonAmpliaciones[key2].accountcode;
	        $("#"+nombreDivTotal).empty();
	        $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );
		}
	}

	if (datosAmpliaciones.length == 0) {
		// No existen registros poner en 0 total general
		totalAmpliaciones = 0;
		fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
	}

	// Generar Contenido HTML para visualizar
	var encabezado = '<tr class="header-verde">';
	var contenido = '</tr>';
	encabezado += '<td>Total</td>';
	contenido += '<td>$ '+formatoComas( redondeaDecimal( totalAmpliaciones ) )+'</td>';
	var num = 1;
	for (var mes in dataJsonMeses ) {
		var nombreMes = dataJsonMeses[mes];
		if (mesesTotales[num] == '' || mesesTotales[num] == null || typeof mesesTotales[num] == 'undefined') {
			// Si no tiene información
			mesesTotales[num] = 0;
		}
		encabezado += '<td>'+nombreMes+'</td>';
		contenido += '<td>$ '+formatoComas( redondeaDecimal( mesesTotales[num] ) )+'</td>';
		num ++;
	}
	encabezado += '</tr>';
	contenido += '</tr>';
	$('#tablaAmpliacionesTotales tbody').empty();
	$('#tablaAmpliacionesTotales tbody').append(encabezado + contenido);
}

function fnValidarClave(clave, dataJson, panel, mensaje) {
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clave) {
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				if (panel == panelReducciones) {
					// muestraMensaje(mensaje, 3, 'divMensajeOperacionReducciones', 5000);
					muestraModalGeneral(3, titulo, mensaje);
				}else if (panel == panelAmpliaciones) {
					// muestraMensaje(mensaje, 3, 'divMensajeOperacionAmpliaciones', 5000);
					muestraModalGeneral(3, titulo, mensaje);
				}else{
					// muestraMensaje(mensaje, 3, 'divMensajeOperacion', 5000);
					muestraModalGeneral(3, titulo, mensaje);
				}
				return false;
			}
		}
	}
	
	return true;
}

function fnPresupuestoEliminar(clave, panel, sinConfirmacion=0) {
	//console.log("clave: "+clave);
	//console.log("panel: "+panel);
	//console.log("tipoAdecuacion: "+$('#selectTipoDoc').val());
	statusGuardar = 1;
	if (sinConfirmacion == 0 && ($('#selectTipoDoc').val() == 2 || $('#selectTipoDoc').val() == 4
		|| ($('#selectTipoDoc').val() == 6 && $('#selectTipoSolicitud').val() == 1)
		|| ($('#selectTipoDoc').val() == 7 && $('#selectTipoSolicitud').val() == 1)
		|| ($('#selectTipoDoc').val() == 9 && $('#selectTipoSolicitud').val() == 1)
		)) {
		// Validacion tipo 2
		// Validacion tipo 4
		// Validacion tipo EC
		// Validacion tipo IC
		// Validacion tipo MC
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar la Clave Presupuestal '+clave+' de Reducciones y Ampliaciones</p>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\')');
		return false;
	}

	//Opcion para operacion
	var tipo = "";
	if (panel == panelReducciones) {
		tipo = "Reduccion";
	}else if (panel == panelAmpliaciones) {
		tipo = "Ampliacion";
	}

	if (($('#selectTipoDoc').val() == 2 || $('#selectTipoDoc').val() == 4
		|| ($('#selectTipoDoc').val() == 6 && $('#selectTipoSolicitud').val() == 1)
		|| ($('#selectTipoDoc').val() == 7 && $('#selectTipoSolicitud').val() == 1)
		|| ($('#selectTipoDoc').val() == 9 && $('#selectTipoSolicitud').val() == 1)
		)) {
		// Validacion tipo 2
		// Validacion tipo 4
		// Validacion tipo EC
		// Validacion tipo IC
		// Validacion tipo MC
		tipo = 'Reduccion/Ampliacion';
	}

	// Agregar datos para eliminar al guardar
	var obj = new Object();
	obj.type = type;
	obj.transno = transno;
	obj.clave = clave;
	obj.tipo = tipo;
	obj.tipoAdecuacion = $('#selectTipoDoc').val();
	datosEliminarRedAmp.push(obj);
	
	// No entrar al if ya que hace el proceso antes de guardar
	if (transno != '0' && 1 == 2) {
		// Si tiene folio
		dataObj = { 
		        option: 'eliminaPresupuesto',
				type: type,
				transno: transno,
				clave: clave,
				tipo: tipo,
				tipoAdecuacion: $('#selectTipoDoc').val()
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
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    if(data.result){
				muestraModalGeneral(3, titulo, data.contenido);
		    	// muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
		    	
		    	//fnObtenerPresupuestoNoCaptura();
		    	// Eliminar Renglon
		    	fnEliminarRenglon(clave, panel, $('#selectTipoDoc').val());

		    	// Datos lista de busqueda
				fnObtenerPresupuestoBusqueda();
				datosPresupuestosBusquedaReducciones = datosPresupuestosBusquedaFiltros;
				datosPresupuestosBusquedaAmpliaciones = datosPresupuestosBusquedaFiltros;

				// Validaciones
				fnValidaciones();
		    }else{
		    	// muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
				muestraModalGeneral(3, titulo, data.contenido);
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		});
	} else {
		// Si no tiene folio
		//fnObtenerPresupuestoNoCaptura();

		// Eliminar Renglon
		fnEliminarRenglon(clave, panel, $('#selectTipoDoc').val());

		// Datos lista de busqueda
		fnObtenerPresupuestoBusqueda();
		datosPresupuestosBusquedaReducciones = datosPresupuestosBusquedaFiltros;
		datosPresupuestosBusquedaAmpliaciones = datosPresupuestosBusquedaFiltros;

		// Validaciones
		fnValidaciones();

		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = 'La clave presupuestal se ha eliminado';
		muestraModalGeneral(3, titulo, mensaje);
	}
}

function fnQuitarRenglonDatosArray(type, transno, clave, tipo, tipoAdecuacion) {
	// Funcion para eliminar registros de la base de datos
	var respuesta = true;
	// Si tiene folio
	dataObj = { 
	        option: 'eliminaPresupuesto',
			type: type,
			transno: transno,
			clave: clave,
			tipo: tipo,
			tipoAdecuacion: tipoAdecuacion
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
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    if(data.result){
			respuesta = true;
	    }else{
	    	// muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
			respuesta = true;
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});

	return respuesta;
}

function fnEliminarRenglon(clave, panel, tipoAdecuacion) {
	if (tipoAdecuacion == 2 || tipoAdecuacion == 4 
		|| ($('#selectTipoDoc').val() == 6 && $('#selectTipoSolicitud').val() == 1)
		|| ($('#selectTipoDoc').val() == 7 && $('#selectTipoSolicitud').val() == 1)
		|| ($('#selectTipoDoc').val() == 9 && $('#selectTipoSolicitud').val() == 1)
		) {
		// Validacion tipo 2
		// Validacion tipo 4
		// Validacion tipo EC
		// Validacion tipo IC
		// Validacion tipo MC
		for (var key in datosReducciones ) {
			for (var key2 in datosReducciones[key]) {
				var dataJsonReducciones = datosReducciones[key];
				if (dataJsonReducciones[key2].accountcode == clave) {
					datosReducciones.splice(key, 1);
					break;
				}
			}
		}
	
		for (var key in datosAmpliaciones ) {
			for (var key2 in datosAmpliaciones[key]) {
				var dataJsonAmpliaciones = datosAmpliaciones[key];
				if (dataJsonAmpliaciones[key2].accountcode == clave) {
					datosAmpliaciones.splice(key, 1);
					break;
				}
			}
		}
	}else{
		if (panel == panelReducciones) {
			for (var key in datosReducciones ) {
				for (var key2 in datosReducciones[key]) {
					var dataJsonReducciones = datosReducciones[key];
					if (dataJsonReducciones[key2].accountcode == clave) {
						datosReducciones.splice(key, 1);
						break;
					}
				}
			}
		}else if (panel == panelAmpliaciones) {
			for (var key in datosAmpliaciones ) {
				for (var key2 in datosAmpliaciones[key]) {
					var dataJsonAmpliaciones = datosAmpliaciones[key];
					if (dataJsonAmpliaciones[key2].accountcode == clave) {
						datosAmpliaciones.splice(key, 1);
						break;
					}
				}
			}
		}
	}

	fnRecargarDatosPaneles();
}

function fnAutorizarAdecuacion(){
	// Funcion de General
	//fnGeneralAutorizarAdecuacion(transno, type, 'txtProcesoSicop', 'txtFolioMap', $('#selectTipoDoc').val(), $('#selectTipoSolicitud').val(), 0);
	
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

	if (statusGuardar == 1) {
		// Si realizo cambios y no cumple con las reglas
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Sin Reglas Validadas</p>';
		muestraModalGeneral(4, titulo, mensaje);
		return true;
	}

	if (modificoEncabezado == 1) {
		// Si realizo cambios y no cumple con las reglas
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Guardar Información antes de Autorizar</p>';
		muestraModalGeneral(4, titulo, mensaje);
		return true;
	}

	if (transno == 0) {
		// Si no tiene folio
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Guardar Información para realizar autorización</p>';
		muestraModalGeneral(4, titulo, mensaje);
		return true;
	}

	var mensaje = '<h4>¿Desea Autorizar el Folio '+transno+'?</h4>';
	muestraModalGeneralConfirmacion(4, titulo, mensaje, '', 'fnGeneralAutorizarAdecuacion(\''+transno+'\', \''+type+'\', \'txtProcesoSicop\', \'txtFolioMap\', \'txtFechaApl\', \''+$('#selectTipoDoc').val()+'\', \''+$('#selectTipoSolicitud').val()+'\', \'0\')');
}

function fnCambiarEstatusAdecuacion(statusid) {
	//console.log("fnCambiarEstatusAdecuacion");
	
	var dataJsonEstatus = new Array();
	var obj = new Object();
	obj.transno = transno;
	obj.type = type;
	obj.statusid = '4';
	dataJsonEstatus.push(obj);

	dataObj = { 
		option: 'actualizarEstatus',
		dataJsonNoCapturaSeleccionados: dataJsonEstatus,
		statusid: statusid,
		sStatus: 1
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
			var Link_Adecuaciones = document.getElementById("linkPanelAdecuaciones");
			Link_Adecuaciones.click();
		}else{
			var Link_Adecuaciones = document.getElementById("linkPanelAdecuaciones");
			Link_Adecuaciones.click();
		}
	})
	.fail(function(result) {
		var Link_Adecuaciones = document.getElementById("linkPanelAdecuaciones");
		Link_Adecuaciones.click();
		//console.log("ERROR");
		//console.log( result );
	});
}

function fnActualizarDatosAutorizada(transno, type, nameSicop, nameMap, nameFecha, nameAdecuacion, nameSolicitud) {
	// console.log("fnActualizarDatosAutorizada");
    var pSicop = $("#"+nameSicop).val();
    var fMap = $("#"+nameMap).val();
    var fecha = $("#"+nameFecha).val();
    var tipoAdecuacion = $("#"+nameAdecuacion).val();
    var tipoSolicitud = $("#"+nameSolicitud).val();
    var errorVal = 0;
    var mensaje = "";

    // console.log("pSicop: "+pSicop+" - fMap: "+fMap+" - noCaptura: "+noCaptura+" - type: "+type);
    // console.log("tipoAdecuacion: "+tipoAdecuacion+" - tipoSolicitud: "+tipoSolicitud);
    if ((tipoAdecuacion == 6 || tipoAdecuacion == 7) && (pSicop.trim() == "" || fMap.trim() == "")) {
        // Validacion 6 y 7, Clase (Externas e Internas)
        mensaje = 'Completar la información (P SICOP, F MAP)';
        errorVal = 1;
    }else if ((tipoAdecuacion == 8 || tipoAdecuacion == 9) && (pSicop.trim() == "")) {
        // Validacion 8 y 9, Clase (Sin notificación, Movto. sólo GRP)
        mensaje = 'Completar la información (P SICOP)';
        errorVal = 1;
    }else if(fecha.trim() == "") {
        mensaje = 'Completar la información (Fecha APL)';
        errorVal = 1;
    }
    // console.log("errorVal: "+errorVal);
    if (errorVal == 1) {
        // if (!$('#ModalGeneral').is(':hidden')) {
        //     muestraMensajeTiempo(mensaje, 1, 'ModalGeneral_Advertencia', 5000);
        // }else{
        //     var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        //     muestraModalGeneral(3, titulo, mensaje);
        // }
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
    }

    //Opcion para operacion
    dataObj = { 
        option: 'actualizarAdecAutizada',
        pSicop: pSicop,
        fMap: fMap,
        fecha: fecha,
        type: type,
        transno: transno
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/GLBudgetsByTagV2_modelo.php",
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
    })
    .fail(function(result) {
        //console.log("ERROR");
        //console.log( result );
    });
}

function fnObtenerBotones(divMostrar) {
	//Opcion para operacion
	var verDatos = 0;
	if (autorizarGeneral == 1 && permisoEditarEstCapturado == 0) {
		verDatos = 1;
	}
	dataObj = { 
	        option: 'obtenerBotones',
	        autorizarGeneral: verDatos,
	        soloActFoliosAutorizada: soloActFoliosAutorizada
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
	    	info=data.contenido.datos;
	    	//console.log("botones: "+JSON.stringify(info));
	    	var contenido = '';
	    	for (var key in info) {
	    		var funciones = '';
	    		if (info[key].statusid == 1) {
	    			statusGuardar = info[key].statusid;
	    			funciones = 'fnPresupuestoCaptura('+statusGuardar+')';
	    		}
	    		if (info[key].statusid == 10) {
	    			funciones = 'fnNuevaClavePresupuestal()';
	    		}
	    		if (info[key].statusid == 6) {
	    			funciones = 'fnAutorizarAdecuacion()';
	    		}
	    		if (info[key].statusid == 99) {
	    			// Si se rechaza dejar en estatus 3, ya que solo rechaza en estatus 4
	    			funciones = 'fnCambiarEstatusAdecuacion(3)';
	    		}
	    		if (info[key].statusid == 98) {
	    			// Solo es actualizar Fecha, P SICOP y F MAP
	    			funciones = 'fnActualizarDatosAutorizada(\''+transno+'\', \''+type+'\', \'txtProcesoSicop\', \'txtFolioMap\', \'txtFechaApl\', \'selectTipoDoc\', \'selectTipoSolicitud\')';
	    		}
	    		
	    		if ((info[key].statusid != 5 && info[key].statusid != 99) || (autorizarGeneral == 1 && permisoEditarEstCapturado == 1)) {
	    			if (info[key].statusid == 0) {
	    				// Si es cancelar poner vinculo con liga al Panel
	    				contenido += '&nbsp;&nbsp;&nbsp; \
	    				<a id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" href="GLBudgetsByTagV2_Panel.php?" class="btn btn-default botonVerde '+info[key].clases+'">'+info[key].namebutton+'</a>';
	    			}else{
	    				// contenido += '&nbsp;&nbsp;&nbsp; \
	    				// <button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" onclick="'+funciones+'" class="btn btn-default botonVerde '+info[key].clases+'">'+info[key].namebutton+'</button>';
	    				contenido += '&nbsp;&nbsp;&nbsp; \
	    				<component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
	    			}
	    		}
	    	}
	    	$('#'+divMostrar).append(contenido);
	    	fnEjecutarVueGeneral('divBotones');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnRecargarDatosPaneles() {
	//console.log("///// fnRecargarDatosPaneles /////");
	$('#'+tablaReducciones+' tbody').empty();
	$('#'+tablaAmpliaciones+' tbody').empty();

	// Numero de linea
	numLineaReducciones = 1;
	numLineaAmpliaciones = 1;

	// Id de clave para encabezado
	idClavePresupuestoReducciones = 0;
	idClavePresupuestoAmpliaciones = 0;

	// Total por panel
	totalReducciones = 0;
	totalAmpliaciones = 0;

	for (var key in datosReducciones ) {
		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	}

	for (var key in datosAmpliaciones ) {
		fnMostrarPresupuesto(datosAmpliaciones[key], tablaAmpliaciones, panelAmpliaciones);
	}

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

function fnValidaciones(mostrarValidaciones=0, guardarDatos=0) {
	var tipoAdecuacion = $('#selectTipoDoc').val();
	var tipoSolicitud = $('#selectTipoSolicitud').val();
	var selectUnidadResponsable = $('#selectUnidadNegocio').val();
	var txtJustificacion = $('#txtJustificacion').val();

	if (autorizarGeneral == 1) {
		// Se va autorizar y deshabilitar pagina
		return false;
	}

	// console.log("tipoAdecuacion: "+tipoAdecuacion);
	// console.log("tipoSolicitud: "+tipoSolicitud);
	// Clase id: 6-E
	// Clase id: 7-I
	// Clase id: 8-S
	// Clase id: 9-M	

	// tipoSol id: 1-C
	// tipoSol id: 2-T
	// tipoSol id: 3-R
	// tipoSol id: 4-A
	// tipoSol id: 5-I
	// tipoSol id: 6-O
	errorValidacion = 0;
	
	var mensaje = "";

	if (selectUnidadResponsable == '-1' || selectUnidadResponsable.trim() == '' || selectUnidadResponsable == '0') {
		// Validar Unidad Responsable
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar Unidad CR para continuar con el proceso</p>';
	}

	if (txtJustificacion.trim() == '') {
		// Validar Justificación
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Justificación</p>';
	}

	if (tipoAdecuacion == '0' || tipoSolicitud == '0') {
		// Validar Clase y Tipo de Solicitud
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar Clase y/o Tipo de Solicitud</p>';
	}

	mensaje += fnValidarSeleccionTipoAfectacion();

	mensaje += fnValidarClavesMontoCero();

	if (tipoAdecuacion == 1) {
		// Validacion tipo 1, Externas

		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		mensaje += fnTotalMesCompesando();

		mensaje += fnClaveCortaIgual();
	}else if (tipoAdecuacion == 2) {
		// Validacion tipo 2, Externas de Calendario

		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalPorClave();
	}else if (tipoAdecuacion == 3) {
		// Validacion tipo 3, Internas Internas

		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		var claveLargaConfig = 1;
		// UR-UE-EDO-GF-F-SF-PG-AI-PP-CP-PARTIDA-TG-FF-PPI (clave larga) 
		for (var key in datosReducciones ) {
			for (var key2 in datosReducciones[key]) {
				var dataJsonReducciones = datosReducciones[key];
				//console.log("Clave: "+dataJsonReducciones[key2].accountcode+" - Corta: "+dataJsonReducciones[key2].claveLarga);
				for (var keyBusq in datosPresupuestosBusquedaAmpliaciones ) {
					if (dataJsonReducciones[key2].claveLarga == "" || dataJsonReducciones[key2].claveLarga == null) {
						claveLargaConfig = 0;
						break;
					}
					if (dataJsonReducciones[key2].claveLarga == datosPresupuestosBusquedaAmpliaciones[keyBusq].claveLarga) {
						//console.log("Clave Larga Reducciones: "+dataJsonReducciones[key2].claveLarga+" - Busqueda Igual: "+datosPresupuestosBusquedaAmpliaciones[keyBusq].claveLarga);
						datosPresupuestosBusquedaAmpliaciones.splice(keyBusq, 1);
					}
				}
			}
		}
		fnBusquedaAmpliacion(datosPresupuestosBusquedaAmpliaciones);
		
		mensaje += fnClaveCortaDiferente();

		mensaje += fnTotalMesCompesando();

		mensaje += fnMismaClaveRedAmp();

		mensaje += fnTotalGeneralIgual();
	}else if (tipoAdecuacion == 4) {
		// Validacion tipo 4, Internas de Calendario

		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalPorClave();
	}else if (tipoAdecuacion == 5) {
		// Validacion tipo 5, Internas Comunicables

		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		mensaje += fnClaveCortaIgual();

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalMesCompesando();
	}else if (tipoAdecuacion == 8 && tipoSolicitud == 2) {
		// Validacion tipo ST, I. Sin Notificación (Clase “S” Tipo “T”) = Internas-Internas
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		var claveLargaConfig = 1;
		// UR-UE-EDO-GF-F-SF-PG-AI-PP-CP-PARTIDA-TG-FF-PPI (clave larga) 
		for (var key in datosReducciones ) {
			for (var key2 in datosReducciones[key]) {
				var dataJsonReducciones = datosReducciones[key];
				//console.log("Clave: "+dataJsonReducciones[key2].accountcode+" - Corta: "+dataJsonReducciones[key2].claveLarga);
				for (var keyBusq in datosPresupuestosBusquedaReducciones ) {
					// if (dataJsonReducciones[key2].claveLarga == "" || dataJsonReducciones[key2].claveLarga == null) {
					// 	claveLargaConfig = 0;
					// 	break;
					// }
					if (dataJsonReducciones[key2].accountcode == datosPresupuestosBusquedaReducciones[keyBusq].accountcode) {
						//console.log("Clave Larga Reducciones: "+dataJsonReducciones[key2].claveLarga+" - Busqueda Igual: "+datosPresupuestosBusquedaAmpliaciones[keyBusq].claveLarga);
						datosPresupuestosBusquedaReducciones.splice(keyBusq, 1);
					}
				}
			}
		}
		fnBusquedaAmpliacion(datosPresupuestosBusquedaAmpliaciones);
		
		// mensaje += fnClaveCortaDiferente();
	
		mensaje += fnTotalMesCompesandoClaveCorta();

		mensaje += fnTotalMesCompesando();

		mensaje += fnMismaClaveRedAmp();

		mensaje += fnTotalGeneralIgual();

		var arrayTipoSolRed = [ 35 ];
		var arrayTipoSolAmp = [ 30, 32 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 6 && tipoSolicitud == 2) {
		// Validacion tipo ET, II. EXTERNAS de TRASPASO (Clase “E” Tipo “T”) = Externas
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		var validacionComponente = 0;

		console.log("****************");

		// Validaciones Tipo de Gasto
		var arrayRed = [ 2, 3, 7 ];
		var arrayAmp = [ 1 ];
		validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'ctga', 0);
		console.log("validacionComponente TG: "+validacionComponente);
		
		if (validacionComponente == 0) {
			// Validaciones Finalidad
			var arrayRed = [ 0, 2, 3, 4, 5, 6, 7, 8, 9 ];
			var arrayAmp = [ 1 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'id_finalidad', 0);
			console.log("validacionComponente FI 1: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Finalidad
			var arrayRed = [ 0, 1, 3, 4, 5, 6, 7, 8, 9 ];
			var arrayAmp = [ 2 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'id_finalidad', 0);
			console.log("validacionComponente FI 2: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Fuente de Financiamiento
			var arrayRed = [ 2, 3 ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cfin', 0);
			console.log("validacionComponente FF: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Fuente de Financiamiento Viceversa
			var arrayRed = [ ];
			var arrayAmp = [ 2, 3 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cfin', 0);
			console.log("validacionComponente FF Viceversa: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida
			var arrayRed = [ 2, 3, 4, 5, 6, 7, 8, 9 ];
			var arrayAmp = [ 1 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida: "+validacionComponente);
		}
		
		if (validacionComponente == 0) {
			// Validaciones Partida Viceversa
			var arrayRed = [ 1 ];
			var arrayAmp = [ 2, 3, 4, 5, 6, 7, 8, 9 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida Viceversa: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida 43101
			var arrayRed = [ '43101' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0);
			var validacionComponenteS1 = validacionComponente;
			console.log("validacionComponente Partida 43101: "+validacionComponente);

			var validacionComponenteS2 = 0;
			if (validacionComponenteS1 == 1) {
				var arrayRed = [ ];
				var arrayAmp = [ '43101' ];
				validacionComponenteS2 = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0);
			} else if (validacionComponente == 0) {
				var arrayRed = [ ];
				var arrayAmp = [ '43101' ];
				validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0);
			}

			if (validacionComponenteS1 == 1 && validacionComponenteS2 == 1 && datosAmpliaciones.length == 1) {
				// Viene S con solo un registro
				errorValidacion = 1;
			}

			console.log("validacionComponente Partida 43101 S1: "+validacionComponenteS1);
			console.log("validacionComponente Partida 43101 S2: "+validacionComponenteS2);
		}

		if (validacionComponente == 0) {
			// Validaciones Programa Presupuestario
			var arrayRed = [ 'S' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cppt', 0, 0, 1);
			var validacionComponenteS1 = validacionComponente;
			console.log("validacionComponenteS1 PP: "+validacionComponente);

			var validacionComponenteS2 = 0;
			if (validacionComponenteS1 == 1) {
				var arrayRed = [ ];
				var arrayAmp = [ 'S' ];
				validacionComponenteS2 = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cppt', 0, 0, 1);
				console.log("validacionComponenteS2 PP Viceversa: "+validacionComponente);
			} else if (validacionComponente == 0) {
				// Validaciones Programa Presupuestario, Viceversa
				var arrayRed = [ ];
				var arrayAmp = [ 'S' ];
				validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cppt', 0, 0, 1);
				console.log("validacionComponente PP Viceversa: "+validacionComponente);
			}

			if (
				validacionComponenteS1 == 1 && validacionComponenteS2 == 1 
				&& 
				((datosReducciones.length == 1 || datosAmpliaciones.length == 1))
				) {
				// Viene S con solo un registro
				validacionComponente = 0;
			}

			console.log("validacionComponente Partida S S1: "+validacionComponenteS1);
			console.log("validacionComponente Partida S S2: "+validacionComponenteS2);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida 37
			var arrayRed = [ '37' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 2);
			var validacionComponenteS1 = validacionComponente;
			console.log("validacionComponente Partida 37: "+validacionComponente);

			var validacionComponenteS2 = 0;
			if (validacionComponenteS1 == 1) {
				var arrayRed = [ ];
				var arrayAmp = [ '37' ];
				validacionComponenteS2 = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 2);
			} else if (validacionComponente == 0) {
				// Validaciones Partida 37 Viceversa
				var arrayRed = [ ];
				var arrayAmp = [ '37' ];
				validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 2);
				console.log("validacionComponente Partida 37 Viceversa: "+validacionComponente);
			}

			if (validacionComponenteS1 == 1 && validacionComponenteS2 == 1 && datosAmpliaciones.length == 1) {
				// Viene S con solo un registro
				validacionComponente = 0;
			}

			console.log("validacionComponente Partida 37 S1: "+validacionComponenteS1);
			console.log("validacionComponente Partida 37 S2: "+validacionComponenteS2);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida Capitulo 7
			var arrayRed = [ '7' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida 7: "+validacionComponente);
		}
		
		if (validacionComponente == 0) {
			// Validaciones Partida Capitulo 7 Viceversa
			var arrayRed = [ ];
			var arrayAmp = [ '7' ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida 7 Viceversa: "+validacionComponente);
		}
		
		if (validacionComponente == 0) {
			// Si no se cumplen una de las reglas
			errorValidacion = 1;
		}

		mensaje += fnTotalMesCompesando();

		mensaje += fnClaveCortaIgual();

		mensaje += fnClaveCortaDiferentes();

		if (errorValidacion == 1) {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Verficar información en cuanto a los campos clase y tipo de solicitud</p>';
		}

		var arrayTipoSolRed = [ 35 ];
		var arrayTipoSolAmp = [ 30, 32 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 6 && tipoSolicitud == 1) {
		// Validacion tipo EC, III. EXTERNAS DE CALENDARIO (Clase “E” Tipo “C”) = Externas de Calendario
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalPorClave();

		// mensaje += fnDeshabesDesActualRed();

		// mensaje += fnDeshabesAntActualAmp();
		
		mensaje += fnMesesAntesYDespuesRedAmp();

		mensaje += fnClavesIgualesClaveLarga();

		var arrayTipoSolRed = [ 35 ];
		var arrayTipoSolAmp = [ 32 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 7 && tipoSolicitud == 1) {
		// Validacion tipo IC, IV. INTERNAS DE CALENDARIO (Clase “I” Tipo “C”) = Internas de Calendario
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalPorClave();

		mensaje += fnTotalMesCompesando();

		// mensaje += fnDeshabesDesActualRed();

		// mensaje += fnDeshabesAntActualAmp();
		
		mensaje += fnMesesAntesYDespuesRedAmp();

		mensaje += fnClavesIgualesClaveLarga();

		var arrayTipoSolRed = [ 35 ];
		var arrayTipoSolAmp = [ 32 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 7 && tipoSolicitud == 2) {
		// Validacion tipo IT, V. INTERNAS DE TRASPASO (Clase “I” Tipo “T”) = Internas Comunicables.
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		var validacionComponente = 0;

		console.log("****************");

		// Validaciones Tipo de Gasto
		var arrayRed = [ 2, 3, 7 ];
		var arrayAmp = [ 1 ];
		validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'ctga', 0);
		console.log("validacionComponente TG: "+validacionComponente);
		
		if (validacionComponente == 0) {
			// Validaciones Finalidad
			var arrayRed = [ 0, 2, 3, 4, 5, 6, 7, 8, 9 ];
			var arrayAmp = [ 1 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'id_finalidad', 0);
			console.log("validacionComponente FI 1: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Finalidad
			var arrayRed = [ 0, 1, 3, 4, 5, 6, 7, 8, 9 ];
			var arrayAmp = [ 2 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'id_finalidad', 0);
			console.log("validacionComponente FI 2: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Fuente de Financiamiento
			var arrayRed = [ 2, 3 ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cfin', 0);
			console.log("validacionComponente FF: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Fuente de Financiamiento Viceversa
			var arrayRed = [ ];
			var arrayAmp = [ 2, 3 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cfin', 0);
			console.log("validacionComponente FF Viceversa: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida
			var arrayRed = [ 2, 3, 4, 5, 6, 7, 8, 9 ];
			var arrayAmp = [ 1 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida: "+validacionComponente);
		}
		
		if (validacionComponente == 0) {
			// Validaciones Partida Viceversa
			var arrayRed = [ 1 ];
			var arrayAmp = [ 2, 3, 4, 5, 6, 7, 8, 9 ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida Viceversa: "+validacionComponente);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida 43101
			var arrayRed = [ '43101' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0);
			var validacionComponenteS1 = validacionComponente;
			console.log("validacionComponente Partida 43101: "+validacionComponente);

			var validacionComponenteS2 = 0;
			if (validacionComponenteS1 == 1) {
				var arrayRed = [ ];
				var arrayAmp = [ '43101' ];
				validacionComponenteS2 = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0);
			} else if (validacionComponente == 0) {
				var arrayRed = [ ];
				var arrayAmp = [ '43101' ];
				validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0);
				console.log("validacionComponente Partida 43101 viceversa: "+validacionComponente);
			}

			if (validacionComponenteS1 == 1 && validacionComponenteS2 == 1) {
				// Viene S con solo un registro
				// errorValidacion = 1;
				validacionComponente = 0;
			}

			console.log("validacionComponente Partida 43101 S1: "+validacionComponenteS1);
			console.log("validacionComponente Partida 43101 S2: "+validacionComponenteS2);
		}

		if (validacionComponente == 0) {
			// Validaciones Programa Presupuestario
			var arrayRed = [ 'S' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cppt', 0, 0, 1);
			var validacionComponenteS1 = validacionComponente;
			console.log("validacionComponenteS1 PP: "+validacionComponente);

			var validacionComponenteS2 = 0;
			if (validacionComponenteS1 == 1) {
				var arrayRed = [ ];
				var arrayAmp = [ 'S' ];
				validacionComponenteS2 = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cppt', 0, 0, 1);
				console.log("validacionComponenteS2 PP Viceversa: "+validacionComponente);
			} else if (validacionComponente == 0) {
				// Validaciones Programa Presupuestario, Viceversa
				var arrayRed = [ ];
				var arrayAmp = [ 'S' ];
				validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'cppt', 0, 0, 1);
				console.log("validacionComponente PP Viceversa: "+validacionComponente);
			}

			if (
				validacionComponenteS1 == 1 && validacionComponenteS2 == 1 
				&& 
				((datosReducciones.length == 1 || datosAmpliaciones.length == 1))
				) {
				// Viene S con solo un registro
				// errorValidacion = 1;
				validacionComponente = 0;
			}

			console.log("validacionComponente Partida S S1: "+validacionComponenteS1);
			console.log("validacionComponente Partida S S2: "+validacionComponenteS2);
		}

		console.log("validacionComponente 11: "+validacionComponente);

		if (validacionComponente == 0) {
			// Validaciones Partida 37
			var arrayRed = [ '37' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 2);
			var validacionComponenteS1 = validacionComponente;
			console.log("validacionComponente Partida 37: "+validacionComponente);

			var validacionComponenteS2 = 0;
			if (validacionComponenteS1 == 1) {
				var arrayRed = [ ];
				var arrayAmp = [ '37' ];
				validacionComponenteS2 = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 2);
			} else if (validacionComponente == 0) {
				// Validaciones Partida 37 Viceversa, con mensajes
				var arrayRed = [ ];
				var arrayAmp = [ '37' ];
				validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 2);
				console.log("validacionComponente Partida 37 Viceversa: "+validacionComponente);
			}

			if (
				validacionComponenteS1 == 1 && validacionComponenteS2 == 1 
				&& 
				((datosReducciones.length == 1 || datosAmpliaciones.length == 1))
				) {
				// Viene S con solo un registro
				// errorValidacion = 1;
				validacionComponente = 0;
			}

			console.log("validacionComponente Partida 37 S1: "+validacionComponenteS1);
			console.log("validacionComponente Partida 37 S2: "+validacionComponenteS2);
		}

		if (validacionComponente == 0) {
			// Validaciones Partida Capitulo 7
			var arrayRed = [ '7' ];
			var arrayAmp = [ ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida 7: "+validacionComponente);
		}
		
		if (validacionComponente == 0) {
			// Validaciones Partida Capitulo 7 Viceversa
			var arrayRed = [ ];
			var arrayAmp = [ '7' ];
			validacionComponente = fnValComponentePresupuestal(arrayRed, arrayAmp, 'partida_esp', 0, 0, 1);
			console.log("validacionComponente Partida 7 Viceversa: "+validacionComponente);
		}
		
		if (validacionComponente == 1) {
			// Si no se cumplen una de las reglas
			errorValidacion = 1;
		}

		mensaje += fnClaveCortaIgual();

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalMesCompesando();

		mensaje += fnClaveCortaDiferentes();

		if (errorValidacion == 1) {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Verficar información en cuanto a los campos clase y tipo de solicitud</p>';
		}

		var arrayTipoSolRed = [ 35 ];
		var arrayTipoSolAmp = [ 30, 32 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 6 && tipoSolicitud == 3) {
		// Validacion tipo ER, VI. CLASE “E” (Externa) TIPO DE SOLICITUD “R” (Reducción Ramo 23)
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		$('#'+tablaAmpliaciones+' tbody').empty();

		if (transno != 0) {
			for (var key in datosAmpliaciones) {
				for (var key2 in datosAmpliaciones[key]) {
					var dataJson2 = datosAmpliaciones[key];

					// Agregar datos para eliminar al guardar
					var obj = new Object();
					obj.type = type;
					obj.transno = transno;
					obj.clave = dataJson2[key2].accountcode;
					obj.tipo = 'Ampliacion';
					obj.tipoAdecuacion = $('#selectTipoDoc').val();
					datosEliminarRedAmp.push(obj);
				}
			}
		}
		
		datosAmpliaciones = new Array();

		mensaje += fnTAfectacionRed36();

		mensaje += fnSeleccionarConcR23();

		var arrayTipoSolRed = [ 36 ];
		var arrayTipoSolAmp = [ ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 6 && tipoSolicitud == 4) {
		// Validacion tipo EA, VII. CLASE “E” (Externa) TIPO DE SOLICITUD “A” (Ampliación Ramo 23)
		
		$("#txtBuscarReducciones").prop("disabled", true);
		$("#txtBuscarApliaciones").prop("disabled", false);

		$("#btnBuscarReduccion").prop("disabled", true);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		$('#'+tablaReducciones+' tbody').empty();

		if (transno != 0) {
			// Agregar datos para eliminar al guardar
			for (var key in datosReducciones) {
				for (var key2 in datosReducciones[key]) {
					var dataJson2 = datosReducciones[key];

					// Agregar datos para eliminar al guardar
					var obj = new Object();
					obj.type = type;
					obj.transno = transno;
					obj.clave = dataJson2[key2].accountcode;
					obj.tipo = 'Reduccion';
					obj.tipoAdecuacion = $('#selectTipoDoc').val();
					datosEliminarRedAmp.push(obj);
				}
			}
		}

		datosReducciones = new Array();

		mensaje += fnTAfectacionAmp31o33();

		mensaje += fnSeleccionarConcR23();

		var arrayTipoSolRed = [ ];
		var arrayTipoSolAmp = [ 31, 33 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 6 && tipoSolicitud == 5) {
		// Validacion tipo EI, VIII. CLASE “E” (Externa) TIPO DE SOLICITUD “I” (glyphicon-info-signresos Excedentes)
		
		$("#txtBuscarReducciones").prop("disabled", true);
		$("#txtBuscarApliaciones").prop("disabled", false);
		
		$("#btnBuscarReduccion").prop("disabled", true);
		$("#btnBuscarAmpliaciones").prop("disabled", false);

		$('#'+tablaReducciones+' tbody').empty();

		if (transno != 0) {
			// Agregar datos para eliminar al guardar
			for (var key in datosReducciones) {
				for (var key2 in datosReducciones[key]) {
					var dataJson2 = datosReducciones[key];

					// Agregar datos para eliminar al guardar
					var obj = new Object();
					obj.type = type;
					obj.transno = transno;
					obj.clave = dataJson2[key2].accountcode;
					obj.tipo = 'Reduccion';
					obj.tipoAdecuacion = $('#selectTipoDoc').val();
					datosEliminarRedAmp.push(obj);
				}
			}
		}
		
		datosReducciones = new Array();

		mensaje += fnTAfectacionAmp31o33();

		mensaje += fnSeleccionarConcR23(16);

		var arrayTipoSolRed = [ ];
		var arrayTipoSolAmp = [ 31, 33 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 6 && tipoSolicitud == 6) {
		// Validacion tipo EO, IX. CLASE “E” (Externa) TIPO DE SOLICITUD “O” (Otros Ramos)
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);
		
		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);
		
		mensaje += fnTAfectacionRed36();

		mensaje += fnTAfectacionAmp31o33();

		mensaje += fnDeshabilitaPanelNoSeleccion();

		var arrayTipoSolRed = [ 36 ];
		var arrayTipoSolAmp = [ 31, 33 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else if (tipoAdecuacion == 9 && tipoSolicitud == 1) {
		// Validacion tipo MC, X. MOVIMIENTOS DE CALENDARIO (Clase “M” Tipo “C”) = Movimientos de Calendario Internos
		
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", true);

		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", true);

		mensaje += fnTotalGeneralIgual();

		mensaje += fnTotalPorClave();

		mensaje += fnClavesIgualesClaveLarga();

		mensaje += fnRedAmpMesDiferente();

		var arrayTipoSolRed = [ 35 ];
		var arrayTipoSolAmp = [ 32 ];
		fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayTipoSolRed, arrayTipoSolAmp);
	}else{
		errorValidacion == 1;
		// Recargar datos para tipo de afectacion este completo
		fnRecargarDatosPaneles();
		if (tipoAdecuacion != '0' && tipoSolicitud != '0') {
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> '+tituloModalValidaciones+'</p></h3>';
			muestraModalGeneral(4, titulo, 'No existen validaciones para la Clase y Tipo de Solicitud seleccionada');
		}
	}

	mensaje += fnAgregarTipoAfectacionDefault();

	// Deshabilitar tipo de afectacion cuando existe adiciones
	fnDeshabilitarSeleccionTipoAfectacion();

	if (mostrarValidaciones == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> '+tituloModalValidaciones+'</p></h3>';
		muestraModalGeneral(4, titulo, mensaje);
	}

	if (guardarDatos == 1) {
		// solo cuando presiona el boton validar
		if (errorValidacion == 1) {
			statusGuardar = 1;
		}else{
			statusGuardar = 2;
		}
		//fnPresupuestoCaptura(statusGuardar, mensaje);
	}
}

function fnAgregarTipoAfectacionDefault() {
	// Función para asignar tipo de operacion por default
	var mensaje = '';
	// Recorrer ampliaciones
	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJson2 = datosAmpliaciones[key];
			if (dataJson2[key2].tipoAfectacion == '0' && ($("#selectTipoSolicitud").val() == 2 || $("#selectTipoSolicitud").val() == 4)) {
				// Si no tiene selección y es de Traspaso
				var nombreSelect = "selectTipoDoc_"+panelAmpliaciones+"_"+dataJson2[key2].accountcode+"";
				var datoEncontrado = 0;
				$("#"+nombreSelect+" option").each(function(){
					// console.log('opcion '+$(this).text()+' valor '+ $(this).attr('value'));
					if ($(this).attr('value') == '32' || $(this).attr('value') == '33') {
						datoEncontrado = $(this).attr('value');
					}
				});
				// console.log("datoEncontrado: "+datoEncontrado);
				if (datoEncontrado != 0) {
					dataJson2[key2].tipoAfectacion = datoEncontrado;
					$('#'+nombreSelect).val(''+datoEncontrado);
					$('#'+nombreSelect).multiselect('rebuild');
				}
			}
		}
	}
	datosAmpliaciones = datosAmpliaciones;

	return mensaje;
}

function fnValidarClavesMontoCero() {
	// Valida si existen claves con monto 0, en aplicaciones y reducciones
	var mensaje = "";

	// Validar Reducciones
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			// Variable buscar informacion
			var encontroClave = 1;
            for (var mes in dataJsonMeses) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                if (parseFloat(dataJsonReducciones[key2][nombreMesSel]) != parseFloat('0')) {
                	// Si selecciono dos claves iguales
                	encontroClave = 0;
                }
        	}
        	if (encontroClave == 1) {
        		// Todos los montos son en 0
        		errorValidacion = 1;
        		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html()+' no tiene montos capturados</p>';
        	}
		}
	}

	// Validar Ampliaciones
	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			// Variable buscar informacion
			var encontroClave = 1;
            for (var mes in dataJsonMeses) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                if (parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]) != parseFloat('0')) {
                	// Si selecciono dos claves iguales
                	encontroClave = 0;
                }
        	}
        	if (encontroClave == 1) {
        		// Todos los montos son en 0
        		errorValidacion = 1;
        		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html()+' no tiene montos capturados</p>';
        	}
		}
	}

	return mensaje;
}

function fnRedAmpMesDiferente() {
	// Valida que el mes de la reduccion sea diferente de la ampliacion
	var mensaje = "";

	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			// Variable buscar informacion
			var encontroClave = 0;
            for (var keyAmp in datosAmpliaciones ) {
				for (var key2Amp in datosAmpliaciones[keyAmp]) {
					var dataJsonAmpliaciones = datosAmpliaciones[keyAmp];
					if (dataJsonReducciones[key2].accountcode == dataJsonAmpliaciones[key2Amp].accountcode) {
						
						for (var mes in dataJsonMeses) {
			                var nombreMes = dataJsonMeses[mes];
			                var nombreMesSel = dataJsonMeses[mes]+"Sel";
			                var totRed = dataJsonReducciones[key2][nombreMesSel];
			                var totAmp = dataJsonAmpliaciones[key2Amp][nombreMesSel];

			                if (parseFloat(totRed) != parseFloat(0) && parseFloat(totAmp) != parseFloat(0)) {
			                	// Si selecciono dos claves iguales
			                	errorValidacion = 1;
								mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
								mensaje += ' La Clave '+dataJsonReducciones[key2].accountcode+' repite mes de Reducción y Ampliación en el Mes de '+nombreMes+'</p>';
			                }
		            	}

					}
				}
			}
		}
	}

	return mensaje;
}

function fnClaveCortaDiferentes() {
	// Valida que la clave corta de reduccion no se encuentre ampliacion y viceversa
	var mensaje = "";

	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			// Variable buscar informacion
			var encontroClave = 0;
            for (var keyAmp in datosAmpliaciones ) {
				for (var key2Amp in datosAmpliaciones[keyAmp]) {
					var dataJsonAmpliaciones = datosAmpliaciones[keyAmp];
					if (dataJsonReducciones[key2].claveCorta == dataJsonAmpliaciones[key2Amp].claveCorta) {
						encontroClave = 1;
					}
				}
			}

			if (encontroClave == 1) {
				errorValidacion = 1;
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Reducciones la clave corta '+dataJsonReducciones[key2].claveCorta+' se encuentra en Ampliaciones</p>';
			}
		}
	}

	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			// Variable buscar informacion
			var encontroClave = 0;
            for (var keyAmp in datosReducciones ) {
				for (var key2Amp in datosReducciones[keyAmp]) {
					var dataJsonReducciones = datosReducciones[keyAmp];
					if (dataJsonAmpliaciones[key2].claveCorta == dataJsonReducciones[key2Amp].claveCorta) {
						encontroClave = 1;
					}
				}
			}

			if (encontroClave == 1) {
				errorValidacion = 1;
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ampliaciones la clave corta '+dataJsonReducciones[key2].claveCorta+' se encuentra en Reducciones</p>';
			}
		}
	}

	return mensaje;
}

function fnClavesIgualesClaveLarga() {
	// Validar que las claves se encuentren en Reduccion y Ampliacion (Claves iguales)
	var mensaje = "";

	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			// Variable buscar informacion
			var encontroClave = 0;
            for (var keyAmp in datosAmpliaciones ) {
				for (var key2Amp in datosAmpliaciones[keyAmp]) {
					var dataJsonAmpliaciones = datosAmpliaciones[keyAmp];
					if (dataJsonReducciones[key2].accountcode == dataJsonAmpliaciones[key2Amp].accountcode) {
						encontroClave = 1;
					}
				}
			}

			if (encontroClave == 0) {
				errorValidacion = 1;
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html()+' no se encuentra en Ampliaciones</p>';
			}
		}
	}

	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			// Variable buscar informacion
			var encontroClave = 0;
            for (var keyAmp in datosReducciones ) {
				for (var key2Amp in datosReducciones[keyAmp]) {
					var dataJsonReducciones = datosReducciones[keyAmp];
					if (dataJsonAmpliaciones[key2].accountcode == dataJsonReducciones[key2Amp].accountcode) {
						encontroClave = 1;
					}
				}
			}

			if (encontroClave == 0) {
				errorValidacion = 1;
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html()+' no se encuentra en Reducciones</p>';
			}
		}
	}

	return mensaje;
}

function fnTotalMesCompesandoClaveCorta() {
	// Valida el total del mes compensado a nivel clave corta
	var mensaje = "";
	var clavesReduccion = new Array();
	var clavesAmpliacion = new Array();

	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			// console.log("red larga: "+dataJsonReducciones[key2].accountcode);
			// console.log("red corta: "+dataJsonReducciones[key2].claveCorta);
			var encontroClave = 0;
			for (var keyClaves in clavesReduccion) {
				if (dataJsonReducciones[key2].claveCorta == clavesReduccion[keyClaves].claveCorta) {
					encontroClave = 1;
					for (var mes in dataJsonMeses) {
						var nombreMesSel = dataJsonMeses[mes]+"Sel";
						var datoArray = clavesReduccion[keyClaves][nombreMesSel];
						datoArray = parseFloat(datoArray) + parseFloat(dataJsonReducciones[key2][nombreMesSel]);
						clavesReduccion[keyClaves][nombreMesSel] = datoArray;
					}
				}
			}
			// console.log("encontroClave: "+encontroClave);
			if (encontroClave == 0) {
				var obj = new Object();
				obj['claveCorta'] = dataJsonReducciones[key2].claveCorta;
				for (var mes in dataJsonMeses) {
					var nombreMesSel = dataJsonMeses[mes]+"Sel";
					obj[nombreMesSel] = parseFloat(dataJsonReducciones[key2][nombreMesSel]);
				}
				clavesReduccion.push(obj);
			}
		}
	}

	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			// console.log("amp larga: "+dataJsonAmpliaciones[key2].accountcode);
			// console.log("amp corta: "+dataJsonAmpliaciones[key2].claveCorta);
			var encontroClave = 0;
			for (var keyClaves in clavesAmpliacion) {
				if (dataJsonAmpliaciones[key2].claveCorta == clavesAmpliacion[keyClaves].claveCorta) {
					encontroClave = 1;
					for (var mes in dataJsonMeses ) {
						var nombreMesSel = dataJsonMeses[mes]+"Sel";
						var datoArray = clavesAmpliacion[keyClaves][nombreMesSel];
						datoArray = parseFloat(datoArray) + parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
						clavesAmpliacion[keyClaves][nombreMesSel] = datoArray;
					}
				}
			}
			// console.log("encontroClave: "+encontroClave);
			if (encontroClave == 0) {
				var obj = new Object();
				obj['claveCorta'] = dataJsonAmpliaciones[key2].claveCorta;
				for (var mes in dataJsonMeses ) {
					var nombreMesSel = dataJsonMeses[mes]+"Sel";
					obj[nombreMesSel] = parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
				}
				clavesAmpliacion.push(obj);
			}
		}
	}

	// console.log("clavesReduccion agrupadas: "+JSON.stringify(clavesReduccion));
	// console.log("clavesAmpliacion agrupadas: "+JSON.stringify(clavesAmpliacion));

	for (var keyClavesReduccion in clavesReduccion) {
		// console.log("clave: "+clavesReduccion[keyClavesReduccion].claveCorta);
		var encontroClave = 0;
		for (var keyClavesAmpliacion in clavesAmpliacion) {
			if (clavesReduccion[keyClavesReduccion].claveCorta == clavesAmpliacion[keyClavesAmpliacion].claveCorta) {
				encontroClave = 1;
				for (var mes in dataJsonMeses ) {
					var nombreMesSel = dataJsonMeses[mes]+"Sel";
					if (parseFloat(clavesReduccion[keyClavesReduccion][nombreMesSel]) != parseFloat(clavesAmpliacion[keyClavesAmpliacion][nombreMesSel])) {
						errorValidacion = 1;
						mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
						mensaje += 'Existen diferencias en la Clave Corta ';
						mensaje += clavesReduccion[keyClavesReduccion].claveCorta+' en el Mes de '+dataJsonMeses[mes]+'</p>';
					}
				}
			}
		}
		// console.log("encontroClave: "+encontroClave);
		if (encontroClave == 0) {
			errorValidacion = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Existen diferencias en la Clave Corta '+clavesReduccion[keyClavesReduccion].claveCorta+'</p>';
		}
	}
	
	return mensaje;
}

function fnValComponentePresupuestal(arrayValRed, arrayValAmp, campoValidar="", obligatorio=1, caracterInicio=0, caracterFin=0) {
	// Validacion por componente de la clave presupuestal
	var mensaje = "";
	var valDatoComponente = 0;
	var valDatoComponenteRed = 0;
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			var encontroDato = 0;
			var valorCampo = ""; // Valor de la clave
			var datosSel = ""; // Datos a elegir
			var nombreVisual = ""; // Nombre de Etiqueta
			// console.log("dataJsonReducciones: "+JSON.stringify(dataJsonReducciones));
			for (var keyClave in dataJsonReducciones[key2].datosClave) {
				// console.log("nombreCampo: "+dataJsonReducciones[key].datosClave[keyClave].nombreCampo+" - valor: "+dataJsonReducciones[key].datosClave[keyClave].valor);
				if (dataJsonReducciones[key2].datosClave[keyClave].nombreCampo == campoValidar) {
					// Si es el campo que debe validar
					valorCampo = dataJsonReducciones[key2].datosClave[keyClave].valor;
					nombreVisual = dataJsonReducciones[key2].datosClave[keyClave].nombre;
					for (var i = 0; i < arrayValRed.length; i++) {
						// Recorrer informacion puede seleccionar
						var valorClave = dataJsonReducciones[key2].datosClave[keyClave].valor;
						if (caracterFin != 0) {
							// Solo caracteres empezando de primero
							valorClave = valorClave.substr(caracterInicio, caracterFin);
						}
						if (valorClave == arrayValRed[i]) {
							encontroDato = 1;
							valDatoComponenteRed = 1;
						}
						if (i == 0) {
							datosSel += arrayValRed[i];
						}else{
							datosSel += ", "+arrayValRed[i];
						}
					}
					break;
				}
			}
			// if (encontroDato == 0 && obligatorio == 1) {
			// 	errorValidacion = 1;
			// 	if (valDatoComponente == 0) {
			// 		valDatoComponente = 1;
			// 		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+nombreVisual+' para Reducciones debe ser '+datosSel+'</p>';
			// 	}
			// 	mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html()+' el '+nombreVisual+' es '+valorCampo+'</p>';
			// }
		}
	}

	var valDatoComponente = 0;
	var valDatoComponenteAmp = 0;
	for (var keyAmp in datosAmpliaciones ) {
		for (var key2Amp in datosAmpliaciones[keyAmp]) {
			var dataJsonAmpliaciones = datosAmpliaciones[keyAmp];
			// console.log("ampliacion: "+JSON.stringify(datosAmpliaciones[keyAmp]));
			var encontroDato = 0;
			var valorCampo = ""; // Valor de la clave
			var datosSel = ""; // Datos a elegir
			var nombreVisual = ""; // Nombre de Etiqueta
			for (var keyClave in dataJsonAmpliaciones[key2Amp].datosClave) {
				// console.log("nombreCampo: "+dataJsonAmpliaciones[key].datosClave[keyClave].nombreCampo+" - valor: "+dataJsonAmpliaciones[key].datosClave[keyClave].valor);
				if (dataJsonAmpliaciones[key2Amp].datosClave[keyClave].nombreCampo == campoValidar) {
					// Si es el campo que debe validar
					valorCampo = dataJsonAmpliaciones[key2Amp].datosClave[keyClave].valor;
					nombreVisual = dataJsonAmpliaciones[key2Amp].datosClave[keyClave].nombre;
					for (var i = 0; i < arrayValAmp.length; i++) {
						// Recorrer informacion puede seleccionar
						var valorClave = dataJsonAmpliaciones[key2Amp].datosClave[keyClave].valor;
						if (caracterFin != 0) {
							// Solo caracteres empezando de primero
							valorClave = valorClave.substr(caracterInicio, caracterFin);
						}
						if (valorClave == arrayValAmp[i]) {
							encontroDato = 1;
							valDatoComponenteAmp = 1;
						}
						if (i == 0) {
							datosSel += arrayValAmp[i];
						}else{
							datosSel += ", "+arrayValAmp[i];
						}
					}
				}
			}
			// if (encontroDato == 0 && obligatorio == 1) {
			// 	errorValidacion = 1;
			// 	if (valDatoComponente == 0) {
			// 		valDatoComponente = 1;
			// 		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+nombreVisual+' para Ampliaciones debe ser '+datosSel+'</p>';
			// 	}
			// 	mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html()+' el '+nombreVisual+' es '+valorCampo+'</p>';
			// }
		}
	}

	if (valDatoComponenteRed == 1 && valDatoComponenteAmp == 1) {
		mensaje = 1;
	} else if (valDatoComponenteRed === 1 && arrayValAmp.length == 0) {
		mensaje = 1;
	} else if (valDatoComponenteAmp === 1 && arrayValRed.length == 0) {
		mensaje = 1;
	} else {
		mensaje = 0;
	}

	return mensaje;
}

function fnValComponentePresupuestal_ANT(arrayValRed, arrayValAmp, campoValidar="", obligatorio=1) {
	// Validacion por componente de la clave presupuestal
	var mensaje = "";
	var valDatoComponente = 0;
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			var encontroDato = 0;
			var valorCampo = ""; // Valor de la clave
			var datosSel = ""; // Datos a elegir
			var nombreVisual = ""; // Nombre de Etiqueta
			// console.log("dataJsonReducciones: "+JSON.stringify(dataJsonReducciones));
			for (var keyClave in dataJsonReducciones[key2].datosClave) {
				// console.log("nombreCampo: "+dataJsonReducciones[key].datosClave[keyClave].nombreCampo+" - valor: "+dataJsonReducciones[key].datosClave[keyClave].valor);
				if (dataJsonReducciones[key2].datosClave[keyClave].nombreCampo == campoValidar) {
					// Si es el campo que debe validar
					valorCampo = dataJsonReducciones[key2].datosClave[keyClave].valor;
					nombreVisual = dataJsonReducciones[key2].datosClave[keyClave].nombre;
					for (var i = 0; i < arrayValRed.length; i++) {
						// Recorrer informacion puede seleccionar
						var valorClave = dataJsonReducciones[key2].datosClave[keyClave].valor;
						if (campoValidar == 'partida_esp') {
							// Si es Partida, Solo validar Primer Caracter
							valorClave = valorClave.substr(0, 1);
						}
						if (valorClave == arrayValRed[i]) {
							encontroDato = 1;
						}
						if (i == 0) {
							datosSel += arrayValRed[i];
						}else{
							datosSel += ", "+arrayValRed[i];
						}
					}
					break;
				}
			}
			if (encontroDato == 0 && obligatorio == 1) {
				errorValidacion = 1;
				if (valDatoComponente == 0) {
					valDatoComponente = 1;
					mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+nombreVisual+' para Reducciones debe ser '+datosSel+'</p>';
				}
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html()+' el '+nombreVisual+' es '+valorCampo+'</p>';
			}
		}
	}

	var valDatoComponente = 0;
	for (var keyAmp in datosAmpliaciones ) {
		for (var key2Amp in datosAmpliaciones[keyAmp]) {
			var dataJsonAmpliaciones = datosAmpliaciones[keyAmp];
			// console.log("ampliacion: "+JSON.stringify(datosAmpliaciones[keyAmp]));
			var encontroDato = 0;
			var valorCampo = ""; // Valor de la clave
			var datosSel = ""; // Datos a elegir
			var nombreVisual = ""; // Nombre de Etiqueta
			for (var keyClave in dataJsonAmpliaciones[key2Amp].datosClave) {
				// console.log("nombreCampo: "+dataJsonAmpliaciones[key].datosClave[keyClave].nombreCampo+" - valor: "+dataJsonAmpliaciones[key].datosClave[keyClave].valor);
				if (dataJsonAmpliaciones[key2Amp].datosClave[keyClave].nombreCampo == campoValidar) {
					// Si es el campo que debe validar
					valorCampo = dataJsonAmpliaciones[key2Amp].datosClave[keyClave].valor;
					nombreVisual = dataJsonReducciones[key2Amp].datosClave[keyClave].nombre;
					for (var i = 0; i < arrayValAmp.length; i++) {
						// Recorrer informacion puede seleccionar
						var valorClave = dataJsonAmpliaciones[key2Amp].datosClave[keyClave].valor;
						if (campoValidar == 'partida_esp') {
							// Si es Partida, Solo validar Primer Caracter
							valorClave = valorClave.substr(0, 1);
						}
						if (valorClave == arrayValAmp[i]) {
							encontroDato = 1;
						}
						if (i == 0) {
							datosSel += arrayValAmp[i];
						}else{
							datosSel += ", "+arrayValAmp[i];
						}
					}
				}
			}
			if (encontroDato == 0 && obligatorio == 1) {
				errorValidacion = 1;
				if (valDatoComponente == 0) {
					valDatoComponente = 1;
					mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+nombreVisual+' para Ampliaciones debe ser '+datosSel+'</p>';
				}
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html()+' el '+nombreVisual+' es '+valorCampo+'</p>';
			}
		}
	}

	return mensaje;
}

function fnMesesAntesYDespuesRedAmp() {
	// Los meses a reducir y/o ampliar dependerán del mes en que se elabora la Adecuación Presupuestaria
	// Ejemplo: si la fecha de hoy correspondiera al mes de abril, solamente podría reducir entre enero y abril, y la ampliación sería entre mayo y diciembre. 
	// O viceversa, la ampliación sería entre enero y abril, y la reducción sería entre mayo y diciembre.
	var mensaje = "";
	//console.log("********");
	//console.log("mesActualAdecuacion: "+mesActualAdecuacion);
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
            
            var numMes = 1;
			var numMenor = 0;
            var numMayor = 0;
			for (var mes in dataJsonMeses) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                if (Number(numMes) <= Number(mesActualAdecuacion) && parseFloat(dataJsonReducciones[key2][nombreMesSel]) != 0) {
                	//console.log("selecccionado en "+nombreMes);
                	numMenor = 1;
                }

                if (Number(numMes) > Number(mesActualAdecuacion) && parseFloat(dataJsonReducciones[key2][nombreMesSel]) != 0) {
                	//console.log("selecccionado en "+nombreMes);
                	numMayor = 1;
                }
                numMes ++;
            }

            //console.log("numMenor: "+numMenor);
            //console.log("numMayor: "+numMayor);
            //console.log("datosAmpliaciones: "+JSON.stringify(datosAmpliaciones));
            for (var keyAmp in datosAmpliaciones ) {
				for (var key2Amp in datosAmpliaciones[keyAmp]) {
					var dataJsonAmpliaciones = datosAmpliaciones[keyAmp];

					if (dataJsonReducciones[key2].accountcode == dataJsonAmpliaciones[key2Amp].accountcode) {
						var numMes = 1;
						for (var mes in dataJsonMeses ) {
			                var nombreMes = dataJsonMeses[mes];
			                var nombreMesSel = dataJsonMeses[mes]+"Sel";

			                //console.log("numMes: "+Number(numMes)+" - mesActualAdecuacion: "+Number(mesActualAdecuacion)+" - total: "+parseFloat(dataJsonAmpliaciones[key2Amp][nombreMesSel]));
			               	var errorVal = 0;
			                if (numMenor == 1 && Number(numMes) <= Number(mesActualAdecuacion) && parseFloat(dataJsonAmpliaciones[key2Amp][nombreMesSel]) != 0) {
			                	// console.log("error seleccion de claves antes del actual");
			                	errorValidacion = 1;
			                	errorVal = 1;
				            }
				            if (numMayor == 1 && Number(numMes) > Number(mesActualAdecuacion) && parseFloat(dataJsonAmpliaciones[key2Amp][nombreMesSel]) != 0) {
				            	// console.log("error seleccion de claves despues del actual");
				            	errorValidacion = 1;
				            	errorVal = 1;
				            }

				            if (errorVal == 1) {
				            	mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
								mensaje += 'Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html();
								mensaje += ' y Ampliaciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelAmpliaciones).html();
								mensaje += '. La Ampliación debe ser diferente a los meses que se reduce.</p>';
				            }

							numMes ++;
			            }
					}
				}
			}
		}
	}

	return mensaje;
}

function fnDeshabilitaPanelNoSeleccion() {
	// Valida que agregaste primero, Reduccción o Ampliación y deshabilita la otra opción
	var mensaje = "";
	if (datosReducciones.length > 0) {
		// Deshabilitar Ampliaciones
		$("#txtBuscarApliaciones").prop("disabled", true);
		$("#btnBuscarAmpliaciones").prop("disabled", true);
		datosAmpliaciones = new Array();
		$('#'+tablaAmpliaciones+' tbody').empty();
	}else if (datosAmpliaciones.length > 0) {
		// Deshabilitar Reducciones
		$("#txtBuscarReducciones").prop("disabled", true);
		$("#btnBuscarReduccion").prop("disabled", true);
		datosReducciones = new Array();
		$('#'+tablaReducciones+' tbody').empty();
	}else{
		// Habilitar Reducciones y Ampliaciones
		$("#txtBuscarReducciones").prop("disabled", false);
		$("#txtBuscarApliaciones").prop("disabled", false);
		
		$("#btnBuscarReduccion").prop("disabled", false);
		$("#btnBuscarAmpliaciones").prop("disabled", false);
	}
	return mensaje;
}

function fnSeleccionarConcR23(optionVal="") {
	// Valida Concepto Ramo 23 que sea selecccionado
	var mensaje = "";
	if (optionVal == "" && ($("#selectConcR23").val() == '0') || $("#selectConcR23").val() == '' || $("#selectConcR23").val() == null) {
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Concepto del Ramo 23</p>';
	}else if (optionVal != "" && $("#selectConcR23").val() != optionVal) {
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Concepto del Ramo 23 ('+optionVal+')</p>';
	}else{
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Concepto del Ramo 23</p>';
	}
	return mensaje;	
}

function fnDeshabesAntActualAmp() {
	// Deshabilita meses antes del actual y el actual en Ampliaciones
	var mensaje = "";
	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			var numMes = 1;
			for (var mes in dataJsonMeses ) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                //console.log(""+nombreMes+" - mesActualAdecuacion: "+mesActualAdecuacion+" - numMes: "+numMes);
                if (Number(mesActualAdecuacion) >= Number(numMes)) {
                	totalAmpliaciones = parseFloat(totalAmpliaciones) - parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
                	fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
					dataJsonAmpliaciones[key2][nombreMesSel] = 0;
					$("#"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones+"_"+nombreMes).val("0");
					$("#"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones+"_"+nombreMes).prop("disabled", true);
				}else{
					$("#"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones+"_"+nombreMes).prop("disabled", false);
				}
				numMes ++;
            }
		}
	}

	return mensaje;
}

function fnDeshabesDesActualRed() {
	// Deshabilita meses despues del actual en Reducciones
	var mensaje = "";
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			var numMes = 1;
			for (var mes in dataJsonMeses ) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                
                if (Number(mesActualAdecuacion) < Number(numMes)) {
                	totalReducciones = parseFloat(totalReducciones) - parseFloat(dataJsonReducciones[key2][nombreMesSel]);
                	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
					dataJsonReducciones[key2][nombreMesSel] = 0;
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).val("0");
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
				}else{
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", false);
				}
				numMes ++;
            }
		}
	}

	return mensaje;
}

function fnDeshabilitarSeleccionTipoAfectacion() {
	// Función para deshabilitar tipo de afectacion cuando exiten adiciones
	var seleccion = 1;
	var mensaje = "";
	
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			if (dataJsonReducciones[key2].tipoAfectacion == '0') {
				// seleccion = 0;
				// mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La clave '+dataJsonReducciones[key2].accountcode;
				// mensaje += ' del panel Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html();
				// mensaje += ' sin Tipo de Operación</p>';
			} else {
				// Validar si es Adición
				// dataJsonTipoDocReduccion
				// dataJsonTipoDocAmpliacion
				for (var keyAfectacion in dataJsonTipoDocReduccion) {
					// Recorrer Reducciones o Amplicaciones
					if (dataJsonTipoDocReduccion[keyAfectacion].value == dataJsonReducciones[key2].tipoAfectacion && dataJsonTipoDocReduccion[keyAfectacion].nu_claveNueva == 1) {
						// Deshabilitar seleccion
						$('#selectTipoDoc_'+panelReducciones+"_"+dataJsonReducciones[key2].accountcode).multiselect('disable');
					}
				}
			}
		}
	}

	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			if (dataJsonAmpliaciones[key2].tipoAfectacion == '0') {
				// seleccion = 0;
				// mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La clave '+dataJsonAmpliaciones[key2].accountcode;
				// mensaje += ' del panel Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html();
				// mensaje += ' sin Tipo de Operación</p>';
			} else {
				// Validar si es Adición
				// dataJsonTipoDocReduccion
				// dataJsonTipoDocAmpliacion
				for (var keyAfectacion in dataJsonTipoDocAmpliacion) {
					// Recorrer Reducciones o Amplicaciones
					if (dataJsonTipoDocAmpliacion[keyAfectacion].value == dataJsonAmpliaciones[key2].tipoAfectacion && dataJsonTipoDocAmpliacion[keyAfectacion].nu_claveNueva == 1) {
						// Deshabilitar seleccion
						$('#selectTipoDoc_'+panelAmpliaciones+"_"+dataJsonAmpliaciones[key2].accountcode).multiselect('disable');
					}
				}
			}
		}
	}

	return mensaje;
}

function fnValidarSeleccionTipoAfectacion(valSoloClave=0) {
	var seleccion = 1;
	var mensaje = "";
	
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			if (dataJsonReducciones[key2].tipoAfectacion == '0' && valSoloClave == 0) {
				seleccion = 0;
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La clave '+dataJsonReducciones[key2].accountcode;
				mensaje += ' del panel Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html();
				mensaje += ' sin Tipo de Operación</p>';
			} else {
				// Validar si es Adición
				// dataJsonTipoDocReduccion
				// dataJsonTipoDocAmpliacion
				for (var keyAfectacion in dataJsonTipoDocReduccion) {
					// Recorrer Reducciones o Amplicaciones
					if (dataJsonTipoDocReduccion[keyAfectacion].value == dataJsonReducciones[key2].tipoAfectacion && dataJsonTipoDocReduccion[keyAfectacion].nu_claveNueva == 1) {
						// Deshabilitar seleccion
						$('#selectTipoDoc_'+panelReducciones+"_"+dataJsonReducciones[key2].accountcode).multiselect('disable');
						
						// Validar estrcutuca programatica
						var estProgramatica = fnGenerarEstructuraClaveNueva(dataJsonReducciones[key2].datosClave, 'nu_programatica', 'nu_programatica_orden', '-');
						var estEconomica = fnGenerarEstructuraClaveNueva(dataJsonReducciones[key2].datosClave, 'nu_economica', 'nu_economica_orden', '-');
						var estAdministrativa = fnGenerarEstructuraClaveNueva(dataJsonReducciones[key2].datosClave, 'nu_administrativa', 'nu_administrativa_orden', '-');
						var estPartida = fnGenerarEstructuraClaveNueva(dataJsonReducciones[key2].datosClave, 'nu_relacion_partida', 'nu_relacion_partida_orden', '-');
						// console.log("estProgramatica: "+estProgramatica);
						// console.log("estEconomica: "+estEconomica);
						// console.log("estAdministrativa: "+estAdministrativa);
						// console.log("estPartida: "+estPartida);
						var respuesta = fnValidarEstructuraClaveNueva(estProgramatica, estEconomica, estAdministrativa, estPartida, 'En Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html());
						if (respuesta != 1 && valSoloClave == 0) {
							// Si tiene error mostrar
							errorValidacion = 1;
							mensaje += respuesta;
						}

						// Validar si existe clave
						respuesta = fnValidarExisteClaveAdicion(dataJsonReducciones[key2].accountcode, 'En Reducciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones).html());
						if (respuesta != 1) {
							// Si tiene error mostrar
							errorValidacion = 1;
							mensaje += respuesta;
						}
					}
				}
			}
		}
	}

	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			if (dataJsonAmpliaciones[key2].tipoAfectacion == '0' && valSoloClave == 0) {
				seleccion = 0;
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La clave '+dataJsonAmpliaciones[key2].accountcode;
				mensaje += ' del panel Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html();
				mensaje += ' sin Tipo de Operación</p>';
			} else {
				// Validar si es Adición
				// dataJsonTipoDocReduccion
				// dataJsonTipoDocAmpliacion
				for (var keyAfectacion in dataJsonTipoDocAmpliacion) {
					// Recorrer Reducciones o Amplicaciones
					if (dataJsonTipoDocAmpliacion[keyAfectacion].value == dataJsonAmpliaciones[key2].tipoAfectacion && dataJsonTipoDocAmpliacion[keyAfectacion].nu_claveNueva == 1) {
						// Deshabilitar seleccion
						$('#selectTipoDoc_'+panelAmpliaciones+"_"+dataJsonAmpliaciones[key2].accountcode).multiselect('disable');

						// Validar estrcutuca programatica
						var estProgramatica = fnGenerarEstructuraClaveNueva(dataJsonAmpliaciones[key2].datosClave, 'nu_programatica', 'nu_programatica_orden', '-');
						var estEconomica = fnGenerarEstructuraClaveNueva(dataJsonAmpliaciones[key2].datosClave, 'nu_economica', 'nu_economica_orden', '-');
						var estAdministrativa = fnGenerarEstructuraClaveNueva(dataJsonAmpliaciones[key2].datosClave, 'nu_administrativa', 'nu_administrativa_orden', '-');
						var estPartida = fnGenerarEstructuraClaveNueva(dataJsonAmpliaciones[key2].datosClave, 'nu_relacion_partida', 'nu_relacion_partida_orden', '-');
						// console.log("estProgramatica: "+estProgramatica);
						// console.log("estEconomica: "+estEconomica);
						// console.log("estAdministrativa: "+estAdministrativa);
						// console.log("estPartida: "+estPartida);
						var respuesta = fnValidarEstructuraClaveNueva(estProgramatica, estEconomica, estAdministrativa, estPartida, 'En Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html());
						if (respuesta != 1 && valSoloClave == 0) {
							// Si tiene error mostrar
							errorValidacion = 1;
							mensaje += respuesta;
						}

						// Validar si existe clave
						respuesta = fnValidarExisteClaveAdicion(dataJsonAmpliaciones[key2].accountcode, 'En Ampliaciones linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html());
						if (respuesta != 1) {
							// Si tiene error mostrar
							errorValidacion = 1;
							mensaje += respuesta;
						}
					}
				}
			}
		}
	}

	if (seleccion == 1 && valSoloClave == 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Tipo de Operación en Reducciones</p>';
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Tipo de Operación en Ampliaciones</p>';
	}else{
		errorValidacion = 1;
	}

	return mensaje;
}

function fnValidarExisteClaveAdicion(accountcode, mensajeInicial = '') {
	// Funcion para validar si existe la clave de adicion
	var respuesta = 0;

	dataObj = { 
	        option: 'validarClaveAdicion',
	        accountcode: accountcode,
	        transno: transno,
			mensajeInicial: mensajeInicial
	      };
    $.ajax({
	  async:false,
	  cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/GLBudgetsByTagV2_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
        	respuesta = 1;
        }else{
        	respuesta = data.contenido;
        }
    })
    .fail(function(result) {
        console.log( result );
    });

	return respuesta;
}

function fnValidarEstructuraClaveNueva(estProgramatica, estEconomica, estAdministrativa, estPartida, mensajeInicial = '') {
	// Funcion para validar las estructuras
	var respuesta = 0;

	dataObj = { 
	        option: 'validarEstructuras',
	        estProgramatica: estProgramatica,
	        estEconomica: estEconomica,
	        estAdministrativa: estAdministrativa,
			estPartida: estPartida,
			mensajeInicial: mensajeInicial
	      };
    $.ajax({
	  async:false,
	  cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/GLBudgetsByTagV2_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
        	respuesta = 1;
        }else{
        	respuesta = data.contenido;
        }
    })
    .fail(function(result) {
        console.log( result );
    });

	return respuesta;
}

function fnGenerarEstructuraClaveNueva(JSONDatos, tipoEstructura = '', ordenEstructura = '', separacion = '-') {
	// Validar estructura Programatica
	var estructura = '';
	var datosFormarCadena = new Array();
	var numElementos = 0; // Numero de elementos de la estructura
	for (var key in JSONDatos ) {
		if (JSONDatos[key][''+tipoEstructura] == 1 && Number(JSONDatos[key][''+ordenEstructura]) > Number(numElementos)) {
			// Obtener el numero de elementos de la estructura
			// console.log("nombre: "+JSONDatos[key].nombre);
			numElementos = JSONDatos[key][''+ordenEstructura];
		}
	}
	
	for (var elemento = 1;  elemento <= numElementos; elemento++) {
		// Formar la cadena con los elementos
		for (var key in JSONDatos ) {
			if (JSONDatos[key][''+tipoEstructura] == 1 && Number(JSONDatos[key][''+ordenEstructura]) == Number(elemento)) {
				if (estructura == '') {
					estructura = JSONDatos[key].valor;
				} else {
					estructura = estructura + separacion + JSONDatos[key].valor;
				}
			}
		}
	}
	return estructura;
}

function fnValidarTipoAfectacion(tipoAdecuacion, tipoSolicitud, arrayReduccion, arrayAmpliacion) {
	// Validar Reducciones
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			var nombreSelect = "selectTipoDoc_"+panelReducciones+"_"+dataJsonReducciones[key2].accountcode+"";
			// var valorSelect = $("#"+nombreSelect).val();
			// console.log("valorSelect: "+valorSelect);
			// console.log("nombreSelect: "+nombreSelect);
		    $("#"+nombreSelect+" option").each(function(){
				// console.log('opcion '+$(this).text()+' valor '+ $(this).attr('value'));
				if ($(this).attr('value') != '0') {
					var encontro = 0;
					for (var i = 0; i < arrayReduccion.length; i++) {
						if ($(this).attr('value') == arrayReduccion[i]) {
							encontro = 1;
							break;
						}
						//console.log("En el índice '" + i + "' hay este valor: " + arrayReduccion[i]);
					}
					if (encontro == 0) {
						// Eliminar datos
						$("#"+nombreSelect+" option[value='"+$(this).attr('value')+"']").remove();
					}
				}
			});

		    // Validar si es 1 registro seleccion por default, Inicio
			var numRegistros = 0;
			var cmbDatoSelect = '';
			$("#"+nombreSelect+" option").each(function(){
				if ($(this).attr('value') != '0') {
					// Registros diferentes a la default
					numRegistros ++;
					cmbDatoSelect = $(this).attr('value');
				}
			});
			if (numRegistros == 1) {
				// Si solo es un registro dejarlo seleccionado
				dataJsonReducciones[key2].tipoAfectacion = cmbDatoSelect;
				$("#"+nombreSelect+" option").each(function(){
					if ($(this).attr('value') != cmbDatoSelect) {
						// Eliminar renglon de seleccion
						$("#"+nombreSelect+" option[value='"+$(this).attr('value')+"']").remove();
					}
				});
			}
			$('#'+nombreSelect).multiselect('rebuild');
			// Validar si es 1 registro seleccion por default, Fin
		}
	}

	// Validar Ampliaciones
	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			var nombreSelect = "selectTipoDoc_"+panelAmpliaciones+"_"+dataJsonAmpliaciones[key2].accountcode+"";
			// var valorSelect = $("#"+nombreSelect).val();
			// console.log("valorSelect: "+valorSelect);
		    $("#"+nombreSelect+" option").each(function(){
				//console.log('opcion '+$(this).text()+' valor '+ $(this).attr('value'));
				if ($(this).attr('value') != '0') {
					var encontro = 0;
					for (var i = 0; i < arrayAmpliacion.length; i++) {
						if ($(this).attr('value') == arrayAmpliacion[i]) {
							encontro = 1;
							break;
						}
						//console.log("En el índice '" + i + "' hay este valor: " + arrayAmpliacion[i]);
					}
					if (encontro == 0) {
						// Eliminar datos
						$("#"+nombreSelect+" option[value='"+$(this).attr('value')+"']").remove();
						$('#'+nombreSelect).multiselect('rebuild');
					}
				}
			});

			// Validar si es 1 registro seleccion por default, Inicio
			var numRegistros = 0;
			var cmbDatoSelect = '';
			$("#"+nombreSelect+" option").each(function(){
				if ($(this).attr('value') != '0') {
					// Registros diferentes a la default
					numRegistros ++;
					cmbDatoSelect = $(this).attr('value');
				}
			});
			if (numRegistros == 1) {
				// Si solo es un registro dejarlo seleccionado
				dataJsonAmpliaciones[key2].tipoAfectacion = cmbDatoSelect;
				$("#"+nombreSelect+" option").each(function(){
					if ($(this).attr('value') != cmbDatoSelect) {
						// Eliminar renglon de seleccion
						$("#"+nombreSelect+" option[value='"+$(this).attr('value')+"']").remove();
					}
				});
			}
			$('#'+nombreSelect).multiselect('rebuild');
			// Validar si es 1 registro seleccion por default, Fin
		}
	}
}

function fnTAfectacionRed36() {
	// Se validar tipo de afectacion Clave de Adición “31” o Ampliación “33” (“Tipo de Operación” = Ampliación o Adición Líquida)
	var mensaje = "";
	var numAfec = 0;
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			if (dataJsonReducciones[key2].tipoAfectacion != "36") {
				numAfec = 1;
			}
		}
	}

	if (numAfec == 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Tipo de Operación (36 - Reducción Líquida)</p>';
	}else{
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Tipo de Operación (36 - Reducción Líquida)</p>';
	}

	return mensaje;
}

function fnTAfectacionAmp31o33() {
	// Se validar tipo de afectacion Clave de Adición “31” o Ampliación “33” (“Tipo de Operación” = Ampliación o Adición Líquida)
	var mensaje = "";
	var numAfec = 0;
	//console.log("datosAmpliaciones: "+JSON.stringify(datosAmpliaciones));
	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			if (dataJsonAmpliaciones[key2].tipoAfectacion != "31" && dataJsonAmpliaciones[key2].tipoAfectacion != "33") {
				numAfec = 1;
			}
		}
	}

	if (numAfec == 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Tipo de Operación (31 - Adición Líquida, 33 - Ampliación Líquida)</p>';
	}else{
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Tipo de Operación (31 - Adición Líquida, 33 - Ampliación Líquida)</p>';
	}

	return mensaje;
}

function fnSoloReduccciones() {
	var mensaje = "";

	$("#txtBuscarReducciones").prop("disabled", false);
	$("#txtBuscarApliaciones").prop("disabled", true);

	// Sin ampliaciones
	datosAmpliaciones = new Array();

	$('#'+tablaAmpliaciones+' tbody').empty();

	return mensaje;
}

function fnTotalMesCompesando() {
	var montoTotalMes = 0;
	var mensaje = "";
	for (var mes in dataJsonMeses ) {
		var nombreMes = dataJsonMeses[mes];
		var nombreMesSel = dataJsonMeses[mes]+"Sel";

		var totRed = 0;
		for (var key in datosReducciones ) {
			for (var key2 in datosReducciones[key]) {
				var dataJsonReducciones = datosReducciones[key];
				totRed += parseFloat(dataJsonReducciones[key2][nombreMesSel]);
			}
		}

		var totAmp = 0;
		for (var key in datosAmpliaciones ) {
			for (var key2 in datosAmpliaciones[key]) {
				var dataJsonAmpliaciones = datosAmpliaciones[key];
				totAmp += parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
			}
		}

		if (parseFloat(totRed) != parseFloat(totAmp)) {
			montoTotalMes = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En '+nombreMes+' existen diferencias en el Total en Reducciones y Ampliaciones (Compensado)</p>';
		}
	}

	if (montoTotalMes == 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Monto total por Mes (Compensado)</p>';
	}else{
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Monto total por Mes (Compensado)</p>';
	}

	return mensaje;
}

function fnTotalPorClave() {
	var totalPorClave = 0;
	var mensaje = "";
	//console.log("antes datosAmpliaciones: "+JSON.stringify(datosAmpliaciones));
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];

			if (datosAmpliaciones[key] == null) {
				// Validacion si faltan datos en el panel Ampliacion
				//mensaje += '<h3><p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar la Clave '+dataJsonReducciones[key2].accountcode+' en Ampliaciones</p></h3>';
				
				fnObtenerPresupuesto(dataJsonReducciones[key2].accountcode, tablaAmpliaciones, panelAmpliaciones, '', 'Nuevo');
				totalPorClave = 1;
				fnValidaciones();
				break;
			}

			var dataJsonAmpliaciones = datosAmpliaciones[key];

			var totalRed = 0;
			var totalAmp = 0;

			for (var mes in dataJsonMeses ) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                var totRed = dataJsonReducciones[key2][nombreMesSel];
                var totAmp = dataJsonAmpliaciones[key2][nombreMesSel];
                //console.log("mes: "+nombreMes+" - totRed: "+totRed+" - totAmp: "+totAmp);
                
                // No deshabilitar cajas para mostrar error visual
				// if (Number(totRed) > 0) {
				// 	totalAmpliaciones = parseFloat(totalAmpliaciones) - parseFloat(dataJsonAmpliaciones[key2][nombreMesSel]);
				// 	fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
				// 	dataJsonAmpliaciones[key2][nombreMesSel] = 0;
				// 	$("#"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones+"_"+nombreMes).val("0");
				// 	$("#"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones+"_"+nombreMes).prop("disabled", true);
				// }else{
				// 	$("#"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones+"_"+nombreMes).prop("disabled", false);
				// }
				
				totalRed += parseFloat(totRed);
				totalAmp += parseFloat(totAmp);
            }
            
            if (parseFloat(totalRed) != parseFloat(totalAmp)) {
            	// Existe diferencias en total de la clave
            	mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Diferencias en Reducción linea ';
            	mensaje += $("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelReducciones).html();
            	mensaje += ' y Ampliación linea '+$("#Renglon_"+dataJsonAmpliaciones[key2].accountcode+"_"+panelAmpliaciones).html();
            	mensaje += ' de la Clave '+dataJsonReducciones[key2].accountcode+'</p>';
            	totalPorClave = 1;
            }
		}
	}
	//console.log("despues datosAmpliaciones: "+JSON.stringify(datosAmpliaciones));
	// console.log("************************");

	if (totalPorClave == 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Total de Reducción y Ampliación por Clave Presupuestal</p>';
	}else{
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Total de Reducción y Ampliación por Clave Presupuestal</p>';
	}

	return mensaje;
}

function fnClaveCortaIgual() {
	var claveCortaConfig = 1;
	var mensaje = "";
	// UR-EDO-GF-F-SF-PG-AI-PP-PARTIDA-TG-FF-PPI (clave corta) 
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			//console.log("Clave: "+dataJsonReducciones[key2].accountcode+" - Corta: "+dataJsonReducciones[key2].claveCorta);
			for (var keyBusq in datosPresupuestosBusquedaReducciones ) {
				if (dataJsonReducciones[key2].claveCorta == "" || dataJsonReducciones[key2].claveCorta == null) {
					claveCortaConfig = 0;
					break;
				}
				if (dataJsonReducciones[key2].claveCorta == datosPresupuestosBusquedaReducciones[keyBusq].claveCorta) {
					//console.log("Reducciones: "+dataJsonReducciones[key2].claveCorta+" - Busqueda Igual: "+datosPresupuestosBusquedaReducciones[keyBusq].claveCorta);
					datosPresupuestosBusquedaReducciones.splice(keyBusq, 1);
				}
			}
		}
	}
	fnBusquedaReduccion(datosPresupuestosBusquedaReducciones);

	// UR-EDO-GF-F-SF-PG-AI-PP-PARTIDA-TG-FF-PPI (clave corta) 
	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			//console.log("Clave: "+dataJsonAmpliaciones[key2].accountcode+" - Corta: "+dataJsonAmpliaciones[key2].claveCorta);
			for (var keyBusq in datosPresupuestosBusquedaAmpliaciones ) {
				if (dataJsonAmpliaciones[key2].claveCorta == datosPresupuestosBusquedaAmpliaciones[keyBusq].claveCorta) {
					//console.log("Reducciones: "+dataJsonAmpliaciones[key2].claveCorta+" - Busqueda Igual: "+datosPresupuestosBusquedaAmpliaciones[keyBusq].claveCorta);
					datosPresupuestosBusquedaAmpliaciones.splice(keyBusq, 1);
				}
			}
		}
	}
	fnBusquedaAmpliacion(datosPresupuestosBusquedaAmpliaciones);

	if (claveCortaConfig == 1) {
		// mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Misma Clave Corta en Reducciones</p>';
		// mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Misma Clave Corta en Ampliaciones</p>';
	}else{
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Sin Configuración para Clave Corta</p>';
	}

	return mensaje;
}

function fnClaveCortaDiferente() {
	var claveCortaConfig = 1;
	var mensaje = "";
	// UR-EDO-GF-F-SF-PG-AI-PP-PARTIDA-TG-FF-PPI (clave corta) 
	//console.log("Reducciones antes "+datosPresupuestosBusquedaReducciones.length);
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			//console.log("Clave: "+dataJsonReducciones[key2].accountcode+" - Corta: "+dataJsonReducciones[key2].claveCorta);
			for (var keyBusq in datosPresupuestosBusquedaReducciones ) {
				if (dataJsonReducciones[key2].claveCorta == "" || dataJsonReducciones[key2].claveCorta == null) {
					claveCortaConfig = 0;
					break;
				}
				if (dataJsonReducciones[key2].claveCorta != datosPresupuestosBusquedaReducciones[keyBusq].claveCorta) {
					//console.log("Reducciones: "+dataJsonReducciones[key2].claveCorta+" - Busqueda Igual: "+datosPresupuestosBusquedaReducciones[keyBusq].claveCorta);
					//console.log("keyBusq: "+keyBusq);
					datosPresupuestosBusquedaReducciones.splice(keyBusq, 1);
				}
			}
		}
	}
	//console.log("Reducciones despues "+datosPresupuestosBusquedaReducciones.length);
	//console.log("datosReducciones: "+JSON.stringify(datosReducciones));
	//console.log("datosPresupuestosBusquedaReducciones: "+JSON.stringify(datosPresupuestosBusquedaReducciones));
	fnBusquedaReduccion(datosPresupuestosBusquedaReducciones);

	// UR-EDO-GF-F-SF-PG-AI-PP-PARTIDA-TG-FF-PPI (clave corta) 
	for (var key in datosAmpliaciones ) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJsonAmpliaciones = datosAmpliaciones[key];
			//console.log("Clave: "+dataJsonAmpliaciones[key2].accountcode+" - Corta: "+dataJsonAmpliaciones[key2].claveCorta);
			for (var keyBusq in datosPresupuestosBusquedaAmpliaciones ) {
				if (dataJsonAmpliaciones[key2].claveCorta != datosPresupuestosBusquedaAmpliaciones[keyBusq].claveCorta) {
					//console.log("Reducciones: "+dataJsonAmpliaciones[key2].claveCorta+" - Busqueda Igual: "+datosPresupuestosBusquedaAmpliaciones[keyBusq].claveCorta);
					datosPresupuestosBusquedaAmpliaciones.splice(keyBusq, 1);
				}
			}
		}
	}
	//console.log("datosAmpliaciones: "+JSON.stringify(datosAmpliaciones));
	//console.log("datosPresupuestosBusquedaAmpliaciones: "+JSON.stringify(datosPresupuestosBusquedaAmpliaciones));
	fnBusquedaAmpliacion(datosPresupuestosBusquedaAmpliaciones);

	if (claveCortaConfig == 1) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Diferente Clave Corta en Reducciones</p>';
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Diferente Clave Corta en Ampliaciones</p>';
	}else{
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-danger" aria-hidden="true"></i> Sin Configuración para Clave Corta</p>';
	}

	return mensaje;
}

function fnTotalGeneralIgual() {
	var mensaje = "";
	if (totalReducciones == totalAmpliaciones && totalReducciones > 0 && totalAmpliaciones > 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Total General</p>';
	}else{
		errorValidacion = 1;
		if (totalReducciones == 0 && totalAmpliaciones == 0) {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Total General, Ampliaciones y Reducciones se encuentra en 0</p>';
		}else{
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Total General</p>';
		}
	}

	return mensaje;
}

function fnMismaClaveRedAmp() {
	var mensaje = "";
	var mismaClave = 0;
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];

			for (var keyy in datosAmpliaciones ) {
				for (var key22 in datosAmpliaciones[keyy]) {
					var dataJsonAmpliaciones = datosAmpliaciones[keyy];
					//console.log("Ampliacion: "+JSON.stringify(dataJsonAmpliaciones[key22]));
					if (dataJsonReducciones[key2].accountcode == dataJsonAmpliaciones[key22].accountcode) {
						mismaClave = 1;
						mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> La clave '+dataJsonReducciones[key2].accountcode;
						mensaje += ' existe en el panel de Ampliaciones linea '+$("#Renglon_"+dataJsonReducciones[key2].accountcode+"_"+panelAmpliaciones).html()+'</p>';
					}
				}
			}
		}
	}

	if (mismaClave == 0) {
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Misma Clave Presupuestal en Reducciones y Ampliaciones</p>';
	}else{
		errorValidacion = 1;
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Misma Clave Presupuestal en Reducciones y Ampliaciones</p>';
	}

	return mensaje;
}

function fnCambioTipoAdecuacion(select) {
	//console.log("tipoAdecuacion: "+select.value);
	statusGuardar = 1;

	if (select.value != '0') {
		// Habilitar cajas
		$("#txtBuscarReducciones").prop("disabled", true);
		$("#txtBuscarApliaciones").prop("disabled", true);

		// Habilitar tipo de Operación
		fnCrearDatosSelect(dataJsonTipoDocReduccion, '.selectTipoAdecuacionReduccion', '');
	}else{
		$("#txtBuscarReducciones").prop("disabled", true);
		$("#txtBuscarApliaciones").prop("disabled", true);
	}

	// Recargar datos del Tipo de Solicitud
	fnTipoDeSolicitud(select.name, '.selectTipoSolicitud', 2);

	fnRecargarDatosPaneles();

	// Datos lista de busqueda
	// fnObtenerPresupuestoBusqueda();
	datosPresupuestosBusquedaReducciones = datosPresupuestosBusquedaFiltros;
	datosPresupuestosBusquedaAmpliaciones = datosPresupuestosBusquedaFiltros;

	// Validacion ConcR23
	fnSelectConcR23();

	//Hacer validaciones
	fnValidaciones("");

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

function fnCambioTipoSolicitud() {
	statusGuardar = 1;
	$("#txtBuscarReducciones").prop("disabled", true);
	$("#txtBuscarApliaciones").prop("disabled", true);

	fnRecargarDatosPaneles();

	// Datos lista de busqueda
	fnObtenerPresupuestoBusqueda();
	datosPresupuestosBusquedaReducciones = datosPresupuestosBusquedaFiltros;
	datosPresupuestosBusquedaAmpliaciones = datosPresupuestosBusquedaFiltros;

	// Validacion ConcR23
	fnSelectConcR23();	

	//Hacer validaciones
	fnValidaciones("");

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

function fnObtenerPresupuestoBusqueda(panel="", filtroCaja=0) {
	var legalidBus = "";
	var tagrefBus = "";
	var concR23 = "";
	var dataJson = new Array();
	var ramoBusqueda = 0;

	muestraCargandoGeneral();

	if (panel == panelReducciones) {
		$('#btnBuscarReduccion').attr("disabled", true);
		legalidBus = $('#selectRazonSocial').val();
		tagrefBus = $('#selectUnidadNegocio').val();
		for (var key in nombreElementosFiltroReducciones) {
			nombreElementosFiltroReducciones[key].valor = $("#"+nombreElementosFiltroReducciones[key].nombre).val();
		}
		dataJson = nombreElementosFiltroReducciones;
		ramoBusqueda = $("#selectRamoCr").val();
	}else if (panel == panelAmpliaciones) {
		$('#btnBuscarAmpliaciones').attr("disabled", true);
		if (panel == panelAmpliaciones && ($('#selectTipoDoc').val() == 2 || $('#selectTipoDoc').val() == 4
			|| ($('#selectTipoDoc').val() == 6 && $('#selectTipoSolicitud').val() == 1)
			|| ($('#selectTipoDoc').val() == 7 && $('#selectTipoSolicitud').val() == 1)
			|| ($('#selectTipoDoc').val() == 9 && $('#selectTipoSolicitud').val() == 1)
			)) {
			// Validacion tipo 2
			// Validacion tipo 4
			// Validacion tipo EC
			// Validacion tipo IC
			// Validacion tipo MC
			// muestraMensaje('Agregar Clave en Reducción para la Clase seleccionada', 3, 'divMensajeOperacion', 5000);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Clave en Reducción para la Clase seleccionada</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
		}else{
			legalidBus = $('#selectRazonSocialRec').val();
			tagrefBus = $('#selectUnidadNegocioRec').val();
			for (var key in nombreElementosFiltroAmpliaciones) {
				nombreElementosFiltroAmpliaciones[key].valor = $("#"+nombreElementosFiltroAmpliaciones[key].nombre).val();
			}
			dataJson = nombreElementosFiltroAmpliaciones;
		}
		ramoBusqueda = $("#selectRamoRec").val();
	}else{
		legalidBus = $('#selectRazonSocial').val();
		tagrefBus = $('#selectUnidadNegocio').val();
	}

	if ($('#selectTipoDoc').val() == '6' && $('#selectTipoSolicitud').val() == '5' && $('#selectConcR23').val() == '16') {
		// Validacion tipo EI
		concR23 = $('#selectConcR23').val();
	}

	// No buscar con filtros
	legalidBus = "";
	tagrefBus = "";

	dataObj = { 
	        option: 'obtenerPresupuestosBusqueda',
	        legalid: legalidBus,
	        tagref: tagrefBus,
	        type: type,
			transno: transno,
	        tipoAdecuacion: $('#selectTipoDoc').val(),
	        tipoSolicitud: $('#selectTipoSolicitud').val(),
	        concR23: concR23,
	        filtrosClave: dataJson,
	        ramoBusqueda: ramoBusqueda
	      };
    $.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/GLBudgetsByTagV2_modelo.php",
		data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
            //console.log("datosPresupuestosBusqueda: "+JSON.stringify(datosPresupuestosBusqueda));
            datosPresupuestosBusquedaFiltros = data.contenido.datos;
            datosPresupuestosBusquedaReducciones = data.contenido.datos;
            datosPresupuestosBusquedaAmpliaciones = data.contenido.datos;
            if (panel == panelReducciones) {
            	$('#btnBuscarReduccion').attr("disabled", false);
            	// console.log("Reducciones");
				// datosPresupuestosBusquedaReducciones = data.contenido.datos;
				// if (datosPresupuestosBusquedaReducciones.length > 0) {
				// 	fnBusquedaReduccion(datosPresupuestosBusquedaReducciones);
				// }
				if (filtroCaja == 0) {
					fnBusquedaFiltrosModal(data.contenido.datos, panelReducciones);
				} else {
					fnBusquedaReduccion(data.contenido.datos);
				}
			}else if (panel == panelAmpliaciones) {
				$('#btnBuscarAmpliaciones').attr("disabled", false);
				// console.log("Ampliaciones");
				// datosPresupuestosBusquedaAmpliaciones = data.contenido.datos;
				// if (datosPresupuestosBusquedaAmpliaciones.length > 0) {
				// 	fnBusquedaAmpliacion(datosPresupuestosBusquedaAmpliaciones);
				// }x
				if (filtroCaja == 0) {
					fnBusquedaFiltrosModal(data.contenido.datos, panelAmpliaciones);
				} else {
					fnBusquedaAmpliacion(data.contenido.datos);
				}
			}else{
				// console.log("Todos");
				datosPresupuestosBusqueda = data.contenido.datos;
				if (datosPresupuestosBusqueda.length > 0) {
					fnBusquedaReduccion(datosPresupuestosBusqueda);
	    			fnBusquedaAmpliacion(datosPresupuestosBusqueda);
	    		}
			}
			ocultaCargandoGeneral();
        }else{
        	$('#btnBuscarReduccion').attr("disabled", false);
			$('#btnBuscarAmpliaciones').attr("disabled", false);
			ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log( result );
        ocultaCargandoGeneral();
    });
}

function fnSeleccionarTodoBusqueda(checkbox) {
	if( $('#'+checkbox.name).prop('checked') ) {
		for(var key in datosPresupuestosBusquedaFiltros) {
			//$('#check_filtro_'+datosPresupuestosBusquedaFiltros[key].accountcode).prop('checked');
			$('#check_filtro_'+datosPresupuestosBusquedaFiltros[key].accountcode).prop('checked',true);
		}
	}else{
		for(var key in datosPresupuestosBusquedaFiltros) {
			//$('#check_filtro_'+datosPresupuestosBusquedaFiltros[key].accountcode).prop('checked');
			$('#check_filtro_'+datosPresupuestosBusquedaFiltros[key].accountcode).prop('checked',false);
		}
	}
}

function fnBusquedaFiltrosModal(jsonData, panel) {
	var titulo = "";
	if (panel == panelReducciones) {
		titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Agregar a Reducciones</p></h3>';
	}else if (panel == panelAmpliaciones) {
		titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Agregar a Ampliaciones</p></h3>';
	}
	var mensaje = '<p><input type="checkbox" id="check_filtro_todos" name="check_filtro_todos" onchange="fnSeleccionarTodoBusqueda(this)" /> Seleccionar Todos</p>';
	for (var key in jsonData) {
		// '<input type="checkbox" id="checkbox_'.$myrow ['transno'].'" name="checkbox_'.$myrow ['transno'].'" title="Seleccionar" value="'.$myrow ['statusid'].'" onchange="fnValidarProcesoCambiarEstatus()" />';
		mensaje += '<p><input type="checkbox" id="check_filtro_'+jsonData[key].accountcode+'" name="check_filtro_'+jsonData[key].accountcode+'" /> '+jsonData[key].accountcode+'</p>';
	}
	muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnAgregarClavesModal('"+panel+"')", "", 1);
}

function fnAgregarClavesModal(panel) {
	//console.log("fnAgregarClavesModal - panel: "+panel);
	var datosAgregar = new Array();
	// Obtener datos que se van a agregar
	for(var key in datosPresupuestosBusquedaFiltros) {
		if( $('#check_filtro_'+datosPresupuestosBusquedaFiltros[key].accountcode).prop('checked') ) {
			// Guardar datos seleccionados
			var obj = new Object();
			obj.clave = datosPresupuestosBusquedaFiltros[key].accountcode;
			datosAgregar.push(obj);
		}
	}
	// console.log("datos modal: "+datosAgregar.length);
	for(var key in datosAgregar) {
		// Recorrer datos seleccionados y agregar al panel
		if (panel == panelReducciones) {
			fnObtenerPresupuesto(datosAgregar[key].clave, tablaReducciones, panelReducciones, '', 'Nuevo');
		}else if (panel == panelAmpliaciones) {
			fnObtenerPresupuesto(datosAgregar[key].clave, tablaAmpliaciones, panelAmpliaciones, '', 'Nuevo');
		}
	}
}

function fnBusquedaReduccion(jsonData) {
	// console.log("busqueda Reducciones");
	// console.log("jsonData: "+JSON.stringify(jsonData));
	$( "#txtBuscarReducciones").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.accountcode + "");
            //$( "#txtBuscarReducciones" ).val( ui.item.accountcode );
            $( "#txtBuscarReducciones" ).val( "" );
            fnObtenerPresupuesto(ui.item.accountcode, tablaReducciones, panelReducciones, '', 'Nuevo');

            //datosPresupuestosBusqueda = { budgetid: ui.item.budgetid, accountcode: ui.item.accountcode, valorLista: ui.item.valorLista};

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

		return $( "<li>" )
		.append( "<a>" + item.valorLista + "</a>" )
		.appendTo( ul );

    };
}

function fnBusquedaAmpliacion(jsonData) {
	// console.log("busqueda Ampliaciones");
	// console.log("jsonData: "+JSON.stringify(jsonData));
	$( "#txtBuscarApliaciones").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.accountcode + "");
            //$( "#txtBuscarApliaciones" ).val( ui.item.accountcode );
            $( "#txtBuscarApliaciones" ).val( "" );
            fnObtenerPresupuesto(ui.item.accountcode, tablaAmpliaciones, panelAmpliaciones, '', 'Nuevo');

            //datosPresupuestosBusqueda = { budgetid: ui.item.budgetid, accountcode: ui.item.accountcode, valorLista: ui.item.valorLista};

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

		return $( "<li>" )
		.append( "<a>" + item.valorLista + "</a>" )
		.appendTo( ul );

    };
}