//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;
window.renglon = 1;
window.municipios = [];
window.home = window.url + '/configuracionFirmas.php';
window.municipioEntidad = [];
window.clavesGeneral = [];
window.clavesCombustibles = [];
window.peajeTransporte = [];
window.cantPernoctaOld = 0;
window.datosPaises = [];
window.tipoDivisa = 'MX';

$( window ).load(function() {
   
    fnObtenerInformacion(); 
    $("#btnBusqueda").click(function() {
    	fnObtenerInformacion();
    })

    setTimeout(function(){$(".multiselect-container").width("300px");}, 1500);

});


/******************
 *    Metodos     *
* ***************/

/**
 * Obtener datos para llenar los select
 * @param  string prefix prefijo de la url
 * @return jquery
 */

function fnObtenerInformacion() {
	var ur = "";
	var selectUr = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUr.selectedOptions.length; i++) {
        if (i == 0) {
            ur = "'"+selectUr.selectedOptions[i].value+"'";
        }else{
            ur = ur+", '"+selectUr.selectedOptions[i].value+"'";
        }
    }

	var ue = "";
	var selectUe = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUe.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUe.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUe.selectedOptions[i].value+"'";
        }
    }
    console.log("ue",ue)
    var reportes = "";
	var selectReportes = document.getElementById('selectReportes');
    for ( var i = 0; i < selectReportes.selectedOptions.length; i++) {
        if (i == 0) {
            reportes = "'"+selectReportes.selectedOptions[i].value+"'";
        }else{
            reportes = reportes+", '"+selectReportes.selectedOptions[i].value+"'";
        }
    }

    console.log("ejecutar"+selectUr+" "+selectUe+" "+selectReportes); 
    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
          ur: ur,
	      ue: ue,
	      reportes: reportes
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/configuracionFirmasModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			ocultaCargandoGeneral();
			dataJson = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			dataJsonNoCaptura = data.contenido.datos;
			
			console.log( "dataJson: " + JSON.stringify(columnasNombresGrid) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [ 0, 1, 2];
			var columnasVisuales= [ 0, 1, 2, 3,4];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
			// fnEjecutarVueGeneral();
			//$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
		}else{
			ocultaCargandoGeneral();
			muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function eliminar (idClave){
    if (confirm("Esta seguro que desea eliminar")) {
        dataObj = { 
            option: 'eliminarInformacion',
            idClave: idClave,  
        };
      
      $.ajax({
          method: "POST",
          dataType:"json",
          url: "modelo/ClasificacionProgramaticaModelo.php",
          data:dataObj
      })
      .done(function( data ) {
          //console.log("Bien");
          if(data.result){
             //Si trae informacion
              var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
              muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se elmino el registro </p>');
              location.reload();
            }else{
              ocultaCargandoGeneral();
              muestraMensaje('No se elimino el registro ya que esta relacionado con un Programa Presupuestario', 3, 'divMensajeOperacion', 5000); 
          }
      })
      .fail(function(result) {
        ocultaCargandoGeneral();
        // console.log("ERROR");
        // console.log( result );
      });
    }
}

