/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
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
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(ur){
	console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ur: ur
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Finalidad_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	
	    	fnAgregarGridv2(info, 'divCatalogo','Descripcion,85%,Descripción,');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/** Muestra formulario para agregar un nuevo registro */
function fnAgregarCatalogoModal(){
	console.log("fnAgregarCatalogoModal");

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

	if (clave == "" || descripcion == "") {
		muestraMensaje('Agregar Finalidad y descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}

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
	      url: "modelo/ABC_Finalidad_modelo.php",
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

function fnModificar(ur){
	proceso = "Modificar";

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ur: ur
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Finalidad_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
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

function fnEliminar(ur){
	$("#txtClaveEliminar").val(""+ur);
	var descripcion = $('#txtDescripcion').val();

	var mensaje = '<h3>Desea eliminar la Finalidad '+ur+' - '+descripcion+'</h3>';
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var ur = $('#txtClaveEliminar').val();

	$('#ModalUREliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarUR',
	        ur: ur
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Finalidad_modelo.php",
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