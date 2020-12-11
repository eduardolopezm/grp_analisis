

var datosConceptos;
var proceso = "";
var idSelect = 0;
var idClaveGen = 0;

//console.log("data: "+JSON.stringify(data)); 


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
	      url: "modelo/abcRubroIngresoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	//info=data.contenido.datosCatalogo;
	    	
	    	//fnAgregarGridv2(info, 'divCatalogo', "busqueda");
	    	dataReasignacionJason = data.contenido.datosCatalogo;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [0, 1]; //, 4
			var columnasVisuales= [0, 1, 2, 3, 4, 5]; //, 6
	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);
	    	
	    	
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});


}

function fnChangeCmbCapitulos(){
	var valor = $('#selectCapitulos').val();
	var contenido = "";
	$('#selectConceptos').empty();
	for (x in dataConceptos) { 	
		//$('option[value="' +dataConceptos[x][0] + '"]', $('#selectConceptos')).remove(); 
		if ((dataConceptos[x][0][0] == valor)) {
			contenido += '<option value="'+dataConceptos[x][0]+'">' + dataConceptos[x][1] +'</option>';
 		}
 	}
 	$('#selectConceptos').append(contenido);
 	$('#selectConceptos').multiselect('rebuild');
	fnChangeCmbConceptos();
}

function fnChangeCmbConceptos(){
	var valor = $('#selectConceptos').val();
	var contenido = "";

	$('#selectPartidasGenericas').empty();
	for (x in dataPartidaGenerica) { 	
		//si la primera y segunda letra es igual al valor del maestro
		if ((dataPartidaGenerica[x][0][0]+dataPartidaGenerica[x][0][1] == valor)) {
			contenido += '<option value="'+dataPartidaGenerica[x][0]+'">' + dataPartidaGenerica[x][1] +'</option>';
 		}
 	}
 	$('#selectPartidasGenericas').append(contenido);
 	$('#selectPartidasGenericas').multiselect('rebuild');
} 



function fnAgregarCatalogoModal(){
	$('#divMensajeOperacion').addClass('hide');

	fnChangeCmbCapitulos();

	//inicializa en vacio todos los datos de la pantalla
	$('#selectCapitulos').val(null);
	$('#selectCapitulos').multiselect('rebuild');
	$('#selectConceptos').val(null);
	$('#selectConceptos').multiselect('rebuild');
	$('#selectPartidasGenericas').val(null);
	$('#selectPartidasGenericas').multiselect('rebuild');
	$('#txtPartidaEspecifica').val("");
	$('#txtNombre').val("");

	$('#selectCapitulos').multiselect('enable');
	$('#selectConceptos').multiselect('enable');
	$('#selectPartidasGenericas').multiselect('enable');
	$('#txtPartidaEspecifica').prop("readonly", false);

	$('#clave').prop('disabled', false);
	$('#clave').val('');
	$('#description').val('');

	proceso = "Agregar";
	cppt_original = "";
	ramo_original = "";

	var titulo = '<span class="glyphicon glyphicon-info-sign"></span> Agregar Rubro Ingreso';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
	
}

function fnAgregar(){
	
	_MensajeValidacion = "";
	if ($("#clave").val().length < 1) _MensajeValidacion += "Falta capturar la clave";
	if ($("#description").val().length == 0) _MensajeValidacion += "Falta descripción";


    var clave = $('#clave').val();
    var description = $('#description').val();
	var msg = "";
	if ((clave == "" || clave == null || clave === 'undefined') || (description == "") || (description == null)) {
		if (clave == "" || clave == '0' || clave == 0 || clave == null || clave === 'undefined') {
			msg += '<p>Falta capturar la clave </p>';
		}
		if (description=="" ) {
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
                description: description,
		        proceso: proceso
		      };
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/abcRubroIngresoModelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
			var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
		    if(data.result){
		    	muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
				fnLimpiarTabla('divTabla', 'divCatalogo');
                fnMostrarDatos('');
                clave.val('');
                description.val('');
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		});
	}
	
}

function fnModificarOG(ur){
	$('#divMensajeOperacion').addClass('hide');
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ur: ur,
	        proceso: proceso
	      };
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/abcRubroIngresoModelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	    	info=data.contenido.datosCatalogo;

	    	$('#clave').val(info[0].clave);
	    	$('#clave').prop('disabled', true);
	    	$('#description').val(info[0].descripcion);
			proceso = "Modificar";

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Modificar Rubro</h3>';
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

function fnEliminarOG(ur, clave){
	$("#txtClaveEliminar").val(""+ur);
    idSelect = ur;
    idClaveGen = clave;
	var mensaje = 'Desea eliminar el Rubro ('+clave+')';
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}



function fnEliminarEjecuta(){
	var id = idSelect;
	

	$('#ModalUREliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarUR',
	        id: id,
	        clave: idClaveGen
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/abcRubroIngresoModelo.php",
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

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$('#selectCapitulos').change(fnChangeCmbCapitulos);
	$('#selectConceptos').change(fnChangeCmbConceptos);
	
});
