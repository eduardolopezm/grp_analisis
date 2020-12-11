/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de Agosto del 2017
 */
// 
/* variables globales inicio */
var _tip_check='';
var regreso = 0;
var idanexoGlobal=-1;
var urGlobal=-1;
var tipoGlobal=-1;
var idrequisicionGlobal=-1;
var preglob= 0;
var partidas_presupuesto= [];
var url = "modelo/Captura_Requisicion_modelo_V_3.php";
var iCntPI = 0; // contador de elementos con presupuesto insuficiente.
var iCntND = 0; // contador de elementos sin disponibilidad en almacen.
var iCntEmpty = 0; // contador para saber si esta vacio el panel
var proceso = ""; // Valor del proceso que se llamara al modelo.
var noReq = noRequisicionGeneral;
var urlCR = urlCReq;
var estatusGenerarLayout = 4;
var funcionGenerarLayout = 2265;
var typeGenerarLayout = 19;
var tipoLayout = 1;
var periodoR = periodoReq;
var viewAnexo = 0; //variable que resetea la visibilidad del boton del anexo tecnico
var anexoAsignado = 0; // variable que indica si se ha asignado un anexo técnico a la requisición
var selectedNoExist = new Array(); // seleccion del checkbox de lo no existente
var selectedSolAut = new Array(); // seleccion del checkbox de la solicitud automatica
var idFolioNoe = 0;
var idFolioSolA = 0;
var existeFolioAnexo = false;

// Cambios pruebas, se agrega variable para partidas
var jsonPartidaGeneralBienes = new Array();
var jsonPartidaGeneralServicios = new Array();
var jsonIdElementoAnexoTecnico = new Array();
var nuAnexoTecnico = 0;
// Crear un elemento div añadiendo estilos CSS
var containerElemento = $(document.createElement('div')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });
var containerArticulo = $(document.createElement('div')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });
var containerServicio = $(document.createElement('div')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });

var containerInstrumental = $(document.createElement('div')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });
var posicionesUltimas=[];
$(document).ready(function(){
    // muestraCargandoGeneral();
});
/* variables globales fin */
$(document).ready(function() {
    muestraCargandoGeneral();
    setTimeout(function(){
        $("input[type=text]").focus(function () {
            this.select();
        });
        $('[data-toggle="tooltip"]').tooltip();

        $("#idtxtRequisicionView").addClass("hide");
        $("#idtxtRequisicionView").prop("readonly", true);
        $("#idtxtRequisicionView").attr('disabled', 'disabled');

        $("#idFechaElaboracion").prop("readonly", true);
        $("#idFechaElaboracion").attr('disabled', 'disabled');

        // Cambios pruebas, se traen las partidas para mostrar informacion, se crearon funciones para informacion
        jsonPartidaGeneralBienes = fnObtenerPartidasProductos('', '');
        jsonPartidaGeneralServicios = fnObtenerPartidasServicios('', '');

        fnEditorTextoClose();
        fnFijarFecha();
        fnFijarFechaSiguiente(1);
        if (noRequisicionGeneral > 0) {
            $("#idtxtRequisicion").val("" + noRequisicionGeneral);
            $("#idtxtRequisicion").prop("readonly", true);
            $("#idtxtRequisicion").attr('disabled', 'disabled');

            var noReq = noRequisicionGeneral;
            if (noReq > 0) {
                //muestraCargandoGeneral();
                fnLimpiar(noReq);
                fnLoadRequisicion(noReq);
                //ocultaCargandoGeneral();
            }
        }

        $('#idPopupEditorClose').click(function () {
            $('.idPopupEditor').fadeOut('slow');
            $('.popup-overlay').fadeOut('slow');
            return false;
        });

        $('input:checkbox[name=anexoTecnicoCheck]').click(function () {
            if ($(this).is(':checked')) {
                $(".btnanexorenglonart").removeClass('hide');
                $(".btnanexorenglonserv").removeClass('hide');
            } else if (nuAnexoTecnico != 0) {
                var $pie = $('<span/>');
                $pie.append(// A
                        $('<button/>', {// B
                            html: 'Si', 'data-dismiss': 'modal', class: 'btn botonVerde'
                            , click: function () {// C
                                $(".btnanexorenglonart").addClass('hide');
                                $(".addedReglonArticulo").val("");
                                $(".btnanexorenglonserv").addClass('hide');
                                $(".addedReglonServicio").val("");
                                $('#anexoTecnicoCheck').prop('checked', false);
                                if (nuAnexoTecnico != 0) {
                                    fnGuardarAnexoTecnico($("#idtxtRequisicion").val(), 0);
                                }
                            }// C
                        })// B
                        );// A
                $pie.append(// A
                        $('<button/>', {// B
                            html: 'No', 'data-dismiss': 'modal', class: 'btn botonVerde'
                            , click: function () {// C
                                $('#anexoTecnicoCheck').prop('checked', true);
                            }// C
                        })// B
                        );// A
                muestraModalGeneral(
                        3,
                        'Confirmación',
                        `Se han vinculado datos entre el Anexo técnico ${nuAnexoTecnico} con la requisición. <br/> ¿Está seguro de eliminar el vínculo hecho?`,
                        $pie
                        );
            } else {
                $(".btnanexorenglonart").addClass('hide');
                $(".btnanexorenglonserv").addClass('hide');
                $(".addedReglonArticulo").val("");
                $(".addedReglonServicio").val("");
            }
        });

        // Guardar Información
        $("#idBtnGuardarCR").click(function () {
            muestraCargandoGeneral();
            setTimeout(function () {
                fnGuardarRequisicion();
                //ocultaCargandoGeneral();
            }, 10000);
        });

        // Agregar nuevo Articulo
        $("#idBtnAgregarArticulo").click(function () {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            if ($("#selectUnidadNegocio").val() == '-1' || $("#selectUnidadNegocio").val() == '0') {
                // Validar UR
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar  UR para continuar con el proceso</p>';
                muestraModalGeneral(3, titulo, mensaje);
                return true;
            } else if ($("#selectUnidadEjecutora").val() == '-1' || $("#selectUnidadEjecutora").val() == '0') {
                // Validar UE
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar  UE para continuar con el proceso</p>';
                muestraModalGeneral(3, titulo, mensaje);
                return true;
            }
            muestraCargandoGeneral();
            setTimeout(function () {
                fnValidaExistenciaRequisicion(producto);
                ocultaCargandoGeneral();
            }, 2000);
        });

        // Agregar nuevo Servicio
        $("#idBtnAgregarServicio").click(function () {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            if ($("#selectUnidadNegocio").val() == '-1' || $("#selectUnidadNegocio").val() == '0') {
                // Validar UR
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar  UR para continuar con el proceso</p>';
                muestraModalGeneral(3, titulo, mensaje);
                return true;
            } else if ($("#selectUnidadEjecutora").val() == '-1' || $("#selectUnidadEjecutora").val() == '0') {
                // Validar UE
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar  UE para continuar con el proceso</p>';
                muestraModalGeneral(3, titulo, mensaje);
                return true;
            }
            muestraCargandoGeneral();
            setTimeout(function () {
                fnValidaExistenciaRequisicion(servicio);
                ocultaCargandoGeneral();
            }, 2000);
        });

        // Agregar nuevo Instrumental
        $("#idBtnAgregarInstrumental").click(function () {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            if ($("#selectUnidadNegocio").val() == '-1' || $("#selectUnidadNegocio").val() == '0') {
                // Validar UR
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar  UR para continuar con el proceso</p>';
                muestraModalGeneral(3, titulo, mensaje);
                return true;
            } else if ($("#selectUnidadEjecutora").val() == '-1' || $("#selectUnidadEjecutora").val() == '0') {
                // Validar UE
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Seleccionar  UE para continuar con el proceso</p>';
                muestraModalGeneral(3, titulo, mensaje);
                return true;
            }
            muestraCargandoGeneral();
            setTimeout(function () {
                fnValidaExistenciaRequisicion(instrumental);
                ocultaCargandoGeneral();
            }, 2000);
        });


        // Cambios pruebas, se trae configuracion de perfil al cargar pagina
        fnObtenerPerfilUsr();

        // comportamiento en el onblur para decimales
        $(document).on('blur', '.fixDecimal', function () {
            $(this).val(fixDecimales($(this).val()))
        });
        $(document).on('focus', '.fixDecimal', function () {
            $(this).val(fixDecimales($(this).val()))
        });
    },1000);

});

// regresa el perfil del usuario para poder hacer validaciones de permisos
function fnObtenerPerfilUsr(){
    // muestraCargandoGeneral();
    var status = $("#idStatusReq").val();
    var perfilUsr = "";
    var perfilid = "";
    if(status == ''){
        status = 'Creada';
    }
       
    dataObj = {
            option: 'buscarPerfilUsr'
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
                dataPerfil = data.contenido.datos;
                // alert("dataPerfil: "+JSON.stringify(dataPerfil));
                for (var info in dataPerfil) {
                    perfilUsr = dataPerfil[info].userid;
                    perfilid = dataPerfil[info].profileid;
                }
                $("#idperfilusr").val(""+perfilid);
                
                if((perfilid == 9 || perfilid == 10 || perfilid == 11 || perfilid == 7) && (status == 'Creada' || status == 'Capturado')){
                    $("#idBtnGuardarCR").removeClass('hide');
                    $("#idBtnCancelarCR").removeClass('hide');
                } else if((perfilid == 10 || perfilid == 11 || perfilid == 7) && ( status == 'Creada' || status == 'Validar')){
                    $("#idBtnGuardarCR").removeClass('hide');
                    $("#idBtnCancelarCR").removeClass('hide');
                } else if((perfilid == 11 || perfilid == 7) && ( status == 'Creada' || status == 'PorAutorizar')){
                    $("#idBtnGuardarCR").removeClass('hide');
                    $("#idBtnCancelarCR").removeClass('hide');
                }
            }else{
                //muestraMensaje('No se guardo la Requisición', 3, 'divMensajeOperacion', 5000);
            }
            // ocultaCargandoGeneral();
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            // ocultaCargandoGeneral();
        });
        // ocultaCargandoGeneral();
}

// regresa elestatus de la requisicion actual para poder hacer las validaciones con el perfil del usuario
function fnObtenerStatusReq(idReq){
    // muestraCargandoGeneral();
    var sR = "";
    dataObj = {
            option: 'buscarStatusReq',
            idReq: idReq
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
                dataStatus = data.contenido.datos;
                var statusReq = "";
                var statusReqVisual = "";
                for (var info in dataStatus) {
                    statusReq = dataStatus[info].status;
                    statusReqVisual = dataStatus[info].statusVisual;
                }
                //statusReq = dataStatus;
                $("#statusReq").text(statusReq);
                $("#statusReq").prop("readonly", true);
                $("#statusReq").attr('disabled', 'disabled');
                $("#idStatusReq").val(""+statusReq);

                $("#statusReqVisual").text(statusReqVisual);

                // Cambios pruebas, se comenta que no obtenga la configuración del perfil
                fnObtenerPerfilUsr();
                sR = statusReq;
            }else{
                //muestraMensaje('No se guardo la Requisición', 3, 'divMensajeOperacion', 5000);
                console.log(data.contenido);
                $("#statusReq").text("");
                $("#statusReq").prop("readonly", true);
                $("#statusReq").attr('disabled', 'disabled');
                $("#idStatusReq").val("");

                $("#statusReqVisual").text("");

                sR= "";
            }
            // ocultaCargandoGeneral();
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            // ocultaCargandoGeneral();
        });
        return sR;
}

// calcula el costo total por articulo
function fnCalculaTotal(orden){
    var cantidad = $("#addedCantidadArticulo" + orden).val();
    var precio = $("#addedPEArticulo" + orden).val();
    var existencia = $("#contDispArt" + orden).val();
    var disponibilidad = existencia - cantidad;
    var total = /*formatoComas*/fixDecimales( redondeaDecimal(cantidad * precio)+"" );

    $("#addedCantidadTotalArticulo" + orden).val("$ "+total);

    if( disponibilidad >= 0){
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = iCntND + 1;
         $("#addedCantidadTotalArticulo"+orden).trigger('change');
     }else{
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("No Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = 0;
     }

    $("#addedCantidadTotalArticulo"+orden).trigger('change');
}
 
// valida el presupuesto existente   
function fnValidaPresupuesto(orden) {
    var a = $("#addedCantidadArticulo" + orden).val();
    var b = $("#addedPEArticulo" + orden).val();
    var t = /*formatoComas*/ fixDecimales( redondeaDecimal(a * b)+"" );
    var p = $("#addPresupuestoH"+orden).val();
    var d = p - t;
    var idReq = $("#idtxtRequisicion").val();
    var pe = $("#selectCvePartidaEspecifica" + orden).val();

    $("#addedCantidadTotalArticulo" + orden).val("$ "+t);

    $("#addedCantidadTotalArticulo"+orden).trigger('change');
}; 

/**
 * Obtiene el presupuesto a partir de una clave presupuestal generada de una partida especifica.
 */
function fnObtenerPresupuesto(orden){
    var partidaEspecifica = $("#selectCvePartidaEspecifica" + orden).val();
    if(typeof $("#p"+partidaEspecifica).val() === 'undefined') {
        var disp = 0;

    }else{
        var disp = $("#p"+partidaEspecifica).val();
    }
    var cvePArt = $("#selectPartidaEspecificaCvePresupuestal"+orden).val();
    var cvePServ = $("#selectPartidaEspecificaCvePresupuestalServ"+orden).val();
    if(typeof cvePArt === 'undefined'){
        var cveP = cvePServ;
    }else if(typeof cvePServ === 'undefined') {
        var cveP = cvePArt;
    }
    
    dataObj = { 
            option: 'obtenerPresupuesto',
            clave: cveP
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    }).done(function( data ) {
        if(data.result){
            info=data.contenido.datos;
            var nombreMes = data.contenido.nombreMes;
            //console.log("presupuesto: "+JSON.stringify(info));
            var clave = ""
            if(disp > 0){
                clave = disp;
            }else{
                clave =info[0][nombreMes];    
            }
            $("#addPresupuestoH"+ orden).val(""+clave);
            $("#addPresupuesto"+ orden).html(clave);
            $("#addPresupuesto"+ orden).prop("readonly", true);
            $("#addPresupuesto"+orden).attr('disabled', 'disabled');   
        }else{
            
            muestraMensaje(data.Mensaje, 3, 'divMensajeOperacion', 5000);
        }
    })
    .fail(function(result) {
        
        console.log("ERROR");
        console.log( result );
    });
}

// hace el calculo para el presupuesto descontando le monto de los procutos de la misma partida
function fnObtenerPresupuestoDisponible(orden){
    var partidaEspecifica = $("#selectCvePartidaEspecifica" + orden).val();
    var cvePArt = $("#selectPartidaEspecificaCvePresupuestal"+orden).val();
    var cvePServ = $("#selectPartidaEspecificaCvePresupuestalServ"+orden).val();
    var ppto_disponible = 0;

    if(typeof cvePArt === 'undefined'){
        var cveP = cvePServ;
    }else if(typeof cvePServ === 'undefined') {
        var cveP = cvePArt;
    }
    dataObj = { 
            option: 'obtenerPresupuesto',
            clave: cveP
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    }).done(function( data ) {
        if(data.result){
            info=data.contenido.datos;
            var nombreMes = data.contenido.nombreMes;
            ppto_disponible =info[0][nombreMes]; 
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });

    return ppto_disponible;
}

/**
 * Valida la existencia de la requisición, desde la agregacion de productos en caso de no existir la crea.
 * 
 */
function fnValidaExistenciaRequisicion(mbflag){
    var idRequisicion = $("#idtxtRequisicion").val();
    var fElabora = $("#idFechaElaboracion").val();
    var fEntrega = $("#idFechaEntrega").val();
    var dependencia = $("#selectRazonSocial").val();
    var ur = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();
    var mbflag = mbflag;
    var obs = $("#txtAreaObs").val();
    obs = '';
    $('#idBtnGuardarCR').addClass('hide');
    $('#idBtnCancelarCR').addClass('hide');

    if(idRequisicion > 0){
        fnRequisicionExistente(idRequisicion, dependencia, ur, ue, mbflag);
    }else{
        fnNuevaRequisicion(fElabora, fEntrega, dependencia, ur, ue, mbflag, obs);
    }
}

/**
 * Crea una nueva requisición 
 */
function fnNuevaRequisicion(fElabora, fEntrega, dependencia, ur, ue, mbflag, obs){
    var cont = 0;
    console.log("nueva requi");
    if ((fElabora == "") || (fEntrega == "") || (dependencia == "") || (ur == "")|| (ue == "")) {
        muestraMensaje('Es necesario seleccionar las fechas, unidad responsable y unidad ejecutora  para crear una nueva Requisición', 3, 'msjValidacion', 5000);
        return false;
    }else{
        // muestraCargandoGeneral();
        rs = $('#selectRazonSocial option:selected').text();
        un = $('#selectUnidadNegocio option:selected').text();
        ue = $('#selectUnidadEjecutora option:selected').text();
        $('#selectRazonSocial').empty();
        $("#selectRazonSocial").html("<option value="+dependencia+">"+rs+"</option>");
        $("#selectRazonSocial").multiselect('rebuild');
        $('#selectUnidadNegocio').empty();
        $("#selectUnidadNegocio").html("<option value="+ur+">"+un+"</option>");
        $("#selectUnidadNegocio").multiselect('rebuild');

        var valueUE = $('#selectUnidadEjecutora option:selected').val();
        var textoUE = $('#selectUnidadEjecutora option:selected').text();

        // Cambios pruebas, no seleccionar la ue al agregar registros
        $('#selectUnidadEjecutora').empty();
        $("#selectUnidadEjecutora").html('<option value="'+valueUE+'">'+textoUE+'</option>');
        $("#selectUnidadEjecutora").multiselect('rebuild');

        dataObj = {
            option: 'agregarCapturaRequisicion',
            fechaElabora: fElabora,
            fechaEntrega: fEntrega,
            rs: dependencia,
            un: ur,
            ue: ue, 
            mbflag: mbflag,
            obs: $("#txtAreaObs").val(),
            codigoExpediente: $("#codigoExpediente").val(),
            selectCFDI: $("#selectCFDI").val()
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
                dataR = data.contenido.datos
                cont++;
                var idReq = dataR[0].orderno;
                $("#idtxtRequisicionView").addClass("hide");
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                $("#idtxtRequisicion").val("" + idReq);
                $("#idtxtRequisicion").prop("readonly", true);
                $("#idtxtRequisicion").attr('disabled', 'disabled');
                fnObtenerStatusReq(idReq);
                console.log("orden inicial nueva: %s", cont);
                fnAgregarElemento(dependencia, ur, ue, mbflag, idReq, fEntrega, cont);
                // ocultaCargandoGeneral();
            }else{
                //muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No se agrego la Requisición');
                  muestraModalGeneralConfirmacion(3,'<h3><p><i class"glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>',
                  "No se agrego la requisición","","fnNuevaRequisicion","fElabora, fEntrega, dependencia, ur, ue, mbflag, obs");
                // ocultaCargandoGeneral();
            }
        }).fail(function(result) {
            // ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
        });
    }
}
/**
 * Crea noevos elementos a una requisición existente
 */
function fnRequisicionExistente(idRequisicion, dependencia, ur, ue, mbflag){
    console.log("requi existente");
    // muestraCargandoGeneral();

    dataObj = {
        option: 'requisicionExistente',
        idRequisicion: idRequisicion,
        ur: ur,
        ue:ue
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
                dataReqExist = data.contenido.datos;
                var idReq = "";
                var noReq = "";
                var fEntrega = "";
                var orden = "";
                for (var info in dataReqExist) {
                    idReq = dataReqExist[info].orderno;
                    noReq = dataReqExist[info].noRequisition;
                    orden = dataReqExist[info].orden;
                    fEntrega = dataReqExist[info].fechaEnt;
                }

                fnObtenerStatusReq(idReq);
                if(orden == '' || orden == 0 || orden === 'undefined' || orden == null){
                    //orden = 1;
                    /* MOSTRAR MODAL*/
                    muestraModalGeneralConfirmacion(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i>Error</p></h3>',
                    'Hubo un error al ejecutar la consulta lo cual impide asignarle un no. de renglón. ¿Desea volver a intentarlo?',"","fnRequisicionExistente",
                    "idRequisicion, dependencia, ur, ue, mbflag");
                }
                else{
                    console.log("orden inicial existente: %s", orden);
                    fnAgregarElemento(dependencia, ur, ue, mbflag, idRequisicion, fEntrega, orden);
                }
                // ocultaCargandoGeneral();
                
            }else{
                // ocultaCargandoGeneral();
            }

        }).fail(function(result) {
            // ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
        });
}

// carga la informacion existente segun el id de la requisicion seleccionada
function fnLoadRequisicion(noReq){
    // muestraCargandoGeneral();
    // setTimeout(function(){ 

    fnReindexar();
    fnObtenerStatusReq(noReq);
    // Cambios pruebas, se comenta que no obtenga la configuración del perfil
    fnObtenerPerfilUsr();
    $("#selectUnidadNegocio").prop("readonly", true);
    $("#selectUnidadNegocio").attr('disabled', 'disabled');
    $("#selectUnidadNegocio").addClass('disponible');

    $("#idtxtRequisicionView").removeClass("hide");
    $("#idtxtRequisicionView").prop("readonly", true);
    $("#idtxtRequisicionView").attr('disabled', 'disabled');
    $("#idStatusReq").removeClass("hide");
    $("#idStatusReq").prop("readonly", true);
    $("#idStatusReq").attr('disabled', 'disabled');

    dataObj={
        option: 'loadRequisicion',
        req: noReq
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
            //console.log( "dataJson: " + JSON.stringify(data) );
            info = data.contenido.datos;
            
            var rs = "";
            var ur = "";
            var fechaCreacion = "";
            var fechadelivery = "";
            var comments = "";
            var idr = "";
            var noReq = "";
            var unidadEjecutora = "";
            var anexoTec = "";
            var codigoExpediente = "";
            var selectCFDI = "-1";

            rs = info[0].rs;
            ur = info[0].ur;
            unidadEjecutora = info[0].unidadEjecutora;
            fechaCreacion = info[0].fechaCreacion;
            fechadelivery = info[0].fechadelivery;
            comments = info[0].comments;
            idr = info[0].idr;
            noReq = info[0].noReq;
            anexoTec = info[0].anexoTec;
            nuAnexoTecnico = info[0].nuAnexoTecnico;
            codigoExpediente = info[0].codigoExpediente;
            selectCFDI = info[0].selectCFDI;

            if(anexoTec == 1){
                viewAnexo=1;
                $('input:checkbox[name=anexoTecnicoCheck]').attr('checked',true);
            }else{
                viewAnexo=0;
                $('input:checkbox[name=anexoTecnicoCheck]').attr('checked',false);
            }
             idanexoGlobal= 'anexo' ; 
             urGlobal=ur;
             tipoGlobal=19;
             idrequisicionGlobal=idr;

            $("#idtxtRequisicionView").html(noReq);
            $("#idtxtNoRequisicion").val(""+noReq);
            $('#selectRazonSocial').empty();
            $("#selectRazonSocial").html("<option value="+rs+">"+rs+"</option>");
            $("#selectRazonSocial").multiselect('rebuild');
            $('#selectUnidadNegocio').empty();
            $("#selectUnidadNegocio").html("<option value="+ur+">"+ur+"</option>");
            $("#selectUnidadNegocio").multiselect('rebuild');
            $('#selectUnidadEjecutora').empty();
            $("#selectUnidadEjecutora").html("<option value="+unidadEjecutora+">"+unidadEjecutora+"</option>");
            $("#selectUnidadEjecutora").multiselect('rebuild');
            $("#txtAreaObs").val(""+comments);
            $("#idFechaElaboracion").val(""+fechaCreacion);
            $("#idFechaEntrega").val(""+fechadelivery);
            $("#codigoExpediente").val(""+codigoExpediente);
            $("#selectCFDI").val(""+selectCFDI);
            $("#selectCFDI").multiselect('rebuild');

            var periodo = $("#idFechaElaboracion").data("periodo");
            var tagref = $("#selectUnidadNegocio").val();
            
            fnMostrarElementosRequisicion(idr);
            //fnActualizaPresupuesto();
            //fnActualizaPresupuestoServicio();
        }
        // ocultaCargandoGeneral();

    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
        // ocultaCargandoGeneral();
    });
// }, 2000);
//     ocultaCargandoGeneral();
}

function fnObtenerPartidasProductos(tagref, periodoR) {
    // Función para obtener las partidas de los productos
    var datos = new Array();
    dataObj = { 
      option: 'mostrarPartidaEspecificaProductos',
      tagref: tagref,
      periodo: periodoR,
      fnGeneral: 1
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: 'modelo/componentes_modelo.php',
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            datos = data.contenido.datos;
            // console.log("datos partida funcion: "+JSON.stringify(datos));
        }else{
            datos = new Array();
            // muestraMensaje('No se agrego el elemento a la Requisición ', 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });

    return datos;
}

function fnObtenerPartidasServicios(tagref, periodoR) {
    // Función para obtener las partidas de los productos
    var datos = new Array();
    dataObj = { 
      option: 'mostrarPartidaEspecificaServicios',
      tagref: tagref,
      periodo: periodoR,
      fnGeneral: 1
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: 'modelo/componentes_modelo.php',
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            datos = data.contenido.datos;
            // console.log("datos partida funcion: "+JSON.stringify(datos));
        }else{
            datos = new Array();
            // muestraMensaje('No se agrego el elemento a la Requisición ', 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });

    return datos;
}

/**
 * Buscar una requisición existente
 */
function fnMostrarElementosRequisicion(idRequisicion){
    dataObj = {
        option: 'mostrarRequisicion',
        requi: idRequisicion
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
            
            var idRequisicion = "";
            var idPartida = "";
            var descPartida = "";
            var idItemDetalle = "";
            var idItem = "";
            var descItem = "";
            var unidad = "";
            var tipo = "";
            var precio = "";
            var cantidad = "";
            var cantidadSolALmacen = "";
            var cantidadReal = "";
            var total = "";
            var existencia = "";
            var orden = "";
            var clavepresupuestal = "";
            var descLarga = "";
            var fEntrega = $("#idFechaEntrega").val();
            var renglon = "";
            var indiceElement = 0;
            var statusReq = "";
            var cm = '';

            containerArticulo[0].innerHTML = "";
            containerServicio[0].innerHTML = "";

            // Cambios pruebas, se traen las partidas para mostrar informacion, se crearon funciones para informacion
            // jsonPartidaGeneralBienes = fnObtenerPartidasProductos('', '');
            // jsonPartidaGeneralServicios = fnObtenerPartidasServicios('', '');

            for (var info in dataJson) {
                indiceElement++;
                //console.log(JSON.stringify(dataJson[info]));
                idRequisicion = dataJson[info].idRequisicion;
                statusReq = dataJson[info].statusReq;
                idPartida = dataJson[info].idPartida;
                descPartida = dataJson[info].descPartida;
                idItemDetalle = dataJson[info].idItemDetalle;
                idItem = dataJson[info].idItem;
                descItem = dataJson[info].descItem;
                cm = dataJson[info].cm;
                unidad = dataJson[info].unidad;
                tipo = dataJson[info].tipo;
                precio = dataJson[info].precio;
                cantidad = dataJson[info].cantidad;
                cantidadSolALmacen = dataJson[info].cantidadSolALmacen;
                cantidadReal = dataJson[info].cantidadReal;
                //total = dataJson[info].total;
                total = precio * cantidad;
                existencia = dataJson[info].existencia;
                orden = dataJson[info].orden;
                clavepresupuestal = dataJson[info].clavePresupuestal;
                descLarga = dataJson[info].descLarga;
                renglon = dataJson[info].renglon;
                
                if(statusReq == 'Autorizado' || statusReq == 'Authorised'){
                    cantidad = cantidadReal;
                    total = precio * cantidad;
                    console.log(total);
                }

                if(tipo == 'B'){
                    fnArticulo(idRequisicion, orden, fEntrega, indiceElement, idPartida, idItem, descItem,cm, unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga,renglon, viewAnexo, idItemDetalle);
                }else{
                    fnServicio(idRequisicion, orden, fEntrega, indiceElement, idPartida, descPartida, idItem, descItem,precio,clavepresupuestal,descLarga,renglon, viewAnexo, idItemDetalle);
                }
            }
            // cuando la requisición se enceuntre autorizada se bloqueara todos los elementos de la pantalla
            if(statusReq == 'Autorizado' || statusReq == 'Authorised'){
                $('input').attr('disabled',true); // bloqueo de inputs
                $('select').multiselect('disable'); // bloqueo de selects
                $('.btn.btn-info.btn-xs.glyphicon.glyphicon-comment').attr('disabled',true).attr('onclick',''); // bloqueo de botones de detalle
            }
            // se coloca el fix para los decimales 
            $('.fixDecimal').each(function(index, el) {
                $(el).val( fixDecimales($(el).val()) );
            });
        }else {
            muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

// agrega elementos dinamicos al grid de articulos y servicios
function fnAgregarElemento(dependencia, ur, ue, mbflag, idReq, fEntrega, orden){
    console.log("orden inicial: %s", orden);
    console.log("requi inicial: %s", idReq);
    $('#idBtnGuardarCR').removeClass('hide');
    $('#idBtnCancelarCR').removeClass('hide');
    var arregloOrden = [];
    var newOrden = 0;
    var ordenVal = 0;
    var maxOrden = 0;
    var indiceRenglon = 0;
    var indiceIncremento = 0;
    var ord = parseInt(orden); 
    
    console.log("orden ord agregar: %s", ord);
    
    if(ord > 0){
        $(".indice").each(function (){
            ordenVal = $(this).text();
            console.log("orden interno agregrar: %s",ordenVal);
            if(ordenVal != ''){
                arregloOrden.push(ordenVal);
            }
        });

        arregloOrden.sort(function(a, b){return a-b});
        console.log("+++++");
        console.log(arregloOrden);
        console.log("+++++");
        maxOrden = arregloOrden.length;
        maxOrden++;

        for (var info in arregloOrden) {
            indiceIncremento++;
            console.log("indiceIncremento agregar antes: %s", indiceIncremento);
            $("#num"+ arregloOrden[info]).text(indiceIncremento);
        }
        cont = ord;
        
    }else{
        cont = 1;
        indiceIncremento = 1;
    }

    dataObj = {
        option: 'agregarElementosRequisicion',
        idReq: idReq,
        fecEn: fEntrega,
        orden: cont
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
            if(mbflag == 'B'){
                console.log("cont art", cont);
                console.log("indiceIncremento art", maxOrden);
                fnArticulo(idReq, cont, fEntrega, maxOrden);
                // if(viewAnexo == 1){
                //     $("#btnanexorenglonart"+cont).removeClass('hide');
                // }else{
                //     $("#btnanexorenglonart"+cont).addClass('hide');
                // }
            }else if(mbflag == 'D'){
                console.log("cont serv", cont);
                console.log("indiceIncremento serv", maxOrden);
                fnServicio(idReq, cont, fEntrega, maxOrden);
                // if(viewAnexo == 1){
                //     $("#btnanexorenglonserv"+cont).removeClass('hide');
                // }else{
                //     $("#btnanexorenglonserv"+cont).addClass('hide');
                // }
            }else{
                fnInstrumental(idReq, cont, fEntrega, maxOrden);
                if(viewAnexo == 1){
                    $("#btnanexorenglonInstrumental"+cont).removeClass('hide');
                }else{
                    $("#btnanexorenglonInstrumental"+cont).addClass('hide');
                }
            }
        }else{
            //muestraMensaje('No se agrego el elemento a la Requisición ', 3, 'divMensajeOperacion', 5000);
            muestraModalGeneralConfirmacion(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información:</p></h3>',
            "No se agrego el elemento a la Requisición","","fnAgregarElemento","dependencia, ur, ue, mbflag, idReq, fEntrega, orden");
        }

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function sort(a,b){
    a = parseInt(a.orden);
    b = parseInt(b.orden);
    if(a > b) {
        return 1;
    } else if (a < b) {
        return -1;
    }
        return 0;
}

// remover el elemento seleccionado
function fnRemoverElemento(idReq, noReq, orden, mbflag){
   
    var indiceRenglon= 0;
    var actualOrden = 0;
    var arrayOrden = [];
    var arrayOrdenIndice = [];

    if(mbflag = 'B'){
        $('#addedReglonArticulo' + orden).val("");
        //$(this).parent().parent().remove();
        //$(this).remove();
        $("#idElementArticulo" + orden).remove();
        $("#idCvePresupuestal" + orden).remove();
    }
    if(mbflag = 'D'){
        $('#addedReglonServicio' + orden).val("");
        //$(this).parent().parent().remove();
        //$(this).remove();
        $("#idElementServicio" + orden).remove();
        $("#idCvePresupuestal" + orden).remove();
    }
    $(".indice").each(function () {
        if($(this).text() != ''){
            actualOrden = $(this).text();
            arrayOrden.push(actualOrden);
            iCntEmpty = 1;
        }        
    });
    
    /*
     * YO ESTA PARTE LA PONDRIA DESPUES DE HABER
     * ACTUALIZADO Y ELIMINADO EL RENGLÓN DE LA 
     * REQUISICIÓN PORQUE SI SE PRODUCE UNA FALLA
     * NO QUITO EL RENGLÓN SI NO HASTA DESPUÉS DE A VER
     * HECHO LA ELIMINACIÓN Y LA ACTUALIZACIÓN
     */
    
    arrayOrden.sort(function(a, b){return a-b});
    //console.log("Arreglo despues de eliminar:"+JSON.stringify(arrayOrden));
    if(arrayOrden != null || arrayOrden != '' || arrayOrden != 0 || arrayOrden.length > 0  || arrayOrden !== 'undefined') { 
        for (var info in arrayOrden) {
            indiceRenglon++;
            $("#num"+ arrayOrden[info]).text(indiceRenglon);
        }
    }else{
        iCntEmpty = 0;
    } 
    console.log("iCntEmpty %s: ",iCntEmpty);
    if(iCntEmpty == 0){
        $('#idBtnGuardarCR').addClass('hide');
    }
    //fnEliminarElemento(idReq, noReq, orden);
}
// elmimina el elemento seleccionado
function fnEliminarElemento(idReq, noReq, orden, mbflag){
    console.log(orden);
    dataObj = {
        option: 'eliminarElementosRequisicion',
        idReq: idReq,
        noReq: noReq,
        orden: orden
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
            //muestraMensaje('Se elimino el elemento Articulo a la Requisición: '+ r, 1, 'divMensajeOperacion', 5000);
            //fnReindexar(); 
            //orden = orden - 1;   
            fnRemoverElemento(idReq, noReq, orden, mbflag);
        }else{
            //muestraMensaje('No se elimino el elemento a la Requisición: '+ noReq, 3, 'divMensajeOperacion', 5000);  
            muestraModalGeneralConfirmacion(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i>Error</p></h3>',
            'Hubo un error al ejecutar la consulta no se pudo eliminar el elemento de la requisicion '+idReq+'.¿Desea volver a intentarlo?',"",
            "fnEliminarElemento","idReq, noReq, orden, mbflag");
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnDuplicarElemento(idReq, noReq, orden, mbflag){


    dataObj = {
        option: 'duplicarElementosRequisicion',
        idReq: idReq,
        noReq: noReq,
        orden: orden
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
            fnLoadRequisicion(idReq);
        }else{
            //muestraMensaje('No se elimino el elemento a la Requisición: '+ noReq, 3, 'divMensajeOperacion', 5000);  
            muestraModalGeneralConfirmacion(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i>Información</p></h3>',
            'Hubo un error al ejecutar la consulta no se pudo duplicar el elemento de la requisicion '+idReq+'.¿Desea volver a intentarlo?',"",
            "fnDuplicarElemento","idReq, noReq, orden, mbflag");
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnObtenerCvePresupuestal(partida, cveFrom, tagref, ue){
    var contenidoCvePresupuestal = "";
    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: partida,
        datotagref: tagref,
        datoue: ue
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
            datacvePreJson = data.contenido.datos;
            for (var info in datacvePreJson) {
                if(cveFrom[0].cvePresupuestal == datacvePreJson[info].cvePresupuestal){
                    contenidoCvePresupuestal += "<option selected value='" + datacvePreJson[info].cvePresupuestal + "'>" + datacvePreJson[info].cvePresupuestal + "</option>";    
                }else{
                    contenidoCvePresupuestal += "<option value='" + datacvePreJson[info].cvePresupuestal + "'>" + datacvePreJson[info].cvePresupuestal + "</option>";
                }
                
            }
        }
    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });
    return contenidoCvePresupuestal;

}

function fnObtenerCppt(partida, cveFrom, tagref, ue){
    var cppt = "";
    dataObj = { 
        option: 'mostrarCppt',
        dato: partida,
        datotagref: tagref,
        datoue: ue,
        clavepresupuestal: cveFrom
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
            dataCpptJson = data.contenido.datos;
            for (var info in dataCpptJson) {
                cppt = dataCpptJson[0].diferenciador;
            }
        }
    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });
    return cppt;

};

// Funcion que trae las claves presupuestales por la partida especifica
function fnReloadCvePpto(partida, ur, ue) {
    var regresaCvePpto= '';
    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: cpeprod,
        datotagref: tagref,
        datoue: ue
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
            regresaCvePpto = 1;
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
    return regresaCvePpto;
}

// Funcion que trae las claves presupuestales por la partida especifica
function fnCargaPartidaCvePpto(orden) {
    muestraCargandoGeneral();
    
    
    setTimeout(function (){
            var regresadatos= false;
    var cpeprod = $("#selectCvePartidaEspecifica" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();
    var esto = this;

    fnCargaPartidaCvePptoProduct(orden, tagref, ue);

    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: cpeprod,
        datotagref: tagref,
        datoue: ue
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj,
        
        /*beforeSend: function(){
            muestraCargandoGeneral();
        }*/

    }).done(function(data) {
    
        if (data.result) {
            ocultaCargandoGeneral();
            dataJson = data.contenido.datos;
            //console.log( "dataJson: " + JSON.stringify(dataJson) );
            var contenidoCvePresupuestal = "";
            var diferenciador = "";

            for (var info in dataJson) {
                contenidoCvePresupuestal += "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                diferenciador = dataJson[info].diferenciador;
                regresadatos= true;
            }

            $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestal" + orden).append(contenidoCvePresupuestal);
            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestal" + orden);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).multiselect('rebuild');
            $("#selectPartidaEspecificaCvePresupuestal" + orden).prop("readonly", true);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).attr('disabled', 'disabled');
            $("#addedUMArticulo" + orden).text('PZA');
            $("#addedCantidadArticulo" + orden).val("");
            $("#addedPEArticulo" + orden).val("");
            $("#addedCantidadTotalArticulo" + orden).val("");
            $("#addPresupuesto" + orden).empty();
            $("#idCvePresupuestal" + orden).removeClass("hide");
            
            // ocultaCargandoGeneral();

            if (!regresadatos) {
                //muestraMensaje("No existen clave presupuestal registrada para la partida "+cpeprod);
                //muestraMensaje("La partida "+ cpeprod +" no cuenta con presupuesto asignado para el ejercicio 2018");
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No existe una clave presupuestal para esta partida');
            }
        }
    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });
    return regresadatos;
    },2000);


    
}

//muestralos productos asignados a la partida selecionada
function fnCargaPartidaCvePptoProduct(orden, tagref, ue) {
    var cvePartida = $("#selectCvePartidaEspecifica" + orden).val();

    dataObj = { 
        option: 'mostrarPartidaCvePptoProdct',
        datoTagref: tagref,
        datoUe: ue,
        datoCvePartida: cvePartida
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj,
        /*beforeSend: function(){
            muestraCargandoGeneral();
        }*/
    }).done(function(data) {
    
        if (data.result) {
            ocultaCargandoGeneral();
            dataJson = data.contenido.datos;
            var contenidoIdArt = "";
            var contenidoDescArt = "";

            for (var info in dataJson) {
                contenidoIdArt += "<option value='" + dataJson[info].idProducto + "'>" + dataJson[info].idProducto + "</option>";
                contenidoDescArt += "<option value='" + dataJson[info].descripcionProducto + "'>" + dataJson[info].descripcionProducto + "</option>";
            }
            $("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append('<option value="0">Clave...</option>'+contenidoIdArt);
            fnFormatoSelectGeneral("#selectCveArticulo" + orden);
            $("#selectCveArticulo" + orden).multiselect('rebuild');
            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append('<option value="0">Articulo...</option>'+contenidoDescArt);
            fnFormatoSelectGeneral("#selectArticulo" + orden);
            $("#selectArticulo" + orden).multiselect('rebuild');
            $("#idCvePresupuestal" + orden).removeClass("hide");
            //// ocultaCargandoGeneral();
            if (dataJson.length == 0) {
               // muestraMensaje("La partida "+ cvePartida +" no tiene artículos relacionados");
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'La partida '+ cvePartida +' no tiene artículos relacionados');
            }
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });

}

// Funcion para traer claves presupuestales de acuerdo a la partida de servicios
function fnCargaPartidaCvePptoServicios(orden, tagref, cpeprod, ue) {
    var regresadatos= false;

    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: cpeprod,
        datotagref: tagref,
        datoue: ue
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
            //console.log( "dataJson: " + JSON.stringify(dataJson) );
            var contenidoCvePresupuestal = "";

            for (var info in dataJson) {
                contenidoCvePresupuestal += "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                regresadatos= true;
            }

            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoCvePresupuestal);
            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestalServ" + orden);
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect('rebuild');
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).prop("readonly", true);
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).attr('disabled', 'disabled');
            $("#idCvePresupuestal" + orden).removeClass("hide");
            
            // ocultaCargandoGeneral();

            if (!regresadatos) {
                //muestraMensaje("No existen clave presupuestal registrada para la partida "+cpeprod);
                //muestraMensaje("La partida "+ cpeprod +" no cuenta con presupuesto asignado para el ejercicio 2018");
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No existe una clave presupuestal para esta partida');
            }
        }
    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });

    return regresadatos;
}

// trae la informacion relacionada al articulo selecciondo por clave
function fnClaveArticulo(orden) {
    muestraCargandoGeneral();
    
    
    setTimeout(function (){
    var cveprod = $("#selectCveArticulo" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();
    var idReq = $("#idtxtRequisicion").val();
    dataObj = { 
        option: 'mostrarCveArticuloDatos',
        datocveart: cveprod,
        datodescart: '',
        datotagref: tagref,
        datoUe: ue
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj,
        
        /*beforeSend: function(){
            muestraCargandoGeneral();
        }*/
    }).done(function(data){
        if(data.result){
            
            dataJson = data.contenido.datos;
            var contIdArt = "";
            var contDescArt = "";
            var contDescPartida = "";
            var contUnidadArt = "";
            var contPrecioEArt = "";
            var contDispArt = "";
            for (var info in dataJson) {
                contIdArt += "<option value='" + dataJson[info].idProducto + "'>" + dataJson[info].idProducto + "</option>";
                contDescArt += "<option value='" + dataJson[info].descripcionProducto + "'>" + dataJson[info].descripcionProducto + "</option>";
                contUnidadArt = dataJson[info].unidad;
                contPrecioEArt = dataJson[info].precioEstimado;
                contDispArt = dataJson[info].existencia;
            }
            if(contPrecioEArt == 0){
                contPrecioEArt = '';
            }
            if(contDispArt == 0){
                contDispArt = '';
            }
            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append(contDescArt);
            $("#selectArticulo" + orden).multiselect('rebuild');
            $("#addedUMArticulo" + orden).empty();
            $("#addedUMArticulo" + orden).append(contUnidadArt);
            $("#addedUMArticulo" + orden).prop("readonly", true);
            $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
            $("#addedCantidadArticulo" + orden).val("");
            $("#addedCantidadArticulo" + orden).prop("readonly", false);
            $("#addedCantidadArticulo" + orden).attr('disabled', false);
            $("#addedPEArticulo" + orden).prop("readonly", false);
            $("#addedPEArticulo" + orden).attr('disabled', false);
            $("#addedPEArticulo" + orden).val("");
            $("#addedPEArticulo" + orden).val(""+ /*formatoComas*/( redondeaDecimal(contPrecioEArt) ) ); 
            $("#addedCantidadTotalArticulo" + orden).val("");
            $("#addedReglonArticulo" + orden).val("");
            $("#validaPresupuesto" + orden).prop("readonly", true);
            $("#validaPresupuesto" + orden).attr('disabled', 'disabled');

            fnObtenerPresupuesto(orden);
            ocultaCargandoGeneral();
        }else{
             ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    }); 
            }, 2000);
}

// funcion que trae los datos por la descripcion del articulo
function fnDescArticulo(orden){
    
    muestraCargandoGeneral();
    setTimeout(function (){
    var descprod = $("#selectArticulo" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();
    var noReq = $("#idtxtRequisicion").val();

    dataObj = { 
        option: 'mostrarCveArticuloDatos',
        datocveart: '',
        datodescart: descprod,
        datotagref: tagref,
        datoUe: ue
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj,
        
        /*beforeSend: function(){
            muestraCargandoGeneral();
        }*/

    }).done(function(data){
        if(data.result){
            
            
            dataJson = data.contenido.datos;
            var contIdArt = "";
            var contDescArt = "";
            var contDescPartida = "";
            var contUnidadArt = "";
            var contPrecioEArt = "";
            var contDispArt = "";

            for (var info in dataJson) {
                contIdArt += "<option value='" + dataJson[info].idProducto + "'>" + dataJson[info].idProducto + "</option>";
                contDescArt += "<option value='" + dataJson[info].descripcionProducto + "'>" + dataJson[info].descripcionProducto + "</option>";
                contUnidadArt = dataJson[info].unidad;
                contPrecioEArt = dataJson[info].precioEstimado;
                contDispArt = dataJson[info].existencia;
            }
            if(contPrecioEArt == 0){
                contPrecioEArt = '';
            }
            if(contDispArt == 0){
                contDispArt = '';
            }

            $("#contDispArt" + orden).val(""+ contDispArt);
            $("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append(contIdArt);
            $("#selectCveArticulo" + orden).multiselect('rebuild');
            $("#addedUMArticulo" + orden).empty();
            $("#addedUMArticulo" + orden).html(contUnidadArt);
            $("#addedUMArticulo" + orden).prop("readonly", true);
            $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
            $("#addedCantidadArticulo" + orden).val("");
            $("#addedCantidadArticulo" + orden).prop("readonly", false);
            $("#addedCantidadArticulo" + orden).attr('disabled', false);
            $("#addedPEArticulo" + orden).prop("readonly", false);
            $("#addedPEArticulo" + orden).attr('disabled', false);
            $("#addedPEArticulo" + orden).val("");
            if(contPrecioEArt == 0){
                $("#addedPEArticulo" + orden).val("");
            }else{
                $("#addedPEArticulo" + orden).val(""+ /*formatoComas*/( redondeaDecimal(contPrecioEArt) ) );
            }
            
            $("#addedCantidadTotalArticulo" + orden).val("");
            $("#addedReglonArticulo" + orden).val("");
            $("#validaPresupuesto" + orden).prop("readonly", true);
            $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
            fnObtenerPresupuesto(orden);
             ocultaCargandoGeneral();
        }else{
             ocultaCargandoGeneral();
        }

    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });
            }, 2000);
}

// Funcion que calcula el total por la cantidad del articulo
function fnCantidadArticulo(orden) {
    var a = $("#addedCantidadArticulo" + orden).val();
    var b = $("#addedPEArticulo" + orden).val();
    var e = $("#contDispArt" + orden).val();
    var t = a * b;
    var p = $("#addPresupuestoH"+orden).val();
    var d = p - t;
    var s = e - a;
    var noReq = $("#idtxtRequisicion").val();
    $("#addedCantidadTotalArticulo" + orden).val(""+t);

    if(d >= 0){
        $("#idCvePresupuestal" + orden).removeClass("hide");
        $("#addPresupuestoH"+orden).val(""+p);
        $("#validaPresupuesto" + orden).val("Ppto Suficiente");
        $("#validaPresupuesto" + orden).css('border','solid 1px #1B693F');
        $("#validaPresupuesto" + orden).css('color','#1B693F');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        iCntPI = 0;
    }else{
        $("#idCvePresupuestal" + orden).removeClass("hide");
        $("#addPresupuestoH"+orden).val(""+p);
        $("#validaPresupuesto" + orden).val("Ppto Insuficiente");
        $("#validaPresupuesto" + orden).css('border','solid 1px #ff0000');
        $("#validaPresupuesto" + orden).css('color','#ff0000');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        iCntPI = iCntPI +1;
    }
    if( s >= 0){
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = iCntND + 1;
     }else{
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("No Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = 0;
     }
}

// agrega dinamicamente nuevos renglones al grid de articulos
function fnArticulo(idRequisicion, orden, fEntrega, indiceIncremento, idPartida, idItem, descItem,cm, unidad, cantidad, precio, total, existencia, clavepresupuestal, descLarga, renglon, anexoTec, idItemDetalle){
    var idReq = $("#idtxtRequisicion").val();
    var noReq = $("#idtxtNoRequisicion").val();
    var dependencia = $('#selectRazonSocial').val();
    var ur = $('#selectUnidadNegocio').val();
    var ue = $('#selectUnidadEjecutora').val();
    var status = $("#idStatusReq").val();
    var perfilid = $("#idperfilusr").val();
    var tagref= $("#selectUnidadNegocio").val();
    var contenidoClaves= "";
    var forSelectAnexo = $('#anexoTecnicoCheck').is(':checked');
    console.log("status en el articulo inicial: %s", status);

    if(noReq == '' || noReq == null || noReq === 'undefined' || noReq == 0  ){
        noReq = 0;
    }
    
    $(containerArticulo).append('<div class="row p0 m0">'+
            '<ol id=idElementArticulo' + orden + ' class="idElementArticulo col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                //'<li id="idEliminarArticulo' + orden + '" class="w5p pt5"><span class="btRemoveArticulo btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveArticulo' + orden + '" title="Eliminar" onclick="fnRemoverElemento('+idReq+', '+noReq+', '+orden+', \'B\');"></span></li>' + 
                '<li id="idEliminarArticulo' + orden + '" class="w5p pt5"><span class="btRemoveArticulo btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveArticulo' + orden + '" title="Eliminar" onclick="fnEliminarElemento('+idReq+', '+noReq+', '+orden+', \'B\');"></span></li>' + 
                '<li id="idDuplicarArticulo' + orden + '" class="w3p pt3"><span class="glyphicon glyphicon-share btn btn-default btn-xs" id="btDuplicarArticulo' + orden + '" title="Duplicar" onclick="fnDuplicarElemento('+idReq+', '+noReq+', '+orden+', \'B\');"></span></li>' + 
                '<li id="idNumArticulo' + orden + '" class="w2p pt2"><input type="text" id="nProd' + orden + '" class="hide" value="'+orden+'"/><label  class="hide indice w100p text-center" type="text" id="numArticulo' + orden + '">'+orden+'</label><label class="num w100p text-center" type="text" id="num' + orden + '">'+indiceIncremento+'</label></li>' + 
                '<li id="idCvePartida'+orden+'" class="w10p p0"><select id="selectCvePartidaEspecifica' + orden + '" name="selectCvePartidaEspecifica' + orden + '" class="w100p form-control selectCvePartidaEspecifica" onchange="fnCargaPartidaCvePpto('+orden+');"></select></li>'+
                '<li id="idCvePartidaCveArticulo' + orden + '" class="w10p p0"><select id="selectCveArticulo' + orden + '" name="selectCveArticulo' + orden + '" class="selectCveArticulo form-control" onchange="fnClaveArticulo('+orden+');"><option value="0">Cve ...</option></select></li>'+
                // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                // '<li id="idCvePartidaDescArticulo' + orden + '" class="w40p p0"><select id="selectArticulo' + orden + '" name="selectArticulo' + orden + '" class="form-control selectArticulo" onchange="fnDescArticulo('+orden+');"><option value="0">Articulos ...</option></select></li>'+
                '<li id="idCvePartidaDescArticulo' + orden + '" class="w25p p0"><select id="selectArticulo' + orden + '" name="selectArticulo' + orden + '" class="form-control selectArticulo" onchange="fnDescArticulo('+orden+');"><option value="0">Articulos ...</option></select></li>'+
                '<li id="idAddedDescArticulo' + orden + '" class="w10p"><input class="w100p" id="descArticulo' + orden + '" type="text"  class="form-control"/></li>' + 
                '<li id="idUMArticulo' + orden + '" class="w5p pt5"><label  class="w100p addedUMArticulo" type="text" id="addedUMArticulo' + orden + '"></label></li>'+
                '<li id="idCantidadArticulo' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="addedCantidadArticulo vacia num w100p text-center" type="text" id="addedCantidadArticulo' + orden + '" placeholder="Cantidad" onblur="fnCalculaTotal('+orden+');" readonly="readonly" disabled="disabled"></li>'+
                //'<li id="idPEArticulo' + orden + '" class="w5p pt5"><input value="" class="w100p addedPEArticulo text-right" type="number" step="0.01" id="addedPEArticulo' + orden + '" placeholder="Precio" onblur="fnValidaPresupuesto('+orden+');" min="0" onkeypress="return fnsoloDecimalesGeneral(event, this)" ></li>'+
                // @NOTE: se modifica la sigueinte linea para agregar un valor por defecto en cero "0". modificación echa en la fecha 09.04.18
                // @NOTE: se modifica la sigueinte linea para quitar un valor por defecto en cero "0". modificación echa en la fecha 10.04.18
                // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                // '<li id="idPEArticulo' + orden + '" class="w5p pt5"><div id="precioart' + orden + '" class="m0 p0"><component-decimales class="m0 p0 h25" id="addedPEArticulo' + orden + '" name="addedPEArticulo' + orden + '" onblur="fnValidaPresupuesto('+orden+');"></component-decimales></div></li>'+ 
                '<li class="w1p pt9"><div class="m0 p0">$</div></li>'+ 
                '<li id="idPEArticulo' + orden + '" class="w7p pt5"><div id="precioart' + orden + '" class="m0 p0"><component-decimales class="m0 p0 h25 fixDecimal" id="addedPEArticulo' + orden + '" name="addedPEArticulo' + orden + '" onblur="fnValidaPresupuesto('+orden+');" onkeyup="return maxLongDecUp(event);" readonly="readonly" disabled="disabled"></component-decimales></div></li>'+ 
                // '<li id="idPEArticulo' + orden + '" class="w5p pt5"><div id="precioart' + orden + '" class="m0 p0"><component-decimales class="m0 p0 h25" id="addedPEArticulo' + orden + '" name="addedPEArticulo' + orden + '" value="0" onblur="fnValidaPresupuesto('+orden+');"></component-decimales></div></li>'+
                // '<li id="idCantidadTotalArticulo' + orden + '" class="w5p pt5"><input value="" class="addedCantidadTotalArticulo soloNumeros w100p  text-right" type="number" id="addedCantidadTotalArticulo' + orden + '" placeholder="Total" readonly="readonly" disabled="disabled"></li>'+
                '<li id="idCantidadTotalArticulo' + orden + '" class="w9p pt5"><input value="" class="addedCantidadTotalArticulo soloNumeros w100p  text-right" type="text" id="addedCantidadTotalArticulo' + orden + '" placeholder="Total" readonly="readonly" disabled="disabled"></li>'+
                // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                // '<li id="idRenglonAnexoArticulo' + orden + '" class="w10p pt5"><input onkeypress="return fnSoloBorrar(event)" class="addedReglonArticulo empty w70p mr2" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonart'+orden+'" class="btnanexorenglonart hide w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnSeleccionaAnexo('+idReq+',\''+ur+'\',\''+ue+'\','+orden+');fnMostrarRequisicionModal();"></div></li>'+
                '<li id="idRenglonAnexoArticulo' + orden + '" class="w8p pt5"><input onkeypress="return fnSoloBorrar(event)" class="addedReglonArticulo empty w70p mr2" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonart'+orden+'" class="btnanexorenglonart '+(forSelectAnexo?'':'hide')+' w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnSeleccionaAnexo('+idReq+',\''+ur+'\',\''+ue+'\','+orden+', \'B\');"></div></li>'+
            '</ol>' +
        '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupuestal: </label></span></li>'+
                    // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                    // '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                    '<li class="w80p" id="idAddCvePresupuestal' + orden + '" >'+
                        '<select id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"></select>'+
                    '</li>'+
                    '<li class="w10p"><input type="hidden" id="diferenciador'+orden+'" /></li>'+
                '</ol>'+
            '</div>');

    $('#idMainListContentArticulo').append(containerArticulo);
    fnEjecutarVueGeneral('precioart' + orden );

    // Cambios pruebas, variable para deshabilitar pagina
    var deshabilitaPagina = 0;

    if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original' || status == 'Authorised'){
    //if(status != 'Creada' || status != 'Capturado' || status != 'Validar' || status != 'PorAutorizar'){
        $("#idAnexoTap").addClass('hide');
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
        // Cambios pruebas, se deshabilita observaciones
        $("#txtAreaObs").attr('disabled', 'disabled');
        deshabilitaPagina = 1;
    }else if((perfilid == 9 ) && (status == 'Validar' || status == 'PorAutorizar' )){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
        // Cambios pruebas, se deshabilita observaciones
        $("#txtAreaObs").attr('disabled', 'disabled');
        deshabilitaPagina = 1;
    }else if((perfilid == 10 ) && ( status == 'PorAutorizar' )){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
        // Cambios pruebas, se deshabilita observaciones
        $("#txtAreaObs").attr('disabled', 'disabled');
        deshabilitaPagina = 1;
    } else {
        $("#addedCantidadArticulo" + orden).prop("readonly", false); 
        $("#addedCantidadArticulo" + orden).attr('disabled', false);
        // Cambios pruebas, Se deshabilita caja de precio para ser editada
        $("#precioart" + orden).prop("readonly", false); 
        $("#precioart" + orden).attr('disabled', false);
        $("#addedPEArticulo" + orden).prop("readonly", false);
        $("#addedPEArticulo" + orden).attr('disabled', false);
    }

    if(!$.isNumeric(idPartida)){
        // Cambios pruebas, Se agrega para que pueda cambiar la partida
        // if (!fnCargarPartidaProducto(periodoR, tagref, orden)) {
        //     muestraMensaje("No existen claves de partida en articulos para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
        // }
        $("#selectCvePartidaEspecifica" + orden).empty();
        $("#selectCvePartidaEspecifica" + orden).append( fnCrearDatosSelect(jsonPartidaGeneralBienes, '', '', 1) );

        if(!forSelectAnexo){
            $("#btnanexorenglonart"+orden).addClass('hide');
        }else{
            $("#btnanexorenglonart"+orden).removeClass('hide');
        }
        //$("#num" + orden).text(indiceIncremento);
        //$("#numArticulo" + orden).text(indiceIncremento);
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');

    } else {
        var total = /*formatoComas*/fixDecimales( redondeaDecimal(total)+"" );
        
        if(!forSelectAnexo){
            $("#btnanexorenglonart"+orden).addClass('hide');
        }else{
            $("#btnanexorenglonart"+orden).removeClass('hide');
        }
        $("#num" + orden).text(orden);
        $("#numArticulo" + orden).text(orden);
        $("#selectCveArticulo" + orden).empty();
        $("#selectCveArticulo" + orden).html('<option value="'+idItem+'">'+idItem+'</option>');
        $("#selectArticulo" + orden).empty();
        $("#selectArticulo" + orden).html('<option value="'+descItem+'">'+descItem+'</option>');
        $("#descArticulo" + orden).val(''+cm);
        $("#addedUMArticulo" + orden).html(unidad);
        $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
        $("#addedCantidadArticulo" + orden).val(""+cantidad);
        $("#addedPEArticulo" + orden).val(""+ /*formatoComas*/( redondeaDecimal(precio) ) );
        $("#addedCantidadTotalArticulo" + orden).val("$ "+total);
        $("#idCvePresupuestal" + orden).removeClass("hide");

        if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original' || status == 'Authorised' || deshabilitaPagina == 1){
            // Cambios pruebas, se valida estatus para solo mostrar el registro
            $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestal" + orden).html('<option value="'+clavepresupuestal[0].cvePresupuestal+'">'+clavepresupuestal[0].cvePresupuestal+'</option>');
        } else {
            for (var clave in clavepresupuestal) {
                contenidoClaves += "<option value='" + clavepresupuestal[clave].cvePresupuestal + "'>" + clavepresupuestal[clave].cvePresupuestal + "</option>";
            }
            var cve = fnObtenerCvePresupuestal(idPartida, clavepresupuestal, tagref, ue);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
            //$("#selectPartidaEspecificaCvePresupuestal" + orden).append(contenidoClaves);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).append(cve);
            var cveactual = $("#selectPartidaEspecificaCvePresupuestal" + orden).val();

            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestal"+orden);
        }
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).multiselect('rebuild');
        
        var cppt = fnObtenerCppt(idPartida, cveactual, tagref, ue);
        console.log(cppt);
        $("#diferenciador" + orden).val(""+cppt);
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');

        var a = $("#addedCantidadArticulo" + orden).val();
        var b = $("#addedPEArticulo" + orden).val();
        var e = existencia;
        var t = a * b;
        var p = $("#addPresupuestoH"+orden).val();
        var d = p - t;
        var s = e - a;

        if( s > 0){
            $("#addDispArticulo" + orden).val("");
            $("#addDispArticulo" + orden).html("Disponible");
            $("#addDispArticulo" + orden).prop("readonly", true);
            $("#addDispArticulo" + orden).attr('disabled', 'disabled');
            iCntND = iCntND + 1;

        }else{
            $("#addDispArticulo" + orden).val("");
            $("#addDispArticulo" + orden).html("No Disponible");
            $("#addDispArticulo" + orden).prop("readonly", true);
            $("#addDispArticulo" + orden).attr('disabled', 'disabled');
            iCntND = 0;
        }
            
        $("#addedReglonArticulo" + orden).val(""+renglon);
        $("#addedReglonArticulo" + orden).prop("readonly", true);
        $("#addedReglonArticulo" + orden).attr('disabled', 'disabled');

        if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original' || status == 'Authorised' || deshabilitaPagina == 1){
            // Cambios pruebas, se valida estatus para solo mostrar el registro
            $("#selectCvePartidaEspecifica" + orden).empty();
            $("#selectCvePartidaEspecifica" + orden).html('<option value="'+idPartida+'">'+idPartida+'</option>');
        } else {
            // Cambios pruebas, Se agrega para que pueda cambiar la partida
            $("#selectCvePartidaEspecifica" + orden).empty();
            $("#selectCvePartidaEspecifica" + orden).append( fnCrearDatosSelect(jsonPartidaGeneralBienes, '', idPartida, 1) );
        }
    }
    fnFormatoSelectGeneral("#selectCvePartidaEspecifica"+orden);
}

// modfica los valores de cada renglon de articulos
function fnModificarArticulo(req,orden){
    console.log("modificando orden: %s",orden);
    console.log("modificando req: %s",req);
    var ic = $("#selectCveArticulo" + orden).val();
    var fe = $("#idFechaEntrega").val();
    var id = $("#selectArticulo" + orden).val();
    var cm = $("#descArticulo" + orden).val();
    var up = $("#addedPEArticulo" + orden).val();
    var al = $("#addQtyArticulo" + orden).val();
    var cp = $("#selectPartidaEspecificaCvePresupuestal" + orden).val();
    var qy = $("#addedCantidadArticulo" + orden).val();
    var comments = $("#txtAreaObs").val();
    var total = qy * up;
    $("#addedCantidadTotalArticulo"+ orden).append(total);
    var renglon = $("#addedReglonArticulo" + orden).val();
    var cppt = $("#diferenciador"+orden).val();
    console.log("este es el diferienciador",cppt);
    dataObj = {
        option: 'modificarElementosRequisicion',
        req: req,
        itemcode: ic,
        fechent: fe,
        itemdesc: id,
        price: up,
        cantidad: qy,
        almacen: al,
        order: orden,
        cvepre: cp,
        comments: comments,
        renglon: renglon,
        cppt: cppt,
        cm:cm
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
            //muestraMensaje('Se modifico la Requisición con el número: ' + req, 1, 'divMensajeOperacion', 5000);
        } else {
            muestraMensaje('No se Modifico la Requisición', 3, 'divMensajeOperacion', 5000);
            
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function fnClavePartidaServicioCodigo(orden, select) {
    // Agrega que la clave del servicio se la del catalogo
    console.log("codigo servicio: "+select.value);
    $("#cveServicio"+orden).val(""+select.value);
}

// agrega dinamicamente nuevos renglones al grid de servicios
function fnServicio(idRequisicion, orden, fEntrega, indiceIncremento, idPartida, descPartida, idItem, descItem, precio, clavepresupuestal, descLarga, renglon, anexoTec, idItemDetalle){
    var idReq = $("#idtxtRequisicion").val();
    var noReq = $("#idtxtNoRequisicion").val();
    var dependencia = $('#selectRazonSocial').val();
    var ur = $('#selectUnidadNegocio').val();
    var ue = $('#selectUnidadEjecutora').val();
    var status = $("#idStatusReq").val();
    var perfilid = $("#idperfilusr").val();
    var tagref= $("#selectUnidadNegocio").val();
    var contenidoClaves= "";
    var forSelectAnexo = $('#anexoTecnicoCheck').is(':checked');
    console.log("orden en el servicio inicial: %s", orden);
    console.log("indiceIncremento en el servicio : %s", indiceIncremento);
    if(noReq == '' || noReq == null || noReq === 'undefined' || noReq == 0  ){
        noReq = 0;
    }

    $(containerServicio).append('<div class="row p0 m0">' +
        '<ol id=idElementServicio' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' + 
                //'<li id="idEliminarServicio' + orden + '" class="w5p pt5"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove bt" id="btRemoveServicio' + orden + '" title="Eliminar" onclick="fnRemoverElemento('+idReq+', '+noReq+', '+orden+', \'D\');"></span></li>' + 
                '<li id="idEliminarServicio' + orden + '" class="w5p pt5"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove bt" id="btRemoveServicio' + orden + '" title="Eliminar" onclick="fnEliminarElemento('+idReq+', '+noReq+', '+orden+', \'D\');"></span></li>' + 
                '<li id="idNumServicio' + orden + '" class="w5p pt5"><input type="text" id="nServ' + orden + '" class="hide" value="'+orden+'"/><label  class="hide indice w50p text-center" type="text" id="numServicio' + orden + '">'+orden+'</label><label class="num w100p text-center" type="text" id="num' + orden + '">'+indiceIncremento+'</label></li>' + 
                '<li id="idAddedPartidaServicio' + orden + '" class="w10p"><select id="selectCvePartidaEspecificaServ' + orden + '" name="selectCvePartidaEspecificaServ' + orden + '" class="form-control selectCvePartidaEspecificaServ" onchange="fnClavePartidaServicio(' + orden + ', this.name)"></select></li>' + 
                '<li id="idAddedDescPartida' + orden + '" class="w20p"><select id="selectDescPartidaEspecificaServ' + orden + '" name="selectDescPartidaEspecificaServ' + orden + '" class="form-control selectDescPartidaEspecificaServ" onchange="fnClavePartidaServicioCodigo(' + orden + ', this)"></select></li>' + 
                '<li id="idAddedDescServicio' + orden + '" class="w35p pt5"><input type="text" id="cveServicio' + orden + '" class="hide"/><input class="w95p" id="descServicio' + orden + '" type="text" /></li>' + 
                '<li id="idAddLongDescServicio'+ orden +'" class="w5p pt5"><div class="btn btn-info btn-xs glyphicon glyphicon-comment" id="btLongDescServicio' + orden + '" onclick="fnEditorTextoOpen('+orden+');"></div></li>'+
                '<li id="idCantidadServicio' + orden + '" class="w5p pt5"><label  class="w100p addedCantidadServicio text-center" type="text" id="addedCantidadServicio' + orden + '">1</label></li>'+
                //'<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><input class="w100p addedPEServicio" type="number" step="0.01" id="addedPEServicio' + orden + '" placeholder="Precio" min="0" onkeypress="return fnsoloDecimalesGeneral(event, this)"></li>' + 
                // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                // '<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><div id="precioserv' + orden + '" class="m0 p0"><component-decimales class="m0 p0 h25" id="addedPEServicio' + orden + '" name="addedPEServicio' + orden + '" value="0" ></component-decimales></div></li>' + 
                '<li id="idAddedPEServicio' + orden + '" class="w7p pt5"><div id="precioserv' + orden + '" class="m0 p0"><component-decimales class="m0 p0 h25 fixDecimal" id="addedPEServicio' + orden + '" name="addedPEServicio' + orden + '" value="0" onkeyup="return maxLongDecUp(event);" readonly="readonly" disabled="disabled"></component-decimales></div></li>' + 
                // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                // '<li id="idAddedReglonServicio' + orden + '" class="w10p pt5"><input onkeypress="return fnSoloBorrar(event)" class="addedReglonServicio w70p mr2" type="text" id="addedReglonServicio' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonserv'+orden+'" class="btnanexorenglonserv hide w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnSeleccionaAnexo('+idReq+',\''+ur+'\',\''+ue+'\','+orden+');fnMostrarRequisicionModal();"></div></li>'+
                '<li id="idAddedReglonServicio' + orden + '" class="w8p pt5"><input onkeypress="return fnSoloBorrar(event)" class="addedReglonServicio w70p mr2" type="text" id="addedReglonServicio' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonserv'+orden+'" class="btnanexorenglonserv '+(forSelectAnexo?'':'hide')+' w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnSeleccionaAnexo('+idReq+',\''+ur+'\',\''+ue+'\','+orden+', \'D\');"></div></li>'+
                '</ol>' +
            '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupuestal: </label></span></li>'+
                    // @NOTE: Cambio de tamaño conforme a solicitud @date:11.04.18
                    // '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                    '<li class="w80p" id="idAddCvePresupuestal' + orden + '" >'+
                        '<select id="selectPartidaEspecificaCvePresupuestalServ' + orden + '" name="selectPartidaEspecificaCvePresupuestalServ' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestalServ"><option value="0">Clave Presupestal</option></select>'+
                    '</li>'+
                    '<li class="w10p"><input type="hidden" id="diferenciador'+orden+'" /></li>'+
                '</ol>'+
            '</div>'+
            '<div id="idPopupEditor'+orden+'" class="idPopupEditor" style="display: none;">'+
                '<div class="content-popup">'+
                    '<div class="popupEditorClose" onclick="fnEditorTextoClose()"><a href="#" id="idPopupEditorClose" onclick="fnEditorTextoClose()">x</a></div>'+
                    '<div class="hA">'+
                        '<h2>Descripción del Servicio</h2>'+
                        '<textarea class="w100p" name="" id="idLongText'+orden+'" cols="" rows="20"></textarea>'+
                        '<div id="idBtnGuardarLongText'+orden+'" class="btn btn-default" onclick="fnEditorTextoClose();">Guardar</div>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="popup-overlay" style="display: none;"></div>');
            
    $('#idMainListContentServicio').append(containerServicio);
    fnEjecutarVueGeneral('precioserv' + orden );

    // Cambios pruebas, variable para deshabilitar pagina
    var deshabilitaPagina = 0;

    if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original' || status == 'Authorised'){
    //if(status != 'Creada' || status != 'Capturado' || status != 'Validar' || status != 'PorAutorizar'){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#idBtnAgregarServicio").addClass('hide');
        $("#idAnexoTap").addClass('hide');
        $("#btRemoveServicio" + orden).addClass('hide');
        $("#btRemoveServicio" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonserv" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
        // Cambios pruebas, se deshabilita observaciones
        $("#txtAreaObs").attr('disabled', 'disabled');
        $("#descServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).addClass('hide');
        deshabilitaPagina = 1;
    }else if((perfilid == 9 ) && (status == 'Validar' || status == 'PorAutorizar' )){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveServicio" + orden).addClass('hide');
        $("#btRemoveServicio" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonserv" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
        // Cambios pruebas, se deshabilita observaciones
        $("#txtAreaObs").attr('disabled', 'disabled');
        $("#descServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).addClass('hide');
        deshabilitaPagina = 1;
    }else if((perfilid == 10 ) && ( status == 'PorAutorizar' )){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveServicio" + orden).addClass('hide');
        $("#btRemoveServicio" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonserv" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
        // Cambios pruebas, se deshabilita observaciones
        $("#txtAreaObs").attr('disabled', 'disabled');
        $("#descServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).addClass('hide');
        deshabilitaPagina = 1;
    } else {
        // Cambios pruebas, Se deshabilita caja de precio para ser editada
        $("#addedPEServicio" + orden).prop("readonly", false); 
        $("#addedPEServicio" + orden).attr('disabled', false);
    }

    if(!$.isNumeric(idPartida)){
        // Cambios pruebas, Se agrega para que pueda cambiar la partida
        // if (!fnCargarPartidaServicio(periodoR, tagref, orden)) {
        //     muestraMensaje("No existen claves de partida en servicios para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
        // }
        $("#selectCvePartidaEspecificaServ" + orden).empty();
        $("#selectCvePartidaEspecificaServ" + orden).append( fnCrearDatosSelect(jsonPartidaGeneralServicios, '', '', 1) );
        //$("#num" + orden).text(indiceIncremento);
        //$("#numServicio" + orden).text(indiceIncremento);
        $("#addedPEServicio" + orden).prop("readonly", true);
        $("#addedPEServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).prop("readonly", true);
        $("#btLongDescServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).removeAttr("onclick");
        if(viewAnexo == 0){
            $("#btnanexorenglonserv"+orden).addClass('hide');
        }else{
            $("#btnanexorenglonserv"+orden).removeClass('hide');
        }

    }else{
        $("#num" + orden).text(orden);
        $("#numServicio" + orden).text(orden);
        $("#selectDescPartidaEspecificaServ" + orden).empty();
        $("#selectDescPartidaEspecificaServ" + orden).html('<option value="'+idItem+'">'+descPartida+'</option>');
        $("#cveServicio"+ orden).val(""+idItem);
        $("#descServicio"+ orden).val(""+descItem);
        $("#addedCantidadServicio" + orden).html('1');
        $("#addedPEServicio" + orden).val(""+ /*formatoComas*/( redondeaDecimal(precio) ) );
        $("#idLongText" + orden).val(""+descLarga);
        $("#idBtnGuardarLongText" + orden).removeAttr("onclick");
        $("#idCvePresupuestal" + orden).removeClass("hide");
        
        if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original' || status == 'Authorised' || deshabilitaPagina == 1){
            // Cambios pruebas, se valida estatus para solo mostrar el registro
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).html('<option value="'+clavepresupuestal[0].cvePresupuestal+'">'+clavepresupuestal[0].cvePresupuestal+'</option>');
        } else {
            for (var clave in clavepresupuestal) {
                contenidoClaves= '<option value="'+clavepresupuestal[clave].cvePresupuestal+'">'+clavepresupuestal[clave].cvePresupuestal+'</option>';
            }
            var cve = fnObtenerCvePresupuestal(idPartida, clavepresupuestal, tagref, ue);
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
            //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoClaves);
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(cve);
            var cveActual = $("#selectPartidaEspecificaCvePresupuestalServ" + orden).val();

            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestalServ"+orden);
        }
        //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect("rebuild");
        var cppt = fnObtenerCppt(idPartida, cveActual, tagref, ue);
        $("#diferenciador" + orden).val(""+cppt);

        $("#addedReglonServicio" + orden).val(""+renglon);
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        // $("#addedPEServicio" + orden).prop("readonly", true);
        // $("#addedPEServicio" + orden).attr('disabled', 'disabled');
        
        if(viewAnexo == 0){
            $("#btnanexorenglonserv"+orden).addClass('hide');
        }else{
            $("#btnanexorenglonserv"+orden).removeClass('hide');
        }

        if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original' || status == 'Authorised' || deshabilitaPagina == 1){
            // Cambios pruebas, se valida estatus para solo mostrar el registro
            $("#selectCvePartidaEspecificaServ" + orden).empty();
            $("#selectCvePartidaEspecificaServ" + orden).html('<option value="'+idPartida+'">'+idPartida+'</option>');
        } else {
            // Cambios pruebas, Se agrega para que pueda cambiar la partida
            $("#selectCvePartidaEspecificaServ" + orden).empty();
            $("#selectCvePartidaEspecificaServ" + orden).append( fnCrearDatosSelect(jsonPartidaGeneralServicios, '', idPartida, 1) );
        }
    }
    fnFormatoSelectGeneral("#selectCvePartidaEspecificaServ"+orden);
    fnFormatoSelectGeneral("#selectDescPartidaEspecificaServ"+orden);
}

// muestra los datos de los servicios por clave de la partida o por la descripción de la partida
function fnClavePartidaServicio(orden, elemento) {
    muestraCargandoGeneral();
    
    
    setTimeout(function (){

    var tagref = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();
    var partida = $("#selectCvePartidaEspecificaServ" + orden).val();
    var partidaDesc = $("#selectDescPartidaEspecificaServ"+orden+" option:selected").text();
    var regresadatos= false;
    var contenidoPartidaEspDescServ = "";
    var contenidoPartidaEspCveServ = "";
    var contenidoCveServ = "";
    var contenidoPrecioEServ = "";
    var contenidoCvePresupuestalServ = "";
    console.log(partida);
    console.log(partidaDesc);

    contenidoCvePresupuestalServ= $("#selectDescPartidaEspecificaServ"+orden+" option:selected").attr("data-clave");

    if (elemento == "selectDescPartidaEspecificaServ"+orden) {
        partida = $("#selectDescPartidaEspecificaServ" + orden).val();
    }
    dataObj = { 
        option: 'mostrarPartidaCveServicio',
        datoserv: partida,
        datotagref: tagref
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj,
        
        /*beforeSend: function(){
            muestraCargandoGeneral();
        }*/
    }).done(function(data) {
        if (data.result) {
            ocultaCargandoGeneral();
            dataJson = data.contenido.datos;
            var num = 1;
            for (var info in dataJson) {
                contenidoPartidaEspDescServ += "<option value='" + dataJson[info].idPartidaEspecifica + "'>" + dataJson[info].descPartidaEspecifica + "</option>";
                contenidoPartidaEspCveServ += "<option value='" + dataJson[info].idPartidaEspecifica + "'>" + dataJson[info].idPartidaEspecifica + "</option>";
                if (num == 1) {
                    contenidoCveServ = dataJson[info].idServicio;
                }
                contenidoPrecioEServ = dataJson[info].precioEstimado;
                contenidoCvePresupuestalServ += "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                num ++;
            }
            regresadatos= true;
        }
         ocultaCargandoGeneral();
    }).fail(function(result) {
         ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
         ocultaCargandoGeneral();
    });

     if (elemento == "selectDescPartidaEspecificaServ"+orden) {
        if(regresadatos){
            $("#selectCvePartidaEspecificaServ" + orden).empty();
            $("#selectCvePartidaEspecificaServ" + orden).append(contenidoPartidaEspCveServ);
            $("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');
        }else{
            $("#selectCvePartidaEspecificaServ" + orden).empty();
            $("#selectCvePartidaEspecificaServ" + orden).append("<option value='" + partida + "'>" + partida + "</option>");
            $("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');
        }
    }
    if (elemento == "selectCvePartidaEspecificaServ"+orden) {
        if(regresadatos){
            $("#selectDescPartidaEspecificaServ" + orden).empty();
            $("#selectDescPartidaEspecificaServ" + orden).append(contenidoPartidaEspDescServ);
            $("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');
        }else{
            $("#selectDescPartidaEspecificaServ" + orden).empty();
            $("#selectDescPartidaEspecificaServ" + orden).append("<option value='" + partida + "'>" + partidaDesc + "</option>");
            $("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');
        }
            
    }

    /*if (elemento == "selectDescPartidaEspecificaServ"+orden) {
        if(regresadatos){
            $("#selectCvePartidaEspecificaServ" + orden).empty();
            $("#selectCvePartidaEspecificaServ" + orden).append(contenidoPartidaEspCveServ);
            $("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');
        }
    }
    if (elemento == "selectCvePartidaEspecificaServ"+orden) {
        if(regresadatos){
            $("#selectDescPartidaEspecificaServ" + orden).empty();
            $("#selectDescPartidaEspecificaServ" + orden).append(contenidoPartidaEspDescServ);
            $("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');
        }
            
    }*/
    console.log("contenidoCveServ: "+contenidoCveServ);
    $("#cveServicio"+ orden).empty();
    $("#cveServicio"+ orden).val(""+contenidoCveServ);
    
    $("#addedCantidadServicio" + orden).html(1); 
    if(contenidoPrecioEServ <= 0){
        $("#addedPEServicio" + orden).val("");
    }else{
        $("#addedPEServicio" + orden).val( /*formatoComas*/( redondeaDecimal(contenidoPrecioEServ) ) );   // valor unitario del servicio    
    }   // cantidad por default
    
    $("#addedPEServicio" + orden).prop("readonly", false);
    $("#addedPEServicio" + orden).attr('disabled', false);

    $("#btLongDescServicio" + orden).prop("readonly", false);
    $("#btLongDescServicio" + orden).attr('disabled', false);
    $("#btLongDescServicio" + orden).attr("onclick","fnEditorTextoOpen("+orden+")");
    $("#addedReglonServicio" + orden).prop("readonly", false);
    $("#addedReglonServicio" + orden).attr('disabled', false);
    $("#btnanexorenglonserv" + orden).prop("readonly", false);
    $("#btnanexorenglonserv" + orden).attr('disabled', false);
 
    fnCargaPartidaCvePptoServicios(orden, tagref, partida, ue);

    if (!regresadatos) {
        //muestraMensaje("No existen clave y descripción de servicio.", 3, "divMensajeOperacion");
        muestraMensaje("La clave no cuenta con configuración CAMBS.", 3, "divMensajeOperacion");
    }

     // ocultaCargandoGeneral();

    return regresadatos;
            }, 2000);
}

/**Modifica el elemento creado en la seccion Servicios*/
function fnModificarServicio(r,orden){
    console.log("modificando req: %s",r);
    var r = $("#idtxtRequisicion").val();
    var ic = $("#cveServicio" + orden).val();
    var fe = $("#idFechaEntrega").val();
    var id = $("#descServicio"+ orden).val();
    var up = $("#addedPEServicio" + orden).val();
    var cp = $("#selectPartidaEspecificaCvePresupuestalServ" + orden).val();
    var rn = $("#addedReglonServicio" + orden).val();
    var lt = $("#idLongText" + orden).val();
    var cppt = $('#diferenciador'+orden).val();

    console.log("modificando ic: %s",ic);
    console.log("modificando fe: %s",fe);
    console.log("modificando id: %s",id);
    console.log("modificando up: %s",up);
    console.log("modificando cp: %s",cp);
    console.log("modificando rn: %s",rn);
    console.log("modificando lt: %s",lt);
    console.log("modificando cppt: %s",cppt);

    dataObj = {
        option: 'modificarElementosRequisicion',
        req: r,
        itemcode: ic,
        fechent: fe,
        itemdesc: id,
        price: up,
        cantidad: '1',
        almacen: '0',
        order: orden,
        cvepre:cp,
        longText: lt,
        renglon: rn,
        cppt: cppt
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
            //fnActualizaPresupuestoServicio();
            //muestraMensaje('Se modifico la Requisición con el número: ' + r, 1, 'divMensajeOperacion', 5000);
        } else {
            muestraMensaje('No se Modifico la Requisición ', 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log( result );
    });
}

function fnInstrumental(noReq, orden, fEntrega, indiceIncremento, idPartida, idItem, descItem, unidad, cantidad, precio, total, existencia, clavepresupuestal, descLarga, renglon, anexoTec){
    var idReq = $("#idtxtRequisicion").val();
    var dependencia = $('#selectRazonSocial').val();
    var ur = $('#selectUnidadNegocio').val();
    var ue = $('#selectUnidadEjecutora').val();
    var status = $("#idStatusReq").val();
    var perfilid = $("#idperfilusr").val();
    var tagref= $("#selectUnidadNegocio").val();
    var contenidoClaves= "";

    $(containerInstrumental).append('<div class="row p0 m0">'+
            '<ol id=idElementInstrumental' + orden + ' class="idElementInstrumental col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                '<li id="idEliminarInstrumental' + orden + '" class="w5p pt5"><span class="btRemoveInstrumental btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveInstrumental' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumInstrumental' + orden + '" class="w5p pt5"><input type="text" id="nInstrumental' + orden + '" class="hide" value="'+orden+'"/><label  class="w100p text-center" type="text" id="numInstrumental' + orden + '"></label></li>' + 
                '<li id="idCvePartidaInstrumental'+orden+'" class="w10p p0"><select id="selectCvePartidaEspecificaInstrumental' + orden + '" name="selectCvePartidaEspecificaInstrumental' + orden + '" class="w100p form-control selectCvePartidaEspecificaInstrumental" onchange="fnCargaPartidaCveInstr('+orden+');"></select></li>'+
                '<li id="idCvePartidaCveInstrumental' + orden + '" class="w10p p0"><select id="selectCveInstrumental' + orden + '" name="selectCveInstrumental' + orden + '" class="selectCveInstrumental form-control" onchange=""><option value="0">Cve ...</option></select></li>'+
                '<li id="idCvePartidaDescInstrumental' + orden + '" class="w50p p0"><select id="selectInstrumental' + orden + '" name="selectInstrumental' + orden + '" class="form-control selectInstrumental" onchange=""><option value="0">Instrumentales ...</option></select></li>'+
                '<li id="idPEInstrumental' + orden + '" class="w10p pt5 pl5"><input value="" class="soloNumeros w100p addedPEInstrumental text-right" type="number" id="addedPEArticulo' + orden + '" placeholder="Precio" onblur="" readonly="readonly" disabled="disabled"></li>'+
                '<li id="idRenglonAnexoInstrumental' + orden + '" class="w10p pt5"><input onkeypress="return fnSoloBorrar(event)" class="addedReglonInstrumental empty w70p mr2" type="text" id="addedReglonInstrumental' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonart'+orden+'" class="btnanexorenglonart hide w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnSeleccionaAnexo('+idReq+',\''+ur+'\',\''+ue+'\','+orden+');"></div></li>'+
            '</ol>' +
        '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupestal: </label></span></li>'+
                    '<li class="w50p" id="idAddCvePresupuestalInstrumental' + orden + '" >'+
                        '<select id="selectPartidaEspecificaCvePresupuestalInstrumental' + orden + '" name="selectPartidaEspecificaCvePresupuestalInstrumental' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestalInstrumental"></select>'+
                    '</li>'+
                    '<li class="w10p"><input type="hidden" id="diferenciador'+orden+'" /></li>'+
                '</ol>'+
            '</div>');

    $('#idMainListContentInstrumental').append(containerInstrumental);

    $("#btRemoveInstrumental" + orden).click(function() { 
        $('#addedReglonInstrumental' + orden).val("");
        $(this).parent().parent().remove();
        $("#idCvePresupuestal" + orden).remove();
        fnEliminarElemento(noReq,orden);
    });

    //if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original'){
    if(status != 'Creada' || status != 'Capturado' || status != 'Validar' || status != 'PorAutorizar'){
        $("#idAnexoTap").addClass('hide');
        $("#idBtnAgregarInstrumental").addClass('hide');
        $("#btRemoveInstrumental" + orden).addClass('hide');
        $("#btRemoveInstrumental" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonInstrumental" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }else if((perfilid == 9 ) && (status == 'Validar' || status == 'PorAutorizar' )){
        $("#idBtnAgregarInstrumental").addClass('hide');
        $("#btRemoveInstrumental" + orden).addClass('hide');
        $("#btRemoveInstrumental" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonInstrumental" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }else if((perfilid == 10 ) && ( status == 'PorAutorizar' )){
        $("#idBtnAgregarInstrumental").addClass('hide');
        $("#btRemoveInstrumental" + orden).addClass('hide');
        $("#btRemoveInstrumental" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonInstrumental" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    } else{
        $("#addedCantidadInstrumental" + orden).prop("readonly", false); 
        $("#addedCantidadInstrumental" + orden).attr('disabled', false);
        $("#addedPEInstrumental" + orden).prop("readonly", false);
        $("#addedPEInstrumental" + orden).attr('disabled', false);
    }

    if(!$.isNumeric(idPartida)){
        if (!fnCargarPartidaInstrumental(periodoR, tagref, orden)) {
            muestraMensaje("No existen claves de partida en Instrumental para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
        }

        if(viewAnexo == 0){
            $("#btnanexorenglonInstrumental"+orden).addClass('hide');
        }else{
            $("#btnanexorenglonInstrumental"+orden).removeClass('hide');
        }

        $("#numInstrumental" + orden).html(""+orden);
        $("#numInstrumental" + orden).prop("readonly", true);
        $("#numInstrumental" + orden).attr('disabled', 'disabled');      

    } else {

        if(viewAnexo == 0){
            $("#btnanexorenglonInstrumental"+orden).addClass('hide');
        }else{
            $("#btnanexorenglonInstrumental"+orden).removeClass('hide');
        }
        
    }
    fnFormatoSelectGeneral("#selectCvePartidaEspecificaInstrumental"+orden);
    fnFormatoSelectGeneral("#selectCveInstrumental"+orden);
    fnFormatoSelectGeneral("#selectInstrumental"+orden);
    fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestalInstrumental"+orden);
}

function fnModificarInstrumental(req,orden){
    var ic = $("#selectCveInstrumental" + orden).val();
    var fe = $("#idFechaEntrega").val();
    var id = $("#selectInstrumental" + orden).val();
    var up = $("#addedPEInstrumental" + orden).val();
    var cp = $("#selectPartidaEspecificaCvePresupuestal" + orden).val();
    var qy = $("#addedCantidadArticulo" + orden).val();
    var comments = $("#txtAreaObs").val();
    var renglon = $("#addedReglonArticulo" + orden).val();

    dataObj = {
        option: 'modificarElementosRequisicion',
        req: req,
        itemcode: ic,
        fechent: fe,
        itemdesc: id,
        price: up,
        cantidad: 1,
        almacen: 0,
        order: orden,
        cvepre: cp,
        comments: comments,
        renglon: renglon
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
            //muestraMensaje('Se modifico la Requisición con el número: ' + req, 1, 'divMensajeOperacion', 5000);
        } else {
            muestraMensaje('No se Modifico la Requisición', 3, 'divMensajeOperacion', 5000);
            
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

// Mostrar las claves instrumental
function fnCargaPartidaCveInstr(orden){
    // muestraCargandoGeneral();

    var regresadatos= false;
    var cepinstrumental = $("#selectCvePartidaEspecificaInstrumental" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();

    //fnCargaPartidaCvePptoInstrumental(orden, tagref, ue);

    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: cepinstrumental,
        datotagref: tagref,
        datoue: ue
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
            //console.log( "dataJson: " + JSON.stringify(dataJson) );
            var contenidoCvePresupuestal = "";
            var diferenciador = "";

            for (var info in dataJson) {
                contenidoCvePresupuestal += "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                diferenciador = dataJson[info].diferenciador;
                regresadatos= true;
            }

            $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestal" + orden).append(contenidoCvePresupuestal);
            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestal" + orden);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).multiselect('rebuild');
            $("#selectPartidaEspecificaCvePresupuestal" + orden).prop("readonly", true);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).attr('disabled', 'disabled');
            $("#addedUMArticulo" + orden).text('PZA');
            $("#addedCantidadArticulo" + orden).val("");
            $("#addedPEArticulo" + orden).val("");
            $("#addedCantidadTotalArticulo" + orden).val("");
            $("#addPresupuesto" + orden).empty();
            $("#idCvePresupuestal" + orden).removeClass("hide");
            
            // ocultaCargandoGeneral();

            if (!regresadatos) {
                //muestraMensaje("No existen clave presupuestal registrada para la partida "+cpeprod);
                //muestraMensaje("La partida "+ cpeprod +" no cuenta con presupuesto asignado para el ejercicio 2018");
                muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>', 'No existe una clave presupuestal para esta partida');
            }
        }
    }).fail(function(result) {
        // ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });

    return regresadatos;
}

// valida que los datos de los detalles de la requisición no se encuentren vacios
function fnValida(){
    var numero= 0;
    var mensaje= "";
    var respuesta= false;
    var ordenVisual = 0;
    var valoresItem = new Array();
    var item = "";
    var itemRepetido = "";
    var ordenItem = 0;
    $('#ModalGeneral_Mensaje').addClass('maxh400');
    $('#ModalGeneral_Mensaje').addClass('overflowY');
    
    // Recorrer bienes para validaciones
    $("#idMainListContentArticulo div[id*=idCvePresupuestal]").each(function(index, el){
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');
        // console.log("numero: "+numero);
        var obj = new Object();
        obj.num = numero;
        obj.partida = $('#selectCvePartidaEspecifica'+numero).val();
        obj.articulo = $('#selectCveArticulo'+numero).val();
        obj.clavePresu = $('#selectPartidaEspecificaCvePresupuestal'+numero).val();
        valoresItem.push(obj);
    });

    // console.log("valoresItem: "+JSON.stringify(valoresItem));

    $("#idMainListContentArticulo div[id*=idCvePresupuestal]").each(function(index, el){
        var arreglegloItems = 0;
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');
        ordenVisual = $("#num"+numero).text();
        itemActual = $('#selectCveArticulo'+numero).val();
        var itemActualPartida = $('#selectCvePartidaEspecifica'+numero).val();
        var itemActualClavePresupuestal = $('#selectPartidaEspecificaCvePresupuestal'+numero).val();

        for (var key in valoresItem) {
            // console.log("itemActual: "+itemActual);
            // console.log("numero: "+numero+" - "+valoresItem[key].num);
            // console.log("itemActualPartida: "+itemActualPartida+" - "+valoresItem[key].partida);
            // console.log("itemActual: "+itemActual+" - "+valoresItem[key].articulo);
            // console.log("itemActualClavePresupuestal: "+itemActualClavePresupuestal+" - "+valoresItem[key].clavePresu);
            if (
                itemActual != '0'
                && numero != valoresItem[key].num 
                && itemActualPartida == valoresItem[key].partida 
                && itemActual == valoresItem[key].articulo 
                && itemActualClavePresupuestal == valoresItem[key].clavePresu) {
                // console.log("iguales num: "+numero);
                arreglegloItems = 1;
            }
        }

        if(arreglegloItems == 1){
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' ya tiene seleccionado un artículo con la misma clave presupuestal</p>';
        }

        if ($("#selectCvePartidaEspecifica"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada partida especifica.</p>';
        }

        if ($("#selectCveArticulo"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada la clave de artículo.'+"<br>";
        }

        if ($("#selectArticulo"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada la descripción del artículo.'+"<br>";
        }
        
        if ($("#selectPartidaEspecificaCvePresupuestal"+numero).val() == 0 
            || $("#selectPartidaEspecificaCvePresupuestal"+numero).val() == null
            || $("#selectPartidaEspecificaCvePresupuestal"+numero).val() == 'undefined') {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no cuenta con clave presupuestal.'+"<br>";
        }

        if ($("#addedCantidadArticulo"+numero).val() == 0 || $("#addedCantidadArticulo"+numero).val() == "" || $("#addedCantidadArticulo"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene capturada la cantidad.'+"<br>";
        }

        if ($("#addedPEArticulo"+numero).val() == 0 || $("#addedPEArticulo"+numero).val() == "" || $("#addedPEArticulo"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene capturado el precio.'+"<br>";
        }
    });

    // Recorrer servicios para validaciones
    valoresItem = new Array();
    $("#idMainListContentServicio div[id*=idCvePresupuestal]").each(function(index, el){
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');
        // console.log("numero: "+numero);
        var obj = new Object();
        obj.num = numero;
        obj.partida = $('#selectCvePartidaEspecificaServ'+numero).val();
        obj.articulo = $('#selectDescPartidaEspecificaServ'+numero).val();
        obj.clavePresu = $('#selectPartidaEspecificaCvePresupuestalServ'+numero).val();
        valoresItem.push(obj);
    });
    // console.log("valoresItem: "+JSON.stringify(valoresItem));

    $("#idMainListContentServicio div[id*=idCvePresupuestal]").each(function(index, el){
        var arreglegloItems = 0;
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');
        ordenVisual = $("#num"+numero).text();
        // console.log(numero);
        // console.log(ordenVisual);

        for (var key in valoresItem) {
            // console.log("numero: "+numero+" - "+valoresItem[key].num);
            // console.log("Partida: "+$("#selectCvePartidaEspecificaServ"+numero).val()+" - "+valoresItem[key].partida);
            // console.log("Articulo: "+$("#selectDescPartidaEspecificaServ"+numero).val()+" - "+valoresItem[key].articulo);
            // console.log("Clave: "+$("#selectPartidaEspecificaCvePresupuestalServ"+numero).val()+" - "+valoresItem[key].clavePresu);
            if (
                $("#selectCvePartidaEspecificaServ"+numero).val() != '0'
                && numero != valoresItem[key].num 
                && $("#selectCvePartidaEspecificaServ"+numero).val() == valoresItem[key].partida 
                && $("#selectDescPartidaEspecificaServ"+numero).val() == valoresItem[key].articulo 
                && $("#selectPartidaEspecificaCvePresupuestalServ"+numero).val() == valoresItem[key].clavePresu) {
                // console.log("iguales num: "+numero);
                arreglegloItems = 1;
            }
        }

        if(arreglegloItems == 1){
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' ya tiene seleccionado un servicio con la misma clave presupuestal</p>';
        }

        if ($("#selectCvePartidaEspecificaServ"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada una partida especifica.'+"<br>";            
        }

        if ($("#selectDescPartidaEspecificaServ"+numero).val() == 0 
            || $("#selectDescPartidaEspecificaServ"+numero).val() == null
            || $("#selectDescPartidaEspecificaServ"+numero).val() == 'undefined') {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada la descripción de la partida específica.'+"<br>";
        }

        if ($("#descServicio"+numero).val() == 0 || $("#descServicio"+numero).val() == "" || $("#descServicio"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene capturada una descripción larga del servicio.'+"<br>";
        }

        if ($("#selectPartidaEspecificaCvePresupuestalServ"+numero).val() == 0 
            || $("#selectPartidaEspecificaCvePresupuestalServ"+numero).val() == null
            || $("#selectPartidaEspecificaCvePresupuestalServ"+numero).val() == 'undefined') {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no cuenta con clave presupuestal.'+"<br>";
        }

        if ($("#addedPEServicio"+numero).val() == 0 || $("#addedPEServicio"+numero).val() == "" || $("#addedPEServicio"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene capturado el precio.'+"<br>";
        }
    });

    $("#idMainListContentInstrumental div[id*=idCvePresupuestal]").each(function(index, el){
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');

        /*if ($("#selectCvePartidaEspecifica"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene seleccionada partida especifica.</p>';
        }*/

        if ($("#selectCveInstrumental"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada la clave del instrumental.'+"<br>";
        }

        if ($("#selectInstrumental"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene seleccionada la descripción del instrumental.'+"<br>";
        }

        /*if ($("#addedCantidadArticulo"+numero).val() == 0 || $("#addedCantidadArticulo"+numero).val() == "" || $("#addedCantidadArticulo"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene capturada la cantidad.'+"<br>";
        }*/

        if ($("#addedPEInstrumental"+numero).val() == 0 || $("#addedPEInstrumental"+numero).val() == "" || $("#addedPEInstrumental"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ ordenVisual +' no tiene capturado el precio.'+"<br>";
        }
    });
    // alert("nuAnexoTecnico: "+nuAnexoTecnico);
    if( $('#anexoTecnicoCheck').is(':checked') && nuAnexoTecnico > 0) {
        var noRequisicionGeneral = noRequisicionGeneral || $("#idtxtRequisicion").val();
        // envia la cnatida dde coincidencias que se encuentran bajo el creterio de que pertenecen al anexo
        // y no cuentan don un orden de la requisicion es decir que no an sido asignados. Se espera 
        // 0 = "Todos los datos se eucuentan asignados"
        // 1 = "Se encuentra uno o mas renglones del anexo sin asignar"
        respuesta = fnValidarAnexoTecnico(noRequisicionGeneral, nuAnexoTecnico);
        // si se se encotraron valores no se procede con la información
        if (respuesta == 1) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Existen renglones sin asignar en el Anexo Técnico</p>';
        }
    }

    if (mensaje != '') {
        respuesta = true;
    }

    if (respuesta == 0) {
        respuesta = false;
    }
    
    // en caso de que no se ecuentre asignado o no cuente con un anexo y el cehck esta en true
    // se manda el mensaje de error
    if(respuesta == 1 && mensaje != '') {
        muestraModalGeneral(3,'Error de Datos', mensaje);
    }

    return respuesta;
}

function fnValidarAnexoTecnico(idReq, idAnexo) {
    // Función para validar que todos los renglones del anexo se encuentren seleccionados
    var respuesta = 0;
    // noRequisicionGeneral
    
    dataObj= {
        option: 'validarSeleccionAnexo',
        idReq: idReq,
        idAnexo: idAnexo
    };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType: "json",
          url: url,
          data: dataObj
      })
    .done(function( data ) {
        respuesta = data.contenido;
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });

    return respuesta;
}

// inicia el proceso del guardado de la requisicion, dependiendo si es nueva o ya existente
function fnGuardarRequisicion(){
    var vacio = "";
    var noReq = $("#idtxtNoRequisicion").val();
    var idReq = $("#idtxtRequisicion").val();
    var statusR = fnObtenerStatusReq(idReq);

    if(statusR == 'Autorizado' || statusR == 'Cancelado' ){
        muestraMensaje('No se puede modificar una requisición en estatus ' + statusR, 3, 'divMensajeOperacion');
        $('#btnCerrarModalGeneral').addClass('cerrarModalErrorOriginal');
    }else{
        var comments = $("#txtAreaObs").val();
        comments = '';
        var fechaFrom = $("#idFechaElaboracion").val();
        var fechaTo = $("#idFechaEntrega").val();
        var tagref = $("#selectUnidadNegocio").val();
        var ue = $("#selectUnidadEjecutora").val();
        var anexoArt = [];
        var anexoServ = [];

        if( $('#anexoTecnicoCheck').is(':checked')  ) {
            viewAnexo = 1;
        }else{
            viewAnexo = 0;
        }
        // validar datos de captura en pantalla
        if (!fnValida()) {
            if(idReq == ''){
                // ocultaCargandoGeneral();
                muestraMensaje('No existe una Requisición para guardar', 3, 'divMensajeOperacion', 5000);
            }else{
                if(viewAnexo == 0){
                    fnRemoveAnexoTecnico(idReq);
                }
                $("#idMainListContentArticulo select[id*=selectCvePartidaEspecifica]").each(function(index, el) {
                    numero= $(this).attr('id');
                    numero= numero.replace('selectCvePartidaEspecifica','');
                    ordenVisual = $("#num"+numero).text();

                    fnModificarArticulo(idReq, numero);
                });
                $("#idMainListContentServicio select[id*=selectCvePartidaEspecificaServ]").each(function(index, el) {
                    numero= $(this).attr('id');
                    numero= numero.replace('selectCvePartidaEspecificaServ','');
                    ordenVisual = $("#num"+numero).text();

                    fnModificarServicio(idReq, numero);
                });
                /*$("#idMainListContentInstrumental select[id*=selectCvePartidaEspecificaInstrumental]").each(function(index, el) {
                    numero= $(this).attr('id');
                    numero= numero.replace('selectCvePartidaEspecificaInstrumental','');

                    fnModificarInstrumental(idReq, numero);
                });*/
                
                if(noReq > 0){
                    
                    fnGuardarRequisicionExistente(idReq, noReq, comments, fechaFrom, fechaTo, tagref, ue, viewAnexo);
                }else{

                    fnGuardarRequisicionNueva(idReq, comments, fechaFrom, fechaTo, tagref, ue, viewAnexo);
                    console.log(idReq);
                }
            }
        }
    }
    //ocultaCargandoGeneral();
}

// guarda una requisición nueva y asigna el requisitionno
function fnGuardarRequisicionNueva(idReq, comments, fechaFrom, fechaTo, tagref, ue, anexoTec){
    //fnValida();
    console.log(idReq);
    //muestraCargandoGeneral();
    //setTimeout(function (){
    dataObj = {
            option: 'guardarRequisicionNueva',
            idReq: idReq,
            status: 'Capturado',
            comments: $("#txtAreaObs").val(),
            fechaFrom: fechaFrom,
            fechaTo: fechaTo,
            tagref: tagref,
            ue:ue,
            anexoTec: anexoTec
        };
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj,
            
            /*beforeSend: function(){
                muestraCargandoGeneral();
            }*/
        }).done(function(data) {
            if (data.result) {

                noReq= data.contenido;
                
                fnObtenerStatusReq(idReq);

                $("#idtxtRequisicionView").html(noReq);
                $("#idtxtNoRequisicion").val(""+noReq);
                $("#idtxtRequisicionView").removeClass("hide");
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                $("#idStatusReq").removeClass("hide");
                $("#idStatusReq").prop("readonly", true);
                $("#idStatusReq").attr('disabled', 'disabled');
                // Cambios pruebas, se comenta opcion de recarga de datos
                fnLoadRequisicion(idReq);
                var genUrlEnc = fnGenerarUrlEnc(idReq, noReq);
                $('#urlEncriptadaRequisicionInput').val(""+genUrlEnc);
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                //var mensaje = '<p> ¿Quiere hacer la generación de la solicitud “No Existencia” o “Solicitud Almacén”? </p>';
                var mensaje = '<p> ¿Desea generar: '+
                    '<ul>'+
                        // '<li>No Existencia</li>'+
                        // '<li>Solicitud al almacén</li>'+
                        '<li>La suficiencia presupuestal</li>'+
                    '</ul>'+
                '</p>';
                // Cambios pruebas, se agrega que se visualice la informacion y si es solo servicios solo muestre la suficiencia
                // $("#noExistencia").css({ 'display': "block" });
                // $("#solicitudAlmacen").css({ 'display': "block" });

                $("#noExistencia").removeClass("active");
                $("#suficienciaPresupuestal").addClass("active");
                $("#solicitudAlmacen").removeClass("active");

                $("#idNoExistenciaModalTab").removeClass("active");
                $("#idSufPresupuestalModalTab").addClass("active");
                $("#idSolAlmacenModalTab").removeClass("active");

                // Cambios pruebas, validar si tiene articulos o servicios para mostrar mensaje de proceso
                var tieneArticulos = 0;
                var tieneServicios = 0;
                $("#idMainListContentArticulo div[id*=idCvePresupuestal]").each(function(index, el){
                    tieneArticulos = 1;
                });
                $("#idMainListContentServicio div[id*=idCvePresupuestal]").each(function(index, el){
                    tieneServicios = 1;
                });

                if (tieneArticulos == 0) {
                    mensaje = '<p> ¿Desea generar: '+
                        '<ul>'+
                            '<li>La suficiencia presupuestal</li>'+
                        '</ul>'+
                    '</p>';
                    $("#noExistencia").css({ 'display': "none" });
                    $("#solicitudAlmacen").css({ 'display': "none" });
                    var divSificiencia = document.getElementById("suficienciaPresupuestal");
                    divSificiencia.click();
                }
                ocultaCargandoGeneral();

                if (tieneArticulos == 1 || tieneServicios == 1) {
                    // Cambios pruebas, validar para mostrar mensaje
                    muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPanelValidarRequisicion('+idReq+')'); 
                }
            }else{
                 ocultaCargandoGeneral();
                //muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
                muestraModalGeneralConfirmacion(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i>Error</p></h3>',
                data.contenido+". ¿Desea guardar de nuevo la requisición?","","fnGuardarRequisicionNueva","idReq, comments, fechaFrom, fechaTo, tagref, ue, anexoTec");
            }

        }).fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
        });
       //         }, 2000);
}

// modifica una requisición existente y guarda los cambios generados en la misma
function fnGuardarRequisicionExistente(idReq, noReq, comments, fechaFrom, fechaTo, tagref, ue, anexoTec){
    //muestraCargandoGeneral();
    //setTimeout(function (){
    var status = $("#idStatusReq").val();
    var genUrlEnc = fnGenerarUrlEnc(idReq, noReq);
    $('#urlEncriptadaRequisicionInput').val(""+genUrlEnc);

    dataObj = {
        option: 'guardarRequisicion',
        idReq: idReq,
        noReq: noReq,
        status: status,
        comments: $("#txtAreaObs").val(),
        codigoExpediente: $("#codigoExpediente").val(),
        selectCFDI: $("#selectCFDI").val(),
        fechaFrom: fechaFrom,
        fechaTo: fechaTo,
        tagref: tagref,
        ue:ue,
        anexoTec: anexoTec
    };

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj,
        /*beforeSend: function(){
            muestraCargandoGeneral();
        }*/
        
    }).done(function(data) {
        if (data.result) {
            //ocultaCargandoGeneral();
            //noReq= data.contenido;
            //muestraMensaje('Se guardó la Requisición ', 1, 'divMensajeOperacion', 5000); 
            $("#idtxtRequisicionView").removeClass("hide");
            $("#idtxtRequisicionView").prop("readonly", true);
            $("#idtxtRequisicionView").attr('disabled', 'disabled');
            // Cambios pruebas, se comenta opcion de recarga de datos
            fnLoadRequisicion(idReq);
            //// ocultaCargandoGeneral();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            //var mensaje = '<p> ¿Quiere hacer la generación de la solicitud “No Existencia” o “Solicitud Almacén”? </p>';
            var mensaje = '<p> ¿Desea generar: '+
                    '<ul>'+
                        // '<li>No Existencia</li>'+
                        // '<li>Solicitud al almacén</li>'+
                        '<li>La suficiencia presupuestal</li>'+
                    '</ul>'+
                '</p>';
            // Cambios pruebas, se agrega que se visualice la informacion y si es solo servicios solo muestre la suficiencia
            // $("#noExistencia").css({ 'display': "block" });
            // $("#solicitudAlmacen").css({ 'display': "block" });

            $("#noExistencia").removeClass("active");
            $("#suficienciaPresupuestal").addClass("active");
            $("#solicitudAlmacen").removeClass("active");

            $("#idNoExistenciaModalTab").removeClass("active");
            $("#idSufPresupuestalModalTab").addClass("active");
            $("#idSolAlmacenModalTab").removeClass("active");

            // Cambios pruebas, validar si tiene articulos o servicios para mostrar mensaje de proceso
            var tieneArticulos = 0;
            var tieneServicios = 0;
            $("#idMainListContentArticulo div[id*=idCvePresupuestal]").each(function(index, el){
                tieneArticulos = 1;
            });
            $("#idMainListContentServicio div[id*=idCvePresupuestal]").each(function(index, el){
                tieneServicios = 1;
            });

            if (tieneArticulos == 0) {
                mensaje = '<p> ¿Desea generar: '+
                    '<ul>'+
                        '<li>La suficiencia presupuestal</li>'+
                    '</ul>'+
                '</p>';
                $("#noExistencia").css({ 'display': "none" });
                $("#solicitudAlmacen").css({ 'display': "none" });
                var divSificiencia = document.getElementById("suficienciaPresupuestal");
                divSificiencia.click();
            }
            
            ocultaCargandoGeneral();

            if (tieneArticulos == 1 || tieneServicios == 1) {
                // Cambios pruebas, validar para mostrar mensaje
                muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPanelValidarRequisicion('+idReq+')'); 
            }
        }else{
            ocultaCargandoGeneral();
            if(data.contenido){
                muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
                $('#btnCerrarModalGeneral').addClass('cerrarModalErrorOriginal');
            }else{
                //ocultaCargandoGeneral();
                //muestraMensaje('No se guardó la Requisición', 3, 'divMensajeOperacion', 5000);
                muestraModalGeneralConfirmacion(3,'<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Error</p></h3>', 
                    'No se guardo la requisición por el siguiente motivo: '+data.contenido+".¿Desea volver a guardar?","fnGuardarRequisicionExistente",
                    "idReq, noReq, comments, fechaFrom, fechaTo, tagref, ue, anexoTec");
            }
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });
      //      }, 2000);
}

function fnGuardarAnexoTecnico(idRequisicion, statusAnexo){
    // se comenta el siguiente códiogo debino al no uso del mismo 
    // quedando como depreciada
    var dependencia = $('#selectRazonSocial').val()
        , tagref = $('#selectUnidadNegocio').val()
        , ue = $('#selectUnidadEjecutora').val();
    console.log("Identificador de requisicion %s", idRequisicion);
    console.log("Numero de Anexo %s", nuAnexoTecnico);
    console.log("dependencia %s", dependencia);
    console.log("tagref %s", tagref);
    console.log("ue %s", ue);
    /************************************************/
        dataObj= {
            ue             : ue,
            tagref         : tagref,
            option         : 'retiraAnexo',
            dependencia    : dependencia,
            idRequisicion  : idRequisicion,
            nuAnexoTecnico : nuAnexoTecnico,
        };
        $.ajax({
            async:false,
            cache:false,
              method: "POST",
              dataType: "json",
              url: url,
              data: dataObj
          })
        .done(function( data ) {
            if(data.result) {
                nuAnexoTecnico = 0;
                existeFolioAnexo = false;
                // console.log(data);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log( result );
        });
    /************************************************/
}

function fnReplicaRequisicion(idrequi, norequi, tagref, ue, comments, fDelivery){
    var datos = [];
    var datosRequisicion = [];
    var newReq = [];
    var contador = 0;
    
    $("#tableNoExistenciaModalTab tr").each(function (index) {
            //console.log($(this).text());
            contador++;
            var orden, cvepre, item, desc, qty, precio, mbflag;
            $(this).children("td").each(function (index2) {
                switch (index2) {
                    case 0:
                    orden = $(this).text();
                    break;
                    case 1:
                    cvepre = $(this).text();
                    break;
                    case 2:
                    item = $(this).text();
                    break;
                    case 3:
                    desc = $(this).text();
                    break;
                    case 4:
                    qty = $(this).text();
                    break;
                    case 5:
                    precio = $(this).text();
                    break;
                    case 6:
                    mbflag = $(this).text();
                    break;
                }
                datos[contador]= [{'orden': orden,'cvepre': cvepre, 'item': item,"desc": desc, "qty": qty, "precio": precio, "mbflag": mbflag }] ;
                
            });
            if(contador > 1 && mbflag != 'D'){
                datosRequisicion.push(datos[contador]);   
            }
        });
    dataObj = {
        option: 'replicarRequisicion',
        idrequi: idrequi,
        norequi: norequi,
        tagref: tagref,
        ue: ue,
        comments: comments + "... Requisición generada a partir de la requisición " + norequi,
        fDelivery: fDelivery,
        datosRequisicion : datosRequisicion
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
            newIdReq = data.contenido.datoIdReq;
            newNoReq = data.contenido.datoNoReq;
            newReq[0] =  [{'newIdReq': newIdReq,'newNoReq': newNoReq}] ;
            console.log(newReq[0]);
        }else{
            console.log("Error en la replica");
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
    return newReq[0];
}

function fnValidarNoExistencia(idr){
    var idNoE = "";
    dataObj = {
        option: 'validarNoExistencia',
        idrequi: idr
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
            idNoE = data.contenido.datos;
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
    return idNoE;
}

function fnValidaSolAlmacen(idr){
    var idSolA = "";
    dataObj = {
        option: 'validarSolAlmacen',
        idrequi: idr
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
            idSolA = data.contenido.datos;
        }else{
            idSolA = 0;
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
    return idSolA;
}

// Funcion para generar la nueva No Existencia de acuerdo a las validaciones en guardar
function fnGeneraNoExistencia(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery){
    var folioNoE = fnValidarNoExistencia(idrequi);

    if(folioNoE > 0){
        var linkActNoE = fnActualizaNoExistencia(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery, folioNoE);
        $("#idNoExistenciaModalTab").append('<div><div class="fl mt30">'+linkActNoE+'</div></div>');
    }else{
        var noExistvalores = [];
        var orden = 0;
        var newNoExistencia = 0;
        var itemcode = "";
        var itemdesc = "";
        var precio = "";
        var cantsol = "";
        var cantFalt = "";
        var contador=1;
        var datos=[];
        var mensaje = "";

        $("#tableNoExistenciaModalTab tr").each(function (index) {
            //console.log($(this).text());
            contador++;
            var orden, cvepre, item, desc, qty, precio, mbflag, iditem;

            $(this).children("td").each(function (index2) {
                switch (index2) {
                    case 0:
                        orden = $(this).text();
                        break;
                    case 1:
                        cvepre = $(this).text();
                        break;
                    case 2:
                        item = $(this).text();
                        break;
                    case 3:
                        desc = $(this).text();
                        break;
                    case 4:
                        qty = $(this).text();
                        break;
                    case 5:
                        precio = $(this).text();
                        break;
                    case 6:
                        mbflag = $(this).text();
                        break;
                    case 7:
                        iditem = $(this).text();
                        break;
                }

                datos[contador]= [{'orden': orden,'cvepre': cvepre, 'item': item,"desc": desc, "qty": qty, "precio": precio, "mbflag": mbflag , "iditem": iditem}] ;
            });

            if(contador > 1 && mbflag != 'D'){
                noExistvalores.push(datos[contador]);
            }
        });
        
        if(noExistvalores){
            dataObj = {
                option: 'generarNoExistencia',
                idrequi: idrequi,
                norequi: norequi,
                dependencia: dependencia,
                tagref: tagref,
                ue: ue,
                comments: comments + " Solicitud de no existencia generada a partir de la requisición " + norequi,
                fDelivery: fDelivery,
                noExistvalores : noExistvalores
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
                    newNoExistencia = data.contenido.datos;
                    idFolioNoe = newNoExistencia;
                }else{
                    $("#idNoExistenciaModalTab").append('<div><div class="fl mt30"><b>No hay elementos para generar una no existencia</b></div></div>');
                }
            }).fail(function(result) {
                console.log("ERROR");
                console.log(result);
            });
            //location.replace("./noExistencia.php?");
        }else{
            $("#idNoExistenciaModalTab").append('<div><div class="fl mt30"><b>No hay elementos para generar una no existencia</b></div></div>');   
        }
    }
    return newNoExistencia;
}

function fnActualizaNoExistencia(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery, folioNoE){
    var contadorNE=0;
    var arrayNoE = [];
    var noExistvalores = [];
    var linkNoExistencia = 0;

    $("#tableNoExistenciaModalTab tr").each(function (index) {
        contadorNE++;
        var ordenNE, cvepreNE, itemNE, descNE, qtyNE, precioNE, mbflag, iditem;

        $(this).children("td").each(function (index2) {
            switch (index2) {
                case 0:
                    ordenNE = $(this).text();
                    break;
                case 1:
                    cvepreNE = $(this).text();
                    break;
                case 2:
                    itemNE = $(this).text();
                    break;
                case 3:
                    descNE = $(this).text();
                    break;
                case 4:
                    qtyNE = $(this).text();
                    break;
                case 5:
                    precioNE = $(this).text();
                    break;
                case 6:
                    mbflag = $(this).text();
                    break;
                case 7:
                    iditem = $(this).text();
                    break;
            }

            arrayNoE[contadorNE]= [{'ordenNE': ordenNE,'cvepreNE': cvepreNE, 'itemNE': itemNE,"descNE": descNE, "qtyNE": qtyNE, "precioNE": precioNE, "iditem": iditem }] ;
            
        });

        if(contadorNE > 1){
            noExistvalores.push(arrayNoE[contadorNE]);  
        }
    });

    console.log("****************");
    console.log("arrayNoE: "+JSON.stringify(arrayNoE));
    console.log("****************");

    if(noExistvalores.length > 0 || noExistvalores != '' || noExistvalores !== 'undefined' || noExistvalores != null){
        dataObj = {
            option: 'actualizarNoExistencia',
            idrequi: idrequi,
            norequi: norequi,
            folioNoE: folioNoE,
            noExistvalores: noExistvalores
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
                linkNoExistencia = data.contenido;
            }else{
                linkNoExistencia = '<b>No hay elementos para generar una no existencia</b>';
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
    }else{
        linkNoExistencia = '<b>No hay elementos para generar una no existencia</b>';
    }
    
    return linkNoExistencia;
}

function fnGeneraSolAlmacen(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery, almacen){
    var folioSolA = fnValidaSolAlmacen(idrequi);
    var idFolioSolAlmacen = 0;
    var solAlmacen = [];
    var orden = 0;
    var itemcode = "";
    var itemdesc = "";
    var precio = "";
    var cantsol = "";
    var cantFalt = "";
    var contador=1;
    var datos=[];
    var mensaje = "";

    if(folioSolA > 0 || folioSolA != null ){
        var linkActSolA = fnActualizaSolAlmacen(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery, folioSolA, almacen);
        $("#idSolAlmacenModalTab").append('<div><div class="fl mt30">'+linkActSolA+'</div></div>');
    }else{
        $("#tableSolAlmacenModalTab tr").each(function (index) {
            contador++;
            var orden, cvepre, item, desc, qty, precio, itemOntrst;
            $(this).children("td").each(function (index2) {
                switch (index2) {
                    case 0:
                    orden = $(this).text();
                    break;
                    case 1:
                    cvepre = $(this).text();
                    break;
                    case 2:
                    item = $(this).text();
                    break;
                    case 3:
                    desc = $(this).text();
                    break;
                    case 4:
                    qty = $(this).text();
                    break;
                    case 5:
                    precio = $(this).text();
                    break;
                    case 7:
                    itemOntrst = $(this).text();
                    break;
                }
                datos[contador]= [{'orden': orden,'cvepre': cvepre, 'item': item,"desc": desc, "qty": qty, "precio": precio, "itemOntrst": itemOntrst}] ;
            });
            if(contador > 1){
                solAlmacen.push(datos[contador]);   
            }
            console.log(solAlmacen);
        });

        if(solAlmacen.length > 0 || solAlmacen != 0 || solAlmacen != '' || solAlmacen != null || solAlmacen !== 'undefined' ){
            dataObj = {
                option: 'generarSolAlmacen',
                idrequi: idrequi,
                norequi: norequi,
                dependencia: dependencia,
                tagref: tagref,
                ue: ue,
                fDelivery: fDelivery,
                comments: comments,
                solAlmacen: solAlmacen,
                almacen: almacen
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
                    idFolioSolAlmacen = data.contenido.datos;
                    idFolioNoe = idFolioSolAlmacen;
                }else{
                    // Cambios pruebas, agrego mensaje del modelo
                    $("#idSolAlmacenModalTab").append(''+data.Mensaje);
                }
            }).fail(function(result) {
                console.log("ERROR");
                console.log(result);
            });

        }else{
            $("#idSolAlmacenModalTab").append('<div><div class="fl mt30"><b>No hay elementos para generar una solicitud al almacén</b></div></div>');
        }
        
    }
}

function fnActualizaSolAlmacen(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery, folioSolA, almacen){
    var linkSolAlmacen = "";
    var solAlmacen = [];   
    var orden = 0;
    var itemcode = "";
    var itemdesc = "";
    var precio = "";
    var cantsol = "";
    var cantFalt = "";
    var contador=1;
    var datos=[];
    var mensaje = "";

    $("#tableSolAlmacenModalTab tr").each(function (index) {
        contador++;
        var orden, cvepre, item, desc, qty, precio, itemOntrst;

        $(this).children("td").each(function (index2) {
            switch (index2) {
                case 0:
                    orden = $(this).text();
                    break;
                case 1:
                    cvepre = $(this).text();
                    break;
                case 2:
                    item = $(this).text();
                    break;
                case 3:
                    desc = $(this).text();
                    break;
                case 4:
                    qty = $(this).text();
                    break;
                case 5:
                    precio = $(this).text();
                    break;
                case 7:
                    itemOntrst = $(this).text();
                    break;
            }

            datos[contador]= [{'orden': orden,'cvepre': cvepre, 'item': item,"desc": desc, "qty": qty, "precio": precio, "itemOntrst": itemOntrst}] ;
        });

        if(contador > 1){
            solAlmacen.push(datos[contador]);   
        }
        console.log(solAlmacen);
    });

    if(solAlmacen.length > 0 || solAlmacen != 0 || solAlmacen != '' || solAlmacen != null || solAlmacen !== 'undefined' ){
        dataObj = {
            option: 'actualizarSolAlmacen',
            idrequi: idrequi,
            norequi: norequi,
            folioSolA: folioSolA,
            dependencia: dependencia,
            tagref: tagref,
            ue: ue,
            fDelivery: fDelivery,
            comments: comments,
            solAlmacen: solAlmacen,
            almacen: almacen
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
                linkSolAlmacen = data.contenido;
            }else{
                linkSolAlmacen = "<b>No hay elementos para generar una solicitud al almacén</b>";
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
    }else{
        linkSolAlmacen = "<b>No hay elementos para generar una solicitud al almacén</b>";
    }
    return linkSolAlmacen;
}

// funcion que valida la existencia para separar No Existencia y solicitud al almacen
function fnPanelValidarRequisicion(idr){
    // Cambios pruebas, deshabilita boton
    $('#ModalGeneral').modal('hide');

    $("#modal-obs").css('display','block');
    var dependencia = $('#selectRazonSocial').val();
    var tagref = $('#selectUnidadNegocio').val();
    var uejecutora = $('#selectUnidadEjecutora').val();
    var folioNoE = fnValidarNoExistencia(idr);
    var folioSolA = fnValidaSolAlmacen(idr);
    var cvepre_presupuesto= new Array();
    var pptodata = new Array();

    var tableNE = $(document.createElement('table')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });

    var tableSP = $(document.createElement('table')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });

    var tableSA = $(document.createElement('table')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });

    // visual
    $(tableNE).empty();
    $(tableNE).addClass('tableHeaderVerde');
    $(tableNE).attr('id','tableNoExistenciaModalTab');
    $(tableNE).attr('border','1');
    $(tableNE).attr('bordercolor','#DDDDDD');
    $(tableNE).append('<tr>'+
              '<th class="text-center w15p">Núm. de Renglón</th>'+
              '<th class="w70p">Descripción del Artículo</th>'+
              '<th class="text-center w15p">Cantidad</th>'+
            '</tr>');
    $(tableSP).empty();
    $(tableSP).addClass('tableHeaderVerde');
    $(tableSP).attr('id','tableSufPresupuestalModalTab');
    $(tableSP).attr('border','1');
    $(tableSP).attr('bordercolor','#DDDDDD');
    $(tableSP).append('<tr>'+
              '<th class="text-center w10p">Núm. de Renglón</th>'+
              '<th class="w45p">Descripción del Artículo</th>'+
              '<th class="text-center w45p">Estatus</th>'+
            '</tr>');
    $(tableSA).empty();
    $(tableSA).addClass('tableHeaderVerde');
    $(tableSA).attr('id','tableSolAlmacenModalTab');
    $(tableSA).attr('border','1');
    $(tableSA).attr('bordercolor','#DDDDDD');
    $(tableSA).append('<tr>'+
              '<th class="text-center w15p">Núm. de Renglón</th>'+
              '<th class="w65p">Descripción del Artículo</th>'+
              '<th class="text-center w10p">Cantidad</th>'+
            '</tr>');
    dataObj = {
        option: 'validarRequisicionPanel',
        idReq: idr,
        dependencia: dependencia,
        ur: tagref,
        ue: uejecutora,
        idrequisicion: $("#idtxtNoRequisicion").val()
    };   

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if(data.contenido){
            dataReq = data.contenido.datos;
            var idrequi = dataReq[0].idrequi;
            var norequi = dataReq[0].norequi;
            var dependencia = dataReq[0].dependencia;
            var tagref = dataReq[0].tagref;
            var ue = dataReq[0].ue;
            var comments = dataReq[0].comments;
            comments = '';
            var fDelivery = dataReq[0].fdelivery;
            var clavepre = "";
            var mbflag = "";
            var idDetalleSolAlmacen = 0;
            var idItemDetalle = 0;
            var item = "";
            var descitem = "";
            var longdesc = "";
            var orden = 0;
            var precio = 0;
            var qtyReq = 0;
            var qtySolA = 0;
            var tot = 0;
            var mbflag = "";
            var qty = 0;
            var qtyNoE = 0;
            var qtyStock = 0;
            var qtyStockDisp = 0;
            var qtyontransit = 0;
            var pptoActual = 0;
            var pptoDisp = 0;
            var totQty = 0;
            var totQtyExist = 0;
            var totQtyFaltante = 0;
            var totPptoSol = 0;
            var totPptoActual = 0;
            var totPptoFaltante = 0;
            var totPptoDisponible = 0;
            var mensajeValidacion ="";
            var qtyDiferencia= 0;
            var totalcantidadsolicitud= 0;
            var $qtyDisponible = 0;

            for (var info in dataReq) {
                clavepre = dataReq[info].clavepre;
                mbflag = dataReq[info].mbflag;
                idDetalleSolAlmacen = dataReq[info].idDetalleSolAlmacen;
                idItemDetalle = dataReq[info].idItemDetalle;
                item = dataReq[info].itemcode;
                descitem = dataReq[info].itemdescription;
                longdesc = dataReq[info].longdesc;
                orden = dataReq[info].orderlineno_;
                renglon = dataReq[info].renglon;
                precio = dataReq[info].precio;
                qtyReq = dataReq[info].qtyReq;
                qtySolA = dataReq[info].qtySolA;
                qtyDiferencia= dataReq[info].qtyDiferencia;
                qtyDisponible= dataReq[info].qtyDisponible;
                tot = dataReq[info].tot;
                pptoActual = dataReq[info].pptoActual;
                qtyStock = dataReq[info].qtyStock;
                qtyStockDisp = dataReq[info].qtyStockDisp;
                qtyontransit = dataReq[info].qtyontransit;
                pptoDisp = pptoActual - tot;
                mensajeValidacion = dataReq[info].mensajeValidacion;
                almacen = dataReq[info].almacen;
                totalcantidadsolicitud= parseInt(qtySolA) + parseInt(qtyDiferencia);
                qtyNoE = 0;

                console.log('%%%%%');
                console.log("qtyReq: %s", qtyReq);
                console.log("qtySolA: %s", qtySolA);
                console.log("qtyNoE: %s", qtyNoE);
                console.log("qtyDiferencia: %s", qtyDiferencia);
                console.log("qtyStockDisp: %s", qtyStockDisp);
                console.log("qtyDisponible: %s", qtyDisponible);
                console.log("qtyStock: %s", qtyStock);
                console.log("qtyontransit: %s", qtyontransit);
                console.log('%%%%%');

                if(typeof pptoActual === 'undefined' || pptoActual == '' || pptoActual == null ){
                    pptoActual = 0;
                }
                $(tableSP).append('<tr class="rowTableSP"><td class="text-center">'+orden+'</td><td class="hide">'+ clavepre +'</td><td class="hide">'+ item +'</td><td>'+descitem+'</td><td class="hide" id="txtfila'+orden+'"></td><td class="hide" id="fila'+orden+'"></td><td class="hide">'+tot+'</td><td class="hide">'+precio+'</td><td>'+mensajeValidacion+'</td><td class="hide">'+idItemDetalle+'</td></tr>');
                
                if(mbflag == 'D'){
                    qtyStockDisp = 1;
                }else{
                    /*if(qtyDisponible > 0){
                        qty = qtyDiferencia;
                    }*/
                    if(qtyDiferencia < 0){
                        qtyNoE = (-1)*qtyDiferencia;
                        $(tableNE).append('<tr class="rowTableNE"><td class="text-center">'+orden+'</td><td class="hide">'+ clavepre +'</td><td class="hide">'+ item +'</td><td>'+descitem+'</td><td class="text-center">'+qtyNoE+'</td><td class="hide">'+precio+'</td><td class="hide">'+mbflag+'</td><td class="hide">'+idItemDetalle+'</td></tr>');
                        //qty = qtyStock;
                    }
                    // Cambios pruebas
                    //qty = qtySolA;
                    if(qtySolA > 0){
                        $(tableSA).append('<tr class="rowTableSA"><td class="text-center">'+orden+'</td><td class="hide">'+ clavepre +'</td><td class="hide">'+ item +'</td><td>'+descitem+'</td><td class="text-center">'+qtySolA+'</td><td class="hide">'+precio+'</td><td class="hide">'+qtyStockDisp+'</td><td class="hide">'+idItemDetalle+'</td></tr>');
                    }
                }
            }

            $("#idSufPresupuestalModalTab").append(tableSP);
            $("#idNoExistenciaModalTab").append(tableNE);

            if(folioNoE == 0 || folioNoE == '' || folioNoE === 'undefined' || folioNoE == null){
                var rowTableNE = $(".rowTableNE").text();
                if(rowTableNE.trim() != '' && rowTableNE.trim() != null && rowTableNE.trim() !== 'undefined' ){
                    $("#idNoExistenciaModalTab").append('<div><button id="btnGeneraNoExistencia" class="mt30 botonVerde fl" onclick="fnGeneraNoExistencia('+idr+','+norequi+',\''+dependencia+'\',\''+tagref+'\',\''+ue+'\',\''+comments+'\',\''+fDelivery+'\')">Generar</button></div>');
                }else{
                    $('#btnGeneraNoExistencia').remove();
                    $("#idNoExistenciaModalTab").append('<div><div class="fl mt30"><b>No hay elementos para generar una no existencia</b></div></div>');
                }
            }else{
                var linkNoExistencia = fnActualizaNoExistencia(idr, norequi, dependencia, tagref, ue, comments, fDelivery, folioNoE);
                $("#idNoExistenciaModalTab").append('<div><div class="fl mt30">'+linkNoExistencia+'</div></div>');
            }
            
            $("#idSolAlmacenModalTab").append(tableSA);
            if(folioSolA == 0 || folioSolA == '' || folioSolA === 'undefined' || folioSolA == null){
                var rowTableSA = $(".rowTableSA").text();
                if(rowTableSA != '' && rowTableSA != null && rowTableSA !== 'undefined' ){
                    $("#idSolAlmacenModalTab").append('<div><button id="btnGeneraSolAlmacen" class="mt30 botonVerde fl" onclick="fnGeneraSolAlmacen('+idrequi+','+norequi+',\''+dependencia+'\',\''+tagref+'\',\''+ue+'\',\''+comments+'\',\''+fDelivery+'\','+almacen+');">Generar</button></div>');                    
                }else{
                    $('#btnGeneraSolAlmacen').remove();
                    $("#idSolAlmacenModalTab").append('<div><div class="fl mt30"><b>No hay elementos para generar una solicitud al almacén</b></div></div>');
                }                    
            }else{
                var linkSolAlmacen = fnActualizaSolAlmacen(idrequi, norequi, dependencia, tagref, ue, comments, fDelivery, folioSolA, almacen);
                $("#idSolAlmacenModalTab").append('<div><div class="fl mt30">'+linkSolAlmacen+'</div></div>');
            }

            presudisponible=fnCompararPresupuesto(dataReq);
            
            for(a=0;a<(presudisponible.length);a++){
                pintar=presudisponible[a].split("|");
                disponible=pintar[0];
                txtdisp="";
                if(disponible >= 0){
                    disponible = disponible;
                    txtdisp = "Disponible";
                }else{
                    disponible = 0;
                    txtdisp = "No Disponible";
                }
                posiciones=pintar[1];
                //$("#fila"+( parseInt(posiciones) +1) ).append(disponible);
                $("#fila"+( parseInt(posiciones) +1) ).append(parseFloat(disponible).toFixed(2));
                $("#txtfila"+( parseInt(posiciones) +1) ).append(txtdisp);
            }


           
            $('#btnYesModalConfi').prop( "disabled", false );
       
            // ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnGenerarUrlEnc(idRequisicion, noRequisicion){
    var ulrEncriptada = "";
    dataObj = {
        option: 'generarUrlEnc',
        idRequisicion: idRequisicion,
        noRequisicion: noRequisicion
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
            ulrEncriptada = data.contenido;
        }else{
           
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    }); 
    return  ulrEncriptada;
}

// cancela todo movimiento no guardado en la requisición
function fnCancelarRequisicion(){
  var req = $("#idtxtRequisicion").val();
  dataObj = {
            option: 'cancelarRequisicion',
            noReq: req,
            status: 'Cancelado'
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
                muestraMensaje('Se cancelo todo movimiento no guardado en la Requisición ', 1, 'divMensajeOperacion', 5000); 
                $('#btnCerrarModalGeneral').addClass('cerrarModalCancelarReuisicion');
            }else{
                muestraMensaje('No se cancelo ningún movimiento en la Requisición ', 3, 'divMensajeOperacion', 5000);
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });  
}

// regresa un nivel de estatus
function fnRechazarRequisicion(){
    var idReq = $("#idtxtRequisicion").val();
    var noReq = $("#idtxtNoRequisicion").val();
    dataObj = {
            option: 'rechazarRequisicion',
            noReq: idReq
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
                muestraMensaje('Se Rechaza la Requisición ', 1, 'divMensajeOperacion', 5000); 
                location.replace("./PO_SelectOSPurchOrder.php?");
            }else{
                muestraMensaje('No se rechazo la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

// regresa al panel de la requisición
function fnRegresarPanelRequisicion(){
    location.replace("./PO_SelectOSPurchOrder.php");
}

// valida que exista el presupuesto para la partida asignada
function fnValidarPresupuestoPartida(orden){
    var idRequisicion = $("#idtxtRequisicion").val();
    var noRequisicion = $("#idtxtNoRequisicion").val();
    var partidaEspecifica = $("#selectCvePartidaEspecifica" + orden ).val();
    var presupuestoActual = $("#addPresupuestoH" + orden).val();
    var cvePresupuestal = $("#selectPartidaEspecificaCvePresupuestal"+orden).val();

    dataObj= {
        option: 'validarPresupuestoPartida',
        idReq: idRequisicion,
        noReq: noRequisicion,
        partidaEsp: partidaEspecifica,
        presupuestoActual: presupuestoActual,
        orden: orden,
        cvePresupuestal: cvePresupuestal
    };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            $("#addPresupuesto" + orden).html(data.contenido.nuevoprsupuesto);
        }else{
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}
function fnReindexar(){
    // muestraCargandoGeneral();

   
    var idReq = $("#idtxtRequisicion").val();
    dataObj= {
        option: 'reIndexar',
        idReq: idReq
    };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            //dataIndex = data.contenido.datos;
            //var indexReq = "";
            //for (var info in dataIndex) {
            //indexReq = dataIndex[info].orderlineno_;
            //}
        }else{
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });

}

// pone en ceros los estatus y ordenes de los elementos que no cumplan con las codiciones para guardar la información
function fnLimpiar(idReq){
    dataObj= {
        option: 'limpiar',
        idReq: idReq
    };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType: "json",
          url: url,
          data: dataObj
      })
    .done(function( data ) {
        if(data.result){
        }else{
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

// genera el layout que se utiliza para cargar la información de los anexos tecnicos
function fnGenerarLayoutRequisicion(){
    var noReq = $("#idtxtRequisicion").val();
    if(Number(noReq) > 0){
        var jsonData = new Array();
        var obj = new Object();
        obj.transno = noReq;
        jsonData.push(obj);

        fnGenerarArchivoLayout(funcionGenerarLayout, typeGenerarLayout, jsonData, tipoLayout);
    }
}

function fnSeleccionaAnexo(idRequi, ur, ue, ordenElementoRequi, mbflag){// A
    if(existeFolioAnexo) {
        fnMuestraAnexo(idRequi, ur, ue, ordenElementoRequi, nuAnexoTecnico, mbflag );
    } else {// B
        dataObj = { 
            option: 'seleccionaAnexo',
            idrequi: idRequi,
            ur: ur,
            ue: ue
        };
        $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType:"json",
            url: url,
            data:dataObj
        }).done(function( data ) {// C
            fnMostrarRequisicionModal();
            if(data.result){
                dataSelAnexo = data.contenido.datos;
                dataFolioAnexo = data.contenido.datosFolioExisteAnexo;
                //console.log(data.contenido.datosFolioExisteAnexo);
                // se cambia la declaracion de la siguiente variable a un entorno global
                // var existeFolioAnexo = dataFolioAnexo[0].folioAnexo;
                // se acambio el nombre del almacenamiento del folio del anexo seleccionado
                var folioAnexoSeleccionado = dataFolioAnexo[0].folioAnexo;
                var folioAnexo = "";
                var urAnexo = "";
                var ueAnexo = "";
                var ordenAnexo = 0;
                var requiAnexo = "";
                var statusAnexo = "";
                nuAnexoTecnico = dataFolioAnexo[0].folioAnexo;
                //console.log(existeFolioAnexo);
                //se debe comprobar si existe anexo o no antes de hacer la consulta de los anexos existentes
                // if(existeFolioAnexo > 0){
                //     fnMuestraAnexo(idRequi, ur, ue, ordenElementoRequi, folioAnexoSeleccionado, mbflag );
                // } else {
                // }
                $("#divAnexoTabla").empty();
                $("#divAnexoTabla").append('<div id="mensajeAnexo" class="hide"></div>');
                //$("#divAnexoTabla").append('<div id="divAnexoTablaHeader" class="w80p h25"><div class="w25p plr5 fl">#</div><div class="w25p plr5 fl">Nº Anexo</div><div class="w25p plr5 fl">UR</div><div class="w25p plr5 fl">UE</div></div>');
                $("#divAnexoTabla").append('<table id="tablaSelAnexo" class="w80p tableHeaderVerde" border="1" bordercolor="#DDDDDD"></table>');
                $("#tablaSelAnexo").append('<tr><th class="w5p text-center">#</th><th class="w20p text-center">Seleccionar</th><th class="w20p text-center">Nº Anexo</th><th class="w20p text-center">UR</th><th class="w20p text-center">UE</th><th class="w10p text-center">Opción</th></tr>');
                if(dataSelAnexo == null || dataSelAnexo === 'undefined' || dataSelAnexo == 0 ){
                    $('#mensajeAnexo').removeClass('hide');
                    $('#mensajeAnexo').append('<p class="ftc3"><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Sin anexos por mostrar</p>');
                }else{
                    for (var info in dataSelAnexo) {
                        ordenAnexo ++;
                        folioAnexo = dataSelAnexo[info].folioAnexo;
                        urAnexo = dataSelAnexo[info].ur;
                        ueAnexo = dataSelAnexo[info].ue;
                        requiAnexo = dataSelAnexo[info].idrequisicion;
                        statusAnexo = dataSelAnexo[info].ind_status;
                        if(statusAnexo == 2){
                            $("#tablaSelAnexo").append('<tr><td class="text-center">'+ordenAnexo+'</td><td class="text-center"><input class="seleAnexoTec" type="radio" id="seleAnexoTec'+ordenAnexo+'" name="seleAnexoTec" value="'+folioAnexo+'"/></td><td class="text-center">'+folioAnexo+'</td><td class="text-center">'+urAnexo+'</td><td class="text-center">'+ueAnexo+'</td><td><a href="#" id="btnNextAnexo'+ordenAnexo+'" class="anexoSiguiente hide" onclick="fnMuestraAnexo('+ idRequi +',\''+ur+'\',\''+ue+'\','+ordenElementoRequi+','+folioAnexo+', \''+mbflag+'\',true)">Ver detalle '+folioAnexo+'</a><div id="btnSaveAnexo'+ordenAnexo+'" class="anexoSave hide botonVerde btn-sm" onclick="fnSaveAnexo('+ idRequi +',\''+ur+'\',\''+ue+'\','+ordenElementoRequi+','+folioAnexo+','+ordenAnexo+')">Guardar</div></td></tr>');
                        }else{
                            $("#tablaSelAnexo").append('<tr><td class="text-center">'+ordenAnexo+'</td><td class="text-center"><input class="seleAnexoTec" type="radio" id="seleAnexoTec'+ordenAnexo+'" name="seleAnexoTec" value="'+folioAnexo+'" checked/></td><td class="text-center">'+folioAnexo+'</td><td class="text-center">'+urAnexo+'</td><td class="text-center">'+ueAnexo+'</td><td><a href="#" id="btnNextAnexo'+ordenAnexo+'" class="anexoSiguiente" onclick="fnMuestraAnexo('+ idRequi +',\''+ur+'\',\''+ue+'\','+ordenElementoRequi+','+folioAnexo+', \''+mbflag+'\')">Ver detalle '+folioAnexo+'</a><div id="btnSaveAnexo'+ordenAnexo+'" class="anexoSave hide botonVerde btn-sm" onclick="fnSaveAnexo('+ idRequi +',\''+ur+'\',\''+ue+'\','+ordenElementoRequi+','+folioAnexo+','+ordenAnexo+')">Guardar</div></tr>');
                        }
                    }
                }
            } else {
                $('#mensajeAnexo').removeClass('hide');
                $('#mensajeAnexo').append('<p class="ftc3"><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Sin anexos por mostrar</p>');
            }
        })// C
        .fail(function(result) {
            console.log("ERROR");
            console.log( result );
        });
    }// B
}// A

///////////////////////////////////////////////
// depreciado por mal uso dentro del sistema //
///////////////////////////////////////////////
$(document).on('click','.seleAnexoTec',function(event){
    var rootId = $(this).attr('id').replace('seleAnexoTec','');
    $.each($('#tablaSelAnexo input[type=radio]'), function(index, value) { 
        var id = $(this).attr('id').replace('seleAnexoTec','')
            ,valorActual = $(this).val();
        // console.log(nuAnexoTecnico == valorActual, nuAnexoTecnico, valorActual, rootId);
        //  se compruba que el elemento seleccionado sea igual
        //  al dato de anexo guardado
        if( (nuAnexoTecnico == valorActual) && (id == rootId) ){
            $("#btnNextAnexo"+id).removeClass('hide');
            $("#btnSaveAnexo"+id).addClass('hide');
            $('#mensajeAnexo').empty();
            $('#mensajeAnexo').addClass('hide');
        }else if(id == rootId){
            $('.anexoSiguiente').addClass('hide');
            $('.anexoSave').addClass('hide');
            $('#mensajeAnexo').empty();
            $('#mensajeAnexo').addClass('hide');
            $("#btnSaveAnexo"+id).removeClass('hide');
        }else{
            $("#btnNextAnexo"+id).addClass('hide');
            $("#btnSaveAnexo"+id).addClass('hide');
        }
        // value= $(this).attr('id');
        // value= value.replace('seleAnexoTec','');
        // if(numero == value){
        //     $('.anexoSiguiente').addClass('hide');
        //     $('.anexoSave').addClass('hide');
        //     $('#mensajeAnexo').empty();
        //     $('#mensajeAnexo').addClass('hide');
        //     $("#btnSaveAnexo"+numero).removeClass('hide');
        // }
    });
    /******************************************************************************
        numero= $(this).attr('id');
        numero= numero.replace('seleAnexoTec','');
        // comprobacion de elemento ya seleccionado
        if($(this).is(':checked')) {
            $.each($('#tablaSelAnexo input[type=radio]'), function(index, value) { 
                value= $(this).attr('id');
                value= value.replace('seleAnexoTec','');
                if(numero == value){
                    $('.anexoSiguiente').addClass('hide');
                    $('.anexoSave').addClass('hide');
                    $('#mensajeAnexo').empty();
                    $('#mensajeAnexo').addClass('hide');
                    $("#btnSaveAnexo"+numero).removeClass('hide');
                }
            });
        } else {
            $("#btnNextAnexo"+numero).addClass('hide');
            $("#btnSaveAnexo"+numero).addClass('hide');
            $('#mensajeAnexo').empty();
            $('#mensajeAnexo').addClass('hide');
        }  
    ******************************************************************************/
});

function fnSaveAnexo(idrequi, ur, ue, ordenElementoRequi, folioAnexo, ordenAnexo, confirmacion){
    var confirmacion = confirmacion||false;
    if(nuAnexoTecnico == 0){
        folioAnexo
    }
    if((nuAnexoTecnico != 0 && nuAnexoTecnico != folioAnexo) && !confirmacion){
        muestraModalGeneralConfirmacion(
            3,'Advertencia',
            '¿Realmente desea cambiar el anexo técnico seleccionado <strong>('+nuAnexoTecnico+')</strong>?','',
            `fnSaveAnexo('${idrequi}', '${ur}', '${ue}', '${ordenElementoRequi}', '${folioAnexo}', '${ordenAnexo}', true)`
        );
        $('#ModalGeneral').css('z-index',10000);
        return;
    }
    nuAnexoTecnico = folioAnexo;
    dataObj = { 
        option: 'saveAnexo',
        idReq: idrequi,
        ur: ur,
        ue: ue,
        ordenElementoRequi: ordenElementoRequi,
        folioAnexo: folioAnexo
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    }).done(function( data ) {
        if(data.result){
            // se coloca la variable de confirmacion de seleccion de anexo,
            // la cual tiene que ser removida en la funcion de remoscion 
            // de anexo tecnico
            existeFolioAnexo = true;
            $("#btnSaveAnexo"+ordenAnexo).addClass('save');
            $("#btnNextAnexo"+ordenAnexo).addClass('next');
            $('#mensajeAnexo').empty();
            $('#mensajeAnexo').addClass('mensajeSuccess')
            $('#mensajeAnexo').append('<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;'+data.contenido+'</p>');
            $('#mensajeAnexo').removeClass('hide');
            $("#btnSaveAnexo"+ordenAnexo).addClass('hide');
            $("#btnNextAnexo"+ordenAnexo).removeClass('hide');
        }else{
            $('#mensajeAnexo').empty();
            $('#mensajeAnexo').addClass('mensajeError')
            $('#mensajeAnexo').append('<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;'+data.contenido+'</p>');
            $('#mensajeAnexo').removeClass('hide');
            $("#btnSaveAnexo"+ordenAnexo).addClass('hide');
            $("#btnNextAnexo"+ordenAnexo).addClass('hide');
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

// muestra modal para seleccionar los elementos del anexo tecnico
function fnMuestraAnexo(idRequi, ur, ue, ordenElementoRequi, folioAnexo, mbflag, forceShow){
    anexoAsignado = 1;
    var contAnexo = $("#addedReglonArticulo"+ordenElementoRequi).val();
    
    if(typeof contAnexo === 'undefined'){
      contAnexo = $("#addedReglonServicio"+ordenElementoRequi).val();
    }

    // Datos a validar
    var partida = '';
    var stockid = '';
    if (mbflag == 'B') {
        partida = $("#selectCvePartidaEspecifica"+ordenElementoRequi).val();
        stockid = $("#selectCveArticulo"+ordenElementoRequi).val();
    } else {
        partida = $("#selectCvePartidaEspecificaServ"+ordenElementoRequi).val();
        stockid = $("#cveServicio"+ordenElementoRequi).val();
    }

    if(!forceShow){
        if(partida == 0 || stockid == 0){
            muestraModalGeneral(3,'Error de Datos', 'Es necesario seleccionar la partida y el clave.');
            $('#ModalGeneral').css('z-index',10000);
            return;
        }
    }

    dataObj = { 
        option: 'muestraInfoAnexo',
        idReq: idRequi,
        ur: ur,
        ue: ue,
        ordenElementoRequi: ordenElementoRequi,
        folioAnexo: folioAnexo,
        partida: partida,
        stockid: stockid
    };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        fnMostrarRequisicionModal();
        if(data.result){
            dataAnexoJason = data.contenido.datos;
            var idAnexo = "";
            var idPartida = "";
            var idProceso = "";
            var nombreBienServ = "";
            var descBienServ = "";
            var ordenAnexo = "";
            $("#divAnexoTabla").empty();
            $("#divAnexoTabla").append('<div id="mensajeAnexo" class="hide"></div>');
            $("#divAnexoTabla").append('<div id="titileAnexo"><h3 class="m0 pb10"><b> Anexo Técnico '+ folioAnexo +'</b></h3></div>');
            $("#divAnexoTabla").append('<table id="divAnexoTablaHeader" class="w90p tableHeaderVerde" border="1" bordercolor="#DDDDDD"></table>');
            $("#divAnexoTablaHeader").append('<tr><th class="w10p text-center">Sel</th><th class="w10p text-center">#</th><th class="w25p text-center">Bien/Servicio</th><th class="w30p text-center">Descripción</th><th class="w25p text-center">Observaciones</th></tr>');
            if(dataAnexoJason === 'undefined' || dataAnexoJason == 0 || dataAnexoJason == null || dataAnexoJason == ''){
                $('#tablaSelAnexo').addClass('hide');
                $('#mensajeAnexo').removeClass('hide');
                $('#mensajeAnexo').append('<p class="ftc3"><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;Sin elementos por mostrar</p>');
            }else {
                // Cambios pruebas, limpiar variable
                jsonIdElementoAnexoTecnico = new Array();
                if(contAnexo != ''){
                    var cadenaAnexo = contAnexo, 
                    separador = ",", 
                    arregloDeSubCadenasAnexo = cadenaAnexo.split(separador);

                    for (var info in dataAnexoJason) {
                        idAnexoElemento = dataAnexoJason[info].idAnexoElemento
                        idAnexo = dataAnexoJason[info].idanexo;
                        idPartida = dataAnexoJason[info].idpartida;
                        idProceso = dataAnexoJason[info].idproceso;
                        ordenAnexo = dataAnexoJason[info].ordenAnexo;
                        nombreBienServ = dataAnexoJason[info].bienServicio;
                        descBienServ = dataAnexoJason[info].descripcion_bien_serv;
                        observaciones = dataAnexoJason[info].observaciones;
                        var valCheked = "";
                        $.each(arregloDeSubCadenasAnexo, function(index, valueCadenasAnexo) { 
                            if(valueCadenasAnexo == idProceso){
                                valCheked = valueCadenasAnexo;
                            }
                        });
                        
                        // Cambios pruebas, se agregan id de los check
                        var obj = new Object();
                        obj.idAnexoElemento = idAnexoElemento;
                        jsonIdElementoAnexoTecnico.push(obj);
                        
                        if(valCheked == idProceso ){
                            fnAsignarElementoAnexo(idRequi, idAnexo, idAnexoElemento, ordenElementoRequi, idProceso, 1);
                            $("#divAnexoTablaHeader").append('<tr><td class="text-center"><input type="checkbox" id="selElementoAnexo'+idAnexoElemento+'" name="selElementoAnexo'+idAnexoElemento+'" value="'+idAnexoElemento+'" onclick="fnAgregaRenglon('+ idRequi +','+ idAnexo +','+ idAnexoElemento +','+ordenElementoRequi+','+idProceso+')" checked /></td><td class="text-center">'+idProceso+'</td><td>'+nombreBienServ+'</td><td>'+descBienServ+'</td><td>'+observaciones+'</td></tr>');
                        }else{
                            fnAsignarElementoAnexo(idRequi, idAnexo, idAnexoElemento, ordenElementoRequi, idProceso, 0);
                            $("#divAnexoTablaHeader").append('<tr><td class="text-center"><input type="checkbox" id="selElementoAnexo'+idAnexoElemento+'" name="selElementoAnexo'+idAnexoElemento+'" value="'+idAnexoElemento+'" onclick="fnAgregaRenglon('+ idRequi +','+ idAnexo +','+ idAnexoElemento +','+ordenElementoRequi+','+idProceso+')"  /></td><td class="text-center">'+idProceso+'</td><td>'+nombreBienServ+'</td><td>'+descBienServ+'</td><td>'+observaciones+'</td></tr>');
                        }
                    }
                }else{
                    for (var info in dataAnexoJason) {
                        idAnexoElemento = dataAnexoJason[info].idAnexoElemento
                        idAnexo = dataAnexoJason[info].idanexo;
                        idPartida = dataAnexoJason[info].idpartida;
                        idProceso = dataAnexoJason[info].idproceso;
                        ordenAnexo = dataAnexoJason[info].ordenAnexo;
                        nombreBienServ = dataAnexoJason[info].bienServicio;
                        descBienServ = dataAnexoJason[info].descripcion_bien_serv;
                        observaciones = dataAnexoJason[info].observaciones;

                        // Cambios pruebas, se agregan id de los check
                        var obj = new Object();
                        obj.idAnexoElemento = idAnexoElemento;
                        jsonIdElementoAnexoTecnico.push(obj);
                        
                        $("#divAnexoTablaHeader").append('<tr><td class="text-center"><input type="checkbox" id="selElementoAnexo'+idAnexoElemento+'" name="selElementoAnexo'+idAnexoElemento+'" value="'+idAnexoElemento+'" onclick="fnAgregaRenglon('+ idRequi +','+ idAnexo +','+ idAnexoElemento +','+ordenElementoRequi+','+idProceso+')"  /></td><td class="text-center">'+idProceso+'</td><td>'+nombreBienServ+'</td><td>'+descBienServ+'</td><td>'+observaciones+'</td></tr>');
                    }
                }
                //$("#divAnexoTabla").append('<div class="w100p p5 mt10">'+
                    //'<div id="btnbackAnexo'+idAnexoElemento+'" class="w20p botonVerde" onclick="fnSeleccionaAnexo('+idRequi+',\''+ur+'\',\''+ue+'\','+ordenElementoRequi+');">Regresar</div>'+
                  //  '<div id="btnCloseAnexo" data-dismiss="modal" class="w20p botonVerde">Cerrar</div>'+
                    //'<div id="btnAsignarElementosAnexo" class="w20p botonVerde fl" onclick="fnAsignarElementoAnexo('+ idAnexo +','+ idAnexoElemento +','+ordenElementoRequi+','+idProceso+');">Asignar Elementos</div>'+
                //'</div>');
            }
            $("#divAnexoTabla").append('<div class="w100p p5 mt10">'+
                '<div id="btnCloseAnexo" data-dismiss="modal" class="w20p botonVerde">Cerrar</div>'+
            '</div>');
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });  
}

// permite seleccionar los renglones del anexo tecnico que serán ligados a los productos o servicios seleccionados
function fnAgregaRenglon(idRequi, idAnexo, idAnexoElemento, ordenElementoRequi, ordenAnexo){
    var contAnexoArt = $("#addedReglonArticulo"+ordenElementoRequi).val();
    var contAnexoServ = $("#addedReglonServicio"+ordenElementoRequi).val();
    var idReq = $("#idtxtRequisicion").val();
    var contAnexo = "";
    
    if((contAnexoArt != '' || contAnexoArt == '') && (typeof contAnexoServ === 'undefined')){
        contAnexo = contAnexoArt;
    } else if ((typeof contAnexoArt === 'undefined') && (contAnexoServ != '' || contAnexoServ == '' )){
        contAnexo = contAnexoServ;
    }
    var seleccionado = 0;
    if($('input:checkbox[name=selElementoAnexo'+idAnexoElemento+']').is(':checked')) {
        seleccionado = 1;
        //$('#selElementoAnexo'+idAnexoElemento).parents('tr').addClass("seleccionado");
        if (contAnexo == ''){
            $("#addedReglonArticulo"+ordenElementoRequi).val(""+ordenAnexo);
            $("#addedReglonServicio"+ordenElementoRequi).val(""+ordenAnexo);
        }else{
            contAnexo = $("#addedReglonArticulo"+ordenElementoRequi).val();
            if(typeof contAnexo === 'undefined'){
                contAnexo = $("#addedReglonServicio"+ordenElementoRequi).val();
                $("#addedReglonServicio"+ordenElementoRequi).val(""+contAnexo+","+ordenAnexo);
            }else{
                $("#addedReglonArticulo"+ordenElementoRequi).val(""+contAnexo+","+ordenAnexo);
            }
        }
        fnAsignarElementoAnexo(idRequi, idAnexo, idAnexoElemento, ordenElementoRequi, ordenAnexo, 1);
    }else{
        //$('#selElementoAnexo'+idAnexoElemento).parents('tr').removeClass("seleccionado");
        contAnexo = contAnexo.split(',');
        var newContAnexo = [];
        $.each(contAnexo, function(index, valueContenidoAnexo) { 
            if(valueContenidoAnexo == ordenAnexo){
            }else{
                newContAnexo.push(valueContenidoAnexo);
            }
        });
        $("#addedReglonServicio"+ordenElementoRequi).val(""+newContAnexo);
        $("#addedReglonArticulo"+ordenElementoRequi).val(""+newContAnexo);
        fnAsignarElementoAnexo(idRequi, idAnexo, idAnexoElemento, ordenElementoRequi, ordenAnexo, 0);
    }
    
    // habilitar o deshabilitar check
    for (var key in jsonIdElementoAnexoTecnico) {
        if (seleccionado == 1) {
            if (idAnexoElemento != jsonIdElementoAnexoTecnico[key].idAnexoElemento) {
                $("#selElementoAnexo" + jsonIdElementoAnexoTecnico[key].idAnexoElemento).prop("disabled", true);
            }
        } else {
            $("#selElementoAnexo" + jsonIdElementoAnexoTecnico[key].idAnexoElemento).prop("disabled", false);
        }
    }
}

function fnAsignarElementoAnexo(idReq, idAnexo, idAnexoElemento, ordenElementoRequi, ordenAnexo, check){
    dataObj = { 
        option: 'asignaElementoAnexo',
        idReq: idReq,
        idAnexo: idAnexo,
        idAnexoElemento: idAnexoElemento,
        ordenElementoRequi: ordenElementoRequi,
        ordenAnexo: ordenAnexo,
        check: check
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){ }else{ }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function fnRemoveAnexoTecnico(idR){
    dataObj = { 
            option: 'removeAnexo',
            idReq: idR            
          };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            anexoAsignado = 0;
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

// revisa que exista elementos para hacer el calculo del presupuesto
function fnChecarExistencia(arregloDeSubCadenas, idPartida){
    var contarexistencia=0;
     for (var x in arregloDeSubCadenas) {
        if(arregloDeSubCadenas[x] == idPartida){
            contarexistencia++;
        }
    }
    return contarexistencia;
}

/** Muestra un formuario con los datos crgados del excel */
function fnMostrarRequisicionModal(){
    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Anexo Técnico</h3>';
    $('#ModalCR_Titulo').empty();
    $('#ModalCR_Titulo').append(titulo);
    $('#ModalCR').modal('show');
}

/**
 * Muestra un modal
 */
function fnEditorTextoOpen(orden){
    $('#idPopupEditor'+orden).css("display","block");
    $('#idPopupEditor'+orden).fadeIn('slow');
    $('.popup-overlay').fadeIn('slow');
    $('.popup-overlay').height($(window).height());
    return false;

}

/**
 * Cierra el modal 
 */
function fnEditorTextoClose(){
    $('.idPopupEditor').fadeOut('slow');
        $('.popup-overlay').fadeOut('slow');
        return false;
}

/**
 * Fija la Fecha de elaboración.
 */
function fnFijarFecha(){
    dataObj = { 
            option: 'getFechaServidor',
          };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            info=data.contenido.Fecha;
            $("#idFechaElaboracion").val(""+info[0].fechaDMY); 
            //$("#dateHasta").val(""+info[0].fechaDMY); 
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}
/**
 * Fija la Fecha requerida a un dia despues del dia actual.
 */
function fnFijarFechaSiguiente(dias){
    dataObj = { 
            option: 'getFechaServidorSiguiente',
            numerodias:dias
          };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            info=data.contenido.Fecha;
            $("#idFechaEntrega").val(""+info[0].fechaDMY); 
            //$("#dateHasta").val(""+info[0].fechaDMY); 
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

// una vez calculado el nuevo presupuesto por partida regresa el presupuesto sin la cantidad total 
function fnActualizaPresupuesto() {
    var ppto_disponible= 0;
    var numero= "";
    var total_partida= 0;
    var suficiente= 0;

    $("#idMainListContentArticulo select[id*=selectCvePartidaEspecifica]").each(function(index, el) {
        if (typeof partidas_presupuesto[this.value] !== "undefined"){
            partidas_presupuesto[this.value].disponible= partidas_presupuesto[this.value].ppto_inicial;
        }
    });

    $("#idMainListContentArticulo select[id*=selectCvePartidaEspecifica]").each(function(index, el) {
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecifica','');
        suficiente= 0;

        if ($("#addedCantidadTotalArticulo"+numero).val() == ""){
            total_partida= 0;
        } else {
            total_partida= parseFloat($("#addedCantidadTotalArticulo"+numero).val());
        }

        if (typeof partidas_presupuesto[this.value] === "undefined"){
            ppto_disponible= fnObtenerPresupuestoDisponible(numero);
            partidas_presupuesto[this.value]= {"disponible": ppto_disponible-total_partida, "ppto_inicial": ppto_disponible} ;
            $("#addPresupuesto"+numero).html(ppto_disponible);
            suficiente= ppto_disponible-total_partida;
        } else {
            $("#addPresupuesto"+numero).html(partidas_presupuesto[this.value].disponible);
            partidas_presupuesto[this.value].disponible -= total_partida;
            suficiente= partidas_presupuesto[this.value].disponible;
        }

        if(suficiente >= 0){
            $("#validaPresupuesto" + numero).val("Ppto Suficiente");
            $("#validaPresupuesto" + numero).css('border','solid 1px #1B693F');
            $("#validaPresupuesto" + numero).css('color','#1B693F');
            $("#validaPresupuesto" + numero).prop("readonly", true);
            $("#validaPresupuesto" + numero).attr('disabled', 'disabled');
            iCntPI = 0;
       }else{
            $("#validaPresupuesto" + numero).val("Ppto Insuficiente");
            $("#validaPresupuesto" + numero).css('border','solid 1px #ff0000');
            $("#validaPresupuesto" + numero).css('color','#ff0000');
            $("#validaPresupuesto" + numero).prop("readonly", true);
            $("#validaPresupuesto" + numero).attr('disabled', 'disabled');
            iCntPI = iCntPI +1;
       }
    });
}

// hace los calculos del presupuesto con los servicios
function fnActualizaPresupuestoServicio() {
    var ppto_disponible= 0;
    var numero= "";
    var total_partida= 0;
    var suficiente= 0;

    $("#idMainListContentServicio select[id*=selectCvePartidaEspecificaServ]").each(function(index, el) {
        if (typeof partidas_presupuesto[this.value] !== "undefined"){
            partidas_presupuesto[this.value].disponible= partidas_presupuesto[this.value].ppto_inicial;
        }
    });

    $("#idMainListContentServicio select[id*=selectCvePartidaEspecificaServ]").each(function(index, el) {
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecificaServ','');
        suficiente= 0;

        if ($("#addedPEServicio"+numero).val() == ""){
            total_partida= 0;
        } else {
            total_partida= parseFloat($("#addedPEServicio"+numero).val());
        }

        if (typeof partidas_presupuesto[this.value] === "undefined"){
            ppto_disponible= fnObtenerPresupuestoDisponible(numero);
            partidas_presupuesto[this.value]= {"disponible": ppto_disponible-total_partida, "ppto_inicial": ppto_disponible} ;
            $("#addPresupuesto"+numero).html(ppto_disponible);
            suficiente= ppto_disponible-total_partida;
        } else {
            $("#addPresupuesto"+numero).html(partidas_presupuesto[this.value].disponible);
            partidas_presupuesto[this.value].disponible -= total_partida;
            suficiente= partidas_presupuesto[this.value].disponible;
        }

        if(suficiente >= 0){
            $("#validaPresupuesto" + numero).val("Ppto Suficiente");
            $("#validaPresupuesto" + numero).css('border','solid 1px #1B693F');
            $("#validaPresupuesto" + numero).css('color','#1B693F');
            $("#validaPresupuesto" + numero).prop("readonly", true);
            $("#validaPresupuesto" + numero).attr('disabled', 'disabled');
            iCntPI = 0;
       }else{
            $("#validaPresupuesto" + numero).val("Ppto Insuficiente");
            $("#validaPresupuesto" + numero).css('border','solid 1px #ff0000');
            $("#validaPresupuesto" + numero).css('color','#ff0000');
            $("#validaPresupuesto" + numero).prop("readonly", true);
            $("#validaPresupuesto" + numero).attr('disabled', 'disabled');
            iCntPI = iCntPI +1;
       }
    });
}

// muestra los productos por partida especifica
function fnCargarPartidaProducto(periodo,tagref,orden, idPartida=''){
    var regresadatos= false;
    var contenidoDescPartidaEspecifica = "";
    var contenidosCvePartidaEspecifica = "";
    var contenidoPartidaEspecificaCvePresupuestal = "";

    //Opcion para operacion
    dataObj = { 
          option: 'mostrarPartidaEspecificaProductos',
          tagref: tagref,
          periodo: periodo
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
          dataJson = data.contenido.datos;
          contenidosCvePartidaEspecifica += '<option value="0">Partida...</option>';

          for (var info in dataJson) {
            var selected = '';
            if (dataJson[info].partidacalculada == idPartida) {
                selected = ' selected ';
            }
            contenidosCvePartidaEspecifica += "<option "+selected+" value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
            /*contenidoDescPartidaEspecifica += "<option value='"+dataJson[info].partidadescripcion+"'>"+dataJson[info].partidadescripcion+"</option>";
            contenidoPartidaEspecificaCvePresupuestal += "<option value='"+dataJson[info].clavePresupuestal+"'>"+dataJson[info].clavePresupuestal+"</option>";*/
            regresadatos= true;
          }

          if (idPartida != '') {
            // Si es editar regresar datos
            regresadatos= contenidosCvePartidaEspecifica;
          }
          $('#selectCvePartidaEspecifica'+orden).empty();
          $('#selectCvePartidaEspecifica'+orden).append(contenidosCvePartidaEspecifica);
      }
    })
    .fail(function(result) {
       console.log("ERROR");
       console.log( result );
    });
    return regresadatos;
}

// muestra los servicios por partida especifica
function fnCargarPartidaServicio(periodo, tagref, orden){
    var regresadatos= false;
    var contenidosCvePartidaEspecificaServ = "";
    var contenidoDescPartidaEspecificaServ = "";
    //Opcion para operacion
    dataObj = { 
      option: 'mostrarPartidaEspecificaServicios',
      tagref: tagref,
      periodo: periodo
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
          dataJson = data.contenido.datos;
          contenidosCvePartidaEspecificaServ += '<option value="0">Partida...</option>';
          contenidoDescPartidaEspecificaServ += '<option value="0">Descripción...</option>';
          
          for (var info in dataJson) {
            contenidosCvePartidaEspecificaServ += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
            contenidoDescPartidaEspecificaServ += "<option value='"+dataJson[info].partidacalculada+"' data-clave='"+ dataJson[info].clavePresupuestal +"'>"+dataJson[info].partidadescripcion+"</option>";
            regresadatos= true;
          }
          $('#selectCvePartidaEspecificaServ'+orden).empty();
          $('#selectCvePartidaEspecificaServ'+orden).append(contenidosCvePartidaEspecificaServ);
          
          // Cambios pruebas, se comenta para que no muestre informacion se homologa a bienes
          // $('#selectDescPartidaEspecificaServ'+orden).empty();
          // $('#selectDescPartidaEspecificaServ'+orden).append(contenidoDescPartidaEspecificaServ);
      }
    })
    .fail(function(result) {
       console.log("ERROR");
       console.log( result );
    });

    return regresadatos;
}

function fnCargarPartidaInstrumental(periodo,tagref,orden){
    var regresadatos= false;
    var contenidoDescPartidaEspecificaInstr = "";
    var contenidosCvePartidaEspecificaInstr = "";
    var contenidoPartidaEspecificaCvePresupuestalInstr = "";

    //Opcion para operacion
    dataObj = { 
          option: 'mostrarPartidaEspecificaInstrumentales',
          tagref: tagref,
          periodo: periodo
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
          dataPartidaInstrJson = data.contenido.datos;
          contenidoDescPartidaEspecificaInstr += '<option value="0">Partida...</option>';

          for (var info in dataPartidaInstrJson) {
            contenidoDescPartidaEspecificaInstr += "<option value='"+dataPartidaInstrJson[info].partidacalculada+"'>"+dataPartidaInstrJson[info].partidacalculada+"</option>";
            /*contenidoDescPartidaEspecifica += "<option value='"+dataJson[info].partidadescripcion+"'>"+dataJson[info].partidadescripcion+"</option>";
            contenidoPartidaEspecificaCvePresupuestal += "<option value='"+dataJson[info].clavePresupuestal+"'>"+dataJson[info].clavePresupuestal+"</option>";*/
            regresadatos= true;
          }
          $('#selectCvePartidaEspecificaInstrumental'+orden).empty();
          $('#selectCvePartidaEspecificaInstrumental'+orden).append(contenidoDescPartidaEspecificaInstr);
      }else{
        regresadatos= false;
      }
    })
    .fail(function(result) {
       console.log("ERROR");
       console.log( result );
    });
    return regresadatos;
}

$(document).on('click','#idBtnCancelarCR',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
});

$(document).on('click','#btnCerrarModalGeneral',function(){
    // ocultaCargandoGeneral();
});

$(document).on('click','.cerrarModalCancelar',function(){
    fnReindexar();
    location.replace("./PO_SelectOSPurchOrder.php?");
});
$(document).on('click','.closeAnexo',function(){
    $('#ModalCR').css('display','none');
});
$(document).on('click','.cerrarModalErrorOriginal',function(){
    var noReq = $("#idtxtNoRequisicion").val();
    var idReq = $("#idtxtRequisicion").val();
    var genUrlEnc = fnGenerarUrlEnc(idReq, noReq);
    location.replace("./Captura_Requisicion_V_3.php?"+genUrlEnc);
});

// validador para que permita decimales
$(document).on('keypress', '.soloNumeros', function(event) {
    var text = $(this).val();
    var cuenta = (text.match(/./g) || []).length;
    if(text.includes(".")){
      var texto2=[];
      texto2=(text.split("."));
      if (texto2[1].length >1 || texto2[0].includes(".")) {
              event.preventDefault();
      }
    }    
 });

// permite cerrar el modal de las validaciones
$(document).on('click','.closeModalRequi',function(){
    $("#modal-obs").css('display','none');
    $('#idNoExistenciaModalTab').empty();
    $('#idSufPresupuestalModalTab').empty();
    $('#idSolAlmacenModalTab').empty();
});

$(document).on('click','#btnGeneraNoExistencia',function(){
    var mensaje = "";
     var encUrl = $('#urlEncriptadaRequisicionInput').val();
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarNoExist');
    $('#btnGeneraNoExistencia').addClass('hide');
    $('#btnGeneraNoExistencia').removeAttr("onclick");
    $('#btnGeneraNoExistencia').remove();
    mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;Se ha generado la no existencia con el folio: <a target="_blank" href="./panel_no_existencias.php?"" style="color: blue; "><u>'+idFolioNoe+'</u></a></p>';
    $("#idNoExistenciaModalTab").append('<div><div class="fl mt30">'+mensaje+'</div></div>');
    
});
$(document).on('click','#btnActualizaNoExistencia',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarNoExist');
});

$(document).on('click','.cerrarModalGenerarNoExist',function(){
    $('#btnGeneraNoExistencia').addClass('hide');
    $('#btnGeneraNoExistencia').removeAttr("onclick");
    $('#btnGeneraNoExistencia').remove();
    var encUrl = $('#urlEncriptadaRequisicionInput').val();
    //location.replace("./Captura_Requisicion_V_3.php?"+encUrl);
    window.open('./panel_no_existencias.php?','_blank');
});

$(document).on('click','#btnGeneraSolAlmacen',function(){
    var mensaje = "";
    var encUrl = $('#urlEncriptadaRequisicionInput').val();
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarSolAlmacen');
    $('#btnGeneraSolAlmacen').addClass('hide');
    $('#btnGeneraSolAlmacen').removeAttr("onclick");
    $('#btnGeneraSolAlmacen').remove();
    mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-success" aria-hidden="true"></i>&nbsp;&nbsp;Se ha generado la Solicitud al almacén con el folio: <a target="_blank" href="./almacen.php?"" style="color: blue; "><u>'+idFolioNoe+'</u></a></p>';
    $("#idSolAlmacenModalTab").append('<div><div class="fl mt30">'+mensaje+'</div></div>');
});
$(document).on('click','#btnActualizaSolAlmacen',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalGenerarSolAlmacen');
});

$(document).on('click','.cerrarModalGenerarSolAlmacen',function(){
    $('#btnGeneraSolAlmacen').addClass('hide');
    $('#btnGeneraSolAlmacen').removeAttr("onclick");
    $('#btnGeneraSolAlmacen').remove();
    var encUrl = $('#urlEncriptadaRequisicionInput').val();
    //location.replace("./Captura_Requisicion_V_3.php?"+encUrl);
    window.open('./almacen.php','_blank');
    //window.open('./panel_no_existencias.php','_blank');
});
$(document).on('click','.cerrarModalCancelarReuisicion',function(){
    var encUrl = $('#urlEncriptadaRequisicionInput').val();
    location.replace("./Captura_Requisicion_V_3.php?"+encUrl);
    //location.replace("./PO_SelectOSPurchOrder.php?");
});

$(document).on('click','.closeTable',function(){
    $("#idTableReqValidacion").css('display','none');
    $("#idTableHeader").empty();
    $("#idTableContReq").empty();
    $("#idTableReqBotones").empty();
});
$(document).on('click','#idBtnCloseTable',function(){
    $("#idTableHeader").empty();
    $("#idTableContReq").empty();
    $("#idTableReqBotones").empty();
});


$(document).on('click','#btnYesModalConfi',function(e){
    e.stopPropagation();
    e.preventDefault();
    //$(this).prop('onclick', null);
    $(this).prop( "disabled", true );

});
