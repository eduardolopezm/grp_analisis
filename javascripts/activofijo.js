var datosCabms;
var categoryid; 
var cuentacontable = "";
var datosDepreciacion;
var cargartagreff = "";

$( document ).ready(function() {

    fnListaInformacionGeneral("", "#txtProveedor", "proveedor");
    
});


function esIgual(valor) {


}

function fnCambioCategoria() {
    //Obtener datos de las bahias
    $.ajax({
        async: false,
        method: "POST",
        dataType: "json",
        url: "modelo/activofijo_modelo.php",
        data: {
            // aCategoriaSeleccionada: $("#selectClaveCabms").val(),
            aCategoriaSeleccionada: $("#selectCategoriaActivo").val(),
            cargarDepreciacionDefault: true
        }
    }).done(function(data) {
        var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
        if (data.result) {

            for (var info in data.contenido) {
                $('#txtTasaDepreciacion').val(data.contenido[info].texto);
                $('#txtAniosVidaUtil').val(data.contenido[info].aniosdepreciacion);

                categoryid = data.contenido[info].categoryid;
                cuentacontable = data.contenido[info].cuentacontable;
            }

        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnCambioUnidadNegocio() {

}

function fnCambioRazonSocial() {
    muestraCargandoGeneral();
    //console.log("fnObtenerUnidadNegocio");
    // Inicio Unidad de Negocio

    legalid = $("#selectRazonSocial").val();
    //Opcion para operacion
    dataObj = {
        option: 'mostrarUnidadNegocio',
        legalid: legalid
    };
    //Obtener datos de las bahias
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/imprimirreportesconac_modelo.php",
            data: dataObj,
            async: false
        })
        .done(function(data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion

                dataJson = data.contenido.datos;
                //console.log( "dataJson: " + JSON.stringify(dataJson) );
                //alert(JSON.stringify(dataJson));
                var contenido = "<option value='0'>Seleccionar...</option>";
                for (var info in dataJson) {
                    contenido += "<option value='" + dataJson[info].tagref + "'>" + dataJson[info].tagdescription + "</option>";
                }
                $('#selectUnidadNegocio').empty();
                $('#selectUnidadNegocio').append(contenido);
                $('#selectUnidadNegocio').multiselect('rebuild');
                ocultaCargandoGeneral();

                if (cargartagreff != "")
                    $("#selectUnidadNegocio").selectpicker('val', cargartagreff);
                $("#selectUnidadNegocio").multiselect('refresh');
                $(".selectUnidadNegocio").css("display", "none");

            } else {
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) ); 
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
            ocultaCargandoGeneral();
        });
    // Fin Unidad de Negocio
}

function fnAgregar() {

    var capitulo = "";
    var concepto = "";
    var partidageneral = "";
    _MensajeValidacion = "";

    if(getVer!=0){
        return false;
    }

    proceso = "Agregar";
    msjInicio='<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El Campo ';
    msjFin=' es obligatorio.</p>';

    if (fnObtenerOption('selectUnidadNegocio') == "") _MensajeValidacion += msjInicio +' UR'+msjFin;
    if (fnObtenerOption('selectUnidadEjecutora') == "") _MensajeValidacion += msjInicio +' UE'+msjFin;
    if ($('#txtClaveBien').val() == "") _MensajeValidacion += msjInicio +' Clave de Bien'+msjFin;
    if (fnObtenerOption('selectAlmacen') == "") _MensajeValidacion += msjInicio +' Almacén '+msjFin;;
    if (fnObtenerOption('selectCategoriaActivo') == "") _MensajeValidacion += msjInicio +' Partida Especifica '+msjFin;
    if (fnObtenerOption('selectProcesoContabilizarActivo') == "" || fnObtenerOption('selectProcesoContabilizarActivo') == "'0'") _MensajeValidacion += msjInicio +' Proceso'+msjFin;
    if(fnObtenerOption('selectClaveCABMS') == ""){
        if($('#selectClaveCABMS option').size()>1){
            _MensajeValidacion += msjInicio +' Clave CABMS'+msjFin;
        }
    }

    if ($("#selectTipoBien").val() == "" || $("#selectTipoBien").val() == "0") _MensajeValidacion += msjInicio +' Tipo de Bien'+msjFin;

    if ($("#txtDescripcionCorta").val() == "") _MensajeValidacion += msjInicio +' Descripción Corta'+msjFin;
    if ($("#txtDescripcionLarga").val() == "") _MensajeValidacion += msjInicio +' Descripción Larga'+msjFin;
    
    if ($("#selectTipoPropietario").val() == "" || $("#selectTipoPropietario").val() == "0") _MensajeValidacion += msjInicio +' Inventario'+msjFin;

    if ($("#txtCosto").val() == "" || $("#txtCosto").val() == "0") _MensajeValidacion += msjInicio +' Valor Factura'+msjFin;
    
    if ($("#txtTasaDepreciacion").val() == "0" || $("#txtTasaDepreciacion").val() == "0.00" || $("#txtTasaDepreciacion").val() == "") _MensajeValidacion += msjInicio +' Tasa de Depreciación'+msjFin;
    if ($("#txtAniosVidaUtil").val() == "0" || $("#txtAniosVidaUtil").val() == "0.00") _MensajeValidacion += msjInicio +' Años de Vida Util'+msjFin;
    if ($("#txtFechaAdquisicion").val() == "") _MensajeValidacion += msjInicio +' Fecha de Adquisición'+msjFin;
    if ($("#txtFechaIncorporacionPatrimonial").val() == "") _MensajeValidacion += msjInicio +' Fecha de Incorporación'+msjFin;

    //Tipo 1 = vehiculo
    if($('#selectTipoBien').val() =="3"){
        if ($("#txtColor").val() == "") _MensajeValidacion += msjInicio +' Color'+msjFin;
        if ($("#txtPlacas").val() == "") _MensajeValidacion += msjInicio +' Placas'+msjFin;
        if ($("#txtMarca").val() == "") _MensajeValidacion += msjInicio +' Marca'+msjFin;
        if ($("#txtModelo").val() == "") _MensajeValidacion += msjInicio +' Modelo'+msjFin;
        if ($("#txtAnio").val() == "") _MensajeValidacion += msjInicio +' Año'+msjFin;
    }

    if($('#txtFechaAdquisicion').val() !="" && $('#txtFechaIncorporacionPatrimonial').val()!=""){

        var fechaAdq = $('#txtFechaAdquisicion').val();
        var resAdq = fechaAdq.split("-");

        var fechaInco = $('#txtFechaIncorporacionPatrimonial').val();
        var resInco = fechaInco.split("-");

        var fechaAdquisicion  = new Date(resAdq[2]+'-'+resAdq[1]+'-'+resAdq[0]);
        var fechaIncorporacion = new Date(resInco[2]+'-'+resInco[1]+'-'+resInco[0]);
        
        if(fechaAdquisicion > fechaIncorporacion){
            _MensajeValidacion += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>La fecha de adquisición no debe ser mayor a la fecha de incorporación patrimonial<p>';
        }
    }

    if (_MensajeValidacion != "") {
        muestraMensaje('' + _MensajeValidacion, 3, 'mensajesValidaciones', 5000);
        return false;
    }

    $('#ModalUR').modal('hide');

    if (ActivoFijoID == 0) {
        proceso = 'Agregar';
    } else {
        proceso = 'Actualizar';
    }

    var chkAsegurado = "0";

    if( $('#chkAsegurado').is(':checked') ) {
        chkAsegurado = "1";
    }



    //Opcion para operacion
    dataObj = {
        new_edit: true,
        eco: $("#txtNumeroEconomico").val(),
        Description: $("#txtDescripcionCorta").val(),
        LongDescription: $("#txtDescripcionLarga").val(),
        AssetLocation: $("#txtUbicacion").val(),
        DepnType: $("#selectTipoDepreciacion").val(),
        DepnRate: $("#txtTasaDepreciacion").val(),
        SerialNo: $("#txtNumeroSerie").val(),
        FixedAssetOwnerType: $("#selectTipoPropietario").val(),
        BarCode: $("#txtNumeroInventario").val(),
        procesoscontabilizar: $("#selectProcesoContabilizarActivo").val(),
        FixedAssetType: '1', //$("#selectTipoActivo").val(),
        calibrationdate: '2017-02-12',//No lo usan en sagarpa
        lastmaintenancedate: '2017-02-12',//No lo usan en sagarpa
        //FixedAssetOwnerType: 0,
        model: $("#txtModelo").val(),
        factura: $("#txtNumeroFactura").val(),
        marca: $("#txtMarca").val(),
        size: $("#txtTamanio").val(),
        select_tagrefowner: $("#selectUnidadNegocio").val(),
        ue: $("#selectUnidadEjecutora").val(),
        select_legalbusiness: $("#selectRazonSocial").val(),
        almacen: $("#selectAlmacen").val(),
        datepurchased: $("#txtFechaAdquisicion").val(),
        FechaIncorporacionPatrimonial: $("#txtFechaIncorporacionPatrimonial").val(),
        costo: $("#txtCosto").val(),
        FixedAssetStatus: 1,
        ActivoFijoID: ActivoFijoID,
        FixedCABM: $("#selectClaveCABMS").val(),
        txtClaveBien: $('#txtClaveBien').val(),
        AssetCategoryID: $("#selectCategoriaActivo").val(),
        option: 'grabar',
        activo: $('#selectEstatus').val(),
        proceso: proceso,

        tipoBien: $('#selectTipoBien').val(),
        color: $("#txtColor").val(),
        anio: $("#txtAnio").val(),
        placas: $("#txtPlacas").val(),
        proveedor: $("#txtProveedor").val(),
        observacion: $("#txtObservacion").val(),
        asegurado:chkAsegurado
    };
    //Obtener datos de las bahias
    $.ajax({
            async: false,
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
            if (data.result) {
                if (data.exito == 1) {
                    muestraMensaje(data.Mensaje, 1, 'mensajesValidaciones', 5000,'');
                } else {
                    muestraMensaje(data.Mensaje, 1, 'mensajesValidaciones', 5000, '');
                }

                $.each(data.contenido.datos,function(index, el) {
                    $('#txtNumeroInventario').val(el.numInventario);
                });

                fnLimpiarTabla('divTabla', 'divCatalogo');

            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

function fnChangeOwnerType(valor) {

    //alert(valor);

    switch (valor) {
        case '1':
            $("#selectTipoDepreciacion").removeClass('ocultar')
            $("#tipoDepreciaciongroup").removeClass('ocultar')
            $("#DepnTypelbl").removeClass('ocultar')
            $("#DepnTypetxt").removeClass('ocultar')

            break;
        case '2':
            $("#DepnRatetxt").addClass('ocultar');
            $("#DepnRatelbl").addClass('ocultar');
            $("#DepnTypelbl").addClass('ocultar')
            $("#DepnTypetxt").addClass('ocultar')
            $('#DepnRate').val('0');

            break;
        default:
            break;
    }

}

function fnDatosSelet() { //carga datos a los select
    var retorno = [];
    dataObj = {
        proceso: 'datosSelectSolicitud',
        depreciaciondefault: true
    };
    muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                retorno.push(data.contenido.clave);
                retorno.push(data.contenido.descripcion);
                retorno.push(data.contenido.cams);

                datosCabms = data.contenido.cams;
                datosDepreciacion = data.contenido.depreciacionDefaultPorCategoria;
                retorno.push(data.contenido.partida);

                //alert(data.contenido.partida);
                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });

    return retorno;

}

function fnCargarActivoFijo(aAssetId) {
    
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/activofijo_modelo.php",
        data: {
            aAssetId: aAssetId
        },
        async: false
    }).done(function(data) {
        if (data) {

            //Si trae informacion
            var activofijo = data.contenido;
            $("#btnCancelar").addClass('hide');
            //construye todos las opciones del catalogo de cuentas
            $("#txtActivoFijo").val(activofijo.eco);
            $("#txtNumeroInventario").val(activofijo.BarCode);
            $("#txtDescripcionCorta").val(activofijo.Description);
            $("#txtDescripcionLarga").val(activofijo.LongDescription);
            $("#txtClaveBien").val(activofijo.clavebien);

            $("#selectTipoDepreciacion").val(activofijo.DepnType);

            $("#txtNumeroSerie").val(activofijo.SerialNo);
            $("#selectOrigen").val(activofijo.FixedAssetOwnerType);
            $("#txtActivoFijo").val(activofijo.BarCode);
            $("#selectTipoActivo").val(activofijo.FixedAssetType);
            $("#txtTamanio").val(activofijo.size);
            $("#txtTasaDepreciacion").val(activofijo.DepnRate);
            $("#txtCosto").val(activofijo.costo);
            $("#txtNumeroFactura").val(activofijo.NumeroFactura);
            $("#txtMarca").val(activofijo.Marca);

            $('#txtClaveBien').val(activofijo.clavebien);
            $('#selectProcesoContabilizarActivo').val(activofijo.contabilizado);
            $('#selectProcesoContabilizarActivo').multiselect('rebuild');

            $("#txtFechaIncorporacionPatrimonial").val(activofijo.fechaIncorporacionPatrimonial);
            $("#txtFechaAdquisicion").val(activofijo.datepurchased);

            //$("#selectClaveCabms").val(activofijo.cabm);

            
            // $(".selectClaveCabms").css("display", "none");
            //console.log(activofijo.tagrefowner);
            $('#selectUnidadNegocio').val(activofijo.tagrefowner);
            $('#selectUnidadNegocio').multiselect('rebuild');
            //$('#selectUnidadNegocio').multiselect('disable');

            fnCambioUnidadResponsableGeneral('selectUnidadNegocio', 'selectUnidadEjecutora');
            

            $('#selectUnidadEjecutora').val(activofijo.ue);
            $('#selectUnidadEjecutora').multiselect('rebuild');
            //$('#selectUnidadEjecutora').multiselect('disable');
            fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora'), 'selectAlmacen');

            $("#selectAlmacen").val(activofijo.loccode);
            $("#selectAlmacen").multiselect('rebuild');
            //$("#selectAlmacen").multiselect('disable');

            $("#selectCategoriaActivo").val(activofijo.AssetCategoryID);
            $("#selectCategoriaActivo").multiselect('rebuild');

            var sqlCAMBS="SELECT eq_stockid as valor, concat(eq_stockid,' - ', descPartidaEspecifica) as descripcion FROM tb_partida_articulo WHERE partidaEspecifica = '"+document.getElementById('selectCategoriaActivo').value+"';";
            fnLlenarSelect(sqlCAMBS,'selectClaveCABMS');
            fnCambioCategoria();

            $("#selectClaveCABMS").val(''+activofijo.cabm);
            $("#selectClaveCABMS").multiselect('rebuild');

            $("#selectRazonSocial").val(activofijo.legalid);
            $("#selectRazonSocial").multiselect('rebuild');
            //$("#selectRazonSocial").multiselect('rebuild');
            $("#selectEstatus").val(''+activofijo.active);
            $("#selectEstatus").multiselect('rebuild');


            $("#selectTipoBien").val(''+activofijo.tipo_bien);
            $("#selectTipoBien").multiselect('rebuild');

            $("#selectTipoPropietario").val(''+activofijo.FixedAssetOwnerType);
            $("#selectTipoPropietario").multiselect('rebuild');

            $('#txtPlacas').val(activofijo.placas);
            $('#txtUbicacion').val(activofijo.AssetLocation);
            $('#txtObservacion').val(activofijo.observaciones);
            $('#txtColor').val(activofijo.color_bien);
            $('#txtProveedor').val(activofijo.proveedor);
            $('#txtAnio').val(activofijo.anio);


            $( '#chkAsegurado' ).prop( "checked" , false);

            if(activofijo.asegurado == "1"){
                $( '#chkAsegurado' ).prop( "checked" , true);
            }
            


            cargartagreff = activofijo.tagrefowner;
            categoryid = activofijo.AssetCategoryID;

            model: $("#txtModelo").val(activofijo.model);

        }
    });
}

function fnBorrarActivo() {
    //Opcion para operacion
    dataObj = {
        eco: $("#txtNumeroEconomico").val(),
        Description: $("#txtDescripcionCorta").val(),
        LongDescription: $("#txtDescripcionLarga").val(),
        AssetCategoryID: $("#selectCategoriaActivo").val(),
        AssetLocation: $("#selectLocationsActivo").val(),
        DepnType: $("#selectTipoDepreciacion").val(),
        DepnRate: $("#txtTasaDepreciacion").val(),
        SerialNo: $("#txtNumeroSerie").val(),
        FixedAssetOwnerType: $("#selectOrigen").val(),
        BarCode: $("#txtActivoFijo").val(),
        FixedAssetType: 1, //$("#selectTipoActivo").val(),
        calibrationdate: '2017-02-12',
        lastmaintenancedate: '2017-02-12',
        model: $("#txtModelo").val(),
        factura: $("#txtNumeroFactura").val(),
        marca: $("#txtMarca").val(),
        size: null,
        select_tagrefowner: $("#selectUnidaddenegocio").val(),
        almacen: "",
        costo: 0,
        FixedAssetStatus: 1,
        ActivoFijoID: ActivoFijoID,
        option: 'grabar',
        proceso: 'borrar'
    };
    //Obtener datos de las bahias
    $.ajax({
            async: false,
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            var titulo = '<h3><i class="fa-exclamation-circle" aria-hidden="true"></i></h3>';
            if (data.result) {
                if (data.Mensaje == '') {
                    muestraMensaje(data.ErrMsg, 1, 'mensajesValidaciones', 5000, '');
                } else
                    muestraMensaje(data.Mensaje, 1, 'mensajesValidaciones', 5000, 'activofijo_panel.php');
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

function fnFiltrarPorCuenta(aInfo, aFiltro, aControl) {
    contenido = "";
    for (x in aInfo) {
        if (aFiltro == null)
            contenido += '<option value="' + aInfo[x][Object.getOwnPropertyNames(aInfo[x])[0]] + '">' + aInfo[x][Object.getOwnPropertyNames(aInfo[x])[1]] + '</option>';
        else
        if (aInfo[x].id_cuenta.startsWith(aFiltro))
            contenido += '<option value="' + aInfo[x].id_cuenta + '">' + aInfo[x].descripcion + '</option>';
    }

    $(aControl).html(contenido);
    $(aControl).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        includeSelectAllOption: true


    });
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
    console.log(option);
    return option;
}

function fnCargaInicial() {
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/activofijo_modelo.php",
        data: {
            cargarinicio: true
        },
        async: false

    }).done(function(data) {
        if (data.result) {
            //Si trae informacion
            var tipoActivos = data.contenido.tipoActivos;
            var categoriaactivos = data.contenido.infocatalogoactivos;
            var activoslocations = data.contenido.activoslocations;
            var selectOrigen = data.contenido.selectOrigen;
            var selectTipoDepreciacion = data.contenido.selectTipoDepreciacion;
            var selectUnidaddenegocio = data.contenido.selectUnidaddenegocio;
            var procesoscontabilizar = data.contenido.infoprocesoscontabilizar

            //fnDatosSelet();
            //construye todos las opciones del catalogo de cuentas

            fnFiltrarPorCuenta(tipoActivos, null, '#selectTipoActivo');
            //fnFiltrarPorCuenta(categoriaactivos, null, '#selectCategoriaActivo');
            fnFiltrarPorCuenta(categoriaactivos, null, '#selectClaveCabms');
            //fnFiltrarPorCuenta(activoslocations, null, '#selectLocationsActivo');
            fnFiltrarPorCuenta(selectOrigen, null, '#selectOrigen');
            //fnFiltrarPorCuenta(selectUnidaddenegocio, null, '#selectUnidaddenegocio');
            fnFiltrarPorCuenta(selectTipoDepreciacion, null, '#selectTipoDepreciacion');

            fnCrearDatosSelect(procesoscontabilizar, "#selectProcesoContabilizarActivo")
            //console.log ('activo:'+ActivoFijoID);

        }
    });
}

function fnCargarProcesos(){
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/activofijo_modelo.php",
        data: {
            option: 'procesos'
        },
        async: false

    }).done(function(data) {
        if (data.result) {
            fnCrearDatosSelect(data.contenido, "#selectProcesoContabilizarActivo");
        }
    });
    
}

function fnOcultarComponentes(){
    $("#btnGuardarActivo").addClass('hide');
    $("#btnCancelar").addClass('hide');
}


function fnLlenarSelect(SQL,componente){
    $('#' + componente).empty();
    $('#' + componente).multiselect('rebuild');

    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/componentes_modelo.php",
        data: {
            option: 'llenarSelect',
            strSQL: SQL
        },
        async: false

    }).done(function(data) {
        if(!data.result){return;}
        var options='';
        
        options = '<option value="-1">Seleccionar...</option>';

        $.each(data.contenido,function(index, el) {
            options += '<option value="'+el.val+'">'+el.text+'</option>';
        });

        // console.log(options);
        // console.log(componente);
        $('#' + componente).empty();
        $('#' + componente).append(options);
        $('#' + componente).multiselect('rebuild');
    });
}

$(document).ready(function() {

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!        Configurar multiselect.                !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $('#selectUnidadEjecutora, #selectAlmacen, #selectProcesoContabilizarActivo, #selectClaveCABMS, #selectEstatus').multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });

    $('.multiselect-container').css({
        'max-height': "200px"
    });
    $('.multiselect-container').css({
        'overflow-y': "scroll"
    });

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!            Cargas Iniciales.                  !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    if (ActivoFijoID != 0) {
        fnCargarProcesos();
        fnCargarActivoFijo(ActivoFijoID);
    } else{
        fnCargaInicial();
        fnCambioUnidadResponsableGeneral('selectUnidadNegocio', 'selectUnidadEjecutora');
        if(fnObtenerOption('selectUnidadNegocio') !="" && fnObtenerOption('selectUnidadEjecutora') !=""){
            fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'),fnObtenerOption('selectUnidadEjecutora') , 'selectAlmacen');
        }
    }
    
    if(getVer !=0){
        fnOcultarComponentes();
        fnBloquearDivs("PanelBusqueda");
    }

    

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!                Eventos Change.                !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $('#selectUnidadNegocio').change(function() {
        fnCambioUnidadResponsableGeneral('selectUnidadNegocio', 'selectUnidadEjecutora');
    });

    $('#selectCategoriaActivo').change(function(){
        fnCambioCategoria();

        var sqlCAMBS="SELECT eq_stockid as valor, concat(eq_stockid,' - ', descPartidaEspecifica) as descripcion FROM tb_partida_articulo WHERE partidaEspecifica = '"+document.getElementById('selectCategoriaActivo').value+"';";
        fnLlenarSelect(sqlCAMBS,'selectClaveCABMS');
    });

    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    //!!                                               !!
    //!!               Eventos click.                  !!
    //!!                                               !!
    //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    $("ul.nav-tabs a").click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });

    $('#btnCancelar').click(function(){
        fnLimpiarCamposForm('frmDatos');
    });

    $('#selectUnidadEjecutora').change(function() {
        fnObtenerAlmacenes(fnObtenerOption('selectUnidadNegocio'), fnObtenerOption('selectUnidadEjecutora'), 'selectAlmacen');
    });


});