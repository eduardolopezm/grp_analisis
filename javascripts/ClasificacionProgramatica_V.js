/**
 * @fileOverview
 *
 * @author Japheth Calzada
 * @version 0.1
 * @Fecha 11 de Julio 2018
 */

//Envio a capa

var url = "modelo/ClasificacionProgramatica_V_Modelo.php";
var id = id;
var Advertencia = false;

/* variables globales fin */
$(document).ready(function(){

    // fnPrograma();
    
    fnGrupo();
    
    if ( idClave !=''){
        fnMostrarDatos(idClave);  
    }
    if(funcionVer == "1"){
        $('#guardarProd').hide();
        $('#eliminar').hide();
        $('#cancelar').hide();
        fnDesabilitarCampos();
    }
    $('#guardarProd').on('click', function(){
        fnSaveClas();
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
    location.replace("./ClasificacionProgramatica.php?");
});

$(document).on('click','#eliminar',function(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
});

function fnDesabilitarCampos(){
    
    $("#clave").attr("disabled",true);
    // $('#programa').multiselect('disable');
    $("#programa").attr("disabled",true);
    $('#grupo').multiselect('disable');
    $('#activo').multiselect('disable');
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
               
            }
            
            $('#clave').val(mbTexto);
          
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnPrograma(){

    dataObj = { 
        option: 'mostrarPrograma'
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
            
            $('#programa').empty();
            $('#programa').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#programa').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
}

function fnGrupo(){

    dataObj = { 
        option: 'mostrarGrupo'
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
            
            $('#grupo').empty();
            $('#grupo').append("<option value='0'>Sin Selección ...</option>" + mbflagNew);
            $('#grupo').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    })
}

function fnValidarDatos(){
    // console.log("entro a validar"+idClave+"->");
    var programa     = $('#programa').val();
    var grupo        = $('#grupo').val();
    var clave        = $("#clave").val();
    var activo       = $("#activo").val();
    var msg          = ''; 

    var datosClasifiacion = [];

    if (clave ==''){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar la Clave.</p>';
    }
   
    if(programa.trim() == '' || programa.trim() == null){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Programa.</p>';
    }

    if(grupo.trim() == '0' || grupo.trim() == null){
        msg += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>Falta proporcionar el Grupo.</p>';
    }
   
    if(msg == ''){
        validar = true;
        datosClasifiacion = {'idClave':idClave,'programa': programa, 'descClave':$("#clave").val(), 'activo':activo, 'grupo': $('#grupo').val()}
    }else{
        
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(3, titulo, msg);
    }
    return datosClasifiacion;
}


function fnSaveWithWarning(){
    Advertencia = false;
    fnSaveClas();
}

function fnSaveClas(){
    $("#btnCerrarModalGeneral").addClass('cerrarModalCancelar');
    var arrayClas = fnValidarDatos();
    // console.log("arrayClas"+arrayClas);
    // console.log(idClave);
    if(Advertencia){
        $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>¿Realmente desea modificar la Información?.</p>','','fnSaveWithWarning()');
        return 0;
    }
    if(idClave == ''){
        // console.log("entro en clave"); 
        if(arrayClas != ''){
            dataObj = { 
                option: 'guardarClas',
                arrayClas: arrayClas
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
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-success" aria-hidden="true"></i> Se agregó el registro '+$("#clave").val()+' del Catálogo Clasificación Programática con éxito</p>');
                }else{
                    $("#btnCerrarModalGeneral").removeClass('cerrarModalCancelar');
                    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                    muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>La clave '+$("#clave").val()+' ya existe.</p>');
                }
            })
            .fail(function(result) {
                console.log( result );
            });
        }
    }else{
        if(arrayClas != ''){
            fnModificarClas(arrayClas);
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

function fnModificarClas(arrayClas){
    // console.log('modificar');
    dataObj = { 
        option: 'modificarClas',
        arrayClas: arrayClas
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
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> Se modifico el registro '+$("#clave").val()+' del Catálogo Clasificación Programática con éxito </p>');
        }
        else{
            if (data.contenido = "presupuestario"){
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i> El registro '+$("#clave").val()+' no se pudo eliminar ya que esta relacionado con un Programa Presupuestario </p>');
            
            }
        }
    })
    .fail(function(result) {
      console.log("ERROR");
      console.log( result );
    });
}

function fnMostrarDatos(idClave){
    fnObtenerDatos(idClave);
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

function fnObtenerDatos(idClave){
    var datosClave = [];

    dataObj = { 
            option: 'obtenerDatos',
            idClave: idClave
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
            datosClave = data.contenido.datos;

            var idClave     = "";
            var id_programa = "";
            var idGrupo     = "";

            for (var info in datosClave) {
                clave     = datosClave[info].clave;
                id_programa = datosClave[info].id_programa;
                idGrupo     = datosClave[info].idGrupo;
            }

            $('#clave').val(clave);
            $("#clave").attr("disabled",true);
            // $('#programa > option[value="'+id_programa+'"]').attr('selected', 'selected');
            // $('#programa').multiselect('rebuild');
            $('#programa').val(id_programa);
            // $('#grupo > option[value="'+idGrupo+'"]').attr('selected', 'selected');
            $('#grupo').val(idGrupo);
            $('#grupo').multiselect('rebuild');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function mayus(e) {

    e.value = e.value.toUpperCase();
}