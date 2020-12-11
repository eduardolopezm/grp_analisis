/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jose Raul Lopez Vazquez
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

$(document).ready(function() {
   // Llamado de UR y UE
    fnObtenerBotones('divBotones');
    load_UR_UE();
    loadSelectStatus();
    loadtypeRefunds();
    typePayment();

    viewResultSearch();


});

function load_UR_UE(){

    var tagref = "";
    var selectUnidadNegocio = document.getElementById('selectUnidadNegocio');
    for ( var i = 0; i < selectUnidadNegocio.selectedOptions.length; i++) {
        if (i == 0) {
            tagref = "'"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }else{
            tagref = tagref+", '"+selectUnidadNegocio.selectedOptions[i].value+"'";
        }
    }

    var ue = "";
    var selectUnidadEjecutora = document.getElementById('selectUnidadEjecutora');
    for ( var i = 0; i < selectUnidadEjecutora.selectedOptions.length; i++) {
        if (i == 0) {
            ue = "'"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }else{
            ue = ue+", '"+selectUnidadEjecutora.selectedOptions[i].value+"'";
        }
    }
}

function loadSelectStatus(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/panel_aviso_reintegros_Modelo.php", {option:'mostrarSelectEstatus'}).then(function(result) {
        var selectData = JSON.parse(result);
        $.each(selectData.Status.datos,function(key, registro) {
            $("#selectEstatusReintegro").append('<option value='+registro.value+'>'+registro.texto+'</option>');
        });
        fnFormatoSelectGeneral(".selectEstatusReintegro");
    });
}

function loadtypeRefunds(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/panel_aviso_reintegros_Modelo.php",{option:'tipodeReintegro'}).then(function(result) {
        var selectData = JSON.parse(result);
        $.each(selectData.Status.datos,function(key, registro) {
            $("#selectTipoReintegro").append('<option value='+registro.value+'>'+registro.texto+'</option>');
        });
        fnFormatoSelectGeneral(".selectTipoReintegro");
    });
}

function typePayment(){

    $.ajaxSetup({async: false, cache:false});
    $.get("modelo/panel_aviso_reintegros_Modelo.php",{option:'typePayment'}).then(function(result){
        var selectDatas = JSON.parse(result);
        // console.log(selectDatas);
        $.each(selectDatas.datatype.datos,function(key, registro) {
            $("#selectOperacionTesoreria").append('<option value='+registro.value+'>'+registro.texto+'</option>');
        });

        fnFormatoSelectGeneral(".selectOperacionTesoreria");
    });
}

function viewResultSearch(){

    var selectUR =  $('#selectUnidadNegocio').val();
    var selectUE =  $('#selectUnidadEjecutora').val();
    var selectESR = $('#selectEstatusReintegro').val();
    var selectTPR = $('#selectTipoReintegro').val();
    var selectPAY = $('#selectOperacionTesoreria').val();

    var txt_folio = $('#txtFolioReintegro').val();

    var resultStringUR = '';
    var resultStringUE = '';
    var resultStringESR = '';
    var resultStringTPR = '';
    var resultStringPAY = '';

   if(selectUR == '' || selectUR == null){
       resultStringUR = '';
   }else{
       for(var i=0; i<selectUR.length; i++){
           resultStringUR += "'"+selectUR[i]+"'"+",";
       }
       resultStringUR = resultStringUR.substring(0,resultStringUR.length -1);
   }
   //
   if(selectUE == '' || selectUE == null){
       resultStringUE = '';
   }else{
       for(var j=0; j<selectUE.length; j++){
           resultStringUE += "'"+selectUE[j]+"'"+",";
       }
       resultStringUE = resultStringUE.substring(0,resultStringUE.length -1);
   }
   //
   if(selectESR == '' || selectESR == null){
       resultStringESR = '';
   }else{
       for(var k=0; k<selectESR.length; k++){
           resultStringESR += "'"+selectESR[k]+"'"+",";
       }
       resultStringESR = resultStringESR.substring(0,resultStringESR.length -1);
   }
   //
   if(selectTPR == '' || selectTPR == null){
       resultStringTPR = '';
   }else{
       for(var l=0; l<selectTPR.length; l++){
           resultStringTPR += "'"+selectTPR[l]+"'"+",";
       }
       resultStringTPR = resultStringTPR.substring(0,resultStringTPR.length -1);
   }


   if(selectPAY == '' || selectPAY == null){
       resultStringPAY = '';
   }else{
       for(var l=0; l<selectPAY.length; l++){
           resultStringPAY += "'"+selectPAY[l]+"'"+",";
       }
       resultStringPAY = resultStringPAY.substring(0,resultStringPAY.length -1);
   }

   $.ajax({
       url: "modelo/panel_aviso_reintegros_Modelo.php",
       async: false,
       cache: false,
       method: "GET",
       dataType: "JSON",
       data: {
           option: 'ResultsSearch',
           ur: resultStringUR,
           ue: resultStringUE,
           statusRefunds: resultStringESR,
           folioRefunds: txt_folio,
           typeRefunds: resultStringTPR,
           typePayment:resultStringPAY,
           starDate: $('#txtFechaDesde').val(),
           endDate: $('#txtFechaHasta').val()
       }

   }).done(function(data){

     //  console.log(data);

       dataJson = data.contenido.datos;
       columnasNombres = data.contenido.columnasNombres;
       columnasNombresGrid = data.contenido.columnasNombresGrid;
       dataJsonNoCaptura = data.contenido.datos;

       fnLimpiarTabla('divTabla', 'divContenidoTabla');

       var nombreExcel = data.contenido.nombreExcel;
       var columnasExcel= [1,2,3,5,6,7,9,10]; // Columnas a Imprimir en Excel
       var columnasVisuales= [0,1,2,3,4,6,7,9,10,16]; // Columnas a Visualizar en Pantalla
       fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

   }).fail(function(error){
       console.log(error);
       var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
       var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+error+'</p>';
       muestraModalGeneral(3, titulo, mensaje);

   });

}

function fnObtenerBotones(divMostrar) {
    //Opcion para operacion
    dataObj = {
        option: 'obtenerBotones',
        type: ''
    };

    $.ajax({
        async: false,
        cache:false,
        method: "GET",
        dataType:"json",
        url: "modelo/panel_aviso_reintegros_Modelo.php",
        data:dataObj
    }).done(function(data) {

        if(data.result){

            //Si trae informacion
            info = data.contenido.datos;

            var contenido = '';
            for (var key in info) {
                var funciones = '';

                //console.log(info[key].statusid);

                if (info[key].statusid == 0) {
                    funciones = 'ConfirmCancelRefunds('+info[key].statusid+')';
                }else if (info[key].statusid == 4) {
                    funciones = 'ConfirmAuthorizeRefunds('+info[key].statusid+')';
                }else if (info[key].statusid == 2){
                    funciones = 'ConfirmforvalidationRefunds('+info[key].statusid+')';
                }else if(info[key].statusid == 3){
                    funciones = 'ConfirmforAuthorizeRefunds('+info[key].statusid+')';
                }else if (info[key].statusid == 99) {
                    funciones = 'ConfirmRejectedRefunds('+info[key].statusid+')';
                }else if(info[key].statusid == 5) {
                    funciones = 'ComfirmApplyforRefunds('+info[key].statusid+')';
                }else{
                   // funciones = 'ConfirmStatusRefunds('+info[key].statusid+')';
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

    }).fail(function(error) {

           //console.log("ERROR");
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+error+'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });
}

function ConfirmCancelRefunds(statusid){

// Función para confirmación de la cancelación
    // Obtener registros seleccionados
     dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

     //console.log(dataJsonNoCapturaSeleccionados);


     if (seleccionoCaptura == 0) {

         var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
         muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
     }else if (estatusDiferentes == 1) {
         var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
         muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
         estatusDiferentes = 0;
     }else {
         var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';


         if(dataJsonNoCapturaSeleccionados.length > 1){
             var mensaje = '<p>¿Desea Cancelar los folios seleccionados?</p>';
         }else{
             var mensaje = '<p>¿Desea Cancelar el folio seleccionado?</p>';
         }
         //var mensaje = '<p>¿Desea Cancelar los Folios Seleccionados?</p>';
         muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
     }
}

function ConfirmAuthorizeRefunds(statusid){
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

    if (seleccionoCaptura == 0) {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else if (estatusDiferentes == 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
        estatusDiferentes = 0;
    }else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';


        if(dataJsonNoCapturaSeleccionados.length > 1){
            var mensaje = '<p>¿Desea Autorizar los folios seleccionados?</p>';
        }else{
            var mensaje = '<p>¿Desea Autorizar el folio seleccionados?</p>';
        }
        //var mensaje = '<p>¿Desea Autorizar los Folios Seleccionados?</p>';
        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
    }
}

function ComfirmApplyforRefunds(statusid){
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

    if (seleccionoCaptura == 0) {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else if (estatusDiferentes == 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
        estatusDiferentes = 0;
    }else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

        if(dataJsonNoCapturaSeleccionados.length > 1){
            var mensaje = '<p>¿Desea Solicitar los folios seleccionados?</p>';
        }else{
            var mensaje = '<p>¿Desea Solicitar el folio seleccionado?</p>';
        }

        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
    }
}

function ConfirmforvalidationRefunds(statusid){
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

    if (seleccionoCaptura == 0) {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else if (estatusDiferentes == 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
        estatusDiferentes = 0;
    }else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

        if(dataJsonNoCapturaSeleccionados.length > 1){
            var mensaje = '<p>¿Desea Avanzar los folios seleccionados?</p>';
        }else{
            var mensaje = '<p>¿Desea Avanzar el folio seleccionado?</p>';
        }




        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
    }
}

function ConfirmforAuthorizeRefunds(statusid){
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

    if (seleccionoCaptura == 0) {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else if (estatusDiferentes == 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
        estatusDiferentes = 0;
    }else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

        if(dataJsonNoCapturaSeleccionados.length > 1){
            var mensaje = '<p>¿Desea Autorizar los folios seleccionados?</p>';
        }else{
            var mensaje = '<p>¿Desea Autorizar el folio seleccionados?</p>';
        }

       // var mensaje = '<p>¿Desea Poner el estatus Por Autorizar los Folios Seleccionados?</p>';
        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
    }
}

function ConfirmRejectedRefunds(statusid){
    dataJsonNoCapturaSeleccionados = fnObtenerDatosSeleccionados();

    if (seleccionoCaptura == 0) {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);
    }else if (estatusDiferentes == 1) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);
        estatusDiferentes = 0;
    }else {

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

            if(dataJsonNoCapturaSeleccionados.length > 1){
                var mensaje = '<p>¿Está seguro de Rechazar los folios seleccionados?</p>';
            }else{
                var mensaje = '<p>¿Está seguro de Rechazar el folio seleccionado?</p>';
            }

        muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "changeStatus('"+statusid+"')");
    }

}
///////
function fnObtenerDatosSeleccionados() {
    // Funcion para obtener los renglones seleccionados de la tabla
    var dataJsonNoCapturaSeleccionados = new Array();

    var estatus = "";
    seleccionoCaptura = 0;
    var griddata = $('#divTabla > #divContenidoTabla').jqxGrid('getdatainformation');
    for (var i = 0; i < griddata.rowscount; i++) {
        var id = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'idCheck');
        if (id == true) {
            var obj = new Object();
            obj.ur = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'ur');
            obj.folio = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'folioExcel');
            obj.type = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'tipo');
            obj.statusUp = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'statusUp');
            obj.idRefunds = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'idrefunds');
            obj.period = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'period');
            obj.notrans = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'folioTransfer');
            obj.typeR = $('#divTabla > #divContenidoTabla').jqxGrid('getcellvalue', i, 'type_refund');
            dataJsonNoCapturaSeleccionados.push(obj);
            seleccionoCaptura = 1;
            if (estatus == "") {
                estatus = obj.statusUp;
            }else if (Number(estatus) != Number(obj.statusUp)) {
                estatusDiferentes = 1;
            }
        }
    }

    return dataJsonNoCapturaSeleccionados;
}

function changeStatus(statusID){

    if(seleccionoCaptura == 0){

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeSinNoCaptura);

    } else if(estatusDiferentes == 1){

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, mensajeEstatusDiferentes);

    } else {

        // Si es Cancelado no validar informacion y Cambiar Estatus

       // alert(statusID);

        UpdateStatus(statusID);
        viewResultSearch();
        /*else if(realizarProceso == 1){

            dataObj = {
                option: 'validarDisponibleNoCaptura',
                dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados
            };

             //Ajax Obtener datos
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/panel_aviso_reintegros_Modelo.php",
                data:dataObj
            }).done(function(data){
                console.log(data);


            }).fail(function(error){
                console.log(error);

            });

        }*/
    }
}

function UpdateStatus(statusID){

    ObjData = {
        optionUpdate: 'UpdateStatusRefunds',
        dataJsonNoCapturaSeleccionados: dataJsonNoCapturaSeleccionados,
        statusid: statusID
    };

    // Ajax Actualizar Cancelar
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: "modelo/panel_aviso_reintegros_Modelo.php",
        data:ObjData
    }).done(function (result){

        //console.log(result.length);
        //console.log(result);

        if(result.length > 1){

            var msgs = '';

            $.each(result,function(key,value) {
               //

                if(value.status == 'success'){

                    msgs += value.msg + "<br/>";

                }else{

                    if(value.status == 'error'){
                        msgs += value.msg + "<br/>";
                    }
                }

            });

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + msgs + '</p>';
            muestraModalGeneral(3, titulo, mensaje);

        }else{

            if(result[0].status == 'success'){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result[0].msg + '</p>';
                muestraModalGeneral(3, titulo, mensaje);

            }else{

                if(result[0].status == 'error'){
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+result[0].msg+'</p>';
                    muestraModalGeneral(3, titulo, mensaje);
                }

            }
        }



/*
        if(result.status == 'error'){
            alert('error');
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+result.msg+'</p>';
            muestraModalGeneral(3, titulo, mensaje);

        }else{

            if(result.status == 'success'){

                console.log(result.msg.length);

                if(result.msg.length > 1){

                    $.each(result.msg,function(key,value) {
                        msgs += value.msg + "<br/>";
                    })

                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.msgs + '</p>';
                    muestraModalGeneral(3, titulo, mensaje);

                }else{
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    var mensaje ='<p><i class="glyphicon glyphicon-ok-sign text-success" aria-hidden="true"></i>   ' + result.msg + '</p>';
                    muestraModalGeneral(3, titulo, mensaje);

                }

            }
        }*/


    }).fail(function (error){

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        var mensaje = '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> '+error+'</p>';
        muestraModalGeneral(3, titulo, mensaje);

    });

}