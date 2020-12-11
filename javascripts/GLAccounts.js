/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jonathan Cendejas Torres
 * @version 0.1
 */

// objetos de configuración
    window.uePorUsuario = new Array();
    window.cuentasTotales = new Array();
    window.cuentasBancarias = new Array();

    if(typeof(Storage)!=="undefined"){
        window.uePorUsuario = ( typeof(window.localStorage.uePorUsuario)!=="undefined" ? JSON.parse(window.localStorage.uePorUsuario) : window.uePorUsuario );
        //window.cuentasTotales = ( typeof(window.localStorage.cuentasTotales)!=="undefined" ? JSON.parse(window.localStorage.cuentasTotales) : window.cuentasTotales );
        //window.cuentasBancarias = ( typeof(window.localStorage.cuentasBancarias)!=="undefined" ? JSON.parse(window.localStorage.cuentasBancarias) : window.cuentasBancarias );
    }
    window.listadoHabilitado = true;

    window.buscadores = [ "cuentaDesde", "cuentaHasta" ];
    window.buscadoresConfiguracion = {};
    $.each(window.buscadores,function(index,valor){
        window.buscadoresConfiguracion[valor] = {};
    });
    window.buscadoresConfiguracion.cuentaDesde.origenDatos = "cuentasTotales";
    window.buscadoresConfiguracion.cuentaHasta.origenDatos = "cuentasTotales";

$(document).ready(function () {

    fnFormatoSelectGeneral('#NivelDesagregacion, #selectNaturalezaFiltro');
    fnFormatoSelectGeneral('.EstatusFiltro');
    

    if (document.querySelector(".infoCuentaContable")) {
        fnObtenerCuentasCont();
    }
    $('#ModalGeneral').on('hidden.bs.modal',function(){
        $("#ModalGeneralTam").width('900')
        $("#ModalGeneral_Advertencia").empty();
        $('#ModalGeneral').find('#cuentaAModificar').remove();
        $('#ModalGeneral').find('#cuentaAModificarNivel').remove();
        window.ReactivacionMultiple = "";
        window.estatusOriginal = "";
    });

    window.zIndexModal = $("#ModalGeneral").zIndex();

    window.estatusOriginal = "";
    window.ReactivacionMultiple = "";
    var validarIdentificador=1;

    //fnCargaSelectGeneros();
});

var dataJsonSelGenero = new Array();
var dataJsonSelGrupo = new Array();
var dataJsonSelRubro = new Array();
var dataJsonSelCuenta = new Array();
var cuentaAgregarModificar = "";

var dataJsonCuentasExistentes = new Array();

var nombreNivel1 = "Genero";
var nombreNivel2 = "Grupo";
var nombreNivel3 = "Rubro";
var nombreNivel4 = "Cuenta";

// Si esta vacia es 5 nivel y adelante
var cuentaNivel4Agregar = "";

// Niveles para dar de alta las cuentas
var numeroNiveles = 5;

var heredaNaturalezaGeneral = '';

function fnObtenerOption(componenteSelect,validacion = '-1'){
    var valores = "";
    var select = document.getElementById(''+componenteSelect);

    for ( var i = 0; i < select.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (select.selectedOptions[i].value != $.trim(validacion)) {
            // Que no se opcion por default
            if (i == 0 || valores=="") {
                valores = "'"+select.selectedOptions[i].value+"'";
            }else{
                valores = valores+", '"+select.selectedOptions[i].value+"'";
            }
        }
    }

    return valores;
}

function fnObtenerCuentasCont() {
    //Opcion para operacion
    dataObj = {
        option: 'obtenerInfoCuentasCont',
        buscarConFiltros: $("#buscarConFiltros").val(),
        selectUnidadNegocioFiltro: $("#selectUnidadNegocioFiltro").val(),
        selectUnidadEjecutoraFiltro: $("#selectUnidadEjecutoraFiltro").val(),
        busquedaPP: $("#busquedaPP").val(),
        busquedaGenero: $("#busquedaGenero").val(),
        cuentaDesde: $("#cuentaDesde").val(),
        cuentaHasta: $("#cuentaHasta").val(),
        EstatusFiltro: $("#EstatusFiltro").val(),
        nivelDesagregacion : fnObtenerOption('NivelDesagregacion'),
        naturaleza:fnObtenerOption('selectNaturalezaFiltro',' ')
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                dataJsonCuentasExistentes = new Array();
                dataJsonCuentasExistentes = data.contenido.datos;
                columnasNombres = data.contenido.columnasNombres;
                columnasNombresGrid = data.contenido.columnasNombresGrid;
                dataJsonNoCaptura = data.contenido.datos;
                //console.log( "dataJsonCuentasExistentes: " + JSON.stringify(dataJsonCuentasExistentes) );
                fnLimpiarTabla('divTabla', 'divContenidoTabla');

                // var columnasDescartarExportar= [8];
                // fnAgregarGrid_Detalle(dataJsonCuentasExistentes, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasDescartarExportar, false);

                var nombreExcel = data.contenido.nombreExcel;
                var columnasExcel = data.contenido.columnasExcel;
                var columnasVisuales = data.contenido.columnasVisuales;

                // var columnasExcel= [0, 1];
                // var columnasVisuales= [0, 1, 2, 3];
                fnAgregarGrid_Detalle(dataJsonCuentasExistentes, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);

                // fnEjecutarVueGeneral();
            } else {
                muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000);
            }
            ocultaCargandoGeneral();
        })
        .fail(function (result) {
            // console.log("ERROR");
            // console.log( result );
            ocultaCargandoGeneral();
        });
}

function fnObtenerGenero() {
    //Opcion para operacion
    dataObj = {
        option: 'mostrarGenero'
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                dataJsonSelGenero = data.contenido.datos;
            }
        })
        .fail(function (result) {
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnObtenerGrupo() {
    var genero = "";
    if (document.getElementById("selectGenero")) {
        var genero = $("#selectGenero").val();
    }
    //Opcion para operacion
    dataObj = {
        option: 'mostrarGrupo',
        genero: genero
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                dataJsonSelGrupo = data.contenido.datos;
            }
        })
        .fail(function (result) {
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnObtenerRubro() {
    var grupo = "";
    if (document.getElementById("selectGrupo")) {
        var grupo = $("#selectGrupo").val();
    }
    //Opcion para operacion
    dataObj = {
        option: 'mostrarRubro',
        grupo: grupo
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                dataJsonSelRubro = data.contenido.datos;
            }
        })
        .fail(function (result) {
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnObtenerCuenta() {
    var rubro = "";
    if (document.getElementById("selectRubro")) {
        var rubro = $("#selectRubro").val();
    }
    //Opcion para operacion
    dataObj = {
        option: 'mostrarCuenta',
        rubro: rubro
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //jararquia informacion
                dataJsonSelCuenta = createHerarchyData(data.contenido.datos);
                ////console.log(dataJsonSelCuenta);
            }
        })
        .fail(function (result) {
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnRegresaNivel(Cuenta){
    var stringsearch = ".";
    for (var count = -1, index = 0; index != -1; count++, index = Cuenta.indexOf(stringsearch, index + 1));
    return count;
}

function createHerarchyData(data) {
    for (dl in data) {
        //por cada valor, verifico si tiene el substring
        substr = data[dl].value
        for (dls = 0; dls < data.length; dls++) {
            if (data[dls].value != substr && data[dls].value.substr(0, substr.length) == substr) {
                //contiene substr
                //revisamos si nivel padre ya tiene arreglo contenedor
                if (typeof data[dl].subs == "undefined") {
                    data[dl].subs = [];
                }
                data[dl].subs.push(data[dls]);
                data[dls] = null;
            }
        }
        //ya adentro, quitamos null
        for (qt = data.length; qt >= 0; qt--) {
            if (data[qt] == null) {
                data.splice(qt, 1);
            }
        }
        //revisamos hijos
        if (typeof data[dl].subs != "undefined") {
            data[dl].subs = createHerarchyData(data[dl].subs);
        }
    }
    return data;
}

function fnHeredaNaturalezaGenero(cuenta) {
    // Obtiene si el genero hereda naturaleza
    var naturaleza = '';
    dataObj = {
        option: 'obtenerNaturaleza',
        cuenta: cuenta
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                //ocultaCargandoGeneral();
                naturaleza = data.contenido;
            } else {
                //ocultaCargandoGeneral();
                naturaleza = '';
            }
        })
        .fail(function (result) {
            //ocultaCargandoGeneral();
            // console.log("ERROR");
            // console.log( result );
        });
    return naturaleza;
}

function fnCambioGenero() {
    fnObtenerGrupo();
    fnCrearDatosSelect(dataJsonSelGrupo, '.selectGrupo', '', 1);
    fnFormarCuentaContable();

    // Obtener si es hereda naturaleza
    heredaNaturalezaGeneral = fnHeredaNaturalezaGenero($("#selectGenero").val());

    if ($("#selectGenero").val() == 7 || $("#selectGenero").val() == 8) {
        // Habilitar naturaleza para genero 7 y 8
        fnHabilitarNaturaleza();
    } else {
        // Deshabilitar naturaleza para genero diferente de 7 y 8
        fnDeshabilitarNaturaleza(heredaNaturalezaGeneral);
    }
}

/**
 * Función para habilitar la naturaleza
 * @return {[type]} [description]
 */
function fnHabilitarNaturaleza() {
    // $('#selectNaturaleza').val(''+heredaNaturalezaGeneral);
    $("#selectNaturaleza").multiselect('rebuild');
    $('#selectNaturaleza').multiselect('enable');

    for (var x = 6; x <= numeroNiveles; x++) {
        // $('#selectNaturaleza'+x).val(''+heredaNaturalezaGeneral);
        $('#selectNaturaleza' + x).multiselect('rebuild');
        $('#selectNaturaleza' + x).multiselect('enable');
    }
}

/**
 * Función para deshabilitar la naturaleza
 * y tomar la del genero
 * @param  {[type]} naturaleza Naturaleza
 * @return {[type]}            [description]
 */
function fnDeshabilitarNaturaleza(naturaleza) {
    $('#selectNaturaleza').val('' + naturaleza);
    $("#selectNaturaleza").multiselect('rebuild');
    $('#selectNaturaleza').multiselect('disable');

    for (var x = 6; x <= numeroNiveles; x++) {
        $('#selectNaturaleza' + x).val('' + naturaleza);
        $('#selectNaturaleza' + x).multiselect('rebuild');
        $('#selectNaturaleza' + x).multiselect('disable');
    }
}

function fnCambioGrupo() {
    fnObtenerRubro();
    fnCrearDatosSelect(dataJsonSelRubro, '.selectRubro', '');
    fnFormarCuentaContable();
    /////fnCambioRubro();
}

function fnCambioRubro() {
    fnObtenerCuenta();
    fnCrearDatosSelect(dataJsonSelCuenta, '.selectCuenta', '');
    fnFormarCuentaContable();
    /////fnCambioCuenta();
}

function fnCambioCuenta() {
    $("#txtSubcuenta").prop("disabled", false);
    //limpiamos niv5 y escondemos campos nivel 6+
    $("#divNivelesMayor5").empty();
    $("select#txtSubcuenta option").remove();
    $("#txtDescripcion").val('');
    //pre-poblamos nivel 5+
    lvl = $("select#selectCuenta").val();
    for (var s in dataJsonSelCuenta) {
        if (dataJsonSelCuenta[s].value == lvl) {
            //nivel 4 seleccionado tiene subniveles, revisamos .subs para poblar/crear campos
            readSubsCreateNivelFields(dataJsonSelCuenta[s].subs,numeroNiveles);
        }
    }
    fnFormarCuentaContable();
}

/**
 * Se ejecuta al cambiar el valor del select de nivel5 o superior
 */
function fnCambioSulvlCuenta(sel){
//poblar descripcion
    _sl=$(sel.currentTarget);
    _slcontainer=_sl.parents('[id*="divNewNivel"]').eq(0);
    //busco si el value es **new_lvl**
    if(_sl.val()!='**new_lvl**') {
        $("[name*='txtDescripcion']", _slcontainer).val('').val($("option:selected", _sl).data('accountname'));
        $("[name*='txtDescripcion']", _slcontainer).attr('readonly', true);
        if(!$("option:selected", _sl).data('accountname')){
            $("[name*='txtDescripcion']", _slcontainer).removeAttr("readonly");
        }
    }else{
        //subnivel manual
        _sl.parent().before(_sl);
        _sl.replaceWith($("<input>",{'class':'form-control',type:'text',name:_sl.attr('name'),id:_sl.attr('id'),placeholder:_sl.attr('placeholder'),title:_sl.attr('title'),onkeypress:"return soloNumeros(event)"}).on('change',fnFormarCuentaContable));
        $("span.multiselect-native-select:first",_slcontainer).empty('').remove();
        $("[name*='txtDescripcion']", _slcontainer).val('');
        $("[name*='txtDescripcion']", _slcontainer).removeAttr("readonly");
    }
    $("#btnAgregarNivel").show();
}

function readSubsCreateNivelFields(sub,lvl) {
    _ssb=(lvl==5)?'':lvl;//para agregar a nivel 5 o posteriores
    $("#txtSubcuenta"+_ssb).append($("<option>",{value:'',text:"Seleccionar..."}));
    for (_s in sub) {
        //creamos options de select
        _thisval=sub[_s].value.split('.')
        _thisval=_thisval[_thisval.length-1]
        //// Se reemplaza sub[_s].texto por _thisval en text: para que no aparezca la descripción en el combo
        $("#txtSubcuenta"+_ssb).append($("<option>",{value:_thisval,text:sub[_s].value}).data('subs',sub[_s].subs).data('accountname',sub[_s].accountname));
    }
    if(typeof sub != "undefined" && sub != null) {
        //option que sigue para crear
        _nextlvltxt = sub[_s].value.split('.');
        _nextlvltxt = (_nextlvltxt.slice(0, _nextlvltxt.length - 1)).join(".");
        _nextlvl = (_thisval * 1) + 1;
        _nextlvl = _nextlvl.toString().padStart(_thisval.length,'0');
        _nextlvltxt += "." + _nextlvl;
    }else{
        //no tengo arreglo de subniveles anteriores, busco el valor del anterior
        _nextlvltxt=$("option:selected","#txtSubcuenta"+(lvl-1)).text().split(" - ")
        _nextlvltxt=_nextlvltxt[0]+".1";
        _nextlvl=1;
    }
    $("#txtSubcuenta"+_ssb).append($("<option>",{value:_nextlvl,text:_nextlvltxt+" - [Crear éste nivel]"}));
    $("#txtSubcuenta"+_ssb).append($("<option>",{value:"**new_lvl**",text:"[Crear nuevo nivel]"}));
    fnFormatoSelectGeneral(".txtSubcuenta" + _ssb);
    $("#txtSubcuenta"+_ssb).multiselect('rebuild');
    $("#txtSubcuenta"+_ssb).off('change').on('change',fnCambioSulvlCuenta);
}

function fnFormarCuentaContable() {
    var cuentaFormada = "";
    cuentaAgregarModificar = "";

    var numCaracteres = 9;

    // Si esta vacio cuentaNivel4Agregar es nivel 5 en adelante

    if (cuentaNivel4Agregar == nombreNivel1) {
        numCaracteres = 1;
    } else if (cuentaNivel4Agregar == nombreNivel2) {
        numCaracteres = 3;
    } else if (cuentaNivel4Agregar == nombreNivel3) {
        numCaracteres = 5;
    } else if (cuentaNivel4Agregar == nombreNivel4) {
        numCaracteres = 7;
    }

    if (cuentaNivel4Agregar == "" || cuentaNivel4Agregar == nombreNivel2 || cuentaNivel4Agregar == nombreNivel3 || cuentaNivel4Agregar == nombreNivel4) {
        var select = $("#selectGenero").val();
        if (select != '0' && select != null && select != '') {
            cuentaFormada = select;
        }
    }
    if (cuentaNivel4Agregar == "" || cuentaNivel4Agregar == nombreNivel3 || cuentaNivel4Agregar == nombreNivel4) {
        var select = $("#selectGrupo").val();
        if (select != '0' && select != null && select != '') {
            cuentaFormada = select;
        }
    }
    if (cuentaNivel4Agregar == "" || cuentaNivel4Agregar == nombreNivel4) {
        var select = $("#selectRubro").val();
        if (select != '0' && select != null && select != '') {
            cuentaFormada = select;
        }
    }
    if (cuentaNivel4Agregar == "") {
        var select = $("#selectCuenta").val();
        if (select != '0' && select != null && select != '') {
            cuentaFormada = select;
        }
    }
    //TODO: el value de txtsubcuenta debe ser el numero solo del subnivel, sin rastro
    var datoNivel5 = "";
    var input = $("#txtSubcuenta").val();
    if (input != null && input != '.' && input.trim() != "") {
        datoNivel5 = input;
        if (cuentaNivel4Agregar == nombreNivel1) {
            cuentaFormada += input;
        } else {
            cuentaFormada += "." + input;
        }
    }

    // Datos niveles 6 y adelante. Obtener datos
    for (var x = 6; x <= numeroNiveles; x++) {
        var input = $("#txtSubcuenta" + x).val();
        if (input != '.' && input.trim() != "" && input != null) {
            cuentaFormada += "." + input;
        }
    }

    // Checar formato
    var val = 1, validacion = 1;
    var caracter = "";
    for (var x = 0; x < cuentaFormada.length; x++) {
        caracter = cuentaFormada.charAt(x);
        //console.log("letra "+caracter);
        if (val == 1) {
            // Validar numero
            val = 2;
            if (isNaN(caracter)) {
                // validacion = 0;
            }
        } else {
            // Validar punto
            val = 1;
            if (caracter != '.') {
                // validacion = 0;
            }
        }
    }
    if (caracter == '.') {
        // Ultimo dato es punto
        validacion = 0;
    }
    if (cuentaFormada.length < Number(numCaracteres)) {
        // Menor a 4 niveles
        validacion = 0;
    }

    // Datos niveles 6 y adelante. Validar niveles vacios
    var valCajaVacia = 0;
    for (var x = 6; x <= numeroNiveles; x++) {
        var input = $("#txtSubcuenta" + x).val();
        if (input != '.' && input.trim() == "" || input == null) {
            for (var y = x + 1; y <= numeroNiveles; y++) {
                var inputy = $("#txtSubcuenta" + y).val();
                if (inputy != '.' && inputy.trim() != "" && inputy != null) {
                    valCajaVacia = 1;
                    var mensajeVacio = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Nivel ' + x + ' se encuentra vacio</h6>';
                    muestraMensajeTiempo(mensajeVacio, 3, 'ModalGeneral_Advertencia', 5000);
                    break;
                }
            }
        } else {
            if (datoNivel5 == "") {
                valCajaVacia = 1;
                var mensajeVacio = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Nivel 5 se encuentra vacio</h6>';
                muestraMensajeTiempo(mensajeVacio, 3, 'ModalGeneral_Advertencia', 5000);
                break;
            }
        }

        if (valCajaVacia == 1) {
            break;
        }
    }

    var contenido = "";
    if (validacion == 1) {
        //console.log("todo bien");
        $("#btnGuardar").prop("disabled", false);
        cuentaAgregarModificar = cuentaFormada;
        contenido += '<h3><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> ' + cuentaFormada + '</h3>';
    } else {
        //console.log("error");
        $("#btnGuardar").prop("disabled", true);
        contenido += '<h3><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + cuentaFormada + '</h3>';
    }

    $('#divCuentaFormada').empty();
    $('#divCuentaFormada').append(contenido);
    //console.log("cuentaFormada: "+cuentaFormada);
}

function fnGuardarCuentaSpinner(){
    $("#ModalGeneral_Advertencia").empty();
    muestraCargandoGeneral();
    $("#ModalGeneral").zIndex("750");
    setTimeout(function(){ fnGuardarCuenta(); }, 500);
}

function fnGuardarCuenta() {
    $("#btnGuardar").prop("disabled", true);
    $("#ModalGeneral_Advertencia").empty();

    // Validar datos vacios
    if (cuentaNivel4Agregar != "" && ($("#txtDescripcion").val() == "" || $("#selectNaturaleza").val() == "")) {
        $("#btnGuardar").prop("disabled", false);

        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar Información</p>';
        muestraMensajeTiempo(mensaje, 3, 'ModalGeneral_Advertencia', 5000);
        ocultaCargandoGeneral();
        $("#ModalGeneral").zIndex(window.zIndexModal);
        return true;
    }

    if (validarIdentificador == 1 && Number(numeroNiveles) > Number(5) && ($("#txtUR").val() == '' || $("#txtUE").val() == '')) {
         var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Falta seleccionar UR y UE</p>';
         muestraMensajeTiempo(mensaje, 3, 'ModalGeneral_Advertencia', 5000);
         return true;
    }

    //Se quito validacion por fnValidarDiferenciador($("#txtUR").val(),$("#txtUE").val(),$("#txtPP").val())
    //Aqui no se ocuparan los identificadores
    /*if(  Number(numeroNiveles) >= Number(5) && cuentaNivel4Agregar == ""  ) {
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Complete (o elimine) la información del Diferenciador (UR, UE y Programa Presupuestario)</p>';
        muestraMensajeTiempo(mensaje, 3, 'ModalGeneral_Advertencia', 5000);
        return true;
    }*/

    //muestraCargandoGeneral();

    if (cuentaNivel4Agregar != "") {
        // Almacenar Nive 1-4
        //console.log("validaicon nivel 1-4");
        var tipo = 0;
        if (cuentaAgregarModificar.length == 1) {
            tipo = cuentaAgregarModificar;
        } else {
            tipo = $("#selectGenero").val();
        }
        // Se eliminaron estos campos porque ya no son usados 
        //txtPP: $("#txtPP").val()

        //Opcion para operacion
        dataObj = {
            option: 'agregarCuenta',
            tipoAlta: cuentaNivel4Agregar,
            cuenta: cuentaAgregarModificar,
            nombre: $("#txtDescripcion").val(),
            naturaleza: $("#selectNaturaleza").val(),
            tipo: tipo,
            txtUR: $("#txtUR").val(),
            txtUE: $("#txtUE").val()
        };
        $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: "modelo/GLAccounts_modelo.php",
            data: dataObj
        })
            .done(function (data) {
                //console.log("Bien");
                if (data.result) {
                    //Si trae informacion
                    //ocultaCargandoGeneral();
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    //$("#ModalGeneralTam").width('600');
                    //muestraModalGeneral(2, titulo, data.Mensaje);
                    muestraMensajeTiempo(data.Mensaje, 1, 'ModalGeneral_Advertencia', 5000);
                    //fnObtenerCuentasCont(); // Se deshabilita al reload del grid después de una consulta exitosa
                    fnFormarCuentaContable();
                    if(cuentaNivel4Agregar=="Genero"){
                        //fnCargaSelectGeneros();
                    }
                    //fnPrepararBuscador();
                } else {
                    //ocultaCargandoGeneral();
                    //fnObtenerCuentasCont();
                    muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
                    fnFormarCuentaContable();
                }
            })
            .fail(function (result) {
                //ocultaCargandoGeneral();
                // console.log("ERROR");
                // console.log( result );
            });
    } else {
        //console.log("*********validaicon nivel 5*********");
        //console.log("cuentaAgregarModificar: "+cuentaAgregarModificar);
        //console.log("dataJsonCuentasExistentes: "+JSON.stringify(dataJsonCuentasExistentes));
        var dataJsonCuentasAgregar = new Array();
        var mensajeValidaciones = "";
        var ultimoMensajeValidacion = "";
        var cuentaFor = "";
        var datosSep = cuentaAgregarModificar.split(".");
        var errorValidacion = 0;
        for (var x = 0; x < datosSep.length; x++) {
            //console.log("dato: "+datosSep[x]);
            if (cuentaFor == "") {
                cuentaFor += "" + datosSep[x];
            } else {
                cuentaFor += "." + datosSep[x];
            }

            var existeCuenta = 0;
            for (var key in dataJsonCuentasExistentes) {
                //console.log("accountcode: "+dataJsonCuentasExistentes[key].accountcode);
                if (cuentaFor == dataJsonCuentasExistentes[key].accountcode) {
                    existeCuenta = 1;
                    break;
                }
            }
            // console.log("cuentaFor: "+cuentaFor+" - tam: "+cuentaFor.length);
            var numNiveles = cuentaFor.split(".");
            if (existeCuenta == 0) {
                // console.log("no existe "+cuentaFor);
                // console.log("numNiveles: "+numNiveles.length);
                if (numNiveles.length == 5) {
                    // console.log("es nivel 5");
                    if (($("#txtDescripcion").val() == "" || $("#selectNaturaleza").val() == "")) {
                        errorValidacion = 1;
                        mensajeValidaciones += '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar Información Nivel 5</h6>';
                    } else {
                        var obj = new Object();
                        obj.cuenta = cuentaFor;
                        obj.nombre = $("#txtDescripcion").val();
                        obj.naturaleza = $("#selectNaturaleza").val();
                        obj.tipo = $("#selectGenero").val();
                        dataJsonCuentasAgregar.push(obj);
                    }
                } else if (numNiveles.length > 5) {
                    // console.log("es nivel 6 y adelante - Nivel: "+(x+1));
                    if (($("#txtDescripcion" + (x + 1)).val() == "" || $("#selectNaturaleza" + (x + 1)).val() == "")) {
                        errorValidacion = 1;
                        mensajeValidaciones += '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Completar Información Nivel ' + (x + 1) + '</h6>';
                    } else {
                        var obj = new Object();
                        obj.cuenta = cuentaFor;
                        obj.nombre = $("#txtDescripcion" + (x + 1)).val();
                        obj.naturaleza = $("#selectNaturaleza" + (x + 1)).val();
                        obj.tipo = $("#selectGenero").val();
                        dataJsonCuentasAgregar.push(obj);
                    }
                }
            } else if (numNiveles.length >= 5) {
                ultimoMensajeValidacion = '<h6><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Ya existe ' + cuentaFor + ', no es necesario completar la Información del Nivel</h6>';
            }
        }
        mensajeValidaciones += ultimoMensajeValidacion;
        // console.log("mensajeValidaciones: "+mensajeValidaciones);
        if (errorValidacion == 1) {
            muestraMensajeTiempo(mensajeValidaciones, 3, 'ModalGeneral_Advertencia', 5000);
            $("#btnGuardar").prop("disabled", false);
        } else {
            if (mensajeValidaciones != "") {
                muestraMensajeTiempo(mensajeValidaciones, 1, 'ModalGeneral_Advertencia', 5000);
            }
            //$("#ModalGeneral_Advertencia").empty();
            //console.log("dataJsonCuentasAgregar: "+JSON.stringify(dataJsonCuentasAgregar));
            var mensajeProceso = "",
                mensajeProcesoYaExiste = "",
                numeroCuentasExitosas = 0,
                numeroCuentasFallidas = 0,
                registrado = false;
            for (var key in dataJsonCuentasAgregar) {
                //console.log("dataJsonCuentasAgregar: "+dataJsonCuentasAgregar[key].cuenta);
                //Opcion para operacion
                dataObj = {
                    option: 'agregarCuenta',
                    cuenta: dataJsonCuentasAgregar[key].cuenta,
                    nombre: dataJsonCuentasAgregar[key].nombre,
                    naturaleza: dataJsonCuentasAgregar[key].naturaleza,
                    tipo: dataJsonCuentasAgregar[key].tipo,
                    txtUR: $("#txtUR").val(),
                    txtUE: $("#txtUE").val(),
                    txtPP: $("#txtPP").val()
                };
                $.ajax({
                    async: false,
                    cache: false,
                    method: "POST",
                    dataType: "json",
                    url: "modelo/GLAccounts_modelo.php",
                    data: dataObj
                })
                    .done(function (data) {
                        //console.log("Bien");
                        registrado = ( registrado||data.result ? true : registrado );
                        if (data.result) {
                            mensajeProceso += data.Mensaje;
                            numeroCuentasExitosas++;
                        } else {
                            mensajeProcesoYaExiste = data.Mensaje;
                            numeroCuentasFallidas++;
                        }
                    })
                    .fail(function (result) {
                        //ocultaCargandoGeneral();
                        // console.log("ERROR");
                        // console.log( result );
                    });
            }

            mensajeProceso = ( numeroCuentasExitosas==0&&numeroCuentasFallidas!=0 ? mensajeProcesoYaExiste : "" )+mensajeProceso;

            if (mensajeProceso != "") {
                muestraMensajeTiempo(mensajeProceso, ( registrado ? 1 : 3 ) , 'ModalGeneral_Advertencia', 5000);
                //fnObtenerCuentasCont(); // Se deshabilita al reload del grid después de una consulta exitosa
                fnFormarCuentaContable();
                //fnPrepararBuscador();
            }
        }
    }

    //$("#btnGuardar").prop("disabled", false);
    ocultaCargandoGeneral();
    $("#ModalGeneral").zIndex(window.zIndexModal);
}

function fnGuardarCuentaAjaxOriginal(cuenta, nombre, naturaleza, tipo) {
    //Opcion para operacion
    dataObj = {
        option: 'agregarCuenta',
        cuenta: cuentaAgregarModificar,
        nombre: $("#txtDescripcion").val(),
        naturaleza: $("#selectNaturaleza").val(),
        tipo: $("#selectGenero").val()
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                //ocultaCargandoGeneral();
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, data.contenido);
                fnObtenerCuentasCont();
            } else {
                //ocultaCargandoGeneral();
                muestraMensajeTiempo(data.contenido, 3, 'ModalGeneral_Advertencia', 5000);
            }
        })
        .fail(function (result) {
            //ocultaCargandoGeneral();
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnCrearCuentaNivel4(nombreNivel) {
    ////console.log("fnCrearCuentaNivel4: " + nombreNivel);

    $("#divSelectGenero").css("display", "none");
    $("#divSelectGrupo").css("display", "none");
    $("#divSelectRubro").css("display", "none");
    $("#divSelectCuenta").css("display", "none");

    cuentaNivel4Agregar = nombreNivel;

    var mensajeLabel = "";

    if (nombreNivel == nombreNivel1) {
        mensajeLabel = nombreNivel1;
        // Habilitar Naturaleza
        fnHabilitarNaturaleza();
    } else if (nombreNivel == nombreNivel2) {
        mensajeLabel = nombreNivel2;
        $("#divSelectGenero").css("display", "block");
    } else if (nombreNivel == nombreNivel3) {
        mensajeLabel = nombreNivel3;
        $("#divSelectGenero").css("display", "block");
        $("#divSelectGrupo").css("display", "block");
    } else if (nombreNivel == nombreNivel4) {
        mensajeLabel = nombreNivel4;
        $("#divSelectGenero").css("display", "block");
        $("#divSelectGrupo").css("display", "block");
        $("#divSelectRubro").css("display", "block");
    }

    // Datos niveles 6 y adelante ocultar formulario
    for (var x = 6; x <= numeroNiveles; x++) {
        $("#divNewNivel" + x).css("display", "none");
        $("#txtSubcuenta" + x).val("");
    }

    $("#txtSubcuenta").val("");
    $("#txtSubcuenta").prop("placeholder", "" + ( mensajeLabel=="Genero" ? "Género" : mensajeLabel ) );
    $("#txtSubcuenta").prop("title", "" + ( mensajeLabel=="Genero" ? "Género" : mensajeLabel ) );

    $("#labelCuentaNew").empty();
    $("#labelCuentaNew").append("" + ( mensajeLabel=="Genero" ? "Género" : mensajeLabel ) + ":" );

    $("#txtSubcuenta").prop("maxlength", "1");

    $('#divCuentaFormada').empty();
    //// Remover el botón Agregar Nivel cuando se agregan cuentas
    $('#btnAgregarNivel').hide();
    //// Reactivar txtSubcuenta
    //// readSubsCreateNivelFields($("option:selected","#txtSubcuenta").data('subs'),numeroNiveles);
    $('#txtSubcuenta').multiselect('destroy');
    $("#txtSubcuenta").replaceWith($("<input>",{'class':'form-control',type:'text',name:$("#txtSubcuenta").attr('name'),id:$("#txtSubcuenta").attr('id'),placeholder:"[Crear nuev"+( nombreNivel.toLowerCase()=="cuenta" ? "a" : "o" )+" "+( nombreNivel=="Genero" ? "Género" : nombreNivel ).toLowerCase()+"]",title:$("#txtSubcuenta").attr('title'),onkeypress:"return soloNumeros(event)"}).on('change',fnFormarCuentaContable));
    $("#txtSubcuenta").prop('maxLength',1)
    $('#txtUR').multiselect('select', '');
    $('#txtUE').multiselect('select', '');
    $('#txtPP').multiselect('select', '');
    $("#divIdentificador").hide();

}

function fnGuardarCuentaNivel4(nombreNivel) {
    ////console.log("fnGuardarCuentaNivel4");
}

function fnAgregarModificarCuenta() {

    cuentaNivel4Agregar = "";

    cuentaAgregarModificar = "";

    numeroNiveles = 5;

    $("#btnAgregar").prop("disabled", true);

    $("#ModalGeneral_Advertencia").empty();

    var mensaje = '<div class="row">';

    fnObtenerGenero();
    var optionsGenero = fnCrearDatosSelect(dataJsonSelGenero, "", "", 1);

    // fnObtenerGrupo();
    // var optionsGrupo = fnCrearDatosSelect(dataJsonSelGrupo, "", "", 1);
    var optionsGrupo = "";

    // fnObtenerRubro();
    // var optionsRubro = fnCrearDatosSelect(dataJsonSelRubro, "", "", 1);
    var optionsRubro = "";

    // fnObtenerCuenta();
    // var optionsCuenta = fnCrearDatosSelect(dataJsonSelCuenta, "", "", 1);
    var optionsCuenta = "";

    mensaje += '<div class="col-md-12 col-xs-12">';

    mensaje += '<div id="divMensajeModal" name="divMensajeModal" align="center"></div>';

    mensaje += '<div id="divCuentaFormada" name="divCuentaFormada" align="center"></div>';

    var ocultarIdentificador = "";
    if (validarIdentificador == 0) {
        ocultarIdentificador = 'style="display: none;"';
    }
    //// se removió ' + urDefaultIde + ' del value de txtUR

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!        Se agrego en class el hide,            !!
    //!!          No aplica en est aparte              !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    mensaje += '<div class="row" id="divIdentificador" name="divIdentificador">\
                    <div class="col-md-4 col-xs-12">\
                        <div class="col-md-3 col-xs-12">\
                            <span><label>UR: </label></span>\
                        </div>\
                        <div class="col-md-9 col-xs-12">\
                            <select id="txtUR" name="txtUR" class="form-control selectUnidadNegocio" onchange="">\
                                <option value="-1">Seleccionar...</option>\
                            </select>\
                        </div>\
                        <!--<component-text-label label="UR:" id="txtUR" name="txtUR" maxlength="3" placeholder="UR" title="UR" value="" onchange=""></component-text-label>-->\
                    </div>\
                    <div class="col-md-4 col-xs-12">\
                        <div class="col-md-3 col-xs-12" style="vertical-align: middle;">\
                            <span><label>UE: </label></span>\
                        </div>\
                        <div class="col-md-9 col-xs-12">\
                            <select id="txtUE" name="txtUE" class="form-control selectUnidadEjecutora" onchange="">\
                                <option value="-1">Seleccionar...</option>\
                            </select>\
                        </div>\
                        <!--<component-number-label label="UE:" id="txtUE" name="txtUE" maxlength="2" placeholder="UE" title="UE" onchange="fnFormarCuentaContable()"></component-number-label>-->\
                    </div>\
                    <div class="col-md-4 col-xs-12 hide">\
                        <div class="col-md-3 col-xs-12" style="vertical-align: middle;">\
                            <span><label>PP: </label></span>\
                        </div>\
                        <div class="col-md-9 col-xs-12">\
                            <select id="txtPP" name="txtPP" class="form-control selectProgramaPresupuestario" onchange="fnFormarCuentaContable()">\
                                <option value="-1">Seleccionar...</option>\
                            </select>\
                        </div>\
                        <!--<component-text-label label="Programa Presup:" id="txtPP" name="txtPP" maxlength="4" placeholder="Programa Presupuestario" title="Programa Presupuestario" onchange="fnFormarCuentaContable()"></component-text-label>-->\
                    </div>\
                </div>';

    mensaje += '<div class="row clearfix" id="divSelectGenero" name="divSelectGenero" >\
                  <div class="col-md-2 col-xs-12">\
                      <span><label>Género: </label></span>\
                  </div>\
                  <div class="col-md-9 col-xs-12">\
                      <select id="selectGenero" name="selectGenero" \
                      class="form-control selectGenero" onchange="fnCambioSelectGenero()">\
                      ' + optionsGenero + '\
                      </select>\
                  </div>\
                  <div class="col-md-1 col-xs-12">\
                    <button type="button" id="btnAddGenero" name="btnAddGenero" \
                    onclick="fnCrearCuentaNivel4(\'' + nombreNivel1 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-plus" \
                    style="font-weight: bold;"></button>\
                    <button type="button" id="btnRemGenero" name="btnRemGenero" \
                    onclick="fnBorrarCuentaNivel4(\'' + nombreNivel1 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-trash" \
                    style="font-weight: bold; display: none;"></button>\
                  </div>\
              </div>';
    //mensaje += '<br>';

    mensaje += '<div class="row clearfix" id="divSelectGrupo" name="divSelectGrupo">\
                  <div class="col-md-2 col-xs-12">\
                      <span><label>Grupo: </label></span>\
                  </div>\
                  <div class="col-md-9 col-xs-12">\
                      <select id="selectGrupo" name="selectGrupo" \
                      class="form-control selectGrupo" onchange="fnCambioSelectGrupo()" disabled="true">\
                      ' + optionsGrupo + '\
                      </select>\
                  </div>\
                  <div class="col-md-1 col-xs-12">\
                    <button type="button" id="btnAddGrupo" name="btnAddGrupo" \
                    onclick="fnCrearCuentaNivel4(\'' + nombreNivel2 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-plus" \
                    style="font-weight: bold; display: none;"></button>\
                    <button type="button" id="btnRemGrupo" name="btnRemGrupo" \
                    onclick="fnBorrarCuentaNivel4(\'' + nombreNivel2 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-trash" \
                    style="font-weight: bold; display: none;"></button>\
                  </div>\
              </div>';
    //mensaje += '<br>';

    mensaje += '<div class="row clearfix" id="divSelectRubro" name="divSelectRubro">\
                  <div class="col-md-2 col-xs-12">\
                      <span><label>Rubro: </label></span>\
                  </div>\
                  <div class="col-md-9 col-xs-12">\
                      <select id="selectRubro" name="selectRubro" \
                      class="form-control selectRubro" onchange="fnCambioSelectRubro()" disabled="true">\
                      ' + optionsRubro + '\
                      </select>\
                  </div>\
                  <div class="col-md-1 col-xs-12">\
                    <button type="button" id="btnAddRubro" name="btnAddRubro" \
                    onclick="fnCrearCuentaNivel4(\'' + nombreNivel3 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-plus" \
                    style="font-weight: bold; display: none;"></button>\
                  </div>\
                    <button type="button" id="btnRemRubro" name="btnRemRubro" \
                    onclick="fnBorrarCuentaNivel4(\'' + nombreNivel3 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-trash" \
                    style="font-weight: bold; display: none;"></button>\
              </div>';
    //mensaje += '<br>';

    mensaje += '<div class="row clearfix" id="divSelectCuenta" name="divSelectCuenta">\
                  <div class="col-md-2 col-xs-12">\
                      <span><label>Cuenta: </label></span>\
                  </div>\
                  <div class="col-md-9 col-xs-12">\
                      <select id="selectCuenta" name="selectCuenta" \
                      class="form-control selectCuenta" onchange="fnCambioSelectCuenta()" disabled="true">\
                      ' + optionsCuenta + '\
                      </select>\
                  </div>\
                  <div class="col-md-1 col-xs-12">\
                    <button type="button" id="btnAddCuenta" name="btnAddCuenta" \
                    onclick="fnCrearCuentaNivel4(\'' + nombreNivel4 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-plus" \
                    style="font-weight: bold; display: none;"></button>\
                    <button type="button" id="btnRemCuenta" name="btnRemCuenta" \
                    onclick="fnBorrarCuentaNivel4(\'' + nombreNivel4 + '\')" class="btn btn-default botonVerde glyphicon glyphicon-trash" \
                    style="font-weight: bold; display: none;"></button>\
                  </div>\
              </div>';
    //mensaje += '<br>';

    //***********************
    mensaje += '<div class="row clearfix" id="divNewNivel5" name="divNewNivel5">\
                    <div class="col-md-2 col-xs-12">\
                        <span><label id="labelCuentaNew" name="labelCuentaNew">Nivel 5:</label></span>\
                    </div>\
                    <div class="col-md-3 col-xs-12">\
                        <select id="txtSubcuenta" name="txtSubcuenta" class="txtSubcuenta" placeholder="Nivel5" \
                        title="Nivel5" \
                        onchange="fnCambioSelectNiveles(5)" \
                        style="width: 100%;" disabled/>\
                    </div>\
                    <!--<div class="col-md-3 col-xs-12">\
                        <input type="text" id="txtSubcuenta" name="txtSubcuenta" value="" placeholder="Nivel5" \
                        title="Nivel5" onkeypress="return soloNumeros(event)" \
                        onchange="fnFormarCuentaContable()" \
                        onkeyup="this.onchange();" oninput="this.onchange();" \
                        class="form-control" style="width: 100%;" />\
                    </div>-->\
                    <div class="col-md-3 col-xs-12">\
                        <input type="text" id="txtDescripcion" name="txtDescripcion" value="" placeholder="Descripción" \
                        title="Descripción" class="form-control" style="width: 100%;" />\
                    </div>\
                    <div class="col-md-3 col-xs-12">\
                        <select id="selectNaturaleza" name="selectNaturaleza" \
                        class="form-control selectNaturaleza" >\
                        <option value="">Seleccionar...</option>\
                        <option value="1">Deudora</option>\
                        <option value="-1">Acreedora</option>\
                        <option value="2">Deudora/Acreedora</option>\
                        </select>\
                    </div>\
                </div>\
                ';

    // Datos niveles 6 y adelante. Crear formulario
    for (var x = 6; x <= numeroNiveles; x++) {
        mensaje += fnAgregarNivelCuenta(x);
    }

    mensaje += '<div id="divNivelesMayor5" class="row" name="divNivelesMayor5"></div>';

    // mensaje += '<div align="center" id="divBotonesP" name="divBotonesP">\
    // <button type="button" id="btnGuardar" name="btnGuardar" onclick="fnGuardarCuenta()" class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk" style="font-weight: bold;">&nbsp;Guardar</button>\
    // <button type="button" id="btnRestaurarNuevo" name="btnRestaurarNuevo" onclick="fnAgregarModificarCuenta()" class="btn btn-default botonVerde glyphicon glyphicon-refresh" style="font-weight: bold;">&nbsp;Restaurar</button>\
    // <button type="button" id="btnAgregarNivel" name="btnAgregarNivel" onclick="fnAgregarNivelCuenta()" class="btn btn-default botonVerde glyphicon glyphicon-plus" style="font-weight: bold;"> Agregar Nivel</button>\
    // </div>';

    mensaje += '<div align="center" id="divBotonesP" name="divBotonesP">\
    <component-button type="button" id="btnGuardar" name="btnGuardar" onclick="fnGuardarCuentaSpinner()" class="glyphicon glyphicon-floppy-disk" value="Guardar"></component-button>\
    <component-button type="button" id="btnRestaurarNuevo" name="btnRestaurarNuevo" onclick="fnAgregarModificarCuenta()" class="glyphicon glyphicon-refresh" value="Restaurar"></component-button>\
    <component-button type="button" id="btnAgregarNivel" name="btnAgregarNivel" onclick="fnAgregarNivelCuenta()" class="glyphicon glyphicon-plus" value="Agregar Nivel"></component-button>\
    </div>';

    mensaje += '</div>';

    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneral(4, titulo, mensaje);

    $("#btnAgregar").prop("disabled", false);

    $("#btnGuardar").prop("disabled", true);

    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".selectGenero");
    fnFormatoSelectGeneral(".selectGrupo");
    fnFormatoSelectGeneral(".selectRubro");
    fnFormatoSelectGeneral(".selectCuenta");
    fnFormatoSelectGeneral(".selectNaturaleza");
    fnFormatoSelectGeneral(".txtSubcuenta");
    ////fnFormatoSelectSublvl($("#txtSubcuenta"))
    setTimeout(function () {
        fnEjecutarVueGeneral('divBotonesP');
        fnEjecutarVueGeneral('divIdentificador');
        fnCargaURUEPP();
        //$('#txtUR').multiselect('select', urDefaultIde);
    }, 200);

    $("#btnAgregarNivel").hide();
}

function fnAgregarNivelCuenta(numeroNivelesParametro=0) {
    ////console.log("fnAgregarNivelCuenta");
    var mensaje = '';
    var x = 0;
    if (numeroNivelesParametro == 0) {
        ////console.log("numeroNivelesParametro boton");
        x = numeroNiveles + 1;
        if(x>6){
            $("#btnRemCuenta"+(numeroNiveles)).hide();
        }
        numeroNiveles++;
    } else {
        ////console.log("automatico");
        x = numeroNivelesParametro;
    }
    // console.log("nivel "+x);
    mensaje += '<div class="form-inline clearfix" id="divNewNivel' + x + '" name="divNewNivel' + x + '">\
                    <div class="col-md-2 col-xs-12">\
                        <span><label style="height: 32px;" id="labelCuentaNew' + x + '" name="labelCuentaNew' + x + '">Nivel ' + x + ':</label></span>\
                    </div>\
                    <div class="col-md-3 col-xs-12">\
                        <select id="txtSubcuenta' + x + '" name="txtSubcuenta' + x + '"  class="txtSubcuenta' + x + '" placeholder="Nivel ' + x + '" \
                        title="Nivel ' + x + '" \
                        onchange="fnCambioSelectNiveles('+x+')" \
                        class="form-control" style="width: 100%;" ></select>\
                    </div>\
                    <!--<div class="col-md-3 col-xs-12">\
                        <input type="text" id="txtSubcuenta'+ x +'" name="txtSubcuenta'+ x +'" value="" placeholder="Nivel'+ x +'" \
                        title="Nivel '+ x +'" onkeypress="return soloNumeros(event)" \
                        onchange="fnFormarCuentaContable()" \
                        onkeyup="this.onchange();" oninput="this.onchange();" \
                        class="form-control" style="width: 100%;" />\
                    </div>-->\
                    <div class="col-md-3 col-xs-12">\
                        <input type="text" id="txtDescripcion' + x + '" name="txtDescripcion' + x + '" value="" placeholder="Descripción" \
                        title="Descripción" class="form-control" style="width: 100%;" />\
                    </div>\
                    <div class="col-md-3 col-xs-12">\
                        <select id="selectNaturaleza' + x + '" name="selectNaturaleza' + x + '" \
                        class="form-control selectNaturaleza" >\
                        <option value="">Seleccionar...</option>\
                        <option value="1">Deudora</option>\
                        <option value="-1">Acreedora</option>\
                        <option value="2">Deudora/Acreedora</option>\
                        </select>\
                    </div>\
                    <div class="col-md-1 col-xs-12">\
                        <button type="button" id="btnRemCuenta' + x + '" name="btnRemCuenta' + x + '" \
                        onclick="fnBorrarNivel()" class="btn btn-default botonVerde glyphicon glyphicon-minus" \
                        style="font-weight: bold;"></button>\
                    </div>\
                    <span style="height: 37px;"></span>\
                </div>\
                ';
    if (numeroNivelesParametro == 0) {
        $("#divNivelesMayor5").append("" + mensaje);
        fnFormatoSelectGeneral(".selectNaturaleza");
        readSubsCreateNivelFields($("option:selected","#txtSubcuenta"+(numeroNiveles>6?numeroNiveles-1:'')).data('subs'),numeroNiveles);//subs
        ////fnFormatoSelectSublvl($('#txtSubcuenta' + (numeroNiveles)))
    } else {
        return mensaje;
    }

    if ($("#selectGenero").val() == 7 || $("#selectGenero").val() == 8) {
        // Habilitar naturaleza para genero 7 y 8
        fnHabilitarNaturaleza();
    } else {
        // Deshabilitar naturaleza para genero diferente de 7 y 8
        fnDeshabilitarNaturaleza(heredaNaturalezaGeneral);
    }
    $("#btnAgregarNivel").hide();
}

function fnFormatoSelectSublvl(selobj){
    selobj.multiselect();
}

function fnModificarCuenta(cuenta, nombre, naturaleza, ur="", ue="", pp="", nivel, estatusCuenta) {
    dataObj = {
        option: 'obtenerCuenta',
        cuenta: cuenta
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                nombre = data.contenido.nombre;
                naturaleza = data.contenido.naturaleza;
                nivel = data.contenido.nivel;
                estatusCuenta = data.contenido.estatusCuenta;
                ur= data.contenido.tagref;
                ue= data.contenido.ln_clave;
            } else {
                $("#ModalGeneralTam").width('600');
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje);
            }
        })
        .fail(function (result) {
            // console.log("ERROR");
            // console.log( result );
        });

    console.log("cuenta: " + cuenta);
    console.log("nombre: " + nombre);
    console.log("naturaleza: " + naturaleza);
    console.log("ur: " + ur);
    console.log("ue: " + ue);
    console.log("pp: " + pp);

    cuentaAgregarModificar = cuenta;

    var mensaje = '<div class="row">';

    mensaje += '<div class="col-md-12 col-xs-12" align="center">';

    mensaje += '<div id="divMensajeModal" name="divMensajeModal" align="center"></div>';

    mensaje += '<h3><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i> ' + cuenta + '</h3>';

    var ocultarIdentificador = "";
    if (validarIdentificador == 0) {
        ocultarIdentificador = 'style="display: none;"';
    }
    if (ur.trim() == '') {
        ////ur = urDefaultIde;
    }

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!        Se agrego en class el hide,            !!
    //!!          No aplica en est aparte              !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    mensaje += '<div class="row" id="divIdentificador" name="divIdentificador">\
                    <div class="col-md-4 col-xs-12">\
                        <div class="form-inline row">\
                            <div class="col-md-3">\
                                <span><label>UR: </label></span>\
                            </div>\
                            <div class="col-md-9">\
                                <select id="txtUR" name="txtUR" class="form-control selectUnidadNegocio" onchange="">\
                                    <option value="-1">Seleccionar...</option>\
                                </select>\
                            </div>\
                        </div>\
                        <!--<component-text-label label="UR:" id="txtUR" name="txtUR" maxlength="3" placeholder="UR" title="UR" value="' + ur + '"></component-text-label>-->\
                    </div>\
                    <div class="col-md-4 col-xs-12">\
                        <div class="form-inline row">\
                            <div class="col-md-3" style="vertical-align: middle;">\
                                <span><label>UE: </label></span>\
                            </div>\
                            <div class="col-md-9">\
                                <select id="txtUE" name="txtUE" class="form-control selectUnidadEjecutora" onchange="">\
                                    <option value="-1">Seleccionar...</option>\
                                </select>\
                            </div>\
                        </div>\
                        <!--<component-number-label label="UE:" id="txtUE" name="txtUE" maxlength="2" placeholder="UE" title="UE" value="' + ue + '"></component-number-label>-->\
                    </div>\
                    <div class="col-md-4 col-xs-12 hide">\
                        <div class="form-inline row">\
                            <div class="col-md-3" style="vertical-align: middle;">\
                                <span><label>PP: </label></span>\
                            </div>\
                            <div class="col-md-9">\
                                <select id="txtPP" name="txtPP" class="form-control selectProgramaPresupuestario" onchange="fnFormarCuentaContable()">\
                                    <option value="-1">Seleccionar...</option>\
                                </select>\
                            </div>\
                        </div>\
                        <!--<component-text-label label="Programa Presup:" id="txtPP" name="txtPP" maxlength="4" placeholder="Programa Presupuestario" title="Programa Presupuestario" value="' + pp + '"></component-text-label>-->\
                    </div>\
                </div>';

    mensaje += '<div class="form-inline row">\
                    <div class="col-md-3 col-xs-12" >\
                        <span><label>Descripción:</label></span>\
                    </div>\
                    <div class="col-md-9 col-xs-12">\
                        <input type="text" id="txtDescripcion" name="txtDescripcion" value="' + nombre + '" placeholder="Descripción" \
                        title="Descripción" class="form-control" style="width: 100%;" />\
                    </div>\
                </div>';
    mensaje += '<br>';

    var selectedDeudora = "";
    if (naturaleza == '1') {
        selectedDeudora = " selected";
    }
    var selectedAcreedora = "";
    if (naturaleza == '-1') {
        selectedAcreedora = " selected";
    }
    var selectedDeudoraAcreedora = "";
    if (naturaleza == '2') {
        selectedDeudoraAcreedora = " selected";
    }

    mensaje += '<div class="form-inline row">\
                  <div class="col-md-3">\
                      <span><label>Naturaleza: </label></span>\
                  </div>\
                  <div class="col-md-9">\
                      <select id="selectNaturaleza" name="selectNaturaleza" \
                      class="form-control selectNaturaleza" >\
                      <option value="">Seleccionar...</option>\
                      <option value="1"' + selectedDeudora + '>Deudora</option>\
                      <option value="-1"' + selectedAcreedora + '>Acreedora</option>\
                      <option value="2"' + selectedDeudoraAcreedora + '>Deudora/Acreedora</option>\
                      </select>\
                  </div>\
              </div>';
    mensaje += '<br>';

    if(cuenta.split(".").length>4){
        window.estatusOriginal = ( estatusCuenta==1 ? 1 : 2 );
        var selectedActivo = "";
        if (estatusCuenta == '1') {
            selectedActivo = " selected";
        }
        var selectedInactivo = "";
        if (estatusCuenta == '0') {
            selectedInactivo = " selected";
        }
        mensaje += '<div class="form-inline row">\
                    <div class="col-md-3 col-xs-12" >\
                        <span><label>Estatus:</label></span>\
                    </div>\
                    <div class="col-md-9 col-xs-12">\
                        <select id="selectEstatus" name="selectEstatus" \
                        class="form-control selectEstatus">\
                            <option value="1"' + selectedActivo + '>Activo</option>\
                            <option value="2"' + selectedInactivo + '>Inactivo</option>\
                        </select>\
                    </div>\
                </div>';
        mensaje += '<br>';
    }

    mensaje += '<button type="button" id="btnModificar" name="btnModificar" \
    onclick="fnActualizarCuenta()" class="btn btn-default botonVerde glyphicon glyphicon-floppy-disk" \
    style="font-weight: bold;">&nbsp;Actualizar</button>';

    if(cuenta.split(".").length>444){
        mensaje += '<button type="button" id="btnModificarEstatus" name="btnModificarEstatus" \
        onclick="fnCambiarEstatus(['+!estatusCuenta+',false])" class="btn btn-default botonVerde glyphicon glyphicon-'+( estatusCuenta=="0" ? "ok-sign" : "remove-sign" )+'" \
        style="font-weight: bold;">&nbsp;'+( !estatusCuenta ? "Re" : "Des" )+'activar</button>';
    }

    if(cuenta.split(".").length>666){
        mensaje += '<button type="button" id="btnModificarMultiEstatus" name="btnModificarMultiEstatus" \
        onclick="fnCambiarEstatus(['+!estatusCuenta+',true])" class="btn btn-default botonVerde glyphicon glyphicon-'+( estatusCuenta=="0" ? "ok-sign" : "remove-sign" )+'" \
        style="font-weight: bold;">&nbsp;'+( !estatusCuenta ? "Re" : "Des" )+'activar con todas las sub cuentas</button>';
    }
    /*if( (estatusCuenta&&numSubCuentasActivas)||(!estatusCuenta&&numSubCuentasInactivas) ){
    }*/

    mensaje += '</div>';

    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneral(4, titulo, mensaje);

    // Aplicar formato del SELECT
    fnFormatoSelectGeneral(".selectNaturaleza");
    if(cuenta.split(".").length>4){
        fnFormatoSelectGeneral(".selectEstatus");
    }

    setTimeout(function () {
        fnEjecutarVueGeneral('divIdentificador');
        fnCargaURUEPP();

        $('#txtUR').multiselect('select', ur);
        $('#txtUE').multiselect('select', ue);
        $('#txtPP').multiselect('select', pp);

        if(cuenta.toString().substring(0,1)!=7&&cuenta.toString().substring(0,1)!=8){
            fnDeshabilitarNaturaleza(naturaleza);
        }
        $('#divIdentificador').append('<input type="text" name="cuentaAModificar" id="cuentaAModificar" value="'+cuenta+'" class="hidden"/>');
        $('#divIdentificador').append('<input type="text" name="cuentaAModificarNivel" id="cuentaAModificarNivel" value="'+nivel+'" class="hidden"/>');
    }, 200);

    if(nivel<5){
        $("#divIdentificador").hide();
    }
}

function fnValidarDiferenciador(UR,UE,PP){
    return !( (UR!="-1"&&UE!="-1"&&PP!="-1")||(UR=="-1"&&UE=="-1"&&PP=="-1") ) ? true : false;
}

function fnCambiarEstatus(parametros){
    var tipoAccion = parametros[0],
        multiNivel = parametros[1];
    $("#btnModificarEstatus").hide();
    $("#btnModificarMultiEstatus").hide();
    $("#ModalGeneral_Advertencia").empty();

    fnAccionCambiarEstatus(parametros);
}

function fnAccionCambiarEstatus(parametros){
    var tipoAccion = parametros[0],
        multiNivel = parametros[1];
    dataObj = {
        option: 'cambiarEstatusCuenta',
        cuenta: $("#cuentaAModificar").val()||parametros[2],
        nombre: $("#txtDescripcion").val()||parametros[3],
        naturaleza: $("#selectNaturaleza").val()||parametros[4],
        nivel: $("#cuentaAModificarNivel").val()||parametros[5],
        tipoAccion: ( tipoAccion ? 1 : 0 ),
        multiNivel: ( multiNivel ? 1 : 0 ),
        reactivacionMultiple: window.ReactivacionMultiple
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            $("#ModalGeneralTam").width('600');

            //console.log("Bien");
            if (data.result) {
                $("#txtDescripcion").attr('readonly', true);
                $("#btnModificar").hide();
                //// Línea activa antes del último cambio: fnActualizarCuenta();
                //fnObtenerCuentasCont(); // Se deshabilita al reload del grid después de una consulta exitosa
                muestraModalGeneral(1, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje);

                if(tipoAccion&&window.ReactivacionMultiple!="1"||!tipoAccion&&!multiNivel){
                    $("#divContenidoTabla").jqxGrid('setcellvalue', $('#divContenidoTabla').jqxGrid('getselectedcell')['rowindex'], "estatus", ( tipoAccion ? "Activo" : "Inactivo" ) );
                    var celdaModificar = $("#divContenidoTabla").jqxGrid('getcellvalue', $('#divContenidoTabla').jqxGrid('getselectedcell')['rowindex'], "Modificar").replace(','+( tipoAccion ? 0 : 1 )+')"',','+( tipoAccion ? 1 : 0 )+')"');
                    $("#divContenidoTabla").jqxGrid('setcellvalue', $('#divContenidoTabla').jqxGrid('getselectedcell')['rowindex'], "Modificar", celdaModificar );
                }
            } else {
                //ocultaCargandoGeneral();
                // muestraMensajeTiempo('Ocurrio un problema al realizar la actualización de la información', 3, 'ModalGeneral_Advertencia', 5000);
                //// Línea activa antes del último cambio: var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                //muestraModalGeneral(3, titulo, data.Mensaje);
                //// Línea activa antes del último cambio: muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
                //fnObtenerCuentasCont();

                if(!multiNivel){
                    muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje,'','fnCambiarEstatus',( tipoAccion ? true : false)+',true,\''+$("#cuentaAModificar").val()+'\''+',\''+$("#txtDescripcion").val()+'\''+',\''+$("#selectNaturaleza").val()+'\''+',\''+$("#cuentaAModificarNivel").val()+'\'');
                    if(tipoAccion){
                        window.ReactivacionMultiple = 1;
                        $("#ModalGeneral_Mensaje").append('<h6><label style="font-weight: normal;"> <input type="checkbox" onClick="window.ReactivacionMultiple = ( this.checked ? 2 : 1 );"> No, reactivar <strong>SIN</strong> las subcuentas.</label></h6>');
                    }
                }else{
                    muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje);
                }
            }
        })
        .fail(function (result) {
            //ocultaCargandoGeneral();
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnActualizarCuenta() {
    ////console.log("fnActualizarCuenta");
    $("#ModalGeneral_Advertencia").empty();

    /*if (  validarIdentificador == 1 && fnValidarDiferenciador($("#txtUR").val(),$("#txtUE").val(),$("#txtPP").val()) && $("#divIdentificador").is(":visible")  ){
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Complete (o elimine) la información del Diferenciador (UR, UE y Programa Presupuestario)</p>';
        muestraMensajeTiempo(mensaje, 3, 'ModalGeneral_Advertencia', 5000);
        return true;
    }

    if(!$("#divIdentificador").is(":visible")){
        if($("#txtUE").val()=="-1"||$("#txtPP").val()=="-1"){
            $("#txtUR").multiselect('select','-1');
        }
    }*/

    //Opcion para operacion
    //// Se reemplazó cuenta: cuentaAgregarModificar, porque no pasaba el valor
    /*
        //// Se eliminaron estos campos porque ya no son usados
        txtUR: $("#txtUR").val(),
        txtUE: $("#txtUE").val(),
        txtPP: $("#txtPP").val(),
    */
    dataObj = {
        option: 'actualizarCuenta',
        cuenta: $("#cuentaAModificar").val(),
        nombre: $("#txtDescripcion").val(),
        naturaleza: $("#selectNaturaleza").val(),
        nivel: $("#cuentaAModificarNivel").val(),
        unidadnegocio: $("#txtUR").val(),
        unidadejecutora: $("#txtUE").val()
    };

    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
        .done(function (data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                //ocultaCargandoGeneral();
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                //$("#ModalGeneralTam").width('600');
                //muestraModalGeneral(2, titulo, data.Mensaje);
                muestraMensajeTiempo(data.Mensaje, 1, 'ModalGeneral_Advertencia', 5000);
                //fnObtenerCuentasCont(); // Se deshabilita al reload del grid después de una consulta exitosa
                $("#divContenidoTabla").jqxGrid('setcellvalue', $('#divContenidoTabla').jqxGrid('getselectedcell')['rowindex'], "accountname", $("#txtDescripcion").val() );
                $("#divContenidoTabla").jqxGrid('setcellvalue', $('#divContenidoTabla').jqxGrid('getselectedcell')['rowindex'], "naturaleza", document.getElementById('selectNaturaleza').options[document.getElementById('selectNaturaleza').selectedIndex].innerHTML );
                if(window.estatusOriginal!=""&&window.estatusOriginal!=$("#selectEstatus").val()){
                    fnCambiarEstatus([( $("#selectEstatus").val()=="1" ? true : false ),false]);
                }
            } else {
                //ocultaCargandoGeneral();
                // muestraMensajeTiempo('Ocurrio un problema al realizar la actualización de la información', 3, 'ModalGeneral_Advertencia', 5000);
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                //muestraModalGeneral(3, titulo, data.Mensaje);
                muestraMensajeTiempo(data.Mensaje, 3, 'ModalGeneral_Advertencia', 5000);
                //fnObtenerCuentasCont();
            }
        })
        .fail(function (result) {
            //ocultaCargandoGeneral();
            // console.log("ERROR");
            // console.log( result );
        });
}

function fnCargaURUEPP(){
    // Líneas tomadas desde footer_Index.inc

    var unRegistroURGeneral = 0;
    // Inicio Json para Multiselect
    if (document.querySelector(".selectUnidadNegocio")) {
      // Inicio URG
      var seleccionado= "";
      var seltodos= false;
      var unidad= "";


      if (!$(".selectUnidadNegocio").prop("multiple") && $(".selectUnidadNegocio").data("todos")) {
        seltodos= true;
      }

      if ($(".selectUnidadNegocio").data("unidad") != "" &&  typeof $(".selectUnidadNegocio").data("unidad") != "undefined") {
        unidad= $(".selectUnidadNegocio").data("unidad");
      }

      //Opcion para operacion
      dataObj = {
          option: 'mostrarUnidadNegocio'
        };
      //Obtener datos de las bahias
      $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/componentes_modelo.php",
            data:dataObj
        })
      .done(function( data ) {
          //console.log("Bien");
          if(data.result){
              //Si trae informacion
              dataJson = data.contenido.datos;
              var contenido = "";
              //console.log("datos: "+dataJson.length);

              if (dataJson.length == 1) {
                unRegistroURGeneral = 1;
                seleccionado= " selected ";
              } else if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
              }
              // console.log("seleccionado: "+seleccionado);
              // console.log("unidad: "+unidad);
              for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                  if (dataJson[info].tagref == unidad) {
                    contenido += "<option value='"+dataJson[info].tagref+"' selected>"+dataJson[info].tagdescription+"</option>";
                  } else {
                    contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
                  }
                } else {
                  contenido += "<option value='"+dataJson[info].tagref+"'"+seleccionado+">"+dataJson[info].tagdescription+"</option>";
                }
              }

              $('.selectUnidadNegocio').append(contenido);
          }else{
              // console.log("ERROR Modelo");
              // console.log( JSON.stringify(data) );
          }
      })
      .fail(function(result) {
          // console.log("ERROR");
          // console.log( result );
      });
      // Fin URG
    }

    if (document.querySelector(".selectUnidadEjecutora")) {
      // Inicio URG
      var seleccionado= "";
      var seltodos= false;
      var unidad= "";

      var pathname =  window.location.pathname;

      // Para que en la ventana de viaticos en el select de UE aparezca la opcion "Sin seleccion" 
      if( window.location.pathname.indexOf("viaticos.php") !== -1) {
         seltodos = true;
         ////console.log("entro pathname condicion");
      }

      if (!$(".selectUnidadEjecutora").prop("multiple") && $(".selectUnidadEjecutora").data("todos")) {
        seltodos= true;
      }

      if ($(".selectUnidadEjecutora").data("unidad") != "" &&  typeof $(".selectUnidadEjecutora").data("unidad") != "undefined") {
        unidad= $(".selectUnidadEjecutora").data("unidad");
      }

      //Opcion para operacion
      dataObj = {
              option: 'mostrarUnidadEjecutora'
            };
      //Obtener datos de las bahias
      $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/componentes_modelo.php",
            data:dataObj
        })
      .done(function( data ) {
          
          if(data.result){
              //Si trae informacion
              dataJson = data.contenido.datos;

        //  dataJson.sort(function (a, b) {
        //   var aValue = parseInt(a.ue);
        //   var bValue = parseInt(b.ue);
        //   // ASC
        //       return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
        //   // DESC
        //   //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
        // });


              var contenido = "";
              //console.log("datos: "+dataJson.length);

              if (dataJson.length == 1) {
                seleccionado= " selected ";
              } else if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
              }
              // console.log("seleccionado: "+seleccionado);
              // console.log("unidad: "+unidad);
              for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                  if (dataJson[info].tagref == unidad) {
                    contenido += "<option value='"+dataJson[info].ue+"' selected>"+dataJson[info].uedescription+"</option>";
                  } else {
                    contenido += "<option value='"+dataJson[info].ue+"'>"+dataJson[info].uedescription+"</option>";
                  }
                } else {
                  contenido += "<option value='"+dataJson[info].ue+"'"+seleccionado+">"+dataJson[info].uedescription+"</option>";
                }
              }

              ////console.log("dataJson.length: "+dataJson.length);
              ////console.log("seltodos: "+seltodos);
              //console.log("opcionDefault: "+opcionDefault);
              ////console.log("contenido: "+contenido);

              $('.selectUnidadEjecutora').append(contenido);
          }else{
              // console.log("ERROR Modelo");
              // console.log( JSON.stringify(data) );
          }
      })
      .fail(function(result) {
          // console.log("ERROR");
          // console.log( result );
      });
      // Fin URG
    }

    /*if(document.querySelector(".selectProgramaPresupuestario")) {
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: "modelo/componentes_modelo.php",
            data:{option:'muestraProgramaPresupuestario'}
        }).done(function(res){
            if(!res.result){return;}
            var options='';
            $.each(res.contenido,function(index, el) {
                options += `<option value="${el.val}">${el.text}</option>`;
            });
            $('.selectProgramaPresupuestario').append(options);
        }).fail(function(res){
            throw new Error('No se logro cargar la información del componente reasignación');
        });
    }*/

    fnFuncionSelect();
}

// Nuevas funciones para los primeros 4 niveles, llaman el movimiento que les corresponde y además la función de bloqueo
function fnCambioSelectGenero(){
    fnBloquearNivel("Genero",true);
    fnBloquearNivel("Grupo",false);
        $('#selectGrupo').empty();
        $("#selectGrupo").multiselect('rebuild');
        fnFormatoSelectGeneral(".selectGrupo");
        $('#selectRubro').empty();
        $("#selectRubro").multiselect('rebuild');
        fnFormatoSelectGeneral(".selectRubro");
        $('#selectCuenta').empty();
        $("#selectCuenta").multiselect('rebuild');
        fnFormatoSelectGeneral(".selectCuenta");
        $("#txtSubcuenta").multiselect('select','');
        $('#txtSubcuenta').multiselect('disable');
        $("#txtDescripcion").val("");
        $("#txtDescripcion").removeAttr("readonly");
        $("#selectNaturaleza").multiselect('select','');
        fnCambioSelectNiveles(5);
        fnFormarCuentaContable();
        $("#ModalGeneral_Advertencia").empty();
    fnCambioGenero();
    $("#btnAddGrupo").show();
    //$("#btnRemGenero").show();
    $("#btnRemGenero").attr('disabled', false);
}
function fnCambioSelectGrupo(){
    fnBloquearNivel("Grupo",true);
    fnBloquearNivel("Rubro",false);
        $('#selectRubro').empty();
        $("#selectRubro").multiselect('rebuild');
        fnFormatoSelectGeneral(".selectRubro");
        $('#selectCuenta').empty();
        $("#selectCuenta").multiselect('rebuild');
        fnFormatoSelectGeneral(".selectCuenta");
        $("#txtSubcuenta").multiselect('select','');
        $('#txtSubcuenta').multiselect('disable');
        $("#txtDescripcion").val("");
        $("#txtDescripcion").removeAttr("readonly");
        fnCambioSelectNiveles(5);
        fnFormarCuentaContable();
        $("#ModalGeneral_Advertencia").empty();
    fnCambioGrupo();
    $("#btnAddRubro").show();
    $("#btnRemGenero").hide();
    //$("#btnRemGrupo").show();
    $("#btnRemGrupo").attr('disabled', false);
    fnBloquearCuenta("Cuenta",true);
    $("#btnAddCuenta").attr('disabled', false);
}
function fnCambioSelectRubro(){
    fnBloquearNivel("Rubro",true);
    fnBloquearNivel("Cuenta",false);
        $('#selectCuenta').empty();
        $("#selectCuenta").multiselect('rebuild');
        fnFormatoSelectGeneral(".selectCuenta");
        $("#txtSubcuenta").multiselect('select','');
        $('#txtSubcuenta').multiselect('disable');
        $("#txtDescripcion").val("");
        $("#txtDescripcion").removeAttr("readonly");
        fnCambioSelectNiveles(5);
        fnFormarCuentaContable();
        $("#ModalGeneral_Advertencia").empty();
    fnCambioRubro();
    $("#btnAddCuenta").show();
    $("#btnRemGrupo").hide();
    //$("#btnRemRubro").show();
    $("#btnRemRubro").attr('disabled', false);
    fnBloquearCuenta("Cuenta",false);
}
function fnCambioSelectCuenta(){
    fnBloquearNivel("Cuenta",true);
    fnCambioCuenta();
    $("#btnRemRubro").hide();
    //$("#btnRemCuenta").show();
    $("#btnRemCuenta").attr('disabled', false);
    $("#btnAgregarNivel").show();
}
function fnCambioSelectNiveles(nivelSeleccionado){
    fnFormarCuentaContable();
    for(var c=numeroNiveles;c>nivelSeleccionado;c--){
        fnBorrarNivel();
    }
    numeroNiveles = c;
}

// Función para bloquear los primeros 4 niveles conforme se va avanzando
function fnBloquearNivel(nivel,tipoAccion){
    /*$("#divSelect"+nivel).find('button').each(function(){
        $(this).attr('disabled', tipoAccion);
    });*/
    if(tipoAccion){
        $("#btnAdd"+nivel).hide();
    }else{
        $("#btnAdd"+nivel).show();
    }
}

// Función para bloquear la cuenta sin eliminar el botón de agregar nivel
function fnBloquearCuenta(nivel,tipoAccion){
    $("#divSelect"+nivel).find('button').each(function(){
        $(this).attr('disabled', tipoAccion);
    });
}

// Función para borrar el último nivel
function fnBorrarNivel(){
    if(numeroNiveles>5){
        $("#divNewNivel"+numeroNiveles).remove();
        numeroNiveles--;

        $("#divNewNivel"+numeroNiveles).find('button').each(function(){
            $(this).attr('disabled', false);
        });
        $("#divNewNivel"+numeroNiveles).find('input').each(function(){
            $(this).attr('disabled', false);
        });

        if(numeroNiveles>5){
            $("#btnRemCuenta"+(numeroNiveles)).show();
        }
        $("#btnAgregarNivel").show();
    }
}

function fnBorrarCuentaNivel4(nombreNivel){
    $("#ModalGeneral_Advertencia").empty();
    //Opcion para operacion
    dataObj = {
        option: 'cuentaAInactivar',
        tipoAlta: nombreNivel,
        cuenta: $("#select"+nombreNivel).val()
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
    .done(function (data) {
        //console.log("Bien");
        $("#ModalGeneralTam").width('600');
        if(data.result){
            //Si trae informacion
            muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje,'','fnBorrarCuenta','\''+nombreNivel+'\',\''+$("#select"+nombreNivel).val()+'\',false');
        }else{
            if(data.contenido){
                muestraModalGeneralConfirmacion(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje,'','fnBorrarCuenta','\''+nombreNivel+'\',\''+$("#select"+nombreNivel).val()+'\',true');
            }else{
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje);
            }
        }
    })
    .fail(function (result) {
        // console.log("ERROR");
        // console.log( result );
    });
}

function fnBorrarCuenta(parametros){
    //Opcion para operacion
    dataObj = {
        option: 'cambiarEstatusCuenta',
        tipoAlta: parametros[0],
        cuenta: parametros[1],
        multiNivel: parametros[2],
        tipoAccion: 0
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
    .done(function (data) {
        //console.log("Bien");
        $("#ModalGeneralTam").width('600');
        muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', data.Mensaje);
    })
    .fail(function (result) {
        // console.log("ERROR");
        // console.log( result );
    });
}

function fnMostrarDatos(){
    $("#buscarConFiltros").val("1");
    muestraCargandoGeneral();
    setTimeout(function(){ fnObtenerCuentasCont(); }, 500);
}


function fnCargaSelectGeneros(){
    //Opcion para operacion
    dataObj = {
        option: 'datosSelectGeneros'
    };
    $.ajax({
        async: false,
        cache: false,
        method: "POST",
        dataType: "json",
        url: "modelo/GLAccounts_modelo.php",
        data: dataObj
    })
    .done(function (data) {
        //console.log("Bien");
        if (data.result) {
            //Si trae informacion
            $('#busquedaGenero').empty();
            $.each(data.contenido.content, function(index, val) {
                $('#busquedaGenero').append(new Option(val.label, val.value));
            });
            fnFormatoSelectGeneral(".busquedaGenero");
            $('#busquedaGenero').multiselect('rebuild');
        }
    })
    .fail(function (result) {
        // console.log("ERROR");
        // console.log( result );
    });
}
