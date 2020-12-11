/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Eduardo López Morales
 * @version 0.1
 */

$( document ).ready(function() {
	// Datos de los movimientos
	var opcionGeneral = "";
	if (opcionGeneral == 'generarLayout') {
		fnGenerarArchivoLayout(funcionGeneral, typeGeneral, transnoGeneral, '1');
	}
});

function fnGenerarArchivoLayout(funcion, type, transno, tipoLayout=1,nombreLayout='',nombreLayoutBD1='',guardar=0) {
	//console.log("fnGenerarArchivoLayout");
	//muestraCargandoGeneral();
    //Opcion para operacion
	dataObj = { 
	      option: 'generarLayout',
	      funcion: funcion,
	      type: type,
	      transno: transno,
	      tipoLayout: tipoLayout,
	      nombreLayout:nombreLayout,
	      nombreLayoutBD:nombreLayoutBD1,
	      guardar:guardar
	    };
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/layout_general_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//ocultaCargandoGeneral();
			//Si trae informacion
			link = data.contenido;
			var titulo = '<h3><p><i class="glyphicon glyphicon-list-alt text-success" aria-hidden="true"></i> Descargar</p></h3>';
			muestraModalGeneral(4, titulo, link);
		}else{
			//ocultaCargandoGeneral();
			link = data.contenido;
			var titulo = '<h3><p><i class="glyphicon glyphicon-list-alt text-success" aria-hidden="true"></i> Descargar</p></h3>';
			muestraModalGeneral(4, titulo, link);
			//muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000); 
		}
	})
	.fail(function(result) {
	  //ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}
function fnGenerarArchivoLayoutSinModal(funcion, type, transno, tipoLayout=1,nombreLayout='',nombreLayoutBD1='') {
	var link;
	dataObj = { 
	      option: 'generarLayout',
	      funcion: funcion,
	      type: type,
	      transno: transno,
	      tipoLayout: tipoLayout,
	      nombreLayout:nombreLayout,
	      nombreLayoutBD:nombreLayoutBD1
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    async:false,
	    url: "modelo/layout_general_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		
		if(data.result){
			link = data.contenido;
		}else{
			
			link = data.contenido; 
		}
	})
	.fail(function(result) {
	  //ocultaCargandoGeneral();
	   console.log("ERROR");
	  // console.log( result );
	});
	return link;
}