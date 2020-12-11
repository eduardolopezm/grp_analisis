/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var dataJsonMeses = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

// Nombre de la vista para la tabla de Reduccion y Ampliacion
var tablaReducciones = "tablaReducciones";
var tablaAmpliaciones = "tablaAmpliaciones";

// Datos de la busqueda en Reduccion y Ampliacion
var datosPresupuestosBusqueda = {};
var idClavePresupuestoReducciones = 0;
var idClavePresupuestoAmpliaciones = 0;
var datosReducciones = new Array();
var datosAmpliaciones = new Array();
var datosEliminarRedAmp = new Array();

var datosInfoImpuestos = new Array();

var infoClavesValidar = new Array();
var tipoCom = "";
var folCom = "";
var idCom = "";

// Identificador para el panel, cambiar tambien en la vista fnObtenerPresupuestoBusqueda()
var panelReducciones = 1;
var panelAmpliaciones = 2;
// Totales de Reduccion y Ampliacion
var totalReducciones = 0;
var totalAmpliaciones = 0;
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
var numLineaAmpliaciones = 1;

// Titulo del mensaje de validaciones
var tituloModalValidaciones = "Validaciones";

var typePago = 0;
var transnoPago = 0;

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	if (document.querySelector(".selectTipoOpeCompromiso")) {
      // Muestra los tipos de operación para un compromiso
      dataObj = {
            option: 'mostrarPagosRectificacion'
        };
      fnSelectGeneralDatosAjax('.selectTipoOpeCompromiso', dataObj, 'modelo/componentes_modelo.php', 1);
    }

    // Poner 0 con formato antes de cargar captura
    fnMostrarTotalAmpRed('txtTotalReducciones', 0);

	//Obtener Datos de un No. Captura
	fnObtenerInfoNoCaptura();

	// Deshabilitar pagina
	fnDeshabilitaPagSuficiencia();

	// Datos lista de busqueda
	if ($('#selectUnidadEjecutora').val() != '-1' && type == '294') {
		// Si solo tiene una ue obtener información
		fnObtenerPresupuestoBusqueda();
	}

	$("#btnBuscarCompromiso").click(function() {
		// Buscar información del folio del pago
		fnCambioIdCompromiso($("#txtIdCompromiso").val(), $("#selectTipo").val());
	});
});

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
	
	// Si tiene retenciones poner el total en 0, para despues hacer las operaciones por mes
	var totalRetenciones = 0;
	for (var keyImpuesto in datosInfoImpuestos) {
		datosInfoImpuestos[keyImpuesto].total = 0;
	}

	var mesesTotales = new Array();
	var numLinea = 1;
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

	            // Obtener el porcentaje por mes y clave, hacer suma de todas las claves
	            for (var keyImpuesto in datosInfoImpuestos) {
	            	var promedio = parseFloat(dataJsonReducciones[key2][nombreMesSel]) * parseFloat(datosInfoImpuestos[keyImpuesto].porcentaje);
	            	promedio = parseFloat(promedio) / parseFloat(100);
					datosInfoImpuestos[keyImpuesto].total = parseFloat(datosInfoImpuestos[keyImpuesto].total) + parseFloat(promedio);

					// Mostrar total de retencion
					var nombreElemento = 'txtRetencion_'+datosInfoImpuestos[keyImpuesto].retencion;
					$("#"+nombreElemento).val(''+redondeaDecimal( datosInfoImpuestos[keyImpuesto].total ));

					totalRetenciones = parseFloat(totalRetenciones) + parseFloat(promedio);
				}
	        }
	        // Mostrar el total por clave presupuestal
	        var nombreDivTotal = 'divTotal_'+panelReducciones+'_'+dataJsonReducciones[key2].accountcode+'_'+numLinea;
	        $("#"+nombreDivTotal).empty();
	        $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );

	        numLinea ++;
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

	// Mostrar total retenciones y totoal general
	fnMostrarTotalAmpRed('txtTotalRetencion', totalRetenciones);
	fnMostrarTotalAmpRed('txtTotalGeneral', (parseFloat(totalReducciones) - parseFloat(totalRetenciones)));

	// Totales ampliaciones
	mesesTotales = new Array();
	numLinea = 1;
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
	        var nombreDivTotal = 'divTotal_'+panelAmpliaciones+'_'+dataJsonAmpliaciones[key2].accountcode+'_'+numLinea;
	        $("#"+nombreDivTotal).empty();
	        $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );

	        numLinea ++;
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

/**
 * Función para comprar el tipo de compromiso
 * @param  {[type]} select Valor seleccionado
 * @return {[type]}        [description]
 */
function fnCambioTipoOperacion(select) {
	// Si cambia tipo de operacion

	$("#txtIdCompromiso").prop("disabled", false);
	$("#btnBuscarCompromiso").prop("disabled", false);
	$("#txtBuscarReducciones").prop("disabled", false);
	
	// if ($('#selectUnidadNegocio').val() != '-1' && $('#selectUnidadEjecutora').val() != '-1') {
	// 	// Si solo tiene una ue obtener información
	// 	fnObtenerPresupuestoBusqueda();
	// }

	// type = select;

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
	var numLinea = 1;
	for (var key in datosReducciones) {
		for (var key2 in datosReducciones[key]) {
			var dataJson2 = datosReducciones[key];
			var obj = new Object();
			obj.accountcode = dataJson2[key2].accountcode;
			obj.noDevengado = dataJson2[key2].noDevengado;
			obj.noCompromiso = dataJson2[key2].noCompromiso;
			obj.noRetencion = dataJson2[key2].noRetencion;
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
            
            datosCapturaReducciones.push(obj);
                        
			if (Number(dataJson2[key2].EneroSel) == 0 && Number(dataJson2[key2].FebreroSel) == 0
				&& Number(dataJson2[key2].MarzoSel) == 0 && Number(dataJson2[key2].AbrilSel) == 0
				&& Number(dataJson2[key2].MayoSel) == 0 && Number(dataJson2[key2].JunioSel) == 0
				&& Number(dataJson2[key2].JulioSel) == 0 && Number(dataJson2[key2].AgostoSel) == 0
				&& Number(dataJson2[key2].SeptiembreSel) == 0 && Number(dataJson2[key2].OctubreSel) == 0
				&& Number(dataJson2[key2].NoviembreSel) == 0 && Number(dataJson2[key2].DiciembreSel) == 0) {
				claveMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Debe decir línea '+$("#Renglon_"+dataJson2[key2].accountcode+"_"+panelReducciones+"_"+numLinea).html()+' no tiene cantidad seleccionada</p>';
			}

			numLinea ++;
		}
	}

	numLinea = 1;
	for (var key in datosAmpliaciones) {
		for (var key2 in datosAmpliaciones[key]) {
			var dataJson2 = datosAmpliaciones[key];
			var obj = new Object();
			obj.accountcode = dataJson2[key2].accountcode;
			obj.noDevengado = dataJson2[key2].noDevengado;
			obj.noCompromiso = dataJson2[key2].noCompromiso;
			obj.noRetencion = dataJson2[key2].noRetencion;
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
            
            datosCapturaAmpliaciones.push(obj);
                        
			if (Number(dataJson2[key2].EneroSel) == 0 && Number(dataJson2[key2].FebreroSel) == 0
				&& Number(dataJson2[key2].MarzoSel) == 0 && Number(dataJson2[key2].AbrilSel) == 0
				&& Number(dataJson2[key2].MayoSel) == 0 && Number(dataJson2[key2].JunioSel) == 0
				&& Number(dataJson2[key2].JulioSel) == 0 && Number(dataJson2[key2].AgostoSel) == 0
				&& Number(dataJson2[key2].SeptiembreSel) == 0 && Number(dataJson2[key2].OctubreSel) == 0
				&& Number(dataJson2[key2].NoviembreSel) == 0 && Number(dataJson2[key2].DiciembreSel) == 0) {
				claveMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Dice línea '+$("#Renglon_"+dataJson2[key2].accountcode+"_"+panelReducciones+"_"+numLinea).html()+' no tiene cantidad seleccionada</p>';
			}

			numLinea ++;
		}
	}

	// console.log("datosCapturaReducciones: "+JSON.stringify(datosCapturaReducciones));
	// console.log("datosCapturaAmpliaciones: "+JSON.stringify(datosCapturaAmpliaciones));

	// ocultaCargandoGeneral();
	// return true;

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

	if ($('#selectTipo').val() == '295' || $('#selectTipo').val() == '296') {
		// Si es pago de adquisiciones o subsidios validar compromiso
		var respuesta = fnValidarCompromisoExiste(''+$("#txtIdCompromiso").val(), $("#selectTipo").val());
		if (!respuesta[0]) {
			// Si no existe proveedor
			ocultaCargandoGeneral();
			return true;
		}

		var numRenglon = 1;
		var mensaje = '';
		for (var key in datosCapturaReducciones) {
			var encontro = 0;
			for (var keyVal in infoClavesValidar) {
				if (infoClavesValidar[keyVal].accountcode == datosCapturaReducciones[key].accountcode) {
					encontro = 1;
					break;
				}
			}
			if (encontro == 0) {
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Debe decir el renglón '+numRenglon+' no se encuentra en el compromiso '+datosCapturaReducciones[key].noCompromiso+'</p>';
			}
			numRenglon ++;
		}

		if (mensaje != '') {
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
		}
	}
	// console.log("infoClavesValidar: "+JSON.stringify(infoClavesValidar));
	// console.log("tipoCom: "+tipoCom);
	// console.log("folCom: "+folCom);
	// console.log("idCom: "+idCom);
	// return true;

	if (Number(totalReducciones) != Number(totalAmpliaciones)) {
		// Si son montos diferentes de ampliacion y reduccion
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Monto total de DICE y DEBE DECIR deben ser iguales</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	//Opcion para operacion
	dataObj = { 
	        option: 'guardarOperacion',
	        datosCapturaReducciones: datosCapturaReducciones,
			datosCapturaAmpliaciones: datosCapturaAmpliaciones,
			datosCapturaImpuestos: datosInfoImpuestos,
			type: type,
			transno: transno,
			legalid: legalid,
			tagref: tagref,
			estatus: statusGuardar,
			fechaCaptura: $('#txtFechaCaptura').val(),
			fechaAutorizacion: $('#txtFechaAutorizacion').val(),
			justificacion: $('#txtJustificacion').val(),
			ue: ue,
			selectTipo: $('#selectTipo').val(),
			txtIdCompromiso: $('#txtIdCompromiso').val(),
			typePago: typePago,
			transnoPago: transnoPago,
			tipoCom: tipoCom,
			folCom: folCom,
			idCom: idCom
	      };
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/rectificacionModelo.php",
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

				// Si es original
				$('#selectTipo').multiselect('disable');

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
 * Funcion para cuando cambia el folio del pagado
 * @param  {[type]} idcompromiso Id de compromiso
 * @return {[type]}              [description]
 */
function fnCambioIdCompromiso(idcompromiso, selectTipo) {
	// Cambio el folio del pagado
	if (idcompromiso.trim() == '' || idcompromiso.trim() == null) {
		// Compromiso vacío
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Folio del Pagado se encuentra vacío</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return false;
	}

	var respuesta = fnValidarCompromisoExiste(idcompromiso, selectTipo);
	if (respuesta[0]) {
		// Existe folio del pagado
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Desea Cargar la información del Pago '+idcompromiso+'?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnCargarCompromiso('"+respuesta[1]+"', '"+respuesta[2]+"')");
	}
}

/**
 * Funcion para cargar la información del compromiso original
 * @param  {[type]} typeOriginal    Tipo de documento
 * @param  {[type]} transnoOriginal Número de la operación
 * @return {[type]}                 [description]
 */
function fnCargarCompromiso(typeOriginal, transnoOriginal, decremento = 0) {
	// Cargar el informacion del folio del pago
	// Variable datos anteriores
	var tipoAnt = $("#selectTipo").val();
	var typeAnt = type;
	var transnoAnt = transno;

	type = typeOriginal;
	transno = transnoOriginal;
	typePago = typeOriginal;
	transnoPago = transnoOriginal;

	numLineaReducciones = 1;

	var compromiso = $("#txtIdCompromiso").val();

	//Obtener Datos de un No. Captura
	fnObtenerInfoNoCapturaCompromiso(compromiso, decremento);

	// Poner en 0 las claves presupuestales cargadas
	fnCeroClavesPresupuestales(panelReducciones);

	// Deshabilitar menses anteriores al actual
	fnDeshabilitarMesesAntes();

	// Regresar datos anteriores
	type = typeAnt;
	transno = transnoAnt;

	$('#txtNoCaptura').empty();
	$('#txtNoCaptura').append(transno);

	// $('#selectTipo').val(''+tipoAnt);
	// $('#selectTipo').multiselect('rebuild');

	$("#txtIdCompromiso").prop("disabled", true);
	$("#btnBuscarCompromiso").prop("disabled", true);

	// Justificación vacía
	$('#txtJustificacion').val('');

	// Deshabilitar operación
	$('#selectTipo').multiselect('disable');

	// Deshabilitar UR y UE
	// $('#selectUnidadNegocio').multiselect('disable');
	// $('#selectUnidadEjecutora').multiselect('disable');

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
 * Funcion para validar si existe el folio del pago tecleado
 * @param  {[type]} idcompromiso Folio del Pago
 * @return {[type]}              [description]
 */
function fnValidarCompromisoExiste(idcompromiso, selectTipo) {
	// funcion para validar si existe el folio del pago tecleado
	// console.log("fnValidarCompromisoExistellññls idcompromiso: "+idcompromiso+" - selectTipo: "+selectTipo);
	var respuesta = new Array();

	dataObj = { 
        option: 'existeCompromiso',
        idcompromiso: idcompromiso,
        selectTipo: selectTipo
      };
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/rectificacionModelo.php",
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

	var numLineaRegistro = 0;
	if (panel == panelAmpliaciones) {
		// Ampliaciones
		numLineaRegistro = numLineaAmpliaciones;
	} else {
		// Reducciones
		numLineaRegistro = numLineaReducciones;
	}

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

		if (panel == panelAmpliaciones) {
			// Ampliaciones
			if (idClavePresupuestoAmpliaciones != dataJson[key].idClavePresupuesto) {
				idClavePresupuestoAmpliaciones = dataJson[key].idClavePresupuesto;
				enca = 0;
			}
		} else {
			// Reducciones
			if (idClavePresupuestoReducciones != dataJson[key].idClavePresupuesto) {
				idClavePresupuestoReducciones = dataJson[key].idClavePresupuesto;
				enca = 0;
			}
		}

		if (panel == panelAmpliaciones) {
			// Ampliaciones
			totalAmpliaciones += parseFloat(total);
		} else {
			// Reducciones
			totalReducciones += parseFloat(total);
		}

		if (enca == 0) {
			encabezado += '<tr class="header-verde"><td></td><td></td>';
		}

		if (enca == 0 && $("#selectTipo").val() == '298') {
			// Si es impuestos
			encabezado += '<td '+styleMeses+'>No. Devengado</td>';
		}

		if (enca == 0 && ($("#selectTipo").val() == '298' || $("#selectTipo").val() == '299')) {
			// Si es impuestos y decremento
			encabezado += '<td '+styleMeses+'>Retención</td>';
		}

		if (autorizarGeneral == 1) {
			contenido += '<td></td>';
		}else{
			contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\', \'0\', \''+numLineaRegistro+'\')"></button></td>';
		}

		if ($("#selectTipo").val() == '298') {
			// Si es impuestos
			contenido += '<td '+styleMeses+'>'+dataJson[key].noDevengado+'</td>';
		}

		if (($("#selectTipo").val() == '298' || $("#selectTipo").val() == '299')) {
			// Si es impuestos y decremento
			contenido += '<td '+styleMeses+'>'+dataJson[key].nombreRetencion+'</td>';
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
		var nombreDivTotal = 'divTotal_'+panel+'_'+dataJson[key].accountcode+'_'+numLineaRegistro;
		contenido += '<td '+style+' id="'+nombreDivTotal+'">$ '+formatoComas( redondeaDecimal( 0 ) )+'</td>';

		var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_"+numLineaRegistro+"_"; // No cambiar estructura de nombre o cambiar tambien en fnGuardarSeleccionado()
		var numMes = 1;
		for (var mes in dataJsonMeses ) {
			// Informacion meses para seleccion
			var nombreMes = dataJsonMeses[mes];
			var nombreMesSel = dataJsonMeses[mes]+"Sel";
			var nombreMesCompra = dataJsonMeses[mes]+"Compra";
			var cantidadMes = parseFloat(dataJson[key][nombreMes]);
			var informacionCancelar = "";
			var styleOcultarMes = 'style="display: none;"';
			var styleInputText = ' style="width: 120px;" ';

			var textoMostrar = nombreMes;

			if (Number(mesActualAdecuacion) == Number(numMes)) {
				styleOcultarMes = 'style="text-align:center;"';
				textoMostrar = 'Monto a Pagar';
			}

			if (enca == 0) {
				// Nombres de los mes para el encabezado
				encabezado += '<td '+styleOcultarMes+'>'+textoMostrar+'</td>';
			}
            
            contenido += '<td align="center" '+styleOcultarMes+'> <div align="center">';
            if (panel == panelAmpliaciones) {
            	// Si es panel de ampliaciones
            	contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreMes+'Pago']) ) ) +'<br>';
            } else {
            	// Si es panel de reducciones, disponible
            	if ($('#selectTipo').val() == '295' || $('#selectTipo').val() == '296') {
					// Si es pago de adquisiciones o subsidios validar compromiso
					contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreMes+'Compromiso']) ) ) +'<br>';
	            } else {
	            	contenido += '$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreMes+'Acomulado']) ) ) +'<br>';
	            }
            }

			// contenido += '<input '+deshabilitarElemento+' type="text"  min="0" class="form-control" name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" style="width: 80px;" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" /></td>';
			contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+numLineaRegistro+'\')"></component-decimales>';    
			
			contenido += '</div></td>';

			numMes ++;
		}

		if (enca == 0) {
			encabezado += '</tr>';
		}

		var nombreDiv = 'Renglon_'+dataJson[key].accountcode+'_'+panel+'_'+numLineaRegistro;
		contenido = '<td id="'+nombreDiv+'" name="'+nombreDiv+'">'+numLineaRegistro+'</td>' + contenido;

		contenido = encabezado + '<tr id="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'_'+numLineaRegistro+'" name="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'_'+numLineaRegistro+'" >' + contenido + '</tr>';

		enca = 1;
	}

	if (panel == panelAmpliaciones) {
		// Ampliaciones
		fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
	} else {
		// Reducciones
		fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
	}
        
	$('#'+idTabla+' tbody').append(contenido);

	fnEjecutarVueGeneral('RenglonTR_'+clavePresupuesto+'_'+panel+'_'+numLineaRegistro);

	if (panel == panelAmpliaciones) {
		// Ampliaciones
		numLineaAmpliaciones = parseFloat(numLineaAmpliaciones) + 1;
	} else {
		// Reducciones
		numLineaReducciones = parseFloat(numLineaReducciones) + 1;
	}
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

	fnSelectGeneralDatosAjax('#'+nomUnidadNegocio, dataObj, 'modelo/rectificacionModelo.php');
}

/**
 * Función para cagrar información almacenada del folio del pagado
 * @return {[type]} [description]
 */
function fnObtenerInfoNoCapturaCompromiso(idcompromiso = '', decremento = 0) {
	dataObj = { 
	        option: 'cargarInfoNoCapturaCompromiso',
			type: type,
			transno: transno,
			datosClave: '1',
			datosClaveAdecuacion: '1',
			idcompromiso: idcompromiso,
			decremento: decremento
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/rectificacionModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log(data);
	    if(data.result){
	    	dataJson=data.contenido.datos;
	    //	console.log(dataJson);
	    	
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

					$('#txtIdCompromiso').val("");
					if (data.contenido.txtIdCompromiso != "" || data.contenido.txtIdCompromiso != null) {
						$('#txtIdCompromiso').val(""+data.contenido.txtIdCompromiso);
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
				}

				// console.log("infoClaves: "+JSON.stringify(data.contenido.infoClaves));
				// console.log("tipoCom: "+data.contenido.tipoCom);
				// console.log("folCom: "+data.contenido.folCom);
				// console.log("idCom: "+data.contenido.idCom);
				
				infoClavesValidar = data.contenido.infoClaves;
				tipoCom = data.contenido.tipoCom;
				folCom = data.contenido.folCom;
				idCom = data.contenido.idCom;

		    	idClavePresupuestoAmpliaciones = 0;

				datosAmpliaciones = new Array();
				totalAmpliaciones = 0;
				numLineaAmpliaciones = 1;
		    	for (var key in dataJson) {
					for (var key2 in dataJson[key]) {
						var dataJson2 = dataJson[key];
		    			fnMostrarPresupuesto(dataJson2, tablaAmpliaciones, panelAmpliaciones);
		    			datosAmpliaciones.push(dataJson2);
		    		}
		    	}

		    	// Calcular totales por clave y renglon
				fnCalcularTotalesClaveRenglon();

				fnObtenerPresupuestoBusqueda();
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

/**
 * Función para cagrar información almacenada de la captura a visualizar
 * @return {[type]} [description]
 */
function fnObtenerInfoNoCaptura() {
	dataObj = { 
	        option: 'cargarInfoNoCaptura',
			type: type,
			transno: transno,
			datosClave: '1',
			datosClaveAdecuacion: '1'
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/rectificacionModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
		// console.log(data);
	    if(data.result){
	    	dataJson=data.contenido.datos;
	    	//console.log("dataJson: "+JSON.stringify(dataJson));

            typePago = data.contenido.typePago;
            transnoPago = data.contenido.transnoPago;
            // console.log("typePago: "+typePago+" - transnoPago: "+transnoPago);
            
	    	if (data.contenido.transno != 0) {
	    		$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.transno);
				statusGuardar = data.contenido.estatus;
				
				$('#txtFechaCaptura').val("");
				if (data.contenido.fechaCaptura != "" || data.contenido.fechaCaptura != null) {
					$('#txtFechaCaptura').val(""+data.contenido.fechaCaptura);
				}

				$('#txtIdCompromiso').val("");
				if (data.contenido.txtIdCompromiso != "" || data.contenido.txtIdCompromiso != null) {
					$('#txtIdCompromiso').val(""+data.contenido.txtIdCompromiso);
				}

				if (data.contenido.selectTipo != "" || data.contenido.selectTipo != null) {
					$('#selectTipo').val(''+data.contenido.selectTipo);
					$('#selectTipo').multiselect('rebuild');

					// Deshabilitar operación
					$('#selectTipo').multiselect('disable');

					// Validar captura
					fnCambioTipoOperacion(data.contenido.selectTipo);
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

				// Deshabilitar UR y UE
				// $('#selectUnidadNegocio').multiselect('disable');
				// $('#selectUnidadEjecutora').multiselect('disable');

				fnObtenerPresupuestoBusqueda();
			}

			// console.log("infoClaves: "+JSON.stringify(data.contenido.infoClaves));
			// console.log("tipoCom: "+data.contenido.tipoCom);
			// console.log("folCom: "+data.contenido.folCom);
			// console.log("idCom: "+data.contenido.idCom);
			
			infoClavesValidar = data.contenido.infoClaves;
			tipoCom = data.contenido.tipoCom;
			folCom = data.contenido.folCom;
			idCom = data.contenido.idCom;

			$('#'+tablaReducciones+' tbody').empty();
			$('#'+tablaAmpliaciones+' tbody').empty();
			
			// Mostrar Ampliaciones
	    	idClavePresupuestoAmpliaciones = 0;
	    	idClavePresupuestoReducciones = 0;
			datosAmpliaciones = new Array();
			datosReducciones = new Array();
			totalAmpliaciones = 0;
			totalReducciones = 0;
			numLineaAmpliaciones = 1;
			numLineaReducciones = 1;

	    	for (var key in dataJson) {
				for (var key2 in dataJson[key]) {
					var dataJson2 = dataJson[key];
	    			if (dataJson2[key2].tipoMovimiento == "Reduccion") {
	    				// Reducciones
	    				fnMostrarPresupuesto(dataJson2, tablaReducciones, panelReducciones);
						datosReducciones.push(dataJson2);
	    			} else {
	    				// Ampliaciones
	    				fnMostrarPresupuesto(dataJson2, tablaAmpliaciones, panelAmpliaciones);
	    				datosAmpliaciones.push(dataJson2);
	    			}
	    		}
	    	}

	    	// Calcular totales por clave y renglon
			fnCalcularTotalesClaveRenglon();

	    	if (datosReducciones.length > 0 && usuarioOficinaCentral != 1) {
	    		// Si agrego datos deshabilitar UE
	    		// $('#selectUnidadEjecutora').multiselect('disable');
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

		$("#txtJustificacion").prop("disabled", true);

		$("#txtIdCompromiso").prop("disabled", true);
		$("#btnBuscarCompromiso").prop("disabled", true);
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
			tipoMovimiento: tipoMovimiento,
			selectTipo: $('#selectTipo').val(),
			tipoCom: tipoCom,
			folCom: folCom,
			idCom: idCom
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/rectificacionModelo.php",
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
	    	validacionClave = fnValidarClave(clave, datosReducciones, panel, "Ya existe la Clave Presupuestal "+clave);
    		if (validacionClave) {
    			datosReducciones.push(info);
    		}
	    	
	    	if (validacionClave) {
	    		fnMostrarPresupuesto(info, idTabla, panel);

	    		// Deshabilitar menses anteriores al actual
				fnDeshabilitarMesesAntes();

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

function fnGuardarSeleccionado(clavePresupuesto, input, panel, inputSelect, numLineaModificar) {
	// console.log("clavePresupuesto: "+clavePresupuesto+" - panel: "+panel+" - numLineaModificar: "+numLineaModificar);
	// console.log("caja: "+input.name+" - "+input.value);
	
	statusGuardar = 1; // Si hay cambios poner 1 para validar primero
	
	var dataJson = new Array();
	var infoValidar = '';

	if (panel == panelAmpliaciones) {
		// Ampliaciones
		dataJson = datosAmpliaciones;
		infoValidar = 'Pago';
	} else {
		// Reducciones
		dataJson = datosReducciones;
		if ($('#selectTipo').val() == '295' || $('#selectTipo').val() == '296') {
			// Si es pago de adquisiciones o subsidios validar compromiso
			infoValidar = 'Compromiso';
		} else {
			infoValidar = 'Acomulado';
		}
	}

	if(inputSelect == 'input') {
		if (panel == panelAmpliaciones) {
			// Ampliaciones
			totalAmpliaciones = 0;
		} else {
			// Reducciones
			totalReducciones = 0;
		}
	}
	
	// console.log("dataJson antes: "+JSON.stringify(dataJson));
	var numLinea = 1;
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			// console.log("numLinea: "+numLinea+" - numLineaModificar: "+numLineaModificar);
			if (dataJson2[key2].accountcode == clavePresupuesto && Number(numLinea) == Number(numLineaModificar)) {
				if(inputSelect == 'input') {
					// Cambio un input
					var nombreInput = input.name.split("_");
					var numMes = 1;
					for (var mes in dataJsonMeses ) {
						// Nombres de los mes
		                var nombreMes = dataJsonMeses[mes];
		                if (nombreMes == nombreInput[3]) {
		                	// console.log("nombreMes: "+nombreMes+" disponible: "+dataJson2[key2][nombreMes]+" - input: "+input.value);

		                	var totalMesSuf = parseFloat(dataJson2[key2][nombreMes+infoValidar]);
		                	if ((parseFloat(totalMesSuf) < parseFloat(input.value != "" ? input.value : 0))) {
								var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
								var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para la linea '+numLinea+' en '+nombreMes+' el disponible es $ '+formatoComas( redondeaDecimal( parseFloat(totalMesSuf) ) )+'</p>';
		    					muestraModalGeneral(3, titulo, mensaje);
		                		// $('#'+clavePresupuesto+"_"+panel+"_"+nombreMes).val(""+dataJson2[key2][nombreMes+"Sel"]);
		                		$('#'+clavePresupuesto+"_"+panel+"_"+numLinea+"_"+nombreMes).val("0");
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

				if (panel == panelAmpliaciones) {
					// Ampliaciones
					totalAmpliaciones += parseFloat(total);
				} else {
					// Reducciones
					totalReducciones += parseFloat(total);
				}
			}

			numLinea ++;
		}
	}
	// console.log("dataJson despues: "+JSON.stringify(dataJson));
	if (panel == panelAmpliaciones) {
		// Ampliaciones
		datosAmpliaciones = dataJson;
		fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);
	} else {
		// Reducciones
		datosReducciones = dataJson;
		fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
	}

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
	var numLinea = 1;
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clave) {
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+mensaje+" en la linea "+$("#Renglon_"+clave+"_"+panel+"_"+numLinea).html()+'</p>');
				return false;
			}

			numLinea ++;
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
function fnPresupuestoEliminar(clave, panel, sinConfirmacion=0, numLinea = 0) {
	//console.log("clave: "+clave);
	//console.log("panel: "+panel);
	
	if (sinConfirmacion == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar la Clave Presupuestal '+clave+'</p>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\', \''+numLinea+'\')');
		return false;
	}

	// Agregar datos para eliminar al guardar
	var obj = new Object();
	obj.type = type;
	obj.transno = transno;
	obj.clave = clave;
	datosEliminarRedAmp.push(obj);

	// Eliminar Renglon
	fnEliminarRenglon(clave, panel, numLinea);
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
	      url: "modelo/rectificacionModelo.php",
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
function fnEliminarRenglon(clave, panel, numLineaEliminar) {
	//console.log("fnEliminarRenglon");
	var numLinea = 1;
	// console.log("panelAmpliaciones: "+panelAmpliaciones+" - panel: "+panel);
	if (panel == panelAmpliaciones) {
		// Ampliaciones
		for (var key in datosAmpliaciones ) {
			for (var key2 in datosAmpliaciones[key]) {
				var dataJsonAmpliaciones = datosAmpliaciones[key];
				// console.log("accountcode: "+dataJsonAmpliaciones[key2].accountcode);
				// console.log("clave: "+clave);
				// console.log("numLinea: "+numLinea+" - numLineaEliminar: "+numLineaEliminar);
				if (dataJsonAmpliaciones[key2].accountcode == clave && Number(numLinea) == Number(numLineaEliminar)) {
					// console.log("eliminar");
					datosAmpliaciones.splice(key, 1);
					break;
				}

				numLinea ++;
			}
		}
	} else {
		// Reducciones
		for (var key in datosReducciones ) {
			for (var key2 in datosReducciones[key]) {
				var dataJsonReducciones = datosReducciones[key];
				if (dataJsonReducciones[key2].accountcode == clave && Number(numLinea) == Number(numLineaEliminar)) {
					datosReducciones.splice(key, 1);
					break;
				}

				numLinea ++;
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

	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
	fnMostrarTotalAmpRed('txtTotalAmpliaciones', totalAmpliaciones);


	for (var key in datosReducciones ) {
		console.log(datosReducciones[key], tablaReducciones, panelReducciones);
		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	}

	for (var key in datosAmpliaciones ) {
		fnMostrarPresupuesto(datosAmpliaciones[key], tablaAmpliaciones, panelAmpliaciones);
	}

	// Deshabilitar menses anteriores al actual
	fnDeshabilitarMesesAntes();

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
	      url: "modelo/rectificacionModelo.php",
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
	// Obtener claves de busqueda
	var legalidBus = "";
	var tagrefBus = $('#selectUnidadNegocio').val();
	var concR23 = "";
	var dataJson = new Array();

	var ueBus = $('#selectUnidadEjecutora').val();

	muestraCargandoGeneral();

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
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/rectificacionModelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        ocultaCargandoGeneral();
        if(data.result) {
            //console.log("datosPresupuestosBusqueda: "+JSON.stringify(datosPresupuestosBusqueda));
            datosPresupuestosBusqueda = data.contenido.datos;
            fnBusquedaReduccion(datosPresupuestosBusqueda);
        }
    })
    .fail(function(result) {
    	ocultaCargandoGeneral();
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
