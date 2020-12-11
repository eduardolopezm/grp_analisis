//soliticitud arturo lopez peña
/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña
 * @version 0.1
 */
var cuentaFila = 0;
var nMostrar = 1;
var datosClave = [];
var datosDescripcion = [];
var datosCams = [];
var datosPartida = [];
var articulosSolicitud = '';
var disponibleCantidad = [];
var detalleSolicitud;
var filasGuardadas = 0;
var estatuscerrada = 0;
var eliminados = [];
var actualizados = [];
var renglonultimo = 0;
var datosAguardar = [];
var datosAverificar = [];
var claveGlobal='';
var descripcionGlobal='';
window.renglon=1;
window.titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
function fnDatosSelet() { //carga datos a los select
    var retorno = [];
    var almacen = $('#selectAlmacen').val();
    dataObj = {
        proceso: 'datosSelectSolicitud',
        almacen: almacen
    };
    //alert(almacen);
    muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj,
            cache:false
        })
        .done(function(data) {
            if (data.result) {

                retorno.push(data.contenido.clave);
                retorno.push(data.contenido.descripcion);
                /*retorno.push(data.contenido.cams);
                retorno.push(data.contenido.partida); */

                //alert(data.contenido.partida);
                ocultaCargandoGeneral();
                fnQuitarGeneral();
            } else {
                ocultaCargandoGeneral();
                fnQuitarGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al obtenet  datos para los select");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });
       // ocultaCargandoGeneral();
       fnQuitarGeneral();
    return retorno;

}

function fnNuevoFormato(options,id){
   
   //options=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
    //$.each(content, function(index, val) { options.push({ label:val.texto, title: val.texto, value:val.value }); });
            //window.productos = content;
            $('#'+id).multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            $('#'+id).multiselect('dataprovider', options);
            
            $('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });
      
}
function fnNuevoFormato1(id){
    $('#'+id).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        numberDisplayed: 1,
        includeSelectAllOption: true
    });
   
    
    $('.multiselect-container').css({ 'max-height': "220px" });
    $('.multiselect-container').css({ 'overflow-y': "scroll" });
    //console.log(id);
}
function fnClonar(clonar,agregarA,nfila,select){
    $( "#"+clonar).clone().appendTo( "#"+agregarA );
    
   $("#"+agregarA+" > #"+clonar).show();
   $("#"+agregarA+" > #"+clonar ) .attr('id','selClo'+nfila);
   $("#selClo"+nfila+" > .multiselect-native-select > #"+select).attr('id',select+""+nfila);
  //console.log("#selClo"+nfila+' > .multiselect-native-select > #'+select+').attr("id",'+select+""+nfila);
  /*$("#"+select+""+nfila).multiselect({
    includeSelectAllOption: true
  });*/
  $('#'+select+""+nfila).multiselect({
    enableFiltering: true,
    filterBehavior: 'text',
    enableCaseInsensitiveFiltering: true,
    //buttonWidth: '100%',
    numberDisplayed: 1,
    includeSelectAllOption: true
});
//fnNuevoFormato1(select+""+nfila);
}
function fnAgregarArticulo() {
    $('#btnAgregar').prop("disabled", true);

    
    $('#tablaArticulosSolicitud').append('<tr id="fila' + cuentaFila + '">' +
        '<td class="text-center"><div  class="btnRemoveArticulo" id="quitar' + cuentaFila + '"><span class="btn btn-danger btn-xs glyphicon glyphicon-remove"  title="Eliminar"></span></div></td>' +
        '<td class="nRenglon text-center">' + '<span class="rowVisible"> </span>' + '<input type="hidden" value="' + nMostrar + '" id="rowreg' + cuentaFila + '"></td>' +
        //'<td><select id="selectCamsX' + cuentaFila + '" name="selectCamsX' + cuentaFila + '" class="claveCams" required>'+cams+'</select></td>'+
        //'<td><select id="selectCvePartidaEspecificaX' + cuentaFila + '" name="selectCvePartidaEspecificaX' + cuentaFila + '" class="partidaEspecifica" required>'+partida1+'</select></td>'+
        '<td class="text-center"><select id="selectCveArticuloX' + cuentaFila + '" name="selectCveArticuloX' + cuentaFila + '" class="claveArticulo"  required>' + + '</select></td>' +
       //' <td id="clArt'+cuentaFila+'"> </td>'+
        '<td class="text-center"><select id="selectArticuloX' + cuentaFila + '" name="selectArticuloX' + cuentaFila + '" class="claveDescripcion" required>' + + '</select></td>' +
       // ' <td id="descArt'+cuentaFila+'"> </td>'+
        '<td class="text-center"><div  type="text" id="addedUMArticulo' + cuentaFila + '"></div></td>' +
       // '<td class="text-center"><input  class="cantidadSolicitada text-right"  type="number" id="addedCantidadArticuloX' + cuentaFila + '" placeholder="Cantidad"  required min="0" onkeypress="return isNumberKey(event)"/></td>' +
'<td class="text-center"><input type="text" id="addedCantidadArticuloX' + cuentaFila + '" placeholder="Cantidad"  name="" onchange="" placeholder="" maxlength="100" title="" onkeyup="" onkeypress="return soloNumeros(event)" onpaste="return false" class="cantidadSolicitada text-right form-control" style="width: 80px;"></td>'+
        '</tr>'
    );
    //fnFormatoSelectGeneral('#selectCveArticuloX' + cuentaFila);
    fnNuevoFormato1("selectCveArticuloX" + cuentaFila + "") ;
      $('#'+"selectCveArticuloX" + cuentaFila ).multiselect('dataprovider', claveGlobal);
    
   // fnNuevoFormato(claveGlobal,"clArt"+cuentaFila+"") ;
    
   //fnClonar("selectCveArticuloX","clArt"+ cuentaFila,cuentaFila,"selectCveArticuloX" );

    //fnFormatoSelectGeneral('#selectArticuloX' + cuentaFila);
   fnNuevoFormato1("selectArticuloX" + cuentaFila + "");
   $('#'+"selectArticuloX" + cuentaFila  ).multiselect('dataprovider', descripcionGlobal);
   //fnClonar("selectArticuloX","descArt"+ cuentaFila,cuentaFila,"selectArticuloX" );
    
    //fnFormatoSelectGeneral('#selectCveArticuloX' + cuentaFila);
    //fnFormatoSelectGeneral('#selectArticuloX' + cuentaFila);
    
    //fnFormatoSelectGeneral('#selectCamsX'+cuentaFila);
    //fnFormatoSelectGeneral('#selectCvePartidaEspecificaX'+cuentaFila);
    
    cuentaFila++;
    nMostrar++;
   
    //setTimeout(function(){ fnNuevoFormato1("selectCveArticuloX"+cuentaFila); }, 1);
    //setTimeout(function(){ fnNuevoFormato1("selectArticuloX"+cuentaFila); }, 1);
    
    $('#btnAgregar').prop("disabled", false);
    fnQuitarGeneral();
    fnActualizarRenglon();
   
 ocultaCargandoGeneral();
}


var url = "modelo/Captura_Requisicion_modelo.php";
var periodoR = ''; //periodoReq;
var cont = 0;
var containerArticulo = $(document.createElement('div')).css({
    padding: 'opx',
    margin: '0px',
    width: '100%'
});
var datos = [];

$(document).ready(function() {
    if (visible == 1) {
        fnObtenerBotones('divBotones');
    }
    
    datos = fnDatosSelet();
    
    datosClave = datos[0];
    datosDescripcion = datos[1];
    /*datosCams = datos[2];
    datosPartida = datos[3]; */

       // claveGlobal = datosClave; //fnCrearDatosSelect(datosClave);
        optionsCl=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
        
        $.each(datosClave, function(index, val) { optionsCl.push({ label:val.texto, title: val.texto, value:val.value }); });
        claveGlobal=optionsCl;

        optionsDesc=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
        $.each(datosDescripcion, function(index, val) { optionsDesc.push({ label:val.texto, title: val.texto, value:val.value }); });
    
    
    
        descripcionGlobal = optionsDesc;//fnCrearDatosSelect(datosDescripcion);

     
  
          fnNuevoFormato(claveGlobal,"selectCveArticuloX") ;
          //fnFormatoSelectGeneral('#selectArticuloX' + cuentaFila);
          fnNuevoFormato(descripcionGlobal,"selectArticuloX");

    /*$('#enviarSolcitudAlmacen').click(function(){
        
        fnGuardarDatosSolicitud();

    });*/
 
    $('#home1').click(function() {
        window.open("almacen.php", "_self");
    });
    $('#home2').click(function() {
        window.open("almacen.php", "_self");
    });
    $('#guardar_solicitud').click(function() {
      if(  $('#selectUnidadEjecutora option:selected').val()!=-1){
        fnGuardarDatosSolicitud('24'); // 24
      }else{
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo,'<h4>Seleccione UE.</h4>');
      }
    });
    // checa el numero total de filas en caso de detalle para partir de ahi
    if ($(".folius").length) {
        filasGuardadas = $("#tablaArticulosSolicitud tbody tr").length;

    }
    $('#cancelar_solicitud').click(function (){
 
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Desea salir sin guardar cambios?', '', 'fnCancelar()');

    });

     $('#btnAgregar').click(function(){
        muestraCargandoGeneral();
         setTimeout(function(){ fnAgregarArticulo(); }, 500);

    });

estatusNumero=$('#numeroestatus').val();
if (visible == 1) {


//fnFormatoSelectGeneral(".bancoselect");
// $("#2424").show();
// $("#2323").hide();
/*
fnFormatoSelectGeneral(".selectUnidadEjecutora1");*/
$(".multiselect-native-select").prop('disabled',true);
$(".multiselect").prop('disabled',true);
}
if ((ed1 == 1) && (estatusNumero > 24)) { //40



//$("#2424").show();
//$("#2323").hide();
/*
fnFormatoSelectGeneral(".selectUnidadEjecutora1");*/
$(".multiselect-native-select").prop('disabled',true);
$(".multiselect").prop('disabled',true);
}
if ((ed2 == 1 )&& ((estatusNumero > 24) || (estatusNumero > 41))) { //40


//$("#2424").show();
//$("#2323").hide();
/*
fnFormatoSelectGeneral(".selectUnidadEjecutora1");*/
$(".multiselect-native-select").prop('disabled',true);
$(".multiselect").prop('disabled',true);
}
                            //40
                            //((estatusNumero > 24) || (estatusNumero > 41) || (estatusNumero == 43))
if ((ed3 == 1) && ( (estatusNumero==30) ||estatusNumero==65 ) ) {


// $("#2424").show();
// $("#2323").hide();
/*
fnFormatoSelectGeneral(".selectUnidadEjecutora1");*/
$(".multiselect-native-select").prop('disabled',true);
$(".multiselect").prop('disabled',true);

}





}); // fin ready
function fnCancelar(){
     window.open("almacen.php","_self");
}
function fnActualizarRenglon(){
    if(estatusNumero!=65 ){
     renglon=1;

    $("#tablaArticulosSolicitud tbody tr").each(function() {
     fila=$(this).find(".rowVisible").empty();
     fila.html(""+renglon);
     //fila=$(this).find(".renglon").val(""+cuentaFilas);
     renglon++;
    });
 }
}
$(document).on('click', '.btnRemoveArticulo', function() {
    var a = 1;
    var datosN = '';
    quitar = $(this).attr('id');
    quitar = quitar.replace("quitar", "");
    if ($('#rowreg' + quitar).length) {
        a = $('#rowreg' + quitar).val();
        //eliminados.push(a);
        filasGuardadas -= 1;
        datosN += "'" + a + "',"; //renglon
        datosN += "'0',"; //activo
        datosN += "'" + $('#addedCantidadArticuloX' + (quitar)).val() + "',";
        datosN += "'" + $('#selectCveArticuloX' + (quitar) + ' option:selected').val() + "',";
        datosN += "'" + $('#selectArticuloX' + (quitar) + ' option:selected').val() + "',";
        datosN += "'" + $('#addedUMArticulo' + quitar).html() + "',"; //piezas
        datosN = datosN.slice(0, -1);
        //actualizar.push(datos);
        datosAguardar.push(datosN);
    }
    $('#fila' + quitar).remove();
    /*$(this).parent().parent().remove();
    $("#idCvePresupuestal" + orden).remove();
    fnEliminarArticulo(noReq,orden); */
    //nMostrar -= 1;
    /* actualizar renglon mostratdono  me funciona bien
    $("#tablaArticulosSolicitud tbody tr").each(function () {
        $(this).find('.nRenglon').empty();
        $(this).find('.nRenglon').html(a+'<input type="hidden" value="'+nMostrar+'" id="rowreg'+cuentaFila+'">');
        a++;
    });
    nMostrar=a;*/
fnActualizarRenglon();
});

//para cambiar datos de los select que dependen de la clave
$(document).on('change', '.claveArticulo', function() {
    //fnQueNoSeaRepetido();
    muestraCargandoGeneral();
    
    var valor = this.value;
    id = $(this).attr('id');
    id = id.replace("selectCveArticuloX", "");
    //alert(id);
    //if(cambio==1){
    var valor = $("#selectCveArticuloX" + id + " option:selected").val();
    flag=fnVerificarNoexista(valor,"selectCveArticuloX");
    if(flag){
        muestraModalGeneral(4,titulo,"Ya existe el artículo "+ valor);
    }else{
    setTimeout(function(){
    if(valor!=0){
    dataObj = {
        proceso: 'cambioClave',
        clave: valor
    };
   
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                // alert('dentro clave');
                // alert( data.contenido.datos[0].descripcion +''+ data.contenido.datos[0].cams);

                //vover a renderizar
                muestraCargandoGeneral();
                fnCrearDatosSelect(datosDescripcion, '#selectArticuloX' + id, data.contenido.datos[0].descripcion);
                $('#addedUMArticulo' + id).empty();
                $('#addedUMArticulo' + id).append(data.contenido.datos[0].unidad_medida);
             


                ocultaCargandoGeneral();
                fnQuitarGeneral();
            } else {
                ocultaCargandoGeneral();
                fnQuitarGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error  al detectar cambio del articulo 381");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });
        ocultaCargandoGeneral();
        fnQuitarGeneral();
         }
    }, 1000);
    ocultaCargandoGeneral();
 }
});



$(document).on('change', '.claveDescripcion', function() {
    muestraCargandoGeneral();
    
    var valor = this.value;
    id = $(this).attr('id');
    id = id.replace("selectArticuloX", "");
    var valor = $("#selectArticuloX" + id + " option:selected").val();
    //alert(id);
    flag=fnVerificarNoexista(valor,"selectArticuloX" );
    if(flag){
        muestraModalGeneral(4,titulo,"Ya existe el artículo "+ valor);
    }else{
    setTimeout(function(){
    if(valor!=0){
    dataObj = {
        proceso: 'cambioDescripcion',
        clave: valor
    };
 
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                muestraCargandoGeneral();
                fnCrearDatosSelect(datosClave, '#selectCveArticuloX' + id, data.contenido.datos[0].clave);
                $('#addedUMArticulo' + id).empty();
                $('#addedUMArticulo' + id).append(data.contenido.datos[0].unidad_medida);
             

                ocultaCargandoGeneral();
                fnQuitarGeneral();
            } else {
                ocultaCargandoGeneral();
                fnQuitarGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al cambiar  la descripcion del articulo");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });
}

    }, 1000);
    fnQuitarGeneral();
}
});

///cams



function fnGuardarDatosSolicitud(estatus) {
flag=fnVerificarNoCeros();
if(flag){
    muestraModalGeneral(4,window.titulo,"Hay cantidades en cero no puede guardar");
   }else{

    if (!fnValidarAntesDeEnviarSolicitud()) {
        var filas = $('#tablaArticulosSolicitud tr').length;
        var renglon = 1;
        var datos = '';
        var datosSoli = [];
        var datosSoliN = [];
        filas -= 1;
        /*var id;
        var datosSoli=[];
        if(filas>=0){ */
        if (filas > 0) {


            // if ($(".folius").length) {


            //     $("#tablaArticulosSolicitud tbody tr").each(function() {
                 
            //         var datosV='';
            //         id = $(this).attr('id');
            //         id = id.replace("fila", "");

                    
            //         datosV+= "'" + $('#rowreg' + id).val() + "',";
            //         datosV += "'" + $('#addedCantidadArticuloX' + id).val() + "',";
            //         datosV += "'" + $('#selectCveArticuloX' + id + ' option:selected').val() + "',";// al ultimo para poder quitar
            //         datosV += "'" + $('#selectArticuloX' + id + ' option:selected').val() + "',";
            //         datosV = datosV.slice(0, -1);
            //         datosAverificar.push(datosV);;
            //        // console.log(datosV);
            //     });
              
            //     //mensaje=fnChecarDisponibleAntesDeGuardar(datosAverificar);
            //     datosAverificar=[];
            //     $("#tablaArticulosSolicitud tbody tr").each(function() {
            //         var datosN = '';
                   
            //         id = $(this).attr('id');
            //         id = id.replace("fila", "");

            //         datosN += "'" + $('#rowreg' + id).val() + "',";
            //         datosN += "'1',";
            //         datosN += "'" + $('#addedCantidadArticuloX' + id).val() + "',";
            //         datosN += "'" + $('#selectCveArticuloX' + id + ' option:selected').val() + "',";
            //         datosN += "'" + $('#selectArticuloX' + id + ' option:selected').val() + "',"; // al ultimo para poder quitar
            //         datosN += "'" + $('#addedUMArticulo' + id).html() + "',"; //piezas
            //         datosN = datosN.slice(0, -1);
            //         datosAguardar.push(datosN);
            //     });

            //     fnGuardarTodo($(".folius").html(), datosAguardar,datosAverificar,mensaje);
            //     datosAverificar=[];

            //     /* }else{

            //        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            //        muestraModalGeneral(4, titulo,'<h4>Se actualizo.</h4>');

            //       }*/


            // } else { //guardar normarl

                 $("#tablaArticulosSolicitud tbody tr").each(function() {
                 
                    var datosV='';
                    id = $(this).attr('id');
                    id = id.replace("fila", "");

                    
                    datosV+= "'" + $('#rowreg' + id).val() + "',";
                    datosV += "'" + $('#addedCantidadArticuloX' + id).val() + "',";
                    datosV += "'" + $('#selectCveArticuloX' + id + ' option:selected').val() + "',";// al ultimo para poder quitar
                    datosV += "'" + $('#selectArticuloX' + id + ' option:selected').val() + "',";
                    datosV = datosV.slice(0, -1);
                    datosAverificar.push(datosV);;

                });

                  mensaje='';
                //mensaje=fnChecarDisponibleAntesDeGuardar(datosAverificar);
                datosAverificar=[];
               // alert(datosAverificar);
                //alert(mensaje);
                $ad=1;
                clvA=[];
                qtA=[];
                $filas=[];
                var clv='',qt='';
                $("#tablaArticulosSolicitud tbody tr").each(function() {
                    //alert( $(this).attr('id'));

                    //for(i=0;i<filas;i++){
                    //$('#selectorId option:selected').val()
                    //                  
                    datos = '';
                    id = $(this).attr('id');
                    id = id.replace("fila", "");

                    datos += "'" + $('#addedCantidadArticuloX' + id).val() + "',";
                    qt += "'" + $('#addedCantidadArticuloX' + id).val() + "',";
                    
                    datos += "'" + $('#selectCveArticuloX' + id + ' option:selected').val() + "',";
                    clv += "'" + $('#selectCveArticuloX' + id + ' option:selected').val() + "',";
                    //datos+="'" +  $('#selectCamsX'+id+' option:selected').val()+"',";
                    //datos+="'" +  $('#selectCvePartidaEspecificaX'+id+' option:selected').val()+"',";

                    datos += "'" + $('#selectArticuloX' + id + ' option:selected').val() + "',"; // al ultimo para poder quitar
                    datos += "'" + $('#addedUMArticulo' + id).html() + "',"; //piezas
                    datos += "'" + renglon + "',"; //renglon
                    //datos+="'" + estatus+"',";
                    datos = datos.slice(0, -1);
                   
                    qtA.push($('#addedCantidadArticuloX' + id).val() );
                    clvA.push($('#selectCveArticuloX' + id + ' option:selected').val());
                    
                    datosSoli.push(datos);

                    $filas.push($ad);
                    //datos+='\n';

                    // id=id.toString();
                    renglon++;
                    $ad++;

                });
               
                clv = clv.slice(0, -1);
                qt = qt.slice(0, -1);
                fnEnviarSolicitud(datosSoli,estatus,mensaje,clv,qt,clvA,qtA,$filas);
                datos = '';
                clv='';
                qt='';
                datosSoli=[];
                datosAverificar=[];
                clvA=[];
                qtA=[];
                $ad=1;

            //} fin  folius

        } else {
            //  muestraMensaje('Es necesario agregar Artículos a su solicitud.', 3, 'msjValidacion', 5000);
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, '<h4>Se actualizo la solicitud con número .</h4>');

        }
    } //fin si es falso que faltan datos
}
}

function fnEnviarSolicitud(datosSolicitud, estatus,mensaje,clv,qt,clvA,qtA,filas) {
   // console.log(datosSolicitud);
flag=fnVerificarNoCeros();
if(flag){
    muestraModalGeneral(4,titulo,"Hay cantidades en cero no puede guardar");
   }else{
// if(mensaje==''){
    unidadNegocio = $('#selectUnidadNegocio option:selected').val();
    observaciones = $('#txtAreaObs').val();
    var folio=0;
    if ($(".folius").length){
        folio=$(".folius").html();
    }
    dataObj = {
        proceso: 'guardarSolicitudAlmacen',
        datosSolicitud: datosSolicitud,
        estatus: estatus,
        tag: unidadNegocio,
        observaciones: observaciones,
        nombreEstatus: 'Guardada',
        ue:$('#selectUnidadEjecutora option:selected').val(),
        folio:folio,
        clv:clv,
        qt:qt,
        clvA:clvA,
        qtA:qtA,
        almacen:$('#selectAlmacen').val(),
        renglones:filas
    };
    muestraCargandoGeneral();
    $.ajax({
            async:false,
            cache:false,
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                // alert('dentro clave');
                //muestraMensaje(data.contenido,1, 'OperacionMensaje', 250);
                ocultaCargandoGeneral();
                filasGuardadas = $("#tablaArticulosSolicitud tbody tr").length;
                //fnEliminarFilas();
                //fnActulizarEstadoAlGuardar(data.contenido.folio);// parece que ya nose  ocupa
                $("#numeroestatus").val(24); //40 
                $("#numero_folio").empty();
                $("#numero_folio").append('Folio:<span class="folius">' + data.contenido.folio + '</span>');

                //fnCargaDetalleDeNuevo(data.contenido.folio, ($('#numeroestatus').val()));
                
                muestraModalGeneral(4, titulo, '<div >' + data.contenido.info + '</div>');

                //nMostrar = 1;
                //cuentaFila = 0;

            } else {
                ocultaCargandoGeneral();
                fnQuitarGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al enviar solicitud");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });
    // }else{
    //     var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    //     muestraModalGeneral(4, titulo, '<div >' + mensaje + '</div>');

    // }
    fnQuitarGeneral();
}
}

function fnGuardarTodo(folio, guardar = [],datosAverificar=[],mensaje) {
    console.log(guardar);
flag=fnVerificarNoCeros();
if(flag){
    muestraModalGeneral(4,titulo,"Hay cantidades en cero no puede guardar.");
   }else{



  muestraCargandoGeneral();

    observaciones=$("#txtAreaObs").val();
    dataObj = {
        proceso: 'guardartodo',
        guardar: guardar,
        folio: folio,
        observaciones:observaciones
    };

    $.ajax({
        async:false,
        cache:false,
        method: "POST",
        dataType: "json",
        url: "modelo/almacen_modelo.php",
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {
            ocultaCargandoGeneral();
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, '<div >Se actualizó solicitud con folio ' + folio + '</div>'+"<br></div>" +mensaje+'</div>');
            //muestraModalGeneral(4, titulo,'<div class="text-center">'+data.contenido+'</div>');
            datosAguardar = [];
            datosAverificar=[];
            //fnCargaDetalleDeNuevo(folio, ($('#numeroestatus').val()));
        } else {
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("Error al  guardar todo");
         //console.log(ErrMsg);
        ocultaCargandoGeneral();
        fnQuitarGeneral();
    });

    fnQuitarGeneral();
       } 
}

function fnActualizar(folio, guardar = []) {
    //actualizados.push(n);
    muestraCargandoGeneral();
    var eliminar = [];
    var actualizar = [];
    existe = 0;
    if (eliminados.length > 0) {
        if (actualizados.length > 0) {
            for (a = 0; a < eliminados.length; a++) {
                existe = fnChecarExistencia(actualizados, eliminados[a]);
                if (existe > 0) {
                    actualizados.remove(eliminados[a]);
                    //actualizados.splice(a,1);
                }

            }
        } // fin actualizados

        eliminar = eliminados;
    }
    if (actualizados.length > 0) {
        for (var x in actualizados) {
            //alert(actualizados[x]);
            //nu_cantidad,ln_clave_articulo,txt_descripcion,ln_unidad_medida,ln_renglon
            datosN += "'" + actualizados[x] + "',"; //renglon
            datosN += "'" + $('#addedCantidadArticuloX' + (actualizados[x])).val() + "',";
            datosN += "'" + $('#selectCveArticuloX' + (actualizados[x]) + ' option:selected').val() + "',";
            datosN += "'" + $('#selectArticuloX' + (actualizados[x]) + ' option:selected').val() + "',";
            datosN += "'" + $('#addedUMArticulo' + actualizados[x]).html() + "',"; //piezas
            datosN = datosN.slice(0, -1);
            actualizar.push(datos);

        }
    }

    dataObj = {
        proceso: 'actualizar',
        eliminar: eliminar,
        actualizar: actualizar,
        guardar: guardar,
        folio: folio
    };

    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                filasGuardadas = $("#tablaArticulosSolicitud tbody tr").length;
               // console.log(filasGuardadas);
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, '<div class="text-center">' + data.contenido.info + '</div>');

                nMostrar = 1;
                cuentaFila = 0;
                eliminados = [];
                actualizados = [];
                datosSoliN = [];

                fnCargaDetalleDeNuevo(folio, ($('#numeroestatus').val()));
                ocultaCargandoGeneral();


            } else {
                ocultaCargandoGeneral();

            }
        })
        .fail(function(result) {
            console.log("Error al actualizar 736");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });


}

function fnChecarExistencia(arreglo, valor) {
    var contarexistencia = 0;
    for (var x in arreglo) {
        if (arreglo[x] == valor) {

            contarexistencia++;
        }
    }
    return contarexistencia;
}

function fnAgregarAExistente(datosSolicitud, folio) {

    dataObj = {
        proceso: 'agregarAexistente',
        datosSolicitud: datosSolicitud,
        folio: folio
    };
    // muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                //  ocultaCargandoGeneral();               
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, '<div class="text-center">' + data.contenido.info + '</div>');

                nMostrar = 1;
                cuentaFila = 0;
            } else {
                //    ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error  agregar  existente 784");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });
        fnQuitarGeneral();
}

function fnEliminarGuardados(datos, folio) {

    dataObj = {
        proceso: 'eliminarGuardados',
        datos: datos,
        folio: folio
    };
    // muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                //  ocultaCargandoGeneral();               
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                muestraModalGeneral(4, titulo, '<div class="text-center">' + data.contenido.info + '</div>');

            } else {
                //    ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error eliminar guardados 817");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });

}

function fnActulizarEstadoAlGuardar(folio) {
    datos = $("#Avanzar").attr('onclick');
    datos = datos.substr(datos.indexOf(",") + 1)
    $("#Avanzar").prop('onclick', null);
    $("#Avanzar").attr('onclick', "fnAvanzar(" + folio + "," + datos); //.click('function(90'+datos);
}

function fnEliminarFilas() {
    $("#tablaArticulosSolicitud tbody tr").each(function() {
        $(this).remove();
    });

}
// validar si esta disponible
$(document).on('change', '.claveArticulo', function() {
    id = $(this).attr('id');
    id = id.replace("selectCveArticuloX", "");
    valor = $(this).val();

    datos = fnChecarDisponible("\'" + valor + "'\'");
    //lert(datos[0]);
   
        //alert(datos[0]);
        if (datos[0] > 0) { // si trae informacion
            //alert('disponible');
          if ($("#nodisponible" + id).length) {
                $("#nodisponible" + id).remove();
            }
            if ($('#addedCantidadArticuloX' + id).val() == "") {
              

                if ($("#Introduzca" + id).length) {

                } else {

                     /*if ($("#nodisponible" + id).length){
                        $('#addedCantidadArticuloX' + id).prop('readonly', false);
                        //$('#addedCantidadArticuloX' + id).after('<span style="color:red;" id="nodisponible'+id+'">No disponible. </span>');
                        $('#nodisponible' + id).remove();
                     }*/
                       
                    $('#addedCantidadArticuloX' + id).after('<span  id="Introduzca' + id + '" style="color:red;"> Falta cantidad. </span>');
                }
            }
            //$('#addedCantidadArticuloX'+id).focus();

        } else {
           // $('#addedCantidadArticuloX' + id).prop('readonly', true);
           if ($("#nodisponible" + id).length){

           }else{


            $('#addedCantidadArticuloX' + id).val("" + "0");
            $('#addedCantidadArticuloX' + id).after('<span style="color:red;" id="nodisponible'+id+'">No disponible. </span>');
             }
        }


    

});

$(document).on('change', '.claveDescripcion', function() {
    id = $(this).attr('id');
    id = id.replace("selectArticuloX", "");

    //$('#selectCvePartidaEspecificaX'+id+' option:selected').val()
    valor = $("#selectCveArticuloX" + id).val();
    datos = fnChecarDisponible("\'" + valor + "'\'");

   if (datos[0] > 0) { // si trae informacion
            //alert('disponible');
         if ($("#nodisponible" + id).length) {
                $("#nodisponible" + id).remove();
            }

            if ($('#addedCantidadArticuloX' + id).val() == "") {
              

                if ($("#Introduzca" + id).length) {

                } else {

                     /*if ($("#nodisponible" + id).length){
                        $('#addedCantidadArticuloX' + id).prop('readonly', false);

                        //$('#addedCantidadArticuloX' + id).after('<span style="color:red;" id="nodisponible'+id+'">No disponible. </span>');
                        $('#nodisponible' + id).remove();
                     }*/
                       
                    $('#addedCantidadArticuloX' + id).after('<span  id="Introduzca' + id + '" style="color:red;"> Falta cantidad. </span>');
                }
            }
            //$('#addedCantidadArticuloX'+id).focus();

        } else {
            //$('#addedCantidadArticuloX' + id).prop('readonly', true);
              if ($("#nodisponible" + id).length){

            }else{
                 $('#addedCantidadArticuloX' + id).val("" + "0");
                 $('#addedCantidadArticuloX' + id).after('<span  style="color:red;" id="nodisponible'+id+'">No disponible. </span>');

            }
           
        }

});
// fin validar si esta disponible
function fnSelectsBloqueadosNormal(bloqueado=0,dato='',elemento){
    var  bloqueadoHtml='<div class="">'+
    '<input type="text" id="'+elemento+'"  class="form-control" style="width: 100%" readonly="readonly"> '+
    '</div>';
    var retorno ='';
    if(bloqueado==0){
        retorno =dato;
     
    }else{
        retorno =  bloqueadoHtml;
    }

    return  retorno ;
}
function fnDetalleSolicitud(solicitud, estatusNumero, cargaDatos = 0,ur,desdeFuera=0) {
    $('#idDetalleSolicitud').val(solicitud);
    $('#numeroestatus').val(estatusNumero);
   // console.log(ur);
   // muestraCargandoGeneral();
   
    if (cargaDatos == 1) {

        datos = fnDatosSelet();
        datosClave = datos[0];
        datosDescripcion = datos[1];

    }
    var html = '';
    dataObj = {
        proceso: 'detalleSolicitud',
        solicitud: solicitud,
        ur:ur
    };
   // muestraCargandoGeneral();
    /*if(cargaDatos==0){
    muestraCargandoGeneral();
    }*/
  var  v1='';
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                detalle = data.contenido.detalle
                  ocultaCargandoGeneral();
                  fnSeleccionarDatosSelect("selectUnidadEjecutora",detalle[0].ue);
             
                
               if(detalle[0].ue!="undefined"){
                console.log("Detalle solicitud " +detalle[0].ue);
               } 
                 //<option value="-1">00 - FIDEICOMISO DE RIESGO COMPARTIDO  (FIRCO)</option>-->
                var html = '';
                var j = 0;
                var clase = '';
                var ready = '';
                var selects = '';
                var lblCantidadSolicitada= "";
                var tipo='';
             
                detalleSolicitud = detalle;
                v1=data.contenido.datos1;
                if (!detalle.includes("No hay")) {
                    if (visible == 1) {
                        clase = '';
                        $("#btnAgregar").prop("disabled", true);
                        ready = 'readonly=true';
                         tipo="hidden";
                         selects = 'disable=true';
                    
                    }
                    //c
                    if (ed1 == 1 && (estatusNumero == 24)) { //40

                        clase = 'class="btnRemoveArticulo"';
                        ready = '';
                        selects = '';
                        tipo="text";

                    } else if (ed1 == 1 && (estatusNumero > 24)) { //40
                        clase = '';
                        ready = 'readonly=true';
                        selects = 'disable=true';

                        $("#btnAgregar").prop("disabled", true);
                        
                     $(".multiselect").prop('disabled',true);
                        
                        /*$("#selectRazonSocial").find("button").prop("disable",true);
                        $("#selectUnidadNegocio").find("button").prop("disable",true);
                       
                        $("#selectAlmacen").prop("disable",true); */

                        tipo="hidden";
                     

                    }
                    //v
                    if (ed2 == 1 && ((estatusNumero == 24) || (estatusNumero == 41))) { //40

                        clase = 'class="btnRemoveArticulo"';
                        ready = '';
                        selects = '';
                          tipo="text";

                    } else if (ed2 == 1 && ((estatusNumero > 24) || (estatusNumero > 41))) { //40
                        clase = '';
                        ready = 'readonly=true';
                        selects = 'disable=true';
                        $("#btnAgregar").prop("disabled", true);
                        
                        $(".multiselect").prop('disabled',true);
                     

                        tipo="hidden";
                        /*
                        $("#tablaArticulosSolicitud thead th:eq(" + 0 + ")").css("display","none");
                        $("#tablaArticulosSolicitud tbody tr").find("td:eq(" + 0 + ")").css("display","none");*/
                    }
                            //40
                    //au
                    if (ed3 == 1 && ((estatusNumero == 24) || (estatusNumero == 41) || (estatusNumero == 43))) {

                        clase = 'class="btnRemoveArticulo"';
                        ready = '';
                        selects = '';
                          tipo="text";
                                ///40
                    } else if((ed3 == 1) && ( (estatusNumero==30) ||estatusNumero==65 )){//else if (ed3 == 1 && ((estatusNumero > 24) || (estatusNumero > 41) || (estatusNumero == 43))) {
                        clase = '';
                        ready = 'readonly=true';
                        selects = 'disable=true';
                        $("#btnAgregar").prop("disabled", true);
                        
                        //$("#selectRazonSocial").attr("disable");
                        $(".multiselect").prop('disabled',true);
                      

                        tipo="hidden";
                        
                    }

                    var clave = fnCrearDatosSelect(datosClave);
                    var descripcion = fnCrearDatosSelect(datosDescripcion);
                    var bloqueado=0;
                    if( selects == 'disable=true'){
                        bloqueado=1;
                    }
                    for (i in detalle) {
                        if (ready!= "" || ready.length > 0) {
                            lblCantidadSolicitada= "<span>"+detalle[i].cantidad+"</span>";
                            lblCantidadEntregada="<span>"+ detalle[i].cantidadentregada+"<span>";
                        }
                        
                        //html+='<tr id="f'+i+'">';// con este no quita fila
                        html += '<tr id="fila' + i + '">'
                        html += '<td class="text-center"><div ' + clase + 'id="quitar' + (i) + '" > <span class="btn btn-danger btn-xs glyphicon glyphicon-remove"  title="Eliminar"></span></div></td>';

                        html += '<td class="text-center" > <span class="rowVisible">' +detalle[i].renglon + ' </span><input type="hidden" value="' + detalle[i].renglon + '" id="rowreg' + i + '"></td>';

                        //html+='<td>'+( parseInt(i)+1)+'</td>';
                        //html+='<td>'+detalle[i].cams+'</td>';
                        //html+='<td>'+detalle[i].partida+'</td>';
                        
                        html += '<td class="text-center" id="td'+i+'">'+fnSelectsBloqueadosNormal(bloqueado,'<select id="selectCveArticuloX' + i + '" name="selectCveArticuloX' + i + '" class="claveArticulo"  required >' + clave + '</select>','selectCveArticuloXt'+i )
                        +'</td>'; //'<td class="text-center">'+detalle[i].clave+'</td>';
                        
                        html += '<td class="text-center">'+fnSelectsBloqueadosNormal(bloqueado,'<select id="selectArticuloX' + i + '" name="selectArticuloX' + i + '" class="claveDescripcion" required>' + descripcion + '</select>','selectArticuloXt'+i)+'</td>'; //'<td class="text-center">'+detalle[i].descripcion+'</td>';

                        html += '<td class="text-center"><div  type="text" id="addedUMArticulo' + i + '">' + detalle[i].unidad_medida + '</div></td>'; //'<td class="text-center">'+detalle[i].unidad_medida+'</td>'; 
                        html += '<td class="text-center">'+lblCantidadSolicitada+'<input  class="cantidadSolicitada text-right"  type="'+tipo+'" id="addedCantidadArticuloX' + i + '" placeholder="Cantidad" value="' + detalle[i].cantidad + '" ' + ready + ' min="0" onkeypress="return fnsoloDecimalesGeneral(event, this)"/></td>'; //'<td class="text-center" id="cantidad'+i+'">'+detalle[i].cantidad+'</td>'; //  
                        
                        //al
                        if (visible == 1) {
                            html += '<td>' + '<div class="cantidadDisponibleAlmacen text-center" id="disponible' + i + '"></div>' + '</td>';

                            if (data.contenido.parcial) {

                                if ($("#entregadosth").length) {

                                } else {
                                    var a = 0;
                                    $('#tablaArticulosSolicitud').find('tr').each(function() {
                                        $(this).find('th').eq(6).after('<th id="entregadosth" valign="middle">Entregados</th>');
                                        //$(this).find('td').eq(3).after('<td><input id="entregados'+a+'" type="text" val=""></td>'); 
                                        //a++;
                                    });
                                }
                                faltan = (detalle[i].cantidad - detalle[i].cantidadentregada);
                                //if(detalle[i].estatus=="parcial"){
                                if (faltan > 0) {
                                    // html+='<td>'+'<div class="cantidadDisponibleAlmacen" id="disponible'+i+'"></div>'+'</td>'; 
                                    html += '<td class="text-center">' +lblCantidadEntregada+ '<input class="cantidadentregados" id="entregados' + i + '"  value="' + detalle[i].cantidadentregada + '" type="hidden"   readonly="readonly"/>' + '</td>';
                                    //html+='<td>'+'<input class="cantidadEntregaAlmacen" id="entrega'+i+'"  value="" type="number" name="num" pattern="[0-9]" />' +'</td>'; 
                                    html += fnCuantosFaltan(detalle[i].cantidad, detalle[i].cantidadentregada, i);
                                } else { //cuando es total
                                    //html+='<td>'+'<div class="cantidadDisponibleAlmacen" id="disponible'+i+'"></div>'+'</td>'; 
                                    html += '<td class="text-center">' +lblCantidadEntregada+  '<input class="cantidadentregados" id="entregados' + i + '"  value="' + detalle[i].cantidadentregada + '" type="hidden"  readonly="readonly"/>' + '</td>';
                                    html += '<td>' + '</td>';
                                    html += '<td class="text-center">' + '<span class="Completo"> </span>' + '</td>';
                                    //$('#tablaArticulosSolicitud').find('tr').eq(i).css({"background-color": "#F5F1DE !important"});
                                    $('#tablaArticulosSolicitud > tbody > tr').eq(i).css({
                                        "background-color": "#F5F1DE !important"
                                    });
                                    $('#tablaArticulosSolicitud > tbody > tr').eq(i).addClass('completada');
                                    $('#f' + i).css("background-color", "#000000");
                                }

                            } else { // si la solicitud no es parcial
                                // html+='<td>'+'<div class="cantidadDisponibleAlmacen" id="disponible'+i+'"></div>'+'</td>'; 
                                html += '<td>' + '<input class="cantidadEntregaAlmacen text-right" id="entrega' + i + '"  value="" type="text" onkeypress="return fnsoloDecimalesGeneral(event, this)" name="num" pattern="[0-9]"  required/>' + '</td>';
                               //'<td class="text-center"><input type="text" id="addedCantidadArticuloX' + cuentaFila + '" placeholder="Cantidad"  name="" onchange="" placeholder="" maxlength="100" title="" onkeyup="" onkeypress="return fnsoloDecimalesGeneral(event, this)" onpaste="return false" class="cantidadSolicitada text-right form-control" style="width: 80px;"></td>'+
   
                                html += '<td>' + '<input class="cantidadFaltanAlmacen" id="faltan' + i + '"  value=""   type="text"  readonly="readonly"/>' + '</td>';
                            }
                        } //si es visible fin al

                        articulosSolicitud += "'" + detalle[i].clave + "',";

                        html += '</tr>';
                        j++;
                        renglonultimo = detalle[i].renglon;
                    }

                    cuentaFila = j;
                    nMostrar = parseInt(renglonultimo) + 1;
                    disponibleCantidad = fnChecarDisponible(articulosSolicitud);

                    // console.log(solicitud);
                    $('#tablaArticulosSolicitud').append(html);
                  
                    //agregar dato de disponible pintar
                    if (visible == 1) {

                    
                    for (i = 0; i < disponibleCantidad.length; i++) { // disponible para al almacen
                        $('#disponible' + i).append(disponibleCantidad[i]);
                       // console.log(i+ "-"+ disponibleCantidad[i]);

                    }
                }

                if(selects=='disable=true'){
                    for (a = 0; a < j; a++) {

                        $('#selectCveArticuloXt'+ a).val( detalle[a].clave);

                        $('#selectArticuloXt'+ a).val( detalle[a].descripcion);
                     
                    }
                }else{
                   // console.log(v1);
                    for (a = 0; a < j; a++) {

                    //     //fnNuevoFormato1("selectCveArticuloX" + a+ "") ;
                    //   //  $('#'+"selectCveArticuloX" + a ).multiselect('dataprovider', claveGlobal);
                    //     //fnSeleccionarDatosSelect("selectCveArticuloX" + a, detalle[a].clave);
                        
                    //     $('#'+"selectCveArticuloX" + a ).selectpicker('val', '' + detalle[a].clave);
                    //     //$('#'+"selectCveArticuloX" + a ).multiselect('refresh');

                    //     // //fnNuevoFormato1("selectArticuloX" + a + "");
                    //     //  $('#'+"selectArticuloX" + a  ).multiselect('dataprovider', descripcionGlobal);

                    //     //  fnSeleccionarDatosSelect("selectArticuloX" + a, detalle[a].descripcion);
                   
                    // $("#selectCveArticuloX" + a).remove();
                    // $("#td"+a).append(v1);
                   
                   
                    //fnSeleccionarDatosSelect("selectCveArticuloX3","21110251");
                    
                    // bueno
                        fnCrearDatosSelect(datosClave, '#selectCveArticuloX' + a, detalle[a].clave);
                        fnFormatoSelectGeneral('#selectCveArticuloX' + a);

                        fnCrearDatosSelect(datosDescripcion, '#selectArticuloX' + a, detalle[a].descripcion);
                        fnFormatoSelectGeneral('#selectArticuloX' + a);
                    }
                    // $('#selectCveArticuloX3').selectpicker('val',"21110251");
                    // $('#selectCveArticuloX3').multiselect('refresh');
                    // $('.'+'selectCveArticuloX3').css("display", "none");

                    //fnSeleccionarDatosSelect("selectCveArticuloX3","21110251");
                }

                    ///deshabilitar selects
                      if (visible == 1) {
                        $("#tablaArticulosSolicitud thead th:eq(" + 0 + ")").css("display","none");
                        $("#tablaArticulosSolicitud tbody tr").find("td:eq(" + 0 + ")").css("display","none");

                          for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('disable');
                            $('#selectArticuloX' + a).multiselect('disable');
                            $("#txtAreaObs").prop('disabled', true);
                               
                        }

                    }
                    if (ed1 == 1 && (estatusNumero == 24)) { //40

                         for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('enable');
                            $('#selectArticuloX' + a).multiselect('enable');
                               
                        }

                    } else if (ed1 == 1 && (estatusNumero > 24)) { //40
                        for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('disable');
                            $('#selectArticuloX' + a).multiselect('disable');
                               
                        }
                        
                        $("#tablaArticulosSolicitud thead th:eq(" + 0 + ")").css("display","none");
                        $("#tablaArticulosSolicitud tbody tr").find("td:eq(" + 0 + ")").css("display","none");
                        $("#txtAreaObs").prop('disabled', true);
                        $('#divBotones').hide();
                        $('#nopermitido').show();
                         

                    }

                    if (ed2 == 1 && ((estatusNumero == 24) || (estatusNumero == 41))) { //40

                          for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('enable');
                            $('#selectArticuloX' + a).multiselect('enable');
                               
                        }

                    } else if (ed2 == 1 && ((estatusNumero > 24) || (estatusNumero > 41))) { //40
                        for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('disable');
                            $('#selectArticuloX' + a).multiselect('disable');
                            $("#txtAreaObs").prop('disabled', true);
                            $('#divBotones').hide();
                            $('#nopermitido').show();
                               
                        }
                        
                        $("#tablaArticulosSolicitud thead th:eq(" + 0 + ")").css("display","none");
                        $("#tablaArticulosSolicitud tbody tr").find("td:eq(" + 0 + ")").css("display","none");
                    }

                    if (ed3 == 1 && ((estatusNumero == 24) || (estatusNumero == 41) || (estatusNumero == 43))) {

                         for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('enable');
                            $('#selectArticuloX' + a).multiselect('enable');
                               
                        }

                    } else if (ed3 == 1 && ((estatusNumero > 24) || (estatusNumero > 41) || (estatusNumero == 43))) {
                       for (a = 0; a < j; a++) {
                            $('#selectCveArticuloX' + a).multiselect('disable');
                            $('#selectArticuloX' + a).multiselect('disable');
                               
                        }
                        $("#txtAreaObs").prop('disabled', true);
                        $('#divBotones').hide();
                        $('#nopermitido').show();
                        $("#tablaArticulosSolicitud thead th:eq(" + 0 + ")").css("display","none");
                        $("#tablaArticulosSolicitud tbody tr").find("td:eq(" + 0 + ")").css("display","none");
                    }
                     ///fin deshabilitar selects

                    $("#numero_folio").empty();
                    $("#numero_folio").append('Folio:<span class="folius">' + solicitud + '</span>');
                    $("#txtAreaObs").empty();
                    $("#txtAreaObs").val(detalle[0].observaciones);
                    ocultaCargandoGeneral();

                } else {
                    /*html+='<tr><td colspan="9" style="text-align:center"><h3>No hay información sobre la solicitud.</h3> <td> </tr>';
           $( "#btnAgregar" ).prop( "disabled", true);
           $('#tablaArticulosSolicitud').append(html); */
           ocultaCargandoGeneral();
                }
                  /*if(cargaDatos==0){
                ocultaCargandoGeneral();
                }*/
                ocultaCargandoGeneral();
            } else {
                 /*if(cargaDatos==0){
                ocultaCargandoGeneral();
                } */
                ocultaCargandoGeneral();
            }
            fnActualizarRenglon();
        })
        .fail(function(result) {
            console.log("Error en leer detalle " + solicitud);
            // //console.log(ErrMsg);
             if(cargaDatos==0){
                ocultaCargandoGeneral();
                fnQuitarGeneral();
                } 
                  ocultaCargandoGeneral();
                  fnQuitarGeneral();
        });
    
        ocultaCargandoGeneral();
        fnQuitarGeneral();
        fnActualizarRenglon();
}

function fnAvanzar(solicitud, estatus, nombreEstatus) {
    dataObj = {
        proceso: 'avanzar',
        solicitud: solicitud,
        estatus: estatus,
        nombreEstatus: nombreEstatus
    };
    muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                fnEliminarFilas();
                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al avanzar 1314");
            // //console.log(ErrMsg);
            ocultaCargandoGeneral();
            fnQuitarGeneral();
        });
        fnQuitarGeneral();
}

function fnImprimir(idsolicitud) {
    var salidas;
    dataObj = {
        proceso: 'existeSalida',
        idsolicitud: idsolicitud
    };
    //muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                ocultaCargandoGeneral();
                salidas = data.contenido.salidas;
                var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                
                if(!salidas.includes('No existen')){

                //muestraModalGeneral(4, titulo,'<object data="'+datos+'" width="100%" height="450px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');
                muestraModalGeneral(4, titulo, '<div id="tablaSalidas"> <div id="datosSalidas"> </div> </div><div id="mostrarImpresion"> </div>');

                fnTablaSalidas(salidas, data.contenido.nombreExcel);
                ocultaCargandoGeneral();
                       }else{

                             muestraModalGeneral(4, titulo, salidas);
                             ocultaCargandoGeneral();

                       }
                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al imprimir 1360");
             //console.log(ErrMsg);
             ocultaCargandoGeneral();
             fnQuitarGeneral();
        });

    //fnImprimirSalida(51,idsolicitud);
    ocultaCargandoGeneral();
    fnQuitarGeneral();
}

function fnImprimirSalida(salida, idsolicitud) {
    // impresion

    datos = '';
    //idsolicitud
    dependencia = $('#selectRazonSocial option:selected').text(); //$('#selectRazonSocial').val();
    ur = $("#selectUnidadNegocio option:selected").text(); //$('#selectUnidadNegocio').val();
    almacen = $("#selectAlmacen option:selected").text(); //$("#selectAlmacen").val();

    datosreporte = fnDatosReporte(idsolicitud);
    datos = "almacenImprimirSolicitud.php?PrintPDF=1&solicitud=" + idsolicitud + "&nu_folio=" + salida + "&ur=" + ur + "&fechasolicitud=" + datosreporte[0] + "&almacen=" + almacen + "&dependencia=" + dependencia + "&todos=0&usuariosolicitud=" + datosreporte[1]+"&usuarioentrega=" + usuarioEntrega;

    $("#tablaSalidas").hide();
    $("#mostrarImpresion").empty();
    $("#mostrarImpresion").append('<div class="text-center"><button id="regresaTablaSalidas" class="btn btn-default botonVerde glyphicon glyphicon-home" style="color: #fff;"> Regresar</button></div><object data="' + datos + '" width="100%" height="450px" type="application/pdf"><embed src="' + datos + '" type="application/pdf" />     </object>');
    //ocultaCargandoGeneral();
    //var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    //muestraModalGeneral(4, titulo,'<object data="'+datos+'" width="100%" height="450px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');

    // fin impresion
    //$("#viewSolicitud").html('<object data="'+datos+'" width="60%" height="800px" type="application/pdf"><embed src="'+datos+'" type="application/pdf" />     </object>');

}

function fnTablaSalidas(salidas, nombreExcel) {
    fnLimpiarTabla('tablaSalidas', 'datosSalidas');

    columnasNombres = '';
    columnasNombres += "[";
    //columnasNombres += "{ name: 'id1', type: 'bool'},";
    columnasNombres += "{ name: 'tag', type: 'string' },";
    columnasNombres += "{ name: 'idsolicitud', type: 'string' },";
    columnasNombres += "{ name: 'idsolicitudsinliga',type:'string'},";
    columnasNombres += "{ name: 'fecha', type: 'string' },";

    columnasNombres += "]";
    // Columnas para el GRID
    columnasNombresGrid = '';
    columnasNombresGrid += "[";
    //columnasNombresGrid += " { text: '', datafield: 'id1', width: '5%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
    columnasNombresGrid += " { text: 'UR', datafield: 'tag', width: '40%', cellsalign: 'center', align: 'center', hidden: false },";
    columnasNombresGrid += " { text: 'Salida',datafield: 'idsolicitud', width: '20%', align: 'center',hidden: false,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'Solicitud',datafield: 'idsolicitudsinliga', width: '40%', align: 'center',hidden: true,cellsalign: 'center' },";
    columnasNombresGrid += " { text: 'Fecha', datafield: 'fecha', width: '40%', cellsalign: 'center', align: 'center', hidden: false },";

    columnasNombresGrid += "]";

    var columnasExcel = [0, 2, 3];
    var columnasVisuales = [0, 1, 3];


    fnAgregarGrid_Detalle(salidas, columnasNombres, columnasNombresGrid, 'datosSalidas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
    // $('#tablaSalidas > #datosSalidas').jqxGrid({width:'70%'});
}


$('.cantidadFaltanAlmacen :input').prop("disabled", true);

function fnChecarDisponible(articulosSolicitud) {
    var disponibles = [];
    var almacen = $('#selectAlmacen').val();
    articulosSolicitud = articulosSolicitud.slice(0, -1);
    dataObj = {
        proceso: 'checarDisponible',
        almacen: almacen,
        articulosSolicitud: articulosSolicitud
    };
    //muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                disponibles = data.contenido.datos;
                ocultaCargandoGeneral();
            } else {
            ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error  checar disponible 14554");
             //console.log(ErrMsg);
             ocultaCargandoGeneral();
             fnQuitarGeneral();
        });

    return disponibles;
}
function fnChecarDisponibleAntesDeGuardar(datos=[]) {
    var articulos='';
    var datosaux;
    var mensaje='';
    var disponibles=[];
    var clave='';
    var almacen = $('#selectAlmacen').val();
    

     for(a=0;a<datos.length;a++){

         datosaux= datos[a].split(",");
         articulos+=datosaux[2]+",";
      
 
     }
    articulos= articulos.slice(0, -1);
    dataObj = {
        proceso: 'checarDisponibleMultiple',
        almacen: almacen,
        articulos: articulos
    };
    //muestraCargandoGeneral();
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                disponibles = data.contenido.articulos;
                
                for(a=0;a<datos.length;a++){

                    datosaux= datos[a].split(",");
                    clave=datosaux[2]; //.replace("'","");
                       
                    for (var x in disponibles) {
                        cantidad=datosaux[1].replace("'","");
                        cantidad=cantidad.replace("'","");
                        if (("'"+disponibles[x].stockid+"'") == clave) {
                           // console.log(cantidad);
                            if(parseInt(cantidad)>disponibles[x].disponible){

                                mensaje+="<br><i class='glyphicon glyphicon-remove-sign text-danger' aria-hidden='true'></i> En el renglon "+datosaux[0]+" "+datosaux[3] +"<b> clave artículo "+datosaux[2]+"</b> no tiene disponible";
                                    $('.claveArticulo').each(function() {
                                    val = $(this).val();
                                  
                                    if ("'"+val+"'" == clave){
                                      id = $(this).attr('id');
                                      id=id.replace("selectCveArticuloX","");
                                       //$("#addedCantidadArticuloX"+id).val(0);
                                      
                                    }
                                    });

                            }

                        }
                    }

                }
           
                
                    
        
                //ocultaCargandoGeneral();
            } else {
                //ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error  al checar disponible antes de guardar");
             //console.log(ErrMsg);
             fnQuitarGeneral();
            // ocultaCargandoGeneral();
        });
        fnQuitarGeneral();
    return mensaje;
}

$(document).on('keyup', '.cantidadSolicitada', function(event) {
    $(this).val($(this).val().replace(/[^0-9\.]/g, ''));

    id = $(this).attr('id');
    id = id.replace("addedCantidadArticuloX", "");

    if ($("#Introduzca" + id).length) {
        $("#Introduzca" + id).remove();
    }
   

    /*if($(this).val().length==3){
    event.preventDefault();
    }*/
    /*if($(this).val()==0){
        $(this).val(1);
    }*/ // no funciona pone por delante uno
    /* if($(this).val()==""){
        $(this).val(0);
    }*/ // no funciona pone por delante uno
});
/*$(document).on('change','.cantidadFaltanAlmacen',function(){
    if($(this).val()=="NaN"){
       numero1=$(this).attr('id'); 
       numero1=numero1.replace("faltan","");
       cantidad1=$("#cantidad"+numero1).val();
       $("#faltan"+numero1).val((cantidad1+0));
    }
});*/

$(document).on('KeyDown', '.cantidadSolicitada', function(event) {
    //KeyDown="if(this.value.length==2) return false;"
    if ($(this).val().length == 3) {
        event.preventDefault();
    }

});
$(document).on('KeyDown', '.cantidadSolicitada', function(event){
    //var number = document.getElementsByClassName('cantidadSolicitada');

// Listen for input event on numInput.
    //number.onkeydown = function(e) {
    if(!((event.keyCode > 95 && event.keyCode < 106)
      || (event.keyCode > 47 && event.keyCode < 58) 
      || event.keyCode == 8)) {
        return false;
    }
});
function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : event.keyCode;
    return !(charCode > 31 && (charCode < 48 || charCode > 57));
}
$(document).on("keyup", '.cantidadEntregaAlmacen', function(event) {

    //$(this).val($(this).val().replace(/[^\d].+/, ""));
    $(this).val($(this).val().replace(/[^0-9\.]/g, ''));
    /*if ((event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }*/
    //  $(this).val($(this).val().replace(/[^\d].+/, ""));
    /*if ((event.which < 48 || event.which > 57)) {
        event.preventDefault();
    }*/
    //id de entrega

    // cantidadentregados
    var cantidad1 = 0;
    var limite = 0;
    var disponible1 = 0;
    var entrega = 0;

    numero1 = $(this).attr('id');
    numero1 = numero1.replace("entrega", "");

    entrega = $(this).val();
    cantidad1 = $('#addedCantidadArticuloX' + numero1).val();
    disponible1 = $("#disponible" + numero1).html();


    if ($(".cantidadentregados").length > 0) {

        entregados = $("#entregados" + numero1).val();
        narticuclos = $('#addedCantidadArticuloX' + numero1).val();
        //disponible1=$("#faltaparcial"+numero1).val();
        narticuclos = parseInt(narticuclos);
        entregados = parseInt(entregados);
        limite = narticuclos - entregados;

        if (limite > disponible1) {
            cantidad1 = disponible1;
        } else {
            cantidad1 = limite;
        }


    } else { // cuando no es almacenista

        limite = disponible1;
    }

    //alert(cantidad1);
    cantidad1 = parseInt(cantidad1);
    limite = parseInt(limite);
    disponible1 = parseInt(disponible1);
    entrega = parseInt(entrega);

    if (cantidad1 > disponible1) {

        /*if ($( ".cantidadentregados" ).length) {
          
         alert(limite);
        }else{
            
        }*/

        if (entrega > limite) {

            /*if(limite>disponible1){
                 $(this).val(disponible1);
                 $("#faltan"+numero1).val((disponible1));
            }else{ */


            $(this).val(limite);
            $("#faltan" + numero1).val((cantidad1 - limite));
            // }

        } else {
            $("#faltan" + numero1).val((cantidad1 - entrega));
        }

    } else {

        if (entrega > cantidad1) {
            $(this).val(cantidad1);
            $("#faltan" + numero1).val("0");
        } else {
            $("#faltan" + numero1).val((cantidad1 - entrega));
        }

    }



});
$(document).on('blur', '.cantidadEntregaAlmacen', function() {
    val1 = $(this).val();
    if (val1 == "") {
        $(this).val(0);
        numero1 = $(this).attr('id');
        numero1 = numero1.replace("entrega", "");

        if ($(".cantidadentregados").length > 0) {
            $("#faltan" + numero1).val(parseInt($('#addedCantidadArticuloX' + numero1).val()) - (parseInt($("#entregados" + numero1).val())));
        } else {
            $("#faltan" + numero1).val(parseInt($('#addedCantidadArticuloX' + numero1).val()));
        }
    }

});

$(document).on('change', '.cantidadEntregaAlmacen', function() {
    //val1=$(this).val();
    if (!$(this).val()) {
        $(this).val(0);
    }

});
//$(document).on('change','.cantidadFaltanAlmacen');
function fnSalidaFolio(){
var folio=0;
        dataObj = {
            proceso: 'salidafolio'
        };
    $.ajax({
        method: "POST",
        dataType: "json",
        url: "modelo/almacen_modelo.php",
        async: false,
        cache:false,
        data: dataObj
    })
    .done(function(data) {
        if (data.result) {

            folio = data.contenido;
            ocultaCargandoGeneral();
        } else {
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("Error al generar folio de saldia");
         //console.log(ErrMsg);
         ocultaCargandoGeneral();
    });
    fnQuitarGeneral();
    return folio;
}
function fnGuardarSalida(mensaje, confirmacion = 0, cancelar = 0) {

    idsolicitud = $('#idDetalleSolicitud').val();
    estatusNumero = $('#numeroestatus').val();
    //fnCargaDetalleDeNuevo(idsolicitud);
    if (confirmacion == 1) {

        folio=fnSalidaFolio();

        $("#Surtir").prop('disabled', true);
        if (!fnValicacionesAntesDeSurtir()) {

            var data = '';
            var datosSalida;
            var detalle;
            var datos;
            var articulos = '';
            var cantidades = '';
            var ur = $('#selectUnidadNegocio').val();
            var almacen = $('#selectAlmacen').val();

            if ($(".cantidadentregados").length > 0) { // quiere decir que es parcial

                detalle = detalleSolicitud;
                datos = fnCalcularEstatusSurtido();
                tipoEntregaPorArticulo = datos[1];

                for (i in detalle) {
                    //if(detalle[i].estatus=="parcial"){// solo guarda lo parcial
                    faltan = (detalle[i].cantidad - detalle[i].cantidadentregada);
                    if (faltan > 0) {
                        entrega = $("#entrega" + i).val();
                        faltan = $("#faltan" + i).val();
                        if (entrega > 0) {
                            data += "('" + idsolicitud + "','" + detalle[i].cantidad + "','" + entrega + "','" + faltan + "','" + detalle[i].clave + "','" + detalle[i].descripcion + "','" + detalle[i].cams + "','" + detalle[i].partida + "','" + tipoEntregaPorArticulo[i] + "','" + folio + "','" + detalle[i].unidad_medida + "','" + detalle[i].renglon + "'),";
                            articulos += "'" + detalle[i].clave + "',";
                            cantidades += "'" + entrega + "',";
                        }
                    }

                }



            } else { //si no es entrega parcial
                detalle = detalleSolicitud;
                datos = fnCalcularEstatusSurtido();
                tipoEntregaPorArticulo = datos[1];
                for (i in detalle) {
                    entrega = $("#entrega" + i).val();
                    faltan = $("#faltan" + i).val();
                    data += "('" + idsolicitud + "','" + detalle[i].cantidad + "','" + entrega + "','" + faltan + "','" + detalle[i].clave + "','" + detalle[i].descripcion + "','" + detalle[i].cams + "','" + detalle[i].partida + "','" + tipoEntregaPorArticulo[i] + "','" + folio + "','" + detalle[i].unidad_medida + "','" + detalle[i].renglon + "'),";
                    articulos += "'" + detalle[i].clave + "',";
                    cantidades += "'" + entrega + "',";
                }
            }
            datosSalida = data.slice(0, -1);
            articulos = articulos.slice(0, -1);
            cantidades = cantidades.slice(0, -1);
            //articulos+=')';
            //alert(articulos);
            dataObj = {
                proceso: 'salidas',
                datos: datosSalida,
                estatus: datos[0], //tipo entrega solicitud
                idsolicitud: idsolicitud,
                articulos: articulos,
                cantidades: cantidades,
                ur: ur,
                folio: folio,
                almacen: almacen,
                cerrar: cancelar
            };

            $.ajax({
                    method: "POST",
                    dataType: "json",
                    url: "modelo/almacen_modelo.php",
                    data: dataObj
                })
                .done(function(data) {
                    if (data.result) {
                        // alert("hoal");
                        // 
                         ocultaCargandoGeneral();
                        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
                       

                        fnCargaDetalleDeNuevo(idsolicitud, estatusNumero,1);

                        

                        ocultaCargandoGeneral();
                    } else {
                        ocultaCargandoGeneral();
                    }
                })
                .fail(function(result) {
                    console.log("Error al guardad salida");
                     //console.log(ErrMsg);
                    ocultaCargandoGeneral();
                });

        } // si las validaciones pasaron
        if (cancelar == 1) {
            $("#Surtir").hide();
            $("#Cerrar").hide();
        }
        $("#Surtir").prop('disabled', false);
    } else {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnGuardarSalida(\'' + mensaje + '\',\'' + 1 + '\')');
        ocultaCargandoGeneral();
        if (cancelar == 0) {
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnGuardarSalida(\'' + mensaje + '\',\'' + 1 + '\')');
            ocultaCargandoGeneral();
        } else if (cancelar == 1) {
            muestraModalGeneralConfirmacion(3, titulo, mensaje, '', 'fnGuardarSalida(\'' + mensaje + '\',\'' + 1 + '\',\'' + 1 + '\')');
            ocultaCargandoGeneral();
        }
    }
}

function fnValicacionesAntesDeSurtir() {
    var cuantosVacios = 0;
    var cuantosCeros = 0;
    var faltanDatos = false;
    /*if(($(".cantidadentregados").length)==($(".Completo").length)){
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo,'<h4>Se entregó todo lo requerido en la solicitud.</h4>');
           faltanDatos=true;
    }*/
    $(".cantidadEntregaAlmacen").each(function() {
        //val=$(this).val();
        if (($(this).val()) == "") {
            $(this).val(0);
            cuantosVacios++;
            faltanDatos = true;
        }

    });
    /*if(($(".cantidadentregados").length)==cuantosVacios){
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo,'<h4>No se puede dejar todas las ent.</h4>');
           faltanDatos=true;
    }*/
    /*
    if($(".cantidadEntregaAlmacen").length==cuantosVacios)
    {

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo,'Para surtir productos debe poner una cantidad a entregar.');
            existenVacios=true;
    }else if(cuantosVacios>0){

            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo,'No puede dejar  elementos vacios puede poner cero si es necesario.');
            existenVacios=true;
    }else if(cuantosCeros==$(".cantidadEntregaAlmacen").length){
         var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            
            if((cuantosCeros==$('.Completo').length) &&($('.Faltan').length<0) ){
                muestraModalGeneral(4, titulo,'Se acabó de surtir todos los artículos de la solicitud.');
            }else{
            muestraModalGeneral(4, titulo,'No puede dejar  elementos vacios puede poner cero si es necesario.');
            }
            existenVacios=true;
    }*/
    ocultaCargandoGeneral();
    return faltanDatos;
}

function fnCalcularEstatusSurtido() {
    var existeFaltantes = 0;
    var tipoEntrega = '';
    var tipoEntregaPorArticulo = [];
    var datos = [];
    $("#tablaArticulosSolicitud tbody tr").each(function(index) {
        faltan = $('#faltan' + index).val();

        if (faltan > 0) {
            tipoEntregaPorArticulo.push('parcial');
            existeFaltantes++;
        } else {
            tipoEntregaPorArticulo.push('total');
        }

    }); //fin each

    if (existeFaltantes > 0) {
        tipoEntrega = 'parcial';
    } else {
        tipoEntrega = 'total';
    }
    datos.push(tipoEntrega, tipoEntregaPorArticulo);
    ocultaCargandoGeneral();
    return datos;
} //fin funcion



function fnCuantosFaltan(cantidad, cantidadentregada, i) { // se usa esta funcion para evitar las parciales cuando anteriores al ahora de mostrar

    var datos = '';
    falta = (cantidad - cantidadentregada);
    if (falta > 0) {
        datos += '<td>' + '<input class="cantidadEntregaAlmacen text-right" id="entrega' + i + '"  value="" type="text" onkeypress="return fnsoloDecimalesGeneral(event, this)" name="num" pattern="[0-9]"  />' + '</td>';
        datos += '<td class="text-center" >' + '<input class="cantidadFaltanAlmacen text-center" id="faltan' + i + '"  value="' + falta + '"   type="text"  readonly="readonly"/><span style="color:red;" class="Faltan">Faltan</span>' + '</td>';
    } else {
        datos += '<td>' + '</td>';
        datos += '<td class="text-center">' + '<span class="Completo"></span>' + '</td>';

        $('#tablaArticulosSolicitud > tbody  >  tr').eq(i).css({
            "background-color": "#F5F1DE !important"
        });
        $('#tablaArticulosSolicitud > tbody  >  tr').eq(i).addClass('completada');
        $('#f' + i).css("background-color", "#000000");
    }

    return datos;
}

function fnCargaDetalleDeNuevo(solicitud, estatusNumero,surtir=0) {

    fnEliminarFilas();
    muestraCargandoGeneral();
    setTimeout(function(){ fnDetalleSolicitud(solicitud, estatusNumero); }, 1000);
  
    if(surtir==1){
        ocultaCargandoGeneral();
     
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, '<h4>Se guardaron los datos al surtir la solicitud con éxito.</h4>');
       
    }
 
    ocultaCargandoGeneral();
     fnActualizarRenglon();
    
}
$(document).on('click','#btnCerrarModalGeneral',function(){
    ocultaCargandoGeneral();
   $('div').removeClass("modal-backdrop");
   if (document.getElementById("ModalSpinerGeneral")){
    //document.getElementById("ModalSpinerGeneral").remove();
    $('#ModalSpinerGeneral').modal('hide');
    }

});

function fnConfirmacionCancelar(mesanje, funcion) {
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
    var mensaje = '<h4>¿' + mesanje + '?</h4>';
    muestraModalGeneralConfirmacion(4, titulo, mensaje, "", funcion);
}

function fnValidarAntesDeEnviarSolicitud() {

    var contador = 0;
    var faltanDatos = false;
    /*$('.cantidadSolicitada').each(function() {
        val = $(this).val();
        if (val == 0 || (val == ""))
            contador++;

    });*/

    $('.claveCams').each(function() {
        val = $(this).val();
        if (val == 0)
            contador++;

    });
    $('.partidaEspecifica').each(function() {
        val = $(this).val();
        if (val == 0)
            contador++;

    });

    $('.claveArticulo').each(function() {
        val = $(this).val();
        if (val == 0)
            contador++;

    });
    $('.claveDescripcion').each(function() {
        val = $(this).val();
        if (val == 0)
            contador++;

    });

    if (contador > 0) {
        faltanDatos = true;
    }
    if (faltanDatos) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo, '<h4>Faltan datos.</h4>');

    }

    return faltanDatos;
}

function fnDatosReporte(solicitud) {
    var retorno = [];
    dataObj = {
        proceso: 'datosReporte',
        solicitud: solicitud
    };
    $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            async: false,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {

                retorno.push(data.contenido.datos[0].fecha); // fecha solicitud
                retorno.push(data.contenido.datos[0].usuario); //usuario solicitud
            } else {
                // ocultaCargandoGeneral();
            }
        })
        .fail(function(result) {
            console.log("Error al generar  datos reporte 2067");
             //console.log(ErrMsg);
            ocultaCargandoGeneral();
        });
        fnQuitarGeneral();
    return retorno;

}

$(document).on('cellbeginedit', '#datosSalidas', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'idsolicitud', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'tag', 'editable', false);

});

$(document).on('click', '#regresaTablaSalidas', function() {
    $("#mostrarImpresion").empty();
    $("#tablaSalidas").show();
    $("#mostrarImpresion").empty();
});
// fin arturo lopez peña

function fnObtenerBotones(divMostrar) {
    //Opcion para operacion
    dataObj = {
        proceso: 'obtenerBotones',
        type: ''
    };
    $.ajax({
            async: false,
            cache: false,
            method: "POST",
            dataType: "json",
            url: "modelo/almacen_modelo.php",
            data: dataObj
        })
        .done(function(data) {
            //console.log("Bien");
            if (data.result) {
                //Si trae informacion
                info = data.contenido.datos;
                //console.log("presupuesto: "+JSON.stringify(info));
                nombreEstatus = '';
                var contenido = '';
                for (var key in info) {
                    var funciones = '';


                    /*if (info[key].statusid == 24) { //guardar cap
                     funciones = 'fnGuardarDatosSolicitud('+info[key].statusnext+')';
                    }else*/
                    if (info[key].statusid == 33) { //surtir pedido almacenista
                        //fnNodejarVacios();
                        funciones = "fnGuardarSalida('<h3>¿Desea surtir los artículos?</h3>')";


                    } else if (info[key].statusid == 34) { //cerrar peido almacenista
                        funciones = "fnGuardarSalida('<h3>¿Desea cerrar las solicitud?</h3>',0,1)";

                    } else if (info[key].statusid == 36) { //imprimir

                        sol = $('#idDetalleSolicitud').val();
                        funciones = 'fnImprimir(\'' + sol + '\')';
                    }

                    if (info[key].statusid == 37) { //cuando sea surtir
                        contenido += '&nbsp;&nbsp;&nbsp; \
                <button style="display:none;"></button>';

                    } 

                    if (info[key].statusid == 33) { //cuando sea surtir
                        contenido += '&nbsp;&nbsp;&nbsp; \
                <button type="button" id="' + info[key].namebutton + '" name="' + info[key].namebutton + '" onclick="' + funciones + '" class="ptb3 btn btn-primary ' + info[key].clases + '"> ' + info[key].namebutton + '</button>';

                    } else {
                        contenido += '&nbsp;&nbsp;&nbsp; \
                <button type="button" id="' + info[key].namebutton + '" name="' + info[key].namebutton + '" onclick="' + funciones + '" class="btn btn-default botonVerde ' + info[key].clases + '"> ' + info[key].namebutton + '</button>';

                    }
                }
                $('#' + divMostrar).append(contenido);

            }
        })
        .fail(function(result) {
            console.log("Error  al  obtener  botones");
             //console.log(ErrMsg);
        });
        fnQuitarGeneral();
}

function fnQuitarGeneral(){
     if (document.getElementById("ModalSpinerGeneral")){
    //document.getElementById("ModalSpinerGeneral").remove();
    }
}

function fnQueNoSeaRepetido(aComparar=0){
    // $("#tablaArticulosSolicitud tbody tr").each(function(index) {
    // $dato=$( "select[id*='selectArticuloX]").val();
    //    console.log($dato );
    // });
}

function fnVerificarNoexista(val,donde){
   var valor='',count=0,flag=false;
    $("#tablaArticulosSolicitud tbody tr").each(function() {
        fila=$(this).attr('id');
        fila=fila.replace("fila","");
      
        valor=  $("#"+donde+fila).val();
        if(valor==val){
            count++;
        }
   
    });
    if(count>1){
        flag=true;
    }

    return flag;
}
function fnVerificarNoCeros(){
    var valor='',count=0,flag=false;
    $("#tablaArticulosSolicitud tbody tr").each(function() {
        fila=$(this).attr('id');
        fila=fila.replace("fila","");
      
        valor=  $("#addedCantidadArticuloX"+fila).val();
        if((valor==0) ||(valor=='')){
            count++;
        }
   
    });
    if(count>=1){
        flag=true;
    }

    return flag;
}

function fnActualizarRenglon(){
     renglon=1;
    $("#tablaArticulosSolicitud tbody tr").each(function() {
     fila=$(this).find(".rowVisible").empty();
     fila.html(""+renglon);
     //fila=$(this).find(".renglon").val(""+cuentaFilas);
     renglon++;
    });
}