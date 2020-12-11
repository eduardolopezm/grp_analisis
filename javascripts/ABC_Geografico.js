/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('');
	$('#txtCg').keyup(function(){
    this.value = this.value.replace(/[^0-9\.]/g,'');
		});

	 $("#txtCg").blur(function(){
        var addCero = $("#txtCg").val();
        if (addCero.length == 1){
            $("#txtCg").val("0"+addCero)
        }   
    });

});

var proceso = "";

//console.log("data: "+JSON.stringify(data)); 

function fnMostrarDatos(cg){
	console.log("fnMostrarDatos");

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        cg: cg
	      };
	//Obtener datos de las bahias
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Geografico_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	dataFuncionJason = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			var columnasDescartarExportar = [2, 3];

			var columnasExcel= [0, 1];
			var columnasVisuales= [0,1,2, 3,];
			nombreExcel=data.contenido.nombreExcel;
	    	fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false,false, '', columnasVisuales, nombreExcel);
			//fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
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

	$("#txtCg").prop("readonly", false);
	$('#txtCg').val("");
	$('#txtDescripcion').val("");
	


	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Entidad Federativa </h3>';
	$('#ModalCg_Titulo').empty();
    $('#ModalCg_Titulo').append(titulo);
	$('#ModalCg').modal('show');
}

function fnAgregar(){
	
	var cgnuevo = $('#txtCg').val();
	var descripcion = $('#txtDescripcion').val();
	var msg = "";
	
   //cgnuevo=('0' + cgnuevo).slice(-2);//agregar  cero
	

	/*if (cgnuevo == "" || descripcion == "" ) {
		$('#ModalCg').modal('hide');
		muestraMensaje('Faltan datos', 3, 'mensajesValidaciones', 5000);
		return false;
	}*/
	//alert(cgnuevo+descripcion+fecha);
	/*if ( (cgnuevo == "") && (descripcion == "")) {
		
		$('#ModalCg').modal('hide');
		muestraMensaje('Debe agregar EF y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
		
	}else if (cgnuevo == "") {
		$('#ModalCg').modal('hide');
		muestraMensaje('Debe agregar EF para realizar el proceso', 3, 'msjValidacion', 5000);
		return false;
	}else if (descripcion == "") {
		$('#ModalCg').modal('hide');
		muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		
		return false;
	}*/
	if (cgnuevo == "" || descripcion == "") {
		if (cgnuevo == "" ) {
			msg += '<p>Falta capturar la clave EF </p>';
		}
		if (descripcion=="" ) {
			msg += '<p>Falta capturar el Descripción </p>';
		}

		$('#divMensajeOperacion').removeClass('hide');
		$('#divMensajeOperacion').empty();
		$('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	}else{
		$('#ModalCg').modal('hide');

		dataObj = { 
		        option: 'AgregarCatalogo',
		        cgnuevo: cgnuevo,
		        descripcion: descripcion,
		        proceso: proceso
		      };
			muestraCargandoGeneral();
		$.ajax({
		      method: "POST",
		      dataType:"json",
		      url: "modelo/ABC_Geografico_modelo.php",
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

function fnModificar(cg){
	$('#divMensajeOperacion').addClass('hide');
	proceso = "Modificar";
	dataObj = { 
	        option: 'mostrarCatalogo',
	        cg: cg
	      };
	muestraCargandoGeneral();
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Geografico_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		//console.log("Bien");
	    if(data.result){
	    	//Si trae informacion
	    	info=data.contenido.datos;
	    	for (key in info){
	    		$("#txtCg").val(""+info[key].CG);
	    		$("#txtCg").prop("readonly", true);
	    		$("#txtDescripcion").val(""+info[key].Descripción);	
	    	

			var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Entidad Federativa</h3>';
			$('#ModalCg_Titulo').empty();
		    $('#ModalCg_Titulo').append(titulo);
			$('#ModalCg').modal('show');

		
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

function fnEliminar(cg){
	$("#txtCgEliminar").val(""+cg);

	var mensaje = 'Desea eliminar el Entidad Federativa <b> '+cg+'</b>';
	$('#ModalCgEliminar_Mensaje').empty();
    $('#ModalCgEliminar_Mensaje').append(mensaje);
	$('#ModalCgEliminar').modal('show');
}

function fnEliminarEjecuta(){
	var cg = $('#txtCgEliminar').val();

	$('#ModalCgEliminar').modal('hide');
	
	//Opcion para operacion
	dataObj = { 
	        option: 'eliminarcg',
	        cg: cg
	      };
	  // 
	
muestraCargandoGeneral();
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/ABC_Geografico_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		
	    if(data.result){

	    	var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Información</h3>';
			muestraMensaje(data.contenido, 1, 'OperacionMensaje', 5000);
			fnLimpiarTabla('divTabla', 'divCatalogo');
	    	fnMostrarDatos('');
	    	ocultaCargandoGeneral();
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	    ocultaCargandoGeneral();
	});
}