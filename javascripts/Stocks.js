/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Luis Aguilar Sandoval
 * @version 0.1
 * @Fecha 21 de Agosto del 2017
 */
////
/* variables globales inicio */

//Envio a capa

var url = "modelo/Stocks_Modelo.php";
var idstock = StockID;
var idpartida = PartidaID;
var tipo = Tipo;
var Advertencia = false;

/* variables globales fin */
$(document).ready(function(){
    fnFormatoSelectGeneral("#selectTipoProducto");
    fnFormatoSelectGeneral("#selectPartidaEspecifica");
    fnFormatoSelectGeneral("#selectCabms");
    fnFormatoSelectGeneral("#status");
    fnFormatoSelectGeneral("#units");

    fnMbflag();

    if(idstock != ''){
        fnMostrarDatosStock(idstock, idpartida, tipo);
        fnBloquearStock(idstock);    
    }

    $('#description').on('keyup', function (e) {
        if (!/^[ a-zA-Z0-9./%+#&?¿,{}();_:áéíóúüñÁÉÍÓÚÜN@\-]*$/i.test(this.value)) {
            this.value = this.value.replace(/[^ a-z0-9./%+#&?¿,{}();_:áéíóúüñÁÉÍÓÚÜN@\-]+/ig,"");
        }
    });
    $('#longDescription').on('keyup', function (e) {
        if (!/^[ a-zA-Z0-9./%+#&?¿,{}();_:áéíóúüñÁÉÍÓÚÜN@\-]*$/i.test(this.value)) {
            this.value = this.value.replace(/[^ a-z0-9./%-+#&?¿,{}();_:áéíóúüñÁÉÍÓÚÜN@\-]+/ig,"");
        }
    });
    $('#Familia').on('keypress', function (e) {
        return soloNumeros(e);
    });
    $('#eliminar').on('click', function(){
        fnDeleteStock();
    });
    $('#guardarProd').on('click', function(){
        fnSaveStock();
    });
    $('#selectTipoProducto').on('change', function(){
        //muestraCargandoGeneral();
        mbflag = $( this ).val();
        fnPartidaEspecifica(mbflag);

        fnUnits(mbflag);

        if(mbflag =="B"){
            $('#Familia').prop('readonly',false);
        }
        if(mbflag == "D"){
            $('#Familia').val("");
            $('#Familia').prop('readonly',true);
        }
        //ocultaCargandoGeneral();
    });
    $('#selectPartidaEspecifica').on('change', function(){
        var mbflag = $('#selectTipoProducto').val();
        var partidaID =  $( this ).val();
        fnCabms(partidaID, mbflag);
    });


    if(funcionVer == "1"){
        $('#guardarProd').hide();
        $('#eliminar').hide();
        $('#cancelar').hide();
        fnDesabilitarCampos();
    }

});

$(document).on('click','.cerrarModalCancelar',function(){
    location.replace("./SelectProduct.php");
});

$(document).on('click','#cancelar',function(){
    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    location.replace("./Stocks_V_2.php?");
});

$(document).on('click','#eliminar',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
});

function fnDesabilitarCampos(){
    
    $('#selectTipoProducto').multiselect('disable');
    $('#selectPartidaEspecifica').multiselect('disable');
    $('#selectCabms').multiselect('disable');
    $('#status').multiselect('disable');
    $('#units').multiselect('disable');

    $("#StockID").prop("disabled", true);
    $("#Familia").prop("disabled", true);
    $("#description").prop("disabled", true);
    $("#longDescription").prop("disabled", true);

}


function fnMbflag(){
        dataObj = { 
            option: 'mostrarTipoProducto'
        };
        $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
        })
        .done(function( data ) {
            if(data.result) {
                dataMbflag = data.contenido.datos;
                var mbflagID = "";
                var mbflagDesc = "";
                var mbflagNew = "";
                for (var info in dataMbflag) {
                    mbflagID = dataMbflag[info].value;
                    mbflagDesc = dataMbflag[info].texto;
                    mbflagNew += "<option value="+mbflagID+">"+mbflagDesc+"</option>";
                }
                
                $('#selectTipoProducto').empty();
                $('#selectTipoProducto').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
                $('#selectTipoProducto').multiselect('rebuild');
            }
        })
        .fail(function(result) {
            console.log( result );
        });
}

function fnBloquearStock(idstock){
    dataObj = { 
          option: 'bloquearStock',
          stockID: idstock
        };
        $.ajax({
            method: "POST",
            dataType:"json",
            url: url,
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                if(data.contenido == 1 || data.contenido == '1'){
                    Advertencia = true;
                    //#longDescription, #description, 
                    $('#StockID, #Familia').prop('readonly',true);
                    //$('#status').multiselect('disable');
                    $('#selectTipoProducto').multiselect('disable');
                    $('#selectPartidaEspecifica').multiselect('disable');
                    $('#selectCabms').multiselect('disable');
                    //$('#units').multiselect('disable');
                    //$('#guardarProd').hide();
                    $('#eliminar').hide();
                    $('#cancelar').hide();
                }
            }
        })
        .fail(function(result) {
          console.log("ERROR");
          console.log( result );
        });
}

function fnValidarDatos(){
    var tipo = $('#selectTipoProducto').val();
    var partida = $('#selectPartidaEspecifica').val();
    var cambs = $('#selectCabms').val();
    var code = $('#StockID').val();
    var fam =  $('#Familia').val();
    var status = $('#status').val();
    var unidad = $('#units').val();
    var longDesc = $('#longDescription').val();
    var shortDesc = $('#description').val();
    var msg = "";
    var validar = false;
    var datosStock = [];

    if(tipo == '' || tipo == '0'){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Tipo</p>';
    }
    if(partida == '' || partida == '0'){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Partida Específica.</p>';
    }

    if(partida != '' || partida!="0"){
        dataObj = { 
          option: 'validarCMABSGeneral',
          partida: partida
        };

        $.ajax({
            async:false,
            method: "POST",
            dataType:"json",
            url: url,
            data:dataObj
        })
        .done(function( data ) {
            if(data.result){
                dataJson = data.contenido.datos;
                for (key in dataJson){
                    if(dataJson[key].cambsGeneral =='0'){
                        if(cambs == '' || cambs == '0' || cambs == '-1'){
                            msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Clave CAMBS.</p>';
                        }
                    }
                }
            }
        })
        .fail(function(result) {
          console.log("ERROR");
          console.log( result );
        });
    }

    // if(cambs == '' || cambs == '0' || cambs == '-1'){
    //     msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Clave CAMBS del producto.</p>';
    // }

    if(code == ''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Código.</p>';
    }
    if(tipo == 'B'){
        if(fam == ''){
            msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Familia.</p>';
        }
    }
    if(unidad == '' || unidad == '0'){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Unidad de Medida.</p>';
    }
    

    if(shortDesc == ''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Descripción corta.</p>';
    }
    if(shortDesc.length > 50){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>La descripción corta no puede superar los 50 caracteres</p>';
    }
    if(longDesc == ''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Descripción larga.</p>';
    }
    if(longDesc.length > 250){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>La descripción larga debe ser maximo de 250 caracteres .</p>';
    }
    if(msg == ''){
        validar = true;
        datosStock = {'tipo': tipo, 'partida': partida, 'code':code, 'fam': fam, 'status': status, 'unidad': unidad, 'longDesc': longDesc, 'shortDesc': shortDesc, 'cambs': cambs}
    }else{

        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datosStock;
}

function fnSaveWithWarning(){
    Advertencia = false;
    fnSaveStock();
}

function fnSaveStock(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
    var arrayStock = fnValidarDatos();
    console.log(idstock);
    if(Advertencia){
        $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>¿Realmente desea modificar la Información?.</p>','','fnSaveWithWarning()');
        return 0;
    }
    if(idstock == ''){
        var a = $('#StockID').val();
        console.log("nuevo");
        console.log(a);

        if(arrayStock != ''){
            dataObj = { 
                option: 'guardarProducto',
                arrayStock: arrayStock
            };
            $.ajax({
              async:false,
              cache:false,
              method: "POST",
              dataType:"json",
              url: url,
              data: dataObj
            })
            .done(function( data ) {

                var tipo = $('#selectTipoProducto').val();
                var strTipo = "servicio";
                if(tipo=='B'){
                    strTipo="bien";
                }

                if(data.result) {
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se guardó el '+strTipo+' con el código: '+data.contenido+'</p>');
                }else{
                    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>El código '+data.contenido+' ya está asignado.</p>');
                }
            })
            .fail(function(result) {
                console.log( result );
            });
        }
    }else{
        if(arrayStock != ''){
            fnModificarStock(arrayStock);
        }
    }
}

function fnDeleteStock(){
    var arrayStock = fnValidarDatos();
    var stockid = $('#StockID').val();
    if(arrayStock != ''){
        dataObj = { 
            option: 'eliminarProducto',
            stockid: stockid
        };
        $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
        })
        .done(function( data ) {
            if(data.result) {
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el registro '+data.contenido+' con éxito.</p>');
            }
        })
        .fail(function(result) {
            console.log( result );
        });
    }else{
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>No hay Información para eliminar</p>');
    }
}

function fnModificarStock(arrayStock){
    console.log('modificar');
    dataObj = { 
        option: 'modificarProducto',
        arrayStock: arrayStock
    };
    $.ajax({
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se actualizó el registro con la clave: '+data.contenido+' </p>');
        }
    })
    .fail(function(result) {
      console.log("ERROR");
      console.log( result );
    });
}

function fnMostrarDatosStock(stockid, partidaid, mbflag){
    $('#StockID').prop('readonly',true);
    if(mbflag == 'D'){
        $('#Familia').prop('readonly',true);
    }
    fnPartidaEspecifica(mbflag);
    fnCabms(partidaid, mbflag);
    fnUnits(mbflag);
    fnObtenerDatos(stockid);
}

function fnPartidaEspecifica(mbflag){
    dataObj = { 
            option: 'mostrarPartida',
            mbflag: mbflag
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosPartidaEsp = data.contenido.datos;

            var partidaID = "";
            var partidaDesc = "";
            var partidaNew = "";

            for (var info in datosPartidaEsp) {
                partidaID = datosPartidaEsp[info].value;
                partidaDesc = datosPartidaEsp[info].texto;
                partidaNew += "<option value="+partidaID+">"+partidaDesc+"</option>";
            }
            $('#selectPartidaEspecifica').empty();
            $('#selectPartidaEspecifica').append("<option value='0'>Sin Selección ...</option>" + partidaNew);
            $('#selectPartidaEspecifica').multiselect('rebuild');
            fnCabms(0, mbflag)
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnCabms(partidaid, mbflag){
    dataObj = { 
            option: 'mostrarCabms',
            partidaid: partidaid,
            mbflag: mbflag
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosCabms = data.contenido.datos;
            var cabmsID = "";
            var cabmsDesc = "";
            var cabmsNew = "";

            for (var info in datosCabms) {
                cabmsID = datosCabms[info].value;
                cabmsDesc = datosCabms[info].texto;
                cabmsNew += "<option value="+cabmsID+">"+cabmsDesc+"</option>";
            }
            $('#selectCabms').empty();
            $('#selectCabms').append("<option value='0'>Sin Selección ...</option>" + cabmsNew);
            $('#selectCabms').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnUnits(mbflag){
    dataObj = { 
            option: 'mostrarUnits',
            mbflag: mbflag
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosUnits = data.contenido.datos;

            var unitsID = "";
            var unitsDesc = "";
            var unitsNew = "";

            for (var info in datosUnits) {
                unitsID = datosUnits[info].value;
                unitsDesc = datosUnits[info].texto;
                unitsNew += "<option value='"+unitsDesc+"'>"+unitsDesc+"</option>";
            }
            $('#units').empty();
            $('#units').append("<option value='0'>Sin Selección...</option>" + unitsNew);
            $('#units').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnObtenerDatos(stockid){
    var datosStock = [];

    dataObj = { 
            option: 'buscarProducto',
            stockid: stockid
    };
    $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType:"json",
          url: url,
          data: dataObj
    })
    .done(function( data ) {
        if(data.result) {
            datosStock = data.contenido.datos;

            var stockid = "";
            var description = "";
            var longdescription = "";
            var units = "";
            var mbflag = "";
            var discontinued = "";
            var eq_stockid = "";
            var partidaid = "";
            var nu_cve_familia = "";

            for (var info in datosStock) {
                stockid = datosStock[info].stockid;
                description = datosStock[info].description;
                longdescription = datosStock[info].longdescription;
                units = datosStock[info].units;
                mbflag = datosStock[info].mbflag;
                discontinued = datosStock[info].discontinued;
                eq_stockid = datosStock[info].eq_stockid;
                partidaid = datosStock[info].partidaid;
                nu_cve_familia = datosStock[info].nu_cve_familia;

            }

            $('#selectTipoProducto > option[value="'+mbflag+'"]').attr('selected', 'selected');
            $('#selectTipoProducto').multiselect('rebuild');
            $('#selectPartidaEspecifica > option[value="'+partidaid+'"]').attr('selected', 'selected');
            $('#selectPartidaEspecifica').multiselect('rebuild');
            $('#selectCabms > option[value="'+eq_stockid+'"]').attr('selected', 'selected');
            $('#selectCabms').multiselect('rebuild');
            $('#units > option[value="'+units+'"]').attr('selected', 'selected');
            $('#units').multiselect('rebuild');

            $('#selectTipoProducto > option[value="'+mbflag+'"]').attr('selected', 'selected');
            $('#selectTipoProducto').multiselect('rebuild');
            $('#StockID').val(stockid);
            $('#Familia').val(nu_cve_familia);
            $('#status > option[value="'+discontinued+'"]').attr('selected', 'selected');
            $('#status').multiselect('rebuild');
            $('#longDescription').text(longdescription);
            $('#description').val(description);
            
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}