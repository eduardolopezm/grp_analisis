/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

// Nombre de la vista para la tabla de Reduccion y Ampliacion
var tablaReducciones = "tablaReducciones";
var idClavePresupuestoReducciones = 0;
var datosReducciones = new Array();
var panelReducciones = 1; 

// Filtro Generales
var legalid = "";
var tagref = "";

// Estatus para guardar
var statusGuardar = 1;

// Numero de Linea en Reduccion y Ampliacion
var numLineaReducciones = 1;

$( document ).ready(function() {
	// Datos botones
	fnObtenerBotones('divBotones');

	//Obtener Datos de un No. Captura
	

	// Deshabilitar pagina
	fnDeshabilitaPagSuficiencia();

	$("#btnBusqueda").click(function() {
		// Buscar información de las retenciones
		if (datosReducciones.length > 0) {
			// Si tiene infomacion
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p>¿Desea Cargar la información nuevamente?</p>';
			muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "fnObtenerActivos()");
		} else {
			// Si no tiene obtener activos
			fnObtenerActivos();
		}
	});


	$(' #selectAlmacen, #selectClaveCABMS').multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });

    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

    if(fnObtenerOption('selectUnidadNegocio') !="" && fnObtenerOption('selectUnidadEjecutora') !=""){
    	fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora'), 'selectAlmacen');
    }

    $('#selectPartidaEsp').change(function(){
		fnCargarCabms();
    });

    fnObtenerInfoNoCaptura();

    $('#selectUnidadEjecutora').change(function(){
		fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora'), 'selectAlmacen');
    });


});

function fnCargarCabms(){
	var sqlCAMBS="SELECT eq_stockid as valor, concat(eq_stockid,' - ', descPartidaEspecifica) as descripcion FROM tb_partida_articulo WHERE partidaEspecifica in ("+ fnObtenerOption('selectPartidaEsp') +");";
    fnLlenarSelect2(sqlCAMBS,'selectClaveCABMS');
}

function fnLlenarSelect2(SQL,componente){
    $('#' + componente).empty();
    $('#' + componente).multiselect('rebuild');

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/componentes_modelo.php",
        data: {
            option: 'llenarSelect',
            strSQL: SQL
        },
        async: false

    }).done(function(data) {
        if(!data.result){return;}
        var options='';
        
        //options = '<option value="-1">Seleccionar...</option>';

        $.each(data.contenido,function(index, el) {
            options += '<option value="'+el.val+'">'+el.text+'</option>';
        });

        // console.log(options);
        // console.log(componente);
        $('#' + componente).empty();
        $('#' + componente).append(options);
        $('#' + componente).multiselect('rebuild');
    });
}

// function fnObtenerOption(componenteOrigen) {
//     var option = "";
//     var selectComponenteOrigen = document.getElementById('' + componenteOrigen);

//     for (var i = 0; i < selectComponenteOrigen.selectedOptions.length; i++) {
//         //console.log( unidadesnegocio.selectedOptions[i].value);
//         if (selectComponenteOrigen.selectedOptions[i].value != "-1") {
//             if (i == 0) {
//                 option = "'" + selectComponenteOrigen.selectedOptions[i].value + "'";
//             } else {
//                 option = option + ", '" + selectComponenteOrigen.selectedOptions[i].value + "'";
//             }
//         }
//     }
//     console.log(option);
//     return option;
// }
function fnObtenerOption(componenteSelect, intComillas = 0){
	var valores = "";
	var comillas="'";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {

        	//intComillas = 1 No agregar las comillas
        	if(intComillas == 1){
        		comillas="";
            }

            // Que no se opcion por default
            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+","+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }

    return valores;
}


function fnObtenerActivos() {
	// funcion para obtener activos
	muestraCargandoGeneral();

	var partidas = "";
	var selectPartidaEsp = document.getElementById('selectPartidaEsp');
    for ( var i = 0; i < selectPartidaEsp.selectedOptions.length; i++) {
        if (i == 0) {
            partidas = "'"+selectPartidaEsp.selectedOptions[i].value+"'";
        }else{
            partidas = partidas+", '"+selectPartidaEsp.selectedOptions[i].value+"'";
        }
    }

    var almacenes ="";
    var selectAlmacen = document.getElementById('selectAlmacen');
    for ( var i = 0; i < selectAlmacen.selectedOptions.length; i++) {
        if (i == 0) {
            almacenes = "'"+selectAlmacen.selectedOptions[i].value+"'";
        }else{
            almacenes = almacenes+", '"+selectAlmacen.selectedOptions[i].value+"'";
        }
    }

    var cambs ="";
    var selectCambs = document.getElementById('selectClaveCABMS');
    for ( var i = 0; i < selectCambs.selectedOptions.length; i++) {
        if (i == 0) {
            cambs = "'"+selectCambs.selectedOptions[i].value+"'";
        }else{
            cambs = cambs+", '"+selectCambs.selectedOptions[i].value+"'";
        }
    }


	dataObj = { 
	        option: 'obtenerActivos',
	        tagref: $('#selectUnidadNegocio').val(),
			ue: $('#selectUnidadEjecutora').val(),
			selectTipo: $('#selectTipo').val(),
			selectPartidaEsp: partidas,
			type: type,
			transno: transno,
			cambs: cambs,
			almacenes: almacenes
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/bajaPratrimonialModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	// console.log("info: "+JSON.stringify(info));

	    	datosReducciones = data.contenido.datos;

	    	$('#'+tablaReducciones+' tbody').empty();
	    	idClavePresupuestoReducciones = 0;
			numLineaReducciones = 1;
	    	for (var key in datosReducciones) {
	    		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	    	}

	    	if (datosReducciones.length > 0) {
	    		$('#selectUnidadNegocio').multiselect('disable');
	    		$('#selectUnidadEjecutora').multiselect('disable');
	    		$('#selectTipo').multiselect('disable');
	    	} else {
	    		$('#selectUnidadNegocio').multiselect('enable');
	    		$('#selectUnidadEjecutora').multiselect('enable');
	    		$('#selectTipo').multiselect('enable');
	    	}
	    	
	    	ocultaCargandoGeneral();
	    }else{
	    	ocultaCargandoGeneral();
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+data.Mensaje+'</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
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
			transno: transno
	      };
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/bajaPratrimonialModelo.php",
		data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	datosReducciones=data.contenido.datos;
	    	
	    	if (data.contenido.transno != 0) {
	    		$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.transno);
				statusGuardar = data.contenido.estatus;

				console.log(statusGuardar);

				$('#txtEstatus').empty();
				if (data.contenido.statusname != "" || data.contenido.statusname != null) {
					$('#txtEstatus').append(""+data.contenido.statusname);
				}
				
				$('#txtFechaCaptura').val("");
				if (data.contenido.fechaCaptura != "" || data.contenido.fechaCaptura != null) {
					$('#txtFechaCaptura').val(""+data.contenido.fechaCaptura);
				}

				if (data.contenido.selectTipo != "" || data.contenido.selectTipo != null) {
					$('#selectTipo').val(''+data.contenido.selectTipo);
					$('#selectTipo').multiselect('rebuild');

					// Deshabilitar operación
					$('#selectTipo').multiselect('disable');
				}

				if (data.contenido.selectPartidaEsp != "" || data.contenido.selectPartidaEsp != null) {
					var partidaEspecificaBaja = data.contenido.selectPartidaEsp;
					//console.log(partidaEspecificaBaja);
		        	var arrPartidaEspecificaBaja = partidaEspecificaBaja.split(",");
		        	//console.log(arrPartidaEspecificaBaja);
					$('#selectPartidaEsp').val(arrPartidaEspecificaBaja);
					$('#selectPartidaEsp').multiselect('rebuild');

					//console.log($('#selectPartidaEsp').val());
				}

				$('#txtJustificacion').val(""+data.contenido.justificacion);
				
				if (data.contenido.legalid != "" || data.contenido.legalid != null) {
					legalid = data.contenido.legalid;
					$('#selectRazonSocial').val(''+data.contenido.legalid);
					$('#selectRazonSocial').multiselect('rebuild');
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
				$('#selectUnidadNegocio').multiselect('disable');
				$('#selectUnidadEjecutora').multiselect('disable');


				/*==== Cabms ====*/
				fnCargarCabms();
		        var cabmsBaja = data.contenido.cabms;
		        var arrCabms = cabmsBaja.split(",");
		        $("#selectClaveCABMS").val(arrCabms);
		        $('#selectClaveCABMS').multiselect('rebuild');

		        fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora'), 'selectAlmacen'); 
		        /*==== Almacenes ====*/
		        var almacenesBaja = data.contenido.almacenes;
		        var arrAlmacenesBaja = almacenesBaja.split(",");
		        $("#selectAlmacen").val(arrAlmacenesBaja);
		        $('#selectAlmacen').multiselect('rebuild');

		        $('#txtFechaBaja').val("");
				if (data.contenido.fechaCaptura != "" || data.contenido.fechaBaja != null) {
					$('#txtFechaBaja').val(""+data.contenido.fechaBaja);
				}
			}

			// console.log("datosReducciones: "+JSON.stringify(datosReducciones));
			$('#'+tablaReducciones+' tbody').empty();
	    	idClavePresupuestoReducciones = 0;
			numLineaReducciones = 1;
	    	for (var key in datosReducciones ) {
				fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
			}

	    	if (datosReducciones.length > 0) {
	    		// Si agrego datos deshabilitar UE
	    		$('#selectUnidadNegocio').multiselect('disable');
				$('#selectUnidadEjecutora').multiselect('disable');
				$('#selectTipo').multiselect('disable');
	    	}

	    	if(statusGuardar >= '4'){

	    		fnEstatusCampos();
                fnBloquearDivs("PanelBusqueda");
                fnBloquearDivs("PanelReducciones");
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

function fnEstatusCampos(estatusInput = true, statusCombo='disable'){
	$('#PanelBusqueda input[type="text"], input[type="hidden"], textarea').each(
        function(index){  
            var input = $(this);
            input.prop('disabled', estatusInput);
        }
    );

    $('#PanelBusqueda select').each(
        function(index){  
            var combo = $(this);
            combo.multiselect(statusCombo);
        }
    );
}


/**
 * Función para deshabilitar pagina
 * @return {[type]} [description]
 */
function fnDeshabilitaPagSuficiencia() {
	// Se va autorizar y deshabilitar pagina
	if (autorizarGeneral == 1) {
		$('#selectRazonSocial').multiselect('disable');
		$('#selectUnidadNegocio').multiselect('disable');
		$('#selectUnidadEjecutora').multiselect('disable');

		$('#selectTipo').multiselect('disable');
		$('#selectPartidaEsp').multiselect('disable');

		$("#txtJustificacion").prop("disabled", true);
	}
}

function fnAlmacenarCaptura(estatus, msjvalidaciones="") {
	// console.log("fnAlmacenarCaptura");

	muestraCargandoGeneral();

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

	if ($('#selectTipo').val() == '-1') {
		// Si no selecciono Unidad Ejecutro
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar tipo de bien para continuar con el proceso</p>';
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

	if (datosReducciones.length == 0) {
		// Agregar claves presupuestales
		ocultaCargandoGeneral();
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Agregar bienes patrimoniales para continuar con el proceso</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}


	
	var observacion="";
	$("#tablaReducciones").find(".txtAgregarObservaciones").each(function(index){
		observacion = $(this).val();
		$.each(datosReducciones,function(index2, el) {
			if(index == index2){
				el.observaciones = observacion;
			}
		});
	});



	// console.log(datosReducciones);
	// return false;


	dataObj = { 
	        option: 'guardarOperacion',
	        datosCapturaReducciones: datosReducciones,
			type: type,
			transno: transno,
			legalid: legalid,
			tagref: tagref,
			estatus: statusGuardar,
			fechaCaptura: $('#txtFechaCaptura').val(),
			justificacion: $('#txtJustificacion').val(),
			ue: ue,
			selectTipo: $('#selectTipo').val(),
			selectPartidaEsp: fnObtenerOption('selectPartidaEsp',1),
			selectAlmacen: fnObtenerOption('selectAlmacen',1),
			selectCABMS: fnObtenerOption('selectClaveCABMS',1),
			fechaBaja: $('#txtFechaBaja').val()
	      };
	//Obtener datos de las bahias
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/bajaPratrimonialModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	ocultaCargandoGeneral();
	    	transno = data.contenido.datos.transno;
	    	type = data.contenido.datos.type;

			$('#txtNoCaptura').empty();
			$('#txtNoCaptura').append(data.contenido.datos.transno);

			$('#txtEstatus').empty();
			if (data.contenido.datos.statusname != "" || data.contenido.datos.statusname != null) {
				$('#txtEstatus').append(""+data.contenido.datos.statusname);
			}

			$('#selectUnidadNegocio').multiselect('disable');
	    	$('#selectUnidadEjecutora').multiselect('disable');
			$('#selectTipo').multiselect('disable');

			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    	muestraModalGeneral(3, titulo, data.Mensaje);
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
 * Función para confirmar antes de eliminar una clave presupuestal de la captura
 * @param  {[type]} clave           Clave presupuestal
 * @param  {[type]} panel           Panel de la informacion
 * @param  {Number} sinConfirmacion Variable para confirmación
 * @return {[type]}                 [description]
 */
function fnPresupuestoEliminar(clave, panel, sinConfirmacion=0, numLineaEliminar = 0) {
	// console.log("clave: "+clave);
	// console.log("numLineaEliminar: "+numLineaEliminar);
	
	if (sinConfirmacion == 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar el renglón '+numLineaEliminar+'</p>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\', \''+numLineaEliminar+'\')');
		return false;
	}

	var numLinea = 1;
	for (var key in datosReducciones ) {
		if (datosReducciones[key].noReg == clave && Number(numLinea) == Number(numLineaEliminar)) {
			datosReducciones.splice(key, 1);
			break;
		}

		numLinea ++;
	}

	fnRecargarDatosPaneles();
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
	var nombreSelect = "";
	var tipoAfectacion = "";
	var clavePresupuesto = "";
	
	if (idClavePresupuestoReducciones == 0) {
		enca = 0;
	}

	if (enca == 0) {
		encabezado += '<tr class="header-verde"><td></td><td></td>';
	}

	contenido += '<td '+style+'>'+numLineaReducciones+'</td>';

	if (autorizarGeneral == 1) {
		contenido += '<td></td>';
	} else {
		contenido += '<td '+style+'><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson.noReg+'\', \''+panel+'\', \'0\', \''+numLineaReducciones+'\')"></button></td>';
	}

	if (enca == 0) {
		// encabezado += '<td '+style+'>UR</td>';
		// encabezado += '<td '+style+'>UE</td>';
		// encabezado += '<td '+style+'>Descripción</td>';
		// encabezado += '<td '+style+'>No. de Inventario</td>';
		// encabezado += '<td '+style+'>Tipo de Bien</td>';
		// encabezado += '<td '+style+'>Estatus</td>';
		// encabezado += '<td '+style+'>Valor</td>';
		// encabezado += '</tr>';
		encabezado += '<td '+style+'>UR</td>';
		encabezado += '<td '+style+'>UE</td>';
		encabezado += '<td '+style+'>Almacén</td>';
		encabezado += '<td '+style+'>Partida Especifica</td>';
		encabezado += '<td '+style+'>No. de Inventario</td>';
		encabezado += '<td '+style+'>Descripcion</td>';
		encabezado += '<td '+style+'>Valor</td>';
		encabezado += '<td '+style+'>Motivo Baja</td>';
		encabezado += '</tr>';
	}

	// contenido += '<td '+style+'>'+dataJson.ur+'</td>';
	// contenido += '<td '+style+'>'+dataJson.ue+'</td>';
	// contenido += '<td '+style+'>'+dataJson.descripcion+'</td>';
	// contenido += '<td '+style+'>'+dataJson.noInventario+'</td>';
	// contenido += '<td '+style+'>'+dataJson.tipoName+'</td>';
	// contenido += '<td '+style+'>'+dataJson.estatus+'</td>';
	// contenido += '<td '+style+'>$ '+formatoComas( redondeaDecimal( dataJson.costo ) )+'</td>';
	contenido += '<td '+style+'>'+dataJson.ur+'</td>';
	contenido += '<td '+style+'>'+dataJson.ue+'</td>';
	contenido += '<td '+style+'>'+dataJson.almacen+'</td>';
	contenido += '<td '+style+'>'+dataJson.partida+'</td>';
	contenido += '<td '+style+'>'+dataJson.noInventario+'</td>';
	contenido += '<td '+style+'>'+dataJson.descripcion+'</td>';
	contenido += '<td '+style+'>$ '+formatoComas( redondeaDecimal( dataJson.costo ) )+'</td>';
	contenido += '<td '+style+'><textarea id="txtAgregarObservaciones'+numLineaReducciones+'" name="txtAgregarObservaciones'+numLineaReducciones+'" rows="1" class="w100p form-control txtAgregarObservaciones" placeholder="Observaciones" style="resize: vertical;">'+dataJson.observaciones+'</textarea></td>';

	contenido = encabezado + '<tr id="RenglonTR_'+dataJson.noReg+'_'+panel+'_'+numLineaReducciones+'" name="RenglonTR_'+dataJson.noReg+'_'+panel+'_'+numLineaReducciones+'" >' + contenido + '</tr>';

	if (enca == 0) {
		idClavePresupuestoReducciones = 1;
	}

	numLineaReducciones = parseFloat(numLineaReducciones) + 1;

	$('#'+idTabla+' tbody').append(contenido);

	fnEjecutarVueGeneral('RenglonTR_'+dataJson.noReg+'_'+panel+'_'+numLineaReducciones);
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

	fnSelectGeneralDatosAjax('#'+nomUnidadNegocio, dataObj, 'modelo/bajaPratrimonialModelo.php');
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

	for (var key in datosReducciones ) {
		fnMostrarPresupuesto(datosReducciones[key], tablaReducciones, panelReducciones);
	}

	if (datosReducciones.length == 0) {
		$('#selectUnidadNegocio').multiselect('enable');
	    $('#selectUnidadEjecutora').multiselect('enable');
		$('#selectTipo').multiselect('enable');
	}
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
	      url: "modelo/bajaPratrimonialModelo.php",
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