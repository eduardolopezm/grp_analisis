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
var totalCompras = 0;
var tituloDiferencia = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
var mensajeDiferencia = "";
var decimales = 2;

// Se declara enla vista
//var type = 263;
//var transno = <?php echo $_SESSION['noCaptura']; ?>;

// Filtro Generales
var legalid = "";
var tagref = "";

var ramoDefault = "08";

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

	if (document.querySelector(".selectRamo")) {
		dataObj = { 
	        option: 'mostrarRamo'
	    };
		fnSelectGeneralDatosAjax('.selectRamo', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, ramoDefault);
	}

	//Obtener Datos de un No. Captura
	fnObtenerInfoNoCaptura();

	// Deshabilitar pagina
	fnDeshabilitaPagSuficiencia();

	// Deshabilitar meses que no son del periodo de la requisicion
	fnDeshabilitarMesesRequisicion(type, transno);
	
	if (suficienciaCancelada == 1) {
		fnMostrarInformacionCancelar();
	}

	// Datos lista de busqueda
	fnObtenerPresupuestoBusqueda();
});

function fnMostrarInformacionCancelar() {
	// Muestra información para Generar la nueva Suficiencia
	//console.log("fnMostrarInformacionCancelar");

	document.getElementById("panelSuficienciaInfo").style.display = "block";

	// Id de clave para encabezado
	idClavePresupuestoReducciones = 0;
	numLineaReducciones = 1;
	totalReducciones = 0;
	// console.log("datosReducciones: "+JSON.stringify(datosReducciones));
	for (var key in datosReducciones) {
		fnMostrarPresupuesto(datosReducciones[key], 'tablaReduccionesInfo', 10);
	}
}

function fnDeshabilitarMesesRequisicion(type, transno) {
	// console.log("fnDeshabilitarMesesRequisicion: type: "+type+" - transno: "+transno);

	//Opcion para operacion
	dataObj = { 
	        option: 'obtenerMesRequisicion',
	        type: type,
			transno: transno
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/suficiencia_manual_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	if (data.contenido.datos.tipoSuficiencia == 1 || data.contenido.datos.tipoSuficiencia == 3) {
	    		// Si es automatica deshabilitar meses
	    		// console.log("nombreMes: "+data.contenido.datos.mesName);
	    		fnDesMesSuficienciaAutomatica(data.contenido.datos.mesName);
	    	}else{
	    		//console.log("Manual");
	    		fnDeshabesDesActualRed();
	    	}
	    }else{
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ocurrio un problema al deshabilitar los meses que no corresponden a la suficiencia</p>');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
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
                if (autorizarGeneral == 1) {
                	// Si se va autorizar o esta autorizado
                	$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
                	// console.log(nombreMes+": if 1");
                } 
				// else if (Number(mesActualAdecuacion) < Number(numMes)) {
				// 	totalReducciones = parseFloat(totalReducciones) - parseFloat(dataJsonReducciones[key2][nombreMesSel]);
				// 	fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
				// 	dataJsonReducciones[key2][nombreMesSel] = 0;
				// 	$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).val("0");
				// 	$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
				// 	console.log(nombreMes+": if 2");
				// }
				else{
					$("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", false);
					// console.log(nombreMes+": if 3");
				}
				numMes ++;
            }
		}
	}

	return mensaje;
}

function fnDesMesSuficienciaAutomatica(mesSuficiencia) {
	// Deshabilita meses despues del actual en Reducciones
    var mensaje = "";
    for (var key in datosReducciones) {
        for (var key2 in datosReducciones[key]) {
            var dataJsonReducciones = datosReducciones[key];
            var numMes = 1;
            for (var mes in dataJsonMeses) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes] + "Sel";
                var nombreMesCompra = dataJsonMeses[mes] + "Compra";

                if (mesSuficiencia == nombreMes) {
                    //console.log("Que traes aqui deberíamos de sumar? "+dataJsonReducciones[key2][nombreMesCompra]);
                    if (parseFloat(dataJsonReducciones[key2][nombreMesCompra]) != 0) {
                        totalCompras += parseFloat(dataJsonReducciones[key2][nombreMesCompra]);
                    }
                }

                if (mesSuficiencia != nombreMes) {
                    totalReducciones = parseFloat(totalReducciones) - parseFloat(dataJsonReducciones[key2][nombreMesSel]);

                    //fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
                    dataJsonReducciones[key2][nombreMesSel] = 0;
                    $("#" + dataJsonReducciones[key2].accountcode + "_" + panelReducciones + "_" + nombreMes).val("0");
                    $("#" + dataJsonReducciones[key2].accountcode + "_" + panelReducciones + "_" + nombreMes).prop("disabled", true);
                } else if (autorizarGeneral == 1) {
                    // Cuando esta cancelada o autorizada deshabilitar el mes de la suficiencia
                    $("#" + dataJsonReducciones[key2].accountcode + "_" + panelReducciones + "_" + nombreMes).prop("disabled", true);
                } else {
                    $("#" + dataJsonReducciones[key2].accountcode + "_" + panelReducciones + "_" + nombreMes).prop("disabled", false);
                }

                numMes++;
            }
        }
    }

    if (totalCompras != 0) {
        fnMostrarTotalAmpRed('txtTotalReducciones', totalCompras);
        if(mensajeDiferencia != ""){
            setTimeout(function(){
                ocultaCargandoGeneral();
                fnMuestraModalDiferencia();
                
            }, 4700);
            
        }
        
    }

    return mensaje;
}

function fnDeshabilitaPagSuficiencia() {
	// Se va autorizar y deshabilitar pagina
	if (autorizarGeneral == 1 || (tipoSuficiencia != 2 && tipoSuficiencia != 0)) {
		// Deshabilitar
		$("#txtBuscarReducciones").prop("disabled", true);

		$('#selectRazonSocial').multiselect('disable');
		$('#selectUnidadNegocio').multiselect('disable');
		$('#selectUnidadEjecutora').multiselect('disable');

		$("#txtJustificacion").prop("disabled", true);
	}
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
}

function fnCambioUnidadNegocio() {
	//console.log("fnCambioUnidadNegocio");
	tagref = $("#selectUnidadNegocio").val();
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
		// muestraMensaje('Seleccionar UR para continuar con el proceso', 3, 'ModalGeneral_Advertencia', 5000);
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

	if (claveMensaje != '') {
		// Si existe una clave con cantidad en 0
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, claveMensaje);
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

	//Opcion para operacion
	dataObj = { 
	        option: 'guardarSuficiencia',
	        datosCapturaReducciones: datosCapturaReducciones, //JSON.parse(JSON.stringify(datosCapturaReducciones)), 
			datosCapturaAmpliaciones: datosCapturaAmpliaciones,
			type: type,
			transno: transno,
			legalid: legalid,
			tagref: tagref,
			estatus: statusGuardar,
			fechaCaptura: $('#txtFechaCaptura').val(),
			suficienciaCancelada: suficienciaCancelada,
			justificacion: $('#txtJustificacion').val(),
			ue: ue
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/suficiencia_manual_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
		suficienciaCancelada = 0;
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	transno = data.contenido.datos.transno;
	    	if (transno != 0) {
				$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.datos.transno);

				if (data.contenido.datos.transnoNuevo != 0) {
					transno = data.contenido.datos.transnoNuevo;

					document.getElementById("panelSuficienciaInfo").style.display = "none";
					$('#txtNoCaptura').empty();
					$('#txtNoCaptura').append(transno);

					var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
					muestraModalGeneral(4, titulo, 'Se creó el Folio '+transno+' para la Requisición '+nuRequisicionSuf);
				}else if (msjvalidaciones != "") {
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
	    	}
	    }else{
	    	//Obtener Datos de un No. Captura
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
					$('#txtBuscarReducciones').val("");
					fnObtenerPresupuesto(datosPresupuestosBusqueda[key].accountcode, idTabla, panel, datos[1], 'Nuevo');
					break;
				}
			}
        }
        return false;
    }
}

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
		url: "modelo/suficiencia_manual_modelo.php",
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

					$('#txtJustificacion').val(""+data.contenido.justificacion);
					
					if (data.contenido.legalid != "") {
						legalid = data.contenido.legalid;
						$('#selectRazonSocial').selectpicker('val', ''+data.contenido.legalid);
						$("#selectRazonSocial").multiselect("refresh");
						$(".selectRazonSocial").css("display", "none");
					}
					if (data.contenido.tagref != "") {
						tagref = data.contenido.tagref;
						
						//Opcion para operacion
						dataObj = { 
						      option: 'mostrarUnidadNegocio',
						      legalid: ''
						    };

						fnSelectGeneralDatosAjax('#selectUnidadNegocio', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 1, tagref);
					}
					if (data.contenido.ln_ue != "") {
						//Opcion para operacion
						dataObj = {
					        option: 'mostrarUnidadEjecutora',
					        tagref: data.contenido.tagref
					    };
					    $('#selectUnidadEjecutora').empty();
					    $('#selectUnidadEjecutora').append('<option value="-1">Seleccionar...</option>');
						fnSelectGeneralDatosAjax('#selectUnidadEjecutora', dataObj, 'modelo/GLBudgetsByTagV2_modelo.php', 0, data.contenido.ln_ue);
					}
				}
				// console.log("primero dataJson: "+JSON.stringify(dataJson));
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

		    	fnObtenerPresupuestoBusqueda();

		    	if (datosReducciones.length > 0 && usuarioOficinaCentral != 1) {
		    		// Si agrego datos deshabilitar UE
		    		$('#selectUnidadEjecutora').multiselect('disable');
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
	    	validacionClave = fnValidarClave(clave, datosReducciones, panel, "Ya existe la Clave Presupuestal "+clave+" en la linea "+$("#Renglon_"+clave+"_"+panel).html());
    		if (validacionClave) {
    			datosReducciones.push(info);
    		}
	    	
	    	if (validacionClave) {
	    		fnMostrarPresupuesto(info, idTabla, panel);
	    	}

	    	if (datosReducciones.length > 0 && usuarioOficinaCentral != 1) {
	    		// Si agrego datos deshabilitar UE
	    		$('#selectUnidadEjecutora').multiselect('disable');
	    	}
	    	
	    	//ocultaCargandoGeneral();
	    }else{
	    	//ocultaCargandoGeneral();
	    	// muestraMensaje(data.Mensaje, 3, 'divMensajeOperacion', 5000);
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
	console.log("clavePresupuesto: "+clavePresupuesto+" - panel: "+panel);
	console.log("caja: "+input.name+" - "+input.value);
	
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
					for (var mes in dataJsonMeses ) {
						// Nombres de los mes
		                var nombreMes = dataJsonMeses[mes];
		                if (nombreMes == nombreInput[2]) {
		                	//console.log("nombreMes: "+nombreMes+" disponible: "+dataJson2[key2][nombreMes]+" - input: "+input.value);
		                	var totalMesSuf = parseFloat(dataJson2[key2][nombreMes]);
                            // console.log("Mes Suficiencia: "+totalMesSuf);
		                	if (suficienciaCancelada == 1) {
		                		totalMesSuf = parseFloat(totalMesSuf) + parseFloat(dataJson2[key2][nombreMes+"Suficiencia"]);
		                	}
		                	if ((parseFloat(totalMesSuf) < parseFloat(input.value != "" ? input.value : 0))) {
								muestraMensaje('En '+nombreMes+' el disponible es '+totalMesSuf+' para la clave '+clavePresupuesto, 3, 'divMensajeOperacionReducciones', 5000);
		                		$('#'+clavePresupuesto+"_"+panel+"_"+nombreMes).val(""+dataJson2[key2][nombreMes+"Sel"]);
		                	}else if (
		                		suficienciaCancelada == 1 
		                		&& parseFloat(input.value != "" ? input.value : 0) != parseFloat(dataJson2[key2][nombreMes+"Compra"])) {
		                		muestraMensaje('En la Clave Presupuestal '+dataJson2[key2].accountcode+' el Total de la Requisición es $ '+parseFloat(dataJson2[key2][nombreMes+"Compra"]), 3, 'divMensajeOperacionReducciones', 5000);
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

function fnMostrarTotalAmpRed(divNombre, total) {
	$('#'+divNombre).empty();
	//$('#'+divNombre).html(""+total);
	$('#'+divNombre).html(""+ formatoComas( redondeaDecimal( total ) ) );
}

/**
 * Función para calcular los totales por clave presupuestal y por mes
 * @return {[type]} [description]
 */
function fnCalcularTotalesClaveRenglon() {
	// Calcular totales por clave y renglon
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

	        }
	        // Mostrar el total por clave presupuestal
	        var nombreDivTotal = 'divTotal_'+panelReducciones+'_'+dataJsonReducciones[key2].accountcode+'_'+numLinea;
	        $("#"+nombreDivTotal).empty();
	        $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );

	        numLinea ++;
		}
	}
}

function fnMostrarPresupuesto(dataJson, idTabla, panel) {
	var encabezado = '';
	var contenido = '';
	var enca = 1;
	var style = 'style="text-align:center;"';
	var styleMeses = 'style="text-align:center;"';
	var nombreSelect = "";
	var tipoAfectacion = "";
	var clavePresupuesto = "";
	var partida = "";

	// console.log("fnMostrarPresupuesto dataJson: "+JSON.stringify(dataJson));

	// numLineaReducciones
	for (var key in dataJson) {
		tipoAfectacion = dataJson[key].tipoAfectacion;
		clavePresupuesto = dataJson[key].accountcode;
        partida = dataJson[key].partida_esp;

		var total = 0;
        //var totalCompra = 0;
		for (var mes in dataJsonMeses ) {
			// Nombres de los mes
			var nombreMes = dataJsonMeses[mes];
			total = parseFloat(total) + parseFloat(dataJson[key][nombreMes+"Sel"]);
			//totalCompra = parseFloat(totalCompra) + parseFloat(dataJson[key][nombreMes+"Compra"]);
		}

		if (idClavePresupuestoReducciones != dataJson[key].idClavePresupuesto) {
			idClavePresupuestoReducciones = dataJson[key].idClavePresupuesto;
			enca = 0;
		}
		totalReducciones += parseFloat(total);

		if (enca == 0) {
			encabezado += '<tr class="header-verde"><td></td><td></td>';
		}

		if (autorizarGeneral == 1 || (tipoSuficiencia != 2 && tipoSuficiencia != 0)) {
			contenido += '<td></td>';
		}else{
			contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\')"></button></td>';
		}

		var deshabilitarElemento = '';
		if (autorizarGeneral == 1 || (tipoSuficiencia != 2 && tipoSuficiencia != 0)) {
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

		// if (enca == 0) {
		// 	for (var mes in dataJsonMeses ) {
		// 		// Nombres de los mes para el encabezado
		// 		var nombreMes = dataJsonMeses[mes];
		// 		encabezado += '<td '+styleMeses+'>'+nombreMes+'</td>';
		// 	}
		// }

		//console.log("JSONMESES: "+JSON.stringify(dataJsonMeses));

		var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_"; // No cambiar estructura de nombre o cambiar tambien en fnGuardarSeleccionado()
		//console.log("Total: "+totalReducciones);
		/*
		* DEFINIMOS DOS VARIABLES PARA GUARDAR EL
		* DISPONIBLE DE LA SUFICIENCIA Y EL MONTO
		* NUEVO EL DE COMPRA PARA HACER LA COMPARATIVA
		* SI ES MAYOR LA COMPRA AL DISPONIBLE
		* MOSTRAMOS LA MODAL CORRESPONDIENTE
		* 
		*/
		
		if (enca == 0) {
			// Columna para total por clave, encabezado
			encabezado += '<td '+styleMeses+'>Total</td>';
		}
		// Columna para total por clave, información
		var nombreDivTotal = 'divTotal_'+panel+'_'+dataJson[key].accountcode+'_'+numLineaReducciones;
		contenido += '<td '+style+' id="'+nombreDivTotal+'">$ '+formatoComas( redondeaDecimal( 0 ) )+'</td>';

		var cantidadMesCompraUno = 0;
		var disponibleInicial = 0;
		//console.log("nombreInputMeses: "+nombreInputMeses);
		for (var mes in dataJsonMeses ) {
			// Informacion meses para seleccion
			var nombreMes = dataJsonMeses[mes];
			var nombreMesSel = dataJsonMeses[mes]+"Sel";
			var nombreMesCompra = dataJsonMeses[mes]+"Compra";
			var cantidadMes = parseFloat(dataJson[key][nombreMes]);
			//console.log("nombreMes: "+nombreMes);
			//console.log("nombreMesSel: "+nombreMesSel);
			if(parseFloat(dataJson[key][nombreMesCompra]) != 0 && panel == 1){
				cantidadMesCompraUno = parseFloat(dataJson[key][nombreMesCompra]);
				disponibleInicial = parseFloat(dataJson[key][nombreMes]) + totalReducciones;
				/*console.log("CantidadMesCompraUno: "+cantidadMesCompraUno);
				console.log("DisponibleInicial: "+disponibleInicial);*/
				if(cantidadMesCompraUno > disponibleInicial){
					mensajeDiferencia += "<p>Para la partida => "+partida+" del monto nuevo => "+cantidadMesCompraUno+" supera al monto disponible <br>";
					mensajeDiferencia += disponibleInicial+" de la partida mencionada</p>";
				}
				/*var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p>Se va a eliminar la Clave Presupuestal '+clave+'</p>';
				muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\')');*/
			}
                        
			var informacionCancelar = "";
			var styleOcultarMes = 'style="text-align:center;"';
			var styleInputText = ' style="width: 80px;" ';
			if (nuRequisicionSuf != '0') {
				// Si tiene requisicion habilitar el mes de la requisicion
				styleOcultarMes = 'style="text-align:center; display: none;"';
				// console.log(nombreMesCompra+" Compra: "+Math.abs(dataJson[key][nombreMesCompra]));
				if (Math.abs(dataJson[key][nombreMesCompra]) != '0') {
					styleOcultarMes = 'style="text-align:center; align-content: center;"';
					styleInputText = ' style="width: 100px;" ';
					informacionCancelar += " <br> Debe Ser $ "+Math.abs(dataJson[key][nombreMesCompra]);
				}
			}

			if (enca == 0) {
				// Nombres de los mes para el encabezado
				encabezado += '<td '+styleOcultarMes+'>'+nombreMes+'</td>';
			}
                        
			if(panel == 1){
				if(tipoSuficiencia == 2){
					contenido += '<td align="center" '+styleOcultarMes+'>$ '+(parseFloat(dataJson[key][nombreMes]))+'<br>';
				}else{
					contenido += '<td align="center" '+styleOcultarMes+'>$ '+(parseFloat(dataJson[key][nombreMes]) - (parseFloat(dataJson[key][nombreMesCompra]) - parseFloat(dataJson[key][nombreMesSel])))+informacionCancelar+'<br>';    
				}
			}else{
				contenido += '<td align="center" '+styleOcultarMes+'>$ '+dataJson[key][nombreMes]+informacionCancelar+'<br>';
			}          
			
			if(tipoSuficiencia != 2){
				contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesCompra])+'" onblur="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')"></component-decimales>';
			}else{
				contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onblur="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')"></component-decimales>';    
			}
			
			// console.log(nombreMes+": "+Math.abs(dataJson[key][nombreMes]));
			// console.log(nombreMes+" sel: "+Math.abs(dataJson[key][nombreMesSel]));
			// console.log("styleOcultarMes: "+styleOcultarMes);

			contenido += '</td>';
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
    fnMostrarTotalAmpRed('txtTotalReduccionesInfo', totalReducciones);

	$('#'+idTabla+' tbody').append(contenido);

	fnEjecutarVueGeneral('RenglonTR_'+clavePresupuesto+'_'+panel);

	if (tipoSuficiencia == 2) {
		// Si es manual
		fnDeshabesDesActualRed();
	}
}

function fnValidarClave(clave, dataJson, panel, mensaje) {
	for (var key in dataJson) {
		for (var key2 in dataJson[key]) {
			var dataJson2 = dataJson[key];
			//console.log("datos: "+JSON.stringify(dataJson2[key2]));
			if (dataJson2[key2].accountcode == clave) {
				muestraMensaje(mensaje, 3, 'divMensajeOperacionReducciones', 5000);
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
	      url: "modelo/suficiencia_manual_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("data: "+JSON.stringify(data));
	    if(data.result){
	    	// muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
	    	// var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    	// muestraModalGeneral(3, titulo, data.contenido);
	    	respuesta = true;
	    }else{
	    	// muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
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
}

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

	if (datosReducciones.length == 0 && usuarioOficinaCentral != 1) {
		// Si agrego datos deshabilitar UE
		$('#selectUnidadEjecutora').multiselect('enable');
	}

	// Calcular totales por clave y renglon
	fnCalcularTotalesClaveRenglon();
}

function fnConfirmarCancelacionSuficiencia(statusid) {
	// Confirmar cancelacion de Suficiencia, Para crear una Nueva
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = "<h4>Se va a Cancelar el Folio "+transno+", Creando un nuevo Folio para la Requisición "+nuRequisicionSuf+"</h4>";
	muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnAlmacenarCaptura('"+statusid+"')");
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
	      url: "modelo/suficiencia_manual_modelo.php",
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
	    		if (info[key].statusid == 1 && suficienciaCancelada != 1) {
	    			statusGuardar = info[key].statusid;
	    			funciones = 'fnAlmacenarCaptura('+statusGuardar+')';
	    		}
	    		if (info[key].statusid == 1 && suficienciaCancelada == 1) {
	    			statusGuardar = info[key].statusid;
	    			funciones = 'fnConfirmarCancelacionSuficiencia('+statusGuardar+')';
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

function fnRegresarPanel() {
	// Al cancelar regresar al panel
	var Link_Panel = document.getElementById("linkPanelAdecuaciones");
	Link_Panel.click();
}

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
      url: "modelo/suficiencia_manual_modelo.php",
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

function fnMuestraModalDiferencia(){
    //muestraModalGeneral(Size = 4, titulo = "", mensaje = "", pie = "", funcion = "")
    //muestraModalGeneral(3, tituloDiferencia, mensajeDiferencia+"<br><p>No se podrá generar la orden de compra</p>", "", 'fnRegresarPanel()');
    muestraModalGeneral(3, tituloDiferencia, mensajeDiferencia+"<br><p>No se podrá generar la orden de compra</p>", "", '');
    
    $("#Guardar").addClass("disabled");
    $("#Guardar").attr("onclick","");
    //muestraModalGeneralConfirmacion(3, tituloDiferencia, mensajeDiferencia, '', 'fnRegresarPanel()');
}