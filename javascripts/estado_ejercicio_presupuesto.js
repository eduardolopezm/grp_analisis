/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

var dataJsonSelConfigClave = new Array();
var dataJsonSelConfigClaveSel = new Array();
var tipoInformacion = 1;

$( document ).ready(function() {
	if (document.querySelector(".selectTipoMovimiento")) {
		// Presupuesto
		tipoInformacion = 1;
		fnCargarTipoInformación('.selectTipoMovimiento', 1);
	}
	if (document.querySelector(".selectConfigClave") && 1 == 2) {
		// dataObj = { 
		// 	option: 'mostrarConfiguracionClave'
		// };
		// fnSelectGeneralDatosAjax('.selectConfigClave', dataObj, 'modelo/componentes_modelo.php');

		var mensajeError = '<option value="">Sin Datos</option>';
	    options = { 
	        option: 'mostrarConfiguracionClave'
	    };
	    //Obtener datos de las bahias
	    $.ajax({
	        async:false,
	        cache:false,
	        method: "POST",
	        dataType:"json",
	        url: 'modelo/componentes_modelo.php',
	        data: options
	    })
	    .done(function( data ) {
	        //console.log("Bien");
	        if(data.result){
	            //Si trae informacion
	            var valorSelect = '';
	            if (data.contenido.datos.length == 1) {
	            	valorSelect = data.contenido.datos[0].value;
	            }
	            // console.log("valorSelect: "+valorSelect);
	            $('.selectConfigClave').append( fnCrearDatosSelect(data.contenido.datos, '', valorSelect, 0) );
	        }else{
	            $('.selectConfigClave').append( mensajeError );
	        }

	        $('.selectConfigClave').multiselect('rebuild');

	        if (data.contenido.datos.length == 1) {
	        	// Traer datos cuando solo es un registro
	        	fnObtenerConfigClave();
	        }
	    })
	    .fail(function(result) {
	        // Mensaje Error
	        console.log("result: "+result);
	    });
	}

	$("#tabMinistrado").click(function() {
		// Ministrado
		fnCargarTipoInformación('.selectTipoMovimiento', 2);
		tipoInformacion = 2;
	});
	$("#tabPresupuesto").click(function() {
		// Presupuesto
		fnCargarTipoInformación('.selectTipoMovimiento', 1);
		tipoInformacion = 1;
	});
	$("#tabradicado").click(function() {
		// Presupuesto
		fnCargarTipoInformación('.selectTipoMovimiento', 3);
		tipoInformacion = 3;
	});
});

/**
 * Función para cargar el tipo de movimiento de acuerdo al estado a visualizar
 * @param  {String} elemento Id del Elemento a visualizar
 * @param  {Number} tipo     Tipo de moviento, 1 - Presupuesto, 2 - Ministrado, 3 - Radicado
 * @return {[type]}          [description]
 */
function fnCargarTipoInformación(elemento = '', tipo = 1) {
	// Funcion para cargar el tipo de movimiento
	muestraCargandoGeneral();

	dataObj = { 
        option: 'mostrarTipo',
        tipo: tipo,
        tipoPresupuesto: $("#selectTipoPresupuesto").val()
    };
	fnSelectGeneralDatosAjax(''+elemento, dataObj, 'modelo/estado_ejercicio_presupuesto_modelo.php', 0);

	ocultaCargandoGeneral();
}

function fnObtenerConfigClave() {
	console.log("fnObtenerConfigClave");

	if ($("#selectConfigClave").val() == '0') {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar una Configuración</p>';
		muestraModalGeneral(3, titulo, mensaje);
	}else{
		muestraCargandoGeneral();

		//Opcion para operacion
		dataObj = { 
			option: 'datosConfiguracionClave',
			idClavePresupuesto: $("#selectConfigClave").val()
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
			if(data.result){
				//Si trae informacion
				ocultaCargandoGeneral();
				dataJsonSelConfigClave = data.contenido.datos;
				fnSeleccionarDatosClave(data.contenido.datos);
			}else{
				ocultaCargandoGeneral();
				var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
				var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se pudo traer la Configuración</p>';
				muestraModalGeneral(3, titulo, mensaje);
			}
		})
		.fail(function(result) {
			ocultaCargandoGeneral();
			//console.log("ERROR");
			//console.log( result );
		});
	}
}

function fnSeleccionarDatosClave(dataJson) {
	var contenido = '<div class="panel-body">'; // <h4>Componentes de Clave</h4>
	//contenido += '<h5><input type="checkbox" id="checkTodoConfig" name="checkTodoConfig" onclick="javascript:fnSeleccionCheckbox(this);"> Todos</h5>';
	for (var key in dataJson) {
		var elementoMostrar = "";
		var nombreElemento = 'selConfig_'+dataJson[key].nombre;

		contenido += '<div class="col-md-2 col-xs-4">';
		contenido += '<label><input type="checkbox" id="'+nombreElemento+'" name="'+nombreElemento+'" checked="true"> '+dataJson[key].nombre+'</label>';
		contenido += '</div>';
	}
	contenido += '</div>';

	document.getElementById("divTituloComponentes").style.display = "block";

	$("#divSelConfigClave").empty();
    $("#divSelConfigClave").append(contenido);
}

function fnSeleccionCheckbox(obj){
    //console.log("checked: "+obj.checked);
    var I = document.getElementById('appVue').value;
    //alert("valor de :" + I);
    for (i=0;i<document.formSelect.elements.length;i++){
        if(document.formSelect.elements[i].type == "checkbox"){
        	if ($("#"+obj.id).prop('checked')) {
        		document.formSelect.elements[i].checked = 1;
        	} else {
        		document.formSelect.elements[i].checked = 0;
        	}
        }
    }
}

function fnEstadoEjercicio() {
	//console.log("fnObtenerAdecuaciones");
	dataJsonSelConfigClaveSel = new Array();
	var datosSeleccion = 1;
	// console.log("dataJsonSelConfigClave: "+JSON.stringify(dataJsonSelConfigClave));
	for (var key in dataJsonSelConfigClave) {
		var elementoMostrar = "";
		var nombreElemento = 'selConfig_'+dataJsonSelConfigClave[key].nombre;
		if($('#'+nombreElemento).prop('checked') ) {
			//console.log("sel "+nombreElemento);
			datosSeleccion = 0;
			var obj = new Object();
			obj.campoPresupuesto = dataJsonSelConfigClave[key].campoPresupuesto;
			obj.nombre = dataJsonSelConfigClave[key].nombre;
			obj.tamEstEjercicio = dataJsonSelConfigClave[key].tamEstEjercicio;
			dataJsonSelConfigClaveSel.push(obj);
		}
	}

	if (datosSeleccion == 1) {
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar información a visualizar</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
	}

	// console.log("datos: "+JSON.stringify(dataJsonSelConfigClaveSel));
	
	var legalid = "";
	var selectRazonSocial = document.getElementById('selectRazonSocial');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            legalid = selectRazonSocial.selectedOptions[i].value;
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
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            ue = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
    }

    var meses = "";
	var selectMeses = document.getElementById('selectMeses');
    for ( var i = 0; i < selectMeses.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            meses = ""+Number(selectMeses.selectedOptions[i].value)+"";
        }else{
            meses = meses+", "+Number(selectMeses.selectedOptions[i].value)+"";
        }
    }

    var tipoMovimiento = "";
	var selectTipo = document.getElementById('selectTipo');
    for ( var i = 0; i < selectTipo.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
		if (i == 0) {
            tipoMovimiento = selectTipo.selectedOptions[i].value;
        }else{
            tipoMovimiento = tipoMovimiento+", "+selectTipo.selectedOptions[i].value;
        }
    }

    if (tipoMovimiento == "") {
    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar tipo de movimiento</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
    }

    var idClave = $("#selectConfigClave").val();

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerEstadoEjercicio',
	      legalid: legalid,
	      tagref: tagref,
	      ue: ue,
	      tipoMovimiento: tipoMovimiento,
	      idClave: idClave,
	      selConfig: dataJsonSelConfigClaveSel,
	      tipoInformacion: tipoInformacion,
	      meses: meses
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/estado_ejercicio_presupuesto_modelo.php",
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
			dataJsonNoCaptura = data.contenido.datos;
			//console.log( "dataJson: " + JSON.stringify(dataJson) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var columnasDescartarExportar= [];
			//fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasDescartarExportar, false);

			// arreglo que guarda las columnas que se ocultan para exportar a excel
            var columnasExcel= [];
            var columnasVisuales= [];
            var nombreExcel = data.contenido.nombreExcel;
            // console.log("numColumnasGeneral: "+data.contenido.numColumnasGeneral);
            for (var i = 0; i <= data.contenido.numColumnasGeneral; i++) {
            	columnasExcel.push( i );
            	columnasVisuales.push( i );
            }
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, "", columnasVisuales, nombreExcel);
		}else{
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la información</p>';
			muestraModalGeneral(3, titulo, mensaje);
		}
	})
	.fail(function(result) {
      ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function fnCambioRazonSocial() {
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio
	var legalid = "";
	var selectRazonSocial = document.getElementById('selectRazonSocial');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            legalid = selectRazonSocial.selectedOptions[i].value;
        }else{
            legalid = legalid+", "+selectRazonSocial.selectedOptions[i].value;
        }
    }
    
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/GLBudgetsByTagV2_Panel_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
	  //console.log("Bien");
	  if(data.result){
	      //Si trae informacion
	      
	      dataJson = data.contenido.datos;
	      //console.log( "dataJson: " + JSON.stringify(dataJson) );
	      //alert(JSON.stringify(dataJson));
	      var contenido = "";
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