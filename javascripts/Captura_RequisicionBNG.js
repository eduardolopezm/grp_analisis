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

var url = "modelo/Captura_Requisicion_modeloBNG.php";
var iCnt = 0; // contador de elementos.
var iCntPI = 0; // contador de elementos con presupuesto insuficiente.
var iCntND = 0; // contador de elementos sin disponibilidad en almacen.
var proceso = ""; // Valor del proceso que se llamara al modelo.

//var mbflag = ""; // Bandera que indica si es Producto o Servicio.
var noReq = noRequisicionGeneral;
//var ultimamodificacion =fnPreData(noReq);
var urlCR = urlCReq;

var estatusGenerarLayout = 4;
var funcionGenerarLayout = 2265;
var typeGenerarLayout = 19;
var tipoLayout = 1;
var periodoR = periodoReq;

/* variables globales fin */

/*  Sección de document ready Inicio */

var posicionesUltimas=[];

$(document).ready(function() {
    $("#idtxtRequisicionView").addClass("hide");
    $("#idtxtRequisicionView").prop("readonly", true);
    $("#idtxtRequisicionView").attr('disabled', 'disabled');

    $("#idFechaElaboracion").prop("readonly", true);
    $("#idFechaElaboracion").attr('disabled', 'disabled');
    fnFijarFecha();
    fnFijarFechaSiguiente(1);

    $('#idPopupEditorClose').click(function() {
        $('.idPopupEditor').fadeOut('slow');
        $('.popup-overlay').fadeOut('slow');
        return false;
    });
});

function fnCalculaDispValidaPpto(orden){
    var idReq = $("#idtxtRequisicion").val();
    var cantidadArt = $("#addedCantidadArticulo" + orden).val();
    var precioArt = $("#addedPEArticulo" + orden).val();
    var stock = $("#contDispArt" + orden).val();
    var ppto = $("#addPresupuestoH"+orden).val();
    var partidaEsp = $("#selectCvePartidaEspecifica" + orden).val();
    var totalArt = cantidadArt * precioArt;
    var dispArt = stock - cantidadArt;
    var suficiencia = ppto - totalArt;

    $("#addedCantidadTotalArticulo" + orden).val(""+totalArt);

    if( dispArt >= 0){
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

     if(suficiencia > 0){
        $("#idCvePresupuestal" + orden).removeClass("hide");
        $("#addPresupuestoH"+orden).val(""+ppto);
        $("#validaPresupuesto" + orden).val("Ppto Suficiente");
        $("#validaPresupuesto" + orden).css('border','solid 1px #1B693F');
        $("#validaPresupuesto" + orden).css('color','#1B693F');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        iCntPI = 0;

    }else{
        $("#idCvePresupuestal" + orden).removeClass("hide");
        $("#addPresupuestoH"+orden).val(""+ppto);
        $("#validaPresupuesto" + orden).val("Ppto Insuficiente");
        $("#validaPresupuesto" + orden).css('border','solid 1px #ff0000');
        $("#validaPresupuesto" + orden).css('color','#ff0000');
        $("#validaPresupuesto" + orden).prop("readonly", true);
        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
        iCntPI = iCntPI +1;
    }
}

function fnAgregarElemento(mbflag){
    
    iCnt = iCnt + 1;
    var tagrefR = $("#selectUnidadNegocio").val();

    if(mbflag == 'B'){
        fnCargarPartidaProducto(periodoR,tagrefR); 
        fnArticulo(iCnt);
    }else{
        fnCargarPartidaServicio(periodoR,tagrefR);
        fnServicio(iCnt);
    }
}

function fnArticulo(orden){
    
    $('#idMainListContentArticulo').append(
    '<div id=idElemento' + orden + ' class="elemento m0 p0">'+
        '<div class="row p0 m0">'+
            '<ol id=idElementArticulo' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                '<li id="idEliminarArticulo' + orden + '" class="w5p pt5"><span class="btnRemove btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveArticulo' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumArticulo' + orden + '" class="w5p pt5"><input type="text" id="nProd' + orden + '" class="hide" value="'+orden+'"/><label  class="w100p text-center" type="text" id="numArticulo' + orden + '">' + orden + '</label></li>' + 
                '<li id="idCvePartida'+orden+'" class="w10p p0"><select id="selectCvePartidaEspecifica' + orden + '" name="selectCvePartidaEspecifica' + orden + '" class="w100p form-control selectCvePartidaEspecifica" onchange="fnCargaPartidaArticulos('+orden+', '+ noReq +');"><option value="0">Partida...</option></select></li>'+
                '<li id="idCvePartidaCveArticulo' + orden + '" class="w10p p0"><select id="selectCveArticulo' + orden + '" name="selectCveArticulo' + orden + '" class="selectCveArticulo form-control" onchange="fnClaveArticulo('+orden+');"><option value="0">Cve ...</option></select></li>'+
                '<li id="idCvePartidaDescArticulo' + orden + '" class="w30p p0"><select id="selectArticulo' + orden + '" name="selectArticulo' + orden + '" class="form-control selectArticulo" onchange="fnDescArticulo('+orden+');"><option value="0">Articulos ...</option></select></li>'+
                '<li id="idUMArticulo' + orden + '" class="w5p pt5"><label  class="w100p addedUMArticulo" type="text" id="addedUMArticulo' + orden + '"></label></li>'+
                '<li id="idCantidadArticulo' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="addedCantidadArticulo num w100p text-center" type="text" id="addedCantidadArticulo' + orden + '" placeholder="Cantidad" onblur="fnCalculaDispValidaPpto('+orden+')"></li>'+
                '<li id="idPEArticulo' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="num w100p addedPEArticulo text-right" type="text" id="addedPEArticulo' + orden + '" placeholder="Precio" onblur="fnCalculaDispValidaPpto('+orden+')"></li>'+
                '<li id="idCantidadTotalArticulo' + orden + '" class="w5p pt5"><input class="addedCantidadTotalArticulo coin w100p  text-right" type="text" id="addedCantidadTotalArticulo' + orden + '" placeholder="Total" /></li>'+
                '<li id="idDisponibilidadArticulo' + orden + '" class="w10p pt5"><label  class="w100p addDispArticulo" type="text" id="addDispArticulo' + orden + '"></label><input type="text" id="contDispArt'+orden+'" class="hide" value="0"></li>'+
                '<li id="idRenglonAnexoArticulo' + orden + '" class="w10p pt5"><input onkeypress="return fnSoloBorrar(event)" class="w70p mr2" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/><div id="btnanexorenglonart'+orden+'" class="w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnMuestraAnexo(' + orden + '),fnMostrarRequisicionModal()"></div></li>'+
            '</ol>' +
        '</div>'+
        '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
            '<ol>'+
                '<li class="w15p pt5"><span><label>Clave Presupestal: </label></span></li>'+
                '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                    '<input type="text" id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"/>'+
                '</li>'+
                '<li class="w10p"><span><label>Presupuesto: </label></span></li>'+
                '<li class="w10p" id="idAddPresupuesto' + orden + '">'+
                    '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH" type="hidden" />'+
                    '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                '</li>'+
                '<li class="w10p" id="idValidaPresupuesto' + orden + '" >'+
                    '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto" type="text" />'+
                '</li>'+
            '</ol>'+
        '</div>'+
    '</div>');

    $("#btRemoveArticulo" + orden).click(function() { 
        $(this).parent().parent().parent().parent().remove();
        iCnt = iCnt - 1;
    });

    $("#selectCvePartidaEspecifica" + orden).empty();
    $("#selectCvePartidaEspecifica" + orden).append('<option value="0">Partida...</option>' + contenidosCvePartidaEspecifica);
    fnFormatoSelectGeneral("#selectCvePartidaEspecifica" + orden);
    $("#selectCvePartidaEspecifica" + orden).multiselect('rebuild');
    $("#addedCantidadTotalArticulo" + orden).prop("readonly", true);
    $("#addedCantidadTotalArticulo" + orden).attr('disabled', 'disabled');

}
function fnServicio(orden){
    
    $('#idMainListContentServicio').append(
        '<div id=idElemento' + orden + ' class="elemento m0 p0">'+
            '<div class="row p0 m0">' +
                '<ol id=idElementServicio' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' + 
                    '<li id="idEliminarServicio' + orden + '" class="w5p pt5"><span class="btnRemove btn btn-danger btn-xs glyphicon glyphicon-remove bt" id="btRemoveServicio' + orden + '" title="Eliminar"></span></li>' + 
                    '<li id="idNumServicio' + orden + '" class="w5p pt5"><input type="text" id="nServ' + orden + '" class="hide" value="'+orden+'"/><label  class="w50p text-center" type="text" id="numServicio' + orden + '">' + orden + '</label></li>' + 
                    '<li id="idAddedPartidaServicio' + orden + '" class="w10p"><select id="selectCvePartidaEspecificaServ' + orden + '" name="selectCvePartidaEspecificaServ' + orden + '" class="form-control selectCvePartidaEspecificaServ"><option value="0">Cve ...</option></select></li>' + 
                    '<li id="idAddedDescPartida' + orden + '" class="w20p"><select id="selectDescPartidaEspecificaServ' + orden + '" name="selectDescPartidaEspecificaServ' + orden + '" class="form-control selectDescPartidaEspecificaServ"><option value="0">Descripción ...</option></select></li>' + 
                    '<li id="idAddedDescServicio' + orden + '" class="w35p pt5"><input type="text" id="cveServicio' + orden + '" class="hide"/><input class="w95p" id="descServicio' + orden + '" type="text" /></li>' + 
                    '<li id="idAddLongDescServicio'+ orden +'" class="w5p pt5"><span class="btn btn-info btn-xs glyphicon glyphicon-comment" id="btLongDescServicio' + orden + '" onclick="fnEditorTextoOpen()"></span></li>'+
                    '<li id="idCantidadServicio' + orden + '" class="w5p pt5"><label  class="w100p addedCantidadServicio text-center" type="text" id="addedCantidadServicio' + orden + '">1</label></li>'+
                    '<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><input onkeypress="return soloNumeros(event)" class="w100p addedPEServicio" type="text" id="addedPEServicio' + orden + '" placeholder="Precio"/></li>' + 
                    '<li id="idAddedReglonServicio' + orden + '" class="w10p pt5"><input class="w70p mr2" type="text" id="addedReglonServicio' + orden + '" value="" placeholder="Renglón"/><div class="w20p mb3 p0 btn btn-default btn-xs glyphicon glyphicon-th-list" onclick="fnEditorTextoOpen(),fnMuestraAnexo(' + orden + ')"></li>' + 
                '</ol>' +
            '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">'+
                '<ol>'+
                    '<li class="w15p pt5"><span><label>Clave Presupestal: </label></span></li>'+
                    '<li class="w50p" id="idAddCvePresupuestal' + orden + '" >'+
                        '<input type="text" id="selectPartidaEspecificaCvePresupuestalServ' + orden + '" name="selectPartidaEspecificaCvePresupuestalServ' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestalServ"/>'+
                    '</li>'+
                    '<li class="w10p"><span><label>Presupuesto: </label></span></li>'+
                    '<li class="w10p" id="idAddPresupuesto' + orden + '">'+
                        '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH" placeholder="Presupuesto" type="hidden" />'+
                        '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                    '</li>'+
                    '<li class="w10p" id="idValidaPresupuesto' + orden + '" >'+
                        '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto" type="text" />'+
                    '</li>'+
                '</ol>'+
            '</div>'+
            /*'<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">' +
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
                    '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH" placeholder="Presupuesto" type="text" />'+
                    '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                '</div>' +
                '<div id="idValidaPresupuesto' + orden + '" class="col-lg-2 col-md-2 col-sm-2">'+
                    '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto Suficiente" type="text" />'+
                '</div>' +
            '</div>'+*/
            '<div id="idPopupEditor'+orden+'" class="idPopupEditor" style="display: none;">'+
                '<div class="content-popup">'+
                    '<div class="popupEditorClose" onclick="fnEditorTextoClose()"><a href="#" id="idPopupEditorClose" onclick="">x</a></div>'+
                    '<div class="hA">'+
                        '<h2>Descripción del Servicio</h2>'+
                        '<textarea class="w100p" name="" id="idLongText'+orden+'" cols="" rows="20"></textarea>'+
                    '</div>'+
                '</div>'+
            '</div>'+
            '<div class="popup-overlay" style="display: none;"></div>'+
        '</div>');

    /*$("#btRemoveServicio" + orden).click(function() { 
                $(this).parent().parent().remove();
                $("#idCvePresupuestal" + orden).remove();
                iCnt = iCnt - 1;
            });*/
    $("#btRemoveServicio" + orden).click(function() { 
        $(this).parent().parent().parent().parent().remove();
        iCnt = iCnt - 1;
    });

}
function fnGuardarRequisicion(){
    alert("Guardar Movimientos... ");
    $("#idMainListContentServicio").val();
    //id="btRemoveServicio' + orden + '"
    //var elementos = $("#idMainListContentArticulo .elemento").length;
    var elementos = [];
    alert(elementos);
    for (var elemento in elementos) {
        alert(elemento);
    }

}

function fnCancelarMovimientos(){
    alert("Cancelar Movimientos... ");
}

// Funcion que trae los articulos por la partida especifica
function fnCargaPartidaArticulos(orden, orderno) {
    muestraCargandoGeneral();
    var cpeprod = $("#selectCvePartidaEspecifica" + orden).val();
    var tagref = $("#selectUnidadNegocio").val();
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

            ocultaCargandoGeneral();
        }
    }).fail(function(result) {
        ocultaCargandoGeneral();
        console.log("ERROR");
        console.log(result);
        //alert(result);
    });
}
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

            //fnModificarArticulo(idReq, orden);
            fnObtenerPresupuesto(orden);
            fnCambioPresupuesto(orden);
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
            //fnModificarArticulo(noReq,orden);
            fnObtenerPresupuesto(orden);
            fnCambioPresupuesto(orden);
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

$(document).on('change','.addedCantidadTotalArticulo',function(){
    

});
function fnCambioPresupuesto(id){

    var partida;
    var partida2;
 
   partida=$('#selectCvePartidaEspecifica'+id+' option:selected').val();

    var presupuesto = $(".addPresupuestoH");
    var presupuestoInicial=presupuesto.eq(0);
  

    var presupuestoAux=0;
     
      var inicio=0;
      var total=0;
        var j=0;
        var total1=0;
        var ultima=0;

    for(var i=0; i<presupuesto.length; i++){

    var numerod;
     numerod= presupuesto.eq(i).attr('id'); //id
     numerod=numerod.replace("addPresupuestoH",""); //id

     partida2=$('#selectCvePartidaEspecifica'+numerod).val();

     if(Number(partida)==Number(partida2)){
           presu= $('#addPresupuesto'+numerod).text();
           if(j==0){
                inicio=presu;
           }
           if(j!=0){

                ultimo=0;
                for(d=0;d<posicionesUltimas.length;d++){
                  if(posicionesUltimas[d].includes(partida)){
                    y=posicionesUltimas[d].split('-');
                    ultimo=y[1];
                  }
                }
                    //alert(ultima);
               total=$('#addedCantidadTotalArticulo'+(ultima)).val();
               total1= total1+parseInt(total);
               inicio=inicio-total;
               if(inicio > 0){
                $('#addPresupuesto'+numerod).text(inicio);
                $("#validaPresupuesto" + numerod).val("Ppto Suficiente");
                $("#validaPresupuesto" + numerod).css('border','solid 1px #1B693F');
                $("#validaPresupuesto" + numerod).css('color','#1B693F');
                $("#validaPresupuesto" + numerod).prop("readonly", true);
                $("#validaPresupuesto" + numerod).attr('disabled', 'disabled');
                iCntPI = 0;
               }else{
                $('#addPresupuesto'+numerod).html(0);
                $("#validaPresupuesto" + numerod).val("Ppto Insuficiente");
                $("#validaPresupuesto" + numerod).css('border','solid 1px #ff0000');
                $("#validaPresupuesto" + numerod).css('color','#ff0000');
                $("#validaPresupuesto" + numerod).prop("readonly", true);
                $("#validaPresupuesto" + numerod).attr('disabled', 'disabled');
                iCntPI = iCntPI +1;
               }
               
            

           }
       
        j++;
        ultima=numerod;
     }

    }
    posicionesUltimas.push(partida+'-'+ultima);

}
$(document).on('change','.addedCantidadTotalArticulo',function(){

       numero= $(this).attr('id');
       //alert(numero);
       numero=numero.replace('addedCantidadTotalArticulo','');
       fnCambioPresupuesto(numero);
     

    
});

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
            info=data.contenido.Fecha;
            $("#idFechaElaboracion").val(""+info[0].fechaDMY); 
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
            info=data.contenido.Fecha;
            $("#idFechaEntrega").val(""+info[0].fechaDMY); 
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
    });
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
