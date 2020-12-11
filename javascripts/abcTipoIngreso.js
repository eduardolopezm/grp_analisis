/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Juan Jose Ledesma
 * @version 0.1
 */
var url = "modelo/abcTipoIngresoModelo.php";
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
function fnMostrarDatos(idRubro,idTipo){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        idRubro: idRubro,
	        idTipo: idTipo
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

	// $("#selectedRubro").multiselect('rebuild');
	$("#txtClave").prop("readonly", false);
	$('#txtClave').val("");
	$('#txtDescripcion').val("");

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Tipo</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	//$("button").removeAttr('disabled');
	var claveRubro = $('#selectRubro').val();
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	
	if (clave == "" || descripcion == "") {
		if (clave == "" ) {
			msg += '<p>Falta capturar la clave TI </p>';
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
		        claveRubro: claveRubro,
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

function fnModificar(idTipo,descripcion,claveRubro,descRubro){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
		        option: 'mostrarCatalogo',
		        idTipo: idTipo,
		        descripcion: descripcion,
		        claveRubro: claveRubro,
				descRubro: descRubro
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
		    		//$("#selectedRubro").empty();
	                //$("#selectedRubro").html('<option value="'+claveRubro+'">'+claveRubro+' - '+descFinalidad+'</option>');
	                
	                $("#selectRubro option[value="+ claveRubro +"]").attr("selected",true);
	                $("#selectRubro").multiselect('rebuild');
	                $(".multiselect").attr('disabled', 'disabled');
	                //$("#selectedRubro").prop('disabled', 'disabled');
		    		$("#txtClave").prop("readonly", true);
		    		$("#txtClave").val(""+info[key].tipoIngreso);
		    		$("#txtDescripcion").val(""+info[key].descripcion);	
				}
				//$('#selectedRubro').empty();
				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Tipo</3>';
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

function fnEliminar(idTipo,descTipo,claveRubro){
	//$("button").removeAttr('disabled');
	$("#txtClaveEliminar").val(""+idTipo);
	$("#txtFinalidadEliminar").val(""+claveRubro);

	var mensaje = 'Desea eliminar la Función '+claveRubro+'.'+idTipo+' - '+descTipo;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var idTipo = $('#txtClaveEliminar').val();
	var claveRubro = $('#txtFinalidadEliminar').val();
	var descripcion = $('#txtDescripcion').val();

	$('#ModalUREliminar').modal('hide');

	if(!fnExistencia(claveRubro, idTipo)){
		muestraMensaje('No es posible eliminar la función seleccionada por que tiene una o mas subfunciones asignadas' , 3, 'divMensajeOperacion', 5000);
	}else{
		dataObj = { 
	        option: 'eliminarUR',
	        descripcion: descripcion,
	        idTipo: idTipo,
	        claveRubro: claveRubro
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

function fnExistencia(claveRubro, idFuncion){
	var existe = true;
	dataObj = { 
	    option: 'existeSubfuncion',
	    claveRubro: claveRubro,
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