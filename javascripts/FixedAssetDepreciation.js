$(document).ready(function() {
    $("#selectUnidadNegocio").val($('#postUR').val());
    $("#selectUnidadNegocio").multiselect('rebuild');
    $("#selectUnidadEjecutora").val($('#postUE').val());
    $("#selectUnidadEjecutora").multiselect('rebuild');
   
    $('#btnBuscarValidacion').click(function(){
        var msjErr="";
        var vacios=1;

        if(fnObtenerOption("selectUnidadNegocio")==""){
            msjErr+="<p>El campo <b>UR</b> es necesario para continuar</p>";
            vacios= parseInt(vacios)+1;
        }

        if(fnObtenerOption("selectUnidadEjecutora")==""){
            msjErr+="<p>El campo <b>UE</b> es necesario para continuar</p>";
            vacios= parseInt(vacios)+1;
        }

        if(msjErr!=""){
            muestraModalGeneral(3,'Advertencia de datos',msjErr);
            return false;
        }else{
            $('#btnBuscar').click ();
            return true;
        }

    });

    if($("#txtFolioGenerado").length > 0){
	    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
	    var mensaje = '<p>Se genero la siguiente poliza con folio:<b> ' +$('#txtFolioGenerado').val()+'</b></p>';
    	muestraModalGeneral(3,titulo,mensaje,'','fnActualizarPagina()');
    }

    $("#btnPDF").click(function(){
        fnGenerarReporte(0);
    });

    $('#btnXLS').click(function(){
        fnGenerarReporte(1);
    });
});

function fnActualizarPagina(){
    window.location.href ="FixedAssetDepreciation.php";
}

function fnGenerarReporte(xsl){
    aDato = "PrintPDF=>1&reporte=>rptDepreciacionActivoFijo";
    aDato += "&anio=>2018";
    aDato += "&entepublico=>" + $("#selectUnidadNegocio>option:selected").html();
    aDato += "&tagref=>" + $("#selectUnidadNegocio").val();
    aDato += "&ue=>" + $("#selectUnidadEjecutora").val();
    aDato += "&fechainicial=>01-01-2018";
    aDato += "&fechafinal=>01-01-2018";
    aDato += "&concepto=>2300";
    aDato += "&nombreArchivo=>ReporteDepreciacionActivoFijo";
    if(xsl == "1"){
        aDato += "&tipoDescarga=>x";
    }

    var fd = new FormData();
    var Link_PrintPDF =""
    fd.append("option","encryptarURL");
    fd.append("url",aDato);

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

    window.open(""+Link_PrintPDF, "_blank");

}

function fnObtenerOption(componenteOrigen) {
    var option = "";
    var selectComponenteOrigen = document.getElementById('' + componenteOrigen);

    for (var i = 0; i < selectComponenteOrigen.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (selectComponenteOrigen.selectedOptions[i].value != "-1") {
            if (i == 0) {
                option = "'" + selectComponenteOrigen.selectedOptions[i].value + "'";
            } else {
                option = option + ", '" + selectComponenteOrigen.selectedOptions[i].value + "'";
            }
        }
    }
    return option;
}