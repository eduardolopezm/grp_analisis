/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */
//
class datosGenerales {
	constructor(type = 49, transno = 0, capitulos = '', ur = '', ue = '', partidasEsp = '') {
		this.type = type;
		this.transno = transno;
		this.capitulos = capitulos;
		this.ur = ur;
		this.ue = ue;
		this.partidasEsp = partidasEsp;
		this.infoClavesAnual = new Array();
		this.infoMeses = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

		this.infoBotones = new Array();

		this.infoClavesBusqueda = new Array();

		this.infoAutorizada = new Array();

		this.infoAutorizadaVar = new Array();
		
		this.totalGeneral = 0;
		this.totalCapitulos = 0;
		this.totalUr = 0;
		this.totalUe = 0;
		this.totalClaveAnual = 0;

		this.perEncabezado = 0;
		this.perTechos = 0;
		this.perClaves = 0;

		this.utilizarPaaas = 0;
		this.utilizarUe = 0;
		this.utilizarSoloUnaFase= 0;
		this.validarJustificacion = 0;

		this.faseCapuraPanel = 1;
		this.errorValidacion = 0;
		this.statusid = 1;

		this.numRenglonesTotalAnual = 1;

		this.configuracionClave = 0;
		this.configuracionClaveInfo = new Array();
		
		this.descripcion = '';
		this.fechaCaptura = '';
		this.anio = '';
		this.anioAnterior = '';

		this.nomTablaCapitulos = 'tablaCapitulos';
		this.nomTablaUnidadResponsable = 'tablaUnidadResponsable';
		this.nomTablaUnidadEjecutora = 'tablaUnidadEjecutora';
		this.nomTablaClavePresupuestalAnual = 'tablaClavePresupuestalAnual';

		this.nomPanelCapitulos = 'PanelCapitulo';
		this.nomPanelUnidadResponsable = 'PanelUnidadResponsable';
		this.nomPanelUnidadEjecutora = 'PanelUnidadEjecutora';
		this.nomPanelClavePresupuestalAnual = 'PanelClavePresupuestalAnual';

		this.nomDivTotalCapitulo = 'divTotalCapitulo';
		this.nomDivTotalUnidadResponsable = 'divTotalUnidadResponsable';
		this.nomDivTotalUnidadEjecutora = 'divTotalUnidadEjecutora';
		this.nomDivTotalAnual= 'divTotalAnual';
	}

	get getType() {
		return this.type;
	}

	setType(type = 0) {
		this.type = type;
	}

	get getTransno() {
		return this.transno;
	}

	setTransno(transno = 0) {
		this.transno = transno;
	}

	get getCapitulos() {
		return this.capitulos;
	}

	setCapitulos(capitulos = '') {
		this.capitulos = capitulos;
	}

	get getUr() {
		return this.ur;
	}

	setUr(ur = '') {
		this.ur = ur;
	}

	get getUe() {
		return this.ue;
	}

	setUe(ue = '') {
		this.ue = ue;
	}

	get getPartidasEsp() {
		return this.partidasEsp;
	}

	setPartidasEsp(partidasEsp = '') {
		this.partidasEsp = partidasEsp;
	}

	get getInfoClavesAnual() {
		return this.infoClavesAnual;
	}

	setInfoClavesAnual(infoClavesAnual = '') {
		this.infoClavesAnual = infoClavesAnual;
	}

	get getInfoClavesMeses() {
		return this.infoMeses;
	}

	setInfoClavesMeses(infoMeses = '') {
		this.infoMeses = infoMeses;
	}

	get getInfoBotones() {
		return this.infoBotones;
	}

	setInfoBotones(infoBotones = '') {
		this.infoBotones = infoBotones;
	}

	get getInfoClavesBusqueda() {
		return this.infoClavesBusqueda;
	}

	setInfoClavesBusqueda(infoClavesBusqueda = '') {
		this.infoClavesBusqueda = infoClavesBusqueda;
	}

	get getInfoAutorizada() {
		return this.infoAutorizada;
	}

	setInfoAutorizada(infoAutorizada = '') {
		this.infoAutorizada = infoAutorizada;
	}

	get getInfoAutorizadaVar() {
		return this.infoAutorizadaVar;
	}

	setInfoAutorizadaVar(infoAutorizadaVar = '') {
		this.infoAutorizadaVar = infoAutorizadaVar;
	}

	get getTotalGeneral() {
		return this.totalGeneral;
	}

	setTotalGeneral(totalGeneral = 0) {
		this.totalGeneral = totalGeneral;
	}

	get getTotalCapitulos() {
		return this.totalCapitulos;
	}

	setTotalCapitulos(totalCapitulos = 0) {
		this.totalCapitulos = totalCapitulos;
	}

	get getTotalUr() {
		return this.totalUr;
	}

	setTotalUr(totalUr = 0) {
		this.totalUr = totalUr;
	}

	get getTotalUe() {
		return this.totalUe;
	}

	setTotalUe(totalUe = 0) {
		this.totalUe = totalUe;
	}

	get getTotalClaveAnual() {
		return this.totalClaveAnual;
	}

	setTotalClaveAnual(totalClaveAnual = 0) {
		this.totalClaveAnual = totalClaveAnual;
	}

	get getDescripcion() {
		return this.descripcion;
	}

	setDescripcion(descripcion = '') {
		this.descripcion = descripcion;
	}

	get getFechaCaptura() {
		return this.fechaCaptura;
	}

	setFechaCaptura(fechaCaptura = '') {
		this.fechaCaptura = fechaCaptura;
	}
	
	get getAnio() {
		return this.anio;
	}

	setAnio(anio = '') {
		this.anio = anio;
	}

	get getAnioAnterior() {
		return this.anioAnterior;
	}

	setAnioAnterior(anioAnterior = '') {
		this.anioAnterior = anioAnterior;
	}

	get getNomTablaCapitulos() {
		return this.nomTablaCapitulos;
	}

	setNomTablaCapitulos(nomTablaCapitulos = '') {
		this.nomTablaCapitulos = nomTablaCapitulos;
	}

	get getNomTablaUnidadResponsable() {
		return this.nomTablaUnidadResponsable;
	}

	setNomTablaUnidadResponsable(nomTablaUnidadResponsable = '') {
		this.nomTablaUnidadResponsable = nomTablaUnidadResponsable;
	}

	get getNomTablaUnidadEjecutora() {
		return this.nomTablaUnidadEjecutora;
	}

	setNomTablaUnidadEjecutora(nomTablaUnidadEjecutora = '') {
		this.nomTablaUnidadEjecutora = nomTablaUnidadEjecutora;
	}

	get getFaseCapuraPanel() {
		return this.faseCapuraPanel;
	}

	setFaseCapuraPanel(faseCapuraPanel = 1) {
		this.faseCapuraPanel = faseCapuraPanel;
	}

	get getErrorValidacion() {
		return this.errorValidacion;
	}

	setErrorValidacion(errorValidacion = 0) {
		this.errorValidacion = errorValidacion;
	}
	
	get getUtilizarPaaas() {
		return this.utilizarPaaas;
	}

	setUtilizarPaaas(utilizarPaaas = 0) {
		this.utilizarPaaas = utilizarPaaas;
	}

	get getPerEncabezado() {
		return this.perEncabezado;
	}

	setPerEncabezado(perEncabezado = 0) {
		this.perEncabezado = perEncabezado;
	}
	
	get getPerTechos() {
		return this.perTechos;
	}

	setPerTechos(perTechos = 0) {
		this.perTechos = perTechos;
	}
	
	get getPerClaves() {
		return this.perClaves;
	}

	setPerClaves(perClaves = 0) {
		this.perClaves = perClaves;
	}

	get getUtilizarSoloUnaFase() {
		return this.utilizarSoloUnaFase;
	}

	setUtilizarSoloUnaFase(utilizarSoloUnaFase = 0) {
		this.utilizarSoloUnaFase = utilizarSoloUnaFase;
	}

	get getValidarJustificacion() {
		return this.validarJustificacion;
	}

	setValidarJustificacion(validarJustificacion = 0) {
		this.validarJustificacion = validarJustificacion;
	}

	get getUtilizarUe() {
		return this.utilizarUe;
	}

	setUtilizarUe(utilizarUe = 0) {
		this.utilizarUe = utilizarUe;
	}
	
	get getNomPanelCapitulos() {
		return this.nomPanelCapitulos;
	}

	setNomPanelCapitulos(nomPanelCapitulos = 0) {
		this.nomPanelCapitulos = nomPanelCapitulos;
	}

	get getNomPanelUnidadResponsable() {
		return this.nomPanelUnidadResponsable;
	}

	setNomPanelUnidadResponsable(nomPanelUnidadResponsable = 0) {
		this.nomPanelUnidadResponsable = nomPanelUnidadResponsable;
	}

	get getNomPanelUnidadEjecutora() {
		return this.nomPanelUnidadEjecutora;
	}

	setNomPanelUnidadEjecutora(nomPanelUnidadEjecutora = 0) {
		this.nomPanelUnidadEjecutora = nomPanelUnidadEjecutora;
	}
	
	get getNomDivTotalCapitulo() {
		return this.nomDivTotalCapitulo;
	}

	setNomDivTotalCapitulo(nomDivTotalCapitulo = 0) {
		this.nomDivTotalCapitulo = nomDivTotalCapitulo;
	}

	get getNomDivTotalUnidadResponsable() {
		return this.nomDivTotalUnidadResponsable;
	}

	setNomDivTotalUnidadResponsable(nomDivTotalUnidadResponsable = 0) {
		this.nomDivTotalUnidadResponsable = nomDivTotalUnidadResponsable;
	}

	get getNomDivTotalUnidadEjecutora() {
		return this.nomDivTotalUnidadEjecutora;
	}

	setNomDivTotalUnidadEjecutora(nomDivTotalUnidadEjecutora = 0) {
		this.nomDivTotalUnidadEjecutora = nomDivTotalUnidadEjecutora;
	}

	get getNomDivTotalAnual() {
		return this.nomDivTotalAnual;
	}

	setNomDivTotalAnual(nomDivTotalAnual = 0) {
		this.nomDivTotalAnual = nomDivTotalAnual;
	}
	
	get getStatusid() {
		return this.statusid;
	}

	setStatusid(statusid = 1) {
		this.statusid = statusid;
	}

	get getNomTablaClavePresupuestalAnual() {
		return this.nomTablaClavePresupuestalAnual;
	}

	setNomTablaClavePresupuestalAnual(nomTablaClavePresupuestalAnual = '') {
		this.nomTablaClavePresupuestalAnual = nomTablaClavePresupuestalAnual;
	}

	get getNomPanelClavePresupuestalAnual() {
		return this.nomPanelClavePresupuestalAnual;
	}

	setNomPanelClavePresupuestalAnual(nomPanelClavePresupuestalAnual = '') {
		this.nomPanelClavePresupuestalAnual = nomPanelClavePresupuestalAnual;
	}

	get getConfiguracionClave() {
		return this.configuracionClave;
	}

	setConfiguracionClave(configuracionClave = 0) {
		this.configuracionClave = configuracionClave;
	}

	get getConfiguracionClaveInfo() {
		return this.configuracionClaveInfo;
	}

	setConfiguracionClaveInfo(configuracionClaveInfo = new Array()) {
		this.configuracionClaveInfo = configuracionClaveInfo;
	}

	get getNumRenglonesTotalAnual() {
		return this.numRenglonesTotalAnual;
	}

	setNumRenglonesTotalAnual(numRenglonesTotalAnual = 1) {
		this.numRenglonesTotalAnual = numRenglonesTotalAnual;
	}
}

window.objGeneralAnteproyecto = new datosGenerales(type, transno, '', '', '', '');

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');
	
	// Obtener datos Generales
	fnInfoAnteProyectoGeneral();

	objGeneralAnteproyecto.setFechaCaptura(fechaActualAde);
	objGeneralAnteproyecto.setAnio(anioActualAdecuacion);
	objGeneralAnteproyecto.setAnioAnterior(Number(anioActualAdecuacion - 1));

	// Mostrar informacion Capitulo
	fnMostrarInformacionCapitulo(objGeneralAnteproyecto.getNomTablaCapitulos, objGeneralAnteproyecto.getCapitulos );

	// Mostrar información Unidad Responsable
	fnMostrarInformacionUR(objGeneralAnteproyecto.getNomTablaUnidadResponsable, objGeneralAnteproyecto.getUr );

	// Mostrar información Unidad Ejecutora
	fnMostrarInformacionUE(objGeneralAnteproyecto.getNomTablaUnidadEjecutora, objGeneralAnteproyecto.getUe );

	if (document.querySelector(".selectConfigClave")) {
		// Obtener configuración de la clave
		fnCargarConfiguracionClave('.selectConfigClave');
	}

	if (Number(objGeneralAnteproyecto.getTransno) > Number(0)) {
		// Tiene Folio, traer informacion de la captura
		fnObtenerInformacionCaptura(objGeneralAnteproyecto.getType, objGeneralAnteproyecto.getTransno);
	}

	// Datos lista de busqueda del presupuesto anterior
	fnObtenerPresupuestoBusqueda(objGeneralAnteproyecto.getAnio);

	// Bloquear para cambiar informacion
	if (objGeneralAnteproyecto.getPerEncabezado == '0') {
		fnBloquearEncabezado();
	} else {
		fnBloquearEncabezado(false);
	}
	if (objGeneralAnteproyecto.getPerTechos == '0') {
		fnBloquearTechoPresupuestal();
	} else {
		fnBloquearTechoPresupuestal(false);
	}
	if (objGeneralAnteproyecto.getPerClaves == '0') {
		fnBloquearClavesPresupuestarias();
	}

	$("#btnAgregarClaveAnual").click(function() {
		// Agregar Clave anual
		fnAgregarClaveAnual();
	});

	$("#btnAgregarClaveAnterior").click(function() {
		// Agregar claves anteproyecto anterior
		fnMostrarClavesAnteproyectoAnterior();
	});

	$("#btnValidaciones").click(function() {
		// Validar montos de los paneles
		fnValidacionesInformacion(1);
	});

	$("#btnDividirCantidadMeses").click(function() {
		// Dividir cantidad en los 12 meses
		fnDividirCantidadMesesClaveAnual();
	});

	$("#btnCantidadMesesTotal").click(function() {
		// Sumar totales de los meses al total anual
		fnSumarTotalesMesesAnual();
	});

	$("#btnExportarClaves").click(function() {
		fnGenerarDescargarClaves();
	});

	// var accountcode = "'2018-08-I6L-1-3-04-00-001-O001-11301-1-1-09-00000000000-I6L02-00009-6030000000','2018-08-I6L-1-3-04-00-001-O001-13101-1-1-09-00000000000-I6L02-00009-6030000000','2018-08-I6L-1-3-04-00-001-O001-14403-1-1-09-00000000000-I6L00-00009-6030000000'";
	// fnObtenerPresupuesto(accountcode);
});

/**
 * Funcion para exporar las claves presupuestales
 * @return {[type]} [description]
 */
function fnGenerarDescargarClaves() {
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	// console.log("jsonInfo: "+JSON.stringify(datosAgregados));
	
	if (datosAgregados.length == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existen claves capturadas</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	var num = 1;
	var encabezado = '';
	var contenido = '';
	for (var key in datosAgregados) {
		var infoClave = '';
		var jsonInfo = datosAgregados[key].claveInfo;
		for (var key2 in jsonInfo) {
			if (num == 1) {
                // Datos de Encabezado
                if (encabezado == '') {
                    encabezado += '"'+jsonInfo[key2].nombre+'" : "'+jsonInfo[key2].nombre+'"';
                } else {
                    encabezado += ', "'+jsonInfo[key2].nombre+'" : "'+jsonInfo[key2].nombre+'"';
                }
            }

            if (infoClave == '') {
                infoClave += '"'+jsonInfo[key2].nombre+'" : "'+jsonInfo[key2].valor+'"';
            } else {
                infoClave += ', "'+jsonInfo[key2].nombre+'" : "'+jsonInfo[key2].valor+'"';
            }
		}

		if (num == 1) {
			if (encabezado == '') {
				encabezado += '"Total" : "Total"';
			} else {
				encabezado += ', "Total" : "Total"';
			}
		}

		if (infoClave == '') {
			infoClave += '"Total" : "'+datosAgregados[key].totalAnual+'"';
		} else {
			infoClave += ', "Total" : "'+datosAgregados[key].totalAnual+'"';
		}

		// Total por mes
		var jsonInfoMeses = datosAgregados[key].mesesInfo;
		var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
		for (var keymes in jsonInfoMeses) {
			for (var mes in dataJsonMeses) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				if (num == 1) {
					if (encabezado == '') {
						encabezado += '"'+nombreMes+'" : "'+nombreMes+'"';
					} else {
						encabezado += ', "'+nombreMes+'" : "'+nombreMes+'"';
					}
				}

				if (infoClave == '') {
					infoClave += '"'+nombreMes+'" : "'+jsonInfoMeses[keymes][nombreMes]+'"';
				} else {
					infoClave += ', "'+nombreMes+'" : "'+jsonInfoMeses[keymes][nombreMes]+'"';
				}
			}
		}

		if (contenido == '') {
			contenido += '{'+infoClave+'}';
		} else {
			contenido += ', {'+infoClave+'}';
		}

		num ++;
	}

	// console.log("encabezado: "+encabezado);
	// console.log("contenido: "+contenido);
	var cadenaCSV = '';
	if (encabezado != '' && contenido != '') {
		cadenaCSV = '[{'+encabezado+'}, '+contenido+']';
	} else if (encabezado != '') {
		cadenaCSV = '[{'+encabezado+'}]';
	} else if (contenido != '') {
		cadenaCSV = '['+contenido+']';
	}

	if (cadenaCSV != '') {
		var json = $.parseJSON(cadenaCSV);
		// console.log("json: "+JSON.stringify(json));
		fnGenerarCsvGeneral(json, 'ClavesAnteproyecto_'+objGeneralAnteproyecto.getTransno);
	} else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede Exportar a Excel</p>';
		muestraModalGeneral(3, titulo, mensaje);
	}
}

/**
 * Función para obtener la configuración de clave y almacenar el anteproyecto con esa configuración
 * @param  {[type]} elemento Elemento donde se va a poner la información
 * @return {[type]}          [description]
 */
function fnCargarConfiguracionClave(elemento) {
	// Agregar informacion de acuerdo a año
	dataObj = { 
		option: 'mostrarConfiguracionClave',
		year: $("#txtAnio").val()
	};
	fnSelectGeneralDatosAjax(''+elemento, dataObj, 'modelo/componentes_modelo.php');
}

function fnValidarInfoCaptura() {
	if (objGeneralAnteproyecto.getInfoAutorizadaVar == 1) {
		// Bloquear para cambiar informacion
		fnBloquearEncabezado();
		fnBloquearTechoPresupuestal();
		fnBloquearClavesPresupuestarias();
	} else {
		// Validar informacion

		// Bloquear panel de techos presupuestales - Inicio
		var dataJson = new Array();

		if (objGeneralAnteproyecto.getUtilizarUe == 1) {
			// Panel con UE
			dataJson = objGeneralAnteproyecto.getUe;
		} else {
			// Panel con UR
			dataJson = objGeneralAnteproyecto.getUr;
		}
		
		for (var key in dataJson) {
			var jsonCapitulos = dataJson[key].Capitulo;
			for (var key2 in jsonCapitulos) {
				// Recorrer los capitulos
				var nombreElemento = '';
				var bloquear = true;
				var encontroDato = 0;

				var infoAutorizada = objGeneralAnteproyecto.getInfoAutorizada;
				for (var keyInfoAuto in infoAutorizada) {
					if (objGeneralAnteproyecto.getUtilizarUe == 1) {
						// Panel con UE
						if (infoAutorizada[keyInfoAuto].ur == dataJson[key].value
							&& infoAutorizada[keyInfoAuto].ue == dataJson[key].value2
							&& infoAutorizada[keyInfoAuto].capitulo == jsonCapitulos[key2].value) {
							encontroDato = 1;
							if (infoAutorizada[keyInfoAuto].estatus == 0) {
								bloquear = false;
							}
						}
						// nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
					} else {
						// Panel con UR
						if (infoAutorizada[keyInfoAuto].ur == dataJson[key].value
							&& infoAutorizada[keyInfoAuto].capitulo == jsonCapitulos[key2].value) {
							encontroDato = 1;
							if (infoAutorizada[keyInfoAuto].estatus == 0) {
								bloquear = false;
							}
						}
						// nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
					}
				}

				if (encontroDato == 0) {
					bloquear = false;
				}
				
				if (objGeneralAnteproyecto.getUtilizarUe == 1) {
					// Panel con UE
					nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
				} else {
					// Panel con UR
					nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
				}

				if (objGeneralAnteproyecto.getPerTechos == '0') {
					// No tiene permiso techos
					bloquear = true;
				}

				$("#"+nombreElemento).prop("readonly", bloquear);
			}
		}
		// Bloquear panel de techos presupuestales - Fin
		
		// Bloquear panel de claves presupuestales - Inicio
		var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
		var num = 1;
		for (var key in datosAgregados) {
			var bloquear = true;
			var encontroDato = 0;

			var jsonInfo = datosAgregados[key].claveInfo;
			var partida = '';
			var tagref = '';
			var aux1 = '';
			for (var key2 in jsonInfo) {
				if (jsonInfo[key2].nombreCampo == 'partida_esp') {
					// Si es partida obtener informacion de captura
					partida = datosAgregados[key].claveInfo[key2].valor;
					// break;
				}
				if (jsonInfo[key2].nombreCampo == 'tagref') {
					// Si es ur obtener informacion de captura
					tagref = datosAgregados[key].claveInfo[key2].valor;
					// break;
				}
				if (jsonInfo[key2].nombreCampo == 'ln_aux1') {
					// Si es aux obtener informacion de captura
					aux1 = datosAgregados[key].claveInfo[key2].valor;
					// break;
				}
			}

			var tagClave = aux1.substring(0, 3);
	    	var ueClave = aux1.substring(3, 5);

			// console.log("partida: "+partida+" - tagref: "+tagref+" - aux1: "+aux1+" - tagClave: "+tagClave+" - ueClave: "+ueClave);
			// return true;

			var infoAutorizada = objGeneralAnteproyecto.getInfoAutorizada;
			for (var keyInfoAuto in infoAutorizada) {
				if (objGeneralAnteproyecto.getUtilizarUe == 1) {
					// Panel con UE
					if (infoAutorizada[keyInfoAuto].ur == tagref
						&& infoAutorizada[keyInfoAuto].ue == ueClave
						&& infoAutorizada[keyInfoAuto].capitulo.trim().substring(0, 1) == partida.trim().substring(0, 1)) {
						encontroDato = 1;
						if (infoAutorizada[keyInfoAuto].estatus == 0) {
							bloquear = false;
						}
					}
					// nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
				} else {
					// Panel con UR
					if (infoAutorizada[keyInfoAuto].ur == tagref
						&& infoAutorizada[keyInfoAuto].capitulo.trim().substring(0, 1) == partida.trim().substring(0, 1)) {
						encontroDato = 1;
						if (infoAutorizada[keyInfoAuto].estatus == 0) {
							bloquear = false;
						}
					}
					// nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
				}
			}

			if (encontroDato == 0) {
				bloquear = false;
			}

			if (objGeneralAnteproyecto.getPerClaves == '0') {
				// No tiene permiso
				bloquear = true;
			}

			// Boton eliminar
			var nombreInput = "btn_eliminar_"+num;
			$("#"+nombreInput).prop("disabled", bloquear);
			
			// Boton seleccionar
			nombreInput = "check_dividir_cantidad_"+num;
			$("#"+nombreInput).prop("disabled", bloquear);

			var jsonInfo = datosAgregados[key].claveInfo;
			for (var key2 in jsonInfo) {
				// Elementos de la clave
				nombreInput = "inputElementoAnual_"+num+"_"+jsonInfo[key2].nombre+"";
				$("#"+nombreInput).prop("readonly", bloquear);
			}

			// Total Anual por clave
			nombreInput = "inputTotalAnual_"+num+"";
			$("#"+nombreInput).prop("readonly", bloquear);

			// Total por mes
			var jsonInfoMeses = datosAgregados[key].mesesInfo;
			var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
			for (var keymes in jsonInfoMeses) {
				for (var mes in dataJsonMeses) {
					// Nombres de los mes
					var nombreMes = dataJsonMeses[mes];
					// Mostrar datos del mes
					nombreInput = "inputTotalMes_"+nombreMes+"_"+num+"";
					$("#"+nombreInput).prop("readonly", bloquear);
				}
			}

			nombreInput = "inputJustificacion_"+num+"";
			$("#"+nombreInput).prop("readonly", bloquear);

			num ++;
		}
		// Bloquear panel de claves presupuestales - Fin
	} 
}

/**
 * Función para bloquear o desbloquear panel de encabezado
 * @param  {Boolean} bloquear Variable para bloquear o desbloquear: true o false
 * @return {[type]}           [description]
 */
function fnBloquearEncabezado(bloquear = true) {
	// Bloquear panel encabezado
	if (objGeneralAnteproyecto.getPerEncabezado == '0') {
		// No tiene permiso
		bloquear = true;
	}
	$("#txtNumberDecimales").prop("readonly", bloquear);
	$("#txtFechaCaptura").prop("readonly", bloquear);
	$("#checkPaaas").prop("disabled", bloquear);
	$("#checkUE").prop("disabled", bloquear);
	// $("#txtAnio").prop("readonly", bloquear);
	$("#checkValidarJustificación").prop("disabled", bloquear);
	$("#txtJustificacion").prop("readonly", bloquear);
	$("#checkSoloFase").prop("disabled", bloquear);
}

/**
 * Función para bloquear o desbloquear panel de techo presupuestal
 * @param  {Boolean} bloquear Variable para bloquear o desbloquear: true o false
 * @return {[type]}           [description]
 */
function fnBloquearTechoPresupuestal(bloquear = true) {
	// Bloquear panel de techos presupuestales
	if (objGeneralAnteproyecto.getPerTechos == '0') {
		// No tiene permiso techos
		bloquear = true;
	}

	var dataJson = new Array();

	if (objGeneralAnteproyecto.getUtilizarUe == 1) {
		// Panel con UE
		dataJson = objGeneralAnteproyecto.getUe;
	} else {
		// Panel con UR
		dataJson = objGeneralAnteproyecto.getUr;
	}
	
	for (var key in dataJson) {
		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Recorrer los capitulos
			var nombreElemento = '';// 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
			// var nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
			
			if (objGeneralAnteproyecto.getUtilizarUe == 1) {
				// Panel con UE
				nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
			} else {
				// Panel con UR
				nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
			}

			$("#"+nombreElemento).prop("readonly", bloquear);
			$("#"+nombreElemento).prop("disabled", bloquear);
		}
	}
}

/**
 * Función para bloquear o desbloquear panel de claves presupuestarias
 * @param  {Boolean} bloquear Variable para bloquear o desbloquear: true o false
 * @return {[type]}           [description]
 */
function fnBloquearClavesPresupuestarias(bloquear = true) {
	// Bloquear panel de claves presupuestales
	if (objGeneralAnteproyecto.getPerClaves == '0') {
		// No tiene permiso
		bloquear = true;
	}
	
	$("#txtBuscarClavesAnual").prop("readonly", bloquear);
	$("#btnAgregarClaveAnual").prop("disabled", bloquear);
	$("#btnAgregarClaveAnterior").prop("disabled", bloquear);

	if (bloquear) {
		$('#selectConfigClave').multiselect('disable');
	} else {
		$('#selectConfigClave').multiselect('enable');
	}

	// console.log("setStatusid: "+objGeneralAnteproyecto.getStatusid);

	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var num = 1;
	for (var key in datosAgregados) {
		// Boton eliminar
		var nombreInput = "btn_eliminar_"+num;
		$("#"+nombreInput).prop("disabled", bloquear);
		
		// Boton seleccionar
		nombreInput = "check_dividir_cantidad_"+num;
		$("#"+nombreInput).prop("disabled", bloquear);

		var jsonInfo = datosAgregados[key].claveInfo;
		for (var key2 in jsonInfo) {
			// Elementos de la clave
			nombreInput = "inputElementoAnual_"+num+"_"+jsonInfo[key2].nombre+"";
			$("#"+nombreInput).prop("readonly", bloquear);
		}

		// Total Anual por clave
		nombreInput = "inputTotalAnual_"+num+"";
		if (Number(objGeneralAnteproyecto.getStatusid) >= Number(2)) {
			// Si es estatus anual o calendarizado, etc se bloque la captura del total de la clave
			$("#"+nombreInput).prop("readonly", true);
		} else{
			// Bloquear por parametro
			$("#"+nombreInput).prop("readonly", bloquear);
		}

		// Total por mes
		var jsonInfoMeses = datosAgregados[key].mesesInfo;
		var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
		for (var keymes in jsonInfoMeses) {
			for (var mes in dataJsonMeses) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				// Mostrar datos del mes
				nombreInput = "inputTotalMes_"+nombreMes+"_"+num+"";
				// $("#"+nombreInput).prop("readonly", bloquear);
				if (Number(objGeneralAnteproyecto.getStatusid) >= Number(3)) {
					// Si es estatus calendarizado, etc se bloque la captura del mes
					$("#"+nombreInput).prop("readonly", true);
				} else if (Number(objGeneralAnteproyecto.getStatusid) == Number(2)) {
					// Si es anual
					$("#"+nombreInput).prop("readonly", false);
				} else{
					// Bloquear por parametro
					$("#"+nombreInput).prop("readonly", bloquear);
				}
			}
		}

		nombreInput = "inputJustificacion_"+num+"";
		$("#"+nombreInput).prop("readonly", bloquear);

		num ++;
	}
}

/**
 * Función para bloquear o desbloquear panel de los botones
 * @param  {Boolean} bloquear Variable para bloquear o desbloquear: true o false
 * @return {[type]}           [description]
 */
function fnBloquearDivBotones(bloquear = true) {
	// Bloquear panel encabezado
	var jsonInfo = objGeneralAnteproyecto.getInfoBotones;
	for (var key in jsonInfo) {
		if (bloquear) {
			// Ocultar botones
			$("#"+jsonInfo[key].namebutton2).css("display", "none");
		} else {
			// Mostrar botones
			$("#"+jsonInfo[key].namebutton2).removeAttr("style");
		}
	}

	if (bloquear) {
		// Ocultar botones, pagina default
		$("#btnValidaciones").css("display", "none");
		$("#btnDividirCantidadMeses").css("display", "none");
		$("#btnCantidadMesesTotal").css("display", "none");
	} else {
		// Mostrar botones, pagina default
		$("#btnValidaciones").removeAttr("style");
		$("#btnDividirCantidadMeses").removeAttr("style");
		$("#btnCantidadMesesTotal").removeAttr("style");
	}
}

/**
 * Funcion para validaciones al autorizar la captura
 * @param  {[type]} statusid Estatus de autorizacion
 * @return {[type]}          [description]
 */
function fnAutorizarCapitulo(statusid) {
	// Autorizar capitulos seleccionados
	if (objGeneralAnteproyecto.getErrorValidacion == 0) {
		// Errores en información
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen errores en la información capturada</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return false;
	}

	if (objGeneralAnteproyecto.getStatusid != 3) {
		// Errores en información
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No es posible realizar la operación en el estatus actual</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return false;
	}

	// Cambiar estatus
	fnCambiarEstatusCaptura(statusid);
}

/**
 * Función para generar el csv con clave corta
 * @return {[type]} [description]
 */
function fnGenerarCsvClaveCorta() {
	// Funcion para generar csv con informacion en clave corta
	if (objGeneralAnteproyecto.getErrorValidacion == 0) {
		// Errores en información
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen errores en la información capturada</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return false;
	}

	// Almacenar informacion para despues generar el archivo csv
	fnAlmacenarCaptura(objGeneralAnteproyecto.getStatusid, 1);
}

/**
 * Función para generar el csv con clave larga
 * @return {[type]} [description]
 */
function fnGenerarCsvClaveLarga() {
	// Funcion para generar csv con informacion en clave larga
	if (objGeneralAnteproyecto.getErrorValidacion == 0) {
		// Errores en información
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen errores en la información capturada</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return false;
	}

	// Almacenar informacion para despues generar el archivo csv
	fnAlmacenarCaptura(objGeneralAnteproyecto.getStatusid, 2);
}

/**
 * Función para realizar una suma de los totales de los meses y agregarla al total anual
 * @return {[type]} [description]
 */
function fnSumarTotalesMesesAnual() {
	// Sumar los totales de los meses al total anual
	var selecciono = 0;
	for (var renglon = 1; renglon <= objGeneralAnteproyecto.getNumRenglonesTotalAnual - 1; renglon++) {
		if( $('#check_dividir_cantidad_'+renglon).prop('checked') ) {
			// Si encuentra uno seleccionado
			selecciono = 1;
		}
	}
	if (selecciono == 1) {
		var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
		var num = 1;
		for (var key in datosAgregados) {
			if( $('#check_dividir_cantidad_'+num).prop('checked') ) {
				var totalAnual = 0;
				
				// Total por mes
				var jsonInfoMeses = datosAgregados[key].mesesInfo;
				var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
				for (var keymes in jsonInfoMeses) {
					for (var mes in dataJsonMeses) {
						// Nombres de los mes
						var nombreMes = dataJsonMeses[mes];
						// Mostrar datos del mes
						var nombreInput = "inputTotalMes_"+nombreMes+"_"+num+"";
						totalAnual = parseFloat(totalAnual) + parseFloat($("#"+nombreInput).val());
					}
				}

				datosAgregados[key].totalAnual = totalAnual;

				var nombreInput = "inputTotalAnual_"+num+"";
				$("#"+nombreInput).val(totalAnual);
			}
			num ++;
		}

		// Asignar nueva información
		objGeneralAnteproyecto.setInfoClavesAnual(datosAgregados);
		fnGuardarSeleccionadoClaveAnual('');

		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se llevó a cabo el cálculo del importe anual en las claves presupuestarias seleccionadas</p>';
		muestraModalGeneral(3, titulo, mensaje);
	} else {
		// No selecciono ninguno
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No tiene seleccionado ningún registro</p>';
		muestraModalGeneral(3, titulo, mensaje);
	}
}

/**
 * Función para la cantidad del total dividirla en los 12 meses
 * la difrencia la suma al ultimo mes
 * @return {[type]} [description]
 */
function fnDividirCantidadMesesClaveAnual() {
	// Dividir cantidad anual en los meses
	var selecciono = 0;
	for (var renglon = 1; renglon <= objGeneralAnteproyecto.getNumRenglonesTotalAnual - 1; renglon++) {
		if( $('#check_dividir_cantidad_'+renglon).prop('checked') ) {
			// Si encuentra uno seleccionado
			selecciono = 1;
		}
	}

	if (selecciono == 1) {
		// Realizar dividir total en meses
		var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
		var num = 1;
		for (var key in datosAgregados) {
			if( $('#check_dividir_cantidad_'+num).prop('checked') ) {
				// Si selecciono divicion
				var nombreInput = "inputTotalAnual_"+num+"";
				var totalAnual = $("#"+nombreInput).val();
				// Operaciones
				var totalmes = Number( Math.trunc( totalAnual / 12) );
				var ultimoMesDif = Number(totalAnual) - Number(totalmes * 12);
				
				// Total por mes
				var jsonInfoMeses = datosAgregados[key].mesesInfo;
				var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
				for (var keymes in jsonInfoMeses) {
					var numMes = 1;
					for (var mes in dataJsonMeses) {
						// Nombres de los mes
						var nombreMes = dataJsonMeses[mes];
						// Mostrar datos del mes
						var nombreInput = "inputTotalMes_"+nombreMes+"_"+num+"";
						if (numMes == 12) {
							// Si es diciembre poner diferencia
							totalmes = Number(totalmes) + Number(ultimoMesDif);
							datosAgregados[key].mesesInfo[keymes][nombreMes] = totalmes;
							$("#"+nombreInput).val(totalmes);
						} else {
							// Solo el total por por mes
							datosAgregados[key].mesesInfo[keymes][nombreMes] = totalmes;
							$("#"+nombreInput).val(totalmes);
						}
						numMes ++;
					}
				}
			}
			num ++;
		}

		// Asignar nueva información
		objGeneralAnteproyecto.setInfoClavesAnual(datosAgregados);
		fnGuardarSeleccionadoClaveAnual('');

		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Se llevó a cabo la calendarización a doceavas partes en las claves presupuestarias seleccionadas</p>';
		muestraModalGeneral(3, titulo, mensaje);
	} else {
		// No selecciono ninguno
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No tiene seleccionado ningún registro</p>';
		muestraModalGeneral(3, titulo, mensaje);
	}
}

/**
 * Función para mostrar las claves del anteproyecto anterior
 * @return {[type]} [description]
 */
function fnMostrarClavesAnteproyectoAnterior() {
	// Agregar claves anteproyecto anterior
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = '';//'<div style="overflow-x:scroll;overflow-y:scroll;height:250px;">';
	mensaje += '<p><input type="checkbox" id="check_filtro_todos" name="check_filtro_todos" onchange="fnSeleccionarTodoModal(this)" /> Seleccionar Todos</p>';
	var jsonData = objGeneralAnteproyecto.getInfoClavesBusqueda;
	for (var key in jsonData) {
		mensaje += '<p><input type="checkbox" id="check_filtro_'+jsonData[key].accountcode+'" name="check_filtro_'+jsonData[key].accountcode+'" /> '+jsonData[key].accountcode+'</p>';
	}
	// mensaje += '</div>';
	muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnAgregarClavesModal()");
}

/**
 * Función para seleccionar los check de las claves del anteproyecto
 * @param  {[type]} checkbox Elemento html
 * @return {[type]}          [description]
 */
function fnSeleccionarTodoModal(checkbox) {
	var jsonData = objGeneralAnteproyecto.getInfoClavesBusqueda;
	if( $('#'+checkbox.name).prop('checked') ) {
		for(var key in jsonData) {
			$('#check_filtro_'+jsonData[key].accountcode).prop('checked',true);
		}
	}else{
		for(var key in jsonData) {
			$('#check_filtro_'+jsonData[key].accountcode).prop('checked',false);
		}
	}
}

/**
 * Función para obtener los check seleccionados, obtener información para mostrar en pantalla
 * @return {[type]} [description]
 */
function fnAgregarClavesModal() {
	var accountcode = "";
	var jsonData = objGeneralAnteproyecto.getInfoClavesBusqueda;
	// Obtener datos que se van a agregar
	for(var key in jsonData) {
		if( $('#check_filtro_'+jsonData[key].accountcode).prop('checked') ) {
			// Guardar datos seleccionados
			if (accountcode == '') {
				accountcode = "'"+jsonData[key].accountcode+"'";
			} else {
				accountcode += ", '"+jsonData[key].accountcode+"'";
			}
		}
	}
	// console.log("accountcode: "+accountcode);
	if (accountcode != '') {
		// Si selecciono claves
		fnObtenerPresupuesto(accountcode);
	}
}

/**
 * Función para obtener la información de una clave del presupuesto anterior
 * @param  {String} accountcode Clave o claves para obtener la información, ejemplo: "'clave', 'clave'"
 * @return {[type]}             [description]
 */
function fnObtenerPresupuesto(accountcode = '') {
	// Obtener información de la clave seleccionada
	// console.log("accountcode: "+accountcode);
	if (fnValidarClave(objGeneralAnteproyecto.getInfoClavesAnual, accountcode)) {
		// Si no existe agregar información
		dataObj = { 
		        option: 'obtenerPresupuesto',
		        clave: accountcode
		      };
		$.ajax({
			async:false,
			cache:false,
			method: "POST",
			dataType:"json",
			url: "modelo/anteproyectoModelo.php",
			data:dataObj
		  })
		.done(function( data ) {
		    if(data.result){
		    	//Si trae informacion
		    	var dataJson = data.contenido.datos;
		    	for (var key in dataJson) {
		    		var obj = new Object();
					obj.accountcode = dataJson[key].accountcode;
					obj.claveInfo = dataJson[key].claveInfo;
					obj.mesesInfo = dataJson[key].mesesInfo;
					obj.totalAnual = dataJson[key].totalAnual;
					obj.justificacion = dataJson[key].justificacion;

					// Agregar informacion creada
					var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
					datosAgregados.push(obj);
					objGeneralAnteproyecto.setInfoClavesAnual(datosAgregados);

					// Enviar informacion a visializar
					var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
					fnMuestraClaveAnual(datosAgregados[datosAgregados.length - 1]);
		    	}

		    	fnGuardarSeleccionadoClaveAnual('');
		    	//ocultaCargandoGeneral();
		    }else{
		    	//ocultaCargandoGeneral();
		    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>'+data.Mensaje+'</p>';
				muestraModalGeneral(3, titulo, mensaje);
		    }
		})
		.fail(function(result) {
			//ocultaCargandoGeneral();
			console.log("ERROR");
		    console.log( result );
		});
	}
}

/**
 * Función para validar si ya existe la clave seleccionada
 * @param  {[type]} dataJson Información con las claves seleccionadas
 * @param  {[type]} clave    Clave que se quiere agregar
 * @return {[type]}          [description]
 */
function fnValidarClave(dataJson, clave) {
	// Validar que la clave no exista en los registros
	var clave = clave.replace(objGeneralAnteproyecto.getAnioAnterior, objGeneralAnteproyecto.getAnio);
	var num = 1;
	for (var key in dataJson) {
		if (dataJson[key].accountcode == clave) {
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ya existe la clave en la línea '+num+'</p>';
			muestraModalGeneral(3, titulo, mensaje);
			return false;
		}
		num ++;
	}
	
	return true;
}

/**
 * Función para obtener información del presupuesto anterior
 * @return {[type]} [description]
 */
function fnObtenerPresupuestoBusqueda(year = 0) {
	// Traer claves presupuestales para filtro
	dataObj = { 
	        option: 'obtenerPresupuestosBusqueda',
	        year: year
	      };
    $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/anteproyectoModelo.php",
      data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
        	objGeneralAnteproyecto.setInfoClavesBusqueda(data.contenido.datos);
            fnBusquedaInput(objGeneralAnteproyecto.getInfoClavesBusqueda);
        }else{
        	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema para cargar claves del presupuesto anterior</p>';
			muestraModalGeneral(3, titulo, mensaje);
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

/**
 * Función para desplegar lista con información en la caja de busqueda
 * del presupuesto anterior
 * @param  {[type]} jsonData Información con las claves
 * @return {[type]}          [description]
 */
function fnBusquedaInput(jsonData) {
	// Función para desplegar lista en la caja de busqueda
	$( "#txtBuscarClavesAnual").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            $( this ).val( ui.item.accountcode + "");
            //$( "#txtBuscarClavesAnual" ).val( ui.item.accountcode );
            $( "#txtBuscarClavesAnual" ).val( "" );
            fnObtenerPresupuesto("'"+ui.item.accountcode+"'");
            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( "<a>" + item.valorLista + "</a>" )
		.appendTo( ul );
    };
}

/**
 * Función para actualizar información que cambio
 * @param  {[type]} input      Elemento html con la información
 * @param  {[type]} numRenglon Número de renglon que cambio
 * @return {[type]}            [description]
 */
function fnGuardarSeleccionadoClaveAnual(input = '', numRenglon = 0) {
	// Guardar dato que cambio
	// console.log("nombre: "+input.name+" - valor: "+input.value);
	// console.log("nombre: "+input.name+" - valor2: "+input.value.toUpperCase());
	// console.log("numRenglon: "+numRenglon);
	// console.log("info antes: "+JSON.stringify(objGeneralAnteproyecto.getInfoClavesAnual));
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var num = 1;
	var total = 0;
	for (var key in datosAgregados) {
		// if (Number(num) == Number(numRenglon)) {
			// Si es mismo regisro actualizar dato
			var jsonInfo = datosAgregados[key].claveInfo;
			var accountcode = '';
			for (var key2 in jsonInfo) {
				var nombreInputClaveElemento = "inputElementoAnual_"+num+"_"+jsonInfo[key2].nombre+"";
				if (input.name == nombreInputClaveElemento) {
					// Encontro campo de clave
					// console.log("campo: "+jsonInfo[key2].nombre);
					datosAgregados[key].claveInfo[key2].valor = input.value.toUpperCase();
					$("#"+nombreInputClaveElemento).val(""+input.value.toUpperCase());
				}

				if (jsonInfo[key2].nombreCampo == 'anho') {
					// Si es año mostrar el del anteproyecto
					datosAgregados[key].claveInfo[key2].valor = objGeneralAnteproyecto.getAnio;
				}

				if (accountcode == '') {
					accountcode = datosAgregados[key].claveInfo[key2].valor;
				} else {
					accountcode = accountcode + '-' + datosAgregados[key].claveInfo[key2].valor;
				}
			}

			datosAgregados[key].accountcode = accountcode;

			// Total Anual por clave
			var nombreInput = "inputTotalAnual_"+num+"";
			if (input.name == nombreInput) {
				// Si cambio el total
				datosAgregados[key].totalAnual = input.value;
			}

			// Total por mes
			var jsonInfoMeses = datosAgregados[key].mesesInfo;
			var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
			var totalMes = 0;
			for (var keymes in jsonInfoMeses) {
				for (var mes in dataJsonMeses) {
					// Nombres de los mes
					var nombreMes = dataJsonMeses[mes];
					// Mostrar datos del mes
					var nombreInput = "inputTotalMes_"+nombreMes+"_"+num+"";
					datosAgregados[key].mesesInfo[keymes][nombreMes] = $("#"+nombreInput).val();

					totalMes = parseFloat(totalMes) + parseFloat($("#"+nombreInput).val());
				}
			}

			var nombreInput = "inputJustificacion_"+num+"";
			if (input.name == nombreInput) {
				// Si cambio el total
				datosAgregados[key].justificacion = input.value;
			}
		// }

		if (Number(objGeneralAnteproyecto.getStatusid) == Number(2) || Number(objGeneralAnteproyecto.getUtilizarSoloUnaFase) == 1) {
			// Total por calendario
			total = parseFloat(total) + parseFloat(totalMes);
		} else {
			// Total de claves anual
			total = parseFloat(total) + parseFloat(datosAgregados[key].totalAnual);
		}

		num ++;
	}

	objGeneralAnteproyecto.setInfoClavesAnual(datosAgregados);
	objGeneralAnteproyecto.setTotalClaveAnual(total);
	// console.log("total anual: "+objGeneralAnteproyecto.getTotalClaveAnual);
	// console.log("info despues: "+JSON.stringify(objGeneralAnteproyecto.getInfoClavesAnual));
	
	// Mostrar total
	fnMostrarTotalFormato(objGeneralAnteproyecto.getNomDivTotalAnual, objGeneralAnteproyecto.getTotalClaveAnual);

	// Validaciones para generar información
	// fnValidacionesInformacion();
	objGeneralAnteproyecto.setErrorValidacion(0);
}

/**
 * Función para agregar registro para clave anual
 * @return {[type]} [description]
 */
function fnAgregarClaveAnual() {
	// Funcion para agregar un renglon a la claves Anuales
	if (objGeneralAnteproyecto.getConfiguracionClave == 0) {
		// No tiene seleccionado configuracion de la clave
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar Configuración de Clave Presupuestal</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return false;
	}

	var datosConfigClave = objGeneralAnteproyecto.getConfiguracionClaveInfo;
	for (var key in datosConfigClave) {
		datosConfigClave[key].valor = "";
	}

	var obj = new Object();
	obj.accountcode = '';
	// obj.claveInfo = objGeneralAnteproyecto.getConfiguracionClaveInfo;
	obj.claveInfo = datosConfigClave;
	// Datos meses
	var objMeses = new Object();
	objMeses.Enero = 0;
	objMeses.Febrero = 0;
	objMeses.Marzo = 0;
	objMeses.Abril = 0;
	objMeses.Mayo = 0;
	objMeses.Junio = 0;
	objMeses.Julio = 0;
	objMeses.Agosto = 0;
	objMeses.Septiembre = 0;
	objMeses.Octubre = 0;
	objMeses.Noviembre = 0;
	objMeses.Diciembre = 0;
	var datosMeses = new Array();
	datosMeses.push(objMeses);
	obj.mesesInfo = datosMeses;
	obj.totalAnual = 0;
	obj.justificacion = '';
	
	// Agregar informacion creada
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	datosAgregados.push(obj);
	objGeneralAnteproyecto.setInfoClavesAnual(datosAgregados);
	
	// Enviar informacion a visializar
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	fnMuestraClaveAnual(datosAgregados[datosAgregados.length - 1]);
}

/**
 * Función para eliminar el registro seleccionado
 * @param  {String} accountcode [description]
 * @return {[type]}             [description]
 */
function fnEliminarClaveAnual(numRenglon = '', sinConfirmacion = 0) {
	// Eliminar registro selecccionado
	if (sinConfirmacion == 0) {
		// Mensaje de confirmación para eliminar
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar el renglón '+numRenglon+'</p>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnEliminarClaveAnual(\''+numRenglon+'\',\'1\')');
		return false;
	}

	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var num = 1;
	for (var key in datosAgregados) {
		if (num == numRenglon) {
			// Si es el número de renglón eliminar información
			datosAgregados.splice(key, 1);
			break;
		}
		num ++;
	}
	// Agregar informacion
	objGeneralAnteproyecto.setInfoClavesAnual(datosAgregados);
	objGeneralAnteproyecto.setNumRenglonesTotalAnual(1);

	// Recargar datos con información
	$('#'+objGeneralAnteproyecto.getNomTablaClavePresupuestalAnual+' tbody').empty();
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	for (var key in datosAgregados) {
		// Mostrar información
		fnMuestraClaveAnual(datosAgregados[key]);
	}

	fnGuardarSeleccionadoClaveAnual('');

	fnValidarInfoCaptura();
}

/**
 * Función para mostrar el registro agregado a la tabla de claves anuales
 * @return {[type]} [description]
 */
function fnMuestraClaveAnual(jsonInfoMostrar = new Array()) {
	// Muestra claves con total anual
	var encabezado = '';
	var contenido = '';
	var jsonInfo = jsonInfoMostrar.claveInfo;
	var jsonInfoMeses = jsonInfoMostrar.mesesInfo;
	var panel = 1;
	var style = 'style="text-align:center;"';

	var numRenglon = objGeneralAnteproyecto.getNumRenglonesTotalAnual;
	
	if (numRenglon == 1) {
		encabezado += '<tr class="header-verde"><td></td><td></td><td></td>';
	}

	contenido += '<td '+style+'>'+numRenglon+'</td>';
	contenido += '<td '+style+'><button id="btn_eliminar_'+numRenglon+'" name="btn_eliminar_'+numRenglon+'" class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnEliminarClaveAnual(\''+numRenglon+'\')"></button></td>';
	contenido += '<td '+style+'>';
	contenido += '<input type="checkbox" id="check_dividir_cantidad_'+numRenglon+'" name="check_dividir_cantidad_'+numRenglon+'" /> ';
	contenido += '</td>';

	for (var key2 in jsonInfo) {
		var valor = jsonInfo[key2].valor;
		if (jsonInfo[key2].nombreCampo == 'anho') {
			// Año del ante proyecto
			style = 'style="display:none;"';
		} else {
			style = 'style="text-align:center;"';
		}

		if (numRenglon == 1) {
			encabezado += '<td '+style+'>'+jsonInfo[key2].nombre+'</td>';
		}

		var nombreInputClaveElemento = "inputElementoAnual_"+numRenglon+"_"+jsonInfo[key2].nombre+"";
		contenido += '<td '+style+'>';
		contenido += '<input type="text" class="form-control" name="'+nombreInputClaveElemento+'" id="'+nombreInputClaveElemento+'" value="'+valor+'" style="width: 80px;" onchange="fnGuardarSeleccionadoClaveAnual(this, \''+numRenglon+'\')" />';
		contenido += '</td>';
	}

	var nombreInputClaveTotal = "inputTotalAnual_"+numRenglon+"";
	valor = jsonInfoMostrar.totalAnual;
	contenido += '<td><input type="text" min="0" class="form-control" name="'+nombreInputClaveTotal+'" id="'+nombreInputClaveTotal+'" value="'+Math.abs(valor)+'" style="width: 80px;" onkeypress="return soloNumeros(event)" onchange="fnCambioCantidadValidar(this), fnGuardarSeleccionadoClaveAnual(this, \''+numRenglon+'\')" /></td>';

	if (numRenglon == 1) {
		encabezado += '<td '+style+'>Total</td>';
	}

	var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
	for (var key in jsonInfoMeses) {
		for (var mes in dataJsonMeses) {
			// Nombres de los mes
			var nombreMes = dataJsonMeses[mes];
			if (numRenglon == 1) {
				encabezado += '<td '+style+'>'+nombreMes+'</td>';
			}
			// Mostrar datos del mes
			var nombreInputClaveTotal = "inputTotalMes_"+nombreMes+"_"+numRenglon+"";
			valor = jsonInfoMeses[key][nombreMes];
			contenido += '<td><input type="text" min="0" class="form-control" name="'+nombreInputClaveTotal+'" id="'+nombreInputClaveTotal+'" value="'+Math.abs(valor)+'" style="width: 80px;" onkeypress="return soloNumeros(event)" onchange="fnCambioCantidadValidar(this), fnGuardarSeleccionadoClaveAnual(this, \''+numRenglon+'\')" /></td>';
			// console.log(nombreMes+": "+jsonInfoMeses[key][nombreMes]);
		}
	}

	var nombreInputClaveTotal = "inputJustificacion_"+numRenglon+"";
	valor = jsonInfoMostrar.justificacion;
	contenido += '<td>';
	contenido += '<textarea name="'+nombreInputClaveTotal+'" id="'+nombreInputClaveTotal+'" style="width: 160px;" cols="10" rows="1" value="'+valor+'" onchange="fnGuardarSeleccionadoClaveAnual(this, \''+numRenglon+'\')" class="form-control"></textarea>';
	contenido += '</td>';

	if (numRenglon == 1) {
		encabezado += '<td '+style+'>Justificación</td>';
		encabezado += '</tr>';
	}

	contenido = encabezado + '<tr>' + contenido + '</tr>';

	numRenglon ++;
	
	objGeneralAnteproyecto.setNumRenglonesTotalAnual(numRenglon);

	$('#'+objGeneralAnteproyecto.getNomTablaClavePresupuestalAnual+' tbody').append(contenido);

	$('#'+nombreInputClaveTotal).val(''+jsonInfoMostrar.justificacion);
}

/**
 * Función para validar información de captura
 * @return {[type]} [description]
 */
function fnValidacionesInformacion(mostrarValidaciones = 0) {
	// Funcion para validar informacion de captura
	var mensaje = '';
	var totalPanel = 0;
	
	if (objGeneralAnteproyecto.getUtilizarUe == 1) {
		// Total de la ue
		totalPanel = objGeneralAnteproyecto.getTotalUe;
	} else {
		// Total de la ur
		totalPanel = objGeneralAnteproyecto.getTotalUr;
	}

	if (Number(totalPanel) == Number(objGeneralAnteproyecto.getTotalClaveAnual)) {
		// Si son iguales no hay errores en totales
		objGeneralAnteproyecto.setErrorValidacion(1);
		mensaje += '<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> Totales iguales</p>';
	} else{
		// Existen diferencias en los totales
		objGeneralAnteproyecto.setErrorValidacion(0);
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen diferencias en los Totales</p>';
	}

	// Validar total por capitulo en entre el panel de capuitulos y unidad responsable
	// mensaje += fnValidarTotalCapituloPanelUr();

	// Validar total por unidad responsable entre el panel de unidad responsable y unidad ejecutora
	// mensaje += fnValidarTotalUrPanelUe();

	if (Number(objGeneralAnteproyecto.getInfoClavesAnual.length) > Number(0)) {
		// Tiene registros en clave presupuestal anual
		
		// Validar montos ur
		mensaje += fnValidarTotalUr();

		// Validar montos capitulo
		mensaje += fnValidarTotalCapitulo();

		if (objGeneralAnteproyecto.getUtilizarUe == 1) {
			// Validar montos por ue
			mensaje += fnValidarTotalUe();
		}

		// Validar montos de los meses con el total anual
		mensaje += fnValidarTotalesAnualMeses();

		mensaje += fnValidarDatosClavePresupuestal();

		if (objGeneralAnteproyecto.getValidarJustificacion == 1) {
			// Validar que los registros de la clave tengan justificacion
			mensaje += fnValidarJustificacionClaves();
		}

		mensaje += fnValidarMismaClave();

		var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
		var num = 1;
		for (var key in datosAgregados) {
			var estProgramatica = fnGenerarEstructuraClaveNueva(datosAgregados[key].claveInfo, 'nu_programatica', 'nu_programatica_orden', '-');
			var estEconomica = fnGenerarEstructuraClaveNueva(datosAgregados[key].claveInfo, 'nu_economica', 'nu_economica_orden', '-');
			var estAdministrativa = fnGenerarEstructuraClaveNueva(datosAgregados[key].claveInfo, 'nu_administrativa', 'nu_administrativa_orden', '-');
			var estPartida = fnGenerarEstructuraClaveNueva(datosAgregados[key].claveInfo, 'nu_relacion_partida', 'nu_relacion_partida_orden', '-');
			// console.log("estProgramatica: "+estProgramatica);
			// console.log("estEconomica: "+estEconomica);
			// console.log("estAdministrativa: "+estAdministrativa);
			// console.log("estPartida: "+estPartida);

			var respuesta = fnValidarEstructuraClaveNueva(estProgramatica, estEconomica, estAdministrativa, estPartida, datosAgregados[key].claveInfo, 'En el renglón '+num);
			if (respuesta != 1) {
				// Si tiene error mostrar
				objGeneralAnteproyecto.setErrorValidacion(0);
				mensaje += respuesta;
			}

			num ++;
		}
	} else {
		// Si no tiene claves anuales
		objGeneralAnteproyecto.setErrorValidacion(0);
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se han agregado claves presupuestales</p>';
	}

	if (mostrarValidaciones == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(4, titulo, mensaje);
	}
}

function fnValidarMismaClave() {
	// Función para validar que no exista la clave presupuestal dos veces
	var mensaje = "";

	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var datosAgregadosComparaciones = objGeneralAnteproyecto.getInfoClavesAnual;
	var num = 1;
	for (var key in datosAgregados) {
		// accountcode
		var num2 = 1;
		for (var key2 in datosAgregadosComparaciones) {
			if ((datosAgregados[key].accountcode == datosAgregadosComparaciones[key2].accountcode)
				&& num != num2) {
				// Si encuentra datos repetidos
				objGeneralAnteproyecto.setErrorValidacion(0);
				datosAgregadosComparaciones.splice(key2, 1);
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existe la misma clave en los renglones '+num+' y '+num2+'</p>';
			}
			num2 ++;
		}

		num ++;
	}

	return mensaje;
}

/**
 * Función para validar que la captura sea de acuerdo a lo que se tiene asignado
 * @return Mensaje con contenido html si encuentra errores
 */
function fnValidarDatosClavePresupuestal() {
	// Si trae informacion, validar que las partidas sean a las que se tiene acceso
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var dataJsonCapitulos = objGeneralAnteproyecto.getCapitulos;
	var dataJsonUe = objGeneralAnteproyecto.getUe;
	var dataJsonPartidasEsp = objGeneralAnteproyecto.getPartidasEsp;
	var numRenglon = 1;
	var errores = 0;
	var mensaje = '';
	for (var key in datosAgregados) {
		var jsonInfo = datosAgregados[key].claveInfo;
		var partida = '';
		var tagref = '';
		var aux1 = '';
		var nombreElementoUr = '';
		for (var key2 in jsonInfo) {
			if (jsonInfo[key2].nombreCampo == 'partida_esp') {
				// Si es partida obtener informacion de captura
				partida = datosAgregados[key].claveInfo[key2].valor;
				// break;
			}
			if (jsonInfo[key2].nombreCampo == 'tagref') {
				// Si es ur obtener informacion de captura
				tagref = datosAgregados[key].claveInfo[key2].valor;
				nombreElementoUr = datosAgregados[key].claveInfo[key2].nombre;
				// break;
			}
			if (jsonInfo[key2].nombreCampo == 'ln_aux1') {
				// Si es aux obtener informacion de captura
				aux1 = datosAgregados[key].claveInfo[key2].valor;
				// break;
			}
		}
		// console.log("partida: "+partida+" - tagref: "+tagref+" - aux1: "+aux1);
		// Recorrer capitulos a los que se tiene acceso
		var encontro = 0;
		for (var key2 in dataJsonCapitulos) {
			if (dataJsonCapitulos[key2].value.trim().substring(0, 1) == partida.trim().substring(0, 1)) {
				// Encontro captura en configuracion
				encontro = 1;
			}
		}

		var encontroPartida = 0;
		for (var key2 in dataJsonPartidasEsp) {
			if (dataJsonPartidasEsp[key2].value.trim() == partida.trim()) {
				// Encontro captura en configuracion
				encontroPartida = 1;
			}
		}

		if (isEmpty(partida.trim())) {
			// Partida vacía
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene capturada la partida</p>';
		} else if (encontro == 0) {
			// No encontro captura en configuracion
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene acceso al capítulo agregado</p>';
		}

		if (encontroPartida == 0) {
			// No encontro captura en configuracion
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene acceso a la partida agregada</p>';
		}

		// Recorrer unidades responsables y unidades ejecutoras para saber si tiene acceso
		var encontro = 0;
		var encontro2 = 0;
		var tagClave = aux1.substring(0, 3);
    	var ueClave = aux1.substring(3, 5);
		for (var key2 in dataJsonUe) {
			// var nombreElemento = 'UE_'+dataJsonUe[key2].value+'_'+dataJsonUe[key2].value2+'_'+jsonCapitulos[key2].value;
			if (dataJsonUe[key2].value == tagref) {
				// Encontro captura en configuracion
				encontro = 1;
			}
			var tag = dataJsonUe[key2].value;
    		var ue = dataJsonUe[key2].value2;
			if (tag == tagClave && ue == ueClave) {
				// Encontro captura en configuracion
				encontro2 = 1;
			}
		}
		
		if (tagref.trim() == '' || tagref.trim() == null || tagref.trim() == 'undefined') {
			// Partida vacía
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene capturada la unidad responsable</p>';
		} else if (encontro == 0) {
			// No encontro captura en configuracion
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene acceso a la unidad responsable agregada</p>';
		}
		
		if (aux1.trim() == '' || aux1.trim() == null || aux1.trim() == 'undefined') {
			// Partida vacía
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene capturado el auxiliar 1</p>';
		} else if (encontro2 == 0) {
			// No encontro captura en configuracion
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene acceso al auxiliar 1 capturado</p>';
		}

		var infoAutorizada = objGeneralAnteproyecto.getInfoAutorizada;
		for (var keyInfoAuto in infoAutorizada) {
			if (objGeneralAnteproyecto.getUtilizarUe == 1) {
				// Panel con UE
				if (infoAutorizada[keyInfoAuto].ur == tagref
					&& infoAutorizada[keyInfoAuto].ue == ueClave
					&& infoAutorizada[keyInfoAuto].capitulo.trim().substring(0, 1) == partida.trim().substring(0, 1)) {
					if (infoAutorizada[keyInfoAuto].estatus == 1) {
						errores = 1;
						var nombreInput = "inputElementoAnual_"+numRenglon+"_"+nombreElementoUr+"";
						if( !$('#'+nombreInput).prop('readonly') ) {
							mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' existe información no disponible. UR: '+tagref+', UE: '+ueClave+', Capítulo: '+partida.trim().substring(0, 1)+'</p>';
						}
					}
				}
			} else {
				// Panel con UR
				if (infoAutorizada[keyInfoAuto].ur == tagref
					&& infoAutorizada[keyInfoAuto].capitulo.trim().substring(0, 1) == partida.trim().substring(0, 1)) {
					if (infoAutorizada[keyInfoAuto].estatus == 1) {
						errores = 1;
						var nombreInput = "inputElementoAnual_"+numRenglon+"_"+nombreElementoUr+"";
						if( !$('#'+nombreInput).prop('readonly') ) {
							mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' existe información no disponible. UR: '+tagref+', Capítulo: '+partida.trim().substring(0, 1)+'</p>';
						}
					}
				}
			}
		}

		numRenglon ++;
	}

	return mensaje;
}

/**
 * Función para validar que la captura sea de acuerdo a lo que se tiene asignado
 * @return Mensaje con contenido html si encuentra errores
 */
function fnValidarJustificacionClaves() {
	// Si trae informacion, validar que las partidas sean a las que se tiene acceso
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var numRenglon = 1;
	var errores = 0;
	var mensaje = '';
	for (var key in datosAgregados) {
		var justificacion = datosAgregados[key].justificacion;

		if (justificacion.trim() == '' || justificacion.trim() == null || justificacion.trim() == 'undefined') {
			// Partida vacía
			errores = 1;
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> En el renglón '+numRenglon+' no tiene capturada la Justificación</p>';
		}

		numRenglon ++;
	}

	return mensaje;
}

/**
 * Función para validar que la suma de los meses sea igual al total anual
 * @return {[type]} Mensaje con contenido html para mostrar si hay errores
 */
function fnValidarTotalesAnualMeses() {
	// Validar montos de los meses con el total anual
	var mensaje = '';
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	var num = 1;
	for (var key in datosAgregados) {
		// Total por mes
		var totalAnual = 0;
		var jsonInfoMeses = datosAgregados[key].mesesInfo;
		var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
		for (var keymes in jsonInfoMeses) {
			for (var mes in dataJsonMeses) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				// Mostrar datos del mes
				totalAnual = parseFloat(totalAnual) + parseFloat(datosAgregados[key].mesesInfo[keymes][nombreMes]);
			}
		}

		if (Number(datosAgregados[key].totalAnual) != Number(totalAnual)
			&& 
			(
				Number(objGeneralAnteproyecto.getStatusid) == Number(2)
				||
				Number(objGeneralAnteproyecto.getUtilizarSoloUnaFase) == 1
			)
				) {
			// Si son diferentes
			objGeneralAnteproyecto.setErrorValidacion(0);
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
			mensaje += ' El renglón '+num+' tiene diferencias en los totales.';
			mensaje += ' Total anual $ '+fixDecimales( redondeaDecimal(datosAgregados[key].totalAnual)+"" );
			mensaje += ' - Total Calendarizado $ '+fixDecimales( redondeaDecimal(totalAnual)+"" );
			mensaje += '</p>';
		} else if (Number(datosAgregados[key].totalAnual) == Number(0) || Number(totalAnual) == Number(0)) {
			// Si no tiene informacion capturada
			objGeneralAnteproyecto.setErrorValidacion(0);
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
			mensaje += ' El renglón '+num+' no tiene totales capturados';
			mensaje += '</p>';
		}

		num ++;
	}

	return mensaje;
}

/**
 * Función para validar el total por unidad ejecutora
 * Entre el panel del techo presupuestal y claves presupuestarias agregadas
 * @return {[type]} Mensaje con contenido html de error
 */
function fnValidarTotalUe() {
	// Validar montos por los techos presupuestales
	var mensaje = '';
	var jsonAgrupado = new Array();
	var dataJson = objGeneralAnteproyecto.getUe;
	// console.log("dataJson: "+JSON.stringify(dataJson));
	
	for (var key in dataJson) {
		var exiteDato = 0;
		for (var keyUr in jsonAgrupado) {
			if (jsonAgrupado[keyUr].value == dataJson[key].value && jsonAgrupado[keyUr].value2 == dataJson[key].value2) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupado[keyUr].select = parseFloat(jsonAgrupado[keyUr].select) + parseFloat(dataJson[key].select);
				break;
			}
		}

		if (exiteDato == 0) {
			// Agregar informacion
			var obj = new Object();
			obj.value = dataJson[key].value;
			obj.texto = dataJson[key].texto;
			obj.value2 = dataJson[key].value2;
			obj.texto2 = dataJson[key].texto2;
			obj.select = dataJson[key].select;
			jsonAgrupado.push(obj);
		}
	}
	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));
	
	// Agrupar sumas por UR de las claves presupuestales
	var jsonAgrupadoAnual = new Array();
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	for (var key in datosAgregados) {
		var jsonInfo = datosAgregados[key].claveInfo;
		var datoAgrupacion = '';
		var datoAgrupacion2 = '';
		for (var key2 in jsonInfo) {
			if (jsonInfo[key2].nombreCampo == 'tagref') {
				// Si es año mostrar el del anteproyecto
				datoAgrupacion = datosAgregados[key].claveInfo[key2].valor;
				// break;
			}
			if (jsonInfo[key2].nombreCampo == 'ln_aux1') {
				// Si es año mostrar el del anteproyecto
				datoAgrupacion2 = datosAgregados[key].claveInfo[key2].valor;
				// break;
			}
		}

		// Total por mes
		var totalAnual = 0;
		var jsonInfoMeses = datosAgregados[key].mesesInfo;
		var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
		for (var keymes in jsonInfoMeses) {
			for (var mes in dataJsonMeses) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				// Mostrar datos del mes
				totalAnual = parseFloat(totalAnual) + parseFloat(datosAgregados[key].mesesInfo[keymes][nombreMes]);
			}
		}

		var exiteDato = 0;
		for (var keyUr in jsonAgrupadoAnual) {
			if (jsonAgrupadoAnual[keyUr].value == datoAgrupacion && jsonAgrupadoAnual[keyUr].value2 == datoAgrupacion2) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupadoAnual[keyUr].select = parseFloat(jsonAgrupadoAnual[keyUr].select) + parseFloat(totalAnual);
				break;
			}
		}

		if (exiteDato == 0) {
			// Agregar informacion
			var obj = new Object();
			obj.value = datoAgrupacion;
			obj.value2 = datoAgrupacion2;
			obj.select = totalAnual;
			jsonAgrupadoAnual.push(obj);
		}
	}
	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));
	// console.log("jsonAgrupadoAnual: "+JSON.stringify(jsonAgrupadoAnual));
	// return true;

	for (var key in jsonAgrupado) {
		// Datos del techos presupuestales
		for (var key2 in jsonAgrupadoAnual) {
			// Datos de las claves presupuestales
			if (jsonAgrupado[key].value == jsonAgrupadoAnual[key2].value && jsonAgrupado[key].value2 == jsonAgrupadoAnual[key2].value2.substring(3, 5)) {
				// Si encontro registro
				if (jsonAgrupado[key].select != jsonAgrupadoAnual[key2].select) {
					// Tiene cantidades diferentes
					objGeneralAnteproyecto.setErrorValidacion(0);
					mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
					mensaje += ' El techo presupuestal para '+jsonAgrupado[key].value+' - '+jsonAgrupado[key].value2;
					mensaje += ' es de $ '+fixDecimales( redondeaDecimal(jsonAgrupado[key].select)+"" );
					mensaje += '</p>';
				}
				jsonAgrupadoAnual.splice(key2, 1);
				break;
			}
		}
	}
	// console.log("jsonAgrupadoAnual despues: "+JSON.stringify(jsonAgrupadoAnual));
	if (Number(jsonAgrupadoAnual.length) > Number(0)) {
		// Si tiene registros, existen UR no contenpladas en los techos presupuestales
		objGeneralAnteproyecto.setErrorValidacion(0);
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
		mensaje += ' El existen Unidades Ejecutoras no contempladas en el techo presupuestal';
		mensaje += '</p>';
	}
	
	return mensaje;
}

/**
 * Función para validar el total por capitulo
 * Entre el panel del techo presupuestal y claves presupuestarias agregadas
 * @return {[type]} Mensaje con contenido html de error
 */
function fnValidarTotalCapitulo() {
	// Validar montos por los techos presupuestales
	var mensaje = '';
	var jsonAgrupado = new Array();
	var dataJson = new Array();

	if (objGeneralAnteproyecto.getUtilizarUe == 1) {
		// Panel con UE
		dataJson = objGeneralAnteproyecto.getUe;
	} else {
		// Panel con UR
		dataJson = objGeneralAnteproyecto.getUr;
	}
	
	for (var key in dataJson) {
		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Recorrer los capitulos
			var exiteDato = 0;
			for (var keyUr in jsonAgrupado) {
				if (jsonAgrupado[keyUr].value == jsonCapitulos[key2].value) {
					exiteDato = 1;
					// Sumar cantidad
					jsonAgrupado[keyUr].select = parseFloat(jsonAgrupado[keyUr].select) + parseFloat(jsonCapitulos[key2].select);
					break;
				}
			}

			if (exiteDato == 0) {
				// Agregar informacion
				var obj = new Object();
				obj.value = jsonCapitulos[key2].value;
				obj.texto = jsonCapitulos[key2].texto;
				obj.select = jsonCapitulos[key2].select;
				jsonAgrupado.push(obj);
			}
		}
	}
	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));
	
	// Agrupar sumas por UR de las claves presupuestales
	var jsonAgrupadoAnual = new Array();
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	// console.log("datosAgregados: "+JSON.stringify(datosAgregados));
	for (var key in datosAgregados) {
		var jsonInfo = datosAgregados[key].claveInfo;
		var datoAgrupacion = '';
		for (var key2 in jsonInfo) {
			if (jsonInfo[key2].nombreCampo == 'partida_esp') {
				// Si es año mostrar el del anteproyecto
				datoAgrupacion = datosAgregados[key].claveInfo[key2].valor;
				break;
			}
		}

		// Total por mes
		var totalAnual = 0;
		var jsonInfoMeses = datosAgregados[key].mesesInfo;
		var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
		for (var keymes in jsonInfoMeses) {
			for (var mes in dataJsonMeses) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				// Mostrar datos del mes
				totalAnual = parseFloat(totalAnual) + parseFloat(datosAgregados[key].mesesInfo[keymes][nombreMes]);
			}
		}
		// console.log("datoAgrupacion: "+datoAgrupacion);

		var exiteDato = 0;
		for (var keyUr in jsonAgrupadoAnual) {
			if (jsonAgrupadoAnual[keyUr].value.substring(0, 1) == datoAgrupacion.substring(0, 1)) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupadoAnual[keyUr].select = parseFloat(jsonAgrupadoAnual[keyUr].select) + parseFloat(totalAnual);
				break;
			}
		}

		if (exiteDato == 0) {
			// Agregar informacion
			var obj = new Object();
			obj.value = datoAgrupacion;
			obj.select = totalAnual;
			jsonAgrupadoAnual.push(obj);
		}
	}

	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));
	// console.log("jsonAgrupadoAnual: "+JSON.stringify(jsonAgrupadoAnual));
	// return true;

	for (var key in jsonAgrupado) {
		// Datos del techos presupuestales
		for (var key2 in jsonAgrupadoAnual) {
			// Datos de las claves presupuestales
			if (jsonAgrupado[key].value.substring(0, 1) == jsonAgrupadoAnual[key2].value.substring(0, 1)) {
				// Si encontro registro
				if (jsonAgrupado[key].select != jsonAgrupadoAnual[key2].select) {
					// Tiene cantidades diferentes
					objGeneralAnteproyecto.setErrorValidacion(0);
					mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
					mensaje += ' El techo presupuestal para '+jsonAgrupado[key].value+' - '+jsonAgrupado[key].texto;
					mensaje += ' es de $ '+fixDecimales( redondeaDecimal(jsonAgrupado[key].select)+"" );
					mensaje += '</p>';
				}
				jsonAgrupadoAnual.splice(key2, 1);
				break;
			}
		}
	}
	// console.log("jsonAgrupadoAnual despues: "+JSON.stringify(jsonAgrupadoAnual));
	if (Number(jsonAgrupadoAnual.length) > Number(0)) {
		// Si tiene registros, existen UR no contenpladas en los techos presupuestales
		objGeneralAnteproyecto.setErrorValidacion(0);
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
		mensaje += ' El existen Capítulos no contempladas en el techo presupuestal';
		mensaje += '</p>';
	}
	
	return mensaje;
}

/**
 * Función para validar el total por unidad responsable
 * Entre el panel de unidad responsable y claves presupuestarias agregadas
 * @return {[type]} Mensaje con contenido html de error
 */
function fnValidarTotalUr() {
	// Validar montos por los techos presupuestales
	var mensaje = '';
	var jsonAgrupado = new Array();
	var dataJson = new Array();

	if (objGeneralAnteproyecto.getUtilizarUe == 1) {
		// Panel con UE
		dataJson = objGeneralAnteproyecto.getUe;
	} else {
		// Panel con UR
		dataJson = objGeneralAnteproyecto.getUr;
	}

	for (var key in dataJson) {
		var exiteDato = 0;
		for (var keyUr in jsonAgrupado) {
			if (jsonAgrupado[keyUr].value == dataJson[key].value) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupado[keyUr].select = parseFloat(jsonAgrupado[keyUr].select) + parseFloat(dataJson[key].select);
				break;
			}
		}

		if (exiteDato == 0) {
			// Agregar informacion
			var obj = new Object();
			obj.value = dataJson[key].value;
			obj.texto = dataJson[key].texto;
			obj.select = dataJson[key].select;
			jsonAgrupado.push(obj);
		}
	}
	
	// Agrupar sumas por UR de las claves presupuestales
	var jsonAgrupadoAnual = new Array();
	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
	for (var key in datosAgregados) {
		var jsonInfo = datosAgregados[key].claveInfo;
		var datoAgrupacion = '';
		for (var key2 in jsonInfo) {
			if (jsonInfo[key2].nombreCampo == 'tagref') {
				// Si es año mostrar el del anteproyecto
				datoAgrupacion = datosAgregados[key].claveInfo[key2].valor;
				break;
			}
		}

		// Total por mes
		var totalAnual = 0;
		var jsonInfoMeses = datosAgregados[key].mesesInfo;
		var dataJsonMeses = objGeneralAnteproyecto.getInfoClavesMeses;
		for (var keymes in jsonInfoMeses) {
			for (var mes in dataJsonMeses) {
				// Nombres de los mes
				var nombreMes = dataJsonMeses[mes];
				// Mostrar datos del mes
				totalAnual = parseFloat(totalAnual) + parseFloat(datosAgregados[key].mesesInfo[keymes][nombreMes]);
			}
		}

		var exiteDato = 0;
		for (var keyUr in jsonAgrupadoAnual) {
			if (jsonAgrupadoAnual[keyUr].value == datoAgrupacion) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupadoAnual[keyUr].select = parseFloat(jsonAgrupadoAnual[keyUr].select) + parseFloat(totalAnual);
				break;
			}
		}

		if (exiteDato == 0) {
			// Agregar informacion
			var obj = new Object();
			obj.value = datoAgrupacion;
			obj.select = totalAnual;
			jsonAgrupadoAnual.push(obj);
		}
	}

	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));
	// console.log("jsonAgrupadoAnual: "+JSON.stringify(jsonAgrupadoAnual));

	for (var key in jsonAgrupado) {
		// Datos del techos presupuestales
		for (var key2 in jsonAgrupadoAnual) {
			// Datos de las claves presupuestales
			if (jsonAgrupado[key].value == jsonAgrupadoAnual[key2].value) {
				// Si encontro registro
				if (jsonAgrupado[key].select != jsonAgrupadoAnual[key2].select) {
					// Tiene cantidades diferentes
					objGeneralAnteproyecto.setErrorValidacion(0);
					mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
					mensaje += ' El techo presupuestal para '+jsonAgrupado[key].value+' - '+jsonAgrupado[key].texto;
					mensaje += ' es de $ '+fixDecimales( redondeaDecimal(jsonAgrupado[key].select)+"" );
					mensaje += '</p>';
				}
				jsonAgrupadoAnual.splice(key2, 1);
				break;
			}
		}
	}
	// console.log("jsonAgrupadoAnual despues: "+JSON.stringify(jsonAgrupadoAnual));
	if (Number(jsonAgrupadoAnual.length) > Number(0)) {
		// Si tiene registros, existen UR no contenpladas en los techos presupuestales
		objGeneralAnteproyecto.setErrorValidacion(0);
		mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>';
		mensaje += ' El existen Unidades Responsables no contempladas en el techo presupuestal';
		mensaje += '</p>';
	}
	
	return mensaje;
}

/**
 * Función para validar el total por unidad responsable
 * Entre el panel de unidad responsable y unidad ejecutora
 * @return {[type]} Mensaje con contenido html de error
 */
function fnValidarTotalUrPanelUe() {
	var mensaje = '';
	var jsonAgrupado = new Array();
	var dataJson = objGeneralAnteproyecto.getUe;

	for (var key in dataJson) {
		var exiteDato = 0;
		for (var keyUr in jsonAgrupado) {
			if (jsonAgrupado[keyUr].tagref == dataJson[key].value) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupado[keyUr].select = parseFloat(jsonAgrupado[keyUr].select) + parseFloat(dataJson[key].select);
				break;
			}
		}

		if (exiteDato == 0) {
			var obj = new Object();
			obj.tagref = dataJson[key].value;
			obj.select = dataJson[key].select;
			jsonAgrupado.push(obj);
		}
	}

	// Agrupar montos de panel de unidad responsable
	var jsonAgrupado2 = new Array();
	var dataJson = objGeneralAnteproyecto.getUr;
	for (var key in dataJson) {
		var exiteDato = 0;
		for (var keyUr in jsonAgrupado2) {
			if (jsonAgrupado2[keyUr].tagref == dataJson[key].value) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupado2[keyUr].select = parseFloat(jsonAgrupado2[keyUr].select) + parseFloat(dataJson[key].select);
				break;
			}
		}

		if (exiteDato == 0) {
			var obj = new Object();
			obj.tagref = dataJson[key].value;
			obj.texto = dataJson[key].texto;
			obj.select = dataJson[key].select;
			jsonAgrupado2.push(obj);
		}
	}
	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));
	// console.log("jsonAgrupado2: "+JSON.stringify(jsonAgrupado2));

	// Recorrer información para validar datos,
	// Se recorren datos de las unidades respomsables y se valida con la suma de los UR por UE
	for (var key in jsonAgrupado2) {
		for (var keyUr in jsonAgrupado) {
			if ((jsonAgrupado[keyUr].tagref == jsonAgrupado2[key].tagref) &&
				Number(jsonAgrupado[keyUr].select) != Number(jsonAgrupado2[key].select)) {
				// Si tiene error mostrar
				objGeneralAnteproyecto.setErrorValidacion(0);
				// console.log("diferencias "+jsonAgrupado2[key].tagref);
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> UR '+
				jsonAgrupado2[key].tagref+' - '+jsonAgrupado2[key].texto+' es de $ '+fixDecimales( redondeaDecimal(jsonAgrupado2[key].select)+"" )
				+', en las unidades ejecutoras se tiene '+fixDecimales( redondeaDecimal(jsonAgrupado[keyUr].select)+"" )+'</p>';
			} else{
				// console.log("iguales "+jsonAgrupado2[key].tagref);
			}
		}
	}

	return mensaje;
}

/**
 * Función para validar el total por capitulo
 * Entre el panel de capitulo y unidad responsable
 * @return {[type]} Mensaje con contenido html de error
 */
function fnValidarTotalCapituloPanelUr() {
	var mensaje = '';
	var jsonAgrupado = new Array();
	var dataJson = objGeneralAnteproyecto.getUr;
	
	for (var key in dataJson) {
		var exiteDato = 0;
		for (var keyUr in jsonAgrupado) {
			if (jsonAgrupado[keyUr].capitulo == dataJson[key].value2) {
				exiteDato = 1;
				// Sumar cantidad
				jsonAgrupado[keyUr].select = parseFloat(jsonAgrupado[keyUr].select) + parseFloat(dataJson[key].select);
				break;
			}
		}

		if (exiteDato == 0) {
			var obj = new Object();
			obj.capitulo = dataJson[key].value2;
			obj.select = dataJson[key].select;
			jsonAgrupado.push(obj);
		}
	}
	// console.log("jsonAgrupado: "+JSON.stringify(jsonAgrupado));

	// Recorrer información para validar datos,
	// Se recorren datos de los capitulos y se valida con la suma de los capitulos por UR
	var dataJson = objGeneralAnteproyecto.getCapitulos;
	// console.log("dataJson: "+JSON.stringify(dataJson));
	for (var key in dataJson) {
		for (var keyUr in jsonAgrupado) {
			if ((jsonAgrupado[keyUr].capitulo == dataJson[key].value) &&
				Number(jsonAgrupado[keyUr].select) != Number(dataJson[key].select)) {
				// Si tiene error mostrar
				objGeneralAnteproyecto.setErrorValidacion(0);
				// console.log("diferencias "+dataJson[key].value);
				mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Capítulo '+
				dataJson[key].value+' - '+dataJson[key].texto+' es de $ '+fixDecimales( redondeaDecimal(dataJson[key].select)+"" )
				+', en las unidades responsables se tiene '+fixDecimales( redondeaDecimal(jsonAgrupado[keyUr].select)+"" )+'</p>';
			} else{
				// console.log("iguales "+dataJson[key].value);
			}
		}
	}

	return mensaje;
}

/**
 * Función para crear las estructuras y poder validar contra catalogos unicos
 * @param  {[type]} JSONDatos       Información de la clave presupuestal
 * @param  {String} tipoEstructura  Tipo de estructura
 * @param  {String} ordenEstructura Campo del orden de la estructura
 * @param  {String} separacion      Caracter de la sepacion entre los campos
 * @return {[type]}                 Regresa la estructura creada
 */
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

/**
 * Función para validar las estructuras de la clave presupuestal
 * Compara contra catalogos unicos
 * @param  {[type]} estProgramatica   Estructura programatica
 * @param  {[type]} estEconomica      Estrcutura econimica
 * @param  {[type]} estAdministrativa Estrcutura administrativa
 * @param  {[type]} estPartida        Estructura partida
 * @param  {String} mensajeInicial    Mensaje inicial de validacion
 * @return {[type]}                   Regresa 1 si estan correctas las validaciones o una
 * candena con mensaje si encuentra errores
 */
function fnValidarEstructuraClaveNueva(estProgramatica, estEconomica, estAdministrativa, estPartida, jsonInfo = new Array(), mensajeInicial = '') {
	// Funcion para validar las estructuras
	var respuesta = 0;

	dataObj = { 
	        option: 'validarEstructuras',
	        estProgramatica: estProgramatica,
	        estEconomica: estEconomica,
	        estAdministrativa: estAdministrativa,
			estPartida: estPartida,
			mensajeInicial: mensajeInicial,
			jsonInfoClaves: jsonInfo
	      };
    $.ajax({
	  async:false,
	  cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/anteproyectoModelo.php",
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

/**
 * Función para obtener información de la captura
 * @param  {[type]} type    Tipo de documento
 * @param  {[type]} transno Folio de la trasacción
 * @return {[type]}         [description]
 */
function fnObtenerInformacionCaptura(type, transno) {
	// Obtener información de captura
	dataObj = { 
	        option: 'cargarInfoNoCaptura',
			type: type,
			transno: transno
	      };
	$.ajax({
		  async:false,
	      cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/anteproyectoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	objGeneralAnteproyecto.setType(data.contenido.type);
	    	objGeneralAnteproyecto.setTransno(data.contenido.transno);
	    	objGeneralAnteproyecto.setTotalGeneral(data.contenido.totalGeneral);
	    	objGeneralAnteproyecto.setFechaCaptura(data.contenido.fechaCaptura);
	    	objGeneralAnteproyecto.setUtilizarPaaas(data.contenido.usarPaaas);
	    	objGeneralAnteproyecto.setAnio(data.contenido.anio);
	    	objGeneralAnteproyecto.setAnioAnterior(Number(data.contenido.anio - 1));
	    	objGeneralAnteproyecto.setDescripcion(data.contenido.descripcion);
	    	objGeneralAnteproyecto.setErrorValidacion(data.contenido.validacion);
	    	objGeneralAnteproyecto.setUtilizarUe(data.contenido.usarUe);
	    	objGeneralAnteproyecto.setConfiguracionClave(data.contenido.configClavePresupuesto);
	    	objGeneralAnteproyecto.setInfoClavesAnual(data.contenido.datosClaveAnual);
	    	objGeneralAnteproyecto.setUtilizarSoloUnaFase(data.contenido.usarSoloUnaFase);
	    	objGeneralAnteproyecto.setValidarJustificacion(data.contenido.validarJustificacion);
	    	objGeneralAnteproyecto.setInfoAutorizada(data.contenido.infoAutorizada);
	    	objGeneralAnteproyecto.setInfoAutorizadaVar(data.contenido.infoAutorizadaVar);

	    	// console.log("infoAutorizada: "+JSON.stringify(objGeneralAnteproyecto.getInfoAutorizada));
	    	// console.log("infoAutorizadaVar: "+JSON.stringify(objGeneralAnteproyecto.getInfoAutorizadaVar));

	    	$('#txtNoCaptura').empty();
			$('#txtNoCaptura').append(objGeneralAnteproyecto.getTransno);
			$('#txtNumberDecimales').val(formatoComas(redondeaDecimal( parseFloat(objGeneralAnteproyecto.getTotalGeneral))));
			$('#txtFechaCaptura').val(objGeneralAnteproyecto.getFechaCaptura);
			$('#txtAnio').val(objGeneralAnteproyecto.getAnio);
			$('#txtJustificacion').val(objGeneralAnteproyecto.getDescripcion);

			if (objGeneralAnteproyecto.getUtilizarPaaas == 1) {
				// Seleccionar check
				$('#checkPaaas').attr('checked', true);
			} else {
				// No seleccionar check
				$('#checkPaaas').attr('checked', false);
			}

			if (objGeneralAnteproyecto.getUtilizarUe == 1) {
				// Seleccionar check
				$('#checkUE').attr('checked', true);
				$("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).css( "display", "none" );
				$("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).css( "display", "block" );
			} else {
				// No seleccionar check
				$('#checkUE').attr('checked', false);
				$("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).css( "display", "block" );
				$("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).css( "display", "none" );
			}

			if (objGeneralAnteproyecto.getUtilizarSoloUnaFase == 1) {
				// Seleccionar check
				$('#checkSoloFase').attr('checked', true);
			} else {
				// No seleccionar check
				$('#checkSoloFase').attr('checked', false);
			}

			if (objGeneralAnteproyecto.getValidarJustificacion == 1) {
				// Seleccionar check
				$('#checkValidarJustificación').attr('checked', true);
			} else {
				// No seleccionar check
				$('#checkValidarJustificación').attr('checked', false);
			}

			if (data.contenido.datosCapitulos.length > 0) {
				// Tiene Datos de capitulo
				$("#"+objGeneralAnteproyecto.getNomPanelCapitulos).addClass("in");
				var jsonInfo = data.contenido.datosCapitulos;
			    for (var info in jsonInfo) {
			    	var nombreElemento = 'Capitulo_'+jsonInfo[info].texto;
			    	$("#"+nombreElemento).val(jsonInfo[info].value);
				}
			}
			//formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreReintegro]) ) )
			if (data.contenido.datosUnidadResponsable.length > 0) {
				// Tiene Datos de unidad responsable
				$("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).addClass("in");
				var jsonInfo = data.contenido.datosUnidadResponsable;
				var total = 0;
				var nombreElementoAnt = '';
			    for (var info in jsonInfo) {
			    	// Mostrar informacion por ur, capitulo
			    	var nombreElemento = 'UR_'+jsonInfo[info].texto+'_'+jsonInfo[info].value2;
			    	if (nombreElementoAnt != '' && nombreElementoAnt != nombreElemento) {
			    		// Si ya son registros de otro renglon
			    		$("#"+nombreElementoAnt).val(total);
			    		total = 0;
			    	}
			    	nombreElementoAnt = 'UR_'+jsonInfo[info].texto+'_'+jsonInfo[info].value2;
			    	// Mostrar datos capturados
			    	$("#"+nombreElemento).val(jsonInfo[info].value);
			    	// Sumar totales por renglon
			    	total = parseFloat(total) + parseFloat(jsonInfo[info].value);
				}
				// Si es el ultimo registro
				$("#"+nombreElementoAnt).val(total);
			}

			if (data.contenido.datosUnidadEjecutora.length > 0) {
				// Tiene Datos de unidad ejecutora
				$("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).addClass("in");
				var jsonInfo = data.contenido.datosUnidadEjecutora;
				var total = 0;
				var nombreElementoAnt = '';

			    for (var info in jsonInfo) {
			    	// Mostrar informacion por ur, ue, capitulo
			    	var nombreElemento = 'UE_'+jsonInfo[info].texto+'_'+jsonInfo[info].value2+'_'+jsonInfo[info].capitulo;
			    	if (nombreElementoAnt != '' && nombreElementoAnt != nombreElemento) {
			    		// Si ya son registros de otro renglon
			    		$("#"+nombreElementoAnt).val(total);
			    		total = 0;
			    	}
			    	nombreElementoAnt = 'UE_'+jsonInfo[info].texto+'_'+jsonInfo[info].value2;
			    	// Mostrar datos capturados
					//	var dtaMostrar = ;
			    	$("#"+nombreElemento).val(jsonInfo[info].value);
			    	// Sumar totales por renglon
			    	total = parseFloat(total) + parseFloat(jsonInfo[info].value);


				}
				// Si es el ultimo registro
				//	var viewDTA = total
				$("#"+nombreElementoAnt).val(total);

				//  console.log($("#"+nombreElementoAnt).val(formatoComas( redondeaDecimal( parseFloat(viewDTA)))));
			}

			fnCargarConfiguracionClave('.selectConfigClave');

			if (objGeneralAnteproyecto.getConfiguracionClave  != 0 
            && objGeneralAnteproyecto.getConfiguracionClave  != null
            && objGeneralAnteproyecto.getConfiguracionClave  != 'undefined') {
				$('#selectConfigClave').val(''+objGeneralAnteproyecto.getConfiguracionClave);
				$('#selectConfigClave').multiselect('rebuild');

				$("#"+objGeneralAnteproyecto.getNomPanelClavePresupuestalAnual).addClass("in");
				// Obtener configuracion clave
				fnObtenerConfiguracionClave(objGeneralAnteproyecto.getConfiguracionClave);
			}

			if (objGeneralAnteproyecto.getInfoClavesAnual.length > 0) {
				// Mostrar información de las claves anuales
		    	var datosAgregados = objGeneralAnteproyecto.getInfoClavesAnual;
				for (var key in datosAgregados) {
					fnMuestraClaveAnual(datosAgregados[key]);
				}
			}

			// Calcular totales
			fnCambioCantidadCapitulo('');
			fnCambioCantidadUr('');
			fnCambioCantidadUe('');
			fnGuardarSeleccionadoClaveAnual('');

			// Poner información de carga
			objGeneralAnteproyecto.setErrorValidacion(data.contenido.validacion);

			// Poner información de estatus
			objGeneralAnteproyecto.setStatusid(data.contenido.estatus);

			// Validar captura
			fnValidarInfoCaptura();

			if (objGeneralAnteproyecto.getStatusid == 1) {
				// Si es captura habilitar datos
				// fnBloquearEncabezado(false);
				// fnBloquearTechoPresupuestal(false);
				// fnBloquearClavesPresupuestarias(false);
			}

			if (objGeneralAnteproyecto.getStatusid == 2 
				|| objGeneralAnteproyecto.getStatusid == 3
				|| objGeneralAnteproyecto.getStatusid == 4) {
				// Bloquear para cambiar informacion
				// fnBloquearEncabezado();
				// fnBloquearTechoPresupuestal();
				// fnBloquearClavesPresupuestarias();
			}
			if (objGeneralAnteproyecto.getStatusid == 5) {
				// Si esta autorizada
				fnBloquearDivBotones();
				// Bloquear para cambiar informacion
				fnBloquearEncabezado();
				fnBloquearTechoPresupuestal();
				fnBloquearClavesPresupuestarias();
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

function fnValidarAnteproyecto() {
	var respuesta = '';
	dataObj = { 
		option: 'validarAnte',
		anio: objGeneralAnteproyecto.getAnio
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/anteproyectoModelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			// ocultaCargandoGeneral();
			respuesta = data.contenido;
		}else{
			// ocultaCargandoGeneral();
			respuesta = data.contenido;
		}
	})
	.fail(function(result) {
		// ocultaCargandoGeneral();
		respuesta = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se realizó la validación de anteproyectos autorizados con el año agregado</p>';
	});

	return respuesta;
}

/**
 * Función para guardar la información capturada
 * @param  {[type]} estatus    Estatus del registro
 * @param  {String} generarCsv Generar CSV
 * @return {[type]}            [description]
 */
function fnAlmacenarCaptura(estatus, generarCsv = '0') {
	// Guardar Informacion
	muestraCargandoGeneral();

	if (Number(objGeneralAnteproyecto.getTotalGeneral) == Number(0)) {
		// No selecciono monto general
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar importe SHCP</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}
	
	if (Number(objGeneralAnteproyecto.getInfoClavesAnual.length) > Number(0) 
		&& ($("#selectConfigClave").val() == '0'
		|| $("#selectConfigClave").val() == null
		|| $("#selectConfigClave").val() == 'undefined')) {
		// Tiene claves
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar Configuración de Clave Presupuestal</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	// if (objGeneralAnteproyecto.getStatusid == 2 
	// 	|| objGeneralAnteproyecto.getStatusid == 3
	// 	|| objGeneralAnteproyecto.getStatusid == 4) {
	// 	// Bloquear para cambiar informacion
	// 	fnBloquearEncabezado();
	// 	fnBloquearTechoPresupuestal();
	// 	fnBloquearClavesPresupuestarias();
	// }
	if (objGeneralAnteproyecto.getStatusid == 5) {
		// Si esta autorizada
		ocultaCargandoGeneral();
		fnBloquearDivBotones();
	}

	// Validar que la infomacion de las claves sea de acuerdo a la configuración
	var mensaje = fnValidarDatosClavePresupuestal();
	if (mensaje != '') {
		// Encontro errores
		ocultaCargandoGeneral();
		objGeneralAnteproyecto.setErrorValidacion(0);
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	// Validar que no se repitan las claves en la captura
	var mensaje = fnValidarMismaClave();
	if (mensaje != '') {
		// Encontro errores
		ocultaCargandoGeneral();
		objGeneralAnteproyecto.setErrorValidacion(0);
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	if (objGeneralAnteproyecto.getValidarJustificacion == 1) {
		// Validar que los registros de la clave tengan justificacion
		var mensaje = fnValidarJustificacionClaves();
		if (mensaje != '') {
			// Encontro errores
			ocultaCargandoGeneral();
			objGeneralAnteproyecto.setErrorValidacion(0);
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, mensaje);
			return true;
		}
	}

	var mensaje = fnValidarAnteproyecto();
	if (mensaje != '') {
		// Encontro errores
		ocultaCargandoGeneral();
		objGeneralAnteproyecto.setErrorValidacion(0);
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}
	
	dataObj = { 
	        option: 'guardarInformacion',
	        estatus: estatus,
			type: objGeneralAnteproyecto.getType,
			transno: objGeneralAnteproyecto.getTransno,
			totalGeneral: objGeneralAnteproyecto.getTotalGeneral,
			fechaCaptura: objGeneralAnteproyecto.getFechaCaptura,
			usarPaaas: objGeneralAnteproyecto.getUtilizarPaaas,
			anio: objGeneralAnteproyecto.getAnio,
			descripcion: objGeneralAnteproyecto.getDescripcion,
			validacion: objGeneralAnteproyecto.getErrorValidacion,
			usarUe: objGeneralAnteproyecto.getUtilizarUe,
			configClavePresupuesto: objGeneralAnteproyecto.getConfiguracionClave,
			jsonCapitulos: objGeneralAnteproyecto.getCapitulos,
			jsonUr: objGeneralAnteproyecto.getUr,
			jsonUe: objGeneralAnteproyecto.getUe,
			jsonPartidas: objGeneralAnteproyecto.getPartidasEsp,
			jsonInfoAnual: objGeneralAnteproyecto.getInfoClavesAnual,
			usarSoloUnaFase: objGeneralAnteproyecto.getUtilizarSoloUnaFase,
			validarJustificacion: objGeneralAnteproyecto.getValidarJustificacion,
			generarCsv: generarCsv,
			infoAutorizada: objGeneralAnteproyecto.getInfoAutorizada
	      };
	$.ajax({
		  async:false,
	      cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/anteproyectoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	// transno = data.contenido.datos.transno;
	    	objGeneralAnteproyecto.setTransno(data.contenido.datos.transno);
	    	$('#txtNoCaptura').empty();
			$('#txtNoCaptura').append(objGeneralAnteproyecto.getTransno);

			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.Mensaje);

		    if (objGeneralAnteproyecto.getUtilizarUe == 1) {
		    	// Si utiliza ue, borrar datos de la ur
		    	fnInformacionUrEnCero(objGeneralAnteproyecto.getUr);
		    } else {
		    	// Si utiliza ur, borrar datos de la ue
		    	fnInformacionUeEnCero(objGeneralAnteproyecto.getUe);
		    }

			if (data.contenido.datos.cadenaCSV != '') {
				// Generar CSV
				// console.log("cadenaCSV: "+data.contenido.datos.cadenaCSV);
				var json = $.parseJSON(data.contenido.datos.cadenaCSV);
				// console.log("json: "+JSON.stringify(json));
				fnGenerarCsvGeneral(json, data.contenido.datos.nombreCsv);
			}

			// Agregar datos de validacion
			objGeneralAnteproyecto.setErrorValidacion(data.contenido.datos.validacion);

			// Poner información de estatus
			objGeneralAnteproyecto.setStatusid(data.contenido.datos.estatus);

			if (objGeneralAnteproyecto.getStatusid == 2 
				|| objGeneralAnteproyecto.getStatusid == 3
				|| objGeneralAnteproyecto.getStatusid == 4) {
				// Bloquear para cambiar informacion
				fnBloquearEncabezado();
				fnBloquearTechoPresupuestal();
				fnBloquearClavesPresupuestarias();
			}
			if (objGeneralAnteproyecto.getStatusid == 5) {
				// Si esta autorizada
				fnBloquearDivBotones();
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
 * Función para generar el rechazo de un CSV
 * @param  {[type]} estatus Estatus del registro
 * @return {[type]}         [description]
 */
function fnCambiarEstatusCaptura(estatus) {
	// Función para rechazar cuando se genero un CSV
	if (objGeneralAnteproyecto.getStatusid == 5) {
		// No selecciono monto general
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede rechazar en el estatus que se encuentra</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	dataObj = { 
	        option: 'actualizarEstatus',
	        estatus: estatus,
			type: objGeneralAnteproyecto.getType,
			transno: objGeneralAnteproyecto.getTransno,
			totalGeneral: objGeneralAnteproyecto.getTotalGeneral,
			fechaCaptura: objGeneralAnteproyecto.getFechaCaptura,
			usarPaaas: objGeneralAnteproyecto.getUtilizarPaaas,
			anio: objGeneralAnteproyecto.getAnio,
			descripcion: objGeneralAnteproyecto.getDescripcion,
			validacion: objGeneralAnteproyecto.getErrorValidacion,
			usarUe: objGeneralAnteproyecto.getUtilizarUe,
			configClavePresupuesto: objGeneralAnteproyecto.getConfiguracionClave,
			jsonCapitulos: objGeneralAnteproyecto.getCapitulos,
			jsonUr: objGeneralAnteproyecto.getUr,
			jsonUe: objGeneralAnteproyecto.getUe,
			jsonInfoAnual: objGeneralAnteproyecto.getInfoClavesAnual,
			usarSoloUnaFase: objGeneralAnteproyecto.getUtilizarSoloUnaFase,
			validarJustificacion: objGeneralAnteproyecto.getValidarJustificacion,
			generarCsv: 0
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
	    	ocultaCargandoGeneral();
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.contenido.mensaje);

		    // Poner información de estatus
			objGeneralAnteproyecto.setStatusid(data.contenido.estatus);

			if (objGeneralAnteproyecto.getStatusid == 2 
				|| objGeneralAnteproyecto.getStatusid == 3
				|| objGeneralAnteproyecto.getStatusid == 4) {
				// Bloquear para cambiar informacion
				fnBloquearEncabezado();
				fnBloquearTechoPresupuestal();
				fnBloquearClavesPresupuestarias();
			} else {
				// Bloquear para cambiar informacion
				fnBloquearEncabezado(false);
				fnBloquearTechoPresupuestal(false);
				fnBloquearClavesPresupuestarias(false);
				fnBloquearDivBotones(false);
			}
			if (objGeneralAnteproyecto.getStatusid == 5) {
				// Si esta autorizada
				fnBloquearDivBotones();
			}
	    }else{
	    	//Obtener Datos de un No. Captura
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, data.contenido.mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

/**
 * Función para poner registros del panel de la ur en 0
 * @param  {Array}  dataJson Información a reccorrer
 * @return {[type]}          [description]
 */
function fnInformacionUrEnCero(dataJson = new Array()) {
	// Poner información de la ur en 0
	for (var key in dataJson) {
		var nombreElemento = 'UR_'+dataJson[key].value;
		// Asignar valor, caja total
		dataJson[key].select = 0;
		$("#"+nombreElemento).val('0');

		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Asigna valor a caja de capitulo
			nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
			dataJson[key].Capitulo[key2].select = 0;
			$("#"+nombreElemento).val('0')
		}
	}
	objGeneralAnteproyecto.setUr(dataJson);
	fnCambioCantidadUr('');
}

/**
 * Función para poner registros del panel de la ue en 0
 * @param  {Array}  dataJson Información a reccorrer
 * @return {[type]}          [description]
 */
function fnInformacionUeEnCero(dataJson = new Array()) {
	// Poner información de la ur en 0
	for (var key in dataJson) {
		var nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2;
		// Asignar valor, caja total
		dataJson[key].select = 0;
		$("#"+nombreElemento).val('0');

		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Asigna valor a caja de capitulo
			nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
			dataJson[key].Capitulo[key2].select = 0;
			$("#"+nombreElemento).val('0');
		}
	}
	objGeneralAnteproyecto.setUe(dataJson);
	fnCambioCantidadUe('');
}

/**
 * Función para agregar selección de configuración de la clave
 * @param  {[type]} select [description]
 * @return {[type]}        [description]
 */
function fnCambioConfiguracionClave(select){
	// Cambio configuración de clave
	objGeneralAnteproyecto.setConfiguracionClave(select.value);
	
	// Obtener configuracion clave
	fnObtenerConfiguracionClave(objGeneralAnteproyecto.getConfiguracionClave);
}

/**
 * Función para obtener la configuración de la clave
 * @param  {[type]} config Configuración seleccionada
 * @return {[type]}        [description]
 */
function fnObtenerConfiguracionClave(config) {
	// Obtener configuracion de la clave
	muestraCargandoGeneral();

	dataObj = { 
	      option: 'mostrarConfiguracionClave',
	      config: config
	    };
	
	$.ajax({
		async:false,
	    cache:false,
	    method: "POST",
	    dataType:"json",
	    url: "modelo/anteproyectoModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			ocultaCargandoGeneral();
			objGeneralAnteproyecto.setConfiguracionClaveInfo(data.contenido.datos);
		}else{
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la Configuración</p>';
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
 * Función para agregar la fecha cambiada
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioFecha(input) {
	objGeneralAnteproyecto.setFechaCaptura(input.value);
}

/**
 * Función para agregar la descripción cambiada
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioDescriocion(input) {
	objGeneralAnteproyecto.setDescripcion(input.value);
}

/**
 * Función para agregar si usa paaas
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioUsarPaaas(input) {
	if( $('#'+input.name).prop('checked') ) {
		// Usar paaas
	    objGeneralAnteproyecto.setUtilizarPaaas(1);
	} else {
		// No usar paaas
		objGeneralAnteproyecto.setUtilizarPaaas(0);
	}
}

/**
 * Función para agregar si usa solo una fase
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioUsarSoloUnaFase(input) {
	if( $('#'+input.name).prop('checked') ) {
		// Usar solo una fase
	    objGeneralAnteproyecto.setUtilizarSoloUnaFase(1);
	} else {
		// No usar solo una fase
		objGeneralAnteproyecto.setUtilizarSoloUnaFase(0);
	}
	// Calcular totales
	fnGuardarSeleccionadoClaveAnual('');
}

/**
 * Función para agregar si valida justificacion
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioUsarJustificacion(input) {
	if( $('#'+input.name).prop('checked') ) {
		// Usar solo una fase
	    objGeneralAnteproyecto.setValidarJustificacion(1);
	} else {
		// No usar solo una fase
		objGeneralAnteproyecto.setValidarJustificacion(0);
	}
}

/**
 * Función para agregar si usa ue
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioUsarUe(input) {
	if( $('#'+input.name).prop('checked') ) {
		// Usar ue
	    objGeneralAnteproyecto.setUtilizarUe(1);
	    $("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).css( "display", "none" );
	    $("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).css( "display", "block" );
	} else {
		// No usar ue
		objGeneralAnteproyecto.setUtilizarUe(0);
		$("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).css( "display", "block" );
		$("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).css( "display", "none" );
	}
}

/**
 * Función para agregar el Año cambiado
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioAnio(input) {
	if (Number(input.value) >= Number(anioActualAdecuacion)) {
		// Actualizar Año
		objGeneralAnteproyecto.setAnio(input.value);
		objGeneralAnteproyecto.setAnioAnterior(Number(input.value - 1));
	} else{
		// Dejar información anterior
		$("#"+input.name).val(objGeneralAnteproyecto.getAnio);
	}

	fnCargarConfiguracionClave('.selectConfigClave');

	// Datos lista de busqueda del presupuesto anterior
	fnObtenerPresupuestoBusqueda(objGeneralAnteproyecto.getAnio);
}

/**
 * Función para validar si esta vacio el importe principal
 * @param  {[type]} input Caja de texto para evaluar
 * @return {[type]}       [description]
 */
function fnCambioCantidadValidar(input) {
	// Funcion para validar si la cantidad principal esta vacía
	if (isEmpty(input.value)) {
		$("#"+input.name).val('0');
	}
}

/**
 * Función para mostrar los totales con formato
 * @param  {[type]} divMostrar Id del div a mostrar la información
 * @param  {Number} importe    Importe a mostrar
 * @return {[type]}            [description]
 */
function fnMostrarTotalFormato(divMostrar, importe = 0) {
	$('#'+divMostrar).empty();
	$('#'+divMostrar).html("$ "+formatoComas(fixDecimales( redondeaDecimal(importe)+"" )));	
}

/**
 * Función para asignar el nuevo monto general
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioCantidadGeneral(input) {
	// Asignar monto general
	objGeneralAnteproyecto.setTotalGeneral(input.value);

	// if (Number(objGeneralAnteproyecto.getTotalGeneral) > Number(0)) {
	// 	// Si capturo información
	// 	if (objGeneralAnteproyecto.getUtilizarSoloUnaFase == 1) {
	// 		// Mostrar panel ue
	// 		$("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).addClass("in");
	// 	} else {
	// 		// Mostrar panel ur
	// 		$("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).addClass("in");
	// 	}
	// }

	// Validaciones para generar información
	// fnValidacionesInformacion();
	objGeneralAnteproyecto.setErrorValidacion(0);
}

/**
 * Función cambio cantidad en los capitulos
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioCantidadCapitulo(input = '') {
	// Funcion para hacer usuma de información
	var dataJson = objGeneralAnteproyecto.getCapitulos;
	//console.log(dataJson);
	var total = 0;
	for (var key in dataJson) {
		var nombreElemento = 'Capitulo_'+dataJson[key].value;
		total = parseFloat(total) + parseFloat($("#"+nombreElemento).val());
		// Asignar valor, para almacenamiento
		dataJson[key].select = $("#"+nombreElemento).val();
	}

	// Asiganar nuevos valores
	objGeneralAnteproyecto.setCapitulos(dataJson);

	// Asignar suma de capitulos
	objGeneralAnteproyecto.setTotalCapitulos(total);

	if (Number(objGeneralAnteproyecto.getTotalCapitulos) > Number(0)) {
		// Mostrar capitulos
		$("#"+objGeneralAnteproyecto.getNomPanelUnidadResponsable).addClass("in");
	}

	// Mostrar total
	fnMostrarTotalFormato(objGeneralAnteproyecto.getNomDivTotalCapitulo, objGeneralAnteproyecto.getTotalCapitulos);

	// Validaciones para generar información
	// fnValidacionesInformacion();
	objGeneralAnteproyecto.setErrorValidacion(0);
}

/**
 * Función cambio cantidad de las unidades responsables
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioCantidadUr(input) {
	// Funcion para hacer usuma de información
	var dataJson = objGeneralAnteproyecto.getUr;
	var totalGeneral = 0;
	for (var key in dataJson) {
		var total = 0;
		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Sumar datos de los capitulos
			var nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
			var valor = $("#"+nombreElemento).val();


			totalGeneral = parseFloat(totalGeneral) + parseFloat($("#"+nombreElemento).val());
			total = parseFloat(total) + parseFloat($("#"+nombreElemento).val());
			
			dataJson[key].Capitulo[key2].select = $("#"+nombreElemento).val();
		}
		// Asignar valor, para almacenamiento
		var nombreElemento = 'UR_'+dataJson[key].value;
		dataJson[key].select = total;
    	$("#"+nombreElemento).val(formatoComas( redondeaDecimal( parseFloat(total))));
	}

	// Asiganar nuevos valores
	objGeneralAnteproyecto.setUr(dataJson);

	// Asignar suma de capitulos
	objGeneralAnteproyecto.setTotalUr(totalGeneral);

	if (Number(objGeneralAnteproyecto.getTotalUr) > Number(0)) {
		// Mostrar capitulos
		// $("#"+objGeneralAnteproyecto.getNomPanelUnidadEjecutora).addClass("in");
	}

	// Mostrar total
	fnMostrarTotalFormato(objGeneralAnteproyecto.getNomDivTotalUnidadResponsable, objGeneralAnteproyecto.getTotalUr);

	// Validaciones para generar información
	// fnValidacionesInformacion();
	objGeneralAnteproyecto.setErrorValidacion(0);
}

/**
 * Función cambio cantidad de las unidades ejecutoras
 * @param  {[type]} input Elemento html para obtener información
 * @return {[type]}       [description]
 */
function fnCambioCantidadUe(input) {
	// Funcion para hacer usuma de información
	var dataJson = objGeneralAnteproyecto.getUe;
	var totalGeneral = 0;
	for (var key in dataJson) {
		var total = 0;
		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Sumar datos de los capitulos
			var nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
			totalGeneral = parseFloat(totalGeneral) + parseFloat($("#"+nombreElemento).val());
			total = parseFloat(total) + parseFloat($("#"+nombreElemento).val());
			dataJson[key].Capitulo[key2].select = $("#"+nombreElemento).val();
		}
		// Asignar valor, para almacenamiento
		var nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2;
		dataJson[key].select = total;
      // var xxx =  formatoComas( redondeaDecimal( parseFloat(total)));
     //  console.log(xxx);
		$("#"+nombreElemento).val(formatoComas( redondeaDecimal( parseFloat(total))));
	}
	
	// Asiganar nuevos valores
	objGeneralAnteproyecto.setUe(dataJson);
	
	// Asignar suma de capitulos
	objGeneralAnteproyecto.setTotalUe(totalGeneral);

	// Mostrar total
	fnMostrarTotalFormato(objGeneralAnteproyecto.getNomDivTotalUnidadEjecutora, objGeneralAnteproyecto.getTotalUe);

	// Validaciones para generar información
	// fnValidacionesInformacion();
	objGeneralAnteproyecto.setErrorValidacion(0);
}

/**
 * Función para mostrar informacion de las unidades ejecutora
 * @param  {[type]} divMostrar        Id de la tabla a mostrar la información
 * @param  {[type]} dataJson JSON con la información
 * @return {[type]}                   [description]
 */
function fnMostrarInformacionUE(divMostrar, dataJson) {
	// console.log("fnMostrarInformacionCapitulo");
	// console.log("dataJson: "+JSON.stringify(dataJson));
	
	var style = 'style="text-align:center;"';
    var style2 = 'style="text-align:center; width:300px !important;"';
	var encabezado = '<tr class="header-verde">';
	encabezado += '<td></td><td '+style+'>UR</td><td '+style+'>Unidad Ejecutora</td><td></td><td '+style2+'>Importe</td>';

	var contenido = '';

	$('#'+divMostrar+' tbody').empty();
	var numLinea = 1;

	for (var key in dataJson) {
		// Recorrrer informacion de la ue
		var nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2;
		contenido += '<tr>';
		contenido += '<td '+style+'>'+numLinea+'</td>';
		contenido += '<td>'+dataJson[key].value+' - '+dataJson[key].texto+'</td>';
		contenido += '<td>'+dataJson[key].value2+' - '+dataJson[key].texto2+'</td>';
		contenido += '<td style="text-align: right;">$</td>';
		contenido += '<td '+style2+'>';
		contenido += '<component-text-label readonly="true" name="'+nombreElemento+'" id="'+nombreElemento+'" value="'+Math.abs('0')+'" onchange="fnCambioCantidadValidar(this), fnCambioCantidadUe(this)"></component-text-label>';
		contenido += '</td>';

		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Mostrar los capitulos en columnas
			if (numLinea == 1) {
				encabezado += '<td '+style+'>'+jsonCapitulos[key2].value+'</td>';
			}
			// Concatenar capitulo
			nombreElemento = 'UE_'+dataJson[key].value+'_'+dataJson[key].value2+'_'+jsonCapitulos[key2].value;
			contenido += '<td '+style+'>';
			contenido += '<component-number name="'+nombreElemento+'" id="'+nombreElemento+'" value="'+Math.abs('0')+'" onchange="fnCambioCantidadValidar(this), fnCambioCantidadUe(this)"></component-number>';
			contenido += '</td>';
		}

		contenido += '</tr>';

		numLinea ++;
	}

	encabezado += '</tr>';

	// Renglon de total
	// contenido += '<tr>';
	// contenido += '<td colspan="5" style="text-align:right;">Total</td>';
	// contenido += '<td style="text-align:right;" id="'+objGeneralAnteproyecto.getNomDivTotalUnidadEjecutora+'" name="'+objGeneralAnteproyecto.getNomDivTotalUnidadEjecutora+'">$ '+fixDecimales( redondeaDecimal(0)+"" )+'</td>';
	// contenido += '</tr>';

	contenido = encabezado + contenido;
	$('#'+divMostrar+' tbody').append(contenido);

	fnEjecutarVueGeneral(''+divMostrar);
}

/**
 * Función para mostrar informacion de las unidades responsables
 * @param  {[type]} divMostrar        Id de la tabla a mostrar la información
 * @param  {[type]} dataJson JSON con la información
 * @return {[type]}                   [description]
 */
function fnMostrarInformacionUR(divMostrar, dataJson) {
	// console.log("fnMostrarInformacionCapitulo");
	// console.log("dataJson: "+JSON.stringify(dataJson));
	
	var style = 'style="text-align:center;"';
	var style2 = 'style="text-align:center; width:300px !important;"';
	var encabezado = '<tr class="header-verde">';
	encabezado += '<td></td><td '+style+'>UR</td><td></td><td '+style2+'>Importe</td>';
	var contenido = '';

	$('#'+divMostrar+' tbody').empty();
	var numLinea = 1;

	for (var key in dataJson) {
		// console.log("texto: "+dataJsonCapitulos[key].texto);
		var nombreElemento = 'UR_'+dataJson[key].value;
		contenido += '<tr>';
		contenido += '<td '+style+'>'+numLinea+'</td>';
		contenido += '<td>'+dataJson[key].value+' - '+dataJson[key].texto+'</td>';
		// contenido += '<td>'+dataJson[key].value2+' - '+dataJson[key].texto2+'</td>';
		contenido += '<td style="text-align: right;">$</td>';
		contenido += '<td '+style2+'>';
		//contenido += '<component-number readonly="true" name="'+nombreElemento+'" id="'+nombreElemento+'" value="'+Math.abs('0')+'" onchange="fnCambioCantidadValidar(this), fnCambioCantidadUr(this)"></component-number>';
        contenido += '<component-text-label  readonly="true" name="'+nombreElemento+'" id="'+nombreElemento+'" value="'+Math.abs('0')+'" onchange="fnCambioCantidadValidar(this), fnCambioCantidadUr(this)"></component-text-label>';
        contenido += '</td>';

		var jsonCapitulos = dataJson[key].Capitulo;
		for (var key2 in jsonCapitulos) {
			// Mostrar los capitulos en columnas
			if (numLinea == 1) {
				encabezado += '<td '+style+'>'+jsonCapitulos[key2].value+'</td>';
			}
			// Concatenar capitulo
			nombreElemento = 'UR_'+dataJson[key].value+'_'+jsonCapitulos[key2].value;
			contenido += '<td '+style+'>';
			contenido += '<component-number name="'+nombreElemento+'" id="'+nombreElemento+'" value="'+Math.abs('0')+'" onchange="fnCambioCantidadValidar(this), fnCambioCantidadUr(this)"></component-number>';
			contenido += '</td>';
		}

		contenido += '</tr>';

		numLinea ++;
	}

	encabezado += '</tr>';

	// Renglon de total
	// contenido += '<tr>';
	// contenido += '<td colspan="5" style="text-align:right;">Total</td>';
	// contenido += '<td style="text-align:right;" id="'+objGeneralAnteproyecto.getNomDivTotalUnidadResponsable+'" name="'+objGeneralAnteproyecto.getNomDivTotalUnidadResponsable+'">$ '+fixDecimales( redondeaDecimal(0)+"" )+'</td>';
	// contenido += '</tr>';

	contenido = encabezado + contenido;
	$('#'+divMostrar+' tbody').append(contenido);

	fnEjecutarVueGeneral(''+divMostrar);
}

/**
 * Función para mostrar informacion de los capitulos
 * @param  {[type]} divMostrar        Id de la tabla a mostrar la información
 * @param  {[type]} dataJson JSON con la información
 * @return {[type]}                   [description]
 */
function fnMostrarInformacionCapitulo(divMostrar, dataJson) {
	// console.log("fnMostrarInformacionCapitulo");
	// console.log("dataJson: "+JSON.stringify(dataJson));
	
	var style = 'style="text-align:center;"';
	var encabezado = '<tr class="header-verde"><td></td><td '+style+'>Capítulo</td><td '+style+'>Descripción</td><td></td><td '+style+'>Importe</td></tr>';
	var contenido = '';

	$('#'+divMostrar+' tbody').empty();
	var numLinea = 1;

	for (var key in dataJson) {
		// console.log("texto: "+dataJson[key].texto);

		//console.log(dataJson[key].value); //formatoComas(redondeaDecimal( parseFloat(
		var nombreElemento = 'Capitulo_'+dataJson[key].value;
		contenido += '<tr>';
		contenido += '<td '+style+'>'+numLinea+'</td>';
		contenido += '<td '+style+'>'+dataJson[key].value+'</td>';
		contenido += '<td>'+dataJson[key].texto+'</td>';
		contenido += '<td style="text-align: right;">$</td>';
		contenido += '<td '+style+'>';
		contenido += '<component-number name="'+nombreElemento+'" id="'+nombreElemento+'" value="'+Math.abs('0')+'" onchange="fnCambioCantidadValidar(this), fnCambioCantidadCapitulo(this)"></component-number>';
		contenido += '</td>';
		contenido += '</tr>';

		numLinea ++;
	}

	// Renglon de total
	// contenido += '<tr>';
	// contenido += '<td colspan="4" style="text-align:right;">Total</td>';
	// contenido += '<td style="text-align:right;" id="'+objGeneralAnteproyecto.getNomDivTotalCapitulo+'" name="'+objGeneralAnteproyecto.getNomDivTotalCapitulo+'">$ '+fixDecimales( redondeaDecimal(0)+"" )+'</td>';
	// contenido += '</tr>';

	contenido = encabezado + contenido;
	$('#'+divMostrar+' tbody').append(contenido);

	fnEjecutarVueGeneral(''+divMostrar);
}

/**
 * Función para obtener información general
 * @return {[type]} [description]
 */
function fnInfoAnteProyectoGeneral() {
	// console.log("fnInfoAnteProyectoGeneral");
	
	muestraCargandoGeneral();

	dataObj = { 
	      option: 'mostrarDatosGenerales'
	    };
	$.ajax({
		async:false,
	    cache:false,
	    method: "POST",
	    dataType:"json",
	    url: "modelo/anteproyectoModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			// Datos a capturar
			objGeneralAnteproyecto.setCapitulos(data.contenido.datos.infoCapitulo);
			objGeneralAnteproyecto.setUr(data.contenido.datos.infoUR);
			objGeneralAnteproyecto.setUe(data.contenido.datos.infoUE);
			//console.log(data.contenido.datos.infoUE);
			objGeneralAnteproyecto.setPartidasEsp(data.contenido.datos.infoPartidas);
			// Permisos para paneles
			objGeneralAnteproyecto.setPerEncabezado(data.contenido.datos.perEncabezado);
			objGeneralAnteproyecto.setPerTechos(data.contenido.datos.perTechos);
			objGeneralAnteproyecto.setPerClaves(data.contenido.datos.perClaves);
			ocultaCargandoGeneral();
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

/**
 * Función para obtener los botones de funcionalidad
 * @param  {[type]} divMostrar Recibe id del div donde se va a pintar la información
 * @return {[type]}            [description]
 */
function fnObtenerBotones(divMostrar = '') {
	//Opcion para operacion
	var verDatos = 0;
	// if (autorizarGeneral == 1 && permisoEditarEstCapturado == 0) {
	// 	verDatos = 1;
	// }
	soloActFoliosAutorizada = 0;
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
	      url: "modelo/anteproyectoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	//console.log("botones: "+JSON.stringify(info));
	    	var contenido = '';
	    	var jsonDatosBotones = new Array();
	    	for (var key in info) {
	    		var funciones = '';
	    		if (info[key].statusid == 1) {
	    			funciones = 'fnAlmacenarCaptura('+info[key].statusid+')';
	    		} else if (info[key].statusid == 4) {
	    			funciones = 'fnAutorizarCapitulo('+info[key].statusid+')';
	    		} else if (info[key].statusid == 95) {
	    			funciones = 'fnGenerarCsvClaveCorta()';
	    		} else if (info[key].statusid == 96) {
	    			funciones = 'fnGenerarCsvClaveLarga()';
	    		} else if (info[key].statusid == 99) {
	    			funciones = 'fnCambiarEstatusCaptura('+info[key].statusid+')';
	    		}

	    		if (info[key].statusid == 0) {
	    			funciones = 'fnRegresarPanel()';
	    		}

	    		// Informacion
				var obj = new Object();
				obj.namebutton2 = info[key].namebutton2;
				jsonDatosBotones.push(obj);

	    		// Mostrar botones
    			contenido += '&nbsp;&nbsp;&nbsp; \
    			<component-button id="'+info[key].namebutton2+'" name="'+info[key].namebutton2+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
	    	}

	    	// Agregar datos
	    	objGeneralAnteproyecto.setInfoBotones(jsonDatosBotones);

	    	// Mostrar botones
	    	$('#'+divMostrar).append(contenido);
	    	fnEjecutarVueGeneral('divBotones');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}