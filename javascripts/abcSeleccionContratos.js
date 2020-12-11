 /**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesùs Reyes Santos
 * @version 0.1
 */
var url = "modelo/abcSeleccionContratosModelo.php";
var proceso = "";

$( document ).ready(function() {
	//Mostrar Catalogo
	fnMostrarDatos('','');
	fnBusquedaContribuyente();
});

//console.log("data: "+JSON.stringify(data)); 

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(){
	//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');

	//Opcion para operacion
	dataObj = { 
	        option: 'mostrarCatalogo',
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
			var columnasExcel= [0, 1, 2,3];
			var columnasVisuales= [0, 1, 2, 3, 4,5];
			fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	    }
	})
	.fail(function(result) {
		console.log("ERROR");
	    console.log( result ); 
	});
}

	function fnBuscar() {
	var unidadNegocio = "";
	var selectUnidadNegocio = document.getElementById('selectUnidadNegocioFiltro');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            unidadNegocio = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            unidadNegocio = unidadNegocio+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
	}

	var unidadEjecutora = "";
	var selectUnidadEjecutora = document.getElementById('selectUnidadEjecutoraFiltro');
    for ( var i = 0; i < selectUnidadEjecutora.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            unidadEjecutora = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            unidadEjecutora = unidadEjecutora+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
	}

	var contratos = "";
	var selectContratos = document.getElementById('selectContratosFiltro');
    for ( var i = 0; i < selectContratos.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            contratos = "'"+selectContratos.selectedOptions[i].value+"'";
        }else{
            contratos = contratos+", '"+selectContratos.selectedOptions[i].value+"'";
        }
	}
	

//console.log("fnMostrarDatos");
	//$("button").removeAttr('disabled');
	dataObj = { 
		option: 'obtenerInformacion',
		contribuyente: $("#contribuyenteIDFiltro").val(),
		txtFechaInicial: $("#txtFechaInicial").val(),
		txtFechaFinal: $("#txtFechaFinal").val(),
		contratos: contratos,
		unidadNegocio: unidadNegocio,
		unidadEjecutora: unidadEjecutora
	  };
	//   console.log('tipoAlmacen'+tipoAlmacen);
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

function fnGenerarAdeudos(id_contratos){
	 
	console.log(id_contratos);
	
	var ruta = "infoPanelContratos.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Generar Adeudos</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}

function fnPaseDeCobro(id_contratos){
	   
	var ruta = "paseCobro.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Pase de Cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}

function fnHistorial(id_contratos){
	   
	var ruta = "historialPaseCobro.php?modal=true&id_contratos="+id_contratos;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i>Historial de Pases de Cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
    
}



function fnBusquedaContribuyente() {

    dataObj = { 
            option: 'mostrarContribuyentes'
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/componentes_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
			console.log(data);
			fnBusquedaFiltroFormato(data.contenido.datos);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se Obtuvieron los Contribuyentes</p>');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnBusquedaFiltroFormato(jsonData) {
    console.log("busqueda fnBusquedaCog");
    console.log("jsonData: "+JSON.stringify(jsonData));
    $( "#contribuyente").autocomplete({
		minLength: 4,
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
			$( "#contribuyente" ).val( ui.item.value );
            $( "#contribuyenteID" ).val( ui.item.texto );
			

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>" + item.value + "</a>" )
        .appendTo( ul );

	};
	
	$( "#contribuyenteFiltro").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
			$( "#contribuyenteFiltro" ).val( ui.item.value );
            $( "#contribuyenteIDFiltro" ).val( ui.item.texto );
			

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>" + item.value + "</a>" )
        .appendTo( ul );

    };
}

/******************************************* stocks  ********************************************************************************/