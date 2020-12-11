var ramoDefault = "08";

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');

	if (document.querySelector(".selectRamo")) {
		// Inicio Tipo de Documento Adecuaciones
		//Opcion para operacion
		dataObj = {
			option: 'mostrarRamo'
		};
		//Obtener datos de las bahias
		$.ajax({
			async:false,
            cache:false,
			method: "POST",
			dataType:"json",
			url: "modelo/ABC_Actividad_Institucional_modelo.php",
			data:dataObj
		})
		.done(function( data ) {
			//console.log("Bien");
			if(data.result){
				//Si trae informacion
				dataJsonRamo = data.contenido.datos;
				$('.selectRamo').append( fnCrearDatosSelect(dataJsonRamo, '', ramoDefault, 0) );
				//fnCrearDatosSelect(dataJson, '.selectTipoDocumentoAdecuaciones');
			}else{
				//console.log("ERROR Modelo");
				//console.log( JSON.stringify(data) );
			}
		})
		.fail(function(result) {
			//console.log("ERROR");
			//console.log( result );
		});
		// Fin Tipo de Documento Adecuaciones
	}
});

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
	      url: "modelo/ABC_Actividad_Institucional_modelo.php",
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
	$('#txtCain').prop("readonly", false);

	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Actividad Institucional</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#txtCain').val("");
	$('#txtDescripcion').val("");
	//$('#txtActivo').val("");
	//$('#txtFechaEfectiva').val("");

	$('#ModalUR').modal('show');
	proceso = "Agregar";
	cain_original = "";
	ramo_original = "";


}

function fnAgregar(){

	var ramo = $('#selectRamoCr').val();
	var cain = $('#txtCain').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";
	//var activo = $('#txtActivo').val();
	//var fechaefectiva = $('#txtFechaEfectiva').val();

	/*if (ramo == "" || cain == "" || descripcion == "") { //|| activo == "" || fechaefectiva == ""
		$('#ModalUR').modal('hide');
		muestraMensaje('Faltan datos. ', 3, 'mensajesValidaciones', 5000);
		return false;
	}*/
	/*if ( (cain == "") && (descripcion == "")) {

		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar AI y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;

	}else if (cain == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar AI para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);

		return false;
	}*/
	if (cain == "" || descripcion == "") {
		if (cain == "" ) {
			msg += '<p>Falta capturar la clave AI </p>';
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
		        ramo: ramo,
		        cain: cain,
		        descripcion: descripcion,
		        //activo: activo,
		        //fechaefectiva: fechaefectiva,
		        proceso: proceso,
		        ramo_original: ramo_original,
		        cain_original: cain_original
		      };
		//Obtener datos de las bahias
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABC_Actividad_Institucional_modelo.php",
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

function fnModificar(ramo, cain){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	ramo_original = ramo;
	cain_original = cain;

	$('#txtCain').prop("readonly", true);

	//Opcion para operacion
	dataObj = {
	        option: 'mostrarCatalogo',
	        ramo: ramo,
	        cain: cain,
	        proceso: proceso
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Actividad_Institucional_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datosCatalogo;


	    	for (key in info){
	  	    	$('#selectRamoCr').selectpicker('val',''+info[0].cve_ramo);
				$('#selectRamoCr').multiselect('refresh');
				$(".selectRamo").css("display", "none");
				$('#txtCain').val(info[0].cain);
				$('#txtDescripcion').val(info[0].descripcion);
				//$('#txtActivo').val(info[0].activo);
				//$('#txtFechaEfectiva').val(info[0].fecha_efectiva);
			}

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Actividad Institucional</h3>';
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


function fnEliminar(ramo, cain){
	$("#txtClaveEliminar").val(""+ramo+","+cain);
	// se comenta la siguiente linea de codigo por modificacion a solicitud del usuario
	// @date:17.04.18
	// @author:desarrollo
	// var mensaje = 'Desea eliminar la Actividad Institucional (ramo:'+ramo+' - AI :' + cain+')';
	var mensaje = `Desea eliminar la Actividad Institucional: ${cain}`;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var ur = $('#txtClaveEliminar').val().split(',');;
	var ramo = ur[0];
	var cain = ur[1];

	$('#ModalUREliminar').modal('hide');

	//Opcion para operacion
	dataObj = {
	        option: 'eliminarUR',
	        ramo: ramo,
	        cain: cain
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Actividad_Institucional_modelo.php",
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
