//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;
window.renglon = 1;
window.municipios = [];
window.home = window.url + '/viaticos.php';
window.municipioEntidad = [];
window.clavesGeneral = [];
window.clavesCombustibles = [];
window.peajeTransporte = [];
window.cantPernoctaOld = 0;
window.datosPaises = [];
window.tipoDivisa = 'MX';

$( window ).load(function() {
   
    //obtener datos para los selects
    if (document.querySelector(".clave")) {
		dataObj = { 
            option: 'mostrarClaves'
          };
          fnSelectGeneralDatosAjax('.clave', dataObj, 'modelo/ClasificacionProgramaticaModelo.php', 0);
    }
    if (document.querySelector(".programa")) {
		dataObj = { 
            option: 'mostrarPrograma'
          };
          fnSelectGeneralDatosAjax('.programa', dataObj, 'modelo/ClasificacionProgramaticaModelo.php', 0);
    }
    if (document.querySelector(".grupo")) {
		dataObj = { 
            option: 'mostrarGrupo'
          };
          fnSelectGeneralDatosAjax('.grupo', dataObj, 'modelo/ClasificacionProgramaticaModelo.php', 0);
    }
    
    fnObtenerInformacion();
    $("#btnBusqueda").click(function() {
    	fnObtenerInformacion();
    })

});

/******************
 *    Metodos     *
* ***************/

/**
 * Obtener datos para llenar los select
 * @param  string prefix prefijo de la url
 * @return jquery
 */
function getSelects(prefix) {
    muestraCargandoGeneral();
    params = {
        method: 'getSelects'
    }
    console.log(prefix + '/modelo/ABC_Jerarquias_Modelo.php')
    $.ajax({
        method: "POST",
       // dataType: "JSON",
        url: prefix + '/modelo/ABC_Jerarquias_Modelo.php',
        data: params
    }).done(function(res) {
        console.log("variable res de los getSelects: "+res)
        if (res.success) {
          console.log(res.content); 
         // fnFormatoSelectGeneral('#jerarquia'); 
          fnFormatoSelectGeneral('#tipoGasto');   
          fnFormatoSelectGeneral('#tipoSol');   
         // fnCrearSelectCero(res.content.jerarquias, '#jerarquia', 'Se', 1); 
          fnCrearDatosSelect(res.content.zonas, '#tipoGasto', 0, 1);  
          fnCrearDatosSelect(res.content.tipoSol, '#tipoSol', 0, 1);      
        } else {
            muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
        }
        ocultaCargandoGeneral();
    }).fail( function( jqXHR, textStatus, errorThrown ) {
      console.log(res)
      muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
      ocultaCargandoGeneral();
      });
}


function fnCrearSelectCero(dataJson, elementoClase = "", valor = "", valorInicial = 1) {
  var contenido = "";
  if (valorInicial == 1) {
      var contenido = "<option value='Se'>Seleccionar...</option>";
  }
  for (var info in dataJson) {
      var selected = "";
      if (dataJson[info].value == valor) {
          selected = "selected";
      }
      contenido += "<option value='" + dataJson[info].value + "' " + selected + ">" + dataJson[info].texto + "</option>";
  }
  if (elementoClase == "") {
      return contenido;
  } else {
      // Si trae nombre para los datos
      $(elementoClase).empty();
      $(elementoClase).append(contenido);
      $(elementoClase).multiselect('rebuild');
  }
}


function fnObtenerInformacion() {
	var clave = "";
	var selectClave = document.getElementById('clave');
    for ( var i = 0; i < selectClave.selectedOptions.length; i++) {
        if (i == 0) {
            clave = "'"+selectClave.selectedOptions[i].value+"'";
        }else{
            clave = clave+", '"+selectClave.selectedOptions[i].value+"'";
        }
    }

	var grupo = "";
	var selectGrupo = document.getElementById('grupo');
    for ( var i = 0; i < selectGrupo.selectedOptions.length; i++) {
        if (i == 0) {
            grupo = "'"+selectGrupo.selectedOptions[i].value+"'";
        }else{
            grupo = grupo+", '"+selectGrupo.selectedOptions[i].value+"'";
        }
    }

    var programa = "";
	 var selectPrograma = document.getElementById('programa');
    for ( var i = 0; i < selectPrograma.selectedOptions.length; i++) {
        if (i == 0) {
            programa = "'"+selectPrograma.selectedOptions[i].value+"'";
        }else{
            programa = programa+", '"+selectPrograma.selectedOptions[i].value+"'";
        }
    }

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
          clave: clave,
	      grupo: grupo,
	      programa: programa
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
			ocultaCargandoGeneral();
			dataJson = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			dataJsonNoCaptura = data.contenido.datos;
			
			// console.log( "dataJson: " + JSON.stringify(columnasNombresGrid) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [ 0, 1, 2];
			var columnasVisuales= [ 0, 1, 2, 3, 4, 5];
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

function fnTImeEliminar(idClave,descripcion){
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
          muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se eliminó el registro '+ descripcion +' del Catálogo Clasificación Programática con éxito </p>');
          fnObtenerInformacion();
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

function fnEliminarClave(idClave,descripcion){
    muestraCargandoGeneral();
    setTimeout(function(){fnTImeEliminar(idClave,descripcion);}, 2000);
    
}
function eliminar (idClave,descripcion){
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneralConfirmacion(3, titulo, 'Desea eliminar la Clasificación Programática '+descripcion, '', 'fnEliminarClave(\''+idClave+'\',\''+descripcion+'\')');
}
