/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');

	$('#txtCtga').keyup(function(){
    this.value = this.value.replace(/[^0-9\.]/g,'');
		}); 
});

var proceso = "";

function fnMostrarDatos(ctga){
	console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ctga: ctga
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Tipo_de_gasto_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	//info=data.contenido.datosCatalogo;
	    	
	    	//fnAgregarGridv2(info, 'divCatalogo','b');
	    	dataFuncionJason = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			var columnasDescartarExportar = [2, 3];
			var columnasExcel= [0, 1];
			var columnasVisuales= [0,1,2, 3,];
			nombreExcel=data.contenido.nombreExcel;

	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false,false, '', columnasVisuales, nombreExcel);
	    	//fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasDescartarExportar, false);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}

function fnAgregarCatalogoModal(){
	$('#divMensajeOperacion').addClass('hide');

	proceso = "Agregar";

	$("#txtCtga").prop("readonly", false);
	$('#txtCtga').val("");
	$('#txtDescripcion').val("");



	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Tipo de Gasto</h3>';
	$('#ModalCtga_Titulo').empty();
    $('#ModalCtga_Titulo').append(titulo);
	$('#ModalCtga').modal('show');
}

function fnAgregar(){
	
	var ctganuevo = $('#txtCtga').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";

	/*if (ctganuevo == "" || descripcion == "" ) {
		$('#ModalCtga').modal('hide');
		muestraMensaje('Faltan datos', 3, 'mensajesValidaciones', 5000);
		return false;
	}*/
	//alert(ctganuevo+descripcion+fecha);
	/*if ( (ctganuevo == "") && (descripcion == "")) {
		
		$('#ModalCtga').modal('hide');
		muestraMensaje('Debe agregar TG y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
		
	}else if (ctganuevo == "") {
		$('#ModalCtga').modal('hide');
		muestraMensaje('Debe agregar TG para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalCtga').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		
		return false;
	}*/
	if (ctganuevo == "" || descripcion == "") {
		if (ctganuevo == "" ) {
			msg += '<p>Falta capturar la clave TG </p>';
		}
		if (descripcion=="" ) {
			msg += '<p>Falta capturar el Descripción </p>';
		}

		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		
	}else{
		$('#ModalCtga').modal('hide');

		dataObj = { 
		        option: 'AgregarCatalogo',
		        ctganuevo: ctganuevo,
		        descripcion: descripcion,
		   
		        proceso: proceso
		      };
		muestraCargandoGeneral();
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABC_Tipo_de_gasto_modelo.php",
		      data:dataObj
		  })
		.done(function( data ) {
			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
		    if(data.result){
		    	muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
					var datos=data.contenido;
		   			if(datos.includes("Ya existe")){
		    	
		    		}else{
		    		fnLimpiarTabla('divTabla', 'divCatalogo');	
		    		fnMostrarDatos('');
		    		}

		    	ocultaCargandoGeneral();
		    }else{
		    	ocultaCargandoGeneral();
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		    ocultaCargandoGeneral();
		});
	}
}

function fnModificar(ctga){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
	        option: 'mostrarCatalogo',
	        ctga: ctga,
	        proceso:proceso
	      };
	muestraCargandoGeneral();
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Tipo_de_gasto_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	for (key in info){
	    		$("#txtCtga").val(""+info[key].TG);
	    		$("#txtCtga").prop("readonly", true);
	    		$("#txtDescripcion").val(""+info[key].Descripción);	
	    		

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Tipo de Gasto</h3>';
			$('#ModalCtga_Titulo').empty();
		    $('#ModalCtga_Titulo').append(titulo);
			$('#ModalCtga').modal('show');
	    		}
	    		//muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
	    		ocultaCargandoGeneral();
	    }else{
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});
}

function fnEliminar(ctga){
	$("#txtCtgaEliminar").val(""+ctga);

	var mensaje = 'Desea eliminar el Tipo de Gasto <b> '+ctga+'</b>';
	$('#ModalCtgaEliminar_Mensaje').empty();
    $('#ModalCtgaEliminar_Mensaje').append(mensaje);
	$('#ModalCtgaEliminar').modal('show');
}

function fnEliminarEjecuta(){
	var ctga = $('#txtCtgaEliminar').val();

	$('#ModalCtgaEliminar').modal('hide');
	dataObj = { 
	        option: 'eliminarctga',
	        ctga: ctga
	      };
	  // 
	muestraCargandoGeneral();
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Tipo_de_gasto_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	   
	    	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
			muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
			fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    	ocultaCargandoGeneral();
	    }else{
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});
}