//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;
window.renglon = 1;

$(window).load(function() {

    //obtener datos para los selects
    if (document.querySelector(".pp")) {
        dataObj = {
            option: 'mostrarPp'
        };
        fnSelectGeneralDatosAjax('.pp', dataObj, 'modelo/ABC_Conceptos_Nomina_Modelo.php', 0);
    }
    if (document.querySelector(".partida")) {
        dataObj = {
            option: 'mostrarPartida'
        };
        fnSelectGeneralDatosAjax('.partida', dataObj, 'modelo/ABC_Conceptos_Nomina_Modelo.php', 0);
    }
    if (document.querySelector(".concepto")) {
        dataObj = {
            option: 'mostrarConcepto'
        };
        fnSelectGeneralDatosAjax('.concepto', dataObj, 'modelo/ABC_Conceptos_Nomina_Modelo.php', 0);
    }

    fnObtenerInformacion();
    $("#btnBusqueda").click(function() {
        fnObtenerInformacion();
    })

});



function fnObtenerInformacion() {
    var pp = "";
    var selectPp = document.getElementById('pp');
    for (var i = 0; i < selectPp.selectedOptions.length; i++) {
        if (i == 0) {
            pp = "'" + selectPp.selectedOptions[i].value + "'";
        } else {
            pp = pp + ", '" + selectPp.selectedOptions[i].value + "'";
        }
    }

    var partida = "";
    var selectPartida = document.getElementById('partida');
    for (var i = 0; i < selectPartida.selectedOptions.length; i++) {
        if (i == 0) {
            partida = "'" + selectPartida.selectedOptions[i].value + "'";
        } else {
            partida = partida + ", '" + selectPartida.selectedOptions[i].value + "'";
        }
    }

    var concepto = "";
    var selectConcepto = document.getElementById('concepto');
    for (var i = 0; i < selectConcepto.selectedOptions.length; i++) {
        if (i == 0) {
            concepto = "'" + selectConcepto.selectedOptions[i].value + "'";
        } else {
            concepto = concepto + ", '" + selectConcepto.selectedOptions[i].value + "'";
        }
    }

    muestraCargandoGeneral();

    //Opcion para operacion
    dataObj = {
        option: 'obtenerInformacion',
        pp: pp,
        partida: partida,
        concepto: concepto,
    };

    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/ABC_Conceptos_Nomina_Modelo.php",
            data: dataObj
        })
        .done(function(data) {
            console.log("Bien");
            if (data.result) {
                //Si trae informacion
                ocultaCargandoGeneral();
                dataJson = data.contenido.datos;
                columnasNombres = data.contenido.columnasNombres;
                columnasNombresGrid = data.contenido.columnasNombresGrid;
                dataJsonNoCaptura = data.contenido.datos;

                console.log("dataJson: " + JSON.stringify(columnasNombresGrid));
                fnLimpiarTabla('divTabla', 'divContenidoTabla');
                //fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

                var nombreExcel = data.contenido.nombreExcel;
                var columnasExcel = [0, 1, 2, 3, 4];
                var columnasVisuales = [0, 1, 2, 3, 4, 5, 6, 7];
                fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
                // fnEjecutarVueGeneral();
                //$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
            } else {
                ocultaCargandoGeneral();
                muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000);
            }
        })
        .fail(function(result) {
            ocultaCargandoGeneral();
            //console.log("ERROR");
            // console.log( result );
        });
}

function eliminar(idMonto, descripction, jerarquia, zonaEconomica) {
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar la jerarquía ' + descripction + '?', '', 'fnEliminarClave(\'' + idMonto + '\',\'' + descripction + '\',' + jerarquia + ',\'' + zonaEconomica + '\')');
}

function fnTImeEliminar(idMonto, descripction, jerarquia, zonaEconomica) {
    dataObj = {
        option: 'eliminarInformacion',
        idMonto: idMonto,
        jerarquia: jerarquia,
        zonaEconomica: zonaEconomica
    };

    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/ABC_Conceptos_Nomina_Modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                //Si trae informacion
                ocultaCargandoGeneral();
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el registro ' + descripction + ' del Catálogo Jerarquías con éxito </p>');
                //setTimeout(function(){ $('.modal-backdrop').remove();}, 1500);
                fnObtenerInformacion();
            } else {

                muestraMensaje('No es posible eliminar la jerarquía ' + descripction + ' ya que se encuentra vinculada a otro registro', 3, 'divMensajeOperacion', 5000);
            }
        })
        .fail(function(result) {
            ocultaCargandoGeneral();
        });

}


function fnFormatoSelectGeneral(idClase) {
    $("" + idClase).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
    // Estilos para el diseño del select
    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });
}