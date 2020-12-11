/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Desarrollo
 * @version 0.1
 */
$( document ).ready(function() {
   fnCambioUnidadNegocio();

   // Cargar UE de la UR seleccionada
   fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');

   fnFormatoSelectGeneral(".tipoDescarga");
});

function fnAbrirReporte() {
    var reporte = $("#selectReportes>option:selected").html(), aDato = "", $objectContent = $('#objectContent');
    if($('#selectReportes').val() == 0){
        muestraModalGeneral(3,'Error de Datos','Es necesario que seleccione el reporte que desea generar.');
        $objectContent.addClass('hidden');
        return false;
    }

    if($('#tipoDescarga').val() == 0){
        muestraModalGeneral(3,'Error de Datos','Es necesario que seleccione el tipo de descarga que desea exportar.');
        $objectContent.addClass('hidden');
        return false;
    }
    
    //$objectContent.removeClass('hidden');

    // Datos de UE
    var valores = "";
    var select = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        // Que no se opcion por default
        if (select.selectedOptions[i].value != '-1') {
        	if (i == 0) {
	            valores = ""+select.selectedOptions[i].value+"";
	        }else{
	            valores = valores+", "+select.selectedOptions[i].value+"";
	        }
        }
    }

    if ($("#txtFechaInicial").val() == '' || $("#txtFechaFinal").val() == '') {
    	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    	var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar fechas para mostrar información</p>';
		muestraModalGeneral(3, titulo, mensaje);
		return true;
    }

    // agregado de tipo de reporte
    aDato = "PrintPDF=>1&reporte=>"+$("#selectReportes").val();
    // datos extra del reporte
    aDato += "&anio=>" + $("#selectAnio").val();
    aDato += "&entepublico=>" + $("#selectUnidadNegocio>option:selected").html();
    aDato += "&tagref=>" + $("#selectUnidadNegocio").val();
    aDato += "&ue=>" + valores;
    aDato += "&fechainicial=>" + $("#txtFechaInicial").val();
    aDato += "&fechafinal=>" + $("#txtFechaFinal").val();
    aDato += "&concepto=>2300";
    aDato += "&nombreArchivo=>"+$("#selectReportes :selected").attr('label');
    if($("#tipoDescarga").val()=="x"){
        aDato += "&tipoDescarga=>x";
    }
    console.log("reporte: "+$("#selectReportes").val());
    console.log("aDato: "+aDato);
    var fd = new FormData();
    var Link_PrintPDF =""
    fd.append("option","encryptarURL");
    fd.append("url",aDato);

    /*Encriptar URL*/

    $.ajax({
        async:false,
        url:"modelo/imprimirreportesconac_modelo.php",
        type:'POST',
        data: fd, 
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
            if(data.result){
                Link_PrintPDF = data.Mensaje;
                // Link_PrintPDF.href = data.Mensaje;
                // Link_PrintPDF.click();
            }
        }
    });


    // envio de cargando
    // muestraCargandoGeneral();
    // adjuducacion de url
    //console.log("aDato: "+aDato);
    //$('#objectContent').attr('src', aDato);
    //window.open(""+aDato, "_blank");
    window.open(""+Link_PrintPDF, "_blank");
}

function fnCambioRazonSocial() {
	muestraCargandoGeneral();
	legalid = $("#selectRazonSocial").val();
	//Opcion para operacion
	dataObj = { option: 'mostrarUnidadNegocio', legalid: legalid };
	//Obtener datos de las bahias
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/imprimirreportesconac_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//Si trae información
		if(data.result){
			dataJson = data.contenido.datos;
			var contenido = "<option value='0'>Seleccionar...</option>";
			for (var info in dataJson) { contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>"; }
			$('#selectUnidadNegocio').empty();
			$('#selectUnidadNegocio').append(contenido);
			$('#selectUnidadNegocio').multiselect('rebuild');
		}
		ocultaCargandoGeneral();
	})
	// en caso de fallo
	.fail(function(result) { ocultaCargandoGeneral(); });
}

function fnCambioUnidadNegocio() {
	muestraCargandoGeneral();
	//console.log("fnCambioRazonSocial");
	tagref = $("#selectUnidadNegocio").val();
	//Opcion para operacion
	dataObj = { option: 'cargarReportesConfigurados', tagref: tagref };

	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/imprimirreportesconac_modelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		// carga de las opciones
		if(data.result){ $('#selectReportes').multiselect('dataprovider',data.contenido); }
		// remoción de modal de carga
		ocultaCargandoGeneral();
	})
	// caso de fallo
	.fail(function(result) { ocultaCargandoGeneral(); });
}
