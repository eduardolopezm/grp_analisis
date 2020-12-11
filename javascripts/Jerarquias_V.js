/**
 * @fileOverview
 *
 * @author Japheth Calzada
 * @version 0.1
 * @Fecha 5 de Julio 2018
 */

//Envio a capa

var url = "modelo/Jerarquias_V_Modelo.php";
var id = id;
var Advertencia = false;

/* variables globales fin */
$(document).ready(function(){
    fnFormatoSelectGeneral("#jerarquia");
    
    fnJerarquia();
    fnTipoGasto(); 
    fnTipoSol();
    
    if ( idJerarquia !=''){
        fnMostrarDatos(idJerarquia);  
    }
    if(funcionVer == "1"){
        $('#guardarProd').hide();
        $('#eliminar').hide();
        $('#cancelar').hide();
        fnDesabilitarCampos();
    }
    $('#guardarProd').on('click', function(){
        fnSaveJerarquia();
    });
    $("#tipoSol").on('change',function(){
        $('#tipoGasto').multiselect('enable');
        if ($("#tipoSol").val () == 2){
            $('#tipoGasto').multiselect('select', '0');
            $('#tipoGasto').multiselect('rebuild');
            $('#tipoGasto').multiselect('disable');
        }
        if ($("#tipoSol").val () == 1&&$('#tipoGasto > option').length<3){
            $('#tipoGasto').multiselect('select', '1');
            $('#tipoGasto').multiselect('rebuild');
        }
    })

});

$(document).on('click','.cerrarModalCancelar',function(){
    location.replace("./SelectProduct.php");
});



$(document).on('click','#cancelar',function(){
    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    location.replace("./ABC_Jerarquias.php?");
});

$(document).on('click','#eliminar',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
});

function fnDesabilitarCampos(){
    
    $('#jerarquia').multiselect('disable');
    $('#tipoSol').multiselect('disable');
    $('#tipoGasto').multiselect('disable');
    $("#monto").prop("disabled", true);

}


function fnJerarquia(){
    console.log("no llegue"); 
    dataObj = { 
        option: 'mostrarJerarquia'
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
            console.log(dataMbflag); 
            var    mbflagNew   =  ''; 
            for (var info in dataMbflag) {
                mbValue     = dataMbflag[info].value;
                mbTexto     = dataMbflag[info].texto;
                mbflagNew   += "<option value="+mbValue+">"+mbTexto+"</option>";
            }
            
            $('#jerarquia').empty();
            $('#jerarquia').append("<option value='-1'>Sin Selección ...</option>" + mbflagNew);
            $('#jerarquia').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}
function fnTipoGasto(){
    dataObj = { 
        option: 'mostrarTipoGasto'
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
            console.log(dataMbflag); 
            var    mbflagNew   =  ''; 
            for (var info in dataMbflag) {
                mbValue     = dataMbflag[info].value;
                mbTexto     = dataMbflag[info].texto;
                mbflagNew   += "<option value="+mbValue+">"+mbTexto+"</option>";
            }
            
            $('#tipoGasto').empty();
            $('#tipoGasto').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#tipoGasto').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
}
function fnTipoSol(){
    dataObj = { 
        option: 'mostrarTipoSol'
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
            console.log(dataMbflag); 
            var    mbflagNew   =  ''; 
            for (var info in dataMbflag) {
                mbValue     = dataMbflag[info].value;
                mbTexto     = dataMbflag[info].texto;
                mbflagNew   += "<option value="+mbValue+">"+mbTexto+"</option>";
            }
            
            $('#tipoSol').empty();
            $('#tipoSol').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#tipoSol').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
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
    var jerarquia   = $("#jerarquia option:selected").text();
    var tipoSol     = $('#tipoSol').val();
    var tipoGasto   = $('#tipoGasto').val();
    var monto       =  $('#monto').val();
    var msg         = ''; 
    var datosJerarquia = [];
    console.log(jerarquia); 
    if(jerarquia == 'Sin Selección ...'){ 
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Jerarquía</p>';
    }
    if(tipoSol == '' || tipoSol  == '0'){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el tipo de Comisión.</p>';
    }
    else if (tipoSol == 1){
        if(tipoGasto == '' || tipoGasto  == '0'){
            msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la zona económica.</p>';
        }
    }
    if(monto == '' || monto  == '0'){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el monto.</p>';
    }
    if(msg == ''){
        validar = true;
        datosJerarquia = {'id_nu_monto_jerarquia':idJerarquia,'jerarquia': $("#jerarquia").val(), 'tipoSol': tipoSol, 'tipoGasto':tipoGasto, 'monto': monto}
    }else{
        
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datosJerarquia;
}


function fnSaveWithWarning(){
    Advertencia = false;
    fnSaveJerarquia();
}

function fnSaveJerarquia(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
    var arrayJerarquia = fnValidarDatos();
    console.log("arrayJerarquia"+arrayJerarquia);
    console.log(idJerarquia);
    if(Advertencia){
        $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>¿Realmente desea modificar la Información?.</p>','','fnSaveWithWarning()');
        return 0;
    }
    if(idJerarquia == ''){

        if(arrayJerarquia != ''){
            dataObj = { 
                option: 'guardarJerarquia',
                arrayJerarquia: arrayJerarquia
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
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se guardó la jerarquía '+$("#jerarquia option:selected").text()+'</p>');
                }else{
                    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>La jerarquía '+$("#jerarquia option:selected").text()+' ya se encuentra registrada.</p>');
                }
            })
            .fail(function(result) {
                console.log( result );
            });
        }
    }else{
    var arrayJerarquia = fnValidarDatos();
        if(arrayJerarquia != ''){
            fnModificarJerarquia(arrayJerarquia);
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

function fnModificarJerarquia(arrayJerarquia){
    console.log('modificar');
    dataObj = { 
        option: 'modificarJerarquia',
        arrayJerarquia: arrayJerarquia
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
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se actualizó el registro de la jerarquía: '+$("#jerarquia option:selected").text()+' </p>');
        }
    })
    .fail(function(result) {
      console.log("ERROR");
      console.log( result );
    });
}


function fnMostrarDatos(idJerarquia){
   
    fnObtenerDatos(idJerarquia);
   
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

function fnObtenerDatos(idJerarquia){
    var datosJerarquia = [];

    dataObj = { 
            option: 'obtenerDatos',
            idJerarquia: idJerarquia
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
            datosJerarquia = data.contenido.datos;

            var id_nu_monto_jerarquia = "";
            var id_nu_jerarquia = "";
            var amt_importe = "";
            var ind_tipo = "";
            var id_zona_economica = "";

            for (var info in datosJerarquia) {
                id_nu_monto_jerarquia = datosJerarquia[info].id_nu_monto_jerarquia;
                id_nu_jerarquia = datosJerarquia[info].id_nu_jerarquia;
                amt_importe = datosJerarquia[info].amt_importe;
                ind_tipo = datosJerarquia[info].ind_tipo;
                id_zona_economica = datosJerarquia[info].id_zona_economica;
            }

            $('#jerarquia > option[value="'+id_nu_jerarquia+'"]').attr('selected', 'selected');
            $('#jerarquia').multiselect('rebuild');
            $('#jerarquia').multiselect('disable');
            $('#tipoSol > option[value="'+ind_tipo+'"]').attr('selected', 'selected');
            $('#tipoSol').multiselect('rebuild');
            $('#tipoSol').multiselect('disable');
            $('#tipoGasto > option[value="'+id_zona_economica+'"]').attr('selected', 'selected');
            $('#tipoGasto').multiselect('rebuild');
            $('#tipoGasto').multiselect('disable');
            $('#monto').val(amt_importe);
            
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}