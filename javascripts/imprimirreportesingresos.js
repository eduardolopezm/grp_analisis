/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Desarrollo
 * @version 0.1
 */
$( document ).ready(function() {
    
 
    // Cargar UE de la UR seleccionada
   
 
    fnFormatoSelectGeneral(".tipoDescarga");
});

function fnCambioReporteIngreso() {
    if ($("#selectReportes").val() == 'rptInformeDiarioIngresos') {
        // $("#divSelectUrFiltros").css("display", "block");
        $("#divSelectUrFiltros").css("display", "none");
    } else {
        $("#divSelectUrFiltros").css("display", "none");
    }

    if (($("#selectReportes").val() == 'rptIngresosObjParcialDetallado') 
        || ($("#selectReportes").val() == 'rptIngresosDiarioPagos')) {
        $("#divSelectObjPrincipalFiltros").css("display", "block");
    } else {
        $("#divSelectObjPrincipalFiltros").css("display", "none");
    }
}
 
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

    if ($("#txtFechaInicial").val() == '' || $("#txtFechaFinal").val() == '') {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar fechas para mostrar información</p>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
    }

    if ($("#selectReportes").val() == 'rptIngresosPorSemana') {
        // Si es reporte por semana, validar fechas
        var txtFechaInicial = $("#txtFechaInicial").val();
        var txtFechaFinal = $("#txtFechaFinal").val();
        if (txtFechaInicial.substring(3, 10) != txtFechaFinal.substring(3, 10)) {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se puede elegir meses diferentes para el reporte seleccionado</p>';
            muestraModalGeneral(3, titulo, mensaje);
            return true;
        }
    }

    var aleatorioPrincipal = (Math.floor(Math.random() * 100000) + 1);
    var aleatorioParcial = (Math.floor(Math.random() * 100000) + 1);

    // agregado de tipo de reporte
    aDato = "PrintPDF=>1&reporte=>"+$("#selectReportes").val();
    // datos extra del reporte 
    aDato += "&selectUnidadNegocio=>" + $("#selectUnidadNegocio").val();
    aDato += "&selectObjetoPrincipal=>" + aleatorioPrincipal;
    aDato += "&selectObjetoParcial=>" + aleatorioParcial;
    aDato += "&fechainicial=>" + $("#txtFechaInicial").val();
    aDato += "&fechafinal=>" + $("#txtFechaFinal").val();
    aDato += "&nombreArchivo=>"+$("#selectReportes :selected").text();
    if($("#tipoDescarga").val()=="x"){
        aDato += "&tipoDescarga=>x";
    }
    
    console.log("reporte: "+$("#selectReportes").val());
    console.log("aDato: "+aDato);
    var fd = new FormData();
    var Link_PrintPDF =""
    fd.append("option","encryptarURL");
    fd.append("url",aDato);

    var datosPrin = "";
    var selectRazonSocial = document.getElementById('selectObjetoPrincipal');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        if (i == 0) {
            datosPrin = selectRazonSocial.selectedOptions[i].value;
        }else{
            datosPrin = datosPrin+","+selectRazonSocial.selectedOptions[i].value;
        }
    }

    var datosPar = "";
    var selectRazonSocial = document.getElementById('selectObjetoParcial');
    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        if (i == 0) {
            datosPar = selectRazonSocial.selectedOptions[i].value;
        }else{
            datosPar = datosPar+","+selectRazonSocial.selectedOptions[i].value;
        }
    }

    fd.append("selectObjetoPrincipal",datosPrin);
    fd.append("selectObjetoParcial",datosPar);
    fd.append("aleatorioPrincipal",aleatorioPrincipal);
    fd.append("aleatorioParcial",aleatorioParcial);

    /*Encriptar URL*/
    $.ajax({
        async:false, 
        url:"modelo/imprimirreportesingresos_modelo.php",
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
        url: "modelo/imprimirreportesingresos_modelo.php",
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
 

 