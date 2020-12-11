/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var url ="modelo/SelectEmpleado_Modelo.php";
var modelo="modelo/ABC_Empleados_Modelo.php";
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
$(document).ready(function() {
  $('#sn_rfc').prop('onpaste', null);
  $('#sn_curp').prop('onpaste', null);
 
  $('#CurrCode').multiselect('disable');
    fnFormatoSelectGeneral(".id_nu_puesto");

  $('#btnRegresar').click(function() {

    window.open("SelectEmpleado.php", "_self");//Antes SelectSupplier
  });


 $("#sn_clave_empleado").change(function(){

    $valor=$(this).val();
    if($valor.length>3){
      fnValidarClavEmplIdSupp($valor);
    }

 });

/*$("#cuenta").change(function(){
  //fnVerificarCuentaBanco($(this).val());
});

$("#clabe").change(function(){
  //validarInterbancaria();
});*/


 fnCuentaBanco("bancoExistentes","bank2");
 fnCuentas();

$('#btnCuentaBank').click(function(){
        $('#ModalCuentaBank').draggable();
        $('#ModalCuentaBank_Titulo').empty();
        $('#ModalCuentaBank_Titulo').append(titulo);
        $('#ModalCuentaBank').modal('show');
        $("#valMsgCountBank").empty();
});

    $('#btnSaveCBank').click(function() {
        fnCountBankSave();

    });

    /*$('#regimen').change(function() {

        $regimen = $(this).val();

        switch ($regimen) {

            case '0':

                break;

            case '1':
                fnMostrarEsconder('hide');
                break;

            case '2':
                fnMostrarEsconder('show');
                break;

        }
    });*/

$('#btnPartida').click(function(){
        $('#ModalPartidas').draggable();
        $('#ModalPartidas_Titulo').empty();
        $('#ModalPartidas_Titulo').append(titulo);
        $('#ModalPartidas').modal('show');
});

  $('#cuentasCon').change(function(){
    if($(this).val()){
      fnChecarCuentacontable( $(this).val());
    }
  });
  // comportamiento de inputs de búsqueda
  $(".buscarCuenta").focusout(function(event){
      id = $(this).attr('id').replace("cuenta__","");
      if($(this).val()!=$("#"+id).val()){
          var buscarCoicidencia = new RegExp('^'+$(this).val()+'$' , "i");

          var arr = jQuery.map(window.cuentasMenores[id], function (value,index) {
              return value.match(buscarCoicidencia) ? index : null;
          });

          if(!arr.length){
              var arr = jQuery.map(window.cuentasMayores[id], function (value,index) {
                  return value.match(buscarCoicidencia) ? index : null;
              });
          }

          if(arr.length){
              $("#"+id).val($(this).val());
          }else{
              $("#"+id).val("");
              $(this).val($("#"+id).val());
          }
      }
  });
  $(".buscarCuenta").keypress(function(event){
      return fnSoloNumeros(event);
  });
  $(".buscarCuenta").keyup(function(){
      id          = $(this).attr('id');
      idBuscador  = "#"+id;
      id          = id.replace("cuenta__","");
      idHidden    = "#"+id;
      idDiv       = "#sugerencia-"+id;
      id          = idHidden.replace("#","");

      // no funciona para buscar el numero de puntos que aparece
      // data=$(this).val();
      // var busca = new RegExp('.','g');
      // var veces = (data.match(RegExp('.','g') || [] ).length);
      // console.log(veces," ", data);

      veces = vecesRepeticiones($(this).val(), ".") ;

      if($(this).val()!=''){
          var buscar = $(this).val(); 
          var retorno = "<ul id='articulos-lista-consolida'>";
          var buscarCoicidencia = new RegExp('^'+buscar , "i");

          var arr = jQuery.map(window.cuentasMenores[id], function (value,index) {
              return value.match(buscarCoicidencia) ? index : null;
          });

          for(a=0;a<arr.length;a++){
              val = arr[a];
              retorno+="<li onClick='fnSelectCuenta(\""+window.cuentasMenores[id][val]+"\",\""+idDiv+"\",\""+idBuscador+"\",\""+idHidden+"\")'><a href='#'>"+window.cuentasMenores[id][val]+" - "+window.textoMenores[id][val]+"</a></li>";
          }

          if(veces>4){
                  var arr = jQuery.map(window.cuentasMayores[id], function (value,index) {
                      return value.match(buscarCoicidencia) ? index : null;
                  });

                  for (a=0; a<arr.length;a++){
                      val=arr[a];
                      retorno+="<li onClick='fnSelectCuenta(\""+window.cuentasMayores[id][val]+"\",\""+idDiv+"\",\""+idBuscador+"\",\""+idHidden+"\")'><a href='#'>"+window.cuentasMayores[id][val]+" - "+window.textoMayores[id][val]+"</a></li>";
                  }

          }

          retorno+="</ul>";

          $.each(buscadores,function(index, valor){
              if(idDiv!="#sugerencia-"+valor){
                  $("#sugerencia-"+valor).hide();
                  $("#sugerencia-"+valor).empty();
              }
          });

          $(idDiv).show();
          $(idDiv).empty();
          $(idDiv).append(retorno);
      }else{
          $(idDiv).hide();
          $(idDiv).empty();
      }
  });
  $("body").click(function(evt){
      if(evt.target.id.substr(0,8)=="cuenta__"||evt.target.id.substr(0,11)=="sugerencia-"){
          var divActivo = "";
          divActivo = ( evt.target.id.substr(0,8)=="cuenta__" ? evt.target.id.substr(8) : divActivo );
          divActivo = ( evt.target.id.substr(0,11)=="sugerencia-" ? evt.target.id.substr(11) : divActivo );
          $.each(buscadores,function(index, valor){
              if($("#sugerencia-"+valor).is(":visible")&&valor!=divActivo){
                  $("#sugerencia-"+valor).hide();
                  $("#sugerencia-"+valor).empty();
              }
          });
          return;
      }
      $.each(buscadores,function(index, valor){
          if($("#sugerencia-"+valor).is(":visible")){
              $("#sugerencia-"+valor).hide();
              $("#sugerencia-"+valor).empty();
          }
      });
  });

  /*$('#Email').change(function(){
    valido=validaEmail($(this).val());
    valido==true ? '' : $(this).val("");
  });

  $('#EstadoSel').change(function(){
    fnMunicipio();
  });*/

  $('#sn_rfc').change(function(){
    fnValidarRfc($('#sn_clave_empleado').val());
    console.log("cambio sn_rfc");
  });
    $("#sn_rfc").keyup(function(){
        var upperclave = $("#sn_rfc").val();
        upperclave = upperclave.toUpperCase();  
        $("#sn_rfc").val(""+upperclave);
    });
    $("#sn_curp").keyup(function(){
        var upperclave = $("#sn_curp").val();
        upperclave = upperclave.toUpperCase();  
        $("#sn_curp").val(""+upperclave);
    });
    $("#ln_nombre").keyup(function(){
        var upperclave = $("#ln_nombre").val();
        upperclave = upperclave.toUpperCase();  
        $("#ln_nombre").val(""+upperclave);
    });
    $("#sn_primer_apellido").keyup(function(){
        var upperclave = $("#sn_primer_apellido").val();
        upperclave = upperclave.toUpperCase();  
        $("#sn_primer_apellido").val(""+upperclave);
    });
    $("#sn_segundo_apellido").keyup(function(){
        var upperclave = $("#sn_segundo_apellido").val();
        upperclave = upperclave.toUpperCase();  
        $("#sn_segundo_apellido").val(""+upperclave);
    });
  /*$('#moraltaxid').change(function(){
     fnValidarRfc($('#sn_clave_empleado').val());
     console.log("cambio moraltaxid");
     //
  });*/

$("#confirmacionModalGeneral1").click(function(e){
        e.preventDefault();
        e.stopPropagation();
        tipo=$("#tipoMg").val();
        accion=$("#accionMg").val();
        $("#ModalGeneral1").modal('hide'); 
        console.log('jx tipo='+tipo);
        
        if(tipo=='partida'){
            fnDesactivarPartida($('#sn_clave_empleado').val(),partida,accion);
        }else{
            
            fnDesactivarCuenta($('#sn_clave_empleado').val(),cuenta,clabe,accion);    
        }
                   
    });

});// fin ready
/*function fnVerificarCuentaBanco(cuenta) {
    var cuenat1 = '',
        cuenta2, flag = false;
    cuenta1 = cuenta.substr(0, 3);
    cuenta2 = $("#valClabe").val();
    console.log(cuenta1 + " primeros3");


    if (cuenta1 != cuenta2) {
        //muestraModalGeneral(4, titulo,"La cuenta no contiene la clabe del banco."); 
        $("#valMsgCountBank").show();
        $("#valMsgCountBank").empty();
        $("#valMsgCountBank").append("<h5>El número de cuenta no contiene la clave del banco.</h5> ");
        flag = true;
    } else {
        $("#valMsgCountBank").hide();
        flag = false;
    }

    return flag;
}

function fnMostrarEsconder(tipo) {

    if (tipo == 'hide') {

        //$('#fisicaNombre').show();
        $('#fisicaCurp').show();
        $('#fisicaRfc').show();

        $('#moralRazonSocial').hide();
        $('#moralRfc').hide();

    } else {
        //$('#moralRfc').show();
        $('#moralRazonSocial').show();
        $('#moralRfc').show();

        $('#fisicaCurp').hide();
        //$('#fisicaNombre').hide();
        $('#fisicaRfc').hide();

    }

}*/
$(document).on('change','#partidapresupuestal',function(){
   var capitulos=[];
   $('#partidapresupuestal :selected').each(function(){
     capitulos.push($(this).val());

    });
    fnMultiplesCapitulo(capitulos);
  
});
$(document).on('change','#presuuestaloncepto',function(){
   var conceptos=[];
   $('#presuuestaloncepto :selected').each(function(){
     conceptos.push($(this).val());
    });
    fnMultiplesConceptos(conceptos);
  
});
$(document).on('change','#partidagenerica',function(){
   var  genericas=[];
   $('#partidagenerica :selected').each(function(){
     genericas.push($(this).val());
    });
    fnMultiplesGenericas(genericas);
   
});

$(document).on('change','#partidaespecifica',function(){
  fnEvitarPartidaVacia();
   
}) 

function fnMultiplesCapitulo(capitulos=[],conceptosSel=[]){

 //muestraCargandoGeneral();
         dataObj = { 
          proceso: 'multiplesCapitulos',
          capitulos: capitulos
        };
        
        $.ajax({
          async:false,
          cache:false,
          method: "POST",
          dataType: "json",
          url: modelo,
          data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                $('#presupuestalconceptoespacio').empty();
                datos = data.contenido.conceptos;

                 select = fnCrearDatosSelect(datos,"","",0);
                 //$('#divConcepto').show();
               
                 $('#presupuestalconceptoespacio').empty();
                $('#presupuestalconceptoespacio').append('<select id="presuuestaloncepto" name="presuuestaloncepto[]" multiple="multiple"  class="presupuestalconcepto"  required>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".presupuestalconcepto");
                  $('#selectpresupuestal').show();
                  fnEvitarPartidaVacia();

                  if(conceptosSel.length>0){


                  $('#presuuestaloncepto').selectpicker('val', conceptosSel );
                  $('#presuuestaloncepto').multiselect('refresh');
                  //$('.presuuestaloncepto').css("display", "none");
            
                  $('.presupuestalconcepto').hide();
                   }

                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });


}

function fnMultiplesConceptos(conceptos=[],partidasSel=[]){

 //muestraCargandoGeneral();
         dataObj = { 
          proceso: 'multiplesConceptos',
          conceptos: conceptos
        };
        
        $.ajax({
          async:false,
          cache:false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                $('#partidagenericaespacio').empty();
                //datos = data.contenido.genericas;
                datos = data.contenido.especificas;
                 select = fnCrearDatosSelect(datos,"","",0);
                // $("#divPartida").show();
                 $('#partidagenericaespacio').empty();
                 $('#partidagenericaespacio').append('<select id="partidagenerica" name="partidagenerica[]" class="partidagenerica" multiple="multiple"   required>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".partidagenerica");
                  $('#selectpartida').show();
                  fnEvitarPartidaVacia();

                  // if(partidasSel.length>0){
                  //   $('#partidagenerica').selectpicker('val', partidas );
                  //   $('#partidagenerica').multiselect('refresh');
                  //   $('.partidagenerica').css("display", "none");
                  // }

                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });


}

function fnMultiplesGenericas(genericas=[]){

 //muestraCargandoGeneral();
         dataObj = { 
          proceso: 'multiplesGenericas',
          genericas: genericas
        };
        
        $.ajax({
          async:false,
          cache:false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                $('#partidaespecificaespacio').empty();
                datos = data.contenido.especificas;

                 select = fnCrearDatosSelect(datos,"","",0);
                $('#partidaespecificaespacio').append('<select id="partidaespecifica" name="partidaespecifica[]" class="partidaespecifica" multiple="multiple" required>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".partidaespecifica");
                  $('#selectespecifica').show();
                  fnEvitarPartidaVacia();

                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }


        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });


}

function fnEvitarPartidaVacia(){

    var partidapresupuestal= 0;
    var presuuestaloncepto=  0;
    var partidagenerica=     0;
    var especificca =        0;
   if(($('#partidapresupuestal').length>0) && ($('#presuuestaloncepto').length>0) && ($('#partidagenerica').length>0) &&($('#partidaespecifica').length>0)  ) {
       partidapresupuestal= $('#partidapresupuestal').val();
       presuuestaloncepto=  $('#presuuestaloncepto').val();
       partidagenerica=     $('#partidagenerica').val();
       especificca =        $('#partidaespecifica').val();
 
    if((especificca!=0) && (partidapresupuestal!=0)  && (presuuestaloncepto!=0) &&(partidagenerica!=0)){
        $('#enviarbtn').show();
    }else{
         $('#enviarbtn').hide();
    }
  }
}

/*$(document).on('change','#partidapresupuestal',function(){
    var capitulo = this.value;
    fnPartidaPresuPuestalConcepto(capitulo);
  
});

$(document).on('change','#presuuestaloncepto',function(){
    var capitulo = this.value;
    fnPartidagenerica(capitulo);
   
});

$(document).on('change','#partidagenerica',function(){
    var capitulo = this.value;
    fnPartidaespecifica(capitulo);
   
});

$(document).on('change','#partidaespecifica',function(){
  fnEvitarPartidaVacia();
   
}) */
function fnPartidaPresuPuestalConcepto(capitulo) {

        //muestraCargandoGeneral();
         dataObj = { 
          proceso: 'presuPuestalConcepto',
          capitulo: capitulo
        };
        
        $.ajax({
          async:false,
          cache:false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                $('#presupuestalconceptoespacio').empty();
                datos = data.contenido.presuuestaloncepto;

                 select = fnCrearDatosSelect(datos);
                $('#presupuestalconceptoespacio').append('<select id="presuuestaloncepto" name="presuuestaloncepto" class="presupuestalconcepto"  required>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".presupuestalconcepto");
                  $('#selectpresupuestal').show();
                  fnEvitarPartidaVacia();

                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
    }




function fnPartidagenerica(capitulo) {

        //muestraCargandoGeneral();
         dataObj = { 
          proceso: 'partidagenerica',
          capitulo: capitulo
        };
        
        $.ajax({
          async:false,
          cache:false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                $('#partidagenericaespacio').empty();
                datos = data.contenido.partidagenerica;

                 select = fnCrearDatosSelect(datos);
                $('#partidagenericaespacio').append('<select id="partidagenerica" name="partidagenerica" class="partidagenerica"  required>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".partidagenerica");
                  $('#selectpartida').show();
                  fnEvitarPartidaVacia();

                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
    }
function fnPartidaespecifica(capitulo) {

        //muestraCargandoGeneral();
         dataObj = { 
          proceso: 'partidaespecifica',
          capitulo: capitulo
        };
        
        $.ajax({
          async:false,
          cache:false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj
        })
        .done(function(data) {

            if (data.result) {
                $('#partidaespecificaespacio').empty();
                datos = data.contenido.partidaespecifica;

                 select = fnCrearDatosSelect(datos);
                $('#partidaespecificaespacio').append('<select id="partidaespecifica" name="partidaespecifica" class="partidaespecifica"  required>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".partidaespecifica");
                  $('#selectespecifica').show();
                  fnEvitarPartidaVacia();

                ocultaCargandoGeneral();
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            ocultaCargandoGeneral();
        });
    }

function fnRegresar(){
   window.open("SelectEmpleado.php", "_self");
}

function fnValidadLongitudRfc(){
  flag=false;
  rfc='';
  rfc= $('#sn_rfc').val();
  // console.log(rfc+ " rfc");
  if(rfc.length<10){
    flag=false;
  }else{
    flag=true;
  }

  //console.log(rfc.length +" size rfc");
  retorno=[];
  retorno.push(flag);
  retorno.push(rfc);

  return retorno; 
}

function fnValidadLongitudCurp(){
  flag=true;
  curp='-24';
  curp= $('#sn_curp').val();

  if(curp!='-24'){
    if(curp.length<14){
      flag=false;
    }else{
      flag=true;
    }
   }

   return flag;
}

function fnValidarDatos(){
  claveempleado = $('#sn_clave_empleado').val();
  nombre = $('#ln_nombre').val();
  apPat = $('#sn_primer_apellido').val();
  apMat = $('#sn_segundo_apellido').val();

  var mensajeURUE = [],
      mensajeNombre = [],
      capturaRFCCurp = [],
      longitudRFCCurp = [],
      validezRFCCurp = [],
      erroresDeCaptura = [],
      vecesValidezRFCCurp = 0,
      diferenciaRFCCurp = false;

  if($('#selectUnidadNegocio').val()=="-1"){
    mensajeURUE.push("la UR");
  }

  if($('#selectUnidadEjecutora').val()=="-1"){
    mensajeURUE.push("la UE");
  }

  if(mensajeURUE.length){
    erroresDeCaptura.push("Es necesario proporcionar "+mensajeURUE.join(" y ")+".");
  }

  if(nombre==''||!(nombre.length>0)){
    mensajeNombre.push("el nombre");
  }

  if((apPat==""||!apPat.length>0)&&(apMat==""||!apMat.length>0)){
    mensajeNombre.push("alguno de los apellidos");
  }

  if(mensajeNombre.length){
    erroresDeCaptura.push("Es necesario proporcionar "+mensajeNombre.join(" y ")+" del empleado.");
  }

  if(claveempleado==''||!(claveempleado.length>0)){
    erroresDeCaptura.push("Es necesario proporcionar la clave del empleado.");
  }

  if($('#sn_rfc').val()==''||!($('#sn_rfc').val().length>0)){
    capturaRFCCurp.push("el RFC");
  }

  if($('#sn_curp').val()==''||!($('#sn_curp').val().length>0)){
    capturaRFCCurp.push("la CURP");
  }

  if(capturaRFCCurp.length){
    erroresDeCaptura.push("Es necesario proporcionar "+capturaRFCCurp.join(" y ")+" del empleado.");
  }

  if($('#sn_rfc').val().length&&!fnValidadLongitudRfc()[0]){
    longitudRFCCurp.push("del RFC");
  }

  if($('#sn_curp').val().length&&!fnValidadLongitudCurp()){
    longitudRFCCurp.push("de la CURP");
  }

  if(capturaRFCCurp.length<2&&longitudRFCCurp.length){
    erroresDeCaptura.push("La longitud "+longitudRFCCurp.join(" y ")+" no es la requerida.");
  }

  if(fnValidadLongitudRfc()[0]&&!validarRfcGenerico($('#sn_rfc').val())){
    validezRFCCurp.push("RFC");
    vecesValidezRFCCurp = 1;
  }

  if(fnValidadLongitudCurp()&&!validarCurp($('#sn_curp').val())){
    validezRFCCurp.push("CURP");
    vecesValidezRFCCurp += 2;
  }

  if(capturaRFCCurp.length<2&&longitudRFCCurp.length<2&&validezRFCCurp.length){
    erroresDeCaptura.push(validezRFCCurp.join(" y ")+" inválid"+( vecesValidezRFCCurp==1 ? "o" : ( vecesValidezRFCCurp==2 ? "a" : "os" )  )+".");
  }

  if(!capturaRFCCurp.length&&!longitudRFCCurp.length&&!validezRFCCurp.length&&$('#sn_rfc').val().substr(0,10)!=$('#sn_curp').val().substr(0,10)){
    erroresDeCaptura.push("Existen discrepancias entre el RFC y la CURP.");
    diferenciaRFCCurp = true;
  }
  
  return erroresDeCaptura;
}

function fnGuardar(){
  claveempleado = $('#sn_clave_empleado').val();
  nombre = $('#ln_nombre').val();
  apPat = $('#sn_primer_apellido').val();
  apMat = $('#sn_segundo_apellido').val();

  var erroresDeCaptura = fnValidarDatos(),
      validado = ( erroresDeCaptura.length ? false : true );

  if(validado){
        muestraCargandoGeneral();
        datosarreglo=[];
        var i=0;
        var formData = new FormData(document.getElementById('empleadoAlta'));
        formData.append("proceso","guardarInfo");
        formData.append("Puesto",$('#id_nu_puesto :selected').text());
        formData.append("selectUnidadNegocio", $("#selectUnidadNegocio").val());

         $.ajax({
            url: modelo,
            async:false,
            cache:false,  
            type: "post",
            dataType: "json",
            data: formData,
            contentType: false,
            processData: false
        })
        .done(function(data) {

            if (data.result) {
                info= data.contenido.Mensaje;
                muestraModalGeneral(4, titulo,info); 
                ocultaCargandoGeneral();

                $('#ModalGeneral').on('hidden.bs.modal',function(){
                  var form = document.createElement("form");
                  form.setAttribute("method", "post");
                  form.setAttribute("action", "ABC_Empleados.php");
                  form.setAttribute("target", "_self");

                  var hiddenField = document.createElement("input");
                  hiddenField.setAttribute("type", "hidden");
                  hiddenField.setAttribute("name", "idEmp");
                  hiddenField.setAttribute("value", data.contenido.idEmpleado);
                  form.appendChild(hiddenField);

                   var hiddenField2 = document.createElement("input");
                  hiddenField2.setAttribute("type", "hidden");
                  hiddenField2.setAttribute("name", "mod");
                  hiddenField2.setAttribute("value", 1);
                  form.appendChild(hiddenField2);

                  document.body.appendChild(form);
                  form.submit();
                });

                //$('#panelBancos').show();
                $('#panelPartidas').show();
                 $("#btnGuardarEmp").prop('onclick', null);
                $('#sn_clave_empleado').prop('readonly', true);
                $('#sn_clave_empleado').attr('readonly', true);


                $("#btnGuardarEmp").attr('onclick', 'fnModificar()');
                
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
             muestraModalGeneral(4, titulo,"Hubo un error al guardar datos del empleado"); 
            ocultaCargandoGeneral();
        });
  }else{
    muestraModalGeneral(4, titulo, erroresDeCaptura.join("<br>"));
  }

}

function fnModificar(){
  var erroresDeCaptura = fnValidarDatos(),
      validado = ( erroresDeCaptura.length ? false : true );

  if(validado){
    muestraModalGeneralConfirmacion(3, titulo, '¿Desea modificar el empleado?', '', 'fnSendCambios()');
  }else{
    muestraModalGeneral(4, titulo, erroresDeCaptura.join("<br>"));
  }
}
function fnSendCambios(){
  claveempleado = $('#sn_clave_empleado').val();
  nombre = $('#ln_nombre').val();
  apPat = $('#sn_primer_apellido').val();
  apMat = $('#sn_segundo_apellido').val();

   // muestraCargandoGeneral();
  var flag=false;
  var flagC=true;
  var rfcCorrecto=false;
  flag= fnValidadLongitudRfc();
  flagC=fnValidadLongitudCurp();

    if(  ( nombre!=''&&(nombre.length>0) )&&( (apPat!=""&&apPat.length>0)||(apMat!=""&&apMat.length>0) )  ){
      var rfcValido=false;
      var curpValido=false;

      rfcValido= validarRfcGenerico($('#sn_rfc').val());
      curpValido=validarCurp($('#sn_curp').val());
        
      if( ( claveempleado!=''&&(claveempleado.length>0) )  ){
		if((flag[0]==true) && (flagC==true)){
			if((rfcValido==false)||(curpValido==false)){
				//muestraModalGeneral(4, titulo,"RFC o CURP no valido.");
			}
		}else{
			//muestraModalGeneral(4, titulo,"La longitud del RFC o CURP no es la requerida.");
		}

        datosarreglo=[];
        var i=0;
        var formData = new FormData(document.getElementById('empleadoAlta'));
        formData.append("proceso","modificarInfo");
        formData.append("selectUnidadNegocio", $("#selectUnidadNegocio").val());

        console.log(formData);

         $.ajax({
            url: modelo,
            type: "post",
            dataType: "json",
            data: formData,
            cache: false,
            async:false,
            contentType: false,
            processData: false
        })
        .done(function(data) {

            if (data.result) {
                info= data.contenido;
                muestraModalGeneral(4, titulo,info); 
               // ocultaCargandoGeneral();
                
            } else {
                ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
             muestraModalGeneral(4, titulo,"Hubo un error al modificar los datos del empleado"); 
            ocultaCargandoGeneral();
        });
      }else{
        muestraModalGeneral(4, titulo,"Es necesario proporcionar la clave del empleado.");
      }
    }else{
      muestraModalGeneral(4, titulo,"Es necesario proporcionar el nombre del empleado.");
    }
}
function  fnValidarClavEmplIdSupp(id,soloValidar=0){

    var retorno=false;
   // muestraCargandoGeneral();
         dataObj = { 
          proceso: 'existeClavEmplIdSupp',
          idSupp: id
        };
        
        $.ajax({
            cache: false,
            async:false,
            method: "POST",
            dataType: "json",
            url: modelo,
            data: dataObj,
        })
        .done(function(data) {

            if (data.result) {
                if(soloValidar==0){
                  datos = data.contenido;
                  if(datos.existeEmp||datos.existeSup){
                    muestraModalGeneral(4, titulo,'La clave de empleado ya fue asignada, elija otra');
                    $('#sn_clave_empleado').val("");
                    $('#sn_clave_empleado').focus();
                    retorno=datos;
                  }
                 }else{
                    retorno=false;
                 }
                
                //ocultaCargandoGeneral();
            } else {
                //ocultaCargandoGeneral();
            }

        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            //ocultaCargandoGeneral();
        });
        return retorno;
}

function  fnValidarRfc(id,soloValidar=0){
  var rfc='';
    
  rfc=$('#sn_rfc').val();
  if(rfc!=''){
  dataObj = { 
    proceso: 'existeRFC',
    rfc: rfc,
    id: window.idEmp
  };

  $.ajax({
    async:false,
    cache: false,
    method: "POST",
    dataType: "json",
    url: modelo,
    data: dataObj,
  })
  .done(function(data) {

      if (data.result) {
           vacio="";
          if(soloValidar==0){
            datos = data.contenido;
            if(datos==true){
              muestraModalGeneral(4, titulo,'El RFC proporcionado ya fue dado de alta. ');
              $('#sn_rfc').val(vacio);
              $('#sn_rfc').focus(vacio);
            }
            retorno=datos;
          }
      }else{
        retorno=false;
      }
          
          //ocultaCargandoGeneral();
  })
  .fail(function(result) {
    console.log("ERROR");
    console.log(result);
    //ocultaCargandoGeneral();
  });
  }

}

$(document).on('click','#btnCerrarModalGeneral',function(){
    ocultaCargandoGeneral();
   $('div').removeClass("modal-backdrop");
   if (document.getElementById("ModalSpinerGeneral")){
    //document.getElementById("ModalSpinerGeneral").remove();
    $('#ModalSpinerGeneral').modal('hide');
    }

});


function  fnCuentas(){
 // ids=fnChecarSeleccionadosProv();
 // if(ids.length>0){
  dataObj = { 
          proceso: 'cuentasContables',
          idEmp: ( !((typeof window.idEmp)==="undefined") ? window.idEmp : "" )
        };
    // 
  //muestraCargandoGeneral();
  $.ajax({
      async:false,
      cache: false,
        method: "POST",
        dataType:"json",
        url: url,
        data:dataObj
    })
  .done(function( data ) {
      if(data.result){
     
      
      cuentas=data.contenido.cuentas;
      cargarLista("cuentasCon",data.contenido);
      diot=data.contenido.diot;
      
        

    optionsCuentas=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
        
        $.each(cuentas, function(index, val) { optionsCuentas.push({ label:val.texto, title: val.texto, value:val.value }); });
        

          /*$('#'+"cuentasCon").multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            $('#'+"cuentasCon").multiselect('dataprovider',optionsCuentas);*/
            
      //$('.multiselect-container').css({ 'max-height': "220px" });
            //$('.multiselect-container').css({ 'overflow-y': "scroll" });



        optionsDiot=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
        
        $.each(diot, function(index, val) { optionsDiot.push({ label:val.texto, title: val.texto, value:val.value }); });
        

          $('#'+"diotSel").multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            $('#'+"diotSel").multiselect('dataprovider',optionsDiot);
            
            $('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });

    
          //fnEjecutarVueGeneral('parteSelec');
         
      $( '<button class="btn  botonVerde glyphicon glyphicon-save" id="btnGuardar">Guardar</button>' ).insertBefore( "#btnCerrarModalGeneral" );
      $('#ModalCuentaCon').draggable();  
        $('#ModalCuentaCon_Titulo').empty();
        $('#ModalCuentaCon_Titulo').append(titulo);
        $('#ModalCuentaCon').modal('show');
            //fnMostrarDatos();
        ocultaCargandoGeneral();
      }else{
        ocultaCargandoGeneral();
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      ocultaCargandoGeneral();
  });
    // }else{

    //        muestraModalGeneral(4, titulo,"Seleccione al menos un proveedor");

    // }
}



 function fnCuentaBanco(idAdd,idSel){
  var datos=new Array();
  dataObj = { 
          proceso: 'getBanco',
   
        };

  $.ajax({
        method: "POST",
        dataType:"json",
        url: modelo,
        data:dataObj,
        async:false,
        cache:false
    })
  .done(function( data ) {

    
    
      if(data.result){
        
      info=data.contenido.DatosBanco;
      html='<option select value="0">  Seleccionar.. </option>';
      for(i in info){
        html+='<option select value="'+info[i].id+'"> '+info[i].banco;+' </option>';
        //console.log(info[i].cuenta+info[i].banco);
      }
      var html1='';
      html1+='<div class="col-xs-12 col-md-9"> <select name="'+idSel+'" id="'+idSel+'" class="'+idSel+'"> </div>';
      html1+=html;
      html1+=' </select>';
      $("#"+idAdd).empty();
      $("#"+idAdd).append(' <div class="col-xs-12 col-md-3"> <span><b>Bancos:</b> </span> </div>'+html1);
   //   console.log($('.'+idSel));
    

       $('.'+idSel).multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            //$('#'+id).multiselect('dataprovider', options);
            
      $('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });
            $('.'+idSel).multiselect('rebuild');

      }else{

        
      }
  })
  .fail(function(result) {
    console.log("ERROR");
      console.log( result );
      
  });
  return  datos;
 }


 /*function fnCuentasContablesProv(provedor){

     var url ="modelo/SelectEmpleado_Modelo.php";
  
  
    //Opcion para operacion
    dataObj = { 
            proceso: 'cuentasProv',
            idSupp: provedor
          };
    //Obtener datos de las bahias
    $.ajax({
          method: "POST",
          dataType:"json",
          url:url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            datos=data.contenido.cuentasProv;


            fnLimpiarTabla('tablaCuentas', 'datosCuentas');

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: '', type: 'bool'},";
            columnasNombres += "{ name: 'cuenta', type: 'string' },";
            columnasNombres += "{ name: 'concepto', type: 'string'},";
            columnasNombres += "{ name: 'diot', type: 'string'},";
            columnasNombres += "{ name: 'modificar', type: 'string'},";    
        
            columnasNombres += "]";


            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            //columnasNombresGrid += " { text: '', datafield: '', width: '6%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'Cuenta',datafield: 'cuenta', width: '25%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Concepto',datafield: 'concepto', width: '33%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Diot',datafield: 'diot', width: '33%', align: 'center',hidden: false,cellsalign: 'center' },";
          columnasNombresGrid += "   { text: '',datafield: 'modificar', width: '9%', align: 'center',hidden: false,cellsalign: 'center' },";
          
            columnasNombresGrid += "]";

            var columnasExcel = [ 1,2,3,4];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(datos, columnasNombres, columnasNombresGrid, 'datosCuentas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

            //$('#tablaCuentas > #datosCuentas').jqxGrid({width:'40%'});
          


        }
    })
    .fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos del proveedor"); 
        console.log("ERROR");
        console.log( result );
    });
}

function  fnExisteCuentaBancaria(){
 var  retorno=false;
  dataObj = { 
            proceso: 'existeCuentaBancaria',
            ids:$('#sn_clave_empleado').val(), //ids,
            bank:$("#bank2").val(),
            cuenta: $('#cuenta').val(),
            clabe:$('#clabe').val()
         
          };
    $.ajax({
          method: "POST",
          dataType:"json",
          url: modelo,
          data:dataObj,
          async:false
      })
    .done(function( data ) {
        if(data.result){
       
          
          retorno=data.contenido;
          
        }else{
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
        ocultaCargandoGeneral();
    });


    return retorno;
   
}*/

function  fnCountBankSave(){
 //existeCuenta=fnExisteCuentaBancaria();
// verificaNcuenta=fnVerificarCuentaBanco($('#cuenta').val());
// alert();
// if((retorno==false)){
b=$("#bank2").val();
r=$("#ref").val();
c=$('#cuenta').val();
cl=$('#clabe').val();
flag=false;

var $msg='';
if( (b==0) ){
//muestraModalGeneral(4, titulo,'Existen datos  vacios.');
    $msg+='Seleccione un banco.';
   flag=true;       
}
if((r=="") || (r==null)){
    $msg+='<br>Proporcione una Referencia.';
     flag=true;   
}

if((c=="") || (c==null)){
    $msg+='<br>Proporcione un número de cuenta.';
    flag=true;   
}
if((cl=="") || (cl==null)){
    $msg+='<br>Proporcione una CLABE interbancaria.';
    flag=true;  
}
if((c.length<10) && (c!="")){
  $msg+='<br>El número de cuenta no es del tamaño necesario.';
  flag=true;  
}
if((cl.length<18) && (cl!="")){
  $msg+='<br>La CLABE de cuenta no es del tamaño necesario.';
  flag=true;  
}
if(flag==true){
  $("#valMsgCountBank").show();
  $("#valMsgCountBank").empty();
  $("#valMsgCountBank").append($msg);
}
if(flag==false){

  dataObj = { 
            proceso: 'saveCountBank',
            ids:$('#sn_clave_empleado').val(), //ids,
            bank:$("#bank2").val(),
            ref:$("#ref").val(),
            cuenta: $('#cuenta').val(),
            clabe:$('#clabe').val()
         
          };
    //console.log($("#bank2").val());
   // muestraCargandoGeneral();
    $.ajax({
      async:false,
      cache: false,
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
       
           //$('#ModalCuentaCon').modal('hide');


           //muestraModalGeneral(4, titulo,data.contenido); 
            $("#valMsgCountBank").show();
            $("#valMsgCountBank").empty();
            $("#valMsgCountBank").append(data.contenido);
  
           contador=0;
            ocultaCargandoGeneral();
            $("#bank2").val("");
            $("#bank2").multiselect('select','');
            $("#ref").val("");
            $('#cuenta').val("");
            $('#clabe').val("");

            fnCountsBanksProv($('#id_nu_empleado').val());
        }else{
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
        ocultaCargandoGeneral();
    });
      // }else{
      //   muestraModalGeneral(4, titulo,'Ya se agregaron los datos bancarios proporcionados'); 
      // }
    }
}

$(document).on('cellbeginedit', '#datosCuentas', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'cuenta', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'concepto', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'diot', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'modificar', 'editable', false);

    
      
     
});


$(document).on('cellbeginedit', '#datosCuentasBancarias', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'idSupp', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'idRef', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'cuenta', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'clabe', 'editable', false);
    //$(this).jqxGrid('setcolumnproperty', 'modificar', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'desactivar', 'editable', false);
      
     
});


$(document).on('cellbeginedit', '#datosPartidas', function(event) {

    $(this).jqxGrid('setcolumnproperty', 'partida', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'descri', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'fecha', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'usuario', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'desactivar', 'editable', false);  
     
});

 function fnCountsBanksProv(provedor){

     var url ="modelo/SelectEmpleado_Modelo.php";
    dataObj = { 
        proceso: 'countsBanksProv',
        idSupp: provedor
    };
    //Obtener datos de las bahias
    $.ajax({
      async:false,
      cache: false,
          method: "POST",
          dataType:"json",
          url:url,
          data:dataObj
    })
    .done(function( data ) {
        if(data.result){
            datos=data.contenido.cuentasBancarias;

            fnLimpiarTabla('tablaCuentasBancarias', 'datosCuentasBancarias');

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: '', type: 'bool'},";
            columnasNombres += "{ name: 'idSupp', type: 'string' },";
            columnasNombres += "{ name: 'idRef', type: 'string'},";
            //columnasNombres += "{ name: 'idBank', type: 'string'},";
            columnasNombres += "{ name: 'cuenta', type: 'string'},"; 
            columnasNombres += "{ name: 'clabe', type: 'string'},"; 
            //columnasNombres += "{ name: 'modificar', type: 'string'},";        
            columnasNombres += "{ name: 'desactivar', type: 'string'},"; 
            columnasNombres += "]";

            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            //columnasNombresGrid += " { text: '', datafield: '', width: '6%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'ID proveedor',  datafield: 'idSupp', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Referencia',    datafield: 'idRef', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Cuenta bancaria',datafield: 'cuenta', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'CLABE',datafield: 'clabe', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
            //columnasNombresGrid += " { text: '',datafield: 'modificar', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: '',datafield: 'desactivar', width: '13%', align: 'center',hidden: false,cellsalign: 'center' },";
            //columnasNombresGrid += "   { text: '',datafield: 'clabe', width: '9%', align: 'center',hidden: false,cellsalign: 'center' },";

            columnasNombresGrid += "]";

            var columnasExcel = [ 0.1,2,3];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = data.contenido.nombreExcel;
            console.log("SQL= "+data.contenido.sql);

            fnAgregarGrid_Detalle(datos, columnasNombres, columnasNombresGrid, 'datosCuentasBancarias', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

            //$('#tablaCuentas > #datosCuentas').jqxGrid({width:'40%'});

        }
    })
    .fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos del proveedor"); 
        console.log("ERROR");
        console.log( result );
    });
}



function fnPartidas(provedor) {

    var url = modelo;
    dataObj = {
        proceso: 'partidas2',
        idSupp: provedor
    };

    $.ajax({
      async:false,
      cache: false,
            method: "POST",
            dataType: "json",
            url: url,
            data: dataObj
        })
        .done(function(data) {
            if (data.result) {
                datos = data.contenido.datos;

                fnLimpiarTabla('tablaPartidas', 'datosPartidas');

                columnasNombres = '';
                columnasNombres += "[";
                //columnasNombres += "{ name: '', type: 'bool'},";
                columnasNombres += "{ name: 'partida', type: 'string' },";
                columnasNombres += "{ name: 'descri', type: 'string' },";
                columnasNombres += "{ name: 'fecha', type: 'string'},";
                columnasNombres += "{ name: 'usuario', type: 'string'},";
                columnasNombres += "{ name: 'desactivar', type: 'string'},";
                columnasNombres += "]";

                columnasNombresGrid = '';
                columnasNombresGrid += "[";
                //columnasNombresGrid += " { text: '', datafield: '', width: '6%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
                columnasNombresGrid += " { text: 'Partida',  datafield: 'partida', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
                columnasNombresGrid += " { text: 'Descripción',  datafield: 'descri', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
                columnasNombresGrid += " { text: 'Fecha',    datafield: 'fecha', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
                columnasNombresGrid += " { text: 'Usuario',    datafield: 'usuario', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
                columnasNombresGrid += " { text: '',    datafield: 'desactivar', width: '13%', align: 'center',hidden: false,cellsalign: 'center' },";

                columnasNombresGrid += "]";

                var columnasExcel = [0, 1, 2, 3];
                var columnasVisuales = [0, 1, 2, 3, 4];
                nombreExcel = data.contenido.nombreExcel;

                fnAgregarGrid_Detalle(datos, columnasNombres, columnasNombresGrid, 'datosPartidas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);


            }
        })
        .fail(function(result) {
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(4, titulo, "Hubo un error al mostrar los datos del proveedor");
            console.log("ERROR");
            console.log(result);
        });
}



function fnGuardarPartidas(){
    var mensaje = '';
    mensaje = fnExistePartidas();
        
    if(mensaje==''){
        if($("#partidagenerica").val()!=null && ($("#partida2FF").val()!=0) ){
            console.log($("#partidagenerica").val() +" partidas");
             console.log($("#partida2FF").val() +"ff");
        muestraCargandoGeneral();
        datosarreglo = [];
        var i = 0;
        var formData = new FormData(document.getElementById('partidasForm'));
        formData.append("proceso", "partidas2guardar");
        formData.append("SupplierID", $('#sn_clave_empleado').val());
        
        $.ajax({
                url: modelo,
                async:false,
                cache: false,
                type: "post",
                dataType: "json",
                data: formData,                
                contentType: false,
                processData: false
            })
            .done(function(data) {

                if (data.result) {
                    info = data.contenido;
                    muestraModalGeneral(4, titulo, 'Partidas agregadas');
                    ocultaCargandoGeneral();
                    $('#ModalPartidas').modal('hide');
                    fnPartidas($('#id_nu_empleado').val());
                    $("#partidagenerica option:selected").prop("selected", false);
                    $("#partidagenerica").multiselect('rebuild');

                } else {
                    ocultaCargandoGeneral();
                }

            })
            .fail(function(result) {
                console.log("ERROR");
                console.log(result);
                muestraModalGeneral(4, titulo, "Hubo un error al guardar datos del proveedor"); 
                ocultaCargandoGeneral();
            });
        }else{
            muestraModalGeneral(4, titulo, 'No  hay partidas seleccionadas');
             $('#ModalPartidas').modal('hide');
        }
    } else {
        muestraModalGeneral(4, titulo, mensaje);
        $('#ModalPartidas').modal('hide');
    }

}


     
function  fnChecarCuentacontable(){
 
  dataObj = { 
            proceso: 'checarCuentaContable',
            cuenta:$("#cuentasCon").val()
          };
      // 
    muestraCargandoGeneral();
    $.ajax({
      async:false,
      cache: false,
          method: "POST",
          dataType:"json",
          url: modelo,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            mensaje=data.contenido;
          if(mensaje!=''){
              muestraModalGeneral(4, titulo,mensaje); 
              //fnSeleccionarDatosSelect("cuentasCon",0);
           }
     
           ocultaCargandoGeneral();
        }else{
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
        ocultaCargandoGeneral();
    });
   
}

/*function validaEmail(mail) {
 if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail)){
    return (true)
  }
     muestraModalGeneral(4, titulo,'Email no valido.'); 
    return (false)
}*/


function  fnExistePartidas(){
 var  retorno='';

        datosarreglo=[];
        
        dataObj = { 
            proceso:'existenPartidas',
            partidas: $('#partidagenerica').val(),
            SupplierID:$('#sn_clave_empleado').val()
          };

         $.ajax({
            url: modelo,
            async:false,
            cache: false,
            type: "post",
            dataType: "json",
            data: dataObj          
        })
        .done(function(data) {

            if (data.result) {
               retorno = data.contenido;
               //console.log(retorno);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            muestraModalGeneral(4, titulo, "Hubo un error al guardar datos del proveedor"); 
            ocultaCargandoGeneral();
        });

    return retorno;
   
}

/*function  fnMunicipio(sele='muni'){
  estado=0;
  if($('#EstadoSel').val()!=''){
    estado=$('#EstadoSel').val()
  }
        dataObj = { 
            proceso:'getMunicipios',
            estado: estado
          };
         
         $.ajax({
            url: modelo,

            type: "post",
            dataType: "json",
            data: dataObj,
            cache: false,
            async: false
        })
        .done(function(data) {

            if (data.result) {

              datos = data.contenido.datos;

                 select = fnCrearDatosSelect(datos,"","",0);

                $('#Address3').empty();
                $('#Address3').append('<select id="municipios" name="municipios" class="municipios"  required><option> Seleccionar.. </option>' +select+ '</select>');
                  
                  fnFormatoSelectGeneral(".municipios");
                 // $('#selectespecifica').show();
                  fnEvitarPartidaVacia();
                 if(sele!='muni'){
                    
                    $('#municipios').selectpicker('val', sele);
                    $('#municipios').multiselect('refresh');
                    $('.municipios').css("display", "none");
                     fnSeleccionarDatosSelect("municipios",sele);
                     console.log(sele +"--");
                  }
                ocultaCargandoGeneral();
            
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
             muestraModalGeneral(4, titulo,"Hubo un error al guardar datos del proveedor"); 
            ocultaCargandoGeneral();
        });

}*/


function rfc(rfc, aceptarGenerico = true) {
    
    const re = /^([A-ZÑ&]{3,4})?(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01]))?([A-Z\d]{2})([A\d])$/;
    var validado = rfc.match(re);
    if (!validado) //Coincide con el formato general del regex?
        return false;
    //Separar el dígito verificador del resto del RFC
    const digitoVerificador = validado.pop(),
        rfcSinDigito = validado.slice(1).join(''),
        len = rfcSinDigito.length,
        //Obtener el digito esperado
        diccionario = "0123456789ABCDEFGHIJKLMN&OPQRSTUVWXYZ Ñ",
        indice = len + 1;
    var suma,
        digitoEsperado;
    if (len == 12) suma = 0
    else suma = 481; //Ajuste para persona moral
    for (var i = 0; i < len; i++) suma += diccionario.indexOf(rfcSinDigito.charAt(i)) * (indice - i);
    digitoEsperado = 11 - suma % 11;
    if (digitoEsperado == 11) digitoEsperado = 0;
    else if (digitoEsperado == 10) digitoEsperado = "A";
    //El dígito verificador coincide con el esperado?
    // o es un RFC Genérico (ventas a público general)?
    if ((digitoVerificador != digitoEsperado) && (!aceptarGenerico || rfcSinDigito + digitoVerificador != "XAXX010101000")) return false;
    else if (!aceptarGenerico && rfcSinDigito + digitoVerificador == "XEXX010101000") return false;
    return rfcSinDigito + digitoVerificador;
}

function  validarRfcGenerico(rfc){

  var   retorno =false;
  const rfcFisicaFromato = /^([A-ZÑ\x26]{3,4}([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1]))([A-Z\d]{3})?$/ ;

  var match = rfcFisicaFromato.exec(rfc);
  //var   correctoRfcFisico = rfc.match(rfcFisicaFromato);
 
  if(match!=null){
    retorno=true;
  }else{
    retorno=false;
  }

  return  retorno;
}


function  validarCurp(rfc){

  var   retorno =false;
  const curpFromato = /^[a-zA-Z]{1}[AaEeIiOoUuXx]{1}[a-zA-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HhMm]{1}(AS|as|BC|bc|BS|bs|CC|cc|CS|cs|CH|ch|CL|cl|CM|cm|DF|df|DG|dg|GT|gt|GR|gr|HG|hg|JC|jc|MC|mc|MN|mn|MS|ms|NT|nt|NL|nl|OC|oc|PL|pl|QT|qt|QR|qr|SP|sp|SL|sl|SR|sr|TC|tc|TS|ts|TL|tl|VZ|vz|YN|yn|ZS|zs|NE|ne)[B-Db-dF-Hf-hJ-Nj-nP-Tp-tV-Zv-z]{3}[0-9a-zA-Z]{1}[0-9]{1}$/;

  var match = curpFromato.exec(rfc);

  //var   correctoRfcFisico = rfc.match(rfcFisicaFromato);
  
  if(match!=null){
    retorno=true;
  }else{
    retorno=false;
  }

  return  retorno;
}

$(document).on('change','#bank2',function(){
 
 //getClaveBank($(this).val());
 $("#cuenta").val("");
  $('#valMsgCountBank').hide();

});
function getClaveBank(valBank){

        dataObj = { 
            proceso:'getClabeBanco',
            bank: valBank
          };
         
         $.ajax({
            url: modelo,
            async:false,
            cache: false,
            type: "post",
            dataType: "json",
            data: dataObj            
        })
        .done(function(data) {

            if (data.result) {

              datos = data.contenido;
              
              $("#valClabe").val(datos);

                ocultaCargandoGeneral();
            
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
             muestraModalGeneral(4, titulo,"Hubo un error al verificar la clabe del banco"); 
            ocultaCargandoGeneral();
        });
}


function validarInterbancaria(){


        dataObj = { 
            proceso:'validarInterbancaria',
            clabe: $("#clabe").val(),
            bancoClabe:$("#valClabe").val(),
            nCuenta:$("#cuenta").val()

          };
         
         $.ajax({
            url: modelo,

            type: "post",
            dataType: "json",
            data: dataObj,
            cache: false,
            async: false
        })
        .done(function(data) {

            if (data.result) {

              datos = data.contenido;
              if(datos){
                   $("#valMsgCountBank").hide();
                   $("#valMsgCountBank").empty();
              }else{
                  
                  $("#valMsgCountBank").show();
                   $("#valMsgCountBank").empty();
                  $("#valMsgCountBank").append("<h5>La clabe interbancaria no es correcta</h5> ");
  
              }
            
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
             muestraModalGeneral(4, titulo,"Hubo un error al verificar la clabe interbancaria"); 
            ocultaCargandoGeneral();
        });
}


function validaNumLetrasGuion(e) {
    var tecla = (document.all) ? e.keyCode : e.which;
    //console.log("entra con el dato:"+e);

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla==8){
        return true;
    }

    if (  tecla== 43 || tecla==231 || tecla==46) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }

    // Patron de entrada, en este caso solo acepta numeros
    var patron = /[ñÑA-Za-z0-9\\-]/; //new RegExp("^[A-Z]{1,2}\\-"); ///[0-9-.]/; ///[^A-Za-z0-9]+/g; //var patron =/[^A-Za-z0-9\\-]+/g;
    var tecla_final = String.fromCharCode(tecla);

    return patron.test(tecla_final);
}
function ValidarPrimeraPosicion(e) {
   var tecla = (document.all) ? e.keyCode : e.which;
    //console.log("entra con el dato:"+e);

    //Tecla de retroceso para borrar, siempre la permite
    if (tecla==8){
        return true;
    }

    if (  tecla== 43 || tecla==231 || tecla==46) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }

    // Patron de entrada, en este caso solo acepta numeros
    var patron = /^[a-zA-Z][0-9]{11}$/; //new RegExp("^[A-Z]{1,2}\\-"); ///[0-9-.]/; ///[^A-Za-z0-9]+/g; //var patron =/[^A-Za-z0-9\\-]+/g;
    var tecla_final = String.fromCharCode(tecla);

    return patron.test(tecla_final);
}



function ValidarPrimeraPosicion(e) {
   
   var anterior='';
   anterior=e.target.value;
   var longitud=anterior.length;
   var flag=true;
   var tecla = (document.all) ? e.keyCode : e.which;
    
    if (tecla==8){
        return true;
    }

    if ( tecla== 45 ||  tecla== 43 || tecla==231 || tecla==46) {
        return false;
    }

    if (/[!òó+ç]/.test(tecla)) {
        return false;
    }
    if(anterior!='' && longitud==1){
    // Patron de entrada, en este caso solo acepta numeros
    var patron = /^[a-zA-Z]/; 
    var tecla_final =e.target.value; 
    
    flag=patron.test(tecla_final);
    console.log(flag);
    if(flag==true){
        flag=true;
      
    }else{
     
       e.target.value="";
       flag=false;
     }
    }else if(anterior!='' && longitud>=1){
       var patron = /[0-9]/; 
    var tecla_final =String.fromCharCode(tecla);
    
    flag=patron.test(tecla_final);
    }
    return flag;
}

function validaPrimerLetraDown(e, valor, len) {
    var elemento = e.target
      ,tecla = e.hasOwnProperty('keyCode') ? e.keyCode : e.which
    ,valText = String.fromCharCode(tecla).toLowerCase()
    ,valLength = valor.length
    ,fullText = ""+valor+valText
    ,len = len||11
    ,reg;
  if(tecla==8){ return true; }
  if(fullText.length==1){ reg = /^[a-zA-Z]{1}/g; }
  else{ reg = new RegExp("^[a-zA-Z]{1}[0-9]{"+valLength+","+len+"}","g"); }
  // console.log(tecla, valText, valor, fullText, fullText.length, reg, reg.test(fullText),reg.test(fullText)?fullText:valor);
    elemento.value = reg.test(fullText)?fullText:valor;
  return reg.test(fullText);
}

// $(document).on('cellselect', '#tablaCuentasBancarias > #datosCuentasBancarias', function(event) {
//     solicitudEnlace = event.args.datafield;

//     if (solicitudEnlace == 'desactivar') {
//             fila = event.args.rowindex;
//             accion='';
//             idSupp = $('#tablaCuentasBancarias > #datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'idSupp');  
//             cuenta = $('#tablaCuentasBancarias > #datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'cuenta');
//             clabe = $('#tablaCuentasBancarias > #datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'clabe');
//             desactivar = $('#tablaCuentasBancarias > #datosCuentasBancarias').jqxGrid('getcellvalue', fila, 'desactivar');
            
//             accion = $('<div>').append(desactivar).find('u').prop('class');
//             //console.log(accion);
//               $("#ModalGeneral1").modal('show');
//               $("#ModalGeneral1_Mensaje").empty();
//               $("#ModalGeneral1_Mensaje").append('¿Está seguro de '+accion+' la cuenta?');
//               $("#confirmacionModalGeneral1").click(function(){
//               $("#ModalGeneral1").modal('hide'); 
//                     fnDesactivarCuenta(idSupp,cuenta,clabe,accion);
//              });
            
//     }
// });

// $(document).on('cellselect', '#tablaPartidas > #datosPartidas', function(event) {
//     solicitudEnlace = event.args.datafield;

//     if (solicitudEnlace == 'desactivar') {
//             fila = event.args.rowindex;
//             accion='';
//             partida = $('#tablaPartidas > #datosPartidas').jqxGrid('getcellvalue', fila, 'partida');  
//             desactivar = $('#tablaPartidas > #datosPartidas').jqxGrid('getcellvalue', fila, 'desactivar');
            
//             accion = $('<div>').append(desactivar).find('u').prop('class');
//             //console.log(accion);
//               $("#ModalGeneral1").modal('show');
//               $("#ModalGeneral1_Mensaje").empty();
//               $("#ModalGeneral1_Mensaje").append('¿Está seguro de '+accion+' la partida?');
//               $("#confirmacionModalGeneral1").click(function(){
//               $("#ModalGeneral1").modal('hide'); 
//               accion=accion.toLowerCase();
//                     fnDesactivarPartida($('#sn_clave_empleado').val(),partida,accion);
//              });
            
//     }
// });



function fnDesactivarCuenta(idSupp, cuenta, clabe, accion) {
    //muestraCargandoGeneral();
      console.log(" fncuenta");
    dataObj = {
        proceso: 'desactivarCuenta',
        supp: idSupp,
        cuenta: cuenta,
        clabe: clabe,
        accion: accion

    };

    $.ajax({
            url: modelo,
            type: "post",
            dataType: "json",
            data: dataObj,
            cache: false,
            async: false
        })
        .done(function(data) {

            if (data.result) {
                fnCountsBanksProv($('#id_nu_empleado').val());
                muestraModalGeneral(4, titulo, data.contenido.mensaje);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            muestraModalGeneral(4, titulo, "Hubo un error al desactivar/activar cuenta bancaria.");
            //ocultaCargandoGeneral();
        });
}

function fnDesactivarPartida(idSupp, partida,accion) {
    //muestraCargandoGeneral();
     console.log(" fnpartida");
    dataObj = {
        proceso: 'desactivarPartida',
        supp: idSupp,
        partida: partida,
        accion: accion

    };

    $.ajax({
            url: modelo,
            type: "post",
            dataType: "json",
            data: dataObj,
            cache: false,
            async: false
        })
        .done(function(data) {

            if (data.result) {
                fnPartidas($('#id_nu_empleado').val());
                muestraModalGeneral(4, titulo, data.contenido.mensaje);
            }
        })
        .fail(function(result) {
            console.log("ERROR");
            console.log(result);
            muestraModalGeneral(4, titulo, "Hubo un error al desactivar/activar cuenta bancaria.");
           // ocultaCargandoGeneral();
        });
}
function fnMuestraModal(accion,leyenda,funcion=true){
    msj="";
    accion=accion.toLowerCase();
    if(accion=='activo'){
        msj="inactivar";
    }else{
        msj="activar";
    }
    $("#tipoMg").val(leyenda);
    $("#accionMg").val(accion);
    $("#ModalGeneral1").modal('show');
    $("#ModalGeneral1_Mensaje").empty();
    $("#ModalGeneral1_Mensaje").append('¿Está seguro de '+msj+' la '+leyenda+'?');
    $("#confirmacionModalGeneral1").prop('onclick', null);
   
    
    
}

function cargarLista(Elemento,res){
    window.cuentasMenores[Elemento] = new Array();
    window.textoMenores[Elemento] = new Array();
    window.cuentasMayores[Elemento] = new Array();
    window.textoMayores[Elemento] = new Array();

    menores = res.cuentasMenores;
    mayores = res.cuentasMayores;
    for(ad in menores){
        window.cuentasMenores[Elemento].push(menores[ad].value);
        window.textoMenores[Elemento].push(menores[ad].text);
    }
    for(ad in mayores){
        window.cuentasMayores[Elemento].push(mayores[ad].value);
        window.textoMayores[Elemento].push(mayores[ad].text);
    }
}

function fnSelectCuenta(valor='',idDiv,idBuscador,idHidden) {
    $(idDiv).hide();
    $(idDiv).empty();

    $(idBuscador).val(""+valor);
    $(idHidden).val(""+valor);

    if($("#cuentaDesde").val()!=""&&$("#cuentaHasta").val()!=""){
        if($("#cuentaDesde").val()>$("#cuentaHasta").val()){
            $("#ModalGeneralTam").width('600');
            muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', "La cuenta <strong>hasta</strong> no puede ser menor que la cuenta <strong>desde</strong>.");
            $("#cuentaHasta").val("");
            $("#cuenta__cuentaHasta").val("");
        }
    }
}

function vecesRepeticiones(data,busca) {
    var res = data.split(busca);
    if(res && res.length >0){
        return res.length - 1;
    }else{
        return 0;
    }
}
