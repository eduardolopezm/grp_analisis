/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 */
 var url = "modelo/ABC_Reasignaciones_modelo.php";
 var proceso = "";
 
$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$("#txtClave").blur(function(){
	    var addCero = $("#txtClave").val();
		if (addCero.length == 1){
			$("#txtClave").val("0"+addCero)
		}	
	});
	
});



//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(idreasignacion){
	//console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        idreasignacion: idreasignacion
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: url,
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	dataReasignacionJason = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			
	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');

	    	var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1];
			var columnasVisuales= [0, 1, 2, 3];
			fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
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
	$('#txtClave').val("");
	$('#txtDescripcion').val("");

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Reasignación</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if (clave == "" || descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Agregar Reasignación y descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}*/
	/*if ( (clave == "") && (descripcion == "")) {
		
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Reasignación y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
		
	}else if (clave == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Reasignación para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		
		return false;
	}*/
	if (clave == "" || descripcion == "") {
		if (clave == "" ) {
			msg += '<p>Falta capturar la clave RG </p>';
		}
		if (descripcion=="" ) {
			msg += '<p>Falta capturar el Descripción </p>';
		}

		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		
	}else{
		$('#ModalUR').modal('hide');

		//Opcion para operacion
		dataObj = { 
		        option: 'AgregarCatalogo',
		        clave: clave,
		        descripcion: descripcion,
		        proceso: proceso
		      };
		//Obtener datos de las bahias
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: url,
		      data:dataObj
		  })
		.done(function( data ) {
		    if(data.result){
		    	muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);

		    	fnLimpiarTabla('divTabla', 'divContenidoTabla');
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

function fnModificar(idreasignacion){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        idreasignacion: idreasignacion
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: url,
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	console.log(info);
	    	for (key in info){
	    		$("#txtClave").val(""+info[0].Clave);
	    		$("#txtClave").prop("readonly", true);
	    		//$("#txtClave").attr('disabled', 'disabled');
	    		$("#txtDescripcion").val(""+info[0].Descripcion);	
			}

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Reasignación</h3>';
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

function fnEliminar(idreasignacion,descReasignacion){
	$("#txtClaveEliminar").val(""+idreasignacion);

	var mensaje = 'Desea eliminar la Reasignación '+idreasignacion+' - '+descReasignacion;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var idreasignacion = $('#txtClaveEliminar').val();
	var descripcion = $('#txtDescripcion').val();

	$('#ModalUREliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarUR',
	        descripcion: descripcion,
	        idreasignacion: idreasignacion
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Reasignaciones_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
			muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);

			fnLimpiarTabla('divTabla', 'divContenidoTabla');
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