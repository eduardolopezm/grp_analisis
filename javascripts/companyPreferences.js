/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var proceso = "";

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');

	$("#btnAgregar").click(function() {
		proceso = 'Agregar';
		fnMostrarInfoCap();
	});
});

function fnPonerEstilosMod() {
	var numero = document.documentElement.clientHeight;
	numero = Number(numero) - Number(300);
	$("#ModalGeneral_Mensaje").css("overflow-x", "scroll");
	$("#ModalGeneral_Mensaje").css("overflow-y", "scroll");
	$("#ModalGeneral_Mensaje").css("height", numero+"px");
}

function fnQuitarEstilosMod() {
	$("#ModalGeneral_Mensaje").css("overflow-x", "");
	$("#ModalGeneral_Mensaje").css("overflow-y", "");
	$("#ModalGeneral_Mensaje").css("height", "");
}

function fnMostrarDatos(){
	muestraCargandoGeneral();

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo'
	      };
	//Obtener datos de las bahias
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/companyPreferencesModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    ocultaCargandoGeneral();

	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;

	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];
			var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];
			fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnGuardarInformacion(){
	if ($('#selectUnidadNegocio').val() == '-1') {
		// Si no selecciono Unidad Responsable
		$('#divPreferenciasEmpMensajes').empty();
		$('#divPreferenciasEmpMensajes').append('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Seleccionar UR para continuar con el proceso</p></div>');
		// $('#divPreferenciasEmpMensajes').append('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UR para continuar con el proceso</p>');
		return true;
	}

	if ($('#selectUnidadEjecutora').val() == '-1') {
		// Si no selecciono Unidad Ejecutro
		$('#divPreferenciasEmpMensajes').empty();
		$('#divPreferenciasEmpMensajes').append('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Seleccionar UE para continuar con el proceso</p></div>');
		// $('#divPreferenciasEmpMensajes').append('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar UE para continuar con el proceso</p>');
		return true;
	}

	if ($('#txtIngresoEstimado').val().trim() == ''
		|| $('#txtIngresoPorEjecutar').val().trim() == ''
		|| $('#txtIngresoModificado').val().trim() == ''
		|| $('#txtIngresoDevengado').val().trim() == ''
		|| $('#txtIngresoRecaudado').val().trim() == ''
		|| $('#txtEgresoAprobado').val().trim() == ''
		|| $('#txtEgresoPorEjercer').val().trim() == ''
		|| $('#txtEgresoModificado').val().trim() == ''
		|| $('#txtEgresoComprometido').val().trim() == ''
		|| $('#txtEgresoDevengado').val().trim() == ''
		|| $('#txtEgresoEjercido').val().trim() == ''
		|| $('#txtEgresoPagado').val().trim() == '') {
		// Si no selecciono Unidad Ejecutro
		$('#divPreferenciasEmpMensajes').empty();
		$('#divPreferenciasEmpMensajes').append('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>Existen Cuentas Ingreso y/o Egreso vacías</p></div>');
		// $('#divPreferenciasEmpMensajes').append('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Existen Cuentas Ingreso y/o Egreso vacías</p>');
		return true;
	}

	$('#divPreferenciasEmpMensajes').empty();

	muestraCargandoGeneral();

	dataObj = { 
	        option: 'AgregarCatalogo',
	        proceso: proceso,
	        ur: $('#selectUnidadNegocio').val(),
	        ue: $('#selectUnidadEjecutora').val(),

	        txtIngresoEstimado: $('#txtIngresoEstimado').val().trim(),
	        txtIngresoPorEjecutar: $('#txtIngresoPorEjecutar').val().trim(),
	        txtIngresoModificado: $('#txtIngresoModificado').val().trim(),
	        txtIngresoDevengado: $('#txtIngresoDevengado').val().trim(),
	        txtIngresoRecaudado: $('#txtIngresoRecaudado').val().trim(),

	        txtEgresoAprobado: $('#txtEgresoAprobado').val().trim(),
	        txtEgresoPorEjercer: $('#txtEgresoPorEjercer').val().trim(),
	        txtEgresoModificado: $('#txtEgresoModificado').val().trim(),
	        txtEgresoComprometido: $('#txtEgresoComprometido').val().trim(),
	        txtEgresoDevengado: $('#txtEgresoDevengado').val().trim(),
	        txtEgresoEjercido: $('#txtEgresoEjercido').val().trim(),
	        txtEgresoPagado: $('#txtEgresoPagado').val().trim()
	      };
	$.ajax({
		  async:false,
	  	  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/companyPreferencesModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		ocultaCargandoGeneral();
	    if(data.result){
	    	fnQuitarEstilosMod();

	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			muestraModalGeneral(3, titulo, data.Mensaje);

	    	fnMostrarDatos('');
	    }else{
	    	$('#divPreferenciasEmpMensajes').empty();
	    	$('#divPreferenciasEmpMensajes').append(''+data.Mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );
	});
}

function fnModificar(ur, ue){
	muestraCargandoGeneral();

	proceso = "Modificar";
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ur: ur,
	        ue: ue
	      };
	$.ajax({
		  async:false,
		  cache:false,
	      method: "POST",
	      dataType:"json",
	      url: "modelo/companyPreferencesModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		ocultaCargandoGeneral();

	    if(data.result){
	    	info=data.contenido.datosCatalogo;
	    	// console.log("datosCatalogo: "+JSON.stringify(info));

	    	if (info.length > 0) {
	    		fnMostrarInfoCap(info[0].ur, info[0].urName, info[0].ue, info[0].ueName, 
	    			info[0].ingresoEstimado, info[0].ingresoPorEjecutar, info[0].ingresoModificado, info[0].ingresoDevengado, 
	    			info[0].ingresoRecaudado, info[0].egresoAprobado, info[0].egresoPorEjercer, info[0].egresoModificado, 
	    			info[0].egresoComprometido, info[0].egresoDevengado, info[0].egresoEjercido, info[0].egresoPagado);
	    	} else {
	    		fnQuitarEstilosMod();
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la informaición</p>';
				muestraModalGeneral(3, titulo, mensaje);
	    	}
	    }else{
	    	fnQuitarEstilosMod();
	    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la informaición</p>';
			muestraModalGeneral(3, titulo, mensaje);
	    }
	})
	.fail(function(result) {
		ocultaCargandoGeneral();
		console.log("ERROR");
	    console.log( result );

	    fnQuitarEstilosMod();
	    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la informaición</p>';
		muestraModalGeneral(3, titulo, result);
	});
}

function fnUrComboLoc() {
	var contenido = '';

	dataObj = {
		option: 'mostrarUnidadNegocio'
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/componentes_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result) {
			dataJson = data.contenido.datos;

			var seleccionado = '';
			if (dataJson.length == 1) {
				seleccionado= ' selected ';
			} else {
				contenido += '<option value="-1" selected>Seleccionar...</option>';
			}
			
			for (var info in dataJson) {
				contenido += '<option value="'+dataJson[info].tagref+'" '+seleccionado+'>'+dataJson[info].tagdescription+'</option>';
			}
		} else {
			contenido = '<option value="-1" selected>Seleccionar...</option>';
		}
	})
	.fail(function(result) {
		// console.log("ERROR");
		// console.log( result );
	});

	return contenido;
}

function fnUeComboLoc() {
	var contenido = '';

	dataObj = {
		option: 'mostrarUnidadEjecutora'
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/componentes_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result) {
			dataJson = data.contenido.datos;

			var seleccionado = '';
			if (dataJson.length == 1) {
				seleccionado= ' selected ';
			} else {
				contenido += '<option value="-1" selected>Seleccionar...</option>';
			}
			
			for (var info in dataJson) {
				contenido += '<option value="'+dataJson[info].ue+'" '+seleccionado+'>'+dataJson[info].uedescription+'</option>';
			}
		} else {
			contenido = '<option value="-1" selected>Seleccionar...</option>';
		}
	})
	.fail(function(result) {
		// console.log("ERROR");
		// console.log( result );
	});

	return contenido;
}

function fnCargarCuentasConfig() {
	muestraCargandoGeneral();

	dataObj = { 
	        option: 'obtClavesConfig',
	        ur: $('#selectUnidadNegocio').val(),
	    	ue: $('#selectUnidadEjecutora').val()
	      };
    $.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/companyPreferencesModelo.php",
		data: dataObj
    })
    .done(function( data ) {
    	ocultaCargandoGeneral();
        if(data.result) {
        	// console.log("ingresoEstimado: "+JSON.stringify(data.contenido.datos.ingresoEstimado));
        	// console.log("length: "+data.contenido.datos.ingresoEstimado.length);
            // console.log("ultimo accountcode: "+data.contenido.datos.ingresoEstimado[data.contenido.datos.ingresoEstimado.length - 1].accountcode);
            
            fnCrearListaConfig(data.contenido.datos.ingresoEstimado, 'txtIngresoEstimado');
            if (proceso == 'Agregar' && data.contenido.datos.ingresoEstimado.length > 0) {
            	$("#txtIngresoEstimado").val(""+data.contenido.datos.ingresoEstimado[data.contenido.datos.ingresoEstimado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.ingresoPorEjecutar, 'txtIngresoPorEjecutar');
            if (proceso == 'Agregar' && data.contenido.datos.ingresoPorEjecutar.length > 0) {
            	$("#txtIngresoPorEjecutar").val(""+data.contenido.datos.ingresoPorEjecutar[data.contenido.datos.ingresoPorEjecutar.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.ingresoModificado, 'txtIngresoModificado');
            if (proceso == 'Agregar' && data.contenido.datos.ingresoModificado.length > 0) {
            	$("#txtIngresoModificado").val(""+data.contenido.datos.ingresoModificado[data.contenido.datos.ingresoModificado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.ingresoDevengado, 'txtIngresoDevengado');
            if (proceso == 'Agregar' && data.contenido.datos.ingresoDevengado.length > 0) {
            	$("#txtIngresoDevengado").val(""+data.contenido.datos.ingresoDevengado[data.contenido.datos.ingresoDevengado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.ingresoRecaudado, 'txtIngresoRecaudado');
            if (proceso == 'Agregar' && data.contenido.datos.ingresoRecaudado.length > 0) {
            	$("#txtIngresoRecaudado").val(""+data.contenido.datos.ingresoRecaudado[data.contenido.datos.ingresoRecaudado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoAprobado, 'txtEgresoAprobado');
            if (proceso == 'Agregar' && data.contenido.datos.egresoAprobado.length > 0) {
            	$("#txtEgresoAprobado").val(""+data.contenido.datos.egresoAprobado[data.contenido.datos.egresoAprobado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoPorEjercer, 'txtEgresoPorEjercer');
            if (proceso == 'Agregar' && data.contenido.datos.egresoPorEjercer.length > 0) {
            	$("#txtEgresoPorEjercer").val(""+data.contenido.datos.egresoPorEjercer[data.contenido.datos.egresoPorEjercer.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoModificado, 'txtEgresoModificado');
            if (proceso == 'Agregar' && data.contenido.datos.egresoModificado.length > 0) {
            	$("#txtEgresoModificado").val(""+data.contenido.datos.egresoModificado[data.contenido.datos.egresoModificado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoComprometido, 'txtEgresoComprometido');
            if (proceso == 'Agregar' && data.contenido.datos.egresoComprometido.length > 0) {
            	$("#txtEgresoComprometido").val(""+data.contenido.datos.egresoComprometido[data.contenido.datos.egresoComprometido.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoDevengado, 'txtEgresoDevengado');
            if (proceso == 'Agregar' && data.contenido.datos.egresoDevengado.length > 0) {
            	$("#txtEgresoDevengado").val(""+data.contenido.datos.egresoDevengado[data.contenido.datos.egresoDevengado.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoEjercido, 'txtEgresoEjercido');
            if (proceso == 'Agregar' && data.contenido.datos.egresoEjercido.length > 0) {
            	$("#txtEgresoEjercido").val(""+data.contenido.datos.egresoEjercido[data.contenido.datos.egresoEjercido.length - 1].accountcode);
            }

            fnCrearListaConfig(data.contenido.datos.egresoPagado, 'txtEgresoPagado');
            if (proceso == 'Agregar' && data.contenido.datos.egresoPagado.length > 0) {
            	$("#txtEgresoPagado").val(""+data.contenido.datos.egresoPagado[data.contenido.datos.egresoPagado.length - 1].accountcode);
            }
        } else {
        	$('#divPreferenciasEmpMensajes').empty();
        	$('#divPreferenciasEmpMensajes').append('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>No se creó la lista de Cuentas Contables para realizar la configuración</p></div>');
			// $('#divPreferenciasEmpMensajes').append('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se creó la lista de Cuentas Contables para realizar la configuración</p>');
        }
    })
    .fail(function(result) {
    	ocultaCargandoGeneral();
        console.log( result );
        $('#divPreferenciasEmpMensajes').empty();
        $('#divPreferenciasEmpMensajes').append('<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert">&times;</button><p>No se creó la lista de Cuentas Contables para realizar la configuración</p></div>');
		// $('#divPreferenciasEmpMensajes').append('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se creó la lista de Cuentas Contables para realizar la configuración</p>');
    });
}

function fnCrearListaConfig(jsonData, nombreElemento = '') {
	// console.log("jsonData: "+JSON.stringify(jsonData));
	$( "#" + nombreElemento ).autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            $( this ).val( ui.item.accountcode + "");
            $( "#" + nombreElemento  ).val( ui.item.accountcode );
            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
		return $( "<li>" )
		.append( "<a>" + item.accountname + "</a>" )
		.appendTo( ul );
    };
}

function fnMostrarInfoCap(ur = '', urName = '', ue = '', ueName = '', 
	ingresoEstimado = '', ingresoPorEjecutar = '', ingresoModificado = '', ingresoDevengado = '', 
	ingresoRecaudado = '', egresoAprobado = '', egresoPorEjercer = '', egresoModificado = '', 
	egresoComprometido = '', egresoDevengado = '', egresoEjercido = '', egresoPagado = '') {

	fnPonerEstilosMod();

	$('#divPreferenciasEmpMensajes').empty();

	var valoresUr = '<option value="-1" selected>Seleccionar...</option>';
	var valoresUe = '<option value="-1" selected>Seleccionar...</option>';

	if (ur.trim() != '' && ur.trim() != null) {
		valoresUr = '<option value="'+ur+'" selected>'+urName+'</option>'
	} else {
		valoresUr = fnUrComboLoc();
	}

	if (ue.trim() != '' && ue.trim() != null) {
		valoresUe = '<option value="'+ue+'" selected>'+ueName+'</option>'
	} else {
		valoresUe = fnUeComboLoc();
	}

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	var mensaje = '';
	mensaje += '\
	<div class="row container-fluid" id="divPreferenciasEmp" name="divPreferenciasEmp">\
		<div id="divPreferenciasEmpMensajes" name="divPreferenciasEmpMensajes"></div>\
	    <div class="row"></div>\
	    <div class="col-md-6">\
	        <div class="form-inline row">\
	            <div class="col-md-3">\
	                <span><label>UR: </label></span>\
	            </div>\
	            <div class="col-md-9">\
	                <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral(\'selectUnidadNegocio\',\'selectUnidadEjecutora\')">\
	                    '+valoresUr+'\
	                </select>\
	            </div>\
	        </div>\
	    </div>\
	    <div class="col-md-6">\
	        <div class="form-inline row">\
	            <div class="col-md-3">\
	                <span><label>UE: </label></span>\
	            </div>\
	            <div class="col-md-9">\
	                <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora" onchange="fnCargarCuentasConfig()">\
	                    '+valoresUe+'\
	                </select>\
	            </div>\
	        </div>\
	    </div>\
	    <div class="row"></div>\
	    <br>\
	    <h5>Cuentas Ingreso</h5>\
	    <div class="row"></div>\
	    <div class="col-md-4">\
	        <component-text-label label="Estimado:" id="txtIngresoEstimado" name="txtIngresoEstimado" placeholder="Estimado" title="Estimado" value="'+ingresoEstimado+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Por Ejecutar:" id="txtIngresoPorEjecutar" name="txtIngresoPorEjecutar" placeholder="Por Ejecutar" title="Por Ejecutar" value="'+ingresoPorEjecutar+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Modificado:" id="txtIngresoModificado" name="txtIngresoModificado" placeholder="Modificado" title="Modificado" value="'+ingresoModificado+'"></component-text-label>\
	    </div>\
	    <div class="row"></div>\
	    <br>\
	    <div class="col-md-4">\
	        <component-text-label label="Devengado:" id="txtIngresoDevengado" name="txtIngresoDevengado" placeholder="Devengado" title="Devengado" value="'+ingresoDevengado+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Recaudado:" id="txtIngresoRecaudado" name="txtIngresoRecaudado" placeholder="Recaudado" title="Recaudado" value="'+ingresoRecaudado+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	    </div>\
	    <div class="row"></div>\
	    <br>\
	    <h5>Cuentas Egreso</h5>\
	    <div class="row"></div>\
	    <div class="col-md-4">\
	        <component-text-label label="Aprobado:" id="txtEgresoAprobado" name="txtEgresoAprobado" placeholder="Aprobado" title="Aprobado" value="'+egresoAprobado+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Por Ejercer:" id="txtEgresoPorEjercer" name="txtEgresoPorEjercer" placeholder="Por Ejercer" title="Por Ejercer" value="'+egresoPorEjercer+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Modificado:" id="txtEgresoModificado" name="txtEgresoModificado" placeholder="Modificado" title="Modificado" value="'+egresoModificado+'"></component-text-label>\
	    </div>\
	    <div class="row"></div>\
	    <br>\
	    <div class="col-md-4">\
	        <component-text-label label="Comprometido:" id="txtEgresoComprometido" name="txtEgresoComprometido" placeholder="Comprometido" title="Comprometido" value="'+egresoComprometido+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Devengado:" id="txtEgresoDevengado" name="txtEgresoDevengado" placeholder="Devengado" title="Devengado" value="'+egresoDevengado+'"></component-text-label>\
	    </div>\
	    <div class="col-md-4">\
	        <component-text-label label="Ejercido:" id="txtEgresoEjercido" name="txtEgresoEjercido" placeholder="Ejercido" title="Ejercido" value="'+egresoEjercido+'"></component-text-label>\
	    </div>\
	    <div class="row"></div>\
	    <br>\
	    <div class="col-md-4">\
	        <component-text-label label="Pagado:" id="txtEgresoPagado" name="txtEgresoPagado" placeholder="Pagado" title="Pagado" value="'+egresoPagado+'"></component-text-label>\
	    </div>\
	    <div class="row"></div>\
	    <div align="center">\
	      <component-button type="button" id="btnGuardarInfo" name="btnGuardarInfo" onclick="fnGuardarInformacion()" value="Guardar" class="glyphicon glyphicon-floppy-disk"></component-button>\
	      <br><br>\
	    </div>\
	</div>\
	';
	muestraModalGeneral(4, titulo, mensaje);

	setTimeout(function () {
        fnEjecutarVueGeneral('divPreferenciasEmp');
        fnFormatoSelectGeneral('.selectUnidadNegocio');
        fnFormatoSelectGeneral('.selectUnidadEjecutora');

        if (proceso == 'Modificar') {
        	fnCargarCuentasConfig();
        }
    }, 200);
}