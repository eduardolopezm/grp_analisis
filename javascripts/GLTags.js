/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Eduardo López Morales
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
 * @param  {String} tagref Código del Registro para obtener la información
 */
function fnMostrarDatos(tagref){
	//console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        tagref: tagref
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GLTags_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	
	    	fnAgregarGridv2(info, 'divCatalogo');
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

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Unidad Responsable</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	document.getElementById("msjValidacion").style.display = "none";
	
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();

	if (clave == "" || descripcion == "") {
		document.getElementById("msjValidacion").style.display = "block";
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
	      url: "modelo/ABC_UnidadesResponsables_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
	    if(data.result){
	    	muestraModalGeneral(3, titulo, data.contenido);

	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

/**
 * Muestra formulario para modificar la información del registro seleccionado
 * @param  {String} ur Código del Registro para obtener la información
 */
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
	      url: "modelo/ABC_UnidadesResponsables_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;
	    	for (key in info){
	    		$("#txtClave").val(""+info[key].UR);
	    		$("#txtClave").prop("readonly", true);
	    		$("#txtDescripcion").val(""+info[key].Descripcion);	
			}

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Unidad Responsable</h3>';
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
function fnEliminar(ur){
	$("#txtClaveEliminar").val(""+ur);

	var mensaje = '<h3>Desea eliminar la unidad responsable '+ur+'</h3>';
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

/** Elimina el registro al confirmar la eliminación */
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
	      url: "modelo/ABC_UnidadesResponsables_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
			muestraModalGeneral(3, titulo, data.contenido);

			fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}