function fnAbrirReporte() {
  aDato = "";
  
  aDato = "PrintSituacionFinanciera.php?PrintPDF=1&reporte=activo_reporteetiquetas";
  
  
  
  

  

    aDato += "&anio=" + $("#selectAnio").val();

    aDato += "&entepublico=" + $("#selectUnidadNegocio>option:selected").html();
    aDato += "&tagref=" + $("#selectUnidadNegocio").val();
    aDato += "&fechainicial=" + $("#txtFechaInicial").val();
    aDato += "&fechafinal=" + $("#txtFechaFinal").val();
    aDato += "&concepto=2300";
     $("#viewReporte").html('<object data="'+ aDato+'" width="100%" height="800px" type="application/pdf">         <embed src="'+ aDato+'" type="application/pdf" />     </object>');
}

function fnCambioRazonSocial() {
	muestraCargandoGeneral();
	//console.log("fnObtenerUnidadNegocio");
	// Inicio Unidad de Negocio

	legalid = $("#selectRazonSocial").val();
	//Opcion para operacion
	dataObj = { 
	      option: 'mostrarUnidadNegocio',
	      legalid: legalid
	    };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/imprimirreportesconac_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
	  //console.log("Bien");
	  if(data.result){
	      //Si trae informacion
	      
	      dataJson = data.contenido.datos;
	      //console.log( "dataJson: " + JSON.stringify(dataJson) );
	      //alert(JSON.stringify(dataJson));
	      var contenido = "<option value='0'>Seleccionar...</option>";
	      for (var info in dataJson) {
	        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
	      }
		$('#selectUnidadNegocio').empty();
		$('#selectUnidadNegocio').append(contenido);
		$('#selectUnidadNegocio').multiselect('rebuild');
		ocultaCargandoGeneral();

	  }else{
	      // console.log("ERROR Modelo");
	      // console.log( JSON.stringify(data) ); 
	      ocultaCargandoGeneral();
	  }
	})
	.fail(function(result) {
	  // console.log("ERROR");
	  // console.log( result );
	  ocultaCargandoGeneral();
	});
	// Fin Unidad de Negocio
}




function fnCambioUnidadNegocio() {
	muestraCargandoGeneral();
	
	
	ocultaCargandoGeneral();
}




$( document ).ready(function() {


   fnCambioUnidadNegocio();
	
});