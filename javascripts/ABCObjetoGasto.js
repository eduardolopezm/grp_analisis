

var datosConceptos;
var proceso = "";

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
	      url: "modelo/ABCObjetoGasto_modelo.php",
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
			var columnasExcel= [0, 1, 2, 3]; //, 4
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

	


	proceso = "Agregar";
	cppt_original = "";
	ramo_original = "";

	var titulo = '<span class="glyphicon glyphicon-info-sign"></span> Agregar Partida Específica';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
	
}

function fnAgregar(){
	var capitulo = "";
	var concepto = "";
	var partidageneral = "";
	_MensajeValidacion = "";
	//var capitulo = $('#txtCapitulo').val();
	if ($("#selectCapitulos").val() != null) capitulo = $("#selectCapitulos").val()[0]; //extrae el primer caracter
	if ($("#selectConceptos").val() != null) concepto = $('#selectConceptos').val()[1]; //extrae el segundo caracter
	if ($("#selectPartidasGenericas").val() != null) partidageneral = $('#selectPartidasGenericas').val()[2]; //extrae el tercer caracter
	//if (!$('#txtPartidaEspecifica').val().startsWith($("#selectPartidasGenericas").val())) _MensajeValidacion += "Los datos de la partida especifica no coinciden con la partida genérica.";
	if ($("#txtPartidaEspecifica").val().length < 2) _MensajeValidacion += "Partida específica debe ser de 2 caracteres";
	if ($("#txtNombre").val().length == 0) _MensajeValidacion += "Falta descripción";


	var partidaespecifica = $('#txtPartidaEspecifica').val();
	var nombre = $('#txtNombre').val();
	var cap = $('#selectCapitulos').val();
	var msg = "";
	if ((cap == "" || cap == '0' || cap == 0 || cap == null || cap === 'undefined') || (partidaespecifica == "") || (nombre == "")) {
		if (cap == "" || cap == '0' || cap == 0 || cap == null || cap === 'undefined') {
			msg += '<p>Falta capturar la clave Capitulo </p>';
		}
		if (partidaespecifica == "" ) {
			msg += '<p>Falta capturar la clave Partida Específica </p>';
		}
		if (nombre=="" ) {
			msg += '<p>Falta capturar el Descripción </p>';
		}

		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		
	}else{
		$('#ModalUR').modal('hide');
		dataObj = { 
		        option: 'AgregarCatalogo',
		        capitulo: capitulo,
		        concepto: concepto,
		        partidageneral: partidageneral,
		        partidaespecifica: partidaespecifica,
		        nombre: nombre,
		        proceso: proceso
		      };
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABCObjetoGasto_modelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
			var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
		    if(data.result){
		    	muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
				fnLimpiarTabla('divTabla', 'divCatalogo');
		    	fnMostrarDatos('');
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
	      url: "modelo/ABCObjetoGasto_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	    	info=data.contenido.datosCatalogo;

	    	$('#txtCapitulo').val(info[0].capitulo);
	    	$("#selectCapitulos").selectpicker('val',[info[0].capitulo]);
	    	$("#selectCapitulos").multiselect('refresh');
	    	$(".selectCapitulos").css("display", "none");

	    	

	    	fnChangeCmbCapitulos();

			$('#txtConcepto').val(info[0].concepto);
			$("#selectConceptos").selectpicker('val',[info[0].concepto]);
	    	$("#selectConceptos").multiselect('refresh');
	    	$(".selectConceptos").css("display", "none");
	    	fnChangeCmbConceptos();

	    	
	    	$("#selectPartidasGenericas").selectpicker('val',[info[0].partida_gen]);
	    	$("#selectPartidasGenericas").multiselect('refresh');
	    	$(".selectPartidasGenericas").css("display", "none");


			$('#txtPartidaGeneral').val(info[0].partida_gen);
			$('#txtPartidaGeneral').prop("readonly", true);


			$('#txtPartidaEspecifica').val(info[0].partida_esp);
			$('#txtNombre').val(info[0].nombre);

			proceso = "Modificar";

			$('#selectCapitulos').multiselect('disable');
			$('#selectConceptos').multiselect('disable');
			$('#selectPartidasGenericas').multiselect('disable');
			$('#txtPartidaEspecifica').prop("readonly", true);
			

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span>Modificar Partida Específica</h3>';
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

function fnEliminarOG(ur){
	$("#txtClaveEliminar").val(""+ur);

	var mensaje = 'Desea eliminar la Partida Especifica ('+ur+')';
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}



function fnEliminarEjecuta(){
	var ur = $('#txtClaveEliminar').val().split(',');;
	var id_claveespecifica = ur[0];
	

	$('#ModalUREliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarUR',
	        id_claveespecifica: id_claveespecifica
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABCObjetoGasto_modelo.php",
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
