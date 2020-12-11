var modelo = "modelo/cierreContableAnualModelo.php";
var tituloGeneralSucces = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
var tituloGeneralDanger = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';

$(document).ready(function (){
    fnObtenerAnios();

    $('#btnGenerarPiliza').click(function(){
        if(fnValidacionCamposObligatorios() == false){
            return false;
        }

        muestraModalGeneralConfirmacion(3,tituloGeneralSucces,'Se generará la póliza de cierre anual, ¿Desea continuar?', '','fnCierreContableAnual()','');
    });
});


function fnObtenerAnios(){

    $('.selectFromPeriod').empty();
    $('.selectFromPeriod').multiselect('rebuild');
    
    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: modelo,
        data:{option:'obtenerAniosCierre'}
    }).done(function(res){
        if(!res.result){return;}
        var options='';
        options = "<option value='-1'>Seleccionar...</option>";
        $.each(res.contenido.datos,function(index, el) {
            options += '<option value="'+ el.val +'">'+ el.text +'</option>';
        });

        $('.selectFromPeriod').empty();
        $('.selectFromPeriod').append(options);
        $('.selectFromPeriod').multiselect('rebuild');
    }).fail(function(res){
        throw new Error('No se logro cargar la información de años de cierre');
    });

}

function fnValidacionCamposObligatorios(){
    var errMensaje = "";
    if($('#selectUnidadNegocio').val() == "" || $('#selectUnidadNegocio').val() == "-1" ){
        errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UR es obligatorio.</p>';
    }

    if($('#selectUnidadEjecutora').val() == "" || $('#selectUnidadEjecutora').val() == "-1" ){
        errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo UE es obligatorio.</p>';
    }

    if($('#selectFromPeriod').val() == "" || $('#selectFromPeriod').val() == "-1" ){
        errMensaje += '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> El campo Año a Cerrar es obligatorio.</p>';
    }

    if(errMensaje !=""){
        muestraModalGeneral(3, tituloGeneralDanger, errMensaje);
        return false;
    }else{
        return true;
    }
}


function fnCierreContableAnual(){

    
    
    muestraCargandoGeneral();

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType:"json",
        url: modelo,
        data:{option:'cierreContable', ur:$('#selectUnidadNegocio').val(),ue:$('#selectUnidadEjecutora').val(), anio:$('#selectFromPeriod').val(), folio_ue: $('#txtFolio').val() }
    }).done(function(res){
        if(!res.result){return;}
        
        muestraModalGeneral(3, tituloGeneralSucces, res.Mensaje);

        ocultaCargandoGeneral();
    }).fail(function(res){
        throw new Error('No se logro cargar la información de años de cierre');
        ocultaCargandoGeneral();
    });

}