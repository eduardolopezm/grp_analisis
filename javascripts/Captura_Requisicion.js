/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de Agosto del 2017
 */

/* variables globales inicio */
var idanexoGlobal=-1;
var urGlobal=-1;
var tipoGlobal=-1;
var idrequisicionGlobal=-1;
var preglob= 0;
var partidas_presupuesto= [];
var url = "modelo/Captura_Requisicion_modelo.php";
var cont = 0; // contador de elementos.
var iCntPI = 0; // contador de elementos con presupuesto insuficiente.
var iCntND = 0; // contador de elementos sin disponibilidad en almacen.
var proceso = ""; // Valor del proceso que se llamara al modelo.
var noReq = noRequisicionGeneral;
var urlCR = urlCReq;
var estatusGenerarLayout = 4;
var funcionGenerarLayout = 2265;
var typeGenerarLayout = 19;
var tipoLayout = 1;
var periodoR = periodoReq;
var viewAnexo= 0;
// Crear un elemento div añadiendo estilos CSS
var containerArticulo = $(document.createElement('div')).css({
        padding: 'opx',
        margin: '0px',
        width: '100%'
    });
var containerServicio = $(document.createElement('div')).css({
        padding: '0px',
        margin: '0px',
        width: '100%'
    });
var posicionesUltimas=[];
/* variables globales fin */

$(document).ready(function() {
    $("#idtxtRequisicionView").addClass("hide");
    $("#idtxtRequisicionView").prop("readonly", true);
    $("#idtxtRequisicionView").attr('disabled', 'disabled');

    $("#idFechaElaboracion").prop("readonly", true);
    $("#idFechaElaboracion").attr('disabled', 'disabled');

    $('input:checkbox[name=dividirAsignacion]').css({
        transform: 'scale(2)'
    });

    fnEditorTextoClose();
    fnFijarFecha();
    fnFijarFechaSiguiente(1);
    
    if( noRequisicionGeneral > 0){
        $("#idtxtRequisicion").val(""+noRequisicionGeneral);
        $("#idtxtRequisicion").prop("readonly", true);
        $("#idtxtRequisicion").attr('disabled', 'disabled');

        var noReq = noRequisicionGeneral;
        //var periodoR = periodoReq;
        //alert("noreq" + noReq);

        if(noReq > 0){
            muestraCargandoGeneral();
            fnLimpiar(noReq);
            fnLoadRequisicion(noReq);
            ocultaCargandoGeneral();
        }
    }
    
    $('#idPopupEditorClose').click(function() {
        $('.idPopupEditor').fadeOut('slow');
        $('.popup-overlay').fadeOut('slow');
        return false;
    });
    
   $("#enviarArchivosMultiples").click(function(){
    
    $("#idMainListContentArticulo select[id*=selectCvePartidaEspecifica]").each(function(index, el) {
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecifica','');
        $("#btnanexorenglonart"+numero).removeClass('hide');
        viewAnexo=1;
    });
    $("#idMainListContentServicio select[id*=selectCvePartidaEspecificaServ]").each(function(index, el) {
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecificaServ','');
        $("#btnanexorenglonserv"+numero).removeClass('hide');
        viewAnexo=1;
    });
   });

   $("#btnConfirmarEliminar").click(function(){
    $("#idMainListContentArticulo select[id*=selectCvePartidaEspecifica]").each(function(index, el) {
        viewAnexo=0;
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecifica','');
        $("#addedReglonArticulo"+numero).val("");
        $("#btnanexorenglonart"+numero).addClass('hide');
        
    });
    $("#idMainListContentServicio select[id*=selectCvePartidaEspecificaServ]").each(function(index, el) {
        viewAnexo=0;
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecificaServ','');
        $("#addedReglonServicio"+numero).val("");
        $("#btnanexorenglonserv"+numero).addClass('hide');
        
    });
   });
});
// se deja para usar checkbox del grid
/*function fnChecarSeleccionados(){
        var requisicionesCS= new Array();
        var griddata = $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getdatainformation');
        var cadena='';

        for (var i = 0; i < griddata.rowscount; i++){
            id=  $('#divTablaArchivos > #divDatosArchivos').jqxGrid('getcellvalue',i, 'id1');

            if(id==true){
                requisicionesCS.push($('#divTablaArchivos > #divDatosArchivos').jqxGrid('getcellvalue',i, 'idrequisicionH'));
            }
        }

       return requisicionesCS;
    }*/

function fnObtenerStatusReq(idReq){
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
                for (var info in dataStatus) {
                    statusReq = dataStatus[info].status;
                }
                $("#statusReq").html(statusReq);
                $("#statusReq").prop("readonly", true);
                $("#statusReq").attr('disabled', 'disabled');
                $("#idStatusReq").val(""+statusReq);
                fnObtenerPerfilUsr();
                
            }else{
                //muestraMensaje('No se guardo la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            //alert(result);
        });
}

function fnObtenerPerfilUsr(){
    var status = $("#idStatusReq").val();
    var perfilUsr = "";
    var perfilid = "";
       
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
                //alert("perfil actual:" + perfilUsr);
            }else{
                //muestraMensaje('No se guardo la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
}

function fnCalculaTotal(orden){
    var cantidad = $("#addedCantidadArticulo" + orden).val();
    var precio = $("#addedPEArticulo" + orden).val();
    var existencia = $("#contDispArt" + orden).val();
    var disponibilidad = existencia - cantidad;
    var total = cantidad * precio;
    
    $("#addedCantidadTotalArticulo" + orden).val(""+total);

    /*if( disponibilidad >= 0){
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
     }*/

    //fnModificarArticulo(noReq,orden);
    $("#addedCantidadTotalArticulo"+orden).trigger('change');
}
    
function fnValidaPresupuesto(orden) {
    var a = $("#addedCantidadArticulo" + orden).val();
    var b = $("#addedPEArticulo" + orden).val();
    var t = a * b;
    var p = $("#addPresupuestoH"+orden).val();
    var d = p - t;
    var idReq = $("#idtxtRequisicion").val();
    var pe = $("#selectCvePartidaEspecifica" + orden).val();

    $("#addedCantidadTotalArticulo" + orden).val(""+t);

    //fnModificarArticulo(idReq,orden);
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
    var noRequisicion = $("#idtxtRequisicion").val();
    var fElabora = $("#idFechaElaboracion").val();
    var fEntrega = $("#idFechaEntrega").val();
    var razonSocial = $("#selectRazonSocial").val();
    var unidadNegocio = $("#selectUnidadNegocio").val();
    var unidadEjecutora = $("#selectUnidadEjecutora").val();
    var mbflag = mbflag;
    var obs = $("#txtAreaObs").val();


    if(noRequisicion > 0){
        fnRequisicionExistente(noRequisicion,mbflag);
    }else{
        fnNuevaRequisicion(fElabora,fEntrega,razonSocial,unidadNegocio,unidadEjecutora,mbflag,obs);
    } 

   componenteNuevo();
}

/**
 * Crea una nueva requisición 
 */
function fnNuevaRequisicion(fElabora,fEntrega,razonSocial,unidadNegocio,unidadEjecutora,mbflag,obs){
    if ((fElabora == "") || (fEntrega == "") || (razonSocial == "") || (unidadNegocio == "")|| (unidadEjecutora == "")) {
        muestraMensaje('Es necesario seleccionar las fechas, unidad responsable y unidad ejecutora  para crear una nueva Requisición', 3, 'msjValidacion', 5000);
        return false;
    }else{
        muestraCargandoGeneral();
        rs = $('#selectRazonSocial option:selected').text();
        ur = $('#selectUnidadNegocio option:selected').text();
        ue = $('#selectUnidadEjecutora option:selected').text();
        $('#selectRazonSocial').empty();
        $("#selectRazonSocial").html("<option value="+razonSocial+">"+rs+"</option>");
        $("#selectRazonSocial").multiselect('rebuild');
        $('#selectUnidadNegocio').empty();
        $("#selectUnidadNegocio").html("<option value="+unidadNegocio+">"+ur+"</option>");
        $("#selectUnidadNegocio").multiselect('rebuild');
        /*$('#selectUnidadEjecutora').empty();
        $("#selectUnidadEjecutora").html("<option value="+unidadEjecutora+">"+ue+"</option>");
        $("#selectUnidadEjecutora").multiselect('rebuild');*/

        $("#idAnexoTap").removeClass('hide');

        var tagref = $("#selectUnidadNegocio").val();

        dataObj = {
            option: 'agregarCapturaRequisicion',
            fechaElabora: fElabora,
            fechaEntrega: fEntrega,
            rs: razonSocial,
            un: unidadNegocio,
            ue: unidadEjecutora, 
            mbflag: mbflag,
            obs: obs
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
                //var noReq = dataR[0].requisitionno;
                var idReq = dataR[0].orderno;
                
                $("#idtxtRequisicionView").addClass("hide");
                //$("#idtxtRequisicionView").html(noReq);
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                $("#idtxtRequisicion").val("" + idReq);
                $("#idtxtRequisicion").prop("readonly", true);
                $("#idtxtRequisicion").attr('disabled', 'disabled');
                
                fnObtenerStatusReq(idReq);
                fnAgregarElemento(mbflag,idReq,fEntrega,cont);
                
                ocultaCargandoGeneral();

            }else{
                muestraMensaje('No se agrego la Requisición', 3, 'divMensajeOperacion', 5000);
                ocultaCargandoGeneral();
            }
        }).fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
        });
    }
}
/**
 * Crea noevos elementos a una requisición existente
 */
function fnRequisicionExistente(noRequisicion,mbflag){
    muestraCargandoGeneral();

    var tagref = $("#selectUnidadNegocio").val();

   /* $("#selectRazonSocial").prop("readonly", true);
    $("#selectRazonSocial").attr('disabled', 'disabled');
    $("#selectUnidadNegocio").prop("readonly", true);
    $("#selectUnidadNegocio").attr('disabled', 'disabled');
    $("#selectUnidadEjecutora").prop("readonly", true);
    $("#selectUnidadEjecutora").attr('disabled', 'disabled');*/
    
    $("#idAnexoTap").removeClass('hide');

    dataObj = {
        option: 'requisicionExistente',
        reqExist: noRequisicion
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
                var noReq = "";
                var fEntrega = "";
                var orden = "";

                for (var info in dataReqExist) {
                    idReq = dataReqExist[info].orderno;
                    noReq = dataReqExist[info].noRequisition;
                    orden = dataReqExist[info].orden;
                    fEntrega = dataReqExist[info].fechaEnt;
                }

                /*if (mbflag == "B") {
                    if (!fnCargarPartidaProducto(periodoR, tagref, orden)) {
                        ocultaCargandoGeneral();
                        muestraMensaje("No existen claves de partida en articulos para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
                        return false;
                    }
                } else {
                    if (!fnCargarPartidaServicio(periodoR, tagref, orden)) {
                        ocultaCargandoGeneral();
                        muestraMensaje("No existen claves de partida en servicios para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
                        return false;   
                    }
                }*/
                
                fnObtenerStatusReq(idReq);
                fnAgregarElemento(mbflag,idReq,fEntrega,orden);
                ocultaCargandoGeneral();
                
            }else{
                ocultaCargandoGeneral();
            }

        }).fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
        });
}

function fnLoadRequisicion(noReq){
    fnReindexar();
    fnObtenerStatusReq(noReq);
    fnObtenerPerfilUsr();

    $("#idAnexoTap").removeClass('hide');
    $("#idtxtRequisicionView").removeClass("hide");
    $("#idtxtRequisicionView").prop("readonly", true);
    $("#idtxtRequisicionView").attr('disabled', 'disabled');
    $("#idStatusReq").removeClass("hide");
    $("#idStatusReq").prop("readonly", true);
    $("#idStatusReq").attr('disabled', 'disabled');

    //$("#idtxtRequisicion").val(""+noRequisicionGeneral);
    dataObj={
        option: 'loadRequisicion',
        req:noReq
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
            var ue = "";
            var fechaCreacion = "";
            var fechadelivery = "";
            var comments = "";
            var idr = "";
            var noReq = "";
            var unidadEjecutora = "";
            var dividirA = "";

            rs = info[0].rs;
            ue = info[0].ue;
            unidadEjecutora = info[0].unidadEjecutora;
            fechaCreacion = info[0].fechaCreacion;
            fechadelivery = info[0].fechadelivery;
            comments = info[0].comments;
            idr = info[0].idr;
            noReq = info[0].noReq;
            dividirA = info[0].dividirA;

             idanexoGlobal= 'anexo' ; 
             urGlobal=ue;
             tipoGlobal=19;
             idrequisicionGlobal=idr;

            $("#idtxtRequisicionView").html(noReq);
            $("#idtxtNoRequisicion").val(""+noReq);
            $('#selectRazonSocial').empty();
            $("#selectRazonSocial").html("<option value="+rs+">"+rs+"</option>");
            $("#selectRazonSocial").multiselect('rebuild');
            $('#selectUnidadNegocio').empty();
            $("#selectUnidadNegocio").html("<option value="+ue+">"+ue+"</option>");
            $("#selectUnidadNegocio").multiselect('rebuild');
            $('#selectUnidadEjecutora').empty();
            $("#selectUnidadEjecutora").html("<option value="+unidadEjecutora+">"+unidadEjecutora+"</option>");
            $("#selectUnidadEjecutora").multiselect('rebuild');
            $("#txtAreaObs").val(""+comments);
            $("#idFechaElaboracion").val(""+fechaCreacion);
            $("#idFechaEntrega").val(""+fechadelivery);
            if( dividirA == 'P'){
                $('input:checkbox[name=dividirAsignacion]').attr('checked',true);
            }else{
                $('input:checkbox[name=dividirAsignacion]').attr('checked',false);
            }

            var periodo = $("#idFechaElaboracion").data("periodo");
            var tagref = $("#selectUnidadNegocio").val();

            //fnCargarPartidaProducto(periodoR,tagref); 
            //fnCargarPartidaServicio(periodoR,tagref);
            
            fnMostrarElementosRequisicion(idr);
            //fnActualizaPresupuesto();
            //fnActualizaPresupuestoServicio();
        }

    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
    });
}
/**
 * Buscar una requisición existente
 */
function fnMostrarElementosRequisicion(noRequisicion){
    dataObj = {
        option: 'mostrarRequisicion',
        requi: noRequisicion
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
            var idItem = "";
            var descItem = "";
            var unidad = "";
            var tipo = "";
            var precio = "";
            var cantidad = "";
            var total = "";
            var existencia = "";
            var orden = "";
            var clavepresupuestal = "";
            var descLarga = "";
            var fEntrega = $("#idFechaEntrega").val();
            var renglon = "";

            containerArticulo[0].innerHTML = "";
            containerServicio[0].innerHTML = "";

            for (var info in dataJson) {
                idRequisicion = dataJson[info].idRequisicion;
                idPartida = dataJson[info].idPartida;
                descPartida = dataJson[info].descPartida;
                idItem = dataJson[info].idItem;
                descItem = dataJson[info].descItem;
                unidad = dataJson[info].unidad;
                tipo = dataJson[info].tipo;
                precio = dataJson[info].precio;
                cantidad = dataJson[info].cantidad;
                total = dataJson[info].total;
                existencia = dataJson[info].existencia;
                orden = dataJson[info].orden;
                clavepresupuestal = dataJson[info].clavePresupuestal;
                descLarga = dataJson[info].descLarga;
                renglon = dataJson[info].renglon;

                if(tipo == 'B'){
                    fnArticulo(idRequisicion,orden,fEntrega,idPartida,idItem,descItem,unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga,renglon);
                }else{
                    fnServicio(idRequisicion,orden,fEntrega,idPartida,descPartida,idItem,descItem,precio,clavepresupuestal,descLarga,renglon);
                }
            }
        }else {
            muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
    });
}

function fnAgregarElemento(mbflag, noReq, fEntrega, orden){
    
    var tagref = $("#selectUnidadNegocio").val();
    var ord = parseInt(orden);

    if(ord > 0){
        cont = ord + 1;
    }else{
        cont = cont + 1;
    }
    //alert(cont);
    dataObj = {
        option: 'agregarElementosRequisicion',
        noReq: noReq,
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
                fnArticulo(noReq,cont,fEntrega);
                if(viewAnexo == 1){
                    $("#btnanexorenglonart"+cont).removeClass('hide');
                }
            }else{
                fnServicio(noReq,cont,fEntrega);
                if(viewAnexo == 1){
                    $("#btnanexorenglonserv"+cont).removeClass('hide');
                }
            }
        }else{
            muestraMensaje('No se agrego el elemento a la Requisición ', 3, 'divMensajeOperacion', 5000);
        }

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}

function fnEliminarElemento(req,orden){
     dataObj = {
        option: 'eliminarElementosRequisicion',
        noReq: req,
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
            
        }else{
            muestraMensaje('No se elimino el elemento a la Requisición: '+ r, 3, 'divMensajeOperacion', 5000);
            
        }

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}

// Funcion que trae las claves presupuestales por la partida especifica
function fnCargaPartidaCvePpto(orden) {
    muestraCargandoGeneral();

    var regresadatos= false;
    var cpeprod = $("#selectCvePartidaEspecifica" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();

    fnCargaPartidaCvePptoProduct(orden, tagref);

    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: cpeprod,
        datotagref: tagref
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

            /*$("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append('<option value="0">Cve ...</option>');
            fnFormatoSelectGeneral("#selectCveArticulo" + orden);
            $("#selectCveArticulo" + orden).multiselect('rebuild');

            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append('<option value="0">Articulo ...</option>');
            fnFormatoSelectGeneral("#selectArticulo" + orden);
            $("#selectArticulo" + orden).multiselect('rebuild');*/

            $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestal" + orden).append(contenidoCvePresupuestal);
            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestal" + orden);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).multiselect('rebuild');
            $("#selectPartidaEspecificaCvePresupuestal" + orden).prop("readonly", true);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).attr('disabled', 'disabled');

            //$("#selectCveArticulo" + orden).html('<option value="0">Cve ...</option>');
            //$("#selectArticulo" + orden).html('<option value="0">Articulo ...</option>');
            $("#addedUMArticulo" + orden).html("");
            $("#addedCantidadArticulo" + orden).val(0);
            $("#addedPEArticulo" + orden).val(0);

            $("#idCvePresupuestal" + orden).removeClass("hide");
            
            ocultaCargandoGeneral();

            if (!regresadatos) {
                //muestraMensaje("No existen clave presupuestal registrada para la partida "+cpeprod);La clave presupuestal en el renglón "XX" no tiene recurso disponible en el mes en curso.
                muestraMensaje("La clave presupuestal en el renglón " + orden + " no tiene recurso disponible en el mes en curso.");
            }
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });

    return regresadatos;
}

function fnCargaPartidaCvePptoProduct(orden, tagref) {
    var cvePartida = $("#selectCvePartidaEspecifica" + orden).val();

    dataObj = { 
        option: 'mostrarPartidaCvePptoProdct',
        datoTagref: tagref,
        datoCvePartida: cvePartida
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
            var contenidoIdArt = "";
            var contenidoDescArt = "";

            for (var info in dataJson) {
                contenidoIdArt += "<option value='" + dataJson[info].idProducto + "'>" + dataJson[info].idProducto + "</option>";
                contenidoDescArt += "<option value='" + dataJson[info].descripcionProducto + "'>" + dataJson[info].descripcionProducto + "</option>";
                
            }

            $("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append('<option value="0">Cve ...</option>' + contenidoIdArt);
            fnFormatoSelectGeneral("#selectCveArticulo" + orden);
            $("#selectCveArticulo" + orden).multiselect('rebuild');

            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append('<option value="0">Articulo ...</option>' + contenidoDescArt);
            fnFormatoSelectGeneral("#selectArticulo" + orden);
            $("#selectArticulo" + orden).multiselect('rebuild');

            $("#idCvePresupuestal" + orden).removeClass("hide");
            
            //ocultaCargandoGeneral();

        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });

}

// Funcion para traer claves presupuestales de acuerdo a la partida de servicios
function fnCargaPartidaCvePptoServicios(orden, tagref, cpeprod) {
    var regresadatos= false;

    dataObj = { 
        option: 'mostrarPartidaCvePpto',
        dato: cpeprod,
        datotagref: tagref
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

            /*$("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append('<option value="0">Cve ...</option>');
            fnFormatoSelectGeneral("#selectCveArticulo" + orden);
            $("#selectCveArticulo" + orden).multiselect('rebuild');

            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append('<option value="0">Articulo ...</option>');
            fnFormatoSelectGeneral("#selectArticulo" + orden);
            $("#selectArticulo" + orden).multiselect('rebuild');*/

            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoCvePresupuestal);
            fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestalServ" + orden);
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect('rebuild');
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).prop("readonly", true);
            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).attr('disabled', 'disabled');

            //$("#selectCveArticulo" + orden).html('<option value="0">Cve ...</option>');
            //$("#selectArticulo" + orden).html('<option value="0">Articulo ...</option>');
            //$("#addedUMArticulo" + orden).html("");
            //$("#addedCantidadArticulo" + orden).val(0);
            //$("#addedPEArticulo" + orden).val(0);

            $("#idCvePresupuestal" + orden).removeClass("hide");
            
            ocultaCargandoGeneral();

            if (!regresadatos) {
                muestraMensaje("No existen clave presupuestal registrada para la partida "+cpeprod);
            }
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });

    return regresadatos;
}

// funcion que trae los datos por la clave del articulo
function fnClaveArticulo(orden) {
    muestraCargandoGeneral();

    var cveprod = $("#selectCveArticulo" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    var idReq = $("#idtxtRequisicion").val();

    dataObj = { 
        option: 'mostrarCveArticuloDatos',
        datocveart: cveprod,
        datodescart: '',
        datotagref: tagref
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj

    }).done(function(data){
        if(data.result){
            //fnModificarArticulo(req,orden);
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
            //$("#contDispArt" + orden).val(""+contDispArt);
            
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
            $("#addedPEArticulo" + orden).val(""+ contPrecioEArt); 

            fnModificarArticulo(idReq, orden);
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
};

// funcion que trae los datos por la descripcion del articulo
function fnDescArticulo(orden){
    var descprod = $("#selectArticulo" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    var noReq = $("#idtxtRequisicion").val();

    dataObj = { 
        option: 'mostrarCveArticuloDatos',
        datocveart: '',
        datodescart: descprod,
        datotagref: tagref
    };
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj

    }).done(function(data){
        if(data.result){
            
            //fnModificarArticulo(req,orden);
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
            $("#addedPEArticulo" + orden).val(""+ contPrecioEArt);
            
            $("#validaPresupuesto" + orden).prop("readonly", true);
            $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
            //fnModificarArticulo(noReq,orden);
            fnObtenerPresupuesto(orden);
            ocultaCargandoGeneral();
        }else{
            ocultaCargandoGeneral();
        }

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}

// Funcion que calcula el total por la cantidad del articulo
function fnCantidadArticulo(orden) {
    var a = $("#addedCantidadArticulo" + orden).val();
    var b = $("#addedPEArticulo" + orden).val();
    var e = $("#contDispArt" + orden).val();
    //alert(e);
    var t = a * b;
    var p = $("#addPresupuestoH"+orden).val();
    var d = p - t;
    var s = e - a;
    var noReq = $("#idtxtRequisicion").val();
    //alert(s);
    //alert("disponibilidad: "+ p);
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
        //alert("> 0 :"+ s);
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = iCntND + 1;
     }else{
        //alert("< 0 :"+ s);
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("No Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = 0;
     }
    //fnModificarArticulo(noReq,orden);
}

function fnArticulo(noReq,orden,fEntrega,idPartida,idItem,descItem,unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga,renglon){
    var noReq = $("#idtxtRequisicion").val();
    var status = $("#idStatusReq").val();
    var perfilid = $("#idperfilusr").val();
    var tagref= $("#selectUnidadNegocio").val();
    var contenidoClaves= "";
    
    $(containerArticulo).append('<div class="row p0 m0">'+
            '<ol id=idElementArticulo' + orden + ' class="idElementArticulo col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                '<li id="idEliminarArticulo' + orden + '" class="w5p pt5"><span class="btRemoveArticulo btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveArticulo' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumArticulo' + orden + '" class="w5p pt5"><input type="text" id="nProd' + orden + '" class="hide" value="'+orden+'"/><label  class="w100p text-center" type="text" id="numArticulo' + orden + '"></label></li>' + 
                '<li id="idCvePartida'+orden+'" class="w10p p0"><select id="selectCvePartidaEspecifica' + orden + '" name="selectCvePartidaEspecifica' + orden + '" class="w100p form-control selectCvePartidaEspecifica" onchange="fnCargaPartidaCvePpto('+orden+');"></select></li>'+
                '<li id="idCvePartidaCveArticulo' + orden + '" class="w10p p0"><select id="selectCveArticulo' + orden + '" name="selectCveArticulo' + orden + '" class="selectCveArticulo form-control" onchange="fnClaveArticulo('+orden+');"><option value="0">Cve ...</option></select></li>'+
                '<li id="idCvePartidaDescArticulo' + orden + '" class="w40p p0"><select id="selectArticulo' + orden + '" name="selectArticulo' + orden + '" class="form-control selectArticulo" onchange="fnDescArticulo('+orden+');"><option value="0">Articulos ...</option></select></li>'+
                '<li id="idUMArticulo' + orden + '" class="w5p pt5"><label  class="w100p addedUMArticulo" type="text" id="addedUMArticulo' + orden + '"></label></li>'+
                '<li id="idCantidadArticulo' + orden + '" class="w5p pt5"><input value="" onkeypress="return soloNumeros(event)" class="addedCantidadArticulo vacia num w100p text-center" type="text" id="addedCantidadArticulo' + orden + '" placeholder="Cantidad" onblur="fnCalculaTotal('+orden+');" readonly="readonly" disabled="disabled"></li>'+
                //'<li id="idPEArticulo' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="soloNumeros num w100p addedPEArticulo text-right" type="text" id="addedPEArticulo' + orden + '" placeholder="Precio" onblur="fnValidaPresupuesto('+orden+');" readonly="readonly" disabled="disabled"></li>'+
                '<li id="idPEArticulo' + orden + '" class="w5p pt5"><input value="" class="soloNumeros w100p addedPEArticulo text-right" type="number" id="addedPEArticulo' + orden + '" placeholder="Precio" onblur="fnValidaPresupuesto('+orden+');" readonly="readonly" disabled="disabled"></li>'+
                '<li id="idCantidadTotalArticulo' + orden + '" class="w5p pt5"><input value="" class="addedCantidadTotalArticulo soloNumeros w100p  text-right" type="number" id="addedCantidadTotalArticulo' + orden + '" placeholder="Total" readonly="readonly" disabled="disabled"></li>'+
                //'<li id="idDisponibilidadArticulo' + orden + '" class="w10p pt5"><label  class="w100p addDispArticulo" type="text" id="addDispArticulo' + orden + '"></label><input type="text" id="contDispArt'+orden+'" class="hide" value="0"></li>'+
                '<li id="idRenglonAnexoArticulo' + orden + '" class="w10p pt5"><input step="0.01" value="" onkeypress="return fnSoloBorrar(event)" class="soloNumeros empty w70p mr2" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonart'+orden+'" class="hide w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnMuestraAnexo(' + orden + ');fnMostrarRequisicionModal();"></div></li>'+
            '</ol>' +
        '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupestal: </label></span></li>'+
                    '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                        //'<div id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class=" form-control selectPartidaEspecificaCvePresupuestal"></div>'+
                        //'<input type="text" id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"/>'+
                        //'<select id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"><option value="0">Clave Presupestal</option></select>'+
                        '<select id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"></select>'+
                    '</li>'+
                    /*'<li class="w10p"><span><label>Presupuesto: </label></span></li>'+
                    '<li class="w10p" id="idAddPresupuesto' + orden + '">'+
                        '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH hide" placeholder="Presupuesto" type="text" />'+
                        '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                    '</li>'+
                    '<li class="w10p" id="idValidaPresupuesto' + orden + '" >'+
                        '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto Suficiente" type="text" />'+
                    '</li>'+*/
                '</ol>'+
            '</div>');

    $('#idMainListContentArticulo').append(containerArticulo);

    $("#btRemoveArticulo" + orden).click(function() { 
        $(this).parent().parent().remove();
        $("#idCvePresupuestal" + orden).remove();
        fnEliminarElemento(noReq,orden);
    });

    if(status == 'Autorizado' || status == 'Cancelado'){
        $("#idAnexoTap").addClass('hide');
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }else if((perfilid == 9 ) && (status == 'Validar' || status == 'PorAutorizar' )){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }else if((perfilid == 10 ) && ( status == 'PorAutorizar' )){
        $("#idBtnAgregarArticulo").addClass('hide');
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    } else{
        $("#addedCantidadArticulo" + orden).prop("readonly", false); 
        $("#addedCantidadArticulo" + orden).attr('disabled', false);
        $("#addedPEArticulo" + orden).prop("readonly", false);
        $("#addedPEArticulo" + orden).attr('disabled', false);
    }

    if(!$.isNumeric(idPartida)){
        if (!fnCargarPartidaProducto(periodoR, tagref, orden)) {
            muestraMensaje("No existen claves de partida en articulos para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
        }

        $("#numArticulo" + orden).html(""+orden);
        $("#numArticulo" + orden).prop("readonly", true);
        $("#numArticulo" + orden).attr('disabled', 'disabled');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');

        //fnModificarArticulo(noReq,orden);
    } else {
        $("#numArticulo" + orden).html(orden);
        $("#numArticulo" + orden).prop("readonly", true);
        $("#numArticulo" + orden).attr('disabled', 'disabled');
        $("#selectCvePartidaEspecifica" + orden).empty();
        $("#selectCvePartidaEspecifica" + orden).html('<option value="'+idPartida+'">'+idPartida+'</option>');
        $("#selectCveArticulo" + orden).empty();
        $("#selectCveArticulo" + orden).html('<option value="'+idItem+'">'+idItem+'</option>');
        $("#selectArticulo" + orden).empty();
        $("#selectArticulo" + orden).html('<option value="'+descItem+'">'+descItem+'</option>');
        $("#addedUMArticulo" + orden).html(unidad);
        $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
        
        $("#addedCantidadArticulo" + orden).val(""+cantidad);
        $("#addedPEArticulo" + orden).val(""+precio);
        $("#addedCantidadTotalArticulo" + orden).val(""+total);

        $("#idCvePresupuestal" + orden).removeClass("hide");
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).val(""+clavepresupuestal);

        for (var clave in clavepresupuestal) {
            contenidoClaves += "<option value='" + clavepresupuestal[clave].cvePresupuestal + "'>" + clavepresupuestal[clave].cvePresupuestal + "</option>";
        }
        
        $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
        $("#selectPartidaEspecificaCvePresupuestal" + orden).append(contenidoClaves);
        fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestal"+orden);
        $("#selectPartidaEspecificaCvePresupuestal" + orden).multiselect('rebuild');
        
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).prop("readonly", true);
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).attr('disabled', 'disabled');
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
        $("#btnanexorenglonart" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonart" + orden).removeAttr("onclick");
        
    }

    fnFormatoSelectGeneral("#selectCvePartidaEspecifica"+orden);
}

function fnModificarArticulo(req,orden){
    var ic = $("#selectCveArticulo" + orden).val();
    var fe = $("#idFechaEntrega").val();
    var id = $("#selectArticulo" + orden).val();
    var up = $("#addedPEArticulo" + orden).val();
    var al = $("#addQtyArticulo" + orden).val();
    var cp = $("#selectPartidaEspecificaCvePresupuestal" + orden).val();
    var qy = $("#addedCantidadArticulo" + orden).val();
    var comments = $("#txtAreaObs").val();
    var total = qy * up;
    $("#addedCantidadTotalArticulo"+ orden).append(total);
    var renglon = $("#addedReglonArticulo" + orden).val();

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

function fnServicio(noReq,orden,fEntrega,idPartida,descPartida,idItem,descItem,precio,clavepresupuestal,descLarga,renglon){
    var noReq = $("#idtxtRequisicion").val();
    var status = $("#idStatusReq").val();
    var perfilid = $("#idperfilusr").val();
    var tagref= $("#selectUnidadNegocio").val();
    var contenidoClaves= "";

    $(containerServicio).append('<div class="row p0 m0">' +
        '<ol id=idElementServicio' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' + 
                '<li id="idEliminarServicio' + orden + '" class="w5p pt5"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove bt" id="btRemoveServicio' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumServicio' + orden + '" class="w5p pt5"><input type="text" id="nServ' + orden + '" class="hide" value="'+orden+'"/><label  class="w50p text-center" type="text" id="numServicio' + orden + '">' + orden + '</label></li>' + 
                '<li id="idAddedPartidaServicio' + orden + '" class="w10p"><select id="selectCvePartidaEspecificaServ' + orden + '" name="selectCvePartidaEspecificaServ' + orden + '" class="form-control selectCvePartidaEspecificaServ" onchange="fnClavePartidaServicio(' + orden + ', this.name)"></select></li>' + 
                '<li id="idAddedDescPartida' + orden + '" class="w20p"><select id="selectDescPartidaEspecificaServ' + orden + '" name="selectDescPartidaEspecificaServ' + orden + '" class="form-control selectDescPartidaEspecificaServ" onchange="fnClavePartidaServicio(' + orden + ', this.name)"></select></li>' + 
                '<li id="idAddedDescServicio' + orden + '" class="w35p pt5"><input type="text" id="cveServicio' + orden + '" class="hide"/><input class="w95p" id="descServicio' + orden + '" type="text" /></li>' + 
                '<li id="idAddLongDescServicio'+ orden +'" class="w5p pt5"><div class="btn btn-info btn-xs glyphicon glyphicon-comment" id="btLongDescServicio' + orden + '" onclick="fnEditorTextoOpen('+orden+');"></div></li>'+
                '<li id="idCantidadServicio' + orden + '" class="w5p pt5"><label  class="w100p addedCantidadServicio text-center" type="text" id="addedCantidadServicio' + orden + '">1</label></li>'+
                //'<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class=" w100p addedPEServicio" type="text" id="addedPEServicio' + orden + '" placeholder="Precio"></li>' + 
                '<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><input class="soloNumeros w100p addedPEServicio" type="number" id="addedPEServicio' + orden + '" placeholder="Precio"></li>' + 
                '<li id="idAddedReglonServicio' + orden + '" class="w10p pt5"><input step="0.01" onkeypress="return fnSoloBorrar(event)" class="w70p mr2" type="text" id="addedReglonServicio' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonserv'+orden+'" class="hide w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnMuestraAnexo(' + orden + ');fnMostrarRequisicionModal();"></div></li>'+
                '</ol>' +
            '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupestal: </label></span></li>'+
                    '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                        //'<div id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class=" form-control selectPartidaEspecificaCvePresupuestal"></div>'+
                        //'<input type="text" id="selectPartidaEspecificaCvePresupuestalServ' + orden + '" name="selectPartidaEspecificaCvePresupuestalServ' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestalServ"/>'+
                        '<select id="selectPartidaEspecificaCvePresupuestalServ' + orden + '" name="selectPartidaEspecificaCvePresupuestalServ' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestalServ"><option value="0">Clave Presupestal</option></select>'+
                    '</li>'+
                    /*'<li class="w10p"><span><label>Presupuesto: </label></span></li>'+
                    '<li class="w10p" id="idAddPresupuesto' + orden + '">'+
                        '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH" placeholder="Presupuesto" type="hidden" />'+
                        '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                    '</li>'+
                    '<li class="w10p" id="idValidaPresupuesto' + orden + '" >'+
                        '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto Suficiente" type="text" />'+
                    '</li>'+*/
                '</ol>'+
            '</div>'+
            '<div id="idPopupEditor'+orden+'" class="idPopupEditor" style="display: none;">'+
                '<div class="content-popup">'+
                    '<div class="popupEditorClose" onclick="fnEditorTextoClose()"><a href="#" id="idPopupEditorClose" onclick="fnEditorTextoClose()">x</a></div>'+
                    '<div class="hA">'+
                        '<h2>Descripción del Servicio</h2>'+
                        '<textarea class="w100p" name="" id="idLongText'+orden+'" cols="" rows="20"></textarea>'+
                        '<div id="idBtnGuardarLongText'+orden+'" class="btn btn-default" onclick="fnModificarServicio('+noReq+','+orden+');fnEditorTextoClose();">Guardar</div>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="popup-overlay" style="display: none;"></div>');
            
    $('#idMainListContentServicio').append(containerServicio);

    $("#btRemoveServicio" + orden).click(function() { 
        $(this).parent().parent().remove();
        $("#idCvePresupuestal" + orden).remove();
        fnEliminarElemento(noReq,orden);
    });

    if(status == 'Autorizado' || status == 'Cancelado' || status == 'Original'){
        $("#idBtnAgregarServicio").addClass('hide');
        $("#idAnexoTap").addClass('hide');
        $("#btRemoveServicio" + orden).addClass('hide');
        $("#btRemoveServicio" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonserv" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }else if((perfilid == 9 ) && (status == 'Validar' || status == 'PorAutorizar' )){
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveServicio" + orden).addClass('hide');
        $("#btRemoveServicio" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonserv" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }else if((perfilid == 10 ) && ( status == 'PorAutorizar' )){
        $("#idBtnAgregarServicio").addClass('hide');
        $("#btRemoveServicio" + orden).addClass('hide');
        $("#btRemoveServicio" + orden).attr('disabled', 'disabled');
        $("#btnanexorenglonserv" + orden).addClass('hide');
        $("#idFechaEntrega").attr('disabled', 'disabled');
    }

    if(!$.isNumeric(idPartida)){
        if (!fnCargarPartidaServicio(periodoR, tagref, orden)) {
            muestraMensaje("No existen claves de partida en servicios para la UR y UE seleccionada.", 3, 'divMensajeOperacion');
        }

        /*$("#selectCvePartidaEspecificaServ" + orden).empty();
        $("#selectCvePartidaEspecificaServ" + orden).append('<option value="0">Cve...</option>' + contenidosCvePartidaEspecificaServ);*/
        //fnFormatoSelectGeneral("#selectCvePartidaEspecificaServ"+orden);
        //$("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');
        //$("#selectDescPartidaEspecificaServ" + orden).empty();
        //$("#selectDescPartidaEspecificaServ" + orden).append('<option value="0">Partida...</option>' + contenidoDescPartidaEspecificaServ);
        //fnFormatoSelectGeneral("#selectDescPartidaEspecificaServ"+orden);
        //$("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');

        $("#addedPEServicio" + orden).prop("readonly", true);
        $("#addedPEServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).prop("readonly", true);
        $("#btLongDescServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).removeAttr("onclick");
        //$("#addedReglonServicio" + orden).prop("readonly", true);
        //$("#addedReglonServicio" + orden).attr('disabled', 'disabled');
        //$("#btnanexorenglonserv" + orden).prop("readonly", true);
        //$("#btnanexorenglonserv" + orden).attr('disabled', 'disabled');

    }else{
        $("#numServicio" + orden).html(orden);
        $("#numServicio" + orden).prop("readonly", true);
        $("#numServicio" + orden).attr('disabled', 'disabled');
        $("#selectCvePartidaEspecificaServ" + orden).empty();
        $("#selectCvePartidaEspecificaServ" + orden).html('<option value="'+idPartida+'">'+idPartida+'</option>');
        $("#selectDescPartidaEspecificaServ" + orden).empty();
        $("#selectDescPartidaEspecificaServ" + orden).html('<option value="'+descPartida+'">'+descPartida+'</option>');
        $("#cveServicio"+ orden).val(""+idItem);
        $("#descServicio"+ orden).val(""+descItem);
        $("#addedCantidadServicio" + orden).html('1');
        $("#addedPEServicio" + orden).val(""+precio);
        $("#idLongText" + orden).val(""+descLarga);
        $("#idBtnGuardarLongText" + orden).removeAttr("onclick");
        //$("#idLongText" + orden).html(descLarga);
        $("#idCvePresupuestal" + orden).removeClass("hide");
        //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).val(clavepresupuestal);
        
        for (var clave in clavepresupuestal) {
            contenidoClaves= '<option value="'+clavepresupuestal[clave].cvePresupuestal+'">'+clavepresupuestal[clave].cvePresupuestal+'</option>';
        }

        $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
        $("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoClaves);
        fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestalServ"+orden);
        $("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect("rebuild");

        $("#addedReglonServicio" + orden).val(""+renglon);
        //$("#validaPresupuesto" + orden).prop("readonly", true);
        //$("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        $("#addedPEServicio" + orden).prop("readonly", true);
        $("#addedPEServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).prop("readonly", true);
        $("#btLongDescServicio" + orden).attr('disabled', 'disabled');
        $("#btLongDescServicio" + orden).removeAttr("onclick");
        //$("#addedReglonServicio" + orden).prop("readonly", true);
        //$("#addedReglonServicio" + orden).attr('disabled', 'disabled');
        //$("#btnanexorenglonserv" + orden).prop("readonly", true);
        //$("#btnanexorenglonserv" + orden).attr('disabled', 'disabled');
        //$("#btnanexorenglonserv" + orden).removeAttr("onclick");
        //$("#btnanexorenglonserv" + orden).removeClass('hide');
        //alert(viewAnexo);
        if(viewAnexo == 0){
            $("#btnanexorenglonserv"+orden).addClass('hide');
            //$("#btnanexorenglonserv"+cont).removeClass('hide');
        }else{
            $("#btnanexorenglonserv"+orden).removeClass('hide');
            //$("#btnanexorenglonserv"+cont).addClass('hide');
        }
    }

    fnFormatoSelectGeneral("#selectCvePartidaEspecificaServ"+orden);
    fnFormatoSelectGeneral("#selectDescPartidaEspecificaServ"+orden);
}

function fnClavePartidaServicio(orden, elemento) {
    muestraCargandoGeneral();

    var tagref = $("#selectUnidadNegocio").val();
    var partida = $("#selectCvePartidaEspecificaServ" + orden).val();
    var regresadatos= false;
    var contenidoPartidaEspDescServ = "";
    var contenidoCveServ = "";
    var contenidoPrecioEServ = "";
    var contenidoCvePresupuestalServ = "";

    if (elemento == "selectDescPartidaEspecificaServ"+orden) {
        partida = $("#selectDescPartidaEspecificaServ" + orden).val();
        contenidoCvePresupuestalServ= $("#selectDescPartidaEspecificaServ"+orden+" option:selected").attr("data-clave");

        $("#selectCvePartidaEspecificaServ" + orden).empty();
        $("#selectCvePartidaEspecificaServ" + orden).append("<option value='" + partida + "'>" + partida + "</option>");
        $("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');

        regresadatos= true;
    } else {
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
            data: dataObj

        }).done(function(data) {
            if (data.result) {
                dataJson = data.contenido.datos;
                //console.log( "dataJson: " + JSON.stringify(dataJson) );

                for (var info in dataJson) {
                    contenidoPartidaEspDescServ += "<option value='" + dataJson[info].idPartidaEspecifica + "'>" + dataJson[info].descPartidaEspecifica + "</option>";
                    
                    contenidoCveServ = dataJson[info].idServicio;
                    contenidoPrecioEServ = dataJson[info].precioEstimado;
                    contenidoCvePresupuestalServ += "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";

                    regresadatos= true;
                }

                if (contenidoPartidaEspDescServ !== "") {
                    $("#selectDescPartidaEspecificaServ" + orden).empty();
                    $("#selectDescPartidaEspecificaServ" + orden).append(contenidoPartidaEspDescServ);
                    $("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');
                }
            }

        }).fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
            //alert(result);
        });     
    }

    $("#cveServicio"+ orden).empty();
    $("#cveServicio"+ orden).val(""+contenidoCveServ);
    
    $("#addedCantidadServicio" + orden).html(1);    // cantidad por default
    $("#addedPEServicio" + orden).val(contenidoPrecioEServ);   // valor unitario del servicio
    $("#addedPEServicio" + orden).prop("readonly", false);
    $("#addedPEServicio" + orden).attr('disabled', false);

    $("#btLongDescServicio" + orden).prop("readonly", false);
    $("#btLongDescServicio" + orden).attr('disabled', false);
    $("#btLongDescServicio" + orden).attr("onclick","fnEditorTextoOpen("+orden+")");
    $("#addedReglonServicio" + orden).prop("readonly", false);
    $("#addedReglonServicio" + orden).attr('disabled', false);
    $("#btnanexorenglonserv" + orden).prop("readonly", false);
    $("#btnanexorenglonserv" + orden).attr('disabled', false);

    fnCargaPartidaCvePptoServicios(orden, tagref, partida);
    
    //$("#idCvePresupuestal" + orden).removeClass("hide");
    //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).val(contenidoCvePresupuestalServ);
    //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
    //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoCvePresupuestalServ);
    //fnFormatoSelectGeneral("#selectPartidaEspecificaCvePresupuestalServ" + orden);
    //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect('rebuild');
    //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).prop("readonly", true);
    //$("#selectPartidaEspecificaCvePresupuestalServ" + orden).attr('disabled', 'disabled');
    //$("#addPresupuesto"+ orden).html(fnObtenerPresupuestoDisponible(orden));

    fnModificarServicio(noReq,orden);

    if (!regresadatos) {
        muestraMensaje("No existe clave y descripción de servicio.", 3, "divMensajeOperacion");
    }

    ocultaCargandoGeneral();

    return regresadatos;
}

/**Modifica el elemento creado en la seccion Servicios*/
function fnModificarServicio(r,orden){
    var r = $("#idtxtRequisicion").val();
    var ic = $("#cveServicio" + orden).val();
    var fe = $("#idFechaEntrega").val();
    var id = $("#descServicio"+ orden).val();
    var up = $("#addedPEServicio" + orden).val();
    var cp = $("#selectPartidaEspecificaCvePresupuestalServ" + orden).val();
    var rn = $("#addedReglonServicio" + orden).val();
    var lt = $("#idLongText" + orden).val();

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
        renglon: rn
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
            fnActualizaPresupuestoServicio();
            //muestraMensaje('Se modifico la Requisición con el número: ' + r, 1, 'divMensajeOperacion', 5000);
        } else {
            muestraMensaje('No se Modifico la Requisición ', 3, 'divMensajeOperacion', 5000);
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log( result );
    });
}

function fnGuardarRequisicion(){
    var noReq = $("#idtxtNoRequisicion").val();
    var idReq = $("#idtxtRequisicion").val();
    var comments = $("#txtAreaObs").val();
    var fechaFrom = $("#idFechaElaboracion").val();
    var fechaTo = $("#idFechaEntrega").val();
    var tagref = $("#selectUnidadNegocio").val();
    var ue = $("#selectUnidadEjecutora").val();
    var dividirA = $('input:checkbox[name=dividirAsignacion]:checked').val();
    if( dividirA == 'on'){
        dividirA = 'P';
    }else{
        dividirA = 'T';
    }

    $("#idMainListContentArticulo select[id*=selectCvePartidaEspecifica]").each(function(index, el) {
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecifica','');

        fnModificarArticulo(idReq, numero);
    });

    $("#idMainListContentServicio select[id*=selectCvePartidaEspecificaServ]").each(function(index, el) {
        numero= $(this).attr('id');
        numero= numero.replace('selectCvePartidaEspecificaServ','');

        fnModificarServicio(idReq, numero);
    });

    // validar datos de captura en pantalla
    if (fnValida()) {
        if(idReq == ''){
            muestraMensaje('No existe una Requisición para guardar', 3, 'divMensajeOperacion', 5000);
        }else{
            if(noReq > 0){
                fnGuardarRequisicionExistente(idReq, noReq, comments, fechaFrom, fechaTo, tagref, ue, dividirA);
            }else{
                fnGuardarRequisicionNueva(idReq, comments, fechaFrom, fechaTo, tagref, ue, dividirA);
            }
        }
    }
}

function fnValida(){
    var numero= 0;
    var mensaje= "";
    var respuesta= true;

    // recorrer elementos de la pestaña de articulos
    $("#idMainListContentArticulo div[id*=idCvePresupuestal]").each(function(index, el){
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');

        if ($("#selectCvePartidaEspecifica"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene seleccionada partida especifica.</p>';
        }

        if ($("#selectCveArticulo"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene seleccionada la clave de artículo.'+"<br>";
        }

        if ($("#selectArticulo"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene seleccionada la descripción del artículo.'+"<br>";
        }

        if ($("#addedCantidadArticulo"+numero).val() == 0 || $("#addedCantidadArticulo"+numero).val() == "" || $("#addedCantidadArticulo"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene capturada la cantidad.'+"<br>";
        }

        if ($("#addedPEArticulo"+numero).val() == 0 || $("#addedPEArticulo"+numero).val() == "" || $("#addedPEArticulo"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene capturado el precio.'+"<br>";
        }
    });

    $("#idMainListContentServicio div[id*=idCvePresupuestal]").each(function(index, el){
        numero= $(this).attr('id');
        numero= numero.replace('idCvePresupuestal','');

        if ($("#selectCvePartidaEspecificaServ"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene seleccionada una partida especifica.'+"<br>";            
        }

        if ($("#selectDescPartidaEspecificaServ"+numero).val() == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene seleccionada la descripción de la partida específica.'+"<br>";
        }

        if ($("#descServicio"+numero).val() == 0 || $("#descServicio"+numero).val() == "" || $("#descServicio"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene capturada una descripción larga del servicio.'+"<br>";
        }

        if ($("#addedPEServicio"+numero).val() == 0 || $("#addedPEServicio"+numero).val() == "" || $("#addedPEServicio"+numero).length == 0) {
            mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp;El renglón '+ numero +' no tiene capturado el precio.'+"<br>";
        }
    });

    if (mensaje.length > 0) {
        muestraMensaje(mensaje, 3, 'divMensajeOperacion');
        respuesta= false;
    }

    return respuesta;
}

function fnGuardarRequisicionNueva(idReq, comments, fechaFrom, fechaTo, tagref, ue, dividirA){
    //alert(dividirA);
    if (fnValidarRequisicion(idReq)) {
        dataObj = {
            option: 'guardarRequisicionNueva',
            noReq: idReq,
            status: 'Capturado',
            comments: comments,
            fechaFrom: fechaFrom,
            fechaTo: fechaTo,
            tagref: tagref,
            ue:ue,
            dividirA: dividirA
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
                noReq= data.contenido;
                muestraMensaje('Se guardó la Requisición ' + noReq, 1, 'divMensajeOperacion', 5000); 
                fnObtenerStatusReq(idReq);

                $("#idtxtRequisicionView").html(noReq);
                $("#idtxtNoRequisicion").val(""+noReq);
                $("#idtxtRequisicionView").removeClass("hide");
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                $("#idStatusReq").removeClass("hide");
                $("#idStatusReq").prop("readonly", true);
                $("#idStatusReq").attr('disabled', 'disabled');
                //fnReindexar();
                //location.replace("./PO_SelectOSPurchOrder.php");
                //fnLoadRequisicion(idReq);
            }else{
                muestraMensaje('No se guardó la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
    }else{
        fnValida();
    }
    
}

function fnGuardarRequisicionExistente(idReq, requistionno, comments, fechaFrom, fechaTo, tagref, ue, dividirA){
    var status = $("#idStatusReq").val();
    //alert(dividirA);
    if (fnValidarRequisicion(idReq)) {
        dataObj = {
                option: 'guardarRequisicion',
                noReq: idReq,
                status: status,
                comments: comments,
                fechaFrom: fechaFrom,
                fechaTo: fechaTo,
                tagref: tagref,
                ue:ue,
                dividirA: dividirA
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
                //noReq= data.contenido;
                //alert(requistionno);
                muestraMensaje('Se guardó la Requisición '+ requistionno, 1, 'divMensajeOperacion', 5000); 
                //$("#idtxtRequisicionView").html(noReq);
                $("#idtxtRequisicionView").removeClass("hide");
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                //fnReindexar();
                //location.replace("./PO_SelectOSPurchOrder.php");
                //fnLoadRequisicion(idReq);

                ocultaCargandoGeneral();
            }else{
                muestraMensaje('No se guardó la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });
    }
}

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
                location.replace("./PO_SelectOSPurchOrder.php?");
            }else{
                muestraMensaje('No se cancelo ningún movimiento en la Requisición ', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
        });  

}

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

function fnRegresarPanelRequisicion(){
    location.replace("./PO_SelectOSPurchOrder.php");
}

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
            //console.log( "dataIndex: " + JSON.stringify(dataIndex) );
            //alert(JSON.stringify(dataIndex));
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
            //alert("Exito");
        }else{
            //alert("Error");
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

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

function fnAgregaRenglon(ordenAnexo,ordenReq,ordenPartida){
    //$("#"+ordenAnexo+"_"+ordenPartida).attr('disabled',false);
    //alert("Renglón:" + ordenPartida + "Anexo:" + ordenAnexo + "Orden: " + ordenReq);
   
    var contAnexoArt = $("#addedReglonArticulo"+ordenReq).val();
    var contAnexoServ = $("#addedReglonServicio"+ordenReq).val();
    var noReq = $("#idtxtRequisicion").val();
    //alert(contAnexoArt + " - " + contAnexoServ);
    var contAnexo = "";
    if((contAnexoArt != '' || contAnexoArt == '') && (typeof contAnexoServ === 'undefined')){
        contAnexo = contAnexoArt;
    } else if ((typeof contAnexoArt === 'undefined') && (contAnexoServ != '' || contAnexoServ == '' )){
        contAnexo = contAnexoServ;
    }
    if (contAnexo == ''){
        $("#addedReglonArticulo"+ordenReq).val(""+ordenPartida);
        $("#addedReglonServicio"+ordenReq).val(""+ordenPartida);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).attr('disabled',true);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).prop("readonly", true);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).removeAttr("onclick");
        //$("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).unbind( "click" );
        //$("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).off( "click", "**" );.removeAttr("onclick");
        //$("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).addClass( "hide" );
        //$("#addedReglonArticulo"+ordenReq).trigger('change');
        fnModificarArticulo(noReq,ordenReq);
        fnModificarServicio(noReq,ordenReq);
    }else{
        contAnexo = $("#addedReglonArticulo"+ordenReq).val();
        if(typeof contAnexo === 'undefined'){
            contAnexo = $("#addedReglonServicio"+ordenReq).val();
            $("#addedReglonServicio"+ordenReq).val(""+contAnexo+","+ordenPartida);
            $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).attr('disabled',true);
            $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).prop("readonly", true);
            $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).removeAttr("onclick");
            fnModificarServicio(noReq,ordenReq);
        }else{
            $("#addedReglonArticulo"+ordenReq).val(""+contAnexo+","+ordenPartida);
            $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).attr('disabled',true);
            $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).prop("readonly", true);
            $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).removeAttr("onclick");
            fnModificarServicio(noReq,ordenReq);
        }

    }
}

function fnMuestraAnexo(orden){
    var idReq = $("#idtxtRequisicion").val();
    var contAnexo = $("#addedReglonArticulo"+orden).val();
    if(typeof contAnexo === 'undefined'){
        contAnexo = $("#addedReglonServicio"+orden).val();
    }

    dataObj = { 
            option: 'muestraInfoAnexo',
            idReq: idReq,
            orden: orden
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
            
            dataAnexoJason = data.contenido.datos;
            var idAnexo = "";
            var idPartida = "";
            var nombreBienServ = "";
            var descBienServ = "";
            var ordenPd = "";
            var partida = "";
            $("#divAnexoTabla").empty();

            $("#divAnexoTabla").append('<div id="divAnexoTablaHeader" class="h25"><div class="w10p plr5 fl">#</div><div class="w10p plr5 fl">Nº Elemento</div><div class="w10p plr5 fl">Ánexo</div><div class="w30p plr5 fl">Bien_Servicio</div><div class="w40p pr5 fl">Descripción</div></div>');
            if(contAnexo != ''){
                var cadena = contAnexo, 
                separador = ",", 
                arregloDeSubCadenas = cadena.split(separador);
                var contarexistencia=0;
                
                for (var info in dataAnexoJason) {
                        idAnexo = dataAnexoJason[info].idanexo;
                        idPartida = dataAnexoJason[info].idpartida;
                        ordenPd = dataAnexoJason[info].ordenpd;
                        nombreBienServ = dataAnexoJason[info].bienServicio;
                        descBienServ = dataAnexoJason[info].descripcion_bien_serv;
                        partida = dataAnexoJason[info].partida_esp;
                        contarexistencia=  fnChecarExistencia(arregloDeSubCadenas,idPartida);

                           if(contarexistencia>0){
                                $("#divAnexoTabla").append('<div class="h30"><div id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w10p btn btn-default btn-xs plr5 fl"  disabled="disabled">'+idPartida+'</div><div class="w10p plr5 fl">'+orden+'</div><div class="w10p plr5 fl">'+idAnexo+'</div><div class="w30p plr5 fl"><textarea title="'+nombreBienServ+'" class="w100p" rows="1">'+nombreBienServ+'</textarea></div><div class="w40p pr5 fl"><textarea title="'+descBienServ+'" class="w95p" rows="1">'+descBienServ+'</textarea><span class="tooltipRC w250 h250 overflowX overflowY">'+descBienServ+'</span></div></div>');
                            }else{
                                $("#divAnexoTabla").append('<div class="h30"><div id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w10p btn btn-default btn-xs plr5 fl" onclick="fnAgregaRenglon('+ idAnexo +','+ ordenPd +','+ idPartida +')">'+idPartida+'</div><div class="w10p plr5 fl">'+orden+'</div><div class="w10p plr5 fl">'+idAnexo+'</div><div class="w30p plr5 fl"><textarea title="'+nombreBienServ+'" class="w100p" rows="1">'+nombreBienServ+'</textarea></div><div class="w40p pr5 fl"><textarea title="'+descBienServ+'" class="w95p" rows="1">'+descBienServ+'</textarea><span class="tooltipRC w250 h250 overflowX overflowY">'+descBienServ+'</span></div></div>');
                                
                            }
                    }
                
            }else{
                for (var info in dataAnexoJason) {
                    idAnexo = dataAnexoJason[info].idanexo;
                    idPartida = dataAnexoJason[info].idpartida;
                    ordenPd = dataAnexoJason[info].ordenpd;
                    nombreBienServ = dataAnexoJason[info].bienServicio;
                    descBienServ = dataAnexoJason[info].descripcion_bien_serv;
                    partida = dataAnexoJason[info].partida_esp;
                    $("#divAnexoTabla").append('<div class="h30"><div id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w10p btn btn-default btn-xs plr5 fl" onclick="fnAgregaRenglon('+ idAnexo +','+ ordenPd +','+ idPartida +')">'+idPartida+'</div><div class="w10p plr5 fl">'+orden+'</div><div class="w10p plr5 fl">'+idAnexo+'</div><div class="w30p plr5 fl"><textarea title="'+nombreBienServ+'" class="w100p" rows="1">'+nombreBienServ+'</textarea></div><div class="w40p pr5 fl"><textarea title="'+descBienServ+'" class="w95p" rows="1">'+descBienServ+'</textarea><span class="tooltipRC w250 h250 overflowX overflowY">'+descBienServ+'</span></div></div>');
                }
            }

        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function fnChecarExistencia(arregloDeSubCadenas,idPartida){
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
    //console.log("fnAgregarCatalogoModal");

    var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Anéxo Técnico</h3>';
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
    /*$('.idPopupEditor').css("display","block");
    $('.idPopupEditor').fadeIn('slow');
    $('.popup-overlay').fadeIn('slow');
    $('.popup-overlay').height($(window).height());*/
    //$("#idEditorContent").load('editortexto.php');
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
            proceso: 'getFechaServidor',
          };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
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

function fnFijarFechaSiguiente(dias){
    dataObj = { 
            proceso: 'getFechaServidorSiguiente',
            numerodias:dias
          };
    $.ajax({
        async:false,
        cache:false,
          method: "POST",
          dataType:"json",
          url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
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

function fnCargarPartidaProducto(periodo,tagref,orden){
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
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          dataJson = data.contenido.datos;
          //console.log( "dataJson: " + JSON.stringify(dataJson) );

          contenidosCvePartidaEspecifica += '<option value="0">Partida...</option>';

          for (var info in dataJson) {
            contenidosCvePartidaEspecifica += "<option value='"+dataJson[info].partidacalculada+"'>"+dataJson[info].partidacalculada+"</option>";
            /*contenidoDescPartidaEspecifica += "<option value='"+dataJson[info].partidadescripcion+"'>"+dataJson[info].partidadescripcion+"</option>";
            contenidoPartidaEspecificaCvePresupuestal += "<option value='"+dataJson[info].clavePresupuestal+"'>"+dataJson[info].clavePresupuestal+"</option>";*/

            regresadatos= true;
          }

          $('#selectCvePartidaEspecifica'+orden).empty();
          $('#selectCvePartidaEspecifica'+orden).append(contenidosCvePartidaEspecifica);
      }
    })
    .fail(function(result) {
      // console.log("ERROR");
      // console.log( result );
    });

    return regresadatos;
}

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
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
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
          
          $('#selectDescPartidaEspecificaServ'+orden).empty();
          $('#selectDescPartidaEspecificaServ'+orden).append(contenidoDescPartidaEspecificaServ);
      }
    })
    .fail(function(result) {
      // console.log("ERROR");
      // console.log( result );
    });

    return regresadatos;
}
/*
$(document).on('change','#idMainListContentArticulo',function(){
    fnActualizaPresupuesto();
});

$(document).on('change','#idMainListContentServicio',function(){
    fnActualizaPresupuestoServicio(); 
});
*/

function componenteNuevo() {
    var trans='all';
     trans= $("#idtxtRequisicion").val();
     $('#transnoArchivo').val(trans);

     //fnLimpiarTabla('divTablaArchivos', 'divDatosArchivos');
          //      fnAgregarGridv2(datosArchivos,'divDatosArchivos','b');
    //<input type="file"  class="btn bgc8"  name="combina('archivos','[]')"  id="combina('cargarMultiples')"  multiple="multiple"  style="display none;"/>-->
    html='<div class="cargarArchivosComponente" >'+
    '<input type="hidden" id="esMultiple" name="esMultiple" value="0">'+
    '<input type="hidden" value="" name="componente" id="componenteArchivos"/>'+
    '<input type="hidden" value="2265" id="funcionArchivos" name="funcionArchivos"/>'+
    '<input  type="hidden"  value="19" id="tipoArchivo"/>'+
    '<input  type="hidden"  value="0" id="transnoArchivo"/>'+
    '<div id="mensajeArchivos"> </div>'+
    '<div  id="subirArchivos"  class="col-md-12">'+
        '   <div class="col-md-12" style="color:#fff !important;">'+
            '      <div class="col-md-6">'+
                '  <div id="tipoInputFile"> </div>'+

                '<button  class="btn bgc8" id="cuadroDialogoCarga" onclick="fnCargarArchivos()">'+
                    '   <span class="glyphicon glyphicon-file"></span>'+
                    '  Cargar archivo(s)'+
                    '</button >'+
                '<br>'+
                '<br/>'+
                '<button id="enviarArchivosMultiples" class="btn bgc8" style="display:none;" >Subir</button>'+
                '<br/>'+
                '<br/>'+
                '</div>'+
            '<br>'+
            '</div>'+
        '<div id="muestraAntesdeEnviar" class="col-md-12 col-xs-12"> </div>'+
        '<br/> <br/>'+
        '</div>'+
    '<div id="enlaceDescarga" class="col-md-12 col-xs-12"> </div>'+
    '<div id="accionesArchivos"  style="color:#fff !important;display:none;">'+
        '<div class="col-md-3">'+
            '<button id="eliminarMultiples" class="btn bgc8" onclick="fnBorrarConfirmaArch()" >Eliminar</button>'+
            '<br/>'+
            '</div>'+
        '<div class="col-md-3">'+
            '<button id="descargarMultiples" class="btn bgc8" onclick="fnProcesosArchivosSubidos("descargar")" >Descargar</button>'+
            '<br/>'+
            '</div>'+
        '</div>'+
    '<div name="divTablaArchivos" id="divTablaArchivos" class="col-md-12 col-xs-12">'+
        '<div name="divDatosArchivos" class="col-md-12 col-xs-12" id="divDatosArchivos"></div>'+
        '</div>'+
    '<div class="modal fade" id="ModalBorrarArchivos"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel">'+
        '   <div class="modal-dialog" role="document" name="ModalGeneralTam" id="ModalGeneralTam">'+
            '     <div class="modal-content">'+
                '     <div class="navbar navbar-inverse navbar-static-top">'+
                    '          <div class="col-md-lg menu-usuario">'+
                      '  <span class="glyphicon glyphicon-remove" data-dismiss="modal"></span>'+
                   ' </div>'+
                    '<div id="navbar" class="navbar-collapse collapse">'+
                     '   <div class="nav navbar-nav">'+
                      '      <div class="title-header">'+
                       '         <div id="ModalBorrarArchivos_Titulo" ></div>'+
                        '    </div>'+
                        '</div>'+
                    '</div>'+
                    '<div class="linea-verde"></div>'+
                '</div>'+
                '<div class="modal-body" id="ModalBorrarArchivos_Mensaje">'+
                 '   <div class="col-md-9" id="listaBorrarArchivos" >'+
                  '      <h3>¿Desea borrar los archivos seleccionados?</h3>'+
                   ' </div>'+
                '</div>'+
                '<br> <br> <br>'+
                '<div class="modal-footer">'+
                 '   <div class="col-xs-6 col-md-6 text-right">'+
                  '      <div id="procesandoPagoEspere"> </div> <br>'+
                       ' <button id="btnConfirmarEliminar" name="btnConfirmarEliminar" type="button" title="" class="btn btn-default botonVerde"  onclick="fnProcesosArchivosSubidos("eliminar")" >'+
                        '    Eliminar'+
                        '</button>'+

                        '<button id="btnCerrarConfirma" name="btnCerrarConfirma" type="button" title="" class="btn btn-default botonVerde"   >'+
                        'Eliminar'+
                        '</button>'+
                    '</div>'+
                '</div>'+
            '</div>'+
        '</div>'+
    '</div>'+
'</div>';
 
  ///var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
      //      muestraModalGeneral(4, titulo,html);

   //$('#verAnexos').append(html);
    //fnEjecutarVueGeneral('verAnexos');
   
}

 $(document).on('keypress', '.soloNumeros', function(event) {

    var text = $(this).val();
    var cuenta = (text.match(/./g) || []).length;
    //cuenta.match( new RegExp('.','g') ).length;
    //var cuenta = /\.{1}\.+/g.test(text);
    // console.log("->"+cuenta+" ");
    if(text.includes(".")){

      var texto2=[];
      texto2=(text.split("."));
       //despues del punto solo 2 con el length. y con el or includes. la primera parte ya tiene  punto decimal
      if (texto2[1].length >1 || texto2[0].includes(".")) {
              event.preventDefault();
      }
    }    

 });

 $("input[type=text]").focus(function(){       
  this.select();
});

 $("input[type=number]").focus(function(){       
  this.select();
});