//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;
window.renglon = 1;
window.municipios = [];
window.municipioEntidad = [];
window.clavesGeneral = [];
window.clavesCombustibles = [];
window.peajeTransporte = [];
window.cantPernoctaOld = 0;
window.datosPaises = [];
window.tipoDivisa = 'MX';

$( window ).load(function() {
   
    //obtener datos para los selects
    /*if (document.querySelector(".jerarquia")) {
		dataObj = { 
            option: 'mostrarJerarquias'
          };
          fnSelectGeneralDatosAjax('.jerarquia', dataObj, 'modelo/ABC_Jerarquias_Modelo.php', 0);
    }
    if (document.querySelector(".tipoSol")) {
		dataObj = { 
            option: 'mostrarTipoComision'
          };
          fnSelectGeneralDatosAjax('.tipoSol', dataObj, 'modelo/ABC_Jerarquias_Modelo.php', 0);
    }
    if (document.querySelector(".tipoGasto")) {
		dataObj = { 
            option: 'mostrarTipoGasto'
          };
          fnSelectGeneralDatosAjax('.tipoGasto', dataObj, 'modelo/ABC_Jerarquias_Modelo.php', 0);
    }
    
   */
  $("#btNuevaTransferencia").click(function (){
    window.open("nominaNuevo.php", "_self");
    }); 

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
	var mes = "";
	var selectMes = document.getElementById('selectMes');
    for ( var i = 0; i < selectMes.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            mes = "'"+selectMes.selectedOptions[i].text+"'";
        }else{
            mes = mes+", '"+selectMes.selectedOptions[i].text+"'";
        }
    }

	var tipoNomina = "";
	var selectTipoNomina = document.getElementById('tipoNomina');
    for ( var i = 0; i < selectTipoNomina.selectedOptions.length; i++) {
        if (i == 0) {
            tipoNomina = "'"+selectTipoNomina.selectedOptions[i].text+"'";
        }else{
            tipoNomina = tipoNomina+", '"+selectTipoNomina.selectedOptions[i].text+"'";
        }
    }

    var noQuincena = "";
	var selectnoQuincena = document.getElementById('noQuincena');
    for ( var i = 0; i < selectnoQuincena.selectedOptions.length; i++) {
        if (i == 0) {
        noQuincena = "'"+selectnoQuincena.selectedOptions[i].value+"'";
        }else{
            noQuincena = noQuincena+", '"+selectnoQuincena.selectedOptions[i].value+"'";
        }
    }
    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
          mes:mes, 
          tipoNomina:tipoNomina,
          noQuincena:noQuincena,
          folio: $("#folio").val(),
          txtFechaInicio: $("#txtFechaInicio").val(),
          txtFechaFin: $("#txtFechaFin").val()
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/nominaModelo.php",
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
			var columnasExcel= [ 0, 1, 2, 3,4];
			var columnasVisuales= [ 0, 1, 2, 3,4,5,6,7];
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

function eliminar (idMonto,descripction,jerarquia,zonaEconomica){
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar la jerarquía '+descripction+'?', '', 'fnEliminarClave(\''+idMonto+'\',\''+descripction+'\','+jerarquia+',\''+zonaEconomica+'\')');
}

function fnTImeEliminar(idMonto,descripction,jerarquia,zonaEconomica){
    dataObj = { 
        option: 'eliminarInformacion',
        idMonto: idMonto,  
        jerarquia: jerarquia,
        zonaEconomica:zonaEconomica
    };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/ABC_Jerarquias_Modelo.php",
      data:dataObj
  })
  .done(function( data ) {
      if(data.result){
          //Si trae informacion
          ocultaCargandoGeneral();
          var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
          muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el registro '+descripction+' del Catálogo Jerarquías con éxito </p>');
          //setTimeout(function(){ $('.modal-backdrop').remove();}, 1500);
          fnObtenerInformacion();
        }else{
          
          muestraMensaje('No es posible eliminar la jerarquía '+descripction+' ya que se encuentra vinculada a otro registro', 3, 'divMensajeOperacion', 5000); 
      }
  })
  .fail(function(result) {
    ocultaCargandoGeneral();
  });

}
function fnEliminarClave(idMonto,descripction,jerarquia,zonaEconomica){
    muestraCargandoGeneral();
    setTimeout(function(){fnTImeEliminar(idMonto,descripction,jerarquia,zonaEconomica);}, 2000);
    
}
