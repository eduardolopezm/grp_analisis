//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;

$( window ).load(function() {
   
    //obtener datos para los selects
    if (document.querySelector(".banco")) {
		dataObj = { 
            option: 'mostrarBanco'
          };
          fnSelectGeneralDatosAjax('.banco', dataObj, 'modelo/ABCBanks_Modelo.php', 0);
    }
    if (document.querySelector(".descripcion")) {
		dataObj = { 
            option: 'mostrarDescripcion'
          };
          fnSelectGeneralDatosAjax('.descripcion', dataObj, 'modelo/ABCBanks_Modelo.php', 0);
    }
    if (document.querySelector(".clave")) {
		dataObj = { 
            option: 'mostrarClave'
          };
          fnSelectGeneralDatosAjax('.clave', dataObj, 'modelo/ABCBanks_Modelo.php', 0);
    }
    fnObtenerInformacion();
    $("#btnBusqueda").click(function() {
    	fnObtenerInformacion();
    })

});

function fnObtenerInformacion() {
	var banco       = "";
	var selectBanco = document.getElementById('banco');
    for ( var i = 0; i < selectBanco.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            banco = "'"+selectBanco.selectedOptions[i].value+"'";
        }else{
            banco = banco+", '"+selectBanco.selectedOptions[i].value+"'";
        }
    }

	var descripcion       = "";
	var selectDescripcion = document.getElementById('descripcion');
    for ( var i = 0; i < selectDescripcion.selectedOptions.length; i++) {
        if (i == 0) {
            descripcion = "'"+selectDescripcion.selectedOptions[i].value+"'";
        }else{
            descripcion = descripcion+", '"+selectDescripcion.selectedOptions[i].value+"'";
        }
    }

    var clave       = "";
	var selectClave = document.getElementById('clave');
    for ( var i = 0; i < selectClave.selectedOptions.length; i++) {
        if (i == 0) {
            clave = "'"+selectClave.selectedOptions[i].value+"'";
        }else{
            clave = clave+", '"+selectClave.selectedOptions[i].value+"'";
        }
    }

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
	      banco: banco,
	      descripcion: descripcion,
	      clave: clave,
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/ABCBanks_Modelo.php",
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
			var columnasVisuales= [ 0, 1, 2, 3,4,5,6];
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

function eliminar (idBanco,descripcion){
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar el banco '+descripcion+'?', '', 'fnEliminarBanco(\''+idBanco+'\',\''+descripcion+'\')');
}

function fnTImeEliminar(idBanco,descripcion){
    console.log("si llego aca "); 
    dataObj = { 
        option: 'eliminarInformacion',
        idBanco: idBanco,  
        descripcion: descripcion,
    };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/ABCBanks_Modelo.php",
      data:dataObj
  })
  .done(function( data ) {
      if(data.result){
          //Si trae informacion
          ocultaCargandoGeneral();
          var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
          muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el banco '+descripcion+' con éxito </p>');
          fnObtenerInformacion();
        }else{
          
          muestraMensaje('No es posible eliminar la jerarquía '+descripcion+' ya que se encuentra vinculada a otro registro', 3, 'divMensajeOperacion', 5000); 
      }
  })
  .fail(function(result) {
    ocultaCargandoGeneral();
  });

}
function fnEliminarBanco(idBanco,descripcion){
    muestraCargandoGeneral();
    setTimeout(function(){fnTImeEliminar(idBanco,descripcion);}, 1000);
    
}
