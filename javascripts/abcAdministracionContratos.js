 /**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesùs Reyes Santos
 * @version 0.1
 */
var url = "modelo/abcAdministracionContratosModelo.php";
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
function fnMostrarDatos(id_contratos,id_loccode){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        id_contratos: id_contratos,
	        id_loccode: id_loccode
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
			var columnasExcel= [0, 1, 2,3,4,5];
			var columnasVisuales= [0, 1, 2, 3, 4,5,6,7,8];
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
	$('#selectObjetoPrincipal').val("");
	$('#selectObjetoPrincipal').multiselect('enable');
	$('#selectEstatus').val("");
	$("#selectEstatus").multiselect('rebuild');
	$('#selectRecargos').val("");
	$("#selectRecargos").multiselect('rebuild');
	$('#selectMulta').val("");
	$("#selectMulta").multiselect('rebuild');
	$('#txtDescripcion').val("");
	$('#selectReporte').val("");
	$('#selectReporte').multiselect('enable');
	

	//$("#selectFuenteRecurso").multiselect('rebuild');
	// $("#txtClave").prop("readonly", false);
	// $('#txtClave').val("");
	// document.getElementById('divCalve').style.display = 'block';
	// $('#txtDescripcion').val("");
	// $('#selectObjetoPrincipal').multiselect('enable');
	// document.getElementById('divClaveFalsa').style.display = 'none';
	


	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Contrato de Contribuyente</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar(){
	//$("button").removeAttr('disabled');
	var id_contratos = $('#txtIdContratos').val();
	var id_loccode = $('#selectObjetoPrincipal').val();
	var nu_estatus = $('#selectEstatus').val();
	var nu_recargos = $('#selectRecargos').val();
	var nu_multa = $('#selectMulta').val();
	var descripcion = $('#txtDescripcion').val();
	var reporte = $('#selectReporte').val();
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
	

	if (id_loccode == "" || id_loccode == null || id_loccode == 0 || id_loccode == "0" || id_loccode === "undefined" ||  nu_estatus == "" || nu_estatus == null  || nu_estatus === "undefined" ||  nu_recargos == "" || nu_recargos == null  || nu_recargos === "undefined" || nu_multa == "" || nu_multa == null  || nu_multa === "undefined" || descripcion=="" || descripcion == null  || descripcion === "undefined") {
		if (id_loccode == "" || id_loccode == null || id_loccode == 0 || id_loccode == "0" || id_loccode === "undefined") {
			msg += '<p>Falta capturar el objeto principal </p>';
		}
		if (nu_estatus == "" || nu_estatus == null  || nu_estatus === "undefined") {
			msg += '<p>Falta capturar el estatus </p>';
		}
		if (nu_recargos == "" || nu_recargos == null  || nu_recargos === "undefined") {
			msg += '<p>Falta capturar si aplica recargos </p>';
		}
		if (nu_multa=="" || nu_multa == null  || nu_multa === "undefined" ) {
			msg += '<p>Falta capturar si aplica multa </p>';
		}
		if (descripcion=="" || descripcion == null  || descripcion === "undefined" ) {
			msg += '<p>Falta capturar la descripción </p>';
		}

		

		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		
		

	}else{
		$('#ModalUR').modal('hide');
		dataObj = { 
				option: 'AgregarCatalogo',
				id_contratos: id_contratos,
		        id_loccode: id_loccode,
				nu_estatus: nu_estatus,
				nu_recargos: nu_recargos,
				nu_multa: nu_multa,
				descripcion: descripcion,
				reporte: reporte,
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




function fnModificar(id_contratos,id_loccode,nu_estatus,nu_recargos,nu_multa,descripcion,reporte){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
		        option: 'mostrarCatalogo',
		        id_contratos: id_contratos,
		        id_loccode: id_loccode,
		        nu_estatus: nu_estatus,
				nu_recargos: nu_recargos,
				descripcion: descripcion,
				reporte: reporte,
				nu_multa: nu_multa
				
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
	                $("#selectObjetoPrincipal").val(""+id_loccode);
					$("#selectObjetoPrincipal").multiselect('rebuild');
					$('#selectObjetoPrincipal').multiselect('disable');
	                //$("#selectFinalidad").prop('disabled', 'disabled');
					$("#selectEstatus").val(""+nu_estatus);
					$("#selectEstatus").multiselect('rebuild');
					$("#selectRecargos").val(""+nu_recargos);
					$("#selectRecargos").multiselect('rebuild');
					$("#selectMulta").val(""+nu_multa);
					$("#selectMulta").multiselect('rebuild');
					$("#txtIdContratos").val(""+id_contratos);
					$("#txtDescripcion").val(""+descripcion);
					$("#selectReporte").val(""+reporte);
					$("#selectReporte").multiselect('rebuild');
						
				}
				//$('#selectFinalidad').empty();
				var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Contrato de Contribuyente </3>';
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
		id_contratos: $("#txtFolio").val(),
		nu_estatus: $("#selectE").val(),
		nu_recargos: $("#selectR").val(),
		nu_multa: $("#selectM").val(),
		id_loccode: tipoAlmacen
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
		var columnasExcel= [0, 1, 2,3,4,5];
		var columnasVisuales= [0, 1, 2, 3, 4,5,6,7,8];
		fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	}
})
.fail(function(result) {
	console.log("ERROR");
	console.log( result ); 
});	

}

function fnObjetos(id_contratos,id_loccode){
	 
	
	var ruta = "abcObjetosContrato.php?modal=true&id_contratos="+id_contratos+"&id_loccode="+id_loccode;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Objetos Contrato</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}

function fnAtributos(id_contratos,locationname){
	   
	
	
	var ruta = "abcAtributosContrato.php?modal=true&id_contratos="+id_contratos+"&id_loccode="+locationname;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Atributos Contrato</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}



/******************************************* stocks  ********************************************************************************/