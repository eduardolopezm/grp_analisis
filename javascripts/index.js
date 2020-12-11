function fnModificarFuncion(){ 
	//Mostrar datos para actualizar
	var txtNombreFuncion = $('#txt_nombre_funcion').val();
	var txtcapituloid = $('#cmb_capituloid').val();
	var txtcategoria = $('#cmb_categoria').val();
	var txtactivo = $('#cmb_activo').val();
	var txtfunctionid = $('#txt_functionid').val();
	
	//Opcion para operacion
	dataObj = { 
	        option: 'modificar_funcion',
	        txtNombreFuncion: txtNombreFuncion,
	        txtcapituloid: txtcapituloid,
	        txtcategoria: txtcategoria,
	        txtactivo: txtactivo,
	        txtfunctionid: txtfunctionid
	      };
	
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/index_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
	    if(data.result){
	    	$('#NombreFuncion_'+txtfunctionid).text(''+data.contenido);
	    	var DivModFuncion = document.getElementById("DivModFuncion");
			DivModFuncion.style.display = "none";

			var Titulo = '<h3><span class="glyphicon glyphicon-ok"></span> Exito</h3>';
			var Body = '<h5>Proceso Correcto</h5><br><button class="btn btn-primary">Prueba</button>';

			//muestraModalGeneral(2, Titulo, Body);
			//muestraMensaje('Mensaje de Prueba', 1, 'DivPrueba', 10000);

			window.open("index.php", "_self");
	    }else{
	    	//Error
	    	console.log("Actualizacion incorrecta");
	    	/*$('#ModalMensajes_Titulo').text('Seleccion de Bahia');
	    	$("#ModalMensajes_Mensaje").empty(); //Limpiar datos anteriores
	    	$('#ModalMensajes_Mensaje').text(data.ErrMsg);
	    	$('#ModalMensajes').modal('show');*/
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result );
	});
}