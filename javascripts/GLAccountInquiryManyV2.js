//soliticitud arturo lopez peña
/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña
 * @version 0.1
 */
 
// objetos de configuración
    window.uePorUsuario = new Array();
    window.cuentasTotales = new Array();
    window.cuentasBancarias = new Array();

    if(typeof(Storage)!=="undefined"){
        window.uePorUsuario = ( typeof(window.localStorage.uePorUsuario)!=="undefined" ? JSON.parse(window.localStorage.uePorUsuario) : window.uePorUsuario );
        //window.cuentasTotales = ( typeof(window.localStorage.cuentasTotales)!=="undefined" ? JSON.parse(window.localStorage.cuentasTotales) : window.cuentasTotales );
        //window.cuentasBancarias = ( typeof(window.localStorage.cuentasBancarias)!=="undefined" ? JSON.parse(window.localStorage.cuentasBancarias) : window.cuentasBancarias );
    }
    window.listadoHabilitado = true;

    window.buscadores = [ "cuentaDesde", "cuentaHasta" ];
    window.buscadoresConfiguracion = {};
    $.each(window.buscadores,function(index,valor){
        window.buscadoresConfiguracion[valor] = {};
    });
    window.buscadoresConfiguracion.cuentaDesde.origenDatos = "cuentasTotales";
    window.buscadoresConfiguracion.cuentaHasta.origenDatos = "cuentasTotales";


    $( document ).ready(function() {
     
      //Mostrar Catalogo
      $('#test').hide();
      $('#test2').hide();

      
    });
function fnIniciarVariables(){ 
  window.contador=1;  
  window.titulo= '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
  window.modelo="modelo/GLAccountInquiryManyV2Modelo.php";
  window.htmlImpresionAuxMayor = '';
}

function fnCrearSelects(){
    
    dataObj = {
      proceso: 'getSelects'
    };
    $.ajax({
        method: "POST",
        dataType: "json",
        url: modelo,
        data: dataObj,
        async:false,
        cache:false
    })
    .done(function(data) {
        if (data.result) {
          /*Elemento = "cuentaDesde";
          window.cuentasMenores[Elemento] = new Array();
          window.textoMenores[Elemento] = new Array();
          window.cuentasMayores[Elemento] = new Array();
          window.textoMayores[Elemento] = new Array();

          menores = data.contenido.cuentasMenores;
          mayores = data.contenido.cuentasMayores;
          for(ad in menores){
              window.cuentasMenores[Elemento].push(menores[ad].value);
              window.textoMenores[Elemento].push(menores[ad].text);
          }
          for(ad in mayores){
              window.cuentasMayores[Elemento].push(mayores[ad].value);
              window.textoMayores[Elemento].push(mayores[ad].text);
          }
          Elemento = "cuentaHasta";
          window.cuentasMenores[Elemento] = window.cuentasMenores["cuentaDesde"];
          window.textoMenores[Elemento] = window.textoMenores["cuentaDesde"];
          window.cuentasMayores[Elemento] = window.cuentasMayores["cuentaDesde"];
          window.textoMayores[Elemento] = window.textoMayores["cuentaDesde"];*/

          //fnFormatoSelect(".cuentas",data.contenido.cuentas);
          fnFormatoSelect("#tipos",data.contenido.tipos);
          fnFormatoSelect("#tiposPoliza",data.contenido.tipoPolizas);
          ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("Error....");
       ocultaCargandoGeneral();
    });
}

function fnFormatoSelect(select,options){
    $(select).multiselect({
      enableFiltering: true,
      filterBehavior: 'text',
      enableCaseInsensitiveFiltering: true,
      buttonWidth: '100%',
      numberDisplayed: 1,
      includeSelectAllOption: true
    });
    $(select).multiselect('dataprovider', options);
    $('.multiselect-container').css({ 'max-height': "220px" });
    $('.multiselect-container').css({ 'overflow-y': "scroll" });
}

function fnEnviarDatos (form) {
    // event.preventDefault();
    muestraCargandoGeneral();
    var formData = new FormData(document.getElementById(form));
    
  //   for (var value of formData.values()) {
  //     console.log(value); 
  //  }
    var selectUnidadNegocio = $("#selectUnidadNegocio").val();
    if (selectUnidadNegocio == '' || selectUnidadNegocio == null || selectUnidadNegocio == 'undefined') {
      selectUnidadNegocio = '';
    }
    formData.append("proceso", "buscar");
    formData.append("urSel", selectUnidadNegocio);
    
    $.ajax({
        url: modelo,
        type: "post",
        dataType: "json",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        async:false
    })
    .done(function(data){
        sumCargo=0;
        sumAbono=0;
        saldo=0;
        auxAnt="";
        auxAct='';
        auxSig='';
        var html ='';
        head='<thead style="background-color:#1B693F !important; color:#FFF;"><th>Cuenta</th><th>Tipo</th><th>Número</th><th>Fecha</th><th>Concepto</th><th>Cargo</th><th>Abono</th><th>Saldo</th></thead>';
        head='<tr class="header-verde"><th>Cuenta</th><th>Tipo</th><th>Número</th><th>Fecha</th><th>Concepto</th><th style="text-align:center;">Cargo</th><th style="text-align:center;">Abono</th><th style="text-align:center;">Saldo</th></tr>';

        // Obtener número de niveles para agregar renglon de total agrupado
        var numNiveles = fnNivelesCuentaContableGeneral( $('#cuentaFrom').val() );
        var agregarFilaAgrupacion = 0;
        var cuentaAgrupada = '';
        var totalCuentaAgrupada = 0;
        if (numNiveles <= 4) {
          // Agregar fila de 
          agregarFilaAgrupacion = 1;
        }
        
        if(data.result){
          datos=data.contenido.datos;
          var cuentaAcumuladora='';
          var cuenta='';
          var cuentaAnt = '';
          var cuentaAntSola = '';
          var lblSaldo=0;
          var saldoInicial="0";
          var saldoInicialTotal="0";
          var nombreCuenta ='';
          var saldoAcumulado=0;
          var saldoTotal=0;
          var  cssAlign='style="text-align:right;"';

          html= '';
          html+='<table id="my-table" class="table  table-bordered">';

          var saldosIniciales = fnObtenerSaldoInicial();

          for(ad in datos){
            cuenta=datos[ad].account;

            if(ad==0){
              html+= '<tr style="background-color:white;"><td colspan="7" style="border: 0pt solid white;"><h5><b>SALDO INICIAL ACUMULADO</b></h5></td><td style="text-align:right; border: 0pt solid white;"><h5><b>$ '+fixDecimales2(fnObtenerSaldoInicialAcumulado(),2 )+'</b></5></td></tr>';
            }

            if(cuenta != cuentaAnt){

              if(cuentaAnt!=""){
                html+='<tr><td colspan="5" '+cssAlign+'><b>Total</b></td><td '+cssAlign+'> Cargo: '+fixDecimales2( sumCargo,2 )+'</td><td '+cssAlign+'> Abono: '+fixDecimales2(sumAbono,2 )+'</td><td></td></tr><tr><td></td><td colspan="7" '+cssAlign+' >Saldo Final: '+fixDecimales2( saldo,2 )+'</td></tr>';
              }

              nombreCuenta=datos[ad].cuenta;
              totalCuentaAgrupada = parseFloat( totalCuentaAgrupada ) + parseFloat( saldo );
              //console.log('saldo:'+saldo + ' total:'+totalCuentaAgrupada);

              if(ad!=0){
                html+= '<tr style="background-color:white;"><td colspan="8">&nbsp;</td></tr>';
              }

              html+= '<tr style="background-color:white;">';
              html+= '  <td colspan="7" style="border: 0pt solid white;" ><h6><b>'+nombreCuenta.toUpperCase()+'</b></h6></td>';
              var saldoIniciaIndividual ='0.00';
              for(info2 in saldosIniciales){
                saldoIniciaIndividual ='0.00';
                if(saldosIniciales[info2].cuenta == cuenta){
                  saldoIniciaIndividual = fixDecimales(saldosIniciales[info2].saldoInicial+"");
                  if(saldoIniciaIndividual==""){
                    saldoIniciaIndividual="0.00"
                  }
                  break;
                }
              }
              saldo=0;
              html+= '  <td style="text-align:right; border: 0pt solid white;">Saldo Inicial: $ '+saldoIniciaIndividual+'</td>';
              saldo=parseFloat(saldoIniciaIndividual);
              html+= '</tr>';
              html+= head;
              cuentaAnt = cuenta;

              sumCargo=0;
              sumAbono=0;
              
              saldoAcumulado=0;

            }

            var folio=0;
            if(datos[ad].cheque=='0'){
              folio=datos[ad].trans;
            }else{
              folio=datos[ad].cheque;
            }

            var datoCargo = '';
            if (datos[ad].cargo != '') {
              datoCargo = fixDecimales(datos[ad].cargo+"");
              sumCargo = parseFloat( sumCargo ) + parseFloat( datos[ad].cargo );
              saldo = parseFloat( saldo ) + parseFloat( datos[ad].cargo );
            }

            var datoAbono = '';
            if (datos[ad].abono != '') {
              datoAbono = fixDecimales( datos[ad].abono+"");
              sumAbono = parseFloat( sumAbono ) + parseFloat( datos[ad].abono );
              saldo = parseFloat( saldo ) - parseFloat( datos[ad].abono );
            } 

            saldoAcumulado = (sumCargo - sumAbono);

            

            var cta = cuenta.split(".",4);
            

            html+='<tr><td>'+cta[0]+'.'+cta[1]+'.'+cta[2]+'.'+cta[3]+'</td><td>'+datos[ad].tipo+'</td><td>'+folio+'</td><td>'+ datos[ad].fecha+'</td><td style="max-width: 450px;">'+ datos[ad].concepto+'</td><td '+cssAlign+'>'+ fixDecimales2(datoCargo,2) +'</td><td '+cssAlign+'>'+ fixDecimales2(datoAbono,2) +'</td></td><td '+cssAlign+'>'+ fixDecimales2(saldo,2 ) +'</td></tr>';

          }

          if (cuenta != '') {
            html+='<tr><td colspan="5" '+cssAlign+'><b>Total</b></td><td '+cssAlign+'> Cargo: '+fixDecimales2( sumCargo,2 )+'</td><td '+cssAlign+'> Abono: '+fixDecimales2( sumAbono,2 )+'</td><td></td></tr><tr><td></td><td colspan="7" '+cssAlign+'>Saldo Final: '+fixDecimales2( saldo ,2 )+'</td></tr></table>';
            totalCuentaAgrupada = parseFloat( totalCuentaAgrupada ) + parseFloat( saldo );

            // if (cuentaAgrupada != fnObtenerCuentaContableNivel(cuentaAntSola) && cuentaAgrupada != ''){
            html+='<tr><td colspan="5" style="text-align:right;"></td><td><h5><b>SALDO TOTAL: '+fixDecimales2( totalCuentaAgrupada,2 )+'</b></h5></td><td></td><td></td></tr>';
            cuentaAgrupada = '';
            totalCuentaAgrupada = 0;
            // }

            sumCargo=0;
            sumAbono=0;
            saldo=0;
            saldoAcumulado=0;
          }

          html+='</table>';

          $("#datosMovs").empty();
          if (isEmpty(html)) {
            $("#datosMovs").append(''+html);
          }
        }

        if(html==""||html=='<table class="table table-striped"></table>'){
          html= '';
          html+='<table class="table table-striped">';
          html+= '<tr style="background-color:white;"><td colspan="7" style="border: 0pt solid white; text-align: center;"><h5><b>No existe información que mostrar</b></h5></td></tr>';
          html+='</table>';
          $("#datosMovs").empty();
          $("#datosMovs").append(''+html);
        }

        htmlImpresionAuxMayor = html;
    
          $('#test').show();
          $('#test2').show();
    })
    .fail(function(result) {
      console.log("Error al  .");
      ocultaCargandoGeneral();
   });
}
function fixDecimales2(amount, decimals) {
    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.-]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0) 
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
        regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + ',' + '$2');

    return amount_parts.join('.');
}

function fnObtenerSaldoInicialAcumulado(){
  var saldo=0;
  var formData = new FormData(document.getElementById('criteriosFrm'));
  formData.append("proceso", "saldoInicialAcumulado");

  $.ajax({
    url: modelo,
    type: "post",
    dataType: "json",
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    async:false
  }).done(function(data){
    if(data.result){
      saldo=0;
      dataObjJSon=data.contenido.datos;
      if(dataObjJSon.length == 0){
        saldo=0;
      }
      for(info in dataObjJSon){
        saldo=dataObjJSon[info].saldoInicialAcumulado;
      }
    }else{
      saldo=0;
    }
  }).fail(function(result) {
    console.log("Error al obtener saldos iniciales.");
  });

  return saldo;
}

function fnObtenerSaldoInicial(){
  var dataObjJSon=0;
  var saldo=0;
  var formData = new FormData(document.getElementById('criteriosFrm'));
  formData.append("proceso", "cuentasaldoinicial");

  $.ajax({
    url: modelo,
    type: "post",
    dataType: "json",
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    async:false
  }).done(function(data){
    if(data.result){
      saldo=0;
      dataObjJSon=data.contenido.datos;
    }else{
      saldo=0;
      dataObjJSon=[];
    }
  }).fail(function(result) {
    console.log("Error al obtener saldos iniciales.");
  });

  return dataObjJSon;
}


function fnValidarCampos() {
  ue= $('#selectUnidadEjecutora').val();
  cf= $('#cuentaDesde').val();
  ct= $('#cuentaHasta').val();
  dd= $('#dateDesde').val();
  dh= $('#dateHasta').val();
  msg='';
  contador=0;
  flag=false;
  if((ue=='-1')) {
    msg+="<h6><span class='glyphicon glyphicon-remove-sign text-danger' style='' > </span>Falta UE<h6>";
    contador++;
  }
  if(cf=='') {
    msg+="<h6><span class='glyphicon glyphicon-remove-sign text-danger' style='' > </span>Falta Cuenta desde</h6> ";
     contador++;
  }
  if(ct=='') {
    msg+="<h6><span class='glyphicon glyphicon-remove-sign text-danger' style='' > </span>Falta Cuenta hasta </h6> ";
     contador++;
  }
  if(dd=='') {
    msg+="<h6><span class='glyphicon glyphicon-remove-sign text-danger' style='' > </span>Falta Fecha hasta </h6>";
     contador++;
  }
  if(dh=='') {
    msg+="<h6><span class='glyphicon glyphicon-remove-sign text-danger' style='' > </span>Falta Fecha desde</h6>  ";
     contador++;
  }

  if(contador>0){
    muestraModalGeneral(4, titulo,msg);
    flag=true;
  }

  return flag;
}

function fnGenerarReporteAuxMayor(tipo = 1) {
  var dateDesde = $("#dateDesde").val();
  var dateHasta = $("#dateHasta").val();
  var selectUnidadNegocio = $("#selectUnidadNegocio").val();
  if (selectUnidadNegocio == '' || selectUnidadNegocio == null || selectUnidadNegocio == 'undefined') {
    selectUnidadNegocio = '';
  }
  var selectUnidadEjecutora = $("#selectUnidadEjecutora").val();
  if (selectUnidadEjecutora == '' || selectUnidadEjecutora == null || selectUnidadEjecutora == 'undefined') {
    selectUnidadEjecutora = '';
  }
  var cuentaDesde = $("#cuentaDesde").val();
  var cuentaHasta = $("#cuentaHasta").val();
  var saldoInicialAco = fnObtenerSaldoInicialAcumulado();

  window.open("GLAccountInquiryManyV2Impresion.php?tipo="+tipo+"&dateDesde="+dateDesde+"&dateHasta="+dateHasta+"&selectUnidadNegocio="+selectUnidadNegocio+"&selectUnidadEjecutora="+selectUnidadEjecutora+"&cuentaDesde="+cuentaDesde+"&cuentaHasta="+cuentaHasta+"&saldoInicialAco="+saldoInicialAco, "_blank");
}

$(function(){
    fnIniciarVariables();
    muestraCargandoGeneral();
    setTimeout(function(){ 
      fnCrearSelects();

      // $('#selectUnidadNegocio').val('I6L');
      // $('#selectUnidadNegocio').multiselect('rebuild');

      // $('#selectUnidadEjecutora').val('00');
      // $('#selectUnidadEjecutora').multiselect('rebuild');

      // $('#cuentaFrom').val('1');
      // $('#cuentaFrom').multiselect('rebuild');

      // $('#cuentaTo').val('1.2.9.3');
      // $('#cuentaTo').multiselect('rebuild');

      // $('#dateDesde').val('01-01-2018');
      // $('#dateHasta').val('16-05-2018');

      // var Link_Panel = document.getElementById("filtrar");
      // Link_Panel.click();
    },1000);
    //ocultaCargandoGeneral();
    $(".campoListado").parent().innerWidth('100%');
    $(".campoListado").parent().parent().find('.col-md-3').hide();
    $("#filtrar").click(function(){
      flag=false;
      flag= fnValidarCampos();
      if(!flag){
        fnEnviarDatos("criteriosFrm");
      }
    });

    // $("#btnExportaPdf").click(function(){
    //   // impresion pdf y excel
    //   console.log("htmlImpresionAuxMayor: "+htmlImpresionAuxMayor);

    //   fnGenerarReporteAuxMayor(1);
    // });

    $("#btnExportaExcel").click(function(){
      // impresion pdf y excel
      console.log("htmlImpresionAuxMayor: "+htmlImpresionAuxMayor);

      fnGenerarReporteAuxMayor(2);
    });
});
























