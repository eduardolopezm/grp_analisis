/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 */
var url = "modelo/ABC_Funcion_modelo.php";
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
function fnMostrarDatos(idFuncion,idFinalidad){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        idFuncion: idFuncion,
	        idFinalidad: idFinalidad
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
			var columnasExcel= [0, 1, 2];
			var columnasVisuales= [0, 1, 2, 3, 4];
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

	//$("#selectFinalidad").multiselect('rebuild');
	$("#txtClave").prop("readonly", false);
	$('#txtClave').val("");
	$('#txtDescripcion').val("");

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Función</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	//$("button").removeAttr('disabled');
	var idFinalidad = $('#selectFinalidad').val();
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if (idFinalidad == "" || idFinalidad == 0 || clave == "" || descripcion == "") {
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
			msg += '<p>Falta capturar la clave FU </p>';
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
		        idFinalidad: idFinalidad,
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

function fnModificar(idFuncion,descFuncion,idFinalidad,descFinalidad){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
		        option: 'mostrarCatalogo',
		        idFuncion: idFuncion,
		        descFuncion: descFuncion,
		        idFinalidad: idFinalidad,
		        descFinalidad: descFinalidad
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
		    		//$("#selectFinalidad").empty();
	                //$("#selectFinalidad").html('<option value="'+idFinalidad+'">'+idFinalidad+' - '+descFinalidad+'</option>');
	                
	                $("#selectFinalidad option[value="+ idFinalidad +"]").attr("selected",true);
	                $("#selectFinalidad").multiselect('rebuild');
	                $(".multiselect").attr('disabled', 'disabled');
	                //$("#selectFinalidad").prop('disabled', 'disabled');
		    		$("#txtClave").prop("readonly", true);
		    		$("#txtClave").val(""+info[key].Clave);
		    		$("#txtDescripcion").val(""+info[key].Funcion);	
				}
				//$('#selectFinalidad').empty();
				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Función</3>';
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

function fnEliminar(idFuncion,descFuncion,idFinalidad){
	//$("button").removeAttr('disabled');
	$("#txtClaveEliminar").val(""+idFuncion);
	$("#txtFinalidadEliminar").val(""+idFinalidad);

	var mensaje = 'Desea eliminar la Función '+idFuncion+' - '+descFuncion;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var idFuncion = $('#txtClaveEliminar').val();
	var idFinalidad = $('#txtFinalidadEliminar').val();
	var descripcion = $('#txtDescripcion').val();

	$('#ModalUREliminar').modal('hide');

	if(!fnExistencia(idFinalidad, idFuncion)){
		muestraMensaje('No es posible eliminar la función seleccionada por que tiene una o mas subfunciones asignadas' , 3, 'divMensajeOperacion', 5000);
	}else{
		dataObj = { 
	        option: 'eliminarUR',
	        descripcion: descripcion,
	        idFuncion: idFuncion,
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

function fnExistencia(idFinalidad, idFuncion){
	var existe = true;
	dataObj = { 
	    option: 'existeSubfuncion',
	    idFinalidad: idFinalidad,
	    idFuncion: idFuncion,
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