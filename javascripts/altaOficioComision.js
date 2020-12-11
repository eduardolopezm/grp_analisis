var globalItinerario;
var globalDatosGenerales;
var lineToDelete = "";
var arrLinesToDelete = [];
var iniciaOficioInvocada = false;

$(window).load(function(){
    if ( window.location.href.includes("idFolio=") /*idFolio !=""*/) {
        if(!iniciaOficioInvocada) {
            //cargarInformacionFolio(window.url);
            //console.log("entro edicion");
            inicioOficio();
            iniciaOficioInvocada = true;
        }
        cargarInformacionFolio(window.url);
    }else{
        if($("#selectUnidadEjecutora").val()!="-1"){
            obtenClavePresupuestal();
            getEmployees();
        }
    }
    $("#noOficio").attr('readonly', true);
});

$(document).ready(function(){
    fnBloquearFechas(true);

    if(!iniciaOficioInvocada) {
        inicioOficio();
        iniciaOficioInvocada = true;
    }   
    /**
     * como obtener el nombre de la funcion para los casos en los que se quiere enviar
     * el method con el mismo nombre de la funcion de javascript
     * function compruebaNombre(){ console.log(arguments.callee.toString().match(/function ([^\(]+)/)[1]) }
     */

     // Banderapara indicar cuando se selecciona el select de empleado o el de homologar
    window.isEmployeeSelect = true;
    // agregado de forma manual de los estilos porque el vue no los coloca de forma correcta
    $('#txtAreaObs').css({
        resize: 'none',
        'width': '100%'
    });
    $('#txtAreaObs').parent('div').removeClass('input-group');
    // comportamiento del boton de regreso al panel
    $('#regresar').on('click', function() {
        window.location.href = window.url + '/viaticos.php';
    });

    /******************************** COMPROTAMIENTO SOBRE EL OBJETO DOCUMENT ********************************/

    // comportamiento del elemento estado destino
        /*$(document).on('change', '#idEstadoDestino', function() {
            var $that = $(this),
                municipioEntidad = window.municipioEntidad;
            var entidad = parseInt($that.val()),
                $municipio = $that.parents('div[id]').eq(0).find('#municipioItinerario');
            //  comprobacion de elemento no vacio
            if (entidad == 0 || entidad == '') {
                return;
            }
            //  si no se encuentra el municipio en la variable se consultan los datos
            if (!municipioEntidad.hasOwnProperty(entidad)) {
                getMunicipios($that);
            }
            // si se cuenta con datos en la variale se optienen y se agregan los datos
            else {
                $municipio.multiselect('dataprovider', municipioEntidad[entidad]);
            }
            }) */
    

        // comportamiento de eliminacion de lineas $(document)
        $(document).on('click', '.row-delete', function() {
            var $that = $(this);
            var $pie = $('<button>', {
                class: 'btn botonVerde',
                html: 'Aceptar',
                'data-dismiss': 'modal',
                click: function() {

                    // Se guarda el número de linea que se va a eliminar
                    var selectedLine = $that.parent().parent().parent().prop("id");
                    numberLine = parseInt(selectedLine.substr(5,selectedLine.length)) - 1;
                    fnBloquearLinea(false,numberLine-1);
                    lineToDelete = $("#idItinerario"+numberLine).val();
                    arrLinesToDelete.push(lineToDelete);
                    $that.parents('div[id].borderGray').eq(0).remove();
                    renglon--;
                    reordena();
                    if(!$(".renglon").length){
                        fnBloquearFechas(false);
                        $('#trasnporte').multiselect("enable");
                        $('#cantPernocta').removeAttr("readonly");
                    }
                }
            });

            muestraModalGeneralConfirmacion(3, window.tituloGeneral, '¿Realmente desea eliminar esta visita?', $pie);
        })
        // comportamiento de la cantPernocta $(document)
        .on('keyup', '#cantPernocta', function() {
            var $that = $(this);
            // si es diferente de 0 y mayor que cero  y menor que cincuenta lo coloca
            if ($that.val() != 0 && $that.val() > 0 && $that.val() <= 50) {
                cantPernoctaOld = $that.val();
            }
            //  si e smayor a 50 se coloca 50
            else if ($that.val() > 50) {
                cantPernoctaOld = 50;
            }
            // si es == 0 se coloca 0
            else if ($that.val() == 0) {
                cantPernoctaOld = '';
            }
            //  se coloca la cantidad obtenida
            $that.val(cantPernoctaOld);
        })
    /******************************** COMPROTAMIENTO SOBRE EL OBJETO DOCUMENT ********************************/
    // comportamiento del elemento empleado
    $('#idEmpleado').on('change', function() {
        window.isEmployeeSelect =  true;
        getDataEmploye($(this).attr("id"));
        actualizarMontosCuotaItinerario();
        if( !($(this).val()==0||$(this).val()===null) ){
            getEmployees(true);
            if($("#homologar").is(':checked')){
                $("#idEmpleadoHomologar").trigger("change")
            }
        }
    });

    // comportamiento del checkbox de homologacion
    $('#idEmpleadoHomologar').on('change', function() {
        if( $('#homologar').is(':checked') ) {
          //if ( $('#idEmpleadoHomologar').val() != "0" ) {
              window.isEmployeeSelect =  false;
              getDataEmploye($(this).attr("id"));
              actualizarMontosCuotaItinerario();

          //}
        }
    });


    // Activar y desactivar la homologación con todas sus implicaciones
    $('#homologar').on('change', function() {
        // Si hay un registro de itinerario 
        if($("#linea0").length > 0) {
           ////getDataEmploye(window.url);
        }  

        if($("#homologar").is(':checked')){
            var errorAlHomologar = "";

            if($("#idEmpleadoHomologar > option").length<2){
                errorAlHomologar = "No hay empleados con mayor nivel jerárquico que <strong>"+$("#idEmpleado :selected").text()+"</strong>.";
            }
            if($("#idEmpleado").val()==0||$("#idEmpleado").val()===null){
                errorAlHomologar = "Necesita seleccionar un empleado antes de seleccionar la opción Homologar.";
            }

            if(errorAlHomologar==""){
                $('#idEmpleadoHomologar').multiselect('enable');
            }else{
                $("#homologar").prop("checked",false);
                muestraModalGeneral(3, window.tituloGeneral, errorAlHomologar);
            }
        }else{
            $('#idEmpleadoHomologar').multiselect('select', '0');
            $('#idEmpleadoHomologar').multiselect('rebuild');
            $('#idEmpleadoHomologar').multiselect('disable');
            $("#idEmpleado").trigger("change");
        }
    });

    // comportamiento de guardado
    $('#btn-add').on('click', function() {
        fnRecalculaDiasNoches();
        var params = getParams('form-add'),
            itinerarioLineas = getRows('tbl-itinerario', false),
            dias = fnDiasEntreFechas($("#fechaInicio").val(),$("#fechaTermino").val()),
            campos = {
                'selectUnidadNegocio': 'UR',
                'selectUnidadEjecutora': 'UE',
                //'noOficio': 'Oficio No',
                'txtAreaObs': 'Objetivo comisión',
                'clavePresupuestal': 'Clave Presupuestal',
                'idEmpleado': 'Empleado',
                'tipoSol': 'Tipo de Comisión',
                'tipoGasto': 'Tipo de Viático',
                'fechaInicio': 'Fecha de Inicio',
                'fechaTermino': 'Fecha de Término',
                'trasnporte': 'Transporte',
                'idNuViaticos': 'Identificador de Viáticos'
            },
            msg = [],
            mensajeDatosGenerales = [],
            mensajeItinerario = [],
            erroresDeCaptura = [],
            overlaps = [];
        // se procesa la informacion de la forma
        $.each(params, function(index, val) {
            if (campos.hasOwnProperty(index)) {
                /*if (index == 'clavePresupuestal' && params['tipoGasto'] == 1) {
                    if ($.isEmptyObject(val)) {
                        mensajeDatosGenerales.push(campos[index]);
                    }
                } else 
                 && index != 'clavePresupuestal'*/
                if ($.isEmptyObject(val)) {
                    mensajeDatosGenerales.push(campos[index]);
                } else if ( val==0&&(index=='selectUnidadEjecutora'||index=='clavePresupuestal'||index=='idEmpleado'||index=='tipoSol'||index=='tipoGasto') ) {
                    mensajeDatosGenerales.push(campos[index]);
                } else if ( val=="-1"&&(index=='selectUnidadEjecutora') ) {
                    mensajeDatosGenerales.push(campos[index]);
                }
            }
        });
        if(mensajeDatosGenerales.length){
            erroresDeCaptura.push("Es necesario capturar los datos solicitados: "+mensajeDatosGenerales.join(', ')+".");
        }
        if($("#tipoSol").val()=="2"&&dias>20||$("#tipoSol").val()=="1"&&dias>24){
            erroresDeCaptura.push("Las comisiones "+( $("#tipoSol").val()=="2" ? "inter" : "" )+"nacionales no pueden exceder de "+( $("#tipoSol").val()=="2" ? "20" : "24" )+" días.");
        }
        dataParams = {
            method: 'revisaFechasComisiones',
            idEmpleado: $("#idEmpleado").val(),
            idEmpleadoHomologar: $("#idEmpleadoHomologar").val(),
            fechaInicio: $('#fechaInicio').val(),
            fechaTermino: $('#fechaTermino').val(),
            identificador: idFolio
        };
        $.ajax({
            method: "POST",
            dataType: "JSON",
            url: window.url + '/modelo/altaOficioComisionModelo.php',
            data: dataParams,
            async: false
        }).done(function(res){
            if(res.fechasQueSeSobreponenEmpleado>0){
                erroresDeCaptura.push("El empleado ya cuenta con "+res.fechasQueSeSobreponenEmpleado+" comisi"+( res.fechasQueSeSobreponenEmpleado>1 ? "ones" : "ón" )+" en las fechas que se intentan capturar.");
            }
            if(res.fechasQueSeSobreponenHomologado>0){
                //erroresDeCaptura.push("El empleado a homologar ya cuenta con "+res.fechasQueSeSobreponenHomologado+" comisi"+( res.fechasQueSeSobreponenHomologado>1 ? "ones" : "ón" )+" en las fechas que se intentan capturar.");
            }
            if($("#tipoSol").val()=="1"&&res.diasDeComisionNacionalUsadosEmpleado>=48){
                erroresDeCaptura.push("El empleado ya ha usado "+res.diasDeComisionNacionalUsadosEmpleado+" de sus 48 días anuales para comisiones nacionales.");
            }
            if($("#tipoSol").val()=="2"&&res.diasDeComisionInternacionalUsadosEmpleado>=40){
                erroresDeCaptura.push("El empleado ya ha usado "+res.diasDeComisionInternacionalUsadosEmpleado+" de sus 40 días anuales para comisiones internacionales.");
            }
            if($("#tipoSol").val()=="1"&&res.diasDeComisionNacionalUsadosHomologado>=48){
                //erroresDeCaptura.push("El empleado ya ha usado "+res.diasDeComisionNacionalUsadosEmpleado+" de sus 48 días anuales para comisiones nacionales.");
            }
            if($("#tipoSol").val()=="2"&&res.diasDeComisionInternacionalUsadosHomologado>=40){
                //erroresDeCaptura.push("El empleado ya ha usado "+res.diasDeComisionInternacionalUsadosEmpleado+" de sus 40 días anuales para comisiones internacionales.");
            }
        }).fail(function(res) {
            console.log(res);
        });
        if(!mensajeDatosGenerales.length&&!itinerarioLineas.length){
            erroresDeCaptura.push("Debe capturar al menos un registro en el itinerario .");
        }else{
            /* Código que se volvió inútil con la nueva validación de fechas 
            $.each(itinerarioLineas, function(index, val) {
                var fechaIniAct = fnCambiaFechaDeStringAVariableDate(val["fechaInicio"+index]),
                    fechaTerAct = fnCambiaFechaDeStringAVariableDate(val["fechaTermino"+index]);

                for(var c=index;c<itinerarioLineas.length;c++){
                    if(c!=index){
                        if(
                            (index==0&&fechaIniAct>=fnCambiaFechaDeStringAVariableDate($("#fechaInicio"+c).val()))||
                            (val["fechaInicio"+index]==$("#fechaInicio"+c).val()||
                            val["fechaTermino"+index]==$("#fechaTermino"+c).val())
                        ){
                            if(overlaps.indexOf(c)<0){
                                overlaps.push(c);
                            }
                        }
                    }
                }
            });
            */
            //overlaps.sort();
            $.each(itinerarioLineas, function(index, val) {
                var mensajeLineaItinerario = [],
                    fechaIniAct = fnCambiaFechaDeStringAVariableDate(val["fechaInicio"+index]),
                    fechaTerAct = fnCambiaFechaDeStringAVariableDate(val["fechaTermino"+index]);

                if($("#tipoSol").val()==2&&val["pais"+index]<=0){
                    mensajeLineaItinerario.push("País");
                }
                if($("#tipoSol").val()==1&&val["idEstadoDestino"+index]<=0){
                    mensajeLineaItinerario.push("Entidad");
                }
                if($("#tipoSol").val()==1&&val["municipioItinerario"+index]<=0){
                    mensajeLineaItinerario.push("Municipio");
                }
                if(val["dias"+index]==0&&val["pernocta"+index]==0){
                    mensajeLineaItinerario.push("sin estadía en el destino");
                }
                if(fechaIniAct<fnCambiaFechaDeStringAVariableDate($("#fechaInicio").val())){
                    //mensajeLineaItinerario.push("<strong>Fecha Inicio</strong> no puede ser may");
                }
                if(overlaps.indexOf(index)!=-1){
                    mensajeLineaItinerario.push("Las fechas del registro se sobreponen con otras");
                }
                if(fechaIniAct>fechaTerAct){
                    mensajeLineaItinerario.push("<strong>Fecha Inicio</strong> es mayor a <strong>Fecha Término</strong>");
                }
                if(mensajeLineaItinerario.length){
                    mensajeItinerario.push("Línea "+(index+1)+": "+mensajeLineaItinerario.join(", ")+".");
                }
            });
        }
        if($(".renglon").length&&fnValidaDiasNoches().length){
            erroresDeCaptura.push("Se han usado "+fnValidaDiasNoches().join(" y "));
        }
        if(mensajeItinerario.length){
            erroresDeCaptura.push("Revise los siguientes datos del itinerario:<br>"+mensajeItinerario.join("<br>"));
        }

        var validado = ( erroresDeCaptura.length ? false : true );
        if(validado){

            // Se sacó todo el código de if($("#tipoGasto").val() == "1") { y se documentó storeApplication(window.url);
               dataParams = {
                  method: 'tieneFondosClavePresupuestal',
                  clave: $("#clavePresupuestal").val(),
                  fechaInicio: $('#fechaInicio').val()
               }; 

               $.ajax({
                  method: "POST",
                  dataType: "JSON",
                  url: window.url + '/modelo/altaOficioComisionModelo.php',
                  data: dataParams,
                  async: false
               }).done(function(res) {
                    console.log(res);
                    var totalImporte = 0;
                    var tieneFondosCP = false;
                    $(".importe").each(function() {
                           totalImporte += parseInt( $(this).val() );
                    });
                    if(totalImporte < res[0].budget) {
                        tieneFondosCP = true;
                    }
                    if(tieneFondosCP) {
                        storeApplication(window.url);
                    }
                    else {
                        muestraModalGeneral(3, window.tituloGeneral, 'La clave presupuestal no tiene fondos: ');
                    } 
               }).fail(function(res) {
                   console.log(res);
               });
            if($("#tipoGasto").val() == "1") {
            } else {   
                // @FIXME: Colocar que al momento de que se genera se quede en la pantalla para editar aunque no creo que sea necesario
                ////storeApplication(window.url);
            }    
        } else {
            // muestraMensajeTiempo('Es necesario capturar los datos solicitados, campos marcador con rojo', 3, 'mensajes', msgTime);
            muestraModalGeneral(3, window.tituloGeneral, erroresDeCaptura.join("<br>"));
            
        }
    });
    // agregar linea de itinerario
    $('#add').on('click', agregarLineaItinerario);
    // comportamiento del tipo de solicitud
    $('#tipoSol').on('change', function() {
        var lineas = $('#tbl-itinerario').find('div[id].borderGray'),
            $that = $(this),
            montoDiario = window.montoDiario,
            itinerarioLineas = getRows('tbl-itinerario', false);
        // aplicación de pernocta solo si es nacional la comisión
        if ($that.val() != 1) {
            $('#cantPernocta').val(0).attr('readonly', true);
        } else {
            $('#cantPernocta').val('').attr('readonly', false);
        }

        if ($that.val() == 0) {
            // @TODO: Preguntar que se ase en este tipo de situaciones si se elimina la información o se deja
            muestraModalGeneral(3, window.tituloGeneral, 'Es necesario indicar el tipo de comisión.');
            return false;
        }

        // cambio de de columnas en el itinerario
        if (itinerarioLineas.length != 0) {
            // @TODO: eliminar los datos
            fnBorrarItinerario();
            // muestraModalGeneral(3,'Advertencia','Es necesario que elimine el itinerario para poder cambiar el tipo de comisión');
            // return false;
        }
        if ($that.val() == 2) {
            // colocacion por defectos de los datos
            $('#tipoGasto').multiselect('select', 2).multiselect('disable').trigger('change');
            $('#trasnporte').multiselect('select', 4).multiselect('disable').trigger('change');
            // $('#tipoNacionalAreaClaves').addClass('hidden');
            $('#tipoNacionalArea').addClass('hidden');
            $('#tipoInterArea').removeClass('hidden');
        } else {
            $('#tipoGasto').multiselect('rebuild').trigger('change');
            $('#trasnporte').multiselect('deselect', [1, 2, 3, 4]).multiselect('enable').trigger('change');
            // $('#tipoNacionalAreaClaves').removeClass('hidden');
            $('#tipoNacionalArea').removeClass('hidden');
            $('#tipoInterArea').addClass('hidden');
        }
        fnRecalculaDiasNoches();
    });

    // comportamiento del cambio de fecha en cabecera
    $("#fechaInicio").parent().on('dp.change', fnCambioDeFecha);
    $("#fechaTermino").parent().on('dp.change', fnCambioDeFecha);
    /*$('#form-add').on('dp.change', '.componenteFeriadoAtras', function(e) {
        var $that = $(this),
            $input = $that.find('input[id]'),
            $fechaTermino = $('#form-add .componenteFeriadoAtras').eq(1),
            minFechaItinerario = ['#fechaInicio', '#fechaTermino'];
        if ($input.attr('id') == 'fechaInicio') {
            // @TODO: colocar el cambio para los registros del itinerario genrados
            window.minDate = e;
            $fechaTermino.data('DateTimePicker').minDate(window.minDate.date);
        } else if ($input.attr('id') == 'fechaTermino') {
            var fechaDesdeVal = $('#fechaInicio').val(),
                fechaHastaVal = $('#fechaTermino').val(),
                formatoDate = e.date._f;
            var fechaDesde = moment(fechaDesdeVal, formatoDate),
                fechaHasta = moment(fechaHastaVal, formatoDate);
            diferencias = fechaHasta.diff(fechaDesde, 'days');
            if (diferencias < 0) {
                muestraModalGeneral(3, window.tituloGeneral, 'La fecha de <strong>Término</strong>, no puede ser <strong>menor</strong> a la fecha de <strong>Inicio</strong>');
                $fechaTermino.find('input[id]').val(e.date._i);
                return false;
            }
        }
    });*/


    /******************************** COMPROTAMIENTO SOBRE LOS OBJETOS MONTO PEAJE Y MONTO COMBUSTIBLE ********************************/

    $("#montoPeaje").focusout(function() {
        actualizarPasaje();
    });

    $("#montoCombustible").focusout(function() {
        actualizarPasaje();
    });

    // comportamiento de monto del transporte
    $('#datosTransporte').on('blur', function() {
        var $that = $(this);
        $('#tbl-itinerario').find('div[id]').each(function(index, el) {
            $(el).find('#pasaje').val($that.val());
        });
    });

    // comportamieto del regreso y la cancelacion de la informacion
    $('.regresar').on('click', function() {
        window.location.href = window.home;
    });
    // comprotamiento de la unidad ejecutora
    $('#selectUnidadEjecutora').on('change', function() {
        //// Se sacó esta línea del if($('#tipoGasto').val()==1){
        obtenClavePresupuestal();
        if(idFolio==""){
            getEmployees();
        }
        if($('#tipoGasto').val()==1){
        }
    });
    // comportamiento del tipo de gasto
    $('#tipoGasto').on('change', function() {
        var $that = $(this),
            $contenedor = $('#claveP'),
            fecActual = fechaActual(),
            $fechaInicio = $('#fechaInicio'),
            $fechaFin = $('#fechaTermino'),
            $fechaInicioDatepicker = $('#form-add .componenteFeriadoAtras').eq(0),
            $objectDateMoment = moment(),
            disableDate = true;
        var fechaOriginalIni = $("#fechaInicio").val(),
            fechaOriginalFin = $("#fechaTermino").val();
        fnBloquearFechas(true);
        // se retira el bloqueo de los campos de fecha
        if ($that.val() != 0&&idFolio=="") {
            disableDate = false;
            fnBloquearFechas(false);
        }
        //$fechaInicio.attr('disabled', disableDate);
        //$fechaFin.attr('disabled', disableDate);
        // llamada de la obtencion de claves presupuestales //// Anteriormente sólo se llamaba en if ($that.val() == 1) {
        obtenClavePresupuestal();

        // comportamiento aplicado a las fechas
        if ($that.val() == 1) {
            fechaMinDate = false;
            fechaMaxDate = $objectDateMoment;
            //$fechaInicioDatepicker.data('DateTimePicker').maxDate($objectDateMoment).minDate(false);
        } else {
            fechaMinDate = $objectDateMoment;
            fechaMaxDate = false;
            //$fechaInicioDatepicker.data('DateTimePicker').maxDate(false).minDate($objectDateMoment);
        }
        if(idFolio!=""){
            fechaMinDate = false;
            fechaMaxDate = false;
        }
        //
        $("#fechaInicio").parent().data('DateTimePicker').maxDate(fechaMaxDate);
        $("#fechaInicio").parent().data('DateTimePicker').minDate(fechaMinDate);
        $("#fechaTermino").parent().data('DateTimePicker').maxDate(fechaMaxDate);
        $("#fechaTermino").parent().data('DateTimePicker').minDate(fechaMinDate);
        $("#fechaInicio").trigger('change');
        $("#fechaTermino").trigger('change');

        if(fechaOriginalIni!=$("#fechaInicio").val()||fechaOriginalFin!=$("#fechaTermino").val()){
            fnBorrarItinerario();
        }
        // comportamiento para claves presupuestales
        //$('#clavePresupuestal').val('');
        if ($that.val() == 2 || $that.val() == 0) {
            //$contenedor.addClass('hidden');
            //return;
        }
        $contenedor.removeClass('hidden');
        
        // inicializa la clave presupuestal si estamos en modo edición del oficio y es devengado el tipo de gasto
        // Se retiró el factor devengado en el tipo de gasto  && $that.val() == 1
        if(window.location.href.includes("idFolio=")){
            $("#selectUnidadEjecutora").trigger('change');
            setTimeout(function(){
                actualizarClavePresupuestal();
            },500);
        }
    });
}); // FIN jQuery(document).ready(function($) { .... });
/**
 * Funcion para el inicio de las variables que se utilizaran
 * a lo largo del programa
 * @return {[type]} [description]
 */
function inicioOficio() {
    window.bandLog = 1; //varible para mostrar mensajes en consola
    //prevenir submit
    $(document).on('click', 'button', function(e) {
        e.preventDefault();
    });
    //configuraciones y ejecuciones principales
    var url = window.location.href.split('/');
    url.splice(url.length - 1);
    window.url = url.join('/');
    window.msgTime = 3000;
    window.linea = 0;
    window.renglon = 1;
    window.municipios = [];
    window.home = window.url + '/viaticos.php';
    window.municipioEntidad = [];
    window.clavesGeneral = [];
    window.clavesCombustibles = [];
    window.peajeTransporte = [];
    window.cantPernoctaOld = 0;
    window.datosPaises = [];
    window.tipoDivisa = 'MX';
    window.tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    // se encuentra en la función getSelects en caso de ser modificado
    window.perfil; // solo se reserva el espacio o nombre
    window.editable = true;

    // se recuperan las entidades
    obtenEntidades();
    //obtener datos para los selects
    //getSelects(window.url);
    //obtener datos de los municipios
    // getMunicipios(window.url);

    // aplicacion de los estilos de select
    var indicadoresSelect = '#tipoSol, #idEstadoDestino, #idMunicipio, #tipoGasto, #trasnporte,' +
        '#clavePresupuestalPeaje, #clavePresupuestalCombustible, #clavePresupuestalTransportePublico';
    fnFormatoSelectGeneral(indicadoresSelect);
    fnFormatoSelectGeneral(".clavePresupuestal");
    $('#montoPeaje, #montoCombustible, #montoTrasportePublico, #montoPasaje').addClass('w30p pull-left');

    // llamada de la obtencion de claves presupuestale
    //obtenClavePresupuestal();
    // obtención de datos de países
    obtenPaises();

    // colocación de la fecha de inicio como minimo el dia actual
    /////$('#form-add .componenteFeriadoAtras').eq(0).data('DateTimePicker').minDate( idFolio=="" ? moment() : false );
    
    // se inhabilita el select de homologación
    //consoleLog($('#idEmpleadoHomologar').length);
    //$('#idEmpleadoHomologar').multiselect('disable');
    consoleLog('viaticos listo');
}
/**
 * Funcion para el agregado de una nueva linea de itinerario
 * segun los parametros de control presupuestal y datos generales
 * @return {[type]} [description]
 */
function agregarLineaItinerario() {
    var zona = ""; 
    var i = $(".renglon").length;
    var renglonId;
    var lineaId;
    if( window.location.href.includes("idFolio=") ) {
        renglonId = i+1; 
        lineaId   = i+1;
     } else {
        renglonId = i+1//window.renglon;
        lineaId = i+1;//window.linea;
    } 
    var $tbl = $('#tbl-itinerario'),
        template = '',
        tipoCom = $('#tipoSol').val(),
        montoDiario = window.montoDiario,
        idempleado = $('#idEmpleado').val(),
        fechaInicioComision = $('#fechaInicio').val(),
        fechaTerminoComision = $('#fechaTermino').val(),
        mensajeCrearItinerario = [];
    // comprobacion de datos del empleado
    if(idempleado == 0||idempleado===null){
        mensajeCrearItinerario.push("Empleado");
        //muestraModalGeneral(3, window.tituloGeneral, 'Es necesario seleccionar un <strong>empleado</strong>. Para continuar.');
        //return false;
    }
    // comprobacion de los datos de tipo de comicion
    if (tipoCom == 0) {
        mensajeCrearItinerario.push("Tipo de Comisión");
        //muestraModalGeneral(3, window.tituloGeneral, 'Es necesario seleccionar el tipo de comisión. Para obtener la cuota/unidades diarias.');
        //return false;
    }
    // comprobacion de los datos de tipo de comicion
    if ($('#trasnporte').val() == 0) {
        mensajeCrearItinerario.push("Transporte");
        //muestraModalGeneral(3, window.tituloGeneral, 'Es necesario seleccionar el tipo de comisión. Para obtener la cuota/unidades diarias.');
        //return false;
    }
    if(mensajeCrearItinerario.length){
        var temporal = "";
        if(mensajeCrearItinerario.length>1){
            temporal = "</strong> y <strong>"+mensajeCrearItinerario[mensajeCrearItinerario.length-1];
            mensajeCrearItinerario.pop();
        }
        muestraModalGeneral(3, window.tituloGeneral, 'Es necesario seleccionar <strong>'+mensajeCrearItinerario.join("</strong>, <strong>")+temporal+'</strong>. Para continuar.');
        return false;
    }
    $('#trasnporte').multiselect("disable");
    if($('#cantPernocta').val()==""){
        $('#cantPernocta').val("50");
    }
    $('#cantPernocta').attr('readonly', true);
    fnBloquearFechas(true);

    // comprobación y generación de columnas según el tipo de solicitud
    var contenTipoSol = '';
    if (tipoCom == 1) {
        contenTipoSol =
            '<div class="w17p fl vam h35">' +
            '<select id="idEstadoDestino'+i+'" name="idEstadoDestino'+i+'" data-cn="idEstadoDestino" class="form-control idEstadoDestino" data-todos="true" ></select>' +
            '</div>' +
            '<div class="w17p fl vam h35">' +
            '<select id="municipioItinerario'+i+'" name="municipioItinerario'+i+'" data-cn="municipioItinerario" class="form-control municipioItinerario" data-todos="true" ></select>' +
            '</div>';
    } else {
        contenTipoSol =
            '<div class="w34p fl vam h35">' + '<select id="pais'+i+'" name="pais'+i+'" data-cn="pais"  class="form-control pais" data-todos="true" ></select>' + '</div>';
    }
    // desarrollo del agregado
    lineaId = 'linea' + lineaId;

    var montoCom = 0;//(tipoCom == 1 ? montoDiario.nacional/2 : montoDiario.extrangero);

    if (tipoCom == 1) {

        $("#zonaEconomica").show();

        $("#cuota").prop('class','w7p  falsaCabeceraTabla');
        $("#dias").prop('class', 'w7p  falsaCabeceraTabla');
        $("#importe").prop('class', 'w9p  falsaCabeceraTabla');

        template =
            '<div class="row w100p borderGray" id="' + lineaId + '">' +
            '<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
            '<div class="w3p fl vam h35 pt4"> <span  class="btn btn-danger btn-xs glyphicon glyphicon-remove row-delete"></span> </div>' +
            '<div class="w3p fl vam h35 pt10 renglon">' + renglonId + '</div>' +
            contenTipoSol +
            '<div class="w12p fl vam h35">' +
            '<component-date-feriado2 id="fechaInicio'+i+'" name="fechaInicio'+i+'" data-cn="fechaInicio" placeholder="Fecha inicio" value="' + fechaInicioComision + '"></component-date-feriado2>' +
            '</div>' +
            '<div class="w12p fl vam h35">' +
            '<component-date-feriado2 id="fechaTermino'+i+'" name="fechaTermino'+i+'" data-cn="fechaTermino" placeholder="Fecha Término" value="' + fechaTerminoComision + '"></component-date-feriado2>' +
            '</div>' +
            '<div class="w7p fl vam h35"><component-text id="zonaEconomica'+i+'" data-cn="zonaEconomica" name="zonaEconomica'+i+'" readonly value="' + zona + '" class="cuoto"></component-text></div>' +
            '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
            '<div class="w5p fl vam h35"><component-text id="cuota'+i+'" name="cuota'+i+'" data-cn="cuota" readonly value="0" class="cuoto"></component-text></div>' +
            '<div class="w6p fl vam h35"><component-text id="dias'+i+'" name="dias'+i+'" data-cn="dias" readonly  value="1"></component-text></div>' +
            '<div class="w6p fl vam h35"><component-text id="pernocta'+i+'" name="pernocta'+i+'" data-cn="pernocta" readonly value="0"></component-text></div>' +
            '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
            '<div class="w8p fl vam h35"><component-text id="importe'+i+'" name="importe'+i+'" data-cn="importe" readonly value="' + montoCom + '" class="importe"></component-text></div>' +
            '<input type="hidden" id="idItinerario'+i+'" name="idItinerario'+i+'" data-cn="importe" value="-1" >'+
            '</div>' +
            '</div>';
    }
    else {

        $("#zonaEconomica").hide();

        $("#cuota").prop('class','w11p  falsaCabeceraTabla');
        $("#dias").prop('class', 'w9p  falsaCabeceraTabla');
        $("#importe").prop('class', 'w10p  falsaCabeceraTabla');

        template =
            '<div class="row w100p borderGray" id="' + lineaId + '">' +
            '<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
            '<div class="w3p fl vam h35 pt4"> <span  class="btn btn-danger btn-xs glyphicon glyphicon-remove row-delete"></span> </div>' +
            '<div class="w3p fl vam h35 pt10 renglon">' + renglonId + '</div>' +
            contenTipoSol +
            '<div class="w12p fl vam h35">' +
            '<component-date-feriado2 id="fechaInicio'+i+'" name="fechaInicio'+i+'" data-cn="fechaInicio" placeholder="Fecha inicio" value="' + fechaInicioComision + '"></component-date-feriado2>' +
            '</div>' +
            '<div class="w12p fl vam h35">' +
            '<component-date-feriado2 id="fechaTermino'+i+'" name="fechaTermino'+i+'" data-cn="fechaTermino" placeholder="Fecha Término" value="' + fechaTerminoComision + '"></component-date-feriado2>' +
            '</div>' +
            '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
            '<div class="w9p fl vam h35"><component-text id="cuota'+i+'" name="cuota'+i+'" data-cn="cuota" readonly value="0" class="cuoto"></component-text></div>' +
            '<div class="w9p fl vam h35"><component-text id="dias'+i+'" name="dias'+i+'" data-cn="dias" readonly value="1"></component-text></div>' +
            '<div class="w6p fl vam h35"><component-text id="pernocta'+i+'" name="pernocta'+i+'" data-cn="pernocta" readonly value="0"></component-text></div>' +
            '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
            '<div class="w8p fl vam h35"><component-text id="importe'+i+'" name="importe'+i+'" data-cn="importe" readonly value="' + montoCom + '" class="importe"></component-text></div>' +
            '<input type="hidden" id="idItinerario'+i+'" name="idItinerario'+i+'" data-cn="idItinerario" value="-1" >'+
            '</div>' +
            '</div>';

    }        
    
    //
    if(i>0){
        var mensajeFaltaLocacion = [];
        if($("#tipoSol").val()==1){
            if($("#idEstadoDestino"+(i-1)).val()==0||$("#idEstadoDestino"+(i-1)).val()===null){
                mensajeFaltaLocacion.push("Entidad");
            }
            if($("#municipioItinerario"+(i-1)).val()==0||$("#municipioItinerario"+(i-1)).val()===null){
                mensajeFaltaLocacion.push("Municipio");
            }
        }
        if($("#tipoSol").val()==2){
            if($("#pais"+(i-1)).val()==0||$("#pais"+(i-1)).val()===null){
                mensajeFaltaLocacion.push("País");
            }
        }
        if(mensajeFaltaLocacion.length){
            muestraModalGeneral(3, window.tituloGeneral, 'Es necesario proporcionar <strong>'+mensajeFaltaLocacion.join("</strong> y <strong>")+'</strong>. Para continuar.');
            return false;
        }
        fnBloquearLinea(true,i-1);
    }
    $tbl.append(template);


    fnEjecutarVueGeneral(lineaId);
    fnFormatoSelectGeneral('#' + lineaId + ' #municipioItinerario'+i+', #' + lineaId + ' #idEstadoDestino'+i+', #' + lineaId + ' #pais'+i);
    fnCrearDatosSelect(window.entidades, '#' + lineaId + ' #idEstadoDestino'+i, 0, 1);
    fnCrearDatosSelect(window.datosPaises, '#' + lineaId + ' #pais'+i, 0, 1);
    // comportamiento del cambio del calendario
    fnConfiguracionDeParesDeFechas(i);


    /*var sumDiasNoches = parseInt( $("#dias"+i).val() ) + parseInt( $("#pernocta"+i).val() );
    var importeTotal; 

    // Calculo el importe total en dependencia si es un viaje nacional o al extranjero  
    if(tipoCom == 1) {
      importeTotal = montoCom * sumDiasNoches;
    }
    else {
      importeTotal = montoCom * parseInt( $("#dias"+i).val() );
    }

    $("#importe"+i).val(importeTotal); */


    window.linea++;
    window.renglon++;

    $(document).on('change', '#idEstadoDestino'+i, function() {
            var $that = $(this),
                municipioEntidad = window.municipioEntidad;
            var entidad = parseInt($that.val()),
                $municipio = $that.parents('div[id]').eq(0).find('#municipioItinerario'+i);
            //  comprobacion de elemento no vacio
            if (entidad == 0 || entidad == '') {
                return;
            }
            //  si no se encuentra el municipio en la variable se consultan los datos
            if (!municipioEntidad.hasOwnProperty(entidad)) {
                getMunicipios($that,i);
            }
            // si se cuenta con datos en la variale se optienen y se agregan los datos
            else {
                $municipio.multiselect('dataprovider', municipioEntidad[entidad]);
            }
            actualizarZonaEconomica($(this).val(),i);
            fnRecalculaDiasNoches();
            fnRecalculaImporte();
    });


    $(document).on('change', '#pais'+i, function() {

        var itemSel;

        if( $('#homologar').is(':checked') && $('#idEmpleadoHomologar').val() != "0" ) {

               itemSel = $('#idEmpleadoHomologar').val();
        }
        else {

               itemSel = $('#idEmpleado').val();

             }

        var params = {};     

        params.method = 'getDataEmploye';

        params.employe = itemSel;
        params.idFolio = idFolio;
        params.consultarComisiones = ( $('#homologar').is(':checked') ? 0 : 1 );
                $.ajax({
                    method: "POST",
                    dataType: "JSON",
                    url: window.url + '/modelo/altaOficioComisionModelo.php',
                    data: params,
                    async: false
                }).done(function(res) {
                    if (res.success) {

                        var param = {};

                        param.method    = "obtenerCuotaInternacional";
                        param.jerarquia = res.empleado['idJerarquia'];
                        param.tipoComision = "2";

                        $.ajax({
                            method:   "POST",
                            dataType: "JSON",
                            url: window.url + '/modelo/altaOficioComisionModelo.php',
                            data: param,
                            async: false
                        }).done( function(res) {
                            $("#cuota"+i).val( formatoComas(fixDecimales( String(redondeaDecimal(res.cuota) ))) );
                            var dias    = parseInt( $("#dias"+i).val() );
                            var importe = parseFloat(res.cuota) * dias;
                            $("#importe"+i).val( formatoComas(fixDecimales( String(redondeaDecimal(importe) ))) );             
                        });
                        
                   
                    }
                }).fail(function(res) {
                    muestraModalGeneral(3, window.tituloGeneral, 'Ocurrió un incidente inesperado mientras se obtenía el empleado. Favor de contactar al administrador.');
                });
    }); 


}


function actuallizarLineaItinerario() {
    tipoCom = $('#tipoSol').val();
    var montoCom = (tipoCom == 1 ? montoDiario.nacional : montoDiario.extrangero);
    $(".cuoto.form-control").each( function() {
        $(this).val(montoCom);
        //$("#cuota").val(montoCom);
    } );

    if( $(".renglon").length >0 ) {
      //var lineaId = $("#cuota0").parent().parent().parent().prop("id");
      //var linea   = lineaId.substr(5,lineaId.length);
      for (var linea = 0; linea <= $(".renglon").length; linea++) {
          calculaImporte($('#dias'+linea),linea);
      }
         
    }
    /*var lineaId = $("#cuota0").parent().parent().parent().prop("id");
    var linea   = lineaId.substr(5,lineaId.length);

    var numLines = $(".renglon").length;
    
    for (var i = 0; i < numLines; i++) {
       $()  
    } */
    
}

/**
 * Obtener datos para llenar los select
 * @param  string prefix prefijo de la url
 * @return jquery
 */
function getSelects(prefix) {
    muestraCargandoGeneral();
    params = {
        method: 'getSelects'
    }
    
    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: prefix + '/modelo/altaOficioComisionModelo.php',
        data: params,
        async: false
    }).done(function(res) {
        consoleLog("variable res de los getSelects: "+res);
        if (res.success) {
            fnFormatoSelectGeneral('#idEmpleado');
            fnFormatoSelectGeneral('#idEmpleadoHomologar');
            AlinearSelectsDerecha();
            if(idFolio!=""){
                // se agrega la informacion al select de empleados
                fnCrearDatosSelect(res.content.empleados, '#idEmpleado', 0, 1);
                // se agrega la informacion al select de empleados homologa
                fnCrearDatosSelect(res.content.empleados, '#idEmpleadoHomologar', 0, 1);
            }
            // se agrega la informacipon al select de estados destino
            fnCrearDatosSelect(res.content.entidad, '#idEstadoDestino', 0, 1);
            // se agrega la informacion al select de tipo de gasto
            fnCrearDatosSelect(res.content.tipoGasto, '#tipoGasto', 0, 1);
            // se agrega la informacion al select de transporte 
            fnCrearDatosSelect(res.content.transportes, '#trasnporte', 0, 1);
            
            $('#idEmpleadoHomologar').multiselect('disable');


            /* Si estoy en modo edicion y ya se cargaron todos los selects cargo la infromacion del oficio, esto se hace para evitar que
                se traiga la informacion del oficio antes de que se cargue los datos del oficio de viaticos   */
            if( window.location.href.includes("idFolio=") ) {
                    console.log( "cantidad de options en el select empleados: "+ $("#idEmpleado > option").length);
                    ////cargarInformacionFolio(window.url);
            }
            
        } else {
            muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
        }
        // asignación de perfil obtenido
        window.perfil = res.perfil;
        ocultaCargandoGeneral();
    }).fail(function(res) {
        consoleLog(res)
        muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
        ocultaCargandoGeneral();
    });

}

function getEmployees(homologando=false){
    if(!homologando){
        $("select#idEmpleado option[value!='0']").remove();
    }
    $("select#idEmpleadoHomologar option[value!='0']").remove();

    if($("#selectUnidadEjecutora").val()!= "-1") {
        muestraCargandoGeneral();
        params = {
            method: 'getEmployees',
            ue: $("#selectUnidadEjecutora").val()
        }
        if(homologando){
            params.homologado = homologando;
            params.idEmpleado = $("#idEmpleado").val();
        }
        $.ajax({
            method: "POST",
            dataType: "JSON",
            url: window.url + '/modelo/altaOficioComisionModelo.php',
            data: params,
            async: false
        }).done(function(res) {
            if (res.success) {
                AlinearSelectsDerecha();
                if(homologando){
                    // se agrega la información al select de empleados homologados
                    fnCrearDatosSelect(res.content.empleados, '#idEmpleadoHomologar', 0, 1);
                }else{
                    // se agrega la información al select de empleados
                    fnCrearDatosSelect(res.content.empleados, '#idEmpleado', 0, 1);
                }

                /* Si estoy en modo edición, y ya se cargaron todos los selects, cargo la infromación del oficio, esto se hace para evitar que
                    se traiga la información del oficio antes de que se carguen los datos del oficio de viáticos   */
                if( window.location.href.includes("idFolio=") ) {
                        console.log( "cantidad de options en el select empleados: "+ $("#idEmpleado > option").length);
                        ////cargarInformacionFolio(window.url);
                }
            } else {
                muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
            }
            // asignación de perfil obtenido
            window.perfil = res.perfil;
            ocultaCargandoGeneral();
        }).fail(function(res) {
            consoleLog(res)
            muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
            ocultaCargandoGeneral();
        });
    }

    $("#idEmpleado").multiselect('rebuild');
    $("#idEmpleadoHomologar").multiselect('rebuild');
            
    if(!$("#homologar").is(':checked')){
        $('#idEmpleadoHomologar').multiselect('disable');
    }
}

/**
 * Funcion para la obtencion de los datos del empleado
 * segun se seleccione en el combo de empleados
 * @param  {[type]} prefix [description]
 * @return {[type]}        [description]
 */
function getDataEmploye(campoARevisar) {
    var itemSel = 0;
    itemSel = ( campoARevisar=="idEmpleadoHomologar" ? $('#idEmpleadoHomologar').val() : itemSel );
    itemSel = ( campoARevisar=="idEmpleado" ? $('#idEmpleado').val() : itemSel );

    /*if( $('#homologar').is(':checked') && $('#idEmpleadoHomologar').val() != "0" ) {
       itemSel = $('#idEmpleadoHomologar').val();
    }
    else {
       itemSel = $('#idEmpleado').val();
    }*/
    params = {
        method: 'getDataEmploye',
    };
    limpiaEmpleado();
    if (itemSel > 0) {
        // agregado del identificador del empleado
        //console.log("itemSel: "+itemSel);
        params.employe = itemSel;
        params.idFolio = idFolio;
        params.consultarComisiones = ( $('#homologar').is(':checked') ? 0 : 1 );

        //console.log("variable itemSel de params: "+itemSel);
        //  envio de solicituf ajax
        $.ajax({
            method: "POST",
            dataType: "JSON",
            url: window.url + '/modelo/altaOficioComisionModelo.php',
            data: params,
            async: false
        }).done(function(res) {
            if (res.success) {
                // limpiesa de los campos del empleado
                $('#eNombre').val(res.empleado['nombre']);
                if ( window.isEmployeeSelect ) {
                   $('#eRFC').val(res.empleado['rfc']);
                   $('#Epuesto').val(res.empleado['puesto']);
                }
                $('#eJerarquia').val(res.empleado['jerarquia']);
                //window.montoDiario = res.empleado['monto'];

                // se actualiza el monto de la cuota en el grid
                //actuallizarLineaItinerario();

                // muestra del informacion del empleado
                $('#dataEmploye').show();
                // si tuene error de jerarquía o puesto se mostrara elmensaje
                if (res.error) {
                    $pie = $('<button>', {
                        class: 'btn botonVerde',
                        html: 'Aceptar',
                        click: function() {
                            window.location.href = window.home;
                        }
                    });
                    /*$("#idEmpleado").multiselect('select', '0');
                    $("#idEmpleado").multiselect('rebuild');
                    limpiaEmpleado();*/
                    muestraModalGeneral(3, window.tituloGeneral, res.msg);
                }
            } else {
                // limpiesa de los campos del empleado
                limpiaEmpleado();
                // se aoculta los datos del empleado
                $('#dataEmploye').hide();
            }
        }).fail(function(res) {
            muestraModalGeneral(3, window.tituloGeneral, 'Ocurrió un incidente inesperado. Favor de contactar al administrador.');
        });
    } else {
        limpiaEmpleado();
        // se aoculta los datos del empleado
        $('#dataEmploye').hide();
    }
}

/**
 * Funcion para el guardado de la solicitud de comisión
 * @param  {[type]} prefix [description]
 * @return {[type]}        [description]
 */
function storeApplication(prefix) {
    var arrIds = ["#datosGenerales","#controlPresupuestal"];
    var params = getParamsSeveralIds(arrIds);
    params.homologar = ( $("#homologar").is(':checked') ? 1 : 0 );
    params.idEmpleadoHomologar = ( $("#homologar").is(':checked') ? $("#idEmpleadoHomologar").val() : 0 );
    params.clavePresupuestal = $('#clavePresupuestal').val();
    params.rows = getRows('tbl-itinerario');
    params.total = Number( $("#TotalComision").html().split(",").join("").split(" ").join("") );

    var common = {
        html: 'Aceptar',
        class: 'btn btn-danger', 'data-dismiss': 'modal'
    };

    // Verifico que estoy en modo de revision
    if( window.location.href.includes("idFolio=") ){
            params.method = 'actualizarOficioActual';
            if(arrLinesToDelete.length > 0) {
                params.linesToDelete = arrLinesToDelete;
            }

            //console.log(params);
            $.ajax({
                method:   "POST",
                dataType: "JSON",
                url:      prefix + '/modelo/altaOficioComisionModelo.php',
                data: params,
                async: false
            }).done(function(res) {
                common.class = 'btn btn-danger';
                if (res.success) {
                    common.click = function() {
                        window.location.href = window.home;
                    };
                    common.class = 'btn botonVerde';
                }
                var pie = $('<button>', common);
                muestraModalGeneral(3, window.tituloGeneral, res.msg, pie);
                console.log("se actualizó el oficio");   
            }).fail(function(res) {
                opt = 3;
                muestraMensajeTiempo(res.msg, opt, 'mensajes', msgTime);
            });
    }else{ 
            //var params = getParams('form-add');
            params.method = 'store';
            if (params.rows.length == 0) {
                muestraModalGeneral(3, window.tituloGeneral, res.msg);
                return;
            }
            $.ajax({
                method: "POST",
                dataType: "JSON",
                url: prefix + '/modelo/altaOficioComisionModelo.php',
                data: params,
                async: false
            }).done(function(res) {

                console.log(res);
                var titulo = window.tituloGeneral,
                    pie;
                if (res.success) {
                    common.click = function() {
                        window.location.href = window.home;
                    };
                    common.class = 'btn botonVerde';
                    titulo = 'Operación Exitosa';
                }
                var pie = $('<button>', common);
                muestraModalGeneral(3, titulo, res.msg, pie);
            }).fail(function(res) {
                var titulo = window.tituloGeneral,
                    common = {
                        html: 'Aceptar',
                        class: 'btn btn-danger',
                        'data-dismiss': 'modal'
                    },
                    pie;
                var pie = $('<button>', common);
                muestraModalGeneral(3, titulo, res.msg, pie);
            });
       }  
}

/**
 * se obtinen los munipios del id de entididad seleccionada
 * @param  {[type]} prefix [description]
 * @return html
 */
function getMunicipios($entidad,line) {
    
    var $linea = $entidad.parents('div[id]').eq(0),
        idEstadoDestino = $entidad.val();
    var $municipio = $linea.find('#municipioItinerario'+line),
        params = {
            idEstadoDestino: idEstadoDestino,
            method: 'getMunicipios'
        };
    muestraCargandoGeneral();
    $.ajaxSetup({async: false});
    $.post(window.url + '/modelo/altaOficioComisionModelo.php', params)
        .done(function(res) {
            window.municipioEntidad[idEstadoDestino] = res.content;
            $municipio.multiselect('dataprovider', window.municipioEntidad[idEstadoDestino]);
            // si viene el folio en la url es que se esta revisando un oficio
            if(window.location.href.includes("idFolio=")) {
                
               actualizarMunicipio();
               ocultaCargandoGeneral();
            }
        }).fail(function(res) {
            opt = 3;
            muestraMensajeTiempo(res.msg, opt, 'mensajes', msgTime);
            ocultaCargandoGeneral();
        });
}


/**
 * Se obtinen los munipios del id de entididad y fila del itinerario
   Esta funcion solo se usa durante el proceso de edicion o actualizacion de un oficio
 * @param  {[type]} prefix [description]
 * @return html
 */
function getMunicipiosPorFila($entidad,$filaItinerario) {
    
    var $linea = $entidad.parents('div[id]').eq(0),
        idEstadoDestino = $entidad.val();
    var $municipio = $linea.find('#municipioItinerario'+$filaItinerario),
        params = {
            idEstadoDestino: idEstadoDestino,
            method: 'getMunicipios'
        };
    $.ajaxSetup({async: false});
    $.post(window.url + '/modelo/altaOficioComisionModelo.php', params)
        .done(function(res) {
            window.municipioEntidad[idEstadoDestino] = res.content;
            $municipio.multiselect('dataprovider', window.municipioEntidad[idEstadoDestino]);
            // si viene el folio en la url es que se esta revisando un oficio
            if(window.location.href.includes("idFolio=")) {
               actualizarMunicipio();
            }
        }).fail(function(res) {
            opt = 3;
            muestraMensajeTiempo(res.msg, opt, 'mensajes', msgTime);
        });
}


/**
 * Funcion para la obtención de las entidades federativas
 * @param  {[type]} id [description]
 * @return {[type]}    [description]
 */
function obtenEntidades(id) {
    $.ajaxSetup({async: false});
    $.post(window.url + '/modelo/altaOficioComisionModelo.php', {
            method: 'obtenEntidades'
        })
        .done(function(res) {
            window.entidades = res.content;
            //obtener datos para los selects
            getSelects(window.url);
        });
}

/**
 * Mostrar consola
 * @param  string salida texto a mostrar
 * @return consolajs muesta mensajes en consola
 */
function consoleLog(salida) {
    if (bandLog == 1) {
        console.log(salida)
    }
}

/**
 * Funcion para el calculo de los días que se miestran
 * en el itinerario según dos fechas dadas
 * @param  {[type]} e [description]
 * @return {[type]}   [description]
 */
function calculaDias(e) {
    var $that = $(this),
        $input = $that.find('input');
    var linea;
    var lineaId = "";

    console.log("id input: "+ $input.prop("id") );


    // Si di click en unos de los componentes de fecha del itinerario
    if( $input.prop("id").includes("fechaInicio") || $input.prop("id").includes("fechaTermino") ) {
        lineaId = $input.parent().parent().parent().parent().prop("id");
        console.log("id input parent:"+$input.parent().parent().parent().parent().prop("id"));
    } else {
        /* Aqui entra cuando se carga la página y como no se dio click en un componente de fecha el 
        camino para llegar al id de linea es diferente */
        lineaId = $input.parent().parent().prop("id");
        console.log("entro $input id: "+  $input.parent().parent().prop("id"));
    }

    //if( window.location.href.includes("idFolio=") ) {
    linea   = parseInt( lineaId.substr(5,lineaId.length) )-1;
    /*} else {
        linea   = parseInt( lineaId.substr(5,lineaId.length) )-1;
    } +*/

    var fechaIniSol = $('#fechaInicio').val(),
        fechaTerSol = $('#fechaTermino').val(),
        fecha = $input.val(),
        formatDate = e.date._f,
        nomFecha = ($input.attr('id') == 'fechaInicio'+linea ? 'inicio' : 'termino');
    var m = moment(fechaIniSol, formatDate),
        mm = moment(fecha, formatDate);
    var dif = mm.diff(m, 'days');
    // console.log(dif,fechaSol,fecha,e, $that.data('DateTimePicker').date(), $that.find('input').val());
    // comprobacion de fecha inicio

    if (dif < 0) {
        muestraModalGeneral(3, window.tituloGeneral, 'La <strong>fecha de ' + nomFecha + ' de la actividad</strong>, no puede ser <strong>menor</strong> a la <strong>fecha de inicio de la comisión</strong>');
        $input.val(e.date._i);
        return false;
    }
    var mt = moment(fechaTerSol, formatDate),
        mmt = moment(fecha, formatDate);
    var dift = mmt.diff(mt, 'days');
    // comprobacion de fecha de termino
    if (dift > 0) {
        muestraModalGeneral(3, window.tituloGeneral, 'La <strong>fecha de ' + nomFecha + ' de la actividad</strong>, no puede ser <strong>mayor</strong> a la <strong>fecha de termino de la comisión</strong>');
        $input.val(e.date._i);
        return false;
    }
    // genracion de diferencia entre las fechas de la actividad
    var $linea = $that.parents('div[id]').eq(0),
        diasDif = 0;
    var fechaIniAct = $linea.find('#fechaInicio'+linea).val(),
        fechaTerAct = $linea.find('#fechaTermino'+linea).val();

    //console.log("fecha input:"+ fecha+" dif: "+dif+ "fechaIniAct: "+ fechaIniAct + " fechaTerAct: "+ fechaTerAct);

    var m = moment(fechaIniAct, formatDate),
        mm = moment(fechaTerAct, formatDate);
    var dif = mm.diff(m, 'days'); 
    // se aumenta el dia actual
    if (dif >= 0) {
        diasDif = dif + 1;
    }
    // carga de la información de dias
    // 
    $linea.find('#dias'+linea).val(diasDif);
    $linea.find('#pernocta'+linea).val(dif);


    calculaImporte($linea.find('#dias'+linea),linea);
}

/**
 * Función para el calculo del importe quese agregara
 * o por el cual es la visita en determinado itinerario
 * tomando encuenta los dias y el monto por puesto.
 * @param  {[type]} $elemento [description]
 * @return {[type]}           [description]
 */
function calculaImporte($elemento,linea) {
    var $linea = $elemento.parents('div[id]').eq(0),
        porcentaje =  ( parseInt( $('#cantPernocta').val() ) / 100 ) || 0.5 ,
        $tipoSol = $('#tipoSol'),
        nuevoImporte = 0;
    var dias = parseInt( $linea.find('#dias'+linea).val() ),
        cuota = parseFloat( $linea.find('#cuota'+linea).val().replace(",","") ),
        noches = parseInt( $linea.find('#pernocta'+linea).val() ),
        $inporte = $linea.find('#importe'+linea);
    // Si la comisión es internacional se multiplica por los días unicamente
    // ya que no aplica porcentaje de pernocta
    if ($tipoSol.val() == 2) {
        nuevoImporte = dias * cuota;
        console.log("entro1");
    }
    // en el caso de que la comisión sea nacional se aplica el porcentaje dado por noches
    else if ($tipoSol.val() == 1) {
        // var difNochesDias = dias - noches, nuevoPorcentaje = porcentaje/100;
        var nuevacuota = cuota * porcentaje,
            diffDN = ( dias - noches) ; /*  * nuevoPorcentaje*/ ;
        nuevoImporte = (noches * cuota) + (diffDN * nuevacuota);
        console.log(" dias: "+dias+ " noches: "+ noches + " cuota: "+ cuota +" diffDN: "+ diffDN + " nuevacuota: "+ nuevacuota + " porcentaje: "+ porcentaje +" nuevoImporte: "+nuevoImporte);
    }
    
    // asignación del nuevo importe según el calculo
    if (dias >= 0) {
        $inporte.val(nuevoImporte);
        console.log("importe id: "+$inporte.prop("id") );
    }
    fnRecalculaDiasNoches();

    //console.log("nuevo importe: "+nuevoImporte);
}


/**
 * Función para la aplicación del limite de dias permitido
 * dentro de los campos de fechas, aplicando el minimo y el
 * maximo que se puede elegir
 * @param  {[type]} e     [description]
 * @param  {[type]} $el   [description]
 * @param  {[type]} $sedo [description]
 * @return {[type]}       [description]
 */
function aplicaDias(e, $el, $sedo) {
    var obj = {
        inicio: $el.val(),
        termino: $seudo.val()
    };
    $seudo.data('DateTimePicker').minDate(e.date);
}

/**
 * se obtiene la informacion de los formularios por
 * cada una de las lineas generadas en el itinerario
 * @param  {[type]} tbl     [description]
 * @param  {[type]} sendmsg [description]
 * @return {[type]}         [description]
 */
function getRows(tbl, sendmsg) {
    var $tbl = $('#' + tbl),
        rows = [],
        flag = 0,
        campos = getMatchets(tbl + ' #linea1'),
        msg = '',
        sendmsg = sendmsg || true,
        nombreCampo = {
            'municipioItinerario': 'Municipio',
            'fechaInicio': 'Fecha Inicio',
            'fechaTermino': 'Fecha Término',
            'cuota': 'Cuota/Unidades diaria',
            'dias': 'Días',
            'importe': 'Importe',
            'pernocta': 'Pernocta',
            'pasaje': 'Pasaje',
            'idEstadoDestino': 'Estado',
            'idItinerario': 'identificador itinerario',
            'zonaEconomica': 'Zona Economica'
        };

       // Si no tomo valor con linea1, se hace para linea0
       if(campos.length == 0) {
        campos = getMatchets(tbl + ' #linea0');
       } 

    var lineaItinerario;
    //Busco el numero de linea en el que estamos. Solo se utiliza en modo edicion
    //if( window.location.href.includes("idFolio=") ) {
      if(campos.length >0) { 
         var found = false;
         var i=0;
         while(!found)
         {
            if(campos[i].indexOf("cuota") !== -1) {
                lineaItinerario = campos[i].substr(5,campos[i].length);
                found= true;
            }
            else {
                i++;
            }
         }
    //}        

    // eliminacion de datos que no son obligatorios
    //if ( window.location.href.includes("idFolio=") ) {

        $.each(['pernocta'+lineaItinerario, 'pasaje'+lineaItinerario], function(index, val) {
            campos.splice(campos.indexOf(val));
        });
     }
    /*}  else {
        $.each(['pernocta', 'pasaje'], function(index, val) {
            campos.splice(campos.indexOf(val));
        });
    } */   

    if(campos.length >0) { 
        // procesado de las lineas
        $.each($tbl.find('div[id]'), function(index, val) {
            oldIndex = index;
            index = $(val).attr('id');
            if(index != "linea1") {
                campos = getMatchets(tbl + ' #' + index);
                $.each(['pernocta'+lineaItinerario, 'pasaje'+lineaItinerario], function(index, val) {
                    campos.splice(campos.indexOf(val));
                });
            }
            var paramsItinerario = getParams(tbl + ' #' + index),
                errCampo = '',
                f = 0;
            $.each(paramsItinerario, function(ind, valor) {
                if (campos.indexOf(ind) !== -1) {
                    if (($.isEmptyObject(valor) && valor != null && valor != ""/*|| valor == 0*/) && sendmsg) {
                        errCampo += (f == 0 ? '' : ',') + ' ' + nombreCampo[ind];
                        f++;
                        flag++;
                    }
                }
            });
            lineaItinerario++;
            msg += (f != 0 ? "<br>Linea #" + (oldIndex + 1) + " Campos: " + errCampo : '');
            // asignacion de la informacion en caso de ser correcto
            if (flag == 0) {
                rows[oldIndex] = paramsItinerario;
            }
        });
     }   
    if (flag && sendmsg) {
        muestraModalGeneral(3, window.tituloGeneral, 'Es necesario colocar los datos que se indican a continuación:' + msg);
        // colocacion de lineas en cero
        rows = [];
    }
    return rows;
}

/**
 * Función para limpiar los datos de complemento del empleado
 * @return {[type]} [description]
 */
function limpiaEmpleado() {
    $('#eNombre').val('');
    if( window.isEmployeeSelect ) {
      $('#eRFC').val('');
      $('#Epuesto').val('');
    }
    $('#eJerarquia').val('');
}

/**
 * Funcion para la obtención de la calve oresupuestal
 * asi como su asignación a su contenedor
 * @return {[type]} [description]
 */
function obtenClavePresupuestal() {

    if($("#selectUnidadEjecutora").val()!= "-1") {

        // Se sacó este código del if($('#tipoGasto').val()==1){
        $.ajaxSetup({async: false});
        $.post(url + '/modelo/altaOficioComisionModelo.php', {
            method: 'obtenClavePresupuestal',
            UR: $("#selectUnidadNegocio").val(),
            UE: $("#selectUnidadEjecutora").val()
        })
        .then(function(res) {
            if (res.success) {
                window.clavesGeneral = res.clavesGeneral;
                window.clavesCombustibles = res.clavesCombustibles;
                window.peajeTransporte = res.peajeTransporte;
                $('#clavePresupuestal').multiselect('dataprovider', clavesGeneral);
                $('#clavePresupuestalCombustible').multiselect('dataprovider', clavesCombustibles);
                $('#clavePresupuestalPeaje, #clavePresupuestalTransportePublico').multiselect('dataprovider', peajeTransporte);
            }
        });
        if($('#tipoGasto').val()==1){
        }
    } else {
        $('#tipoGasto').multiselect('select', '0');
        $('#tipoGasto').multiselect('rebuild');
        fnBloquearFechas(true);
        muestraModalGeneral(3, window.tituloGeneral, 'Debe seleccionar una UE para establecer la clave presupuestal ');
    }            
}

/**
 * Función para la obtención de la fecha actual
 * @return {[type]} [description]
 */
function fechaActual() {
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth() + 1; //enero es 0
    var yyyy = today.getFullYear();

    if (dd < 10) {
        dd = '0' + dd
    }
    if (mm < 10) {
        mm = '0' + mm
    }

    return dd + '-' + mm + '-' + yyyy;
}

/**
 * Función para la obtención de los paises y asignación
 * en su contenedor o elemento select dentro del itinerario
 * @return {[type]} [description]
 */
function obtenPaises() {
    $.ajaxSetup({async: false});
    $.post(url + '/modelo/altaOficioComisionModelo.php', {
            method: 'obtenPaises'
        })
        .then(function(res) {
            // carga de países en variable global
            datosPaises = res.content;
        });
}

/**
 * Función para el redonde del importe dentro
 * del itinerario en cada actividad
 * @param  {[type]} tabla [description]
 * @return {[type]}       [description]
 */
function reordena(tabla) {
    var $tbl = $(tabla || '#tbl-itinerario');
    var lineas = $tbl.find('div[id]');
    var newLineNumber = 1;
    var newLineComponentNumber = 0;
    lineas.each(function(index, el) {
        var oldId = $(el).find('.renglon').html()-1;
        $(el).find('.renglon').html(++index);

        // actualizo el número de linea para cada uno de los itinerarios que quedan
        //var idLine = $(el).prop("id");
        //var lineNumber = idLine.substr(5,idLine.length);
        

        //if(lineNumber != "1") {

            //newLineNumber = parseInt(lineNumber) - 1;

            $(el).find('input[name], select[name], textarea[name]').each(function(index, el) {
                var $self = $(this);
                
                //newID = $self.prop("id").replace(String(newLineNumber+1),"") + newLineNumber; 
                newID = $self.data("cn")+newLineComponentNumber;
                newID = $self.attr('id').substr(0,$self.attr('id').lastIndexOf(oldId))+newLineComponentNumber;
                $self.prop("id",newID);  
                $self.prop("name",newID);
            });
            $(el).prop("id","linea"+ (newLineNumber));
            newLineNumber++;
            newLineComponentNumber++;
        //}   
    });
}

/**
 * Función para la comprobación dle empleado
 * que ingresa a generar la solicitud.
 * @return {[type]} [description]
 */
function compruebaEmpleadoUsuario() {
    var pie = $('<button>', {
        html: 'Aceptar',
        class: 'btn botonVerde',
        'data-dismiss': 'modal',
        click: function() {
            window.location.href = window.home;
        }
    });
    $.ajaxSetup({async: false});
    $.post(url + '/modelo/altaOficioComisionModelo.php', {
            method: 'compruebaEmpleadoUsuario'
        })
        .then(function(res) {
            if (!res.success) {
                muestraModalGeneral(3, window.tituloGeneral, res.msg, pie);
                return false;
            }
            // procesamiento de la información obtenida del usuario
            if (perfil != 11) {
                $('#idEmpleado').multiselect('select', res.id).multiselect('disable');
            }
        });
}

/**
 * Función para el calculo de la diferencia entre
 * dos fechas dadas, retornando los días de diferencia
 * @param  {[type]} fechaInicio  [description]
 * @param  {[type]} fechaTermino [description]
 * @return {[type]}              [description]
 */
function calculaDiff(fechaInicio, fechaTermino) {
    var formatDate = $('.componenteFeriadoAtras').data('DateTimePicker').format();
    if (typeof fechaInicio !== 'string' && typeof fechaTermino !== 'string') {
        return 0;
    }
    var fi = moment(fechaInicio, formatDate),
        ft = moment(fechaTermino, formatDate);
    return ft.diff(fi, 'days');
}

/**
 * [confirmaDias description]
 * @param  {[type]} el [description]
 * @return {[type]}    [description]
 */
function confirmaDias(el,linea) {
    var $el = $(el),
        $parent = $el.parents('div[id]').eq(0),

        currentVal = $el.val(),
        id = $el.attr('id'),
        fechaInicio = $parent.find('#fechaInicio').val(),
        fechaTermino = $parent.find('#fechaTermino').val(),
        comp = false;
    var tmpCurrentVal = currentVal;
    var diff = calculaDiff(fechaInicio, fechaTermino);
    if (id == 'dias') {
        comp = currentVal == (parseInt(diff) + 1);
    } else {
        // si el valor ingresado es igual al numero de noches correctas
        comp = currentVal == diff;
    }

    if (comp) {
        calculaImporte($el,linea);
    } else {
        switch (id) {
            case "dias":
                muestraModalGeneral(3, window.tituloGeneral, 'El número de días debe coincidir con el intervalo de fecha de inicio y fecha fin ');
                $el.val(parseInt(diff) + 1);
                break;
            case "pernocta":
                muestraModalGeneral(3, window.tituloGeneral, 'El número de noches debe coincidir con el intervalo de fecha de inicio y fecha fin ');
                $el.val(parseInt(diff));
                break;
        }

    }

    return comp;
}

/**
 * función para la actualización del monto del pasaje
 * según los datos colocados en pasaje y combustible
 * @return {[type]} [description]
 */
function actualizarPasaje() {
    $('#montoPasaje').val(parseInt($('#montoPeaje').val()) + parseInt($('#montoCombustible').val()));
}

/**
* Precarga la informacion de un oficio dado un folio (GET) para que el autorizador pueda revizarla
**/
function cargarInformacionFolio() {

    var zona = ""; 


    var fechaInicioComision = $('#fechaInicio').val(),
        fechaTerminoComision = $('#fechaTermino').val(),
        homologado = "";


    params = {
        method: 'getDataOficioComision',
        folio: idFolio
    }
    //console.log("entro");
    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: window.url + '/modelo/altaOficioComisionModelo.php',
        data: params,
        async: false
    }).done(function(res) {
        console.log(res);
        globalDatosGenerales = res[0];

        //window.montoDiario = res[2];
        //var montoDiario = window.montoDiario;

        // guardo los itinerarios en una var. global porque se van a procesar en otra función
        globalItinerario = res[1];

           // comportamiento del elemento estado destino
        for (var i = 0; i < globalItinerario.length; i++) {

            $(document).on('change', '#idEstadoDestino'+i, function() {
                
                var $that = $(this),
                    municipioEntidad = window.municipioEntidad;
                var entidad = parseInt($that.val()),
                    $municipio = $that.parents('div[id]').eq(0).find('#municipioItinerario'+i);
                //  comprobacion de elemento no vacio
                if (entidad == 0 || entidad == '') {
                    return;
                }
                //  si no se encuentra el municipio en la variable se consultan los datos
                if (!municipioEntidad.hasOwnProperty(entidad)) {
                   
                    getMunicipiosPorFila($that,i);
                }
                // si se cuenta con datos en la variale se optienen y se agregan los datos
                else {
                    $municipio.multiselect('dataprovider', municipioEntidad[entidad]);
                }
            });

        }

        window.editable = res[0].editable;
        if(idFolio!=""&&!window.editable){
            $("#btn-update, #btn-add, :button.glyphicon-trash").hide();
        }

        $("#idNuViaticos").val(res[0].idNuViaticos);

        // Lleno el campo UR
        $("#selectUnidadNegocio > option").each(function() {
             if($(this).val() == res[0].ur) {
                $(this).prop("selected",1);
             }
        });
        $('#selectUnidadNegocio').multiselect('rebuild');

        // Lleno el campo UE
        $("#selectUnidadEjecutora > option").each(function() {
             if($(this).val() == res[0].ue) {
                $(this).prop("selected",1);
             }
        });
        $('#selectUnidadEjecutora').multiselect('rebuild');

        // Lleno el campo Oficio No:
        $("#noOficio").val(res[0].folio);

        // Lleno el campo empleado

        /*$("#idEmpleado > option").each(function() {
        
             if($(this).val() == res[0].empleado) {
                console.log("entro condicion");
                $(this).prop("selected",1);
             }
        });*/
        $('#idEmpleado').multiselect('select',res[0].empleado);
        $('#idEmpleado').multiselect('rebuild');
        $('#idEmpleado').change();

        // Lleno el campo tipo viatico
        $("#tipoGasto > option").each(function() {
        
             if($(this).val() == res[0].tipoViatico) {
                $(this).prop("selected",1);
             }
        });
        $('#tipoGasto').multiselect('rebuild');
        $('#tipoGasto').change();


        // Lleno la fecha de inicio
        $("#fechaInicio").val(res[0].fechaInicio.split(" ")[0]);

        // Lleno la fecha de fin
        $("#fechaTermino").val(res[0].fechaFin.split(" ")[0]);


        // Lleno objetivo de la comision
        $("#txtAreaObs").val(res[0].objetivoComision);

        //Lleno el tipo de comisión
        $("#tipoSol > option").each(function() {
        
             if($(this).val() == res[0].tipoSolicitud) {
                $(this).prop("selected",1);
             }
        });
        $('#tipoSol').multiselect('rebuild');

        /** 
           Selecciono pasaje aereo si el tipo de comision es internacional e inactivo el select, 
           de lo contrario, actualizo el tipo de transporte que se guardó
        **/
        if($('#tipoSol').val() == 2) {
            $("#trasnporte").val("4");
        }
        else {
            //Lleno el tipo de Transporte
            $("#trasnporte > option").each(function() {
            
                 if($(this).val() == res[0].tipoTransporte) {
                    $(this).prop("selected",1);
                 }
            });
        }

        $('#trasnporte').multiselect('rebuild');

        $('#cantPernocta').val(res[0].cantPernocta);

        if($('#tipoSol').val() == 2) {
           $('#trasnporte').multiselect('disable'); 
        }

        // verifico si se selecciono la opcion de homologar
        if(res[0].homologar == 1) {

            homologado = res[0].empleado_homologado;
            /*$('#homologar').prop("checked",1);

            $("#idEmpleadoHomologar > option").each(function() {
                    
                if($(this).val() == res[0].empleado_homologado) {
                         
                    $(this).prop("selected",1);

                }
            });
            $('#idEmpleadoHomologar').multiselect('rebuild');*/

                //Invoco el onchange para que se actualice la informacion que depende de este campo
            //$('#idEmpleadoHomologar').change();              
        }

         var lineaId = 1;
         var renglonId = 1;
         var tbl = $('#tbl-itinerario');
         var tipoCom = $('#tipoSol').val();
         var template = "";
         


        // Itero por todos los registro del itinerario para añadirlos al grid de itinerario
       
        for (var i = 0; i < res[1].length;  i++) {
         console.log("itinerario"+i+" "+res[1][i].fechaInicio);   

         //var d1 = Date.parse(res[1][i].fechaInicio);
         //var d2 = Date.parse(res[1][i].fechaFin);

         //var unDia = 1000 * 60 * 60 * 24;
         // Calcula la diferencia entre dos fechas
         //var diferencia_ms = Math.abs(d1 - d2);

         // Convierte a dias
         var dias   =  res[1][i].dias;//Math.round(diferencia_ms/unDia)+1;
         var noches =  res[1][i].ind_pernocta;//dias - 1;
 
         //var montoCom = (tipoCom == 1 ? res[1][i].amt_importe/2*(dias+noches) /*/2*/ : montoDiario.extrangero);
         console.log( "importe: " + parseFloat(res[1][i].amt_importe) );
         var diasNoches = (parseInt(dias)+parseInt(noches));
         var montoCom   = (parseFloat(res[1][i].amt_importe)/2)*diasNoches;
         var zona = res[1][i].zonaEconomica;
         var idItinerario = res[1][i].idItinerario;
         console.log("importe: "+res[1][i].amt_importe+" dias: "+dias+" noches: "+ noches+ " diasNoches "+ diasNoches + " i: "+i+" montoCom= "+montoCom);   
           if (tipoCom == 1) {
              contenTipoSol =
                '<div class="w17p fl vam h35">' +
                '<select id="idEstadoDestino'+i+'" name="idEstadoDestino'+i+'" data-cn="idEstadoDestino" class="form-control idEstadoDestino'+i+'" data-todos="true" ></select>' +
                '</div>' +
                '<div class="w17p fl vam h35">' +
                '<select id="municipioItinerario'+i+'" name="municipioItinerario'+i+'" data-cn="municipioItinerario" class="form-control municipioItinerario'+i+'" data-todos="true" ></select>' +
                '</div>';
            } else {
                contenTipoSol =
                    '<div class="w34p fl vam h35">' + '<select id="pais'+i+'" name="pais'+i+'" data-cn="pais" class="form-control pais'+i+'" data-todos="true" ></select>' + '</div>';
            }

           //lineaId =  'linea'+lineaId;
            if (tipoCom == 1) {

                $("#zonaEconomica").show();

                $("#cuota").prop('class','w7p  falsaCabeceraTabla');
                $("#dias").prop('class', 'w7p  falsaCabeceraTabla');
                $("#importe").prop('class', 'w9p  falsaCabeceraTabla');

                template =
                    '<div class="row w100p borderGray" id="linea' + lineaId + '">' +
                    '<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                    '<div class="w3p fl vam h35 pt4"> <span  class="btn btn-danger btn-xs glyphicon glyphicon-remove row-delete"></span> </div>' +
                    '<div class="w3p fl vam h35 pt10 renglon">' + renglonId + '</div>' +
                    contenTipoSol +
                    '<div class="w12p fl vam h35">' +
                    '<component-date-feriado2 id="fechaInicio'+i+'" name="fechaInicio'+i+'" data-cn="fechaInicio" placeholder="Fecha inicio" value="' + fechaInicioComision + '"></component-date-feriado2>' +
                    '</div>' +
                    '<div class="w12p fl vam h35">' +
                    '<component-date-feriado2 id="fechaTermino'+i+'" name="fechaTermino'+i+'" data-cn="fechaTermino" placeholder="Fecha Término" value="' + fechaTerminoComision + '"></component-date-feriado2>' +
                    '</div>' +
                    '<div class="w7p fl vam h35"><component-text id="zonaEconomica'+i+'" name="zonaEconomica'+i+'" data-cn="zonaEconomica" readonly value="' + zona + '" class="cuoto"></component-text></div>' +
                    '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
                    '<div class="w5p fl vam h35"><component-text id="cuota'+i+'" name="cuota'+i+'" data-cn="cuota" readonly value="'+formatoComas(fixDecimales( String(redondeaDecimal(res[1][i].amt_importe) )))+'" class="cuoto"></component-text></div>' +
                    '<div class="w6p fl vam h35"><component-text id="dias'+i+'" name="dias'+i+'" data-cn="dias" readonly value="1"></component-text></div>' +
                    '<div class="w6p fl vam h35"><component-text id="pernocta'+i+'" name="pernocta'+i+'" data-cn="pernocta" readonly value="'+noches+'"></component-text></div>' +
                    '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
                    '<div class="w8p fl vam h35"><component-text id="importe'+i+'" name="importe'+i+'" data-cn="importe" value="'+ formatoComas(fixDecimales( String(redondeaDecimal(montoCom) ))) +'" class="importe cuoto" readonly></component-text></div>' +
                    '<input type="hidden" id="idItinerario'+i+'" name="idItinerario'+i+'" data-cn="idItinerario" value="'+idItinerario+'" >'+
                    '</div>' +
                    '</div>';

                    console.log("typeof: redondeaDecimal "+ typeof( montoCom ) + " value: " + montoCom );
                    console.log("typeof: redondeaDecimal "+ typeof( redondeaDecimal(montoCom) ) + " value: " + redondeaDecimal(montoCom) );
                    console.log("typeof: fixDecimales "+ typeof( fixDecimales( String(redondeaDecimal(montoCom))) ) + " value: " + fixDecimales( String(redondeaDecimal(montoCom))) );
                    console.log("typeof: formatoComas "+ typeof( formatoComas(fixDecimales( String(redondeaDecimal(montoCom) ))) ) + " value: " + formatoComas(fixDecimales( String(redondeaDecimal(montoCom) ))) );
            }
            else {

                $("#zonaEconomica").hide();

                $("#cuota").prop('class','w11p  falsaCabeceraTabla');
                $("#dias").prop('class', 'w9p  falsaCabeceraTabla');
                $("#importe").prop('class', 'w10p  falsaCabeceraTabla');

                template =
                    '<div class="row w100p borderGray" id="linea' + lineaId + '">' +
                    '<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                    '<div class="w3p fl vam h35 pt4"> <span  class="btn btn-danger btn-xs glyphicon glyphicon-remove row-delete"></span> </div>' +
                    '<div class="w3p fl vam h35 pt10 renglon">' + renglonId + '</div>' +
                    contenTipoSol +
                    '<div class="w12p fl vam h35">' +
                    '<component-date-feriado2 id="fechaInicio'+i+'" name="fechaInicio'+i+'" data-cn="fechaInicio" placeholder="Fecha inicio" value="' + fechaInicioComision + '"></component-date-feriado2>' +
                    '</div>' +
                    '<div class="w12p fl vam h35">' +
                    '<component-date-feriado2 id="fechaTermino'+i+'" name="fechaTermino'+i+'" data-cn="fechaTermino" placeholder="Fecha Término" value="' + fechaTerminoComision + '"></component-date-feriado2>' +
                    '</div>' +
                    '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
                    '<div class="w9p fl vam h35"><component-text id="cuota'+i+'" name="cuota'+i+'" data-cn="cuota" readonly value="'+formatoComas(fixDecimales( String(redondeaDecimal(res[1][i].amt_importe) )))+'" class="cuoto"></component-text></div>' +
                    '<div class="w9p fl vam h35"><component-text id="dias'+i+'" name="dias'+i+'" data-cn="dias" readonly value="1"></component-text></div>' +
                    '<div class="w6p fl vam h35"><component-text id="pernocta'+i+'" name="pernocta'+i+'" data-cn="pernocta" readonly value="'+noches+'"></component-text></div>' +
                    '<div class="w2p fl vam h35" style="line-height: 34px;">$</div>'+
                    '<div class="w8p fl vam h35"><component-text id="importe'+i+'" name="importe'+i+'" data-cn="importe" readonly value="' + formatoComas(fixDecimales( String(redondeaDecimal(montoCom) ))) + '" class="importe"></component-text></div>' +
                    '<input type="hidden" id="idItinerario'+i+'" name="idItinerario'+i+'" data-cn="idItinerario" value="'+idItinerario+'" >'+
                    '</div>' +
                    '</div>';


            }  


            $(document).on('change', '#idEstadoDestino'+i, function() {

                var $that = $(this);
                var    municipioEntidad = window.municipioEntidad;
 
                i = parseInt($that.prop("id").substr(15,$that.prop("id").length));

                var entidad = parseInt($that.val()),
                    $municipio = $that.parents('div[id]').eq(0).find('#municipioItinerario'+i);
                //  comprobacion de elemento no vacio
                if (entidad == 0 || entidad == '') {
                    return;
                }
                //  si no se encuentra el municipio en la variable se consultan los datos
                if (!municipioEntidad.hasOwnProperty(entidad)) {
                    getMunicipios($that,i);
                }
                // si se cuenta con datos en la variale se optienen y se agregan los datos
                else {
                    $municipio.multiselect('dataprovider', municipioEntidad[entidad]);
                }
                actualizarZonaEconomica($(this).val(),i);
            
            });

            $(document).on('change', '#pais'+i, function() {

                var itemSel;

                if( $('#homologar').is(':checked') && $('#idEmpleadoHomologar').val() != "0" ) {

                   itemSel = $('#idEmpleadoHomologar').val();
                }
                else {

                   itemSel = $('#idEmpleado').val();

                }
                params = {
                   method: 'getDataEmploye',
                };

                params.employe = itemSel;
                params.idFolio = idFolio;
                params.consultarComisiones = ( $('#homologar').is(':checked') ? 0 : 1 );
                $.ajax({
                    method: "POST",
                    dataType: "JSON",
                    url: window.url + '/modelo/altaOficioComisionModelo.php',
                    data: params,
                    async: false
                }).done(function(res) {
                    if (res.success) {

                        var param = {};

                        param.method    = "obtenerCuotaInternacional";
                        param.jerarquia = res.empleado['jerarquia'];
                        param.tipoComision = "2";

                        $.ajax({
                            method:   "POST",
                            dataType: "JSON",
                            url: window.url + '/modelo/altaOficioComisionModelo.php',
                            data: param,
                            async: false
                        }).done( function(res) {
                            $("#cuota"+i).val( formatoComas(fixDecimales( String(redondeaDecimal(res.cuota) ))) );
                            var dias    = parseInt( $("#dias"+i).val() );
                            var importe = parseFloat(res.cuota) * dias;
                            $("#importe"+i).val( formatoComas(fixDecimales( String(redondeaDecimal(importe) ))) );
                        });
                        
                   
                    }
                }).fail(function(res) {
                    muestraModalGeneral(3, window.tituloGeneral, 'Ocurrió un incidente inesperado mientras se obtenía el empleado. Favor de contactar al administrador.');
                });
            });         
           
           /*template =
                '<div class="row w100p borderGray" id="linea' + lineaId +'">' +
                '<div class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                '<div class="w3p fl vam h35 pt4"> <span  class="btn btn-danger btn-xs glyphicon glyphicon-remove row-delete"></span> </div>' +
                '<div class="w3p fl vam h35 pt10 renglon">' + renglonId + '</div>' +
                contenTipoSol +
                '<div class="w12p fl vam h35">' +
                '<component-date-feriado2 id="fechaInicio'+i+'" name="fechaInicio'+i+'" placeholder="Fecha inicio" value="' + res[1][i].fechaInicio + '"></component-date-feriado2>' +
                '</div>' +
                '<div class="w12p fl vam h35">' +
                '<component-date-feriado2 id="fechaTermino'+i+'" name="fechaTermino'+i+'" placeholder="Fecha Término" value="' + res[1][i].fechaFin + '"></component-date-feriado2>' +
                '</div>' +
                '<div class="w7p fl vam h35"><component-text id="zonaEconomica'+i+'" name="zonaEconomica'+i+'" readonly value="' + zona + '" class="cuoto"></component-text></div>' +
                '<div class="w7p fl vam h35"><component-text id="cuota'+i+'" name="cuota'+i+'" readonly  value="'+res[1][i].amt_importe+'" title="'+res[1][i].amt_importe+'"></component-text></div>' +
                '<div class="w6p fl vam h35"><component-decimales id="dias'+i+'" name="dias'+i+'" value="'+dias+'" onkeyup="confirmaDias(this,'+i+')"></component-decimales></div>' +
                '<div class="w6p fl vam h35"><component-decimales id="pernocta'+i+'" name="pernocta'+i+'" value="'+noches+'" onkeyup="confirmaDias(this,'+i+')"></component-decimales></div>' +
                '<div class="w10p fl vam h35"><component-text id="importe'+i+'" name="importe'+i+'" readonly value="' + montoCom + '" class="importe"></component-text></div>' +
                '<input type="hidden" id="idItinerario'+i+'" name="idItinerario'+i+'" value="'+ res[1][i].idItinerario +'" >'+
                '</div>' +
                '</div>'; */

            tbl.append(template);

            //$("#fechaInicioItinerario").val(res[1][i].fechaInicio);
            //$("#fechaTerminoItinerario").val(res[1][i].fechaFin);
            fnEjecutarVueGeneral('linea'+lineaId);
            fnFormatoSelectGeneral('#' + 'linea' + lineaId + ' #municipioItinerario'+i+', #' + 'linea' + lineaId + ' #idEstadoDestino'+i+', #' + 'linea' +lineaId + ' #pais'+i);
            fnCrearDatosSelect(window.entidades, '#' + 'linea' +lineaId + ' #idEstadoDestino'+i, 0, 1);
            

            //Selecciono el estado del itinerario
            $("#idEstadoDestino"+i+" > option").each(function() {
                
                 if($(this).val() == res[1][i].estadoDestino) {
                     
                    $(this).prop("selected",1);
                 }
            });
            $('#idEstadoDestino'+i).multiselect('rebuild');

            //Invoco el onchange para que se carguen los municipios
            $('#idEstadoDestino'+i).change();


            fnCrearDatosSelect(window.datosPaises, '#' + 'linea'+ lineaId + ' #pais'+i, 0, 1);


            //Selecciono el país de la linea del itinerario
            var cambiarTablaATipoInternacional = false;
            $("#pais"+i+" > option").each(function() {
                 if($(this).val() == res[1][i].pais) {
                    $(this).prop("selected",1);
                    cambiarTablaATipoInternacional = true;
                 }
            });

            $('#pais'+i).multiselect('rebuild');

            if(cambiarTablaATipoInternacional){
                    $('#tipoNacionalArea').addClass('hidden');
                    $('#tipoInterArea').removeClass('hidden');
            }

            // comportamiento del cambio del calendario
            fnConfiguracionDeParesDeFechas(i,res[1][i].fechaInicio,res[1][i].fechaFin);
            lineaId++;
            renglonId++;
        }
        if(res[0].tipoSolicitud==2){
                $('#tipoNacionalArea').addClass('hidden');
                $('#tipoInterArea').removeClass('hidden');
        }
    }).fail(function(res) {
        consoleLog(res);
        console.log("fallo");
        muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
        ocultaCargandoGeneral();
    });

    setTimeout(function(){
        $("#idEmpleado").trigger("change");
        $('#trasnporte').multiselect("disable");
        if($("#tipoSol").val()!=2&&$('#cantPernocta').val()==""){
            $('#cantPernocta').val("50");
        }
        $('#cantPernocta').attr('readonly', true);
        obtenClavePresupuestal();
        actualizarClavePresupuestal();
        actualizarMunicipio();
        actualizarPais();
        fnBloquearFechas(true);
        if(homologado!=""){
            $('#homologar').prop("checked",1);
            $('#idEmpleadoHomologar').multiselect('select', homologado);
            $('#idEmpleadoHomologar').multiselect('rebuild');
            $('#idEmpleadoHomologar').change();
        }

        for(var c=0;c<$(".renglon").length-1;c++){
            fnBloquearLinea(true,c);
        }

        if(!window.editable){
            $( ":input" ).attr('readonly', true);
            $( ":input" ).prop('disabled',true)
            $( "textarea" ).attr('readonly', true);
            $('select').each(function() {
               $(this).multiselect('disable');
            });
            $("#add").hide();
            $(".row-delete").hide();
            $(":button.glyphicon-home").removeAttr("readonly");
            $(":button.glyphicon-home").removeProp("disabled");
        }
    },500);
}

/**
        Carga los municipios de los itinerarios una vez que se carga el 
        estado solo se utiliza cuando se esta revizando la información
        de un oficio 
**/
function actualizarMunicipio() {

    //Selecciono el municipio del itinerario
    for (var i = 0; i < globalItinerario.length; i++) {
        $("#municipioItinerario"+i+" > option").each(function() {
                
                 if($(this).val() == globalItinerario[i].municipioDestino) {
            
                    $(this).prop("selected",1);
                 }
        });

        $('#municipioItinerario'+i).multiselect('rebuild');
    }
}

/**
        Carga los países de los itinerarios solo se utiliza cuando
        se esta revisando la información de un oficio 
**/
function actualizarPais() {

    //Selecciono el país del itinerario
    for (var i = 0; i < globalItinerario.length; i++) {
        $("#pais"+i+" > option").each(function() {
                
                 if($(this).val() == globalItinerario[i].pais) {
            
                    $(this).prop("selected",1);
                 }
        });

        $('#pais'+i).multiselect('rebuild');
    }
}


/**
        Carga la clave presupuestal de los itinerarios, solo se utiliza cuando se esta revizando la información
        de un oficio 
**/
function actualizarClavePresupuestal() {


        $("#clavePresupuestal > option").each(function() {
                
                 if($(this).val() == globalDatosGenerales.clavePresupuestal) {
            
                    $(this).prop("selected",1);
                 }
        });

        $('#clavePresupuestal').multiselect('rebuild');
    
    
}


function actualizarZonaEconomica(idEstado,linea) {


    params = {
            method: 'getZonaEconomica',
            estado: idEstado
        }
        
        $.ajax({
            method: "POST",
            dataType: "JSON",
            url: window.url + '/modelo/altaOficioComisionModelo.php',
            data: params,
            async: false
        }).done(function(res) {
            
                //console.log("se cargaron las zonas exitosamente");
                //console.log(res);
                $("#zonaEconomica"+linea).val(res.zona);
                actualizarCuotaEnFuncionZona(idEstado,linea,res.idZona);
                

            // asignación de perfil obtenido
            window.perfil = res.perfil;
            ocultaCargandoGeneral();
        }).fail(function(res) {
            consoleLog(res)
            muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
            ocultaCargandoGeneral();
        });

}


function actualizarCuotaEnFuncionZona(idEstado,linea,zona) {

    var idEmpleado;



    if( $('#homologar').is(':checked') && $('#idEmpleadoHomologar').val() != "0" ) {

       idEmpleado = $('#idEmpleadoHomologar').val();
    }
    else {

       idEmpleado = $('#idEmpleado').val();

    }

    params = {
         method: 'actualizarCuotaEnFuncionZona',
         estado: idEstado,
         idEmpleado: idEmpleado, 
         zona:zona
    }

    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: window.url + '/modelo/altaOficioComisionModelo.php',
        data: params,
        async: false
    }).done(function(res) {

        var dias   = parseInt( $("#dias"+linea).val() );
        var noches = parseInt( $("#pernocta"+linea).val() );
        var cuota  = Number(res.cuota);
        var mediaCuota = cuota.toFixed(2)/2;

        var importe = mediaCuota * (dias+noches);

        if(res.cuota != null) {
            //console.log(redondeaDecimal(res.cuota,2));
           //console.log( formatoComas(fixDecimales( String(redondeaDecimal(res.cuota) ))); 
           $("#cuota"+linea).val( formatoComas(fixDecimales( String(redondeaDecimal(res.cuota) ))) );
           $("#importe"+linea).val( formatoComas(fixDecimales( String(redondeaDecimal( String(importe) ) ))) ); 
           fnRecalculaDiasNoches();
        }
        else {
            muestraModalGeneral(3, window.tituloGeneral, 'No se encontró cuota para la jerarquía y zona especificada ');
            //muestraMensajeTiempo("No se encontró cuota para la jerarquía y zona especificada", 3, 'mensajes', msgTime);
            ocultaCargandoGeneral();
        }
    }).fail(function(res) {
            //consoleLog(res)
            muestraModalGeneral(3, window.tituloGeneral, 'No se encontró cuota para la jerarquía y zona especificada ');
            //muestraMensajeTiempo("No se encontró cuota para la jerarquía y zona especificada", 3, 'mensajes', msgTime);
            ocultaCargandoGeneral();
        });
}

function obtenerTransporte($db) {

    $.ajax({
        method: "POST",
        dataType: "JSON",
        url: window.url + '/modelo/altaOficioComisionModelo.php',
        data: params,
        async: false
    }).done(function(res) {
        var options = "";
        for (var i = 0; i < res.length; i++) {
            options += "<option value="+res[i]["idTransporte"]+">"+res[i]["transporte"]+"</option>";
        }
        $("#trasnporte").append(options);
    });
}

function actualizarMontosCuotaItinerario() {

        var totalItinerarios = $(".renglon").length;

        var itemSel;

        if( $('#homologar').is(':checked') && $('#idEmpleadoHomologar').val() != "0" ) {

               itemSel = $('#idEmpleadoHomologar').val();
        }
        else {

               itemSel = $('#idEmpleado').val();

             }

        var params = {};     

        params.method = 'getDataEmploye';

        params.employe = itemSel;
        params.idFolio = idFolio;
        params.consultarComisiones = ( $('#homologar').is(':checked') ? 0 : 1 );
                $.ajax({
                    method: "POST",
                    dataType: "JSON",
                    url: window.url + '/modelo/altaOficioComisionModelo.php',
                    data: params,
                    async: false
                }).done(function(res) {
                    if (res.success) {

                        var param = {};

                        param.method    = "obtenerCuotaInternacional";
                        param.jerarquia = res.empleado['idJerarquia'];
                        param.tipoComision = $("#tipoSol").val();

                        $.ajax({
                            method:   "POST",
                            dataType: "JSON",
                            url: window.url + '/modelo/altaOficioComisionModelo.php',
                            data: param,
                            async: false
                        }).done( function(res) {
                            if(totalItinerarios > 0) {
                                for (var i = 0; i < totalItinerarios; i++) {
                                     $("#cuota"+i).val( formatoComas(fixDecimales( String(redondeaDecimal(res.cuota) ))) );
                                     var totalDiasNoches;
                                     var montoMediaJornada =  parseFloat(res.cuota)/2;
                                     var importe;
                                     if( $("#tipoSol").val() == "1" ) {
                                        totalDiasNoches = parseInt( $("#dias"+i).val() ) + parseInt( $("#pernocta"+i).val() );
                                     }
                                     else {
                                        totalDiasNoches = parseInt( $("#dias"+i).val() );
                                     }
                                     importe = montoMediaJornada * totalDiasNoches;
                                     $("#importe"+i).val( formatoComas(fixDecimales( String(redondeaDecimal(importe) ))) );
                                } 
                            }     
                        });
                        
                   
                    }
                }).fail(function(res) {
                    muestraModalGeneral(3, window.tituloGeneral, 'Ocurrió un incidente inesperado mientras se obtenía el empleado. Favor de contactar al administrador.');
                });
    fnRecalculaDiasNoches();
}

function AlinearSelectsDerecha(){
    if($(".SelectsALaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
        $(".SelectsALaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
    }
}

function fnBorrarItinerario(){
    for(var c=0;c<$(".renglon").length;c++){
        if($("#idItinerario"+c).val()!="-1"){
            arrLinesToDelete.push($("#idItinerario0").val());
        }
    }
    $('#tbl-itinerario').html('');
    fnBloquearFechas(false);
}

function fnBloquearFechas(tipoAccion){
    if(tipoAccion){
        $("#fechaInicio").attr('readonly', true);
        $("#fechaTermino").attr('readonly', true);
    }else{
        $("#fechaInicio").removeAttr("readonly");
        $("#fechaTermino").removeAttr("readonly");
    }
}

function fnBloquearLinea(tipoAccion,lineaAnterior){
    c = lineaAnterior;
    lineaAnterior = (c+1);
    $('#linea'+lineaAnterior+' select').each(
        function(index){
            if(!($(this).attr('id')===undefined)){
                if($(this).attr('id').substr($(this).attr('id').length-c.toString.length)==c){
                    $(this).multiselect( tipoAccion ? "disable" : "enable" );
                }
            }
        }
    );

    $('#linea'+lineaAnterior+' input[type="text"], textarea').each(
        function(index){
            if(!($(this).attr('id')===undefined)){
                if($(this).attr('id').substr($(this).attr('id').length-c.toString.length)==c){
                    if($(this).attr('id')!="zonaEconomica"+c&&$(this).attr('id')!="cuota"+c&&$(this).attr('id')!="dias"+c&&$(this).attr('id')!="pernocta"+c&&$(this).attr('id')!="importe"+c){
                        $(this).attr('readonly', tipoAccion);
                        if(!tipoAccion){
                            $(this).removeAttr("readonly");
                        }
                    }
                }
            }
        }
    );

    if(tipoAccion){
        $("#linea"+lineaAnterior+" .row-delete").hide();
    }else{
        $("#linea"+lineaAnterior+" .row-delete").show();
    }
}

function fnConfiguracionDeParesDeFechas(i,fechaIni="",fechaFin=""){
    var nuevaFechaInicioComision = "",
        fechaIniMinDate = "",
        fechaIniMaxDate = "";

    if(i==0){
        fechaIniMinDate = $('#fechaInicio').val();
        fechaIniMaxDate = $('#fechaInicio').val();
        nuevaFechaInicioComision = $('#fechaInicio').val();
    }else{
        var diferencia = (i-1);
        var fechaConDiferencia = fnCambiaFechaDeStringAVariableDate($('#fechaTermino'+diferencia).val());
        fechaConDiferencia.setDate(fechaConDiferencia.getDate()+( fechaConDiferencia.toUTCString().substr(0,3).toLowerCase()=="fri" ? 3 : 1 ));
        fechaIniMinDate = $('#fechaTermino'+diferencia).val();
        fechaIniMaxDate = ( !fnFechaMenorQue($('#fechaTermino').val(),fechaConDiferencia.toISOString().split("T")[0].split("-").reverse().join("-")) ? fechaConDiferencia.toISOString().split("T")[0].split("-").reverse().join("-") : fechaIniMinDate );
        nuevaFechaInicioComision = $('#fechaTermino'+diferencia).val();
    }
    $("#fechaInicio"+i).parent().data('DateTimePicker').maxDate(fechaIniMaxDate);
    $("#fechaInicio"+i).parent().data('DateTimePicker').minDate(fechaIniMinDate);
    $("#fechaTermino"+i).parent().data('DateTimePicker').maxDate($('#fechaTermino').val());
    $("#fechaTermino"+i).parent().data('DateTimePicker').minDate(fechaIniMinDate);
    $("#fechaInicio"+i).parent().on('dp.change', fnCambioDeFecha);
    $("#fechaTermino"+i).parent().on('dp.change', fnCambioDeFecha);
    $("#fechaInicio"+i).val( fechaIni=="" ? nuevaFechaInicioComision : fechaIni ).trigger('change');
    $("#fechaTermino"+i).val( fechaFin=="" ? $('#fechaTermino').val() : fechaFin ).trigger('change');
}

function fnActualizaGranTotal(){
    var granTotal = 0;
    for(var c=0;c<$(".renglon").length;c++){
        granTotal += parseFloat($("#importe"+c).val().split(",").join(""));
    }
    granTotal = granTotal.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    granTotal = ( granTotal.substr(granTotal.length-3,1)=="," ? granTotal.split(".").join(";").split(",").join(":").split(":").join(".").split(";").join(",") : granTotal );
    $("#TotalComision").html(granTotal);
}

function fnCambioDeFecha(e){
    // Se determina si el campo fue fechaInicio o fechaTermino, en caso de que no sea ninguno de los dos la función termina
    var posicionNumerica = "";
    posicionNumerica = ( $(this).find('input').prop("id").substr(0,11)=="fechaInicio" ? 11 : posicionNumerica );
    posicionNumerica = ( $(this).find('input').prop("id").substr(0,12)=="fechaTermino" ? 12 : posicionNumerica );
    if(posicionNumerica==""){
        return false;
    }

    // Se obtiene el diferenciador que liga cada par de campos fechaInicio y fechaTermino
    var i = $(this).find('input').prop('id').substr(posicionNumerica);
    if(posicionNumerica==11){
        // Se forza que la fecha mínima del campo fechaTermino sea la fecha del campo fechaInicio, sólo cuando fechaInicio tuvo cambios
        $('#fechaTermino'+i).parent().data('DateTimePicker').minDate($('#fechaInicio'+i).val());
    }
    if(fnFechaMenorQue($('#fechaTermino'+i).val(),$('#fechaInicio'+i).val())){
        // Se hacen los ajustes necesarios si el campo fechaTermino tiene una fecha menor a la indicada en fechaInicio
        $('#fechaTermino'+i).val($('#fechaInicio'+i).val());
    }
    //$("#fechaTermino"+i).trigger('change');

    // Ajuste de días y noches sólo para fechas de itinerario
    if(i!=""){
        fnRecalculaDiasNoches();
    }
}

function fnRecalculaImporte(){
    for(var c=0;c<$(".renglon").length;c++){
        cuota = Number($("#cuota"+c).val().split(",").join("").split(" ").join(""));
        pernocta = $("#cantPernocta").val().split(",").join("").split(" ").join("");
        pernocta = Number( pernocta=="" ? 50 : pernocta )/100;
        importe = ( $("#tipoSol").val()==1 ? 
            ( Number($("#dias"+c).val())*(cuota*0.5) )+( Number($("#pernocta"+c).val())*(cuota*pernocta) ) : 
            ( $("#tipoSol").val()==2 ? Number($("#dias"+c).val())*cuota : "" ) );
        $("#importe"+c).val( importe );
    }
    fnActualizaGranTotal();
}

function fnValidaDiasNoches(){
    var diasTotales = fnDiasEntreFechas($("#fechaInicio").val(),$("#fechaTermino").val()),
        nochesTotales = diasTotales-( $("#tipoSol").val()==1 ? 1 : 0 ),
        dias = 0,
        noches = 0,
        mensajes = [];

    for(var c=0;c<$(".renglon").length;c++){
        dias += Number($("#dias"+c).val());
        noches += Number($("#pernocta"+c).val());
    }

    if(diasTotales!=dias){
        mensajes.push(dias+" de "+diasTotales+" día"+( diasTotales>1 ? "s" : "" )+" posibles");
    }
    if($("#tipoSol").val()==1&&nochesTotales!=noches){
        mensajes.push(noches+" de "+nochesTotales+" noche"+( diasTotales>1 ? "s" : "" )+" posibles");
    }

    return mensajes;
}

function fnRecalculaDiasNoches(){
    for(var i=0;i<$(".renglon").length;i++){
        $("#dias"+i).val(fnDiasEntreFechas($("#fechaInicio"+i).val(),$("#fechaTermino"+i).val()));
        $("#pernocta"+i).val(fnDiasEntreFechas($("#fechaInicio"+i).val(),$("#fechaTermino"+i).val())-( $("#tipoSol").val()==1 ? 1 : 0 ));

        // Ajuste de días y noches para la segunda fecha en adelante
        if(i>0){
            if($('#fechaInicio'+i).val()==$("#fechaTermino"+(i-1)).val()){
                $("#dias"+i).val(fnDiasEntreFechas($("#fechaInicio"+i).val(),$("#fechaTermino"+i).val())-1);
                $("#pernocta"+(i-1)).val(fnDiasEntreFechas($("#fechaInicio"+(i-1)).val(),$("#fechaTermino"+(i-1)).val())-1);
            }else{
                $("#pernocta"+(i-1)).val(fnDiasEntreFechas($("#fechaInicio"+(i-1)).val(),$("#fechaTermino"+(i-1)).val()));
            }
        }
    }
    fnRecalculaImporte();
}

// Funciones de fechas
// Convierte strings dd-mm-YYYY, dd/mm/YYYY, dd mm YYYY, YYYY-mm-dd, YYYY/mm/dd y YYYY mm dd a variables Date
function fnCambiaFechaDeStringAVariableDate(fecha){
    fecha = ( fecha.split("-").length==3 ? fecha.split("-") : ( fecha.split("/").length==3 ? fecha.split("/") : fecha.split(" ") ) );
    if(fecha.length!=3){
        return false;
    }
    fecha = ( fecha[0].length==4 ? fecha.join("-") : ( fecha[2].length==4 ?  fecha.reverse().join("-") : false ) );

    return ( fecha!=false ? new Date( fecha ) : fecha );
}

// Compara fechas en formato string, regresa true, false o -1 
function fnFechaMenorQue(fecha1,fecha2){
    fecha1 = fnCambiaFechaDeStringAVariableDate(fecha1);
    fecha2 = fnCambiaFechaDeStringAVariableDate(fecha2);
    if(fecha1==false||fecha2==false){
        return -1;
    }

    return fecha1<fecha2;
}

// Regresa los días naturales entre dos fechas dadas
function fnDiasEntreFechas(fechaIni,fechaFin){
    fechaIni = fnCambiaFechaDeStringAVariableDate(fechaIni);
    fechaFin = fnCambiaFechaDeStringAVariableDate(fechaFin);
    if(fechaIni==false||fechaFin==false){
        return false;
    }
    dias = ( (
                Date.UTC(fechaFin.getFullYear(), fechaFin.getMonth(), fechaFin.getDate()) -
                Date.UTC(fechaIni.getFullYear(), fechaIni.getMonth(), fechaIni.getDate())
            ) / 86400000 ) + 1;

    return dias;
}
