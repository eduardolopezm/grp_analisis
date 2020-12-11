/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author
 * @version 1.0
 */
//
var dataJsonNoCaptura = new Array();
var dataJsonNoCapturaSeleccionados = new Array();
var dataObjDatosBotones = new Array();
var estatusDiferentes = 0;
var seleccionoCaptura = 0;
var mensajeEstatusDiferentes = "Selecciono Folio con Estatus diferente, el Estatus debe ser igual";
var mensajeSinNoCaptura = "Sin selección de Folio";
var dataJsonMeses = new Array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
var autorizarGeneral = 0;
var numLineaReducciones = 1;
var datosReducciones = new Array();
var datosSelect = new Array();
var folioSelect = new Array();
var panelReducciones = 1;
var numPeriod = '';
var Type = '';
var Transno = '';
var tablaReducciones ='tablaReducciones';
var modeClave = 0;
var variosFolios = "";
var validStustus = 0;
var datosEliminarRedAmp = new Array();
var status_de_reintegroUDP = 1;
var Folio_Reintegro_Encabezado = 0;
var tipodePago = 0;

$(document).ready(function() {

    fnObtenerBotones('divBotones');
    loadtypeRefunds();
    typePayment();
    searchFolioofPanel();

    if(upID == 1){

        permissionUsers(transnoRef, typeRef);

    }else{

        disabletypePayment($("#selectTipoReintegro").val());
    }


    $('.validanumericos').keypress(function(e) {
        if(isNaN(this.value + String.fromCharCode(e.charCode)))
            return false;
    }).on("cut copy paste",function(e){
            e.preventDefault();
    });

});

function isset(variable) {
    try {
        return typeof eval(variable) !== 'undefined';
    } catch (err) {
        return false;
    }
}

function getSpinner() {
    var loadingimage =  '<div class="loadings" id="loadings"></div>';

    return loadingimage;
}

function typePayment(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_aviso_reintegros_Modelo.php",{option:'typePayment'}).then(function(result){
        var selectDatas = JSON.parse(result);
        $.each(selectDatas.datatype.datos,function(key, registro) {
            $("#selectOperacionTesoreria").append('<option value='+registro.value+'>'+registro.texto+'</option>');
        });

        fnFormatoSelectGeneral(".selectOperacionTesoreria");
    });
}

function loadtypeRefunds(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/captura_aviso_reintegros_Modelo.php",{option:'typeRefund'}).then(function(result){

        var selectData = JSON.parse(result);

        $.each(selectData.Status.datos,function(key, registro) {
            $("#selectTipoReintegro").append('<option value='+registro.value+'>'+registro.texto+'</option>');
        });

        fnFormatoSelectGeneral(".selectTipoReintegro");

    });
}

function disabletypePayment(typePayment){

    if(typePayment == 1){

        $("#selectOperacionTesoreria").multiselect('rebuild');
        $("#selectUnidadEjecutora").multiselect('rebuild');

        $("#selectOperacionTesoreria").multiselect('disable');
        $("#selectUnidadEjecutora").multiselect('disable');
        //$("#selectUnidadNegocio").multiselect('enable');

        document.getElementById("txtSIAFF").style.display="none";
       // document.getElementById("texttwo").style.display="block" lineTESOFE  CodigoRastreo  NumTransfer

        document.getElementById("lineTESOFE").style.display="block";
        document.getElementById("CodigoRastreo").style.display="block";
        document.getElementById("NumTransfer").style.display="none";

        document.getElementById("textONE").style.display="none";
        document.getElementById("texttwo").style.display="block";

        numLineaReducciones = 1;
        folioSelect = [];

    }else{
       if(typePayment == 2){

           $("#selectOperacionTesoreria").multiselect('rebuild');
           $("#selectUnidadEjecutora").multiselect('rebuild');

           $("#selectOperacionTesoreria").multiselect('disable');
           $("#selectUnidadEjecutora").multiselect('enable');
          // $("#selectUnidadNegocio").multiselect('enable');

           document.getElementById("txtSIAFF").style.display="none";

           document.getElementById("lineTESOFE").style.display="none";
           document.getElementById("CodigoRastreo").style.display="block";
           document.getElementById("NumTransfer").style.display="block";

           document.getElementById("textONE").style.display="block";
           document.getElementById("texttwo").style.display="none";

           numLineaReducciones = 1;
           folioSelect = [];

       }else{
           if(typePayment == 3){


               $("#selectOperacionTesoreria").multiselect('rebuild');
               $("#selectUnidadEjecutora").multiselect('rebuild');

               $("#selectOperacionTesoreria").multiselect('enable');
               $("#selectUnidadEjecutora").multiselect('enable');
           //    $("#selectUnidadNegocio").multiselect('enable');

               document.getElementById("txtSIAFF").style.display="none";

               document.getElementById("lineTESOFE").style.display="none";
               document.getElementById("CodigoRastreo").style.display="block";
               document.getElementById("NumTransfer").style.display="none";

               document.getElementById("textONE").style.display="block";
               document.getElementById("texttwo").style.display="none";

               numLineaReducciones = 1;
               folioSelect = [];

           }
       }
    }
}

function changedisabletypePayment(typePayments){

    var valor = "-1";

    if(typePayments.value == 1){

        $("#selectOperacionTesoreria").multiselect('disable');
        $("#selectUnidadEjecutora").multiselect('disable');

        document.getElementById("txtSIAFF").style.display="none";

        document.getElementById("lineTESOFE").style.display="block";
        document.getElementById("CodigoRastreo").style.display="block";
        document.getElementById("NumTransfer").style.display="none";

        document.getElementById("textONE").style.display="none";
        document.getElementById("texttwo").style.display="block";

        $('#'+tablaReducciones+' tbody').empty();
        $('#txtFolioTransf').val("");
        $('#txtFolioTransfADD').val("");

        numLineaReducciones = 1;
       // $("#selectUnidadEjecutora").multiselect('rebuild');
        folioSelect = [];

    }else{
        if(typePayments.value == 2){

            $("#selectOperacionTesoreria").multiselect('disable');
            $("#selectUnidadEjecutora").multiselect('enable');

            document.getElementById("txtSIAFF").style.display="none";

            document.getElementById("lineTESOFE").style.display="none";
            document.getElementById("CodigoRastreo").style.display="block";
            document.getElementById("NumTransfer").style.display="block";

            document.getElementById("textONE").style.display="block";
            document.getElementById("texttwo").style.display="none";

            $('#'+tablaReducciones+' tbody').empty();
            $('#txtFolioTransf').val("");
            $('#txtFolioTransfADD').val("");

            numLineaReducciones = 1;
            folioSelect = [];

        }else{
            if(typePayments.value == 3){

                $("#selectOperacionTesoreria").multiselect('enable');
                $("#selectUnidadEjecutora").multiselect('enable');


                document.getElementById("txtSIAFF").style.display="none";

                document.getElementById("lineTESOFE").style.display="none";
                document.getElementById("CodigoRastreo").style.display="block";
                document.getElementById("NumTransfer").style.display="none";

                document.getElementById("textONE").style.display="block";
                document.getElementById("texttwo").style.display="none";

                $('#'+tablaReducciones+' tbody').empty();
                $('#txtFolioTransf').val("");
                $('#txtFolioTransfADD').val("");

                numLineaReducciones = 1;
                folioSelect = [];
            }

        }

    }
}

function resettable(){

    $('#'+tablaReducciones+' tbody').empty();
    $('#txtFolioTransf').val("");
    $('#txtFolioTransfADD').val("");

    numLineaReducciones = 1;

    folioSelect = [];

}

function fnObtenerBotones(divMostrar) {

    dataObj = {
        option: 'obtenerBotones',
        type: ''
    };

    $.ajax({
        async:false,
        cache:false,
        method: "GET",
        dataType:"json",
        url: "modelo/captura_aviso_reintegros_Modelo.php",
        data:dataObj
    }).done(function( data ) {

            if(data.result){


                info = data.datos;
                dataObjDatosBotones = data.datos;

                var contenido = '';
                for (var key in info) {
                    var funciones = '';

                   /* if(info[key].statusid == 1){
                        funciones = 'saveRefunds('+info[key].statusid+')';
                    }else if(info[key].statusid == 4){
                        funciones = 'authorizeRefunds('+info[key].statusid+')';
                    }else if(info[key].statusid == 0){
                        funciones = 'cancelSaveRefunds('+info[key].statusid+')';
                    }
                    */

                   if(info[key].statusid == 0){
                        funciones = 'cancelSaveRefunds('+info[key].statusid+')';
                    } else if(info[key].statusid == 1){
                        funciones = 'saveRefunds('+info[key].statusid+')';
                    }else if(info[key].statusid == 4){
                        funciones = 'ConfirmforAuthorizeRefunds('+info[key].statusid+')';
                    }


                    contenido += '&nbsp;&nbsp;&nbsp; <component-button id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" value="'+info[key].namebutton+'" onclick="'+funciones+'" class="'+info[key].clases+'"></component-button>';
                    //contenido += '&nbsp;&nbsp;&nbsp; \
                    //<button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" onclick="'+funciones+'" class="btn btn-default botonVerde '+info[key].clases+'">'+info[key].namebutton+'</button>';
                }
                $('#'+divMostrar).append(contenido);
                fnEjecutarVueGeneral('divBotones');
            }else{
                muestraMensaje('No se obtuvieron los botones para realizar las operaciones', 3, 'divMensajeOperacion', 5000);
            }
    }).fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + result + '</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });
}

function cancelSaveRefunds(statusID){
    location.reload();
}

function saveRefunds(statusID){

    var IDstatus = statusID;

    if($('#selectTipoReintegro').val() == 1){
        var valueSearch = $('#txtFolioTransfADD').val();
    }else{
        var valueSearch = $('#txtFolioTransf').val();
    }

    if($('#selectTipoReintegro').val() == 1) {
        if (valueSearch == '' || valueSearch == null) {
            valueSearch = folioSelect;
        }
    }else{

        if($('#selectTipoReintegro').val() == 2){
            if (valueSearch == '' || valueSearch == null) {
                valueSearch = folioSelect;
            }
        }else{
            if($('#selectTipoReintegro').val() == 3){
                if (valueSearch == '' || valueSearch == null) {
                    valueSearch = folioSelect;
                }
            }
        }
    }



    var valueTotal = $("#ttalGeneral").val();

    loadtypeRefunds();


    //alert("Estatus de Boton: "+IDstatus+"    "+"Folio de Reintegro: "+valueSearch+"    "+"Es Actualizacion o Insercion: "+upID);

    if(valueSearch == '' || valueSearch == null){

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>  Escriba un Número de Folio o Número de Transferencia</p>';
        muestraModalGeneral(3, titulo, mensaje);

    }else{


        if(datosSelect.length > 1){
               modeClave = datosSelect.length;
           }else{
               if(datosSelect.length <= 1){
                   modeClave = datosSelect.length;
               }
        }

        if(upID == 1){

            if(modeClave > 1){

                var arrayData = {
                    fundataup: 'updateData',
                    idStatus: status_de_reintegroUDP,
                    mode_refund: modeClave,
                    ur_id: $('#selectUnidadNegocio').val(),
                    ue_id: $('#selectUnidadEjecutora').val(),
                    lineTesofe:$('#txtLineTesofe').val(),
                    tracking_code: $('#txtCodigoClaveRastreo').val(),
                    process_siaff: $('#txtProcesoSIAFF').val(),
                    transfer_number: $('#txtNumeroTransf').val(),
                    refund_id: $('#selectTipoReintegro').val(),
                    folio_viatics_invoice_transfer: valueSearch,
                    justification: $('#txtJustificacion').val(),
                    issue_date: $('#txtFechaExp').val(),
                    auth_date: $('#txtFechaAut').val(),
                    period: numPeriod,
                    type :Type,
                    transno: Transno,
                    valueTotalG: valueTotal,
                    infoReduct: JSON.stringify(datosSelect),
                    folioselect: JSON.stringify(folioSelect)
                }

            }else{
                if(modeClave <= 1){

                    var arrayData = {
                        fundataup: 'updateData',
                        idStatus: status_de_reintegroUDP,
                        mode_refund: modeClave,
                        ur_id: $('#selectUnidadNegocio').val(),
                        ue_id: $('#selectUnidadEjecutora').val(),
                        lineTesofe:$('#txtLineTesofe').val(),
                        tracking_code: $('#txtCodigoClaveRastreo').val(),
                        process_siaff: $('#txtProcesoSIAFF').val(),
                        transfer_number: $('#txtNumeroTransf').val(),
                        refund_id: $('#selectTipoReintegro').val(),
                        folio_viatics_invoice_transfer: valueSearch,
                        justification: $('#txtJustificacion').val(),
                        issue_date: $('#txtFechaExp').val(),
                        auth_date: $('#txtFechaAut').val(),
                        period: numPeriod,
                        type :Type,
                        transno: Transno,
                        valueTotalG: valueTotal,
                        infoReduct: JSON.stringify(datosSelect),
                        folioselect: JSON.stringify(folioSelect)
                    }

                }
            }

            $.ajax({
                url: "modelo/captura_aviso_reintegros_Modelo.php",
                async: false,
                cache: false,
                method: "POST",
                dataType: "JSON",
                data: arrayData
            }).done(function(result){

                var msgs = '';

                if(result.tipo == 'error') {

                    if(result.message.length > 1){

                        $.each(result.message,function(key,value) {

                            msgs += value.message + "<br/>";
                        })

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                        muestraModalGeneral(3, titulo, mensaje);

                    }else{

                        $.each(result.message,function(key,value) {
                            //msgs += value.message+"'"+",";
                            msgs += value.message + "<br/>";
                        })

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                        muestraModalGeneral(3, titulo, mensaje);

                    }


                }else{
                    if(result.tipo == 'success'){

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.message + '</p>';
                        muestraModalGeneral(3, titulo, mensaje,'','reloadPage();');

                    }
                }

            }).fail(function(error){

                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + error + '</p>';
                muestraModalGeneral(3, titulo, mensaje);
            });

        }else{

            if(valueSearch !='' && datosSelect.length <= 0){

                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>  Debe Seleccionar por lo menos una Clave Presupuestal</p>';
                muestraModalGeneral(3, titulo, mensaje);

            }else{

                if($('#selectTipoReintegro').val() == 1){

                        if(modeClave > 1){

                            var arrayData = {
                                fundata: 'storeData',
                                idStatus: IDstatus,
                                mode_refund: modeClave,
                                ur_id: $('#selectUnidadNegocio').val(),
                                ue_id: $('#selectUnidadEjecutora').val(),
                                lineTesofe:$('#txtLineTesofe').val(),
                                tracking_code: $('#txtCodigoClaveRastreo').val(),
                                process_siaff: $('#txtProcesoSIAFF').val(),
                                transfer_number: $('#txtNumeroTransf').val(),
                                refund_id: $('#selectTipoReintegro').val(),
                                folio_viatics_invoice_transfer: valueSearch,
                                typePayments: $('#selectOperacionTesoreria').val(),
                                justification: $('#txtJustificacion').val(),
                                issue_date: $('#txtFechaExp').val(),
                                auth_date: $('#txtFechaAut').val(),
                                period: numPeriod,
                                type :Type,
                                transno: Transno,
                                valueTotalG: valueTotal,
                                infoReduct: JSON.stringify(datosSelect),
                                folioselect: JSON.stringify(folioSelect)

                            }

                        }else{

                            if(modeClave <= 1){

                                var arrayData = {
                                    fundata: 'storeData',
                                    idStatus: IDstatus,
                                    mode_refund: modeClave,
                                    ur_id: $('#selectUnidadNegocio').val(),
                                    ue_id: $('#selectUnidadEjecutora').val(),
                                    lineTesofe:$('#txtLineTesofe').val(),
                                    tracking_code: $('#txtCodigoClaveRastreo').val(),
                                    process_siaff: $('#txtProcesoSIAFF').val(),
                                    transfer_number: $('#txtNumeroTransf').val(),
                                    refund_id: $('#selectTipoReintegro').val(),
                                    folio_viatics_invoice_transfer: valueSearch,
                                    typePayments: $('#selectOperacionTesoreria').val(),
                                    justification: $('#txtJustificacion').val(),
                                    issue_date: $('#txtFechaExp').val(),
                                    auth_date: $('#txtFechaAut').val(),
                                    period: numPeriod,
                                    type :Type,
                                    transno: Transno,
                                    valueTotalG: valueTotal,
                                    infoReduct: JSON.stringify(datosSelect),
                                    folioselect: JSON.stringify(folioSelect)

                                }

                            }

                        }

                        $.ajax({
                            url: "modelo/captura_aviso_reintegros_Modelo.php",
                            async: false,
                            cache: false,
                            method: "POST",
                            dataType: "JSON",
                            data: arrayData
                        }).done(function(result){

                            var msgs = '';

                            if(result.tipo == 'error') {

                              //  console.log(result.tipo);

                              //  console.log(result.message.length);

                                if(result.message.length > 1){

                                    $.each(result.message,function(key,value) {
                                        msgs += value.message + "<br/>";
                                    })

                                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                                    muestraModalGeneral(3, titulo, mensaje);

                                }else{

                                    $.each(result.message,function(key,value) {
                                        //msgs += value.message+"'"+",";
                                        msgs += value.message + "<br/>";
                                    })

                                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                                    muestraModalGeneral(3, titulo, mensaje);

                                }
                            }else{

                                if(result.tipo == 'success'){


                                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                    var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.message + '</p>';
                                    muestraModalGeneral(3, titulo, mensaje,'','reloadPage();');

                                }
                            }

                        }).fail(function(err){

                            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+ err.responseText+'</p>';
                            muestraModalGeneral(3, titulo, mensaje);

                        });

                }else{

                        if(modeClave > 1){

                            var arrayData = {
                                fundata: 'storeData',
                                idStatus: IDstatus,
                                mode_refund: modeClave,
                                ur_id: $('#selectUnidadNegocio').val(),
                                ue_id: $('#selectUnidadEjecutora').val(),
                                lineTesofe:$('#txtLineTesofe').val(),
                                tracking_code: $('#txtCodigoClaveRastreo').val(),
                                process_siaff: $('#txtProcesoSIAFF').val(),
                                transfer_number: $('#txtNumeroTransf').val(),
                                refund_id: $('#selectTipoReintegro').val(),
                                folio_viatics_invoice_transfer: valueSearch,
                                typePayments: $('#selectOperacionTesoreria').val(),
                                justification: $('#txtJustificacion').val(),
                                issue_date: $('#txtFechaExp').val(),
                                auth_date: $('#txtFechaAut').val(),
                                period: numPeriod,
                                type :Type,
                                transno: Transno,
                                valueTotalG: valueTotal,
                                infoReduct: JSON.stringify(datosSelect),
                                folioselect: JSON.stringify(folioSelect)

                            }

                        }else{

                            if(modeClave <= 1){

                                var arrayData = {
                                    fundata: 'storeData',
                                    idStatus: IDstatus,
                                    mode_refund: modeClave,
                                    ur_id: $('#selectUnidadNegocio').val(),
                                    ue_id: $('#selectUnidadEjecutora').val(),
                                    lineTesofe:$('#txtLineTesofe').val(),
                                    tracking_code: $('#txtCodigoClaveRastreo').val(),
                                    process_siaff: $('#txtProcesoSIAFF').val(),
                                    transfer_number: $('#txtNumeroTransf').val(),
                                    refund_id: $('#selectTipoReintegro').val(),
                                    folio_viatics_invoice_transfer: valueSearch,
                                    typePayments: $('#selectOperacionTesoreria').val(),
                                    justification: $('#txtJustificacion').val(),
                                    issue_date: $('#txtFechaExp').val(),
                                    auth_date: $('#txtFechaAut').val(),
                                    period: numPeriod,
                                    type :Type,
                                    transno: Transno,
                                    valueTotalG: valueTotal,
                                    infoReduct: JSON.stringify(datosSelect),
                                    folioselect: JSON.stringify(folioSelect)

                                }

                            }

                        }

                        $.ajax({
                            url: "modelo/captura_aviso_reintegros_Modelo.php",
                            async: false,
                            cache: false,
                            method: "POST",
                            dataType: "JSON",
                            data: arrayData
                        }).done(function(result){

                            var msgs = '';

                            if(result.tipo == 'error') {

                                if(result.message.length > 1){

                                    $.each(result.message,function(key,value) {
                                        msgs += value.message + "<br/>";
                                    })

                                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                                    muestraModalGeneral(3, titulo, mensaje);

                                }else{

                                    $.each(result.message,function(key,value) {
                                        //msgs += value.message+"'"+",";
                                        msgs += value.message + "<br/>";
                                    })

                                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                                    muestraModalGeneral(3, titulo, mensaje);

                                }
                            }else{

                                if(result.tipo == 'success'){


                                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                    var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.message + '</p>';
                                    muestraModalGeneral(3, titulo, mensaje,'','reloadPage();');

                                }
                            }

                        }).fail(function(err){

                            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+ err.responseText+'</p>';
                            muestraModalGeneral(3, titulo, mensaje);

                        });

                }//fin tipo

            }

        }

    }

}

function reloadPage(){
    location.reload();
}

function viewModalError(msg){

    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>'+msg+'</p>';
    muestraModalGeneral(3, titulo, mensaje);

}

function searchFolioofPanel(){

    var typeOption = 'loadURL';

    $.ajax({
        url: "modelo/captura_aviso_reintegros_Modelo.php",
        async: false,
        cache: false,
        method: "GET",
        dataType: "JSON",
        data: {option: typeOption, idRefund: transnoRef, refundType: typeRef}
    }).done(function(result){
//console.log(result);

        if(result.searchResults.length > 0){

            if(result.searchResults[0].f_id != '' || result.searchResults[0].f_id != null || result.searchResults[0].f_id != 0){

                var contenedorFolio = $('#numero_de_reintegro');
                    contenedorFolio.html("");

                    Folio_Reintegro_Encabezado = result.searchResults[0].f_id;

                   document.getElementById("folio_reintegro").style.display="block";
                   contenedorFolio.html(Folio_Reintegro_Encabezado);

            }


            if(result.searchResults[0].ur != '' || result.searchResults[0].ur != null){
                $('#selectUnidadNegocio').val(result.searchResults[0].ur);
                $('#selectUnidadNegocio').multiselect('rebuild');
            }

            if(result.searchResults[0].ue != '' || result.searchResults[0].ue != null){
                $('#selectUnidadEjecutora').val(result.searchResults[0].ue);
                $('#selectUnidadEjecutora').multiselect('rebuild');
            }

            if(result.searchResults[0].type_payments != '' || result.searchResults[0].type_payments != null){
                $('#selectOperacionTesoreria').val(result.searchResults[0].type_payments);
                $('#selectOperacionTesoreria').multiselect('rebuild');
            }

            if(result.searchResults[0].line_capture_TESOFE != '' || result.searchResults[0].line_capture_TESOFE != null){
                $('#txtLineTesofe').val(result.searchResults[0].line_capture_TESOFE);
            }


            if(result.searchResults[0].tracking_code != '' || result.searchResults[0].tracking_code != null){
                $('#txtCodigoClaveRastreo').val(result.searchResults[0].tracking_code);
            }

          /*  if(result.searchResults[0].process_siaff != '' || result.searchResults[0].process_siaff != null){
                $('#txtProcesoSIAFF').val(result.searchResults[0].process_siaff);
            } */

            if(result.searchResults[0].transfer_number != '' || result.searchResults[0].transfer_number != null){
                $('#txtNumeroTransf').val(result.searchResults[0].transfer_number);
            }

            if(result.searchResults[0].refund_id != '' || result.searchResults[0].refund_id != null){
                $('#selectTipoReintegro').val(result.searchResults[0].refund_id);
                $('#selectTipoReintegro').multiselect('rebuild');
            }

            if(result.searchResults[0].folioRefunds != '' || result.searchResults[0].folioRefunds != null){

               if(result.searchResults[0].refund_id == 1){
                 //  $('#txtFolioTransfADD').val(result.searchResults[0].folioRefunds);
                   $('#txtFolioTransfADD').attr('placeholder',result.searchResults[0].folioRefunds);
                   variosFolios = result.searchResults[0].folioRefunds;

               }else{

                   //$('#txtFolioTransf').val(result.searchResults[0].folioRefunds); result.searchResults[0].type_payments
                   $('#txtFolioTransf').attr('placeholder',result.searchResults[0].folioRefunds);
                   variosFolios = result.searchResults[0].folioRefunds;

                  /* if(result.searchResults[0].refund_id == 2){

                       //$('#txtFolioTransf').val(result.searchResults[0].folioRefunds); result.searchResults[0].type_payments
                       $('#txtFolioTransf').attr('placeholder',result.searchResults[0].folioRefunds);
                       variosFolios = result.searchResults[0].folioRefunds;
                   }else{

                       if(result.searchResults[0].refund_id == 3){
                           //$('#txtFolioTransf').val(result.searchResults[0].folioRefunds); result.searchResults[0].type_payments
                           $('#txtFolioTransf').attr('placeholder',result.searchResults[0].folioRefunds);
                           variosFolios = result.searchResults[0].folioRefunds;
                           tipodePago = result.searchResults[0].type_payments;
                       }

                   }*/

               }

                if(result.searchResults[0].status_refund == 0 || result.searchResults[0].status_refund == 4){
                    viewDetailsRefunds(result.searchResults[0].folioRefunds,0,result.searchResults[0].ur,result.searchResults[0].ue,result.searchResults[0].refund_id,result.searchResults[0].status_refund);
                }
                else{

                    viewDetailsRefunds(result.searchResults[0].folioRefunds,1,result.searchResults[0].ur,result.searchResults[0].ue,result.searchResults[0].refund_id,result.searchResults[0].status_refund);
                }

               /* if(result.searchResults[0].status_refund == 2 && luser == "user.capturista"){
                    viewDetailsRefunds(result.searchResults[0].folioRefunds,1,result.searchResults[0].ur,result.searchResults[0].ue,result.searchResults[0].refund_id);
                    //disabledInputs();
                    disabletypePayment(result.searchResults[0].refund_id);
                }

                if(result.searchResults[0].status_refund == 3 && luser == "user.validador"){
                    viewDetailsRefunds(result.searchResults[0].folioRefunds,1,result.searchResults[0].ur,result.searchResults[0].ue,result.searchResults[0].refund_id);
                    disabletypePayment(result.searchResults[0].refund_id);
                }*/

                /*else{
                    viewDetailsRefunds(result.searchResults[0].folioRefunds,1,result.searchResults[0].ur,result.searchResults[0].ue,result.searchResults[0].refund_id);
                }*/


                if(result.searchResults[0].status_refund != '' || result.searchResults[0].status_refund != null){
                    validStustus = result.searchResults[0].status_refund;
                }

            }

            if(result.searchResults[0].justification != '' || result.searchResults[0].justification != null){
                $('#txtJustificacion').val(result.searchResults[0].justification);
            }

            if(result.searchResults[0].dateStar != '' || result.searchResults[0].dateStar != null){
                $('#txtFechaExp').val(result.searchResults[0].dateStar);
            }

            if(result.searchResults[0].authDate == '' || result.searchResults[0].authDate == null){
                $('#txtFechaAut').val("");
            }

           /* if(result.searchResults[0].status_refund != 0 || result.searchResults[0].status_refund != 4 || result.searchResults[0].status_refund != 1){
                status_de_reintegroUDP = result.searchResults[0].status_refund;
            }*/

            if(result.searchResults[0].status_refund != 1){
                status_de_reintegroUDP = result.searchResults[0].status_refund;
            }

           /* if(result.searchResults[0].cancelDate == '' || result.searchResults[0].cancelDate == null){
                $('#txtFechaAut').val("");
            }*/

           /*if(result.searchResults[0].ttlGeneral != '' || result.searchResults[0].ttlGeneral != null){
                $('#ttalGeneral').val(result.searchResults[0].ttlGeneral);
            }*/

            if(result.searchResults[0].status_refund == 0 || result.searchResults[0].status_refund == 4){

                if(result.searchResults[0].authDate != '' || result.searchResults[0].authDate != null){
                    $('#txtFechaAut').val(result.searchResults[0].authDate);
                }

                disabledInputs(result.searchResults[0].refund_id);
            }


        }


    }).fail(function(err){
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + err + '</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });

}

function disabledInputs(typeRefundD) {

    if(typeRefundD == 1){

        $("#selectOperacionTesoreria").multiselect('disable');
        $("#selectUnidadEjecutora").multiselect('disable');

        $('#selectUnidadNegocio').multiselect('disable');
        $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
        $('#txtProcesoSIAFF').attr('readonly', 'readonly');
        $('#txtNumeroTransf').attr('readonly', 'readonly');
        $('#selectTipoReintegro').multiselect('disable');
        $('#txtJustificacion').attr('readonly', 'readonly');
        $('#txtFolioTransf').attr('readonly', 'readonly');
        $("button[name=btnBusqueda]").attr("disabled", "disabled");
        $('#txtFechaAut').attr('readonly', 'readonly');

        $("button[name=Cancelar]").hide();
        $("button[name=Guardar]").hide();

        document.getElementById("txtSIAFF").style.display="none";

        document.getElementById("lineTESOFE").style.display="block";
        document.getElementById("CodigoRastreo").style.display="block";
        document.getElementById("NumTransfer").style.display="none";

        document.getElementById("textONE").style.display="none";
        document.getElementById("texttwo").style.display="block";

    }else{
        if(typeRefundD == 2){

            $("#selectOperacionTesoreria").multiselect('disable');
            $("#selectUnidadEjecutora").multiselect('disable');

            $('#selectUnidadNegocio').multiselect('disable');
            $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
            $('#txtProcesoSIAFF').attr('readonly', 'readonly');
            $('#txtNumeroTransf').attr('readonly', 'readonly');
            $('#selectTipoReintegro').multiselect('disable');
            $('#txtJustificacion').attr('readonly', 'readonly');
            $('#txtFolioTransf').attr('readonly', 'readonly');
            $("button[name=btnBusqueda]").attr("disabled", "disabled");
            $('#txtFechaAut').attr('readonly', 'readonly');

            $("button[name=Cancelar]").hide();
            $("button[name=Guardar]").hide();

            document.getElementById("txtSIAFF").style.display="none";

            document.getElementById("lineTESOFE").style.display="none";
            document.getElementById("CodigoRastreo").style.display="block";
            document.getElementById("NumTransfer").style.display="block";

            document.getElementById("textONE").style.display="block";
            document.getElementById("texttwo").style.display="none";

        }else{
            if(typeRefundD == 3){

                $("#selectOperacionTesoreria").multiselect('disable');
                $("#selectUnidadEjecutora").multiselect('disable');


                $('#selectUnidadNegocio').multiselect('disable');
                $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
                $('#txtProcesoSIAFF').attr('readonly', 'readonly');
                $('#txtNumeroTransf').attr('readonly', 'readonly');
                $('#selectTipoReintegro').multiselect('disable');
                $('#txtJustificacion').attr('readonly', 'readonly');
                $('#txtFolioTransf').attr('readonly', 'readonly');
                $("button[name=btnBusqueda]").attr("disabled", "disabled");
                $('#txtFechaAut').attr('readonly', 'readonly');

                $("button[name=Cancelar]").hide();
                $("button[name=Guardar]").hide();
                $("button[name=Autorizar]").hide();

                document.getElementById("txtSIAFF").style.display="none";

                document.getElementById("lineTESOFE").style.display="none";
                document.getElementById("CodigoRastreo").style.display="block";
                document.getElementById("NumTransfer").style.display="none";

                document.getElementById("textONE").style.display="block";
                document.getElementById("texttwo").style.display="none";

            }
        }
    }


/*


    $('#selectUnidadNegocio').multiselect('disable');
    $('#selectUnidadEjecutora').multiselect('disable');
    $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
    $('#txtProcesoSIAFF').attr('readonly', 'readonly');
    $('#txtNumeroTransf').attr('readonly', 'readonly');
    $('#selectTipoReintegro').multiselect('disable');
    $('#txtJustificacion').attr('readonly', 'readonly');
    $('#txtFolioTransf').attr('readonly', 'readonly');
    $("button[name=btnBusqueda]").attr("disabled", "disabled");
    $('#txtFechaAut').attr('readonly', 'readonly');

    $("button[name=Cancelar]").hide();
    $("button[name=Guardar]").hide();
    $("button[name=Autorizar]").hide();

    */


}

function searchFolioorTransf(){


    var valueSearch = $('#txtFolioTransf').val();
    var valueUr = $('#selectUnidadNegocio').val();
    var valueUe = $('#selectUnidadEjecutora').val();
    var valuepay = $('#selectOperacionTesoreria').val();
    var valueRf = $('#selectTipoReintegro').val();
    numPeriod = '';

    if(valueSearch == '' || valueSearch == null){

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>  Escriba un Número de Folio o Número de Transferancia</p>';
        muestraModalGeneral(3, titulo, mensaje);

    }else{

        //if(datosSelect.length > 0){
        if(folioSelect.includes(valueSearch) == true){
            var fols = "oculto_"+valueSearch;
            $("#tablaReducciones tbody tr."+fols+"").show();

        }else{

            $.ajax({
                url: "modelo/captura_aviso_reintegros_Modelo.php",
                async:false,
                cache: false,
                method: "GET",
                dataType:"JSON",
                data:{FolioTransf:valueSearch,unitBusiness:valueUr,unitExecuting:valueUe,typepayment:valuepay,typeRefunds:valueRf}

            }).done(function(result){

                if(result.type_refunds == 1){

                    if(result.info.length == 0){

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontro registro del Folio '+ valueSearch +' con los filtros de busqueda o no se encuentra con el estatus de Pagado  </p>';
                        muestraModalGeneral(3, titulo, mensaje);

                      //  $('#txtTotalReducciones').html("0");

                    }else{

                        var selectResults = result.info;

                        idClavePresupuestoReducciones = 0;
                        // datosReducciones = new Array();
                        totalReducciones = 0;

                        for (var key in selectResults) {
                            for (var key2 in selectResults[key]) {

                                for (var mes in dataJsonMeses ) {                         
                                    var nombreMes = dataJsonMeses[mes];                   
                                    selectResults[key][key2][nombreMes+"Sel"] = "0.00";   
                                }                                                         
                                                                                          
                                 selectResults[key][key2]['sequence_siaff'] = "";         

                                var dataJson2 = selectResults[key];
                                fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, 1);
                                datosReducciones.push(dataJson2);
                            }
                        }


                        if(datosReducciones.length > 1){
                            modeClave = datosReducciones.length;
                        }else{
                            if(datosReducciones.length <= 1){
                                modeClave = datosReducciones.length;
                            }
                        }

                        numPeriod = result.period;
                       // Type = result.types;
                       // Transno = result.transno;
                        $('#ttalGeneral').val(result.ttlGeneral);

                    }

                }else{
                    if(result.type_refunds == 3){

                     //   $('#txtTotalReducciones').html("0");
                       // $('#'+tablaReducciones+' tbody').empty();
                        // numLineaReducciones = 1;

                        if(result.info.length == 0){

                            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontro registro del Folio '+ valueSearch +' con los filtros de busqueda  </p>';
                            muestraModalGeneral(3, titulo, mensaje);

                          //  $('#txtTotalReducciones').html("0");

                        }else{

                            var selectResults = result.info;

                            //console.log(selectResults);

                            idClavePresupuestoReducciones = 0;
                           // datosReducciones = new Array();
                            totalReducciones = 0;

                            for (var key in selectResults) {
                                for (var key2 in selectResults[key]) {

                                    for (var mes in dataJsonMeses ) {                         
                                        var nombreMes = dataJsonMeses[mes];                   
                                        selectResults[key][key2][nombreMes+"Sel"] = "0.00";   
                                    }   

                                    var dataJson2 = selectResults[key];
                                    fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, 1);
                                    datosReducciones.push(dataJson2);
                                }
                            }


                            if(datosReducciones.length > 1){
                                modeClave = datosReducciones.length;
                            }else{
                                if(datosReducciones.length <= 1){
                                    modeClave = datosReducciones.length;
                                }
                            }

                            numPeriod = result.period;
                           // Type = result.types;
                           // Transno = result.transno;
                            $('#ttalGeneral').val(result.ttlGeneral);

                            folioSelect.push(valueSearch);


                        }

                    }else{
                        if(result.type_refunds == 2){
                           // $('#'+tablaReducciones+' tbody').empty();
                           // numLineaReducciones = 1;

                            if(result.info.length == 0){

                                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontro registro del Folio '+ valueSearch +' con los filtros de busqueda  </p>';
                                muestraModalGeneral(3, titulo, mensaje);

                              //  $('#txtTotalReducciones').html("0");

                            }else{

                                var selectResults = result.info;

                              //  console.log(selectResults);

                                idClavePresupuestoReducciones = 0;
                               // datosReducciones = new Array();
                                totalReducciones = 0;

                                for (var key in selectResults) {
                                    for (var key2 in selectResults[key]) {

                                        for (var mes in dataJsonMeses ) {                         
                                            var nombreMes = dataJsonMeses[mes];                   
                                            selectResults[key][key2][nombreMes+"Sel"] = "0.00";   
                                        }   

                                        var dataJson2 = selectResults[key];
                                        fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, 1);
                                        datosReducciones.push(dataJson2);
                                    }
                                }

                                 //console.log(datosReducciones);
                                if(datosReducciones.length > 1){
                                    modeClave = datosReducciones.length;
                                }else{
                                    if(datosReducciones.length <= 1){
                                        modeClave = datosReducciones.length;
                                    }
                                }

                                numPeriod = result.period;
                              //  Type = result.types;
                              //  Transno = result.transno;
                                $('#ttalGeneral').val(result.ttlGeneral);
                                folioSelect.push(valueSearch);
                               // selectResults = '';
                            }
                        }
                    }
                    //if Tipo
                }

            }).fail(function(err){
                console.log(err);
            });

        }//fin else


       //alert(valueSearch+"   "+valueUr+"   "+valueUe+"   "+valuepay+"   "+valueRf);



    }

}

function fnMostrarPresupuesto(dataJson, idTabla = 'tablaReducciones', panel = 1, active) {

    var encabezado = '';
    var contenido = '';
    var enca = 1;
    var style = 'style="text-align:center;"';
    var styleMeses = 'style="text-align:center;"';
    var nombreSelect = "";
    var tipoAfectacion = "";
    var clavePresupuesto = "";

    for (var key in dataJson) {

        tipoAfectacion = dataJson[key].tipoAfectacion;
        clavePresupuesto = dataJson[key].accountcode;

        var total = 0;

        for (var mes in dataJsonMeses ) {

            var nombreMes = dataJsonMeses[mes];
            total = parseFloat(total) + parseFloat(dataJson[key][nombreMes+"Sel"]);
        }

        if (idClavePresupuestoReducciones != dataJson[key].idClavePresupuesto) {
            idClavePresupuestoReducciones = dataJson[key].idClavePresupuesto;
            enca = 0;
        }

        totalReducciones += parseFloat(total);

        if (enca == 0) {
            encabezado += '<tr class="header-verde"><td></td><td></td>';
        }

        if (autorizarGeneral == 1) {
            contenido += '<td></td>';
        }else{
            contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\')"></button></td>';
        }

        var deshabilitarElemento = '';
        if (autorizarGeneral == 1) {

            deshabilitarElemento = ' disabled="true" ';
        }

        for (var key2 in dataJson[key].datosClave) {
            if (enca == 0) {
                encabezado += '<td '+style+'>'+dataJson[key].datosClave[key2].nombre+'</td>';
            }
            contenido += '<td '+style+'>'+dataJson[key].datosClave[key2].valor+'</td>';
        }

        if (enca == 0) {

            encabezado += '<td '+styleMeses+'>Total</td>';
        }


        var nombreDivTotal = 'divTotal_'+panel+'_'+dataJson[key].accountcode;
        contenido += '<td '+style+' id="'+nombreDivTotal+'">$ '+formatoComas( redondeaDecimal( 0 ) )+'</td>';

        var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_";
        for (var mes in dataJsonMeses ) {

            var nombreMes = dataJsonMeses[mes];
            var nombreMesSel = dataJsonMeses[mes]+"Sel";
            var nombreMesCompra = dataJsonMeses[mes]+"Compra";
            var cantidadMes = parseFloat(dataJson[key][nombreMes]);
            var cantidadMesOrigen = parseFloat(dataJson[key][nombreMesSel]);
            var nombreReintegro = dataJsonMeses[mes]+"Reintegro";
            var informacionCancelar = "";
            var styleOcultarMes = 'style="text-align:center;"';
            var styleInputText = ' style="width: 80px;" ';





            if (enca == 0) {

                encabezado += '<td '+styleOcultarMes+'>'+nombreMes+'</td>';
            }

            contenido += '<td align="center" '+styleOcultarMes+'>$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreReintegro]) ) ) +'<br>';

            // contenido += '<input '+deshabilitarElemento+' type="text"  min="0" class="form-control" name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" style="width: 80px;" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" /></td>';
            //contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')"></component-decimales>';

            if(Number(dataJson[key][nombreReintegro]) == Number(0)){
                contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" disabled></component-decimales>';

            }else{

                if(active == 1){
                    contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')"></component-decimales>';

                }else{
                    contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" disabled></component-decimales>';

                }

            }

            contenido += '</td>';
        }

        if (enca == 0) {
            encabezado += '</tr>';
        }

        contenido = '<td id="Renglon_'+dataJson[key].accountcode+'_'+panel+'" name="Renglon_'+dataJson[key].accountcode+'_'+panel+'">'+numLineaReducciones+'</td>' + contenido;
        numLineaReducciones = parseFloat(numLineaReducciones) + 1;

        contenido = encabezado + '<tr id="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" name="RenglonTR_'+dataJson[key].accountcode+'_'+panel+'" >' + contenido + '</tr>';

        enca = 1;
    }

   //  fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);

    $('#'+idTabla+' tbody').append(contenido);

    fnEjecutarVueGeneral('RenglonTR_'+clavePresupuesto+'_'+panel);
}

function fnMostrarPresupuesto2(dataJson, idTabla = 'tablaReducciones', panel = 1, active,strefund,lusers) {

    var encabezado = '';
    var contenido = '';
    var enca = 1;
    var style = 'style="text-align:center;"';
    var styleMeses = 'style="text-align:center;"';
    var nombreSelect = "";
    var tipoAfectacion = "";
    var clavePresupuesto = "";
    var sequenceInput = 1;


    for (var key in dataJson) {

        tipoAfectacion = dataJson[key].tipoAfectacion;
        clavePresupuesto = dataJson[key].accountcode;

        var mesRow = parseInt(dataJson[key].mes);
        var total = 0;


        for (var mes in dataJsonMeses) {

            if(mes == mesRow-1){
                var nombreMes = dataJsonMeses[mes];
                total = parseFloat(total) + parseFloat(dataJson[key][nombreMes+"Sel"]);
            }
        }

        if (idClavePresupuestoReducciones != dataJson[key].folioTranfer) {
            idClavePresupuestoReducciones = dataJson[key].folioTranfer;
            enca = 0;
        }

       // console.log(idClavePresupuestoReducciones);
       // console.log(enca);
        totalReducciones += parseFloat(total);

        if (enca == 0) {
            encabezado += '<tr class="header-verde"><td></td>';
        }

      /* if (autorizarGeneral == 1) {
            contenido += '<td></td>';
        }else{
            if(dataJson[key].typeRefund == '1'){
                contenido += '<td></td>';

            }else{
                contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\')"></button></td>';
            }

           contenido += '<td></td>';

        }*/

        var nameIDS = dataJson[key].accountcode+"_"+panel+"_"+nombreMes+dataJson[key].folioTranfer;


        if(enca == 0){
            encabezado += '<td '+styleMeses+'><input type="checkbox" id="all"  onclick="marcar_todos(this);" name="all" checked/><label for="all"></label></td>';
        }


        if (autorizarGeneral == 1) {
            contenido += '<td></td>';
        }else{
            //contenido += '<td><button class="glyphicon glyphicon-remove btn-xs btn-danger" onclick="fnPresupuestoEliminar(\''+dataJson[key].accountcode+'\', \''+panel+'\')"></button></td>';

            contenido += '<td '+style+'>'+
                            '<input type="checkbox" name="check_'+numLineaReducciones+'" id="check_'+numLineaReducciones+'" data-clave="'+dataJson[key].accountcode+'" data-conta="'+numLineaReducciones+'" data-mes="'+nameIDS+'" data-nmes="'+nombreMes+'" data-fol="'+dataJson[key].folioTranfer+'" data-tp="input" onclick="marcar_uno(this);">'+
                            '<label for="check_'+numLineaReducciones+'"></label>'+
                        '</td>';

        }

        var deshabilitarElemento = '';

        if (autorizarGeneral == 1) {

            deshabilitarElemento = ' disabled="true" ';
        }

       ///////////

        if(dataJson[key].typeRefund == '1'){

            var seq = 0;
            var clcgrp = '';
            var clcsicop = '';
            var clcsiaff = '';

            if(enca == 0){

                encabezado += '<td '+styleMeses+'>FOLIO</td>';
                encabezado += '<td '+styleMeses+'>CLC GRP</td>';
                encabezado += '<td '+styleMeses+'>CLC SICOP</td>';
                encabezado += '<td '+styleMeses+'>CLC SIAFF</td>';
                encabezado += '<td '+styleMeses+'>SECUENCIA CLC SIAFF</td>';

            }


            if(dataJson[key].ln_clcGRP == null || dataJson[key].ln_clcGRP == ''){
                clcgrp = '';
            }else{
                clcgrp = dataJson[key].ln_clcGRP;
            }
            if(dataJson[key].ln_clcSicop == null || dataJson[key].ln_clcSicop == ''){
                clcsicop = '';
            }else{
                clcsicop = dataJson[key].ln_clcSicop;
            }
            if(dataJson[key].ln_clcSiaff == null || dataJson[key].ln_clcSiaff == ''){
                clcsiaff = '';
            }else{
                clcsiaff = dataJson[key].ln_clcSiaff;
            }

            contenido += '<td '+style+'>'+dataJson[key].folioTranfer+'</td>';
            contenido += '<td '+style+'>'+clcgrp+'</td>';
            contenido += '<td '+style+'>'+clcsicop+'</td>';
            contenido += '<td '+style+'>'+clcsiaff+'</td>';   // '+Math.abs(dataJson[key][nombreMesSel])+' onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" \''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\'

            if( dataJson[key].sequence_siaff == null || dataJson[key].sequence_siaff == ''){

                contenido += '<td '+style+' align="center">'+
                    '<component-number-label '+styleInputText+deshabilitarElemento+' id="sequence_'+numLineaReducciones+'" name="sequence_'+numLineaReducciones+'"  value="'+seq+'" onchange="addSequence(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\');" disabled></component-number-label>'+
                    '</td>';

            }else{
                contenido += '<td '+style+' align="center">'+
                    '<component-number-label '+styleInputText+deshabilitarElemento+' id="sequence_'+numLineaReducciones+'" name="sequence_'+numLineaReducciones+'"  value="'+dataJson[key].sequence_siaff+'" onchange="addSequence(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\');" disabled></component-number-label>'+
                    '</td>';
            }

        }

        if(dataJson[key].typeRefund == '3'){

            if(enca == 0){

                encabezado += '<td '+styleMeses+'>Folio</td>';

            }

            contenido += '<td '+style+'>'+dataJson[key].folioTranfer+'</td>';

        }

        if(dataJson[key].typeRefund == '2'){

            if(enca == 0){

                encabezado += '<td '+styleMeses+'>Folio</td>';

            }

            contenido += '<td '+style+'>'+dataJson[key].folioTranfer+'</td>';

        }
////////////////////////////

        for (var key2 in dataJson[key].datosClave) {
            if (enca == 0) {
                encabezado += '<td '+style+'>'+dataJson[key].datosClave[key2].nombre+'</td>';
            }
            contenido += '<td '+style+'>'+dataJson[key].datosClave[key2].valor+'</td>';
        }

        if (enca == 0) {

            encabezado += '<td '+styleMeses+'>Total</td>';
        }

        var nombreDivTotal = 'divTotal_'+panel+'_'+dataJson[key].accountcode;
            contenido += '<td '+style+' id="'+nombreDivTotal+'">$ '+formatoComas( redondeaDecimal( 0 ) )+'</td>';

        var nombreInputMeses = dataJson[key].accountcode+"_"+panel+"_";
        var numMes = 1;
        for (var mes in dataJsonMeses ) {

            var nombreMes = dataJsonMeses[mes];
            var nombreMesSel = dataJsonMeses[mes]+"Sel";
            var nombreMesCompra = dataJsonMeses[mes]+"Compra";
            var cantidadMes = parseFloat(dataJson[key][nombreMes]);
            var cantidadMesOrigen = parseFloat(dataJson[key][nombreMesSel]);
            var nombreReintegro = dataJsonMeses[mes]+"Reintegro";
            var informacionCancelar = "";
           // var styleOcultarMes = 'style="text-align:center;"';
            var styleOcultarMes = 'style="display: none;"';   //'
            var styleInputText = ' style="width: 80px;" ';

            var textoMostrar = '';

            if (Number(dataJson[key].mes) == Number(numMes)) {
                styleOcultarMes = 'style="text-align:center;"';
                textoMostrar = nombreMes;
            }

            if (enca == 0) {
               // encabezado += '<td '+styleOcultarMes+'>'+nombreMes+'</td>';
                  encabezado += '<td '+styleOcultarMes+'>'+textoMostrar+'</td>';
            }

            contenido += '<td align="center" '+styleOcultarMes+'>$ '+ formatoComas( redondeaDecimal( parseFloat(dataJson[key][nombreReintegro]) ) ) +'<br>';

            // contenido += '<input '+deshabilitarElemento+' type="text"  min="0" class="form-control" name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" style="width: 80px;" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')" /></td>';
            //contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+'" id="'+nombreInputMeses+nombreMes+'" value="'+Math.abs(dataJson[key][nombreMesSel])+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\')"></component-decimales>';

           // console.log(Number(dataJson[key][nombreReintegro])); redondeaDecimal( parseFloat( Math.abs(dataJson[key][nombreMesSel])
         //   console.log("otrossssss : ",dataJson[key][nombreMesSel],Number(dataJson[key].mes),numMes);
            if(Number(dataJson[key].mes) != Number(numMes)){
                   //console.log("Folio If: "+dataJson[key].folioTranfer+":::"+"Cantidad: "+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel]))));

                    if(dataJson[key].typeRefund == '1'){
                        contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" id="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" value="'+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel])))+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+dataJson[key].folioTranfer+'\')" disabled></component-decimales>';

                    }else{
                      contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" id="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" value="'+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel])))+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+dataJson[key].folioTranfer+'\')" disabled></component-decimales>';
                    }
            }else{
                if(active == 1){
                    if(dataJson[key].typeRefund == '1'){
                        contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" id="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" value="'+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel])))+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+dataJson[key].folioTranfer+'\')" disabled></component-decimales>';

                    }else{
                        contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" id="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" value="'+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel])))+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+dataJson[key].folioTranfer+'\')" disabled></component-decimales>';

                    }


                }else{
                    if(dataJson[key].typeRefund == '1'){
                        contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" id="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" value="'+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel])))+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+dataJson[key].folioTranfer+'\')" disabled></component-decimales>';
                    }else{
                        contenido += '<component-decimales '+styleInputText+deshabilitarElemento+' name="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" id="'+nombreInputMeses+nombreMes+dataJson[key].folioTranfer+'" value="'+formatoComas( redondeaDecimal(parseFloat(dataJson[key][nombreMesSel])))+'" onchange="fnGuardarSeleccionado(\''+dataJson[key].accountcode+'\', this, \''+panel+'\', \'input\', \''+dataJson[key].folioTranfer+'\')" disabled></component-decimales>';

                    }


                }
            }

            contenido += '</td>';
            numMes ++;
        }

        if (enca == 0) {
            encabezado += '</tr>';
        }


        contenido = '<td id="Renglon_'+dataJson[key].accountcode+'_'+panel+dataJson[key].folioTranfer+'" name="Renglon_'+dataJson[key].accountcode+'_'+panel+dataJson[key].folioTranfer+'">'+numLineaReducciones+'</td>' + contenido;
        numLineaReducciones = parseFloat(numLineaReducciones) + 1;

        contenido = encabezado + '<tr id="RenglonTR_'+dataJson[key].accountcode+'_'+panel+dataJson[key].folioTranfer+'" name="RenglonTR_'+dataJson[key].accountcode+'_'+panel+dataJson[key].folioTranfer+'" >' + contenido + '</tr>';
        enca = 1;
    }

  //  console.log(contenido);
    //fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
    $('#'+idTabla+' tbody').append(contenido);
    fnEjecutarVueGeneral('RenglonTR_'+clavePresupuesto+'_'+panel+dataJson[key].folioTranfer);

}

function fnGuardarSeleccionado(clavePresupuesto, input, panel, inputSelect,foliosT) {

   // alert(clavePresupuesto+"  Nombre Input: "+input.name+" Nombre Panel: "+panel+" Nombre tipo: "+inputSelect+":::"+foliosT);

    statusGuardar = 1;

    var dataJson = new Array();
    var tipoMovimiento = "";

   // dataJson = datosReducciones;

    dataJson = datosSelect;

  //  console.log(datosSelect);
 //   console.log(dataJson);

    if(inputSelect == 'input') {
        totalReducciones = 0;
    }

    tipoMovimiento = "Reduccion";

    for (var key in dataJson) {
        for (var key2 in dataJson[key]) {
            var dataJson2 = dataJson[key];

            if (dataJson2.accountcode == clavePresupuesto && dataJson2.folioTranfer == foliosT) {

                var nombreInput = input.name.split("_");

                //var nm = nombreInput[2].substring(0, nombreInput[2].length - 2);
                var nm = nombreInput[2].split(nombreInput[2].match(/\d+/g))[0];
              //  console.log(nm);
                for (var mes in dataJsonMeses) {

                    var nombreMes = dataJsonMeses[mes];
                    if (nombreMes == nm) {
                        var totalMesSuf = parseFloat(dataJson2[nombreMes]);
                        dataJson2[nombreMes+"Sel"] = (input.value != "" ? input.value : 0);
                        break;
                    }

                }


            }


         /*   var mesRow = parseInt(dataJson2.mes);

            if(inputSelect == 'input') {
                totalReducciones= 0;
                var total = 0;
                for (var mes in dataJsonMeses ) {
                    // Nombres de los mes

                    if(mes == mesRow-1){
                        var nombreMes = dataJsonMeses[mes];
                        total = parseFloat(total) + parseFloat(dataJson2[nombreMes+"Sel"]);
                    }

                }
                totalReducciones +=parseFloat(total);
            }//*/
        }
    }

   // datosReducciones = dataJson;
    datosSelect = dataJson;
    fnsumarSeleccion();
   // fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
   // fnCalcularTotalesClaveRenglon2();
  //  console.log(datosSelect);
}

function fnMostrarTotalAmpRed(divNombre, total) {
    $('#'+divNombre).empty();
    $('#'+divNombre).html(""+ formatoComas( redondeaDecimal( total ) ) );
}

function fnCalcularTotalesClaveRenglon2() {

    var mesesTotales = new Array();
    for (var key in datosSelect) {
        for (var key2 in datosSelect[key]) {
            var dataJsonReducciones = datosSelect[key];

            var totalClave = 0;
            var num = 1;
            for (var mes in dataJsonMeses ) {

                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                if (!Number(mesesTotales[num])) {
                    mesesTotales[num] = 0;
                }

                mesesTotales[num] = parseFloat(mesesTotales[num]) + parseFloat(dataJsonReducciones[nombreMesSel]);
                num ++;
               // totalClave += parseFloat(dataJsonReducciones[nombreMesSel]);

             //   totalClave = parseFloat(totalClave) + parseFloat(dataJsonReducciones[nombreMesSel]);

                totalClave +=  eval(dataJsonReducciones[nombreMesSel].replace(',','').replace(',',''));


            }


            var nombreDivTotal = 'divTotal_'+panelReducciones+'_'+dataJsonReducciones.accountcode;
            $("#"+nombreDivTotal).empty();
            $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );
        }



    }


    if (datosSelect.length == 0) {

        totalReducciones = 0;
       // fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
    }


    var encabezado = '<tr class="header-verde">';
    var contenido = '</tr>';
    encabezado += '<td>Total</td>';
    contenido += '<td>$ '+formatoComas( redondeaDecimal( totalReducciones ) )+'</td>';
    var num = 1;
    for (var mes in dataJsonMeses ) {
        var nombreMes = dataJsonMeses[mes];
        if (mesesTotales[num] == '' || mesesTotales[num] == null || typeof mesesTotales[num] == 'undefined') {
            // Si no tiene información
            mesesTotales[num] = 0;
        }
        encabezado += '<td>'+nombreMes+'</td>';
        contenido += '<td>$ '+formatoComas( redondeaDecimal( mesesTotales[num] ) )+'</td>';
        num ++;
    }
    encabezado += '</tr>';
    contenido += '</tr>';

    $('#tablaReduccioneTotales tbody').empty();
    $('#tablaReduccioneTotales tbody').append(encabezado + contenido);
}

function fnCalcularTotalesClaveRenglon() {

    var mesesTotales = new Array();
    for (var key in datosReducciones ) {
        for (var key2 in datosReducciones[key]) {
            var dataJsonReducciones = datosReducciones[key];

            var totalClave = 0;
            var num = 1;
            for (var mes in dataJsonMeses ) {

                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";
                if (!Number(mesesTotales[num])) {
                    mesesTotales[num] = 0;
                }
                mesesTotales[num] = parseFloat(mesesTotales[num]) + parseFloat(dataJsonReducciones[key2][nombreMesSel]);
                num ++;
                totalClave = parseFloat(totalClave) + parseFloat(dataJsonReducciones[key2][nombreMesSel]);
            }

            var nombreDivTotal = 'divTotal_'+panelReducciones+'_'+dataJsonReducciones[key2].accountcode;
            $("#"+nombreDivTotal).empty();
            $("#"+nombreDivTotal).append('$ '+ formatoComas( redondeaDecimal( totalClave ) ) );
        }
    }

    if (datosReducciones.length == 0) {

        totalReducciones = 0;
      //  fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
    }


    var encabezado = '<tr class="header-verde">';
    var contenido = '</tr>';
    encabezado += '<td>Total</td>';
    contenido += '<td>$ '+formatoComas( redondeaDecimal( totalReducciones ) )+'</td>';
    var num = 1;
    for (var mes in dataJsonMeses ) {
        var nombreMes = dataJsonMeses[mes];
        if (mesesTotales[num] == '' || mesesTotales[num] == null || typeof mesesTotales[num] == 'undefined') {
            // Si no tiene información
            mesesTotales[num] = 0;
        }
        encabezado += '<td>'+nombreMes+'</td>';
        contenido += '<td>$ '+formatoComas( redondeaDecimal( mesesTotales[num] ) )+'</td>';
        num ++;
    }
    encabezado += '</tr>';
    contenido += '</tr>';
    $('#tablaReduccioneTotales tbody').empty();
    $('#tablaReduccioneTotales tbody').append(encabezado + contenido);
}

function viewDetailsRefunds(valueSearch,actives,urD,ueD,typesRefunds,statusRefund) {

    var valueSearch = valueSearch;

   // console.log(valueSearch);
    $.ajax({
        url: "modelo/captura_aviso_reintegros_Modelo.php",
        async: false,
        cache: false,
        method: "GET",
        dataType: "JSON",
        data: {FolioTransfNoCapture: valueSearch, idRefund: transnoRef, refundType: typeRef, unitBusiness:urD, unitExecuting: ueD, typeunitRefund:typesRefunds, tpg:tipodePago}
    }).done(function(result){

        var selectResults = result.info;

      //  console.log("Datos ::: "+result.info);
        idClavePresupuestoReducciones = 0;
        datosReducciones = new Array();
        totalReducciones = 0;

        if(typesRefunds == 1){



            for (var key in selectResults) {
                for (var key2 in selectResults[key]) {
                    var dataJson2 = selectResults[key];
                    fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, actives, statusRefund,luser);
                    datosReducciones.push(dataJson2);
                }
            }

            folioSelect = result.foliosData;


          if(statusRefund == 4){
              marcar_todosINIC(false,typesRefunds);
          }else{
              marcar_todosINI(true);
          }

            fnsumarSeleccion();

        }else{



            if(typesRefunds == 2){

                for (var key in selectResults) {
                    for (var key2 in selectResults[key]) {
                        var dataJson2 = selectResults[key];
                        fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, actives, statusRefund,luser);
                        datosReducciones.push(dataJson2);
                    }
                }

                folioSelect = result.foliosData;
                if(statusRefund == 4){
                    marcar_todosINIC(false,typesRefunds);
                }else{

                    marcar_todosINI(true);
                }

                fnsumarSeleccion();

            }else{
                if(typesRefunds == 3){

                  // console.log(selectResults);

                    for (var key in selectResults) {
                        for (var key2 in selectResults[key]) {
                            var dataJson2 = selectResults[key];
                            fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, actives, statusRefund,luser);
                            datosReducciones.push(dataJson2);
                        }
                    }
                    folioSelect = result.foliosData;
                    if(statusRefund == 4){
                        marcar_todosINIC(false,typesRefunds);
                    }else{
                        marcar_todosINI(true);
                    }

                    fnsumarSeleccion();


                }
            }

        }

       //numPeriod = result.period;
        Type = result.types;
        Transno = result.transno;
        //$('#ttalGeneral').val(result.ttlGeneral);


    }).fail(function(error){
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>'+ error +'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });
}

function fnPresupuestoEliminar(clave, panel, sinConfirmacion=0) {

    if (sinConfirmacion == 0) {
        var tipo = "Reducciones";
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p>Se va a eliminar la Clave Presupuestal '+clave+'</p>';
        muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnPresupuestoEliminar(\''+clave+'\',\''+panel+'\',\'1\')');
        return false;
    }

    var obj = new Object();
    obj.type = typeRef;
    obj.transno = transnoRef;
    obj.clave = clave;
    datosEliminarRedAmp.push(obj);

    fnEliminarRenglon(clave, panel);
}

function fnEliminarRenglon(clave, panel) {

    for (var key in datosReducciones ) {
        for (var key2 in datosReducciones[key]) {
            var dataJsonReducciones = datosReducciones[key];
            if (dataJsonReducciones[key2].accountcode == clave) {
                datosReducciones.splice(key, 1);
                break;
            }
        }
    }

    if (datosReducciones.length == 0) {
        $("button[name=Guardar]").attr("disabled", "disabled");
    }

    fnRecargarDatosPaneles();

}

function fnRecargarDatosPaneles() {

    $('#'+tablaReducciones+' tbody').empty();

    numLineaReducciones = 1;
    idClavePresupuestoReducciones = 0;
    totalReducciones = 0;

  //  fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);

    for (var key in datosReducciones ) {
        fnMostrarPresupuesto2(datosReducciones[key], tablaReducciones, panelReducciones,1);
    }

    //fnDeshabilitarMesesAntes();

    //fnCambioFecha();

    if (datosReducciones.length == 0 && usuarioOficinaCentral != 1) {
        // Si agrego datos deshabilitar UE
        // $('#selectUnidadEjecutora').multiselect('enable');
    }

    fnCalcularTotalesClaveRenglon();
}

function fnDeshabilitarMesesAntes(mesInicio = 0, mesFin = 0) {

    var mensaje = "";
    for (var key in datosReducciones ) {
        for (var key2 in datosReducciones[key]) {
            var dataJsonReducciones = datosReducciones[key];
            var numMes = 1;
            for (var mes in dataJsonMeses ) {
                var nombreMes = dataJsonMeses[mes];
                var nombreMesSel = dataJsonMeses[mes]+"Sel";

                if (autorizarGeneral == 1) {
                    $("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
                } else if (
                    (Number(mesActualAdecuacion) > Number(numMes))
                    ||
                    ((Number(numMes) < Number(mesInicio)) && (Number(mesInicio) != Number(0) && Number(mesFin) != Number(0)))
                    ||
                    ((Number(numMes) > Number(mesFin)) && (Number(mesInicio) != Number(0) && Number(mesFin) != Number(0)))
                ) {
                    totalReducciones = parseFloat(totalReducciones) - parseFloat(dataJsonReducciones[key2][nombreMesSel]);
                   // fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
                    dataJsonReducciones[key2][nombreMesSel] = 0;
                    $("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).val("0");
                    $("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", true);
                } else {
                    $("#"+dataJsonReducciones[key2].accountcode+"_"+panelReducciones+"_"+nombreMes).prop("disabled", false);
                }
                numMes ++;
            }
        }
    }

    return mensaje;
}

function fnCambioFecha() {

    var res1 = $("#txtFechaInicio").val().split("-");
    var res2 = $("#txtFechaFinal").val().split("-");

    if (res1.length == 3 && res2.length == 3) {

        if (Number(res1[2]) > Number(yearActualAdecuacion)) {
            res1[1] = 12;
        }
        if (Number(res2[2]) > Number(yearActualAdecuacion)) {
            res2[1] = 12;
        }
        fnDeshabilitarMesesAntes(res1[1], res2[1]);
    } else {
        // Realizar en tiempo
        // setTimeout(function (){
        // 	fnCambioFecha();
        // }, 2000);
    }
}

function ConfirmforAuthorizeRefunds(statusid){
   /* if (seleccionoCaptura == 0) {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else if (estatusDiferentes == 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
    }else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p>¿Desea Poner el estatus Por Autorizar los Folios Seleccionados?</p>';
        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
    }*/

  /* if($('#selectTipoReintegro').val() == 1){
       var fol = variosFolios;
   } else {
       var fol = $('#txtFolioTransf').val();
   } */

    if($('#selectTipoReintegro').val() == 1){
        var fol = Folio_Reintegro_Encabezado;
    } else {
        var fol = Folio_Reintegro_Encabezado;
    }


    if($('#selectTipoReintegro').val() == 1){

        if($('#txtLineTesofe').val() == '' && $('#txtCodigoClaveRastreo').val() == ''){

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar Linea de Captura TESOFE y Codigo de Rastreo  </p>';
            muestraModalGeneral(3, titulo, mensaje);
        }else{
            if($('#txtLineTesofe').val() == '' && $('#txtCodigoClaveRastreo').val() != ''){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar Linea de Captura TESOFE</p>';
                muestraModalGeneral(3, titulo, mensaje);
            }else{
                if($('#txtLineTesofe').val() != '' && $('#txtCodigoClaveRastreo').val() == ''){
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar el Codigo de Rastreo</p>';
                    muestraModalGeneral(3, titulo, mensaje);
                }else{

                    if($('#txtLineTesofe').val() != '' && $('#txtCodigoClaveRastreo').val() != ''){

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje = '<p>¿Desea Autorizar el reintegro con Folio:  '+fol+' ? </p>';
                        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "UpdateStatus('"+statusid+"')");
                    }
                }
            }
        }
       // if()
    }else{
        if($('#selectTipoReintegro').val() == 2){


            if($('#txtCodigoClaveRastreo').val() == '' && $('#txtNumeroTransf').val() == ''){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar el Codigo de Rastreo y Numero de Transferencia </p>';
                muestraModalGeneral(3, titulo, mensaje);
            }else{
                if($('#txtCodigoClaveRastreo').val() == '' && $('#txtNumeroTransf').val() != ''){

                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar el Codigo de Rastreo </p>';
                    muestraModalGeneral(3, titulo, mensaje);

                }else{
                    if($('#txtCodigoClaveRastreo').val() != '' && $('#txtNumeroTransf').val() == ''){

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar el Numero de Transferencia </p>';
                        muestraModalGeneral(3, titulo, mensaje);

                    }else{
                        if($('#txtCodigoClaveRastreo').val() != '' && $('#txtNumeroTransf').val() != ''){

                            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                            var mensaje = '<p>¿Desea Autorizar el reintegro con Folio:  '+fol+' ? </p>';
                            muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "UpdateStatus('"+statusid+"')");

                        }
                    }
                }

            }
        }else{
            if($('#selectTipoReintegro').val() == 3){
               if($('#txtCodigoClaveRastreo').val() == ''){
                   var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                   var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> Para poder autorizar el Reintegro con Folio:  '+fol+'  necesita Capturar el Codigo de Rastreo </p>';
                   muestraModalGeneral(3, titulo, mensaje);
               }else{
                   if($('#txtCodigoClaveRastreo').val() != ''){
                       var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                       var mensaje = '<p>¿Desea Autorizar el reintegro con Folio:  '+fol+' ? </p>';
                       muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "UpdateStatus('"+statusid+"')");
                   }
               }
            }
        }


    }

}

function UpdateStatus(statusID){

    if($('#selectTipoReintegro').val() == 1){

        //$('#txtFolioTransfADD').val();

        var valueSearch = folioSelect;
    }else{

        if($('#selectTipoReintegro').val() == 2){
            var valueSearch = folioSelect;
        }else{
            if($('#selectTipoReintegro').val() == 3){
                var valueSearch = folioSelect;
            }
        }

        //var valueSearch = $('#txtFolioTransf').val();
    }

   // var valueSearch = $('#txtFolioTransf').val();
    var valueUr = $('#selectUnidadNegocio').val();
    var valueUe = $('#selectUnidadEjecutora').val();
    var valuepay = $('#selectOperacionTesoreria').val();
    var valueRf = $('#selectTipoReintegro').val();

    var codigoRastreo = $('#txtCodigoClaveRastreo').val();
    var claveTESOFE = $('#txtLineTesofe').val();
    var numeroTran = $('#txtNumeroTransf').val();

   // dataJsonNoCapturaSeleccionados: JSON.stringify(datosSelect),

    ObjData = {
        optionUpdateStatus: 'UpdateStatusRefundsAuth',
        dataJsonNoCapturaSeleccionados: datosSelect,
        statusid: statusID,
        folio: valueSearch,
        tipo: valueRf,
        pago: valuepay,
        ur: valueUr,
        ue: valueUe,
        transno:transnoRef,
        cr:codigoRastreo,
        ct:claveTESOFE,
        nt:numeroTran
    };


    // Ajax Actualizar Cancelar
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/captura_aviso_reintegros_Modelo.php",
        data:{
            statusid: statusID,
            folio: valueSearch,
            tipo: valueRf,
            pago: valuepay,
            ur: valueUr,
            ue: valueUe,
            transno:transnoRef,
            cr:codigoRastreo,
            ct:claveTESOFE,
            nt:numeroTran,
            optionUpdateStatus: 'UpdateStatusRefundsAuth',
            dataJsonNoCapturaSeleccionados: datosSelect

        }
    }).done(function (result){

    /*   if(result.status == 'error'){
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+result.message+'</p>';
            muestraModalGeneral(3, titulo, mensaje);

        }else{
            if(result.status == 'success'){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.message + '</p>';
                muestraModalGeneral(3, titulo, mensaje,'','reloadPage();');
            }
        }

       */

        var msgs = '';

        if(result.status == 'error') {

            //  console.log(result.tipo);

            //  console.log(result.message.length);

            if(result.message.length > 1){

                $.each(result.message,function(key,value) {
                    msgs += value.message + "<br/>";
                })

                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                muestraModalGeneral(3, titulo, mensaje);

            }else{

                $.each(result.message,function(key,value) {
                    //msgs += value.message+"'"+",";
                    msgs += value.message + "<br/>";
                })

                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> ' + msgs + '</p>';
                muestraModalGeneral(3, titulo, mensaje);

            }
        }else{

            if(result.status == 'success'){


                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.message + '</p>';
                muestraModalGeneral(3, titulo, mensaje,'','reloadPage();');

            }
        }


    }).fail(function (error){
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+error+'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });

}

function searchFolioorTransfADD(){

    var valueSearch = $('#txtFolioTransfADD').val();
    var valueUr = $('#selectUnidadNegocio').val();
    var valueUe = $('#selectUnidadEjecutora').val();
    var valuepay = $('#selectOperacionTesoreria').val();
    var valueRf = $('#selectTipoReintegro').val();
    numPeriod = '';

    if(valueSearch == '' || valueSearch == null){

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>  Escriba un Número de Folio o Número de Transferancia</p>';
        muestraModalGeneral(3, titulo, mensaje);

    }else{ // Inicio Busqueda

        if(folioSelect.includes(valueSearch) == true) {

            var fols = "oculto_"+valueSearch;
            $("#tablaReducciones tbody tr."+fols+"").show();

        }else{

            $.ajax({
                url: "modelo/captura_aviso_reintegros_Modelo.php",
                async:false,
                cache: false,
                method: "GET",
                dataType:"JSON",
                data:{FolioTransf:valueSearch,unitBusiness:valueUr,unitExecuting:valueUe,typepayment:valuepay,typeRefunds:valueRf}

            }).done(function(result){

                if(result.type_refunds == 1){

                    if(result.info.length == 0){

                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se encontro registro del Folio '+ valueSearch +' con los filtros de busqueda  </p>';
                        muestraModalGeneral(3, titulo, mensaje);

                        //$('#txtTotalReducciones').html("0");

                    }else{

                        var selectResults = result.info;

                        idClavePresupuestoReducciones = 0;
                        // datosReducciones = new Array();
                        totalReducciones = 0;

                        for (var key in selectResults) {
                            for (var key2 in selectResults[key]) {

                                for (var mes in dataJsonMeses ) {                     
                                    var nombreMes = dataJsonMeses[mes];               
                                    selectResults[key][key2][nombreMes+"Sel"] = "0.00"
                                }                                                     
                                
                                selectResults[key][key2]['sequence_siaff'] = "";    
                                
                                var dataJson2 = selectResults[key];
                                fnMostrarPresupuesto2(dataJson2, 'tablaReducciones', 1, 1);
                                datosReducciones.push(dataJson2);
                            }
                        }


                        if(datosReducciones.length > 1){
                            modeClave = datosReducciones.length;
                        }else{
                            if(datosReducciones.length <= 1){
                                modeClave = datosReducciones.length;
                            }
                        }

                        numPeriod = result.period;
                        //Type = result.types;
                        //Transno = result.transno;
                        $('#ttalGeneral').val(result.ttlGeneral);

                        folioSelect.push(valueSearch);

                    }

                }

            }).fail(function(err){
                console.log(err);
            });


        }//en array

    } // fin Else

}

function addSequence(clavePresupuesto, input, panel, inputSelect){

    statusGuardar = 1;

    var dataJson = new Array();
    var tipoMovimiento = "";

   // dataJson = datosReducciones;

    dataJson = datosSelect;

    if(inputSelect == 'input') {
        totalReducciones = 0;
    }

    tipoMovimiento = "Reduccion";

    for (var key in dataJson) {
        for (var key2 in dataJson[key]) {
            var dataJson2 = dataJson[key];

            if (dataJson2.accountcode == clavePresupuesto) {

                dataJson2['sequence_siaff'] = (input.value != "" ? input.value : 0);

            }

        }
    }

   // datosReducciones = dataJson;
    datosSelect = dataJson;
   // fnMostrarTotalAmpRed('txtTotalReducciones', totalReducciones);
   // fnCalcularTotalesClaveRenglon();

}

function marcar_todos(source){

    checkboxes=document.getElementsByTagName('input');

    datosSelect=[];

    for(i=0;i<checkboxes.length;i++){

        if(checkboxes[i].type == "checkbox"){
            checkboxes[i].checked=source.checked;
            var clv = $(checkboxes[i]).data('clave');
            var Tp = $(checkboxes[i]).data('tp');

            var conta = $(checkboxes[i]).data('conta');
            var nameMesID = $(checkboxes[i]).data('mes');

            statusGuardar = 1;

            var dataJson = new Array();
            var tipoMovimiento = "";

            dataJson = datosReducciones;

            if(Tp == 'input') {
                totalReducciones = 0;
            }

            tipoMovimiento = "Reduccion";

            for (var key in dataJson) {
                for (var key2 in dataJson[key]) {
                    var dataJson2 = dataJson[key];

                    if (dataJson2[key2].accountcode == clv) {

                        dataJson2[key2]['activo'] = "activo";
                        datosSelect.push(dataJson2[key2]);
                    }

                }

            }

            $('#sequence_'+conta).removeAttr("disabled");
            $('#'+nameMesID).removeAttr("disabled");

            $('#'+nameMesID).addClass("suma");
        }



        if(!source.checked){

            var clv = $(checkboxes[i]).data('clave');
            var Tp = $(checkboxes[i]).data('tp');


            var conta = $(checkboxes[i]).data('conta');
            var nameMesID = $(checkboxes[i]).data('mes');

            for (var key in datosSelect ) {


                for (var key2 in datosSelect[key]) {
                    var dataJsonReducciones = datosSelect[key];
                    if (dataJsonReducciones.accountcode == clv) {
                        datosSelect.splice(key, 1);
                        break;
                    }
                }
            }


            $('#sequence_'+conta).attr("disabled", 'disabled');
            $('#'+nameMesID).attr("disabled", 'disabled');
            $('#'+nameMesID).removeClass("suma");
        }
    }

    //datosReducciones = dataJson;
   // datosSelect = dataJson;

    fnsumarSeleccion();
}

function marcar_uno(activo){

    var clv = $(activo).data('clave');
    var Tp = $(activo).data('tp');

    var conta = $(activo).data('conta');
    var nameMesID = $(activo).data('mes');
    var fols = $(activo).data('fol');

    if(activo.checked){

        statusGuardar = 1;

        var dataJson = new Array();
        var tipoMovimiento = "";

        dataJson = datosReducciones;

        if(Tp == 'input') {
            totalReducciones = 0;
        }

        tipoMovimiento = "Reduccion";

        for (var key in dataJson) {
            for (var key2 in dataJson[key]) {
                var dataJson2 = dataJson[key];

                if (dataJson2[key2].accountcode == clv && dataJson2[key2].folioTranfer == fols) {
                    //dataJson2[key2]['activo'] = "activo";
                    datosSelect.push(dataJson2[key2]);
                }

            }
        }

        $('#sequence_'+conta).removeAttr("disabled");
        $('#'+nameMesID).removeAttr("disabled");
        $('#'+nameMesID).addClass("suma");

    }else{
        if(!activo.checked){

            for (var key in datosSelect ) {


                for (var key2 in datosSelect[key]) {
                    var dataJsonReducciones = datosSelect[key];
                    if (dataJsonReducciones.accountcode == clv) {
                        datosSelect.splice(key, 1);
                        break;
                    }
                }
            }

            $('#sequence_'+conta).attr("disabled", 'disabled');
            $('#'+nameMesID).attr("disabled", 'disabled');
            $('#'+nameMesID).removeClass("suma");
        }

    }
    fnsumarSeleccion();

}

function marcar_todosINI(source){

    checkboxes=document.getElementsByTagName('input');

    datosSelect=[];

    for(i=0;i<checkboxes.length;i++){

        if(checkboxes[i].type == "checkbox"){
            checkboxes[i].checked=source;
            var clv = $(checkboxes[i]).data('clave');
            var Tp = $(checkboxes[i]).data('tp');

            var conta = $(checkboxes[i]).data('conta');
            var nameMesID = $(checkboxes[i]).data('mes');



            var MesActual = $(checkboxes[i]).data('nmes');

            var folios = $(checkboxes[i]).data('fol');

            statusGuardar = 1;

            var dataJson = new Array();
            var tipoMovimiento = "";

            dataJson = datosReducciones;

//console.log(dataJson);

            if(Tp == 'input') {
                totalReducciones = 0;
            }

            tipoMovimiento = "Reduccion";

            for (var key in dataJson) {
                for (var key2 in dataJson[key]) {
                    var dataJson2 = dataJson[key];

                    if (dataJson2[key2].accountcode == clv && dataJson2[key2][MesActual+'Sel'] > 0 && dataJson2[key2].folioTranfer == folios ) {

                        dataJson2[key2]['activo'] = "activo";
                        datosSelect.push(dataJson2[key2]);
                       // console.log("+++ Clave ++++");
                       // console.log(datosSelect);

                    }

                }

            }


            if($('#'+nameMesID).val() <= 0){

                var renglon = "Renglon_"+clv+"_1";

                $('#'+nameMesID).attr("disabled", 'disabled');
                $('#sequence_'+conta).attr("disabled", 'disabled');

                checkboxes[i].checked=false;

                 //$('#'+renglon).closest('tr').hide();
                 //$('#'+renglon).closest('tr').addClass('oculto_'+folios);


            }else{
                $('#'+nameMesID).removeAttr("disabled");
                $('#sequence_'+conta).removeAttr("disabled");
                $('#'+nameMesID).addClass("suma");

                checkboxes[i].checked=source;

            }

        }



        if(!source){

            var clv = $(checkboxes[i]).data('clave');
            var Tp = $(checkboxes[i]).data('tp');

            var conta = $(checkboxes[i]).data('conta');
            var nameMesID = $(checkboxes[i]).data('mes');

            for (var key in datosSelect ) {

                for (var key2 in datosSelect[key]) {
                    var dataJsonReducciones = datosSelect[key];
                    if (dataJsonReducciones.accountcode == clv) {
                        datosSelect.splice(key, 1);
                        break;
                    }
                }
            }

            $('#sequence_'+conta).attr("disabled", 'disabled');
            $('#'+nameMesID).attr("disabled", 'disabled');
            $('#'+nameMesID).removeClass("suma");

        }
    }

    fnsumarSeleccion();

}

function marcar_todosINIC(source,tpy){

    checkboxes=document.getElementsByTagName('input');

    datosSelect=[];

    for(i=0;i<checkboxes.length;i++){

        if(!source){

                if(tpy == 3){

                    var nameMesID = $(checkboxes[i]).data('mes');

                    checkboxes[i].checked=false;
                    checkboxes[i].disabled=true;
                    $('#'+nameMesID).attr("disabled", 'disabled');
                    $('#'+nameMesID).addClass("suma");
                    fnsumarSeleccion();
                   // $('#'+nameMesID).removeClass("suma");

                }else{
                    if(tpy == 2){

                        var nameMesID = $(checkboxes[i]).data('mes');

                        checkboxes[i].checked=false;
                        checkboxes[i].disabled=true;
                        $('#'+nameMesID).attr("disabled", 'disabled');
                        $('#'+nameMesID).addClass("suma");
                        fnsumarSeleccion();
                       // $('#'+nameMesID).removeClass("suma");

                    }else{
                        if(tpy == 1){

                            var clv = $(checkboxes[i]).data('clave');
                            var Tp = $(checkboxes[i]).data('tp');


                            var conta = $(checkboxes[i]).data('conta');
                            var nameMesID = $(checkboxes[i]).data('mes');

                            checkboxes[i].checked=false;
                            checkboxes[i].disabled=true;
                            $('#sequence_'+conta).attr("disabled", 'disabled');
                            $('#'+nameMesID).addClass("suma");
                            fnsumarSeleccion();
                            $('#'+nameMesID).attr("disabled", 'disabled');
                         //  $('#'+nameMesID).removeClass("suma");
                          //  fnsumarSeleccion();

                        }
                    }
                }


        }
    }

    //datosReducciones = dataJson;
    // datosSelect = dataJson;
}

function permissionUsers(reintegro,tipoDocumento){
    //alert(reintegro+"   "+tipoDocumento);
    var Objdata = {
        permissionUser:'permisos',
        transnoReintegro:reintegro,
        typeDocument:tipoDocumento
    }

    $.ajax({
        url: "modelo/captura_aviso_reintegros_Modelo.php",
        async: false,
        cache: false,
        method: "GET",
        dataType: "JSON",
        data: Objdata
    }).done(function(result){

        var tipodeReintegro = 0;

        autorizarGeneral = result.autorizarGeneral;
        tipodeReintegro = result.tipoReintegro;

       // alert("Aurizar: "+autorizarGeneral+"       "+"Tipo de Reintegrp:  "+tipodeReintegro);

        fnDeshabilitaElemPag(autorizarGeneral,tipodeReintegro);

    }).fail(function(error){
         console.log(error);
    });

}

function fnDeshabilitaElemPag(auth,typeR){

    if(auth == 1){

        if(typeR == 1){

            $("#selectOperacionTesoreria").multiselect('disable');
            $("#selectUnidadEjecutora").multiselect('disable');

            $('#selectUnidadNegocio').multiselect('disable');
            $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
            $('#txtProcesoSIAFF').attr('readonly', 'readonly');
            $('#txtNumeroTransf').attr('readonly', 'readonly');
            $('#selectTipoReintegro').multiselect('disable');
            $('#txtJustificacion').attr('readonly', 'readonly');
            $('#txtFolioTransf').attr('readonly', 'readonly');
            $('#txtLineTesofe').attr('readonly', 'readonly');


            $("button[name=btnBusqueda]").attr("disabled", "disabled");
            $('#txtFechaAut').attr('readonly', 'readonly');

            $("button[name=Cancelar]").hide();
            $("button[name=Guardar]").hide();

            document.getElementById("txtSIAFF").style.display="none";

            document.getElementById("lineTESOFE").style.display="block";
            document.getElementById("CodigoRastreo").style.display="block";
            document.getElementById("NumTransfer").style.display="none";

            document.getElementById("textONE").style.display="none";
            document.getElementById("texttwo").style.display="block";


            marcar_todosINIC(false,typeR);

        }else{
            if(typeR == 2){

                $("#selectOperacionTesoreria").multiselect('disable');
                $("#selectUnidadEjecutora").multiselect('disable');

                $('#selectUnidadNegocio').multiselect('disable');
                $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
                $('#txtProcesoSIAFF').attr('readonly', 'readonly');
                $('#txtNumeroTransf').attr('readonly', 'readonly');
                $('#selectTipoReintegro').multiselect('disable');
                $('#txtJustificacion').attr('readonly', 'readonly');
                $('#txtFolioTransf').attr('readonly', 'readonly');
                $("button[name=btnBusqueda]").attr("disabled", "disabled");
                $('#txtFechaAut').attr('readonly', 'readonly');

                $("button[name=Cancelar]").hide();
                $("button[name=Guardar]").hide();

                document.getElementById("txtSIAFF").style.display="none";

                document.getElementById("lineTESOFE").style.display="none";
                document.getElementById("CodigoRastreo").style.display="block";
                document.getElementById("NumTransfer").style.display="block";

                document.getElementById("textONE").style.display="block";
                document.getElementById("texttwo").style.display="none";

                marcar_todosINIC(false,typeR);

            }else{
                if(typeR == 3){

                    $("#selectOperacionTesoreria").multiselect('disable');
                    $("#selectUnidadEjecutora").multiselect('disable');


                    $('#selectUnidadNegocio').multiselect('disable');
                    $('#txtCodigoClaveRastreo').attr('readonly', 'readonly');
                    $('#txtProcesoSIAFF').attr('readonly', 'readonly');
                    $('#txtNumeroTransf').attr('readonly', 'readonly');
                    $('#selectTipoReintegro').multiselect('disable');
                    $('#txtJustificacion').attr('readonly', 'readonly');
                    $('#txtFolioTransf').attr('readonly', 'readonly');
                    $("button[name=btnBusqueda]").attr("disabled", "disabled");
                    $('#txtFechaAut').attr('readonly', 'readonly');

                    $("button[name=Cancelar]").hide();
                    $("button[name=Guardar]").hide();

                    document.getElementById("txtSIAFF").style.display="none";

                    document.getElementById("lineTESOFE").style.display="none";
                    document.getElementById("CodigoRastreo").style.display="block";
                    document.getElementById("NumTransfer").style.display="none";

                    document.getElementById("textONE").style.display="block";
                    document.getElementById("texttwo").style.display="none";

                    marcar_todosINIC(false,typeR);

                }
            }
        }


    }else{
        if(auth == 0){

            if(typeR == 1){

                $("#selectOperacionTesoreria").multiselect('disable');
                $("#selectUnidadEjecutora").multiselect('disable');


                document.getElementById("txtSIAFF").style.display="none";

                document.getElementById("lineTESOFE").style.display="block";
                document.getElementById("CodigoRastreo").style.display="block";
                document.getElementById("NumTransfer").style.display="none";

                document.getElementById("textONE").style.display="none";
                document.getElementById("texttwo").style.display="block";

            }else{
                if(typeR == 2){

                    $("#selectOperacionTesoreria").multiselect('disable');
                    $("#selectUnidadEjecutora").multiselect('enable');

                    document.getElementById("txtSIAFF").style.display="none";

                    document.getElementById("lineTESOFE").style.display="none";
                    document.getElementById("CodigoRastreo").style.display="block";
                    document.getElementById("NumTransfer").style.display="block";

                    document.getElementById("textONE").style.display="block";
                    document.getElementById("texttwo").style.display="none";

                }else{
                    if(typeR == 3){

                        $("#selectOperacionTesoreria").multiselect('enable');
                        $("#selectUnidadEjecutora").multiselect('enable');

                        document.getElementById("txtSIAFF").style.display="none";

                        document.getElementById("lineTESOFE").style.display="none";
                        document.getElementById("CodigoRastreo").style.display="block";
                        document.getElementById("NumTransfer").style.display="none";

                        document.getElementById("textONE").style.display="block";
                        document.getElementById("texttwo").style.display="none";
                    }
                }
            }

        }
    }

}//

function fnsumarSeleccion(){

    var cant = $('#txtTotalReducciones');
        cant.html("");

    var sumaGeneral = 0;

    $(".suma").each(function(){
        var str = $(this).val();
        sumaGeneral += eval(str.replace(',','').replace(',',''));
    });

    cant.html(""+ formatoComas( redondeaDecimal( sumaGeneral ) ) );
}

function number_format(number, decimals, decPoint, thousandsSep){
    decimals = decimals || 0;
    number = parseFloat(number);

    if(!decPoint || !thousandsSep){
        decPoint = '.';
        thousandsSep = ',';
    }

    var roundedNumber = Math.round( Math.abs( number ) * ('1e' + decimals) ) + '';
    var numbersString = decimals ? roundedNumber.slice(0, decimals * -1) : roundedNumber;
    var decimalsString = decimals ? roundedNumber.slice(decimals * -1) : '';
    var formattedNumber = "";

    while(numbersString.length > 3){
        formattedNumber += thousandsSep + numbersString.slice(-3)
        numbersString = numbersString.slice(0,-3);
    }

    return (number < 0 ? '-' : '') + numbersString + formattedNumber + (decimalsString ? (decPoint + decimalsString) : '');
}