primeraVezSet2 = true;
var asignadosrows = [];
var eliminadosrows = [];

function fnAbrirReporte() {

}

//
function fnCambiarEmpleado() {
  $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: { cargarResguardo_porEmpleado: true, aEmpleado: $("#selectEmpleado").val() },
            async: false
        })
        .done(function(data) {
            if(data.result){
            //Si trae informacion
            //info=data.contenido.datosCatalogo;
            
            //fnAgregarGridv2(info, 'divCatalogo', "busqueda");
            dataReasignacionJason = data.contenido.infolistadeactivos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;
            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [1, 2, 3, 4];
            var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
            fnLimpiarTabla('divTablaResguardoEmpleado', 'divResguardoDelEmpleado');
            asignadosrows = [];
            eliminadosrows = [];
            
            fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divResguardoDelEmpleado', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);
            
        }
        });
}
 

function fnFiltrarResguardos() {
  alert($('#selectStatustab2').val());
  alert($('#selectEmpleadotab2').val());

  var filtergroup = new $.jqx.filter();

  var filtervalue = $('#selectStatustab2').val();
  var filtercondition = 'EQUAL';
  var filter1 = filtergroup.createfilter('stringfilter', filtervalue, filtercondition);
  var filter_or_operator = 1;
  filtergroup.addfilter(filter_or_operator, filter1);
  $("#divResguardos").jqxGrid('addfilter', 'estatus', filtergroup);
  $("#divResguardos").jqxGrid('applyfilters');


}

function fnCargarInicioResguardos(refrescar) {
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "modelo/activofijo_modelo.php",
      data: { cargarinicio_resguardos: true },
      async: false
  })
  .done(function(data) {
    if(data.result){
      //Si trae informacion
      //info=data.contenido.datosCatalogo;
      
      //fnAgregarGridv2(info, 'divCatalogo', "busqueda");
      dataReasignacionJason = data.contenido.infolistadeactivos;
      columnasNombres = data.contenido.columnasNombres;
      columnasNombresGrid = data.contenido.columnasNombresGrid;
      var nombreExcel = data.contenido.nombreExcel;
      var columnasExcel= [1, 2, 3, 4];
      var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
      fnLimpiarTabla('divTabla', 'divCatalogo');
      asignadosrows = [];
      fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divCatalogo', ' ', 1, columnasExcel, false, true, "", columnasVisuales, nombreExcel);

      $("#divCatalogo").bind('cellendedit', function (event) {
        if (event.args.value) {
            $("#divCatalogo").jqxGrid('selectrow', event.args.rowindex);
        }else {
            $("#divCatalogo").jqxGrid('unselectrow', event.args.rowindex);
        }
      }); 
    }
  });
}

function fnEliminarRenglonDelResguardo(aID, aIDRow, aEr=false) {
  var idAEliminar = -1;

  for (i=0; i<$('#divResguardoDelEmpleado').jqxGrid('getrows').length;i++) {
    var rowid = $('#divResguardoDelEmpleado').jqxGrid('getrowid', i);
    var data = $('#divResguardoDelEmpleado').jqxGrid('getrowdatabyid', rowid);
    if (data.Eliminar.includes(","+aIDRow+",")) {
      //alert(data.Eliminar);
      idAEliminar = i;
    }

  }
  if (idAEliminar >= 0) {
   var rowid = $('#divResguardoDelEmpleado').jqxGrid('getrowid', idAEliminar);
    var data = $('#divResguardoDelEmpleado').jqxGrid('getrowdatabyid', rowid);
    //
    /*$("#divCatalogo").jqxGrid('beginrowedit', rowid);
    $("#divCatalogo").jqxGrid('setcellvalue', rowid, 'Modificar', '<a>Asignado</a>' );
    var data = $('#divCatalogo').jqxGrid('getrowdata', rowid);*/
    
    eliminadosrows.push(data.assetid);

    //alert(data.assetid);

    var commit = $("#divResguardoDelEmpleado").jqxGrid('deleterow', rowid);
  }
//    $("#divResguardoDelEmpleado").jqxGrid('endrowedit', rowid);

}

function fnCrearResguardo() {

  var rows = $("#divCatalogo").jqxGrid('selectedrowindexes');
   asignadosrows =  [];
  for (var m = 0; m < rows.length; m++) {
      var row = $("#divCatalogo").jqxGrid('getrowdata', rows[m]);
      asignadosrows[asignadosrows.length] = row['assetid'];
  }

  
    if (asignadosrows.length == 0 && eliminadosrows.length == 0) {
      muestraMensaje('No hay activos seleccionados para asignación o eliminación del resguardo.', 3, 'mensajesValidaciones', 5000);
      return;
    }




    muestraCargandoGeneral();
  $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: { crearResguardo: true, idResguardo : asignadosrows, idEliminados : eliminadosrows, aEmpleado: $("#selectEmpleado").val()},
            async: false
        })
        .done(function(data) {
            if(data.contenido){
            //Si trae informacion
            //info=data.contenido.datosCatalogo;
            if (data.contenido.folioResguardo > 0)  {
          
            muestraMensaje('se creó el resguardo con el folio:' + data.contenido.folioResguardo, 3, 'mensajesValidaciones', 5000);
            fnImprimir(data.contenido.folioResguardo, true);
            primeraVezSet2 =false;
            $('.nav-tabs a[href="#set2"]').tab('show');
            
            //fnCargarInicioResguardos(data.contenido.folioResguardo);
            //fnCambiarEmpleado();
            fnCargarPrimeraVezSet2("", false);

            $('#oculto').append($('#paso2'));
            $('#oculto').hide();

            $('.modal-body').append($('#viewReporte'));
            $('#oculto').append($('#empleadowizard'));
            

           
         }
            
            
          } else {
            ocultaCargandoGeneral();
          }
        })
        .fail(function (data) {
          ocultaCargandoGeneral();

        });
  ocultaCargandoGeneral();

    


}

// function fnBuscar() {


//   $('#oculto').append($('#empleadowizard'));
//    $('#oculto').append($('#paso1'));
//    $('#oculto').hide();


//    if ($("#txtFechaInicial").val()=="") {
//     muestraMensaje('Capture fecha inicial', 1, 'divMensajeOperacion', 5000);
//     return;
//    }

//    if ($("#txtFechaFinal").val()=="") {
//     muestraMensaje('Capture fecha final', 1, 'divMensajeOperacion', 5000);
//     return;
//    }




//    muestraCargandoGeneral();
//   $.ajax({
//             method: "POST",
//             dataType: "json",
//             url: "modelo/activofijo_modelo.php",
//             data: { cargarinicio_resguardosbtnBuscar: true, Empleado: $('#selectEmpleadotab2').val(), Estatus : $('#selectStatustab2').val(), fechainicial: $("#txtFechaInicial").val(), fechafinal: $("#txtFechaFinal").val() },
//             async: false
//         })
//         .done(function(data) {
//             if(data.result){
//             //Si trae informacion
//             //info=data.contenido.datosCatalogo;
            
//             //fnAgregarGridv2(info, 'divCatalogo', "busqueda");
//             dataReasignacionJason = data.contenido.infolistadeactivos;
//             columnasNombres = data.contenido.columnasNombres;
//             columnasNombresGrid = data.contenido.columnasNombresGrid;

            
//             var nombreExcel = data.contenido.nombreExcel;
//             var columnasExcel= [1, 2, 3, 4];
//             var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
//             fnLimpiarTabla('divTabla2', 'divResguardos');

          
            
//             fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divResguardos', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);
//           }
//         });
//   ocultaCargandoGeneral();

// }

function fnImprimir(aFolio, aEnWizard) {
  if (aEnWizard==false) {

    $('#set2').append($('#viewReporte'));


  }
  aDato = "PrintSituacionFinanciera.php?Folio="+aFolio+"&reporte=resguardo";
   $("#viewReporte").html('<object data="'+ aDato+'" width="100%" height="100%" type="application/pdf">         <embed src="'+ aDato+'" type="application/pdf" />     </object>');  
}

function fnCargarPrimeraVezSet2(aFiltro, refrescarEmpleados) {
  muestraCargandoGeneral();
  $.ajax({
            method: "POST",
            dataType: "json",
            url: "modelo/activofijo_modelo.php",
            data: { cargarinicio_resguardos2tab: true },
            async: false
        })
        .done(function(data) {
            if(data.result){
            //Si trae informacion
            //info=data.contenido.datosCatalogo;
            
            //fnAgregarGridv2(info, 'divCatalogo', "busqueda");
            dataReasignacionJason = data.contenido.infolistadeactivos;
            columnasNombres = data.contenido.columnasNombres;
            columnasNombresGrid = data.contenido.columnasNombresGrid;

            fnFiltrarPorCuenta(data.contenido.estatusResguardos, null, '#selectStatustab2');
            fnSeleccionarDatosSelect('selectStatustab2', 'actual');
            var nombreExcel = data.contenido.nombreExcel;
            var columnasExcel= [1, 2, 3, 4];
            var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
            fnLimpiarTabla('divTabla2', 'divResguardos');

            if (refrescarEmpleados==true) {
            fnFiltrarPorCuenta(data.contenido.infoempleados, null, '#selectEmpleado');
            fnFiltrarPorCuenta(data.contenido.infoempleados, null, '#selectEmpleadotab2');
          }

            

            //$('#selectStatustab2').

            
            fnAgregarGrid_Detalle(dataReasignacionJason, columnasNombres, columnasNombresGrid, 'divResguardos', ' ', 1, columnasExcel, false, false, "", columnasVisuales, nombreExcel);
            fnCargarInicioResguardos(0);
            if (aFiltro!="") {

              var checkExist = setInterval(function() {
   if ($('#searchFielddivResguardos').length) {
      $("#searchFielddivResguardos").val("Resguardo-"+aFiltro);
      $("#searchFielddivResguardos").keydown();
      
      clearInterval(checkExist);
   }
}, 100);
              

             

            }
            
            
        }
        });
  ocultaCargandoGeneral();
}

function Edit(row, event) {
    that.editrow = row;
    $("#grid").jqxGrid('beginrowedit', row);
    if (event) {
        if (event.preventDefault) {
            event.preventDefault();
        }
    }
    return false;
}

function Update(row, event) {
    that.editrow = -1;
    $("#grid").jqxGrid('endrowedit', row);
    if (event) {
        if (event.preventDefault) {
            event.preventDefault();
        }
    }
    return false;
}

function Cancel(row, event) {
    that.editrow = -1;
    $("#grid").jqxGrid('endrowedit', row, true);
    if (event) {
        if (event.preventDefault) {
            event.preventDefault();
        }
    }
    return false;
}



function fnFiltrarPorCuenta(aInfo, aFiltro, aControl) {
    contenido = "";
    for (x in aInfo) {
        if (aFiltro == null)
            contenido += '<option value="' + aInfo[x][Object.getOwnPropertyNames(aInfo[x])[0]] + '">' + aInfo[x][Object.getOwnPropertyNames(aInfo[x])[1]] + '</option>';
        else
        if (aInfo[x][Object.getOwnPropertyNames(aInfo[x])[0]].startsWith(aFiltro))
            contenido += '<option value="'+ aInfo[x][Object.getOwnPropertyNames(aInfo[x])[0]] + '">' + aInfo[x][Object.getOwnPropertyNames(aInfo[x])[1]] + '</option>';
    } 

    $(aControl).html(contenido);
    $(aControl).multiselect({
        enableFiltering: true,
        filterBehavior: 'text',
        includeSelectAllOption: true


    });
}


function fnAgregarCatalogoModal(){

     muestraModalGeneral(4, "Editar activo fijo", "<object type='text/html' data='activofijo.php?modal=true&new=true' width='100%' height='100%' ></object>");


   /*$.ajax({
       data:  {},
       url:   'activofijo.php',
       type:  'post',
       beforeSend: function () {
           // document.getElementById('tareas').innerHTML = "<br><br><br><br><br><div style='width:1000px;height:100px;margin:auto;text-align:center'> <img src='images/enProceso.gif' width=50 height=50 align='center'  /></div><br>";
       },
       success:  function (response) {
            //$("#MyProducto").html(response);
          muestraModalGeneral(4, "Editar activo fijo", response);
       }
   });*/
}


function fnRenderEdit(row, column, value) {
  alert('a');
                          var eventName = "onclick";
                          if ($.jqx.mobile.isTouchDevice()) {
                              eventName = "on" + $.jqx.mobile.getTouchEventName('touchstart');
                          }

                          if (row === that.editrow) {
                              return "<div style='text-align: center; width: 100%; top: 7px; position: relative;'><a " + eventName + "='Update(" + row + ", event)' style='color: inherit;' href='javascript:;'>Update</a><span style=''>/</span>" + "<a " + eventName + "='Cancel(" + row + ", event)' style='color: inherit;' href='javascript:;'>Cancel</a></div>";
                          }

                          return "<a " + eventName + "='Edit(" + row + ", event)' style='color: inherit; margin-left: 50%; left: -15px; top: 7px; position: relative;' href='javascript:;'>Edit</a>";
                      

}


function fnPasarPaso2() {
  $('#oculto').append($('#paso1'));
  $('#paso1').hide();
  $('#paso2').show();
  $('.modal-body').append($('#paso2'));
  fnCargarInicioResguardos(0);

}


function fnModificarResguardo(aUserID) {

  if (aUserID != 0) {

  fnSeleccionarDatosSelect('selectEmpleado', aUserID);
  fnCambiarEmpleado();
}

  muestraModalGeneral();


    $('#oculto').hide();

  $('.modal-body').append($('#empleadowizard'));
  $('.modal-body').append($('#paso1'));

  $('#oculto').append($('#viewReporte'));


  $('#empleadowizard').show();
  $('#oculto').append($('#paso2'));
  $('#paso1').show();


  

}



function fnModificarActivoFijo(aID, aIDRow, aEr=false){

  if ($('#selectEmpleado').val() == 0) {

    muestraMensaje('Seleccione primero un empleado.', 3, 'mensajesValidaciones', 5000);
    
    return;
  }

    var rowid = $('#divCatalogo').jqxGrid('getrowid', aIDRow);
    var data = $('#divCatalogo').jqxGrid('getrowdatabyid', rowid);
    //
    $("#divCatalogo").jqxGrid('beginrowedit', rowid);
    $("#divCatalogo").jqxGrid('setcellvalue', rowid, 'Modificar', '<a>Asignado</a>' );
    var data = $('#divCatalogo').jqxGrid('getrowdata', rowid);
    
    asignadosrows.push(data.assetid);


    var row = {};

    row["assetid"]  = data["assetid"];
    row["barcode"]  = data["barcode"];
    row["fecha"]  = new Date();

    
    var commit = $("#divResguardoDelEmpleado").jqxGrid('addrow', null, row);



    $("#divCatalogo").jqxGrid('endrowedit', rowid);
}

function fnGenerarResguardo(){

  var err = 0;
  var mensaje="";

  if($('#selectUnidadNegocio_modal').val() == -1){
    err+=1;
    mensaje+="<p>El campo UR es obligatorio</p>";
  }
  if($('#selectUnidadEjecutora_modal').val() == -1){
    err+=1;
    mensaje+="<p>El campo UE es obligatorio</p>";
  }
  if($('#selectEmpleados_modal').val() == -1){
    err+=1;
    mensaje+="<p>El campo Empleado es obligatorio</p>";
  }
  if($('#selectUnidadEjecutora_modal').val() == -1){
    err+=1;
    mensaje+="<p>El campo Activo es obligatorio</p>";
  }
   
  if(err>=1){
    $('#modalMsg').addClass('show');
    muestraMensajeTiempo(mensaje, 3, 'modalMsg', 5000);
    return false;
  }

  //Agregar todos los componentes
  //var fd = new FormData(document.getElementById('frmGenerarResguardo'));
  var fd = new FormData();

  // //Aregamos el option 
  fd.append("option","generarResguardo");
  fd.append("selectUnidadNegocio_modal",fnObtenerOption('selectUnidadNegocio_modal'));
  fd.append("selectUnidadEjecutora_modal",fnObtenerOption('selectUnidadEjecutora_modal'));
  fd.append("txtObservaciones",$('#txtObservaciones').val());
  fd.append("selectEmpleados_modal",fnObtenerOption('selectEmpleados_modal'));
  // fd.append("selectPatrimonio_modal",fnObtenerOption('selectPatrimonio_modal'));

  $.ajax({
      async:false,
      url:"modelo/activofijo_modelo.php",
      type:'POST',
      data: fd,
      cache: false,
      contentType: false,
      processData: false,
      dataType: 'json',
      success: function (data) {
          $('#ModalUR').modal('hide');
          if(data.result){
            fnMostrarDatos('');
            muestraMensaje(data.Mensaje, 1, 'divMensajeOperacion', 5000);
          }else{
              muestraMensaje(data.Mensaje, 3, 'divMensajeOperacion', 5000);
          }
      }   
  }); 

}

function fnAgregar(){
  window.location.href = "resguardo_detalles.php";

  //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
  //SE CAMBIO EL PROCESOS SE COMENTO CODIGO ANTERIOR
  //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

  // $('#divMensajeOperacion').empty();
  // $('#divMensajeOperacion').addClass('hide');

  // $('#modalMsg').empty();
  // $('#modalMsg').addClass('hide');

  // fnTraeActivoFijo('selectUnidadNegocio_modal','selectPatrimonio_modal');
  // fnTraeEmpleados('selectUnidadNegocio_modal','selectEmpleados_modal');

  // var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Resguardo</h3>';
  // $('#ModalUR_Titulo').empty();
  // $('#ModalUR_Titulo').append(titulo);
  // $('#ModalUR').modal('show');
}


function fnMostrarDatos(idfinalidad){
  //console.log("fnMostrarDatos");

  //var fd = new FormData(document.getElementById('frmFiltros'));
  var fd = new FormData();
  fd.append("option","mostrarRegistros");
  fd.append("selectUnidadNegocio",fnObtenerOption('selectUnidadNegocio'));
  fd.append("selectUnidadEjecutora",fnObtenerOption('selectUnidadEjecutora'));
  // fd.append("selectPatrimonio",fnObtenerOption('selectPatrimonio'));
  fd.append("txtFolio",$('#txtFolio').val());
  fd.append("selectEmpleadotab2",fnObtenerOption('selectEmpleadotab2'));
  // fd.append("txtFechaInicial",$('#txtFechaInicial').val());
  // fd.append("txtFechaFinal",$('#txtFechaFinal').val());


  //Obtener datos de las bahias
  $.ajax({
    async:false,
    url:"modelo/activofijo_modelo.php",
    type:'POST',
    data: fd,
    cache: false,
    contentType: false,
    processData: false,
    dataType: 'json',
    success: function (data) {
      if(data.result){
        dataFinalidadJason = data.contenido.datos;
        columnasNombres = data.contenido.columnasNombres;
        columnasNombresGrid = data.contenido.columnasNombresGrid;
      
        fnLimpiarTabla('divTabla2', 'divResguardos');

        var nombreExcel = data.contenido.nombreExcel;

        var columnasExcel= [0,1,2,4,5,6,7,8,9];
        var columnasVisuales= [0,1,3,4,5,6,7,8,9];
        fnAgregarGrid_Detalle(dataFinalidadJason, columnasNombres, columnasNombresGrid, 'divResguardos', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
       
        }   
    }
  }); 

}

function fnDetalles(folio){
  alert(folio);
}



function fnTraeActivoFijo(componenteOrigen,componenteDestino){
  var fd = new FormData();
  
  fd.append("option","muestraPatrimonio");
  fd.append("ur",fnObtenerOption(componenteOrigen));

  //Obtener datos de las bahias
  $.ajax({
    async:false,
    url:"modelo/componentes_modelo.php",
    type:'POST',
    data: fd,
    cache: false,
    contentType: false,
    processData: false,
    dataType: 'json',
    success: function (data) {
      if(data.result){
        var seleccionado="";
        var contenido="";
        var opcionDefault = 1;

        dataJson = data.contenido;
        
        if ($("#"+componenteDestino).prop("multiple")) {
            var opcionDefault = 0;
        }

        if (dataJson.length == 1) {
            seleccionado = " selected ";
        } else if (opcionDefault == 1) {
            // Si tiene mas opciones mostrar opcion de seleccion
            contenido += "<option value='-1'>Seleccionar...</option>";
        }

        for (var info in dataJson) {
          contenido += "<option value='" + dataJson[info].val + "'" + seleccionado + ">" + dataJson[info].text + "</option>";
        }

        //$('#selectRazonSocial').html(contenidoDependencia);
        $('#' + componenteDestino).empty();
        $('#' + componenteDestino).append(contenido);
        $('#' + componenteDestino).multiselect('rebuild');
      }   
    }
  }); 
}

function fnTraeEmpleados(componenteOrigen,componenteOrigen2,componenteDestino){
  var fd = new FormData();
  
  fd.append("option","muestraEmpleados");
  fd.append("ur",fnObtenerOption(componenteOrigen));
  fd.append("ue",fnObtenerOption(componenteOrigen2));

  //Obtener datos de las bahias
  $.ajax({
    async:false,
    url:"modelo/componentes_modelo.php",
    type:'POST',
    data: fd,
    cache: false,
    contentType: false,
    processData: false,
    dataType: 'json',
    success: function (data) {
      if(data.result){
        var seleccionado="";
        var contenido="";
        var opcionDefault = 1;

        dataJson = data.contenido;
        
        if ($("#"+componenteDestino).prop("multiple")) {
            var opcionDefault = 0;
        }

        if (dataJson.length == 1) {
            seleccionado = " selected ";
        } else if (opcionDefault == 1) {
            // Si tiene mas opciones mostrar opcion de seleccion
            contenido += "<option value='-1'>Seleccionar...</option>";
        }

        for (var info in dataJson) {
          contenido += "<option value='" + dataJson[info].val + "'" + seleccionado + ">" + dataJson[info].text + "</option>";
        }

        //$('#selectRazonSocial').html(contenidoDependencia);
        $('#'+componenteDestino).empty();
        $('#'+componenteDestino).append(contenido);
        $('#'+componenteDestino).multiselect('rebuild');
      }   
    }
  }); 
}

// function fnTraeEpleados(ur, ue, componente){

// }

function fnObtenerOption(componenteOrigen) {
    var option = "";
    var selectComponenteOrigen = document.getElementById(''+componenteOrigen);

    for ( var i = 0; i < selectComponenteOrigen.selectedOptions.length; i++) {
        //console.log( unidadesnegocio.selectedOptions[i].value);
      if(selectComponenteOrigen.selectedOptions[i].value !="-1"){
        if (i == 0) {
          option = "'"+selectComponenteOrigen.selectedOptions[i].value+"'";
        }else{
          option = option+", '"+selectComponenteOrigen.selectedOptions[i].value+"'";
        }
      }
    }
    console.log(option);
    return option;
}


$(document).ready(function() {
    //Mostrar Catalogo

    /*$("ul.nav-tabs a").click(function(e) {
        e.preventDefault();
        $(this).tab('show');
    });*/

    

    //fnCargarPrimeraVezSet2("", true);
    //$('#selectEmpleado').hide();

    $('#btnGenerarResguardo').click(function(){
      fnGenerarResguardo();
    });

    fnMostrarDatos('');

    $("#selectUnidadEjecutora_modal").change(function(){
      // fnObtenerEmpleados();
    });

    $('#selectUnidadNegocio').change(function(){
      fnCambioUnidadResponsableGeneral('selectUnidadNegocio', 'selectUnidadEjecutora');
      // fnTraeActivoFijo('selectUnidadNegocio','selectPatrimonio');
      fnTraeEmpleados('selectUnidadNegocio','selectUnidadEjecutora','selectEmpleadotab2');
    });

    $('#selectUnidadNegocio_modal').change(function(){
      fnCambioUnidadResponsableGeneral('selectUnidadNegocio_modal', 'selectUnidadEjecutora_modal');
      fnTraeActivoFijo('selectUnidadNegocio_modal','selectPatrimonio_modal');
      fnTraeEmpleados('selectUnidadNegocio_modal','selectUnidadEjecutora_modal','selectEmpleados_modal');
    });

    $('#selectUnidadEjecutora_modal').change(function(){
      //fnTraeActivoFijo('selectUnidadNegocio_modal','selectPatrimonio_modal');
      fnTraeEmpleados('selectUnidadNegocio_modal','selectUnidadEjecutora_modal','selectEmpleados_modal');
    });

    $('#selectUnidadEjecutora').change(function(){
      //fnTraeActivoFijo('selectUnidadNegocio_modal','selectPatrimonio_modal');
      fnTraeEmpleados('selectUnidadNegocio','selectUnidadEjecutora','selectEmpleadotab2');
    });

    if($('#selectUnidadNegocio').val() != "-1"){
        fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora');
    }

    if($('#selectUnidadEjecutora').val() != "-1"){
        fnTraeEmpleados('selectUnidadNegocio','selectUnidadEjecutora','selectEmpleadotab2');
    }



    /*$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
      var target = $(e.target).attr("href") // activated tab

      if (target == '#set2') {
        if (primeraVezSet2) {
          primeraVezSet2 = false;
          fnCargarPrimeraVezSet2("");



        }
      }
    });
    */



   //fnCargarInicioResguardos(0);

    //fnMostrarDatos('');

    //fnCambioUnidadNegocio();

    //fnFiltrarPorCuenta(info, '2.2.2', '#selectSituacionFinancieraDocumentosporPagaraLargoPlazo');


});