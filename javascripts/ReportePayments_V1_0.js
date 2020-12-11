/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

$( document ).ready(function() {
	if (document.querySelector(".selectBancos")) {
		//Opcion para operacion
		dataObj = { 
			option: 'mostrarBancos'
		};

		fnSelectGeneralDatosAjax('.selectBancos', dataObj, 'modelo/ReportePayments_V1_0Modelo.php', 0);
	}
	
    if (document.querySelector(".selectBancosCuentas")) {
		//Opcion para operacion
		dataObj = { 
			option: 'mostrarBancosCuentas'
		};

		fnSelectGeneralDatosAjax('.selectBancosCuentas', dataObj, 'modelo/ReportePayments_V1_0Modelo.php', 0);
	}

	if (document.querySelector(".selectTipoPagoTeso")) {
		//Opcion para operacion
		dataObj = { 
			option: 'mostrarTipoPago'
		};

		fnSelectGeneralDatosAjax('.selectTipoPagoTeso', dataObj, 'modelo/ReportePayments_V1_0Modelo.php', 0);
	}

	fnObtenerRegistrosSuficiencia();
});

/**
 * Funcion para cargar cuentas de bancos cuando cambia el banco
 * @return {[type]} [description]
 */
function fnCambioBancos() {
	var bancos = "";
	var selectBancos = document.getElementById('selectBancos');
    for ( var i = 0; i < selectBancos.selectedOptions.length; i++) {
        if (i == 0) {
            bancos = "'"+selectBancos.selectedOptions[i].value+"'";
        }else{
            bancos = bancos+", '"+selectBancos.selectedOptions[i].value+"'";
        }
    }

	$('.selectBancosCuentas').empty();
    $('.selectBancosCuentas').multiselect({
        disableIfEmpty: true,
        disabledText: "Cargando datos..."
    });

	dataObj = { 
		option: 'mostrarBancosCuentas',
		bancos: bancos
	};
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: 'modelo/ReportePayments_V1_0Modelo.php',
        data: dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            $('.selectBancosCuentas').append( fnCrearDatosSelect(data.contenido.datos, '', '', 0) );
        }else{
            // $('.selectBancosCuentas').append( mensajeError );
        }

        $('.selectBancosCuentas').multiselect({
            disableIfEmpty: false,
            disabledText: ""
        });

        $('.selectBancosCuentas').multiselect('rebuild');
    })
    .fail(function(result) {
        // Mensaje Error
        // console.log("result: "+result);
    });
}

/**
 * Funcion para obtener información a visualizar en la tabla
 * @return {[type]} [description]
 */
function fnObtenerRegistrosSuficiencia() {
	//console.log("fnObtenerRegistrosSuficiencia");
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
        //console.log( unidadesnegocio.selectedOptions[i].value);
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

    var tipoPago = "";
	var selectTipoPago = document.getElementById('selectTipoPago');
    for ( var i = 0; i < selectTipoPago.selectedOptions.length; i++) {
        if (i == 0) {
            tipoPago = "'"+selectTipoPago.selectedOptions[i].value+"'";
        }else{
            tipoPago = tipoPago+", '"+selectTipoPago.selectedOptions[i].value+"'";
        }
    }

    var bancos = "";
	var selectBancos = document.getElementById('selectBancos');
    for ( var i = 0; i < selectBancos.selectedOptions.length; i++) {
        if (i == 0) {
            bancos = "'"+selectBancos.selectedOptions[i].value+"'";
        }else{
            bancos = bancos+", '"+selectBancos.selectedOptions[i].value+"'";
        }
    }

    var cuentasBancos = "";
	var selectBancosCuentas = document.getElementById('selectBancosCuentas');
    for ( var i = 0; i < selectBancosCuentas.selectedOptions.length; i++) {
        if (i == 0) {
            cuentasBancos = "'"+selectBancosCuentas.selectedOptions[i].value+"'";
        }else{
            cuentasBancos = cuentasBancos+", '"+selectBancosCuentas.selectedOptions[i].value+"'";
        }
    }

    muestraCargandoGeneral();    

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerSificiencia',
	      legalid: legalid,
	      tagref: tagref,
	      fechaDesde: $("#txtFechaDesde").val(),
	      fechaHasta: $("#txtFechaHasta").val(),
	      ue: ue,
	      tipoPago: tipoPago,
	      folio: $("#txtFolioSuficiencia").val(),
	      bancos: bancos,
	      cuentasBancos: cuentasBancos
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/ReportePayments_V1_0Modelo.php",
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
			var columnasExcel= [0, 1, 2, 3, 4, 5, 6, 7];
			var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

			// fnEjecutarVueGeneral();
			//$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
		}else{
			ocultaCargandoGeneral();
			var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
			var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se obtuvo la información</p>';
			muestraModalGeneral(3, titulo, mensaje);
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}