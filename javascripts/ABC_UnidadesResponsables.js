/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

$(document).ready(function() {
    //Mostrar Catalogo
    fnMostrarDatos('');
    $("#clave").keyup(function() {
        var upperclave = $("#clave").val();
        upperclave = upperclave.toUpperCase();
        $("#clave").val("" + upperclave);
    });

});

var proceso = "";

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(ur) {
    //console.log("fnMostrarDatos");

    //Opcion para operacion
    dataObj = {
        option: 'mostrarCatalogo',
        ur: ur
    };
    //Obtener datos de las bahias
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/ABC_UnidadesResponsables_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                info = data.contenido.datosCatalogo;

                dataReasignacionJason = data.contenido.datosCatalogo;
                columnasNombres = data.contenido.columnasNombres;
                columnasNombresGrid = data.contenido.columnasNombresGrid;

                fnLimpiarTabla('divTabla', 'divCatalogo');

                var nombreExcel = data.contenido.nombreExcel;
                var columnasExcel = [0, 1];
                var columnasVisuales = [0, 1, 2, 3];
                fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

/** Muestra formulario para agregar un nuevo registro */
function fnAgregarCatalogoModal() {
    $('#divMensajeOperacion').addClass('hide');
    console.log("fnAgregarCatalogoModal");

    proceso = "Agregar";

    $("#clave").prop("readonly", false);
    $('#clave').val("");
    $('#txtDescripcion').val("");


    $('#clave').val("");
    $('#descripcion').val("");
    $('#legalid').val("");
    $("#legalid").selectpicker('val', "");
    $("#legalid").multiselect('refresh');
    $(".legalid").css("display", "none");
    $('#areacode').val("");
    $('#u_department').val("");
    $('#description').val("");
    $('#address1').val("");
    $('#address2').val("");
    $('#address3').val("");
    $('#address4').val("");
    $('#address5').val("");
    pais = "MEXICO";
    $('#cp').val("");
    $('#tipofact').val("");
    $('#tagdebtorno').val("");
    $('#tagsupplier').val("");
    $('#cmbTipo').val("");
    $("#cmbTipo").selectpicker('val', "");
    $("#cmbTipo").multiselect('refresh');
    $(".cmbTipo").css("display", "none");
    $('#txtNumInterior').val("");




    var titulo = '<span class="glyphicon glyphicon-info-sign"></span> Agregar Unidad Responsable';
    $('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
    $('#ModalUR').modal('show');
    proceso = "Agregar";
}

/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {
    var clave = $('#clave').val();
    var descripcion = $('#description').val();
    var msg = "";
    var op = $('#legalid option:selected').text();
    var tipo = $('#cmbTipo option:selected').text();

    if (clave == "" || clave.length < 3 || descripcion == "") {
        if (clave == "") {
            msg += '<p>Falta capturar la clave UR </p>';
        }
        if(clave.length < 3){
            msg += "La clave no puede ser menor a 3 digitos. Ej: X23.";
        }
        if (descripcion == "") {
            msg += '<p>Falta capturar el Descripción </p>';
        }

        $('#divMensajeOperacion').removeClass('hide');
        $('#divMensajeOperacion').empty();
        $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

    } else {
        $('#ModalUR').modal('hide');

        legalid = $('#legalid').val();
        areacode = $('#areacode').val();
        department = $('#u_department').val();
        description = $('#description').val();
        address1 = $('#address1').val();
        address2 = $('#address2').val();
        address3 = $('#address3').val();
        address4 = $('#address4').val();
        address5 = $('#address5').val();
        pais = "MEXICO";
        cp = $('#cp').val();
        tipofact = $('#tipofact').val();
        tagdebtorno = $('#tagdebtorno').val();
        tagsupplier = $('#tagsupplier').val();
        cmbTipo = $('#cmbTipo').val();
        txtNumInterior = $('#txtNumInterior').val();

        dataObj = {
            option: 'AgregarCatalogo',
            clave: clave,
            proceso: proceso,
            legalid: legalid,
            areacode: areacode,
            department: department,
            description: description,
            address1: address1,
            address2: address2,
            address3: address3,
            address4: address4,
            address5: address5,
            pais: pais,
            cp: cp,
            tipofact: tipofact,
            tagdebtorno: tagdebtorno,
            tagsupplier: tagsupplier,
            cmbTipo: cmbTipo,
            txtNumInterior: txtNumInterior
        };
        //Obtener datos de las bahias
        $.ajax({
                method: "POST",
                dataType: "json",
                url: "modelo/ABC_UnidadesResponsables_modelo.php",
                data: dataObj
            })
            .done(function(data) {
                if (data.result) {
                    muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);

                    fnLimpiarTabla('divTabla', 'divCatalogo');
                    fnMostrarDatos('');
                } else {
                    muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
                }
            })
            .fail(function(result) {
                console.log("ERROR");
                console.log(result);
            });
    }
}

/**
 * Muestra formulario para modificar la información del registro seleccionado
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnModificar(ur) {
    $('#divMensajeOperacion').addClass('hide');
    proceso = "Modificar";

    //Opcion para operacion
    dataObj = {
        option: 'mostrarCatalogo',
        ur: ur
    };
    //Obtener datos de las bahias
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/ABC_UnidadesResponsables_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                //Si trae informacion
                info = data.contenido.datosCatalogo;
                for (key in info) {

                    $("#clave").val("" + info[key].tagref);
                    $("#clave").prop("readonly", true);
                    $("#description").val("" + info[key].Descripcion);
                    $("#address1").val("" + info[key].address1);

                    $("#address1").val("" + info[key].address1);
                    $("#address2").val("" + info[key].address2);
                    $("#address3").val("" + info[key].address3);
                    $("#address4").val("" + info[key].address4);
                    $("#address5").val("" + info[key].address5);
                    $("#address6").val("" + info[key].address6);
                    if (info[key].nu_interior != null) {
                        $('#txtNumInterior').val("" + info[key].nu_interior);
                    } else {
                        $('#txtNumInterior').val("");
                    }


                    $("#cp").val("" + info[key].cp);
                    $("#cmbTipo").val("" + info[key].ln_tipo);
                    $("#cmbTipo").selectpicker('val', info[key].ln_tipo);
                    $("#cmbTipo").multiselect('refresh');
                    $(".cmbTipo").css("display", "none");

                    $("#legalid").selectpicker('val', [info[key].legalid]);
                    $("#legalid").multiselect('refresh');
                    $(".legalid").css("display", "none");
                }

                var titulo = '<span class="glyphicon glyphicon-info-sign"></span> Modificar Unidad Responsable';
                $('#ModalUR_Titulo').empty();
                $('#ModalUR_Titulo').append(titulo);
                $('#ModalUR').modal('show');
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

/** Muestra mensaje de confirmación para eliminar registro */
function fnEliminar(ur) {
    $("#txtClaveEliminar").val("" + ur);

    var mensaje = 'Desea eliminar la Unidad Responsable ' + ur;
    $('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
    $('#ModalUREliminar').modal('show');
}

/** Elimina el registro al confirmar la eliminación */
function fnEliminarEjecuta() {
    var ur = $('#txtClaveEliminar').val();

    $('#ModalUREliminar').modal('hide');

    //Opcion para operacion
    dataObj = {
        option: 'eliminarUR',
        ur: ur
    };
    //Obtener datos de las bahias
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/ABC_UnidadesResponsables_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);

                fnLimpiarTabla('divTabla', 'divCatalogo');
                fnMostrarDatos('');
            } else {
                muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}
