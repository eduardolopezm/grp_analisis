/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var dataJsonMeses = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

// Nombre de la vista para la tabla de Reduccion y Ampliacion
var tablaReducciones = "tablaReducciones";

// Datos de la busqueda en Reduccion y Ampliacion
var datosPresupuestosBusqueda = {};
var idClavePresupuestoReducciones = 0;
var datosReducciones = new Array();
var datosEliminarRedAmp = new Array();

// Identificador para el panel, cambiar tambien en la vista fnObtenerPresupuestoBusqueda()
var panelReducciones = 1; 
// Totales de Reduccion y Ampliacion
var totalReducciones = 0;
var decimales = 2;

// Se declara enla vista
//var type = 259;
//var transno = <?php echo $_SESSION['noCaptura']; ?>;

// Filtro Generales
var legalid = "";
var tagref = "";

var errorValidacion = 0;
// Estatus para guardar
var statusGuardar = 1;

// Numero de Linea en Reduccion y Ampliacion
var numLineaReducciones = 1;

// Titulo del mensaje de validaciones
var tituloModalValidaciones = "Validaciones";

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	if (document.querySelector(".selectTipoOpeCompromiso")) {
      // Muestra los tipos de operación para un compromiso
      dataObj = {
            option: 'mostrarTipoCompromiso'
        };
      fnSelectGeneralDatosAjax('.selectTipoOpeCompromiso', dataObj, 'modelo/compromisoPanelModelo.php', 0);
    }

    var date = new Date();
	date.setDate(date.getDate() - 1);

    // Fecha inicio
	$('#fechaHoyAdelante').datetimepicker({
        format: 'DD-MM-YYYY',
        minDate: date.setDate(date.getDate())
        // maxDate: ("12/31/"+yearActualAdecuacion)
    }).on('dp.change', function (e) { fnCambioFecha(); });;

	// Fecha fin
    $('#fechaHoyAdelante2').datetimepicker({
        format: 'DD-MM-YYYY',
        minDate: date.setDate(date.getDate())
        // maxDate: ("12/31/"+yearActualAdecuacion)
    }).on('dp.change', function (e) { fnCambioFecha(); });;

    // Fecha firma
    $('#fechaHoyAdelante3').datetimepicker({
        format: 'DD-MM-YYYY',
        minDate: date.setDate(date.getDate())
    });

    // Poner 0 con formato antes de cargar captura
    fnMostrarTotalAmpRed('txtTotalReducciones', 0);

	//Obtener Datos de un No. Captura
	fnObtenerInfoNoCaptura();

	// Deshabilitar pagina
	fnDeshabilitaPagSuficiencia();

	// Datos lista de busqueda
	if ($('#selectUnidadEjecutora').val() != '-1') {
		// Si solo tiene una ue obtener información
		fnObtenerPresupuestoBusqueda();
	}

	if (autorizarGeneral != 1) {
		fnListaInformacionGeneral("", "#txtProveedor", "proveedor");

		// Deshabilitar menses anteriorfes al actual
		fnDeshabilitarMesesAntes();

		// Cargar fechas
		fnCambioFecha();
	}

	$("#btnBuscarCompromiso").click(function() {
		// Buscar información del compromiso
		fnCambioIdCompromiso($("#txtIdCompromiso").val());
	});
});

/**
 * Función cuando se cambia la fecha de inicio o fin
 * @return {[type]} [description]
 */
function fnCambioFecha() {
	// Cambio fechas deshabilitar menses no contemplados
	var res1 = $("#txtFechaInicio").val().split("-");
	var res2 = $("#txtFechaFinal").val().split("-");

	if (res1.length == 3 && res2.length == 3) {
		// Realizar validaciones de campos
		if (Number(res1[2]) > Number(yearActualAdecuacion)) {
			// Si es año mayor, poner mes diciembre
			res1[1] = 12;
		}
		if (Number(res2[2]) > Number(yearActualAdecuacion)) {
			// Si es año mayor, poner mes diciembre
			res2[1] = 12;
		}
		fnDeshabilitarMesesAntes(res1[1], res2[1]);
	} else {
		// Realizar en tiempo
		// setTimeout(function (){
		// 	fnCambioFecha();
		// }, 2000);
	}
}

/**
 * Función para deshabilitar los meses anteriores al actual
 * @return {[type]} [description]
 */
function fnDeshabilitarMesesAntes(mesInicio = 0, mesFin = 0) {
	// Deshabilita meses despues del actual en Reducciones
	var mensaje = "";
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			var numMes = 1;
			for (var mes in dataJsonMeses ) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                if (autorizarGeneral == 1) {
                	// Si se va autorizar o esta autorizado
                	$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
                } else if (
                	(Number(mesActualAdecuacion) > Number(numMes))
                	||
                	((Number(numMes) < Number(mesInicio)) && (Number(mesInicio) != Number(0) && Number(mesFin) != Number(0)))
                	|| 
                	((Number(numMes) > Number(mesFin)) && (Number(mesInicio) != Number(0) && Number(mesFin) != Number(0)))
                	) {
                	totalReducciones = parseFloat(totalReducciones) - parseFloat(dataJsonReducciones[key2][nombreMesSel]);
                	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
					dataJsonReducciones[key2][nombreMesSel] = 0;
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).val("0");
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
				} else {
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", false);
				}
				numMes ++;
            }
		}
	}

	return mensaje;
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
}

/**
 * Función para comprar el tipo de compromiso
 * @param  {[type]} select Valor seleccionado
 * @return {[type]}        [description]
 */
function fnCambioTipoOperacion(select) {
	// Si cambia tipo de operacion
	if (select == 'O') {
		// Si es original
		$("#txtIdCompromiso").val('');
		$("#txtIdCompromiso").prop("disabled", true);

		$("#btnBuscarCompromiso").prop("disabled", true);

		$("#txtProveedor").prop("disabled", false);
		$("#txtContratoConvenio").prop("disabled", false);

		$("#txtFechaInicio").prop("disabled", false);
		$("#txtFechaFinal").prop("disabled", false);
		$("#txtFechaFirma").prop("disabled", false);
	} else {
		// Si es incremento o decremento
		$("#txtIdCompromiso").prop("disabled", false);

		$("#btnBuscarCompromiso").prop("disabled", false);

		// $("#txtProveedor").val('');
		$("#txtProveedor").prop("disabled", true);
		// $("#txtContratoConvenio").val('');
		$("#txtContratoConvenio").prop("disabled", true);
		$("#txtFechaInicio").prop("disabled", true);
		$("#txtFechaFinal").prop("disabled", true);
		$("#txtFechaFirma").prop("disabled", true);
	}

	// Recargar información con tipo anterior, ya que lo muestra primero con el original
	fnRecargarDatosPaneles();
}

function fnAlmacenarCaptura(estatus, msjvalidaciones="") {
	// console.log("fnPresupuestoCaptura");
	// console.log("datosReducciones: "+JSON.stringify(datosReducciones));

	muestraCargandoGeneral();
	var datosCapturaReducciones = new Array();
	var datosCapturaAmpliaciones = new Array();
	var datosCapturaCompras = new Array();
	var mesesCompra = new Array("EneroCompra","FebreroCompra","MarzoCompra","AbrilCompra","MayoCompra","JunioCompra","JulioCompra",
	                            "AgostoCompra","SeptiembreCompra","OctubreCompra","NoviembreCompra","DiciembreCompra");

	//Generar datos Reducciones
	var claveMensaje = '';
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJson2 = datosReducciones[key];
			var obj = new Object();
			obj.accountcode = dataJson2[key2].accountcode;
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
			
            for(var mc in mesesCompra){
                if(dataJson2[key2][mesesCompra[mc]] != 0){
                    obj[mesesCompra[mc]] = dataJson2[key2][mesesCompra[mc]];
                }
            }
            
            datosCapturaReducciones.push(obj);
                        
			if (Number(dataJson2[key2].EneroSel) == 0 && Number(dataJson2[key2].FebreroSel) == 0
				&& Number(dataJson2[key2].MarzoSel) == 0 && Number(dataJson2[key2].AbrilSel) == 0
				&& Number(dataJson2[key2].MayoSel) == 0 && Number(dataJson2[key2].JunioSel) == 0
				&& Number(dataJson2[key2].JulioSel) == 0 && Number(dataJson2[key2].AgostoSel) == 0
				&& Number(dataJson2[key2].SeptiembreSel) == 0 && Number(dataJson2[key2].OctubreSel) == 0
				&& Number(dataJson2[key2].NoviembreSel) == 0 && Number(dataJson2[key2].DiciembreSel) == 0) {
				claveMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Línea '+$("#Renglon_"+dataJson2[key2].accountcode+"_"+panelReducciones).html()+' no tiene cantidad seleccionada</p>';
			}
		}
	}

	// console.log("datosCapturaReducciones: "+JSON.stringify(datosCapturaReducciones));

	legalid = $('#selectRazonSocial').val();
	tagref = $('#selectUnidadNegocio').val();
	var ue = $('#selectUnidadEjecutora').val();

	if (tagref == '-1') {
		// Si no selecciono Unidad Responsable
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UR para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (ue == '-1') {
		// Si no selecciono Unidad Ejecutro
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UE para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if ($('#txtContratoConvenio').val().trim() == '' || $('#txtContratoConvenio').val().trim() == null) {
		// Si esta vacío el contrato o convenio
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Contrato/Convenio para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if ($('#txtJustificacion').val().trim() == '' || $('#txtJustificacion').val().trim() == null) {
		// Si esta vacío la justificación
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Justificación para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (claveMensaje != '') {
		// Si existe una clave con cantidad en 0
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, claveMensaje);
		return true;
	}

	if (!fnObtenerDatosProveedor()) {
		// Si no existe proveedor
		ocultaCargandoGeneral();
		return true;
	}

	if ($('#txtRepresentante').val().trim() == '' || $('#txtRepresentante').val().trim() == null) {
		// Si esta vacío la justificación
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar Representante/Legal para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if ($('#selectTipo').val() != 'O') {
		// Si es incremento o decremento, validar que existe el id del compromiso
		var respuesta = fnValidarCompromisoExiste(''+$("#txtIdCompromiso").val());
		if (!respuesta[0]) {
			// Si no existe proveedor
			ocultaCargandoGeneral();
			return true;
		}
	}

	if (datosCapturaReducciones.length == 0) {
		// Agregar claves presupuestales
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar clave presupuestal para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (datosEliminarRedAmp.length > 0 && transno != 0) {
		// Validar si existen registros para eliminar antes del guardado
		// console.log("entra if de eliminar datosEliminarRedAmp");
		for (var key in datosEliminarRedAmp) {
			// datosEliminarRedAmp[key].num
			var res = fnQuitarRenglonDatosArray(datosEliminarRedAmp[key].type, datosEliminarRedAmp[key].transno, datosEliminarRedAmp[key].clave);
		}
	}

	var txtProveedor = $("#txtProveedor").val();
	var separacion = txtProveedor.split(' ');
	txtProveedor = separacion[0];

	//Opcion para operacion
	dataObj = { 
	        option: 'guardarOperacion',
	        datosCapturaReducciones: datosCapturaReducciones, //JSON.parse(JSON.stringify(datosCapturaReducciones)), 
			datosCapturaAmpliaciones: datosCapturaAmpliaciones,
			type: type,
			transno: transno,
			legalid: legalid,
			tagref: tagref,
			estatus: statusGuardar,
			fechaCaptura: $('#txtFechaCaptura').val(),
			fechaInicio: $('#txtFechaInicio').val(),
			fechaFinal: $('#txtFechaFinal').val(),
			fechaFirma: $('#txtFechaFirma').val(),
			justificacion: $('#txtJustificacion').val(),
			ue: ue,
			selectTipo: $('#selectTipo').val(),
			txtIdCompromiso: $('#txtIdCompromiso').val(),
			txtProveedor: txtProveedor,
			txtContratoConvenio: $('#txtContratoConvenio').val()
	      };
	//Obtener datos de las bahias
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/compromisoModelo.php",
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

				$('#txtIdCompromiso').val("");
				if (data.contenido.datos.txtIdCompromiso != "" || data.contenido.datos.txtIdCompromiso != null) {
					$('#txtIdCompromiso').val(""+data.contenido.datos.txtIdCompromiso);
				}

				if ($('#selectTipo').val() == 'O') {
					// Si es original
					$('#selectTipo').multiselect('disable');
				}

				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, data.Mensaje);
	    	}
	    }else{
	    	//Obtener Datos de un No. Captura
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

/**
 * Funcion para cuando cambia un compromiso
 * @param  {[type]} idcompromiso Id de compromiso
 * @return {[type]}              [description]
 */
function fnCambioIdCompromiso(idcompromiso) {
	// Cambio el id del compromiso
	var respuesta = fnValidarCompromisoExiste(idcompromiso);
	if (respuesta[0]) {
		// Existe compromiso
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Desea Cargar la información del Compromiso '+idcompromiso+'?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCargarCompromiso('"+respuesta[1]+"', '"+respuesta[2]+"')");
	}
}

/**
 * Funcion para cargar la información del compromiso original
 * @param  {[type]} typeOriginal    Tipo de documento
 * @param  {[type]} transnoOriginal Número de la operación
 * @return {[type]}                 [description]
 */
function fnCargarCompromiso(typeOriginal, transnoOriginal) {
	// Cargar el informacion del compromiso
	// Variable datos anteriores
	var tipoAnt = $("#selectTipo").val();
	var typeAnt = type;
	var transnoAnt = transno;

	type = typeOriginal;
	transno = transnoOriginal;

	numLineaReducciones = 1;

	var compromiso = $("#txtIdCompromiso").val();
	if (tipoAnt == 'O' || tipoAnt == 'I') {
		// Si es original o incremento
		compromiso = '';
	}

	//Obtener Datos de un No. Captura
	fnObtenerInfoNoCaptura(compromiso);

	// Poner en 0 las claves presupuestales cargadas
	fnCeroClavesPresupuestales(panelReducciones);

	// Deshabilitar menses anteriores al actual
	fnDeshabilitarMesesAntes();

	fnCambioFecha();

	// Regresar datos anteriores
	type = typeAnt;
	transno = transnoAnt;

	$('#txtNoCaptura').empty();
	$('#txtNoCaptura').append(transno);

	$('#selectTipo').val(''+tipoAnt);
	$('#selectTipo').multiselect('rebuild');

	$("#txtProveedor").prop("disabled", true);
	$("#txtContratoConvenio").prop("disabled", true);

	$("#txtIdCompromiso").prop("disabled", true);
	
	$("#txtFechaInicio").prop("disabled", true);
	$("#txtFechaFinal").prop("disabled", true);
	$("#txtFechaFirma").prop("disabled", true);

	// Justificación vacía
	$('#txtJustificacion').val('');

	// Deshabilitar operación
	$('#selectTipo').multiselect('disable');

	// Recargar información con tipo anterior, ya que lo muestra primero con el original
	fnRecargarDatosPaneles();
}

/**
 * Función para poner las claves presupuestales en cero
 * @return {[type]} [description]
 */
function fnCeroClavesPresupuestales(panel = 1) {
	// Poner en cero las claves presupuestales
	var dataJson = new Array();
	
	dataJson = datosReducciones;
	totalReducciones = 0;
	
	//console.log("dataJson antes: "+JSON.stringify(dataJson));
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			for (var mes in dataJsonMeses ) {
				// Nombres de los mes
                var nombreMes = dataJsonMeses[mes];
                $('#'+dataJson2[key2].accountcode+"_"+panel+"_"+nombreMes).val("0");
                dataJson2[key2][nombreMes+"Sel"] = 0;
            }

			var total = 0;
			for (var mes in dataJsonMeses ) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				total = parseFloat(total) + parseFloat(dataJson2[key2][nombreMes+"Sel"]);
			}

			totalReducciones += parseFloat(total);
		}
	}
	//console.log("dataJson despues: "+JSON.stringify(dataJson));
	datosReducciones = dataJson;
	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

/**
 * Funcion para validar si existe el compromiso tecleado
 * @param  {[type]} idcompromiso Id de compromiso
 * @return {[type]}              [description]
 */
function fnValidarCompromisoExiste(idcompromiso) {
	// funcion para validar si existe el compromiso tecleado
	var respuesta = new Array();

	dataObj = { 
        option: 'existeCompromiso',
        idcompromiso: idcompromiso
      };
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/compromisoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	respuesta[0] = true;
	    	respuesta[1] = data.contenido.datos.type;
	    	respuesta[2] = data.contenido.datos.transno;
	    }else{
	    	// Mensaje de error
	    	respuesta[0] = false;
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});

	return respuesta;
}

/**
 * Función para validar y obtener la información del proveedor
 * @return {[type]} [description]
 */
function fnObtenerDatosProveedor() {
	// Obtener los datos del proveedor
	var respuesta = true;
	var proveedor = $("#txtProveedor").val();
	var separacion = proveedor.split(' ');
	proveedor = separacion[0];

	if (proveedor.trim() == '') {
		// Que no sea vacío
		$('#txtRfc').val('');
	    $('#txtRepresentante').val('');
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El Proveedor/Beneficiario se encuentra vacío</p>');
		return false;
	}

	dataObj = { 
        option: 'infoProveedor',
        txtProveedor: proveedor
      };
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/compromisoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	// Agregar datos del proveedor
	    	$('#txtRfc').val(''+data.contenido.datos.rfc);
	    	$('#txtRepresentante').val(''+data.contenido.datos.representante);
	    	$('#txtProveedor').val(''+data.contenido.datos.nombre);
	    	respuesta = true;
	    }else{
	    	// Mensaje de error
	    	respuesta = false;
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});

	return respuesta;
}

/**
 * Función para mostrar información de las claves presupuestales
 * @param  {[type]} dataJson Json con informacón
 * @param  {[type]} idTabla  id de la tabla a mostrar información
 * @param  {[type]} panel    Panel a mostrar la información
 * @return {[type]}          [description]
 */
function fnMostrarPresupuesto(dataJson, idTabla, panel) {
	var encabezado = '';
	var contenido = '';
	var enca = 1;
	var style = 'style="text-align:center;"';
	var styleMeses = 'style="text-align:center;"';
	var nombreSelect = "";
	var tipoAfectacion = "";
	var clavePresupuesto = "";

	// numLineaReducciones
	for (var key in dataJson) {

		tipoAfectacion = dataJson[key].tipoAfectacion;
		clavePresupuesto = dataJson[key].accountcode;

		var total = 0;
		//var totalCompra = 0;
		var numMes = 1;
		for (var mes in dataJsonMeses ) {
			// Nombres de los mes
			var nombreMes = dataJsonMeses[mes];
			if (Number(mesActualAdecuacion) <= Number(numMes)) {
            	total = parseFloat(total) + parseFloat(dataJson[key][nombreMes+"Sel"]);
            }
            numMes ++;
		}

		if (idClavePresupuestoReducciones != dataJson[key].idClavePresupuesto) {
			idClavePresupuestoReducciones = dataJson[key].idClavePresupuesto;
			enca = 0;
		}
		
		totalReducciones += parseFloat(total);

		if (enca == 0) {
			encabezado += '<tr class="header-verde"><td></td><td></td>';
		}

		if (autorizarGeneral == 1) {
			contenido += '<td></td>';
		}else{
			contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\')"></button></td>';
		}

		var deshabilitarElemento = '';
		if (autorizarGeneral == 1) {
			// Se va autorizar y deshabilitar pagina
			deshabilitarElemento = ' disabled="true" ';
		}
		
		//Cargar datos presupuesto
		for (var key2 in dataJson[key].datosClave) {
			if (enca == 0) {
				encabezado += '<td '+style+'>'+dataJson[key].datosClave[key2].nombre+'</td>';
			}
			contenido += '<td '+style+'>'+dataJson[key].datosClave[key2].valor+'</td>';
		}

		if (enca == 0) {
			// Columna para total por clave, encabezado
			encabezado += '<td '+styleMeses+'>Total</td>';
		}

		// Columna para total por clave, información
		var nombreDivTotal = 'divTotal_'+panel+'_'+dataJson[key].accountcode;
		contenido += '<td '+style+' id="'+nombreDivTotal+'">$ '+formatoComas( redondeaDecimal( 0 ) )+'</td>';

		var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_"; // No cambiar estructura de nombre o cambiar tambien en fnGuardarSeleccionado()
		var numMes = 1;
		for (var mes in dataJsonMeses ) {
			// Informacion meses para seleccion
			var nombreMes = dataJsonMeses[mes];
			var nombreMesSel = dataJsonMeses[mes]+"Sel";
			var nombreMesCompra = dataJsonMeses[mes]+"Compra";
			var cantidadMes = parseFloat(dataJson[key][nombreMes]);
			var informacionCancelar = "";
			var styleOcultarMes = 'style="text-align:center;"';
			var styleInputText = ' style="width: 120px;" ';

			if (enca == 0) {
				// Nombres de los mes para el encabezado
				encabezado += '<td '+styleOcultarMes+'>'+nombreMes+'</td>';
			}
            
            contenido += '<td align="center" '+styleOcultarMes+'>';
            if (Number(mesActualAdecuacion) > Number(numMes)) {
            	// Si es menor al actual poner 0 en el disponible
            	contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat( 0 ) ) ) +'<br>';
            	dataJson[key][nombreMesSel] = 0;
            } else if (Number(mesActualAdecuacion) == Number(numMes)
            	&& ($("#selectTipo").val() == 'O' || $("#selectTipo").val() == 'I')) {
            	// Si es el mes actual poner el acomulado
            	// Acomulado
            	contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreMes+'Acomulado']) ) ) +'<br>';
            } else if (Number(mesActualAdecuacion) <= Number(numMes)
            	&& ($("#selectTipo").val() == 'D')) {
            	// Si es el mes actual o superior para el decremento tomar en cuenta el compromiso
            	// Compromiso
            	contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreMes+'Compromiso']) ) ) +'<br>';
            } else {
            	// Disponible
            	contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreMes]) ) ) +'<br>';
            }

			// contenido += '<input '+deshabilitarElemento+' type="text"  min="0" class="form-control" name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" style="width: 80px;" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" /></td>';
			contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')"></component-decimales>';    
			
			contenido += '</td>';

			numMes ++;
		}

		if (enca == 0) {
			encabezado += '</tr>';
		}

		contenido = '<td id="Renglon_'+dataJson[key].accountcode+'_'+panel+'" name="Renglon_'+dataJson[key].accountcode+'_'+panel+'">'+numLineaReducciones+'</td>' + contenido;
		numLineaReducciones = parseFloat(numLineaReducciones) + 1;

		contenido = encabezado + '<tr id="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" name="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" >' + contenido + '</tr>';

		enca = 1;
	}
	
    fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
        
	$('#'+idTabla+' tbody').append(contenido);

	fnEjecutarVueGeneral('RenglonTR_'+clavePresupuesto+'_'+panel);
}

/**
 * Función para obtener información de la clave presupuestal cuando se le da enter a la caja de texto
 * @param  {[type]} evento  Evento de la caja
 * @param  {[type]} idTabla id de la tabla a mostrar la información
 * @param  {[type]} panel   Panel donde se mostrara la información
 * @return {[type]}         [description]
 */
function fnObtenerPresupuestoEnter(evento, idTabla, panel) {
	if (evento.keyCode == 13) {
        //console.log("valor: "+evento.target.value+" - idTabla: "+idTabla+ " - panel: "+panel);
        var datos = evento.target.value.split(",");
        if (datos[0] != "") {
        	//console.log("datosBsucar: "+JSON.stringify(datosPresupuestosBusqueda));
        	for (var key in datosPresupuestosBusqueda) {
				if (datosPresupuestosBusqueda[key].valorLista == datos[0]) {
					$('#txtBuscarReducciones').val("");
					fnObtenerPresupuesto(datosPresupuestosBusqueda[key].accountcode, idTabla, panel, datos[1], 'Nuevo');
					break;
				}
			}
        }
        return false;
    }
}

/**
 * Función si cambia la dependencia solo cargue las UR de esa dependencia
 * @param  {[type]} nomRazonSocial   id del select de dependencia
 * @param  {[type]} nomUnidadNegocio id del select del ur
 * @return {[type]}                  [description]
 */
function fnCambioRazonSocial(nomRazonSocial, nomUnidadNegocio) {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	legalid = $("#"+nomRazonSocial).val();
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };

	fnSelectGeneralDatosAjax('#'+nomUnidadNegocio, dataObj, 'modelo/compromisoModelo.php');
}

/**
 * Función para cagrar información almacenada de la captura a visualizar
 * @return {[type]} [description]
 */
function fnObtenerInfoNoCaptura(idcompromiso = '') {
	dataObj = { 
	        option: 'cargarInfoNoCaptura',
			type: type,
			transno: transno,
			datosClave: '1',
			datosClaveAdecuacion: '1',
			idcompromiso: idcompromiso
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/compromisoModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	dataJson=data.contenido.datos;
                //console.log("Datos: "+JSON.stringify(dataJson));
	    	
	    	if (dataJson != null) {
	    		$('#'+tablaReducciones+' tbody').empty();
		    	if (data.contenido.transno != 0) {
		    		$('#txtNoCaptura').empty();
					$('#txtNoCaptura').append(data.contenido.transno);
					statusGuardar = data.contenido.estatus;
					
					$('#txtFechaCaptura').val("");
					if (data.contenido.fechaCaptura != "" || data.contenido.fechaCaptura != null) {
						$('#txtFechaCaptura').val(""+data.contenido.fechaCaptura);
					}

					$('#txtFechaInicio').val("");
					if (data.contenido.fechaInicio != "" || data.contenido.fechaInicio != null) {
						$('#txtFechaInicio').val(""+data.contenido.fechaInicio);
					}

					$('#txtFechaFinal').val("");
					console.log("fechaFinal: "+data.contenido.fechaFinal);
					if (data.contenido.fechaFinal != "" || data.contenido.fechaFinal != null) {
						$('#txtFechaFinal').val(""+data.contenido.fechaFinal);
					}

					$('#txtFechaFirma').val("");
					if (data.contenido.fechaFirma != "" || data.contenido.fechaFirma != null) {
						$('#txtFechaFirma').val(""+data.contenido.fechaFirma);
					}

					$('#txtIdCompromiso').val("");
					if (data.contenido.txtIdCompromiso != "" || data.contenido.txtIdCompromiso != null) {
						$('#txtIdCompromiso').val(""+data.contenido.txtIdCompromiso);
					}

					$('#txtContratoConvenio').val("");
					if (data.contenido.txtContratoConvenio != "" || data.contenido.txtContratoConvenio != null) {
						$('#txtContratoConvenio').val(""+data.contenido.txtContratoConvenio);
					}

					$('#txtProveedor').val("");
					if (data.contenido.txtProveedor != "" || data.contenido.txtProveedor != null) {
						$('#txtProveedor').val(""+data.contenido.txtProveedor);

						fnObtenerDatosProveedor();
					}

					if (data.contenido.selectTipo != "" || data.contenido.selectTipo != null) {
						$('#selectTipo').val(''+data.contenido.selectTipo);
						$('#selectTipo').multiselect('rebuild');

						if (data.contenido.selectTipo == 'O') {
							// Si es original
							$('#selectTipo').multiselect('disable');
						} else {
							// Si es incremento o decremento
							fnCambioTipoOperacion(data.contenido.selectTipo);

							$("#txtIdCompromiso").prop("disabled", true);
						}
					}

					$('#txtJustificacion').val(""+data.contenido.justificacion);
					
					if (data.contenido.legalid != "" || data.contenido.legalid != null) {
						legalid = data.contenido.legalid;
						$('#selectRazonSocial').selectpicker('val', ''+data.contenido.legalid);
						$("#selectRazonSocial").multiselect("refresh");
						$(".selectRazonSocial").css("display", "none");
					}
					
					if (data.contenido.tagref != "" || data.contenido.tagref != null) {
						tagref = data.contenido.tagref;
						$('#selectUnidadNegocio').val(''+data.contenido.tagref);
						$('#selectUnidadNegocio').multiselect('rebuild');
					}

					if (data.contenido.ln_ue != "" || data.contenido.ln_ue != null) {
						$('#selectUnidadEjecutora').val(''+data.contenido.ln_ue);
						$('#selectUnidadEjecutora').multiselect('rebuild');
					}

					fnObtenerPresupuestoBusqueda();
				}
				// console.log("dataJson: "+JSON.stringify(dataJson));
		    	idClavePresupuestoReducciones = 0;
				datosReducciones = new Array();
				totalReducciones = 0;
		    	for (var key in dataJson) {
					for (var key2 in dataJson[key]) {
						var dataJson2 = dataJson[key];
		    			fnMostrarPresupuesto(dataJson2, tablaReducciones, panelReducciones);
		    			datosReducciones.push(dataJson2);
		    		}
		    	}

		    	// Calcular totales por clave y renglon
				fnCalcularTotalesClaveRenglon();

		    	if (datosReducciones.length > 0 && usuarioOficinaCentral != 1) {
		    		// Si agrego datos deshabilitar UE
		    		// $('#selectUnidadEjecutora').multiselect('disable');
		    	}
	    	}
	    }else{
	    	//muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnDeshabilitaPagSuficiencia() {
	// Se va autorizar y deshabilitar pagina
	if (autorizarGeneral == 1) {
		// Deshabilitar
		$("#txtBuscarReducciones").prop("disabled", true);

		$('#selectRazonSocial').multiselect('disable');
		$('#selectUnidadNegocio').multiselect('disable');
		$('#selectUnidadEjecutora').multiselect('disable');

		$("#txtProveedor").prop("disabled", true);
		$("#txtContratoConvenio").prop("disabled", true);

		$("#txtFechaInicio").prop("disabled", true);
		$("#txtFechaFinal").prop("disabled", true);
		$("#txtFechaFirma").prop("disabled", true);

		$("#txtJustificacion").prop("disabled", true);
	}
}

function fnCambioUnidadNegocio() {
	//console.log("fnCambioUnidadNegocio");
	tagref = $("#selectUnidadNegocio").val();
}

function fnObtenerPresupuesto(clavePresupuesto, idTabla, panel, tipoAfectacion="", nuevo="") {
	//console.log("fnObtenerPresupuesto");
	//muestraCargandoGeneral();
	var tipoMovimiento = "";
	tipoMovimiento = "Reduccion";
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
		url: "modelo/compromisoModelo.php",
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
	    	validacionClave = fnValidarClave(clave, datosReducciones, panel, "Ya existe la Clave Presupuestal "+clave+" en la linea "+$("#Renglon_"+clave+"_"+panel).html());
    		if (validacionClave) {
    			datosReducciones.push(info);
    		}
	    	
	    	if (validacionClave) {
	    		fnMostrarPresupuesto(info, idTabla, panel);

	    		// Deshabilitar menses anteriores al actual
				fnDeshabilitarMesesAntes();

				fnCambioFecha();

				// Deshabilitar operación
				$('#selectTipo').multiselect('disable');
	    	}

	    	if (datosReducciones.length > 0 && usuarioOficinaCentral != 1) {
	    		// Si agrego datos deshabilitar UE
	    		// $('#selectUnidadEjecutora').multiselect('disable');
	    	}
	    	
	    	//ocultaCargandoGeneral();
	    }else{
	    	//ocultaCargandoGeneral();
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+data.Mensaje+'</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		//ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnGuardarSeleccionado(clavePresupuesto, input, panel, inputSelect) {
	//console.log("clavePresupuesto: "+clavePresupuesto+" - panel: "+panel);
	//console.log("caja: "+input.name+" - "+input.value);
	
	statusGuardar = 1; // Si hay cambios poner 1 para validar primero
	
	var dataJson = new Array();
	var tipoMovimiento = "";
	dataJson = datosReducciones;
	if(inputSelect == 'input') {
		totalReducciones = 0;
	}
	tipoMovimiento = "Reduccion";
	//console.log("dataJson antes: "+JSON.stringify(dataJson));
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clavePresupuesto) {
				if(inputSelect == 'input') {
					// Cambio un input
					var nombreInput = input.name.split("_");
					var numMes = 1;
					for (var mes in dataJsonMeses ) {
						// Nombres de los mes
		                var nombreMes = dataJsonMeses[mes];
		                if (nombreMes == nombreInput[2]) {
		                	//console.log("nombreMes: "+nombreMes+" disponible: "+dataJson2[key2][nombreMes]+" - input: "+input.value);
		                	var infoValidar = '';
		                	if (Number(mesActualAdecuacion) == Number(numMes)
		                		&& ($("#selectTipo").val() == 'O' || $("#selectTipo").val() == 'I')) {
		                		// Si el mes es el actual, validar el acomulado para Original e Incremento
		                		infoValidar = 'Acomulado';
		                	} else  if (Number(mesActualAdecuacion) <= Number(numMes)
		                		&& ($("#selectTipo").val() == 'D')) {
		                		// Si el mes es el actual, validar el compromiso para Decremento
		                		infoValidar = 'Compromiso';
		                	}

		                	var totalMesSuf = parseFloat(dataJson2[key2][nombreMes+infoValidar]);
		                	if ((parseFloat(totalMesSuf) < parseFloat(input.value != "" ? input.value : 0))) {
								var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    					muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En '+nombreMes+' el disponible es $ '+formatoComas( redondeaDecimal( parseFloat(totalMesSuf) ) )+' para la clave '+clavePresupuesto+'</p>');
		                		// $('#'+clavePresupuesto+"_"+panel+"_"+nombreMes).val(""+dataJson2[key2][nombreMes+"Sel"]);
		                		$('#'+clavePresupuesto+"_"+panel+"_"+nombreMes).val("0");
		                	}else{
		                		dataJson2[key2][nombreMes+"Sel"] = (input.value != "" ? input.value : 0);
		                	}

		                	break;
		                }
		                numMes ++;
		            }
				}else{
					// Cambio el select, tipo de afectacion
					dataJson2[key2].tipoAfectacion = input.value;
				}
			}

			if(inputSelect == 'input') {
				var total = 0;
				for (var mes in dataJsonMeses ) {
					// Nombres de los mes
					var nombreMes = dataJsonMeses[mes];
					total = parseFloat(total) + parseFloat(dataJson2[key2][nombreMes+"Sel"]);
				}

				totalReducciones += parseFloat(total);
			}
		}
	}
	//console.log("dataJson despues: "+JSON.stringify(dataJson));
	datosReducciones = dataJson;
	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

/**
 * Funcion para formato del total a visualizar
 * @param  {[type]} divNombre Nombre de div a mostrar informacion
 * @param  {[type]} total     Total a visualizar
 * @return {[type]}           [description]
 */
function fnMostrarTotalAmpRed(divNombre, total) {
	$('#'+divNombre).empty();
	$('#'+divNombre).html(""+ formatoComas( redondeaDecimal( total ) ) );
}

/**
 * Funcino para validar si ya existe la clave presupuetal antes de ser agregada
 * @param  {[type]} clave    Clave presupuetsal
 * @param  {[type]} dataJson Json con informacion
 * @param  {[type]} panel    Panel
 * @param  {[type]} mensaje  Mensaje si son iguales
 * @return {[type]}          [description]
 */
function fnValidarClave(clave, dataJson, panel, mensaje) {
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clave) {
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+mensaje+'</p>');
				return false;
			}
		}
	}
	
	return true;
}

/**
 * Función para confirmar antes de eliminar una clave presupuestal de la captura
 * @param  {[type]} clave           Clave presupuestal
 * @param  {[type]} panel           Panel de la informacion
 * @param  {Number} sinConfirmacion Variable para confirmación
 * @return {[type]}                 [description]
 */
function fnPresupuestoEliminar(clave, panel, sinConfirmacion=0) {
	//console.log("clave: "+clave);
	//console.log("panel: "+panel);
	//console.log("tipoAdecuacion: "+$('#selectTipoDoc').val());
	
	if (sinConfirmacion == 0) {
		var tipo = "Reducciones";
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar la Clave Presupuestal '+clave+'</p>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\')');
		return false;
	}

	// Agregar datos para eliminar al guardar
	var obj = new Object();
	obj.type = type;
	obj.transno = transno;
	obj.clave = clave;
	datosEliminarRedAmp.push(obj);

	// Eliminar Renglon
	fnEliminarRenglon(clave, panel);
}

/**
 * Funcion para eliminar un registro de la base de datos ya que fue eliminado en la captura
 * @param  {[type]} type    Tipo de movimiento
 * @param  {[type]} transno Folio de operación
 * @param  {[type]} clave   Clave a eliminar
 * @return {[type]}         [description]
 */
function fnQuitarRenglonDatosArray(type, transno, clave) {
	// Funcion para eliminar registros de la base de datos
	var respuesta = true;
	
	dataObj = { 
	        option: 'eliminaPresupuesto',
			type: type,
			transno: transno,
			clave: clave
	      };
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/compromisoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("data: "+JSON.stringify(data));
	    if(data.result){
	    	// var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    	// muestraModalGeneral(3, titulo, data.contenido);
	    	respuesta = true;
	    }else{
	    	// var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    	// muestraModalGeneral(3, titulo, data.contenido);
	    	respuesta = true;
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});

	return respuesta;
}

/**
 * Funcion para eliminar un registro del array de la informacion de captura
 * @param  {[type]} clave Clave a eliminar
 * @param  {[type]} panel Panel de donde se eliminara la informacion
 * @return {[type]}       [description]
 */
function fnEliminarRenglon(clave, panel) {
	//console.log("fnEliminarRenglon");
	for (var key in datosReducciones ) {
		for (var key2 in datosReducciones[key]) {
			var dataJsonReducciones = datosReducciones[key];
			if (dataJsonReducciones[key2].accountcode == clave) {
				datosReducciones.splice(key, 1);
				break;
			}
		}
	}
	fnRecargarDatosPaneles();

	if (datosReducciones.length == 0) {
		// Si no tiene informacion habilitar operacion
		// Deshabilitar operación
		$('#selectTipo').multiselect('enable');
	}
}

/**
 * Función para recargar información de las claves presupuestales
 * @return {[type]} [description]
 */
function fnRecargarDatosPaneles() {
	//console.log("///// fnRecargarDatosPaneles /////");
	$('#'+tablaReducciones+' tbody').empty();

	// Numero de linea
	numLineaReducciones = 1;

	// Id de clave para encabezado
	idClavePresupuestoReducciones = 0;

	// Total por panel
	totalReducciones = 0;

	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);

	for (var key in datosReducciones ) {
		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	}

	// Deshabilitar menses anteriores al actual
	fnDeshabilitarMesesAntes();

	fnCambioFecha();

	if (datosReducciones.length == 0 && usuarioOficinaCentral != 1) {
		// Si agrego datos deshabilitar UE
		// $('#selectUnidadEjecutora').multiselect('enable');
	}

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

/**
 * Función para cargar información en los botones de las operaciones
 * @param  {[type]} divMostrar id del dov a mostrar la información
 * @return {[type]}            [description]
 */
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
	      url: "modelo/compromisoModelo.php",
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
	    			funciones = 'fnAlmacenarCaptura('+statusGuardar+')';
	    		}
	    		if (info[key].statusid == 0) {
	    			statusGuardar = info[key].statusid;
	    			funciones = 'fnRegresarPanel()';
	    		}
	    		contenido += '&nbsp;&nbsp;&nbsp; \
	    		<component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
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

/**
 * Función para regresar al panel
 * @return {[type]} [description]
 */
function fnRegresarPanel() {
	// Al cancelar regresar al panel
	var Link_Panel = document.getElementById("linkPanelAdecuaciones");
	Link_Panel.click();
}

/**
 * Función para obtener la claves presupuestales y cargar la la lista en la caja de texto de busqueda
 * @param  {String} panel [description]
 * @return {[type]}       [description]
 */
function fnObtenerPresupuestoBusqueda(panel="") {
	var legalidBus = "";
	var tagrefBus = $('#selectUnidadNegocio').val();
	var concR23 = "";
	var dataJson = new Array();

	var ueBus = $('#selectUnidadEjecutora').val();

	dataObj = { 
	        option: 'obtenerPresupuestosBusqueda',
	        legalid: legalidBus,
	        tagref: tagrefBus,
	        ue: ueBus,
	        type: type,
			transno: transno,
	        filtrosClave: dataJson
	      };
    $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/compromisoModelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
            //console.log("datosPresupuestosBusqueda: "+JSON.stringify(datosPresupuestosBusqueda));
            datosPresupuestosBusqueda = data.contenido.datos;
            fnBusquedaReduccion(datosPresupuestosBusqueda);
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

/**
 * Función para cargar los datos de las claves a la caja de texto de busqueda
 * @param  {[type]} jsonData Json con información de las claves
 * @return {[type]}          [description]
 */
function fnBusquedaReduccion(jsonData) {
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
