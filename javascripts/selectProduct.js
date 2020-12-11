/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */
 //Envio a capa
$( document ).ready(function() {
	if (document.querySelector(".selectPartidaGen")) {
		dataObj = { 
	        option: 'mostrarPartidasGen'
	    };
		fnSelectGeneralDatosAjax('.selectPartidaGen', dataObj, 'modelo/selectProductModelo.php', 0);
	}
    if (document.querySelector(".selectPartidaEsp")) {
        dataObj = { 
            option: 'mostrarPartidasEsp'
        };
        fnSelectGeneralDatosAjax('.selectPartidaEsp', dataObj, 'modelo/selectProductModelo.php', 0);
    }
	if (document.querySelector(".selectTipoProducto")) {
		dataObj = { 
	        option: 'mostrarTipoProducto'
	    };
		fnSelectGeneralDatosAjax('.selectTipoProducto', dataObj, 'modelo/selectProductModelo.php', 0);
	}
	if (document.querySelector(".selectProductoCog")) {
		dataObj = { 
	        option: 'mostrarCogProducto'
	    };
		fnSelectGeneralDatosAjax('.selectProductoCog', dataObj, 'modelo/selectProductModelo.php', 0);
	}
	// Datos Información
	fnObtenerInformacion();
	$("#btnBusqueda").click(function() {
    	fnObtenerInformacion();
    });
    $('#selectTipoProducto').change(function() {
    	var mbflag = $('#selectTipoProducto').val();
    	var dataNewGenPartida = "";
    	if(mbflag == null || mbflag.length > 1 || mbflag === 'undefined' || mbflag == ''){
    		mbflag = '';
    	}
    	dataObj = { 
	      option: 'mostrarPartidasGen',
	      mbflag: mbflag[0]
	    };
		$.ajax({
		    method: "POST",
		    dataType:"json",
		    url: "modelo/selectProductModelo.php",
		    data:dataObj
		})
		.done(function( data ) {
			if(data.result){
				dataNewGenPartida = data.contenido.datos;
				var categoriaID = "";
				var categoriaDesc = "";
				var categoriaNew = "";
				for (var info in dataNewGenPartida) {
                    categoriaID = dataNewGenPartida[info].value;
                    categoriaDesc = dataNewGenPartida[info].texto;
                    categoriaNew += "<option value="+categoriaID+">"+categoriaDesc+"</option>";
                }
                $('.selectPartidaGen').empty();
                $('.selectPartidaGen').append(categoriaNew);
                $('.selectPartidaGen').multiselect('rebuild');
			}
		})
		.fail(function(result) {
		  console.log("ERROR");
		  console.log( result );
		});
    });
    $('#selectTipoProducto').change(function() {
        var mbflag = $('#selectTipoProducto').val();
        var dataNewEspPartida = "";
        if(mbflag == null || mbflag.length > 1 || mbflag === 'undefined' || mbflag == ''){
            mbflag = '';
        }
        dataObj = { 
          option: 'mostrarPartidasEsp',
          mbflag: mbflag[0]
        };
        $.ajax({
            method: "POST",
            dataType:"json",
            url: "modelo/selectProductModelo.php",
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                dataNewEspPartida = data.contenido.datos;
                var partidaID = "";
                var partidaDesc = "";
                var partidaNew = "";
                for (var info in dataNewEspPartida) {
                    partidaID = dataNewEspPartida[info].value;
                    partidaDesc = dataNewEspPartida[info].texto;
                    partidaNew += "<option value="+partidaID+">"+partidaDesc+"</option>";
                }
                $('.selectPartidaEsp').empty();
                $('.selectPartidaEsp').append(partidaNew);
                $('.selectPartidaEsp').multiselect('rebuild');
            }
        })
        .fail(function(result) {
          console.log("ERROR");
          console.log( result );
        });
    });
});

function fnObtenerInformacion() {
	var legalid = "";
	var selectRazonSocial = document.getElementById('selectRazonSocial');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            legalid = "'"+selectRazonSocial.selectedOptions[i].value+"'";
        }else{
            legalid = legalid+", '"+selectRazonSocial.selectedOptions[i].value+"'";
        }
    }

	var tagref = "";
	var selectUnidadNegocio = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        if (i == 0) {
            tagref = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            tagref = tagref+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
    }

    var ue = "";
	var selectUnidadEjecutora = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUnidadEjecutora.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
    }

    var patidaGen = "";
	var select = document.getElementById('selectPartidaGen');
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        if (i == 0) {
            patidaGen = "'"+select.selectedOptions[i].value+"'";
        }else{
            patidaGen = patidaGen+", '"+select.selectedOptions[i].value+"'";
        }
    }

    var patidaEsp = "";
    var select = document.getElementById('selectPartidaEsp');
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        if (i == 0) {
            patidaEsp = "'"+select.selectedOptions[i].value+"'";
        }else{
            patidaEsp = patidaEsp+", '"+select.selectedOptions[i].value+"'";
        }
    }

    var tipoProducto = "";
	var select = document.getElementById('selectTipoProducto');
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        if (i == 0) {
            tipoProducto = "'"+select.selectedOptions[i].value+"'";
        }else{
            tipoProducto = tipoProducto+", '"+select.selectedOptions[i].value+"'";
        }
    }
    console.log(patidaGen);
    console.log(tipoProducto);
    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
	      legalid: legalid,
	      tagref: tagref,
	      ue: ue,
	      patidaGen: patidaGen,
          patidaEsp: patidaEsp,
	      txtDescripcion: $("#txtDescripcion").val(),
	      tipoProducto: tipoProducto
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/selectProductModelo.php",
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
			
			//console.log( "dataJson: " + JSON.stringify(dataJson) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [ 0, 1, 2, 3, 4, 5, 6, 7];
			var columnasVisuales= [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
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

/******************************************* stocks  ********************************************************************************/

function fnChangeType(){
        var mbflag = $('#MBFlag').val();
        var dataNewGenPartida = "";
        console.log(mbflag);
        dataObj = { 
          option: 'mostrarSegunTipo',
          mbflag: mbflag
        };
        $.ajax({
            method: "POST",
            dataType:"json",
            url: "modelo/selectProductModelo.php",
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                dataNewGenPartida = data.contenido.datosPartida;
                var categoriaID = "";
                var categoriaDesc = "";
                var categoriaNew = "";
                for (var info in dataNewGenPartida) {
                    categoriaID = dataNewGenPartida[info].value;
                    categoriaDesc = dataNewGenPartida[info].texto;
                    categoriaNew += "<option value="+categoriaID+">"+categoriaDesc+"</option>";
                }
                $('#CategoryID').empty();
                $('#CategoryID').append("<option value='0'>Sin Selección ...</option>" + categoriaNew);
                $('#CategoryID').multiselect('rebuild');
                dataUnidad = data.contenido.datosUnidad;
                var unitsID = "";
                var unitsDesc = "";
                var unitsNew = "";
                for (var info in dataUnidad) {
                    unitsID = dataUnidad[info].value;
                    unitsDesc = dataUnidad[info].texto;
                    unitsNew += "<option value="+unitsDesc+">"+unitsDesc+"</option>";
                }
                $('#Units').empty();
                $('#Units').append("<option value='0'>Sin Selección</option>" + unitsNew);
                $('#Units').multiselect('rebuild');
                dataCabms = data.contenido.datosCabms;
                var cabmsID = "";
                var cabmsNew = "";
                for (var info in dataCabms) {
                    cabmsID = dataCabms[info].value;
                    cabmsNew += "<option value="+cabmsID+">"+cabmsID+"</option>";
                }
                $('#CabmsID').empty();
                $('#CabmsID').append("<option value='0'>Sin Selección</option>" + cabmsNew);
                $('#CabmsID').multiselect('rebuild');
            }
        })
        .fail(function(result) {
          console.log("ERROR");
          console.log( result );
        });
}

function fnValidaStock(){
	var arrayValidaInfo = [];
	var arrayMensajeError = [];
	arrayValidaInfo = [{
		'cve': $('#StockID').val(),
		'shortDesc': $('#Description').val(),
		'longDesc': $('#LongDescription').val(),
		'mbflag': $('#MBFlag').val(),
		'unidad': $('#Units').val(),
		'partida': $('#CategoryID').val(),
		'cabms':  $('#CabmsID').val()
	}];
	//console.log(arrayValidaInfo);
	$.each( arrayValidaInfo, function( index, value ){
		console.log(value);
        /*if(!fnEmpty(value)){

        	arrayMensajeError.push(value);
        }*/
    });
    //console.log(arrayMensajeError);
}

function fnEmpty(element){
	var statusElement = false;
	if( element != '' || element !== 'undefined' || element != null || element != 0){
		statusElement = true;
	}
	return statusElement;
}

function fnSaveStock(){
	fnValidaStock();
}

/******************************************* stocks  ********************************************************************************/