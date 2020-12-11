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

var url = "modelo/Captura_Requisicion_modelo.php";
var cont = 0; // contador de elementos.
var iCntPI = 0; // contador de elementos con presupuesto insuficiente.
var iCntND = 0; // contador de elementos sin disponibilidad en almacen.
var proceso = ""; // Valor del proceso que se llamara al modelo.
var ordenPreError = "";
var ordenDispError = "";
//var mbflag = ""; // Bandera que indica si es Producto o Servicio.
var noReq = noRequisicionGeneral;
//var ultimamodificacion =fnPreData(noReq);
var urlCR = urlCReq;

var estatusGenerarLayout = 4;
var funcionGenerarLayout = 2265;
var typeGenerarLayout = 19;
var tipoLayout = 1;
var periodoR = periodoReq;

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
/* variables globales fin */

/*  Sección de document ready Inicio */

$(document).ready(function() {
    $("#idtxtRequisicionView").addClass("hide");
    $("#idtxtRequisicionView").prop("readonly", true);
    $("#idtxtRequisicionView").attr('disabled', 'disabled');

    $("#idFechaElaboracion").prop("readonly", true);
    $("#idFechaElaboracion").attr('disabled', 'disabled');

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
            fnLimpiar(noReq);
            fnLoadRequisicion(noReq);
        }
    }
    
    $('#idPopupEditorClose').click(function() {
        $('.idPopupEditor').fadeOut('slow');
        $('.popup-overlay').fadeOut('slow');
        return false;
    });
});

function fnCalculaTotal(orden){
    var a = $("#addedCantidadArticulo" + orden).val();
    var b = $("#addedPEArticulo" + orden).val();
    var e = $("#contDispArt" + orden).val();
    var idReq = $("#idtxtRequisicion").val();
    //alert(e);
    var t = a * b;
    var p = $("#addPresupuestoH"+orden).val();
    var d = p - t;
    var pe = $("#selectCvePartidaEspecifica" + orden).val();
    $("#p"+pe).val(d);
    
    //var d = $("#addPresupuestoH"+orden).val();
    var s = e - a;
    
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
 
    fnModificarArticulo(idReq,orden);
}
    
function fnValidaPresupuesto(orden) {
    var a = $("#addedCantidadArticulo" + orden).val();
    var b = $("#addedPEArticulo" + orden).val();
    var t = a * b;
    var p = $("#addPresupuestoH"+orden).val();
    var d = p - t;
    var idReq = $("#idtxtRequisicion").val();
    var pe = $("#selectCvePartidaEspecifica" + orden).val();
    $("#p"+pe).val(d);

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
    
    fnModificarArticulo(idReq,orden);
};

function fnClaveArticulo(orden) {
    muestraCargandoGeneral();

    var cveprod = $("#selectCveArticulo" + orden).val();
    //alert(cveprod);
    var tagref = $("#selectUnidadNegocio").val();
    var idReq = $("#idtxtRequisicion").val();
    //alert(tagref);

    dataObj = { 
        option: 'mostrarCveArticuloDatos',
        datocveart: cveprod,
        datodescart: '',
        datotagref: tagref
    };
    $.ajax({
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

            $("#contDispArt" + orden).val(""+contDispArt);
            
            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append(contDescArt);
            $("#selectArticulo" + orden).multiselect('rebuild');

            $("#addedUMArticulo" + orden).empty();
            $("#addedUMArticulo" + orden).append(contUnidadArt);
            $("#addedUMArticulo" + orden).prop("readonly", true);
            $("#addedUMArticulo" + orden).attr('disabled', 'disabled');

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

/*  Sección de document ready Fin       */

/*      Sección Nueva Requisición Inicio    */
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
    //alert("cvePArt: "+cvePArt);
    var cvePServ = $("#selectPartidaEspecificaCvePresupuestalServ"+orden).val();
    //alert("cvePServ: "+ cvePServ);
    if(typeof cvePArt === 'undefined'){
        var cveP = cvePServ;
        //alert("cvePServ: "+ cveP);
    }else if(typeof cvePServ === 'undefined') {
        var cveP = cvePArt;
        //alert("cvePArt: "+ cveP);
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
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            info=data.contenido.datos;
            var nombreMes = data.contenido.nombreMes;
            //console.log("presupuesto: "+JSON.stringify(info));
            var clave = "";
            //var clave2 = "";
            //clave2 =info[0].accountcode;
            if(disp > 0){
                clave = disp;
            }else{
                clave =info[0][nombreMes];    
            }
            

            //alert("clave:"+clave);
            //alert("clave2:"+clave2);
            /*$("#addPresupuestoH"+orden).val(""+clave);
            $("#addPresupuesto"+orden).html(clave);
            $("#addPresupuesto"+orden).prop("readonly", true);*/
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
    var mbflag = mbflag;
    var obs = $("#txtAreaObs").val();

    //alert("Tipo de elemento a agregar" + mbflag);
    //alert("folio"+nFolio);
    //alert("fecha_Elabora"+fElabora);
    //alert("fecha_Entrega"+fEntrega);
    //alert("unidad de negocio" + unidadNegocio);
    //alert("dependencia" + razonSocial);
    if(noRequisicion > 0){
        //alert(" agregar elementos");
        
        fnRequisicionExistente(noRequisicion,mbflag);
        
    }else{
        //alert("Crear nueva requisicion");
        fnNuevaRequisicion(fElabora,fEntrega,razonSocial,unidadNegocio,mbflag,obs);
        //fnObtenerPerfilUsr();
    }  
}

/**
 * Crea una nueva requisición 
 */
function fnNuevaRequisicion(fElabora,fEntrega,razonSocial,unidadNegocio,mbflag,obs){
    if ((fElabora == "") || (fEntrega == "") || (razonSocial == "") || (unidadNegocio == "")) {
        muestraMensaje('Es necesario seleccionar las fechas y la Unidad Responsable para crear una nueva Requisición', 3, 'msjValidacion', 5000);
        return false;
    }else{
        muestraCargandoGeneral();
            //var period = $("#idFechaElaboracion").data("periodo");
            var tagref = $("#selectUnidadNegocio").val();
            fnCargarPartidaProducto(periodoR,tagref); 
            fnCargarPartidaServicio(periodoR,tagref);
        dataObj = {
            option: 'agregarCapturaRequisicion',
            fechaElabora: fElabora,
            fechaEntrega: fEntrega,
            rs: razonSocial,
            un: unidadNegocio,
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
                //console.log(data);
            if (data.result) {
                //Si trae informacion
                dataR = data.contenido.datos
                //muestraMensaje('Se agrego la Requisición con el número: '+ data.contenido, 1, 'divMensajeOperacion', 5000);
                //var noReq = dataR[0].requisitionno;
                var idReq = dataR[0].orderno;
                //alert(noReq);
                //$("#idtxtRequisicion").css("display","none");
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
                //ALgun error
                muestraMensaje('No se agrego la Requisición', 3, 'divMensajeOperacion', 5000);
                ocultaCargandoGeneral();
                
            }
        }).fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
        });
        //ocultaCargandoGeneral();
    }
}
/**
 * Crea noevos elementos a una requisición existente
 */
function fnRequisicionExistente(noRequisicion,mbflag){
    //alert("mbflag" +mbflag);
    muestraCargandoGeneral();

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

                fnAgregarElemento(mbflag,idReq,fEntrega,orden);
                ocultaCargandoGeneral();
                
            }else{
                ocultaCargandoGeneral();
            }

        }).fail(function(result) {
            ocultaCargandoGeneral();
            console.log("ERROR");
            console.log(result);
            //alert(result);
        });
}

function fnLoadRequisicion(noReq){
    muestraCargandoGeneral();
    fnObtenerStatusReq(noReq);
    $("#idtxtRequisicionView").removeClass("hide");
    $("#idtxtRequisicionView").prop("readonly", true);
    $("#idtxtRequisicionView").attr('disabled', 'disabled');
    $("#idStatusReq").removeClass("hide");
    $("#idStatusReq").prop("readonly", true);
    $("#idStatusReq").attr('disabled', 'disabled');
    //$("#idtxtRequisicion").val(""+noRequisicionGeneral);
    //alert("Requisicion a cargar " + r);
    dataObj={
        option: 'loadRequisicion',
        req:noReq
    };
    $.ajax({
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        //console.log("Bien");
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

            rs = info[0].rs;
            ue = info[0].ue;
            fechaCreacion = info[0].fechaCreacion;
            fechadelivery = info[0].fechadelivery;
            comments = info[0].comments;
            idr = info[0].idr;
            noReq = info[0].noReq;

             idanexoGlobal= 'anexo' ; 
                         urGlobal=ue;
                         tipoGlobal=19;
                         idrequisicionGlobal=idr;
            /*alert("rs:"+ rs);
            alert("rs:"+ ue);
            alert("fechaCreacion:"+ fechaCreacion);
            alert("fechadelivery:"+ fechadelivery);
            alert("comments:"+ comments);*/

            $("#idtxtRequisicionView").html(noReq);
            $("#idtxtNoRequisicion").val(""+noReq);
            //alert(noReq);
            $('#selectRazonSocial').empty();
            //$("#selectRazonSocial").html("<option value="+info[0].rs+">"+info[0].rs+"</option>");
            $("#selectRazonSocial").html("<option value="+rs+">"+rs+"</option>");
            $("#selectRazonSocial").multiselect('rebuild');
            //$("#selectRazonSocial").val(""+rs);
            $('#selectUnidadNegocio').empty();
            //$("#selectUnidadNegocio").html("<option value="+info[0].ue+">"+info[0].ue+"</option>");
            $("#selectUnidadNegocio").html("<option value="+ue+">"+ue+"</option>");
            $("#selectUnidadNegocio").multiselect('rebuild');
            //$("#selectUnidadNegocio").val(""+ue);
            //$("#selectUnidadNegocio").html(info[0].ue);
            $("#txtAreaObs").val(""+comments);
            $("#idFechaElaboracion").val(""+fechaCreacion);
            $("#idFechaEntrega").val(""+fechadelivery);

            var periodo = $("#idFechaElaboracion").data("periodo");
            
            var tagref = $("#selectUnidadNegocio").val();
            //alert(tagref);
            fnCargarPartidaProducto(periodoR,tagref); 
            fnCargarPartidaServicio(periodoR,tagref);
            fnMostrarElementosRequisicion(idr);
            //ocultaCargandoGeneral();
        }else{
            ocultaCargandoGeneral();
        }
        ocultaCargandoGeneral();
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
    ocultaCargandoGeneral();
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
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        muestraCargandoGeneral();
        if (data.result) {
            
            //muestraMensaje("mostarar requisición: "+ data.contenido, 1, 'divMensajeOperacion', 5000);
            dataJson = data.contenido.datos;
            //alert(dataMostrar);
            //console.log( "dataMostrar in: " + JSON.stringify(dataJson) );
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
                //console.log( "dataMostrar for: " + JSON.stringify(dataJson) );
                if(tipo == 'B'){
                    fnArticulo(idRequisicion,orden,fEntrega,idPartida,idItem,descItem,unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga,renglon);
                }else{

                    fnServicio(idRequisicion,orden,fEntrega,idPartida,descPartida,idItem,descItem,precio,clavepresupuestal,descLarga,renglon);
                }
            }
            ocultaCargandoGeneral();
        }else {
            muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
            //ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
   // ocultaCargandoGeneral();
}

/*      Sección Nueva Requisición Fin    */

/*      Sección para agregar nuevos elementos a la Requisición Inicio    */

function fnAgregarElemento(mbflag,noReq,fEntrega,orden){
    
    //alert("Tipo de elemento a insertar: " + mbflag);
    //alert("Ord del elemento: " + orden);
    var ord = parseInt(orden);
    if(ord > 0){
        cont = ord + 1;
    }else{
        cont = cont + 1;
    }
    
    //alert("Orden del elemento: " + cont);
    
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
            //muestraMensaje('Se agrego el elemento a la Requisición: '+ noReq, 1, 'divMensajeOperacion', 5000);
            if(mbflag == 'B'){
                //alert("esto es un producto " + mbflag);
                //fnArticulo(noReq,orden,fEntrega,idPartida,idItem,descItem,unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga);
                fnArticulo(noReq,cont,fEntrega);
            }else{
                //alert("esto es un Servicio " + mbflag);
                fnServicio(noReq,cont,fEntrega);
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

// Funcion que trae los articulos por la partida especifica
function fnCargaPartidaArticulos(orden, orderno) {
    muestraCargandoGeneral();

    var cpeprod = $("#selectCvePartidaEspecifica" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
    //fnModificarArticulo(req,orden);
    //alert("tagref:" + tagref);
    dataObj = { 
        option: 'mostrarPartidaCveArticulo',
        dato: cpeprod,
        datotagref: tagref,
        orderno: orderno
    };

    $.ajax({
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
            var contenidoDescPartida = "";
            var contenidoUnidadArt = "";
            var contenidoCvePresupuestal = "";

            for (var info in dataJson) {
                contenidoIdArt += "<option value='" + dataJson[info].idProducto + "'>" + dataJson[info].idProducto + "</option>";
                contenidoDescArt += "<option value='" + dataJson[info].descripcionProducto + "'>" + dataJson[info].descripcionProducto + "</option>";
                contenidoUnidadArt = dataJson[info].unidad;
                //contenidoCvePresupuestal = "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                contenidoCvePresupuestal = dataJson[info].cvePresupuestal;
            }

            $("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append('<option value="0">Cve ...</option>' + contenidoIdArt);
            fnFormatoSelectGeneral("#selectCveArticulo" + orden);
            $("#selectCveArticulo" + orden).multiselect('rebuild');

            $("#selectArticulo" + orden).empty();
            $("#selectArticulo" + orden).append('<option value="0">Articulo ...</option>' + contenidoDescArt);
            fnFormatoSelectGeneral("#selectArticulo" + orden);
            $("#selectArticulo" + orden).multiselect('rebuild');

            $("#selectPartidaEspecificaCvePresupuestal" + orden).val(""+contenidoCvePresupuestal);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).prop("readonly", true);
            $("#selectPartidaEspecificaCvePresupuestal" + orden).attr('disabled', 'disabled');

            $("#idCvePresupuestal" + orden).removeClass("hide");
            if(typeof $("#p" +cpeprod ).val() === 'undefined'){
                $("#idCvePartida" + orden).append('<input type= "hidden" id="p'+cpeprod+'" value="0" />');
            }

            ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}

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

            $("#contDispArt" + orden).val(""+ contDispArt);
            
            $("#selectCveArticulo" + orden).empty();
            $("#selectCveArticulo" + orden).append(contIdArt);
            $("#selectCveArticulo" + orden).multiselect('rebuild');
            $("#addedUMArticulo" + orden).val("");
            $("#addedUMArticulo" + orden).val(""+ contUnidadArt);
            $("#addedUMArticulo" + orden).prop("readonly", true);
            $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
            
            $("#addedPEArticulo" + orden).val("");
            $("#addedPEArticulo" + orden).val(""+ contPrecioEArt);
            
            $("#validaPresupuesto" + orden).prop("readonly", true);
            $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
            fnModificarArticulo(noReq,orden);
            fnObtenerPresupuesto(orden);
            //ocultaCargandoGeneral();
        }else{
            //ocultaCargandoGeneral();
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
        ordenPreError = orden;
    }
    if( s >= 0){
        //alert("> 0 :"+ s);
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = iCntND + 1;
        ordenDispError = orden;
     }else{
        //alert("< 0 :"+ s);
        $("#addDispArticulo" + orden).val("");
        $("#addDispArticulo" + orden).html("No Disponible");
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
        iCntND = 0;
     }
    /*$("#addDispArticulo" + orden).val("");
    $("#addDispArticulo" + orden).html(contDispArt);
    $("#addDispArticulo" + orden).prop("readonly", true);
    $("#addDispArticulo" + orden).attr('disabled', 'disabled');*/
    fnModificarArticulo(noReq,orden);
}

function fnArticulo(noReq,orden,fEntrega,idPartida,idItem,descItem,unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga,renglon){
    var noReq = $("#idtxtRequisicion").val();
    var status = $("#idStatusReq").val();

    $(containerArticulo).append('<div class="row p0 m0">'+
            '<ol id=idElementArticulo' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                '<li id="idEliminarArticulo' + orden + '" class="w5p pt5"><span class="btRemoveArticulo btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveArticulo' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumArticulo' + orden + '" class="w5p pt5"><input type="hidden" id="nProd' + orden + '" value="'+orden+'"/><label  class="w100p text-center" type="text" id="numArticulo' + orden + '"></label></li>' + 
                '<li id="idCvePartida'+orden+'" class="w10p p0"><select id="selectCvePartidaEspecifica' + orden + '" name="selectCvePartidaEspecifica' + orden + '" class="w100p form-control selectCvePartidaEspecifica" onchange="fnCargaPartidaArticulos('+orden+', '+ noReq +');"><option value="0">Partida...</option></select></li>'+
                '<li id="idCvePartidaCveArticulo' + orden + '" class="w10p p0"><select id="selectCveArticulo' + orden + '" name="selectCveArticulo' + orden + '" class="selectCveArticulo form-control" onchange="fnClaveArticulo('+orden+');"><option value="0">Cve ...</option></select></li>'+
                '<li id="idCvePartidaDescArticulo' + orden + '" class="w30p p0"><select id="selectArticulo' + orden + '" name="selectArticulo' + orden + '" class="form-control selectArticulo" onchange="fnDescArticulo('+orden+');"><option value="0">Articulos ...</option></select></li>'+
                '<li id="idUMArticulo' + orden + '" class="w5p pt5"><label  class="w100p addedUMArticulo" type="text" id="addedUMArticulo' + orden + '"></label></li>'+
                '<li id="idCantidadArticulo' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="addedCantidadArticulo num w100p text-center" type="text" id="addedCantidadArticulo' + orden + '" placeholder="Cantidad" onblur="fnCalculaTotal('+orden+');"></li>'+
                '<li id="idPEArticulo' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="num w100p addedPEArticulo text-right" type="text" id="addedPEArticulo' + orden + '" placeholder="Precio" onblur="fnValidaPresupuesto('+orden+');"></li>'+
                '<li id="idCantidadTotalArticulo' + orden + '" class="w5p pt5"><input class="coin w100p addedCantidadTotalArticulo text-right" type="text" id="addedCantidadTotalArticulo' + orden + '" placeholder="Total"/></li>'+
                '<li id="idDisponibilidadArticulo' + orden + '" class="w10p pt5"><label  class="w100p addDispArticulo" type="text" id="addDispArticulo' + orden + '"></label><input type="hidden" id="contDispArt'+orden+'" value="0"></li>'+
                '<li id="idRenglonAnexoArticulo' + orden + '" class="w10p pt5"><input onkeypress="return fnSoloBorrar(event)" class="w70p mr2" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonart'+orden+'" class="w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnMuestraAnexo(' + orden + '),fnMostrarRequisicionModal()"></div></li>'+
                //'<li id="idRenglonAnexoArticulo' + orden + '" class="w10p pt5"><input onkeypress="return fnSoloBorrar(event)" class="RequisicionArticuloCambio w70p mr2" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/><div class="w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnEditorTextoOpen(),fnMuestraAnexo(),fnMostrarRequisicionModal()"></div></li>'+
            '</ol>' +
        '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupestal: </label></span></li>'+
                    '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                        //'<div id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class=" form-control selectPartidaEspecificaCvePresupuestal"></div>'+
                        '<input type="text" id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"/>'+
                    '</li>'+
                    '<li class="w10p"><span><label>Presupuesto: </label></span></li>'+
                    '<li class="w10p" id="idAddPresupuesto' + orden + '">'+
                        '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH hide" placeholder="Presupuesto" type="text" />'+
                        '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                    '</li>'+
                    '<li class="w10p" id="idValidaPresupuesto' + orden + '" >'+
                        '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto Suficiente" type="text" />'+
                    '</li>'+
                '</ol>'+
            '</div>');

    $('#idMainListContentArticulo').append(containerArticulo);

    $("#btRemoveArticulo" + orden).click(function() { 
        $(this).parent().parent().remove();
        $("#idCvePresupuestal" + orden).remove();
        fnEliminarArticulo(noReq,orden);
        orden = orden - 1;
    });
    if(status == 'Autorizado'){
        //alert(status);
        $("#btRemoveArticulo" + orden).addClass('hide');
        $("#btRemoveArticulo" + orden).attr('disabled', 'disabled');
    }

    //if(idPartida != 'undefinded'){
    if(!$.isNumeric(idPartida)){
        $("#numArticulo" + orden).html(""+orden);
        $("#numArticulo" + orden).prop("readonly", true);
        $("#numArticulo" + orden).attr('disabled', 'disabled');
        $("#selectCvePartidaEspecifica" + orden).empty();
        $("#selectCvePartidaEspecifica" + orden).append('<option value="0">Partida...</option>' + contenidosCvePartidaEspecifica);
        //$("#selectCvePartidaEspecifica" + orden).multiselect('rebuild');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');

        //fnFormatoSelectGeneral("#selectCvePartidaEspecifica"+orden);

        fnModificarArticulo(noReq,orden);

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
        $("#addedCantidadArticulo" + orden).attr('disabled', 'disabled');
        $("#addedPEArticulo" + orden).val(""+precio);
        $("#addedPEArticulo" + orden).attr('disabled', 'disabled'); 
        $("#addedCantidadTotalArticulo" + orden).val(""+total);
        $("#addedCantidadTotalArticulo" + orden).prop("readonly", true);
        $("#addedCantidadTotalArticulo" + orden).attr('disabled', 'disabled'); 
        //$("#addDispArticulo" + orden).html(existencia);
        //$("#addDispArticulo" + orden).attr('disabled', 'disabled');
        $("#idCvePresupuestal" + orden).removeClass("hide");
        //$("#addedReglonArticulo").html(renglon);
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).val("");
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).html('<option value="'+clavepresupuestal+'">'+clavepresupuestal+'</option>');
        //$("#selectPartidaEspecificaCvePresupuestal" + orden).html(clavepresupuestal);
        $("#selectPartidaEspecificaCvePresupuestal" + orden).val(""+clavepresupuestal);
        $("#selectPartidaEspecificaCvePresupuestal" + orden).prop("readonly", true);
        $("#selectPartidaEspecificaCvePresupuestal" + orden).attr('disabled', 'disabled');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        fnObtenerPresupuesto(orden);

        var a = $("#addedCantidadArticulo" + orden).val();
        var b = $("#addedPEArticulo" + orden).val();
        var e = existencia;
        //alert(e);
        var t = a * b;
        var p = $("#addPresupuestoH"+orden).val();
        var d = p - t;
        var s = e - a;
        var pe = $("#selectCvePartidaEspecifica" + orden).val();
        $("#p"+pe).val(d);
        if(d > 0){
            $("#idCvePresupuestal" + orden).removeClass("hide");
            $("#addPresupuestoH"+orden).val(""+p);
            $("#validaPresupuesto" + orden).val("Ppto Suficiente");
            $("#validaPresupuesto" + orden).css('border','solid 1px #1B693F');
            $("#validaPresupuesto" + orden).css('color','#1B693F');
            $("#validaPresupuesto" + orden).prop("readonly", true);
            $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        }else{
            $("#idCvePresupuestal" + orden).removeClass("hide");
            $("#addPresupuestoH"+orden).val(""+p);
            $("#validaPresupuesto" + orden).val("Ppto Insuficiente");
            $("#validaPresupuesto" + orden).css('border','solid 1px #ff0000');
            $("#validaPresupuesto" + orden).css('color','#ff0000');
            $("#validaPresupuesto" + orden).prop("readonly", true);
            $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        }
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
        $("#addDispArticulo" + orden).prop("readonly", true);
        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
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
    
    /*alert("clave Presupuestal" + cp );
    alert("Requisición" + r );
    alert("Clave Servicio" + ic);
    alert("Fecha Entrega" + fe );
    alert("Servicio" + id);
    alert("Precio Estimdo" + up );
    alert("Cantidad" + qy);
    alert("Orden" + ol);*/

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
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        //console.log("Bien");
        if (data.result) {
            //muestraMensaje('Se modifico la Requisición con el número: ' + req, 1, 'divMensajeOperacion', 5000);
            
        } else {
            muestraMensaje('No se Modifico la Requisición', 3, 'divMensajeOperacion', 5000);
            
            // console.log("ERROR Modelo");
            // console.log( JSON.stringify(data) ); 
        }
    }).fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}
/**Elimina el elemento creado en la sección articulos y de la base de datos*/
function fnEliminarArticulo(r,orden){
    
    //alert("Se elimina el elemento" + o);
    dataObj = {
        option: 'eliminarElementosRequisicion',
        noReq: r,
        orden: orden
    };
    $.ajax({
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            //muestraMensaje('Se elimino el elemento Articulo a la Requisición: '+ r, 1, 'divMensajeOperacion', 5000);
            //fnReindexar(); 
            orden = orden - 1;
            
        }else{
            muestraMensaje('No se elimino el elemento a la Requisición: '+ r, 3, 'divMensajeOperacion', 5000);
            
        }

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}

function fnServicio(noReq,orden,fEntrega,idPartida,descPartida,idItem,descItem,precio,clavepresupuestal,descLarga,renglon){
    $(containerServicio).append('<div class="row p0 m0">' +
        '<ol id=idElementServicio' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' + 
                '<li id="idEliminarServicio' + orden + '" class="w5p pt5"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove bt" id="btRemoveServicio' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumServicio' + orden + '" class="w5p pt5"><input type="text" id="nServ' + orden + '" class="hide" value="'+orden+'"/><label  class="w50p text-center" type="text" id="numServicio' + orden + '">' + orden + '</label></li>' + 
                '<li id="idAddedPartidaServicio' + orden + '" class="w10p"><select id="selectCvePartidaEspecificaServ' + orden + '" name="selectCvePartidaEspecificaServ' + orden + '" class="form-control selectCvePartidaEspecificaServ"><option value="0">Cve ...</option></select></li>' + 
                '<li id="idAddedDescPartida' + orden + '" class="w20p"><select id="selectDescPartidaEspecificaServ' + orden + '" name="selectDescPartidaEspecificaServ' + orden + '" class="form-control selectDescPartidaEspecificaServ"><option value="0">Descripción ...</option></select></li>' + 
                //'<li id="idAddedDescServicio' + orden + '" class="w35p"><input type="text" id="selectCveServicio' + orden + '" class="hide"/><select id="selectServicio' + orden + '" name="selectServicio' + orden + '" class="form-control selectServicio"><option value="0">Servicios</option></select></li>' + 
                '<li id="idAddedDescServicio' + orden + '" class="w35p pt5"><input type="text" id="cveServicio' + orden + '" class="hide"/><input class="w95p" id="descServicio' + orden + '" type="text" /></li>' + 
                '<li id="idAddLongDescServicio'+ orden +'" class="w5p pt5"><span class="btn btn-info btn-xs glyphicon glyphicon-comment" id="btLongDescServicio' + orden + '" onclick="fnEditorTextoOpen()"></span></li>'+
                '<li id="idCantidadServicio' + orden + '" class="w5p pt5"><label  class="w100p addedCantidadServicio text-center" type="text" id="addedCantidadServicio' + orden + '">1</label></li>'+
                '<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="w100p addedPEServicio" type="text" id="addedPEServicio' + orden + '" placeholder="Precio"/></li>' + 
                //'<li id="idAddFileServico' + orden + '" class="w10p"><label for="addFileServicio' + orden + '" class="w60p btn btn-info glyphicon glyphicon-paperclip"></label><input type="file" id="addFileServicio' + orden + '" value="" multiple class="hide"></li>' + 
                '<li id="idAddedReglonServicio' + orden + '" class="w10p pt5"><input class="w70p mr2" type="text" id="addedReglonServicio' + orden + '" value="" placeholder="Renglón"/><div class="w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnEditorTextoOpen(),fnMuestraAnexo(' + orden + ')"></li>' + 
                '</ol>' +
            '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">' +
                '<div class="col-lg-2 col-md-2 col-sm-2 pt10">' +
                    '<span><label>Clave Presupestal: </label></span>' +
                '</div>' +
                '<div id="idAddCvePresupuestal' + orden + '" class="col-lg-6 col-md-6 col-sm-6">'+
                    '<select id="selectPartidaEspecificaCvePresupuestalServ' + orden + '" name="selectPartidaEspecificaCvePresupuestalServ' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestalServ"><option value="0">Clave Presupestal</option></select>'+
                '</div>' +
                '<div class="col-lg-1 col-md-1 col-sm-1 pt10">' +
                    '<span><label>Presupuesto: </label></span>' +
                '</div>' +
                '<div id="idAddPresupuesto' + orden + '" class="col-lg-1 col-md-1 col-sm-1 pt10">'+
                    '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH hide" placeholder="Presupuesto" type="text" />'+
                    '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                '</div>' +
                '<div id="idValidaPresupuesto' + orden + '" class="col-lg-2 col-md-2 col-sm-2">'+
                    '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto Suficiente" type="text" />'+
                '</div>' +
            '</div>'+
            '<div id="idPopupEditor'+orden+'" class="idPopupEditor" style="display: none;">'+
                '<div class="content-popup">'+
                    '<div class="popupEditorClose" onclick="fnEditorTextoClose()"><a href="#" id="idPopupEditorClose" onclick="">x</a></div>'+
                    '<div class="hA">'+
                        '<h2>Descripción del Servicio</h2>'+
                        '<textarea class="w100p" name="" id="idLongText'+orden+'" cols="" rows="20"></textarea>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="popup-overlay" style="display: none;"></div>');
            $('#idMainListContentServicio').append(containerServicio);

            $("#btRemoveServicio" + orden).click(function() { 
                $(this).parent().parent().remove();
                $("#idCvePresupuestal" + orden).remove();
                fnEliminarServicio(noReq,orden);
                orden = orden - 1;
            });

            if(!$.isNumeric(idPartida)){
                $("#selectCvePartidaEspecificaServ" + orden).empty();
                $("#selectCvePartidaEspecificaServ" + orden).append('<option value="0">Cve ...</option>' + contenidosCvePartidaEspecificaServ);
                $("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');
                $("#selectCvePartidaEspecificaServ" + orden).change(function(){
                    //muestraCargandoGeneral();
                    //var idserv = $("#selectCveServicio" + orden).val();
                    var cpeserv = $("#selectCvePartidaEspecificaServ" + orden).val();
                    var tagref = $("#selectUnidadNegocio").val();
                    //alert("tagref:" + tagref);
                    dataObj = { 
                        option: 'mostrarPartidaCveServicio',
                        datoserv: cpeserv,
                        datotagref: tagref
                    };
                    $.ajax({
                        method: "POST",
                        dataType: "json",
                        url: url,
                        data: dataObj
                    }).done(function(data) {
                        if (data.result) {
                            dataJson = data.contenido.datos;
                            //console.log( "dataJson: " + JSON.stringify(dataJson) );
                            var contenidoPartidaEspServ= "";
                            var contenidoCveServ = "";
                            var contenidoDescServ = "";
                            var contenidoPartidaEspDescServ = "";
                            var contenidoCantidadServ = "";
                            var contenidoPrecioEServ = "";
                            var contenidoDispServ = "";
                            var contenidoCvePresupuestalServ = "";
                            for (var info in dataJson) {
                                contenidoPartidaEspServ += "<option value='" + dataJson[info].idPartidaEspecifica + "'>" + dataJson[info].idPartidaEspecifica + "</option>";
                                contenidoPartidaEspDescServ += "<option value='" + dataJson[info].descPartidaEspecifica + "'>" + dataJson[info].descPartidaEspecifica + "</option>";
                                contenidoCveServ = dataJson[info].idServicio;

                                contenidoDescServ += "<option value='" + dataJson[info].descripcionServicio + "'>" + dataJson[info].descripcionServicio + "</option>";
                                contenidoCantidadServ = 1;
                                contenidoPrecioEServ = dataJson[info].precioEstimado;
                                contenidoCvePresupuestalServ = "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                            }

                            $("#selectDescPartidaEspecificaServ" + orden).empty();
                            $("#selectDescPartidaEspecificaServ" + orden).append(contenidoPartidaEspDescServ);
                            $("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');
                            
                            $("#cveServicio"+ orden).empty();
                            $("#cveServicio"+ orden).val(""+contenidoCveServ);
                            
                            $("#descServicio"+ orden).blur(function(){
                                //alert("Se guarda el servicio");
                                fnModificarServicio(noReq,orden);
                            });
                            $("#addedCantidadServicio" + orden).html(contenidoCantidadServ);
                            $("#addedPEServicio" + orden).val(""+contenidoPrecioEServ);
                            $("#addedPEServicio" + orden).blur(function(){
                                            
                                var b = $("#addedPEServicio" + orden).val();
                                            
                                var p = $("#addPresupuestoH"+orden).val();
                                var d = p - b;
                                //alert("total: "+ t);
                                if(d > 0){
                                    $("#validaPresupuesto" + orden).val("Ppto Suficiente");
                                    $("#validaPresupuesto" + orden).css('border','solid 1px #1B693F');
                                    $("#validaPresupuesto" + orden).css('color','#1B693F');
                                    $("#validaPresupuesto" + orden).prop("readonly", true);
                                    $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                                    iCntPI = 0;
                                }else{
                                    $("#validaPresupuesto" + orden).val("Ppto Insuficiente");
                                    $("#validaPresupuesto" + orden).css('border','solid 1px #ff0000');
                                    $("#validaPresupuesto" + orden).css('color','#ff0000');
                                    $("#validaPresupuesto" + orden).prop("readonly", true);
                                    $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                                    //muestraMensaje('Es necesario tener presuspuesto suficiente ', 3, 'divMensajeOperacion', 5000);
                                    iCntPI = iCntPI + 1;
                                    //alert(iCntPI );
                                }
                                
                                fnModificarServicio(noReq,orden);
                            });
                            $("#idLongText" + orden).blur(function(){
                                fnModificarServicio(noReq,orden);
                            });
                            $("#idCvePresupuestal" + orden).removeClass("hide");
                            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
                            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoCvePresupuestalServ);
                            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect('rebuild');

                            fnModificarServicio(noReq,orden);
                            fnObtenerPresupuesto(orden); 
                            //ocultaCargandoGeneral();
                        }else{
                            //ocultaCargandoGeneral();
                        }

                    }).fail(function(result) {
                        console.log("ERROR");
                        console.log(result);
                        //alert(result);
                    });     
                    //fnModificarServicio(noReq,orden);
                });

                $("#selectDescPartidaEspecificaServ" + orden).empty();
                $("#selectDescPartidaEspecificaServ" + orden).append('<option value="0">Partida ...</option>' + contenidoDescPartidaEspecificaServ);
                $("#selectDescPartidaEspecificaServ" + orden).multiselect('rebuild');
                $("#selectDescPartidaEspecificaServ" + orden).change(function(){
                    //muestraCargandoGeneral();
                    //var idserv = $("#selectCveServicio" + orden).val();
                    var cpeserv = $("#selectDescPartidaEspecificaServ" + orden).val();
                    var tagref = $("#selectUnidadNegocio").val();
                    //alert("tagref:" + tagref);
                    dataObj = { 
                        option: 'mostrarPartidaCveServicio',
                        datoserv: cpeserv,
                        datotagref: tagref
                    };
                    $.ajax({
                        method: "POST",
                        dataType: "json",
                        url: url,
                        data: dataObj
                    }).done(function(data) {
                        if (data.result) {
                            dataJson = data.contenido.datos;
                            //console.log( "dataJson: " + JSON.stringify(dataJson) );
                            var contenidoPartidaEspServ= "";
                            var contenidoCveServ = "";
                            var contenidoDescServ = "";
                            var contenidoPartidaEspDescServ = "";
                            var contenidoCantidadServ = "";
                            var contenidoPrecioEServ = "";
                            var contenidoDispServ = "";
                            var contenidoCvePresupuestalServ = "";
                            for (var info in dataJson) {
                                contenidoPartidaEspServ += "<option value='" + dataJson[info].idPartidaEspecifica + "'>" + dataJson[info].idPartidaEspecifica + "</option>";
                                contenidoPartidaEspDescServ += "<option value='" + dataJson[info].descPartidaEspecifica + "'>" + dataJson[info].descPartidaEspecifica + "</option>";
                                contenidoCveServ = dataJson[info].idServicio;
                                contenidoDescServ += "<option value='" + dataJson[info].descripcionServicio + "'>" + dataJson[info].descripcionServicio + "</option>";
                                contenidoCantidadServ = 1;
                                contenidoPrecioEServ = dataJson[info].precioEstimado;
                                contenidoCvePresupuestalServ = "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                            }
                            $("#selectCvePartidaEspecificaServ" + orden).empty();
                            $("#selectCvePartidaEspecificaServ" + orden).append(contenidoPartidaEspServ);
                            $("#selectCvePartidaEspecificaServ" + orden).multiselect('rebuild');
                            
                            $("#idCvePresupuestal" + orden).removeClass("hide");
                            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).empty();
                            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).append(contenidoCvePresupuestalServ);
                            $("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect('rebuild');
                            //fnModificarServicio(noReq,orden);
                            fnObtenerPresupuesto(orden); 
                            //ocultaCargandoGeneral();
                        }else{
                            //ocultaCargandoGeneral();
                        }

                    }).fail(function(result) {
                        console.log("ERROR");
                        console.log(result);
                        //alert(result);
                    });     
                    //fnModificarServicio(noReq,orden);
                })

            }else{

                $("#numServicio" + orden).html(orden);
                $("#numServicio" + orden).prop("readonly", true);
                $("#numServicio" + orden).attr('disabled', 'disabled');
                $("#selectCvePartidaEspecificaServ" + orden).empty();
                //$("#selectCvePartidaEspecifica" + orden).append(idPartida);
                $("#selectCvePartidaEspecificaServ" + orden).html('<option value="'+idPartida+'">'+idPartida+'</option>');
                //$("#selectCvePartidaEspecifica" + orden).multiselect('rebuild');
                $("#selectDescPartidaEspecificaServ" + orden).empty();
                $("#selectDescPartidaEspecificaServ" + orden).html('<option value="'+descPartida+'">'+descPartida+'</option>');
                //$("#selectCveServicio" + orden).val(""+idItem);
                //$("#selectServicio" + orden).val(null);
                //$("#selectServicio" + orden).html('<option value="'+descItem+'">'+descItem+'</option>');
                //$("#selectArticulo" + orden).multiselect('rebuild');
                $("#cveServicio"+ orden).val(""+idItem);
                $("#descServicio"+ orden).val(""+descItem);
                $("#addedCantidadServicio" + orden).html('1');
                $("#addedPEServicio" + orden).val(""+precio);
                /*$("#addedPEServicio" + orden).blur(function(){
                                            
                    var b = $("#addedPEServicio" + orden).val();
                                
                    var p = $("#addPresupuestoH"+orden).val();
                    var d = p - b;
                    //alert("total: "+ t);
                    if(d > 0){
                        $("#validaPresupuesto" + orden).val("Ppto Suficiente");
                        $("#validaPresupuesto" + orden).css('border','solid 1px #1B693F');
                        $("#validaPresupuesto" + orden).css('color','#1B693F');
                        $("#validaPresupuesto" + orden).prop("readonly", true);
                        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                    }else{
                        $("#validaPresupuesto" + orden).val("Ppto Insuficiente");
                        $("#validaPresupuesto" + orden).css('border','solid 1px #ff0000');
                        $("#validaPresupuesto" + orden).css('color','#ff0000');
                        $("#validaPresupuesto" + orden).prop("readonly", true);
                        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                    }
                    
                    fnModificarServicio(noReq,orden);
                });*/
                $("#idLongText" + orden).html(descLarga);
                $("#idCvePresupuestal" + orden).removeClass("hide");
                $("#selectPartidaEspecificaCvePresupuestalServ" + orden).val(null);
                $("#selectPartidaEspecificaCvePresupuestalServ" + orden).html('<option value="'+clavepresupuestal+'">'+clavepresupuestal+'</option>');
                $("#selectPartidaEspecificaCvePresupuestalServ" + orden).multiselect('rebuild');
                $("#addedReglonServicio" + orden).html(renglon);
                $("#validaPresupuesto" + orden).prop("readonly", true);
                        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                fnObtenerPresupuesto(orden); 
                var b = $("#addedPEServicio" + orden).val();
                                
                    var p = $("#addPresupuestoH"+orden).val();
                    var d = p - b;
                    //alert("total: "+ t);
                    if(d > 0){
                        $("#validaPresupuesto" + orden).val("Ppto Suficiente");
                        $("#validaPresupuesto" + orden).css('border','solid 1px #1B693F');
                        $("#validaPresupuesto" + orden).css('color','#1B693F');
                        $("#validaPresupuesto" + orden).prop("readonly", true);
                        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                    }else{
                        $("#validaPresupuesto" + orden).val("Ppto Insuficiente");
                        $("#validaPresupuesto" + orden).css('border','solid 1px #ff0000');
                        $("#validaPresupuesto" + orden).css('color','#ff0000');
                        $("#validaPresupuesto" + orden).prop("readonly", true);
                        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                    }
            }
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
    //alert(lt);
    //alert(ic);
    /*alert("clave Presupuestal" + cp );
    alert("Requisición" + r );
    alert("Clave Servicio" + ic);
    alert("Fecha Entrega" + fe );
    alert("Servicio" + id);
    alert("Precio Estimdo" + up );
    alert("Cantidad" + qy);
    alert("Orden" + ol);*/
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
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        //console.log("Bien");
        if (data.result) {
            //muestraMensaje('Se modifico la Requisición con el número: ' + r, 1, 'divMensajeOperacion', 5000);
        } else {
            muestraMensaje('No se Modifico la Requisición ', 3, 'divMensajeOperacion', 5000);
            // console.log("ERROR Modelo");
            // console.log( JSON.stringify(data) ); 
        }
    }).fail(function(result) {
        // console.log("ERROR");
        // console.log( result );
    });
}
/**Elimina el elemento creado en la sección Servicios y de la base de datos*/
function fnEliminarServicio(r,o){
    //alert("Se elimina el elemento" + o);
   dataObj = {
        option: 'eliminarElementosRequisicion',
        noReq: r,
        orden: o
    };
    $.ajax({
        method: "POST",
        dataType: "json",
        url: url,
        data: dataObj
    }).done(function(data) {
        if (data.result) {
            //muestraMensaje('Se elimino el elemento Servicio a la Requisición: '+ r, 1, 'divMensajeOperacion', 5000); 
            cont = o - 1;
            //fnReindexar();
        }else{
            muestraMensaje('No se elimino el elemento a la Requisición ', 3, 'divMensajeOperacion', 5000);
        }

    }).fail(function(result) {
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}

function fnGuardarRequisicion(){
    var noReq = $("#idtxtNoRequisicion").val();
    var idReq = $("#idtxtRequisicion").val();
    var comments = $("#txtAreaObs").val();
    var fechaFrom = $("#idFechaElaboracion").val();
    var fechaTo = $("#idFechaEntrega").val();
    //alert("Guardar Requisicion: " + req +' - '+ status +' - '+ comments +' - '+ fechaFrom +' - '+ fechaTo );
    //alert(noReq);
    if(idReq == ''){
        muestraMensaje('No existe una Requisición para guardar', 3, 'divMensajeOperacion', 5000);
    }else{
        //alert(noReq);
        if(noReq > 0){
            //alert("existe, guardar elementos" + noReq);
            fnGuardarRequisicionExistente(idReq,comments,fechaFrom,fechaTo);
        }else{
            //alert("No existe, guardar Requisición" + noReq);
            fnGuardarRequisicionNueva(idReq,comments,fechaFrom,fechaTo);
        }
    }
}

function fnGuardarRequisicionNueva(idReq, comments, fechaFrom, fechaTo){
    if (fnValidarRequisicion(idReq)) {
        dataObj = {
            option: 'guardarRequisicionNueva',
            noReq: idReq,
            status: 'Capturado',
            comments: comments,
            fechaFrom: fechaFrom,
            fechaTo: fechaTo
        };
        $.ajax({
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                noReq= data.contenido;
                //alert(noReq);
                muestraMensaje('Se guardo la Requisición ', 1, 'divMensajeOperacion', 5000); 
                fnObtenerStatusReq(idReq);

                $("#idtxtRequisicionView").html(noReq);
                $("#idtxtNoRequisicion").val(""+noReq);
                $("#idtxtRequisicionView").removeClass("hide");
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                $("#idStatusReq").removeClass("hide");
                $("#idStatusReq").prop("readonly", true);
                $("#idStatusReq").attr('disabled', 'disabled');
                fnReindexar();
                //location.reload();
                //location.replace("https://23.111.130.190/ap_grp/Captura_Requisicion.php?ModifyOrderNumber="+idReq);
                //location.replace("./Captura_Requisicion.php?ModifyOrderNumber="+idReq);
                location.replace("./PO_SelectOSPurchOrder.php");
            }else{
                muestraMensaje('No se guardo la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            //alert(result);
        });
    }
    
}

function fnGuardarRequisicionExistente(idReq,comments,fechaFrom,fechaTo){
    var status = $("#idStatusReq").val();
    
    if (fnValidarRequisicion(idReq)) {
        dataObj = {
                option: 'guardarRequisicion',
                noReq: idReq,
                status: status,
                comments: comments,
                fechaFrom: fechaFrom,
                fechaTo: fechaTo
            };

        $.ajax({
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                noReq= data.contenido;
                //alert(noReq);
                muestraMensaje('Se guardo la Requisición ', 1, 'divMensajeOperacion', 5000); 
                //$("#idtxtRequisicionView").html(noReq);
                $("#idtxtRequisicionView").removeClass("hide");
                $("#idtxtRequisicionView").prop("readonly", true);
                $("#idtxtRequisicionView").attr('disabled', 'disabled');
                fnReindexar();
                //location.reload();
                //location.replace("https://23.111.130.190/ap_grp/Captura_Requisicion.php?ModifyOrderNumber="+idReq);
                //location.replace("./Captura_Requisicion.php?ModifyOrderNumber="+idReq);
                location.replace("./PO_SelectOSPurchOrder.php");
            }else{
                muestraMensaje('No se guardo la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            //alert(result);
        });
    }
}

function fnCancelarRequisicion(){
  //alert("Cancelar ");
  var req = $("#idtxtRequisicion").val();
  dataObj = {
            option: 'cancelarRequisicion',
            noReq: req,
            status: 'Cancelado'
        };
        $.ajax({
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                muestraMensaje('Se cancelo todo movimiento no guardado en la Requisición ', 1, 'divMensajeOperacion', 5000); 
                //location.reload();
                //location.replace("https://23.111.130.190//ap_grp/PO_SelectOSPurchOrder.php?");
                location.replace("./PO_SelectOSPurchOrder.php?");
            }else{
                muestraMensaje('No se cancelo ningún movimiento en la Requisición ', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            //alert(result);
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
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                muestraMensaje('Se Rechaza la Requisición ', 1, 'divMensajeOperacion', 5000); 
                //location.replace("https://23.111.130.190/ap_grp/Captura_Requisicion.php?ModifyOrderNumber="+idReq);
                //location.replace("https://23.111.130.190//ap_grp/PO_SelectOSPurchOrder.php?");
                location.replace("./PO_SelectOSPurchOrder.php?");
            }else{
                muestraMensaje('No se rechazo la Requisición', 3, 'divMensajeOperacion', 5000);
            }

        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            //alert(result);
        });
}

function fnRegresarPanelRequisicion(){
    location.replace("./PO_SelectOSPurchOrder.php");
}

function fnReindexar(){
    var idReq = $("#idtxtRequisicion").val();
    //alert(idReq);
    dataObj= {
        option: 'reIndexar',
        idReq: idReq
    };
    $.ajax({
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
            //dataIndex = data.contenido.datos;
            //console.log( "dataIndex: " + JSON.stringify(dataIndex) );
            //alert(JSON.stringify(dataIndex));
            //var indexReq = "";
            //for (var info in dataIndex) {
            //indexReq = dataIndex[info].orderlineno_;
            //alert(indexReq);
            //alert(dataIndex.length);
            //}
            //location.reload();
        }else{
            alert("Error");
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });

}

function fnLimpiar(idReq){
    //alert(idReq);
    dataObj= {
        option: 'limpiar',
        idReq: idReq
    };
    $.ajax({
          method: "POST",
          dataType: "json",
          url: url,
          data: dataObj
      })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
            //alert("Exito");
             //location.reload();
        }else{
            alert("Error");
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

function fnGenerarLayoutRequisicion(){
    var noReq = $("#idtxtNoRequisicion").val();

    if(noReq > 0){
        //alert("Se genera un layout de la requisicion: " + noReq);
        var jsonData = new Array();
        var obj = new Object();
        obj.transno = noReq;
        jsonData.push(obj);

        fnGenerarArchivoLayout(funcionGenerarLayout, typeGenerarLayout, jsonData, tipoLayout);

    }else {
        alert("Error al generar el layout de la requisición ");
    }
    
}

function fnObtenerStatusReq(idReq){
    //alert(idReq);
    dataObj = {
            option: 'buscarStatusReq',
            idReq: idReq
        };
        $.ajax({
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
                //return statusReq;
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
    //alert("status: " + status);
    dataObj = {
            option: 'buscarPerfilUsr'
        };
        $.ajax({
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        }).done(function(data) {
            if (data.result) {
                dataPerfil = data.contenido.datos;
                var perfilUsr = "";
                var perfilid = "";
                for (var info in dataPerfil) {
                    perfilUsr = dataPerfil[info].userid;
                    perfilid = dataPerfil[info].profileid;
                }
                //alert("Perfil: " + perfilid);
                if((perfilid == 9 || perfilid == 10 || perfilid == 11 || perfilid == 7) && (status == 'Creada' || status == 'Capturado')){
                    //fnObtenerBotones('divBotones');  
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
            //alert(result);
        });
}

/*      Sección para agregar nuevos elementos a la Requisición Fin    */

/*      Sección Inicio    */
/*      Sección Fin    */

/*      Sección Funciones Generales Inicio    */
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
        //alert(contAnexo);
        $("#addedReglonArticulo"+ordenReq).val(""+ordenPartida);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).attr('disabled',true);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).prop("readonly", true);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).addClass( "hide" );
        //$("#addedReglonArticulo"+ordenReq).trigger('change');
        fnModificarArticulo(noReq,ordenReq);
    }else{
        //alert(ordenAnexo);
        contAnexo = $("#addedReglonArticulo"+ordenReq).val();

        //alert('arreglo: '+ arregloDeSubCadenas[0]);
        //console.log(arregloDeSubCadenas);
        $("#addedReglonArticulo"+ordenReq).val(""+contAnexo+","+ordenPartida);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).attr('disabled',true);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).prop("readonly", true);
        $("#"+ordenAnexo+"_"+ordenPartida+"_"+ordenReq).addClass( "hide" );
        //$("#addedReglonArticulo"+ordenReq).trigger('change');
        fnModificarArticulo(noReq,ordenReq);
    }
}

function fnMuestraAnexo(orden){
    var idReq = noReq;
    var contAnexo = $("#addedReglonArticulo"+orden).val();
    //alert(idReq + ' - ' + orden)
    //alert(contAnexo);
    dataObj = { 
            option: 'muestraInfoAnexo',
            idReq: idReq,
            orden: orden
          };
    $.ajax({
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
            //fnLimpiarTabla('divTablaAnexo', 'divAnexoTabla');
            $("#divAnexoTabla").val("");
            $("#divAnexoTabla").append('<div class="borderTest"><span class="w5p plr5">Nº Elemento</span><span class="w5p plr5">Anexo</span><span class="w5p pr5">#</span><span class="w35p pr5">Bien_Servicio</span><span class="w40p pr5">Descripción</span></div>');
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
                                //alert("diferente");
                                $("#divAnexoTabla").append('<div class="borderTest"><span class="w5p plr5">'+orden+'</span><span class="w5p plr5">'+idAnexo+'</span><span id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w5p btn btn-default btn-xs pr5 hide"></span><span class="w35p pr5">'+nombreBienServ+'</span><span class="w40p pr5">'+descBienServ+'</span></div>');
                            }else{
                                $("#divAnexoTabla").append('<div class="borderTest"><span class="w5p plr5">'+orden+'</span><span class="w5p plr5">'+idAnexo+'</span><span id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w5p btn btn-default btn-xs pr5" onclick="fnAgregaRenglon('+ idAnexo +','+ ordenPd +','+ idPartida +')">'+idPartida+'</span><span class="w35p pr5">'+nombreBienServ+'</span><span class="w40p pr5">'+descBienServ+'</span></div>');
                                
                            }
                        //alert(idPartida);
                        //$("#divAnexoTabla").append('<div class="borderTest"><span class="w5p plr5">'+orden+'</span><span class="w5p plr5">'+idAnexo+'</span><span id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w5p btn btn-default btn-xs pr5 hide"></span><span class="w35p pr5">'+nombreBienServ+'</span><span class="w40p pr5">'+descBienServ+'</span></div>');
                    }
                
            }else{
                for (var info in dataAnexoJason) {
                    idAnexo = dataAnexoJason[info].idanexo;
                    idPartida = dataAnexoJason[info].idpartida;
                    ordenPd = dataAnexoJason[info].ordenpd;
                    nombreBienServ = dataAnexoJason[info].bienServicio;
                    descBienServ = dataAnexoJason[info].descripcion_bien_serv;
                    partida = dataAnexoJason[info].partida_esp;
                    //alert(idPartida);
                    $("#divAnexoTabla").append('<div class="borderTest"><span class="w5p plr5">'+orden+'</span><span class="w5p plr5">'+idAnexo+'</span><span id="'+idAnexo+"_"+idPartida+"_"+orden+'" class="w5p btn btn-default btn-xs pr5" onclick="fnAgregaRenglon('+ idAnexo +','+ ordenPd +','+ idPartida +')">'+idPartida+'</span><span class="w35p pr5">'+nombreBienServ+'</span><span class="w40p pr5">'+descBienServ+'</span></div>');
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
                            //alert(arregloDeSubCadenas[info]);
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
function fnEditorTextoOpen(){
    $('#idPopupEditor').css("display","block");
    $('#idPopupEditor').fadeIn('slow');
    $('.popup-overlay').fadeIn('slow');
    $('.popup-overlay').height($(window).height());
    //$("#idEditorContent").load('editortexto.php');
    return false;

}

/**
 * Cierra el modal 
 */
function fnEditorTextoClose(){
    $('#idPopupEditorClose').click(function() {
        $('#idPopupEditor').fadeOut('slow');
        $('.popup-overlay').fadeOut('slow');
        return false;
    });
}

/**
 * Fija la Fecha de elaboración.
 */
function fnFijarFecha(){
    dataObj = { 
            proceso: 'getFechaServidor',
          };
    $.ajax({
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
          method: "POST",
          dataType:"json",
          url: "modelo/GeneralPaymentsDetailsV2_modelo.php",
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            //Si trae informacion
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
/**
 * Función que da comportamiento al select de las depencias.
 */
function fnCambioRazonSocial() {
    //console.log("fnObtenerUnidadNegocio");
    // Inicio Unidad de Negocio
    var legalid = "";
    var selectRazonSocial = $("#selectRazonSocial").val();

    for ( var i = 0; i < selectRazonSocial.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
        if (i == 0) {
            legalid = selectRazonSocial.selectedOptions[i].value;
        }else{
            legalid = legalid+", "+selectRazonSocial.selectedOptions[i].value;
        }
    }
    
    //Opcion para operacion
    dataObj = { 
          option: 'mostrarUnidadNegocio',
          legalid: legalid
        };
    //Obtener datos de las bahias
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          
          dataJson = data.contenido.datos;
          //console.log( "dataJson: " + JSON.stringify(dataJson) );
          //alert(JSON.stringify(dataJson));
          var contenido = "<option value='0'>Seleccionar...</option>";
          for (var info in dataJson) {
            contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
          }
        $('#selectUnidadNegocio').empty();
        $('#selectUnidadNegocio').append(contenido);
        $('#selectUnidadNegocio').multiselect('rebuild');
      }else{
          // console.log("ERROR Modelo");
          // console.log( JSON.stringify(data) ); 
      }
    })
    .fail(function(result) {
      // console.log("ERROR");
      // console.log( result );
    });
    // Fin Unidad de Negocio
}

/* Muestra la unidad de negocio por usuario de sesión */
function fnCambioUnidadNegocio() {
    //console.log("fnCambioRazonSocial");
    tagref = $("#selectUnidadNegocio").val();
}

/**
 * Muestra los botones asignados segun permiso y función
 * @param  {String} divMostrar Para mostrar el div donde se pintaran los botones
 */
function fnObtenerBotones(divMostrar) {
    //Opcion para operacion
    dataObj = { 
            option: 'obtenerBotones',
            type: ''
          };
    //Obtener datos de las bahias
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            //Si trae informacion
            info=data.contenido.datos;
            //console.log("presupuesto: "+JSON.stringify(info));
            var contenido = '';
            for (var key in info) {
                var funciones = '';
                if (info[key].statusid == 1) {
                    funciones = 'fnGuardarRequisicion()';
                }else if (info[key].statusid == 2) {
                    
                    funciones = 'fnValidarRequisicion()';
                }else if (info[key].statusid == 6) {
                    
                    funciones = 'fnCancelarRequisicion()';
                }
                else if (info[key].statusid == 99) {
                    
                    funciones = 'fnAvanzarRequisicion()';
                }else if (info[key].statusid == 5) {
                    
                    funciones = 'fnRechazarRequisicion()';
                }else if (info[key].statusid == 4) {
                    
                    funciones = 'fnAutorizarRequisicion()';
                }else if (info[key].statusid == 3) {
                    
                    funciones = 'fnPorAutorizarRequisicion()';
                }else if (info[key].statusid == 12) {
                    
                    funciones = 'fnRegresarPanelRequisicion()';
                }
                contenido += '&nbsp;&nbsp;&nbsp; \
                <button type="button" id="'+info[key].namebutton+'" name="'+info[key].namebutton+'" onclick="'+funciones+'" class="btn btn-default botonVerde '+info[key].clases+'">'+info[key].namebutton+'</button>';
            }
            $('#'+divMostrar).append(contenido);
            //fnEjecutarVueGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
}

//$(document).on('change','.RequisicionArticuloCambio',function(){
/*$(document).on('change','#addedReglonArticulo'+cont,function(){
    //AQUI MANDAS GUARDAR LA FUNCION
   //id= $(this).attr('id');
    alert(cont);
    fnModificarArticulo(noReq,cont);
});*/
// Datos botones
//fnObtenerBotones_Funcion('divBotones', $("#idPanelRequisiciones").data("funcion"));

/*      Sección Funciones Generales Fin    */