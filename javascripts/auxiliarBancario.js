var tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
var dataJsonNoCaptura = new Array();

$(document).ready(function (){

	$('#btn-search').click(function(){
		fnObtenerReporteAuxiliarBancos();
	});


    loadBanks();
    fnFormatoSelectGeneral(".selectBanco");
    fnFormatoSelectGeneral(".selectClave");

    fnObtenerReporteAuxiliarBancos();

});


function fnObtenerOption(componenteSelect, intComillas = 0){
	var valores = "";
	var comillas="'";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != '-1') {

        	//intComillas = 1 No agregar las comillas
        	if(intComillas == 1){
        		comillas="";
            }

            // Que no se opcion por default
            if (i == 0) {
                valores = ""+comillas+select.selectedOptions[i].value+comillas+"";
            }else{
                valores = valores+","+comillas+select.selectedOptions[i].value+comillas+"";
            }
        }
    }

    return valores;
}

function fnObtenerReporteAuxiliarBancos(){
	var fd = new FormData();

	fd.append("option", "obtenerAuxiliarBanco");
	fd.append("selectUnidadNegocio", fnObtenerOption('selectUnidadNegocio'));
	fd.append("selectUnidadEjecutora", fnObtenerOption('selectUnidadEjecutora'));
    fd.append("selectProgramaPresupuestal", fnObtenerOption('selectProgramaPresupuestal'));
    fd.append("selectCapitulo", fnObtenerOption('selectCapitulo'));
	fd.append("selectClave", fnObtenerOption('selectClave'));
    fd.append("selectBanco", fnObtenerOption('selectBanco'));
	fd.append("dateDesde", $('#dateDesde').val());
	fd.append("dateHasta", $('#dateHasta').val());

    muestraCargandoGeneral();

	$.ajax({
	    async:false,
	    url:"modelo/auxiliarBancarioModelo.php",
	    type:'POST',
	    data: fd, 
	    cache: false,
	    contentType: false,
	    processData: false,
	    dataType: 'json',
	    success: function (data) {

            dataJson = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            dataJsonNoCaptura = data.contenido.datos;

            fnLimpiarTabla('divTabla', 'divContenidoTabla');

            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [1,2,3,4,5,6,7,8,10,11,12,13,14]; // Columnas a Imprimir en Excel
            var columnasVisuales= [1,2,3,4,5,6,7,8,10,11,12,13,14]; // Columnas a Visualizar en Pantalla
            fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

            /*if(data.result){

                $("#main-container").show();
                $("#tblContenidoMovs >tbody").empty();
                var dataMovimientos = data.contenido.datos;
                var strHTML = "";

                for(var key in dataMovimientos){
                    strHTML += '<tr>';
                    strHTML += '	<td style="text-align:center;">'+dataMovimientos[key].transdate+'</td>';
                    strHTML += '	<td style="text-align:left;">'+dataMovimientos[key].bankaccountname+'</td>';
                    strHTML += '	<td style="text-align:left;">'+dataMovimientos[key].concepto+'</td>';
                    strHTML += '	<td style="text-align:left;">'+dataMovimientos[key].banktranstype+'</td>';
                    strHTML += '	<td style="text-align:right;" > '+fnFormatoNumeroMX('',2)+'</td>';
                    strHTML += '	<td style="text-align:right;" > '+fnFormatoNumeroMX(dataMovimientos[key].cargos,2)+'</td>';
                    strHTML += '	<td style="text-align:right;" > '+fnFormatoNumeroMX(dataMovimientos[key].abonos,2)+'</td>';
                    strHTML += '	<td style="text-align:right;" > '+fnFormatoNumeroMX('',2)+'</td>';
                    strHTML += '</tr>';
                }

                $("#tblContenidoMovs >tbody").append(strHTML);
                $("#divEspacioado").hide();
            }*/


	    }
	});

    ocultaCargandoGeneral();
}


function fnFormatoNumeroMX(monto,decimales=2){
    var strMonto="0.00";
	
	if(!isNaN(monto)){
	    if(parseFloat(monto) == '0' || monto ==""){
	        strMonto = "0.00";
	    }else{
	        strMonto = new Intl.NumberFormat('es-MX').format(parseFloat(monto));
	    }
	}

	if(strMonto != "0.00" && strMonto != ""){
		var arrSTRMonto = strMonto.split('.');
		var entero = arrSTRMonto[0];
		var decimal = arrSTRMonto[1];
		var strDecimal = "";

		if(arrSTRMonto[1]){
			if(decimal.length >= decimales){
				strDecimal = decimal.substring(0, decimales);
			}else{
				strDecimal = decimal;
				for (var i = decimal.length; i <= decimales -1; i++) {
					strDecimal = strDecimal +"0";
				}
			}
	    }else{
	    	for (var i = 0; i <= decimales-1; i++) {
				strDecimal = strDecimal +"0";
			}
	    }

	    strMonto = entero+"."+strDecimal;
	}

	return strMonto;
}


function loadBanks(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/auxiliarBancarioModelo.php",{optionBank:'listBank'}).then(function(result) {

        var selectData = JSON.parse(result);

        $.each(selectData.banks.dtaBanks,function(key, registro) {
            $("#selectBanco").append('<option value='+registro.id+'>'+registro.name+'</option>');
        });

        fnFormatoSelectGeneral(".selectBanco");
    });
}


function bankAccount(idBanks){

    var idBankAccount = idBanks.value;

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/auxiliarBancarioModelo.php",{optionSelect:'listAccount',idBanks:idBankAccount}).then(function(result) {

        var selectData = JSON.parse(result);

        //console.log(selectData);
        $('#selectClave').empty();
      //  $('#selectClave').append("<option value='-1' selected> Sin selección </option>");

        $.each(selectData.accounts.dtaBanksacounts,function(key, registro) {
            $("#selectClave").append('<option value='+registro.id+'>'+registro.number+'</option>');
        });

       fnFormatoSelectGeneral(".selectClave");

        $('.selectClave').multiselect('rebuild');
    });

}

function alerta(dta){

	alert(dta.value)

}
