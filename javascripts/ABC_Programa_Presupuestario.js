var ramoDefault = "08";


$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$("#txtCPPT").keyup(function(){
	    var cadena = $("#txtCPPT").val();
	    var primerLetra = cadena.substr(0, 1);
	    if(primerLetra  >= 0 && primerLetra <= 9){
	    	primerLetra = parseInt(primerLetra);
	    }
	    if(jQuery.type( primerLetra ) === "number"){
	    	console.log(primerLetra);
	    	$("#txtCPPT").val("");
	    }else{
	    	cadena = cadena.toUpperCase();
			$("#txtCPPT").val(""+cadena);
	    }
	});

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
			url: "modelo/ABC_Programa_Presupuestario_modelo.php",
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
	      url: "modelo/ABC_Programa_Presupuestario_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	//info=data.contenido.datosCatalogo;

	    	//fnAgregarGridv2(info, 'divCatalogo', "filtrado");
	    	//
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

	$('#txtCPPT').prop("readonly", false);


	$('#txtCPPT').val("");
	$('#txtDescripcion').val("");
	$('#txtActivo').val("");
	$('#txtFechaEfectiva').val("");




	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Programa Presupuestario</h3>';
	$('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
	$('#ModalUR').modal('show');
	proceso = "Agregar";
	cppt_original = "";
	ramo_original = "";
}

function fnAgregar(){
	var ramo = $('#selectRamoCr').val();
	var cppt = $('#txtCPPT').val();
	var descripcion = $('#txtDescripcion').val();
	var activo = $('#txtActivo').val();
	var fechaefectiva = $('#txtFechaEfectiva').val();
	cppt = fnCapitalLetter(cppt);
	var msg = "";
	/*if (ramo == "" || cppt == "" || descripcion == "" || activo == "" || fechaefectiva == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Faltan datos. ', 3, 'mensajesValidaciones', 5000);
		return false;
	}*/
	/*if ( (cppt == "") && (descripcion == "")) {

		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar PP y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;

	}else if (cppt == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar PP para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalUR').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);

		return false;
	}*/
	if (cppt == "" || descripcion == "") {
			if (cppt == "" ) {
				msg += '<p>Falta capturar la clave PP </p>';
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
			        ramo: ramo,
			        cppt: cppt,
			        descripcion: descripcion,
			        activo: activo,
			        proceso: proceso,
			        fechaefectiva: fechaefectiva,
			        ramo_original: ramo_original,
			        cppt_original: cppt_original
			      };
			//Obtener datos de las bahias
			$.ajax({
			      method: "POST",
			      dataType:"json",
			      url: "modelo/ABC_Programa_Presupuestario_modelo.php",
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


function fnModificar(ramo, cppt){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	ramo_original = ramo;
	cppt_original = cppt;
	$('#txtCPPT').prop("readonly", true);

	//Opcion para operacion
	dataObj = {
	        option: 'mostrarCatalogo',
	        ramo: ramo,
	        cppt: cppt,
	        proceso: proceso
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Programa_Presupuestario_modelo.php",
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

				$('#txtCPPT').val(info[0].cppt);
				$('#txtDescripcion').val(info[0].descripcion);
				$('#txtActivo').val(info[0].activo);
				$('#txtFechaEfectiva').val(info[0].fecha_efectiva);
			}

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Programa Presupuestario</h3>';
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

function fnEliminar(ramo, cppt){
	$("#txtClaveEliminar").val(""+ramo+","+cppt);
	// se comenta la siguiente linea de codigo por modificacion a solicitud del usuario
	// @date:17.04.18
	// @author:desarrollo
	// var mensaje = 'Desea eliminar el Programa Presupuestario (ramo:'+ramo+' - PP :' + cppt+')';
	var mensaje = `Desea eliminar el Programa Presupuestario: ${cppt}`;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var ur = $('#txtClaveEliminar').val().split(',');;
	var ramo = ur[0];
	var cppt = ur[1];

	$('#ModalUREliminar').modal('hide');

	//Opcion para operacion
	dataObj = {
	        option: 'eliminarUR',
	        ramo: ramo,
	        cppt: cppt
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Programa_Presupuestario_modelo.php",
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

function fnCapitalLetter(string){
  return string.charAt(0).toUpperCase() + string.slice(1);
}
