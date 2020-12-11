/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
}); 

var proceso = "";

//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} desc_ramo Código del Registro para obtener la información
 */
function fnMostrarDatos(desc_ramo){
	console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        desc_ramo: desc_ramo
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABCRamo_modelo.php",
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
		    columnasNombres += "{ name: 'Clave', type: 'string' },";
            columnasNombres += "{ name: 'Descripcion', type: 'string' },";
            columnasNombres += "{ name: 'Modificar', type: 'string' },";                
            columnasNombres += "{ name: 'Eliminar', type: 'string' }";
		    columnasNombres += "]";

		    // Columnas para el GRID
		    columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'RA', datafield: 'Clave', width: '6%', cellsalign: 'center', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Descripción', datafield: 'Descripcion', width: '80%', cellsalign: 'left', align: 'center', hidden: false },";
            columnasNombresGrid += " { text: 'Modificar', datafield: 'Modificar', width: '7%', cellsalign: 'center', align: 'center', hidden: false },";
		    columnasNombresGrid += " { text: 'Eliminar', datafield: 'Eliminar', width: '7%', cellsalign: 'center', align: 'center', hidden: false }";
            columnasNombresGrid += "]";

            var columnasDescartarExportar= [2, 3];

	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(info, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1];
			var columnasVisuales= [0, 1, 2, 3];
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

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Ramo</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	var claveId = $('#txtClaveId').val();
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if (clave == "" && descripcion == "") {

		muestraMensaje('Debe agregar Ramo y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if(clave == ""){
		muestraMensaje('Debe agregar Ramo para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if(descripcion == ""){
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}*/
	if (clave == "" || descripcion == "") {
		if (clave == "" ) {
			msg += '<p>Falta capturar la clave RA </p>';
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
		        proceso: proceso
		      };
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABCRamo_modelo.php",
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

	dataObj = { 
	        option: 'mostrarCatalogo',
	        desc_ramo: id
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABCRamo_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	    	info=data.contenido.datosCatalogo;
	    	for (key in info){
	    		$("#txtClaveId").val(""+id);
	    		$("#txtClave").val(""+info[key].Clave);
	    		$("#txtClave").prop("readonly", true);
	    		$("#txtDescripcion").val(""+info[key].Descripcion);	
			}
			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Ramo</h3>';
			$('#ModalUR_Titulo').empty();
		    $('#ModalUR_Titulo').append(titulo);
			$('#ModalUR').modal('show');
	    }else{
	    	$('#divMensajeOperacion').removeClass('hide');
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
	        desc_ramo: id
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABCRamo_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	var Clave = "";
	    	var Descripcion = "";
	    	for (key in info){
	    		Clave = info[key].Clave;
	    		Descripcion = info[key].Descripcion;
			}

			var mensaje = 'Desea eliminar el Ramo '+Clave+' - '+Descripcion;
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
	      url: "modelo/ABCRamo_modelo.php",
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

/** Comparar tamaño de la clave si es un digito le agrega 0 */
function fnVerificarClave() {
	var clave = $("#txtClave").val();
	if (clave.length == 1) {
		$("#txtClave").val("0"+clave);
	}
	//alert("num: "+clave.length);
}