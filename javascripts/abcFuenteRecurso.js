/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesùs Reyes Santos
 * @version 0.1
 */
var url = "modelo/abcFuenteRecursoModelo.php";
var proceso = "";
 
$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('','');
});

//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(id_fuente,id_identificacion){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        id_fuente: id_fuente,
	        id_identificacion: id_identificacion
	      };
	//Obtener datos de las bahias
	$.ajax({
		  async:false,
          cache:false,
	      method: "POST",
	      dataType:"json",
	      url: url,
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	dataFuncionJason = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			
	    	fnLimpiarTabla('divTabla', 'divContenidoTabla');

	    	var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1, 2, 3];
			var columnasVisuales= [0, 1, 2, 3, 4, 5];
			fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
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

	//$("#selectFuenteRecurso").multiselect('rebuild');
	$("#txtClave").prop("readonly", false);
	$('#txtClave').val("");
	$('#txtDescripcion').val("");
	$('#selectFuenteRecurso').multiselect('enable');

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Fuente de Recurso</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	//$("button").removeAttr('disabled');
	var id_identificacion = $('#selectFuenteRecurso').val();
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if (id_identificacion == "" || id_identificacion == 0 || clave == "" || descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Agregar Finalidad, Función y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}*/
	/*if (clave == "" && descripcion == "") {
		
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Función y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
		
	}else if (clave == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Función para realizar el proceso', 3, 'msjValidacion', 5000);
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
		        id_identificacion: id_identificacion,
		        clave: clave,
		        descripcion: descripcion,
		        proceso: proceso
		      };
		//Obtener datos de las bahias
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
		    	muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);

		    	fnLimpiarTabla('divTabla', 'divContenidoTabla');
		    	fnMostrarDatos('','');
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
function fnModificar(id_fuente,desc_fuente,id_identificacion,desc_identificacion){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
		        option: 'mostrarCatalogo',
		        id_fuente: id_fuente,
		        desc_fuente: desc_fuente,
		        id_identificacion: id_identificacion,
		        desc_identificacion: desc_identificacion
		      };
		//Obtener datos de las bahias
		$.ajax({
			  async:false,
	          cache:false,
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
		    	for (key in info){
	                $("#selectFuenteRecurso").val(""+id_identificacion);
	                $("#selectFuenteRecurso").multiselect('rebuild');
					$('#selectFuenteRecurso').multiselect('disable');
	                //$("#selectFinalidad").prop('disabled', 'disabled');
		    		$("#txtClave").prop("readonly", true);
		    		$("#txtClave").val(""+info[key].Clave);
		    		$("#txtDescripcion").val(""+info[key].Funcion);	
				}
				//$('#selectFinalidad').empty();
				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Fuente del Recurso </3>';
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


function fnEliminar(idFuente,descFuente,idIdentificacion){
	//$("button").removeAttr('disabled');
	$("#txtClaveEliminar").val(""+idFuente);
	$("#txtFuenteEliminar").val(""+idIdentificacion);

	var mensaje = 'Desea eliminar la Fuente del Recurso '+idFuente+' - '+descFuente;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var idFuente = $('#txtClaveEliminar').val();
	var idIdentificacion = $('#txtFuenteEliminar').val();
	var descripcion = $('#txtDescripcion').val();

	$('#ModalUREliminar').modal('hide');

	if(!fnExistencia(idIdentificacion, idFuente)){
		muestraMensaje('No es posible eliminar la fuente del recurso seleccionada por que tiene una o mas subfunciones asignadas' , 3, 'divMensajeOperacion', 5000);
	}else{
		dataObj = { 
	        option: 'eliminarUR',
	        descripcion: descripcion,
	        idFuente: idFuente,
	        idIdentificacion: idIdentificacion
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
				muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
				fnLimpiarTabla('divTabla', 'divContenidoTabla');
		    	fnMostrarDatos('','');
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

function fnExistencia(idIdentificacion, idFuente){
	var existe = true;
	dataObj = { 
	    option: 'existeSubfuncion',
	    idIdentificacion: idIdentificacion,
	    idFuente: idFuente,
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