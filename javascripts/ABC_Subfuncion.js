/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval 
 * @version 0.1
 */
 var url = "modelo/ABC_Subfuncion_modelo.php";
 var proceso = "";

$(document).ready(function() {
    //Mostrar Catalogo
    fnMostrarDatos('','','');
    $("#txtClave").blur(function(){
        var addCero = $("#txtClave").val();
        if (addCero.length == 1){
            // $("#txtClave").val("0"+addCero)
        } 
    });
});

/**
 * Muestra la información del catalogo completo o de forma individual
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnMostrarDatos(idSubfuncion,idFuncion,idFinalidad) {

    dataObj = {
        option: 'mostrarCatalogo',
        idSubfuncion: idSubfuncion,
        idFuncion: idFuncion,
        idFinalidad: idFinalidad
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            
            dataSubfuncionJason = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            
            fnLimpiarTabla('divTabla', 'divContenidoTabla');

            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [0, 1, 2, 3];
            var columnasVisuales= [0, 1, 2, 3, 4, 5];
            fnAgregarGrid_Detalle(dataSubfuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}
/** Muestra formulario para agregar un nuevo registro */
function fnAgregarCatalogoModal() {
    $('#divMensajeOperacion').addClass('hide');
    proceso = "Agregar";

    $("#selectFinalidad option[value=0]").attr("selected",true);
    $("#selectFinalidad").multiselect('rebuild');
    $("#selectFuncion option[value=0]").attr("selected",true);
    $("#selectFuncion").multiselect('rebuild');
    $("#txtClave").prop("readonly", false);
    $('#txtClave').val("");
    $('#txtDescripcion').val("");
    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Subfunción</h3>';
    $('#ModalUR_Titulo').empty();
    $('#ModalUR_Titulo').append(titulo);
    $('#ModalUR').modal('show');
}
/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {
    var finalidad = $('#selectFinalidad').val();
    var funcion = $('#selectFuncion').val();
    var clave = $('#txtClave').val();
    var descripcion = $('#txtDescripcion').val();
    var msg = "";

    /*if (clave == "" || descripcion == "" || funcion == "0" || funcion == "" || finalidad == "0" || finalidad == "") {

        $('#ModalUR').modal('hide');
        muestraMensaje('Agregar Subfunción y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
        return false;
    }*/
    /*if (clave == "" && descripcion == "") {
        
        $('#ModalUR').modal('hide');
        muestraMensaje('Debe agregar Subfunción y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
        return false;
        
        }else if (clave == "") {
            $('#ModalUR').modal('hide');
            muestraMensaje('Debe agregar Subfunción para realizar el proceso', 3, 'msjValidacion', 5000);
            return false;
        }else if (descripcion == "") {
            $('#ModalUR').modal('hide');
            muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
            
            return false;
    }*/
    if (finalidad == "" || finalidad == null || finalidad == 0 || finalidad == "0" || finalidad === "undefined" || funcion == "" || funcion == null || funcion == 0 || funcion == "0" || funcion === "undefined"|| clave == "" || descripcion == "") {
        if (finalidad == "" || finalidad == null || finalidad == 0 || finalidad == "0" || finalidad === "undefined" ) {
            msg += '<p>Falta capturar la clave FI </p>';
        }
        if (funcion == "" || funcion == null || funcion == 0 || funcion == "0" || funcion === "undefined"  ) {
            msg += '<p>Falta capturar la clave FU </p>';
        }
        if (clave == "" ) {
            msg += '<p>Falta capturar la clave SF </p>';
        }
        if (descripcion=="" ) {
            msg += '<p>Falta capturar el Descripción </p>';
        }

        $('#divMensajeOperacion').removeClass('hide');
        $('#divMensajeOperacion').empty();
        $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
        
    }else{
        $('#ModalUR').modal('hide');
        dataObj = {
            option: 'AgregarCatalogo',
            funcion: funcion,
            finalidad: finalidad,
            clave: clave,
            descripcion: descripcion,
            proceso: proceso
        };
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
                fnLimpiarTabla('divTabla', 'divContenidoTabla');
                fnMostrarDatos('','','');
            } else {
                muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
    }
}
/**
 * Muestra formulario para modificar la información del registro seleccionado
 * @param  {String} ur Código del Registro para obtener la información
 */
function fnModificar(idSubfuncion,descSubfuncion,idFuncion,descFuncion,idFinalidad,descFinalidad) {
    $('#divMensajeOperacion').addClass('hide');
    //alert(idSubfuncion+ ' - ' +descSubfuncion+ ' - ' +idFuncion+ ' - ' +descFuncion+ ' - ' +idFinalidad+ ' - ' +descFinalidad);
    proceso = "Modificar";

    dataObj = {
        option: 'mostrarCatalogo',
        idSubfuncion: idSubfuncion,
        descSubfuncion: descSubfuncion,
        idFuncion: idFuncion,
        descFuncion: descFuncion,
        idFinalidad: idFinalidad,
        descFinalidad: descFinalidad
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            info = data.contenido.datos;
            for (key in info) {
                
                $("#selectFinalidad option[value="+ idFinalidad +"]").attr("selected",true);
                $("#selectFinalidad").multiselect('rebuild');
                $("#selectFinalidad").multiselect('disable');
                $("#selectFinalidad").trigger('change')
                //$(".multiselect").attr('disabled', 'disabled');
                
                $("#selectFuncion option[value="+ idFuncion +"]").attr("selected",true);
                $("#selectFuncion").multiselect('rebuild');
                $("#selectFuncion").multiselect('disable');
                //$(".multiselect").attr('disabled', 'disabled');

                $("#txtClave").val("" + info[key].Clave);
                $("#txtClave").prop("readonly", true);
                $("#txtDescripcion").val("" + info[key].Subfuncion);
            }
            var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Subfunción</h3>';
            $('#ModalUR_Titulo').empty();
            $('#ModalUR_Titulo').append(titulo);
            $('#ModalUR').modal('show');
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}
/** Muestra mensaje de confirmación para eliminar registro */
function fnEliminar(idSubfuncion,descSubfuncion,idFuncion,idFinalidad) {
    $("#txtFinEliminar").val("" + idFinalidad);
    $("#txtFunEliminar").val("" + idFuncion);
    $("#txtClaveEliminar").val("" + idSubfuncion);
    
    var mensaje = 'Desea eliminar la Subfunción ' + idSubfuncion + ' - '+descSubfuncion;
    $('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
    $('#ModalUREliminar').modal('show');
}
/** Elimina el registro al confirmar la eliminación */
function fnEliminarEjecuta() {
    var idFinalidad = $('#txtFinEliminar').val();
    var idFuncion = $('#txtFunEliminar').val();
    var idSubfuncion = String($('#txtClaveEliminar').val());
    var descripcion = $('#txtDescripcion').val();
    if(idSubfuncion < 10){
        idSubfuncion = '0'+idSubfuncion;
    }
    console.log(idSubfuncion);
    $('#ModalUREliminar').modal('hide');
    dataObj = {
        option: 'eliminarUR',
        descripcion: descripcion,
        idSubfuncion: idSubfuncion,
        idFuncion: idFuncion,
        idFinalidad: idFinalidad
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
            fnLimpiarTabla('divTabla', 'divContenidoTabla');
            fnMostrarDatos('','','');
        } else {
            muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnFinalidadFuncion() {
    var idFinalidad = $("#selectFinalidad").val();
    dataObj = {
        option: 'mostrarFuncion',
        idFinalidad: idFinalidad
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            dataJson = data.contenido.datos;
            var contenido = "";
            for (var info in dataJson) {
                contenido += "<option value='" + dataJson[info].id_funcion + "'>" + dataJson[info].funciondescription + "</option>";
            }
            $('#selectFuncion').empty();
            $('#selectFuncion').append('<option value="0">Sin selección...</option>'+contenido);
            $('#selectFuncion').multiselect('rebuild');
        } else {
            console.log("ERROR Modelo");
            console.log( JSON.stringify(data) ); 
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}