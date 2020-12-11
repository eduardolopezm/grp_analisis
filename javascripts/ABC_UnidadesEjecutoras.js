/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$("#txtClave").keyup(function(){
	    var upperclave = $("#txtClave").val();
		upperclave = upperclave.toUpperCase();	
		$("#txtClave").val(""+upperclave);
	});
});

var proceso = "";
var cmbRazonSocialGeneral = "";
var cmbEstadoGeneral = "";

//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ue Código del Registro para obtener la información
 */
function fnMostrarDatos(ue){
	console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ue: ue
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_UnidadesEjecutoras_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	
	    	//fnAgregarGridv2(info, 'divCatalogo', 'Prueba');

	    	var columnasNombres= "", columnasNombresGrid= "";
	    	// Columnas para el GRID
		    columnasNombres += "[";
		    columnasNombres += "{ name: 'UR', type: 'string' },";
		    columnasNombres += "{ name: 'UE', type: 'string' },";
		    columnasNombres += "{ name: 'Estado', type: 'string' },";
		    columnasNombres += "{ name: 'Auxiliar1', type: 'string' },";
            columnasNombres += "{ name: 'Descripcion', type: 'string' },";
            columnasNombres += "{ name: 'Modificar', type: 'string' },";                
            columnasNombres += "{ name: 'Eliminar', type: 'string' }";
		    columnasNombres += "]";

		    // Columnas para el GRID
		    columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'UR', datafield: 'UR', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'UE', datafield: 'UE', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'Estado', datafield: 'Estado', width: '5%', cellsalign: 'center', align: 'center', hidden: true },";
            columnasNombresGrid += " { text: 'AUX1', datafield: 'Auxiliar1', width: '8%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Descripción', datafield: 'Descripcion', width: '78%', cellsalign: 'left', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
		    columnasNombresGrid += " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
            columnasNombresGrid += "]";

	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(info, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [3, 4];
			var columnasVisuales= [3, 4, 5, 6];
			fnAgregarGrid_Detalle(info, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/** Muestra formulario para agregar un nuevo registro */
function fnAgregarCatalogoModal(){
	$('#divMensajeOperacion').addClass('hide');

	proceso = "Agregar";

	$("#txtClave").prop("readonly", false);
	$('#txtClaveId').val("");
	$('#txtClave').val("");
	$('#txtDescripcion').val("");

	$('#cmbRazonSocial').val(null);
	$('#cmbRazonSocial').multiselect('rebuild');
	$('#cmbRazonSocial').multiselect('enable');
	
	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Auxiliar 1</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	var claveId = $('#txtClaveId').val();
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var cmbRazonSocial = $('#cmbRazonSocial').val();
	var cmbEstado = $('#cmbEstado').val();
	var msg = "";

	/*if (clave == "" || descripcion == "" || clave == "") {
		muestraMensaje('Completar la información para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}*/
	if (cmbRazonSocial == "" || cmbRazonSocial === "undefined" || cmbRazonSocial == "-1" || cmbRazonSocial == 0 || cmbRazonSocial == null || clave == "" || descripcion == "") {
		if (cmbRazonSocial == "" || cmbRazonSocial === "undefined" || cmbRazonSocial == "-1" || cmbRazonSocial == 0 || cmbRazonSocial == null ) {
			msg += '<p>Falta capturar la clave UR </p>';
		}
		if (clave == "" ) {
			msg += '<p>Falta capturar la clave UE </p>';
		}
		if (descripcion=="" ) {
			msg += '<p>Falta capturar el Descripción </p>';
		}

		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		
	}else{
		$('#ModalUR').modal('hide');

	
		dataObj = { 
		        option: 'AgregarCatalogo',
		        claveId: claveId,
		        clave: clave,
		        descripcion: descripcion,
		        proceso: proceso,
				ur: cmbRazonSocial,
				estado: cmbEstado
		      };
	
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABC_UnidadesEjecutoras_modelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
		    if(data.result){
		    	muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);

		    	fnLimpiarTabla('divTabla', 'divCatalogo');
		    	fnMostrarDatos('');
		    }else{
		    	muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		});
	}
}

/**
 * Muestra formulario para modificar la información del registro seleccionado
 * @param  {String} id Código del Registro para obtener la información
 */
function fnModificar(id){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ue: id
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_UnidadesEjecutoras_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	//alert(JSON.stringify(info));
	    	for (key in info){
	    		$("#txtClaveId").val(""+id);
	    		$("#txtClave").val(""+info[key].UE);
	    		$("#txtClave").prop("readonly", true);
	    		$("#txtDescripcion").val(""+info[key].Descripcion);

	    		if (info[key].UR != "") {
					$("#cmbRazonSocial").selectpicker('val',[info[key].UR]);
			    	$("#cmbRazonSocial").multiselect('refresh');
			    	$(".selectUnidadNegocio").css("display", "none");
			    	$('#cmbRazonSocial').multiselect('disable');
	    		}

	    		if (info[key].Estado != "") {
		    		$('#cmbEstado').selectpicker('val', ''+info[key].Estado);
			        $("#cmbEstado").multiselect("refresh");
			        $(".selectGeografico").css("display", "none");
			    }
			}

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Auxiliar 1</h3>';
			$('#ModalUR_Titulo').empty();
		    $('#ModalUR_Titulo').append(titulo);
			$('#ModalUR').modal('show');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/** Muestra mensaje de confirmación para eliminar registro */
function fnEliminar(id){
	$("#txtClaveEliminar").val(""+id);

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ue: id
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_UnidadesEjecutoras_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	var UE = "";
	    	var Descripcion = "";
	    	for (key in info){
	    		UE = info[key].UE;
	    		Descripcion = info[key].Descripcion;
			}

			var mensaje = 'Desea eliminar el Auxiliar 1 '+UE+' - '+Descripcion;
			$('#ModalUREliminar_Mensaje').empty();
		    $('#ModalUREliminar_Mensaje').append(mensaje);
			$('#ModalUREliminar').modal('show');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/** Elimina el registro al confirmar la eliminación */
function fnEliminarEjecuta(){
	var claveId = $('#txtClaveEliminar').val();

	$('#ModalUREliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarUR',
	        claveId: claveId
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_UnidadesEjecutoras_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
			muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
			fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    }else{
	    	muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}