/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 */
var url = "modelo/ABC_Finalidad_modelo.php";
var proceso = "";

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
});

function fnMostrarDatos(idfinalidad){
	//console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        idfinalidad: idfinalidad
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

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Finalidad</h3>';
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
			msg += '<p>Falta capturar la clave FI </p>';
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

function fnModificar(idfinalidad){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
	        option: 'mostrarCatalogo',
	        idfinalidad: idfinalidad
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

				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Finalidad</h3>';
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
function fnEliminar(idfinalidad,descFinalidad){
	$("#txtClaveEliminar").val(""+idfinalidad);
	//var descripcion = $('#txtDescripcion').val();

	var mensaje = 'Desea eliminar la Finalidad '+idfinalidad+' - '+descFinalidad;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var idfinalidad = $('#txtClaveEliminar').val();
	var descripcion = $('#txtDescripcion').val();
	$('#ModalUREliminar').modal('hide');
	if(!fnExistencia(idfinalidad)){
		muestraMensaje('No es posible eliminar la finalidad seleccionada por que tiene una o mas funciones asignadas' , 3, 'divMensajeOperacion', 5000);
		//muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No es posible eliminar la finalidad seleccionada por que tiene una o mas funciones asignadas');
	}else{
		dataObj = { 
		        option: 'eliminarUR',
		        descripcion: descripcion,
		        idfinalidad: idfinalidad
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

function fnExistencia(idFinalidad){
	console.log(idFinalidad);
	var existe = true;
	dataObj = { 
	    option: 'existeFuncion',
	    idFinalidad: idFinalidad
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