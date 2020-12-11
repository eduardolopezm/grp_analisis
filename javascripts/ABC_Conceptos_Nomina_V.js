/**
 * @fileOverview
 *
 * @author Japheth Calzada
 * @version 0.1
 * @Fecha 09 Agosto 2019
 */

var url = "modelo/ABC_Conceptos_Nomina_V_Modelo.php";
var id = id;
var Advertencia = false;

/* variables globales fin */
$(document).ready(function() {

    fnPp();
    fnPartida();
    fnTipoConcepto();

    if (idConcepto != '') {
        fnMostrarDatos(idConcepto);
    }
    if (funcionVer == "1") {
        $('#guardarProd').hide();
        $('#eliminar').hide();
        $('#cancelar').hide();

    }

    if (funcionVer == '') {

        document.getElementById('concepto').readOnly = false;
    }
    $('#guardarProd').on('click', function() {
        fnSaveConcepto();
    });

});

$(document).on('click', '#cancelar', function() {
    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    location.replace("./ABC_Conceptos_Nomina.php?");
});




function fnPp() {

    dataObj = {
        option: 'mostrarPp'
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                dataMbflag = data.contenido.datos;
                console.log(dataMbflag);
                var mbflagNew = '';
                for (var info in dataMbflag) {
                    mbValue = dataMbflag[info].value;
                    mbTexto = dataMbflag[info].texto;
                    mbflagNew += "<option value=" + mbValue + ">" + mbTexto + "</option>";
                }

                $('#pp').empty();
                $('#pp').append("<option value=0>Sin Selección ...</option>" + mbflagNew);
                $('#pp').multiselect('rebuild');
            }
        })
        .fail(function(result) {
            console.log(result);
        });
}

function fnPartida() {
    dataObj = {
        option: 'mostrarPartida'
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                dataMbflag = data.contenido.datos;
                console.log(dataMbflag);
                var mbflagNew = '';
                for (var info in dataMbflag) {
                    mbValue = dataMbflag[info].value;
                    mbTexto = dataMbflag[info].texto;
                    mbflagNew += "<option value=" + mbValue + ">" + mbTexto + "</option>";
                }

                $('#partida').empty();
                $('#partida').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
                $('#partida').multiselect('rebuild');
            }
        })
        .fail(function(result) {
            console.log(result);
        })
}

function fnTipoConcepto() {
    $('#tipoConcepto').empty();
    $('#tipoConcepto').append("<option value=0>Sin Selección ...</option>" +
        "<option value='D'>Deducción</option> <option value='P'>Percepción</option>");
    $('#tipoConcepto').multiselect('rebuild');
}


function fnValidarDatos() {
    var pp = $("#pp").val();
    var partida = $('#partida').val();
    var claveConcepto = $('#claveConcepto').val();
    var concepto = $('#concepto').val();
    var cuentaContable = $('#cuentaContable').val();
    var tipoConcepto = $('#tipoConcepto').val();
    var msg = '';
    var datosConcepto = [];

    if (pp == 0) {
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la PP</p>';
    }
    if (partida == 0) {
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Partida.</p>';
    }
    if (claveConcepto == '' || claveConcepto.trim() == "") {
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Clave Concepto.</p>';
    }
    if (concepto == '' || concepto.trim() == "") {
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Concepto.</p>';
    }
    if (cuentaContable == '' || cuentaContable.trim() == "") {
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Cuenta Contable.</p>';
    }
    if (tipoConcepto == 0) {
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Tipo Concepto.</p>';
    }
    if (msg == '') {
        validar = true;
        datosConcepto = { 'pp': pp, 'partida': partida, 'claveConcepto': claveConcepto, 'concepto': concepto, 'cuentaContable': cuentaContable, 'tipoConcepto': tipoConcepto }
    } else {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datosConcepto;
}

function fnSaveConcepto() {
    var arrayConceptos = fnValidarDatos();
    console.log("arrayConceptos", arrayConceptos)
    if (idConcepto == '') {

        if (arrayConceptos != '') {
            dataObj = {
                option: 'guardarConcepto',
                arrayConceptos: arrayConceptos
            };
            $.ajax({
                    async: false,
                    cache: false,
                    method: "POST",
                    dataType: "json",
                    url: url,
                    data: dataObj
                })
                .done(function(data) {

                    if (data.result) {
                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se guardó el concepto ' + $('#concepto').val() + '</p>');
                    } else {
                        $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El concepto ' + $('#concepto').val() + ' ya se encuentra registrado.</p>');
                    }
                })
                .fail(function(result) {
                    console.log(result);
                });
        }
    } else {

        if (arrayConceptos != '') {
            fnModificarConceptos(arrayConceptos, idConcepto);
        }
    }
}

function fnDeleteStock() {
    var arrayStock = fnValidarDatos();
    var stockid = $('#StockID').val();
    if (arrayStock != '') {
        dataObj = {
            option: 'eliminarProducto',
            stockid: stockid
        };
        $.ajax({
                async: false,
                cache: false,
                method: "POST",
                dataType: "json",
                url: url,
                data: dataObj
            })
            .done(function(data) {
                if (data.result) {
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el registro ' + data.contenido + ' con éxito.</p>');
                }
            })
            .fail(function(result) {
                console.log(result);
            });
    } else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>No hay Información para eliminar</p>');
    }
}

function fnModificarConceptos(arrayConceptos, idConcepto) {

    dataObj = {
        option: 'modificarConceptos',
        arrayConceptos: arrayConceptos,
        idConcepto: idConcepto,
    };
    $.ajax({
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se actualizó el registro del concepto: ' + $("#concepto").val() + ' </p>');
            }
        })
        .fail(function(result) {
            console.log(result);
        });
}


function fnMostrarDatos(idConcepto) {

    fnObtenerDatos(idConcepto);

}

function fnPartidaEspecifica(mbflag) {
    dataObj = {
        option: 'mostrarPartida',
        mbflag: mbflag
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                datosPartidaEsp = data.contenido.datos;

                var partidaID = "";
                var partidaDesc = "";
                var partidaNew = "";

                for (var info in datosPartidaEsp) {
                    partidaID = datosPartidaEsp[info].value;
                    partidaDesc = datosPartidaEsp[info].texto;
                    partidaNew += "<option value=" + partidaID + ">" + partidaDesc + "</option>";
                }
                $('#selectPartidaEspecifica').empty();
                $('#selectPartidaEspecifica').append("<option value='0'>Sin Selección ...</option>" + partidaNew);
                $('#selectPartidaEspecifica').multiselect('rebuild');
                fnCabms(0, mbflag)
            }
        })
        .fail(function(result) {
            console.log(result);
        });
}

function fnCabms(partidaid, mbflag) {
    dataObj = {
        option: 'mostrarCabms',
        partidaid: partidaid,
        mbflag: mbflag
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                datosCabms = data.contenido.datos;
                var cabmsID = "";
                var cabmsDesc = "";
                var cabmsNew = "";

                for (var info in datosCabms) {
                    cabmsID = datosCabms[info].value;
                    cabmsDesc = datosCabms[info].texto;
                    cabmsNew += "<option value=" + cabmsID + ">" + cabmsDesc + "</option>";
                }
                $('#selectCabms').empty();
                $('#selectCabms').append("<option value='0'>Sin Selección ...</option>" + cabmsNew);
                $('#selectCabms').multiselect('rebuild');
            }
        })
        .fail(function(result) {
            console.log(result);
        });
}

function fnUnits(mbflag) {
    dataObj = {
        option: 'mostrarUnits',
        mbflag: mbflag
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                datosUnits = data.contenido.datos;

                var unitsID = "";
                var unitsDesc = "";
                var unitsNew = "";

                for (var info in datosUnits) {
                    unitsID = datosUnits[info].value;
                    unitsDesc = datosUnits[info].texto;
                    unitsNew += "<option value='" + unitsDesc + "'>" + unitsDesc + "</option>";
                }
                $('#units').empty();
                $('#units').append("<option value='0'>Sin Selección...</option>" + unitsNew);
                $('#units').multiselect('rebuild');
            }
        })
        .fail(function(result) {
            console.log(result);
        });
}

function fnObtenerDatos(idConcepto) {

    dataObj = {
        option: 'obtenerDatos',
        idConcepto: idConcepto
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                datosConcepto = data.contenido.datos;

                var pp = "";
                var partida = "";
                var clave_concepto = "";
                var desc_concepto = "";
                var tipo_concepto = "";
                var cta_contable = "";

                for (var info in datosConcepto) {
                    pp = datosConcepto[info].pp;
                    partida = datosConcepto[info].partida;
                    clave_concepto = datosConcepto[info].clave_concepto;
                    desc_concepto = datosConcepto[info].desc_concepto;
                    tipo_concepto = datosConcepto[info].tipo_concepto;
                    cta_contable = datosConcepto[info].cta_contable;
                }

                $('#pp > option[value="' + pp + '"]').attr('selected', 'selected');
                $('#pp').multiselect('rebuild');
                $('#pp').multiselect('disable');
                $('#partida > option[value="' + partida + '"]').attr('selected', 'selected');
                $('#partida').multiselect('rebuild');
                $('#partida').multiselect('disable');
                $('#concepto').val(desc_concepto);
                document.getElementById('concepto').readOnly = true;
                $("#claveConcepto").val(clave_concepto);
                document.getElementById('claveConcepto').readOnly = true;
                $("#cuentaContable").val(cta_contable);
                document.getElementById('cuentaContable').readOnly = true;
                $('#tipoConcepto > option[value="' + tipo_concepto + '"]').attr('selected', 'selected');
                $('#tipoConcepto').multiselect('rebuild');
                $('#tipoConcepto').multiselect('disable');

            }
        })
        .fail(function(result) {
            console.log(result);
        });
}