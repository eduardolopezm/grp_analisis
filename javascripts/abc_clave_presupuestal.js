/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var dataJsonConfiguracionClave = new Array();
var nombreElementosClaveNueva = new Array();
var dataJsonDatosElementosClaveNueva = new Array();
var idClavePresupuesto = "";

$( document ).ready(function() {
	dataObj = { 
		option: 'mostrarConfiguracionClave'
	};
	
	$.ajax({
		async:false,
        cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/abc_clave_presupuestal_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			dataJsonConfiguracionClave = data.contenido.datos;
		}else{
			//console.log("ERROR Modelo");
			//console.log( JSON.stringify(data) ); 
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});
}); 

function fnBuscarClavesNuevas() {
	muestraCargandoGeneral();
	//Opcion para operacion
	dataObj = { 
	      option: 'obtenerClavesNuevas',
	      legalid: $("#selectRazonSocial").val(),
	      tagref: $("#selectRazonSocial").val()
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/abc_clave_presupuestal_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			ocultaCargandoGeneral();
			//Si trae informacion
			dataJson = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			//console.log( "dataJson: " + JSON.stringify(dataJson) );
			fnLimpiarTabla('divTablaClavesNuevasManuales', 'divClavesNuevasManuales');
			fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divClavesNuevasManuales', '', 1);
		}else{
			ocultaCargandoGeneral();
			muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function fnNuevaClavePresupuestal() {
	var contenido = "";
	var optionsConfig = fnCrearDatosSelect(dataJsonConfiguracionClave, "", "", 0);

	contenido += '<div id="divMensajeClaveNueva" name="divMensajeClaveNueva"></div>\
					<div class="col-md-12">\
			          <div class="form-inline row">\
			              <div class="col-md-3">\
			                  <span><label>Configuración: </label></span>\
			              </div>\
			              <div class="col-md-9">\
			                  <select id="selectConfiguracionClave" name="selectConfiguracionClave" class="form-control selectConfiguracionClave" onchange="fnObtenerDatosClave()"> \
			                    <option value="-1">Seleccionar...</option> '+optionsConfig+'\
			                  </select>\
			              </div>\
			          </div>\
			        </div><hr/><div id="divFormulario" name="divFormulario"><div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-list-alt text-success" aria-hidden="true"></i> Nueva Clave Presupuetal</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
	fnFormatoSelectGeneral(".selectConfiguracionClave");
}

function fnObtenerDatosClave() {
	console.log("fnObtenerDatosClave");

	idClavePresupuesto = $("#selectConfiguracionClave").val();

	if (idClavePresupuesto == '-1') {
		muestraMensaje('Seleccionar una Configuración', 3, 'divMensajeClaveNueva', 5000);
	}else{
		muestraCargandoGeneral();

		//Opcion para operacion
		dataObj = { 
			option: 'datosConfiguracionClave',
			idClavePresupuesto: idClavePresupuesto
		};
		
		$.ajax({
			async:false,
	        cache:false,
			method: "POST",
			dataType:"json",
			url: "modelo/abc_clave_presupuestal_modelo.php",
			data:dataObj
		})
		.done(function( data ) {
			//console.log("Bien");
			if(data.result){
				//Si trae informacion
				dataJsonDatosElementosClaveNueva = data.contenido.datos;
				fnCrearFormularioNuevaClave('divFormulario', dataJsonDatosElementosClaveNueva);
			}else{
				ocultaCargandoGeneral();
				muestraMensaje('No se pudo traer la Configuración', 3, 'divMensajeClaveNueva', 5000);
			}
		})
		.fail(function(result) {
			ocultaCargandoGeneral();
			//console.log("ERROR");
			//console.log( result );
		});
	}
}

function fnCrearFormularioNuevaClave(divFormulario, dataJson) {
	var contenido = '';
	
	contenido += '<div class="col-md-12">\
				<h4 id="divMostrarClave" name="divMostrarClave" align="center"></h4>\
				</div><br><br>';

	contenido += '<div class="panel-body" style="height: '+($(window).height() - 380)+'px; overflow: scroll;">';

	for (var key in dataJson) {
		var elementoMostrar = "";
		var nombreElemento = 'config_clave_nueva_'+dataJson[key].nombre;

		var obj = new Object();
		obj.nombre = nombreElemento;
		obj.valor = "";
		obj.campoPresupuesto = dataJson[key].campoPresupuesto;
		obj.tabla = dataJson[key].tabla;
		obj.campo = dataJson[key].campo;
		obj.idClavePresupuesto = dataJson[key].idClavePresupuesto;
		nombreElementosClaveNueva.push(obj);

		if (dataJson[key].campoPresupuesto == 'anho') {
			var año = "";
			for (var key2 in dataJson[key].infoSelect) {
				año = dataJson[key].infoSelect[key2].value;
			}
			elementoMostrar += '<input type="text" id="'+nombreElemento+'" name="'+nombreElemento+'" value="'+año+'" placeholder="Año" title="Año" onchange="fnCambioConfig(this)" class="form-control" onpaste="return false" onkeypress="return soloNumeros(event)" maxlength="4" style="width: 100%;" />';
		}else{
			elementoMostrar += '<select id="'+nombreElemento+'" name="'+nombreElemento+'" class="form-control '+nombreElemento+'" onchange="fnCambioConfig(this)">';
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

	contenido += '</div>'; // Cierre de panel-body

	contenido += '\
				<div class="col-md-12" align="center">\
					<button type="button" id="btnGuardarClaveNueva" name="btnGuardarClaveNueva" class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk" onclick="fnGuardarClaveNueva()">Guardar<button>\
				</div><br><br>';
	// <component-button type="button" id="btnGuardarClaveNueva" name="btnGuardarClaveNueva" class="glyphicon glyphicon-plus" onclick="fnGuardarClaveNueva()" value="Guardar"></component-button>
	
	$('#'+divFormulario).empty();
	$('#'+divFormulario).append(contenido);

	for (var key in nombreElementosClaveNueva) {
		fnFormatoSelectGeneral("."+nombreElementosClaveNueva[key].nombre);
	}

	ocultaCargandoGeneral();
}

function fnGuardarClaveNueva() {
	var validacion = 1;
	
	for (var key in nombreElementosClaveNueva) {
		if ($("#"+nombreElementosClaveNueva[key].nombre).val() == "" || $("#"+nombreElementosClaveNueva[key].nombre).val() == "-1") {
			validacion = 0;
		}
		nombreElementosClaveNueva[key].valor = $("#"+nombreElementosClaveNueva[key].nombre).val();
	}

	if (validacion == 0) {
		muestraMensaje('Seleccionar toda la información de la Clave Presupuetal', 3, 'divMensajeClaveNueva', 5000);
	}else{
		// Guardar clave
		muestraCargandoGeneral();
		dataObj = { 
		      option: 'guardarClaveNueva',
		      nombreElementosClaveNueva: nombreElementosClaveNueva,
		      clave: $('#divMostrarClave').html()
		    };
		//Obtener datos de las bahias
		$.ajax({
		    method: "POST",
		    dataType:"json",
		    url: "modelo/abc_clave_presupuestal_modelo.php",
		    data:dataObj
		})
		.done(function( data ) {
		  //console.log("Bien");
		  if(data.result){
		      //Si trae informacion
		      ocultaCargandoGeneral();
		      muestraMensaje(data.contenido, 1, 'divMensajeClaveNueva', 5000);
		  }else{
		  	  ocultaCargandoGeneral();
		      muestraMensaje(data.contenido, 3, 'divMensajeClaveNueva', 5000);
		  }
		})
		.fail(function(result) {
		  ocultaCargandoGeneral();
		  // console.log("ERROR");
		  // console.log( result );
		});
	}
}

function fnCambioConfig(select) {
	var claveNueva = "";
	for (var key in nombreElementosClaveNueva) {
		if ($("#"+nombreElementosClaveNueva[key].nombre).val() != "") {
			if (claveNueva == "") {
				claveNueva += $("#"+nombreElementosClaveNueva[key].nombre).val();
			}else if ($("#"+nombreElementosClaveNueva[key].nombre).val() != '-1') {
				claveNueva += "-"+$("#"+nombreElementosClaveNueva[key].nombre).val();
			}
		}
	}

	$('#divMostrarClave').empty();
	$('#divMostrarClave').append(claveNueva);
}

function fnCambioRazonSocialClaveNueva() {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: $("#selectRazonSocial").val()
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/abc_clave_presupuestal_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
	  //console.log("Bien");
	  if(data.result){
	      //Si trae informacion
	      
	      dataJson = data.contenido.datos;
	      //console.log( "dataJson: " + JSON.stringify(dataJson) );
	      //alert(JSON.stringify(dataJson));
	      var contenido = "<option value='-1'>Seleccionar...</option>";
	      for (var info in dataJson) {
	        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
	      }
		$('#selectUnidadNegocio').empty();
		$('#selectUnidadNegocio').append(contenido);
		$('#selectUnidadNegocio').multiselect('rebuild');
	  }else{
	      // console.log("ERROR Modelo");
	      // console.log( JSON.stringify(data) ); 
	  }
	})
	.fail(function(result) {
	  // console.log("ERROR");
	  // console.log( result );
	});
	// Fin Unidad de Negocio
}

function fnCambioUnidadNegocioClaveNueva() {
	//console.log("fnCambioRazonSocial");
	tagref = $("#selectUnidadNegocio").val();
}