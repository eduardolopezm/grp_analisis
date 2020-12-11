 /**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesùs Reyes Santos
 * @version 0.1
 */
var url = "modelo/abcAdjuntosModelo.php";
var proceso = "";



$( document ).ready(function() {
    //Mostrar Catalogo
    var id_adjunto=document.getElementById("id_adjunto").value;
    fnMostrarDatos(id_adjunto);
    $("#id_adjunto").val(""+id_adjunto);
    $("#id_adjunto2").val(""+id_adjunto);
    $("#id_adjunto3").val(""+id_adjunto);
    
    var id_adjuntoTabla=document.getElementById("id_adjuntoTabla").value;
    if (id_adjuntoTabla != ""){
        fnMostrarDatos(id_adjuntoTabla);
        $("#id_adjunto2").val(""+id_adjuntoTabla);
        $("#id_adjunto3").val(""+id_adjuntoTabla);
    }else{
        console.log(id_adjuntoTabla);
    }
});



    
//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(id_adjunto){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
	        id_adjunto: id_adjunto
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



function fnEliminar(id,urli,namei,idTabla){
	//$("button").removeAttr('disabled');
	$("#idEliminar").val(""+id);
    $("#ruta").val(""+urli);
    $("#idTabla").val(""+idTabla);

	var mensaje = 'Desea eliminar el archivo '+namei;
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){
	var idEliminar = $('#idEliminar').val();
    var ruta = $('#ruta').val();
    var idTabla = $('#idTabla').val();


	$('#ModalUREliminar').modal('hide');
		dataObj = { 
	        option: 'eliminarUR',
	        idEliminar: idEliminar,
	        ruta: ruta
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
		    	fnMostrarDatos(idTabla);
		    }else{
		    	muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		});
	
}



/******************************************* stocks  ********************************************************************************/