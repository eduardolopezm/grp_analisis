 /**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesùs Reyes Santos
 * @version 0.1
 */
var url = "modelo/objetoParcialModelo.php";
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
function fnMostrarDatos(id_parcial,loccode){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        id_parcial: id_parcial,
	        loccode: loccode
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
			var columnasExcel= [0, 1, 2,3,4];
			var columnasVisuales= [0, 1, 2, 3, 4,5];
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
	document.getElementById('divCalve').style.display = 'block';
	$('#txtDescripcion').val("");
	$('#selectObjetoPrincipal').multiselect('enable');
	document.getElementById('divClaveFalsa').style.display = 'none';
	


	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Objeto Parcial</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	//$("button").removeAttr('disabled');
	var id_identificacion = $('#selectObjetoPrincipal').val();
	var clave = $('#txtClave').val();
	var descripcion = $('#txtDescripcion').val();
	var estatus = $('#txtEstatus').val();
	var ingreso = $('#txtIngreso').val();
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

	if (clave == "" || descripcion == "" || estatus == "" || ingreso == "") {
		if (clave == "" ) {
			msg += '<p>Falta capturar la clave </p>';
		}
		if (descripcion=="" ) {
			msg += '<p>Falta capturar el Descripción </p>';
		}
		if (estatus == "" ) {
			msg += '<p>Falta capturar el estatus </p>';
		}
		if (ingreso=="" ) {
			msg += '<p>Falta capturar si disminuye el ingreso </p>';
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
				estatus: estatus,
				ingreso: ingreso,
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

function fnModificar(id_parcial,desc_parcial,loccode,estatus,ingreso,clave){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	
		    		//$("#selectFinalidad").empty();
	                //$("#selectFinalidad").html('<option value="'+idFinalidad+'">'+idFinalidad+' - '+descFinalidad+'</option>');
	                $("#selectObjetoPrincipal").val(""+loccode);
					$("#selectObjetoPrincipal").multiselect('rebuild');
					$('#selectObjetoPrincipal').multiselect('disable');
	                //$("#selectFinalidad").prop('disabled', 'disabled');
		    		$("#txtClave").prop("readonly", true);
					$("#txtClave").val(""+id_parcial);
					document.getElementById('divCalve').style.display = 'none';
					$("#txtClaveFalsa").prop("readonly", true);
					$("#txtClaveFalsa").val(""+clave);
					document.getElementById('divClaveFalsa').style.display = 'block';
					$("#txtDescripcion").val(""+desc_parcial);
					$("#txtEstatus").val(""+estatus);
					$("#txtEstatus").multiselect('rebuild');
					$("#txtIngreso").val(""+ingreso);
					$("#txtIngreso").multiselect('rebuild');	
				
				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Objeto Parcial </3>';
				$('#ModalUR_Titulo').empty();
			    $('#ModalUR_Titulo').append(titulo);
				$('#ModalUR').modal('show');
		
}


function fnEliminar(idFuente,descFuente,idIdentificacion){
	//$("button").removeAttr('disabled');
	$("#txtClaveEliminar").val(""+idFuente);
	$("#txtFuenteEliminar").val(""+idIdentificacion);

	var mensaje = 'Desea eliminar el Objeto Parcial '+idFuente+' - '+descFuente;
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
		muestraMensaje('No es posible eliminar el Objeto Parcial seleccionada por que tiene una o mas subfunciones asignadas' , 3, 'divMensajeOperacion', 5000);
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

	
	function fnBuscar() {
		var tipoAlmacen = "";
	var selectTipoAlmacen = document.getElementById('selectObjetoP');
    for ( var i = 0; i < selectTipoAlmacen.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            tipoAlmacen = "'"+selectTipoAlmacen.selectedOptions[i].value+"'";
        }else{
            tipoAlmacen = tipoAlmacen+", '"+selectTipoAlmacen.selectedOptions[i].value+"'";
        }
	}


//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');
	dataObj = { 
		option: 'obtenerInformacion',
		txtClave: $("#txtC").val(),
		txtEstatus: $("#txtE").val(),
		txtDescripcion: $("#txtD").val(),
		tipoAlmacen: tipoAlmacen
	  };
	  console.log('tipoAlmacen'+tipoAlmacen);
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
		var columnasExcel= [0, 1, 2,3,4];
		var columnasVisuales= [0, 1, 2, 3, 4,5];
		fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	}
})
.fail(function(result) {
	console.log("ERROR");
	console.log( result ); 
});	

}

/******************************************* stocks  ********************************************************************************/