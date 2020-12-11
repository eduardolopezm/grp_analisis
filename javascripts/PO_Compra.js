/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de Agosto del 2017
 */

var url= "modelo/PO_Compra_modelo.php";
var idReq = idRequisicionGeneral;
var noReq = noRequisicionGeneral;
var iCnt = 0;
var estatusGenerarLayout = 4;
var funcionGenerarLayout = 2265;
var typeGenerarLayout = 19;
var tipoLayout = 1;
var periodoR = periodoReq;
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

$(document).ready(function () {
	fnMuestraRequicionesAutorizadas(idReq,noReq);

	$('#idPopupEditorClose').click(function() {
        $('.idPopupEditor').fadeOut('slow');
        $('.popup-overlay').fadeOut('slow');
        return false;
    });

});

function fnMuestraRequicionesAutorizadas(idReq,noReq){
	//alert(idReq + ' - ' + noReq);
    dataObj = { 
            option: 'traeRequicionesAutorizadas',
            idReq: idReq,
            noReq: noReq
        };
    $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj

        }).done(function(data) {
            //console.log("Bien");
            if (data.result) {
            dataRequisicionJason = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            var columnasDescartarExportar = [0];
            fnLimpiarTabla('divTabla', 'divRequisicionTabla');
            fnAgregarGrid_Detalle(dataRequisicionJason, columnasNombres, columnasNombresGrid, 'divRequisicionTabla', ' ', 1, columnasDescartarExportar, false);

            }else{
            	alert("Error");
            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            
        });

}

function fnMuestraProveedores(){
	var nombreProveedor = $("#Keywords").val();
	var codeProveedor = $("#SuppCode").val();
	var rfcProveedor = $("#SuppTaxid").val();
    //alert(nombreProveedor +' - '+ codeProveedor +' - '+ rfcProveedor);
	if (nombreProveedor == "" && codeProveedor == "" && rfcProveedor == "") {
		muestraMensaje('Agregar elemento para relizar la busqueda', 3, 'msjValidacion', 5000);
		return false;
	}
	dataObj = { 
            option: 'traeProveedores',
            nombreProveedor: nombreProveedor,
            codeProveedor: codeProveedor,
            rfcProveedor: rfcProveedor
        };
    $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj

        }).done(function(data) {
            //console.log("Bien");
            if (data.result) {
            dataProveedorJason = data.contenido.datos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            var columnasDescartarExportar = [];
            $("#datosProveedor").removeClass("hide");
            $("#divTabla2").removeClass("hide");
            fnLimpiarTabla('divTabla2', 'divProveedorTabla');
            fnAgregarGrid_Detalle(dataProveedorJason, columnasNombres, columnasNombresGrid, 'divProveedorTabla', ' ', 1, columnasDescartarExportar, false);

            }else{

            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            
        });
}
function fnSeleccionarProveedor(supplierid){
	//alert("compra" + supplierid);
	//$("#datosProveedor").addClass("hide");
    $("#divTabla2").addClass("hide");
    $("#infoProveedor").removeClass("hide");
    dataObj = { 
            option: 'muestraProveedor',
            supplierid: supplierid
        };
    $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj

        }).done(function(data) {
            //console.log("Bien");
            if (data.result) {
                dataProveedorSeleccionadoJson = data.contenido.datos;
                var supplierid = "";
                var suppname = "";
                var address1 = "";
                var address2 = "";
                var address3 = "";
                var address4 = "";
                var taxid = "";
                var currcode = "";

                for (var info in dataProveedorSeleccionadoJson) {
                    supplierid = dataProveedorSeleccionadoJson[info].supplierid;
                    suppname = dataProveedorSeleccionadoJson[info].suppname;
                    taxid = dataProveedorSeleccionadoJson[info].taxid;
                    address1 = dataProveedorSeleccionadoJson[info].address1;
                    address2 = dataProveedorSeleccionadoJson[info].address2;
                    address3 = dataProveedorSeleccionadoJson[info].address3;
                    address4 = dataProveedorSeleccionadoJson[info].address4;
                    currcode = dataProveedorSeleccionadoJson[info].currcode;
                }
                $("#idCodePro").val("" + supplierid);
                $("#codePro").html(supplierid);
                $("#idNomPro").val("" + suppname);
                $("#nomPro").html(suppname);
                $("#idRFCPro").val("" + taxid);
                $("#rfcPro").html(taxid);
                $("#idColPro").val("" + address1);
                $("#colPro").html(address1);
                $("#idCallePro").val("" + address2);
                $("#callePro").html(address2);
                $("#idCiudadPro").val("" + address3);
                $("#ciudadPro").html(address3);
                $("#idEdoPro").val("" + address4);
                $("#edoPro").html(address4);
                $("#idMonedaPro").val("" + currcode);
                $("#monedaPro").html(currcode);
            
            }else{

            }
        }).fail(function(result) {
            console.log("ERROR");
            console.log(result);
            
        });
}
function fnMostrarAgregarElemento(){
    $("#agregarElemento").removeClass("hide");
    //alert("Agregar Elemento");
}
function fnAgregarElementoRequisicion(mbflag,idReq){
    alert(mbflag + " - " + idReq);
    dataObj = {
        option: 'agregarElementosRequisicion',
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
        var orden = "";
        if (data.result) {
            //muestraMensaje('Se agrego el elemento a la Requisición: '+ noReq, 1, 'divMensajeOperacion', 5000);
            //orden = data.contenido;
            dataR = data.contenido.datos
                //muestraMensaje('Se agrego la Requisición con el número: '+ data.contenido, 1, 'divMensajeOperacion', 5000);
                //var noReq = dataR[0].requisitionno;
                var orden = dataR[0].orden;
                var tagref = dataR[0].tagref;
            fnCargarPartidaProducto(periodoR,tagref); 
            fnCargarPartidaServicio(periodoR,tagref);

            if(mbflag == 'B'){
                //alert("esto es un producto " + mbflag);
                //alert(data.contenido);
                fnArticulo(orden);
            }else{
                //alert("esto es un Servicio " + mbflag);
                fnServicio(orden);
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
//function fnArticulo(noReq,orden,fEntrega,idPartida,idItem,descItem,unidad,cantidad,precio,total,existencia,clavepresupuestal,descLarga,renglon){
function fnArticulo(orden){    
    /*alert("noReq: "+noReq);
    alert("orden: "+orden);
    alert("idPartida: "+idPartida);
    alert("idItem: "+idItem);
    alert("fEntrega: "+fEntrega);
    alert("descItem: "+descItem);
    alert("unidad: "+unidad);
    alert("cantidad: "+cantidad);
    alert("precio: "+precio);
    alert("total: "+total);
    alert("existencia: "+existencia);
    alert("clavepresupuestal: "+clavepresupuestal);
    alert("descLarga: "+descLarga);*/

    $(containerArticulo).append('<div class="row p0 m0">'+
            '<ol id=idElementArticulo' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' +
                '<li id="idEliminarArticulo' + orden + '" class="w5p pt5"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove" id="btRemoveArticulo' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumArticulo' + orden + '" class="w5p pt5"><input type="text" id="nProd' + orden + '" class="hide" value="'+orden+'"/><label  class="w100p text-center" type="text" id="numArticulo' + orden + '"></label></li>' + 
                '<li id="idCvePartida" class="w10p p0"><select id="selectCvePartidaEspecifica' + orden + '" name="selectCvePartidaEspecifica' + orden + '" class="w100p form-control selectCvePartidaEspecifica"><option value="0">Partida...</option></select></li>'+
                '<li id="idCvePartidaCveArticulo' + orden + '" class="w10p p0"><select id="selectCveArticulo' + orden + '" name="selectCveArticulo' + orden + '" class="form-control selectCveArticulo"><option value="0">Cve ...</option></select></li>'+
                '<li id="idCvePartidaDescArticulo' + orden + '" class="w30p p0"><select id="selectArticulo' + orden + '" name="selectArticulo' + orden + '" class="form-control selectArticulo"><option value="0">Articulos ...</option></select></li>'+
                '<li id="idUMArticulo' + orden + '" class="w5p pt5"><label  class="w100p addedUMArticulo" type="text" id="addedUMArticulo' + orden + '"></label></li>'+
                '<li id="idCantidadArticulo' + orden + '" class="w5p pt5"><input class="w100p addedCantidadArticulo text-center" type="text" id="addedCantidadArticulo' + orden + '" placeholder="Cantidad"/></li>'+
                '<li id="idPEArticulo' + orden + '" class="w5p pt5"><input onchange="" class="coin w100p addedPEArticulo text-right" type="text" id="addedPEArticulo' + orden + '" placeholder="Precio" /></li>'+
                '<li id="idCantidadTotalArticulo' + orden + '" class="w5p pt5"><input class="coin w100p addedCantidadTotalArticulo text-right" type="text" id="addedCantidadTotalArticulo' + orden + '" placeholder="Total"/></li>'+
                '<li id="idDisponibilidadArticulo' + orden + '" class="w10p pt5"><label  class="w100p addDispArticulo" type="text" id="addDispArticulo' + orden + '"></label></li>'+
                '<li id="idRenglonAnexoArticulo' + orden + '" class="w10p pt5"><input class="w100p" type="text" id="addedReglonArticulo' + orden + '" value="" placeholder="Renglón"/></li>'+
            '</ol>' +
        '</div>'+
            '<div id="idCvePresupuestal' + orden + '" class="row borderB ptb5 m0 text-center hide">' +
                '<div class="col-lg-2 col-md-2 col-sm-2 pt10">' +
                    '<span><label>Clave Presupestal: </label></span>' +
                '</div>' +
                '<div id="idAddCvePresupuestal' + orden + '" class="col-lg-6 col-md-6 col-sm-6">'+
                    '<select id="selectPartidaEspecificaCvePresupuestal' + orden + '" name="selectPartidaEspecificaCvePresupuestal' + orden + '" class="form-control selectPartidaEspecificaCvePresupuestal"><option value="0">Clave Presupestal</option></select>'+
                '</div>' +
                '<div class="col-lg-1 col-md-1 col-sm-1 pt10">' +
                    '<span><label>Presupuesto: </label></span>' +
                '</div>' +
                '<div id="idAddPresupuesto' + orden + '" class="col-lg-1 col-md-1 col-sm-1 text-center pt10">'+
                    '<input id="addPresupuestoH' + orden + '" name="addPresupuestoH' + orden + '" class="addPresupuestoH hide" placeholder="Presupuesto" type="text" />'+
                    '<label  id="addPresupuesto' + orden + '" name="addPresupuesto' + orden + '" class="w100p addPresupuesto" type="text"></label>'+
                '</div>' +
                '<div id="idValidaPresupuesto' + orden + '" class="col-lg-2 col-md-2 col-sm-2">'+
                    '<input id="validaPresupuesto' + orden + '" name="validaPresupuesto' + orden + '" class="form-control validaPresupuesto" placeholder="Ppto Suficiente" type="text" />'+
                '</div>' +
            '</div>');

            $('#idMainListContentArticulo').append(containerArticulo);

            $("#btRemoveArticulo" + orden).click(function() { 
                $(this).parent().parent().remove();
                $("#idCvePresupuestal" + orden).remove();
                //fnEliminarArticulo(noReq,orden);
                orden = orden - 1;
            });
            
                $("#numArticulo" + orden).html(""+orden);
                $("#numArticulo" + orden).prop("readonly", true);
                $("#numArticulo" + orden).attr('disabled', 'disabled');
                $("#selectCvePartidaEspecifica" + orden).empty();
                $(".btn-group").addClass('w100p');
                $("#selectCvePartidaEspecifica" + orden).append('<option value="0">Partida...</option>' + contenidosCvePartidaEspecifica);
                $("#selectCvePartidaEspecifica" + orden).multiselect('rebuild');
                /*$("#selectCvePartidaEspecifica" + orden).change(function(){
                    muestraCargandoGeneral();
                    var cpeprod = $("#selectCvePartidaEspecifica" + orden).val();
                    var tagref = $("#selectUnidadNegocio").val();
                    //fnModificarArticulo(req,orden);
                    //alert("tagref:" + tagref);
                    dataObj = { 
                        option: 'mostrarPartidaCveArticulo',
                        dato: cpeprod,
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
                            var contenidoIdArt = "";
                            var contenidoDescArt = "";
                            var contenidoDescPartida = "";
                            var contenidoUnidadArt = "";
                            var contenidoPrecioEArt = "";
                            var contenidoDispArt = "";
                            var contenidoCvePresupuestal = "";
                            for (var info in dataJson) {
                                contenidoIdArt += "<option value='" + dataJson[info].idProducto + "'>" + dataJson[info].idProducto + "</option>";
                                contenidoDescArt += "<option value='" + dataJson[info].descripcionProducto + "'>" + dataJson[info].descripcionProducto + "</option>";
                                contenidoUnidadArt = dataJson[info].unidad;
                                contenidoPrecioEArt = dataJson[info].precioEstimado;
                                contenidoDispArt = dataJson[info].existencia;
                                contenidoCvePresupuestal = "<option value='" + dataJson[info].cvePresupuestal + "'>" + dataJson[info].cvePresupuestal + "</option>";
                            }
                            $("#selectCveArticulo" + orden).empty();
                            $("#selectCveArticulo" + orden).append('<option value="0">Cve ...</option>' + contenidoIdArt);
                            $("#selectCveArticulo" + orden).multiselect('rebuild');
                            $("#selectCveArticulo" + orden).change(function(){
                                //muestraCargandoGeneral();
                                var cveprod = $("#selectCveArticulo" + orden).val();
                                //alert(cveprod);
                                var tagref = $("#selectUnidadNegocio").val();
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
                                        
                                        $("#selectArticulo" + orden).empty();
                                        $("#selectArticulo" + orden).append(contDescArt);
                                        $("#selectArticulo" + orden).multiselect('rebuild');
                                        $("#addedUMArticulo" + orden).empty();
                                        $("#addedUMArticulo" + orden).append(contUnidadArt);
                                        $("#addedUMArticulo" + orden).prop("readonly", true);
                                        $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
                                        $("#addedCantidadArticulo" + orden).blur(function(){
                                            var a = $("#addedCantidadArticulo" + orden).val();
                                            var b = $("#addedPEArticulo" + orden).val();
                                            var t = a * b;
                                            var p = $("#addPresupuestoH"+orden).val();
                                            var d = p - t;
                                            //alert("disponibilidad: "+ p);
                                            $("#addedCantidadTotalArticulo" + orden).val(""+t);
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
                                            
                                            $("#addDispArticulo" + orden).val("");
                                            $("#addDispArticulo" + orden).html(contDispArt);
                                            $("#addDispArticulo" + orden).prop("readonly", true);
                                            $("#addDispArticulo" + orden).attr('disabled', 'disabled');
                                            //fnModificarArticulo(noReq,orden);
                                            
                                        });
                                        $("#addedPEArticulo" + orden).val("");
                                        $("#addedPEArticulo" + orden).val(""+ contPrecioEArt); 
                                        $("#addedPEArticulo" + orden).blur(function(){
                                            var a = $("#addedCantidadArticulo" + orden).val();
                                            var b = $("#addedPEArticulo" + orden).val();
                                            var t = a * b;
                                            var p = $("#addPresupuestoH"+orden).val();
                                            var d = p - t;
                                            //alert("total: "+ t);
                                            $("#addedCantidadTotalArticulo" + orden).val(""+t);
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
                                            
                                            $("#addDispArticulo" + orden).val("");
                                            $("#addDispArticulo" + orden).html(contDispArt);
                                            $("#addDispArticulo" + orden).prop("readonly", true);
                                            $("#addDispArticulo" + orden).attr('disabled', 'disabled');
                                            //fnModificarArticulo(noReq,orden);
                                        });
                                        $("#addDispArticulo" + orden).val("");
                                        $("#addDispArticulo" + orden).html(contDispArt);
                                        $("#addDispArticulo" + orden).prop("readonly", true);
                                        $("#addDispArticulo" + orden).attr('disabled', 'disabled');

                                        //fnModificarArticulo(noReq,orden);
                                        //ocultaCargandoGeneral();
                                    }else{
                                        //ocultaCargandoGeneral();
                                    }

                                }).fail(function(result) {
                                    console.log("ERROR");
                                    console.log(result);
                                    //alert(result);
                                });

                            });

                            $("#selectArticulo" + orden).empty();
                            $("#selectArticulo" + orden).append('<option value="0">Articulo ...</option>' + contenidoDescArt);
                            $("#selectArticulo" + orden).multiselect('rebuild');
                            $("#selectArticulo" + orden).change(function(){
                                //muestraCargandoGeneral();
                                var descprod = $("#selectArticulo" + orden).val();
                                var tagref = $("#selectUnidadNegocio").val();
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
                                        
                                        $("#selectCveArticulo" + orden).empty();
                                        $("#selectCveArticulo" + orden).append(contIdArt);
                                        $("#selectCveArticulo" + orden).multiselect('rebuild');
                                        $("#addedUMArticulo" + orden).val("");
                                        $("#addedUMArticulo" + orden).val(""+ contUnidadArt);
                                        $("#addedUMArticulo" + orden).prop("readonly", true);
                                        $("#addedUMArticulo" + orden).attr('disabled', 'disabled');
                                        $("#addedCantidadArticulo" + orden).blur(function(){
                                            var a = $("#addedCantidadArticulo" + orden).val();
                                            var b = $("#addedPEArticulo" + orden).val();
                                            var t = a * b;
                                            var p = $("#addPresupuestoH"+orden).val();
                                            var d = p - t;
                                            //alert("total: "+ t);
                                            $("#addedCantidadTotalArticulo" + orden).val(""+t);
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
                                            
                                            $("#addDispArticulo" + orden).val("");
                                            $("#addDispArticulo" + orden).html(contDispArt);
                                            $("#addDispArticulo" + orden).prop("readonly", true);
                                            $("#addDispArticulo" + orden).attr('disabled', 'disabled');
                                            //fnModificarArticulo(noReq,orden);
                                            
                                        });
                                        $("#addedPEArticulo" + orden).val("");
                                        $("#addedPEArticulo" + orden).val(""+ contPrecioEArt);
                                        $("#addedPEArticulo" + orden).blur(function(){
                                            var a = $("#addedCantidadArticulo" + orden).val();
                                            var b = $("#addedPEArticulo" + orden).val();
                                            var t = a * b;
                                            var p = $("#addPresupuestoH"+orden).val();
                                            var d = p - t;
                                            //alert("total: "+ t);
                                            $("#addedCantidadTotalArticulo" + orden).val(""+t);
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
                                            
                                            $("#addDispArticulo" + orden).val("");
                                            $("#addDispArticulo" + orden).html(contDispArt);
                                            $("#addDispArticulo" + orden).prop("readonly", true);
                                            $("#addDispArticulo" + orden).attr('disabled', 'disabled');
                                            //fnModificarArticulo(noReq,orden);
                                        });
                                        $("#addDispArticulo" + orden).val("");
                                        $("#addDispArticulo" + orden).val(""+contDispArt);
                                        $("#addDispArticulo" + orden).prop("readonly", true);
                                        $("#addDispArticulo" + orden).attr('disabled', 'disabled');
                                        $("#validaPresupuesto" + orden).prop("readonly", true);
                                        $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
                                        //fnModificarArticulo(noReq,orden);
                                        //ocultaCargandoGeneral();
                                    }else{
                                        //ocultaCargandoGeneral();
                                    }

                                }).fail(function(result) {
                                    console.log("ERROR");
                                    console.log(result);
                                    //alert(result);
                                });

                            });
                            $("#idCvePresupuestal" + orden).removeClass("hide");
                            $("#selectPartidaEspecificaCvePresupuestal" + orden).empty();
                            $("#selectPartidaEspecificaCvePresupuestal" + orden).append(contenidoCvePresupuestal);
                            $("#selectPartidaEspecificaCvePresupuestal" + orden).multiselect('rebuild');
                            //fnModificarArticulo(noReq,orden);
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
                    //fnModificarArticulo(noReq,orden);     
                });*/
                $("#addedReglonArticulo" + orden).html(renglon);
                $("#validaPresupuesto" + orden).prop("readonly", true);
                $("#validaPresupuesto" + orden).attr('disabled', 'disabled');
    
}
function fnServicio(orden){
    alert("Servicio" + orden);
    $(containerServicio).append('<div class="row p0 m0">' +
        '<ol id=idElementServicio' + orden + ' class="col-lg-12 col-md-12 col-sm-12 p0 m0">' + 
                '<li id="idEliminarServicio' + orden + '" class="w5p pt5"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove bt" id="btRemoveServicio' + orden + '" title="Eliminar"></span></li>' + 
                '<li id="idNumServicio' + orden + '" class="w5p pt5"><input type="text" id="nServ' + orden + '" class="hide" value="'+orden+'"/><label  class="w50p text-center" type="text" id="numServicio' + orden + '"></label></li>' + 
                '<li id="idAddedPartidaServicio' + orden + '" class="w10p"><select id="selectCvePartidaEspecificaServ' + orden + '" name="selectCvePartidaEspecificaServ' + orden + '" class="form-control selectCvePartidaEspecificaServ"><option value="0">Cve ...</option></select></li>' + 
                '<li id="idAddedDescPartida' + orden + '" class="w20p"><select id="selectDescPartidaEspecificaServ' + orden + '" name="selectDescPartidaEspecificaServ' + orden + '" class="form-control selectDescPartidaEspecificaServ"><option value="0">Descripción ...</option></select></li>' + 
                //'<li id="idAddedDescServicio' + orden + '" class="w35p"><input type="text" id="selectCveServicio' + orden + '" class="hide"/><select id="selectServicio' + orden + '" name="selectServicio' + orden + '" class="form-control selectServicio"><option value="0">Servicios</option></select></li>' + 
                '<li id="idAddedDescServicio' + orden + '" class="w35p pt5"><input type="text" id="cveServicio' + orden + '" class="hide"/><input class="w95p" id="descServicio' + orden + '" type="text" /></li>' + 
                '<li id="idAddLongDescServicio'+ orden +'" class="w5p pt5"><span class="btn btn-info btn-xs glyphicon glyphicon-comment" id="btLongDescServicio' + orden + '" onclick="fnEditorTextoOpen()"></span></li>'+
                '<li id="idCantidadServicio' + orden + '" class="w5p pt5"><label  class="w100p addedCantidadServicio text-center" type="text" id="addedCantidadServicio' + orden + '">1</label></li>'+
                '<li id="idAddedPEServicio' + orden + '" class="w5p pt5"><input class="w100p addedPEServicio" type="text" id="addedPEServicio' + orden + '" placeholder="Precio"/></li>' + 
                //'<li id="idAddFileServico' + orden + '" class="w10p"><label for="addFileServicio' + orden + '" class="w60p btn btn-info glyphicon glyphicon-paperclip"></label><input type="file" id="addFileServicio' + orden + '" value="" multiple class="hide"></li>' + 
                '<li id="idAddedReglonServicio' + orden + '" class="w10p pt5"><input class="w100p" type="text" id="addedReglonServicio' + orden + '" value="" placeholder="Renglón"/></li>' + 
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
                //fnEliminarServicio(noReq,orden);
                orden = orden - 1;
            });
}