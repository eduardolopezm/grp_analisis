/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Desarrollo
 * @version 0.1
 */
$( document ).ready(function() {
    fnFormatoSelectGeneral(".tipoDescarga");
    // fnListaInformacionGeneral("", "#txtProveedor", "proveedor");
});

function fnCambioReporteEgresos() {
    if ($("#selectReportes").val() == 'rptEgresosProveedores') {
        $("#divFiltroProveedor").css("display", "block");
    } else {
        $("#divFiltroProveedor").css("display", "none");
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

    //$objectContent.removeClass('hidden');

    if ($("#txtFechaInicial").val() == '' || $("#txtFechaFinal").val() == '') {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar fechas para mostrar información</p>';
        muestraModalGeneral(3, titulo, mensaje);
        return true;
    }

    // agregado de tipo de reporte
    aDato = "PrintPDF=>1&reporte=>"+$("#selectReportes").val();
    aDato += "&txtProveedor=>" + $("#txtProveedor").val();
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

    /*Encriptar URL*/

    $.ajax({
        async:false, 
        url:"modelo/imprimirreportesegresos_modelo.php",
        type:'POST',
        data: fd, 
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (data) {
            if(data.result){
                Link_PrintPDF = data.Mensaje;
            }
        }
    });

    window.open(""+Link_PrintPDF, "_blank");
}