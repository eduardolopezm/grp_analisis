/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var dataJsonGestionDetalle = new Array();
var idTabla = 'tablaDetalle';
var tipoPolizaGeneral = 0;
var nombreRegistroTabla = 'trRegistro_';
var nombreElementoDetalle = 'txtDato';
var nombreDivEncabezado = 'trEncabezado';

$( document ).ready(function() {
	if (document.querySelector(".selectTipoPolizaSeguros")) {
		dataObj = { 
	        option: 'mostrarTipoPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectTipoPolizaSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 1);
	}
	if (document.querySelector(".selectAseguradoraSeguros")) {
		dataObj = { 
	        option: 'mostrarAseguradoraPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectAseguradoraSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 1);
	}
	if (document.querySelector(".selectCoberturaSeguros")) {
		dataObj = { 
	        option: 'mostrarCoberturaPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectCoberturaSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 1);
	}

	$("#btnAgregarDetalle").click(function() {
        fnAgregarDatosDetalle();
    });

    $("#btnGuardarInfo").click(function() {
    	// Botón de Guardar
    	fnGuardarInformacion();
    });

    fnCargarInformacion(typeDoc, transnoDoc);
});

function fnCargarInformacion(typeDoc, transnoDoc){
	// Mostrar informacion de poliza
	console.log("typeDoc: "+typeDoc+" - transnoDoc: "+transnoDoc);

	muestraCargandoGeneral();

	dataObj = { 
	        option: 'cargarInformacion',
	        typeDoc: typeDoc,
	        transnoDoc: transnoDoc
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/gestionPolizasModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	// console.log("respuesta: "+JSON.stringify(data.contenido));
	    	if (transnoDoc != 0) {
	    		dataJsonGestionDetalle = data.contenido.dataJsonGestionDetalle;
				$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.transnoDoc);
				$('#txtFolio').val("");
				$('#txtFolio').val(""+data.contenido.txtFolio);
				$('#txtDeducible').val("");
				$('#txtDeducible').val(""+data.contenido.txtDeducible);
				$('#txtCoAseguro').val("");
				$('#txtCoAseguro').val(""+data.contenido.txtCoAseguro);
				$('#txtFechaDesde').val("");
				$('#txtFechaDesde').val(""+data.contenido.txtFechaDesde);
				$('#txtFechaHasta').val("");
				$('#txtFechaHasta').val(""+data.contenido.txtFechaHasta);
				if (data.contenido.txtTagref != "") {
					$('#selectUnidadNegocio').selectpicker('val', ''+data.contenido.txtTagref);
					$("#selectUnidadNegocio").multiselect("refresh");
					$(".selectUnidadNegocio").css("display", "none");
					$("#selectUnidadNegocio").multiselect("disable");
				}
				if (data.contenido.txtUe != "") {
					$('#selectUnidadEjecutora').selectpicker('val', ''+data.contenido.txtUe);
					$("#selectUnidadEjecutora").multiselect("refresh");
					$(".selectUnidadEjecutora").css("display", "none");
					$("#selectUnidadEjecutora").multiselect("disable");

				}
				if (data.contenido.txtTipoPoliza != "") {
					$('#selectPoliza').selectpicker('val', ''+data.contenido.txtTipoPoliza);
					$("#selectPoliza").multiselect("refresh");
					$(".selectTipoPolizaSeguros").css("display", "none");
					$("#selectPoliza").multiselect("disable");

				}
				if (data.contenido.selectAseguradora != "") {
					$('#selectAseguradora').selectpicker('val', ''+data.contenido.selectAseguradora);
					$("#selectAseguradora").multiselect("refresh");
					$(".selectAseguradoraSeguros").css("display", "none");
				}
				if (data.contenido.selectCobertura != "") {
					$('#selectCobertura').selectpicker('val', ''+data.contenido.selectCobertura);
					$("#selectCobertura").multiselect("refresh");
					$(".selectCoberturaSeguros").css("display", "none");
				}
				if (data.contenido.selectEstatusPoliza != "") {
					$('#selectEstatusPoliza').selectpicker('val', ''+data.contenido.selectEstatusPoliza);
					$("#selectEstatusPoliza").multiselect("refresh");
					$(".selectEstatusPoliza").css("display", "none");
				}

				fnMostrarDetalleDatos(dataJsonGestionDetalle, 1);
	    	}
	    }else{
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    	muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo Cargar la Información del Folio '+transnoDoc+'</p>');
	    }
	    ocultaCargandoGeneral();
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnGuardarInformacion() {
	// Funcion para guardar información
	var txtTagref = $("#selectUnidadNegocio").val();
	var txtUe = $("#selectUnidadEjecutora").val();
	var txtTipoPoliza = $("#selectPoliza").val();
	var txtFolio = $("#txtFolio").val();
	var selectAseguradora = $("#selectAseguradora").val();
	var selectCobertura = $("#selectCobertura").val();
	var txtDeducible = $("#txtDeducible").val();
	var txtCoAseguro = $("#txtCoAseguro").val();
	var txtFechaDesde = $("#txtFechaDesde").val();
	var txtFechaHasta = $("#txtFechaHasta").val();
	var selectEstatusPoliza = $("#selectEstatusPoliza").val();

	console.log("dataJsonGestionDetalle: "+JSON.stringify(dataJsonGestionDetalle));

	if (txtFolio.trim() == '' || selectAseguradora.trim() == '0' || selectCobertura.trim() == '0'
		|| txtDeducible.trim() == '' || txtCoAseguro.trim() == '' || txtFechaHasta.trim() == ''
		|| txtFechaHasta.trim() == '' || dataJsonGestionDetalle.length == 0 || txtTagref.trim() == '-1' || txtTipoPoliza.trim() == '0' || selectCobertura.trim() == '0') {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<h4> Completar la siguiente información</h4>';
		if (txtTagref.trim() == '-1') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Unidad Responsable</p>';
		}
		if (txtTipoPoliza.trim() == '0') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Tipo de Póliza</p>';
		}
		if (txtFolio.trim() == '') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Folio Póliza</p>';
		}
		if (selectEstatusPoliza.trim() == '0') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Estatus Póliza</p>';
		}
		if (selectAseguradora.trim() == '0') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Aseguradora</p>';
		}
		if (selectCobertura.trim() == '0') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Cobertura</p>';
		}
		if (txtDeducible.trim() == '') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Deducible</p>';
		}
		if (txtCoAseguro.trim() == '') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Co-aseguro</p>';
		}
		if (txtFechaDesde.trim() == '') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Fecha Desde</p>';
		}
		if (txtFechaHasta.trim() == '') {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Fecha Hasta</p>';
		}
		if (dataJsonGestionDetalle.length == 0) {
			mensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No existe registros</p>';
		}

		muestraModalGeneral(3, titulo, mensaje);
	} else {
		// Almacenar información
		muestraCargandoGeneral();

		dataObj = { 
		        option: 'guardarInformacion',
		        typeDoc: typeDoc,
		        transnoDoc: transnoDoc,
				txtTagref: txtTagref,
				txtUe: txtUe,
				txtTipoPoliza: txtTipoPoliza,
		        txtFolio: txtFolio,
				selectAseguradora: selectAseguradora,
				selectCobertura: selectCobertura,
				txtDeducible: txtDeducible,
				txtCoAseguro: txtCoAseguro,
				txtFechaDesde: txtFechaDesde,
				txtFechaHasta: txtFechaHasta,
				dataJsonGestionDetalle: dataJsonGestionDetalle,
				selectEstatusPoliza: selectEstatusPoliza
		      };
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/gestionPolizasModelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
			//console.log("Bien");
		    if(data.result){
		    	transnoDoc = data.contenido.datos.transno;

		    	$('#txtNoCaptura').empty();
				$('#txtNoCaptura').append(data.contenido.datos.transno);

		    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, data.Mensaje);
		    }else{
		    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		    	muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo Guardar la Información</p>');
		    }
		    ocultaCargandoGeneral();
		})
		.fail(function(result) {
			ocultaCargandoGeneral();
			console.log("ERROR");
		    console.log( result );
		});
	}
}

function fnNumeroElementosFormulario() {

	// Verificar numero de Elementos
	var tipoPoliza = $("#selectPoliza").val();
	var numElementos = 0;
	
	if (tipoPoliza == '1') {
		// Formulario Vehículo
		//1 - Vehiculo, 2 - Marca, 3 - SubMarca, 4 - Año, 5 - precio, 6 - Ubicacion 
		numElementos = 6;

		//Realizaron cambios
		//1 - Numero Inventario, 2 - Marca, 3 - Modelo, 4 - Año, 5 - Color, 6 - Placas, 7 - Precio Factura, 8 - Ubicacion 

		numElementos = 8;
	} else if (tipoPoliza == '2') {
		// Formulario Muebles
		//1 - Numero Inventario, 2 - Marcar, 3 - Modeloe, 4 - no Serie, 5 - Factura, 6 - Precio 
		numElementos = 6;

	} else if (tipoPoliza == '3') {
		// Formulario Inmuebles
		//1 - Tipo, 2 - Niveles, 3 - uso, 4 - Año Construcion, 5 - Valor Avaluo, 6 - Ubicacion 
		numElementos = 6;

		//1 - Numero Inventario, 2 - Niveles, 3 - uso, 4 - Año Construcion, 5 - Valor Avaluo, 6 - Ubicacion 
		numElementos = 6;
	} else if (tipoPoliza == '4') {
		// Formulario Vida
		//1 - Paterno, 2 - Materno, 3 - Nombre, 4 - Tipo, 5 - Precio
		numElementos = 5;

		//1 - Calve Empleado, 2 - Paterno, 3 - Materno, 4 - Nombre, 5 - CURP, 6 - RFC, 7 - Precio 
		numElementos = 7;
	}

	return numElementos;
}

function fnEncabezadoTabla() {
	// Elementos para el encabezado de la tabla
	var tipoPoliza = $("#selectPoliza").val();
	var encabezadoTabla = '<tr id="'+nombreDivEncabezado+'" class="header-verde">';
	var style = 'style="text-align:center;"';

	encabezadoTabla += '<td '+style+'></td>';
	encabezadoTabla += '<td '+style+'>No</td>';
	if (tipoPoliza == '1') {
		// Formulario Vehículo
		encabezadoTabla += '<td '+style+'>Número Inventario</td>';
		encabezadoTabla += '<td '+style+'>Marca</td>';
		encabezadoTabla += '<td '+style+'>Modelo</td>';
		encabezadoTabla += '<td '+style+'>Año</td>';
		encabezadoTabla += '<td '+style+'>Color</td>';
		encabezadoTabla += '<td '+style+'>Placas</td>';
		encabezadoTabla += '<td '+style+'>Precio Factura</td>';
		//encabezadoTabla += '<td '+style+'>Factura</td>';
	} else if (tipoPoliza == '2') {
		// Formulario Muebles
		encabezadoTabla += '<td '+style+'>Número Inventario</td>';
		encabezadoTabla += '<td '+style+'>Marca</td>';
		encabezadoTabla += '<td '+style+'>Modelo</td>';
		encabezadoTabla += '<td '+style+'>Número Serie</td>';
		encabezadoTabla += '<td '+style+'>Factura</td>';
		encabezadoTabla += '<td '+style+'>Precio</td>';
	} else if (tipoPoliza == '3') {
		// Formulario Inmuebles
		encabezadoTabla += '<td '+style+'>Número Inventario</td>';
		encabezadoTabla += '<td '+style+'>Niveles</td>';
		encabezadoTabla += '<td '+style+'>Uso</td>';
		encabezadoTabla += '<td '+style+'>Año Contrucción</td>';
		encabezadoTabla += '<td '+style+'>Valor Avalúo</td>';
		encabezadoTabla += '<td '+style+'>Ubicación</td>';
	} else if (tipoPoliza == '4') {
		// Formulario Vida
		encabezadoTabla += '<td '+style+'>Clave Empleado</td>';
		encabezadoTabla += '<td '+style+'>Paterno</td>';
		encabezadoTabla += '<td '+style+'>Materno</td>';
		encabezadoTabla += '<td '+style+'>Nombre</td>';
		encabezadoTabla += '<td '+style+'>CURP</td>';
		encabezadoTabla += '<td '+style+'>RFC</td>';
		encabezadoTabla += '<td '+style+'>Precio</td>';
	}

	encabezadoTabla += '</tr>';

	return encabezadoTabla;
}

function fnLimpiarAgregarInformacion(divFormulario, contenido) {
	// Limpiar y agregar contenido a un div
	$('#'+divFormulario).empty();
    $('#'+divFormulario).append(contenido);

    fnSelectFormulario();

    fnEjecutarVueGeneral(''+divFormulario);
}

function fnAgregarDatosDetalle() {
	// Agrega datos en vacio para renglon nuevo
	// console.log("fnAgregarDatosDetalle");

	var numElementos = fnNumeroElementosFormulario();

	if (numElementos != 0) {
		var obj = new Object();
		var datos = new Array();
		for (var x= 1; x <= numElementos; x++) {
			obj[nombreElementoDetalle+x] = '';
		}
		// datos.push(obj);

		dataJsonGestionDetalle.push(obj);
	} else {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar Tipo de Póliza</p>');
	}
	
	// console.log("dataJsonGestionDetalle: "+JSON.stringify(dataJsonGestionDetalle));
	if (dataJsonGestionDetalle.length > 0) {
		// Si tiene registros mostra info
		fnMostrarDetalleDatos(dataJsonGestionDetalle);
	}
}

function fnSelectFormulario() {
	// Agregar Información Select
	if (document.querySelector(".selectTipoAseguramientoSeguros")) {
		dataObj = { 
	        option: 'mostrarAseguramientoPolizaSeguros'
	    };
		fnSelectGeneralDatosAjax('.selectTipoAseguramientoSeguros', dataObj, 'modelo/gestionPolizasPanelModelo.php', 1);
	}
}

function fnEliminarRenglonDetalleConfirmar(numRenglon) {
	// Mensaje de confirmacion para eliminar un renglon
	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = '<p>Se va a eliminar el Registro No. '+numRenglon+'</p>';
	muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnEliminarRenglonDetalle(\''+numRenglon+'\')');
}

function fnEliminarRenglonDetalle(numRenglon) {
	// Elimina el renglon del array, vuleve a mostrar el listado
	numRenglon --;
	dataJsonGestionDetalle.splice(numRenglon, 1);
	fnMostrarDetalleDatos(dataJsonGestionDetalle, 1);
}

function fnCambioDatoFormulario(elemento, numRenglon, nombreDato, numTipoPoliza=0, numRenglonTipoPoliza = 0) {
	
	var contador=0;
	var numRecorrido = 1;
	var strOption="obtenerInfoPatrimonio";

	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	//!!        Obtener info de patrimonio.            !!
	//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	if(numTipoPoliza == 4){
		strOption="obtenerInfoEmpleado";
	}

	dataObj = { 
	        option: strOption,
	        assetid: elemento.value
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/gestionPolizasPanelModelo.php",
	      data:dataObj,
	      async:false,
          cache:false
	  })
	.done(function( data ) {
	    if(data.result){

	    	var dataJson = data.contenido.datos;
	    	
	    	if(!data.contenido.datos){
	    		for (var i = 2; i <= numRenglonTipoPoliza; i++) {
	    			$('#'+numRenglon+'_txtDato'+i).val('');
	    		}
	    		return;
	    	}

	    	switch(numTipoPoliza) {
			    case 1:
				        $.each(dataJson,function(index, el) {
				        	$('#'+numRenglon+'_txtDato2').val(el.marca);
				        	$('#'+numRenglon+'_txtDato3').val(el.modelo);
				        	$('#'+numRenglon+'_txtDato4').val(el.anio);
				        	$('#'+numRenglon+'_txtDato5').val(el.color);
				        	$('#'+numRenglon+'_txtDato6').val(el.placas);
				        	$('#'+numRenglon+'_txtDato7').val(el.precio);

							for (var key in dataJsonGestionDetalle) {
								if (numRecorrido == numRenglon) {
									dataJsonGestionDetalle[key]['txtDato1'] = elemento.value;
									dataJsonGestionDetalle[key]['txtDato2'] = el.marca;
									dataJsonGestionDetalle[key]['txtDato3'] = el.modelo;
									dataJsonGestionDetalle[key]['txtDato4'] = el.anio;
									dataJsonGestionDetalle[key]['txtDato5'] = el.color;
									dataJsonGestionDetalle[key]['txtDato6'] = el.placas;
									dataJsonGestionDetalle[key]['txtDato7'] = el.precio;
								}
								numRecorrido ++;
							}
				        });
			        break;
			    case 2:
			    	$.each(dataJson,function(index, el) {
			        	$('#'+numRenglon+'_txtDato2').val(el.marca);
			        	$('#'+numRenglon+'_txtDato3').val(el.modelo);
			        	$('#'+numRenglon+'_txtDato4').val(el.serie);
			        	$('#'+numRenglon+'_txtDato5').val(el.factura);
			        	$('#'+numRenglon+'_txtDato6').val(el.precio);

						for (var key in dataJsonGestionDetalle) {
							if (numRecorrido == numRenglon) {
								dataJsonGestionDetalle[key]['txtDato1'] = elemento.value;
								dataJsonGestionDetalle[key]['txtDato2'] = el.marca;
								dataJsonGestionDetalle[key]['txtDato3'] = el.modelo;
								dataJsonGestionDetalle[key]['txtDato4'] = el.serie;
								dataJsonGestionDetalle[key]['txtDato5'] = el.factura;
								dataJsonGestionDetalle[key]['txtDato6'] = el.precio;
							}
							numRecorrido ++;
						}
			        });
			        
			        break;

			    case 3:
			    	$.each(dataJson,function(index, el) {
			        	$('#'+numRenglon+'_txtDato6').val(el.ubicacion);

						for (var key in dataJsonGestionDetalle) {
							if (numRecorrido == numRenglon) {
								dataJsonGestionDetalle[key]['txtDato1'] = elemento.value;
								dataJsonGestionDetalle[key]['txtDato6'] = el.ubicacion;
							}
							numRecorrido ++;
						}
			        });
			        
			        break;
			    case 4:

			    	$.each(dataJson,function(index, el) {
			        	$('#'+numRenglon+'_txtDato2').val(el.paterno);
			        	$('#'+numRenglon+'_txtDato3').val(el.materno);
			        	$('#'+numRenglon+'_txtDato4').val(el.nombre);
			        	$('#'+numRenglon+'_txtDato5').val(el.curp);
			        	$('#'+numRenglon+'_txtDato6').val(el.rfc);

						for (var key in dataJsonGestionDetalle) {
							if (numRecorrido == numRenglon) {
								dataJsonGestionDetalle[key]['txtDato1'] = elemento.value;
								dataJsonGestionDetalle[key]['txtDato2'] = el.paterno;
								dataJsonGestionDetalle[key]['txtDato3'] = el.materno;
								dataJsonGestionDetalle[key]['txtDato4'] = el.nombre;
								dataJsonGestionDetalle[key]['txtDato5'] = el.curp;
								dataJsonGestionDetalle[key]['txtDato6'] = el.rfc;
							}
							numRecorrido ++;
						}
			        });

			    	break;
			    default:
			        
			}

	    }
	    ocultaCargandoGeneral();
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});


	// Actualizar informacion en el Array, cada cambios que exista en un elemento

	console.log(dataJsonGestionDetalle);
}

function fnCargarCampos(elemento, numRenglon, nombreDato){
	// Actualizar informacion en el Array, cada cambios que exista en un elemento
	var numRecorrido = 1;
	for (var key in dataJsonGestionDetalle) {
		if (numRecorrido == numRenglon) {
			dataJsonGestionDetalle[key][nombreDato] = elemento.value;
		}
		numRecorrido ++;
	}
	console.log(dataJsonGestionDetalle);
}

function fnMostrarDetalleDatos(dataJsonGestionDetalle, todaTabla=0) {
	// Muestra informacion en la tabla
	var tipoPoliza = $("#selectPoliza").val();
	var numElementos = fnNumeroElementosFormulario();
	var contenido = '';
	var numRecorrido = 1;
	var style = ' style="text-align:center;" ';
	var styleDatos = ' style="width: 200px;" ';
	var styleAño = ' style="width: 70px;" ';
	var stylePrecio = ' style="width: 130px;" ';
	var tipoPolizaAltaPatrimonio = 0;

	if (dataJsonGestionDetalle.length == 1 || todaTabla == 1) {
		$('#'+idTabla+' tbody').empty();
		contenido += fnEncabezadoTabla();
	}
	console.log(dataJsonGestionDetalle);
	// console.log("***********");
	for (var key in dataJsonGestionDetalle) {
		// console.log("renglon: "+nombreRegistroTabla+numRecorrido);

		if (dataJsonGestionDetalle.length == 1 || numRecorrido == dataJsonGestionDetalle.length || todaTabla == 1) {

			contenido += '<tr id="'+nombreRegistroTabla+numRecorrido+'">';
			contenido += '<td '+style+'><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnEliminarRenglonDetalleConfirmar(\''+numRecorrido+'\')"></button></td>';
			contenido += '<td '+style+'>'+numRecorrido+'</td>';

			if (tipoPoliza == '1') {
				// Formulario Vehículo
				// numElementos = 6;
				
				//1 - Vehiculo, 2 - Marca, 3 - SubMarca, 4 - Año, 5 - precio, 6 - Ubicacion 
				//Realizaron cambios
				//1 - Numero Inventario, 2 - Marca, 3 - Modelo, 4 - Año, 5 - Color, 6 - Placas, 7 - Precio Factura 
				tipoPolizaAltaPatrimonio = 3;
				var nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'1';
				var nombreComboClave = numRecorrido+'_'+nombreElementoDetalle+'1';
				var nombreDato = nombreElementoDetalle+'1';
				contenido += '<td><select onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\','+tipoPoliza+',7)" id="'+nombreComboClave+'" name="'+nombreComboClave+'" class="form-control selectTipoVehiculo" title="Número de Inventario "></select></td>';

				//contenido += '<td><component-text onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Vehículo" title="Vehículo" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'2';
				nombreDato = nombreElementoDetalle+'2';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Marca" title="Marca" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text ></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'3';
				nombreDato = nombreElementoDetalle+'3';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Modelo" title="Modelo" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text ></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'4';
				nombreDato = nombreElementoDetalle+'4';
				//contenido += '<td><component-number id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Año" title="Año" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' maxlength="4"></component-number></td>';
				contenido += '<td><input type="text" class="form-control" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Año" title="Año" value="'+dataJsonGestionDetalle[key][nombreDato]+'" onkeypress="return fnSoloNumeros(event)" '+styleAño+' maxlength="4"  readonly/></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'5';
				nombreDato = nombreElementoDetalle+'5';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Color" title="Color" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+stylePrecio+' readonly></component-text ></td>';

				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'6';
				nombreDato = nombreElementoDetalle+'6';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Placas" title="Placas" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+stylePrecio+' readonly></component-text ></td>';


				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'7';
				nombreDato = nombreElementoDetalle+'7';
				contenido += '<td><component-decimales id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Precio Factura" title="Precio" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+stylePrecio+' readonly></component-decimales ></td>';
				
				// nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'8';
				// nombreDato = nombreElementoDetalle+'8';
				// contenido += '<td><component-text onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Factura" title="Factura" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
			} else if (tipoPoliza == '2') {
				// Formulario Muebles
				// numElementos = 6;
				tipoPolizaAltaPatrimonio = 1;
				var nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'1';
				var nombreDato = nombreElementoDetalle+'1';
				contenido += '<td><select onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\','+tipoPoliza+',6)" id="'+nombreCaja+'" name="'+nombreCaja+'" class="form-control selectTipoVehiculo" title="Número de Inventario "></select></td>';
				//contenido += '<td><component-text onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Tipo" title="Tipo" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'2';
				nombreDato = nombreElementoDetalle+'2';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Marca" title="Marca" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text ></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'3';
				nombreDato = nombreElementoDetalle+'3';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Modelo" title="Modelo" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text ></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'4';
				nombreDato = nombreElementoDetalle+'4';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Número Serie" title="Número Serie" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text ></td>';
			
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'5';
				nombreDato = nombreElementoDetalle+'5';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Factura" title="Factura" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+stylePrecio+' readonly></component-text ></td>';

				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'6';
				nombreDato = nombreElementoDetalle+'6';
				contenido += '<td><component-decimales id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Precio" title="Precio" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+stylePrecio+' readonly></component-decimales ></td>';
				
			} else if (tipoPoliza == '3') {
				// Formulario Inmuebles
				// numElementos = 6;
				tipoPolizaAltaPatrimonio = 2;
				var nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'1';
				var nombreDato = nombreElementoDetalle+'1';
				//contenido += '<td><component-text onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\','+tipoPoliza+',6)" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Tipo" title="Tipo" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
				contenido += '<td><select onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\','+tipoPoliza+',6)" id="'+nombreCaja+'" name="'+nombreCaja+'" class="form-control selectTipoVehiculo" title="Número de Inventario "></select></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'2';
				nombreDato = nombreElementoDetalle+'2';
				contenido += '<td><component-text onchange="fnCargarCampos(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Niveles" title="Niveles" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'3';
				nombreDato = nombreElementoDetalle+'3';
				contenido += '<td><component-text onchange="fnCargarCampos(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Uso" title="Uso" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'4';
				nombreDato = nombreElementoDetalle+'4';
				//contenido += '<td><component-number onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Año" title="Año" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' maxlength="4"></component-number></td>';
				contenido += '<td><input type="text" class="form-control" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Año" title="Año" value="'+dataJsonGestionDetalle[key][nombreDato]+'" onkeypress="return fnSoloNumeros(event)" onBlur="fnCargarCampos(this, '+numRecorrido+', \''+nombreDato+'\')" '+styleAño+' maxlength="4" /></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'5';
				nombreDato = nombreElementoDetalle+'5';
				contenido += '<td><component-decimales onchange="fnCargarCampos(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Valor Avalúo" title="Valor Avalúo" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-decimales></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'6';
				nombreDato = nombreElementoDetalle+'6';
				contenido += '<td><component-text id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Ubicación" title="Ubicación" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text></td>';
			} else if (tipoPoliza == '4') {
				// Formulario Vida
				// numElementos = 5;
				tipoPolizaAltaPatrimonio = 4;
				 styleDatos = ' style="width: 150px;" ';
				var nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'1';
				var nombreDato = nombreElementoDetalle+'1';
				//contenido += '<td><component-text onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Paterno" title="Paterno" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+'></component-text></td>';
				contenido += '<td><select onchange="fnCambioDatoFormulario(this, '+numRecorrido+', \''+nombreDato+'\','+tipoPoliza+',7)" id="'+nombreCaja+'" name="'+nombreCaja+'" class="form-control selectTipoEmpleado" title="Clave Empleado "></select></td>';

				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'2';
				nombreDato = nombreElementoDetalle+'2';
				contenido += '<td><component-text  id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Paterno" title="Paterno" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'3';
				nombreDato = nombreElementoDetalle+'3';
				contenido += '<td><component-text  id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Materno" title="Materno" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'4';
				nombreDato = nombreElementoDetalle+'4';
				contenido += '<td><component-text  id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Nombre" title="Nombre" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'5';
				nombreDato = nombreElementoDetalle+'5';
				contenido += '<td><component-text  id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="CURP" title="CURP" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text></td>';
				
				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'6';
				nombreDato = nombreElementoDetalle+'6';
				contenido += '<td><component-text  id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="RFC" title="RFC" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+styleDatos+' readonly></component-text></td>';

				nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'7';
				nombreDato = nombreElementoDetalle+'7';
				 styleDatos = ' style="width: 120px;" ';

				contenido += '<td><component-decimales onchange="fnCargarCampos(this, '+numRecorrido+', \''+nombreDato+'\')" id="'+nombreCaja+'" name="'+nombreCaja+'" placeholder="Precio" title="Precio" value="'+dataJsonGestionDetalle[key][nombreDato]+'" '+stylePrecio+'></component-decimales></td>';
			}
			
			contenido += '</tr>';
		}

		numRecorrido ++;
	}

	// $('#'+idTabla+' tbody').empty();
	$('#'+idTabla+' tbody').append(contenido);
	if (dataJsonGestionDetalle.length == 1 || todaTabla == 1) {
		fnEjecutarVueGeneral(''+idTabla);
	} else {
		fnEjecutarVueGeneral(''+nombreRegistroTabla+(numRecorrido - 1)); // Se resta 1 ya que al final del ciclo lo suma
	}

	fnSelectFormulario();

	var contadorRenlgon = 1;
	for (var key in dataJsonGestionDetalle) {
		if (dataJsonGestionDetalle.length == 1 || contadorRenlgon == dataJsonGestionDetalle.length || todaTabla == 1) {

			//if(tipoPoliza == '1'){
				fnSelectFormularioPatrimonio(contadorRenlgon+'_'+nombreElementoDetalle+'1',tipoPolizaAltaPatrimonio);
				if(dataJsonGestionDetalle[key]['txtDato1'] != ""){
					$('#'+contadorRenlgon+'_'+nombreElementoDetalle+'1').val(dataJsonGestionDetalle[key]['txtDato1']);
					$('#'+contadorRenlgon+'_'+nombreElementoDetalle+'1').multiselect('rebuild');
				}
			//}
		}
		contadorRenlgon ++;
	}
	

	// if (dataJsonGestionDetalle.length > 0 && todaTabla == 1 && tipoPoliza == '4') {
	// 	// Valor select poliza de vida
	// 	var numRecorrido = 1;
	// 	for (var key in dataJsonGestionDetalle) {
	// 		var nombreCaja = numRecorrido+'_'+nombreElementoDetalle+'4';
	// 		var nombreDato = nombreElementoDetalle+'4';
	// 		$('#'+nombreCaja).selectpicker('val', ''+dataJsonGestionDetalle[key][nombreDato]);
	// 		$("#"+nombreCaja).multiselect("refresh");
	// 		$(".selectTipoAseguramientoSeguros").css("display", "none");

	// 		numRecorrido ++;
	// 	}
	// }
}

function fnSelectFormularioPatrimonio(componente = "",tipoPatrimonio) {
	// Agregar Información Select
	if(componente ==""){
		return false;
	}

	var strOption="mostrarPatrimonioPorTipo";

	if(tipoPatrimonio == 4){
		strOption = "mostrarEmpleados";
	}
	
	if (document.getElementById(""+componente)) {
	
		dataObj = { 
	        option: strOption,
	        tipoPatrimonio : tipoPatrimonio,
	        ur : $('#selectUnidadNegocio').val(),
	        ue : $('#selectUnidadEjecutora').val()
	    };
		fnSelectGeneralDatosAjax('#'+componente, dataObj, 'modelo/gestionPolizasPanelModelo.php', 1);
	}
}

function fnCambiaTipoPolizaProceso(valor) {
	// Cuando Cambie limpiar datos
	dataJsonGestionDetalle = new Array();
	$('#'+idTabla+' tbody').empty();

	tipoPolizaGeneral = $("#selectPoliza").val();
}

function fnNoCambiarDatosSelect() {
	// Poner dato en el select de la poliza anterior, si no desea eliminar informacion capturada
	// console.log("fnNoCambiarDatosSelect");
	$("#selectPoliza").selectpicker('val',[tipoPolizaGeneral]);
	$("#selectPoliza").multiselect('refresh');
	$(".selectTipoPolizaSeguros").css("display", "none");
	//$('#selectPoliza').multiselect('disable');
}

function fnCambiaTipoPoliza(select){
	// Cambia el Tipo de Póliza
	// console.log("dato: "+select.value);

	if (dataJsonGestionDetalle.length > 0) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Se va a eliminar el detalle de la Póliza. Tiene '+dataJsonGestionDetalle.length+' registros</p>';
		var pie = '\
            <div class="input-group pull-right">\
                <button class="btn btn-default botonVerde" onclick="fnCambiaTipoPolizaProceso('+select.value+')" data-dismiss="modal">Si</button>\
                <button class="btn btn-default botonVerde" data-dismiss="modal" onclick="fnNoCambiarDatosSelect()" id="btnCerrarModalGeneral" name="btnCerrarModalGeneral">No</button>\
            </div>';
		muestraModalGeneralConfirmacion(3, titulo, mensaje, pie, 'fnCambiaTipoPolizaProceso(\''+select.value+'\')');
		return true;
	} else {
		fnCambiaTipoPolizaProceso(select.value);
	}
}