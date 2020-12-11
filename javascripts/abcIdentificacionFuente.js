/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos
 * @version 0.1
 */
var url = "modelo/abcIdentificacionFuenteModelo.php";
var proceso = "";

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
});

function fnMostrarDatos(id_identificacion){
	//console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        id_identificacion: id_identificacion
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
	    	dataFinalidadJason = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			
	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');

	    	var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1];
			var columnasVisuales= [0, 1, 2, 3];
			fnAgregarGrid_Detalle(dataFinalidadJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
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
	//console.log("fnAgregarCatalogoModal");

	proceso = "Agregar";

	$("#txtClave").prop("readonly", false);
	$('#txtClave').val("");
	$('#txtDescripcion').val("");

    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Identificación de la Fuente</h3>';


	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if ( (clave == "") && (descripcion == "")) {
		
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Finalidad y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
		
	}else if (clave == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Finalidad para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		
		return false;
	}*/
	if (clave == "" || descripcion == "") {
		if (clave == "" ) {
			msg += '<p>Falta capturar la clave </p>';
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

function fnModificar(id_identificacion){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
	        option: 'mostrarCatalogo',
	        id_identificacion: id_identificacion
	      };
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: url,
		      data:dataObj
		  })
		.done(function( data ) {
		    if(data.result){
		    	info=data.contenido.datos;
		    	for (key in info){
		    		$("#txtClave").val(""+info[key].Clave);
		    		$("#txtClave").prop("readonly", true);
		    		$("#txtDescripcion").val(""+info[key].Descripcion);	
				}

				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Identificación de la Fuente</h3>';
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
function fnEliminar(id_identificacion,desc_identificacion){
	$("#txtClaveEliminar").val(""+id_identificacion);
	//var descripcion = $('#txtDescripcion').val();

	var mensaje = 'Desea eliminar la Identificación de la Fuente '+id_identificacion+' - '+desc_identificacion;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var id_identificacion = $('#txtClaveEliminar').val();
	var desc_identificacion = $('#txtDescripcion').val();
	$('#ModalUREliminar').modal('hide');
	if(!fnExistencia(id_identificacion)){
		muestraMensaje('No es posible eliminar la Identificación de la Fuente seleccionada por que tiene una o mas funciones asignadas.' , 3, 'divMensajeOperacion', 5000);
		//muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No es posible eliminar la finalidad seleccionada por que tiene una o mas funciones asignadas');
	}else{
		dataObj = { 
		        option: 'eliminarUR',
		        desc_identificacion: desc_identificacion,
		        id_identificacion: id_identificacion
		      };
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: url,
		      data:dataObj
		  })
		.done(function( data ) {
		    if(data.result){
				muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
				//muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', data.contenido);
				fnLimpiarTabla('divTabla', 'divContenidoTabla');
		    	fnMostrarDatos('');
		    }else{
		    	muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
		    	//muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', data.contenido);
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		});
	}
}

function fnExistencia(id_identificacion){
	console.log(id_identificacion);
	var existe = true;
	dataObj = { 
	    option: 'existeFuncion',
	    id_identificacion: id_identificacion
	};
	$.ajax({
		async:false,
        cache:false,
	    method: "POST",
	    dataType:"json",
	    url: url,
	    data:dataObj
	})
	.done(function( data ) {
	    if(data.result){
	    	existe = true;
	    }else{
	    	existe = false;
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
	return	existe;
}