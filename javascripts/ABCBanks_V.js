/**
 * @fileOverview
 *
 * @author Japheth Calzada
 * @version 0.1
 * @Fecha 5 de Julio 2018
 */

//Envio a capa

var url = "modelo/ABCBanks_V_Modelo.php";
var id = id;
var Advertencia = false;

/* variables globales fin */
$(document).ready(function(){

    if(funcionVer == "1"){
        fnBanco();
        finDescripcion(); 
        fnClave()

        $('#guardarProd').hide();
        $('#eliminar').hide();
        $('#cancelar').hide();
    }
    if ( idBanco !=''){
        fnMostrarDatos(idBanco); 
        if( modificar != '') {
            fnEstatus(); 

        }
    }

    $('#guardarProd').on('click', function(){
        fnSaveBank();
    });
    $("#tipoSol").on('change',function(){
        $('#tipoGasto').multiselect('enable');
        if ($("#tipoSol").val () == 2){
            $('#tipoGasto').multiselect('disable');
        }
    })

});

$(document).on('click','.cerrarModalCancelar',function(){
    location.replace("./SelectProduct.php");
});



$(document).on('click','#cancelar',function(){
    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
    location.replace("./ABCBanks.php?");
});

$(document).on('click','#eliminar',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
});


function fnBanco(){
    dataObj = { 
        option: 'mostrarBanco'
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
            var    mbflagNew   =  ''; 
            for (var info in dataMbflag) {
                mbValue     = dataMbflag[info].value;
                mbTexto     = dataMbflag[info].texto;
                mbflagNew   += "<option value="+mbValue+">"+mbTexto+"</option>";
            }
            
            $('#banco').empty();
            $('#banco').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#banco').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}
function finDescripcion(){

    dataObj = { 
        option: 'mostrarDescripcion'
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
            
            $('#descripcion').empty();
            $('#descripcion').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#descripcion').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
}
function fnClave(){
    dataObj = { 
        option: 'mostrarClave'
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
            
            $('#clave').empty();
            $('#clave').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#clave').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
}

function fnEstatus(){
    dataObj = { 
        option: 'mostrarEstatus'
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
            
            $('#estatus').empty();
            $('#estatus').append("<option value='-1'>Sin Selección ...</option>" + mbflagNew);
            $('#estatus').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
}
function fnValidarDatos(){
    var banco       = $("#txtBanco").val();
    var descripcion = $('#txtDescripcion').val();
    var clave       = $('#txtClave').val();
    var estatus     = $('#estatus').val();
    var txtEstatus  = $("#estatus option:selected").text();
    var msg         = ''; 
    var datosEstatus = [];
    if(banco == ''){ 
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el nombre del Banco</p>';
    }
    if(descripcion == ''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Descripción.</p>';
    }
    if (modificar == '' && funcionVer == '' ) {
        if(clave == ''){
            msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Clave.</p>';
        }
    }
    if ( modificar == 1){
        if ( txtEstatus == 'Sin Selección ...'){
            msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Estatus.</p>';
        }
    }
    if(msg == ''){
        validar = true;
        datosEstatus = {'banco':banco,'descripcion':descripcion, 'clave': clave, 'estatus':estatus}
    }else{
        
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datosEstatus;
}


function fnSaveBank(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');

    var arrayBanco = fnValidarDatos();

    if(Advertencia){
        $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>¿Realmente desea modificar la Información?.</p>','','fnSaveWithWarning()');
        return 0;
    }

    if(idBanco == ''){

        if(arrayBanco != ''){
            dataObj = { 
                option: 'guardarBanco',
                arrayBanco: arrayBanco
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
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se guardó el banco '+$("#txtDescripcion").val()+'</p>');
                }else{

                    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>'+data.ErrMsg+'</p>');
                }
            })
            .fail(function(result) {
                console.log( result );
            });
        }
    }else{
    var arrayBanco = fnValidarDatos();
        if(arrayBanco != ''){
            fnModificarBancos(arrayBanco);
        }
    }
}

function fnModificarBancos(arrayBanco){
    console.log('modificar');
    dataObj = { 
        option: 'modificarBancos',
        arrayBanco: arrayBanco,
        idBanco: idBanco
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
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se actualizó el registro del banco '+ $('#txtDescripcion').val()+' </p>');
        }else if(data.ErrMsg != ''){
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> No fue posible actualizar el estado del banco del banco '+ $('#txtDescripcion').val()+' </p>');
        }
    })
    .fail(function(result) {
      console.log("ERROR");
      console.log( result );
    });
}


function fnMostrarDatos(idBanco){
    muestraCargandoGeneral();
    fnObtenerDatos(idBanco);
   
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

function fnObtenerDatos(idBanco){
    var datosBanco= [];

    dataObj = { 
            option: 'obtenerDatos',
            idBanco: idBanco
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
            datosBanco = data.contenido.datos;

            var bank_id                 = "";
            var bank_name               = "";
            var bank_shortdescription   = "";
            var bank_clave              = "";

            for (var info in datosBanco) {
                bank_id                 = datosBanco[info].bank_id;
                bank_name               = datosBanco[info].bank_name;
                bank_shortdescription   = datosBanco[info].bank_shortdescription;
                bank_clave              = datosBanco[info].bank_clave;
            }
            if (  modificar != ''){
                $("#txtBanco").val(bank_name); 
                $("#txtDescripcion").val (bank_shortdescription); 
                
                setTimeout(function(){$("#estatus").val(1); $('#estatus').multiselect('rebuild');}, 500); 
                

            }else{
                $('#banco > option[value="'+bank_id+'"]').attr('selected', 'selected');
                $('#banco').multiselect('rebuild');
                $('#banco').multiselect('disable');
                $('#descripcion > option[value="'+bank_id+'"]').attr('selected', 'selected');
                $('#descripcion').multiselect('rebuild');
                $('#descripcion').multiselect('disable');
                $('#clave > option[value="'+bank_clave+'"]').attr('selected', 'selected');
                $('#clave').multiselect('rebuild');
                $('#clave').multiselect('disable');
            }  
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}