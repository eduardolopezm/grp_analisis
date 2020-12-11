$( document ).ready(function() {
	//Mostrar Catalogo
	fnCargarDatos();
});

var proceso = "";

//console.log("data: "+JSON.stringify(data)); 

function fnCargarDatos()
{
	console.log("fnCargarDatos");

	//Opcion para operacion
	dataObj = { 
	        opcion: 'cargarDatosDesdeBD',
	        
	      };
	//
	$.ajax({
	      method: "POST",
	      dataType:"json",
	      url: "modelo/GeneralAccountsPayableAuthProcV2_modelo.php",
	      data:dataObj
	  })
	.done(function( data ) {
		
	    if(data.result)
	    {
	    	//Si trae informacion
	    info=data.contenido.datosCatalogo;
	    		//console.log(info);
	    		$.each(info, function(key, value) {
      			alert(key+ value)
				});
		
        }
	    
	})
	.fail(function(result) {
		console.log("Errror al cargar datos");
	    console.log( result );
	});
}

