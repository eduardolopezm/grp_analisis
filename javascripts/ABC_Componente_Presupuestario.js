$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$("#txtcp").keyup(function(){
	    var upperclave = $("#txtcp").val();
		upperclave = upperclave.toUpperCase();
		$("#txtcp").val(""+upperclave);
	});
});

//console.log("data: "+JSON.stringify(data));

function fnMostrarDatos(ur){
	$('#divMensajeOperacion').addClass('hide');

	//Opcion para operacion
	dataObj = {
	        option: 'mostrarCatalogo',
	        ur: ur
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Componente_Presupuestario_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	//info=data.contenido.datosCatalogo;

	    	//fnAgregarGridv2(info, 'divCatalogo', "filtrado");
	    	dataReasignacionJason = data.contenido.datosCatalogo;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1];
			var columnasVisuales= [0, 1, 2, 3];
	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);

	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnAgregarCatalogoModal(){
	$('#divMensajeOperacion').addClass('hide');

	$('#txtcp').prop("readonly", false);


	$('#txtcp').val("");
	$('#txtDescripcion').val("");


	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Auxiliar 3</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
	proceso = "Agregar";
	cp_original = "";
}

function fnAgregar(){
	var cp = $('#txtcp').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if (cp == "" || descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Faltan datos. ', 3, 'mensajesValidaciones', 5000);
		return false;
	} */
	/*if ( (cp == "") && (descripcion == "")) {

		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Auxiliar 3 y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;

	}else if (cp == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Auxiliar 3 para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);

		return false;
	}*/
	if (cp == "" || descripcion == "") {
		if (cp == "" ) {
			msg += '<p>Falta capturar la clave Auxiliar 3 </p>';
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
		        cp: cp,
		        descripcion: descripcion,
		        proceso: proceso,
		        cp_original: cp_original
		      };
		//Obtener datos de las bahias
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABC_Componente_Presupuestario_modelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
			var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
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

function fnModificar(cp){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	cp_original = cp;

	$('#txtcp').prop("readonly", true);
	//Opcion para operacion
	dataObj = {
	        option: 'mostrarCatalogo',
	        cp: cp,
	        proceso: proceso
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Componente_Presupuestario_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;

	    	$('#txtcp').val(info[0].cp);
			$('#txtDescripcion').val(info[0].descripcion);

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Auxiliar 3</h3>';
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

function fnEliminar(cp){
	$("#txtClaveEliminar").val(""+cp);
	// se comenta la siguiente linea de codigo por modificacion a solicitud del usuario
	// @date:17.04.18
	// @author:desarrollo
	// var mensaje = 'Desea eliminar el Auxiliar 3 (AUX3:'+cp+')';
	var mensaje = `Desea eliminar el Auxiliar 3: ${cp}`;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}



function fnEliminarEjecuta(){
	var cp = $('#txtClaveEliminar').val();

	$('#ModalUREliminar').modal('hide');

	//Opcion para operacion
	dataObj = {
	        option: 'eliminarUR',
	        cp: cp
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Componente_Presupuestario_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
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
